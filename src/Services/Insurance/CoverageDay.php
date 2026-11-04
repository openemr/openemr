<?php

/**
 * The day each use asks the coverage service about, written once per use so
 * callers don't each decide which date counts.
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
use Psr\Clock\ClockInterface;

final readonly class CoverageDay
{
    public function __construct(private ClockInterface $clock)
    {
    }

    /**
     * A stored DATE or DATETIME as its calendar day, or null when it is blank,
     * zero or not a date. The time is dropped, so a visit at 10:00 asks about
     * its whole day.
     */
    public static function fromStored(?string $value): ?DateTimeImmutable
    {
        if ($value === null || strlen($value) < 10) {
            return null;
        }
        $text = substr($value, 0, 10);
        $day = DateTimeImmutable::createFromFormat('!Y-m-d', $text);
        return $day !== false && $day->format('Y-m-d') === $text ? $day : null;
    }

    /**
     * A lab order's bill-to (HL7 IN1, the requisition): the specimen's
     * collection date, else the order date, else today. A lab test's date of
     * service is its collection date (42 CFR 414.510(a)).
     */
    public function forLabOrder(?string $collected, ?string $ordered): DateTimeImmutable
    {
        return self::fromStored($collected) ?? self::fromStored($ordered) ?? $this->today();
    }

    /**
     * A visit, a charge or a claim: the encounter's date, else today.
     */
    public function forVisit(?string $encounterDate): DateTimeImmutable
    {
        return self::fromStored($encounterDate) ?? $this->today();
    }

    /**
     * Screens and documents that show current coverage: today, the clock's
     * calendar day in its own time zone.
     */
    public function today(): DateTimeImmutable
    {
        return $this->clock->now()->setTime(0, 0);
    }
}
