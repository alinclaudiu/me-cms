<?php
declare(strict_types=1);
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

use MeCms\Validation\BannerAndPhotoValidator;

/**
 * Photo validator class
 */
class PhotoValidator extends BannerAndPhotoValidator
{
    /**
     * Construct
     */
    public function __construct()
    {
        parent::__construct();

        $this->add('album_id', [
            'naturalNumber' => [
                'message' => I18N_SELECT_VALID_OPTION,
                'rule' => 'naturalNumber',
            ],
        ])->requirePresence('album_id', 'create');
    }
}
