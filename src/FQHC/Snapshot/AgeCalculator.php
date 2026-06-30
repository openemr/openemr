<?php

/**
 * Pure age-in-years calculation from a stored date of birth.
 *
 * Takes the "as of" date as a parameter so it is deterministic and unit-testable
 * (no hidden clock). Returns null for missing or unparseable dates rather than
 * guessing — UDS age bands must not be fabricated from bad data.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Snapshot;

use DateTimeImmutable;

final class AgeCalculator
{
    /**
     * Whole years from `$dob` to `$asOf`. `$dob` may be `YYYY-MM-DD` or a
     * datetime; anything else (null, empty, the OpenEMR "0000-00-00" sentinel,
     * a future date) yields null.
     */
    public static function years(?string $dob, DateTimeImmutable $asOf): ?int
    {
        if ($dob === null) {
            return null;
        }

        $datePart = substr(trim($dob), 0, 10);
        if ($datePart === '' || str_starts_with($datePart, '0000')) {
            return null;
        }

        $birth = DateTimeImmutable::createFromFormat('!Y-m-d', $datePart);
        if ($birth === false) {
            return null;
        }

        if ($birth > $asOf) {
            return null;
        }

        return (int) $birth->diff($asOf)->format('%y');
    }
}
