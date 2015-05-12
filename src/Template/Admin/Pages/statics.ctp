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

<?php $this->assign('title', __d('me_cms', 'Static Pages')); ?>

<div class="pages index">
	<?= $this->Html->h2(__d('me_cms', 'Static Pages')) ?>
	<table class="table table-striped">
		<tr>
			<th><?= __d('me_cms', 'Filename') ?></th>
			<th><?= __d('me_cms', 'Title') ?></th>
			<th><?= __d('me_cms', 'Path') ?></th>
		</tr>
		<?php foreach($pages as $page): ?>
			<tr>
				<td>
					<?php 
						$title = $this->Html->link($page['StaticPage']['filename'], $url = am(['_name' => 'page'], $args = $page['StaticPage']['args']));
						
						echo $this->Html->strong($title);
						
						$actions = [
							$this->Html->link(__d('me_cms', 'Open'), $url, ['icon' => 'external-link', 'target' => '_blank'])
						];
						
						echo $this->Html->ul($actions, ['class' => 'actions']);
					?>
				</td>
				<td><?= $page['StaticPage']['title'] ?></td>
				<td><?= $page['StaticPage']['path'] ?></td>
			</tr>
		<?php endforeach; ?>
	</table>
</div>