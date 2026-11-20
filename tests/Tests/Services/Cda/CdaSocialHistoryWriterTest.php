<?php

/**
 * A C-CDA import's social history is written as a new version of the patient's history, keeping the rest.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Cda;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\Cda\CdaSocialHistoryWriter;
use OpenEMR\Services\SocialHistoryService;
use PHPUnit\Framework\TestCase;

class CdaSocialHistoryWriterTest extends TestCase
{
    private const TOBACCO = 'imported|currenttobacco|2024-01-21';

    private int $pid;
    private SocialHistoryService $service;
    private CdaSocialHistoryWriter $writer;

    protected function setUp(): void
    {
        $next = QueryUtils::fetchSingleValue("SELECT COALESCE(MAX(pid), 0) + 1 AS next_pid FROM patient_data", 'next_pid');
        $this->assertIsNumeric($next);
        $this->pid = (int) $next;
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO patient_data (pid, uuid, fname, lname, `date`) VALUES (?, ?, 'test-fixture-history', 'test-fixture-history', NOW())",
            [$this->pid, random_bytes(16)]
        );
        $this->service = new SocialHistoryService();
        $this->writer = new CdaSocialHistoryWriter($this->service);
    }

    protected function tearDown(): void
    {
        self::removeHistory($this->pid);
        QueryUtils::sqlStatementThrowException("DELETE FROM patient_data WHERE pid = ?", [$this->pid]);
    }

    /**
     * A patient's history rows, with the uuids each save registered: the row's own and the FHIR Observation
     * mappings the save event adds for it.
     */
    public static function removeHistory(int $pid): void
    {
        $where = "target_uuid IN (SELECT uuid FROM history_data WHERE pid = ?)";
        QueryUtils::sqlStatementThrowException("DELETE FROM uuid_registry WHERE uuid IN (SELECT uuid FROM uuid_mapping WHERE $where)", [$pid]);
        QueryUtils::sqlStatementThrowException("DELETE FROM uuid_mapping WHERE $where", [$pid]);
        QueryUtils::sqlStatementThrowException("DELETE FROM uuid_registry WHERE uuid IN (SELECT uuid FROM history_data WHERE pid = ?)", [$pid]);
        QueryUtils::sqlStatementThrowException("DELETE FROM history_data WHERE pid = ?", [$pid]);
    }

    /**
     * The latest history row is the patient's current history: a new tobacco status must leave their alcohol
     * history, which the document didn't carry, and the rest as they were.
     */
    public function testAnExistingHistoryKeepsWhatTheDocumentDoesNotCarry(): void
    {
        $this->service->create(['pid' => $this->pid, 'alcohol' => 'kept|currentalcohol|2020-01-01', 'exercise_patterns' => 'daily walks']);
        // Saved earlier, as a patient's history is: the latest row is found by date, to the second.
        QueryUtils::sqlStatementThrowException("UPDATE history_data SET `date` = NOW() - INTERVAL 1 DAY WHERE pid = ?", [$this->pid]);

        $this->writer->write($this->pid, ['tobacco' => self::TOBACCO]);

        $current = $this->service->getHistoryData($this->pid);
        $this->assertIsArray($current);
        $this->assertSame(self::TOBACCO, $current['tobacco']);
        $this->assertSame('kept|currentalcohol|2020-01-01', $current['alcohol']);
        $this->assertSame('daily walks', $current['exercise_patterns']);
        $this->assertSame(2, $this->rows());
    }

    /**
     * history_data's date has one-second precision, so a version written in the same second as the one before
     * it shares its date; the later row must still be the current one.
     */
    public function testTheVersionWrittenLastIsCurrentEvenInTheSameSecond(): void
    {
        $this->service->create(['pid' => $this->pid, 'alcohol' => 'kept|currentalcohol|2020-01-01']);
        QueryUtils::sqlStatementThrowException("UPDATE history_data SET `date` = '2026-01-01 00:00:00' WHERE pid = ?", [$this->pid]);

        $this->writer->write($this->pid, ['tobacco' => self::TOBACCO]);
        QueryUtils::sqlStatementThrowException("UPDATE history_data SET `date` = '2026-01-01 00:00:00' WHERE pid = ?", [$this->pid]);

        $current = $this->service->getHistoryData($this->pid);
        $this->assertIsArray($current);
        $this->assertSame(self::TOBACCO, $current['tobacco']);
        $this->assertSame('kept|currentalcohol|2020-01-01', $current['alcohol']);
    }

    /**
     * A document approved on the review screen with only a smoking status: the screen's alcohol fields are
     * empty, and the patient's alcohol history must stay as it was.
     */
    public function testAReviewedSmokingStatusKeepsTheAlcoholHistory(): void
    {
        $this->service->create(['pid' => $this->pid, 'alcohol' => 'kept|currentalcohol|2020-01-01', 'exercise_patterns' => 'daily walks']);
        QueryUtils::sqlStatementThrowException("UPDATE history_data SET `date` = NOW() - INTERVAL 1 DAY WHERE pid = ?", [$this->pid]);

        $this->writer->write(
            $this->pid,
            CdaSocialHistoryWriter::reviewedChanges([null, 'quittobacco', '2024-01-21', '3'], [null, null, false])
        );

        $current = $this->service->getHistoryData($this->pid);
        $this->assertIsArray($current);
        $this->assertSame('|quittobacco|2024-01-21|3', $current['tobacco']);
        $this->assertSame('kept|currentalcohol|2020-01-01', $current['alcohol']);
        $this->assertSame('daily walks', $current['exercise_patterns']);
    }

    public function testAPatientWithNoHistoryGetsAFirstRow(): void
    {
        $this->writer->write($this->pid, ['tobacco' => self::TOBACCO]);

        $current = $this->service->getHistoryData($this->pid);
        $this->assertIsArray($current);
        $this->assertSame(self::TOBACCO, $current['tobacco']);
        $this->assertSame(1, $this->rows());
    }

    public function testNothingCarriedWritesNothing(): void
    {
        $this->writer->write($this->pid, []);

        $this->assertSame(0, $this->rows());
    }

    private function rows(): int
    {
        $count = QueryUtils::fetchSingleValue("SELECT COUNT(*) AS n FROM history_data WHERE pid = ?", 'n', [$this->pid]);
        $this->assertIsNumeric($count);
        return (int) $count;
    }
}
