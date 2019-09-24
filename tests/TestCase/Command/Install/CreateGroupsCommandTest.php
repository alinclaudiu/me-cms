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
namespace MeCms\Test\TestCase\Command\Install;

use Cake\Datasource\ConnectionManager;
use MeCms\TestSuite\TestCase;
use MeTools\TestSuite\ConsoleIntegrationTestTrait;

/**
 * CreateGroupsCommandTest class
 */
class CreateGroupsCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    /**
     * @var bool
     */
    public $autoInitializeClass = true;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.MeCms.Users',
        'plugin.MeCms.UsersGroups',
    ];

    /**
     * Test for `execute()` method
     * @test
     */
    public function testExecute()
    {
        //A group already exists
        $this->exec('me_cms.create_groups -v');
        $this->assertExitWithSuccess();
        $this->assertOutputEmpty();
        $this->assertErrorContains('Some user groups already exist');

        //With no user groups
        $UsersGroups = $this->getTable('MeCms.UsersGroups');
        $UsersGroups->deleteAll(['id is NOT' => null]);
        $this->exec('me_cms.create_groups -v');
        $this->assertExitWithSuccess();
        $this->assertOutputContains('The user groups have been created');
        $this->assertErrorEmpty();

        //Checks the user groups exist
        $this->assertEquals([1, 2, 3], $UsersGroups->find()->extract('id')->toList());

        $this->skipIf(IS_WIN);

        //Tests for Postgres and Sqlite
        $backupTestConnection = ConnectionManager::getConfig('test');
        foreach (['postgres', 'sqlite'] as $testDatabase) {
            ConnectionManager::drop('test');
            ConnectionManager::setConfig('test', ConnectionManager::get('test_' . $testDatabase));
            $this->loadFixtures();

            $UsersGroups->deleteAll(['id is NOT' => null]);
            $this->exec('me_cms.create_groups -v');
            $this->assertExitWithSuccess();
        }
        ConnectionManager::drop('test');
        ConnectionManager::setConfig('test', $backupTestConnection);
    }
}
