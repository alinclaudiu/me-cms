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

use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\ORM\ResultSet;
use MeCms\Controller\Admin\AppController;
use MeCms\Model\Entity\PostsCategory;

/**
 * PostsCategories controller
 * @property \MeCms\Model\Table\PostsCategoriesTable $PostsCategories
 */
class PostsCategoriesController extends AppController
{
    /**
     * Called before the controller action.
     * You can use this method to perform logic that needs to happen before
     *  each controller action
     * @param \Cake\Event\EventInterface $event An Event instance
     * @return \Cake\Network\Response|null
     * @uses \MeCms\Model\Table\PostsCategoriesTable::getTreeList()
     */
    public function beforeFilter(EventInterface $event): ?Response
    {
        $result = parent::beforeFilter($event);
        if ($result) {
            return $result;
        }

        if ($this->getRequest()->isAction(['add', 'edit'])) {
            $this->set('categories', $this->PostsCategories->getTreeList());
        }

        return null;
    }

    /**
     * Checks if the provided user is authorized for the request
     * @param array|\ArrayAccess|null $user The user to check the authorization
     *  of. If empty the user in the session will be used
     * @return bool `true` if the user is authorized, otherwise `false`
     * @uses \MeCms\Controller\Component\AuthComponent::isGroup()
     */
    public function isAuthorized($user = null): bool
    {
        //Only admins can delete posts categories. Admins and managers can access other actions
        return $this->Auth->isGroup($this->getRequest()->isDelete() ? ['admin'] : ['admin', 'manager']);
    }

    /**
     * Lists posts categories
     * @return void
     * @uses \MeCms\Model\Table\PostsCategoriesTable::getTreeList()
     */
    public function index(): void
    {
        $categories = $this->PostsCategories->find()
            ->contain(['Parents' => ['fields' => ['title']]])
            ->orderAsc(sprintf('%s.lft', $this->PostsCategories->getAlias()))
            ->formatResults(function (ResultSet $results) {
                //Gets categories as tree list
                $treeList = $this->PostsCategories->getTreeList()->toArray();

                return $results->map(function (PostsCategory $category) use ($treeList) {
                    return $category->set('title', $treeList[$category->get('id')]);
                });
            });

        $this->set(compact('categories'));
    }

    /**
     * Adds posts category
     * @return \Cake\Http\Response|null|void
     */
    public function add()
    {
        $category = $this->PostsCategories->newEmptyEntity();

        if ($this->getRequest()->is('post')) {
            $category = $this->PostsCategories->patchEntity($category, $this->getRequest()->getData());

            if ($this->PostsCategories->save($category)) {
                $this->Flash->success(I18N_OPERATION_OK);

                return $this->redirect($this->referer(['action' => 'index']));
            }

            $this->Flash->error(I18N_OPERATION_NOT_OK);
        }

        $this->set(compact('category'));
        $this->set('title', __d('me_cms', 'Add posts category'));
        $this->render('form');
    }

    /**
     * Edits posts category
     * @param string $id Posts category ID
     * @return \Cake\Http\Response|null|void
     */
    public function edit(string $id)
    {
        $category = $this->PostsCategories->get($id);

        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $category = $this->PostsCategories->patchEntity($category, $this->getRequest()->getData());

            if ($this->PostsCategories->save($category)) {
                $this->Flash->success(I18N_OPERATION_OK);

                return $this->redirect($this->referer(['action' => 'index']));
            }

            $this->Flash->error(I18N_OPERATION_NOT_OK);
        }

        $this->set(compact('category'));
        $this->set('title', __d('me_cms', 'Edit posts category'));
        $this->render('form');
    }

    /**
     * Deletes posts category
     * @param string $id Posts category ID
     * @return \Cake\Http\Response|null
     */
    public function delete(string $id): ?Response
    {
        $this->getRequest()->allowMethod(['post', 'delete']);

        //Before deleting, it checks if the category has some posts
        $category = $this->PostsCategories->get($id);
        [$method, $message] = ['alert', I18N_BEFORE_DELETE];
        if (!$category->get('post_count')) {
            $this->PostsCategories->deleteOrFail($category);
            [$method, $message] = ['success', I18N_OPERATION_OK];
        }
        call_user_func([$this->Flash, $method], $message);

        return $this->redirect($this->referer(['action' => 'index']));
    }
}
