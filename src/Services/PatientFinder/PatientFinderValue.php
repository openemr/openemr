<?php

/**
 * PatientFinderValue
 *
 * Small internal helper for coercing the `mixed` values that come out of the
 * request array and untyped database rows into the scalars the Patient Finder
 * works with. Centralising the narrowing keeps the finder services free of
 * repeated `is_scalar()`/`is_numeric()` guards while staying strict-typing safe.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 Open Plan IT Ltd
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 * @internal
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientFinder;

final class PatientFinderValue
{
    /**
     * Coerce a mixed value to a string, treating non-scalars as the empty string.
     */
    public static function string(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }

    /**
     * Coerce a mixed value to an int, treating non-numeric values as zero.
     */
    public static function int(mixed $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
    }
}
