<?php

/**
 * PatientFinderAppointments
 *
 * The Next Appt and Last Appt values the Patient Finder shows: each patient's soonest
 * appointment after now and latest one before now, leaving out canceled ones. A repeating
 * appointment is stored as one row dated at its series' start, so its dates are worked out
 * the way the calendar works them out (fetchEvents() in appointments.inc.php), which also
 * leaves out a series' excluded dates. A series is expanded outward from today over its own
 * recurrence period, then twice and four times that and so on, until its nearest date
 * either side of now turns up or the series runs out, so a long period or a run of
 * excluded dates is still found without expanding the whole series.
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

    /**
     * How many times a series' search window doubles before giving up: 2^7 periods, enough
     * for a run of excluded dates, while a series with a broken spec stays bounded.
     */
    private const MAX_WINDOW_DOUBLINGS = 7;

    /** The calendar's every-N units (event_repeat_freq_type in pc_recurrspec). */
    private const REPEAT_EVERY_DAY = 0;
    private const REPEAT_EVERY_WEEK = 1;
    private const REPEAT_EVERY_MONTH = 2;
    private const REPEAT_EVERY_YEAR = 3;
    private const REPEAT_EVERY_WORK_DAY = 4;

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
     * A series' dates nearest now: from today (or its start) forward until one after now, and
     * from today (or its end) back until one before now, each search growing from one period.
     *
     * @param array<mixed> $row the series' event row
     * @return list<array<mixed>>
     */
    private function seriesOccurrences(array $row, DateTimeImmutable $now): array
    {
        $eid = PatientFinderValue::int($row['pc_eid'] ?? null);
        $start = PatientFinderValue::string($row['pc_eventDate'] ?? null);
        $endDate = PatientFinderValue::string($row['pc_endDate'] ?? null);
        $end = $endDate === '' || $endDate === '0000-00-00' ? null : $endDate;
        $today = $now->format('Y-m-d');
        [$count, $unit] = self::period($row);
        $found = [];

        // Forward: the first date after now.
        $from = max($start, $today);
        for ($k = 1; $k <= 1 << self::MAX_WINDOW_DOUBLINGS && ($end === null || $from <= $end); $k *= 2) {
            $to = self::shift($from, $count * $k, $unit, +1);
            $to = $end === null ? $to : min($to, $end);
            $dates = ($this->expandSeries)($from, $to, $eid);
            $after = array_filter($dates, static fn(array $d): bool => (self::startOf($d) ?? $now) > $now);
            if ($after !== []) {
                array_push($found, ...array_values($after));
                break;
            }
            if ($end !== null && $to >= $end) {
                break;
            }
        }

        // Back: the last date before now, from today or from the series' end.
        $anchor = $end === null ? $today : min($end, $today);
        for ($k = 1; $k <= 1 << self::MAX_WINDOW_DOUBLINGS && $start <= $anchor; $k *= 2) {
            $from = max($start, self::shift($anchor, $count * $k, $unit, -1));
            $dates = ($this->expandSeries)($from, $anchor, $eid);
            $before = array_filter($dates, static fn(array $d): bool => (self::startOf($d) ?? $now) < $now);
            if ($before !== []) {
                array_push($found, ...array_values($before));
                break;
            }
            if ($from === $start) {
                break;
            }
        }
        return $found;
    }

    /**
     * A series' recurrence period as a count and a date unit (D, M or Y), read from its
     * pc_recurrspec the way fetchEvents() reads it, plus slack for work days. A spec that
     * cannot be read counts as a day, so the doubling search still covers it.
     *
     * @param array<mixed> $row
     * @return array{int, string}
     */
    private static function period(array $row): array
    {
        $spec = @unserialize(PatientFinderValue::string($row['pc_recurrspec'] ?? null), ['allowed_classes' => false]);
        $spec = is_array($spec) ? $spec : [];
        if (PatientFinderValue::int($row['pc_recurrtype'] ?? null) === self::RECURR_TYPE_ON) {
            return [max(1, PatientFinderValue::int($spec['event_repeat_on_freq'] ?? null)), 'M'];
        }
        $freq = max(1, PatientFinderValue::int($spec['event_repeat_freq'] ?? null));
        return match (PatientFinderValue::int($spec['event_repeat_freq_type'] ?? null)) {
            self::REPEAT_EVERY_DAY => [$freq, 'D'],
            self::REPEAT_EVERY_WEEK => [$freq * 7, 'D'],
            self::REPEAT_EVERY_MONTH => [$freq, 'M'],
            self::REPEAT_EVERY_YEAR => [$freq, 'Y'],
            // N work days skip weekends: at most N + 2 days a week.
            self::REPEAT_EVERY_WORK_DAY => [$freq + 2 * intdiv($freq, 5) + 3, 'D'],
            // Days of the week (type 3) and anything else repeat within a week.
            default => [7, 'D'],
        };
    }

    /**
     * A Y-m-d date moved by a count of days, months or years, and a day further, forward
     * (+1) or back (-1).
     */
    private static function shift(string $date, int $count, string $unit, int $direction): string
    {
        $base = DateTimeImmutable::createFromFormat('!Y-m-d', $date) ?: new DateTimeImmutable('@0');
        $interval = new \DateInterval('P' . $count . $unit);
        $moved = $direction > 0 ? $base->add($interval) : $base->sub($interval);
        return ($direction > 0 ? $moved->modify('+1 day') : $moved->modify('-1 day'))->format('Y-m-d');
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
