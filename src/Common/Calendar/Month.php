<?php

/**
 * Month enum with translatable labels.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Calendar;

enum Month: int
{
    case January = 1;
    case February = 2;
    case March = 3;
    case April = 4;
    case May = 5;
    case June = 6;
    case July = 7;
    case August = 8;
    case September = 9;
    case October = 10;
    case November = 11;
    case December = 12;

    /**
     * Return the translated month name.
     *
     * Call xl() on each literal arm so translation-key extraction tools
     * (e.g. xgettext, grep) can find every key statically.
     */
    public function label(): string
    {
        return match ($this) {
            self::January => xl('January'),
            self::February => xl('February'),
            self::March => xl('March'),
            self::April => xl('April'),
            self::May => xl('May'),
            self::June => xl('June'),
            self::July => xl('July'),
            self::August => xl('August'),
            self::September => xl('September'),
            self::October => xl('October'),
            self::November => xl('November'),
            self::December => xl('December'),
        };
    }
}
