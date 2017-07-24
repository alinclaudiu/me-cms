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
    protected $_virtual = ['path', 'preview'];

    /**
     * Gets the photo path (virtual field)
     * @return string|void
     */
    protected function _getPath()
    {
        if (empty($this->_properties['album_id']) || empty($this->_properties['filename'])) {
            return;
        }

        return PHOTOS . $this->_properties['album_id'] . DS . $this->_properties['filename'];
    }

    /**
     * Gets the photo preview (virtual field)
     * @return array|void Array with `preview`, `width` and `height` keys
     * @uses _getPath()
     */
    protected function _getPreview()
    {
        $preview = $this->_getPath();

        if (!$preview) {
            return;
        }

        $thumb = (new ThumbCreator($preview))->resize(1200, 1200)->save(['format' => 'jpg']);
        $preview = thumbUrl($thumb, true);

        list($width, $height) = getimagesize($thumb);

        return compact('preview', 'width', 'height');
    }
}
