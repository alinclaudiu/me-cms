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
$this->extend('/Admin/Common/index');
$this->assign('title', __d('me_cms', 'Changelogs'));
$this->Html->css(ME_CMS . '.admin/changelogs', ['block' => 'css_bottom']);
?>

<div class="well">
    <?= $this->Form->createInline(false, ['type' => 'get']) ?>
    <fieldset>
        <?php
            echo $this->Form->label('file', __d('me_cms', 'Changelog'));
            echo $this->Form->control('file', [
                'default' => $this->request->getQuery('file'),
                'label' => __d('me_cms', 'Changelog'),
                'name' => 'file',
                'onchange' => 'send_form(this)',
            ]);
            echo $this->Form->submit(__d('me_cms', 'Select'));
        ?>
    </fieldset>
    <?= $this->Form->end() ?>
</div>

<?php if (!empty($changelog)) : ?>
<div id="changelog">
    <?= $this->CommonMark->convertToHtml($changelog) ?>
</div>
<?php endif; ?>