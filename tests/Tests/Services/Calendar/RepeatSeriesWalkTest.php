<?php

/**
 * The flow board expands repeating appointments in PHP. A repeat whose
 * next date does not move used to stay in that walk. These tests keep
 * the real expansion on a fixture row and check that it finishes.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Calendar;

use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../library/appointments.inc.php';

class RepeatSeriesWalkTest extends TestCase
{
    private const PID = 99055001;

    private const TITLE = 'phpunit-repeat-walk';

    private int $categoryId;

    private int $providerId;

    protected function setUp(): void
    {
        $category = QueryUtils::querySingleRow('SELECT pc_catid FROM openemr_postcalendar_categories ORDER BY pc_catid LIMIT 1');
        $provider = QueryUtils::querySingleRow('SELECT id FROM users ORDER BY id LIMIT 1');
        $this->categoryId = $this->requiredId(is_array($category) ? ($category['pc_catid'] ?? null) : null);
        $this->providerId = $this->requiredId(is_array($provider) ? ($provider['id'] ?? null) : null);
        $this->removeFixtures();
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO patient_data (pid, fname, lname, DOB, sex, pubpid) VALUES (?, 'ZZ', 'Repeat', '1980-01-01', 'Male', ?)",
            [self::PID, 'ZZ' . self::PID]
        );
    }

    protected function tearDown(): void
    {
        $this->removeFixtures();
    }

    #[Test]
    public function testADailySeriesLandsOnOneRequestedDay(): void
    {
        $this->insertSeries('1', '1', '0', '2016-01-04', '2026-12-31');

        $rows = $this->rowsOn('2024-06-03', '2024-06-03');

        $this->assertCount(1, $rows);
        $first = $rows[0] ?? null;
        $this->assertIsArray($first);
        $this->assertSame('2024-06-03', $first['pc_eventDate'] ?? null);
    }

    #[Test]
    public function testADailySeriesExpandsAcrossAQuarter(): void
    {
        $this->insertSeries('1', '1', '0', '2016-01-04', '2026-12-31');

        // 1 Jun through 31 Aug 2024 is 92 days.
        $this->assertCount(92, $this->rowsOn('2024-06-01', '2024-08-31'));
    }

    #[Test]
    public function testAnUnhandledRepeatTypeFinishesAndSkipsDaysItCannotReach(): void
    {
        $this->insertSeries('1', '1', '5', '2016-01-04', '2026-12-31');

        $started = microtime(true);
        $inside = $this->rowsOn('2024-06-03', '2024-06-03');
        $today = $this->rowsOn('2026-10-05', '2026-10-05');
        $elapsed = microtime(true) - $started;

        $this->assertSame([], $inside);
        $this->assertSame([], $today);
        $this->assertLessThan(2.0, $elapsed);
    }

    #[Test]
    public function testAnUnhandledRepeatTypeStillShowsItsStartDay(): void
    {
        $this->insertSeries('1', '1', '5', '2016-01-04', '2024-12-31');

        $rows = $this->rowsOn('2016-01-04', '2016-01-04');

        $this->assertCount(1, $rows);
        $first = $rows[0] ?? null;
        $this->assertIsArray($first);
        $this->assertSame('2016-01-04', $first['pc_eventDate'] ?? null);
    }

    #[Test]
    public function testAZeroIntervalIsNotExpanded(): void
    {
        $this->insertSeries('1', '0', '0', '2016-01-04', '2026-12-31');

        $this->assertSame([], $this->rowsOn('2024-06-03', '2024-06-03'));
    }

    #[Test]
    public function testAWeeklySeriesCountsTheMondaysInTheSpan(): void
    {
        // 2020-01-06 and 2024-06-03 are Mondays. 1 Jan through 31 Mar 2024 holds 13 of them.
        $this->insertSeries('1', '1', '1', '2020-01-06', '2026-12-31');

        $this->assertCount(1, $this->rowsOn('2024-06-03', '2024-06-03'));
        $this->assertCount(13, $this->rowsOn('2024-01-01', '2024-03-31'));
    }

    #[Test]
    public function testAMonthlyWeekdaySeriesCountsTwelveInAYear(): void
    {
        $this->insertSeries('2', '0', '0', '2024-01-09', '2024-12-31', [
            'event_repeat_on_num' => '2',
            'event_repeat_on_day' => '2',
            'event_repeat_on_freq' => '1',
        ]);

        $this->assertCount(1, $this->rowsOn('2024-06-01', '2024-06-30'));
        $this->assertCount(12, $this->rowsOn('2024-01-01', '2024-12-31'));
    }

    /**
     * @param array<string, string> $spec
     */
    private function insertSeries(
        string $recurrType,
        string $frequency,
        string $frequencyType,
        string $start,
        string $end,
        array $spec = [],
    ): void {
        $stored = serialize($spec + [
            'event_repeat_freq' => $frequency,
            'event_repeat_freq_type' => $frequencyType,
            'event_repeat_on_num' => '1',
            'event_repeat_on_day' => '0',
            'event_repeat_on_freq' => '0',
            'exdate' => '',
        ]);
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO openemr_postcalendar_events
                (pc_catid, pc_aid, pc_pid, pc_title, pc_eventDate, pc_endDate, pc_startTime, pc_endTime, pc_duration, pc_recurrtype, pc_recurrspec, pc_alldayevent)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)',
            [
                $this->categoryId,
                $this->providerId,
                self::PID,
                self::TITLE,
                $start,
                $end,
                '09:00:00',
                '09:15:00',
                900,
                $recurrType,
                $stored,
            ]
        );
    }

    /**
     * @return array<int|string, mixed>
     */
    private function rowsOn(string $from, string $to): array
    {
        return fetchEvents($from, $to, ' AND e.pc_title = ?', null, false, 0, [self::TITLE]);
    }

    private function requiredId(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && preg_match('/^[0-9]+$/', $value) === 1) {
            return (int) $value;
        }

        $this->fail('The fixture id was not a whole number.');
    }

    private function removeFixtures(): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM openemr_postcalendar_events WHERE pc_title = ?',
            [self::TITLE]
        );
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM patient_data WHERE pid = ?',
            [self::PID]
        );
    }
}
