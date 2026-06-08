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

    /**
     * Build the render-data array for `week_print/outlook_ajax_template.html.twig`.
     *
     * week_print's per-provider layout pairs day N with day N+4 across
     * up to 4 rows. The consumer-side work is mostly assembling the
     * dayPairs structure the template iterates plus the date-range
     * header label.
     *
     * @param  array<string, list<array<string, mixed>>> $aEvents       Date-keyed events array.
     * @param  list<array<string, mixed>>                $providers
     * @param  list<string>                              $shortDayNames
     *
     * @return array<string, mixed>
     */
    public function buildWeekPrintRenderData(
        array $aEvents,
        array $providers,
        string $dateYmd,
        array $shortDayNames,
        int $calendarApptStyle,
        string $tplImagePath
    ): array {
        $year = substr($dateYmd, 0, 4);
        $month = substr($dateYmd, 4, 2);
        $currentMini = $this->viewModel->buildMiniCalendar("{$year}-{$month}-15");

        $monthInt = (int) $month;
        $yearInt = (int) $year;
        $monthInt++;
        if ($monthInt > 12) {
            $monthInt = 1;
            $yearInt++;
        }
        $nextMini = $this->viewModel->buildMiniCalendar(sprintf('%04d-%02d-15', $yearInt, $monthInt));

        // First and last dates for the page header "Month DD - Month DD".
        $dates = array_keys($aEvents);
        $firstDate = $dates[0] ?? '';
        $lastDate = $dates[count($dates) - 1] ?? '';

        $firstTs = $firstDate !== '' ? strtotime($firstDate) : false;
        $lastTs = $lastDate !== '' ? strtotime($lastDate) : false;

        $dateRange = [
            'firstMonth' => $firstTs !== false ? date('F', $firstTs) : '',
            'firstDay'   => $firstTs !== false ? date('d', $firstTs) : '',
            'lastMonth'  => $lastTs !== false ? date('F', $lastTs) : '',
            'lastDay'    => $lastTs !== false ? date('d', $lastTs) : '',
        ];

        // Per provider, build dayPairs: list of {left, right} pairs where
        // right is the date 4 days after left. Up to 4 pairs (the legacy
        // breaks at iter 4).
        $providersGrid = [];
        foreach ($providers as $provider) {
            $providerIdRaw = $provider['id'] ?? null;
            $providerId = is_int($providerIdRaw) || is_string($providerIdRaw) ? (int) $providerIdRaw : 0;

            $dayPairs = [];
            $loopcount = 0;
            foreach ($aEvents as $date => $events) {
                if ($loopcount >= 4) {
                    break;
                }
                $loopcount++;

                $dateTs = strtotime($date);
                $datePlusFourTs = $dateTs !== false ? $dateTs + (4 * 86400) : false;
                $datePlusFour = $datePlusFourTs !== false ? date('Y-m-d', $datePlusFourTs) : '';

                $leftEvents = $this->decorateWeekPrintEventsForDay($events, $date, $providerId, $calendarApptStyle, $tplImagePath);
                $rightDayEvents = $aEvents[$datePlusFour] ?? [];
                $rightEvents = $this->decorateWeekPrintEventsForDay($rightDayEvents, $datePlusFour, $providerId, $calendarApptStyle, $tplImagePath);

                $left = [
                    'weekdayLabel' => $dateTs !== false ? date('D', $dateTs) : '',
                    'dateMD'       => $dateTs !== false ? date('m/d', $dateTs) : '',
                    'events'       => $leftEvents,
                ];
                $right = isset($aEvents[$datePlusFour]) && $datePlusFourTs !== false
                    ? [
                        'weekdayLabel' => date('D', $datePlusFourTs),
                        'dateMD'       => date('m/d', $datePlusFourTs),
                        'events'       => $rightEvents,
                    ]
                    : null;

                $dayPairs[] = ['left' => $left, 'right' => $right];
            }

            $providersGrid[] = [
                'id'       => $providerId,
                'fname'    => is_string($provider['fname'] ?? null) ? $provider['fname'] : '',
                'lname'    => is_string($provider['lname'] ?? null) ? $provider['lname'] : '',
                'dayPairs' => $dayPairs,
            ];
        }

        return [
            'providers'         => $providersGrid,
            'dowList'           => $this->viewModel->dayOfWeekList(),
            'A_SHORT_DAY_NAMES' => $shortDayNames,
            'dateRange'         => $dateRange,
            'currentMonthMini'  => $currentMini,
            'nextMonthMini'     => $nextMini,
        ];
    }

    /**
     * Decorate one day's events for a single provider in week_print.
     * Skips events for other providers, skips empty eids, then applies
     * buildWeekPrintEventContent.
     *
     * @param  list<array<string, mixed>> $events
     * @return list<array<string, mixed>>
     */
    private function decorateWeekPrintEventsForDay(
        array $events,
        string $date,
        int $providerId,
        int $calendarApptStyle,
        string $tplImagePath
    ): array {
        $decorated = [];
        $eventDateYmd = substr($date, 0, 4) . substr($date, 5, 2) . substr($date, 8, 2);

        foreach ($events as $event) {
            $aidRaw = $event['aid'] ?? null;
            $aid = is_int($aidRaw) || is_string($aidRaw) ? (int) $aidRaw : 0;
            if ($aid !== 0 && $aid !== $providerId) {
                continue;
            }

            $eidRaw = $event['eid'] ?? null;
            if (!is_int($eidRaw) && !is_string($eidRaw)) {
                continue;
            }
            if ($eidRaw === '' || $eidRaw === 0) {
                continue;
            }

            $catidRaw = $event['catid'] ?? 0;
            $catid = is_int($catidRaw) || is_string($catidRaw) ? (int) $catidRaw : 0;

            $evtClass = $this->viewModel->eventClassForCategory($catid);
            $overrideRaw = $event['eventViewClass'] ?? null;
            if (is_string($overrideRaw)) {
                $evtClass = $overrideRaw;
            }

            $bodyContent = $this->viewModel->buildWeekPrintEventContent(
                $event,
                $date,
                $calendarApptStyle,
                $tplImagePath
            );

            $decorated[] = [
                'eid'         => $eidRaw,
                'eventDate'   => $eventDateYmd,
                'evtClass'    => $evtClass,
                'catcolor'    => is_string($event['catcolor'] ?? null) ? $event['catcolor'] : '',
                'bodyContent' => $bodyContent,
            ];
        }

        return $decorated;
    }
}
