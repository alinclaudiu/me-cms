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
namespace MeCms\Model\Validation;

use MeCms\Model\Validation\AppValidator;

class BannersPositionValidator extends AppValidator {
	/**
	 * Construct.
	 * 
	 * Adds some validation rules.
	 * @uses MeCms\Model\Validation\AppValidator::__construct()
	 */
    public function __construct() {
        parent::__construct();
		
		//Name
		$this->add('name', [
			'lengthBetween' => [
				'message'	=> __d('me_cms', 'Must be between {0} and {1} chars', 6, 100),
				'rule'		=> ['lengthBetween', 6, 100]
			],
			'slug' => [
				'message'	=> __d('me_cms', 'Allowed chars: lowercase letters, numbers, dash'),
				'rule'		=> [$this, 'slug']
			],
			'unique' => [
				'message'	=> __d('me_cms', 'This value is already used'),
				'provider'	=> 'table',
				'rule'		=> 'validateUnique'
			]
		])->requirePresence('name', 'create');
		
        return $this;
	}
}