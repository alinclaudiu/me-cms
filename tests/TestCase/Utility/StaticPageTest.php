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
namespace MeCms\Test\TestCase\Utility;

use Cake\Cache\Cache;
use Cake\TestSuite\TestCase;
use MeCms\Core\Plugin;
use MeCms\Utility\StaticPage;
use Reflection\ReflectionTrait;

/**
 * StaticPageTest class
 */
class StaticPageTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @var \MeCms\Utility\StaticPage
     */
    protected $StaticPage;

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        Cache::clearAll();

        Plugin::load('TestPlugin');

        $this->StaticPage = new StaticPage;
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        ini_set('intl.default_locale', 'en_US');

        Plugin::unload('TestPlugin');

        unset($this->StaticPage);
    }

    /**
     * Test for `_appPath()` method
     * @test
     */
    public function testAppPath()
    {
        $result = rtr($this->invokeMethod($this->StaticPage, '_appPath'));
        $this->assertEquals('tests/test_app/TestApp/Template/StaticPages/', $result);
    }

    /**
     * Test for `_pluginPath()` method
     * @test
     */
    public function testPluginPath()
    {
        $result = rtr($this->invokeMethod($this->StaticPage, '_pluginPath', ['TestPlugin']));
        $this->assertEquals('tests/test_app/TestApp/Plugin/TestPlugin/src/Template/StaticPages/', $result);
    }

    /**
     * Test for `all()` method
     * @test
     */
    public function testAll()
    {
        $pages = $this->StaticPage->all();

        foreach ($pages as $page) {
            $this->assertInstanceOf('Cake\ORM\Entity', $page);
            $this->assertInstanceOf('Cake\I18n\FrozenTime', $page->modified);
        }

        //Checks filenames
        $filenames = collection($pages)->extract('filename')->toList();

        $this->assertEquals([
            'page-from-app',
            'cookies-policy-it',
            'cookies-policy',
            'test-from-plugin',
            'page-on-first-from-plugin',
            'page_on_second_from_plugin',
        ], $filenames);

        //Checks paths
        $paths = collection($pages)->extract('path')->toList();

        $this->assertEquals([
            'tests/test_app/TestApp/Template/StaticPages/page-from-app.ctp',
            'src/Template/StaticPages/cookies-policy-it.ctp',
            'src/Template/StaticPages/cookies-policy.ctp',
            'tests/test_app/TestApp/Plugin/TestPlugin/src/Template/StaticPages/test-from-plugin.ctp',
            'tests/test_app/TestApp/Plugin/TestPlugin/src/Template/StaticPages/first-folder/page-on-first-from-plugin.ctp',
            'tests/test_app/TestApp/Plugin/TestPlugin/src/Template/StaticPages/first-folder/second_folder/page_on_second_from_plugin.ctp',
        ], $paths);

        //Checks slugs
        $slugs = collection($pages)->extract('slug')->toList();

        $this->assertEquals([
            'page-from-app',
            'cookies-policy-it',
            'cookies-policy',
            'test-from-plugin',
            'first-folder/page-on-first-from-plugin',
            'first-folder/second_folder/page_on_second_from_plugin',
        ], $slugs);

        //Checks titles
        $titles = collection($pages)->extract('title')->toList();

        $this->assertEquals([
            'Page From App',
            'Cookies Policy It',
            'Cookies Policy',
            'Test From Plugin',
            'Page On First From Plugin',
            'Page On Second From Plugin',
        ], $titles);
    }

    /**
     * Test for `get()` method
     * @test
     */
    public function testGet()
    {
        //Gets all slugs from pages
        $slugs = collection($this->StaticPage->all())->extract('slug')->toList();

        //Now, on the contrary, gets all pages from slugs
        $pages = collection($slugs)->map(function ($slug) {
            return $this->StaticPage->get($slug);
        })->toList();

        $this->assertEquals([
            '/StaticPages/page-from-app',
            ME_CMS . './StaticPages/cookies-policy-it',
            ME_CMS . './StaticPages/cookies-policy',
            'TestPlugin./StaticPages/test-from-plugin',
            'TestPlugin./StaticPages/first-folder/page-on-first-from-plugin',
            'TestPlugin./StaticPages/first-folder/second_folder/page_on_second_from_plugin',
        ], $pages);

        //Tries to get a no existing page
        $this->assertFalse($this->StaticPage->get('no-Existing'));
    }

    /**
     * Test for `get()` method, using a different locale
     * @test
     */
    public function testGetDifferentLocale()
    {
        $this->assertEquals(ME_CMS . './StaticPages/cookies-policy', $this->StaticPage->get('cookies-policy'));

        ini_set('intl.default_locale', 'it');

        $this->assertEquals(ME_CMS . './StaticPages/cookies-policy-it', $this->StaticPage->get('cookies-policy'));
    }

    /**
     * Test for `paths()` method
     * @test
     */
    public function testPaths()
    {
        $paths = $this->invokeMethod($this->StaticPage, 'paths');

        $this->assertEquals(Cache::read('paths', 'static_pages'), $paths);

        //Gets relative paths
        $paths = collection($paths)->extract(function ($path) {
            return rtr($path);
        })->toList();

        $this->assertEquals([
            'tests/test_app/TestApp/Template/StaticPages/',
            'src/Template/StaticPages/',
            'tests/test_app/TestApp/Plugin/TestPlugin/src/Template/StaticPages/',
        ], $paths);
    }

    /**
     * Test for `slug()` method
     * @test
     */
    public function testSlug()
    {
        $files = [
            'my-file',
            'my-file.ctp',
            '/first/second/my-file.ctp',
            '/first/second/my-file.php',
        ];

        foreach ($files as $file) {
            $this->assertEquals('my-file', $this->invokeMethod($this->StaticPage, 'slug', [$file, '/first/second']));
            $this->assertEquals('my-file', $this->invokeMethod($this->StaticPage, 'slug', [$file, '/first/second/']));
        }

        $result = $this->invokeMethod($this->StaticPage, 'slug', ['first/my-file.ctp', '/first/second']);
        $this->assertEquals('first/my-file', $result);

        $result = $this->invokeMethod($this->StaticPage, 'slug', ['/first/second/third/my-file.ctp', '/first/second']);
        $this->assertEquals('third/my-file', $result);
    }

    /**
     * Test for `title()` method
     * @test
     */
    public function testTitle()
    {
        $expected = [
            'Page From App',
            'Cookies Policy It',
            'Cookies Policy',
            'Test From Plugin',
            'Page On First From Plugin',
            'Page On Second From Plugin',
        ];

        //Gets all slugs from pages
        $slugs = collection($this->StaticPage->all())->extract('slug')->toList();

        //Now gets all title from slugs
        $titles = collection($slugs)->map(function ($slug) {
            return $this->StaticPage->title($slug);
        })->toList();

        $this->assertEquals($expected, $titles);

        //Gets all paths from pages
        $paths = collection($this->StaticPage->all())->extract('path')->toList();

        //Now gets all title from paths
        $titles = collection($paths)->map(function ($path) {
            return $this->StaticPage->title($path);
        })->toList();

        $this->assertEquals($expected, $titles);
    }
}
