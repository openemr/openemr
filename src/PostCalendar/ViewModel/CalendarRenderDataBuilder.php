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
     * Build the render-data array for `day_print/outlook_ajax_template.html.twig`.
     *
     * First timed view through the builder — brings the geometry +
     * overlap pipeline online. Per-event work runs:
     *   normalizeAllDayEvent -> extendInEventDuration (catid 2 only) ->
     *   computeEventGeometry -> overlap-positions lookup.
     *
     * @param  array<string, list<array<string, mixed>>> $aEvents
     * @param  list<array<string, mixed>>                $providers
     * @param  list<array{hour: int|string, minute: int|string, mer?: string}> $times
     * @param  list<string>                              $shortDayNames
     *
     * @return array<string, mixed>
     */
    public function buildDayPrintRenderData(
        array $aEvents,
        array $providers,
        array $times,
        int $intervalMinutes,
        string $dateYmd,
        array $shortDayNames,
        int $calendarApptStyle,
        string $tplImagePath,
        bool $isTwelveHourFormat
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

        // Clinic window in minutes-from-midnight, derived from the
        // first and last entries of $times. Same derivation the legacy
        // does inline in every timed-view template.
        $firstTime = $times[0] ?? ['hour' => 0, 'minute' => 0];
        $lastTime = $times[count($times) - 1] ?? ['hour' => 0, 'minute' => 0];
        $clinicStartMin = ((int) $firstTime['hour']) * 60 + ((int) $firstTime['minute']);
        $clinicEndMin = ((int) $lastTime['hour']) * 60 + ((int) $lastTime['minute']);

        $timeslotHeightVal = 20;
        $timeslotHeightUnit = 'px';
        $timeslotCss = $timeslotHeightVal . $timeslotHeightUnit;

        // The displayed date is the only date in $aEvents for day_print
        // (the controller passes a single-day events list).
        $dates = array_keys($aEvents);
        $eventDate = $dates[0] ?? '';
        $eventTs = $eventDate !== '' ? strtotime($eventDate) : false;
        $dateHeader = [
            'dateLabel'     => $eventTs !== false ? date('d F Y', $eventTs) : '',
            'weekdayLabel'  => $eventTs !== false ? date('l', $eventTs) : '',
        ];
        $eventDateYmd = $eventDate !== ''
            ? substr($eventDate, 0, 4) . substr($eventDate, 5, 2) . substr($eventDate, 8, 2)
            : '';

        // timeRows for the times-column display. Each row carries the
        // displayLabel (already 12h-shifted via displayStartHour where
        // applicable) plus isOnTheHour for the boldface treatment.
        $timeRows = [];
        foreach ($times as $slot) {
            $hourInt = (int) $slot['hour'];
            $minuteInt = (int) $slot['minute'];
            $minuteStr = sprintf('%02d', $minuteInt);
            $displayHour = $this->viewModel->displayStartHour(sprintf('%02d:00:00', $hourInt), $isTwelveHourFormat);
            $startampm = ($slot['mer'] ?? '') === 'pm' ? 2 : 1;
            $timeRows[] = [
                'hour'         => $hourInt,
                'minute'       => $minuteInt,
                'startampm'    => $startampm,
                'isOnTheHour'  => $minuteInt === 0,
                'displayLabel' => $displayHour . ':' . $minuteStr,
            ];
        }

        // Per-provider decoration. Each provider gets a list of decorated
        // events with geometry, overlap-positions, and bodyContent.
        $providersGrid = [];
        foreach ($providers as $provider) {
            $providerIdRaw = $provider['id'] ?? null;
            $providerId = is_int($providerIdRaw) || is_string($providerIdRaw) ? (int) $providerIdRaw : 0;

            $rawDayEvents = $aEvents[$eventDate] ?? [];

            $overlapPositions = $this->viewModel->detectEventOverlap(
                $rawDayEvents,
                $times,
                $intervalMinutes,
                $providerId
            );

            $decoratedEvents = $this->decorateDayPrintEvents(
                $rawDayEvents,
                $eventDateYmd,
                $eventDate,
                $providerId,
                $times,
                $intervalMinutes,
                $clinicStartMin,
                $clinicEndMin,
                $timeslotHeightVal,
                $timeslotHeightUnit,
                $overlapPositions,
                $calendarApptStyle,
                $tplImagePath,
                $isTwelveHourFormat
            );

            $providersGrid[] = [
                'id'       => $providerId,
                'fname'    => is_string($provider['fname'] ?? null) ? $provider['fname'] : '',
                'lname'    => is_string($provider['lname'] ?? null) ? $provider['lname'] : '',
                'username' => is_string($provider['username'] ?? null) ? $provider['username'] : '',
                'classForWeekend' => 'work-day', // print views don't legacy-distinguish; preserved as work-day
                'events'   => $decoratedEvents,
            ];
        }

        return [
            'providers'         => $providersGrid,
            'dowList'           => $this->viewModel->dayOfWeekList(),
            'A_SHORT_DAY_NAMES' => $shortDayNames,
            'dateHeader'        => $dateHeader,
            'currentMonthMini'  => $currentMini,
            'nextMonthMini'     => $nextMini,
            'timeRows'          => $timeRows,
            'timeslotCss'       => $timeslotCss,
        ];
    }

    /**
     * Decorate one day's events for one provider in day_print, including
     * geometry + overlap + IN-event lookahead.
     *
     * @param  list<array<string, mixed>> $events
     * @param  list<array{hour: int|string, minute: int|string}> $times
     * @param  array<int|string, array{width: float, leftpos: float}> $overlapPositions
     * @return list<array<string, mixed>>
     */
    private function decorateDayPrintEvents(
        array $events,
        string $eventDateYmd,
        string $eventDate,
        int $providerId,
        array $times,
        int $intervalMinutes,
        int $clinicStartMin,
        int $clinicEndMin,
        int $timeslotHeightVal,
        string $timeslotHeightUnit,
        array $overlapPositions,
        int $calendarApptStyle,
        string $tplImagePath,
        bool $isTwelveHourFormat
    ): array {
        $decorated = [];
        $firstTimeSlot = $times[0] ?? ['hour' => 0, 'minute' => 0];

        foreach ($events as $rawEvent) {
            $aidRaw = $rawEvent['aid'] ?? null;
            $aid = is_int($aidRaw) || is_string($aidRaw) ? (int) $aidRaw : 0;
            if ($aid !== 0 && $aid !== $providerId) {
                continue;
            }

            $eidRaw = $rawEvent['eid'] ?? null;
            if (!is_int($eidRaw) && !is_string($eidRaw)) {
                continue;
            }
            if ($eidRaw === '' || $eidRaw === 0) {
                continue;
            }

            // Per-event pipeline: all-day normalization -> IN duration
            // extension -> geometry -> overlap-positions.
            $event = $this->viewModel->normalizeAllDayEvent(
                $rawEvent,
                $firstTimeSlot,
                $clinicStartMin,
                $clinicEndMin
            );
            $event = $this->viewModel->extendInEventDuration(
                $event,
                $events,
                $providerId,
                $clinicEndMin
            );

            $startTime = is_string($event['startTime'] ?? null) ? $event['startTime'] : '00:00:00';
            $startMin = $this->viewModel->startTimeToMinutes($startTime);
            $durationSecRaw = $event['duration'] ?? 0;
            $durationSec = is_int($durationSecRaw) || is_string($durationSecRaw) ? (int) $durationSecRaw : 0;
            $durationMin = (int) ($durationSec / 60);

            $geometry = $this->viewModel->computeEventGeometry(
                $startMin,
                $durationMin,
                $clinicStartMin,
                $intervalMinutes,
                $timeslotHeightVal,
                $timeslotHeightUnit
            );

            $catidRaw = $event['catid'] ?? 0;
            $catid = is_int($catidRaw) || is_string($catidRaw) ? (int) $catidRaw : 0;
            $evtClass = $this->viewModel->eventClassForCategory($catid);
            $overrideRaw = $event['eventViewClass'] ?? null;
            if (is_string($overrideRaw)) {
                $evtClass = $overrideRaw;
            }

            $position = $overlapPositions[$eidRaw] ?? null;
            $divWidthCss = $position !== null ? 'width: ' . $position['width'] . '%' : '';
            $divLeftCss = $position !== null ? 'left: ' . $position['leftpos'] . '%' : '';

            $bodyContent = $this->viewModel->buildDayPrintEventContent(
                $event,
                $calendarApptStyle,
                $isTwelveHourFormat,
                $tplImagePath
            );

            $entry = [
                'eid'         => $eidRaw,
                'catid'       => $catid,
                'eventDate'   => $eventDateYmd,
                'evtClass'    => $evtClass,
                'catcolor'    => is_string($event['catcolor'] ?? null) ? $event['catcolor'] : '',
                'evtTop'      => $geometry['top'],
                'evtHeight'   => $geometry['height'],
                'divWidth'    => $divWidthCss,
                'divLeft'     => $divLeftCss,
                'bodyContent' => $bodyContent,
            ];

            // IN events (catid 2) need the time-label DIV above the
            // body. The label sits one timeslot above the body.
            if ($catid === 2) {
                $inLabelTopPx = ($geometry['startInterval'] - 1) * $timeslotHeightVal;
                $entry['inLabelTop']     = $inLabelTopPx . $timeslotHeightUnit;
                $entry['inLabelHeight']  = $timeslotHeightVal . $timeslotHeightUnit;
                $startH = (int) substr($startTime, 0, 2);
                $startM = substr($startTime, 3, 2);
                $dispStartH = $this->viewModel->displayStartHour($startTime, $isTwelveHourFormat);
                $entry['inLabelContent'] = htmlspecialchars($dispStartH . ':' . $startM, ENT_QUOTES) . ' ' . $bodyContent;
            }

            $decorated[] = $entry;
        }

        return $decorated;
    }

    /**
     * Build the render-data array for `month/ajax_template.html.twig`.
     *
     * Screen view — adds the provider-picker + date-nav scaffolding
     * the print views don't carry, plus the per-event facility-row
     * lookup that drives the 3-way display dispatch
     * (normal / current-facility / filtered-other).
     *
     * The consumer is responsible for the monthSelectorHtml string
     * (ob_start the legacy require) and the chevron icon classes
     * (computed from session language_direction).
     *
     * @param  array<string, list<array<string, mixed>>> $aEvents
     * @param  list<array<string, mixed>>                $providers   Providers selected to be rendered (subset of provinfo).
     * @param  list<array<string, mixed>>                $provinfo    All providers (for the picker dropdown).
     * @param  list<array<string, mixed>>                $facilities
     * @param  list<string>                              $shortDayNames
     *
     * @return array<string, mixed>
     */
    public function buildMonthScreenRenderData(
        array $aEvents,
        array $providers,
        array $provinfo,
        array $facilities,
        string $dateYmd,
        array $shortDayNames,
        int $pcFacility,
        int $calendarApptStyle,
        string $tplImagePath,
        string $webroot,
        string $prevMonthUrl,
        string $nextMonthUrl,
        string $chevronIconLeft,
        string $chevronIconRight,
        string $monthSelectorHtml,
        bool $showAllFacilitiesOption,
        string $currentMonthLabel
    ): array {
        $year = substr($dateYmd, 0, 4);
        $month = substr($dateYmd, 4, 2);
        $currentMini = $this->viewModel->buildMiniCalendar(
            "{$year}-{$month}-15",
            $dateYmd
        );

        $isToday = $dateYmd === date('Ymd');

        // Selected provider usernames for the multi-select default.
        $selectedUsernames = [];
        foreach ($providers as $provider) {
            $uname = $provider['username'] ?? null;
            if (is_string($uname)) {
                $selectedUsernames[] = $uname;
            }
        }

        // Prev/next month YYYYMMDD with "stay on same day if possible"
        // (legacy block computes this with checkdate decrement).
        $cDay = (int) substr($dateYmd, 6, 2);
        $cMonth = (int) $month;
        $cYear = (int) $year;
        $prevMonthYmd = $this->computeAdjacentMonth($cYear, $cMonth, $cDay, -1);
        $nextMonthYmd = $this->computeAdjacentMonth($cYear, $cMonth, $cDay, 1);
        $prevMonthTs = strtotime($prevMonthYmd);
        $nextMonthTs = strtotime($nextMonthYmd);
        $prevMonthName = $prevMonthTs !== false ? date('F', $prevMonthTs) : '';
        $nextMonthName = $nextMonthTs !== false ? date('F', $nextMonthTs) : '';

        // Per-provider day grid. Each provider gets a table; each table
        // has a list of day cells. Pre-computed isStartOfWeek /
        // isEndOfWeek flags so the template knows when to open/close
        // <tr> tags without re-doing date math.
        $dowList = $this->viewModel->dayOfWeekList();
        $providersGrid = [];
        foreach ($providers as $provider) {
            $providerIdRaw = $provider['id'] ?? null;
            $providerId = is_int($providerIdRaw) || is_string($providerIdRaw) ? (int) $providerIdRaw : 0;

            $days = [];
            foreach ($aEvents as $date => $dateEvents) {
                $dateTs = strtotime($date);
                if ($dateTs === false) {
                    continue;
                }
                $dayOfWeek = (int) date('w', $dateTs);
                $eventDateYmd = substr($date, 0, 4) . substr($date, 5, 2) . substr($date, 8, 2);

                $isWeekend = $dayOfWeek === 0 || $dayOfWeek === 6;
                $decoratedEvents = $this->decorateMonthScreenEvents(
                    $dateEvents,
                    $date,
                    $eventDateYmd,
                    $providerId,
                    $pcFacility,
                    $calendarApptStyle,
                    $tplImagePath,
                    $webroot
                );

                $days[] = [
                    'dateYmd'         => $eventDateYmd,
                    'dateDisplay'     => date('d', $dateTs),
                    'dateAttrTitle'   => date('d M Y', $dateTs),
                    'gotoUrl'         => '#', // consumer can override with pnModURL if needed
                    'classForWeekend' => $isWeekend ? 'weekend-day' : 'work-day',
                    'isStartOfWeek'   => $dayOfWeek === $dowList[0],
                    'isEndOfWeek'     => $dayOfWeek === $dowList[6],
                    'events'          => $decoratedEvents,
                ];
            }

            $dateHeaderLabels = [];
            foreach (array_slice(array_keys($aEvents), 0, 7) as $headerDate) {
                $hTs = strtotime($headerDate);
                $dateHeaderLabels[] = $hTs !== false ? date('D', $hTs) : '';
            }

            $providersGrid[] = [
                'id'                => $providerId,
                'fname'             => is_string($provider['fname'] ?? null) ? $provider['fname'] : '',
                'lname'             => is_string($provider['lname'] ?? null) ? $provider['lname'] : '',
                'dateHeaderLabels'  => $dateHeaderLabels,
                'days'              => $days,
            ];
        }

        return [
            'viewtype'                => 'month',
            'Date'                    => $dateYmd,
            'currentMonthLabel'       => $currentMonthLabel,
            'isToday'                 => $isToday,
            'PREV_MONTH_URL'          => $prevMonthUrl,
            'NEXT_MONTH_URL'          => $nextMonthUrl,
            'chevron_icon_left'       => $chevronIconLeft,
            'chevron_icon_right'      => $chevronIconRight,
            'dowList'                 => $dowList,
            'A_SHORT_DAY_NAMES'       => $shortDayNames,
            'prevMonth'               => $prevMonthYmd,
            'nextMonth'               => $nextMonthYmd,
            'prevMonthName'           => $prevMonthName,
            'nextMonthName'           => $nextMonthName,
            'currentMiniCal'          => $currentMini,
            'monthSelectorHtml'       => $monthSelectorHtml,
            'showFacilitySelect'      => count($facilities) > 1,
            'showAllFacilitiesOption' => $showAllFacilitiesOption,
            'pc_facility'             => $pcFacility,
            'facilities'              => $facilities,
            'provinfo'                => $provinfo,
            'selectedUsernames'       => $selectedUsernames,
            'providersGrid'           => $providersGrid,
            'webroot'                 => $webroot,
        ];
    }

    /**
     * Decorate one day's events for one provider in month-screen.
     * Skips catid 2/3 (rendered nowhere in month). Looks up the
     * event's facility row, then computes the 3-way display dispatch
     * (matching pc_facility / not matching / no filter) — the
     * template just renders whichever variant the consumer chose.
     *
     * @param  list<array<string, mixed>> $events
     * @return list<array<string, mixed>>
     */
    private function decorateMonthScreenEvents(
        array $events,
        string $eventDate,
        string $eventDateYmd,
        int $providerId,
        int $pcFacility,
        int $calendarApptStyle,
        string $tplImagePath,
        string $webroot
    ): array {
        $decorated = [];
        foreach ($events as $event) {
            $aidRaw = $event['aid'] ?? null;
            $aid = is_int($aidRaw) || is_string($aidRaw) ? (int) $aidRaw : 0;
            if ($aid !== 0 && $aid !== $providerId) {
                continue;
            }

            $catidRaw = $event['catid'] ?? 0;
            $catid = is_int($catidRaw) || is_string($catidRaw) ? (int) $catidRaw : 0;
            if ($catid === 2 || $catid === 3) {
                continue;
            }

            $eidRaw = $event['eid'] ?? null;
            if (!is_int($eidRaw) && !is_string($eidRaw)) {
                continue;
            }

            $evtClass = $this->viewModel->eventClassForCategory($catid);
            $overrideRaw = $event['eventViewClass'] ?? null;
            if (is_string($overrideRaw)) {
                $evtClass = $overrideRaw;
            }

            $catcolor = is_string($event['catcolor'] ?? null) ? $event['catcolor'] : '';
            $facilityRow = is_array($event['facility_row'] ?? null) ? $event['facility_row'] : null;
            $facilityId = is_array($facilityRow) && isset($facilityRow['id'])
                ? (is_int($facilityRow['id']) || is_string($facilityRow['id']) ? (int) $facilityRow['id'] : 0)
                : 0;

            $built = $this->viewModel->buildMonthScreenEventContent(
                $event,
                $eventDate,
                $calendarApptStyle,
                $tplImagePath,
                $webroot
            );

            // 3-way facility-filter dispatch.
            if ($pcFacility === 0) {
                $displayBgColor = $catcolor;
                $displayContentHtml = $built['content'];
            } elseif ($pcFacility === $facilityId) {
                $displayBgColor = $catcolor;
                $displayContentHtml = $built['content'];
            } else {
                $displayBgColor = 'var(--gray300)';
                $facilityName = is_array($facilityRow) && is_string($facilityRow['name'] ?? null)
                    ? $facilityRow['name']
                    : '';
                $displayContentHtml = "<span class='text-center text-danger'>" . attr($facilityName) . '</span>';
            }

            $pccattype = is_string($event['pccattype'] ?? null) ? $event['pccattype'] : '';

            $decorated[] = [
                'eid'                 => $eidRaw,
                'eventDate'           => $eventDateYmd,
                'pccattype'           => $pccattype,
                'evtClass'            => $evtClass,
                'displayBgColor'      => $displayBgColor,
                'displayContentHtml'  => $displayContentHtml,
                'tooltip'             => $built['tooltip'],
            ];
        }

        return $decorated;
    }

    /**
     * Build the render-data array for `day/ajax_template.html.twig`.
     *
     * Combines month-screen's picker + nav scaffolding with day_print's
     * geometry pipeline. The big difference from day_print is the
     * screen-specific per-event decoration (groups, picture hover,
     * 3-way facility filter) handled by buildDayScreenEventContent.
     *
     * @param  array<string, list<array<string, mixed>>> $aEvents
     * @param  list<array<string, mixed>>                $providers
     * @param  list<array<string, mixed>>                $provinfo
     * @param  list<array<string, mixed>>                $facilities
     * @param  list<array{hour: int|string, minute: int|string, mer?: string}> $times
     * @param  list<string>                              $shortDayNames
     *
     * @return array<string, mixed>
     */
    public function buildDayScreenRenderData(
        array $aEvents,
        array $providers,
        array $provinfo,
        array $facilities,
        array $times,
        int $intervalMinutes,
        string $dateYmd,
        array $shortDayNames,
        int $pcFacility,
        int $calendarApptStyle,
        string $tplImagePath,
        string $webroot,
        string $prevDayUrl,
        string $nextDayUrl,
        string $chevronIconLeft,
        string $chevronIconRight,
        string $monthSelectorHtml,
        bool $showAllFacilitiesOption,
        string $dayHeaderLabel,
        bool $isTwelveHourFormat
    ): array {
        $year = substr($dateYmd, 0, 4);
        $month = substr($dateYmd, 4, 2);
        $currentMini = $this->viewModel->buildMiniCalendar("{$year}-{$month}-15", $dateYmd);

        $isToday = $dateYmd === date('Ymd');

        $selectedUsernames = $this->extractSelectedUsernames($providers);

        $cDay = (int) substr($dateYmd, 6, 2);
        $cMonth = (int) $month;
        $cYear = (int) $year;
        $prevMonthYmd = $this->computeAdjacentMonth($cYear, $cMonth, $cDay, -1);
        $nextMonthYmd = $this->computeAdjacentMonth($cYear, $cMonth, $cDay, 1);
        $prevMonthName = $this->ymdMonthName($prevMonthYmd);
        $nextMonthName = $this->ymdMonthName($nextMonthYmd);

        $firstTime = $times[0] ?? ['hour' => 0, 'minute' => 0];
        $lastTime = $times[count($times) - 1] ?? ['hour' => 0, 'minute' => 0];
        $clinicStartMin = ((int) $firstTime['hour']) * 60 + ((int) $firstTime['minute']);
        $clinicEndMin = ((int) $lastTime['hour']) * 60 + ((int) $lastTime['minute']);

        $timeslotHeightVal = 20;
        $timeslotHeightUnit = 'px';
        $timeslotCss = $timeslotHeightVal . $timeslotHeightUnit;
        $timeRows = $this->buildTimeRows($times, $isTwelveHourFormat);

        $dates = array_keys($aEvents);
        $eventDate = $dates[0] ?? '';
        $eventDateYmd = $eventDate !== ''
            ? substr($eventDate, 0, 4) . substr($eventDate, 5, 2) . substr($eventDate, 8, 2)
            : '';

        $providersGrid = [];
        foreach ($providers as $provider) {
            $providerIdRaw = $provider['id'] ?? null;
            $providerId = is_int($providerIdRaw) || is_string($providerIdRaw) ? (int) $providerIdRaw : 0;

            $rawDayEvents = $aEvents[$eventDate] ?? [];

            $overlapPositions = $this->viewModel->detectEventOverlap(
                $rawDayEvents,
                $times,
                $intervalMinutes,
                $providerId
            );

            $decoratedEvents = $this->decorateDayScreenEvents(
                $rawDayEvents,
                $eventDateYmd,
                $eventDate,
                $providerId,
                $pcFacility,
                $times,
                $intervalMinutes,
                $clinicStartMin,
                $clinicEndMin,
                $timeslotHeightVal,
                $timeslotHeightUnit,
                $overlapPositions,
                $calendarApptStyle,
                $tplImagePath,
                $webroot,
                $isTwelveHourFormat
            );

            $providersGrid[] = [
                'id'              => $providerId,
                'fname'           => is_string($provider['fname'] ?? null) ? $provider['fname'] : '',
                'lname'           => is_string($provider['lname'] ?? null) ? $provider['lname'] : '',
                'username'        => is_string($provider['username'] ?? null) ? $provider['username'] : '',
                'classForWeekend' => 'work-day',
                'events'          => $decoratedEvents,
            ];
        }

        return [
            'viewtype'                => 'day',
            'Date'                    => $dateYmd,
            'dayHeaderLabel'          => $dayHeaderLabel,
            'isToday'                 => $isToday,
            'PREV_DAY_URL'            => $prevDayUrl,
            'NEXT_DAY_URL'            => $nextDayUrl,
            'chevron_icon_left'       => $chevronIconLeft,
            'chevron_icon_right'      => $chevronIconRight,
            'dowList'                 => $this->viewModel->dayOfWeekList(),
            'A_SHORT_DAY_NAMES'       => $shortDayNames,
            'prevMonth'               => $prevMonthYmd,
            'nextMonth'               => $nextMonthYmd,
            'prevMonthName'           => $prevMonthName,
            'nextMonthName'           => $nextMonthName,
            'currentMiniCal'          => $currentMini,
            'monthSelectorHtml'       => $monthSelectorHtml,
            'showFacilitySelect'      => count($facilities) > 1,
            'showAllFacilitiesOption' => $showAllFacilitiesOption,
            'pc_facility'             => $pcFacility,
            'facilities'              => $facilities,
            'provinfo'                => $provinfo,
            'selectedUsernames'       => $selectedUsernames,
            'timeRows'                => $timeRows,
            'timeslotCss'             => $timeslotCss,
            'providers'               => $providersGrid,
            'webroot'                 => $webroot,
        ];
    }

    /**
     * Build the render-data array for `week/ajax_template.html.twig`.
     *
     * Similar to day-screen but with 7 day-columns per provider. Each
     * column gets the same per-event treatment day-screen does
     * (geometry, overlap, IN-event two-DIV), with the week-specific
     * apptToggle class suffix on the wrapping span.
     *
     * @param  array<string, list<array<string, mixed>>> $aEvents
     * @param  list<array<string, mixed>>                $providers
     * @param  list<array<string, mixed>>                $provinfo
     * @param  list<array<string, mixed>>                $facilities
     * @param  list<array{hour: int|string, minute: int|string, mer?: string}> $times
     * @param  list<string>                              $shortDayNames
     *
     * @return array<string, mixed>
     */
    public function buildWeekScreenRenderData(
        array $aEvents,
        array $providers,
        array $provinfo,
        array $facilities,
        array $times,
        int $intervalMinutes,
        string $dateYmd,
        array $shortDayNames,
        int $pcFacility,
        int $calendarApptStyle,
        string $tplImagePath,
        string $webroot,
        string $prevWeekUrl,
        string $nextWeekUrl,
        string $chevronIconLeft,
        string $chevronIconRight,
        string $monthSelectorHtml,
        bool $showAllFacilitiesOption,
        string $weekHeaderLabel,
        bool $isTwelveHourFormat,
        string $apptToggle = ''
    ): array {
        $year = substr($dateYmd, 0, 4);
        $month = substr($dateYmd, 4, 2);
        $currentMini = $this->viewModel->buildMiniCalendar("{$year}-{$month}-15", $dateYmd);

        $isToday = $dateYmd === date('Ymd');
        $selectedUsernames = $this->extractSelectedUsernames($providers);

        $cDay = (int) substr($dateYmd, 6, 2);
        $cMonth = (int) $month;
        $cYear = (int) $year;
        $prevMonthYmd = $this->computeAdjacentMonth($cYear, $cMonth, $cDay, -1);
        $nextMonthYmd = $this->computeAdjacentMonth($cYear, $cMonth, $cDay, 1);
        $prevMonthName = $this->ymdMonthName($prevMonthYmd);
        $nextMonthName = $this->ymdMonthName($nextMonthYmd);

        $firstTime = $times[0] ?? ['hour' => 0, 'minute' => 0];
        $lastTime = $times[count($times) - 1] ?? ['hour' => 0, 'minute' => 0];
        $clinicStartMin = ((int) $firstTime['hour']) * 60 + ((int) $firstTime['minute']);
        $clinicEndMin = ((int) $lastTime['hour']) * 60 + ((int) $lastTime['minute']);

        $timeslotHeightVal = 20;
        $timeslotHeightUnit = 'px';
        $timeslotCss = $timeslotHeightVal . $timeslotHeightUnit;
        $timeRows = $this->buildTimeRows($times, $isTwelveHourFormat);

        $providersGrid = [];
        foreach ($providers as $provider) {
            $providerIdRaw = $provider['id'] ?? null;
            $providerId = is_int($providerIdRaw) || is_string($providerIdRaw) ? (int) $providerIdRaw : 0;

            $dayColumns = [];
            foreach ($aEvents as $columnDate => $dateEvents) {
                $columnTs = strtotime($columnDate);
                if ($columnTs === false) {
                    continue;
                }
                $columnYmd = substr($columnDate, 0, 4) . substr($columnDate, 5, 2) . substr($columnDate, 8, 2);
                $dayOfWeek = (int) date('w', $columnTs);
                $isWeekend = $dayOfWeek === 0 || $dayOfWeek === 6;

                $overlapPositions = $this->viewModel->detectEventOverlap(
                    $dateEvents,
                    $times,
                    $intervalMinutes,
                    $providerId
                );

                $columnEvents = $this->decorateWeekScreenEvents(
                    $dateEvents,
                    $columnYmd,
                    $columnDate,
                    $providerId,
                    $pcFacility,
                    $times,
                    $intervalMinutes,
                    $clinicStartMin,
                    $clinicEndMin,
                    $timeslotHeightVal,
                    $timeslotHeightUnit,
                    $overlapPositions,
                    $calendarApptStyle,
                    $tplImagePath,
                    $webroot,
                    $apptToggle
                );

                $dayColumns[] = [
                    'dateYmd'         => $columnYmd,
                    'dateAttrTitle'   => date('d M Y', $columnTs),
                    'gotoUrl'         => '#', // overridden by consumer via pnModURL if needed
                    'dayHeaderLabel'  => date('D m/d', $columnTs),
                    'classForWeekend' => $isWeekend ? 'weekend-day' : 'work-day',
                    'isCurrentDay'    => $columnYmd === $dateYmd,
                    'events'          => $columnEvents,
                ];
            }

            $providersGrid[] = [
                'id'         => $providerId,
                'fname'      => is_string($provider['fname'] ?? null) ? $provider['fname'] : '',
                'lname'      => is_string($provider['lname'] ?? null) ? $provider['lname'] : '',
                'username'   => is_string($provider['username'] ?? null) ? $provider['username'] : '',
                'dayColumns' => $dayColumns,
            ];
        }

        return [
            'viewtype'                => 'week',
            'Date'                    => $dateYmd,
            'weekHeaderLabel'         => $weekHeaderLabel,
            'isToday'                 => $isToday,
            'PREV_WEEK_URL'           => $prevWeekUrl,
            'NEXT_WEEK_URL'           => $nextWeekUrl,
            'chevron_icon_left'       => $chevronIconLeft,
            'chevron_icon_right'      => $chevronIconRight,
            'dowList'                 => $this->viewModel->dayOfWeekList(),
            'A_SHORT_DAY_NAMES'       => $shortDayNames,
            'prevMonth'               => $prevMonthYmd,
            'nextMonth'               => $nextMonthYmd,
            'prevMonthName'           => $prevMonthName,
            'nextMonthName'           => $nextMonthName,
            'currentMiniCal'          => $currentMini,
            'monthSelectorHtml'       => $monthSelectorHtml,
            'showFacilitySelect'      => count($facilities) > 1,
            'showAllFacilitiesOption' => $showAllFacilitiesOption,
            'pc_facility'             => $pcFacility,
            'facilities'              => $facilities,
            'provinfo'                => $provinfo,
            'selectedUsernames'       => $selectedUsernames,
            'timeRows'                => $timeRows,
            'timeslotCss'             => $timeslotCss,
            'providers'               => $providersGrid,
            'webroot'                 => $webroot,
        ];
    }

    /**
     * Decorate one day's events for one provider in week-screen. Same
     * structure as decorateDayScreenEvents but calls
     * buildWeekScreenEventContent (which adds the apptToggle wrapper
     * suffix and the show-appointment toggle anchor for patient appts).
     *
     * @param  list<array<string, mixed>> $events
     * @param  list<array{hour: int|string, minute: int|string}> $times
     * @param  array<int|string, array{width: float, leftpos: float}> $overlapPositions
     * @return list<array<string, mixed>>
     */
    private function decorateWeekScreenEvents(
        array $events,
        string $eventDateYmd,
        string $eventDate,
        int $providerId,
        int $pcFacility,
        array $times,
        int $intervalMinutes,
        int $clinicStartMin,
        int $clinicEndMin,
        int $timeslotHeightVal,
        string $timeslotHeightUnit,
        array $overlapPositions,
        int $calendarApptStyle,
        string $tplImagePath,
        string $webroot,
        string $apptToggle
    ): array {
        $decorated = [];
        $firstTimeSlot = $times[0] ?? ['hour' => 0, 'minute' => 0];

        foreach ($events as $rawEvent) {
            $aidRaw = $rawEvent['aid'] ?? null;
            $aid = is_int($aidRaw) || is_string($aidRaw) ? (int) $aidRaw : 0;
            if ($aid !== 0 && $aid !== $providerId) {
                continue;
            }

            $eidRaw = $rawEvent['eid'] ?? null;
            if (!is_int($eidRaw) && !is_string($eidRaw)) {
                continue;
            }
            if ($eidRaw === '' || $eidRaw === 0) {
                continue;
            }

            $event = $this->viewModel->normalizeAllDayEvent(
                $rawEvent,
                $firstTimeSlot,
                $clinicStartMin,
                $clinicEndMin
            );
            $event = $this->viewModel->extendInEventDuration(
                $event,
                $events,
                $providerId,
                $clinicEndMin
            );

            $startTime = is_string($event['startTime'] ?? null) ? $event['startTime'] : '00:00:00';
            $startMin = $this->viewModel->startTimeToMinutes($startTime);
            $durationSecRaw = $event['duration'] ?? 0;
            $durationSec = is_int($durationSecRaw) || is_string($durationSecRaw) ? (int) $durationSecRaw : 0;
            $durationMin = (int) ($durationSec / 60);

            $geometry = $this->viewModel->computeEventGeometry(
                $startMin,
                $durationMin,
                $clinicStartMin,
                $intervalMinutes,
                $timeslotHeightVal,
                $timeslotHeightUnit
            );

            $catidRaw = $event['catid'] ?? 0;
            $catid = is_int($catidRaw) || is_string($catidRaw) ? (int) $catidRaw : 0;
            $evtClass = $this->viewModel->eventClassForCategory($catid);
            $overrideRaw = $event['eventViewClass'] ?? null;
            if (is_string($overrideRaw)) {
                $evtClass = $overrideRaw;
            }

            $position = $overlapPositions[$eidRaw] ?? null;
            $divWidthCss = $position !== null ? 'width: ' . $position['width'] . '%' : '';
            $divLeftCss = $position !== null ? 'left: ' . $position['leftpos'] . '%' : '';

            $built = $this->viewModel->buildWeekScreenEventContent(
                $event,
                $calendarApptStyle,
                $tplImagePath,
                $webroot,
                $apptToggle
            );
            if ($built['extraClass'] !== '') {
                $evtClass .= $built['extraClass'];
            }

            $catcolor = is_string($event['catcolor'] ?? null) ? $event['catcolor'] : '';
            $facilityRow = is_array($event['facility_row'] ?? null) ? $event['facility_row'] : null;
            $facilityId = is_array($facilityRow) && isset($facilityRow['id'])
                ? (is_int($facilityRow['id']) || is_string($facilityRow['id']) ? (int) $facilityRow['id'] : 0)
                : 0;
            if ($pcFacility === 0 || $pcFacility === $facilityId) {
                $displayBgColor = $catcolor;
                $displayContentHtml = $built['content'];
            } else {
                $facilityName = is_array($facilityRow) && is_string($facilityRow['name'] ?? null)
                    ? $facilityRow['name']
                    : '';
                $displayBgColor = 'var(--gray300)';
                $displayContentHtml = "<span class='text-center text-danger'>" . attr($facilityName) . '</span>';
            }

            $pccattype = is_string($event['pccattype'] ?? null) ? $event['pccattype'] : '';

            $entry = [
                'eid'                => $eidRaw,
                'catid'              => $catid,
                'eventDate'          => $eventDateYmd,
                'pccattype'          => $pccattype,
                'evtClass'           => $evtClass,
                'displayBgColor'     => $displayBgColor,
                'displayContentHtml' => $displayContentHtml,
                'tooltip'            => $built['tooltip'],
                'evtTopCss'          => $geometry['top'],
                'evtHeightCss'       => $geometry['height'],
                'divWidthCss'        => $divWidthCss,
                'divLeftCss'         => $divLeftCss,
            ];

            if ($catid === 2) {
                $inLabelTopPx = ($geometry['startInterval'] - 1) * $timeslotHeightVal;
                $entry['inLabelTopCss']    = $inLabelTopPx . $timeslotHeightUnit;
                $entry['inLabelHeightCss'] = $timeslotHeightVal . $timeslotHeightUnit;
                $startM = substr($startTime, 3, 2);
                $dispStartH = $this->viewModel->displayStartHour($startTime, false);
                $entry['inLabelContent']   = attr($dispStartH . ':' . $startM) . ' ' . $built['content'];
            }

            $decorated[] = $entry;
        }

        return $decorated;
    }

    /**
     * Decorate one day's events for one provider in day-screen.
     * Runs the full per-event pipeline (normalize, IN-extend, geometry,
     * overlap) plus the 3-way facility filter and the IN-event two-DIV
     * pattern bits.
     *
     * @param  list<array<string, mixed>> $events
     * @param  list<array{hour: int|string, minute: int|string}> $times
     * @param  array<int|string, array{width: float, leftpos: float}> $overlapPositions
     * @return list<array<string, mixed>>
     */
    private function decorateDayScreenEvents(
        array $events,
        string $eventDateYmd,
        string $eventDate,
        int $providerId,
        int $pcFacility,
        array $times,
        int $intervalMinutes,
        int $clinicStartMin,
        int $clinicEndMin,
        int $timeslotHeightVal,
        string $timeslotHeightUnit,
        array $overlapPositions,
        int $calendarApptStyle,
        string $tplImagePath,
        string $webroot,
        bool $isTwelveHourFormat
    ): array {
        $decorated = [];
        $firstTimeSlot = $times[0] ?? ['hour' => 0, 'minute' => 0];

        foreach ($events as $rawEvent) {
            $aidRaw = $rawEvent['aid'] ?? null;
            $aid = is_int($aidRaw) || is_string($aidRaw) ? (int) $aidRaw : 0;
            if ($aid !== 0 && $aid !== $providerId) {
                continue;
            }

            $eidRaw = $rawEvent['eid'] ?? null;
            if (!is_int($eidRaw) && !is_string($eidRaw)) {
                continue;
            }
            if ($eidRaw === '' || $eidRaw === 0) {
                continue;
            }

            $event = $this->viewModel->normalizeAllDayEvent(
                $rawEvent,
                $firstTimeSlot,
                $clinicStartMin,
                $clinicEndMin
            );
            $event = $this->viewModel->extendInEventDuration(
                $event,
                $events,
                $providerId,
                $clinicEndMin
            );

            $startTime = is_string($event['startTime'] ?? null) ? $event['startTime'] : '00:00:00';
            $startMin = $this->viewModel->startTimeToMinutes($startTime);
            $durationSecRaw = $event['duration'] ?? 0;
            $durationSec = is_int($durationSecRaw) || is_string($durationSecRaw) ? (int) $durationSecRaw : 0;
            $durationMin = (int) ($durationSec / 60);

            $geometry = $this->viewModel->computeEventGeometry(
                $startMin,
                $durationMin,
                $clinicStartMin,
                $intervalMinutes,
                $timeslotHeightVal,
                $timeslotHeightUnit
            );

            $catidRaw = $event['catid'] ?? 0;
            $catid = is_int($catidRaw) || is_string($catidRaw) ? (int) $catidRaw : 0;
            $evtClass = $this->viewModel->eventClassForCategory($catid);
            $overrideRaw = $event['eventViewClass'] ?? null;
            if (is_string($overrideRaw)) {
                $evtClass = $overrideRaw;
            }

            $position = $overlapPositions[$eidRaw] ?? null;
            $divWidthCss = $position !== null ? 'width: ' . $position['width'] . '%' : '';
            $divLeftCss = $position !== null ? 'left: ' . $position['leftpos'] . '%' : '';

            $built = $this->viewModel->buildDayScreenEventContent(
                $event,
                $calendarApptStyle,
                $tplImagePath,
                $webroot,
                $isTwelveHourFormat
            );
            // Append the group class if the content builder flagged it.
            if ($built['extraClass'] !== '') {
                $evtClass .= $built['extraClass'];
            }

            // 3-way facility-filter dispatch (same shape as month-screen).
            $catcolor = is_string($event['catcolor'] ?? null) ? $event['catcolor'] : '';
            $facilityRow = is_array($event['facility_row'] ?? null) ? $event['facility_row'] : null;
            $facilityId = is_array($facilityRow) && isset($facilityRow['id'])
                ? (is_int($facilityRow['id']) || is_string($facilityRow['id']) ? (int) $facilityRow['id'] : 0)
                : 0;
            if ($pcFacility === 0 || $pcFacility === $facilityId) {
                $displayBgColor = $catcolor;
                $displayContentHtml = $built['content'];
            } else {
                $facilityName = is_array($facilityRow) && is_string($facilityRow['name'] ?? null)
                    ? $facilityRow['name']
                    : '';
                $displayBgColor = 'var(--gray300)';
                $displayContentHtml = "<span class='text-center text-danger'>" . attr($facilityName) . '</span>';
            }

            $pccattype = is_string($event['pccattype'] ?? null) ? $event['pccattype'] : '';

            $entry = [
                'eid'                => $eidRaw,
                'catid'              => $catid,
                'eventDate'          => $eventDateYmd,
                'pccattype'          => $pccattype,
                'evtClass'           => $evtClass,
                'displayBgColor'     => $displayBgColor,
                'displayContentHtml' => $displayContentHtml,
                'tooltip'            => $built['tooltip'],
                'evtTopCss'          => $geometry['top'],
                'evtHeightCss'       => $geometry['height'],
                'divWidthCss'        => $divWidthCss,
                'divLeftCss'         => $divLeftCss,
            ];

            if ($catid === 2) {
                $inLabelTopPx = ($geometry['startInterval'] - 1) * $timeslotHeightVal;
                $entry['inLabelTopCss']    = $inLabelTopPx . $timeslotHeightUnit;
                $entry['inLabelHeightCss'] = $timeslotHeightVal . $timeslotHeightUnit;
                $startM = substr($startTime, 3, 2);
                $dispStartH = $this->viewModel->displayStartHour($startTime, $isTwelveHourFormat);
                $entry['inLabelContent']   = attr($dispStartH . ':' . $startM) . ' ' . $built['content'];
            }

            $decorated[] = $entry;
        }

        return $decorated;
    }

    /**
     * Extract the list of provider usernames from the selected-providers
     * input. Used by screen views to pre-select the right options in the
     * multi-select picker.
     *
     * @param  list<array<string, mixed>> $providers
     * @return list<string>
     */
    private function extractSelectedUsernames(array $providers): array
    {
        $names = [];
        foreach ($providers as $provider) {
            $uname = $provider['username'] ?? null;
            if (is_string($uname)) {
                $names[] = $uname;
            }
        }
        return $names;
    }

    /**
     * Format the localized month name of a YYYYMMDD string for the
     * mini-cal prev/next title attributes.
     */
    private function ymdMonthName(string $ymd): string
    {
        $ts = strtotime($ymd);
        return $ts !== false ? date('F', $ts) : '';
    }

    /**
     * Build the timeRows structure for timed-view templates. Each row
     * carries the consumer-formatted displayLabel + isOnTheHour bool
     * + startampm (1=am, 2=pm) for the newEvt() onclick handler.
     *
     * @param  list<array{hour: int|string, minute: int|string, mer?: string}> $times
     * @return list<array{hour:int, minute:int, startampm:int, isOnTheHour:bool, displayLabel:string}>
     */
    private function buildTimeRows(array $times, bool $isTwelveHourFormat): array
    {
        $rows = [];
        foreach ($times as $slot) {
            $hourInt = (int) $slot['hour'];
            $minuteInt = (int) $slot['minute'];
            $minuteStr = sprintf('%02d', $minuteInt);
            $displayHour = $this->viewModel->displayStartHour(sprintf('%02d:00:00', $hourInt), $isTwelveHourFormat);
            $startampm = ($slot['mer'] ?? '') === 'pm' ? 2 : 1;
            $rows[] = [
                'hour'         => $hourInt,
                'minute'       => $minuteInt,
                'startampm'    => $startampm,
                'isOnTheHour'  => $minuteInt === 0,
                'displayLabel' => $displayHour . ':' . $minuteStr,
            ];
        }
        return $rows;
    }

    /**
     * Compute the previous- or next-month YYYYMMDD, staying on the same
     * day-of-month when possible (decrement day until checkdate accepts).
     * Mirrors the legacy:
     *     while (! checkdate($pMonth, $pDay, $pYear)) { $pDay--; }
     */
    private function computeAdjacentMonth(int $year, int $month, int $day, int $offset): string
    {
        $newMonth = $month + $offset;
        $newYear = $year;
        if ($newMonth < 1) {
            $newMonth = 12;
            $newYear--;
        } elseif ($newMonth > 12) {
            $newMonth = 1;
            $newYear++;
        }

        $newDay = $day;
        while ($newDay > 0 && !checkdate($newMonth, $newDay, $newYear)) {
            $newDay--;
        }
        if ($newDay < 1) {
            $newDay = 1;
        }

        return sprintf('%04d%02d%02d', $newYear, $newMonth, $newDay);
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
