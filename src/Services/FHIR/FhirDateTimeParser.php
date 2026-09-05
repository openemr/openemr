<?php

/**
 * FhirDateTimeParser
 *
 * Single parser for FHIR R4 `date`, `dateTime` and `instant` values arriving on
 * the FHIR write path.
 *
 * Every write service previously called `date_create_immutable()` directly on
 * client-supplied values, which is unsafe for three reasons:
 *
 *  1. FHIR R4 permits partial precision (`2024`, `2024-03`).  PHP parses the
 *     year-only form as a *clock time* -- `date_create_immutable('2024')`
 *     returns today's date at 20:24 -- so a legal FHIR onset of "2024" was
 *     silently stored as today.
 *  2. PHP's parser rolls impossible calendar dates forward: `2024-02-30`
 *     becomes `2024-03-01` instead of being rejected.
 *  3. A value carrying an offset (`2024-03-15T10:30:00+05:00`) was formatted
 *     with `->format('Y-m-d H:i:s')` while still in the *sender's* offset, so
 *     the wall-clock time written to the database did not agree with every
 *     other timestamp OpenEMR stores in server-local time.
 *
 * This class validates against the FHIR R4 regexes, rejects impossible dates,
 * normalizes to the server timezone, and -- importantly -- *throws* rather than
 * returning null on bad input.  `FhirServiceBase::insert()`/`update()` call
 * `parseFhirResource()` inside the try/catch in
 * `FhirGenericRestController::post()`/`put()`, so an
 * `\InvalidArgumentException` here surfaces to the client as a 400
 * OperationOutcome instead of a silently dropped element.
 *
 * @see https://build.fhir.org/datatypes.html#dateTime
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\FHIR;

final class FhirDateTimeParser
{
    public const PRECISION_YEAR = 'year';
    public const PRECISION_MONTH = 'month';
    public const PRECISION_DAY = 'day';
    public const PRECISION_TIME = 'time';

    public const DB_DATE_FORMAT = 'Y-m-d';
    public const DB_DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * MySQL DATE / DATETIME cannot represent years below 1000.  FHIR's own
     * regex permits 0001, so values below this are rejected rather than handed
     * to the database to mangle.
     */
    private const MIN_YEAR = 1000;

    /**
     * FHIR R4 date / dateTime / instant, with two intentional narrowings from
     * the published regex:
     *
     *  - day `00` is not accepted (the spec regex allows `0[0-9]`)
     *  - a timezone offset is required whenever a time is present, per the
     *    dateTime invariant ("If hours and minutes are specified, a timezone
     *    SHALL be populated")
     */
    private const REGEX = '/^(?<year>\d{4})'
        . '(?:-(?<month>0[1-9]|1[0-2])'
        . '(?:-(?<day>0[1-9]|[12]\d|3[01])'
        . '(?:T(?<hour>[01]\d|2[0-3]):(?<minute>[0-5]\d):(?<second>[0-5]\d|60)(?:\.\d+)?'
        . '(?<tz>Z|[+-](?:(?:0\d|1[0-3]):[0-5]\d|14:00)))?'
        . ')?)?$/';

    /**
     * Returns the precision of a FHIR date/dateTime value, or null when the
     * value is not a well-formed FHIR date/dateTime at all.
     */
    public static function precision(string $value): ?string
    {
        $parts = self::matchValue(trim($value));
        return $parts === null ? null : $parts['precision'];
    }

    /**
     * True when the value is a well-formed FHIR date/dateTime naming a real
     * calendar date within the range the database can store.
     */
    public static function isValid(string $value): bool
    {
        $parts = self::matchValue(trim($value));
        if ($parts === null) {
            return false;
        }
        if ((int) $parts['year'] < self::MIN_YEAR) {
            return false;
        }
        return self::isRealCalendarDate($parts);
    }

    /**
     * Parses a FHIR date/dateTime into an OpenEMR `Y-m-d` string.
     *
     * @param mixed  $value       The raw value taken from the FHIR JSON.
     * @param string $elementPath FHIR element path for diagnostics, e.g. "Condition.onsetDateTime".
     * @param bool   $allowPartial When true, `2024` and `2024-03` are widened to
     *                             the first day of the period.  Lossy, so it is
     *                             opt-in per element.
     * @return string|null Null only when the element is absent/empty.
     * @throws \InvalidArgumentException When the value is present but unusable.
     */
    public static function toDbDate(mixed $value, string $elementPath, bool $allowPartial = false): ?string
    {
        return self::toDateTimeImmutable($value, $elementPath, $allowPartial)?->format(self::DB_DATE_FORMAT);
    }

    /**
     * Parses a FHIR date/dateTime into an OpenEMR `Y-m-d H:i:s` string in the
     * server's timezone.
     *
     * @throws \InvalidArgumentException When the value is present but unusable.
     */
    public static function toDbDateTime(mixed $value, string $elementPath, bool $allowPartial = false): ?string
    {
        return self::toDateTimeImmutable($value, $elementPath, $allowPartial)?->format(self::DB_DATETIME_FORMAT);
    }

    /**
     * Parses a FHIR date/dateTime into a \DateTimeImmutable in the server's
     * timezone.  Values carrying an offset are converted, not reinterpreted.
     *
     * @throws \InvalidArgumentException When the value is present but unusable.
     */
    public static function toDateTimeImmutable(
        mixed $value,
        string $elementPath,
        bool $allowPartial = false
    ): ?\DateTimeImmutable {
        if ($value === null) {
            return null;
        }
        if (!is_string($value)) {
            throw new \InvalidArgumentException(
                $elementPath . ' must be a FHIR date/dateTime string, ' . get_debug_type($value) . ' given'
            );
        }
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $parts = self::matchValue($value);
        if ($parts === null) {
            throw new \InvalidArgumentException(
                $elementPath . ': "' . $value . '" is not a valid FHIR date/dateTime'
                . (str_contains($value, 'T')
                    ? ' (a timezone offset is required when a time is present)'
                    : '')
            );
        }

        if ((int) $parts['year'] < self::MIN_YEAR) {
            throw new \InvalidArgumentException(
                $elementPath . ': years before ' . self::MIN_YEAR . ' cannot be stored ("' . $value . '")'
            );
        }

        $precision = $parts['precision'];
        if (($precision === self::PRECISION_YEAR || $precision === self::PRECISION_MONTH) && !$allowPartial) {
            throw new \InvalidArgumentException(
                $elementPath . ': partial-precision value "' . $value . '" cannot be stored for this element'
                . ' -- supply at least a full date (YYYY-MM-DD)'
            );
        }

        if (!self::isRealCalendarDate($parts)) {
            throw new \InvalidArgumentException(
                $elementPath . ': "' . $value . '" is not a real calendar date'
            );
        }

        $normalized = match ($precision) {
            self::PRECISION_YEAR => $parts['year'] . '-01-01',
            self::PRECISION_MONTH => $parts['year'] . '-' . $parts['month'] . '-01',
            default => $value,
        };

        try {
            $parsed = new \DateTimeImmutable($normalized);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(
                $elementPath . ': "' . $value . '" could not be parsed as a date/dateTime',
                0,
                $e
            );
        }

        if ($precision === self::PRECISION_TIME) {
            // Convert the sender's offset to server-local wall clock so the
            // stored value is comparable with every other OpenEMR timestamp.
            $parsed = $parsed->setTimezone(new \DateTimeZone(date_default_timezone_get()));
        }

        return $parsed;
    }

    /**
     * @return array{year: string, month: string, day: string, precision: string}|null
     */
    private static function matchValue(string $value): ?array
    {
        if ($value === '' || preg_match(self::REGEX, $value, $matches) !== 1) {
            return null;
        }
        $month = $matches['month'] ?? '';
        $day = $matches['day'] ?? '';
        $hour = $matches['hour'] ?? '';

        if ($hour !== '') {
            $precision = self::PRECISION_TIME;
        } elseif ($day !== '') {
            $precision = self::PRECISION_DAY;
        } elseif ($month !== '') {
            $precision = self::PRECISION_MONTH;
        } else {
            $precision = self::PRECISION_YEAR;
        }

        return [
            'year' => $matches['year'],
            'month' => $month,
            'day' => $day,
            'precision' => $precision,
        ];
    }

    /**
     * @param array{year: string, month: string, day: string, precision: string} $parts
     */
    private static function isRealCalendarDate(array $parts): bool
    {
        if ($parts['day'] === '') {
            // Year and year-month precision cannot name an impossible date.
            return true;
        }
        return checkdate((int) $parts['month'], (int) $parts['day'], (int) $parts['year']);
    }
}
