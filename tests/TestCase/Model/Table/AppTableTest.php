<?php
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
namespace MeCms\Test\TestCase\Model\Table;

use ArrayObject;
use Cake\Cache\Cache;
use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * AppTableTest class
 */
class AppTableTest extends TestCase
{
    /**
     * @var \MeCms\Model\Table\PhotosTable
     */
    protected $Photos;

    /**
     * @var \MeCms\Model\Table\PostsTable
     */
    protected $Posts;

    /**
     * @var \MeCms\Model\Table\PostsCategoriesTable
     */
    protected $PostsCategories;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.photos',
        'plugin.me_cms.posts',
        'plugin.me_cms.posts_categories',
        'plugin.me_cms.posts_tags',
        'plugin.me_cms.users',
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

        $this->Photos = TableRegistry::get(ME_CMS . '.Photos');
        $this->Posts = TableRegistry::get(ME_CMS . '.Posts');
        $this->PostsCategories = TableRegistry::get(ME_CMS . '.PostsCategories');

        Cache::clear(false, $this->Photos->cache);
        Cache::clear(false, $this->Posts->cache);
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->Photos, $this->Posts, $this->PostsCategories);
    }

    /**
     * Test for `afterDelete()` method
     * @test
     */
    public function testAfterDelete()
    {
        //Writes something on cache
        Cache::write('testKey', 'testValue', $this->Posts->cache);
        $this->assertEquals('testValue', Cache::read('testKey', $this->Posts->cache));

        $this->Posts->afterDelete(new Event(null), new Entity, new ArrayObject);

        //The cache is cleared
        $this->assertFalse(Cache::read('testKey', $this->Posts->cache));
    }

    /**
     * Test for `afterSave()` method
     * @test
     */
    public function testAfterSave()
    {
        //Writes something on cache
        Cache::write('testKey', 'testValue', $this->Posts->cache);
        $this->assertEquals('testValue', Cache::read('testKey', $this->Posts->cache));

        $this->Posts->afterSave(new Event(null), new Entity, new ArrayObject);

        //The cache is cleared
        $this->assertFalse(Cache::read('testKey', $this->Posts->cache));
    }

    /**
     * Test for `beforeSave()` method
     * @test
     */
    public function testBeforeSave()
    {
        $example = [
            'user_id' => 1,
            'category_id' => 1,
            'title' => 'Example',
            'slug' => 'example',
            'text' => 'Example text',
        ];

        $entity = $this->Posts->save($this->Posts->newEntity($example));
        $this->assertNotEmpty($entity->created);
        $this->Posts->delete($entity);

        foreach ([null, ''] as $value) {
            $example['created'] = $value;
            $entity = $this->Posts->save($this->Posts->newEntity($example));
            $this->assertNotEmpty($entity->created);
            $this->Posts->delete($entity);
        }

        $example['created'] = $now = new Time;
        $entity = $this->Posts->save($this->Posts->newEntity($example));
        $this->assertEquals($now, $entity->created);
        $this->Posts->delete($entity);

        foreach ([
            '2017-03-14 20:19',
            '2017-03-14 20:19:00',
        ] as $value) {
            $example['created'] = $value;
            $entity = $this->Posts->save($this->Posts->newEntity($example));
            $this->assertEquals('2017-03-14 20:19:00', $entity->created->i18nFormat('yyyy-MM-dd HH:mm:ss'));
            $this->Posts->delete($entity);
        }

        //Now tries with a record that already exists
        $entity = $this->Posts->get(1);

        foreach ([null, ''] as $value) {
            $entity->created = $value;
            $entity = $this->Posts->save($entity);
            $this->assertNotEmpty($entity->created);
        }
    }

    /**
     * Test for `findActive()` method
     * @test
     */
    public function testFindActive()
    {
        $query = $this->Posts->find('active');
        $this->assertInstanceOf('Cake\ORM\Query', $query);
        $this->assertStringEndsWith('FROM posts Posts WHERE (Posts.active = :c0 AND Posts.created <= :c1)', $query->sql());

        $this->assertTrue($query->valueBinder()->bindings()[':c0']['value']);
        $this->assertInstanceOf('Cake\I18n\Time', $query->valueBinder()->bindings()[':c1']['value']);

        $this->assertNotEmpty($query->count());

        foreach ($query->toArray() as $entity) {
            $this->assertTrue($entity->active);
            $this->assertTrue(!$entity->created->isFuture());
        }
    }

    /**
     * Test for `findPending()` method
     * @test
     */
    public function testFindPending()
    {
        $query = $this->Posts->find('pending');
        $this->assertInstanceOf('Cake\ORM\Query', $query);
        $this->assertStringEndsWith('FROM posts Posts WHERE (Posts.active = :c0 OR Posts.created > :c1)', $query->sql());

        $this->assertFalse($query->valueBinder()->bindings()[':c0']['value']);
        $this->assertInstanceOf('Cake\I18n\Time', $query->valueBinder()->bindings()[':c1']['value']);

        $pendingId = collection($query->toArray())->extract('id')->toList();
        $this->assertEquals([6, 8], $pendingId);
    }

    /**
     * Test for `findRandom()` method
     * @test
     */
    public function testFindRandom()
    {
        $query = $this->Posts->find('random');
        $this->assertInstanceOf('Cake\ORM\Query', $query);
        $this->assertStringEndsWith('FROM posts Posts ORDER BY rand() LIMIT 1', $query->sql());

        $query = $this->Posts->find('random')->limit(2);
        $this->assertStringEndsWith('FROM posts Posts ORDER BY rand() LIMIT 2', $query->sql());
    }

    /**
     * Test for `getList()` method
     * @test
     */
    public function testGetList()
    {
        $query = $this->Photos->getList();
        $this->assertInstanceof('Cake\ORM\Query', $query);
        $this->assertContains('ORDER BY ' . $this->Photos->getDisplayField() . ' ASC', $query->sql());

        $list = $query->toArray();
        $this->assertEquals([
            1 => 'photo1.jpg',
            3 => 'photo3.jpg',
            4 => 'photo4.jpg',
            2 => 'photoa.jpg',
        ], $list);

        $fromCache = Cache::read('photos_list', $this->Photos->cache)->toArray();
        $this->assertEquals($list, $fromCache);
    }

    /**
     * Test for `getTreeList()` method
     * @test
     */
    public function testGetTreeList()
    {
        $query = $this->PostsCategories->getTreeList();
        $this->assertInstanceof('Cake\ORM\Query', $query);

        $list = $query->toArray();
        $this->assertEquals([
            1 => 'First post category',
            3 => '—Sub post category',
            4 => '——Sub sub post category',
            2 => 'Another post category',
        ], $list);

        $fromCache = Cache::read('posts_categories_tree_list', $this->PostsCategories->cache)->toArray();
        $this->assertEquals($list, $fromCache);
    }

    /**
     * Test for `getTreeList()` method, with a model that does not have a tree
     * @expectedException BadMethodCallException
     * @expectedExceptionMessage  Unknown finder method "treeList"
     */
    public function testGetTreeListModelDoesNotHaveTree()
    {
        $this->Posts->getTreeList();
    }

    /**
     * Test for `queryFromFilter()` method
     * @test
     */
    public function testQueryFromFilter()
    {
        $expected = 'FROM posts Posts WHERE (Posts.id = :c0 AND Posts.title like :c1 AND Posts.user_id = :c2 AND Posts.category_id = :c3 AND Posts.active = :c4 AND Posts.priority = :c5 AND Posts.created >= :c6 AND Posts.created < :c7)';

        $data = [
            'id' => 2,
            'title' => 'Title',
            'user' => 3,
            'category' => 4,
            'active' => 'yes',
            'priority' => 3,
            'created' => '2016-12',
        ];

        $query = $this->Posts->queryFromFilter($this->Posts->find(), $data);
        $this->assertInstanceOf('Cake\ORM\Query', $query);
        $this->assertStringEndsWith($expected, $query->sql());

        $params = collection($query->valueBinder()->bindings())->extract('value')->map(function ($value) {
            if ($value instanceof Time) {
                return $value->i18nFormat('yyyy-MM-dd HH:mm:ss');
            }

            return $value;
        })->toList();

        $this->assertEquals([
            2,
            '%Title%',
            3,
            4,
            true,
            3,
            '2016-12-01 00:00:00',
            '2017-01-01 00:00:00',
        ], $params);

        $data['active'] = 'no';

        $query = $this->Posts->queryFromFilter($this->Posts->find(), $data);
        $this->assertStringEndsWith($expected, $query->sql());
        $this->assertEquals(false, $query->valueBinder()->bindings()[':c4']['value']);

        $data = ['filename' => 'image.jpg'];

        $query = $this->Photos->queryFromFilter($this->Photos->find(), $data);
        $this->assertStringEndsWith('FROM photos Photos WHERE Photos.filename like :c0', $query->sql());
        $this->assertEquals('%image.jpg%', $query->valueBinder()->bindings()[':c0']['value']);
    }

    /**
     * Test for `queryFromFilter()` method, with invalid data
     * @test
     */
    public function testQueryFromFilterWithInvalidData()
    {
        $data = [
            'title' => 'ab',
            'priority' => 6,
            'created' => '2016-12-30',
        ];

        $query = $this->Posts->queryFromFilter($this->Posts->find(), $data);
        $this->assertEmpty($query->valueBinder()->bindings());

        $data = ['filename' => 'ab'];

        $query = $this->Photos->queryFromFilter($this->Photos->find(), $data);
        $this->assertEmpty($query->valueBinder()->bindings());
    }
}
