<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\PostCalendar\ViewModel;

/**
 * Builds the data structure that PostCalendar Twig templates iterate.
 *
 * The six legacy Smarty templates (day/week/month ajax_template plus
 * the three print variants) carry hundreds of lines of [-php-] blocks
 * that compute per-event display data and emit HTML inline. This
 * service pulls that logic out so the new Twig templates only iterate
 * pre-decorated data — no inline PHP, no DB lookups during render.
 *
 * Construction takes the same raw inputs the legacy consumers
 * (pnuserapi/pnuser/pnadmin) already build: the events array,
 * providers list, times list, etc. Side-effect-free; safe to
 * instantiate and call from tests without database access (when
 * given pre-built inputs).
 *
 * The full per-view decoration pipeline (evtClass derivation, content
 * HTML build, geometry math, IN-event lookahead, DB-backed facility
 * lookups) lands in subsequent commits — this initial commit covers
 * just the constructor + the day-of-week list, which is the same
 * across all six views.
 */
final readonly class CalendarViewModel
{
    /**
     * @param  ViewType $viewType
     * @param  int      $firstDayOfWeek  0=Sunday..6=Saturday. Out-of-range values are silently clamped to 0 (matches legacy `pnModSetVar` self-heal).
     */
    public function __construct(
        public ViewType $viewType,
        private int $firstDayOfWeek,
    ) {
    }

    /**
     * Seven weekdays in display order starting at firstDayOfWeek.
     *
     * Mirrors the legacy [-php-] block:
     *
     *     while (count($DOWlist) < 7) {
     *         array_push($DOWlist, $tmpDOW);
     *         $tmpDOW++;
     *         if ($tmpDOW > 6) $tmpDOW = 0;
     *     }
     *
     * Out-of-range firstDayOfWeek wraps to 0, matching the legacy
     * auto-correction (which also persisted the corrected value via
     * pnModSetVar; the view-model is read-only and does not persist).
     *
     * @return list<int>  exactly 7 elements, each in [0, 6]
     */
    public function dayOfWeekList(): array
    {
        $start = ($this->firstDayOfWeek < 0 || $this->firstDayOfWeek > 6)
            ? 0
            : $this->firstDayOfWeek;

        $list = [];
        $current = $start;
        while (count($list) < 7) {
            $list[] = $current;
            $current++;
            if ($current > 6) {
                $current = 0;
            }
        }

        return $list;
    }

    /**
     * CSS class for an event based on its category ID and the current view.
     *
     * Mirrors the catid switch that appears (with minor view-specific
     * variations) in all six legacy templates. The view-specific quirks
     * collapsed in here are:
     *
     *  - Week view treats catid 99 (a legacy "holiday" alias) as
     *    `event_holiday`; other views render it as a plain appointment.
     *  - Month view appends `hiddenevent` to the catid 6 class so the
     *    JS `EditEvent` handler short-circuits on it (matches the
     *    legacy [-php-] block in month/ajax_template.html).
     *
     * Callers that need the upstream event-dispatcher override should
     * still consult `$event['eventViewClass']` separately and prefer
     * that value over this method's return — the legacy code does the
     * same. Keeping the override out of this method preserves the
     * pure-transformation contract.
     */
    public function eventClassForCategory(int $categoryId): string
    {
        $base = match ($categoryId) {
            1       => 'event_noshow',
            2       => 'event_in',
            3       => 'event_out',
            4, 8, 11 => 'event_reserved',
            6       => 'event_holiday',
            99      => $this->viewType === ViewType::Week ? 'event_holiday' : 'event_appointment',
            default => 'event_appointment',
        };

        if ($categoryId === 6 && $this->viewType === ViewType::Month) {
            return 'event_holiday hiddenevent';
        }

        return $base;
    }

    /**
     * Returns the localized category name for the special-category IDs
     * (IN/OUT/VACATION/LUNCH/RESERVED), or the raw catname unchanged
     * for everything else.
     *
     * Mirrors the inline conditional inside every legacy template's
     * per-event content build:
     *
     *     if ($catid ==  2) $catname = xl("IN");
     *     else if ($catid ==  3) $catname = xl("OUT");
     *     else if ($catid ==  4) $catname = xl("VACATION");
     *     ...
     */
    public function translatedCategoryName(int $categoryId, string $rawCategoryName): string
    {
        return match ($categoryId) {
            2       => \xl('IN'),
            3       => \xl('OUT'),
            4       => \xl('VACATION'),
            8       => \xl('LUNCH'),
            11      => \xl('RESERVED'),
            default => $rawCategoryName,
        };
    }

    /**
     * Splits a "Lastname, Firstname" patient_name string into its parts.
     *
     * Mirrors the legacy idiom that preg_splits on a comma with optional
     * trailing whitespace, then pads the result with two empty strings so
     * destructuring always assigns both lname and fname.
     *
     * Either half is an empty string when missing (no comma → fname empty;
     * null input → both empty).
     *
     * @return array{lname: string, fname: string}
     */
    public function parsePatientName(?string $patientName): array
    {
        if ($patientName === null || $patientName === '') {
            return ['lname' => '', 'fname' => ''];
        }

        $parts = preg_split('/,\s*/', $patientName, 2);
        if ($parts === false) {
            return ['lname' => $patientName, 'fname' => ''];
        }

        return [
            'lname' => $parts[0],
            'fname' => $parts[1] ?? '',
        ];
    }

    /**
     * Builds the data for a small month-grid calendar of the given date's
     * month. The structure intentionally exposes raw flags (inMonth,
     * isWeekend, isCurrent) rather than baked-in CSS class strings, so
     * the same builder serves both the print PrintDatePicker (which
     * just skips out-of-month days) and the screen mini-calendars
     * (which color out-of-month, weekend, and holiday cells).
     *
     * Replaces the inline PrintDatePicker helper-function definitions
     * inside day_print/week_print/month_print templates, plus the very
     * similar mini-calendar block in day/week/month on-screen templates.
     *
     * @param  string  $monthAnchor   Any parseable date string within the target month (e.g. "2026-03-15", "20260315")
     * @param  ?string $currentDate   YYYYMMDD of the date to mark as `isCurrent`, or null for none
     * @return array{monthLabel: string, year: int, month: int, weeks: list<list<array{dateYmd: string, day: int, dayOfWeek: int, inMonth: bool, isWeekend: bool, isCurrent: bool}>>}
     */
    public function buildMiniCalendar(string $monthAnchor, ?string $currentDate = null): array
    {
        $anchor = strtotime($monthAnchor);
        if ($anchor === false) {
            throw new \InvalidArgumentException("Cannot parse month anchor: {$monthAnchor}");
        }

        $year = (int) date('Y', $anchor);
        $month = (int) date('n', $anchor);
        $dowList = $this->dayOfWeekList();

        // Pad backwards from the 1st until the row starts on dowList[0].
        $startDate = mktime(0, 0, 0, $month, 1, $year);
        // mktime never fails for valid arithmetic, but PHPStan can't prove that.
        if ($startDate === false) {
            throw new \RuntimeException('mktime failed for month start');
        }
        while ((int) date('w', $startDate) !== $dowList[0]) {
            $startDate -= 86400;
        }

        // Pad forwards from the last day of month until the row ends on dowList[6].
        $lastDayOfMonth = (int) date('t', $anchor);
        $endDate = mktime(0, 0, 0, $month, $lastDayOfMonth, $year);
        if ($endDate === false) {
            throw new \RuntimeException('mktime failed for month end');
        }
        while ((int) date('w', $endDate) !== $dowList[6]) {
            $endDate += 86400;
        }

        $weeks = [];
        $currentWeek = [];
        $cursor = $startDate;
        while ($cursor <= $endDate) {
            $cursorYmd = date('Ymd', $cursor);
            $cursorDow = (int) date('w', $cursor);
            $currentWeek[] = [
                'dateYmd'   => $cursorYmd,
                'day'       => (int) date('d', $cursor),
                'dayOfWeek' => $cursorDow,
                'inMonth'   => (int) date('m', $cursor) === $month,
                'isWeekend' => $cursorDow === 0 || $cursorDow === 6,
                'isCurrent' => $currentDate !== null && $cursorYmd === $currentDate,
            ];

            if ($cursorDow === $dowList[6]) {
                $weeks[] = $currentWeek;
                $currentWeek = [];
            }

            // The legacy code added `+1000` seconds per iteration alongside
            // 86400 ("for some unknown reason" per the original author,
            // suspected DST guard). It is not a DST guard — it just drifts
            // forward 16 min per day and over a 35-day grid accumulates
            // past endDate by ~9.5 hours, silently dropping the last
            // partial week (months ending Mon/Tue/Wed lose their last
            // Sat/Sun row). Removed here; the snapshot diff will tell us
            // if any rendering depended on the truncation.
            $cursor += 86400;
        }

        return [
            'monthLabel' => date('F Y', $anchor),
            'year'       => $year,
            'month'      => $month,
            'weeks'      => $weeks,
        ];
    }

    /**
     * Returns a new event array with startTime / duration overridden to
     * span the full clinic window when the event is marked all-day.
     * Non-all-day events pass through unchanged.
     *
     * Mirrors the legacy normalization that runs before the geometry
     * math:
     *     if ($event['alldayevent'] == 1) {
     *         $tmpTime = $times[0];
     *         ...
     *         $event['startTime'] = "$hour:$minute:00";
     *         $event['duration'] = ($calEndMin - $calStartMin) * 60;
     *     }
     *
     * Padding the hour/minute strings to 2 digits matches the legacy
     * defensive `if (strlen(...)<2) { ... = "0".... }` lines.
     *
     * @param  array<string, mixed> $event
     * @param  array{hour: int|string, minute: int|string} $firstTimeSlot
     * @return array<string, mixed>
     */
    public function normalizeAllDayEvent(
        array $event,
        array $firstTimeSlot,
        int $clinicStartMin,
        int $clinicEndMin
    ): array {
        // Legacy stored `alldayevent` as a string "1" / "0" out of MySQL;
        // narrowing via == 1 matches both "1" and int 1 without the loose
        // (int) cast PHPStan rejects on mixed.
        $allDay = $event['alldayevent'] ?? 0;
        if ($allDay !== 1 && $allDay !== '1') {
            return $event;
        }

        $hour   = str_pad((string) $firstTimeSlot['hour'], 2, '0', STR_PAD_LEFT);
        $minute = str_pad((string) $firstTimeSlot['minute'], 2, '0', STR_PAD_LEFT);

        $event['startTime'] = "{$hour}:{$minute}:00";
        $event['duration']  = ($clinicEndMin - $clinicStartMin) * 60;

        return $event;
    }

    /**
     * Parses "HH:MM:SS" or "HH:MM" into a minute-of-day integer.
     *
     * Mirrors the legacy:
     *     $starth = substr($event['startTime'], 0, 2);
     *     $startm = substr($event['startTime'], 3, 2);
     *     $eStartMin = $starth * 60 + $startm;
     *
     * Out-of-format input (no colon, non-numeric parts) returns 0
     * — matches the legacy `(int) substr(...)` coercion behavior.
     */
    public function startTimeToMinutes(string $startTime): int
    {
        $parts = explode(':', $startTime);
        $hour = (int) $parts[0];
        $minute = isset($parts[1]) ? (int) $parts[1] : 0;

        return $hour * 60 + $minute;
    }

    /**
     * Computes positioned-DIV geometry for one event inside the
     * times column. Used by day, week, day_print, week_print.
     *
     * Mirrors the legacy:
     *     $eMinDiff       = $eStartMin - $calStartMin;
     *     $eStartInterval = floor($eMinDiff / $interval);
     *     $evtTop         = $eStartInterval * $timeslotHeightVal . $timeslotHeightUnit;
     *
     *     $eEndMin       = $eStartMin + ($duration / 60);
     *     $eEndInterval  = ceil(($eEndMin - $calStartMin) / $interval);
     *     $evtHeight     = ($eEndInterval - $eStartInterval) * $timeslotHeightVal . $timeslotHeightUnit;
     *
     * `top` and `height` come back as CSS strings with the unit suffix
     * baked in (matches what the legacy templates wrote into `style=""`
     * attributes). `startInterval` / `endInterval` are surfaced too
     * because the overlap detector needs them.
     *
     * @return array{startInterval: int, endInterval: int, top: string, height: string}
     */
    public function computeEventGeometry(
        int $startMinFromMidnight,
        int $durationMinutes,
        int $clinicStartMin,
        int $intervalMinutes,
        int $timeslotHeightVal,
        string $timeslotHeightUnit
    ): array {
        $startInterval = (int) floor(($startMinFromMidnight - $clinicStartMin) / $intervalMinutes);

        $endMin = $startMinFromMidnight + $durationMinutes;
        $endInterval = (int) ceil(($endMin - $clinicStartMin) / $intervalMinutes);

        return [
            'startInterval' => $startInterval,
            'endInterval'   => $endInterval,
            'top'           => ($startInterval * $timeslotHeightVal) . $timeslotHeightUnit,
            'height'        => (($endInterval - $startInterval) * $timeslotHeightVal) . $timeslotHeightUnit,
        ];
    }

    /**
     * Returns true when a category should be skipped during overlap
     * detection. View-specific:
     *
     *  - Day view skips only IN (catid 2). OUT events stay visible and
     *    get re-ordered to the front of each slot by the unshift rule
     *    below.
     *  - Week view and day_print skip both IN and OUT.
     *
     * Month, month_print, week_print don't call the overlap detector
     * at all (they either render in flow layout or rely on the legacy
     * latent dead-code reference to an empty eventPositions array).
     * For those views this predicate is consulted only if a caller
     * explicitly invokes detectEventOverlap, which they shouldn't.
     */
    private function shouldSkipForOverlap(int $categoryId): bool
    {
        if ($categoryId === 2) {
            return true;
        }

        return $categoryId === 3 && $this->viewType !== ViewType::Day;
    }

    /**
     * Detects overlapping events on one provider's date column and
     * returns width/leftpos for each event ID. Implements the per-slot
     * scan from day/week/day_print main loops.
     *
     * Algorithm (preserves legacy semantics):
     *  - For each timeslot, build the list of events overlapping it
     *    (start-in, end-in, or span). Skip events flagged by
     *    shouldSkipForOverlap. Skip events with empty eid. Skip
     *    events whose aid is for another provider (aid 0 = clinic-wide
     *    and never skipped).
     *  - In day view, OUT events (catid 3) get pop-then-unshift'd to
     *    the front of the slot so they render leftmost.
     *  - Each event's recorded width is the SMALLEST it needs across
     *    every slot it overlaps (= more crowded). leftpos is the
     *    GREATEST across every slot (= farther right when an event
     *    only touches the right edge of a crowded slot).
     *  - Returns positions keyed by eid as int|string (matches the raw
     *    event id type — sqlFetch returns strings, fixtures often use
     *    ints).
     *
     * @param  list<array<string, mixed>> $eventsForProvider
     * @param  list<array{hour: int|string, minute: int|string}> $timeSlots
     * @return array<int|string, array{width: float, leftpos: float}>
     */
    public function detectEventOverlap(
        array $eventsForProvider,
        array $timeSlots,
        int $intervalMinutes,
        int $providerId
    ): array {
        $positions = [];

        foreach ($timeSlots as $slot) {
            $slotStartMin = ((int) $slot['hour']) * 60 + ((int) $slot['minute']);
            $slotEndMin = $slotStartMin + $intervalMinutes;

            $eidsInSlot = [];

            foreach ($eventsForProvider as $event) {
                $rawEid = $event['eid'] ?? null;
                if (!is_int($rawEid) && !is_string($rawEid)) {
                    continue;
                }
                if ($rawEid === '' || $rawEid === 0) {
                    continue;
                }
                $eid = $rawEid;

                $catid = $event['catid'] ?? 0;
                $catidInt = is_int($catid) ? $catid : (is_string($catid) ? (int) $catid : 0);

                if ($this->shouldSkipForOverlap($catidInt)) {
                    continue;
                }

                $aid = $event['aid'] ?? 0;
                $aidInt = is_int($aid) ? $aid : (is_string($aid) ? (int) $aid : 0);
                if ($aidInt !== $providerId && $aidInt !== 0) {
                    continue;
                }

                $startTime = $event['startTime'] ?? '00:00:00';
                if (!is_string($startTime)) {
                    continue;
                }
                $eStart = $this->startTimeToMinutes($startTime);

                $durationSec = $event['duration'] ?? 0;
                $durationSecInt = is_int($durationSec) ? $durationSec : (is_string($durationSec) ? (int) $durationSec : 0);
                $eEnd = $eStart + (int) ($durationSecInt / 60);

                $overlaps = ($eStart >= $slotStartMin && $eStart < $slotEndMin)
                    || ($eEnd > $slotStartMin && $eEnd <= $slotEndMin)
                    || ($eStart < $slotStartMin && $eEnd > $slotEndMin);

                if (!$overlaps) {
                    continue;
                }

                $eidsInSlot[] = $eid;
                if ($this->viewType === ViewType::Day && $catidInt === 3) {
                    // OUT event in day view: pop-then-unshift to render leftmost.
                    array_pop($eidsInSlot);
                    array_unshift($eidsInSlot, $eid);
                }
            }

            if (count($eidsInSlot) === 0) {
                continue;
            }

            $width = 100.0 / count($eidsInSlot);
            $leftpos = 0.0;
            foreach ($eidsInSlot as $eid) {
                $prevWidth = $positions[$eid]['width'] ?? null;
                $prevLeft  = $positions[$eid]['leftpos'] ?? null;

                $positions[$eid] = [
                    'width'   => $prevWidth === null ? $width : min($prevWidth, $width),
                    'leftpos' => $prevLeft === null ? $leftpos : max($prevLeft, $leftpos),
                ];

                $leftpos += $width;
            }
        }

        return $positions;
    }
}
