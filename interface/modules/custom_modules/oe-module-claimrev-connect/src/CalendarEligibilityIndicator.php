<?php

/**
 * Calendar event listener that adds eligibility status indicators to
 * appointment blocks on the main OpenEMR calendar.
 *
 * When enabled via the CONFIG_ENABLE_CALENDAR_INDICATORS global setting,
 * this class listens for CalendarUserGetEventsFilter events and enriches
 * each appointment with a CSS class (eventViewClass) based on the patient's
 * primary insurance eligibility status.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ClaimRevConnector;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Events\Appointments\CalendarUserGetEventsFilter;
use OpenEMR\Events\Core\StyleFilterEvent;

class CalendarEligibilityIndicator
{
    public function __construct(private readonly int $staleAgeDays)
    {
    }

    public function filterCalendarEvents(CalendarUserGetEventsFilter $event): CalendarUserGetEventsFilter
    {
        $eventsByDay = $event->getEventsByDays();

        // Collect all unique PIDs from the calendar events
        $pids = [];
        foreach (array_keys($eventsByDay) as $key) {
            $events = $eventsByDay[$key];
            if (!is_array($events)) {
                continue;
            }
            foreach ($events as $calEvent) {
                if (!is_array($calEvent)) {
                    continue;
                }
                $pid = TypeCoerce::asInt($calEvent['pid'] ?? 0);
                if ($pid !== 0) {
                    $pids[$pid] = true;
                }
            }
        }

        if ($pids === []) {
            return $event;
        }

        // Batch-load eligibility status for all PIDs in one query
        $eligMap = $this->loadEligibilityMap(array_keys($pids));

        // Apply eventViewClass to each calendar event
        foreach ($eventsByDay as $key => $dayEvents) {
            if (!is_array($dayEvents)) {
                continue;
            }
            foreach ($dayEvents as $i => $calEvent) {
                if (!is_array($calEvent)) {
                    continue;
                }
                $pid = TypeCoerce::asInt($calEvent['pid'] ?? 0);
                if ($pid === 0) {
                    continue;
                }

                $eligClass = $this->determineEligClass($eligMap[$pid] ?? null);
                if ($eligClass === '') {
                    continue;
                }

                $existingClass = TypeCoerce::asString($calEvent['eventViewClass'] ?? '');
                $calEvent['eventViewClass'] = trim($existingClass . ' ' . $eligClass);
                $dayEvents[$i] = $calEvent;
            }
            $eventsByDay[$key] = $dayEvents;
        }

        $event->setEventsByDays($eventsByDay);
        return $event;
    }

    public function addCalendarStylesheet(StyleFilterEvent $event): void
    {
        if ($event->getPageName() === 'pnuserapi.php' || $event->getPageName() === 'pnadmin.php') {
            $styles = $event->getStyles();
            $styles[] = $this->getAssetPath() . 'css/calendar-eligibility.css';
            $event->setStyles($styles);
        }
    }

    /**
     * Load eligibility data for a batch of patient IDs.
     *
     * @param int[] $pids
     * @return array<int, array{status: ?string, individual_json: ?string, last_date: ?string}>
     */
    private function loadEligibilityMap(array $pids): array
    {
        if ($pids === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($pids), '?'));
        $sql = "SELECT pid, status, individual_json,
                       COALESCE(last_checked, create_date) as last_date
                FROM mod_claimrev_eligibility
                WHERE pid IN ({$placeholders})
                AND payer_responsibility = 'P'";

        $rows = QueryUtils::fetchRecords($sql, $pids);

        $map = [];
        foreach ($rows as $row) {
            $pid = TypeCoerce::asInt($row['pid'] ?? 0);
            $map[$pid] = [
                'status' => isset($row['status']) ? TypeCoerce::asString($row['status']) : null,
                'individual_json' => isset($row['individual_json']) ? TypeCoerce::asString($row['individual_json']) : null,
                'last_date' => isset($row['last_date']) ? TypeCoerce::asString($row['last_date']) : null,
            ];
        }

        return $map;
    }

    /**
     * Determine the CSS class for a patient's eligibility record.
     *
     * @param array{status: ?string, individual_json: ?string, last_date: ?string}|null $eligRecord
     */
    private function determineEligClass(?array $eligRecord): string
    {
        if ($eligRecord === null) {
            return 'event_elig_unchecked';
        }

        $status = strtolower($eligRecord['status'] ?? '');

        // Pending states
        if (in_array($status, ['waiting', 'creating'], true)) {
            return 'event_elig_pending';
        }

        // Error states
        if (in_array($status, ['error', 'senderror'], true)) {
            return 'event_elig_error';
        }

        // Check for staleness
        $lastDate = $eligRecord['last_date'] ?? '';
        if ($lastDate !== '') {
            $ts = strtotime($lastDate);
            if ($ts !== false) {
                $daysSinceCheck = (int) ((time() - $ts) / 86400);
                if ($daysSinceCheck >= $this->staleAgeDays) {
                    return 'event_elig_stale';
                }
            }
        }

        // Success — check coverage status
        if ($status === 'success') {
            $individualJson = $eligRecord['individual_json'] ?? null;
            if ($individualJson !== null) {
                $summaries = AppointmentsPage::getEligibilitySummary($individualJson);
                if ($summaries !== null && $summaries !== []) {
                    if ($summaries[0]->status === 'Active Coverage') {
                        return 'event_elig_active';
                    }
                    return 'event_elig_inactive';
                }
            }
            // Success but no parseable data
            return 'event_elig_active';
        }

        return '';
    }

    private function getAssetPath(): string
    {
        return '/interface/modules/custom_modules/oe-module-claimrev-connect/public/assets/';
    }
}
