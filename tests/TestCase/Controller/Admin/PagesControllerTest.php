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
namespace MeCms\Test\TestCase\Controller\Admin;

use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;
use MeCms\Controller\Admin\PagesController;
use MeCms\TestSuite\Traits\AuthMethodsTrait;

/**
 * PagesControllerTest class
 */
class PagesControllerTest extends IntegrationTestCase
{
    use AuthMethodsTrait;

    /**
     * @var \MeCms\Controller\Admin\PagesController
     */
    protected $Controller;

    /**
     * @var \MeCms\Model\Table\PagesTable
     */
    protected $Pages;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.pages',
        'plugin.me_cms.pages_categories',
    ];

    /**
     * @var array
     */
    protected $url;

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->setUserGroup('admin');

        $this->Controller = new PagesController;

        $this->Pages = TableRegistry::get('MeCms.Pages');

        Cache::clear(false, $this->Pages->cache);

        $this->url = ['controller' => 'Pages', 'prefix' => ADMIN_PREFIX, 'plugin' => ME_CMS];
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->Controller, $this->Pages);
    }

    /**
     * Tests for `beforeFilter()` method
     * @test
     */
    public function testBeforeFilter()
    {
        foreach (['add', 'edit'] as $action) {
            $this->get(array_merge($this->url, compact('action'), [1]));
            $this->assertResponseOk();
            $this->assertNotEmpty($this->viewVariable('categories'));
        }

        $this->get(array_merge($this->url, ['action' => 'index']));
        $this->assertResponseOk();
        $this->assertNotEmpty($this->viewVariable('categories'));

        //`indexStatics` still works
        $this->get(array_merge($this->url, ['action' => 'indexStatics']));
        $this->assertResponseOk();
        $this->assertEmpty($this->viewVariable('categories'));
    }

    /**
     * Tests for `beforeFilter()` method, with no categories
     * @test
     */
    public function testBeforeFilterNoCategories()
    {
        //Deletes all categories
        $this->Pages->Categories->deleteAll(['id IS NOT' => null]);

        foreach (['index', 'add', 'edit'] as $action) {
            $this->get(array_merge($this->url, compact('action'), [1]));
            $this->assertRedirect(['controller' => 'PagesCategories', 'action' => 'index']);
            $this->assertSession('You must first create a category', 'Flash.flash.0.message');
        }

        //`indexStatics` still works
        $this->get(array_merge($this->url, ['action' => 'indexStatics']));
        $this->assertResponseOk();
        $this->assertEmpty($this->viewVariable('categories'));
    }

    /**
     * Tests for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        foreach (['add', 'edit'] as $action) {
            $this->Controller = new PagesController;
            $this->Controller->request = $this->Controller->request->withParam('action', $action);
            $this->Controller->initialize();

            $this->assertContains('KcFinder', $this->Controller->components()->loaded());
        }
    }

    /**
     * Tests for `isAuthorized()` method
     * @test
     */
    public function testIsAuthorized()
    {
        $this->assertGroupsAreAuthorized([
            'admin' => true,
            'manager' => true,
            'user' => false,
        ]);

        //`index` and `indexStatics` actions
        foreach (['index', 'indexStatics'] as $action) {
            $this->Controller = new PagesController;
            $this->Controller->request = $this->Controller->request->withParam('action', $action);

            $this->assertGroupsAreAuthorized([
                'admin' => true,
                'manager' => true,
                'user' => true,
            ]);
        }

        //`delete` action
        $this->Controller = new PagesController;
        $this->Controller->request = $this->Controller->request->withParam('action', 'delete');

        $this->assertGroupsAreAuthorized([
            'admin' => true,
            'manager' => false,
            'user' => false,
        ]);
    }

    /**
     * Tests for `index()` method
     * @test
     */
    public function testIndex()
    {
        $this->get(array_merge($this->url, ['action' => 'index']));
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Pages/index.ctp');

        $pagesFromView = $this->viewVariable('pages');
        $this->assertInstanceof('Cake\ORM\ResultSet', $pagesFromView);
        $this->assertNotEmpty($pagesFromView);

        foreach ($pagesFromView as $page) {
            $this->assertInstanceof('MeCms\Model\Entity\Page', $page);
        }
    }

    /**
     * Tests for `indexStatics()` method
     * @test
     */
    public function testIndexStatics()
    {
        $this->get(array_merge($this->url, ['action' => 'indexStatics']));
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Pages/index_statics.ctp');

        $pagesFromView = $this->viewVariable('pages');
        $this->assertTrue(is_array($pagesFromView));
        $this->assertNotEmpty($pagesFromView);

        foreach ($pagesFromView as $page) {
            $this->assertInstanceof('Cake\ORM\Entity', $page);
        }
    }

    /**
     * Tests for `add()` method
     * @test
     */
    public function testAdd()
    {
        $url = array_merge($this->url, ['action' => 'add']);

        $this->get($url);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Pages/add.ctp');

        $pageFromView = $this->viewVariable('page');
        $this->assertInstanceof('MeCms\Model\Entity\Page', $pageFromView);
        $this->assertNotEmpty($pageFromView);

        //POST request. Data are valid
        $this->post($url, [
            'category_id' => 1,
            'title' => 'new page title',
            'slug' => 'new-page-slug',
            'text' => 'new page text',
        ]);
        $this->assertRedirect(['action' => 'index']);
        $this->assertSession('The operation has been performed correctly', 'Flash.flash.0.message');

        //POST request. Data are invalid
        $this->post($url, ['title' => 'aa']);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertResponseContains('The operation has not been performed correctly');

        $pageFromView = $this->viewVariable('page');
        $this->assertInstanceof('MeCms\Model\Entity\Page', $pageFromView);
        $this->assertNotEmpty($pageFromView);
    }

    /**
     * Tests for `edit()` method
     * @test
     */
    public function testEdit()
    {
        $url = array_merge($this->url, ['action' => 'edit', 1]);

        $this->get($url);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Pages/edit.ctp');

        $pageFromView = $this->viewVariable('page');
        $this->assertInstanceof('MeCms\Model\Entity\Page', $pageFromView);
        $this->assertNotEmpty($pageFromView);

        //Checks if the `created` field has been properly formatted
        $this->assertRegExp('/^\d{4}\-\d{2}\-\d{2}\s\d{2}\:\d{2}$/', $pageFromView->created);

        //POST request. Data are valid
        $this->post($url, ['title' => 'another title']);
        $this->assertRedirect(['action' => 'index']);
        $this->assertSession('The operation has been performed correctly', 'Flash.flash.0.message');

        //POST request. Data are invalid
        $this->post($url, ['title' => 'aa']);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertResponseContains('The operation has not been performed correctly');

        $pageFromView = $this->viewVariable('page');
        $this->assertInstanceof('MeCms\Model\Entity\Page', $pageFromView);
        $this->assertNotEmpty($pageFromView);
    }

    /**
     * Tests for `delete()` method
     * @test
     */
    public function testDelete()
    {
        $this->post(array_merge($this->url, ['action' => 'delete', 1]));
        $this->assertRedirect(['action' => 'index']);
        $this->assertSession('The operation has been performed correctly', 'Flash.flash.0.message');
    }
}
