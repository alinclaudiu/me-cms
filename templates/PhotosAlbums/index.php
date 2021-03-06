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
$this->extend('/common/index');
$this->assign('title', I18N_PHOTOS);

/**
 * Breadcrumb
 */
$this->Breadcrumbs->add(I18N_PHOTOS, ['_name' => 'albums']);
?>

<div class="row">
    <?php
    foreach ($albums as $album) {
        echo $this->Html->div('col-sm-6 col-md-4 mb-4', $this->element('MeCms.views/photo-preview', [
            'link' => $album->get('url'),
            'path' => $album->get('preview'),
            'text' => __d('me_cms', '{0} photos', $album->get('photo_count')),
            'title' => $album->get('title'),
        ]));
    }
    ?>
</div>
