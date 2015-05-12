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
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2015, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */
?>
	
<?php $this->assign('title', __d('me_cms', 'Media browser')); ?>

<div class="systems index">
	<?= $this->Html->h2(__d('me_cms', 'Media browser')) ?>
	<div class="well">
		<?php 
			echo $this->Form->createInline(FALSE, ['type' => 'get']);
			echo $this->Form->label('type', __d('me_cms', 'Type'));
			echo $this->Form->input('type', [
				'default'	=> $this->request->query('type'),
				'onchange'	=> 'send_form(this)'
			]);
			echo $this->Form->submit(__d('me_cms', 'Select'));
			echo $this->Form->end();
		?>
	</div>
	
	<?= empty($kcfinder) ? NULL : $this->Html->iframe($kcfinder, ['id' => 'kcfinder', 'width' => '100%']) ?>
</div>