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
namespace MeCms\Controller\Admin;

use MeCms\Controller\AppController;

/**
 * Posts controller
 * @property \MeCms\Model\Table\PostsTable $Posts
 */
class PostsController extends AppController {
	/**
	 * Called before the controller action. 
	 * You can use this method to perform logic that needs to happen before each controller action.
	 * @param \Cake\Event\Event $event An Event instance
	 * @uses MeCms\Controller\AppController::beforeFilter()
	 * @uses MeCms\Model\Table\PostsCategoriesTable::getList()
	 * @uses MeCms\Model\Table\PostsCategoriesTable::getTreeList()
	 * @uses MeCms\Model\Table\UsersTable::getActiveList()
	 * @uses MeCms\Model\Table\UsersTable::getList()
	 * @uses MeTools\Network\Request::isAction()
	 */
	public function beforeFilter(\Cake\Event\Event $event) {
		parent::beforeFilter($event);
		
		if($this->request->isAction('index')) {
			$categories = $this->Posts->Categories->getList();
			$users = $this->Posts->Users->getList();
		}
		elseif($this->request->isAction(['add', 'edit'])) {
			$categories = $this->Posts->Categories->getTreeList();
			$users = $this->Posts->Users->getActiveList();
		}
		
		//Checks for categories
		if(isset($categories) && empty($categories)) {
			$this->Flash->alert(__d('me_cms', 'Before you can manage posts, you have to create at least a category'));
			$this->redirect(['controller' => 'PostsCategories', 'action' => 'index']);
		}
		
		if(!empty($categories))
			$this->set(compact('categories'));
		
		if(!empty($users))
			$this->set(compact('users'));
	}
	
	/**
	 * Called after the controller action is run, but before the view is rendered.
	 * You can use this method to perform logic or set view variables that are required on every request.
	 * @param \Cake\Event\Event $event An Event instance
	 * @see http://api.cakephp.org/3.0/class-Cake.Controller.Controller.html#_beforeRender
	 * @uses MeCms\Controller\AppController::beforeRender()
	 * @uses MeCms\Controller\Component\KcFinderComponent::configure()
	 */
	public function beforeRender(\Cake\Event\Event $event) {
		parent::beforeRender($event);
		
		//Loads the KcFinder component and configures KCFinder
		$this->loadComponent('MeCms.KcFinder');
		$this->KcFinder->configure();
	}
	
	/**
	 * Check if the provided user is authorized for the request
	 * @param array $user The user to check the authorization of. If empty the user in the session will be used
	 * @return bool TRUE if the user is authorized, otherwise FALSE
	 * @uses MeCms\Controller\AppController::isAuthorized()
	 * @uses MeCms\Controller\Component\AuthComponent::isGroup()
	 * @uses MeCms\Model\Table\PostsTable::isOwnedBy()
	 * @uses MeTools\Network\Request::isAction()
	 */
	public function isAuthorized($user = NULL) {
		//Only admins and managers can edit all posts. Users can edit only their own posts
		if($this->request->isAction('edit') && !$this->Auth->isGroup(['admin', 'manager']))
			return $this->Posts->isOwnedBy($this->request->pass[0], $this->Auth->user('id'));
				
		//Admins and managers can access other actions
		return parent::isAuthorized($user);
	}
	
	/**
     * Lists posts
	 * @uses MeCms\Model\Table\PostsTable::fromFilter()
     */
    public function index() {		
		$this->set('posts', $this->paginate(
			$this->Posts->find()
				->contain([
					'Categories'	=> ['fields' => ['title']],
					'Users'			=> ['fields' => ['first_name', 'last_name']]
				])
				->select(['id', 'title', 'slug', 'priority', 'active', 'created'])
				->where($this->Posts->fromFilter($this->request->query))
				->order(['Posts.created' => 'DESC'])
		));
    }

    /**
     * Adds post
	 * @uses MeCms\Controller\Component\AuthComponent::isGroup()
     */
    public function add() {		
        $post = $this->Posts->newEntity();
		
        if($this->request->is('post')) {
			//Only admins and managers can add posts on behalf of other users
			if(!$this->Auth->isGroup(['admin', 'manager']))
				$this->request->data('user_id', $this->Auth->user('id'));
			
            $post = $this->Posts->patchEntity($post, $this->request->data);
			
            if($this->Posts->save($post)) {
                $this->Flash->success(__d('me_cms', 'The post has been saved'));
                return $this->redirect(['action' => 'index']);
            } 
			else
                $this->Flash->error(__d('me_cms', 'The post could not be saved'));
        }
		
        $this->set(compact('post'));
    }

    /**
     * Edits post
     * @param string $id Post ID
	 * @uses MeCms\Controller\Component\AuthComponent::isGroup()
     * @throws \Cake\Network\Exception\NotFoundException
     */
    public function edit($id = NULL)  {
        $post = $this->Posts->get($id);
		
        if($this->request->is(['patch', 'post', 'put'])) {
			//Only admins and managers can edit posts on behalf of other users
			if(!$this->Auth->isGroup(['admin', 'manager']))
				$this->request->data('user_id', $this->Auth->user('id'));
			
            $post = $this->Posts->patchEntity($post, $this->request->data);
			
            if($this->Posts->save($post)) {
                $this->Flash->success(__d('me_cms', 'The post has been saved'));
                return $this->redirect(['action' => 'index']);
            } 
			else
                $this->Flash->error(__d('me_cms', 'The post could not be saved'));
        }
		
        $this->set(compact('post'));
    }
    /**
     * Deletes post
     * @param string $id Post ID
     * @throws \Cake\Network\Exception\NotFoundException
     */
    public function delete($id = NULL) {
        $this->request->allowMethod(['post', 'delete']);
		
        $post = $this->Posts->get($id);
		
        if($this->Posts->delete($post))
            $this->Flash->success(__d('me_cms', 'The post has been deleted'));
        else
            $this->Flash->error(__d('me_cms', 'The post could not be deleted'));
			
        return $this->redirect(['action' => 'index']);
    }
}