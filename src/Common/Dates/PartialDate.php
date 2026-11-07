<?php

/**
 * A date known only to some precision: "2015", "2015-05", a day, or a time.
 *
 * HL7 v2 TS and C-CDA's TS (effectiveTime/@value) send YYYY[MM[DD[HH[MM[SS[.S]]]]]]
 * with an optional +/-ZZZZ offset. No date column can hold "2015" today, so a
 * caller that must store one stores the period's first instant and loses the
 * precision (dates contract, open question P: a precision column beside the
 * date is a schema change for the maintainers). This class never invents a
 * day from the clock, which is what strtotime() does with "2015".
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Dates;

use DateTimeImmutable;
use DateTimeZone;

final readonly class PartialDate
{
    private function __construct(
        public int $year,
        public int $month,
        public int $day,
        public int $hour,
        public int $minute,
        public int $second,
        public Precision $precision,
        public ?string $offset,
    ) {
    }

    /**
     * Parse an HL7 TS. Anything that isn't one (a real date at its precision) is null.
     */
    public static function fromHl7(string $ts): ?self
    {
        $pattern = '/^(\d{4})(\d{2})?(\d{2})?(\d{2})?(\d{2})?(\d{2})?(?:\.\d{1,4})?([+-]\d{4})?$/';
        if (preg_match($pattern, trim($ts), $m) !== 1) {
            return null;
        }
        // preg_match leaves trailing unmatched groups out and gives a skipped one as ''; the pattern's nesting means a
        // later part is known only when every earlier one is
        $parts = array_pad(array_slice($m, 1, 7), 7, '');
        $known = 0;
        while ($known < 6 && $parts[$known] !== '') {
            $known++;
        }
        $precision = match ($known) {
            1 => Precision::Year,
            2 => Precision::Month,
            3 => Precision::Day,
            4 => Precision::Hour,
            5 => Precision::Minute,
            default => Precision::Second,
        };
        $year = (int)$parts[0];
        $month = $known >= 2 ? (int)$parts[1] : 1;
        $day = $known >= 3 ? (int)$parts[2] : 1;
        $hour = $known >= 4 ? (int)$parts[3] : 0;
        $minute = $known >= 5 ? (int)$parts[4] : 0;
        $second = $known >= 6 ? (int)$parts[5] : 0;
        if ($year < 1 || !checkdate($month, $day, $year) || $hour > 23 || $minute > 59 || $second > 59) {
            return null;
        }
        $offset = $parts[6] !== '' ? $parts[6] : null;
        return new self($year, $month, $day, $hour, $minute, $second, $precision, $offset);
    }

    /**
     * The first day the value covers: 2015 is 2015-01-01, 2015-05 is 2015-05-01.
     */
    public function firstDay(): Day
    {
        return Day::of($this->year, $this->month, $this->day);
    }

    /**
     * The first instant the value covers, as wall-clock time in a zone (the site's when none is given).
     * The value's own offset, if it has one, isn't applied: the caller decides whether to convert.
     */
    public function firstInstant(?DateTimeZone $zone = null): DateTimeImmutable
    {
        return $this->firstDay()->firstInstantIn($zone)->setTime($this->hour, $this->minute, $this->second);
    }

    /**
     * Everything the value covers: [first instant, first instant + one unit of its precision).
     */
    public function period(?DateTimeZone $zone = null): Period
    {
        $first = $this->firstInstant($zone);
        return Period::between($first, $first->modify($this->precision->step()));
    }
}
