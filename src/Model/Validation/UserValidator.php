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
namespace MeCms\Model\Validation;

use Cake\Auth\DefaultPasswordHasher;
use Cake\ORM\TableRegistry;
use MeCms\Model\Validation\AppValidator;

/**
 * User validator class
 */
class UserValidator extends AppValidator
{
    /**
     * Construct.
     *
     * Adds some validation rules.
     * @uses Cake\Auth\DefaultPasswordHasher::check()
     * @uses Cake\ORM\TableRegistry::get()
     * @uses MeCms\Model\Validation\AppValidator::__construct()
     */
    public function __construct()
    {
        parent::__construct();

        //Users group
        $this->add('group_id', [
            'naturalNumber' => [
                'message' => __d('me_cms', 'You have to select a valid option'),
                'rule' => 'naturalNumber'
            ],
        ])->requirePresence('group_id', 'create');

        //Username
        $this->add('username', [
            'lengthBetween' => [
                'message' => __d('me_cms', 'Must be between {0} and {1} chars', 4, 40),
                'rule' => ['lengthBetween', 4, 40],
            ],
            'slug' => [
                'message' => sprintf(
                    '%s: %s',
                    __d('me_cms', 'Allowed chars'),
                    __d('me_cms', 'lowercase letters, numbers, dash')
                ),
                'rule' => [$this, 'slug'],
            ],
            'usernameNotReserved' => [
                'message' => __d('me_cms', 'This value contains a reserved word'),
                'rule' => function ($value) {
                    return (bool)!preg_match('/(admin|manager|root|supervisor|moderator)/i', $value);
                },
            ],
        ])->requirePresence('username', 'create');

        //Email
        $this->requirePresence('email', 'create');

        //Email repeat
        $this->add('email_repeat', [
            'compareWith' => [
                'message' => __d('me_cms', 'Email addresses don\'t match'),
                'rule' => ['compareWith', 'email'],
            ],
        ]);

        //Password
        $this->add('password', [
            'minLength' => [
                'last' => true,
                'message' => __d('me_cms', 'Must be at least {0} chars', 8),
                'rule' => ['minLength', 8],
            ],
            'passwordIsStrong' => [
                'message' => __d('me_cms', 'The password should contain letters, numbers and symbols'),
                'rule' => function ($value) {
                    return (bool)(
                        preg_match('/[A-z]+/', $value) &&
                        preg_match('/\d+/', $value) &&
                        preg_match('/[^A-z\d]+/', $value)
                    );
                },
            ],
        ])->requirePresence('password', 'create');

        //Password repeat
        $this->add('password_repeat', [
            'compareWith' => [
                'message' => __d('me_cms', 'Passwords don\'t match'),
                'rule' => ['compareWith', 'password'],
            ],
        ])->requirePresence('password_repeat', 'create');

        //Old password
        $this->add('password_old', [
            'oldPasswordIsRight' => [
                'message' => __d('me_cms', 'The old password is wrong'),
                'rule' => function ($value, $context) {
                    //Gets the old password
                    $user = TableRegistry::get('Users')
                        ->findById($context['data']['id'])
                        ->select(['password'])
                        ->firstOrFail();

                    //Checks if the password matches
                    return (new DefaultPasswordHasher)->check($value, $user->password);
                },
            ],
        ]);

        //First name
        $this->requirePresence('first_name', 'create');

        //Last name
        $this->requirePresence('last_name', 'create');

        //Banned
        $this->add('banned', [
            'boolean' => [
                'message' => __d('me_cms', 'You have to select a valid option'),
                'rule' => 'boolean',
            ],
        ])->allowEmpty('banned');
    }
}
