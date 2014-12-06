<?php
/**
 * SystemsController
 *
 * This file is part of MeCms.
 *
 * MeCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2014, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCms\Controller
 */

App::uses('MeCmsAppController', 'MeCms.Controller');
App::uses('BannerManager', 'MeCms.Utility');
App::uses('PhotoManager', 'MeCms.Utility');
App::uses('System', 'MeTools.Utility');

/**
 * Systems Controller
 */
class SystemsController extends MeCmsAppController {
	/**
	 * Checks if the provided user is authorized for the request.
	 * @param array $user The user to check the authorization of. If empty the user in the session will be used.
	 * @return bool TRUE if $user is authorized, otherwise FALSE
	 * @uses MeAuthComponenet::isAdmin()
	 */
	public function isAuthorized($user = NULL) {
		//Only admins can access this controller
		return $this->Auth->isAdmin();
	}
	
	/**
	 * Gets the MeCMS version number.
	 * @return string MeCMS version number
	 */
	private function _getVersion() {
		return file_get_contents(CakePlugin::path('MeCms').'version');
	}
	
	/**
	 * Media browser with KCFinder
	 */
	public function admin_browser() {
		//Checks for KCFinder
		if(!is_readable(WWW_ROOT.'kcfinder')) {
			$this->Session->flash(__d('me_cms', '%s is not present into %s', 'FKFinder', WWW_ROOT.'kcfinder'), 'error');
			$this->redirect('/admin');
		}
		
		//Sets the KCFinder session values
		$this->Session->write('KCFINDER', array(
			'denyExtensionRename'	=> TRUE,
			'denyUpdateCheck'		=> TRUE,
			'dirnameChangeChars'	=> array(' ' => '_', ':' => '_'),
			'disabled'				=> FALSE,
			'filenameChangeChars'	=> array(' ' => '_', ':' => '_'),
			'jpegQuality'			=> 100,
			'uploadURL'				=> sprintf('%s/%s', $this->webroot.WEBROOT_DIR, 'uploads')
		));
		
		//Sets the KCFinder path
		$kcfinder = sprintf('%s/%s/browse.php?lang=%s', $this->webroot.WEBROOT_DIR, 'kcfinder', Configure::read('Config.language'));
				
		$this->set(am(array('title_for_layout' => __d('me_cms', 'Media browser')), compact('kcfinder')));
	}
	
	/**
	 * Manages cache and thumbnails.
	 * @uses System::checkCacheStatus()
	 * @uses System::getCacheSize()
	 * @uses System::getThumbsSize()
	 */
	public function admin_cache() {
        $this->set(array(
			'cacheStatus'		=> System::checkCacheStatus(),
			'cacheSize'			=> System::getCacheSize(),
			'title_for_layout'	=> __d('me_cms', 'Cache and thumbs'),
			'thumbsSize'		=> System::getThumbsSize()
        ));
    }
	
	/**
	 * System checkup.
	 * @uses _getVersion()
	 * @uses BannerManager::getFolder()
	 * @uses BannerManager::getTmpPath()
	 * @uses PhotoManager::getFolder()
	 * @uses PhotoManager::getTmpPath()
	 * @uses System::checkApacheModule()
	 * @uses System::checkCache()
	 * @uses System::checkCacheStatus()
	 * @uses System::checkLogs()
	 * @uses System::checkPhpExtension()
	 * @uses System::checkPhpVersion()
	 * @uses System::checkThumbs()
	 * @uses System::checkTmp()
	 * @uses System::dirIsWritable()
	 * @uses System::getCakeVersion()
	 * @uses System::getPluginsVersion()
	 * @uses System::which()
	 */
	public function admin_checkup() {
		$phpRequired = '5.2.8';
		
		//Sets the results of the checks
		$this->set(array(
			'bannersWWW'		=> System::dirIsWritable(BannerManager::getFolder()),
			'bannersTmp'		=> System::dirIsWritable(BannerManager::getTmpPath()),
			'cache'				=> System::checkCache(),
			'cacheStatus'		=> System::checkCacheStatus(),
			'cakeVersion'		=> System::getCakeVersion(),
			'expires'			=> System::checkApacheModule('mod_expires'),
			'ffmpegthumbnailer'	=> System::which('ffmpegthumbnailer'),
			'imagick'			=> System::checkPhpExtension('imagick'),
			'logs'				=> System::checkLogs(),
			'photosWWW'			=> System::dirIsWritable(PhotoManager::getFolder()),
			'photosTmp'			=> System::dirIsWritable(PhotoManager::getTmpPath()),
			'phpRequired'		=> $phpRequired,
			'phpVersion'		=> System::checkPhpVersion($phpRequired),
			'plugins'			=> System::getPluginsVersion('MeCms'),
			'rewrite'			=> System::checkApacheModule('mod_rewrite'),
			'tmp'				=> System::checkTmp(),
			'thumbs'			=> System::checkThumbs(),
			'version'			=> self::_getVersion()
		));
		
		$this->set('title_for_layout', __d('me_cms', 'System checkup'));
	}
	
	/**
	 * Clears the cache.
	 * @uses System::clearCache()
	 */
	public function admin_clear_cache() {
		$this->request->onlyAllow('post', 'delete');
		
		if(System::clearCache())
			$this->Session->flash(__d('me_cms', 'The cache has been cleared'), 'success');
		else
			$this->Session->flash(__d('me_cms', 'The cache is not writable'), 'error');
		
		$this->redirect(array('action' => 'cache'));
	}
	
	/**
	 * Clears the thumbnails.
	 * @uses System::clearThumbs()
	 */
	public function admin_clear_thumbs() {
		$this->request->onlyAllow('post', 'delete');
		
		if(System::clearThumbs())
			$this->Session->flash(__d('me_cms', 'Thumbnails have been deleted'), 'success');
		else
			$this->Session->flash(__d('me_cms', 'Thumbnails have not been deleted'), 'error');
		
		$this->redirect(array('action' => 'cache'));
	}
}