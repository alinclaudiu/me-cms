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

use Cake\Cache\Cache;
use Cake\Core\Configure;

require_once 'constants.php';
require_once 'global_functions.php';

/**
 * MeCms configuration
 */
//Loads the configuration from the plugin
Configure::load('MeCms.mecms');

//Loads the configuration from the application, if exists
if(is_readable(CONFIG.'mecms.php'))
	Configure::load('mecms');

//Fixes value
if(!is_int(Configure::read('MeCms.users.activation')) || Configure::read('MeCms.users.activation') > 2)
	Configure::write('MeCms.users.activation', 1);

//Forces debug on localhost, if required
if(is_localhost() && Configure::read('MeCms.main.debug_on_localhost') && !Configure::read('debug'))
	Configure::write('debug', TRUE);

/**
 * Cache configuration
 */
//Loads the cache configuration from the plugin
Configure::load('MeCms.cache');

//Loads the cache from the application, if exists
if(is_readable(CONFIG.'cache.php'))
	Configure::load('cache');

//Adds all cache configurations
foreach(Configure::read('Cache') as $key => $config) {
	//Drops the default cache
	if($key === 'default')
		Cache::drop('default');
	
	Cache::config($key, $config);
}

//Deletes the cache configuration
Configure::delete('Cache');

/**
 * Widgets configuration
 */
//Loads the widgets configuration from the plugin
Configure::load('MeCms.widgets');

//Loads the widgets from the application, if exists
if(is_readable(CONFIG.'widgets.php'))
	Configure::load('widgets');

//Adds the widgets configuration to the MeCms configuration
Configure::write('MeCms.frontend.widgets', Configure::read('Widgets'));