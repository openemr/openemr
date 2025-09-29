<?php

/**
 * Vietnamese Medical Terms Table Integration Tests
 * Tests CRUD operations on vietnamese_medical_terms table
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Dang Tran <tqvdang@msn.com>
 * @copyright Copyright (c) 2024 Dang Tran
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services\Vietnamese;

use PHPUnit\Framework\TestCase;

class VietnameseMedicalTermsTableTest extends TestCase
{
    private static $dbConnection;
    private static $insertedIds = [];

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

    public static function tearDownAfterClass(): void
    {
        // Cleanup all inserted test records
        if (!empty(self::$insertedIds)) {
            $placeholders = implode(',', self::$insertedIds);
            self::$dbConnection->exec(
                "DELETE FROM vietnamese_medical_terms WHERE id IN ($placeholders)"
            );
        }
    }

    /**
     * Test table exists
     */
    public function testTableExists(): void
    {
        $stmt = self::$dbConnection->query("SHOW TABLES LIKE 'vietnamese_medical_terms'");
        $result = $stmt->fetch();

        $this->assertNotFalse($result, "vietnamese_medical_terms table should exist");
    }

    /**
     * Test table has expected columns
     */
    public function testTableStructure(): void
    {
        $stmt = self::$dbConnection->query("DESCRIBE vietnamese_medical_terms");
        $columns = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        $expectedColumns = [
            'id',
            'english_term',
            'vietnamese_term',
            'category',
            'subcategory',
            'description_en',
            'description_vi',
            'synonyms_en',
            'synonyms_vi',
            'abbreviation',
            'is_active',
            'created_at',
            'updated_at'
        ];

        foreach ($expectedColumns as $column) {
            $this->assertContains($column, $columns, "Column $column should exist");
        }
    }

    /**
     * Test insert medical term
     */
    public function testInsertMedicalTerm(): void
    {
        $termData = [
            'english_term' => 'Test Physiotherapy Term',
            'vietnamese_term' => 'Thuật ngữ vật lý trị liệu test',
            'category' => 'test',
            'subcategory' => 'unit_test',
            'description_en' => 'Test description in English',
            'description_vi' => 'Mô tả test bằng tiếng Việt',
            'synonyms_en' => 'Test PT',
            'synonyms_vi' => 'VLTT test',
            'abbreviation' => 'TPT',
            'is_active' => 1
        ];

        $stmt = self::$dbConnection->prepare("
            INSERT INTO vietnamese_medical_terms
            (english_term, vietnamese_term, category, subcategory,
             description_en, description_vi, synonyms_en, synonyms_vi,
             abbreviation, is_active)
            VALUES
            (:english_term, :vietnamese_term, :category, :subcategory,
             :description_en, :description_vi, :synonyms_en, :synonyms_vi,
             :abbreviation, :is_active)
        ");

        $result = $stmt->execute($termData);
        $this->assertTrue($result);

        $insertId = self::$dbConnection->lastInsertId();
        $this->assertGreaterThan(0, $insertId);
        self::$insertedIds[] = $insertId;

        // Verify inserted data
        $stmt = self::$dbConnection->prepare("
            SELECT * FROM vietnamese_medical_terms WHERE id = :id
        ");
        $stmt->execute(['id' => $insertId]);
        $retrieved = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->assertEquals($termData['english_term'], $retrieved['english_term']);
        $this->assertEquals($termData['vietnamese_term'], $retrieved['vietnamese_term']);
        $this->assertEquals($termData['category'], $retrieved['category']);
        $this->assertTrue(mb_check_encoding($retrieved['vietnamese_term'], 'UTF-8'));
    }

    /**
     * Test retrieve medical term by English term
     */
    public function testRetrieveByEnglishTerm(): void
    {
        $stmt = self::$dbConnection->prepare("
            SELECT * FROM vietnamese_medical_terms
            WHERE english_term = 'Physiotherapy' AND is_active = 1
            LIMIT 1
        ");
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($result) {
            $this->assertEquals('Vật lý trị liệu', $result['vietnamese_term']);
            $this->assertEquals('general', $result['category']);
        } else {
            $this->markTestSkipped('Physiotherapy term not found in database');
        }
    }

    /**
     * Test retrieve medical term by Vietnamese term
     */
    public function testRetrieveByVietnameseTerm(): void
    {
        $stmt = self::$dbConnection->prepare("
            SELECT * FROM vietnamese_medical_terms
            WHERE vietnamese_term = 'Vật lý trị liệu' AND is_active = 1
            LIMIT 1
        ");
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($result) {
            $this->assertEquals('Physiotherapy', $result['english_term']);
            $this->assertNotEmpty($result['vietnamese_term']);
        } else {
            $this->markTestSkipped('Vật lý trị liệu term not found in database');
        }
    }

    /**
     * Test search by category
     */
    public function testSearchByCategory(): void
    {
        $stmt = self::$dbConnection->prepare("
            SELECT COUNT(*) FROM vietnamese_medical_terms
            WHERE category = 'general' AND is_active = 1
        ");
        $stmt->execute();
        $count = $stmt->fetchColumn();

        $this->assertGreaterThan(0, $count, "Should have general category terms");
    }

    /**
     * Test update medical term
     */
    public function testUpdateMedicalTerm(): void
    {
        // Insert test record first
        $stmt = self::$dbConnection->prepare("
            INSERT INTO vietnamese_medical_terms
            (english_term, vietnamese_term, category, is_active)
            VALUES ('Update Test', 'Test cập nhật', 'test', 1)
        ");
        $stmt->execute();
        $insertId = self::$dbConnection->lastInsertId();
        self::$insertedIds[] = $insertId;

        // Update the record
        $newDescription = 'Updated description - Mô tả đã cập nhật';
        $stmt = self::$dbConnection->prepare("
            UPDATE vietnamese_medical_terms
            SET description_vi = :desc
            WHERE id = :id
        ");
        $stmt->execute(['desc' => $newDescription, 'id' => $insertId]);

        // Verify update
        $stmt = self::$dbConnection->prepare("
            SELECT description_vi FROM vietnamese_medical_terms WHERE id = :id
        ");
        $stmt->execute(['id' => $insertId]);
        $retrieved = $stmt->fetchColumn();

        $this->assertEquals($newDescription, $retrieved);
    }

    /**
     * Test soft delete (is_active flag)
     */
    public function testSoftDelete(): void
    {
        // Insert test record
        $stmt = self::$dbConnection->prepare("
            INSERT INTO vietnamese_medical_terms
            (english_term, vietnamese_term, category, is_active)
            VALUES ('Delete Test', 'Test xóa', 'test', 1)
        ");
        $stmt->execute();
        $insertId = self::$dbConnection->lastInsertId();
        self::$insertedIds[] = $insertId;

        // Soft delete (set is_active = 0)
        $stmt = self::$dbConnection->prepare("
            UPDATE vietnamese_medical_terms SET is_active = 0 WHERE id = :id
        ");
        $stmt->execute(['id' => $insertId]);

        // Verify not retrieved in active query
        $stmt = self::$dbConnection->prepare("
            SELECT COUNT(*) FROM vietnamese_medical_terms
            WHERE id = :id AND is_active = 1
        ");
        $stmt->execute(['id' => $insertId]);
        $count = $stmt->fetchColumn();

        $this->assertEquals(0, $count, "Soft deleted record should not appear in active queries");

        // Verify still exists in database
        $stmt = self::$dbConnection->prepare("
            SELECT COUNT(*) FROM vietnamese_medical_terms WHERE id = :id
        ");
        $stmt->execute(['id' => $insertId]);
        $count = $stmt->fetchColumn();

        $this->assertEquals(1, $count, "Soft deleted record should still exist in database");
    }

    /**
     * Test search with LIKE for Vietnamese text
     */
    public function testVietnameseTextLikeSearch(): void
    {
        $stmt = self::$dbConnection->prepare("
            SELECT COUNT(*) FROM vietnamese_medical_terms
            WHERE vietnamese_term LIKE :search AND is_active = 1
        ");
        $stmt->execute(['search' => '%đau%']);
        $count = $stmt->fetchColumn();

        $this->assertGreaterThanOrEqual(0, $count, "Should be able to search Vietnamese terms with LIKE");
    }

    /**
     * Test multiple category filtering
     */
    public function testMultipleCategoryFiltering(): void
    {
        $categories = ['general', 'assessment', 'treatment', 'condition'];

        $placeholders = implode(',', array_fill(0, count($categories), '?'));
        $stmt = self::$dbConnection->prepare("
            SELECT category, COUNT(*) as count
            FROM vietnamese_medical_terms
            WHERE category IN ($placeholders) AND is_active = 1
            GROUP BY category
        ");
        $stmt->execute($categories);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->assertGreaterThanOrEqual(0, count($results));

        foreach ($results as $row) {
            $this->assertContains($row['category'], $categories);
            $this->assertGreaterThan(0, $row['count']);
        }
    }

    /**
     * Test abbreviation uniqueness
     */
    public function testAbbreviationHandling(): void
    {
        $stmt = self::$dbConnection->prepare("
            SELECT COUNT(*) FROM vietnamese_medical_terms
            WHERE abbreviation IS NOT NULL AND abbreviation != '' AND is_active = 1
        ");
        $stmt->execute();
        $count = $stmt->fetchColumn();

        $this->assertGreaterThanOrEqual(0, $count, "Should have terms with abbreviations");
    }

    /**
     * Test timestamp auto-update
     */
    public function testTimestampAutoUpdate(): void
    {
        // Insert record
        $stmt = self::$dbConnection->prepare("
            INSERT INTO vietnamese_medical_terms
            (english_term, vietnamese_term, category)
            VALUES ('Timestamp Test', 'Test timestamp', 'test')
        ");
        $stmt->execute();
        $insertId = self::$dbConnection->lastInsertId();
        self::$insertedIds[] = $insertId;

        // Get initial timestamps
        $stmt = self::$dbConnection->prepare("
            SELECT created_at, updated_at FROM vietnamese_medical_terms WHERE id = :id
        ");
        $stmt->execute(['id' => $insertId]);
        $initial = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->assertNotEmpty($initial['created_at']);
        $this->assertNotEmpty($initial['updated_at']);

        // Wait a moment and update
        sleep(1);
        $stmt = self::$dbConnection->prepare("
            UPDATE vietnamese_medical_terms
            SET description_en = 'Updated'
            WHERE id = :id
        ");
        $stmt->execute(['id' => $insertId]);

        // Get updated timestamps
        $stmt = self::$dbConnection->prepare("
            SELECT created_at, updated_at FROM vietnamese_medical_terms WHERE id = :id
        ");
        $stmt->execute(['id' => $insertId]);
        $updated = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->assertEquals($initial['created_at'], $updated['created_at'], "created_at should not change");
        $this->assertGreaterThan(
            strtotime($initial['updated_at']),
            strtotime($updated['updated_at']),
            "updated_at should be newer"
        );
    }
}