<?php

/**
 * The calendar day a stored DATE or DATETIME names, for the coverage reads.
 *
 * Every day returned is midnight in PHP's default time zone, as the rule's
 * dates are (EffectiveInsuranceRule), so the two compare as days. Which zone
 * OpenEMR's dates use is a suite-wide question; this keeps the coverage code
 * consistent with itself until it is settled.
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
use OpenEMR\Services\Utils\DateFormatterUtils;
use Psr\Clock\ClockInterface;

final class CoverageDay
{
    /**
     * A stored DATE or DATETIME as its calendar day, or null when it is blank,
     * zero or not a date. The time is dropped, so a visit at 10:00 asks about
     * its whole day. Takes what a row or a legacy screen holds: anything but a
     * string counts as blank, so callers need not narrow first.
     */
    public static function fromStored(mixed $value): ?DateTimeImmutable
    {
        $ymd = is_string($value) ? DateFormatterUtils::dbDateToYmd(substr($value, 0, 10)) : null;
        return $ymd === null ? null : new DateTimeImmutable($ymd);
    }

    /**
     * A lab order's bill-to day (the HL7 order's IN1, the requisition): the
     * specimen's collection date, else the order date. With neither readable
     * there is no day, and the caller decides, rather than billing the plan in
     * force today. A lab test's date of service is its collection date
     * (42 CFR 414.510(a)).
     */
    public static function forLabOrder(mixed $collected, mixed $ordered): ?DateTimeImmutable
    {
        return self::fromStored($collected) ?? self::fromStored($ordered);
    }

    /**
     * Today, for the screens and documents that show the coverage in force
     * now: the clock's calendar day, at midnight in PHP's default time zone.
     */
    public static function today(ClockInterface $clock): DateTimeImmutable
    {
        return new DateTimeImmutable($clock->now()->format('Y-m-d'));
    }
}
