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
 * @see         http://api.cakephp.org/3.4/class-Cake.Core.Plugin.html
 */
namespace MeCms\Core;

use MeTools\Core\Plugin as CakePlugin;

/**
 * An utility to handle plugins
 */
class Plugin extends CakePlugin
{
    /**
     * Gets all loaded plugins.
     *
     * Available options are:
     *  - `core`, if `false` exclude the core plugins;
     *  - `exclude`, a plugin as string or an array of plugins to be excluded;
     *  - `order`, if `true` the plugins will be sorted.
     * @param array $options Options
     * @return array Plugins
     * @uses MeTools\Core\Plugin::all()
     */
    public static function all(array $options = [])
    {
        $options = array_merge(['order' => true], $options);

        $plugins = parent::all($options);

        if ($options['order']) {
            $key = array_search(ME_CMS, $plugins);

            if ($key) {
                unset($plugins[$key]);
                array_unshift($plugins, ME_CMS);
            }
        }

        return $plugins;
    }
}
