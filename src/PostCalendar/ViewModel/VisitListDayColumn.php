<?php

/**
 * VisitListDayColumn
 *
 * The Visit List part of one provider's day in a calendar view: a column of the day or week
 * view, a cell of the month view, or a day of a print view. Patients on a Visit List are taken
 * out of the day's other events, and each Visit List block of the day gets the rows of its list
 * instead: list order, stop, patient name, visit category and appointment status. Each view
 * shows as many of those as it has room for.
 *
 * A list is only shown when its block is among the column's events and, when the calendar is
 * filtered to one facility, the block is at that facility. Otherwise its patients stay in the
 * timed grid at their stored time, as do Visit List patients no block claims. They have no
 * duration, so the grid draws them one time slot tall rather than not at all.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\PostCalendar\ViewModel;

use OpenEMR\Appointment\VisitList\VisitList;
use OpenEMR\Appointment\VisitList\VisitListEntry;
use OpenEMR\Appointment\VisitList\VisitListSchedule;

/**
 * @phpstan-type AppointmentStatus array{title: string, color: string}
 * @phpstan-type VisitListRow array{
 *     eid: int,
 *     pid: int,
 *     eventDate: string,
 *     pccattype: string,
 *     sequenceLabel: string,
 *     stopLabel: string,
 *     patientName: string,
 *     categoryName: string,
 *     statusTitle: string,
 *     bgColor: string,
 *     tooltip: string,
 *     patientTooltip: string
 * }
 * @phpstan-type VisitListData array{
 *     title: string,
 *     timeLabel: string,
 *     providerId: int,
 *     eventDate: string,
 *     startHour: string,
 *     startMinute: string,
 *     count: int,
 *     rows: list<VisitListRow>
 * }
 */
