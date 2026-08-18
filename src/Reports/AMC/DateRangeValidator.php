<?php

/**
 * Validates date range ordering for Automated Measure Calculation reports.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Reports\AMC;

use DateTimeImmutable;

final class DateRangeValidator
{
    private const TIMESTAMP_FORMAT = 'Y-m-d H:i:s';

    /**
     * Returns true only when both values are supported timestamps and begin is after end.
     *
     * Empty and malformed values are deliberately left to the report engine's existing
     * handling; this check is limited to rejecting inverted date ranges.
     */
    public static function isInverted(?string $begin, ?string $end): bool
    {
        $beginDate = self::parseTimestamp($begin);
        $endDate = self::parseTimestamp($end);

        return $beginDate !== null && $endDate !== null && $beginDate > $endDate;
    }

    private static function parseTimestamp(?string $value): ?DateTimeImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat('!' . self::TIMESTAMP_FORMAT, $value);
        $errors = DateTimeImmutable::getLastErrors();
        if ($date === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
            return null;
        }

        return $date->format(self::TIMESTAMP_FORMAT) === $value ? $date : null;
    }
}
