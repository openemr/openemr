<?php

/**
 * VisitListService Database Test
 *
 * Visit Lists read from a real database, with repeating blocks worked out by the calendar's
 * own fetchEvents(): a weekly block with one date excluded and that date edited into its own
 * block (as editing "this date only" does), patients on each, and rows the lists leave out.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Appointment\VisitList;

use DateTimeImmutable;
use OpenEMR\Appointment\VisitList\VisitListEntry;
use OpenEMR\Appointment\VisitList\VisitListService;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\TestCase;

class VisitListServiceDatabaseTest extends TestCase
{
    private const MARKER = 'test-fixture-visit-list';

    private VisitListService $service;
    private int $categoryId;
    private int $providerId;
    private int $otherProviderId;

    /** @var array<string, int> pc_eid of each seeded event, by name */
    private array $eids = [];

    protected function setUp(): void
    {
        $this->service = new VisitListService();
        $categoryId = $this->service->categoryId();
        $this->assertNotNull($categoryId, 'the Visit List category is installed');
        $this->categoryId = $categoryId;

        $this->providerId = $this->insertProvider('a');
        $this->otherProviderId = $this->insertProvider('b');

        // Weekly on Mondays through March 2031, 9:00 for 8 hours, with March 17 excluded...
        $this->eids['weekly'] = $this->insertEvent($this->providerId, $this->categoryId, '', '2031-03-03', '09:00:00', 28800, [
            'recurrtype' => 1,
            'endDate' => '2031-03-31',
            'exdate' => '20310317',
        ]);
        // ...because that date was edited into its own block, 10:00 for 2 hours.
        $this->eids['edited'] = $this->insertEvent($this->providerId, $this->categoryId, '', '2031-03-17', '10:00:00', 7200);
        $this->eids['other block'] = $this->insertEvent($this->otherProviderId, $this->categoryId, '', '2031-03-10', '09:00:00', 28800);

        $patient = '1';
        $this->eids['second'] = $this->insertEvent($this->providerId, 5, $patient, '2031-03-10', '09:00:00', 0, ['visitList' => 1, 'seq' => 2, 'stop' => 1]);
        $this->eids['first'] = $this->insertEvent($this->providerId, 5, $patient, '2031-03-10', '09:00:00', 0, ['visitList' => 1, 'seq' => 1, 'stop' => 1]);
        $this->eids['on edited'] = $this->insertEvent($this->providerId, 5, $patient, '2031-03-17', '10:00:00', 0, ['visitList' => 1, 'seq' => 1]);
        $this->eids['left behind'] = $this->insertEvent($this->providerId, 5, $patient, '2031-03-17', '09:00:00', 0, ['visitList' => 1, 'seq' => 1]);
        $this->eids['other provider'] = $this->insertEvent($this->otherProviderId, 5, $patient, '2031-03-10', '09:00:00', 0, ['visitList' => 1, 'seq' => 1]);
        $this->eids['inactive'] = $this->insertEvent($this->providerId, 5, $patient, '2031-03-10', '09:00:00', 0, ['visitList' => 1, 'seq' => 3, 'eventStatus' => 0]);
        $this->eids['timed'] = $this->insertEvent($this->providerId, 5, $patient, '2031-03-10', '09:00:00', 900);
    }

    protected function tearDown(): void
    {
        QueryUtils::sqlStatementThrowException('DELETE FROM openemr_postcalendar_events WHERE pc_title = ?', [self::MARKER]);
        QueryUtils::sqlStatementThrowException('DELETE FROM users WHERE username LIKE ?', [self::MARKER . '%']);
    }

    private function insertProvider(string $suffix): int
    {
        return QueryUtils::sqlInsert(
            'INSERT INTO users (username, fname, lname, authorized, active) VALUES (?, ?, ?, 1, 1)',
            [self::MARKER . '-' . $suffix, 'Visit', 'List ' . $suffix]
        );
    }

    /**
     * @param array{recurrtype?: int, endDate?: string, exdate?: string, visitList?: int, seq?: int, stop?: int, eventStatus?: int} $options
     */
    private function insertEvent(int $providerId, int $categoryId, string $pid, string $date, string $start, int $duration, array $options = []): int
    {
        $recurrspec = serialize([
            'event_repeat_freq' => '1',
            'event_repeat_freq_type' => '1',
            'event_repeat_on_num' => '1',
            'event_repeat_on_day' => '0',
            'event_repeat_on_freq' => '0',
            'exdate' => $options['exdate'] ?? '',
        ]);
        return QueryUtils::sqlInsert(
            'INSERT INTO openemr_postcalendar_events (uuid, pc_catid, pc_multiple, pc_aid, pc_pid, pc_title, pc_time, pc_eventDate, pc_endDate,'
            . ' pc_duration, pc_recurrtype, pc_recurrspec, pc_startTime, pc_eventstatus, pc_facility, pc_visit_list, pc_visit_seq, pc_visit_stop)'
            . ' VALUES (UNHEX(REPLACE(UUID(), \'-\', \'\')), ?, 0, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, 3, ?, ?, ?)',
            [
                $categoryId,
                (string) $providerId,
                $pid,
                self::MARKER,
                $date,
                $options['endDate'] ?? '0000-00-00',
                $duration,
                $options['recurrtype'] ?? 0,
                $recurrspec,
                $start,
                $options['eventStatus'] ?? 1,
                $options['visitList'] ?? 0,
                $options['seq'] ?? null,
                $options['stop'] ?? null,
            ]
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

    public function testListsComeFromRepeatingAndEditedBlocks(): void
    {
        $schedule = $this->service->forDates(
            new DateTimeImmutable('2031-03-09'),
            new DateTimeImmutable('2031-03-22'),
            [$this->providerId]
        );

        $this->assertCount(2, $schedule->lists, 'the weekly block on March 10, and the edited block instead of the weekly one on March 17');

        [$march10, $march17] = $schedule->lists;
        $this->assertSame($this->eids['weekly'], $march10->block->eventId);
        $this->assertSame('2031-03-10 09:00', $march10->block->start->format('Y-m-d H:i'));
        $this->assertSame('2031-03-10 17:00', $march10->block->end->format('Y-m-d H:i'));
        $this->assertSame([$this->eids['first'], $this->eids['second']], self::eids($march10->entries));
        $this->assertSame(1, $march10->entries[0]->stop);

        $this->assertSame($this->eids['edited'], $march17->block->eventId);
        $this->assertSame([$this->eids['on edited']], self::eids($march17->entries));

        $this->assertSame([$this->eids['left behind']], self::eids($schedule->unplaced));
    }

    public function testEachProviderGetsOnlyTheirOwnLists(): void
    {
        $schedule = $this->service->forDates(
            new DateTimeImmutable('2031-03-10'),
            new DateTimeImmutable('2031-03-10'),
            [$this->providerId, $this->otherProviderId]
        );

        $this->assertCount(2, $schedule->lists);
        $this->assertSame($this->providerId, $schedule->lists[0]->block->providerId);
        $this->assertSame([$this->eids['first'], $this->eids['second']], self::eids($schedule->lists[0]->entries));
        $this->assertSame($this->otherProviderId, $schedule->lists[1]->block->providerId);
        $this->assertSame([$this->eids['other provider']], self::eids($schedule->lists[1]->entries));
        $this->assertSame([], $schedule->unplaced);
    }
}
