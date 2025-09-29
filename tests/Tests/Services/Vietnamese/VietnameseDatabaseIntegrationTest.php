<?php

/**
 * Vietnamese Database Integration Tests
 * Comprehensive tests for all Vietnamese PT database tables
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Dang Tran <tqvdang@msn.com>
 * @copyright Copyright (c) 2024 Dang Tran
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services\Vietnamese;

use PHPUnit\Framework\TestCase;

class VietnameseDatabaseIntegrationTest extends TestCase
{
    private static $dbConnection;

    public static function setUpBeforeClass(): void
    {
        // Get database connection using OpenEMR's database connection
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
     * Test database connection with Vietnamese collation
     */
    public function testDatabaseConnectionWithVietnameseCollation(): void
    {
        $stmt = self::$dbConnection->query("SELECT @@collation_connection");
        $collation = $stmt->fetchColumn();

        $this->assertStringContainsString('utf8mb4', $collation);
    }

    /**
     * Test vietnamese_test table exists and has data
     */
    public function testVietnameseTestTableExists(): void
    {
        $stmt = self::$dbConnection->query("SHOW TABLES LIKE 'vietnamese_test'");
        $result = $stmt->fetch();

        $this->assertNotFalse($result, "vietnamese_test table should exist");
    }

    /**
     * Test insert and retrieve Vietnamese text
     */
    public function testInsertAndRetrieveVietnameseText(): void
    {
        $testText = 'Vật lý trị liệu - Test Insert ' . time();

        $stmt = self::$dbConnection->prepare(
            "INSERT INTO vietnamese_test (vietnamese_text) VALUES (:text)"
        );
        $stmt->execute(['text' => $testText]);

        $insertId = self::$dbConnection->lastInsertId();
        $this->assertGreaterThan(0, $insertId);

        // Retrieve and verify
        $stmt = self::$dbConnection->prepare(
            "SELECT vietnamese_text FROM vietnamese_test WHERE id = :id"
        );
        $stmt->execute(['id' => $insertId]);
        $retrieved = $stmt->fetchColumn();

        $this->assertEquals($testText, $retrieved);
        $this->assertTrue(mb_check_encoding($retrieved, 'UTF-8'));

        // Cleanup
        self::$dbConnection->exec("DELETE FROM vietnamese_test WHERE id = $insertId");
    }

    /**
     * Test Vietnamese text search with LIKE
     */
    public function testVietnameseTextSearchWithLike(): void
    {
        $stmt = self::$dbConnection->query(
            "SELECT COUNT(*) FROM vietnamese_test WHERE vietnamese_text LIKE '%Vật lý%'"
        );
        $count = $stmt->fetchColumn();

        $this->assertGreaterThan(0, $count, "Should find records with 'Vật lý'");
    }

    /**
     * Test Vietnamese collation sorting
     */
    public function testVietnameseCollationSorting(): void
    {
        // Insert test records
        $testData = [
            'Phạm Văn C',
            'Nguyễn Văn A',
            'Trần Văn B',
            'Lê Văn D'
        ];

        $insertedIds = [];
        $stmt = self::$dbConnection->prepare(
            "INSERT INTO vietnamese_test (vietnamese_text) VALUES (:text)"
        );

        foreach ($testData as $text) {
            $stmt->execute(['text' => $text]);
            $insertedIds[] = self::$dbConnection->lastInsertId();
        }

        // Retrieve sorted
        $placeholders = implode(',', $insertedIds);
        $stmt = self::$dbConnection->query(
            "SELECT vietnamese_text FROM vietnamese_test
             WHERE id IN ($placeholders)
             ORDER BY vietnamese_text COLLATE utf8mb4_vietnamese_ci"
        );
        $sorted = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        // Vietnamese alphabetical order: L, N, P, T
        $this->assertEquals('Lê Văn D', $sorted[0]);
        $this->assertEquals('Nguyễn Văn A', $sorted[1]);
        $this->assertEquals('Phạm Văn C', $sorted[2]);
        $this->assertEquals('Trần Văn B', $sorted[3]);

        // Cleanup
        self::$dbConnection->exec("DELETE FROM vietnamese_test WHERE id IN ($placeholders)");
    }

    /**
     * Test special Vietnamese characters preservation
     */
    public function testSpecialVietnameseCharactersPreservation(): void
    {
        $specialChars = [
            'á', 'à', 'ả', 'ã', 'ạ',
            'ă', 'ắ', 'ằ', 'ẳ', 'ẵ', 'ặ',
            'â', 'ấ', 'ầ', 'ẩ', 'ẫ', 'ậ',
            'đ', 'Đ',
            'é', 'è', 'ẻ', 'ẽ', 'ẹ',
            'ê', 'ế', 'ề', 'ể', 'ễ', 'ệ',
            'ô', 'ố', 'ồ', 'ổ', 'ỗ', 'ộ',
            'ơ', 'ớ', 'ờ', 'ở', 'ỡ', 'ợ',
            'ư', 'ứ', 'ừ', 'ử', 'ữ', 'ự'
        ];

        $testText = 'Special chars: ' . implode(' ', $specialChars);

        $stmt = self::$dbConnection->prepare(
            "INSERT INTO vietnamese_test (vietnamese_text) VALUES (:text)"
        );
        $stmt->execute(['text' => $testText]);
        $insertId = self::$dbConnection->lastInsertId();

        // Retrieve and verify
        $stmt = self::$dbConnection->prepare(
            "SELECT vietnamese_text FROM vietnamese_test WHERE id = :id"
        );
        $stmt->execute(['id' => $insertId]);
        $retrieved = $stmt->fetchColumn();

        $this->assertEquals($testText, $retrieved);

        foreach ($specialChars as $char) {
            $this->assertStringContainsString($char, $retrieved);
        }

        // Cleanup
        self::$dbConnection->exec("DELETE FROM vietnamese_test WHERE id = $insertId");
    }

    /**
     * Test case-insensitive Vietnamese search
     */
    public function testCaseInsensitiveVietnameseSearch(): void
    {
        $testText = 'Đau Lưng Mãn Tính Test ' . time();

        $stmt = self::$dbConnection->prepare(
            "INSERT INTO vietnamese_test (vietnamese_text) VALUES (:text)"
        );
        $stmt->execute(['text' => $testText]);
        $insertId = self::$dbConnection->lastInsertId();

        // Search with different case
        $stmt = self::$dbConnection->prepare(
            "SELECT COUNT(*) FROM vietnamese_test
             WHERE id = :id AND vietnamese_text LIKE :search"
        );

        // Test lowercase search
        $stmt->execute(['id' => $insertId, 'search' => '%đau lưng%']);
        $this->assertEquals(1, $stmt->fetchColumn());

        // Test uppercase search
        $stmt->execute(['id' => $insertId, 'search' => '%ĐAU LƯNG%']);
        $this->assertEquals(1, $stmt->fetchColumn());

        // Cleanup
        self::$dbConnection->exec("DELETE FROM vietnamese_test WHERE id = $insertId");
    }
}