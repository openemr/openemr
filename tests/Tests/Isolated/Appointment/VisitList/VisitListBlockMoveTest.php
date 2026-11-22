<?php

/**
 * Isolated VisitListBlockMove Test
 *
 * Where a saved block's patients go: a new start time, a new provider, each list to its
 * mapped date, dates with no counterpart, dates before today (left alone), and pairing
 * repeat-pattern dates in order (so "repeat on" series keep their pattern).
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
use OpenEMR\Appointment\VisitList\VisitList;
use OpenEMR\Appointment\VisitList\VisitListBlock;
use OpenEMR\Appointment\VisitList\VisitListBlockMove;
use OpenEMR\Appointment\VisitList\VisitListEntry;
use OpenEMR\Appointment\VisitList\VisitListMove;
use OpenEMR\Appointment\VisitList\VisitListMovePlan;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class VisitListBlockMoveTest extends TestCase
{
    private const TODAY = '2026-01-01';

    private static function block(int $provider, string $start, int $eid = 1): VisitListBlock
    {
        $begin = new DateTimeImmutable($start);
        return new VisitListBlock($eid, $provider, 3, $begin, $begin->modify('+4 hours'));
    }

    /**
     * A list on a block of this provider starting then, with patients of the given event IDs.
     *
     * @param list<int> $eids
     */
    private static function list(int $provider, string $start, array $eids): VisitList
    {
        $block = self::block($provider, $start);
        $entries = [];
        foreach ($eids as $index => $eid) {
            $entries[] = new VisitListEntry($eid, 500 + $eid, $provider, $block->start, $index + 1, null);
        }
        return new VisitList($block, $entries);
    }

    /**
     * Each move as "event ID → provider date time".
     *
     * @return list<string>
     */
    private static function moves(VisitListMovePlan $plan): array
    {
        return array_map(
            static fn (VisitListMove $move): string => $move->entry->eventId . ' → ' . $move->to->providerId . ' ' . $move->to->start->format('Y-m-d H:i'),
            $plan->moves
        );
    }

    /**
     * @param list<VisitListEntry> $entries
     * @return list<int>
     */
    private static function eids(array $entries): array
    {
        return array_map(static fn (VisitListEntry $entry): int => $entry->eventId, $entries);
    }

    public function testANewStartTimeMovesThePatientsToIt(): void
    {
        $plan = VisitListBlockMove::plan(
            [self::list(7, '2026-03-09 09:00', [10, 11])],
            [self::block(7, '2026-03-09 13:00')],
            [7 => 7],
            ['2026-03-09' => '2026-03-09'],
            self::TODAY
        );

        $this->assertSame(['10 → 7 2026-03-09 13:00', '11 → 7 2026-03-09 13:00'], self::moves($plan));
        $this->assertSame([], $plan->stranded);
    }

    public function testAnUnchangedStartMovesNobody(): void
    {
        // Only the length changed: the patients are still at the block's start.
        $plan = VisitListBlockMove::plan(
            [self::list(7, '2026-03-09 09:00', [10])],
            [self::block(7, '2026-03-09 09:00')],
            [7 => 7],
            ['2026-03-09' => '2026-03-09'],
            self::TODAY
        );

        $this->assertSame([], $plan->moves);
        $this->assertSame([], $plan->stranded);
    }

    public function testALengthChangeLeavesAPatientInsideTheBlockWhereItIs(): void
    {
        // A patient at 9:15 is on the 9:00 block too (VisitListBlock::claims()).
        $list = self::list(7, '2026-03-09 09:00', [10]);
        $inside = new VisitListEntry(11, 511, 7, new DateTimeImmutable('2026-03-09 09:15'), 2, null);
        $plan = VisitListBlockMove::plan(
            [new VisitList($list->block, [...$list->entries, $inside])],
            [self::block(7, '2026-03-09 09:00')],
            [7 => 7],
            ['2026-03-09' => '2026-03-09'],
            self::TODAY
        );

        $this->assertSame([], $plan->moves);
        $this->assertSame([], $plan->stranded);
    }

    public function testAMovedBlockTakesAPatientInsideItToItsNewStart(): void
    {
        $list = self::list(7, '2026-03-09 09:00', []);
        $inside = new VisitListEntry(11, 511, 7, new DateTimeImmutable('2026-03-09 09:15'), 1, null);
        $plan = VisitListBlockMove::plan(
            [new VisitList($list->block, [$inside])],
            [self::block(7, '2026-03-09 13:00')],
            [7 => 7],
            ['2026-03-09' => '2026-03-09'],
            self::TODAY
        );

        $this->assertSame(['11 → 7 2026-03-09 13:00'], self::moves($plan));
    }

    public function testANewProviderTakesThePatients(): void
    {
        $plan = VisitListBlockMove::plan(
            [self::list(7, '2026-03-09 09:00', [10])],
            [self::block(8, '2026-03-09 09:00')],
            [7 => 8],
            ['2026-03-09' => '2026-03-09'],
            self::TODAY
        );

        $this->assertSame(['10 → 8 2026-03-09 09:00'], self::moves($plan));
    }

    public function testEachListGoesToItsMappedDate(): void
    {
        // A weekly Monday block moved to Tuesdays.
        $plan = VisitListBlockMove::plan(
            [self::list(7, '2026-03-09 09:00', [10]), self::list(7, '2026-03-16 09:00', []), self::list(7, '2026-03-23 09:00', [12])],
            [self::block(7, '2026-03-24 09:00'), self::block(7, '2026-03-10 09:00'), self::block(7, '2026-03-17 09:00')],
            [7 => 7],
            ['2026-03-09' => '2026-03-10', '2026-03-16' => '2026-03-17', '2026-03-23' => '2026-03-24'],
            self::TODAY
        );

        $this->assertSame(['10 → 7 2026-03-10 09:00', '12 → 7 2026-03-24 09:00'], self::moves($plan));
    }

    public function testADateMappedToNothingOrToAnExcludedDateIsStranded(): void
    {
        // March 16 has no date after the save; March 23 maps to March 24, which the block is excluded from.
        $plan = VisitListBlockMove::plan(
            [self::list(7, '2026-03-09 09:00', [10]), self::list(7, '2026-03-16 09:00', [11, 12]), self::list(7, '2026-03-23 09:00', [13])],
            [self::block(7, '2026-03-09 13:00')],
            [7 => 7],
            ['2026-03-09' => '2026-03-09', '2026-03-23' => '2026-03-24'],
            self::TODAY
        );

        $this->assertSame(['10 → 7 2026-03-09 13:00'], self::moves($plan));
        $this->assertSame([11, 12, 13], self::eids($plan->stranded));
    }

    public function testAProviderMappedToNobodyStrandsTheirPatients(): void
    {
        $plan = VisitListBlockMove::plan(
            [self::list(7, '2026-03-09 09:00', [10])],
            [self::block(8, '2026-03-09 09:00')],
            [],
            ['2026-03-09' => '2026-03-09'],
            self::TODAY
        );

        $this->assertSame([], $plan->moves);
        $this->assertSame([10], self::eids($plan->stranded));
    }

    public function testEachProviderIsPairedSeparately(): void
    {
        $plan = VisitListBlockMove::plan(
            [self::list(7, '2026-03-09 09:00', [10]), self::list(8, '2026-03-09 09:00', [20])],
            [self::block(7, '2026-03-09 11:00', 1), self::block(8, '2026-03-09 11:00', 2)],
            [7 => 7, 8 => 8],
            ['2026-03-09' => '2026-03-09'],
            self::TODAY
        );

        $this->assertSame(['10 → 7 2026-03-09 11:00', '20 → 8 2026-03-09 11:00'], self::moves($plan));
    }

    public function testPatientsOnDatesBeforeTodayAreLeftWhereTheyAre(): void
    {
        // Saved for every date at a new time on March 10: March 9 is history, March 16 moves.
        $plan = VisitListBlockMove::plan(
            [self::list(7, '2026-03-09 09:00', [10, 11]), self::list(7, '2026-03-16 09:00', [12])],
            [self::block(7, '2026-03-09 13:00'), self::block(7, '2026-03-16 13:00')],
            [7 => 7],
            ['2026-03-09' => '2026-03-09', '2026-03-16' => '2026-03-16'],
            '2026-03-10'
        );

        $this->assertSame(['12 → 7 2026-03-16 13:00'], self::moves($plan));
        $this->assertSame([10, 11], self::eids($plan->past));
        $this->assertSame([], $plan->stranded);
    }

    public function testTodaysPatientsMove(): void
    {
        $plan = VisitListBlockMove::plan(
            [self::list(7, '2026-03-09 09:00', [10])],
            [self::block(7, '2026-03-09 13:00')],
            [7 => 7],
            ['2026-03-09' => '2026-03-09'],
            '2026-03-09'
        );

        $this->assertSame(['10 → 7 2026-03-09 13:00'], self::moves($plan));
        $this->assertSame([], $plan->past);
    }

    public function testDateMapPairsPatternDatesInOrder(): void
    {
        // Weekly Mondays moved to Tuesdays, given out of order.
        $this->assertSame(
            ['2026-03-09' => '2026-03-10', '2026-03-16' => '2026-03-17', '2026-03-23' => '2026-03-24'],
            VisitListBlockMove::dateMap(['2026-03-16', '2026-03-09', '2026-03-23'], ['2026-03-10', '2026-03-24', '2026-03-17'])
        );
    }

    public function testDateMapKeepsARepeatOnPattern(): void
    {
        // 2nd Monday of the month → 2nd Tuesday: not one day later every time.
        $this->assertSame(
            ['2026-03-09' => '2026-03-10', '2026-04-13' => '2026-04-14', '2026-05-11' => '2026-05-12'],
            VisitListBlockMove::dateMap(['2026-03-09', '2026-04-13', '2026-05-11'], ['2026-03-10', '2026-04-14', '2026-05-12'])
        );
    }

    public function testDateMapLeavesOutDatesPastTheShorterPattern(): void
    {
        $this->assertSame(
            ['2026-03-09' => '2026-03-10'],
            VisitListBlockMove::dateMap(['2026-03-09', '2026-03-16'], ['2026-03-10'])
        );
    }

    /**
     * @param list<int>       $before
     * @param list<int>       $after
     * @param array<int, int> $expected
     */
    #[DataProvider('providerMapProvider')]
    public function testProviderMap(array $before, array $after, array $expected): void
    {
        $map = VisitListBlockMove::providerMap($before, $after);
        ksort($map);

        $this->assertSame($expected, $map);
    }

    /**
     * @return array<string, array{list<int>, list<int>, array<int, int>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function providerMapProvider(): array
    {
        return [
            'unchanged' => [[7], [7], [7 => 7]],
            'replaced' => [[7], [8], [7 => 8]],
            'one kept, one replaced' => [[7, 8], [7, 9], [7 => 7, 8 => 9]],
            'replaced in order of ID' => [[8, 7], [12, 11], [7 => 11, 8 => 12]],
            'removed with no one added' => [[7, 8], [7], [7 => 7]],
            'added with no one removed' => [[7], [7, 8], [7 => 7]],
        ];
    }
}
