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
 * @see         \MeCms\View\Helper\MenuBuilderHelper
 */

namespace MeCms\View\Helper;

use Cake\View\Helper;

/**
 * Menu Helper.
 *
 * This helper contains methods that will be called automatically to generate
 *  menus for the admin layout.
 * You don't need to call these methods manually, use instead the
 *  `MenuBuilderHelper` helper.
 *
 * Each method must return an array with four values:
 *  - the menu links, as an array of parameters;
 *  - the menu title;
 *  - the options for the menu title;
 *  - the controllers handled by this menu, as an array.
 *
 * See the `\MeCms\View\Helper\MenuBuilderHelper::generate()` method for more
 *  information.
 */
class MenuHelper extends Helper
{
    /**
     * Helpers
     * @var array
     */
    public $helpers = ['MeCms.Auth'];

    /**
     * Internal function to generate the menu for "posts" actions
     * @return array Array with links, title, title options and handled controllers
     */
    public function posts(): array
    {
        $params = ['controller' => 'Posts', 'plugin' => 'MeCms', 'prefix' => ADMIN_PREFIX];
        $links[] = [__d('me_cms', 'List posts'), ['action' => 'index'] + $params];
        $links[] = [__d('me_cms', 'Add post'), ['action' => 'add'] + $params];

        if ($this->Auth->isGroup(['admin', 'manager'])) {
            $params['controller'] = 'PostsCategories';
            $links[] = [__d('me_cms', 'List categories'), ['action' => 'index'] + $params];
            $links[] = [__d('me_cms', 'Add category'), ['action' => 'add'] + $params];
        }

        $links[] = [__d('me_cms', 'List tags'), [
            'controller' => 'PostsTags',
            'action' => 'index',
        ] + $params];

        return [$links, I18N_POSTS, ['icon' => 'far file-alt'], ['Posts', 'PostsCategories', 'PostsTags']];
    }

    /**
     * Internal function to generate the menu for "pages" actions
     * @return array Array with links, title, title options and handled controllers
     */
    public function pages(): array
    {
        $params = ['controller' => 'Pages', 'plugin' => 'MeCms', 'prefix' => ADMIN_PREFIX];
        $links[] = [__d('me_cms', 'List pages'), ['action' => 'index'] + $params];

        if ($this->Auth->isGroup(['admin', 'manager'])) {
            $links[] = [__d('me_cms', 'Add page'), ['action' => 'add'] + $params];
        }

        $links[] = [__d('me_cms', 'List static pages'), ['action' => 'indexStatics'] + $params];

        if ($this->Auth->isGroup(['admin', 'manager'])) {
            $params['controller'] = 'PagesCategories';
            $links[] = [__d('me_cms', 'List categories'), ['action' => 'index'] + $params];
            $links[] = [__d('me_cms', 'Add category'), ['action' => 'add'] + $params];
        }

        return [$links, I18N_PAGES, ['icon' => 'far copy'], ['Pages', 'PagesCategories']];
    }

    /**
     * Internal function to generate the menu for "photos" actions
     * @return array Array with links, title, title options and handled controllers
     */
    public function photos(): array
    {
        $params = ['controller' => 'Photos', 'plugin' => 'MeCms', 'prefix' => ADMIN_PREFIX];
        $links[] = [__d('me_cms', 'List photos'), ['action' => 'index'] + $params];
        $links[] = [__d('me_cms', 'Upload photos'), ['action' => 'upload'] + $params];

        $params['controller'] = 'PhotosAlbums';
        $links[] = [__d('me_cms', 'List albums'), ['action' => 'index'] + $params];
        $links[] = [__d('me_cms', 'Add album'), ['action' => 'add'] + $params];

        return [$links, I18N_PHOTOS, ['icon' => 'camera-retro'], ['Photos', 'PhotosAlbums']];
    }

    /**
     * Internal function to generate the menu for "banners" actions
     * @return array Array with links, title, title options and handled controllers
     */
    public function banners(): array
    {
        //Only admins and managers can access these controllers
        if (!$this->Auth->isGroup(['admin', 'manager'])) {
            return [];
        }

        $params = ['controller' => 'Banners', 'plugin' => 'MeCms', 'prefix' => ADMIN_PREFIX];
        $links[] = [__d('me_cms', 'List banners'), ['action' => 'index'] + $params];
        $links[] = [__d('me_cms', 'Upload banners'), ['action' => 'upload'] + $params];

        if ($this->Auth->isGroup('admin')) {
            $params['controller'] = 'BannersPositions';
            $links[] = [__d('me_cms', 'List positions'), ['action' => 'index'] + $params];
            $links[] = [__d('me_cms', 'Add position'), ['action' => 'add'] + $params];
        }

        return [$links, __d('me_cms', 'Banners'), ['icon' => 'shopping-cart'], ['Banners', 'BannersPositions']];
    }

    /**
     * Internal function to generate the menu for "users" actions
     * @return array Array with links, title, title options and handled controllers
     */
    public function users(): array
    {
        //Only admins and managers can access this controller
        if (!$this->Auth->isGroup(['admin', 'manager'])) {
            return [];
        }

        $params = ['controller' => 'Users', 'plugin' => 'MeCms', 'prefix' => ADMIN_PREFIX];
        $links[] = [__d('me_cms', 'List users'), ['action' => 'index'] + $params];
        $links[] = [__d('me_cms', 'Add user'), ['action' => 'add'] + $params];

        if ($this->Auth->isGroup('admin')) {
            $params['controller'] = 'UsersGroups';
            $links[] = [__d('me_cms', 'List groups'), ['action' => 'index'] + $params];
            $links[] = [__d('me_cms', 'Add group'), ['action' => 'add'] + $params];
        }

        return [$links, I18N_USERS, ['icon' => 'users'], ['Users', 'UsersGroups']];
    }

    /**
     * Internal function to generate the menu for "backups" actions
     * @return array Array with links, title, title options and handled controllers
     */
    public function backups(): array
    {
        //Only admins can access this controller
        if (!$this->Auth->isGroup('admin')) {
            return [];
        }

        $params = ['controller' => 'Backups', 'plugin' => 'MeCms', 'prefix' => ADMIN_PREFIX];
        $links[] = [__d('me_cms', 'List backups'), ['action' => 'index'] + $params];
        $links[] = [__d('me_cms', 'Add backup'), ['action' => 'add'] + $params];

        return [$links, __d('me_cms', 'Backups'), ['icon' => 'database'], ['Backups']];
    }

    /**
     * Internal function to generate the menu for "systems" actions
     * @return array Array with links, title, title options and handled controllers
     */
    public function systems(): array
    {
        //Only admins and managers can access this controller
        if (!$this->Auth->isGroup(['admin', 'manager'])) {
            return [];
        }

        $params = ['controller' => 'Systems', 'plugin' => 'MeCms', 'prefix' => ADMIN_PREFIX];
        $links[] = [__d('me_cms', 'Temporary files'), ['action' => 'tmpViewer'] + $params];
        $links[] = [__d('me_cms', 'System checkup'), ['action' => 'checkup'] + $params];
        $links[] = [__d('me_cms', 'Media browser'), ['action' => 'browser'] + $params];
        $links[] = [__d('me_cms', 'Changelogs'), ['action' => 'changelogs'] + $params];

        if ($this->Auth->isGroup('admin')) {
            $links[] = [__d('me_cms', 'Log management'), [
                'controller' => 'Logs',
                'action' => 'index',
            ] + $params];
        }

        return [$links, __d('me_cms', 'System'), ['icon' => 'wrench'], ['Logs', 'Systems']];
    }
}
