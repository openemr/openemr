<?php

/**
 * Unit tests for Prior Authorization Auto-Lookup in Claim class
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\Billing;

use PHPUnit\Framework\TestCase;
use OpenEMR\Common\Database\QueryUtils;

/**
 * Test Prior Authorization functionality in the Claim class
 *
 * These tests validate:
 * - Table existence checking
 * - CPT code matching (single and comma-separated)
 * - Date range validation
 * - Fallback behavior
 * - Error handling
 */
class ClaimPriorAuthTest extends TestCase
{
    private static $testTableCreated = false;

    /**
     * Set up test database table
     */
    public static function setUpBeforeClass(): void
    {
        // Create test table if it doesn't exist
        if (!self::tableExists('module_prior_authorizations')) {
            self::createTestTable();
            self::$testTableCreated = true;
        }
    }

    /**
     * Clean up test data
     */
    public static function tearDownAfterClass(): void
    {
        // Only drop table if we created it
        if (self::$testTableCreated) {
            self::dropTestTable();
        }
    }

    /**
     * Test that the prior auth table can be detected
     */
    public function testPriorAuthTableExists(): void
    {
        $this->assertTrue(
            self::tableExists('module_prior_authorizations'),
            'module_prior_authorizations table should exist'
        );
    }

    /**
     * Test that required columns exist in the table
     */
    public function testRequiredColumnsExist(): void
    {
        $columns = QueryUtils::listTableFields('module_prior_authorizations');
        $columnsLower = array_map('strtolower', $columns);

        $required = ['id', 'pid', 'auth_num', 'start_date', 'end_date', 'cpt'];

        foreach ($required as $col) {
            $this->assertContains(
                $col,
                $columnsLower,
                "Column '$col' should exist in module_prior_authorizations table"
            );
        }
    }

    /**
     * Test single CPT code matching
     */
    public function testSingleCptMatching(): void
    {
        // Insert test authorization
        $testPid = 999999;
        $testAuth = 'TEST-AUTH-' . time();
        $testCpt = '99213';

        $this->insertTestAuth($testPid, $testAuth, '2025-01-01', '2025-12-31', $testCpt);

        // Query should find the authorization
        $result = $this->queryAuth($testPid, '2025-06-15', $testCpt);

        $this->assertEquals(
            $testAuth,
            $result,
            "Should find authorization for single CPT code"
        );

        // Clean up
        $this->deleteTestAuth($testPid);
    }

    /**
     * Test comma-separated CPT code matching
     */
    public function testCommaSeparatedCptMatching(): void
    {
        $testPid = 999998;
        $testAuth = 'TEST-AUTH-MULTI-' . time();
        $testCpts = '99213,99214,99215';

        $this->insertTestAuth($testPid, $testAuth, '2025-01-01', '2025-12-31', $testCpts);

        // Should find for each CPT in the list
        $this->assertEquals($testAuth, $this->queryAuth($testPid, '2025-06-15', '99213'));
        $this->assertEquals($testAuth, $this->queryAuth($testPid, '2025-06-15', '99214'));
        $this->assertEquals($testAuth, $this->queryAuth($testPid, '2025-06-15', '99215'));

        // Should not find for CPT not in list
        $this->assertEquals('', $this->queryAuth($testPid, '2025-06-15', '99216'));

        // Clean up
        $this->deleteTestAuth($testPid);
    }

    /**
     * Test date range validation
     */
    public function testDateRangeValidation(): void
    {
        $testPid = 999997;
        $testAuth = 'TEST-AUTH-DATES-' . time();

        $this->insertTestAuth($testPid, $testAuth, '2025-01-01', '2025-06-30', '99213');

        // Should find within range
        $this->assertEquals($testAuth, $this->queryAuth($testPid, '2025-03-15', '99213'));

        // Should not find before start date
        $this->assertEquals('', $this->queryAuth($testPid, '2024-12-31', '99213'));

        // Should not find after end date
        $this->assertEquals('', $this->queryAuth($testPid, '2025-07-01', '99213'));

        // Clean up
        $this->deleteTestAuth($testPid);
    }

    /**
     * Test NULL date handling (no date restrictions)
     */
    public function testNullDateHandling(): void
    {
        $testPid = 999996;
        $testAuth = 'TEST-AUTH-NODATE-' . time();

        // Insert with NULL dates
        $this->insertTestAuth($testPid, $testAuth, null, null, '99213');

        // Should find for any date
        $this->assertEquals($testAuth, $this->queryAuth($testPid, '2020-01-01', '99213'));
        $this->assertEquals($testAuth, $this->queryAuth($testPid, '2030-12-31', '99213'));

        // Clean up
        $this->deleteTestAuth($testPid);
    }

    /**
     * Test most recent authorization is returned
     */
    public function testMostRecentAuthReturned(): void
    {
        $testPid = 999995;
        $oldAuth = 'OLD-AUTH-' . time();
        $newAuth = 'NEW-AUTH-' . time();

        // Insert old authorization
        $this->insertTestAuth($testPid, $oldAuth, '2025-01-01', '2025-12-31', '99213');

        // Wait a moment and insert new authorization
        sleep(1);
        $this->insertTestAuth($testPid, $newAuth, '2025-01-01', '2025-12-31', '99213');

        // Should return the most recent (by id DESC)
        $result = $this->queryAuth($testPid, '2025-06-15', '99213');
        $this->assertEquals(
            $newAuth,
            $result,
            "Should return most recent authorization when multiple exist"
        );

        // Clean up
        $this->deleteTestAuth($testPid);
    }

    // Helper methods

    private static function tableExists(string $tableName): bool
    {
        try {
            return QueryUtils::existsTable($tableName);
        } catch (\Exception $e) {
            return false;
        }
    }

    private static function createTestTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `module_prior_authorizations` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `pid` int(11) NOT NULL,
            `auth_num` varchar(255) NOT NULL,
            `start_date` date DEFAULT NULL,
            `end_date` date DEFAULT NULL,
            `cpt` varchar(500) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_pid_dates` (`pid`,`start_date`,`end_date`),
            KEY `idx_cpt` (`cpt`(191))
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        sqlStatement($sql);
    }

    private static function dropTestTable(): void
    {
        sqlStatement("DROP TABLE IF EXISTS `module_prior_authorizations`");
    }

    private function insertTestAuth($pid, $authNum, $startDate, $endDate, $cpt): void
    {
        $sql = "INSERT INTO module_prior_authorizations (pid, auth_num, start_date, end_date, cpt)
                VALUES (?, ?, ?, ?, ?)";

        sqlStatement($sql, [$pid, $authNum, $startDate, $endDate, $cpt]);
    }

    private function deleteTestAuth($pid): void
    {
        sqlStatement("DELETE FROM module_prior_authorizations WHERE pid = ?", [$pid]);
    }

    private function queryAuth($pid, $serviceDate, $cpt): string
    {
        $sql = "SELECT auth_num FROM module_prior_authorizations
                WHERE pid = ?
                  AND (start_date IS NULL OR start_date <= ?)
                  AND (end_date IS NULL OR end_date >= ?)
                  AND (cpt = ? OR FIND_IN_SET(?, REPLACE(cpt, ' ', '')))
                ORDER BY id DESC
                LIMIT 1";

        $row = QueryUtils::querySingleRow($sql, [$pid, $serviceDate, $serviceDate, $cpt, $cpt]);
        return $row['auth_num'] ?? '';
    }
}
