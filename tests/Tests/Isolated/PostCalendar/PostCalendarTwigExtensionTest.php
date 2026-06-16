<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PostCalendar;

use OpenEMR\PostCalendar\PostCalendarTwigExtension;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Twig\TwigFunction;

#[Group('isolated')]
#[Group('postcalendar')]
final class PostCalendarTwigExtensionTest extends TestCase
{
    public function testGetFunctionsRegistersExpectedNames(): void
    {
        $extension = new PostCalendarTwigExtension();
        $names = array_map(
            static fn (TwigFunction $f): string => $f->getName(),
            $extension->getFunctions()
        );

        self::assertContains('pc_sort_events', $names);
    }

    public function testPcSortEventsReturnsEmptyArrayForEmptyInput(): void
    {
        $extension = new PostCalendarTwigExtension();
        self::assertSame([], $extension->pcSortEvents([]));
    }

    public function testPcSortEventsSortsByStartTimeAscendingWithinADate(): void
    {
        $extension = new PostCalendarTwigExtension();

        $input = [
            '2026-06-01' => [
                ['id' => 'a', 'startTime' => '14:00'],
                ['id' => 'b', 'startTime' => '08:30'],
                ['id' => 'c', 'startTime' => '11:15'],
            ],
        ];

        $sorted = $extension->pcSortEvents($input);

        self::assertSame(
            ['b', 'c', 'a'],
            array_column($sorted['2026-06-01'], 'id')
        );
    }

    public function testPcSortEventsSortsEachDateBucketIndependently(): void
    {
        $extension = new PostCalendarTwigExtension();

        $input = [
            '2026-06-01' => [
                ['id' => 'jun-1-late',  'startTime' => '16:00'],
                ['id' => 'jun-1-early', 'startTime' => '09:00'],
            ],
            '2026-06-02' => [
                ['id' => 'jun-2-late',  'startTime' => '13:00'],
                ['id' => 'jun-2-early', 'startTime' => '07:45'],
            ],
        ];

        $sorted = $extension->pcSortEvents($input);

        self::assertSame(['jun-1-early', 'jun-1-late'], array_column($sorted['2026-06-01'], 'id'));
        self::assertSame(['jun-2-early', 'jun-2-late'], array_column($sorted['2026-06-02'], 'id'));
    }

    public function testPcSortEventsTreatsMissingStartTimeAsLowest(): void
    {
        // The legacy Smarty sort comparator (sort_byTimeA) used a one-sided
        // null coalesce: `$a['startTime'] < ($b['startTime'] ?? null)`. We
        // tightened that to coalesce both sides to empty string, which makes
        // events without a startTime sort first — closest to the legacy
        // behaviour without the asymmetry that the original suffered.
        $extension = new PostCalendarTwigExtension();

        $input = [
            '2026-06-01' => [
                ['id' => 'has-time',   'startTime' => '10:00'],
                ['id' => 'no-time'],
                ['id' => 'later-time', 'startTime' => '15:00'],
            ],
        ];

        $sorted = $extension->pcSortEvents($input);

        self::assertSame(
            ['no-time', 'has-time', 'later-time'],
            array_column($sorted['2026-06-01'], 'id')
        );
    }

    public function testPcSortEventsPreservesDateKeys(): void
    {
        $extension = new PostCalendarTwigExtension();

        $input = [
            '2026-06-01' => [['id' => 'x', 'startTime' => '10:00']],
            '2026-06-15' => [['id' => 'y', 'startTime' => '11:00']],
        ];

        $sorted = $extension->pcSortEvents($input);

        self::assertSame(['2026-06-01', '2026-06-15'], array_keys($sorted));
    }

}
