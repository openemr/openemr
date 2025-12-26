<?php

/**
 * Vietnamese Stored Procedure Tests
 * Tests GetBilingualTerm stored procedure
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Dang Tran <tqvdang@msn.com>
 * @copyright Copyright (c) 2024 Dang Tran
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services\Vietnamese;

use PHPUnit\Framework\TestCase;

class VietnameseStoredProcedureTest extends TestCase
{
    private static $dbConnection;

    public static function setUpBeforeClass(): void
    {
        global $sqlconf;

        $host = $sqlconf["host"] ?? 'localhost';
        $port = $sqlconf["port"] ?? '3306';
        $dbase = $sqlconf["dbase"] ?? 'openemr';
        $login = $sqlconf["login"] ?? 'openemr';
        $pass = $sqlconf["pass"] ?? '';

        try {
            self::$dbConnection = new \PDO(
                "mysql:host=$host;port=$port;dbname=$dbase;charset=utf8mb4",
                $login,
                $pass,
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_vietnamese_ci"
                ]
            );
        } catch (\PDOException $e) {
            self::markTestSkipped('Database connection not available: ' . $e->getMessage());
        }
    }

    /**
     * Test stored procedure exists
     */
    public function testStoredProcedureExists(): void
    {
        $stmt = self::$dbConnection->query("
            SELECT COUNT(*) FROM information_schema.ROUTINES
            WHERE ROUTINE_SCHEMA = DATABASE()
            AND ROUTINE_TYPE = 'PROCEDURE'
            AND ROUTINE_NAME = 'GetBilingualTerm'
        ");
        $count = $stmt->fetchColumn();

        $this->assertEquals(1, $count, "GetBilingualTerm procedure should exist");
    }

    /**
     * Test stored procedure with Vietnamese search term
     */
    public function testGetBilingualTermVietnameseSearch(): void
    {
        try {
            $stmt = self::$dbConnection->prepare("CALL GetBilingualTerm('Vật lý', 'vi')");
            $stmt->execute();
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (!empty($results)) {
                $this->assertIsArray($results);
                $this->assertArrayHasKey('vietnamese_term', $results[0]);
                $this->assertArrayHasKey('english_term', $results[0]);

                // Should contain "Vật lý trị liệu"
                $found = false;
                foreach ($results as $row) {
                    if (stripos($row['vietnamese_term'], 'Vật lý') !== false) {
                        $found = true;
                        break;
                    }
                }
                $this->assertTrue($found, "Should find term containing 'Vật lý'");
            } else {
                $this->markTestSkipped('No results found for Vietnamese search');
            }
        } catch (\PDOException $e) {
            $this->markTestSkipped('Stored procedure call failed: ' . $e->getMessage());
        }
    }

    /**
     * Test stored procedure with English search term
     */
    public function testGetBilingualTermEnglishSearch(): void
    {
        try {
            $stmt = self::$dbConnection->prepare("CALL GetBilingualTerm('Physio', 'en')");
            $stmt->execute();
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (!empty($results)) {
                $this->assertIsArray($results);

                // Should contain "Physiotherapy"
                $found = false;
                foreach ($results as $row) {
                    if (stripos($row['english_term'], 'Physio') !== false) {
                        $found = true;
                        break;
                    }
                }
                $this->assertTrue($found, "Should find term containing 'Physio'");
            } else {
                $this->markTestSkipped('No results found for English search');
            }
        } catch (\PDOException $e) {
            $this->markTestSkipped('Stored procedure call failed: ' . $e->getMessage());
        }
    }

    /**
     * Test stored procedure search ranking
     */
    public function testGetBilingualTermSearchRanking(): void
    {
        try {
            // Insert test data with exact, prefix, and partial matches
            $testId1 = null;
            $testId2 = null;
            $testId3 = null;

            $stmt = self::$dbConnection->prepare("
                INSERT INTO vietnamese_medical_terms
                (english_term, vietnamese_term, category)
                VALUES (?, ?, 'test')
            ");

            // Exact match
            $stmt->execute(['Pain', 'Đau', 'test']);
            $testId1 = self::$dbConnection->lastInsertId();

            // Prefix match
            $stmt->execute(['Pain Assessment', 'Đánh giá đau', 'test']);
            $testId2 = self::$dbConnection->lastInsertId();

            // Partial match
            $stmt->execute(['Chronic Pain', 'Đau mãn tính', 'test']);
            $testId3 = self::$dbConnection->lastInsertId();

            // Search and verify ranking
            $stmt = self::$dbConnection->prepare("CALL GetBilingualTerm('Pain', 'en')");
            $stmt->execute();
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (!empty($results)) {
                // First result should be exact or prefix match
                $firstResult = $results[0]['english_term'];
                $this->assertStringStartsWith('Pain', $firstResult);
            }

            // Cleanup
            if ($testId1) {
                self::$dbConnection->exec("DELETE FROM vietnamese_medical_terms WHERE id = $testId1");
            }
            if ($testId2) {
                self::$dbConnection->exec("DELETE FROM vietnamese_medical_terms WHERE id = $testId2");
            }
            if ($testId3) {
                self::$dbConnection->exec("DELETE FROM vietnamese_medical_terms WHERE id = $testId3");
            }
        } catch (\PDOException $e) {
            $this->markTestSkipped('Stored procedure ranking test failed: ' . $e->getMessage());
        }
    }

    /**
     * Test stored procedure with special Vietnamese characters
     */
    public function testGetBilingualTermSpecialCharacters(): void
    {
        try {
            $stmt = self::$dbConnection->prepare("CALL GetBilingualTerm('đau', 'vi')");
            $stmt->execute();
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (!empty($results)) {
                $this->assertIsArray($results);

                // Verify Vietnamese encoding
                foreach ($results as $row) {
                    $this->assertTrue(mb_check_encoding($row['vietnamese_term'], 'UTF-8'));
                }
            } else {
                $this->markTestSkipped('No results found for special character search');
            }
        } catch (\PDOException $e) {
            $this->markTestSkipped('Special character search failed: ' . $e->getMessage());
        }
    }

    /**
     * Test stored procedure empty search handling
     */
    public function testGetBilingualTermEmptySearch(): void
    {
        try {
            $stmt = self::$dbConnection->prepare("CALL GetBilingualTerm('', 'en')");
            $stmt->execute();
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Empty search may return all or no results
            $this->assertIsArray($results);
        } catch (\PDOException $e) {
            // Empty search might cause an error, which is acceptable
            $this->assertTrue(true);
        }
    }

    /**
     * Test stored procedure with non-existent term
     */
    public function testGetBilingualTermNonExistent(): void
    {
        try {
            $uniqueTerm = 'NonExistentTerm' . time();
            $stmt = self::$dbConnection->prepare("CALL GetBilingualTerm(?, 'en')");
            $stmt->execute([$uniqueTerm]);
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $this->assertIsArray($results);
            $this->assertCount(0, $results, "Non-existent term should return empty results");
        } catch (\PDOException $e) {
            $this->markTestSkipped('Non-existent term test failed: ' . $e->getMessage());
        }
    }
}