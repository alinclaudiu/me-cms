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
 * @since       2.17.0
 */
namespace MeCms\Model\Table\Traits;

use Cake\Filesystem\Folder;
use Cake\ORM\Entity;
use DOMDocument;
use MeTools\Utility\Youtube;
use Thumber\ThumbTrait;
use Thumber\Utility\ThumbCreator;

/**
 * This trait provides a method to get the first available image or the preview
 *  of the first YouTube video
 */
trait GetPreviewsFromTextTrait
{
    use ThumbTrait;

    /**
     * Internal method to extract all images from an html string, including the
     *  previews of Youtube videos
     * @param string $html Html string
     * @return array
     * @since 2.23.0
     */
    protected function extractImages($html)
    {
        if (empty($html)) {
            return [];
        }

        $libxmlPreviousState = libxml_use_internal_errors(true);

        $dom = new DOMDocument;
        $dom->loadHTML($html);

        libxml_clear_errors();
        libxml_use_internal_errors($libxmlPreviousState);

        $images = [];

        //Gets all image tags
        foreach ($dom->getElementsByTagName('img') as $item) {
            $src = $item->getAttribute('src');

            if (in_array(strtolower(pathinfo($src, PATHINFO_EXTENSION)), ['gif', 'jpg', 'jpeg', 'png'])) {
                $images[] = $src;
            }
        }

        //Gets all Youtube videos
        if (preg_match_all('/\[youtube](.+?)\[\/youtube]/', $html, $items)) {
            foreach ($items[1] as $item) {
                $images[] = Youtube::getPreview($item);
            }
        }

        return $images;
    }

    /**
     * Internal method to get the preview size
     * @param string $image Image url or path
     * @return array Array with width and height
     */
    protected function getPreviewSize($image)
    {
        return array_slice(getimagesize($image), 0, 2);
    }

    /**
     * Gets all the available images from an html string, including the previews
     *  of Youtube videos, and returns an array of `Entity`
     * @param string $html Html string
     * @return array Array of entities. Each `Entity` has `url`, `width` and
     *  `height` properties
     * @since 2.23.0
     * @uses extractImages()
     * @uses getPreviewSize()
     */
    public function getPreviews($html)
    {
        $images = array_map(function ($url) {
            if ($url && !is_url($url)) {
                //If is relative path
                if (!Folder::isAbsolute($url)) {
                    $url = WWW_ROOT . 'img' . DS . $url;
                }

                if (!file_exists($url)) {
                    return false;
                }

                $thumb = (new ThumbCreator($url))
                    ->resize(1200, 1200)
                    ->save(['format' => 'jpg']);

                $url = $this->getUrl($thumb, true);
            }

            list($width, $height) = $this->getPreviewSize($url);

            return new Entity(compact('url', 'width', 'height'));
        }, $this->extractImages($html));

        return array_filter($images);
    }
}