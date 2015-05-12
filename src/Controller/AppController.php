<?php
/**
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
 * @copyright	Copyright (c) 2015, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Controller;

use App\Controller\AppController as BaseController;
use Cake\Core\Configure;

/**
 * Application controller class
 */
class AppController extends BaseController {
	/**
	 * Uploads a file.
	 * 
	 * This methods renders the element `backend/uploader/response`.
	 * @param array $file File ($_FILE)
	 * @param string $target Target directory
	 * @return mixed Full file path or FALSE
	 */
	protected function _upload($file, $target) {
		//Checks if the file was successfully uploaded
		if(isset($file['error']) && $file['error'] == UPLOAD_ERR_OK && is_uploaded_file($file['tmp_name'])) {
			//Updated the target, adding the filename
			if(!file_exists($target.DS.$file['name']))
				$target .= DS.$file['name'];
			//If the file already exists, adds the name of the temporary file to the filename
			else
				$target .= DS.pathinfo($file['name'], PATHINFO_FILENAME).'_'.basename($file['tmp_name']).'.'.pathinfo($file['name'], PATHINFO_EXTENSION);

			//Checks if the file was successfully moved to the target directory
			if(!@move_uploaded_file($file['tmp_name'], $file['target'] = $target))
				$error = __d('me_cms', 'The file was not successfully moved to the target directory');
		}
		else
			$error = __d('me_cms', 'The file was not successfully uploaded');

		if(!empty($error)) {
			$success = FALSE;
			$this->set(compact('error'));
		}
		
		$this->set(compact('file'));

		//Renders
		$this->render('/Element/backend/uploader/response', FALSE);
		
		return isset($success) && !$success ? FALSE : $target;
	}
	
	/**
	 * Called before the controller action. 
	 * You can use this method to perform logic that needs to happen before each controller action.
	 * @param \Cake\Event\Event $event An Event instance
	 * @see http://api.cakephp.org/3.0/class-Cake.Controller.Controller.html#_beforeFilter
	 * @uses MeTools\Network\Request::hasPrefix()
	 * @uses isOffline()
	 */
	public function beforeFilter(\Cake\Event\Event $event) {
		//Checks if the site has been taken offline
		if($this->isOffline())
			$this->redirect(['_name' => 'offline']);
		
		//TO-DO: fix
		Configure::write('Config.language', 'it');
		
		//If the current request has no prefix, it authorizes the current action
		if(!$this->request->hasPrefix())
			$this->Auth->allow($this->request->action);
		
		if($this->Auth->user())
			$this->Auth->config('authError', __d('me_cms', 'You are not authorized for this action'));
		
		//Sets the paginate limit and the maximum paginate limit
		//See http://book.cakephp.org/3.0/en/controllers/components/pagination.html#limit-the-maximum-number-of-rows-that-can-be-fetched
		$this->paginate['limit'] = $this->paginate['maxLimit'] = $this->request->isPrefix('admin') ? config('backend.records') : config('frontend.records');
	}
	
	/**
	 * Called after the controller action is run, but before the view is rendered.
	 * You can use this method to perform logic or set view variables that are required on every request.
	 * @param \Cake\Event\Event $event An Event instance
	 * @see http://api.cakephp.org/3.0/class-Cake.Controller.Controller.html#_beforeRender
	 * @uses MeTools\Network\Request::isAdmin()
	 */
	public function beforeRender(\Cake\Event\Event $event) {
		//Uses a custom View class (`AppView` or `AdminView`)
		$this->viewClass = $this->request->isAdmin() ? 'MeCms.View/Admin' : 'MeCms.View/App';
		
		//Sets auth data for views
		$this->set('auth', empty($this->Auth) ? FALSE : $this->Auth->user());
	}
	
	/**
	 * Initialization hook method
	 */
	public function initialize() {
		//Loads components
		$this->loadComponent('MeCms.Auth');
        $this->loadComponent('MeTools.Flash');
        $this->loadComponent('RequestHandler');
		
		if(config('security.recaptcha'))
			$this->loadComponent('MeTools.Recaptcha');
    }
	
	/**
	 * Checks if the provided user is authorized for the request
	 * @param array $user The user to check the authorization of. If empty the user in the session will be used
	 * @return bool TRUE if the user is authorized, otherwise FALSE
	 * @uses MeCms\Controller\Component\AuthComponent::isGroup()
	 */
	public function isAuthorized($user = NULL) {		
		//By default, admins and managers can access every action
		return $this->Auth->isGroup(['admin', 'manager']);
	}
	
	/**
	 * Checks if the site is offline
	 * @return bool TRUE if the site is offline, otherwise FALSE
	 * @uses MeTools\Network\Request::isAction()
	 */
	public function isOffline() {
		return config('frontend.offline') && !$this->request->isAction('offline', 'Systems');
	}
}