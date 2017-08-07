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
$this->extend('/Admin/Common/form');
$this->assign('title', $title = __d('me_cms', 'Edit photo'));
?>

<?= $this->Form->create($photo); ?>
<div class='float-form'>
    <?= $this->Form->control('album_id', ['label' => __d('me_cms', 'Album')]) ?>
    <?= $this->Form->control('active', ['label' => I18N_PUBLISHED]) ?>
</div>
<fieldset>
    <p><?= $this->Html->strong(I18N_PREVIEW) ?></p>
    <?php
        echo $this->Thumb->resize($photo->path, ['width' => 1186], ['class' => 'img-thumbnail margin-15']);

        echo $this->Form->control('filename', [
            'disabled' => true,
            'label' => I18N_FILENAME,
        ]);
        echo $this->Form->control('description', [
            'label' => I18N_DESCRIPTION,
            'rows' => 3,
            'type' => 'textarea',
        ]);
    ?>
</fieldset>
<?= $this->Form->submit($title) ?>
<?= $this->Form->end() ?>