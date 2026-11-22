<?php

/**
 * Isolated VisitListOrder Test
 *
 * Renumbering a Visit List: putting a patient at a place, moving the others down, closing
 * gaps, settling duplicate numbers, and a place past the end.
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
use OpenEMR\Appointment\VisitList\VisitListOrder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class VisitListOrderTest extends TestCase
{
    private static function entry(int $eid, ?int $sequence): VisitListEntry
    {
        return new VisitListEntry($eid, 500 + $eid, 7, new DateTimeImmutable('2026-03-09 09:00'), $sequence, null);
    }

    /**
     * Patients 10, 11 and 12 numbered 1, 2 and 3, and patient 13 being placed.
     *
     * @return list<VisitListEntry>
     */
    private static function listWithNewPatient(): array
    {
        return [self::entry(10, 1), self::entry(11, 2), self::entry(12, 3), self::entry(13, 2)];
    }

    /**
     * @param array<int, int> $expected Order # by event ID
     */
    #[DataProvider('placeProvider')]
    public function testPutsThePatientInPlaceAndMovesTheOthersDown(?int $position, array $expected): void
    {
        $this->assertSame($expected, VisitListOrder::place(self::listWithNewPatient(), 13, $position));
    }

    /**
     * @return array<string, array{?int, array<int, int>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function placeProvider(): array
    {
        return [
            'first' => [1, [13 => 1, 10 => 2, 11 => 3, 12 => 4]],
            'middle' => [2, [10 => 1, 13 => 2, 11 => 3, 12 => 4]],
            'last place' => [4, [10 => 1, 11 => 2, 12 => 3, 13 => 4]],
            'past the end' => [9, [10 => 1, 11 => 2, 12 => 3, 13 => 4]],
            'no place' => [null, [10 => 1, 11 => 2, 12 => 3, 13 => 4]],
        ];
    }

    public function testMovingAPatientUpTheList(): void
    {
        $entries = [self::entry(10, 1), self::entry(11, 2), self::entry(12, 3), self::entry(13, 4)];

        $this->assertSame([10 => 1, 13 => 2, 11 => 3, 12 => 4], VisitListOrder::place($entries, 13, 2));
    }

    public function testMovingAPatientDownTheList(): void
    {
        // Patient 10 was #1 and is saved as #3: the list is read before the move, in its old order.
        $entries = [self::entry(10, 1), self::entry(11, 2), self::entry(12, 3), self::entry(13, 4)];

        $this->assertSame([11 => 1, 12 => 2, 10 => 3, 13 => 4], VisitListOrder::place($entries, 10, 3));
    }

    public function testThePlaceCountsPatientsNotTheirOldNumbers(): void
    {
        // Gaps (1, 5, 6): place 2 is after the first patient, not before #5.
        $entries = [self::entry(10, 1), self::entry(11, 5), self::entry(12, 6), self::entry(13, null)];

        $this->assertSame([10 => 1, 13 => 2, 11 => 3, 12 => 4], VisitListOrder::place($entries, 13, 2));
    }

    public function testWithoutAMovingPatientItClosesGapsAndSettlesDuplicates(): void
    {
        // In list order, as the matcher sorts them: two #2s, then a gap before #7, then one with no number.
        $entries = [self::entry(10, 2), self::entry(11, 2), self::entry(12, 7), self::entry(13, null)];

        $this->assertSame([10 => 1, 11 => 2, 12 => 3, 13 => 4], VisitListOrder::place($entries));
    }

    public function testAMovingPatientNotOnTheListChangesNothingElse(): void
    {
        $entries = [self::entry(10, 1), self::entry(11, 3)];

        $this->assertSame([10 => 1, 11 => 2], VisitListOrder::place($entries, 99, 1));
    }

    public function testAnEmptyList(): void
    {
        $this->assertSame([], VisitListOrder::place([], 13, 1));
    }

    public function testRejectsAPlaceBelowOne(): void
    {
        $this->expectException(DomainException::class);
        VisitListOrder::place(self::listWithNewPatient(), 13, 0);
    }
}
