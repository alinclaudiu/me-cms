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
namespace MeCms\Utility;

use Cake\Cache\Cache;
use Cake\Core\App;
use Cake\Filesystem\Folder;
use Cake\I18n\FrozenTime;
use Cake\I18n\I18n;
use Cake\ORM\Entity;
use Cake\Utility\Inflector;
use MeCms\Core\Plugin;

/**
 * An utility to handle static pages
 */
class StaticPage
{
    /**
     * Internal method to get the app path
     * @return string
     * @since 2.17.1
     */
    protected static function _appPath()
    {
        $path = collection(App::path('Template'))->first();

        return Folder::slashTerm($path) . 'StaticPages' . DS;
    }

    /**
     * Internal method to get a plugin path
     * @param string $plugin Plugin name
     * @return string
     * @since 2.17.1
     */
    protected static function _pluginPath($plugin)
    {
        $path = collection(App::path('Template', $plugin))->first();

        return Folder::slashTerm($path) . 'StaticPages' . DS;
    }

    /**
     * Internal method to get all paths for static pages
     * @return array
     * @uses MeCms\Core\Plugin::all()
     * @uses _appPath()
     * @uses _pluginPath()
     */
    protected static function paths()
    {
        $paths = Cache::read('paths', 'static_pages');

        if (empty($paths)) {
            //Adds all plugins to paths
            $paths = collection(Plugin::all())
                ->map(function ($plugin) {
                    return self::_pluginPath($plugin);
                })
                ->filter(function ($path) {
                    return file_exists($path);
                })
                ->toList();

            //Adds APP to paths
            array_unshift($paths, self::_appPath());

            Cache::write('paths', $paths, 'static_pages');
        }

        return $paths;
    }

    /**
     * Internal method to get the slug.
     *
     * It takes the full path and removes the relative path and the extension.
     * @param string $path Path
     * @param string $relativePath Relative path
     * @return string
     */
    protected static function slug($path, $relativePath)
    {
        return preg_replace([
            sprintf('/^%s/', preg_quote(Folder::slashTerm($relativePath), DS)),
            sprintf('/\.%s$/', pathinfo($path, PATHINFO_EXTENSION)),
        ], null, $path);
    }

    /**
     * Gets all static pages
     * @return array Static pages
     * @uses paths()
     * @uses slug()
     * @uses title()
     */
    public static function all()
    {
        foreach (self::paths() as $path) {
            //Gets all files for each path
            $files = (new Folder($path))->findRecursive('^.+\.ctp$', true);

            foreach ($files as $file) {
                $pages[] = new Entity([
                    'filename' => pathinfo($file, PATHINFO_FILENAME),
                    'path' => rtr($file),
                    'slug' => self::slug($file, $path),
                    'title' => self::title(pathinfo($file, PATHINFO_FILENAME)),
                    'modified' => new FrozenTime(filemtime($file)),
                ]);
            }
        }

        return $pages;
    }

    /**
     * Gets a static page
     * @param string $slug Slug
     * @return string|bool Static page or `false`
     * @uses MeCms\Core\Plugin::all()
     * @uses _appPath()
     * @uses _pluginPath()
     */
    public static function get($slug)
    {
        $locale = I18n::locale();

        //Sets the cache name
        $cache = sprintf('page_%s_locale_%s', md5($slug), $locale);

        $page = Cache::read($cache, 'static_pages');

        if (empty($page)) {
            //Sets the (partial) filename
            $filename = implode(DS, array_filter(explode('/', $slug)));

            //Sets the filename patterns
            $patterns = [$filename . '-' . $locale, $filename];

            //Checks if the page exists in APP
            foreach ($patterns as $pattern) {
                $filename = self::_appPath() . $pattern . '.ctp';

                if (is_readable($filename)) {
                    $page = DS . 'StaticPages' . DS . $pattern;

                    break;
                }
            }

            //Checks if the page exists in each plugin
            foreach (Plugin::all() as $plugin) {
                foreach ($patterns as $pattern) {
                    $filename = self::_pluginPath($plugin) . $pattern . '.ctp';

                    if (is_readable($filename)) {
                        $page = $plugin . '.' . DS . 'StaticPages' . DS . $pattern;

                        break;
                    }
                }
            }

            Cache::write($cache, $page, 'static_pages');
        }

        return $page;
    }

    /**
     * Gets the title for a static page from its slug or path
     * @param string $slugOrPath Slug or path
     * @return string
     */
    public static function title($slugOrPath)
    {
        //Gets only the filename (without extension)
        $slugOrPath = pathinfo($slugOrPath, PATHINFO_FILENAME);

        //Turns dashes into underscores (because `Inflector::humanize` will
        //  remove only underscores)
        $slugOrPath = str_replace('-', '_', $slugOrPath);

        return Inflector::humanize($slugOrPath);
    }
}
