<?php

/**
 * VisitListDayColumn
 *
 * The Visit List part of one provider's column in the calendar day view. Patients on a Visit
 * List are taken out of the timed grid, and each Visit List block in the column gets the rows
 * of its list instead: list order, patient name, visit category and appointment status.
 *
 * A list is only shown when its block is among the column's events. When a filter (such as
 * the facility filter) leaves the block out, its patients stay in the timed grid at their
 * stored time rather than disappearing. Visit List patients no block claims stay there too.
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
 *     eventDate: string,
 *     pccattype: string,
 *     sequenceLabel: string,
 *     patientName: string,
 *     categoryName: string,
 *     statusTitle: string,
 *     bgColor: string,
 *     tooltip: string
 * }
 * @phpstan-type VisitListData array{
 *     title: string,
 *     timeLabel: string,
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
     * @param list<array<string, mixed>>     $dayEvents     the day's events, as the day view receives them
     * @param array<string, AppointmentStatus> $statuses    appointment statuses by option_id
     */
    public static function build(
        CalendarViewModel $viewModel,
        array $dayEvents,
        ?VisitListSchedule $schedule,
        int $providerId,
        string $eventDate,
        string $eventDateYmd,
        array $statuses,
        bool $isTwelveHourFormat
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
                'count' => count($rows),
                'rows' => $rows,
            ];
        }

        $gridEvents = array_values(array_filter(
            $dayEvents,
            static fn (array $event): bool => !isset($shownEntryEids[self::eventId($event) ?? 0])
        ));

        return new self($gridEvents, $lists);
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
        $tooltip .= "\n(" . xl('Click to edit') . ')';

        return [
            'eid' => $entry->eventId,
            'eventDate' => $eventDateYmd,
            'pccattype' => CalendarRenderDataBuilder::categoryTypeToken($event['pccattype'] ?? null),
            'sequenceLabel' => $entry->sequence === null ? '#' : '#' . $entry->sequence,
            'patientName' => $patientName,
            'categoryName' => $categoryName,
            'statusTitle' => $statusTitle,
            'bgColor' => $bgColor,
            'tooltip' => $tooltip,
        ];
    }

    private static function timeLabel(VisitList $list, bool $isTwelveHourFormat): string
    {
        $format = $isTwelveHourFormat ? 'g:i' : 'H:i';
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
