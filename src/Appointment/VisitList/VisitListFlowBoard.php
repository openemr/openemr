<?php

/**
 * VisitListFlowBoard
 *
 * Puts the Flow Board's Visit List patients in list order. Each patient on a Visit List sits
 * at its block's start time; the timed appointments at that time come first, then the block's
 * patients by Order #. Each such row gets a `visit_list` key with the Order # label and the
 * block's time range, which the board shows instead of the appointment time.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Appointment\VisitList;

use DateTimeImmutable;

final readonly class VisitListFlowBoard
{
    public function __construct(
        private VisitListService $service = new VisitListService(),
    ) {
    }

    /**
     * The board's rows with Visit List patients in list order and marked (see order()). Reads
     * the Visit Lists of the rows' providers, from the first row's date to the last.
     *
     * @param array<mixed> $rows the board's appointment rows, sorted by date and time
     * @return list<array<mixed>> the rows; anything that is not a row is dropped
     */
    public function arrange(array $rows, bool $isTwelveHourFormat): array
    {
        $rows = array_values(array_filter($rows, is_array(...)));

        $providerIds = [];
        $dates = [];
        foreach ($rows as $row) {
            $providerId = self::positiveInt($row['uprovider_id'] ?? null);
            $date = is_string($row['pc_eventDate'] ?? null)
                ? DateTimeImmutable::createFromFormat('!Y-m-d', $row['pc_eventDate'])
                : false;
            if ($providerId !== null && $date !== false) {
                $providerIds[$providerId] = $providerId;
                $dates[] = $date;
            }
        }
        if ($dates === []) {
            return $rows;
        }

        $schedule = $this->service->forDates(min($dates), max($dates), array_values($providerIds));
        return self::order($rows, $schedule, $isTwelveHourFormat);
    }

    /**
     * Moves each Visit List patient to its block's start time, after the timed appointments
     * there, and puts a block's patients in list order. Blocks starting at the same time keep
     * the schedule's order (provider, then block). Other rows stay at their own date and time;
     * rows at the same date and time keep the order they came in. A row is on a list when its
     * event, provider and date match one of the schedule's entries.
     *
     * Each Visit List row gets `visit_list`: `sequenceLabel` ("#3", or "#" with no Order #)
     * and `timeRange`, the block's start and end ("08:00–12:00", or "8:00 am–12:00 pm").
     *
     * @param list<array<mixed>> $rows
     * @return list<array<mixed>>
     */
    public static function order(array $rows, VisitListSchedule $schedule, bool $isTwelveHourFormat): array
    {
        /** @var array<int, array{list: VisitList, listIndex: int, entry: VisitListEntry, entryIndex: int}> $placed */
        $placed = [];
        foreach ($schedule->lists as $listIndex => $list) {
            foreach ($list->entries as $entryIndex => $entry) {
                $placed[$entry->eventId] = [
                    'list' => $list,
                    'listIndex' => $listIndex,
                    'entry' => $entry,
                    'entryIndex' => $entryIndex,
                ];
            }
        }

        $format = $isTwelveHourFormat ? 'g:i a' : 'H:i';
        $keyed = [];
        foreach ($rows as $index => $row) {
            $date = is_string($row['pc_eventDate'] ?? null) ? $row['pc_eventDate'] : '';
            $time = is_string($row['pc_startTime'] ?? null) ? $row['pc_startTime'] : '';
            $key = [$date, $time, 0, 0, 0, $index];

            $spot = $placed[self::positiveInt($row['pc_eid'] ?? null) ?? 0] ?? null;
            if ($spot !== null && self::isOn($row, $spot['list'])) {
                $block = $spot['list']->block;
                $key = [$date, $block->start->format('H:i:s'), 1, $spot['listIndex'], $spot['entryIndex'], $index];
                $sequence = $spot['entry']->sequence;
                $row['visit_list'] = [
                    'sequenceLabel' => $sequence === null ? '#' : '#' . $sequence,
                    'timeRange' => $block->start->format($format) . '–' . $block->end->format($format),
                ];
            }
            $keyed[] = ['key' => $key, 'row' => $row];
        }

        usort($keyed, static fn(array $a, array $b): int => $a['key'] <=> $b['key']);

        return array_map(static fn(array $item): array => $item['row'], $keyed);
    }

    /**
     * Whether a board row is the given list's patient on the block's date and provider (a
     * repeating appointment's other dates share its event ID).
     *
     * @param array<mixed> $row
     */
    private static function isOn(array $row, VisitList $list): bool
    {
        return ($row['pc_eventDate'] ?? null) === $list->block->date()
            && self::positiveInt($row['uprovider_id'] ?? null) === $list->block->providerId;
    }

    private static function positiveInt(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value > 0 ? $value : null;
        }
        if (is_string($value) && ctype_digit($value)) {
            $int = (int) $value;
            return $int > 0 ? $int : null;
        }
        return null;
    }
}
