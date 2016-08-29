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
 * @author      Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright   Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link        http://git.novatlantis.it Nova Atlantis Ltd
 */

$this->extend('/Admin/Common/index');
$this->assign('title', __d('me_cms', 'Pages categories'));

$this->append('actions', $this->Html->button(
    __d('me_cms', 'Add'),
    ['action' => 'add'],
    ['class' => 'btn-success', 'icon' => 'plus']
));
$this->append('actions', $this->Html->button(
    __d('me_cms', 'Add page'),
    ['controller' => 'Pages', 'action' => 'add'],
    ['class' => 'btn-success', 'icon' => 'plus']
));
?>

<table class="table table-hover">
    <thead>
        <tr>
            <th class="text-center"><?= __d('me_cms', 'ID') ?></th>
            <th><?= __d('me_cms', 'Title') ?></th>
            <th class="min-width text-center"><?= __d('me_cms', 'Parent') ?></th>
            <th class="min-width text-center"><?= __d('me_cms', 'Pages') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($categories as $category) : ?>
            <tr>
                <td class="min-width text-center">
                    <code><?= $category->id ?></code>
                </td>
                <td>
                    <strong>
                        <?php
                            echo $this->Html->link(
                                $category->title,
                                ['action' => 'edit', $category->id]
                            );
                        ?>
                    </strong>
                    <?php
                        $actions = [
                            $this->Html->link(
                                __d('me_cms', 'Edit'),
                                ['action' => 'edit', $category->id],
                                ['icon' => 'pencil']
                            ),
                        ];

                        //Only admins can delete pages categories
                        if ($this->Auth->isGroup('admin')) {
                            $actions[] = $this->Form->postLink(
                                __d('me_cms', 'Delete'),
                                ['action' => 'delete', $category->id],
                                [
                                    'class' => 'text-danger',
                                    'icon' => 'trash-o',
                                    'confirm' => __d('me_cms', 'Are you sure you want to delete this?'),
                                ]
                            );
                        }
                        
                        if ($category->page_count) {
                            $actions[] = $this->Html->link(
                                __d('me_cms', 'Open'),
                                ['_name' => 'pagesCategory', $category->slug],
                                ['icon' => 'external-link', 'target' => '_blank']
                            );
                        }
                        
                        echo $this->Html->ul($actions, ['class' => 'actions']);
                    ?>
                </td>
                <td class="min-width text-center">
                    <?php
                    if (!empty($category->parent->title)) {
                        echo $category->parent->title;
                    }
                    ?>
                </td>
                <td class="min-width text-center">
                    <?php
                    if ($category->page_count) {
                        echo $this->Html->link(
                            $category->page_count,
                            [
                                'controller' => 'Pages',
                                'action' => 'index',
                                '?' => ['category' => $category->id],
                            ],
                            ['title' => __d('me_cms', 'View items that belong to this category')]
                        );
                    } else {
                        echo $category->page_count;
                    }
                    ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>