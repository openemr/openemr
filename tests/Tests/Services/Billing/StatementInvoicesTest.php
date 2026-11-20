<?php

/**
 * Database tests for the invoices a statement run reads and the counts it updates.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Billing;

use OpenEMR\Billing\Statement\StatementInvoices;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\TestCase;

class StatementInvoicesTest extends TestCase
{
    private const ZED_PID = 999921;
    private const ABLE_PID = 999922;

    /** @var array<int, int> encounter => form_encounter.id */
    private array $ids = [];

    protected function setUp(): void
    {
        $this->cleanUp();
        $this->addPatient(self::ZED_PID, 'Zed', 'Zimmer', 'Guardian Gail');
        $this->addPatient(self::ABLE_PID, 'Able', 'Adams', '');
        $this->ids[1] = $this->addEncounter(self::ZED_PID, 99992101, '2026-05-01');
        $this->ids[2] = $this->addEncounter(self::ABLE_PID, 99992201, '2026-06-01');
        $this->ids[3] = $this->addEncounter(self::ABLE_PID, 99992202, '2026-02-01');
        $this->ids[4] = $this->addEncounter(self::ZED_PID, 99992102, '2026-01-01');
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
    }

    public function testReadsTheCheckedInvoicesGroupedByPatientAndOldestFirst(): void
    {
        $invoices = StatementInvoices::forRun([$this->ids[1], $this->ids[2], $this->ids[3]]);

        $this->assertSame([99992202, 99992201, 99992101], array_map(static fn(array $i): int => is_numeric($i['encounter']) ? (int) $i['encounter'] : 0, $invoices));
        $zed = $invoices[2];
        $this->assertSame('Zimmer', $zed['lname']);
        $this->assertSame('Guardian Gail', $zed['guardiansname']);
        $this->assertSame('enc note', $zed['enc_billing_note']);
        $this->assertSame('pat note', $zed['pat_billing_note']);
        $this->assertArrayHasKey('stmt_count', $zed);
        $this->assertArrayHasKey('provider_id', $zed);
    }

    public function testNoInvoicesReadsNothing(): void
    {
        $this->assertSame([], StatementInvoices::forRun([]));
    }

    public function testRecordingARunCountsAStatementAndDatesIt(): void
    {
        StatementInvoices::recordStatementRun([$this->ids[1], $this->ids[2]], '2026-10-30');
        StatementInvoices::recordStatementRun([$this->ids[1]], '2026-10-31');

        $rows = QueryUtils::fetchRecords(
            "SELECT encounter, stmt_count, last_stmt_date FROM form_encounter WHERE id IN (?, ?, ?) ORDER BY encounter",
            [$this->ids[1], $this->ids[2], $this->ids[3]]
        );
        $counts = [];
        foreach ($rows as $row) {
            $date = $row['last_stmt_date'] ?? null;
            $counts[] = [$row['encounter'] ?? null, $row['stmt_count'] ?? null, is_string($date) ? substr($date, 0, 10) : null];
        }
        $this->assertEquals([
            [99992101, 2, '2026-10-31'],
            [99992201, 1, '2026-10-30'],
            [99992202, 0, null],
        ], $counts);
    }

    private function addPatient(int $pid, string $fname, string $lname, string $guardian): void
    {
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO patient_data (pid, pubpid, fname, lname, DOB, sex, street, city, state, postal_code, " .
            "billing_note, guardiansname) VALUES (?, ?, ?, ?, '1950-01-01', 'Female', '1 Main St', 'Rutland', 'VT', " .
            "'05701', 'pat note', ?)",
            [$pid, (string) $pid, $fname, $lname, $guardian]
        );
    }

    private function addEncounter(int $pid, int $encounter, string $date): int
    {
        return QueryUtils::sqlInsert(
            "INSERT INTO form_encounter (date, reason, facility_id, pid, encounter, provider_id, billing_note, stmt_count) " .
            "VALUES (?, 'statement test', 3, ?, ?, 1, 'enc note', 0)",
            [$date . ' 00:00:00', $pid, $encounter]
        );
    }

    private function cleanUp(): void
    {
        foreach ([self::ZED_PID, self::ABLE_PID] as $pid) {
            QueryUtils::sqlStatementThrowException("DELETE FROM form_encounter WHERE pid = ?", [$pid]);
            QueryUtils::sqlStatementThrowException("DELETE FROM patient_data WHERE pid = ?", [$pid]);
        }
        $this->ids = [];
    }
}
