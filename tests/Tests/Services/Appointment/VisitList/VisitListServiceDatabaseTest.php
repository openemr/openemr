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
use DateTimeZone;
use Lcobucci\Clock\FrozenClock;
use OpenEMR\Appointment\VisitList\VisitListBlockRelease;
use OpenEMR\Appointment\VisitList\VisitListBlockSave;
use OpenEMR\Appointment\VisitList\VisitListEditor;
use OpenEMR\Appointment\VisitList\VisitListEntry;
use OpenEMR\Appointment\VisitList\VisitListMoveResult;
use OpenEMR\Appointment\VisitList\VisitListPostedBlock;
use OpenEMR\Appointment\VisitList\VisitListRepeatScope;
use OpenEMR\Appointment\VisitList\VisitListService;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\AppointmentService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;

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
        // Before every fixture date, so no patient is on a past date unless a test says so.
        $this->service = new VisitListService(clock: new FrozenClock(new DateTimeImmutable('2031-01-01 12:00', new DateTimeZone('UTC'))));
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
     * @param array{recurrtype?: int, endDate?: string, exdate?: string, visitList?: int, seq?: int, stop?: int, eventStatus?: int, multiple?: int} $options
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
            . ' pc_duration, pc_recurrtype, pc_recurrspec, pc_startTime, pc_endTime, pc_eventstatus, pc_facility, pc_visit_list, pc_visit_seq, pc_visit_stop)'
            . ' VALUES (UNHEX(REPLACE(UUID(), \'-\', \'\')), ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ADDTIME(?, SEC_TO_TIME(?)), ?, 3, ?, ?, ?)',
            [
                $categoryId,
                $options['multiple'] ?? 0,
                (string) $providerId,
                $pid,
                self::MARKER,
                $date,
                $options['endDate'] ?? null,
                $duration,
                $options['recurrtype'] ?? 0,
                $recurrspec,
                $start,
                $start,
                $duration,
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

    public function testNextOrderAndPlacingAPatientOnTheList(): void
    {
        $start = new DateTimeImmutable('2031-03-10 09:00');
        $this->assertSame(3, $this->service->nextSequence($this->providerId, $start), 'after #1 and #2; the inactive #3 does not count');

        $this->service->place($this->eids['timed'], 3, 2);

        $schedule = $this->service->forDates($start, $start, [$this->providerId]);
        $this->assertSame(
            [$this->eids['first'], $this->eids['second'], $this->eids['timed']],
            self::eids($schedule->lists[0]->entries)
        );
        $this->assertSame(2, $schedule->lists[0]->entries[2]->stop);
        $this->assertSame(
            [['pc_duration' => 0, 'pc_endTime' => '09:00:00']],
            QueryUtils::fetchRecords('SELECT pc_duration, pc_endTime FROM openemr_postcalendar_events WHERE pc_eid = ?', [$this->eids['timed']]),
            'its 15 minutes are gone'
        );
        $this->assertSame(4, $this->service->nextSequence($this->providerId, $start));
    }

    public function testPlacingAPatientMovedToADayWithALaterBlockPutsThemOnIt(): void
    {
        // March 17's block was edited to 10:00; the patient left at 9:00 is not on it.
        $this->service->place($this->eids['left behind'], 2, null);

        $schedule = $this->service->forDates(new DateTimeImmutable('2031-03-17'), new DateTimeImmutable('2031-03-17'), [$this->providerId]);
        $this->assertSame([$this->eids['on edited'], $this->eids['left behind']], self::eids($schedule->lists[0]->entries));
        $this->assertSame([], $schedule->unplaced);
    }

    public function testSavingAMultiProviderPatientKeepsTheOtherCopyOnItsOwnList(): void
    {
        // The other provider also has a 13:00 block on March 10, and their copy is on it.
        $this->insertEvent($this->otherProviderId, $this->categoryId, '', '2031-03-10', '13:00:00', 14400);
        $maxRows = QueryUtils::fetchRecords('SELECT MAX(pc_multiple) AS m FROM openemr_postcalendar_events', []);
        $max = $maxRows[0]['m'] ?? null;
        $multiple = (is_numeric($max) ? (int) $max : 0) + 1;
        $opened = $this->insertEvent($this->providerId, 5, '1', '2031-03-10', '09:00:00', 0, ['visitList' => 1, 'seq' => 1, 'multiple' => $multiple]);
        $other = $this->insertEvent($this->otherProviderId, 5, '1', '2031-03-10', '13:00:00', 0, ['visitList' => 1, 'seq' => 1, 'multiple' => $multiple]);

        $before = $this->service->copiesBefore($opened);
        // What the editor does when the opened copy's provider is removed: it deletes that copy
        // and writes the opened copy's time to every remaining copy.
        QueryUtils::sqlStatementThrowException('DELETE FROM openemr_postcalendar_events WHERE pc_eid = ?', [$opened]);
        QueryUtils::sqlStatementThrowException(
            'UPDATE openemr_postcalendar_events SET pc_startTime = ? WHERE pc_multiple = ?',
            ['09:00:00', $multiple]
        );

        $this->service->place($opened, 4, null, $before);

        $this->assertSame(
            [['pc_startTime' => '13:00:00', 'pc_visit_seq' => 1]],
            QueryUtils::fetchRecords('SELECT pc_startTime, pc_visit_seq FROM openemr_postcalendar_events WHERE pc_eid = ?', [$other]),
            'saved as #4, but it is the only patient on its list'
        );
    }

    /**
     * Order # of each patient on the provider's list on a date, in list order.
     *
     * @return array<int, ?int>
     */
    private function sequencesOn(string $date): array
    {
        $schedule = $this->service->forDates(new DateTimeImmutable($date), new DateTimeImmutable($date), [$this->providerId]);
        $sequences = [];
        foreach ($schedule->lists[0]->entries ?? [] as $entry) {
            $sequences[$entry->eventId] = $entry->sequence;
        }
        return $sequences;
    }

    public function testSavingAPatientAtAPlaceMovesTheOthersDown(): void
    {
        $this->service->place($this->eids['timed'], 1, null);

        $this->assertSame(
            [$this->eids['timed'] => 1, $this->eids['first'] => 2, $this->eids['second'] => 3],
            $this->sequencesOn('2031-03-10')
        );
    }

    public function testMovingAPatientDownTheList(): void
    {
        $this->service->place($this->eids['timed'], null, null);
        $before = $this->service->copiesBefore($this->eids['first']);

        $this->service->place($this->eids['first'], 2, 1, $before);

        $this->assertSame(
            [$this->eids['second'] => 1, $this->eids['first'] => 2, $this->eids['timed'] => 3],
            $this->sequencesOn('2031-03-10')
        );
    }

    public function testMovingAPatientToAnotherDayClosesTheGapTheyLeft(): void
    {
        $before = $this->service->copiesBefore($this->eids['first']);
        // What the editor writes when the date is changed to the next Monday.
        QueryUtils::sqlStatementThrowException(
            'UPDATE openemr_postcalendar_events SET pc_eventDate = ? WHERE pc_eid = ?',
            ['2031-03-24', $this->eids['first']]
        );

        $this->service->place($this->eids['first'], 1, 1, $before);

        $this->assertSame([$this->eids['second'] => 1], $this->sequencesOn('2031-03-10'), '#2 moves up to #1');
        $this->assertSame([$this->eids['first'] => 1], $this->sequencesOn('2031-03-24'));
    }

    public function testRenumberLeavesAPatientThatLeftTheListMeanwhile(): void
    {
        $list = $this->service->listOf($this->eids['first']);
        $this->assertNotNull($list);
        // Another save moves the second patient to the next Monday's list as #3.
        QueryUtils::sqlStatementThrowException(
            'UPDATE openemr_postcalendar_events SET pc_eventDate = ?, pc_visit_seq = 3 WHERE pc_eid = ?',
            ['2031-03-24', $this->eids['second']]
        );

        // The list as read before that move would put the second patient at #1.
        $this->service->renumber($list, $this->eids['second'], 1);

        $this->assertSame([$this->eids['first'] => 2], $this->sequencesOn('2031-03-10'));
        $this->assertSame([$this->eids['second'] => 3], $this->sequencesOn('2031-03-24'));
    }

    public function testDeletingAPatientClosesTheGap(): void
    {
        $this->service->place($this->eids['timed'], null, null);

        (new AppointmentService())->deleteAppointmentRecord($this->eids['first']);

        $this->assertSame(
            [$this->eids['second'] => 1, $this->eids['timed'] => 2],
            $this->sequencesOn('2031-03-10')
        );
    }

    /**
     * Each patient's date, start and provider, by event ID.
     *
     * @param list<int> $eids
     * @return array<int, string> "date start provider"
     */
    private function whereAre(array $eids): array
    {
        $where = [];
        foreach ($eids as $eid) {
            $rows = QueryUtils::fetchRecords('SELECT pc_eventDate, pc_startTime, pc_aid FROM openemr_postcalendar_events WHERE pc_eid = ?', [$eid]);
            $row = $rows[0] ?? [];
            $date = $row['pc_eventDate'] ?? '';
            $start = $row['pc_startTime'] ?? '';
            $provider = $row['pc_aid'] ?? null;
            $provider = is_int($provider) || is_string($provider)
                ? filter_var($provider, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
                : false;
            $where[$eid] = (is_string($date) ? $date : '') . ' ' . (is_string($start) ? $start : '')
                . ' ' . ($provider === $this->providerId ? 'a' : ($provider === $this->otherProviderId ? 'b' : 'other'));
        }
        return $where;
    }

    private function blockSave(VisitListRepeatScope $scope, string $selectedDate): VisitListBlockSave
    {
        $save = $this->service->blockSave($this->eids['weekly'], $scope, $selectedDate);
        $this->assertNotNull($save, 'the weekly block has patients');
        return $save;
    }

    public function testSavingEveryDateAtANewTimeMovesThePatients(): void
    {
        $save = $this->blockSave(VisitListRepeatScope::AllDates, '2031-03-10');
        QueryUtils::sqlStatementThrowException(
            'UPDATE openemr_postcalendar_events SET pc_startTime = ? WHERE pc_eid = ?',
            ['13:00:00', $this->eids['weekly']]
        );

        $result = $this->service->moveWithBlock($save, $this->posted('2031-03-10', '13:00:00'));

        $this->assertSame(2, $result->moved);
        $this->assertSame(0, $result->stranded);
        $this->assertSame(
            [
                $this->eids['first'] => '2031-03-10 13:00:00 a',
                $this->eids['second'] => '2031-03-10 13:00:00 a',
                $this->eids['on edited'] => '2031-03-17 10:00:00 a',
            ],
            $this->whereAre([$this->eids['first'], $this->eids['second'], $this->eids['on edited']]),
            'the date edited into its own block is not part of the series'
        );
        $this->assertSame([$this->eids['first'] => 1, $this->eids['second'] => 2], $this->sequencesOn('2031-03-10'));
    }

    public function testSavingThisDateOnlyMovesOnlyThatDatesPatients(): void
    {
        $later = $this->insertEvent($this->providerId, 5, '1', '2031-03-24', '09:00:00', 0, ['visitList' => 1, 'seq' => 1]);
        $save = $this->blockSave(VisitListRepeatScope::ThisDate, '2031-03-10');
        // What the editor does: exclude March 10 from the series and add it as its own block at 11:00.
        QueryUtils::sqlStatementThrowException(
            'UPDATE openemr_postcalendar_events SET pc_recurrspec = ? WHERE pc_eid = ?',
            [serialize(['event_repeat_freq' => '1', 'event_repeat_freq_type' => '1', 'event_repeat_on_num' => '1',
                'event_repeat_on_day' => '0', 'event_repeat_on_freq' => '0', 'exdate' => '20310317,20310310']), $this->eids['weekly']]
        );
        $this->insertEvent($this->providerId, $this->categoryId, '', '2031-03-10', '11:00:00', 14400);

        $result = $this->service->moveWithBlock($save, $this->posted('2031-03-10', '11:00:00'));

        $this->assertSame(2, $result->moved);
        $this->assertSame(
            [
                $this->eids['first'] => '2031-03-10 11:00:00 a',
                $this->eids['second'] => '2031-03-10 11:00:00 a',
                $later => '2031-03-24 09:00:00 a',
            ],
            $this->whereAre([$this->eids['first'], $this->eids['second'], $later])
        );
    }

    public function testSavingThisAndLaterOnAnotherWeekdayMovesEachDateByItsPlaceInThePattern(): void
    {
        $march24 = $this->insertEvent($this->providerId, 5, '1', '2031-03-24', '09:00:00', 0, ['visitList' => 1, 'seq' => 1]);
        $march31 = $this->insertEvent($this->providerId, 5, '1', '2031-03-31', '09:00:00', 0, ['visitList' => 1, 'seq' => 1]);
        $save = $this->blockSave(VisitListRepeatScope::ThisAndLater, '2031-03-10');
        // What the editor does: end the series the day before, and start a new one on Tuesday March 11
        // with the same repeat settings, ending March 31 (so it has no date for March 31's patient).
        // It keeps the series' excluded dates, so March 17 (a Monday) is still listed, matching no Tuesday.
        QueryUtils::sqlStatementThrowException(
            'UPDATE openemr_postcalendar_events SET pc_endDate = ? WHERE pc_eid = ?',
            ['2031-03-09', $this->eids['weekly']]
        );
        $this->insertEvent($this->providerId, $this->categoryId, '', '2031-03-11', '09:00:00', 28800, [
            'recurrtype' => 1,
            'endDate' => '2031-03-31',
            'exdate' => '20310317',
        ]);

        $result = $this->service->moveWithBlock($save, $this->posted('2031-03-11', '09:00:00'));

        $this->assertSame(3, $result->moved);
        $this->assertSame(1, $result->stranded);
        $this->assertSame(
            [
                $this->eids['first'] => '2031-03-11 09:00:00 a',
                $this->eids['second'] => '2031-03-11 09:00:00 a',
                $march24 => '2031-03-25 09:00:00 a',
                $march31 => '2031-03-31 09:00:00 a',
                $this->eids['on edited'] => '2031-03-17 10:00:00 a',
            ],
            $this->whereAre([$this->eids['first'], $this->eids['second'], $march24, $march31, $this->eids['on edited']]),
            'March 17 was excluded but still counts: March 24 is the 3rd date, so it goes to the 3rd Tuesday'
        );
    }

    public function testMovingTheBlockToAnotherProviderMovesThePatients(): void
    {
        $third = $this->insertProvider('c');
        $save = $this->blockSave(VisitListRepeatScope::AllDates, '2031-03-10');
        QueryUtils::sqlStatementThrowException('UPDATE openemr_postcalendar_events SET pc_aid = ? WHERE pc_eid = ?', [(string) $third, $this->eids['weekly']]);

        $result = $this->service->moveWithBlock($save, $this->posted('2031-03-10', '09:00:00', $third));

        $this->assertSame(2, $result->moved);
        $rows = QueryUtils::fetchRecords(
            'SELECT pc_aid, pc_visit_seq FROM openemr_postcalendar_events WHERE pc_eid IN (?, ?) ORDER BY pc_visit_seq',
            [$this->eids['first'], $this->eids['second']]
        );
        $this->assertSame([['pc_aid' => (string) $third, 'pc_visit_seq' => 1], ['pc_aid' => (string) $third, 'pc_visit_seq' => 2]], $rows);
    }

    public function testPatientsSharingAnOrderNumberKeepTheirOrderWhenTheBlockMoves(): void
    {
        // Both #3 with no stop, so their start puts them in order: 9:15 before 9:30. At the
        // block's new start they share a start too, and the later one has the lower event ID.
        $late = $this->insertEvent($this->providerId, 5, '1', '2031-03-10', '09:30:00', 0, ['visitList' => 1, 'seq' => 3]);
        $early = $this->insertEvent($this->providerId, 5, '1', '2031-03-10', '09:15:00', 0, ['visitList' => 1, 'seq' => 3]);
        $save = $this->blockSave(VisitListRepeatScope::AllDates, '2031-03-10');
        QueryUtils::sqlStatementThrowException(
            'UPDATE openemr_postcalendar_events SET pc_startTime = ? WHERE pc_eid = ?',
            ['13:00:00', $this->eids['weekly']]
        );

        $this->service->moveWithBlock($save, $this->posted('2031-03-10', '13:00:00'));

        // Both moved, not only renumbered where they were.
        $this->assertSame(
            [$early => '2031-03-10 13:00:00 a', $late => '2031-03-10 13:00:00 a'],
            $this->whereAre([$early, $late])
        );
        $rows = QueryUtils::fetchRecords(
            'SELECT pc_eid, pc_visit_seq FROM openemr_postcalendar_events WHERE pc_eid IN (?, ?) ORDER BY pc_visit_seq',
            [$early, $late]
        );
        $this->assertSame([[$early, 3], [$late, 4]], array_map(
            static fn (array $row): array => [
                filter_var($row['pc_eid'], FILTER_VALIDATE_INT),
                filter_var($row['pc_visit_seq'], FILTER_VALIDATE_INT),
            ],
            $rows
        ));
    }

    /**
     * Saves every date of the weekly block as 9:00-9:15, its length the only change.
     */
    private function shortenWeekly(): VisitListMoveResult
    {
        $save = $this->blockSave(VisitListRepeatScope::AllDates, '2031-03-10');
        QueryUtils::sqlStatementThrowException(
            'UPDATE openemr_postcalendar_events SET pc_duration = 900, pc_endTime = ? WHERE pc_eid = ?',
            ['09:15:00', $this->eids['weekly']]
        );
        return $this->service->moveWithBlock($save, $this->posted('2031-03-10', '09:00:00'));
    }

    public function testAShortenedBlockTakesAPatientPastItsNewEndToItsStart(): void
    {
        $inside = $this->insertEvent($this->providerId, 5, '1', '2031-03-10', '09:05:00', 0, ['visitList' => 1, 'seq' => 3]);
        $outside = $this->insertEvent($this->providerId, 5, '1', '2031-03-10', '09:30:00', 0, ['visitList' => 1, 'seq' => 4]);

        $result = $this->shortenWeekly();

        $this->assertSame(1, $result->moved);
        $this->assertSame(
            [
                $this->eids['first'] => '2031-03-10 09:00:00 a',
                $inside => '2031-03-10 09:05:00 a',
                $outside => '2031-03-10 09:00:00 a',
            ],
            $this->whereAre([$this->eids['first'], $inside, $outside])
        );
    }

    public function testAShortenedBlockLeavesAPatientAnotherBlockNowHas(): void
    {
        // An 8:00-10:00 block lost 9:30 to the later-starting weekly block; shortened, the
        // weekly block no longer reaches it, so it is on the 8:00 block's list again. Both
        // lists are renumbered: the weekly list closes the gap it leaves.
        $this->insertEvent($this->providerId, $this->categoryId, '', '2031-03-10', '08:00:00', 7200);
        $outside = $this->insertEvent($this->providerId, 5, '1', '2031-03-10', '09:30:00', 0, ['visitList' => 1, 'seq' => 3]);
        $inside = $this->insertEvent($this->providerId, 5, '1', '2031-03-10', '09:05:00', 0, ['visitList' => 1, 'seq' => 4]);

        $result = $this->shortenWeekly();

        $this->assertSame(0, $result->moved);
        $this->assertSame([$outside => '2031-03-10 09:30:00 a'], $this->whereAre([$outside]));
        $rows = QueryUtils::fetchRecords(
            'SELECT pc_eid, pc_visit_seq FROM openemr_postcalendar_events WHERE pc_eid IN (?, ?, ?, ?) ORDER BY pc_eid',
            [$this->eids['second'], $this->eids['first'], $outside, $inside]
        );
        $sequences = [];
        foreach ($rows as $row) {
            $sequences[filter_var($row['pc_eid'], FILTER_VALIDATE_INT)] = filter_var($row['pc_visit_seq'], FILTER_VALIDATE_INT);
        }
        $this->assertSame(
            [$this->eids['second'] => 2, $this->eids['first'] => 1, $outside => 1, $inside => 3],
            $sequences
        );
    }

    public function testAPatientMovedMeanwhileIsNotCountedAsMoved(): void
    {
        $save = $this->blockSave(VisitListRepeatScope::AllDates, '2031-03-10');
        QueryUtils::sqlStatementThrowException(
            'UPDATE openemr_postcalendar_events SET pc_startTime = ? WHERE pc_eid = ?',
            ['13:00:00', $this->eids['weekly']]
        );
        // Another user moves one patient to another date before the block's patients follow it.
        QueryUtils::sqlStatementThrowException(
            'UPDATE openemr_postcalendar_events SET pc_eventDate = ? WHERE pc_eid = ?',
            ['2031-03-11', $this->eids['second']]
        );

        $result = $this->service->moveWithBlock($save, $this->posted('2031-03-10', '13:00:00'));

        $this->assertSame(1, $result->moved);
        $this->assertSame(
            [
                $this->eids['first'] => '2031-03-10 13:00:00 a',
                $this->eids['second'] => '2031-03-11 09:00:00 a',
            ],
            $this->whereAre([$this->eids['first'], $this->eids['second']])
        );
    }

    public function testABlockSavedByAnotherUserMeanwhileIsNotTakenForThisOne(): void
    {
        // Made before the new provider, so it has the lower ID and would be paired first.
        $intruder = $this->insertProvider('d');
        $third = $this->insertProvider('c');
        $save = $this->blockSave(VisitListRepeatScope::AllDates, '2031-03-10');
        // The save gives the block to the new provider while another user saves a block for
        // someone else on the same date.
        QueryUtils::sqlStatementThrowException('UPDATE openemr_postcalendar_events SET pc_aid = ? WHERE pc_eid = ?', [(string) $third, $this->eids['weekly']]);
        $this->insertEvent($intruder, $this->categoryId, '', '2031-03-10', '09:00:00', 14400);

        $result = $this->service->moveWithBlock($save, $this->posted('2031-03-10', '09:00:00', $third));

        $this->assertSame(2, $result->moved);
        $rows = QueryUtils::fetchRecords(
            'SELECT pc_aid FROM openemr_postcalendar_events WHERE pc_eid IN (?, ?)',
            [$this->eids['first'], $this->eids['second']]
        );
        $this->assertSame([['pc_aid' => (string) $third], ['pc_aid' => (string) $third]], $rows);
    }

    private function posted(string $date, string $start, ?int $providerId = null): VisitListPostedBlock
    {
        $day = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        $this->assertNotFalse($day);
        return new VisitListPostedBlock([$providerId ?? $this->providerId], $day, $start);
    }

    public function testABlockWithNoPatientsHasNothingToMove(): void
    {
        $empty = $this->insertEvent($this->otherProviderId, $this->categoryId, '', '2031-04-07', '09:00:00', 3600);

        $this->assertNull($this->service->blockSave($empty, VisitListRepeatScope::AllDates, '2031-04-07'));
    }

    public function testAPatientAppointmentIsNotABlock(): void
    {
        $this->assertNull($this->service->blockSave($this->eids['first'], VisitListRepeatScope::AllDates, '2031-03-10'));
    }

    public function testSavingEveryDateLeavesPatientsOnDatesBeforeToday(): void
    {
        $march24 = $this->insertEvent($this->providerId, 5, '1', '2031-03-24', '09:00:00', 0, ['visitList' => 1, 'seq' => 1]);
        // Today is March 20: March 10 is history, March 24 is not.
        $this->service = new VisitListService(clock: new FrozenClock(new DateTimeImmutable('2031-03-20 12:00', new DateTimeZone('UTC'))));
        $save = $this->blockSave(VisitListRepeatScope::AllDates, '2031-03-24');
        QueryUtils::sqlStatementThrowException(
            'UPDATE openemr_postcalendar_events SET pc_startTime = ? WHERE pc_eid = ?',
            ['13:00:00', $this->eids['weekly']]
        );

        $result = $this->service->moveWithBlock($save, $this->posted('2031-03-24', '13:00:00'));

        $this->assertSame(1, $result->moved);
        $this->assertSame(2, $result->leftOnPastDates);
        $this->assertSame(
            [
                $this->eids['first'] => '2031-03-10 09:00:00 a',
                $this->eids['second'] => '2031-03-10 09:00:00 a',
                $march24 => '2031-03-24 13:00:00 a',
            ],
            $this->whereAre([$this->eids['first'], $this->eids['second'], $march24])
        );
    }

    /**
     * Each patient's start, length in seconds, and place on a list ("list seq stop", or
     * "timed" when off the list), by event ID.
     *
     * @param list<int> $eids
     * @return array<int, string>
     */
    private function timesOf(array $eids): array
    {
        $times = [];
        foreach ($eids as $eid) {
            $rows = QueryUtils::fetchRecords(
                'SELECT pc_eventDate, pc_startTime, pc_endTime, pc_duration, pc_visit_list, pc_visit_seq, pc_visit_stop'
                . ' FROM openemr_postcalendar_events WHERE pc_eid = ?',
                [$eid]
            );
            $row = $rows[0] ?? [];
            $text = static fn (string $column): string => is_scalar($row[$column] ?? null) ? (string) $row[$column] : '-';
            $times[$eid] = $text('pc_eventDate') . ' ' . $text('pc_startTime') . '-' . $text('pc_endTime') . ' ' . $text('pc_duration') . ' '
                . ($text('pc_visit_list') === '1' ? 'list ' . $text('pc_visit_seq') . ' ' . $text('pc_visit_stop') : 'timed ' . $text('pc_visit_seq') . ' ' . $text('pc_visit_stop'));
        }
        return $times;
    }

    /**
     * Deletes the weekly block through the editor's steps: note its lists, delete, release.
     *
     * @param ?callable(): void $meanwhile what another request does between the note and the release
     */
    private function deleteWeekly(string $scope, string $selectedDate, ?callable $meanwhile = null): void
    {
        $editor = new VisitListEditor($this->service, 15);
        $post = new ParameterBag(['form_action' => 'delete', 'recurr_affect' => $scope, 'selected_date' => $selectedDate]);
        $before = $editor->beforeSave($post, $this->eids['weekly']);
        $this->assertInstanceOf(VisitListBlockRelease::class, $before);
        if ($meanwhile !== null) {
            $meanwhile();
        }

        // AppointmentService reads the date of a "this date" or "this and later" delete from $_POST.
        $hadDate = array_key_exists('selected_date', $_POST);
        $oldDate = $_POST['selected_date'] ?? null;
        $_POST['selected_date'] = $selectedDate;
        try {
            (new AppointmentService())->deleteAppointment($this->eids['weekly'], $scope, $selectedDate);
        } finally {
            if ($hadDate) {
                $_POST['selected_date'] = $oldDate;
            } else {
                unset($_POST['selected_date']);
            }
        }

        $this->assertSame('', $editor->afterSave($post, $this->eids['weekly'], $before));
    }

    public function testDeletingEveryDateOfABlockKeepsItsPatientsAsTimedAppointments(): void
    {
        $march24 = $this->insertEvent($this->providerId, 5, '1', '2031-03-24', '09:00:00', 0, ['visitList' => 1, 'seq' => 1, 'stop' => 4]);

        $this->deleteWeekly('all', '2031-03-10');

        $this->assertSame(
            [
                $this->eids['first'] => '2031-03-10 09:00:00-09:15:00 900 timed - -',
                $this->eids['second'] => '2031-03-10 09:15:00-09:30:00 900 timed - -',
                $march24 => '2031-03-24 09:00:00-09:15:00 900 timed - -',
                $this->eids['on edited'] => '2031-03-17 10:00:00-10:00:00 0 list 1 -',
                $this->eids['timed'] => '2031-03-10 09:00:00-09:15:00 900 timed - -',
            ],
            $this->timesOf([$this->eids['first'], $this->eids['second'], $march24, $this->eids['on edited'], $this->eids['timed']]),
            'the date edited into its own block keeps its list'
        );
        $this->assertSame([], QueryUtils::fetchRecords('SELECT pc_eid FROM openemr_postcalendar_events WHERE pc_eid = ?', [$this->eids['weekly']]));
    }

    public function testDeletingThisDateOfABlockKeepsOnlyThatDatesPatients(): void
    {
        $march24 = $this->insertEvent($this->providerId, 5, '1', '2031-03-24', '09:00:00', 0, ['visitList' => 1, 'seq' => 1]);

        $this->deleteWeekly('current', '2031-03-10');

        $this->assertSame(['2031-03-03', '2031-03-24', '2031-03-31'], $this->weeklyDates(), 'only March 10 is gone (March 17 was already excluded)');

        $this->assertSame(
            [
                $this->eids['first'] => '2031-03-10 09:00:00-09:15:00 900 timed - -',
                $this->eids['second'] => '2031-03-10 09:15:00-09:30:00 900 timed - -',
                $march24 => '2031-03-24 09:00:00-09:00:00 0 list 1 -',
            ],
            $this->timesOf([$this->eids['first'], $this->eids['second'], $march24])
        );
    }

    public function testDeletingThisDateOfAMultiProviderBlockExcludesThatDate(): void
    {
        // With multiple providers per event, a delete of one date writes the excluded date in
        // its own branch, which must use the Ymd format the calendar compares.
        QueryUtils::sqlStatementThrowException(
            'UPDATE openemr_postcalendar_events SET pc_multiple = (SELECT m FROM (SELECT MAX(pc_multiple) + 1 AS m FROM openemr_postcalendar_events) AS x) WHERE pc_eid = ?',
            [$this->eids['weekly']]
        );
        $globals = OEGlobalsBag::getInstance();
        $had = $globals->has('select_multi_providers');
        $old = $globals->getBoolean('select_multi_providers');
        $globals->set('select_multi_providers', true);
        try {
            $this->deleteWeekly('current', '2031-03-10');
        } finally {
            if ($had) {
                $globals->set('select_multi_providers', $old);
            } else {
                // set() also wrote it to $GLOBALS, which the bag reads first; remove both.
                $globals->remove('select_multi_providers');
                unset($GLOBALS['select_multi_providers']);
            }
        }

        $this->assertSame(['2031-03-03', '2031-03-24', '2031-03-31'], $this->weeklyDates());
    }

    public function testPatientsReleasedIntoTheDaysLastSlotEndAtMidnightOfThatDay(): void
    {
        // A block from 23:30 with three patients: the 2nd and 3rd share the 23:45 slot. Their
        // end is written as 24:00:00, as the appointment editor writes it; 00:00:00 would make
        // the open-slot search read the whole day before it as taken.
        $date = new DateTimeImmutable('2031-03-12');
        $this->insertEvent($this->providerId, $this->categoryId, '', '2031-03-12', '23:30:00', 1800);
        $patients = [];
        foreach ([1, 2, 3] as $seq) {
            $patients[] = $this->insertEvent($this->providerId, 5, '1', '2031-03-12', '23:30:00', 0, ['visitList' => 1, 'seq' => $seq]);
        }

        $released = $this->service->release($this->service->forDates($date, $date, [$this->providerId])->lists, 15);

        $this->assertSame(3, $released);
        $this->assertSame(
            [
                $patients[0] => '2031-03-12 23:30:00-23:45:00 900 timed - -',
                $patients[1] => '2031-03-12 23:45:00-24:00:00 900 timed - -',
                $patients[2] => '2031-03-12 23:45:00-24:00:00 900 timed - -',
            ],
            $this->timesOf($patients)
        );
    }

    public function testDeletingEveryDateLeavesPatientsOnDatesBeforeToday(): void
    {
        $march24 = $this->insertEvent($this->providerId, 5, '1', '2031-03-24', '09:00:00', 0, ['visitList' => 1, 'seq' => 1]);
        $this->service = new VisitListService(clock: new FrozenClock(new DateTimeImmutable('2031-03-20 12:00', new DateTimeZone('UTC'))));

        $this->deleteWeekly('all', '2031-03-24');

        $this->assertSame(
            [
                $this->eids['first'] => '2031-03-10 09:00:00-09:00:00 0 list 1 1',
                $march24 => '2031-03-24 09:00:00-09:15:00 900 timed - -',
            ],
            $this->timesOf([$this->eids['first'], $march24])
        );
    }

    public function testGivingABlockAnotherCategoryKeepsItsPatientsAsTimedAppointments(): void
    {
        $editor = new VisitListEditor($this->service, 20);
        $post = new ParameterBag(['form_action' => 'save', 'form_category' => '5', 'recurr_affect' => 'all', 'selected_date' => '2031-03-10']);
        $before = $editor->beforeSave($post, $this->eids['weekly']);
        $this->assertInstanceOf(VisitListBlockRelease::class, $before);
        QueryUtils::sqlStatementThrowException('UPDATE openemr_postcalendar_events SET pc_catid = 5 WHERE pc_eid = ?', [$this->eids['weekly']]);

        $editor->afterSave($post, $this->eids['weekly'], $before);

        $this->assertSame(
            [
                $this->eids['first'] => '2031-03-10 09:00:00-09:20:00 1200 timed - -',
                $this->eids['second'] => '2031-03-10 09:20:00-09:40:00 1200 timed - -',
            ],
            $this->timesOf([$this->eids['first'], $this->eids['second']])
        );
    }

    /**
     * The dates the weekly block shows on in March 2031.
     *
     * @return list<string>
     */
    private function weeklyDates(): array
    {
        $dates = [];
        $from = new DateTimeImmutable('2031-03-01');
        foreach ($this->service->forDates($from, $from->modify('+30 days'), [$this->providerId])->lists as $list) {
            if ($list->block->eventId === $this->eids['weekly']) {
                $dates[] = $list->block->date();
            }
        }
        return $dates;
    }

    public function testDeletingLeavesAPatientMovedMeanwhileWhereItWasMoved(): void
    {
        // Another request moves the second patient to March 24 after the delete noted the lists.
        $this->deleteWeekly('all', '2031-03-10', function (): void {
            QueryUtils::sqlStatementThrowException(
                'UPDATE openemr_postcalendar_events SET pc_eventDate = ? WHERE pc_eid = ?',
                ['2031-03-24', $this->eids['second']]
            );
        });

        $this->assertSame(
            [
                $this->eids['first'] => '2031-03-10 09:00:00-09:15:00 900 timed - -',
                $this->eids['second'] => '2031-03-24 09:00:00-09:00:00 0 list 2 1',
            ],
            $this->timesOf([$this->eids['first'], $this->eids['second']])
        );
    }

    public function testAMoveOntoAnotherBlocksStartLeavesThePatientsWhereTheyAre(): void
    {
        // Two blocks of one provider on April 7: the earlier-made one at 13:00, ours at 9:00.
        $earlier = $this->insertEvent($this->otherProviderId, $this->categoryId, '', '2031-04-07', '13:00:00', 3600);
        $ours = $this->insertEvent($this->otherProviderId, $this->categoryId, '', '2031-04-07', '09:00:00', 3600);
        $patient = $this->insertEvent($this->otherProviderId, 5, '1', '2031-04-07', '09:00:00', 0, ['visitList' => 1, 'seq' => 1]);
        $save = $this->service->blockSave($ours, VisitListRepeatScope::AllDates, '2031-04-07');
        $this->assertNotNull($save);
        // Ours moves to 13:00 too: a patient there goes on the block made first (lower ID).
        QueryUtils::sqlStatementThrowException('UPDATE openemr_postcalendar_events SET pc_startTime = ? WHERE pc_eid = ?', ['13:00:00', $ours]);

        $result = $this->service->moveWithBlock($save, $this->posted('2031-04-07', '13:00:00', $this->otherProviderId));

        $this->assertSame(0, $result->moved);
        $this->assertSame(1, $result->takenByOtherBlock);
        $this->assertSame([$patient => '2031-04-07 09:00:00 b'], $this->whereAre([$patient]));
        $this->assertGreaterThan(0, $earlier);
    }
}
