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

<!DOCTYPE html>
<html lang="en">
	<head>
		<?php
			echo $this->Html->charset();
			echo $this->Layout->viewport();
			echo $this->Html->title($this->fetch('title'));
			echo $this->Html->meta('icon');
			echo $this->Html->meta(__d('me_cms', 'Latest posts'), '/posts/rss', ['type' => 'rss']);
			echo $this->fetch('meta');
			
			echo $this->Html->css([
				'MeTools.font-awesome.min',
				'MeCms.frontend/bootstrap.min',
				'MeTools.default',
				'MeTools.forms',
				'MeCms.frontend/layout',
				'MeCms.frontend/contents',
				'MeCms.frontend/photos'
			]);
			echo $this->fetch('css');
			
			echo $this->Html->js([
				'MeTools.jquery.min',
				'MeCms.frontend/bootstrap.min',
				'MeTools.default'
			]);
			echo $this->fetch('script');
		?>
	</head>
	<body>
		<header>
			<div class="container">
				<?php
					$logo = $this->Html->h1(config('main.title'));

					//Check if the logo image exists
					if(is_readable(WWW_ROOT.'img'.DS.config('frontend.logo')))
						$logo = $this->Html->img(config('frontend.logo'));

					echo $this->Html->link($logo, '/', ['id' => 'logo', 'title' => __d('me_cms', 'Homepage')]);		
				?>
			</div>
			<?=
				//TO-DO: cache!
				$this->element('MeCms.frontend/topbar')
			?>
		</header>
		<div class="container">
			<div class="row">
				<div id="content" class="col-sm-8 col-md-9">
					<?php
						echo $this->Flash->render();
						echo $this->fetch('content');
					?>
				</div>
				<div id="sidebar" class="col-sm-4 col-md-3">
					<?= $this->fetch('sidebar') ?>
					<?= $this->allWidgets() ?>
				</div>
			</div>
		</div>
		<footer class="navbar-fixed-bottom">
			<?=
				//TO-DO: cache!
				$this->element('MeCms.frontend/footer')
			?>
		</footer>
		<?php
			echo $this->Library->analytics(config('frontend.analytics'));
			echo $this->fetch('css_bottom');
			echo $this->fetch('script_bottom');
		?>
	</body>
</html>