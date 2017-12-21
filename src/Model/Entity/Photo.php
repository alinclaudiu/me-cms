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
namespace MeCms\Model\Entity;

use Cake\ORM\Entity;
use Cake\View\HelperRegistry;
use Cake\View\View;
use Thumber\ThumbTrait;
use Thumber\Utility\ThumbCreator;

/**
 * Photo entity
 * @property int $id
 * @property int $album_id
 * @property string $filename
 * @property string $size
 * @property string $description
 * @property bool $active
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 * @property \MeCms\Model\Entity\PhotosAlbum $album
 */
class Photo extends Entity
{
    use ThumbTrait;

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity()
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false,
        'modified' => false,
    ];

    /**
     * Virtual fields that should be exposed
     * @var array
     */
    protected $_virtual = ['path', 'plain_description', 'preview'];

    /**
     * Gets the photo path (virtual field)
     * @return string
     */
    protected function _getPath()
    {
        return PHOTOS . $this->_properties['album_id'] . DS . $this->_properties['filename'];
    }

    /**
     * Gets description as plain text (virtual field)
     * @return string|void
     */
    protected function _getPlainDescription()
    {
        if (empty($this->_properties['description'])) {
            return;
        }

        //Loads the `BBCode` helper
        $BBCode = (new HelperRegistry(new View))->load(ME_TOOLS . '.BBCode');

        return trim(strip_tags($BBCode->remove($this->_properties['description'])));
    }

    /**
     * Gets the photo preview (virtual field)
     * @return array|void Array with `preview`, `width` and `height` keys
     * @uses _getPath()
     */
    protected function _getPreview()
    {
        $thumb = (new ThumbCreator($this->_getPath()))->resize(1200, 1200)->save(['format' => 'jpg']);
        $preview = $this->getUrl($thumb, true);

        list($width, $height) = getimagesize($thumb);

        return compact('preview', 'width', 'height');
    }
}
