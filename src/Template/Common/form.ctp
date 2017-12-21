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
if ($this->fetch('title')) {
    $this->assign('title', $this->fetch('title'));
}
?>

<div class="form">
    <?php
    if ($this->fetch('title')) {
        echo $this->Html->h2($this->fetch('title'));
    }

    echo $this->fetch('content');
    ?>
</div>