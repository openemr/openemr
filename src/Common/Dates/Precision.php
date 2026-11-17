<?php

/**
 * How much of a date is known: a year, a month, a day, or a time to the
 * hour, minute or second (HL7 v2 TS and C-CDA's TS carry any of them).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Dates;

enum Precision: string
{
    case Year = 'year';
    case Month = 'month';
    case Day = 'day';
    case Hour = 'hour';
    case Minute = 'minute';
    case Second = 'second';

    /**
     * The interval one unit of this precision spans, for DateTimeImmutable::modify().
     */
    public function step(): string
    {
        return match ($this) {
            self::Year => '+1 year',
            self::Month => '+1 month',
            self::Day => '+1 day',
            self::Hour => '+1 hour',
            self::Minute => '+1 minute',
            self::Second => '+1 second',
        };
    }
}
