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

if (empty($months) || $months->count() < 2) {
    return;
}

$this->extend('/common/widget');
$this->assign('title', __d('me_cms', 'Posts by month'));

$months = $months->map(function (array $month) {
    return $this->Html->link($month['created']->i18nFormat('MMMM yyyy'), [
        '_name' => 'postsByDate',
        sprintf('%s/%s', $month['created']->i18nFormat('yyyy'), $month['created']->i18nFormat('MM')),
    ]);
})->toArray();

echo $this->Html->ul($months, ['icon' => 'caret-right']);
