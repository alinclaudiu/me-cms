<?php
declare(strict_types=1);
/**
 * This file is part of me-cms.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright   Copyright (c) Mirko Pagliai
 * @link        https://github.com/mirko-pagliai/me-cms
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */

namespace MeCms\Test\TestCase\Model\Entity;

use MeCms\TestSuite\EntityTestCase;

/**
 * UserTest class
 */
class UserTest extends EntityTestCase
{
    /**
     * Called after every test method
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();

        @unlink_recursive(USER_PICTURES, 'empty');
        @unlink(WWW_ROOT . 'img' . DS . 'no-avatar.jpg');
    }

    /**
     * Test for fields that cannot be mass assigned
     * @test
     */
    public function testNoAccessibleProperties()
    {
        $this->assertHasNoAccessibleProperty(['id', 'post_count', 'modified']);
    }

    /**
     * Test for `_getFullName()` method
     * @test
     */
    public function testFullNameGetMutator()
    {
        $this->Entity->set(['first_name' => 'Alfa', 'last_name' => 'Beta']);
        $this->assertEquals('Alfa Beta', $this->Entity->get('full_name'));
    }

    /**
     * Test for `_getPicture()` method
     * @test
     */
    public function testPictureGetMutator()
    {
        $this->assertEquals('MeCms.no-avatar.jpg', $this->Entity->set('id', 1)->get('picture'));

        @create_file(WWW_ROOT . 'img' . DS . 'no-avatar.jpg', null);
        $this->assertEquals('no-avatar.jpg', $this->Entity->get('picture'));

        $id = 0;
        foreach (['jpg', 'jpeg', 'gif', 'png', 'JPEG'] as $extension) {
            @create_file(WWW_ROOT . 'img' . DS . 'users' . DS . ++$id . '.' . $extension);
            $this->assertEquals('users' . DS . $id . '.' . $extension, $this->Entity->set('id', $id)->get('picture'));
        }
    }
}
