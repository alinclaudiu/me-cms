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
 * @since       2.17.5
 */

namespace MeCms\Controller\Traits;

use Cake\I18n\Time;

/**
 * This trait provides a method to fets start and end date as `Time` instances
 *  starting from a string. These can be used for a `where` condition to search
 *  for records based on a date.
 */
trait GetStartAndEndDateTrait
{
    /**
     * Gets start and end date as `Time` instances starting from a string.
     * These can be used for a `where` condition to search for records based on
     *  a date.
     * @param string $date Date as `today`, `yesterday`, `YYYY/MM/dd`,
     *  `YYYY/MM` or `YYYY`
     * @return array Array with start and end date as `Time` instances
     */
    protected function getStartAndEndDate(string $date): array
    {
        $year = $month = $day = null;

        //Sets the start date
        if (in_array($date, ['today', 'yesterday'])) {
            $start = Time::parse($date);
        } else {
            [$year, $month, $day] = array_replace([null, null, null], explode('/', $date));
            $start = Time::now()->setDate((int)$year, (int)($month ?: 1), (int)($day ?: 1));
        }

        $start = $start->setTime(0, 0, 0);
        $end = Time::parse($start)->addYear(1);
        if (($year && $month && $day) || in_array($date, ['today', 'yesterday'])) {
            $end = Time::parse($start)->addDay(1);
        } elseif ($year && $month) {
            $end = Time::parse($start)->addMonth(1);
        }

        return [$start, $end];
    }
}
