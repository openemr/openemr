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
 * Orchestration layer that turns the legacy consumer's raw inputs
 * (the existing `$A_EVENTS`, `$providers`, `$times`, etc. that
 * pnuserapi.php builds) into the render-data array each of the six
 * Twig calendar templates iterates.
 *
 * The CalendarViewModel methods are pure transformations on single
 * events / single dates. This class composes them — looping events
 * per provider, computing nav URLs, building the day-grid structure
 * per view, etc. — so the consumer's switch from pcSmarty to
 * CalendarRenderer stays small (build the data, hand it to the
 * renderer) and the orchestration is unit-testable.
 *
 * Initial scope: month_print only. Subsequent commits add the other
 * five views (week_print, day_print, then month/week/day screen).
 * Consumer (pnuserapi.php) branches by ViewType during the rollout —
 * un-converted views continue to use pcSmarty, converted ones use
 * this builder.
 */
final readonly class CalendarRenderDataBuilder
{
    public function __construct(
        public CalendarViewModel $viewModel,
    ) {
    }

    /**
     * Build the render-data array for `month_print/outlook_ajax_template.html.twig`.
     *
     * Inputs are the same shape pnuserapi.php already assembles for
     * the legacy template — minimal additional work in the consumer
     * to call this.
     *
     * @param  array<string, list<array<string, mixed>>> $aEvents     Date-keyed events array (the legacy $A_EVENTS).
     * @param  list<array<string, mixed>>                $providers   List of provider rows.
     * @param  string                                    $dateYmd     YYYYMMDD form of the current date.
     * @param  list<string>                              $shortDayNames Localized day-name labels indexed 0..6.
     * @param  int                                       $calendarApptStyle  $GLOBALS['calendar_appt_style'] passed in.
     *
     * @return array<string, mixed>
     */
    public function buildMonthPrintRenderData(
        array $aEvents,
        array $providers,
        string $dateYmd,
        array $shortDayNames,
        int $calendarApptStyle
    ): array {
        // Mini-calendars for the current month and the subsequent
        // month. Anchor = YYYY-MM-15 to guarantee mktime success
        // regardless of $dateYmd's day.
        $year = substr($dateYmd, 0, 4);
        $month = substr($dateYmd, 4, 2);
        $currentAnchor = "{$year}-{$month}-15";
        $currentMini = $this->viewModel->buildMiniCalendar($currentAnchor);

        // Subsequent month: increment month, wrap year if needed.
        $monthInt = (int) $month;
        $yearInt = (int) $year;
        $monthInt++;
        if ($monthInt > 12) {
            $monthInt = 1;
            $yearInt++;
        }
        $nextAnchor = sprintf('%04d-%02d-15', $yearInt, $monthInt);
        $nextMini = $this->viewModel->buildMiniCalendar($nextAnchor);

        // Day-of-week map. Templates use this to decide when to open
        // and close <tr> rows in the day grid.
        $dowOfDate = [];
        foreach (array_keys($aEvents) as $date) {
            $ts = strtotime($date);
            if ($ts !== false) {
                $dowOfDate[$date] = (int) date('w', $ts);
            }
        }

        // First 7 weekday names for the column header.
        $dayHeaderDates = array_slice(array_keys($aEvents), 0, 7);

        // Decorate each event in place with the fields the template
        // expects (evtClass, content, eventDateYmd). Skip catids 2
        // and 3 here so the template's iteration doesn't need to
        // filter — keeps the template smaller.
        $decoratedEvents = [];
        foreach ($aEvents as $date => $events) {
            $eventDateYmd = substr($date, 0, 4) . substr($date, 5, 2) . substr($date, 8, 2);
            $decoratedEvents[$date] = [];
            foreach ($events as $event) {
                $catidRaw = $event['catid'] ?? 0;
                $catid = is_int($catidRaw) || is_string($catidRaw) ? (int) $catidRaw : 0;
                if ($catid === 2 || $catid === 3) {
                    continue;
                }
                $evtClass = $this->viewModel->eventClassForCategory($catid);
                $overrideRaw = $event['eventViewClass'] ?? null;
                if (is_string($overrideRaw)) {
                    $evtClass = $overrideRaw;
                }
                $content = $this->viewModel->buildMonthPrintEventContent(
                    $event,
                    $date,
                    $calendarApptStyle
                );

                $decoratedEvents[$date][] = array_merge($event, [
                    'eventDate'    => $eventDateYmd,
                    'evtClass'     => $evtClass,
                    'content'      => $content,
                ]);
            }
        }

        // Date label "March 2026" — single combined string for the
        // page header span.
        $currentTs = strtotime($currentAnchor);
        $dateLabel = $currentTs !== false ? date('F Y', $currentTs) : '';

        return [
            'providers'           => $providers,
            'dowList'             => $this->viewModel->dayOfWeekList(),
            'A_SHORT_DAY_NAMES'   => $shortDayNames,
            'dateLabel'           => $dateLabel,
            'dayHeaderDates'      => $dayHeaderDates,
            'currentMonthMini'    => $currentMini,
            'nextMonthMini'       => $nextMini,
            'A_EVENTS'            => $decoratedEvents,
            'dowOfDate'           => $dowOfDate,
        ];
    }
}
