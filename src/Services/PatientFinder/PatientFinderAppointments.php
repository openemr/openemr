<?php

/**
 * PatientFinderAppointments
 *
 * The Next Appt and Last Appt values the Patient Finder shows: each patient's soonest
 * appointment after now and latest one before now, leaving out canceled ones. A repeating
 * appointment is stored as one row dated at its series' start, so its dates are worked out
 * the way the calendar works them out (fetchEvents() in appointments.inc.php): only a series
 * that spans the window is expanded, and only over one recurrence period (a year at most)
 * either side of now, which holds its nearest date.
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

    /** How far from now a repeating series is expanded: its longest period, a year, and a day. */
    private const SERIES_WINDOW = 'P1Y1D';

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
            'SELECT pc_eid, pc_eventDate, pc_endDate, pc_startTime, pc_recurrtype FROM openemr_postcalendar_events'
            . ' WHERE pc_pid = ? AND pc_apptstatus NOT IN (' . $canceled . ')',
            array_merge([(string) $pid], self::CANCELED_STATUSES)
        );
        $window = new \DateInterval(self::SERIES_WINDOW);
        foreach ($rows as $row) {
            $eventDate = PatientFinderValue::string($row['pc_eventDate'] ?? null);
            if (PatientFinderValue::int($row['pc_recurrtype'] ?? null) > 0) {
                // A series: its next date after now falls within a period (a year at most)
                // of now, and its last date before now within a period of now or of its end.
                $endDate = PatientFinderValue::string($row['pc_endDate'] ?? null);
                $today = $now->format('Y-m-d');
                $horizon = $now->add($window)->format('Y-m-d');
                $hasEnd = $endDate !== '' && $endDate !== '0000-00-00';
                $to = $hasEnd ? min($endDate, $horizon) : $horizon;
                $lastAnchor = $hasEnd ? min($endDate, $today) : $today;
                $from = max(
                    $eventDate,
                    (new DateTimeImmutable($lastAnchor))->sub($window)->format('Y-m-d')
                );
                // A series that starts beyond the window has its first date as its next.
                $occurrences = $from > $to
                    ? [$row]
                    : ($this->expandSeries)($from, $to, PatientFinderValue::int($row['pc_eid'] ?? null));
            } else {
                $occurrences = [$row];
            }
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
