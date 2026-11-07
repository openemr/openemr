<?php

/**
 * Whether a visit's balance is the patient's to pay, at checkout and on the
 * patient portal's payment page.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing;

/**
 * Compares the insurance levels a visit has closed (form_encounter.last_level_closed)
 * with the number of levels on the patient's file.
 */
final class VisitBalanceDue
{
    /**
     * The last_level_closed checkout records for a visit paid as cash, which
     * is not billed to insurance.
     */
    public const CASH_VISIT = 4;

    /**
     * True when no insurance is on file, the visit was paid as cash, or the
     * visit has closed at least as many levels as are on file.
     *
     * A closed level above the count happens when the levels on file have a
     * gap: a sole secondary closes level 2 while one level is on file.
     * Non-numeric values count as 0, as they compared before.
     *
     * @param mixed $levelsOnFile distinct insurance levels on file with a company
     * @param mixed $lastLevelClosed form_encounter.last_level_closed
     */
    public static function fromPatient(mixed $levelsOnFile, mixed $lastLevelClosed): bool
    {
        $levels = is_numeric($levelsOnFile) ? (int) $levelsOnFile : 0;
        $closed = is_numeric($lastLevelClosed) ? (int) $lastLevelClosed : 0;

        return $levels === 0 || $closed === self::CASH_VISIT || $closed >= $levels;
    }
}
