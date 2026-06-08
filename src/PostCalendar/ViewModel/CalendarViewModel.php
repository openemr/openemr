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
}
