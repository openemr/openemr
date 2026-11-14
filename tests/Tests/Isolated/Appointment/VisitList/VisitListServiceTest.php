<?php

/**
 * Isolated VisitListService Test
 *
 * Reading Visit List blocks and patients without a database: the category found by its
 * constant ID, block rows as fetchEvents() returns them, patient rows as the query returns
 * them, and rows the lookups cannot use.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Appointment\VisitList;

use DateTimeImmutable;
use DomainException;
use OpenEMR\Appointment\VisitList\VisitListEntry;
use OpenEMR\Appointment\VisitList\VisitListService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class VisitListServiceTest extends TestCase
{
    private const CATEGORY_ID = 21;

    /** @var list<array{string, array<mixed>}> Each query run: SQL and binds. */
    private array $queries = [];

    /** @var list<array{string, string, string, array<mixed>}> Each fetchEvents call. */
    private array $eventCalls = [];

    /**
     * A service over the given rows.
     *
     * @param list<array<string, mixed>> $blockRows rows fetchEvents() returns
     * @param list<array<string, mixed>> $entryRows rows the Visit List patient query returns
     */
    private function service(array $blockRows, array $entryRows, ?int $categoryId = self::CATEGORY_ID): VisitListService
    {
        $fetchRecords = function (string $sql, array $binds) use ($entryRows, $categoryId): array {
            $this->queries[] = [$sql, $binds];
            if (str_contains($sql, 'openemr_postcalendar_categories')) {
                return $categoryId === null ? [] : [['pc_catid' => (string) $categoryId]];
            }
            return $entryRows;
        };
        $fetchEvents = function (string $from, string $to, string $where, array $binds) use ($blockRows): array {
            $this->eventCalls[] = [$from, $to, $where, $binds];
            return $blockRows;
        };
        return new VisitListService($fetchRecords, $fetchEvents);
    }

    /**
     * @return array<string, mixed>
     */
    private static function blockRow(int $eid, int $provider, string $date, string $start, int $durationSeconds): array
    {
        return [
            'pc_eid' => (string) $eid,
            'uprovider_id' => (string) $provider,
            'pc_eventDate' => $date,
            'pc_startTime' => $start,
            'pc_duration' => (string) $durationSeconds,
            'pc_facility' => '3',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function entryRow(int $eid, int $provider, string $date, string $start, ?string $sequence, ?string $stop): array
    {
        return [
            'pc_eid' => (string) $eid,
            'pc_pid' => (string) (500 + $eid),
            'pc_aid' => (string) $provider,
            'pc_eventDate' => $date,
            'pc_startTime' => $start,
            'pc_visit_seq' => $sequence,
            'pc_visit_stop' => $stop,
        ];
    }

    /**
     * @param list<VisitListEntry> $entries
     * @return list<int>
     */
    private static function eids(array $entries): array
    {
        return array_map(static fn (VisitListEntry $entry): int => $entry->eventId, $entries);
    }

    public function testFindsBlocksAndPutsPatientsOnThem(): void
    {
        $service = $this->service(
            [self::blockRow(1, 7, '2026-03-09', '09:00:00', 28800)],
            [
                self::entryRow(11, 7, '2026-03-09', '09:00:00', '2', '5'),
                self::entryRow(10, 7, '2026-03-09', '09:00:00', '1', null),
            ]
        );

        $schedule = $service->forDates(new DateTimeImmutable('2026-03-09'), new DateTimeImmutable('2026-03-09'), [7]);

        $this->assertCount(1, $schedule->lists);
        $block = $schedule->lists[0]->block;
        $this->assertSame(1, $block->eventId);
        $this->assertSame(7, $block->providerId);
        $this->assertSame(3, $block->facilityId);
        $this->assertSame('2026-03-09 09:00', $block->start->format('Y-m-d H:i'));
        $this->assertSame('2026-03-09 17:00', $block->end->format('Y-m-d H:i'));

        $entries = $schedule->lists[0]->entries;
        $this->assertSame([10, 11], self::eids($entries));
        $this->assertSame(510, $entries[0]->patientId);
        $this->assertSame(1, $entries[0]->sequence);
        $this->assertNull($entries[0]->stop);
        $this->assertSame(2, $entries[1]->sequence);
        $this->assertSame(5, $entries[1]->stop);
    }

    public function testAsksForTheCategoryByConstantIdAndBlocksByItsId(): void
    {
        $this->service([], [])->forDates(new DateTimeImmutable('2026-03-09'), new DateTimeImmutable('2026-03-15'), [7, 9, 7]);

        $this->assertSame(['visit_list'], $this->queries[0][1]);
        $this->assertCount(1, $this->eventCalls);
        [$from, $to, $where, $binds] = $this->eventCalls[0];
        $this->assertSame('2026-03-09', $from);
        $this->assertSame('2026-03-15', $to);
        $this->assertStringContainsString('e.pc_catid = ?', $where);
        $this->assertStringContainsString('e.pc_eventstatus = 1', $where);
        $this->assertStringContainsString('e.pc_aid IN (?, ?)', $where);
        $this->assertSame([self::CATEGORY_ID, '7', '9'], $binds);
    }

    public function testReadsOnlyActiveVisitListPatientsOfTheProvidersAndDates(): void
    {
        $this->service([], [])->forDates(new DateTimeImmutable('2026-03-09'), new DateTimeImmutable('2026-03-15'), [7, 9]);

        [$sql, $binds] = $this->queries[1];
        $this->assertStringContainsString('`pc_visit_list` = 1', $sql);
        $this->assertStringContainsString('`pc_eventstatus` = 1', $sql);
        $this->assertStringContainsString('`pc_aid` IN (?, ?)', $sql);
        $this->assertSame(['7', '9', '2026-03-09', '2026-03-15'], $binds);
    }

    public function testSiteWithoutTheCategoryHasNoBlocksAndPatientsAreUnplaced(): void
    {
        $service = $this->service(
            [self::blockRow(1, 7, '2026-03-09', '09:00:00', 28800)],
            [self::entryRow(10, 7, '2026-03-09', '09:00:00', '1', null)],
            null
        );

        $schedule = $service->forDates(new DateTimeImmutable('2026-03-09'), new DateTimeImmutable('2026-03-09'), [7]);

        $this->assertSame([], $this->eventCalls);
        $this->assertSame([], $schedule->lists);
        $this->assertSame([10], self::eids($schedule->unplaced));
    }

    public function testNoProvidersReadsNothing(): void
    {
        $schedule = $this->service([], [])->forDates(new DateTimeImmutable('2026-03-09'), new DateTimeImmutable('2026-03-09'), []);

        $this->assertSame([], $schedule->lists);
        $this->assertSame([], $this->queries);
        $this->assertSame([], $this->eventCalls);
    }

    public function testRepeatingBlockGivesOneListPerDate(): void
    {
        $service = $this->service(
            [
                self::blockRow(1, 7, '2026-03-09', '09:00:00', 28800),
                self::blockRow(1, 7, '2026-03-16', '09:00:00', 28800),
            ],
            [self::entryRow(10, 7, '2026-03-16', '09:00:00', '1', null)]
        );

        $schedule = $service->forDates(new DateTimeImmutable('2026-03-09'), new DateTimeImmutable('2026-03-16'), [7]);

        $this->assertCount(2, $schedule->lists);
        $this->assertSame([], $schedule->lists[0]->entries);
        $this->assertSame([10], self::eids($schedule->lists[1]->entries));
    }

    public function testRowsMissingWhatTheyNeedAreSkipped(): void
    {
        $service = $this->service(
            [
                ['pc_eid' => '2', 'uprovider_id' => null, 'pc_eventDate' => '2026-03-09', 'pc_startTime' => '09:00:00'],
                ['pc_eid' => '3', 'uprovider_id' => '7', 'pc_eventDate' => '0000-00-00', 'pc_startTime' => '09:00:00'],
                self::blockRow(1, 7, '2026-03-09', '09:00:00', 28800),
            ],
            [
                self::entryRow(10, 7, '2026-03-09', '10:00:00', '1', null),
                ['pc_eid' => '11', 'pc_pid' => '0', 'pc_aid' => '7', 'pc_eventDate' => '2026-03-09', 'pc_startTime' => '10:00:00'],
                ['pc_eid' => '12', 'pc_pid' => '9', 'pc_aid' => '', 'pc_eventDate' => '2026-03-09', 'pc_startTime' => '10:00:00'],
                ['pc_eid' => '13', 'pc_pid' => '9', 'pc_aid' => '7', 'pc_eventDate' => 'not a date', 'pc_startTime' => '10:00:00'],
            ]
        );

        $schedule = $service->forDates(new DateTimeImmutable('2026-03-09'), new DateTimeImmutable('2026-03-09'), [7]);

        $this->assertCount(1, $schedule->lists);
        $this->assertSame([10], self::eids($schedule->lists[0]->entries));
        $this->assertSame([], $schedule->unplaced);
    }

    public function testMissingTimeAndDurationMeanAZeroLengthBlockAtMidnight(): void
    {
        $service = $this->service(
            [['pc_eid' => '1', 'uprovider_id' => '7', 'pc_eventDate' => '2026-03-09', 'pc_startTime' => null, 'pc_duration' => 'x']],
            []
        );

        $block = $service->forDates(new DateTimeImmutable('2026-03-09'), new DateTimeImmutable('2026-03-09'), [7])->lists[0]->block;

        $this->assertSame('2026-03-09 00:00', $block->start->format('Y-m-d H:i'));
        $this->assertEquals($block->start, $block->end);
        $this->assertSame(0, $block->facilityId);
    }

    public function testListOrderAndStopThatAreNotNumbersAreTreatedAsNotGiven(): void
    {
        $service = $this->service(
            [self::blockRow(1, 7, '2026-03-09', '09:00:00', 28800)],
            [self::entryRow(10, 7, '2026-03-09', '09:00:00', 'first', '')]
        );

        $entry = $service->forDates(new DateTimeImmutable('2026-03-09'), new DateTimeImmutable('2026-03-09'), [7])->lists[0]->entries[0];

        $this->assertNull($entry->sequence);
        $this->assertNull($entry->stop);
    }

    public function testCategoryIdIsNullWithoutTheCategory(): void
    {
        $this->assertSame(self::CATEGORY_ID, $this->service([], [])->categoryId());
        $this->assertNull($this->service([], [], null)->categoryId());
    }

    public function testRangeCannotEndBeforeItStarts(): void
    {
        $this->expectException(DomainException::class);
        $this->service([], [])->forDates(new DateTimeImmutable('2026-03-10'), new DateTimeImmutable('2026-03-09'), [7]);
    }

    public function testProviderIdsMustBePositive(): void
    {
        $this->expectException(DomainException::class);
        $this->service([], [])->forDates(new DateTimeImmutable('2026-03-09'), new DateTimeImmutable('2026-03-09'), [0]);
    }

    public function testNextOrderIsOnePastTheHighestOnTheList(): void
    {
        $service = $this->service(
            [self::blockRow(1, 7, '2026-03-09', '09:00:00', 28800)],
            [
                self::entryRow(10, 7, '2026-03-09', '09:00:00', '4', null),
                self::entryRow(11, 7, '2026-03-09', '09:00:00', null, '2'),
                self::entryRow(12, 7, '2026-03-09', '09:00:00', '2', null),
            ]
        );

        $this->assertSame(5, $service->nextSequence(7, new DateTimeImmutable('2026-03-09 09:00')));
        $this->assertSame([['2026-03-09', '2026-03-09']], array_map(
            static fn (array $call): array => [$call[0], $call[1]],
            $this->eventCalls
        ), 'only the one date is read');
    }

    public function testNextOrderIsOneForAnEmptyListOrNoBlock(): void
    {
        $service = $this->service([self::blockRow(1, 7, '2026-03-09', '09:00:00', 3600)], []);

        $this->assertSame(1, $service->nextSequence(7, new DateTimeImmutable('2026-03-09 09:00')), 'empty list');
        $this->assertSame(1, $service->nextSequence(7, new DateTimeImmutable('2026-03-09 13:00')), 'after the block');
        $this->assertSame(1, $service->nextSequence(8, new DateTimeImmutable('2026-03-09 09:00')), 'another provider');
    }

    public function testNextOrderComesFromTheBlockThatWouldClaimThePatient(): void
    {
        // An all-day block with a morning block inside it: a 9:00 patient goes on the morning one.
        $service = $this->service(
            [
                self::blockRow(1, 7, '2026-03-09', '08:00:00', 36000),
                self::blockRow(2, 7, '2026-03-09', '09:00:00', 7200),
            ],
            [
                self::entryRow(10, 7, '2026-03-09', '08:00:00', '7', null),
                self::entryRow(11, 7, '2026-03-09', '09:00:00', '2', null),
            ]
        );

        $this->assertSame(3, $service->nextSequence(7, new DateTimeImmutable('2026-03-09 09:00')));
        $this->assertSame(8, $service->nextSequence(7, new DateTimeImmutable('2026-03-09 08:00')));
    }

    public function testPlaceMarksTheAppointmentAsAVisitListPatient(): void
    {
        $statements = [];
        $service = new VisitListService(
            static fn (string $sql, array $binds): array => [],
            static fn (string $from, string $to, string $where, array $binds): array => [],
            execute: static function (string $sql, array $binds) use (&$statements): void {
                $statements[] = [$sql, $binds];
            }
        );

        $service->place(42, 3, null);
        $service->place(43, null, 5);

        $this->assertCount(2, $statements);
        $this->assertStringContainsString('`pc_visit_list` = 1', $statements[0][0]);
        $this->assertStringContainsString('`pc_duration` = 0', $statements[0][0], 'no time of its own');
        $this->assertSame([3, null, 42], $statements[0][1]);
        $this->assertSame([null, 5, 43], $statements[1][1]);
    }

    /**
     * @return array<string, array{int, ?int, ?int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function invalidPlaceProvider(): array
    {
        return [
            'event ID not positive' => [0, 1, null],
            'order not positive' => [42, 0, null],
            'stop not positive' => [42, 1, -1],
        ];
    }

    #[DataProvider('invalidPlaceProvider')]
    public function testPlaceRejectsNumbersThatAreNotPositive(int $eventId, ?int $sequence, ?int $stop): void
    {
        $service = new VisitListService(
            static fn (string $sql, array $binds): array => [],
            static fn (string $from, string $to, string $where, array $binds): array => [],
            execute: static function (string $sql, array $binds): void {
                self::fail('nothing is written');
            }
        );

        $this->expectException(DomainException::class);
        $service->place($eventId, $sequence, $stop);
    }
}
