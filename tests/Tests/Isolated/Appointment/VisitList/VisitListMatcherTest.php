<?php

/**
 * Isolated VisitListMatcher Test
 *
 * Putting Visit List patients on their blocks by provider, date and time window, choosing
 * between overlapping blocks, and ordering each list by list order, then stop number.
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
use OpenEMR\Appointment\VisitList\VisitListBlock;
use OpenEMR\Appointment\VisitList\VisitListEntry;
use OpenEMR\Appointment\VisitList\VisitListMatcher;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class VisitListMatcherTest extends TestCase
{
    private const DATE = '2026-03-09';

    private static function block(int $eid, int $provider, string $start, string $end, string $date = self::DATE): VisitListBlock
    {
        return new VisitListBlock(
            $eid,
            $provider,
            3,
            new DateTimeImmutable($date . ' ' . $start),
            new DateTimeImmutable($date . ' ' . $end)
        );
    }

    private static function entry(
        int $eid,
        int $provider,
        string $start,
        ?int $sequence = null,
        ?int $stop = null,
        string $date = self::DATE
    ): VisitListEntry {
        return new VisitListEntry($eid, 500 + $eid, $provider, new DateTimeImmutable($date . ' ' . $start), $sequence, $stop);
    }

    /**
     * @param list<VisitListEntry> $entries
     * @return list<int>
     */
    private static function eids(array $entries): array
    {
        return array_map(static fn (VisitListEntry $entry): int => $entry->eventId, $entries);
    }

    public function testPatientInsideTheWindowGoesOnTheBlock(): void
    {
        $schedule = (new VisitListMatcher())->match(
            [self::block(1, 7, '09:00', '17:00')],
            [self::entry(10, 7, '09:00', 1), self::entry(11, 7, '12:30', 2)]
        );

        $this->assertCount(1, $schedule->lists);
        $this->assertSame([10, 11], self::eids($schedule->lists[0]->entries));
        $this->assertSame([], $schedule->unplaced);
    }

    /**
     * @return array<string, array{VisitListEntry}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function unplacedEntryProvider(): array
    {
        return [
            'another provider' => [self::entry(10, 8, '10:00', 1)],
            'another date' => [self::entry(10, 7, '10:00', 1, null, '2026-03-10')],
            'before the block starts' => [self::entry(10, 7, '08:59', 1)],
            'when the block ends' => [self::entry(10, 7, '17:00', 1)],
        ];
    }

    #[DataProvider('unplacedEntryProvider')]
    public function testPatientOutsideTheBlockIsUnplaced(VisitListEntry $entry): void
    {
        $schedule = (new VisitListMatcher())->match([self::block(1, 7, '09:00', '17:00')], [$entry]);

        $this->assertSame([], $schedule->lists[0]->entries);
        $this->assertSame([10], self::eids($schedule->unplaced));
    }

    public function testBlockWithoutLengthClaimsPatientsAtItsStart(): void
    {
        $schedule = (new VisitListMatcher())->match(
            [self::block(1, 7, '09:00', '09:00')],
            [self::entry(10, 7, '09:00', 1), self::entry(11, 7, '09:15', 2)]
        );

        $this->assertSame([10], self::eids($schedule->lists[0]->entries));
        $this->assertSame([11], self::eids($schedule->unplaced));
    }

    public function testBackToBackBlocksEachKeepTheirOwnPatients(): void
    {
        $schedule = (new VisitListMatcher())->match(
            [self::block(2, 7, '13:00', '17:00'), self::block(1, 7, '09:00', '13:00')],
            [self::entry(10, 7, '09:00', 1), self::entry(11, 7, '13:00', 1)]
        );

        $this->assertSame(1, $schedule->lists[0]->block->eventId);
        $this->assertSame([10], self::eids($schedule->lists[0]->entries));
        $this->assertSame(2, $schedule->lists[1]->block->eventId);
        $this->assertSame([11], self::eids($schedule->lists[1]->entries));
    }

    public function testOverlappingBlocksGivePatientToTheLatestStartedBlock(): void
    {
        $schedule = (new VisitListMatcher())->match(
            [self::block(1, 7, '09:00', '17:00'), self::block(2, 7, '12:00', '15:00')],
            [self::entry(10, 7, '10:00', 1), self::entry(11, 7, '12:00', 1)]
        );

        $this->assertSame([10], self::eids($schedule->lists[0]->entries));
        $this->assertSame([11], self::eids($schedule->lists[1]->entries));
    }

    public function testBlocksStartingTogetherGivePatientToTheLowerEventId(): void
    {
        $schedule = (new VisitListMatcher())->match(
            [self::block(5, 7, '09:00', '17:00'), self::block(4, 7, '09:00', '12:00')],
            [self::entry(10, 7, '09:00', 1)]
        );

        $this->assertSame(4, $schedule->lists[0]->block->eventId);
        $this->assertSame([10], self::eids($schedule->lists[0]->entries));
        $this->assertSame([], $schedule->lists[1]->entries);
    }

    public function testRepeatingBlockDatesShareAnEventIdButKeepTheirOwnPatients(): void
    {
        $schedule = (new VisitListMatcher())->match(
            [self::block(1, 7, '09:00', '17:00', '2026-03-16'), self::block(1, 7, '09:00', '17:00')],
            [self::entry(10, 7, '09:00', 1, null, '2026-03-16'), self::entry(11, 7, '09:00', 1)]
        );

        $this->assertSame(self::DATE, $schedule->lists[0]->block->date());
        $this->assertSame([11], self::eids($schedule->lists[0]->entries));
        $this->assertSame('2026-03-16', $schedule->lists[1]->block->date());
        $this->assertSame([10], self::eids($schedule->lists[1]->entries));
    }

    public function testListsAreOrderedByProviderThenStart(): void
    {
        $schedule = (new VisitListMatcher())->match(
            [self::block(3, 9, '08:00', '12:00'), self::block(2, 7, '13:00', '17:00'), self::block(1, 7, '09:00', '12:00')],
            []
        );

        $this->assertSame([1, 2, 3], array_map(
            static fn ($list): int => $list->block->eventId,
            $schedule->lists
        ));
    }

    public function testListIsOrderedByListOrderNotStopNumber(): void
    {
        $schedule = (new VisitListMatcher())->match(
            [self::block(1, 7, '09:00', '17:00')],
            [
                self::entry(10, 7, '09:00', 3, 1),
                self::entry(11, 7, '09:00', 1, 2),
                self::entry(12, 7, '09:00', 2, 2),
            ]
        );

        $this->assertSame([11, 12, 10], self::eids($schedule->lists[0]->entries));
    }

    public function testTiesAndMissingValuesSortLast(): void
    {
        $schedule = (new VisitListMatcher())->match(
            [self::block(1, 7, '09:00', '17:00')],
            [
                self::entry(15, 7, '09:00'),
                self::entry(14, 7, '09:00', 2),
                self::entry(13, 7, '09:00', 2, 4),
                self::entry(12, 7, '09:00', 2, 4),
                self::entry(11, 7, '09:00', 2, 3),
                self::entry(10, 7, '09:00', 1),
            ]
        );

        $this->assertSame([10, 11, 12, 13, 14, 15], self::eids($schedule->lists[0]->entries));
    }

    public function testUrgentCareListWithoutStopNumbersUsesListOrder(): void
    {
        $schedule = (new VisitListMatcher())->match(
            [self::block(1, 7, '08:00', '20:00')],
            [self::entry(12, 7, '08:00', 2), self::entry(10, 7, '08:00', 3), self::entry(11, 7, '08:00', 1)]
        );

        $this->assertSame([11, 12, 10], self::eids($schedule->lists[0]->entries));
    }

    public function testUnplacedPatientsAreInListOrder(): void
    {
        $schedule = (new VisitListMatcher())->match(
            [],
            [self::entry(10, 7, '09:00', 2), self::entry(11, 7, '09:00', 1)]
        );

        $this->assertSame([], $schedule->lists);
        $this->assertSame([11, 10], self::eids($schedule->unplaced));
    }

    public function testListForFindsAPatientsList(): void
    {
        $schedule = (new VisitListMatcher())->match(
            [self::block(1, 7, '09:00', '12:00'), self::block(2, 7, '13:00', '17:00')],
            [self::entry(10, 7, '14:00', 1), self::entry(11, 7, '18:00', 1)]
        );

        $this->assertSame(2, $schedule->listFor(10)?->block->eventId);
        $this->assertNull($schedule->listFor(11));
        $this->assertNull($schedule->listFor(99));
    }

    public function testBlockCannotEndBeforeItStarts(): void
    {
        $this->expectException(DomainException::class);
        self::block(1, 7, '17:00', '09:00');
    }

    /**
     * @return array<string, array{int, int, int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function invalidEntryIdProvider(): array
    {
        return [
            'event' => [0, 1, 1],
            'patient' => [1, 0, 1],
            'provider' => [1, 1, 0],
        ];
    }

    #[DataProvider('invalidEntryIdProvider')]
    public function testEntryIdsMustBePositive(int $eid, int $pid, int $provider): void
    {
        $this->expectException(DomainException::class);
        new VisitListEntry($eid, $pid, $provider, new DateTimeImmutable(self::DATE . ' 09:00'), 1, null);
    }

    /**
     * @return array<string, array{int, int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function invalidBlockIdProvider(): array
    {
        return [
            'event' => [0, 1],
            'provider' => [1, 0],
        ];
    }

    #[DataProvider('invalidBlockIdProvider')]
    public function testBlockIdsMustBePositive(int $eid, int $provider): void
    {
        $this->expectException(DomainException::class);
        self::block($eid, $provider, '09:00', '17:00');
    }
}
