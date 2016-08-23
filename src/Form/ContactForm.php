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
namespace MeCms\Form;

use Cake\Form\Form;
use Cake\Mailer\MailerAwareTrait;

/**
 * ContactForm class.
 *
 * It is used by `MeCms\Controller\SystemsController::contactForm()`.
 */
class ContactForm extends Form
{
    use MailerAwareTrait;

    /**
     * Defines the validator using the methods on Cake\Validation\Validator or
     * loads a pre-defined validator from a concrete class.
     * @param \Cake\Validation\Validator $validator Validator instance
     * @return \MeCms\Model\Validation\AppValidator
     */
    protected function _buildValidator(\Cake\Validation\Validator $validator)
    {
        $validator = new \MeCms\Model\Validation\AppValidator();

        //First name
        $validator->requirePresence('first_name');

        //Last name
        $validator->requirePresence('last_name');

        //Email
        $validator->requirePresence('email');

        //Message
        $validator->requirePresence('message')
            ->add('message', [
                'lengthBetween' => [
                    'message' => __d('me_cms', 'Must be between {0} and {1} chars', 10, 1000),
                    'rule' => ['lengthBetween', 10, 1000],
                ],
            ]);

        return $validator;
    }

    /**
     * Used by `execute()` to execute the form's action
     * @param array $data Form data
     * @return bool
     * @see MeCms\Mailer\ContactFormMailer::contactFormMail()
     */
    protected function _execute(array $data)
    {
        //Sends email
        return $this->getMailer('MeCms.ContactForm')
            ->send('contactFormMail', [$data]);
    }
}
