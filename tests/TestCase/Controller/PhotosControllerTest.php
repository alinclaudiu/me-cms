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
namespace MeCms\Test\TestCase\Controller;

use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;

/**
 * PhotosControllerTest class
 */
class PhotosControllerTest extends IntegrationTestCase
{
    /**
     * @var \MeCms\Model\Table\PhotosTable
     */
    protected $Photos;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.photos',
        'plugin.me_cms.photos_albums',
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

        Cache::clear(false, $this->Photos->cache);
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->Photos);
    }

    /**
     * Adds additional event spies to the controller/view event manager
     * @param \Cake\Event\Event $event A dispatcher event
     * @param \Cake\Controller\Controller|null $controller Controller instance
     * @return void
     */
    public function controllerSpy($event, $controller = null)
    {
        $controller->viewBuilder()->setLayout(false);

        parent::controllerSpy($event, $controller);
    }

    /**
     * Tests for `view()` method
     * @test
     */
    public function testView()
    {
        $photo = $this->Photos->find('active')->contain('Albums')->first();
        $url = ['_name' => 'photo', $photo->album->slug, $photo->id];

        $this->get($url);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Photos/view.ctp');

        $photoFromView = $this->viewVariable('photo');
        $this->assertInstanceof('MeCms\Model\Entity\Photo', $photoFromView);

        $cache = Cache::read(sprintf('view_%s', md5($photo->id)), $this->Photos->cache);
        $this->assertEquals($photoFromView, $cache->first());

        //Backward compatibility for URLs like `/photo/11`
        $this->get('/photo/' . $photo->id);
        $this->assertRedirect($url);
    }

    /**
     * Tests for `preview()` method
     * @test
     */
    public function testPreview()
    {
        $id = $this->Photos->find('pending')->extract('id')->first();

        $this->get(['_name' => 'photosPreview', $id]);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Photos/view.ctp');

        $photoFromView = $this->viewVariable('photo');
        $this->assertInstanceof('MeCms\Model\Entity\Photo', $photoFromView);
    }
}