final readonly class VisitListDayColumn
{
    /** Appointment status that means no status was set (apptstat list). */
    private const NO_STATUS = '-';

    /**
     * @param list<array<string, mixed>> $gridEvents the column's events, without the Visit List patients shown on a list
     * @param array<int, VisitListData>  $lists      each shown list, keyed by its block's pc_eid
     */
    private function __construct(
        public array $gridEvents,
        public array $lists,
    ) {
    }

    /**
     * Split one provider's day of events into the timed grid and the Visit Lists.
     *
     * @param list<array<string, mixed>>       $dayEvents       the day's events, as the day view receives them
     * @param array<string, AppointmentStatus> $statuses        appointment statuses by option_id
     * @param int                              $pcFacility      the facility the calendar is filtered to; 0 for all
     * @param int                              $intervalMinutes the length of one time slot in the grid
     */
    public static function build(
        CalendarViewModel $viewModel,
        array $dayEvents,
        ?VisitListSchedule $schedule,
        int $providerId,
        string $eventDate,
        string $eventDateYmd,
        array $statuses,
        bool $isTwelveHourFormat,
        int $pcFacility = 0,
        int $intervalMinutes = 15
    ): self {
        if ($schedule === null) {
            return new self($dayEvents, []);
        }

        $eventsByEid = [];
        foreach ($dayEvents as $event) {
            $eid = self::eventId($event);
            if ($eid !== null) {
                $eventsByEid[$eid] = $event;
            }
        }

        $lists = [];
        $shownEntryEids = [];
        foreach ($schedule->lists as $list) {
            $block = $list->block;
            if ($block->providerId !== $providerId || $block->date() !== $eventDate) {
                continue;
            }
            if ($pcFacility !== 0 && $block->facilityId !== $pcFacility) {
                continue;
            }
            $blockEvent = $eventsByEid[$block->eventId] ?? null;
            if ($blockEvent === null) {
                continue;
            }

            $rows = [];
            foreach ($list->entries as $entry) {
                $entryEvent = $eventsByEid[$entry->eventId] ?? null;
                if ($entryEvent === null) {
                    continue;
                }
                $rows[] = self::row($viewModel, $entry, $entryEvent, $eventDateYmd, $statuses);
                $shownEntryEids[$entry->eventId] = true;
            }

            $lists[$block->eventId] = [
                'title' => self::string($blockEvent['catname'] ?? null),
                'timeLabel' => self::timeLabel($list, $isTwelveHourFormat),
                'providerId' => $block->providerId,
                'eventDate' => $eventDateYmd,
                'startHour' => $block->start->format('G'),
                'startMinute' => $block->start->format('i'),
                'count' => count($rows),
                'rows' => $rows,
            ];
        }

        // Visit List patients not shown on a list keep their place in the grid, one slot tall.
        $entryEids = [];
        foreach ($schedule->lists as $list) {
            foreach ($list->entries as $entry) {
                $entryEids[$entry->eventId] = true;
            }
        }
        foreach ($schedule->unplaced as $entry) {
            $entryEids[$entry->eventId] = true;
        }
        $slotSeconds = max(1, $intervalMinutes) * 60;

        $gridEvents = [];
        foreach ($dayEvents as $event) {
            $eid = self::eventId($event) ?? 0;
            if (isset($shownEntryEids[$eid])) {
                continue;
            }
            if (isset($entryEids[$eid])) {
                $event['duration'] = $slotSeconds;
            }
            $gridEvents[] = $event;
        }

        return new self($gridEvents, $lists);
    }

    /**
     * The grid events in the order the overlap layout should see them: each block that shows a
     * list goes last. The layout places the events of each time slot side by side in this
     * order and keeps each event at its narrowest width and rightmost place, so a block that
     * starts before the appointments beside it would otherwise end up in the same place as
     * them, drawn over by them. Last, it stays on the right and the appointments on the left.
     *
     * When two of those blocks share a time slot, the order is left as it is: moving both last
     * can give the earlier one the later one's place. The layout works per slot, so each
     * block's time is widened to whole slots before they are compared: a block ending at 10:15
     * and one starting at 10:15 share the 10:00 slot of a 30-minute grid.
     *
     * @param int $intervalMinutes the length of one time slot in the grid
     * @return list<array<string, mixed>>
     */
    public function overlapOrder(int $intervalMinutes): array
    {
        $others = [];
        $blocks = [];
        $spans = [];
        foreach ($this->gridEvents as $event) {
            if (isset($this->lists[self::eventId($event) ?? 0])) {
                $blocks[] = $event;
                $spans[] = self::slots($event, max(1, $intervalMinutes));
            } else {
                $others[] = $event;
            }
        }
        foreach ($spans as $index => [$start, $end]) {
            foreach (array_slice($spans, $index + 1) as [$otherStart, $otherEnd]) {
                if ($start < $otherEnd && $otherStart < $end) {
                    return $this->gridEvents;
                }
            }
        }
        return [...$others, ...$blocks];
    }

    /**
     * Where the overlap layout places each grid event: laid out in overlapOrder() only when
     * that draws fewer events over a block that shows a list than the day's own order, or as
     * few over a block and fewer over each other. Otherwise, ties included, the day's order is
     * kept, as on master. The layout keeps each event's rightmost place and narrowest width
     * across its slots, so moving a block last can make an appointment that also runs past
     * the block cover part of it; the reorder is only taken when it leaves the blocks clearer.
     *
     * @param callable(list<array<string, mixed>>): array<int|string, array{width: float, leftpos: float}> $layout
     * @param int $intervalMinutes the length of one time slot in the grid
     * @return array<int|string, array{width: float, leftpos: float}>
     */
    public function overlapPositions(callable $layout, int $intervalMinutes): array
    {
        $ordered = $this->overlapOrder($intervalMinutes);
        $positions = $layout($ordered);
        if ($ordered === $this->gridEvents) {
            return $positions;
        }
        $dayOrder = $layout($this->gridEvents);
        [$dayOnBlocks, $dayAll] = $this->collisions($dayOrder, $intervalMinutes);
        [$orderedOnBlocks, $orderedAll] = $this->collisions($positions, $intervalMinutes);
        $reorderIsClearer = $orderedOnBlocks < $dayOnBlocks
            || ($orderedOnBlocks === $dayOnBlocks && $orderedAll < $dayAll);
        return $reorderIsClearer ? $positions : $dayOrder;
    }

    /**
     * How many pairs of grid events these positions draw over each other (in the same time
     * slot and sharing part of the column's width): those with a block that shows a list in
     * them, and all of them.
     *
     * @param array<int|string, array{width: float, leftpos: float}> $positions
     * @return array{int, int}
     */
    private function collisions(array $positions, int $intervalMinutes): array
    {
        $placed = [];
        foreach ($this->gridEvents as $event) {
            $eid = $event['eid'] ?? null;
            if ((is_int($eid) || is_string($eid)) && isset($positions[$eid])) {
                $isBlock = isset($this->lists[self::eventId($event) ?? 0]);
                $placed[] = [self::slots($event, max(1, $intervalMinutes)), $positions[$eid], $isBlock];
            }
        }
        $onBlocks = 0;
        $all = 0;
        foreach ($placed as $index => [[$start, $end], $place, $isBlock]) {
            foreach (array_slice($placed, $index + 1) as [[$otherStart, $otherEnd], $other, $otherIsBlock]) {
                $sameTime = $start < $otherEnd && $otherStart < $end;
                $sameWidth = $place['leftpos'] < $other['leftpos'] + $other['width'] - 0.01
                    && $other['leftpos'] < $place['leftpos'] + $place['width'] - 0.01;
                if ($sameTime && $sameWidth) {
                    $all++;
                    if ($isBlock || $otherIsBlock) {
                        $onBlocks++;
                    }
                }
            }
        }
        return [$onBlocks, $all];
    }

    /**
     * The start of an event's first time slot and the end of its last one, in minutes from
     * midnight: its start rounded down and its end rounded up to whole slots.
     *
     * @param array<string, mixed> $event
     * @return array{int, int}
     */
    private static function slots(array $event, int $intervalMinutes): array
    {
        $startTime = self::string($event['startTime'] ?? null);
        $parts = explode(':', $startTime);
        $start = (int) $parts[0] * 60 + (int) ($parts[1] ?? 0);
        $duration = $event['duration'] ?? 0;
        $seconds = is_numeric($duration) ? (int) $duration : 0;
        $end = max($start + intdiv($seconds, 60), $start + 1);
        return [
            intdiv($start, $intervalMinutes) * $intervalMinutes,
            intdiv($end + $intervalMinutes - 1, $intervalMinutes) * $intervalMinutes,
        ];
    }

    /**
     * Appointment statuses by option_id from apptstat list rows. A row's notes hold its color
     * before a "|" (for example "FEFDCF|0"); a color that is not a plain CSS color is dropped.
     *
     * @param list<array<mixed>>         $rows      option_id, title and notes of each status
     * @param callable(string): string   $translate turns a list title into the user's language
     * @return array<string, AppointmentStatus>
     */
    public static function appointmentStatuses(array $rows, callable $translate): array
    {
        $statuses = [];
        foreach ($rows as $row) {
            $optionId = self::string($row['option_id'] ?? null);
            if ($optionId === '') {
                continue;
            }
            $color = explode('|', self::string($row['notes'] ?? null))[0];
            $statuses[$optionId] = [
                'title' => $translate(self::string($row['title'] ?? null)),
                'color' => CalendarRenderDataBuilder::sanitizeCssColor($color),
            ];
        }
        return $statuses;
    }

    /**
     * @param array<string, mixed>             $event
     * @param array<string, AppointmentStatus> $statuses
     * @return VisitListRow
     */
    private static function row(
        CalendarViewModel $viewModel,
        VisitListEntry $entry,
        array $event,
        string $eventDateYmd,
        array $statuses
    ): array {
        $name = $viewModel->parsePatientName(self::string($event['patient_name'] ?? null));
        $patientName = $name['fname'] === '' ? $name['lname'] : $name['lname'] . ', ' . $name['fname'];
        $categoryName = self::string($event['catname'] ?? null);
        $comment = self::string($event['hometext'] ?? null);

        $statusCode = self::string($event['apptstatus'] ?? null);
        $status = $statuses[$statusCode] ?? null;
        $hasStatus = $statusCode !== '' && $statusCode !== self::NO_STATUS;
        $statusTitle = $hasStatus ? ($status['title'] ?? $statusCode) : '';
        $bgColor = CalendarRenderDataBuilder::sanitizeCssColor($status['color'] ?? '');
        if ($bgColor === '') {
            $bgColor = CalendarRenderDataBuilder::sanitizeCssColor(self::string($event['catcolor'] ?? null));
        }

        $tooltip = $patientName;
        if ($categoryName !== '') {
            $tooltip .= "\n" . $categoryName;
        }
        if ($statusTitle !== '') {
            $tooltip .= "\n" . $statusTitle;
        }
        if ($entry->stop !== null) {
            $tooltip .= "\n" . xl('Stop') . ' ' . $entry->stop;
        }
        if ($comment !== '') {
            $tooltip .= "\n" . $comment;
        }
        // As on a timed appointment: double click the row (or click its number, in place
        // of a time) to edit the appointment, click the name to open the chart.
        $tooltip .= "\n(" . xl('double click to edit') . ')';

        return [
            'eid' => $entry->eventId,
            'pid' => $entry->patientId,
            'eventDate' => $eventDateYmd,
            'pccattype' => CalendarRenderDataBuilder::categoryTypeToken($event['pccattype'] ?? null),
            'sequenceLabel' => $entry->sequence === null ? '#' : '#' . $entry->sequence,
            'stopLabel' => $entry->stop === null ? '' : xl('Stop') . ' ' . $entry->stop,
            'patientName' => $patientName,
            'categoryName' => $categoryName,
            'statusTitle' => $statusTitle,
            'bgColor' => $bgColor,
            'tooltip' => $tooltip,
            'patientTooltip' => $patientName . "\n(" . xl('Click to view') . ')',
        ];
    }

    private static function timeLabel(VisitList $list, bool $isTwelveHourFormat): string
    {
        $format = $isTwelveHourFormat ? 'g:ia' : 'H:i';
        return $list->block->start->format($format) . '–' . $list->block->end->format($format);
    }

    /**
     * @param array<string, mixed> $event
     */
    private static function eventId(array $event): ?int
    {
        $eid = $event['eid'] ?? null;
        if (is_int($eid)) {
            return $eid;
        }
        if (!is_string($eid)) {
            return null;
        }
        $parsed = filter_var($eid, FILTER_VALIDATE_INT);
        return $parsed === false ? null : $parsed;
    }

    private static function string(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }
}
