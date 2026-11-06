<?php

/**
 * Which insurance policy is in force for a patient on a day, at each level.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Insurance;

use DateTimeImmutable;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Utils\DateFormatterUtils;

/**
 * The rule:
 *
 * - A policy is in force on day D when its start is on or before D and its
 *   end is on or after D. Both ends are inclusive, as FHIR's Period, HL7 v2's
 *   IN1-12 and IN1-13, and QDM's payer start and stop define them.
 * - A blank start means always. A blank end, or 0000-00-00 (which
 *   sql_mode = '' stores for a mistyped date), means open.
 * - At each level, the policy in force with the latest start wins. A blank
 *   start loses to any stored one, 0000-00-00 included; between two blank
 *   starts the higher id (the later entry) wins.
 * - If that policy has no company ('', '0' or NULL), the level has no
 *   coverage on D. This is the "Unassigned" row staff add to end a level.
 * - Levels are independent: an ended primary doesn't hide a current
 *   secondary. They come back in their stored order; nothing here computes
 *   coordination of benefits.
 *
 * Dates are compared as stored, as SQL compares a DATE column. D is the
 * calendar day of the date given, in its own time zone; its time is ignored.
 * A partial date sql_mode = '' can store (2026-05-00, 2026-00-00, 2026-02-30)
 * is compared as stored too, and the policy's start and end report the days
 * that comparison keeps: an end of 2026-05-00 is in force through 2026-04-30,
 * a start of 2026-05-00 from 2026-05-01. Every date returned is midnight in
 * PHP's default time zone.
 *
 * This is the claim's rule (Claim::loadPayerInfo()), with three differences:
 *
 * - a 0000-00-00 end is open here, where the claim compares it as a day in
 *   year 0, so the policy has ended;
 * - between two blank starts the claim takes whichever row the database
 *   returns first;
 * - a company that isn't a whole number ('Acme') is no company here, where
 *   the claim bills it.
 *
 * getEffectiveInsurances() in library/patient.inc.php is a wrapper over the
 * service, so it follows this rule too; its docblock lists where it differs
 * from the query it replaced.
 */
final class EffectiveInsuranceRule
{
    private const ZERO_DATE = '0000-00-00';

    /**
     * @param list<array<mixed>> $rows one patient's insurance_data rows, each with id,
     *                                 uuid, type, provider, date and date_end
     * @return list<EffectiveInsurance> at most one per level, primary first
     */
    public static function choose(array $rows, DateTimeImmutable $day): array
    {
        $onDay = $day->format('Y-m-d');
        $winners = [];
        foreach ($rows as $row) {
            $type = $row['type'] ?? null;
            $level = is_string($type) ? InsuranceLevel::tryFrom($type) : null;
            $id = self::storedId($row['id'] ?? null);
            if ($level === null || $id === null) {
                continue;
            }
            // '' for a blank start, which sorts below every stored date and so is in force on any day.
            $start = self::storedDate($row['date'] ?? null);
            $end = self::storedDate($row['date_end'] ?? null);
            if ($end === self::ZERO_DATE) {
                $end = '';
            }
            if (strcmp($start, $onDay) > 0 || ($end !== '' && strcmp($end, $onDay) < 0)) {
                continue;
            }
            $held = $winners[$level->value] ?? null;
            if ($held !== null && !self::outranks($start, $id, $held['start'], $held['id'])) {
                continue;
            }
            $companyId = self::storedId($row['provider'] ?? null);
            $winners[$level->value] = [
                'start' => $start,
                'id' => $id,
                'policy' => $companyId === null ? null : new EffectiveInsurance(
                    $level,
                    $id,
                    self::uuidText($row['uuid'] ?? null),
                    $companyId,
                    self::firstDayOf($start),
                    self::lastDayOf($end),
                ),
            ];
        }

        $inForce = [];
        foreach (InsuranceLevel::cases() as $level) {
            $policy = $winners[$level->value]['policy'] ?? null;
            if ($policy !== null) {
                $inForce[] = $policy;
            }
        }
        return $inForce;
    }

