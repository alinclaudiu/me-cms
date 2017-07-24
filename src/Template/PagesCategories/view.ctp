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
$this->extend('/Common/index');
$this->assign('title', $category->title);

/**
 * Userbar
 */
$this->userbar($this->Html->link(
    __d('me_cms', 'Edit category'),
    ['action' => 'edit', $category->id, 'prefix' => ADMIN_PREFIX],
    ['icon' => 'pencil', 'target' => '_blank']
));
$this->userbar($this->Form->postLink(
    __d('me_cms', 'Delete category'),
    ['action' => 'delete', $category->id, 'prefix' => ADMIN_PREFIX],
    [
        'class' => 'text-danger',
        'icon' => 'trash-o',
        'confirm' => __d('me_cms', 'Are you sure you want to delete this?'),
        'target' => '_blank',
    ]
));

/**
 * Breadcrumb
 */
$this->Breadcrumbs->add($category->title, ['_name' => 'pagesCategory', $category->title]);

$pages = collection($pages)->map(function ($page) {
    return $this->Html->link($page->title, ['_name' => 'page', $page->slug]);
})->toList();

echo $this->Html->ul($pages, ['icon' => 'caret-right']);
