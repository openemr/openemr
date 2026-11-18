<?php

/**
 * Isolated VisitListDayLoader Test
 *
 * Reading the day view's Visit Lists and appointment statuses without a database.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PostCalendar\ViewModel;

use OpenEMR\Appointment\VisitList\VisitListService;
use OpenEMR\PostCalendar\ViewModel\VisitListDayLoader;
use PHPUnit\Framework\TestCase;

class VisitListDayLoaderTest extends TestCase
{
    /** @var list<array{string, string, string, array<mixed>}> Each fetchEvents call. */
    private array $eventCalls = [];

    private function loader(): VisitListDayLoader
    {
        $service = new VisitListService(
            static fn (string $sql, array $binds): array
                => str_contains($sql, 'openemr_postcalendar_categories') ? [['pc_catid' => '21']] : [],
            function (string $from, string $to, string $where, array $binds): array {
                $this->eventCalls[] = [$from, $to, $where, $binds];
                return [[
                    'pc_eid' => '1',
                    'uprovider_id' => '7',
                    'pc_eventDate' => '2026-03-09',
                    'pc_startTime' => '09:00:00',
                    'pc_duration' => '28800',
                    'pc_facility' => '3',
                ]];
            }
        );
        return new VisitListDayLoader(
            $service,
            static fn (string $sql): array => str_contains($sql, "'apptstat'") ? [
                ['option_id' => '@', 'title' => 'Arrived', 'notes' => 'FF6619|10'],
                ['option_id' => '-', 'title' => 'None', 'notes' => 'FEFDCF|0'],
            ] : [],
            static fn (string $title): string => 'tr:' . $title
        );
    }

    public function testReadsTheDaysListsForTheProvidersShown(): void
    {
        $schedule = $this->loader()->schedule('20260309', [['id' => '7'], ['id' => 9], ['id' => '0'], ['name' => 'x'], 'junk']);

        $this->assertNotNull($schedule);
        $this->assertCount(1, $schedule->lists);
        $this->assertSame(1, $schedule->lists[0]->block->eventId);
        [$from, $to, , $binds] = $this->eventCalls[0];
        $this->assertSame('2026-03-09', $from);
        $this->assertSame('2026-03-09', $to);
        $this->assertSame([21, '7', '9'], $binds, 'the category, then the providers; rows without one are left out');
    }

    public function testAnUnreadableDateHasNoLists(): void
    {
        $this->assertNull($this->loader()->schedule('not a date', [['id' => '7']]));
        $this->assertSame([], $this->eventCalls);
    }

    public function testReadsTheStatusesTranslated(): void
    {
        $this->assertSame(
            [
                '@' => ['title' => 'tr:Arrived', 'color' => '#FF6619'],
                '-' => ['title' => 'tr:None', 'color' => '#FEFDCF'],
            ],
            $this->loader()->statuses()
        );
    }
}
