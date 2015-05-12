<?php
/**
 * This file is part of MeTools.
 *
 * MeTools is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeTools is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeTools.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2015, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Utility;

use Cake\Core\App;
use Cake\Filesystem\File;
use Cake\Filesystem\Folder;

/**
 * An utility to manage photos.
 * 
 * You can use this utility by adding:
 * <code>
 * use MeCms\Utility\PhotoFile;
 * </code>
 */
class PhotoFile {
    /**
     * Alias for `checkFolder()` method
     * @see checkFolder()
     */
    public static function check() {
        return call_user_func_array([get_class(), 'checkFolder'], func_get_args());
    }
	
	/**
	 * Checks if the folder is writable
	 * @return boolean
	 * @uses folder()
	 */
	public static function checkFolder() {
		return folder_is_writable(self::folder());
	}
	
	/**
	 * Creates the album folder
	 * @param int $album_id Album ID
	 * @return boolean
	 * @uses folder()
	 */
	public static function createFolder($album_id) {
		$folder = new Folder();
		return $folder->create(self::folder($album_id), 0777);
	}
	
	/**
	 * Deletes a photo
	 * @param string $filename File name
	 * @param int $album_id Album ID
	 * @return boolean
	 * @uses path()
	 */
	public static function delete($filename, $album_id) {
		$file = new File(self::path($filename, $album_id));
		return $file->delete();
	}
	
	/**
	 * Deletes an album folder
	 * @param int $album_id Album ID
	 * @return boolean
	 * @uses folder()
	 */
	public static function deleteFolder($album_id) {
		$folder = new Folder(self::folder($album_id));
		return $folder->delete();
	}
	
    /**
     * Alias for `getFolder()` method
     * @see getFolder()
     */
    public static function folder() {
        return call_user_func_array([get_class(), 'getFolder'], func_get_args());
    }
	
	/**
	 * Gets the main folder path.
	 * 
	 * If an album ID is specified, it returns the album folder path
	 * @param type $album_id Album ID (optional)
	 * @return string Folder path
	 */
	public static function getFolder($album_id = NULL) {
		$path = WWW_ROOT.'img'.DS.'photos'.DS;
		
		return empty($album_id) ? $path : $path.$album_id;
	}
	
	/**
	 * Gets the file full path
	 * @param string $filename File name
	 * @param int $album_id Album ID
	 * @return string Path
	 * @uses folder()
	 */
	public static function getPath($filename, $album_id) {
		return self::folder($album_id).DS.$filename;
	}
	
    /**
     * Alias for `getPath()` method
     * @see getPath()
     */
    public static function path() {
        return call_user_func_array([get_class(), 'getPath'], func_get_args());
    }
}