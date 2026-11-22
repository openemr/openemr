<?php

/**
 * Isolated VisitListRelease Test
 *
 * The times a deleted block's patients get: in list order, one interval apart from the
 * block's start, past the block's end if need be, but never past the day's last slot.
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
use OpenEMR\Appointment\VisitList\VisitListEntry;
use OpenEMR\Appointment\VisitList\VisitListRelease;
use PHPUnit\Framework\TestCase;

class VisitListReleaseTest extends TestCase
{
    /**
     * A one-hour block starting then, with patients of the given event IDs in this order.
     *
     * @param list<int> $eids
     */
    private static function list(string $start, array $eids): VisitList
    {
        $begin = new DateTimeImmutable($start);
        $entries = [];
        foreach ($eids as $index => $eid) {
            $entries[] = new VisitListEntry($eid, 500 + $eid, 7, $begin, $index + 1, null);
        }
        return new VisitList(new VisitListBlock(1, 7, 3, $begin, $begin->modify('+1 hour')), $entries);
    }

    /**
     * @param array<int, DateTimeImmutable> $starts
     * @return array<int, string>
     */
    private static function times(array $starts): array
    {
        return array_map(static fn (DateTimeImmutable $start): string => $start->format('H:i'), $starts);
    }

    public function testPatientsAreOneIntervalApartInListOrder(): void
    {
        $starts = VisitListRelease::starts(self::list('2026-03-09 09:00', [12, 10, 11]), 15);

        $this->assertSame([12 => '09:00', 10 => '09:15', 11 => '09:30'], self::times($starts));
    }

    public function testTimesRunPastTheBlocksEnd(): void
    {
        $starts = VisitListRelease::starts(self::list('2026-03-09 09:00', [1, 2, 3, 4, 5]), 20);

        $this->assertSame('10:20', $starts[5]->format('H:i'));
    }

    public function testPatientsPastTheEndOfTheDayShareTheLastSlot(): void
    {
        $starts = VisitListRelease::starts(self::list('2026-03-09 23:15', [1, 2, 3, 4]), 15);

        $this->assertSame([1 => '23:15', 2 => '23:30', 3 => '23:45', 4 => '23:45'], self::times($starts));
        $this->assertSame('2026-03-09', $starts[4]->format('Y-m-d'));
    }

    public function testABlockStartingInTheLastSlotKeepsItsStart(): void
    {
        $starts = VisitListRelease::starts(self::list('2026-03-09 23:50', [1, 2]), 15);

        $this->assertSame([1 => '23:50', 2 => '23:50'], self::times($starts));
    }

    public function testAnEmptyListHasNoTimes(): void
    {
        $this->assertSame([], VisitListRelease::starts(self::list('2026-03-09 09:00', []), 15));
    }

    public function testTheIntervalMustBePositive(): void
    {
        $this->expectException(DomainException::class);
        VisitListRelease::starts(self::list('2026-03-09 09:00', [1]), 0);
    }
}
