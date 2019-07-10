<?php
declare(strict_types=1);
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
use Cake\Http\Response;
use Cake\ORM\ResultSet;
use MeCms\Controller\AppController;
use MeCms\Model\Entity\PagesCategory;

/**
 * PagesCategories controller
 * @property \MeCms\Model\Table\PagesCategoriesTable $PagesCategories
 */
class PagesCategoriesController extends AppController
{
    /**
     * Called before the controller action.
     * You can use this method to perform logic that needs to happen before
     *  each controller action.
     * @param \Cake\Event\Event $event An Event instance
     * @return void
     * @uses \MeCms\Model\Table\PagesCategoriesTable::getTreeList()
     */
    public function beforeFilter(Event $event): void
    {
        parent::beforeFilter($event);

        if ($this->request->isAction(['add', 'edit'])) {
            $this->set('categories', $this->PagesCategories->getTreeList());
        }
    }

    /**
     * Checks if the provided user is authorized for the request
     * @param array|\ArrayAccess|null $user The user to check the authorization
     *  of. If empty the user in the session will be used
     * @return bool `true` if the user is authorized, otherwise `false`
     */
    public function isAuthorized($user = null): bool
    {
        //Only admins can delete pages categories. Admins and managers can access other actions
        return $this->Auth->isGroup($this->request->isDelete() ? ['admin'] : ['admin', 'manager']);
    }

    /**
     * Lists pages categories
     * @return void
     * @uses \MeCms\Model\Table\PagesCategoriesTable::getTreeList()
     */
    public function index(): void
    {
        $categories = $this->PagesCategories->find()
            ->contain(['Parents' => ['fields' => ['title']]])
            ->order([sprintf('%s.lft', $this->PagesCategories->getAlias()) => 'ASC'])
            ->formatResults(function (ResultSet $results) {
                //Gets categories as tree list
                $treeList = $this->PagesCategories->getTreeList()->toArray();

                return $results->map(function (PagesCategory $category) use ($treeList) {
                    return $category->set('title', $treeList[$category->id]);
                });
            });

        $this->set(compact('categories'));
    }

    /**
     * Adds pages category
     * @return \Cake\Http\Response|null|void
     */
    public function add()
    {
        $category = $this->PagesCategories->newEntity();

        if ($this->request->is('post')) {
            $category = $this->PagesCategories->patchEntity($category, $this->request->getData());

            if ($this->PagesCategories->save($category)) {
                $this->Flash->success(I18N_OPERATION_OK);

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(I18N_OPERATION_NOT_OK);
        }

        $this->set(compact('category'));
    }

    /**
     * Edits pages category
     * @param string $id Pages category ID
     * @return \Cake\Http\Response|null|void
     */
    public function edit(string $id)
    {
        $category = $this->PagesCategories->get($id);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $category = $this->PagesCategories->patchEntity($category, $this->request->getData());

            if ($this->PagesCategories->save($category)) {
                $this->Flash->success(I18N_OPERATION_OK);

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(I18N_OPERATION_NOT_OK);
        }

        $this->set(compact('category'));
    }

    /**
     * Deletes pages category
     * @param string $id Pages category ID
     * @return \Cake\Http\Response|null
     */
    public function delete(string $id): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);

        $category = $this->PagesCategories->get($id);

        //Before deleting, it checks if the category has some pages
        if (!$category->page_count) {
            $this->PagesCategories->deleteOrFail($category);
            $this->Flash->success(I18N_OPERATION_OK);
        } else {
            $this->Flash->alert(I18N_BEFORE_DELETE);
        }

        return $this->redirect(['action' => 'index']);
    }
}
