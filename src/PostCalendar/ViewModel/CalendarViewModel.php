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
}
