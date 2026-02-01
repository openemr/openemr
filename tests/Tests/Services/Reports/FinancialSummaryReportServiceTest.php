<?php

/**
 * Integration tests for FinancialSummaryReportService.
 *
 * Verifies the service code financial summary query produces correct totals
 * and does not inflate results when the codes table contains multiple rows
 * for the same code value (different modifiers or code types).
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services\Reports;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\Reports\FinancialSummaryReportService;
use OpenEMR\Services\Reports\ServiceCodeSummary;
use PHPUnit\Framework\TestCase;

class FinancialSummaryReportServiceTest extends TestCase
{
    private FinancialSummaryReportService $service;

    /**
     * Unique encounter number used across all tests, chosen to avoid
     * collisions with real data.
     */
    private const TEST_ENCOUNTER = 999999001;

    /**
     * Test PID that does not exist in a real install.
     */
    private const TEST_PID = 999999001;

    /**
     * Date range that covers our test fixtures.
     */
    private const TEST_DATE = '2025-06-15';

    /**
     * Identifiable code values for test fixtures.
     */
    private const TEST_CODE_A = 'TSTA0001';
    private const TEST_CODE_B = 'TSTB0002';

    /**
     * Track IDs for cleanup.
     *
     * @var array<string, int[]>
     */
    private array $insertedIds = [
        'form_encounter' => [],
        'billing' => [],
        'ar_activity' => [],
        'codes' => [],
        'facility' => [],
    ];

    /**
     * Auto-incrementing sequence number for ar_activity inserts.
     */
    private int $arSequenceNo = 0;

    protected function setUp(): void
    {
        $this->service = new FinancialSummaryReportService();
        $this->cleanUpTestData();
    }

    protected function tearDown(): void
    {
        $this->cleanUpTestData();
    }

    /**
     * One code, one billing row, one ar_activity row.
     * Assert units, billed, paid, adjusted, balance are correct.
     */
    public function testSummaryWithSingleCode(): void
    {
        $this->insertFacility(1, 'Test Facility A');
        $this->insertEncounter(self::TEST_PID, self::TEST_ENCOUNTER, 1);
        $this->insertBilling(self::TEST_PID, self::TEST_ENCOUNTER, self::TEST_CODE_A, 'CPT4', 2, 150.00, 1);
        $this->insertArActivity(self::TEST_PID, self::TEST_ENCOUNTER, self::TEST_CODE_A, 100.00, 20.00);
        $this->insertCode(self::TEST_CODE_A, 1, '', 0);

        $results = $this->service->getServiceCodeSummary(new \DateTimeImmutable(self::TEST_DATE), new \DateTimeImmutable(self::TEST_DATE));

        $result = $this->findByCode($results, self::TEST_CODE_A);
        $this->assertNotNull($result, 'Expected result for code ' . self::TEST_CODE_A);
        $this->assertSame(2, $result->units);
        $this->assertEqualsWithDelta(150.00, $result->billed, 0.01);
        $this->assertEqualsWithDelta(100.00, $result->paid, 0.01);
        $this->assertEqualsWithDelta(20.00, $result->adjusted, 0.01);
        $this->assertEqualsWithDelta(30.00, $result->balance, 0.01);
    }

    /**
     * Core bug fix test: insert 3 rows into codes for the same (code, code_type)
     * with different modifiers. Assert sums are NOT inflated.
     */
    public function testDuplicateCodesWithDifferentModifiers(): void
    {
        $this->insertFacility(1, 'Test Facility A');
        $this->insertEncounter(self::TEST_PID, self::TEST_ENCOUNTER, 1);
        $this->insertBilling(self::TEST_PID, self::TEST_ENCOUNTER, self::TEST_CODE_A, 'CPT4', 1, 200.00, 1);
        $this->insertArActivity(self::TEST_PID, self::TEST_ENCOUNTER, self::TEST_CODE_A, 150.00, 30.00);

        // 3 modifier variants of the same code and code_type
        $this->insertCode(self::TEST_CODE_A, 1, '', 0);
        $this->insertCode(self::TEST_CODE_A, 1, '26', 0);
        $this->insertCode(self::TEST_CODE_A, 1, 'TC', 0);

        $results = $this->service->getServiceCodeSummary(new \DateTimeImmutable(self::TEST_DATE), new \DateTimeImmutable(self::TEST_DATE));

        $result = $this->findByCode($results, self::TEST_CODE_A);
        $this->assertNotNull($result, 'Expected result for code ' . self::TEST_CODE_A);

        // Without the fix, these would be tripled (3x modifier rows)
        $this->assertSame(1, $result->units);
        $this->assertEqualsWithDelta(200.00, $result->billed, 0.01);
        $this->assertEqualsWithDelta(150.00, $result->paid, 0.01);
        $this->assertEqualsWithDelta(30.00, $result->adjusted, 0.01);
        $this->assertEqualsWithDelta(20.00, $result->balance, 0.01);
    }

    /**
     * With financialReportingOnly=true, only codes flagged financial_reporting=1
     * should appear.
     */
    public function testFinancialReportingFilter(): void
    {
        $this->insertFacility(1, 'Test Facility A');
        $this->insertEncounter(self::TEST_PID, self::TEST_ENCOUNTER, 1);

        // Code A: financial_reporting=1
        $this->insertBilling(self::TEST_PID, self::TEST_ENCOUNTER, self::TEST_CODE_A, 'CPT4', 1, 100.00, 1);
        $this->insertArActivity(self::TEST_PID, self::TEST_ENCOUNTER, self::TEST_CODE_A, 80.00, 10.00);
        $this->insertCode(self::TEST_CODE_A, 1, '', 1);

        // Code B: financial_reporting=0
        $this->insertBilling(self::TEST_PID, self::TEST_ENCOUNTER, self::TEST_CODE_B, 'CPT4', 1, 50.00, 2);
        $this->insertArActivity(self::TEST_PID, self::TEST_ENCOUNTER, self::TEST_CODE_B, 40.00, 5.00);
        $this->insertCode(self::TEST_CODE_B, 1, '', 0);

        $results = $this->service->getServiceCodeSummary(
            new \DateTimeImmutable(self::TEST_DATE),
            new \DateTimeImmutable(self::TEST_DATE),
            financialReportingOnly: true,
        );

        $this->assertNotNull($this->findByCode($results, self::TEST_CODE_A));
        $this->assertNull($this->findByCode($results, self::TEST_CODE_B));
    }

    /**
     * When modifiers have mixed financial_reporting values, MAX picks up the 1.
     */
    public function testFinancialReportingMaxAcrossModifiers(): void
    {
        $this->insertFacility(1, 'Test Facility A');
        $this->insertEncounter(self::TEST_PID, self::TEST_ENCOUNTER, 1);
        $this->insertBilling(self::TEST_PID, self::TEST_ENCOUNTER, self::TEST_CODE_A, 'CPT4', 1, 100.00, 1);
        $this->insertArActivity(self::TEST_PID, self::TEST_ENCOUNTER, self::TEST_CODE_A, 80.00, 10.00);

        // One modifier has financial_reporting=1, others have 0
        $this->insertCode(self::TEST_CODE_A, 1, '', 0);
        $this->insertCode(self::TEST_CODE_A, 1, '26', 1);
        $this->insertCode(self::TEST_CODE_A, 1, 'TC', 0);

        $results = $this->service->getServiceCodeSummary(new \DateTimeImmutable(self::TEST_DATE), new \DateTimeImmutable(self::TEST_DATE));

        $result = $this->findByCode($results, self::TEST_CODE_A);
        $this->assertNotNull($result);
        $this->assertTrue($result->financialReporting);
    }

    /**
     * Filtering by facility returns only matching data.
     */
    public function testFacilityFilter(): void
    {
        $this->insertFacility(1, 'Test Facility A');
        $this->insertFacility(2, 'Test Facility B');

        // Encounter in facility 1
        $this->insertEncounter(self::TEST_PID, self::TEST_ENCOUNTER, 1);
        $this->insertBilling(self::TEST_PID, self::TEST_ENCOUNTER, self::TEST_CODE_A, 'CPT4', 1, 100.00, 1);
        $this->insertArActivity(self::TEST_PID, self::TEST_ENCOUNTER, self::TEST_CODE_A, 80.00, 10.00);
        $this->insertCode(self::TEST_CODE_A, 1, '', 0);

        // Encounter in facility 2
        $enc2 = self::TEST_ENCOUNTER + 1;
        $this->insertEncounter(self::TEST_PID, $enc2, 2);
        $this->insertBilling(self::TEST_PID, $enc2, self::TEST_CODE_B, 'CPT4', 1, 50.00, 2);
        $this->insertArActivity(self::TEST_PID, $enc2, self::TEST_CODE_B, 40.00, 5.00);
        $this->insertCode(self::TEST_CODE_B, 1, '', 0);

        // Filter to facility 1 only
        $facilityId = $this->getFacilityId('Test Facility A');
        $this->assertNotNull($facilityId, 'Test facility A should exist');

        $results = $this->service->getServiceCodeSummary(
            new \DateTimeImmutable(self::TEST_DATE),
            new \DateTimeImmutable(self::TEST_DATE),
            facilityId: $facilityId,
        );

        $this->assertNotNull($this->findByCode($results, self::TEST_CODE_A));
        $this->assertNull($this->findByCode($results, self::TEST_CODE_B));
    }

    /**
     * Filtering by provider returns only matching data.
     */
    public function testProviderFilter(): void
    {
        $this->insertFacility(1, 'Test Facility A');
        $this->insertEncounter(self::TEST_PID, self::TEST_ENCOUNTER, 1);

        // Code A billed by provider 1
        $this->insertBilling(self::TEST_PID, self::TEST_ENCOUNTER, self::TEST_CODE_A, 'CPT4', 1, 100.00, 1);
        $this->insertArActivity(self::TEST_PID, self::TEST_ENCOUNTER, self::TEST_CODE_A, 80.00, 10.00);
        $this->insertCode(self::TEST_CODE_A, 1, '', 0);

        // Code B billed by provider 2
        $this->insertBilling(self::TEST_PID, self::TEST_ENCOUNTER, self::TEST_CODE_B, 'CPT4', 1, 50.00, 2);
        $this->insertArActivity(self::TEST_PID, self::TEST_ENCOUNTER, self::TEST_CODE_B, 40.00, 5.00);
        $this->insertCode(self::TEST_CODE_B, 1, '', 0);

        // Filter to provider 1 only
        $results = $this->service->getServiceCodeSummary(
            new \DateTimeImmutable(self::TEST_DATE),
            new \DateTimeImmutable(self::TEST_DATE),
            providerId: 1,
        );

        $this->assertNotNull($this->findByCode($results, self::TEST_CODE_A));
        $this->assertNull($this->findByCode($results, self::TEST_CODE_B));
    }

    /**
     * Billing code not in codes table. Assert financialReporting is null
     * (LEFT JOIN behavior preserved).
     */
    public function testNoMatchingCodesReturnsNullFinancialReporting(): void
    {
        $this->insertFacility(1, 'Test Facility A');
        $this->insertEncounter(self::TEST_PID, self::TEST_ENCOUNTER, 1);
        $this->insertBilling(self::TEST_PID, self::TEST_ENCOUNTER, self::TEST_CODE_A, 'CPT4', 1, 100.00, 1);
        $this->insertArActivity(self::TEST_PID, self::TEST_ENCOUNTER, self::TEST_CODE_A, 80.00, 10.00);
        // Deliberately do NOT insert into codes table

        $results = $this->service->getServiceCodeSummary(new \DateTimeImmutable(self::TEST_DATE), new \DateTimeImmutable(self::TEST_DATE));

        $result = $this->findByCode($results, self::TEST_CODE_A);
        $this->assertNotNull($result, 'Expected result even without codes table match');
        $this->assertNull($result->financialReporting);
    }

    // -------------------------------------------------------------------
    // Fixture helpers
    // -------------------------------------------------------------------

    private function insertFacility(int $index, string $name): void
    {
        // Use a unique name prefix so we can identify and clean up test facilities
        $testName = 'test-fixture-' . $name;

        // Check if facility already exists from a prior test in this run
        $existing = QueryUtils::querySingleRow(
            "SELECT id FROM facility WHERE name = ?",
            [$testName]
        );
        if ($existing) {
            return;
        }

        $id = QueryUtils::sqlInsert(
            "INSERT INTO facility (name) VALUES (?)",
            [$testName]
        );
        $this->insertedIds['facility'][] = $id;
    }

    private function getFacilityId(string $name): ?int
    {
        /** @var array{id: int}|false $row */
        $row = QueryUtils::querySingleRow(
            "SELECT id FROM facility WHERE name = ?",
            ['test-fixture-' . $name]
        );
        return $row ? (int) $row['id'] : null;
    }

    private function insertEncounter(int $pid, int $encounter, int $facilityIndex): void
    {
        $facilityId = $this->getFacilityId(
            $facilityIndex === 1 ? 'Test Facility A' : 'Test Facility B'
        );

        $id = QueryUtils::sqlInsert(
            "INSERT INTO form_encounter (pid, encounter, date, facility_id, reason)"
            . " VALUES (?, ?, ?, ?, ?)",
            [$pid, $encounter, self::TEST_DATE . ' 12:00:00', $facilityId, 'test-fixture-report-test']
        );
        $this->insertedIds['form_encounter'][] = $id;
    }

    private function insertBilling(
        int $pid,
        int $encounter,
        string $code,
        string $codeType,
        int $units,
        float $fee,
        int $providerId
    ): void {
        $id = QueryUtils::sqlInsert(
            "INSERT INTO billing (pid, encounter, code, code_type, units, fee, provider_id, activity)"
            . " VALUES (?, ?, ?, ?, ?, ?, ?, 1)",
            [$pid, $encounter, $code, $codeType, $units, $fee, $providerId]
        );
        $this->insertedIds['billing'][] = $id;
    }

    private function insertArActivity(
        int $pid,
        int $encounter,
        string $code,
        float $payAmount,
        float $adjAmount
    ): void {
        $this->arSequenceNo++;
        $id = QueryUtils::sqlInsert(
            "INSERT INTO ar_activity (pid, encounter, sequence_no, code_type, code, payer_type,"
            . " post_time, post_user, session_id, pay_amount, adj_amount, modified_time, follow_up, account_code)"
            . " VALUES (?, ?, ?, '', ?, 0, NOW(), 1, 0, ?, ?, NOW(), '', '')",
            [$pid, $encounter, $this->arSequenceNo, $code, $payAmount, $adjAmount]
        );
        $this->insertedIds['ar_activity'][] = $id;
    }

    private function insertCode(string $code, int $codeType, string $modifier, int $financialReporting): void
    {
        $id = QueryUtils::sqlInsert(
            "INSERT INTO codes (code, code_type, modifier, financial_reporting, code_text)"
            . " VALUES (?, ?, ?, ?, ?)",
            [$code, $codeType, $modifier, $financialReporting, 'test-fixture-report-test']
        );
        $this->insertedIds['codes'][] = $id;
    }

    /**
     * @param ServiceCodeSummary[] $results
     */
    private function findByCode(array $results, string $code): ?ServiceCodeSummary
    {
        foreach ($results as $result) {
            if ($result->code === $code) {
                return $result;
            }
        }
        return null;
    }

    /**
     * Remove all test fixture data by tracked IDs and known identifiers.
     */
    private function cleanUpTestData(): void
    {
        // Delete by tracked IDs
        foreach ($this->insertedIds['ar_activity'] as $id) {
            QueryUtils::fetchRecordsNoLog("DELETE FROM ar_activity WHERE pid = ?", [self::TEST_PID]);
        }
        foreach ($this->insertedIds['billing'] as $id) {
            QueryUtils::fetchRecordsNoLog("DELETE FROM billing WHERE id = ?", [$id]);
        }
        foreach ($this->insertedIds['form_encounter'] as $id) {
            QueryUtils::fetchRecordsNoLog("DELETE FROM form_encounter WHERE id = ?", [$id]);
        }
        foreach ($this->insertedIds['codes'] as $id) {
            QueryUtils::fetchRecordsNoLog("DELETE FROM codes WHERE id = ?", [$id]);
        }
        foreach ($this->insertedIds['facility'] as $id) {
            QueryUtils::fetchRecordsNoLog("DELETE FROM facility WHERE id = ?", [$id]);
        }

        // Also delete by known identifiers as a safety net
        QueryUtils::fetchRecordsNoLog(
            "DELETE FROM ar_activity WHERE pid = ?",
            [self::TEST_PID]
        );
        QueryUtils::fetchRecordsNoLog(
            "DELETE FROM billing WHERE pid = ?",
            [self::TEST_PID]
        );
        QueryUtils::fetchRecordsNoLog(
            "DELETE FROM form_encounter WHERE reason = 'test-fixture-report-test'",
            []
        );
        QueryUtils::fetchRecordsNoLog(
            "DELETE FROM codes WHERE code_text = 'test-fixture-report-test'",
            []
        );
        QueryUtils::fetchRecordsNoLog(
            "DELETE FROM facility WHERE name LIKE 'test-fixture-%'",
            []
        );

        $this->insertedIds = [
            'form_encounter' => [],
            'billing' => [],
            'ar_activity' => [],
            'codes' => [],
            'facility' => [],
        ];
        $this->arSequenceNo = 0;
    }
}
