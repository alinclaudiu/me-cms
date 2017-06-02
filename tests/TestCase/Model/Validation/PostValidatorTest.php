<?php
/**
 * This file is part of MeCms.
 *
 * MeCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright   Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link        http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Test\TestCase\Model\Validation;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * PostValidatorTest class
 */
class PostValidatorTest extends TestCase
{
    /**
     * @var \MeCms\Model\Table\PostsTable
     */
    protected $Posts;

    /**
     * Example data
     * @var array
     */
    protected $example;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.posts',
        'plugin.me_cms.tags',
    ];

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->Posts = TableRegistry::get(ME_CMS . '.Posts');

        $this->example = [
            'category_id' => 1,
            'user_id' => 1,
            'title' => 'My title',
            'slug' => 'my-slug',
            'text' => 'My text',
        ];
    }

    /**
     * Test validation.
     * It tests the proper functioning of the example data.
     * @test
     */
    public function testValidationExampleData()
    {
        $this->assertEmpty($this->Posts->newEntity($this->example)->getErrors());

        foreach (array_keys($this->example) as $key) {
            //Create a copy of the example data and removes the current value
            $copy = $this->example;
            unset($copy[$key]);

            $this->assertEquals([
                $key => ['_required' => 'This field is required'],
            ], $this->Posts->newEntity($copy)->getErrors());
        }
    }

    /**
     * Test validation for `category_id` property
     * @test
     */
    public function testValidationForCategoryId()
    {
        $this->example['category_id'] = 'string';
        $this->assertEquals([
            'category_id' => ['naturalNumber' => 'You have to select a valid option'],
        ], $this->Posts->newEntity($this->example)->getErrors());
    }

    /**
     * Test validation for `tags` property
     * @test
     */
    public function testValidationForTags()
    {
        foreach (['ab', str_repeat('a', 31)] as $value) {
            $this->example['tags_as_string'] = $value;
            $this->assertEquals([
                'tags' => ['validTagsLength' => 'Each tag must be between 3 and 30 chars'],
            ], $this->Posts->newEntity($this->example)->getErrors());
        }

        foreach (['Abc', 'ab$', 'ab-c', 'ab_c'] as $value) {
            $this->example['tags_as_string'] = $value;
            $this->assertEquals([
                'tags' => ['validTagsChars' => 'Allowed chars: lowercase letters, numbers, space'],
            ], $this->Posts->newEntity($this->example)->getErrors());
        }

        foreach (['abc', str_repeat('a', 30)] as $value) {
            $this->example['tags_as_string'] = $value;
            $this->assertEmpty($this->Posts->newEntity($this->example)->getErrors());
        }

        foreach ([
            'first, second',
            'first,  second',
            'first , second',
            'first ,second',
            'first  ,second',
            'first,second',
            ' first, second',
            'first, second ',
            ' first, second ',
            ' first , second ',
        ] as $value) {
            $this->assertEmpty($this->Posts->newEntity($this->example)->getErrors());
        }
    }
}
