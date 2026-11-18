<?php

/**
 * PatientFinderAppointments
 *
 * The Next Appt and Last Appt values the Patient Finder shows: each patient's soonest
 * appointment after now and latest one before now, leaving out canceled ones. A repeating
 * appointment is stored as one row dated at its series' start, so its dates are worked out
 * the way the calendar works them out (fetchEvents() in appointments.inc.php), which also
 * leaves out a series' excluded dates. A series is searched outward from today in windows
 * that follow on from each other, the first one recurrence period long and each next one
 * twice as long, until its nearest date either side of now turns up. The search stops at
 * the series' start or end, or ten years (two periods for a longer one) from today, and
 * then shows no date for that side.
 *
 * A series whose recurrence the calendar cannot step through is left out rather than
 * expanded: the calendar's own stepping (__increment()) loops forever on an unknown
 * interval unit, such as the freq type 5 older code stored as an "every N" series.
 *
 * Database access, the series expansion and the clock are injected, so the lookups are
 * unit-testable without a database.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientFinder;

use DateTimeImmutable;
use DateTimeZone;
use Lcobucci\Clock\SystemClock;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;
use Psr\Clock\ClockInterface;

final class PatientFinderAppointments
{
    /**
     * Appointment statuses that are not an appointment the patient has or had:
     * "x" canceled and "%" canceled less than 24 hours ahead (apptstat list).
     */
    private const CANCELED_STATUSES = ['x', '%'];

    /** PHP date formats for the date_display_format global (0, 1, 2). */
    private const DATE_FORMATS = ['Y-m-d', 'm/d/Y', 'd/m/Y'];

    /** PHP time formats for the time_display_format global (0 = 24 hr, 1 = 12 hr). */
    private const TIME_FORMATS = ['H:i', 'g:i A'];

    /** How far from today a series is searched, unless two of its periods are longer. */
    private const SEARCH_REACH = 'P10Y';

    /** The calendar's every-N units (event_repeat_freq_type in pc_recurrspec). */
    private const REPEAT_EVERY_DAY = 0;
    private const REPEAT_EVERY_WEEK = 1;
    private const REPEAT_EVERY_MONTH = 2;
    private const REPEAT_EVERY_YEAR = 3;
    private const REPEAT_EVERY_WORK_DAY = 4;
    private const REPEAT_DAYS_EVERY_WEEK = 6;

    /** The calendar's "every N" recurrences (pc_recurrtype): an interval, or days of the week. */
    private const RECURR_TYPE_EVERY = 1;
    private const RECURR_TYPE_DAYS = 3;

    /** The calendar's "on the Nth weekday every N months" recurrence (pc_recurrtype). */
    private const RECURR_TYPE_ON = 2;

    /** @var callable(string, array<int, string|int>): list<array<mixed>> */
    private $fetchRecords;

    /** @var callable(string, string, int): list<array<mixed>> */
    private $expandSeries;

    private readonly ClockInterface $clock;

    private readonly string $displayFormat;

    /**
     * @param callable(string, array<int, string|int>): list<array<mixed>>|null $fetchRecords
     * @param callable(string, string, int): list<array<mixed>>|null $expandSeries a series' dates
     *        from one date to another (Y-m-d), given its pc_eid, as fetchEvents() returns them
     * @param int $dateDisplayFormat the date_display_format global (0 YYYY-MM-DD, 1 MM/DD/YYYY, 2 DD/MM/YYYY)
     * @param int $timeDisplayFormat the time_display_format global (0 24 hr, 1 12 hr)
     */
    public function __construct(
        ?callable $fetchRecords = null,
        ?callable $expandSeries = null,
        ?ClockInterface $clock = null,
        int $dateDisplayFormat = 0,
        int $timeDisplayFormat = 0
    ) {
        $this->fetchRecords = $fetchRecords ?? QueryUtils::fetchRecords(...);
        $this->expandSeries = $expandSeries ?? self::fetchSeriesEvents(...);
        $this->clock = $clock ?? new SystemClock(new DateTimeZone(date_default_timezone_get()));
        $this->displayFormat = (self::DATE_FORMATS[$dateDisplayFormat] ?? self::DATE_FORMATS[0])
            . ' ' . (self::TIME_FORMATS[$timeDisplayFormat] ?? self::TIME_FORMATS[0]);
    }

    /**
     * The lookups, showing times in the site's date and time display formats.
     */
    public static function fromGlobals(OEGlobalsBag $globals): self
    {
        return new self(
            dateDisplayFormat: $globals->getInt('date_display_format'),
            timeDisplayFormat: $globals->getInt('time_display_format')
        );
    }

    /**
     * Each patient's Next and Last Appt values, keyed by pid then by column key, in the
     * site's display format. A patient with no such appointment has no entry for it.
     *
     * @param list<int> $pids
     * @param list<PatientFinderBuiltInColumn> $columns the appointment columns wanted
     * @return array<int, array<string, string>>
     */
    public function values(array $pids, array $columns): array
    {
        $wantNext = in_array(PatientFinderBuiltInColumn::NextAppointment, $columns, true);
        $wantLast = in_array(PatientFinderBuiltInColumn::LastAppointment, $columns, true);
        $values = [];
        if (!$wantNext && !$wantLast) {
            return $values;
        }
        $now = $this->clock->now();
        foreach (array_unique(array_filter($pids, static fn(int $pid): bool => $pid > 0)) as $pid) {
            $times = $this->appointmentTimes($pid, $now);
            if ($wantNext && $times['next'] !== null) {
                $values[$pid][PatientFinderBuiltInColumn::NextAppointment->value] = $times['next']->format($this->displayFormat);
            }
            if ($wantLast && $times['last'] !== null) {
                $values[$pid][PatientFinderBuiltInColumn::LastAppointment->value] = $times['last']->format($this->displayFormat);
            }
        }
        return $values;
    }

    /**
     * One patient's soonest appointment after now and latest before now.
     *
     * @return array{next: ?DateTimeImmutable, last: ?DateTimeImmutable}
     */
    private function appointmentTimes(int $pid, DateTimeImmutable $now): array
    {
        $next = null;
        $last = null;
        $canceled = implode(', ', array_fill(0, count(self::CANCELED_STATUSES), '?'));
        $rows = ($this->fetchRecords)(
            'SELECT pc_eid, pc_eventDate, pc_endDate, pc_startTime, pc_recurrtype, pc_recurrspec'
            . ' FROM openemr_postcalendar_events'
            . ' WHERE pc_pid = ? AND pc_apptstatus NOT IN (' . $canceled . ')',
            array_merge([(string) $pid], self::CANCELED_STATUSES)
        );
        foreach ($rows as $row) {
            $occurrences = PatientFinderValue::int($row['pc_recurrtype'] ?? null) > 0
                ? $this->seriesOccurrences($row, $now)
                : [$row];
            foreach ($occurrences as $occurrence) {
                $at = self::startOf($occurrence);
                if ($at === null) {
                    continue;
                }
                if ($at > $now && ($next === null || $at < $next)) {
                    $next = $at;
                } elseif ($at < $now && ($last === null || $at > $last)) {
                    $last = $at;
                }
            }
        }
        return ['next' => $next, 'last' => $last];
    }

    /**
     * A series' dates nearest now: forward from today (or its start) until one after now, and
     * back from today (or its end) until one before now. Each search moves through windows
     * that follow on from each other, each twice as long as the one before, up to the
     * series' start or end or the search reach, whichever is nearer.
     *
     * @param array<mixed> $row the series' event row
     * @return list<array<mixed>>
     */
    private function seriesOccurrences(array $row, DateTimeImmutable $now): array
    {
        $period = self::period($row);
        if ($period === null) {
            // The calendar cannot step through this recurrence; expanding it would hang.
            return [];
        }
        [$count, $unit] = $period;
        $eid = PatientFinderValue::int($row['pc_eid'] ?? null);
        $start = PatientFinderValue::string($row['pc_eventDate'] ?? null);
        $endDate = PatientFinderValue::string($row['pc_endDate'] ?? null);
        $end = $endDate === '' || $endDate === '0000-00-00' ? null : $endDate;
        $today = $now->format('Y-m-d');
        $found = [];

        // Forward: the first date after now.
        $latest = max(self::moveBy($today, self::SEARCH_REACH, 1), self::shift($today, 2 * $count, $unit, 1));
        $latest = $end === null ? $latest : min($latest, $end);
        $from = max($start, $today);
        for ($periods = 1; $from <= $latest; $periods *= 2) {
            $to = min(self::shift($from, $count * $periods, $unit, 1), $latest);
            $after = array_filter(
                ($this->expandSeries)($from, $to, $eid),
                static fn(array $d): bool => (self::startOf($d) ?? $now) > $now
            );
            if ($after !== []) {
                array_push($found, ...array_values($after));
                break;
            }
            $from = self::moveBy($to, 'P1D', 1);
        }

        // Back: the last date before now, from today or from the series' end.
        $to = $end === null ? $today : min($end, $today);
        $earliest = min(self::moveBy($to, self::SEARCH_REACH, -1), self::shift($to, 2 * $count, $unit, -1));
        $earliest = max($earliest, $start);
        for ($periods = 1; $to >= $earliest; $periods *= 2) {
            $from = max(self::shift($to, $count * $periods, $unit, -1), $earliest);
            $before = array_filter(
                ($this->expandSeries)($from, $to, $eid),
                static fn(array $d): bool => (self::startOf($d) ?? $now) < $now
            );
            if ($before !== []) {
                array_push($found, ...array_values($before));
                break;
            }
            $to = self::moveBy($from, 'P1D', -1);
        }
        return $found;
    }

    /**
     * A series' recurrence period as a count and a date unit (D, M or Y), read from its
     * pc_recurrspec the way fetchEvents() reads it, plus slack for work days; or null when
     * the calendar cannot step through it (an unknown recurrence type or interval unit, or
     * no interval), since its stepping would then never move on.
     *
     * @param array<mixed> $row
     * @return array{int, string}|null
     */
    private static function period(array $row): ?array
    {
        $spec = @unserialize(PatientFinderValue::string($row['pc_recurrspec'] ?? null), ['allowed_classes' => false]);
        $spec = is_array($spec) ? $spec : [];
        $type = PatientFinderValue::int($row['pc_recurrtype'] ?? null);
        if ($type === self::RECURR_TYPE_ON) {
            $months = PatientFinderValue::int($spec['event_repeat_on_freq'] ?? null);
            return $months > 0 ? [$months, 'M'] : null;
        }
        if ($type !== self::RECURR_TYPE_EVERY && $type !== self::RECURR_TYPE_DAYS) {
            return null;
        }
        $unit = $spec['event_repeat_freq_type'] ?? null;
        if (!is_numeric($unit)) {
            return null;
        }
        if ((int) $unit === self::REPEAT_DAYS_EVERY_WEEK) {
            // The days of the week it repeats on, 1 (Sunday) to 7, as "2,4"; it repeats within a week.
            $days = PatientFinderValue::string($spec['event_repeat_freq'] ?? null);
            return preg_match('/^[1-7](,[1-7])*$/', $days) === 1 ? [7, 'D'] : null;
        }
        $freq = PatientFinderValue::int($spec['event_repeat_freq'] ?? null);
        if ($freq <= 0) {
            return null;
        }
        return match ((int) $unit) {
            self::REPEAT_EVERY_DAY => [$freq, 'D'],
            self::REPEAT_EVERY_WEEK => [$freq * 7, 'D'],
            self::REPEAT_EVERY_MONTH => [$freq, 'M'],
            self::REPEAT_EVERY_YEAR => [$freq, 'Y'],
            // N work days skip weekends: at most N + 2 days a week.
            self::REPEAT_EVERY_WORK_DAY => [$freq + 2 * intdiv($freq, 5) + 3, 'D'],
            default => null,
        };
    }

    /**
     * A Y-m-d date moved by a count of days, months or years, and a day further, forward
     * (+1) or back (-1).
     */
    private static function shift(string $date, int $count, string $unit, int $direction): string
    {
        return self::moveBy(self::moveBy($date, 'P' . $count . $unit, $direction), 'P1D', $direction);
    }

    /**
     * A Y-m-d date moved by an ISO 8601 duration, forward (+1) or back (-1).
     */
    private static function moveBy(string $date, string $duration, int $direction): string
    {
        $base = DateTimeImmutable::createFromFormat('!Y-m-d', $date) ?: new DateTimeImmutable('@0');
        $interval = new \DateInterval($duration);
        return ($direction > 0 ? $base->add($interval) : $base->sub($interval))->format('Y-m-d');
    }

    /**
     * When an appointment row starts, or null for a row without a usable date.
     *
     * @param array<mixed> $row
     */
    private static function startOf(array $row): ?DateTimeImmutable
    {
        $date = PatientFinderValue::string($row['pc_eventDate'] ?? null);
        $time = PatientFinderValue::string($row['pc_startTime'] ?? null);
        $at = DateTimeImmutable::createFromFormat(
            '!Y-m-d H:i:s',
            $date . ' ' . ($time === '' ? '00:00:00' : $time),
            new DateTimeZone(date_default_timezone_get())
        );
        return $at === false || $date === '0000-00-00' ? null : $at;
    }

    /**
     * One series' dates between two dates, worked out by the calendar's own fetchEvents().
     *
     * @return list<array<mixed>>
     */
    private static function fetchSeriesEvents(string $from, string $to, int $eid): array
    {
        require_once OEGlobalsBag::getInstance()->getSrcDir() . '/appointments.inc.php';
        return array_values(array_filter(
            fetchEvents($from, $to, ' AND e.pc_eid = ?', null, false, 0, [$eid]),
            is_array(...)
        ));
    }
}
