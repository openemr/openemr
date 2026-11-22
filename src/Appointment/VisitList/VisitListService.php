<?php

/**
 * VisitListService
 *
 * Finds the Visit Lists for some providers over a range of dates: each Visit List block
 * occurrence, and the Visit List patients (pc_visit_list = 1) that belong on it. Blocks are
 * found by the category's constant ID (visit_list), never its pc_catid, which differs from
 * site to site. A repeating block's dates are worked out the way the calendar works them
 * out (fetchEvents() in appointments.inc.php), so excluded dates are left out. Like the
 * calendar, only active events (pc_eventstatus = 1) are read.
 *
 * Database access and the series expansion are injected, so the lookups are unit-testable
 * without a database.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Appointment\VisitList;

use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use DomainException;
use Lcobucci\Clock\SystemClock;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;
use Psr\Clock\ClockInterface;

final class VisitListService
{
    /** pc_constant_id of the Visit List category. */
    public const CATEGORY_CONSTANT_ID = 'visit_list';

    /** @var callable(string, array<int, string|int>): list<array<mixed>> */
    private $fetchRecords;

    /** @var callable(string, string, string, array<int, string|int>): list<array<mixed>> */
    private $fetchEvents;

    /** @var callable(string, array<int, int|string|null>): void */
    private $execute;

    /** @var callable(array<mixed>, string, string): list<string> */
    private $repeatPattern;

    /**
     * @param callable(string, array<int, string|int>): list<array<mixed>>|null $fetchRecords
     * @param callable(string, string, string, array<int, string|int>): list<array<mixed>>|null $fetchEvents
     *        events from one date to another (Y-m-d) with repeating ones expanded to each date,
     *        given an extra WHERE clause and its binds, as fetchEvents() returns them
     * @param callable(string, array<int, int|string|null>): void|null $execute runs a statement that changes rows
     * @param callable(array<mixed>, string, string): list<string>|null $repeatPattern the dates (Y-m-d) an event
     *        row repeats on from one date to another, ignoring its excluded dates
     */
    public function __construct(
        ?callable $fetchRecords = null,
        ?callable $fetchEvents = null,
        private readonly VisitListMatcher $matcher = new VisitListMatcher(),
        ?callable $execute = null,
        ?callable $repeatPattern = null,
        private readonly ClockInterface $clock = new SystemClock(new DateTimeZone('UTC')),
    ) {
        $this->fetchRecords = $fetchRecords ?? QueryUtils::fetchRecords(...);
        $this->fetchEvents = $fetchEvents ?? self::fetchCalendarEvents(...);
        $this->execute = $execute ?? static function (string $sql, array $binds): void {
            QueryUtils::sqlStatementThrowException($sql, $binds);
        };
        $this->repeatPattern = $repeatPattern ?? self::calendarRepeatPattern(...);
    }

    /**
     * The Visit Lists of the given providers from one date to another, both included.
     *
     * @param list<int> $providerIds
     */
    public function forDates(DateTimeImmutable $from, DateTimeImmutable $to, array $providerIds): VisitListSchedule
    {
        $fromDate = $from->format('Y-m-d');
        $toDate = $to->format('Y-m-d');
        if ($toDate < $fromDate) {
            throw new DomainException('Visit List date range cannot end before it starts');
        }

        $providerIds = array_values(array_unique($providerIds));
        if ($providerIds === []) {
            return VisitListSchedule::empty();
        }
        foreach ($providerIds as $providerId) {
            if ($providerId <= 0) {
                throw new DomainException('Visit List provider ID must be positive');
            }
        }

        return $this->matcher->match(
            $this->blocks($fromDate, $toDate, $providerIds),
            $this->entries($fromDate, $toDate, $providerIds)
        );
    }

    /**
     * The Order # a patient added to the Visit List of this provider at this time gets: one
     * past the highest on that list, or 1 when the list is empty or there is no such block.
     */
    public function nextSequence(int $providerId, DateTimeImmutable $start): int
    {
        $claiming = self::claimingList($this->forDates($start, $start, [$providerId]), $providerId, $start);
        if ($claiming === null) {
            return 1;
        }

        $highest = 0;
        foreach ($claiming->entries as $entry) {
            $highest = max($highest, $entry->sequence ?? 0);
        }
        return $highest + 1;
    }

    /**
     * An appointment's copies as they are now, read before the editor saves it so that
     * place() can find them and keep each one's start time afterwards.
     */
    public function copiesBefore(int $eventId): VisitListCopies
    {
        if ($eventId <= 0) {
            throw new DomainException('Visit List patient event ID must be positive');
        }

        $multiple = null;
        $startByProvider = [];
        $lists = [];
        foreach ($this->copyRows($eventId, null) as $row) {
            $multiple ??= self::positiveInt($row['pc_multiple'] ?? null);
            $copyId = self::positiveInt($row['pc_eid'] ?? null);
            $providerId = self::positiveInt($row['pc_aid'] ?? null);
            $start = self::startOf($row);
            if ($providerId === null || $start === null) {
                continue;
            }
            $startByProvider[$providerId] = $start;
            $list = $copyId === null ? null : $this->forDates($start, $start, [$providerId])->listFor($copyId);
            if ($list !== null) {
                $lists[self::listKey($list)] = $list;
            }
        }
        return new VisitListCopies($multiple, $startByProvider, array_values($lists));
    }

    /**
     * Mark an appointment as a Visit List patient with its Order # and Stop #, along with the
     * copies of it for other providers (a multi-provider appointment shares pc_multiple). A
     * Visit List patient has no time of its own, so each loses any duration.
     *
     * Pass the copies as they were before the save ($before) when the editor saved an existing
     * appointment. The copies are then found by their group even when the editor deleted the
     * copy it opened, and a copy whose provider and date are unchanged keeps its own start time
     * rather than the one the editor wrote to every copy.
     *
     * Each copy's provider and date decide the list it belongs on. When no Visit List block of
     * that provider claims its start time on that date (its date or provider was changed), it
     * moves to the start of the provider's first block that day. With no block that day it
     * keeps its time.
     *
     * Each list a copy lands on is then renumbered with that copy at place $sequence (last when
     * null), moving the others down; each list a copy left ($before) is renumbered to close the
     * gap.
     */
    public function place(int $eventId, ?int $sequence, ?int $stop, ?VisitListCopies $before = null): void
    {
        if ($eventId <= 0) {
            throw new DomainException('Visit List patient event ID must be positive');
        }
        if ($sequence !== null && $sequence <= 0) {
            throw new DomainException('Visit List order must be positive');
        }
        if ($stop !== null && $stop <= 0) {
            throw new DomainException('Visit List stop must be positive');
        }

        $copyIds = [];
        foreach ($this->copyRows($eventId, $before?->multiple) as $row) {
            $copyId = self::positiveInt($row['pc_eid'] ?? null);
            if ($copyId === null) {
                continue;
            }
            $start = self::startOf($row);
            $providerId = self::positiveInt($row['pc_aid'] ?? null);
            $startTime = null;
            if ($start !== null && $providerId !== null) {
                $start = $before?->startOn($providerId, $start) ?? $start;
                $startTime = $this->startOnABlock($providerId, $start)->format('H:i:s');
            }

            ($this->execute)(
                'UPDATE `openemr_postcalendar_events` SET `pc_visit_list` = 1, `pc_visit_seq` = ?, `pc_visit_stop` = ?,'
                . ' `pc_startTime` = COALESCE(?, `pc_startTime`), `pc_endTime` = `pc_startTime`,'
                . ' `pc_duration` = 0, `pc_alldayevent` = 0 WHERE `pc_eid` = ?',
                [$sequence, $stop, $startTime, $copyId]
            );
            $copyIds[] = $copyId;
        }

        $renumbered = [];
        foreach ($copyIds as $copyId) {
            $list = $this->listOf($copyId);
            if ($list !== null) {
                $this->renumber($list, $copyId, $sequence);
                $renumbered[self::listKey($list)] = true;
            }
        }
        foreach ($before->lists ?? [] as $left) {
            if (isset($renumbered[self::listKey($left)])) {
                continue;
            }
            $list = $this->current($left);
            if ($list !== null) {
                $this->renumber($list);
            }
        }
    }

    /**
     * Number a list's patients 1 to n in list order, with one patient put at a place (last
     * when the place is null or past the end) and the others moved down to make room. Only the
     * Order # of patients whose number changes is written; Stop # is never changed.
     */
    public function renumber(VisitList $list, ?int $movingEventId = null, ?int $position = null): void
    {
        $numbers = VisitListOrder::place($list->entries, $movingEventId, $position);
        foreach ($list->entries as $entry) {
            $number = $numbers[$entry->eventId] ?? null;
            if ($number === null || $number === $entry->sequence) {
                continue;
            }
            ($this->execute)(
                'UPDATE `openemr_postcalendar_events` SET `pc_visit_seq` = ? WHERE `pc_eid` = ?',
                [$number, $entry->eventId]
            );
        }
    }

    /**
     * The list a Visit List patient is on now, or null when it is not a Visit List patient,
     * is inactive, or no block claims it.
     */
    public function listOf(int $eventId): ?VisitList
    {
        if ($eventId <= 0) {
            throw new DomainException('Visit List patient event ID must be positive');
        }

        $rows = ($this->fetchRecords)(
            'SELECT `pc_aid`, `pc_eventDate`, `pc_startTime` FROM `openemr_postcalendar_events`'
            . ' WHERE `pc_eid` = ? AND `pc_visit_list` = 1 AND `pc_eventstatus` = 1',
            [$eventId]
        );
        $row = $rows[0] ?? null;
        if ($row === null) {
            return null;
        }
        $providerId = self::positiveInt($row['pc_aid'] ?? null);
        $start = self::startOf($row);
        if ($providerId === null || $start === null) {
            return null;
        }
        return $this->forDates($start, $start, [$providerId])->listFor($eventId);
    }

    /**
     * The same block occurrence's list as it is now (its patients may have changed), or null
     * when the block is no longer there.
     */
    public function current(VisitList $list): ?VisitList
    {
        $block = $list->block;
        foreach ($this->forDates($block->start, $block->start, [$block->providerId])->lists as $now) {
            if (self::listKey($now) === self::listKey($list)) {
                return $now;
            }
        }
        return null;
    }

    /**
     * Identifies a block occurrence: its event, provider, and start on that date.
     */
    private static function listKey(VisitList $list): string
    {
        return self::blockKey($list->block);
    }

    private static function blockKey(VisitListBlock $block): string
    {
        return $block->eventId . '|' . $block->providerId . '|' . $block->start->format('Y-m-d H:i:s');
    }

    /**
     * Before the editor saves a Visit List block: the dates the save changes and the lists on
     * them. Null when the event is not a Visit List block or no patient is on those dates, so
     * there is nothing to move.
     *
     * @param string $selectedDate the date the block was opened on (Y-m-d)
     */
    public function blockSave(int $eventId, VisitListRepeatScope $scope, string $selectedDate): ?VisitListBlockSave
    {
        if ($eventId <= 0) {
            throw new DomainException('Visit List block event ID must be positive');
        }
        $categoryId = $this->categoryId();
        if ($categoryId === null) {
            return null;
        }

        $rows = $this->seriesRows(
            'WHERE `pc_eid` = ? OR (`pc_multiple` > 0 AND `pc_multiple` ='
            . ' (SELECT `m`.`pc_multiple` FROM `openemr_postcalendar_events` AS `m` WHERE `m`.`pc_eid` = ?))',
            [$eventId, $eventId]
        );
        $first = $rows[0] ?? null;
        if ($first === null || self::positiveInt($first['pc_catid'] ?? null) !== $categoryId) {
            return null;
        }
        $seriesStart = self::startOf($first);
        if ($seriesStart === null) {
            return null;
        }

        $repeats = (self::int($first['pc_recurrtype'] ?? null) ?? 0) > 0;
        if (!$repeats) {
            $scope = VisitListRepeatScope::AllDates;
        }
        $selected = DateTimeImmutable::createFromFormat('!Y-m-d', $selectedDate);
        $scopeStart = $scope === VisitListRepeatScope::AllDates || $selected === false
            ? $seriesStart->setTime(0, 0)
            : $selected;

        [$eventIds, $providerIds] = self::eventsAndProviders($rows);
        $scopeEnd = $scope === VisitListRepeatScope::ThisDate ? $scopeStart : $this->lastPatientDate($providerIds, $scopeStart);
        if ($scopeEnd === null || $scopeEnd < $scopeStart) {
            return null;
        }

        $lists = [];
        $hasPatients = false;
        foreach ($this->forDates($scopeStart, $scopeEnd, $providerIds)->lists as $list) {
            if (in_array($list->block->eventId, $eventIds, true)) {
                $lists[] = $list;
                $hasPatients = $hasPatients || $list->entries !== [];
            }
        }
        if (!$hasPatients) {
            return null;
        }

        $pattern = $scope === VisitListRepeatScope::ThisDate
            ? [$scopeStart->format('Y-m-d')]
            : ($this->repeatPattern)($first, $scopeStart->format('Y-m-d'), $scopeEnd->format('Y-m-d'));

        return new VisitListBlockSave(
            $scope,
            $eventIds,
            $providerIds,
            self::repeatRule($first),
            $pattern,
            $scopeStart,
            $lists,
            $this->lastEventId(),
        );
    }

    /**
     * After the editor saved a Visit List block: move the patients recorded before the save
     * to the block's occurrences now (see VisitListBlockMove), keeping their Order # and
     * Stop #, then renumber each list they landed on.
     */
    public function moveWithBlock(VisitListBlockSave $before): VisitListMoveResult
    {
        $categoryId = $this->categoryId();
        if ($categoryId === null) {
            return VisitListMoveResult::nothing();
        }

        // Events the save added (a date edited on its own, or a new series from a date on),
        // and for a save of every date the block's own events, if they are still there.
        $added = $this->seriesRows('WHERE `pc_eid` > ? AND `pc_catid` = ?', [$before->lastEventId, $categoryId]);
        $kept = $before->scope === VisitListRepeatScope::AllDates && $before->eventIds !== []
            ? $this->seriesRows(
                'WHERE `pc_catid` = ? AND `pc_eid` IN (' . self::placeholders($before->eventIds) . ')',
                [$categoryId, ...$before->eventIds]
            )
            : [];
        $rows = [...$kept, ...$added];

        $starts = [];
        foreach ($rows as $row) {
            $start = self::startOf($row);
            if ($start !== null) {
                $starts[] = $start->setTime(0, 0);
            }
        }
        if ($starts === []) {
            return $this->apply(VisitListBlockMove::plan($before->lists, [], [], [], $this->today()));
        }
        $newStart = min($starts);
        [$eventIds, $providerIds] = self::eventsAndProviders($rows);

        $dateMap = [];
        if ($before->scope === VisitListRepeatScope::ThisDate || self::repeatRule($rows[0]) === $before->repeatRule) {
            // The same number of pattern dates, with room for them to fall a little further
            // apart (a "repeat on" series moved to another weekday).
            $lastBefore = $before->scopeStart;
            foreach ($before->lists as $list) {
                $lastBefore = max($lastBefore, $list->block->start->setTime(0, 0));
            }
            $days = (int) $before->scopeStart->diff($lastBefore)->days;
            $pattern = $before->scope === VisitListRepeatScope::ThisDate
                ? [$newStart->format('Y-m-d')]
                : ($this->repeatPattern)(
                    $rows[0],
                    $newStart->format('Y-m-d'),
                    $newStart->add(new DateInterval('P' . ($days * 2 + 62) . 'D'))->format('Y-m-d')
                );
            $dateMap = VisitListBlockMove::dateMap($before->pattern, $pattern);
        } else {
            foreach ($before->lists as $list) {
                $dateMap[$list->block->date()] = $list->block->date();
            }
        }

        $after = [];
        if ($dateMap !== []) {
            $from = DateTimeImmutable::createFromFormat('!Y-m-d', min($dateMap));
            $to = DateTimeImmutable::createFromFormat('!Y-m-d', max($dateMap));
            if ($from !== false && $to !== false) {
                foreach ($this->forDates($from, $to, $providerIds)->lists as $list) {
                    if (in_array($list->block->eventId, $eventIds, true)) {
                        $after[] = $list->block;
                    }
                }
            }
        }

        return $this->apply(VisitListBlockMove::plan(
            $before->lists,
            $after,
            VisitListBlockMove::providerMap($before->providerIds, $providerIds),
            $dateMap,
            $this->today()
        ));
    }

    /**
     * Carry out a move plan: give each patient its block occurrence's date, start and
     * provider, then renumber each list patients landed on. A multi-provider copy whose new
     * provider already has a copy of that appointment on that date stays where it is.
     */
    private function apply(VisitListMovePlan $plan): VisitListMoveResult
    {
        $moved = 0;
        $keptForOtherCopy = 0;
        $targets = [];
        foreach ($plan->moves as $move) {
            $entry = $move->entry;
            $to = $move->to;
            if ($entry->providerId !== $to->providerId && $this->groupHasCopy($entry->eventId, $to->providerId, $to->date())) {
                $keptForOtherCopy++;
                continue;
            }
            ($this->execute)(
                'UPDATE `openemr_postcalendar_events` SET `pc_aid` = ?, `pc_eventDate` = ?, `pc_startTime` = ?,'
                . ' `pc_endTime` = ? WHERE `pc_eid` = ?',
                [(string) $to->providerId, $to->date(), $to->start->format('H:i:s'), $to->start->format('H:i:s'), $entry->eventId]
            );
            $moved++;
            $targets[self::blockKey($to)] = $to;
        }

        foreach ($targets as $block) {
            $list = $this->current(new VisitList($block, []));
            if ($list !== null) {
                $this->renumber($list);
            }
        }

        return new VisitListMoveResult($moved, count($plan->stranded), $keptForOtherCopy, count($plan->past));
    }

    /**
     * Today's date (Y-m-d) in the site's time zone, which the calendar's dates are in.
     */
    private function today(): string
    {
        return $this->clock->now()->setTimezone(new DateTimeZone(date_default_timezone_get()))->format('Y-m-d');
    }

    /**
     * Turn the patients on these lists into timed appointments on their provider's schedule:
     * in list order, one interval apart from the block's start (VisitListRelease), each one
     * interval long. They are no longer on a list, so their Order # and Stop # are cleared.
     * Lists on dates before today are left alone, as when a block moves. Returns how many
     * patients got a time.
     *
     * @param list<VisitList> $lists the lists as they were before their block was deleted or changed
     */
    public function release(array $lists, int $intervalMinutes): int
    {
        $today = $this->today();
        $released = 0;
        foreach ($lists as $list) {
            if ($list->block->date() < $today) {
                continue;
            }
            foreach (VisitListRelease::starts($list, $intervalMinutes) as $eventId => $start) {
                ($this->execute)(
                    'UPDATE `openemr_postcalendar_events` SET `pc_visit_list` = 0, `pc_visit_seq` = NULL,'
                    . ' `pc_visit_stop` = NULL, `pc_startTime` = ?, `pc_endTime` = ?, `pc_duration` = ?'
                    . ' WHERE `pc_eid` = ? AND `pc_visit_list` = 1',
                    [
                        $start->format('H:i:s'),
                        $start->add(new DateInterval('PT' . $intervalMinutes . 'M'))->format('H:i:s'),
                        $intervalMinutes * 60,
                        $eventId,
                    ]
                );
                $released++;
            }
        }
        return $released;
    }

    /**
     * How many patients on these lists are on today or later: the ones release() would give
     * times to.
     *
     * @param list<VisitList> $lists
     */
    public function upcomingPatients(array $lists): int
    {
        $today = $this->today();
        $count = 0;
        foreach ($lists as $list) {
            if ($list->block->date() >= $today) {
                $count += count($list->entries);
            }
        }
        return $count;
    }

    /**
     * Whether another appointment of this appointment's multi-provider group is with this
     * provider on this date.
     */
    private function groupHasCopy(int $eventId, int $providerId, string $date): bool
    {
        return ($this->fetchRecords)(
            'SELECT `c`.`pc_eid` FROM `openemr_postcalendar_events` AS `c`'
            . ' JOIN `openemr_postcalendar_events` AS `e` ON `e`.`pc_multiple` = `c`.`pc_multiple`'
            . ' WHERE `e`.`pc_eid` = ? AND `e`.`pc_multiple` > 0 AND `c`.`pc_eid` <> `e`.`pc_eid`'
            . ' AND `c`.`pc_aid` = ? AND `c`.`pc_eventDate` = ?',
            [$eventId, (string) $providerId, $date]
        ) !== [];
    }

    /**
     * The block events matching a WHERE clause, oldest first.
     *
     * @param array<int, string|int> $binds
     * @return list<array<mixed>>
     */
    private function seriesRows(string $where, array $binds): array
    {
        return ($this->fetchRecords)(
            'SELECT `pc_eid`, `pc_aid`, `pc_catid`, `pc_eventDate`, `pc_startTime`, `pc_recurrtype`, `pc_recurrspec`'
            . ' FROM `openemr_postcalendar_events` ' . $where . ' ORDER BY `pc_eid`',
            $binds
        );
    }

    /**
     * The date of the last active Visit List patient of these providers on or after a date,
     * or null when there is none.
     *
     * @param list<int> $providerIds
     */
    private function lastPatientDate(array $providerIds, DateTimeImmutable $from): ?DateTimeImmutable
    {
        if ($providerIds === []) {
            return null;
        }
        $rows = ($this->fetchRecords)(
            'SELECT MAX(`pc_eventDate`) AS `last` FROM `openemr_postcalendar_events`'
            . ' WHERE `pc_aid` IN (' . self::placeholders($providerIds) . ')'
            . ' AND `pc_eventDate` >= ? AND `pc_visit_list` = 1 AND `pc_eventstatus` = 1',
            [...array_map(strval(...), $providerIds), $from->format('Y-m-d')]
        );
        $last = $rows[0]['last'] ?? null;
        if (!is_string($last)) {
            return null;
        }
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $last);
        return $date === false ? null : $date;
    }

    private function lastEventId(): int
    {
        $rows = ($this->fetchRecords)('SELECT MAX(`pc_eid`) AS `last` FROM `openemr_postcalendar_events`', []);
        return self::int($rows[0]['last'] ?? null) ?? 0;
    }

    /**
     * How an event repeats, ignoring its excluded dates, so two events can be compared: its
     * repeat type and the repeat settings in pc_recurrspec.
     *
     * @param array<mixed> $row
     */
    private static function repeatRule(array $row): string
    {
        $type = self::int($row['pc_recurrtype'] ?? null) ?? 0;
        if ($type === 0) {
            return '0';
        }
        $spec = $row['pc_recurrspec'] ?? null;
        $settings = is_string($spec) ? unserialize($spec, ['allowed_classes' => false]) : null;
        if (!is_array($settings)) {
            return (string) $type;
        }
        unset($settings['exdate']);
        ksort($settings);
        return $type . json_encode($settings);
    }

    /**
     * @param list<array<mixed>> $rows
     * @return array{list<int>, list<int>} event IDs, and provider IDs sorted
     */
    private static function eventsAndProviders(array $rows): array
    {
        $eventIds = [];
        $providerIds = [];
        foreach ($rows as $row) {
            $eventId = self::positiveInt($row['pc_eid'] ?? null);
            $providerId = self::positiveInt($row['pc_aid'] ?? null);
            if ($eventId !== null) {
                $eventIds[] = $eventId;
            }
            if ($providerId !== null) {
                $providerIds[] = $providerId;
            }
        }
        $providerIds = array_values(array_unique($providerIds));
        sort($providerIds);
        return [$eventIds, $providerIds];
    }

    /**
     * The appointment and the copies sharing its pc_multiple, or sharing the given group when
     * one is known (the appointment itself may be gone).
     *
     * @return list<array<mixed>>
     */
    private function copyRows(int $eventId, ?int $multiple): array
    {
        $columns = 'SELECT `pc_eid`, `pc_aid`, `pc_multiple`, `pc_eventDate`, `pc_startTime` FROM `openemr_postcalendar_events`';
        if ($multiple !== null) {
            return ($this->fetchRecords)($columns . ' WHERE `pc_eid` = ? OR `pc_multiple` = ?', [$eventId, $multiple]);
        }
        return ($this->fetchRecords)(
            $columns . ' WHERE `pc_eid` = ? OR (`pc_multiple` > 0 AND `pc_multiple` ='
            . ' (SELECT `m`.`pc_multiple` FROM `openemr_postcalendar_events` AS `m` WHERE `m`.`pc_eid` = ?))',
            [$eventId, $eventId]
        );
    }

    /**
     * When a Visit List patient of this provider starting then should start: unchanged when a
     * block claims that time, else the start of the provider's first block that day, else
     * unchanged.
     */
    private function startOnABlock(int $providerId, DateTimeImmutable $start): DateTimeImmutable
    {
        $schedule = $this->forDates($start, $start, [$providerId]);
        if (self::claimingList($schedule, $providerId, $start) !== null) {
            return $start;
        }
        foreach ($schedule->lists as $list) {
            if ($list->block->providerId === $providerId) {
                return $list->block->start;
            }
        }
        return $start;
    }

    /**
     * The list the matcher puts a patient of this provider starting then on: of the blocks that
     * claim the time, the one that starts last, and of equal starts the first (lower pc_eid,
     * as the lists are sorted).
     */
    private static function claimingList(VisitListSchedule $schedule, int $providerId, DateTimeImmutable $start): ?VisitList
    {
        $claiming = null;
        foreach ($schedule->lists as $list) {
            if (!$list->block->claims($providerId, $start)) {
                continue;
            }
            if ($claiming === null || $list->block->start > $claiming->block->start) {
                $claiming = $list;
            }
        }
        return $claiming;
    }

    /**
     * pc_catid of the Visit List category, or null when the site has none.
     */
    public function categoryId(): ?int
    {
        $rows = ($this->fetchRecords)(
            'SELECT `pc_catid` FROM `openemr_postcalendar_categories` WHERE `pc_constant_id` = ?',
            [self::CATEGORY_CONSTANT_ID]
        );
        return self::positiveInt($rows[0]['pc_catid'] ?? null);
    }

    /**
     * @param non-empty-list<int> $providerIds
     * @return list<VisitListBlock>
     */
    private function blocks(string $fromDate, string $toDate, array $providerIds): array
    {
        $categoryId = $this->categoryId();
        if ($categoryId === null) {
            return [];
        }

        $rows = ($this->fetchEvents)(
            $fromDate,
            $toDate,
            ' AND e.pc_catid = ? AND e.pc_eventstatus = 1 AND e.pc_aid IN (' . self::placeholders($providerIds) . ')',
            [$categoryId, ...array_map(strval(...), $providerIds)]
        );

        $blocks = [];
        foreach ($rows as $row) {
            $block = self::blockFromRow($row);
            if ($block !== null) {
                $blocks[] = $block;
            }
        }
        return $blocks;
    }

    /**
     * Visit List patients are single appointments, so they are read directly rather than
     * through the series expansion.
     *
     * @param non-empty-list<int> $providerIds
     * @return list<VisitListEntry>
     */
    private function entries(string $fromDate, string $toDate, array $providerIds): array
    {
        $rows = ($this->fetchRecords)(
            'SELECT `pc_eid`, `pc_pid`, `pc_aid`, `pc_eventDate`, `pc_startTime`, `pc_visit_seq`, `pc_visit_stop`'
            . ' FROM `openemr_postcalendar_events`'
            . ' WHERE `pc_aid` IN (' . self::placeholders($providerIds) . ')'
            . ' AND `pc_eventDate` BETWEEN ? AND ? AND `pc_visit_list` = 1 AND `pc_eventstatus` = 1',
            [...array_map(strval(...), $providerIds), $fromDate, $toDate]
        );

        $entries = [];
        foreach ($rows as $row) {
            $entry = self::entryFromRow($row);
            if ($entry !== null) {
                $entries[] = $entry;
            }
        }
        return $entries;
    }

    /**
     * A block occurrence from a fetchEvents() row, or null for a row missing what a block
     * needs. fetchEvents() gives the provider as uprovider_id and each occurrence's date as
     * pc_eventDate.
     *
     * @param array<mixed> $row
     */
    private static function blockFromRow(array $row): ?VisitListBlock
    {
        $eventId = self::positiveInt($row['pc_eid'] ?? null);
        $providerId = self::positiveInt($row['uprovider_id'] ?? null);
        $start = self::startOf($row);
        if ($eventId === null || $providerId === null || $start === null) {
            return null;
        }

        $durationSeconds = self::nonNegativeInt($row['pc_duration'] ?? null) ?? 0;

        return new VisitListBlock(
            $eventId,
            $providerId,
            self::nonNegativeInt($row['pc_facility'] ?? null) ?? 0,
            $start,
            $start->add(new DateInterval('PT' . $durationSeconds . 'S'))
        );
    }

    /**
     * @param array<mixed> $row
     */
    private static function entryFromRow(array $row): ?VisitListEntry
    {
        $eventId = self::positiveInt($row['pc_eid'] ?? null);
        $patientId = self::positiveInt($row['pc_pid'] ?? null);
        $providerId = self::positiveInt($row['pc_aid'] ?? null);
        $start = self::startOf($row);
        if ($eventId === null || $patientId === null || $providerId === null || $start === null) {
            return null;
        }

        return new VisitListEntry(
            $eventId,
            $patientId,
            $providerId,
            $start,
            self::int($row['pc_visit_seq'] ?? null),
            self::int($row['pc_visit_stop'] ?? null)
        );
    }

    /**
     * When a row starts, or null for a row without a usable date.
     *
     * @param array<mixed> $row
     */
    private static function startOf(array $row): ?DateTimeImmutable
    {
        $date = $row['pc_eventDate'] ?? null;
        $time = $row['pc_startTime'] ?? null;
        if (!is_string($date) || $date === '0000-00-00') {
            return null;
        }
        if (!is_string($time) || $time === '') {
            $time = '00:00:00';
        }

        $start = DateTimeImmutable::createFromFormat(
            '!Y-m-d H:i:s',
            $date . ' ' . $time,
            new DateTimeZone(date_default_timezone_get())
        );
        return $start === false ? null : $start;
    }

    /**
     * An integer from a database value: an int, or a string of digits with an optional
     * leading minus. Anything else (null, empty, text) is null.
     */
    private static function int(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value;
        }
        if (!is_string($value)) {
            return null;
        }
        $parsed = filter_var(trim($value), FILTER_VALIDATE_INT);
        return $parsed === false ? null : $parsed;
    }

    private static function positiveInt(mixed $value): ?int
    {
        $int = self::int($value);
        return $int !== null && $int > 0 ? $int : null;
    }

    private static function nonNegativeInt(mixed $value): ?int
    {
        $int = self::int($value);
        return $int !== null && $int >= 0 ? $int : null;
    }

    /**
     * @param non-empty-list<int> $values
     */
    private static function placeholders(array $values): string
    {
        return implode(', ', array_fill(0, count($values), '?'));
    }

    /**
     * Events between two dates, worked out by the calendar's own fetchEvents().
     *
     * @param array<int, string|int> $binds
     * @return list<array<mixed>>
     */
    private static function fetchCalendarEvents(string $from, string $to, string $where, array $binds): array
    {
        require_once OEGlobalsBag::getInstance()->getSrcDir() . '/appointments.inc.php';
        return array_values(array_filter(
            fetchEvents($from, $to, $where, null, false, 0, $binds),
            is_array(...)
        ));
    }

    /**
     * The dates an event row repeats on from one date to another, worked out by the calendar's
     * own fetchEvents() with the row's excluded dates removed.
     *
     * @param array<mixed> $row a row with pc_eid and pc_recurrspec
     * @return list<string>
     */
    private static function calendarRepeatPattern(array $row, string $from, string $to): array
    {
        $eventId = self::positiveInt($row['pc_eid'] ?? null);
        if ($eventId === null) {
            return [];
        }
        $spec = $row['pc_recurrspec'] ?? null;
        $settings = is_string($spec) ? unserialize($spec, ['allowed_classes' => false]) : null;
        if (is_array($settings)) {
            $settings['exdate'] = '';
        }

        require_once OEGlobalsBag::getInstance()->getSrcDir() . '/appointments.inc.php';
        $events = fetchEvents(
            $from,
            $to,
            null,
            null,
            false,
            0,
            [is_array($settings) ? serialize($settings) : '', $eventId],
            'SELECT e.pc_eventDate, e.pc_endDate, e.pc_startTime, e.pc_endTime, e.pc_duration, e.pc_recurrtype,'
            . ' ? AS pc_recurrspec, e.pc_recurrfreq, e.pc_catid, e.pc_eid FROM openemr_postcalendar_events AS e'
            . ' WHERE e.pc_eid = ?'
        );

        $dates = [];
        foreach ($events as $event) {
            $date = is_array($event) ? ($event['pc_eventDate'] ?? null) : null;
            if (is_string($date) && $date >= $from && $date <= $to) {
                $dates[$date] = $date;
            }
        }
        sort($dates);
        return $dates;
    }
}
