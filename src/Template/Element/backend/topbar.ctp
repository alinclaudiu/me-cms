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

<nav id="topbar" class="navbar navbar-default navbar-inverse navbar-fixed-top" role="navigation">
	<div class="container-fluid">
		<!-- Brand and toggle get grouped for better mobile display -->
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#backend-topbar-collapse">
				<span class="sr-only"><?= __d('me_cms', 'Toggle navigation') ?></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
		</div>
		
		<!-- Collect the nav links, forms, and other content for toggling -->
		<div class="collapse navbar-collapse" id="backend-topbar-collapse">
			<ul class="nav navbar-nav hidden-xs hidden-sm">
				<?=	$homeLink = $this->Html->li($this->Html->link(__d('me_cms', 'Homepage'), ['_name' => 'homepage'], ['icon' => 'home', 'target' => '_blank'])) ?>
			</ul>
			<ul class="nav navbar-nav visible-xs visible-sm">
				<?php
					echo $homeLink;
					
					foreach(config('backend.menu') as $menu) {
						//Gets the plugin name
						list($plugin, $menu) = pluginSplit($menu);

						//Loads the menu helper
						$this->Menu = $this->helpers()->load($helper = sprintf('%s.Menu', $plugin));

						//Gets the menu
						echo $this->Html->li($this->Menu->get($menu, 'dropdown'), ['class' => 'dropdown']);
						
						//Unloads the helper
						$this->helpers()->unload($helper);
					}
					
					echo $menu = $this->Html->li($this->Dropdown->menu($auth['full_name'], ['icon' => 'user'], [
						$this->Html->link(__d('me_cms', 'Change password'), ['controller' => 'Users', 'action' => 'change_password', 'plugin' => 'MeCms']),
						$this->Html->link(__d('me_cms', 'Logout'), ['_name' => 'logout'])
					]),	['class' => 'dropdown']);
				?>
			</ul>
			<ul class="nav navbar-nav navbar-right hidden-xs hidden-sm"><?= $menu; ?></ul>
		</div><!-- /.navbar-collapse -->
	</div><!-- /.container-fluid -->
</nav>