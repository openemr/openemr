<?php

/**
 * The site's clock: "now" and "today" as every date rule reads them.
 *
 * The time-zone decision, made once (dates contract, open question TZ):
 * OpenEMR has one zone per install. interface/globals.php sets PHP's
 * default zone from the Time Zone setting (gbl_time_zone, else php.ini's
 * date.timezone) on every request, and sets the database session to the
 * same offset, so NOW() and PHP's clock agree. That is master's behaviour
 * and it is kept: the site's zone is PHP's default zone. There is no
 * facility or user zone. DATETIME columns hold the site's wall-clock time
 * with no zone.
 *
 * Options not taken here, each a behaviour change for the maintainers to
 * choose: a zone per facility (a new column), a zone per user (a new
 * setting), storing UTC (a data migration). FHIR's conversion to UTC
 * (UtilsService::getLocalDateAsUTC) uses the offset in force now, not on
 * the value's date; that stays as master has it too.
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
use Psr\Clock\ClockInterface;

final readonly class SiteClock implements ClockInterface
{
    /**
     * @param ?ClockInterface $clock a clock to read instead of the system's (tests freeze one); its
     *                               instants are read in the site's zone
     */
    public function __construct(private ?ClockInterface $clock = null)
    {
    }

    /**
     * The site's time zone: PHP's default zone, which globals.php sets from the Time Zone setting.
     */
    public static function zone(): DateTimeZone
    {
        return new DateTimeZone(date_default_timezone_get());
    }

    public function now(): DateTimeImmutable
    {
        $now = $this->clock?->now() ?? new DateTimeImmutable();
        return $now->setTimezone(self::zone());
    }

    /**
     * Today: the calendar day of now in the site's zone.
     */
    public function today(): Day
    {
        return Day::fromDateTime($this->now());
    }
}
