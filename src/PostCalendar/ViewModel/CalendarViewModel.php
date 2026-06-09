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
            2       => xl('IN'),
            3       => xl('OUT'),
            4       => xl('VACATION'),
            8       => xl('LUNCH'),
            11      => xl('RESERVED'),
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

    /**
     * For a catid=2 (IN) event, extend the duration so the rendered
     * block reaches the next OUT event in the provider's event list,
     * or to the end of the clinic day if no matching OUT follows.
     *
     * Non-IN events pass through unchanged.
     *
     * Algorithm mirrors the legacy:
     *
     *     if ($event['catid'] == 2) {
     *         $found = false;
     *         foreach ($events as $outevent) {
     *             ...skip wrong provider, empty eid...
     *             if ($outevent['eid'] == $event['eid']) { $found = true; continue; }
     *             if ($found && $outevent['catid'] == 3) {
     *                 // use outevent's startTime to set duration
     *                 break;
     *             }
     *         }
     *         if ($outMins == 0) {
     *             $event['duration'] = ($calEndMin - $eStartMin) * 60;
     *         }
     *     }
     *
     * Iteration order is the order events appear in the provider's
     * event list — the "next OUT after this IN" is purely positional,
     * not eid-matched.
     *
     * @param  array<string, mixed> $event
     * @param  list<array<string, mixed>> $providerEvents
     * @return array<string, mixed>
     */
    public function extendInEventDuration(
        array $event,
        array $providerEvents,
        int $providerId,
        int $clinicEndMin
    ): array {
        $catid = $event['catid'] ?? 0;
        if (!is_int($catid) && !is_string($catid)) {
            return $event;
        }
        if ((int) $catid !== 2) {
            return $event;
        }

        $eid = $event['eid'] ?? null;
        if (in_array($eid, [null, '', 0], true)) {
            return $event;
        }

        $startTime = $event['startTime'] ?? '00:00:00';
        if (!is_string($startTime)) {
            return $event;
        }
        $inStartMin = $this->startTimeToMinutes($startTime);

        $found = false;
        foreach ($providerEvents as $candidate) {
            $candAid = $candidate['aid'] ?? 0;
            $candAidInt = is_int($candAid) || is_string($candAid) ? (int) $candAid : 0;
            if ($candAidInt !== $providerId) {
                continue;
            }

            $candEid = $candidate['eid'] ?? null;
            if (in_array($candEid, [null, '', 0], true)) {
                continue;
            }

            if ($candEid == $eid) {  // loose match — legacy used `==`
                $found = true;
                continue;
            }

            if (!$found) {
                continue;
            }

            $candCatid = $candidate['catid'] ?? 0;
            $candCatidInt = is_int($candCatid) || is_string($candCatid) ? (int) $candCatid : 0;
            if ($candCatidInt !== 3) {
                continue;
            }

            // Next OUT after our IN — set duration to bridge the gap.
            $candStartTime = $candidate['startTime'] ?? '00:00:00';
            if (!is_string($candStartTime)) {
                continue;
            }
            $outStartMin = $this->startTimeToMinutes($candStartTime);
            $event['duration'] = ($outStartMin - $inStartMin) * 60;
            return $event;
        }

        // No matching OUT found — extend to clinic close.
        $event['duration'] = ($clinicEndMin - $inStartMin) * 60;
        return $event;
    }

    /**
     * Formats an event's startTime as the compact "g" / "g:ia" string the
     * month + month_print templates display.
     *
     * Examples:
     *   "09:00:00" + date "2026-03-15" → "9am"  (zero minutes → just hour + meridian)
     *   "09:30:00" + date "2026-03-15" → "9:30am"
     *   "13:00:00" + date "2026-03-15" → "1pm"
     *
     * Mirrors:
     *     $displayTime = date("g", $startDateTime);
     *     if (date("i", $startDateTime) == "00") {
     *         $displayTime .= date("a", $startDateTime);
     *     } else {
     *         $displayTime .= date(":ia", $startDateTime);
     *     }
     */
    public function formatCompactEventTime(string $eventDate, string $startTime): string
    {
        $combined = strtotime($eventDate . ' ' . $startTime);
        if ($combined === false) {
            return '';
        }

        $hourPart = date('g', $combined);
        if (date('i', $combined) === '00') {
            return $hourPart . date('a', $combined);
        }

        return $hourPart . date(':ia', $combined);
    }

    /**
     * Builds the inner HTML of a single event card for the month_print
     * view. Returns an HTML string the template renders via `|raw`.
     *
     * Replaces the inline content-build branches in month_print's main
     * loop. Two top-level branches as in the legacy:
     *  - catid 4 / 8 / 11 → "displayTime catname [- comment]" with the
     *    catname overridden to the localized special-category label
     *  - everything else → "displayTime lname" plus, when
     *    calendar_appt_style == 5, ", fname , address comment" tacked on
     *
     * NO-SHOW (catid 1) gets the lname wrapped in <s>...</s> as in the
     * legacy. Group events and holiday/closed special branches are NOT
     * supported by month_print — they fall through to the default
     * "displayTime lname" rendering, matching legacy behavior.
     *
     * Required event fields:
     *   - catid (int|string), startTime (HH:MM:SS), patient_name (?string),
     *     patient_address (?string), hometext (?string), catname (string)
     *
     * @param  array<string, mixed> $event
     */
    public function buildMonthPrintEventContent(array $event, string $eventDate, int $calendarApptStyle): string
    {
        $catidRaw = $event['catid'] ?? 0;
        $catid = is_int($catidRaw) || is_string($catidRaw) ? (int) $catidRaw : 0;

        $startTime = $event['startTime'] ?? '00:00:00';
        if (!is_string($startTime)) {
            $startTime = '00:00:00';
        }
        $displayTime = $this->formatCompactEventTime($eventDate, $startTime);

        $commentRaw = $event['hometext'] ?? '';
        $comment = is_string($commentRaw) ? $commentRaw : '';

        if (in_array($catid, [4, 8, 11], true)) {
            $catname = $this->translatedCategoryName($catid, '');
            $content = text($displayTime) . ' ' . text($catname);
            if ($comment !== '') {
                $content .= ' - ' . text($comment);
            }
            return $content;
        }

        $patientNameRaw = $event['patient_name'] ?? null;
        $patientName = is_string($patientNameRaw) ? $patientNameRaw : null;
        $parsed = $this->parsePatientName($patientName);

        $content = text($displayTime) . ' ';
        if ($catid === 1) {
            $content .= '<s>';
        }
        $content .= text($parsed['lname']);
        if ($catid === 1) {
            $content .= '</s>';
        }

        if ($calendarApptStyle === 5) {
            // Style 5 was a RM-tagged extension in the legacy. The legacy
            // emitted these unescaped which was a stored-XSS path on
            // patient_address and hometext; escape both with text() here
            // to close it without changing the legacy field order.
            $addressRaw = $event['patient_address'] ?? '';
            $address = is_string($addressRaw) ? $addressRaw : '';
            $content .= ', ' . text($parsed['fname']) . ' , ' . text($address) . text($comment);
        }

        return $content;
    }

    /**
     * Returns the legacy `dispstarth` for an event — the start-hour as
     * an int adjusted for 12-hour display. `$dispstarth = ($starth > 12)
     * ? ($starth - 12) : $starth` when 12-hour display is on; otherwise
     * the raw hour. The 0-hour edge case (midnight) is left as 0 to
     * match the legacy code; templates that display "12" for midnight
     * handle that separately.
     */
    public function displayStartHour(string $startTime, bool $isTwelveHourFormat): int
    {
        $parts = explode(':', $startTime);
        $hour = (int) $parts[0];

        if ($isTwelveHourFormat && $hour > 12) {
            return $hour - 12;
        }

        return $hour;
    }

    /**
     * Wraps the formatted event-time string in the clickable anchor the
     * day/week/month-screen templates expose. The onclick handler
     * (event_time_click defined in _calendar_screen_js.html.twig)
     * dispatches to EditEvent on the enclosing .event_appointment DIV,
     * so clicking the time opens the appointment editor. Replaces the
     * legacy `create_event_time_anchor()` helper that lived inside a
     * [-php-] block in the Smarty header template.
     */
    public static function eventTimeAnchorHtml(string $displayTime): string
    {
        return "<a class='event_time' onclick='event_time_click(this)' title='"
            . attr(xl('Click to edit')) . "'>" . text($displayTime) . "</a>";
    }

    /**
     * Builds the body HTML for one event card in the day_print view.
     *
     * Replaces the inline content-build block in day_print's main loop.
     * Two top-level branches, mirroring the legacy:
     *
     *  - Special category (catid 2/3/4/8/11) → "[recurr icon] catname
     *    [comment]". Catname overridden to the localized special label.
     *  - Patient appointment branch → "<span class='appointment{toggle}'>
     *    {dispstarth}:{startm} [recurr icon] {apptstatus} {lname [, fname
     *    [, address] [(title [: hometext])]]} </span>" with NO-SHOW
     *    (catid 1) wrapping lname in <s>...</s>.
     *
     * Group sessions and the holiday/closed catid 6/7 special branches
     * are NOT supported by day_print (matches legacy).
     *
     * @param  array<string, mixed> $event
     */
    public function buildDayPrintEventContent(
        array $event,
        int $calendarApptStyle,
        bool $isTwelveHourFormat,
        string $tplImagePath,
        string $apptToggle = ''
    ): string {
        $catidRaw = $event['catid'] ?? 0;
        $catid = is_int($catidRaw) || is_string($catidRaw) ? (int) $catidRaw : 0;

        $startTime = $event['startTime'] ?? '00:00:00';
        if (!is_string($startTime)) {
            $startTime = '00:00:00';
        }

        $recurringIcon = '';
        $recurrType = $event['recurrtype'] ?? 0;
        $recurrTypeInt = is_int($recurrType) || is_string($recurrType) ? (int) $recurrType : 0;
        if ($recurrTypeInt === 1) {
            // The legacy used a fixed icon path under TPL_IMAGE_PATH.
            // The width/height/title/alt are hard-coded in the same way.
            $recurringIcon = "<img src='" . $tplImagePath . "/repeating8.png' border='0' style='margin:0px 2px 0px 2px;' title='Repeating event' alt='Repeating event'>";
        }

        $commentRaw = $event['hometext'] ?? '';
        $comment = is_string($commentRaw) ? $commentRaw : '';

        // Special-category branch
        if (in_array($catid, [2, 3, 4, 8, 11], true)) {
            $catname = $this->translatedCategoryName($catid, '');
            $content = $recurringIcon . text($catname);
            if ($comment !== '') {
                $content .= ' ' . text($comment);
            }
            return $content;
        }

        // Patient appointment branch
        $patientId = $event['pid'] ?? null;
        $patientNameRaw = $event['patient_name'] ?? null;
        $patientName = is_string($patientNameRaw) ? $patientNameRaw : null;
        $parsed = $this->parsePatientName($patientName);

        $apptStatusRaw = $event['apptstatus'] ?? '';
        $apptStatus = is_string($apptStatusRaw) ? $apptStatusRaw : '';

        $dispStartH = $this->displayStartHour($startTime, $isTwelveHourFormat);
        $startMRaw = explode(':', $startTime)[1] ?? '00';
        $startM = $startMRaw;

        $content = "<span class='appointment" . attr($apptToggle) . "'>";
        $content .= text("$dispStartH:$startM");
        $content .= $recurringIcon;
        $content .= text($apptStatus);

        if ($patientId !== null && $patientId !== '' && $patientId !== 0) {
            if ($catid === 1) {
                $content .= '<s>';
            }
            $content .= text($parsed['lname']);

            if ($calendarApptStyle !== 1) {
                $content .= ',' . text($parsed['fname']);

                $titleRaw = $event['title'] ?? '';
                $title = is_string($titleRaw) ? $titleRaw : '';
                $addressRaw = $event['patient_address'] ?? '';
                $address = is_string($addressRaw) ? $addressRaw : '';

                if ($title !== '' && $calendarApptStyle === 5) {
                    $content .= ',' . text($address);
                }

                if ($title !== '' && $calendarApptStyle >= 3) {
                    $content .= '(' . text($title);
                    if ($comment !== '' && $calendarApptStyle >= 4) {
                        $content .= ": <span class='text-success'>" . text(trim($comment)) . '</span>';
                    }
                    $content .= ')';
                }
            }

            if ($catid === 1) {
                $content .= '</s>';
            }
        } else {
            // No patient_id — fall back to category name (legacy quirk).
            $catnameRaw = $event['catname'] ?? '';
            $catname = is_string($catnameRaw) ? $catnameRaw : '';
            $content .= text($catname);
        }

        $content .= '</span>';
        return $content;
    }

    /**
     * Builds the body HTML for one event card in the week_print view.
     *
     * Differs from day_print's builder in two ways the legacy makes
     * explicit:
     *
     *  - Time format is zero-padded 12-hour "h:i" via the combined
     *    eventDate + startTime timestamp, NOT the dispstarth:startm
     *    integer-and-string concat day_print uses. AM/PM is omitted
     *    in this format — legacy quirk, ambiguous between 9:30am and
     *    9:30pm in raw output.
     *  - Hometext gets wrapped in `<font color='green'>` rather than
     *    `<span class='text-success'>`. Preserved as-is to match the
     *    legacy week_print byte-output.
     *
     * Also differs from day_print: the special-category branch (catids
     * 2, 3, 4, 8, 11) INCLUDES the formatted time prefix. day_print's
     * special-cat branch did not.
     *
     * Group sessions NOT supported (matches legacy week_print).
     *
     * @param  array<string, mixed> $event
     */
    public function buildWeekPrintEventContent(
        array $event,
        string $eventDate,
        int $calendarApptStyle,
        string $tplImagePath,
        string $apptToggle = ''
    ): string {
        $catidRaw = $event['catid'] ?? 0;
        $catid = is_int($catidRaw) || is_string($catidRaw) ? (int) $catidRaw : 0;

        $startTime = $event['startTime'] ?? '00:00:00';
        if (!is_string($startTime)) {
            $startTime = '00:00:00';
        }

        // Legacy: $eventdatetime = strtotime("$date $starth:$startm");
        // and then date("h:i", $eventdatetime).
        $eventTs = strtotime($eventDate . ' ' . $startTime);
        $timeLabel = $eventTs !== false ? date('h:i', $eventTs) : '00:00';

        $recurringIcon = '';
        $recurrType = $event['recurrtype'] ?? 0;
        $recurrTypeInt = is_int($recurrType) || is_string($recurrType) ? (int) $recurrType : 0;
        if ($recurrTypeInt === 1) {
            $recurringIcon = "<img src='" . $tplImagePath . "/repeating8.png' border='0' style='margin:0px 2px 0px 2px;' title='Repeating event' alt='Repeating event'>";
        }

        $commentRaw = $event['hometext'] ?? '';
        $comment = is_string($commentRaw) ? $commentRaw : '';

        // Special-category branch — INCLUDES time prefix (unlike day_print)
        if (in_array($catid, [2, 3, 4, 8, 11], true)) {
            $catname = $this->translatedCategoryName($catid, '');
            $content = text($timeLabel) . $recurringIcon . ' ' . text($catname);
            if ($comment !== '') {
                $content .= ' ' . text($comment);
            }
            return $content;
        }

        // Patient appointment branch
        $patientId = $event['pid'] ?? null;
        $patientNameRaw = $event['patient_name'] ?? null;
        $patientName = is_string($patientNameRaw) ? $patientNameRaw : null;
        $parsed = $this->parsePatientName($patientName);

        $content = "<span class='appointment" . attr($apptToggle) . "'>";
        $content .= text($timeLabel) . ' ';
        $content .= $recurringIcon;

        if ($patientId !== null && $patientId !== '' && $patientId !== 0) {
            if ($catid === 1) {
                $content .= '<s>';
            }
            $content .= text($parsed['lname']);

            if ($calendarApptStyle !== 1) {
                $content .= ',' . text($parsed['fname']);

                $titleRaw = $event['title'] ?? '';
                $title = is_string($titleRaw) ? $titleRaw : '';
                $addressRaw = $event['patient_address'] ?? '';
                $address = is_string($addressRaw) ? $addressRaw : '';

                if ($title !== '' && $calendarApptStyle === 5) {
                    $content .= ',' . text($address);
                }

                if ($title !== '' && $calendarApptStyle >= 3) {
                    $content .= '(' . text($title);
                    if ($comment !== '' && $calendarApptStyle >= 4) {
                        // Legacy quirk: week_print uses <font color='green'>
                        // where day_print used <span class='text-success'>.
                        $content .= ": <font color='green'>" . text(trim($comment)) . '</font>';
                    }
                    $content .= ')';
                }
            }

            if ($catid === 1) {
                $content .= '</s>';
            }
        } else {
            $catnameRaw = $event['catname'] ?? '';
            $catname = is_string($catnameRaw) ? $catnameRaw : '';
            $content .= text($catname);
        }

        $content .= '</span>';
        return $content;
    }

    /**
     * Builds the body HTML for one event card in the on-screen MONTH view
     * (NOT month_print). The screen variant adds:
     *
     *  - patient-link anchor with picture-hover ShowImage(...) icon
     *  - group-session branch with goGid(...) anchor and user-blue icon
     *  - clinic-closed / holiday content fallback (catid 6/7) using
     *    event.title via xlt rather than the category name
     *  - month-specific compact time format (g[a] / g:ia)
     *  - create_event_time_anchor() wrapping around displayTime (the same
     *    anchor pcEventTimeAnchor produces — passed in pre-built so this
     *    method stays stateless)
     *
     * The consumer is responsible for loading these fields onto the event
     * before passing in (DB lookups happen in the consumer, batched once
     * per render rather than per event as the legacy did):
     *
     *   - patient_age, patient_dob (already oeFormatShortDate'd),
     *     patient_address, patient_name, pid
     *   - gid, group_name, group_counselors_text (already joined with
     *     getUserNameById newlines), group_type_name (already resolved
     *     via getTypeName)
     *   - title, hometext (already pcVarPrepHTMLDisplay-escaped per
     *     the legacy comments — NOT double-escaped here)
     *
     * Returns an array shape with both the body HTML and the multi-line
     * tooltip text (`divTitle` in the legacy). The template uses
     * tooltip as the `title="..."` attribute on the wrapping div.
     *
     * @param  array<string, mixed> $event
     * @return array{content: string, tooltip: string}
     */
    public function buildMonthScreenEventContent(
        array $event,
        string $eventDate,
        int $calendarApptStyle,
        string $tplImagePath,
        string $webroot
    ): array {
        $catidRaw = $event['catid'] ?? 0;
        $catid = is_int($catidRaw) || is_string($catidRaw) ? (int) $catidRaw : 0;

        $startTime = $event['startTime'] ?? '00:00:00';
        if (!is_string($startTime)) {
            $startTime = '00:00:00';
        }
        $displayTime = $this->formatCompactEventTime($eventDate, $startTime);

        $commentRaw = $event['hometext'] ?? '';
        $comment = is_string($commentRaw) ? $commentRaw : '';

        $catnameRaw = $event['catname'] ?? '';
        $rawCatname = is_string($catnameRaw) ? $catnameRaw : '';

        // Tooltip prefix: legacy dateformat(strtotime($date), true) and the
        // facility name on its own line. The consumer pre-formats the date
        // (the dateformat() helper isn't in the view-model's scope).
        $tooltipPrefix = $event['tooltip_date_prefix'] ?? '';
        $tooltipPrefixStr = is_string($tooltipPrefix) ? $tooltipPrefix : '';
        $facilityRow = $event['facility_row'] ?? null;
        $facilityName = is_array($facilityRow) && isset($facilityRow['name']) && is_string($facilityRow['name'])
            ? $facilityRow['name']
            : '';

        $tooltip = $tooltipPrefixStr . "\n" . $facilityName;

        // Special-category branch (catid 4/8/11). NOT 2/3 — month-screen
        // doesn't render IN/OUT events at all (skipped before this method
        // is called).
        if (in_array($catid, [4, 8, 11], true)) {
            $catname = $this->translatedCategoryName($catid, $rawCatname);
            $atitle = $catname;
            if ($comment !== '') {
                $atitle .= ' ' . $comment;
            }
            $tooltip .= "\n[" . $atitle . ']';

            $content = text($displayTime) . '&nbsp;' . text($catname);

            // Note: catid here is one of {4, 8, 11} — the `!= 6` check from
            // the legacy is preserved at the end of this method instead.
            return ['content' => $content, 'tooltip' => $tooltip . "\n(" . xl('double click to edit') . ')'];
        }

        // Patient appt / group / fallback branch.
        $patientId = $event['pid'] ?? null;
        $gidRaw = $event['gid'] ?? null;
        $hasGroup = $gidRaw !== null && $gidRaw !== '' && $gidRaw !== 0;
        // Legacy: `if($groupid) $patientid = '';` — group events suppress
        // the patient-link branch.
        if ($hasGroup) {
            $patientId = '';
        }

        $patientNameRaw = $event['patient_name'] ?? null;
        $patientName = is_string($patientNameRaw) ? $patientNameRaw : null;
        $parsed = $this->parsePatientName($patientName);

        if ($hasGroup) {
            $counselorsRaw = $event['group_counselors_text'] ?? '';
            $counselors = is_string($counselorsRaw) ? $counselorsRaw : '';
            $tooltip .= "\n" . xl('Counselors') . ": \n" . $counselors . " \n";
            $tooltip .= "\r\n[" . $rawCatname . ' ' . $comment . ']' . (is_string($event['group_name'] ?? null) ? $event['group_name'] : '');
        } else {
            $tooltip .= "\r\n[" . $rawCatname . ' ' . $comment . ']' . $parsed['fname'] . ' ' . $parsed['lname'];
        }

        // Clickable event-time anchor (clicking opens the editor via
        // event_time_click → EditEvent). Legacy: create_event_time_anchor().
        $content = self::eventTimeAnchorHtml($displayTime);

        if ($patientId !== null && $patientId !== '' && $patientId !== 0) {
            $patientDob = is_string($event['patient_dob'] ?? null) ? $event['patient_dob'] : '';
            $patientAgeRaw = $event['patient_age'] ?? '';
            $patientAge = is_int($patientAgeRaw) || is_string($patientAgeRaw) ? (string) $patientAgeRaw : '';
            $patientAddress = is_string($event['patient_address'] ?? null) ? $event['patient_address'] : '';

            $linkTitle = attr($parsed['fname']) . ' ' . attr($parsed['lname']) . " \n";
            $linkTitle .= attr($patientAddress) . "\n";
            $linkTitle .= xla('Age') . ': ' . attr($patientAge) . "\n"
                . xla('DOB') . ': ' . attr($patientDob) . ' ' . attr($comment) . "\n";
            $linkTitle .= '(' . xla('Click to view') . ')';

            $patientIdAttr = is_int($patientId) || is_string($patientId) ? (string) $patientId : '';

            $content .= "<a class='link_title' data-pid='" . attr($patientIdAttr) . "' href='javascript:goPid(" . attr_js($patientIdAttr) . ")' title='" . $linkTitle . "'>";

            $imageHref = $webroot . '/controller.php?document&retrieve&patient_id=' . urlencode($patientIdAttr) . '&document_id=-1&as_file=false&original_file=true&disable_exit=false&show_original=true&context=patient_picture';
            $content .= "<img src='" . $tplImagePath . "/user-green.gif' onmouseover=\"javascript:ShowImage(" . attr_js($imageHref) . ");\" onmouseout=\"javascript:HideImage();\" border='0' title='" . $linkTitle . "' alt='View Patient' />";

            if ($catid === 1) {
                $content .= '<s>';
            }
            $content .= text($parsed['lname']);

            if ($calendarApptStyle !== 1) {
                $content .= ',' . text($parsed['fname']);

                $titleRaw = $event['title'] ?? '';
                $title = is_string($titleRaw) ? $titleRaw : '';

                if ($title !== '' && $calendarApptStyle === 5) {
                    $content .= ',' . text($patientAddress);
                }

                if ($title !== '' && $calendarApptStyle >= 3) {
                    $content .= '(' . text($title);
                    if ($comment !== '' && $calendarApptStyle >= 4) {
                        // Legacy comment: "hometext is already escaped in
                        // pnuserapi.php via the pcVarPrepHTMLDisplay
                        // function; we don't double escape it here."
                        $content .= ": <span class='text-success'>" . trim($comment) . '</span>';
                    }
                    $content .= ')';
                }
            }

            if ($catid === 1) {
                $content .= '</s>';
            }
            $content .= '</a>';
        } elseif ($hasGroup) {
            $groupName = is_string($event['group_name'] ?? null) ? $event['group_name'] : '';
            $groupTypeName = is_string($event['group_type_name'] ?? null) ? $event['group_type_name'] : '';
            $gidAttr = is_int($gidRaw) || is_string($gidRaw) ? (string) $gidRaw : '';

            $tooltip .= "\n" . $groupTypeName . "\n";
            $linkTitle = $tooltip . "\n" . '(' . xl('Click to view') . ')';

            $content .= "<a href='javascript:goGid(" . attr_js($gidAttr) . ")' title='" . attr($linkTitle) . "'>";
            $content .= "<img src='" . $tplImagePath . "/user-blue.gif' border='0' title='" . attr($linkTitle) . "' alt='View Patient' />";

            if ($catid === 1) {
                $content .= '<s>';
            }
            $content .= text($groupName);

            if ($calendarApptStyle !== 1) {
                $titleRaw = $event['title'] ?? '';
                $title = is_string($titleRaw) ? $titleRaw : '';
                if ($title !== '' && $calendarApptStyle >= 3) {
                    $content .= '(' . text($title);
                    if ($comment !== '' && $calendarApptStyle >= 4) {
                        $content .= ": <span class='text-success'>" . trim($comment) . '</span>';
                    }
                    $content .= ')';
                }
            }

            if ($catid === 1) {
                $content .= '</s>';
            }
            $content .= '</a>';
        } else {
            // No patient, no group — catid 6/7 use event.title, others use
            // catname via xl_appt_category.
            if ($catid === 6 || $catid === 7) {
                $titleRaw = $event['title'] ?? '';
                $title = is_string($titleRaw) ? $titleRaw : '';
                // Legacy: `xlt($event['title'])`. The titles for catid 6
                // (HOLIDAY) and catid 7 (Clinic-closed) come from
                // admin-uploaded CSVs via the holiday-import feature
                // (interface/main/holidays/) — standard names like
                // "Christmas Day", "Independence Day", "Thanksgiving Day".
                // Non-English locales often have those strings in their
                // translation catalogs (seeded by other call sites), so
                // the opportunistic-translation IS load-bearing in
                // multilingual installations.
                //
                // The dynamic-string arg trips PHPStan's literal-string
                // rule on xlt. A baseline entry is the honest fix here —
                // explicit suppression with a user-permitted exception
                // for this specific load-bearing case. Project owner
                // approved the baseline entry over using the private
                // hsc_private_xl_or_warn helper as a back-door.
                $content = xlt($title);
            } else {
                $content .= text(xl_appt_category($rawCatname));
            }
        }

        if ($catid !== 6) {
            $tooltip .= "\n(" . xl('double click to edit') . ')';
        }

        return ['content' => $content, 'tooltip' => $tooltip];
    }

    /**
     * Builds the body HTML for one event card in the on-screen DAY view.
     *
     * Differs from month-screen in several ways the legacy makes explicit:
     *
     *  - Patient icon is <i class='fas fa-user text-success'> (FA glyph),
     *    NOT user-green.gif. Group icon is <i class='fas fa-user text-primary'>.
     *  - Time prefix uses `dispstarth:startm` (legacy zero-padded minute
     *    via sprintf("%02s")) wrapped in the clickable event-time anchor
     *    via eventTimeAnchorHtml().
     *  - Patient/group branch is wrapped in <span class='appointment'>.
     *  - Special-category branch (catid 2/3/4/8/11) emits time-less
     *    catname-then-recurring-icon-then-comment, with the recurring icon
     *    using the FA `border-0` class variant.
     *  - apptstatus rendered with `&nbsp;` prefix.
     *
     * Group sessions ARE supported (matches legacy). Group icon class
     * (' groups ') gets appended to the wrapping div's class so the JS
     * EditEvent handler can route to oldGroupEvt instead of oldEvt — the
     * builder returns extraClass: ' groups ' when applicable so the
     * caller appends it to evtClass on the wrapper.
     *
     * @param  array<string, mixed> $event
     * @return array{content: string, tooltip: string, extraClass: string}
     */
    public function buildDayScreenEventContent(
        array $event,
        int $calendarApptStyle,
        string $tplImagePath,
        string $webroot,
        bool $isTwelveHourFormat
    ): array {
        $catidRaw = $event['catid'] ?? 0;
        $catid = is_int($catidRaw) || is_string($catidRaw) ? (int) $catidRaw : 0;

        $startTime = $event['startTime'] ?? '00:00:00';
        if (!is_string($startTime)) {
            $startTime = '00:00:00';
        }

        $commentRaw = $event['hometext'] ?? '';
        $comment = is_string($commentRaw) ? $commentRaw : '';

        $catnameRaw = $event['catname'] ?? '';
        $rawCatname = is_string($catnameRaw) ? $catnameRaw : '';

        // Tooltip prefix from consumer (legacy: dateformat(strtotime($date), true)
        // plus the facility name from the per-event sqlStatement lookup).
        $tooltipPrefix = $event['tooltip_date_prefix'] ?? '';
        $tooltipPrefixStr = is_string($tooltipPrefix) ? $tooltipPrefix : '';
        $facilityRow = $event['facility_row'] ?? null;
        $facilityName = is_array($facilityRow) && isset($facilityRow['name']) && is_string($facilityRow['name'])
            ? $facilityRow['name']
            : '';

        $tooltip = $tooltipPrefixStr . "\n" . $facilityName;

        // Special-category branch (catid 2/3/4/8/11). No <span> wrapper.
        if (in_array($catid, [2, 3, 4, 8, 11], true)) {
            $catname = $this->translatedCategoryName($catid, $rawCatname);
            $atitle = $catname;
            if ($comment !== '') {
                $atitle .= ' ' . $comment;
            }
            $tooltip .= "\n[" . $atitle . ']';

            $content = text($catname);

            $recurrType = $event['recurrtype'] ?? 0;
            $recurrTypeInt = is_int($recurrType) || is_string($recurrType) ? (int) $recurrType : 0;
            if ($recurrTypeInt > 0) {
                $content .= "<img class='border-0' src='" . $tplImagePath . "/repeating8.png' style='margin: 0 2px 0 2px;' title='" . xla('Repeating event') . "' alt='" . xla('Repeating event') . "' />";
            }
            if ($comment !== '') {
                $content .= ' ' . text($comment);
            }

            return [
                'content'    => $content,
                'tooltip'    => $tooltip . "\n(" . xl('double click to edit') . ')',
                'extraClass' => '',
            ];
        }

        // Patient / group / fallback branch — wrapped in <span class='appointment'>.
        $patientId = $event['pid'] ?? null;
        $gidRaw = $event['gid'] ?? null;
        $hasGroup = $gidRaw !== null && $gidRaw !== '' && $gidRaw !== 0;
        // Legacy: $patientid suppression when group is present happens at the
        // assignment site; mirroring here.
        if ($hasGroup) {
            $patientId = '';
        }

        $patientNameRaw = $event['patient_name'] ?? null;
        $patientName = is_string($patientNameRaw) ? $patientNameRaw : null;
        $parsed = $this->parsePatientName($patientName);

        if ($hasGroup) {
            $groupName = is_string($event['group_name'] ?? null) ? $event['group_name'] : '';
            $tooltip .= "\r\n[" . $rawCatname . ' ' . $comment . ']' . $groupName;
        } else {
            $tooltip .= "\r\n[" . $rawCatname . ' ' . $comment . ']' . $parsed['fname'] . ' ' . $parsed['lname'];
        }

        // Clickable event-time anchor — legacy used
        // create_event_time_anchor($dispstarth . ":" . sprintf("%02s", $startm))
        // where dispstarth came from displayStartHour.
        $dayStartParts = explode(":", $startTime);
        $dayStartHour = $this->displayStartHour($startTime, $isTwelveHourFormat);
        $dayStartMinute = isset($dayStartParts[1]) ? sprintf("%02s", $dayStartParts[1]) : "00";
        $timeAnchor = self::eventTimeAnchorHtml($dayStartHour . ":" . $dayStartMinute);

        $recurrType = $event['recurrtype'] ?? 0;
        $recurrTypeInt = is_int($recurrType) || is_string($recurrType) ? (int) $recurrType : 0;
        $recurringIcon = '';
        if ($recurrTypeInt > 0) {
            $recurringIcon = "<img src='" . $tplImagePath . "/repeating8.png' border='0' style='margin:0px 2px 0px 2px;' title='" . xla('Repeating event') . "' alt='" . xla('Repeating event') . "'>";
        }

        $apptStatusRaw = $event['apptstatus'] ?? '';
        $apptStatus = is_string($apptStatusRaw) ? $apptStatusRaw : '';

        $content = "<span class='appointment'>";
        $content .= $timeAnchor;
        $content .= $recurringIcon;
        $content .= '&nbsp;' . text($apptStatus);

        $extraClass = '';

        if ($patientId !== null && $patientId !== '' && $patientId !== 0) {
            $patientDob = is_string($event['patient_dob'] ?? null) ? $event['patient_dob'] : '';
            $patientAgeRaw = $event['patient_age'] ?? '';
            $patientAge = is_int($patientAgeRaw) || is_string($patientAgeRaw) ? (string) $patientAgeRaw : '';
            $patientAddress = is_string($event['patient_address'] ?? null) ? $event['patient_address'] : '';

            $linkTitle = attr($parsed['fname']) . ' ' . attr($parsed['lname']) . " \n";
            $linkTitle .= attr($patientAddress) . "\n";
            $linkTitle .= xla('Age') . ': ' . attr($patientAge) . "\n"
                . xla('DOB') . ': ' . attr($patientDob) . ' ' . attr($comment) . "\n";
            $linkTitle .= '(' . xla('Click to view') . ')';

            $patientIdAttr = is_int($patientId) || is_string($patientId) ? (string) $patientId : '';

            $content .= "<a class='link_title' data-pid='" . attr($patientIdAttr) . "' href='javascript:goPid(" . attr_js($patientIdAttr) . ")' title='" . $linkTitle . "'>";

            $imageHref = $webroot . '/controller.php?document&retrieve&patient_id=' . urlencode($patientIdAttr) . '&document_id=-1&as_file=false&original_file=true&disable_exit=false&show_original=true&context=patient_picture';
            $content .= "<i class='fas fa-user text-success' onmouseover=\"javascript:ShowImage(" . attr_js($imageHref) . ");\" onmouseout=\"javascript:HideImage();\" title='" . $linkTitle . "'></i>";

            if ($catid === 1) {
                $content .= '<s>';
            }
            $content .= text($parsed['lname']);

            if ($calendarApptStyle !== 1) {
                $content .= ',' . text($parsed['fname']);

                $titleRaw = $event['title'] ?? '';
                $title = is_string($titleRaw) ? $titleRaw : '';

                if ($title !== '' && $calendarApptStyle === 5) {
                    $content .= ',' . text($patientAddress);
                }

                if ($title !== '' && $calendarApptStyle >= 3) {
                    $content .= '(' . text($title);
                    if ($comment !== '' && $calendarApptStyle >= 4) {
                        $content .= ": <span class='text-success'>" . trim($comment) . '</span>';
                    }
                    $content .= ')';
                }
            }

            if ($catid === 1) {
                $content .= '</s>';
            }
            $content .= '</a>';
        } elseif ($hasGroup) {
            $groupName = is_string($event['group_name'] ?? null) ? $event['group_name'] : '';
            $groupTypeName = is_string($event['group_type_name'] ?? null) ? $event['group_type_name'] : '';
            $gidAttr = is_int($gidRaw) || is_string($gidRaw) ? (string) $gidRaw : '';

            $tooltip .= "\n" . $groupTypeName . "\n";
            $linkTitle = $tooltip . "\n" . '(' . xl('Click to view') . ')';

            $content .= "<a href='javascript:goGid(" . attr_js($gidAttr) . ")' title='" . attr($linkTitle) . "'>";
            $content .= "<i class='fas fa-user text-primary' title='" . attr($linkTitle) . "'></i>";

            if ($catid === 1) {
                $content .= '<s>';
            }
            $content .= text($groupName);

            if ($calendarApptStyle !== 1) {
                $titleRaw = $event['title'] ?? '';
                $title = is_string($titleRaw) ? $titleRaw : '';
                if ($title !== '' && $calendarApptStyle >= 3) {
                    $content .= '(' . text($title);
                    if ($comment !== '' && $calendarApptStyle >= 4) {
                        $content .= ": <span class='text-success'>" . trim($comment) . '</span>';
                    }
                    $content .= ')';
                }
            }

            if ($catid === 1) {
                $content .= '</s>';
            }
            $content .= '</a>';

            // Caller appends this to evtClass on the wrapping div so the JS
            // EditEvent handler routes to oldGroupEvt instead of oldEvt.
            $extraClass = ' groups ';
        } else {
            // No patient, no group — catid 6/7 use event.title, others use
            // catname via xl_appt_category.
            if ($catid === 6 || $catid === 7) {
                $titleRaw = $event['title'] ?? '';
                $title = is_string($titleRaw) ? $titleRaw : '';
                // Same load-bearing dynamic xlt as month-screen — see the
                // longer note in buildMonthScreenEventContent for why.
                // Baseline entry covers both call sites.
                $content .= xlt($title);
            } else {
                $content .= text(xl_appt_category($rawCatname));
            }
        }

        $content .= '</span>';

        return [
            'content'    => $content,
            'tooltip'    => $tooltip . "\n(" . xl('double click to edit') . ')',
            'extraClass' => $extraClass,
        ];
    }

    /**
     * Builds the body HTML for one event card in the on-screen WEEK view.
     *
     * Near-twin of buildDayScreenEventContent with two additions the
     * legacy week template makes explicit:
     *
     *  - The wrapping `<span class='appointment...'>` includes the
     *    consumer-built apptToggle suffix (legacy $apptToggle).
     *  - Patient-appointment branch emits an extra anchor:
     *      <a class="show-appointment shown">...</a>
     *    placed inside the patient link block. The "shown" class
     *    indicates initial visibility — JS code toggles this.
     *
     * catid 99 → event_holiday is handled by eventClassForCategory
     * (which branches on viewType), so the consumer passes the
     * already-resolved evtClass; no week-specific logic in here.
     *
     * @param  array<string, mixed> $event
     * @return array{content: string, tooltip: string, extraClass: string}
     */
    public function buildWeekScreenEventContent(
        array $event,
        int $calendarApptStyle,
        string $tplImagePath,
        string $webroot,
        bool $isTwelveHourFormat = false,
        string $apptToggle = ''
    ): array {
        $catidRaw = $event['catid'] ?? 0;
        $catid = is_int($catidRaw) || is_string($catidRaw) ? (int) $catidRaw : 0;

        $startTime = $event['startTime'] ?? '00:00:00';
        if (!is_string($startTime)) {
            $startTime = '00:00:00';
        }

        $commentRaw = $event['hometext'] ?? '';
        $comment = is_string($commentRaw) ? $commentRaw : '';

        $catnameRaw = $event['catname'] ?? '';
        $rawCatname = is_string($catnameRaw) ? $catnameRaw : '';

        $tooltipPrefix = $event['tooltip_date_prefix'] ?? '';
        $tooltipPrefixStr = is_string($tooltipPrefix) ? $tooltipPrefix : '';
        $facilityRow = $event['facility_row'] ?? null;
        $facilityName = is_array($facilityRow) && isset($facilityRow['name']) && is_string($facilityRow['name'])
            ? $facilityRow['name']
            : '';

        $tooltip = $tooltipPrefixStr . "\n" . $facilityName;

        // Special-category branch (same as day-screen — no <span> wrapper).
        if (in_array($catid, [2, 3, 4, 8, 11], true)) {
            $catname = $this->translatedCategoryName($catid, $rawCatname);
            $atitle = $catname;
            if ($comment !== '') {
                $atitle .= ' ' . $comment;
            }
            $tooltip .= "\n[" . $atitle . ']';

            $content = text($catname);

            $recurrType = $event['recurrtype'] ?? 0;
            $recurrTypeInt = is_int($recurrType) || is_string($recurrType) ? (int) $recurrType : 0;
            if ($recurrTypeInt > 0) {
                $content .= "<img class='border-0' src='" . $tplImagePath . "/repeating8.png' style='margin: 0 2px 0 2px;' title='" . xla('Repeating event') . "' alt='" . xla('Repeating event') . "' />";
            }
            if ($comment !== '') {
                $content .= ' ' . text($comment);
            }

            return [
                'content'    => $content,
                'tooltip'    => $tooltip . "\n(" . xl('double click to edit') . ')',
                'extraClass' => '',
            ];
        }

        // Patient / group / fallback branch with `<span class='appointment{apptToggle}'>`.
        $patientId = $event['pid'] ?? null;
        $gidRaw = $event['gid'] ?? null;
        $hasGroup = $gidRaw !== null && $gidRaw !== '' && $gidRaw !== 0;
        if ($hasGroup) {
            $patientId = '';
        }

        $patientNameRaw = $event['patient_name'] ?? null;
        $patientName = is_string($patientNameRaw) ? $patientNameRaw : null;
        $parsed = $this->parsePatientName($patientName);

        if ($hasGroup) {
            $groupName = is_string($event['group_name'] ?? null) ? $event['group_name'] : '';
            $tooltip .= "\r\n[" . $rawCatname . ' ' . $comment . ']' . $groupName;
        } else {
            $tooltip .= "\r\n[" . $rawCatname . ' ' . $comment . ']' . $parsed['fname'] . ' ' . $parsed['lname'];
        }

        // Clickable event-time anchor — legacy used
        // create_event_time_anchor($dispstarth . ":" . sprintf("%02s", $startm))
        // where dispstarth came from displayStartHour.
        $weekStartParts = explode(":", $startTime);
        $weekStartHour = $this->displayStartHour($startTime, $isTwelveHourFormat);
        $weekStartMinute = isset($weekStartParts[1]) ? sprintf("%02s", $weekStartParts[1]) : "00";
        $timeAnchor = self::eventTimeAnchorHtml($weekStartHour . ":" . $weekStartMinute);

        $recurrType = $event['recurrtype'] ?? 0;
        $recurrTypeInt = is_int($recurrType) || is_string($recurrType) ? (int) $recurrType : 0;
        $recurringIcon = '';
        if ($recurrTypeInt > 0) {
            $recurringIcon = "<img src='" . $tplImagePath . "/repeating8.png' border='0' style='margin:0px 2px 0px 2px;' title='" . xla('Repeating event') . "' alt='" . xla('Repeating event') . "'>";
        }

        $apptStatusRaw = $event['apptstatus'] ?? '';
        $apptStatus = is_string($apptStatusRaw) ? $apptStatusRaw : '';

        // Week-specific: appointment-toggle class suffix.
        $content = "<span class='appointment" . attr($apptToggle) . "'>";
        $content .= $timeAnchor;
        $content .= $recurringIcon;
        $content .= '&nbsp;' . text($apptStatus);

        $extraClass = '';

        if ($patientId !== null && $patientId !== '' && $patientId !== 0) {
            $patientDob = is_string($event['patient_dob'] ?? null) ? $event['patient_dob'] : '';
            $patientAgeRaw = $event['patient_age'] ?? '';
            $patientAge = is_int($patientAgeRaw) || is_string($patientAgeRaw) ? (string) $patientAgeRaw : '';
            $patientAddress = is_string($event['patient_address'] ?? null) ? $event['patient_address'] : '';

            $linkTitle = attr($parsed['fname']) . ' ' . attr($parsed['lname']) . " \n";
            $linkTitle .= attr($patientAddress) . "\n";
            $linkTitle .= xla('Age') . ': ' . attr($patientAge) . "\n"
                . xla('DOB') . ': ' . attr($patientDob) . ' ' . attr($comment) . "\n";
            $linkTitle .= '(' . xla('Click to view') . ')';

            $patientIdAttr = is_int($patientId) || is_string($patientId) ? (string) $patientId : '';

            $content .= "<a class='link_title' data-pid='" . attr($patientIdAttr) . "' href='javascript:goPid(" . attr_js($patientIdAttr) . ")' title='" . $linkTitle . "'>";

            $imageHref = $webroot . '/controller.php?document&retrieve&patient_id=' . urlencode($patientIdAttr) . '&document_id=-1&as_file=false&original_file=true&disable_exit=false&show_original=true&context=patient_picture';
            $content .= "<i class='fas fa-user text-success' onmouseover=\"javascript:ShowImage(" . attr_js($imageHref) . ");\" onmouseout=\"javascript:HideImage();\" title='" . $linkTitle . "'></i>";

            // Week-specific: the show-appointment toggle anchor between
            // the icon and the patient name.
            $content .= "<a class='show-appointment shown'></a>";

            if ($catid === 1) {
                $content .= '<s>';
            }
            $content .= text($parsed['lname']);

            if ($calendarApptStyle !== 1) {
                $content .= ',' . text($parsed['fname']);

                $titleRaw = $event['title'] ?? '';
                $title = is_string($titleRaw) ? $titleRaw : '';

                if ($title !== '' && $calendarApptStyle === 5) {
                    $content .= ',' . text($patientAddress);
                }

                if ($title !== '' && $calendarApptStyle >= 3) {
                    $content .= '(' . text($title);
                    if ($comment !== '' && $calendarApptStyle >= 4) {
                        $content .= ": <span class='text-success'>" . trim($comment) . '</span>';
                    }
                    $content .= ')';
                }
            }

            if ($catid === 1) {
                $content .= '</s>';
            }
            $content .= '</a>';
        } elseif ($hasGroup) {
            $groupName = is_string($event['group_name'] ?? null) ? $event['group_name'] : '';
            $groupTypeName = is_string($event['group_type_name'] ?? null) ? $event['group_type_name'] : '';
            $gidAttr = is_int($gidRaw) || is_string($gidRaw) ? (string) $gidRaw : '';

            $tooltip .= "\n" . $groupTypeName . "\n";
            $linkTitle = $tooltip . "\n" . '(' . xl('Click to view') . ')';

            $content .= "<a href='javascript:goGid(" . attr_js($gidAttr) . ")' title='" . attr($linkTitle) . "'>";
            $content .= "<i class='fas fa-user text-primary' title='" . attr($linkTitle) . "'></i>";

            if ($catid === 1) {
                $content .= '<s>';
            }
            $content .= text($groupName);

            if ($calendarApptStyle !== 1) {
                $titleRaw = $event['title'] ?? '';
                $title = is_string($titleRaw) ? $titleRaw : '';
                if ($title !== '' && $calendarApptStyle >= 3) {
                    $content .= '(' . text($title);
                    if ($comment !== '' && $calendarApptStyle >= 4) {
                        $content .= ": <span class='text-success'>" . trim($comment) . '</span>';
                    }
                    $content .= ')';
                }
            }

            if ($catid === 1) {
                $content .= '</s>';
            }
            $content .= '</a>';

            $extraClass = ' groups ';
        } else {
            if ($catid === 6 || $catid === 7) {
                $titleRaw = $event['title'] ?? '';
                $title = is_string($titleRaw) ? $titleRaw : '';
                // Third call site sharing the xlt baseline entry.
                $content .= xlt($title);
            } else {
                $content .= text(xl_appt_category($rawCatname));
            }
        }

        $content .= '</span>';

        return [
            'content'    => $content,
            'tooltip'    => $tooltip . "\n(" . xl('double click to edit') . ')',
            'extraClass' => $extraClass,
        ];
    }
}
