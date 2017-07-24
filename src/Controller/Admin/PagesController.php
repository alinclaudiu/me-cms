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
namespace MeCms\Controller\Admin;

use Cake\Event\Event;
use MeCms\Controller\AppController;
use MeCms\Utility\StaticPage;

/**
 * Pages controller
 * @property \MeCms\Model\Table\PagesTable $Pages
 */
class PagesController extends AppController
{
    /**
     * Called before the controller action.
     * You can use this method to perform logic that needs to happen before
     *  each controller action.
     * @param \Cake\Event\Event $event An Event instance
     * @return \Cake\Network\Response|null|void
     * @uses MeCms\Controller\AppController::beforeFilter()
     * @uses MeCms\Model\Table\PagesCategoriesTable::getList()
     * @uses MeCms\Model\Table\PagesCategoriesTable::getTreeList()
     * @uses MeCms\Model\Table\UsersTable::getActiveList()
     * @uses MeCms\Model\Table\UsersTable::getList()
     */
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);

        //Returns, if it's the `indexStatics` action
        if ($this->request->isAction('indexStatics')) {
            return;
        }

        if ($this->request->isAction(['add', 'edit'])) {
            $categories = $this->Pages->Categories->getTreeList();
        } else {
            $categories = $this->Pages->Categories->getList();
        }

        if ($categories->isEmpty()) {
            $this->Flash->alert(__d('me_cms', 'You must first create a category'));

            return $this->redirect(['controller' => 'PagesCategories', 'action' => 'index']);
        }

        $this->set(compact('categories'));
    }

    /**
     * Initialization hook method
     * @return void
     * @uses MeCms\Controller\AppController::initialize()
     */
    public function initialize()
    {
        parent::initialize();

        //Loads KcFinderComponent
        if ($this->request->isAction(['add', 'edit'])) {
            $this->loadComponent(ME_CMS . '.KcFinder');
        }
    }

    /**
     * Check if the provided user is authorized for the request
     * @param array $user The user to check the authorization of. If empty the
     *  user in the session will be used
     * @return bool `true` if the user is authorized, otherwise `false`
     * @uses MeCms\Controller\Component\AuthComponent::isGroup()
     */
    public function isAuthorized($user = null)
    {
        //Everyone can list pages and static pages
        if ($this->request->isAction(['index', 'indexStatics'])) {
            return true;
        }

        //Only admins can delete pages
        if ($this->request->isDelete()) {
            return $this->Auth->isGroup('admin');
        }

        //Admins and managers can access other actions
        return $this->Auth->isGroup(['admin', 'manager']);
    }

    /**
     * Lists pages
     * @return void
     * @uses MeCms\Model\Table\PagesTable::queryFromFilter()
     */
    public function index()
    {
        $query = $this->Pages->find()->contain(['Categories' => ['fields' => ['id', 'title']]]);

        $this->paginate['order'] = ['created' => 'DESC'];

        $pages = $this->paginate($this->Pages->queryFromFilter($query, $this->request->getQuery()));

        $this->set(compact('pages'));
    }

    /**
     * List static pages.
     *
     * Static pages must be located in `APP/View/StaticPages/`.
     * @return void
     * @uses MeCms\Utility\StaticPage::all()
     */
    public function indexStatics()
    {
        $this->set('pages', StaticPage::all());
    }

    /**
     * Adds page
     * @return \Cake\Network\Response|null|void
     */
    public function add()
    {
        $page = $this->Pages->newEntity();

        if ($this->request->is('post')) {
            $page = $this->Pages->patchEntity($page, $this->request->getData());

            if ($this->Pages->save($page)) {
                $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(__d('me_cms', 'The operation has not been performed correctly'));
        }

        $this->set(compact('page'));
    }

    /**
     * Edits page
     * @param string $id Page ID
     * @return \Cake\Network\Response|null|void
     */
    public function edit($id = null)
    {
        $page = $this->Pages->findById($id)
            ->formatResults(function ($results) {
                return $results->map(function ($row) {
                    $row->created = $row->created->i18nFormat(FORMAT_FOR_MYSQL);

                    return $row;
                });
            })
            ->firstOrFail();

        if ($this->request->is(['patch', 'post', 'put'])) {
            $page = $this->Pages->patchEntity($page, $this->request->getData());

            if ($this->Pages->save($page)) {
                $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(__d('me_cms', 'The operation has not been performed correctly'));
        }

        $this->set(compact('page'));
    }
    /**
     * Deletes page
     * @param string $id Page ID
     * @return \Cake\Network\Response|null|void
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        $this->Pages->deleteOrFail($this->Pages->get($id));

        $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));

        return $this->redirect(['action' => 'index']);
    }
}
