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
use OpenEMR\Appointment\VisitList\VisitList;
use OpenEMR\Appointment\VisitList\VisitListBlock;
use OpenEMR\Appointment\VisitList\VisitListCopies;
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

    /**
     * A service whose appointment query returns the given rows, whose blocks are the given
     * fetchEvents() rows, and which records each statement it runs.
     *
     * @param list<array<string, mixed>>       $appointmentRows
     * @param list<array<string, mixed>>       $blockRows
     * @param list<array{string, array<mixed>}> $statements
     */
    private function placingService(array $appointmentRows, array $blockRows, array &$statements): VisitListService
    {
        return new VisitListService(
            function (string $sql, array $binds) use ($appointmentRows): array {
                $this->queries[] = [$sql, $binds];
                if (str_contains($sql, 'openemr_postcalendar_categories')) {
                    return [['pc_catid' => (string) self::CATEGORY_ID]];
                }
                return str_contains($sql, '`pc_multiple`') ? $appointmentRows : [];
            },
            static fn (string $from, string $to, string $where, array $binds): array => $blockRows,
            execute: static function (string $sql, array $binds) use (&$statements): void {
                $statements[] = [$sql, $binds];
            }
        );
    }

    /**
     * @return array<string, mixed>
     */
    private static function appointmentRow(int $eid, int $provider, string $date, string $start, int $multiple = 0): array
    {
        return [
            'pc_eid' => (string) $eid,
            'pc_aid' => (string) $provider,
            'pc_multiple' => (string) $multiple,
            'pc_eventDate' => $date,
            'pc_startTime' => $start,
        ];
    }

    public function testCopiesBeforeNotesTheGroupAndEachProvidersStart(): void
    {
        $statements = [];
        $service = $this->placingService(
            [self::appointmentRow(42, 7, '2026-03-09', '09:00:00', 77), self::appointmentRow(43, 8, '2026-03-09', '13:00:00', 77)],
            [],
            $statements
        );

        $copies = $service->copiesBefore(42);

        $this->assertSame(77, $copies->multiple);
        $this->assertSame('2026-03-09 13:00', $copies->startByProvider[8]->format('Y-m-d H:i'));
        $this->assertSame([], $statements, 'nothing is written');
    }

    public function testCopiesBeforeOfASingleProviderAppointmentHasNoGroup(): void
    {
        $statements = [];
        $service = $this->placingService([self::appointmentRow(42, 7, '2026-03-09', '09:00:00')], [], $statements);

        $this->assertNull($service->copiesBefore(42)->multiple);
    }

    public function testPlaceFindsTheCopiesByTheirGroupWhenTheOpenedCopyWasDeleted(): void
    {
        // The editor removed provider 7, deleting copy 42, and added provider 9 as copy 44.
        $statements = [];
        $service = $this->placingService(
            [self::appointmentRow(43, 8, '2026-03-09', '09:00:00', 77), self::appointmentRow(44, 9, '2026-03-09', '09:00:00', 77)],
            [],
            $statements
        );
        $before = new VisitListCopies(77, [
            7 => new DateTimeImmutable('2026-03-09 09:00'),
            8 => new DateTimeImmutable('2026-03-09 09:00'),
        ]);

        $service->place(42, 2, null, $before);

        $this->assertSame([42, 77], $this->queries[0][1], 'asks for the copies of the group noted before the save');
        $this->assertSame([[2, null, '09:00:00', 43], [2, null, '09:00:00', 44]], array_column($statements, 1));
    }

    public function testPlaceKeepsEachCopysOwnStartWhenItsProviderAndDateAreUnchanged(): void
    {
        // Provider 8's copy was on their 13:00 list; the editor wrote the opened copy's 9:00 to it.
        $statements = [];
        $service = $this->placingService(
            [self::appointmentRow(42, 7, '2026-03-09', '09:00:00', 77), self::appointmentRow(43, 8, '2026-03-09', '09:00:00', 77)],
            [
                self::blockRow(1, 7, '2026-03-09', '09:00:00', 14400),
                self::blockRow(2, 8, '2026-03-09', '09:00:00', 10800),
                self::blockRow(3, 8, '2026-03-09', '13:00:00', 14400),
            ],
            $statements
        );
        $before = new VisitListCopies(77, [
            7 => new DateTimeImmutable('2026-03-09 09:00'),
            8 => new DateTimeImmutable('2026-03-09 13:00'),
        ]);

        $service->place(42, 1, null, $before);

        $this->assertSame([[1, null, '09:00:00', 42], [1, null, '13:00:00', 43]], array_column($statements, 1));
    }

    public function testPlaceUsesTheSavedStartForACopyMovedToAnotherDate(): void
    {
        $statements = [];
        $service = $this->placingService(
            [self::appointmentRow(43, 8, '2026-03-10', '09:00:00', 77)],
            [self::blockRow(2, 8, '2026-03-10', '09:00:00', 14400)],
            $statements
        );
        $before = new VisitListCopies(77, [8 => new DateTimeImmutable('2026-03-09 13:00')]);

        $service->place(43, 1, null, $before);

        $this->assertSame([1, null, '09:00:00', 43], $statements[0][1]);
    }

    public function testCopiesGroupMustBePositive(): void
    {
        $this->expectException(DomainException::class);
        new VisitListCopies(0, []);
    }

    public function testPlaceMarksTheAppointmentAsAVisitListPatient(): void
    {
        $statements = [];
        $service = $this->placingService(
            [self::appointmentRow(42, 7, '2026-03-09', '09:00:00')],
            [self::blockRow(1, 7, '2026-03-09', '09:00:00', 28800)],
            $statements
        );

        $service->place(42, 3, null);

        $this->assertCount(1, $statements);
        $this->assertStringContainsString('`pc_visit_list` = 1', $statements[0][0]);
        $this->assertStringContainsString('`pc_duration` = 0', $statements[0][0], 'no time of its own');
        $this->assertSame([3, null, '09:00:00', 42], $statements[0][1], 'a block claims its time, so it keeps it');
    }

    public function testPlaceMarksEveryProvidersCopy(): void
    {
        $statements = [];
        $service = $this->placingService(
            [self::appointmentRow(42, 7, '2026-03-09', '09:00:00'), self::appointmentRow(43, 8, '2026-03-09', '09:00:00')],
            [self::blockRow(1, 7, '2026-03-09', '09:00:00', 28800), self::blockRow(2, 8, '2026-03-09', '09:00:00', 28800)],
            $statements
        );

        $service->place(42, null, 5);

        $this->assertSame([[null, 5, '09:00:00', 42], [null, 5, '09:00:00', 43]], array_column($statements, 1));
        $this->assertSame([42, 42], $this->queries[0][1], 'asks for the appointment and the copies sharing its pc_multiple');
    }

    public function testPlaceMovesAPatientWhoseDateHasABlockAtAnotherTime(): void
    {
        // Moved to a day whose block starts at 10:00: it goes to the start of that block.
        $statements = [];
        $service = $this->placingService(
            [self::appointmentRow(42, 7, '2026-03-10', '09:00:00')],
            [
                self::blockRow(1, 7, '2026-03-10', '14:00:00', 3600),
                self::blockRow(2, 7, '2026-03-10', '10:00:00', 7200),
            ],
            $statements
        );

        $service->place(42, 1, null);

        $this->assertSame([1, null, '10:00:00', 42], $statements[0][1], "the day's first block");
    }

    public function testPlaceKeepsTheTimeWhenTheDayHasNoBlock(): void
    {
        $statements = [];
        $service = $this->placingService([self::appointmentRow(42, 7, '2026-03-10', '09:30:00')], [], $statements);

        $service->place(42, 1, 2);

        $this->assertSame([1, 2, '09:30:00', 42], $statements[0][1]);
    }

    public function testNextOrderWithEqualStartsComesFromTheBlockThePatientGoesOn(): void
    {
        // Two blocks start at 9:00; the matcher puts patients on the lower pc_eid (4).
        $service = $this->service(
            [
                self::blockRow(5, 7, '2026-03-09', '09:00:00', 3600),
                self::blockRow(4, 7, '2026-03-09', '09:00:00', 3600),
            ],
            [self::entryRow(10, 7, '2026-03-09', '09:00:00', '4', null)]
        );

        $this->assertSame(5, $service->nextSequence(7, new DateTimeImmutable('2026-03-09 09:00')));
    }

    /**
     * A service that records each statement it runs and reads nothing.
     *
     * @param list<array{string, array<mixed>}> $statements
     */
    private static function writingService(array &$statements): VisitListService
    {
        return new VisitListService(
            static fn (string $sql, array $binds): array => [],
            static fn (string $from, string $to, string $where, array $binds): array => [],
            execute: static function (string $sql, array $binds) use (&$statements): void {
                $statements[] = [$sql, $binds];
            }
        );
    }

    /**
     * A list on a 9:00 block of provider 7 with patients of the given event IDs and Order #s, in that order.
     *
     * @param array<int, ?int> $sequences Order # by event ID
     */
    private static function list(array $sequences): VisitList
    {
        $start = new DateTimeImmutable('2026-03-09 09:00');
        $entries = [];
        foreach ($sequences as $eid => $sequence) {
            $entries[] = new VisitListEntry($eid, 500 + $eid, 7, $start, $sequence, null);
        }
        return new VisitList(new VisitListBlock(1, 7, 3, $start, $start->modify('+8 hours')), $entries);
    }

    public function testRenumberWritesOnlyTheNumbersThatChange(): void
    {
        $statements = [];
        // Patient 12 is saved as #1; patient 11 stays #3.
        self::writingService($statements)->renumber(self::list([10 => 1, 11 => 3, 12 => 3]), 12, 1);

        $this->assertSame([[2, 10], [1, 12]], array_column($statements, 1));
        $this->assertSame('UPDATE `openemr_postcalendar_events` SET `pc_visit_seq` = ? WHERE `pc_eid` = ?', $statements[0][0]);
    }

    public function testRenumberClosesGaps(): void
    {
        $statements = [];
        self::writingService($statements)->renumber(self::list([10 => 1, 11 => 4, 12 => null]));

        $this->assertSame([[2, 11], [3, 12]], array_column($statements, 1));
    }

    public function testRenumberOfAListInOrderWritesNothing(): void
    {
        $statements = [];
        self::writingService($statements)->renumber(self::list([10 => 1, 11 => 2]));

        $this->assertSame([], $statements);
    }

    public function testListOfAnAppointmentThatIsNotAVisitListPatientIsNull(): void
    {
        $statements = [];
        $this->assertNull(self::writingService($statements)->listOf(42));
    }

    public function testListOfFindsTheListThePatientIsOn(): void
    {
        $service = new VisitListService(
            static function (string $sql, array $binds): array {
                if (str_contains($sql, 'openemr_postcalendar_categories')) {
                    return [['pc_catid' => (string) self::CATEGORY_ID]];
                }
                if (str_contains($sql, '`pc_eid` = ?')) {
                    return [['pc_aid' => '7', 'pc_eventDate' => '2026-03-09', 'pc_startTime' => '09:00:00']];
                }
                return [self::entryRow(42, 7, '2026-03-09', '09:00:00', '1', null)];
            },
            static fn (string $from, string $to, string $where, array $binds): array => [self::blockRow(1, 7, '2026-03-09', '09:00:00', 3600)],
        );

        $list = $service->listOf(42);

        $this->assertNotNull($list);
        $this->assertSame(1, $list->block->eventId);
        $this->assertSame([42], self::eids($list->entries));
    }

    public function testListOfRejectsAnEventIdThatIsNotPositive(): void
    {
        $statements = [];
        $this->expectException(DomainException::class);
        self::writingService($statements)->listOf(0);
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
