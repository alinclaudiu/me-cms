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
 * @author      Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright   Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link        http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Model\Validation;

use MeCms\Model\Validation\AppValidator;

/**
 * Post validator class
 */
class PostValidator extends AppValidator
{
    /**
     * Construct.
     *
     * Adds some validation rules.
     * @uses MeCms\Model\Validation\AppValidator::__construct()
     */
    public function __construct()
    {
        parent::__construct();

        //Category
        $this->add('category_id', [
            'naturalNumber' => [
                'message' => __d('me_cms', 'You have to select a valid option'),
                'rule' => 'naturalNumber',
            ],
        ])->requirePresence('category_id', 'create');

        //User (author)
        $this->requirePresence('user_id', 'create');

        //Title
        $this->requirePresence('title', 'create');

        //Slug
        $this->requirePresence('slug', 'create');

        //Text
        $this->requirePresence('text', 'create');

        //Tags
        $this->add('tags', [
            'validTagsLength' => [
                'last' => true,
                'message' => __d('me_cms', 'Each tag must be between {0} and {1} chars', 3, 30),
                'rule' => [$this, 'validTagsLength'],
            ],
            'validTagsChars' => [
                'message' => sprintf(
                    '%s: %s',
                    __d('me_cms', 'Allowed chars'),
                    __d('me_cms', 'lowercase letters, numbers, dash')
                ),
                'rule' => [$this, 'validTagsChars'],
            ],
        ])->allowEmpty('tags');
    }

    /**
     * Tags validation method (length).
     * For each tag, it checks if the tag has a valid lenght
     * @param string $value Field value
     * @param array $context Field context
     * @return bool
     */
    public function validTagsLength($value, $context)
    {
        foreach ($value as $tag) {
            //Checks if the tag has between 3 and 30 chars
            if (empty($tag['tag']) || strlen($tag['tag']) < 3 || strlen($tag['tag']) > 30) {
                return false;
            }
        }

        return true;
    }

    /**
     * Tags validation method (syntax).
     * For each tag, it checks if the tag has a valid syntax
     * @param string $value Field value
     * @param array $context Field context
     * @return bool
     */
    public function validTagsChars($value, $context)
    {
        foreach ($value as $tag) {
            //Checks if the tag has only lowercase letters, numbers, hyphen, space
            if (empty($tag['tag']) || !(bool)preg_match('/^[a-z0-9\ ]+$/', $tag['tag'])) {
                return false;
            }
        }

        return true;
    }
}
