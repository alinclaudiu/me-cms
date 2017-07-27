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
namespace MeCms\Test\TestCase\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use MeCms\Controller\Component\KcFinderComponent;
use MeTools\TestSuite\TestCase;

/**
 * KcFinderComponentTest class
 */
class KcFinderComponentTest extends TestCase
{
    /**
     * @var \MeCms\Controller\Component\KcFinderComponent
     */
    protected $KcFinder;

    /**
     * Internal method to get a KcFinder instance
     * @return \MeCms\Controller\Component\KcFinderComponent
     */
    protected function getKcFinderInstance()
    {
        return new KcFinderComponent(new ComponentRegistry(new Controller));
    }

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $file = WWW_ROOT . 'vendor' . DS . 'kcfinder' . DS . 'index.php';

        //@codingStandardsIgnoreStart
        @mkdir(dirname($file), 0777, true);
        @mkdir(UPLOADED);
        //@codingStandardsIgnoreEnd

        file_put_contents($file, null);

        $this->KcFinder = $this->getKcFinderInstance();
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        $file = WWW_ROOT . 'vendor' . DS . 'kcfinder' . DS . 'index.php';

        //@codingStandardsIgnoreStart
        @unlink($file);
        @rmdir(dirname($file));
        //@codingStandardsIgnoreEnd
    }

    /**
     * Test for `_getDefaultConfig()` method
     * @test
     */
    public function testGetDefaultConfig()
    {
        $defaultConfig = $this->invokeMethod($this->KcFinder, '_getDefaultConfig');
        $defaultConfig['uploadDir'] = rtr($defaultConfig['uploadDir']);
        $this->assertEquals([
            'denyExtensionRename' => true,
            'denyUpdateCheck' => true,
            'dirnameChangeChars' => [
                ' ' => '_',
                ':' => '_',
            ],
            'disabled' => false,
            'filenameChangeChars' => [
                ' ' => '_',
                ':' => '_',
            ],
            'jpegQuality' => 100,
            'uploadDir' => 'tests/test_app/TestApp/webroot/files/',
            'uploadURL' => 'http://localhost/files',
            'types' => [
                'images' => '*img',
            ],
            'access' => [
                'dirs' => [
                    'create' => true,
                    'delete' => false,
                    'rename' => false,
                ],
                'files' => [
                    'upload' => true,
                    'delete' => false,
                    'copy' => true,
                    'move' => false,
                    'rename' => false,
                ],
            ],
        ], $defaultConfig);

        //Tries with admin user
        $this->KcFinder->Auth->setUser(['group' => ['name' => 'admin']]);

        $defaultConfig = $this->invokeMethod($this->KcFinder, '_getDefaultConfig');
        $defaultConfig['uploadDir'] = rtr($defaultConfig['uploadDir']);
        $this->assertEquals([
            'denyExtensionRename' => true,
            'denyUpdateCheck' => true,
            'dirnameChangeChars' => [
                ' ' => '_',
                ':' => '_',
            ],
            'disabled' => false,
            'filenameChangeChars' => [
                ' ' => '_',
                ':' => '_',
            ],
            'jpegQuality' => (int)100,
            'uploadDir' => 'tests/test_app/TestApp/webroot/files/',
            'uploadURL' => 'http://localhost/files',
            'types' => [
                'images' => '*img',
            ],
        ], $defaultConfig);
    }

    /**
     * Test for `getTypes()` method
     * @test
     */
    public function testGetTypes()
    {
        $this->assertEquals(['images' => '*img'], $this->KcFinder->getTypes());

        //@codingStandardsIgnoreLine
        @mkdir(UPLOADED . 'docs');

        $this->assertEquals(['docs' => '', 'images' => '*img'], $this->KcFinder->getTypes());

        //@codingStandardsIgnoreLine
        @rmdir(UPLOADED . 'docs');
    }

    /**
     * Test for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        $this->assertArrayKeysEqual([
            'denyExtensionRename',
            'denyUpdateCheck',
            'dirnameChangeChars',
            'disabled',
            'filenameChangeChars',
            'jpegQuality',
            'uploadDir',
            'uploadURL',
            'types',
            'access',
        ], $this->KcFinder->request->session()->read('KCFINDER'));
    }

    /**
     * Test for `initialize()` method, with `uploaded` dir not writable
     * @expectedException \Cake\Network\Exception\InternalErrorException
     * @expectedExceptionMessage File or directory tests/test_app/TestApp/webroot/files/ not writeable
     * @test
     */
    public function testInitializeDirNotWritable()
    {
        //@codingStandardsIgnoreLine
        @rmdir(UPLOADED);

        $this->getKcFinderInstance();
    }

    /**
     * Test for `initialize()` method, with KCFinder not available
     * @expectedException \Cake\Network\Exception\InternalErrorException
     * @expectedExceptionMessage KCFinder is not available
     * @test
     */
    public function testInitializeKCFinderNotAvailable()
    {
        //@codingStandardsIgnoreLine
        @unlink(WWW_ROOT . 'vendor' . DS . 'kcfinder' .DS . 'index.php');

        $this->getKcFinderInstance();
    }
}