    /**
     * The first calendar day on or after a stored date, as SQL compares them:
     * the date itself when it is a real day, else the next real one (a zero
     * day, 2026-05-00, gives 2026-05-01; a zero month, 2026-00-00, gives
     * 2026-01-01).
     */
    private static function firstDayFrom(string $stored): DateTimeImmutable
    {
        $day = self::calendarDay($stored);
        if ($day !== null) {
            return $day;
        }
        $year = substr($stored, 0, 4);
        $month = substr($stored, 5, 2);
        $dayOfMonth = substr($stored, 8, 2);
        if ($month === '00') {
            return new DateTimeImmutable("$year-01-01");
        }
        if (strcmp($month, '12') > 0) {
            return (new DateTimeImmutable("$year-12-31"))->modify('+1 day');
        }
        $firstOfMonth = new DateTimeImmutable("$year-$month-01");
        // A zero day comes before the 1st; any other day that isn't real is past the month's end.
        return $dayOfMonth === '00' ? $firstOfMonth : $firstOfMonth->modify('first day of next month');
    }

    /**
     * The first calendar day after a stored date, as SQL compares them.
     */
    private static function firstDayAfter(string $stored): DateTimeImmutable
    {
        $day = self::calendarDay($stored);
        return $day?->modify('+1 day') ?? self::firstDayFrom($stored);
    }

    /**
     * Whether a policy outranks the one held for its level: the later start,
     * then the higher id.
     */
    private static function outranks(string $start, int $id, string $heldStart, int $heldId): bool
    {
        $byStart = strcmp($start, $heldStart);
        return $byStart !== 0 ? $byStart > 0 : $id > $heldId;
    }

    /**
     * A DATE column's value as it is stored (YYYY-MM-DD, zeros included), or
     * '' when it is blank.
     */
    private static function storedDate(mixed $value): string
    {
        return is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1 ? $value : '';
    }

    /**
     * A stored date as a calendar day, or null when it is blank or isn't a
     * real day (0000-00-00, or 2026-05-00).
     */
    private static function calendarDay(string $stored): ?DateTimeImmutable
    {
        $ymd = DateFormatterUtils::dbDateToYmd($stored);
        return $ymd === null ? null : new DateTimeImmutable($ymd);
    }

    /**
     * A policy's first day in force: null for a blank or 0000-00-00 start
     * (always), else the first calendar day the comparison keeps.
     */
    private static function firstDayOf(string $start): ?DateTimeImmutable
    {
        return $start === '' || $start === self::ZERO_DATE ? null : self::firstDayFrom($start);
    }

    /**
     * A policy's last day in force: null for an open end ('', as choose()
     * reads a blank or 0000-00-00 end), else the day before the first day
     * after it.
     */
    private static function lastDayOf(string $end): ?DateTimeImmutable
    {
        return $end === '' ? null : self::firstDayAfter($end)->modify('-1 day');
    }

    /**
     * A stored id as a positive int: an int, as a read with bound values
     * returns it, or a string of digits (leading zeros and surrounding spaces
     * allowed, as MySQL reads them). Anything else, '' and '0' included, is
     * no id. The rule and the join on its answers (EffectiveInsuranceRow::
     * forPolicies()) read ids this one way.
     */
    public static function storedId(mixed $value): ?int
    {
        if (is_string($value) && ctype_digit(trim($value))) {
            $value = filter_var(ltrim(trim($value), '0'), FILTER_VALIDATE_INT);
        }
        return is_int($value) && $value > 0 ? $value : null;
    }

    /**
     * A stored binary uuid as text, or null when the row has none (anything but 16 bytes).
     */
    private static function uuidText(mixed $bytes): ?string
    {
        return is_string($bytes) && strlen($bytes) === 16 ? UuidRegistry::uuidToString($bytes) : null;
    }
}
