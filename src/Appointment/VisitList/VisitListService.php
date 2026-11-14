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
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;

final class VisitListService
{
    /** pc_constant_id of the Visit List category. */
    public const CATEGORY_CONSTANT_ID = 'visit_list';

    /** @var callable(string, array<int, string|int>): list<array<mixed>> */
    private $fetchRecords;

    /** @var callable(string, string, string, array<int, string|int>): list<array<mixed>> */
    private $fetchEvents;

    /**
     * @param callable(string, array<int, string|int>): list<array<mixed>>|null $fetchRecords
     * @param callable(string, string, string, array<int, string|int>): list<array<mixed>>|null $fetchEvents
     *        events from one date to another (Y-m-d) with repeating ones expanded to each date,
     *        given an extra WHERE clause and its binds, as fetchEvents() returns them
     */
    public function __construct(
        ?callable $fetchRecords = null,
        ?callable $fetchEvents = null,
        private readonly VisitListMatcher $matcher = new VisitListMatcher(),
    ) {
        $this->fetchRecords = $fetchRecords ?? QueryUtils::fetchRecords(...);
        $this->fetchEvents = $fetchEvents ?? self::fetchCalendarEvents(...);
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
}
