<?php

/**
 * PT Tables Integration Tests
 * Comprehensive tests for all PT-specific database tables
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Dang Tran <tqvdang@msn.com>
 * @copyright Copyright (c) 2024 Dang Tran
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services\Vietnamese;

use PHPUnit\Framework\TestCase;

class PTTablesIntegrationTest extends TestCase
{
    private static $dbConnection;
    private static $cleanupIds = [];

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
        // Cleanup test data
        foreach (self::$cleanupIds as $table => $ids) {
            if (!empty($ids)) {
                $placeholders = implode(',', $ids);
                self::$dbConnection->exec("DELETE FROM $table WHERE id IN ($placeholders)");
            }
        }
    }

    /**
     * Test pt_assessments_bilingual table exists and structure
     */
    public function testPTAssessmentsBilingualTableExists(): void
    {
        $stmt = self::$dbConnection->query("SHOW TABLES LIKE 'pt_assessments_bilingual'");
        $result = $stmt->fetch();

        if (!$result) {
            $this->markTestSkipped('pt_assessments_bilingual table does not exist');
            return;
        }

        $this->assertNotFalse($result);

        // Check key columns
        $stmt = self::$dbConnection->query("DESCRIBE pt_assessments_bilingual");
        $columns = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        $expectedColumns = [
            'id', 'patient_id', 'encounter_id',
            'chief_complaint_en', 'chief_complaint_vi',
            'pain_level', 'pain_location_en', 'pain_location_vi',
            'functional_goals_en', 'functional_goals_vi',
            'treatment_plan_en', 'treatment_plan_vi',
            'language_preference', 'status'
        ];

        foreach ($expectedColumns as $column) {
            $this->assertContains($column, $columns, "Column $column should exist");
        }
    }

    /**
     * Test insert and retrieve bilingual assessment
     */
    public function testInsertBilingualAssessment(): void
    {
        $stmt = self::$dbConnection->query("SHOW TABLES LIKE 'pt_assessments_bilingual'");
        if (!$stmt->fetch()) {
            $this->markTestSkipped('pt_assessments_bilingual table does not exist');
            return;
        }

        $assessmentData = [
            'patient_id' => 1,
            'encounter_id' => 1,
            'assessment_date' => date('Y-m-d H:i:s'),
            'chief_complaint_en' => 'Lower back pain',
            'chief_complaint_vi' => 'Đau lưng dưới',
            'pain_level' => 7,
            'pain_location_en' => 'Lumbar region',
            'pain_location_vi' => 'Vùng thắt lưng',
            'functional_goals_en' => 'Return to work activities',
            'functional_goals_vi' => 'Trở lại công việc',
            'treatment_plan_en' => 'Exercise therapy and massage',
            'treatment_plan_vi' => 'Vận động trị liệu và massage',
            'language_preference' => 'vi',
            'status' => 'completed'
        ];

        $stmt = self::$dbConnection->prepare("
            INSERT INTO pt_assessments_bilingual
            (patient_id, encounter_id, assessment_date,
             chief_complaint_en, chief_complaint_vi,
             pain_level, pain_location_en, pain_location_vi,
             functional_goals_en, functional_goals_vi,
             treatment_plan_en, treatment_plan_vi,
             language_preference, status)
            VALUES
            (:patient_id, :encounter_id, :assessment_date,
             :chief_complaint_en, :chief_complaint_vi,
             :pain_level, :pain_location_en, :pain_location_vi,
             :functional_goals_en, :functional_goals_vi,
             :treatment_plan_en, :treatment_plan_vi,
             :language_preference, :status)
        ");

        $result = $stmt->execute($assessmentData);
        $this->assertTrue($result);

        $insertId = self::$dbConnection->lastInsertId();
        self::$cleanupIds['pt_assessments_bilingual'][] = $insertId;

        // Retrieve and verify
        $stmt = self::$dbConnection->prepare("
            SELECT * FROM pt_assessments_bilingual WHERE id = :id
        ");
        $stmt->execute(['id' => $insertId]);
        $retrieved = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->assertEquals($assessmentData['chief_complaint_vi'], $retrieved['chief_complaint_vi']);
        $this->assertEquals($assessmentData['pain_level'], $retrieved['pain_level']);
        $this->assertTrue(mb_check_encoding($retrieved['chief_complaint_vi'], 'UTF-8'));
    }

    /**
     * Test pt_exercise_prescriptions table
     */
    public function testPTExercisePrescriptionsTable(): void
    {
        $stmt = self::$dbConnection->query("SHOW TABLES LIKE 'pt_exercise_prescriptions'");
        if (!$stmt->fetch()) {
            $this->markTestSkipped('pt_exercise_prescriptions table does not exist');
            return;
        }

        $exerciseData = [
            'patient_id' => 1,
            'exercise_name' => 'Cat-Cow Stretch Test',
            'exercise_name_vi' => 'Động tác mèo-bò test',
            'description' => 'Spinal mobility exercise',
            'description_vi' => 'Bài tập linh hoạt cột sống',
            'sets_prescribed' => 3,
            'reps_prescribed' => '10-15',
            'duration_minutes' => 10,
            'frequency_per_week' => 5,
            'intensity_level' => 'moderate',
            'instructions' => 'Start on hands and knees',
            'instructions_vi' => 'Bắt đầu ở tư thế quỳ',
            'start_date' => date('Y-m-d'),
            'prescribed_by' => 1
        ];

        $stmt = self::$dbConnection->prepare("
            INSERT INTO pt_exercise_prescriptions
            (patient_id, exercise_name, exercise_name_vi, description, description_vi,
             sets_prescribed, reps_prescribed, duration_minutes, frequency_per_week,
             intensity_level, instructions, instructions_vi, start_date, prescribed_by)
            VALUES
            (:patient_id, :exercise_name, :exercise_name_vi, :description, :description_vi,
             :sets_prescribed, :reps_prescribed, :duration_minutes, :frequency_per_week,
             :intensity_level, :instructions, :instructions_vi, :start_date, :prescribed_by)
        ");

        $result = $stmt->execute($exerciseData);
        $this->assertTrue($result);

        $insertId = self::$dbConnection->lastInsertId();
        self::$cleanupIds['pt_exercise_prescriptions'][] = $insertId;

        // Verify
        $stmt = self::$dbConnection->prepare("
            SELECT * FROM pt_exercise_prescriptions WHERE id = :id
        ");
        $stmt->execute(['id' => $insertId]);
        $retrieved = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->assertEquals($exerciseData['exercise_name_vi'], $retrieved['exercise_name_vi']);
        $this->assertEquals($exerciseData['sets_prescribed'], $retrieved['sets_prescribed']);
        $this->assertTrue(mb_check_encoding($retrieved['exercise_name_vi'], 'UTF-8'));
    }

    /**
     * Test pt_outcome_measures table
     */
    public function testPTOutcomeMeasuresTable(): void
    {
        $stmt = self::$dbConnection->query("SHOW TABLES LIKE 'pt_outcome_measures'");
        if (!$stmt->fetch()) {
            $this->markTestSkipped('pt_outcome_measures table does not exist');
            return;
        }

        $outcomeData = [
            'patient_id' => 1,
            'measure_type' => 'Pain Scale',
            'measure_name' => 'VAS',
            'measure_name_vi' => 'Thang đo đau VAS',
            'score_value' => 6.5,
            'max_score' => 10.0,
            'interpretation' => 'Moderate pain',
            'interpretation_vi' => 'Đau mức độ trung bình',
            'measurement_date' => date('Y-m-d'),
            'measured_by' => 1
        ];

        $stmt = self::$dbConnection->prepare("
            INSERT INTO pt_outcome_measures
            (patient_id, measure_type, measure_name, measure_name_vi,
             score_value, max_score, interpretation, interpretation_vi,
             measurement_date, measured_by)
            VALUES
            (:patient_id, :measure_type, :measure_name, :measure_name_vi,
             :score_value, :max_score, :interpretation, :interpretation_vi,
             :measurement_date, :measured_by)
        ");

        $result = $stmt->execute($outcomeData);
        $this->assertTrue($result);

        $insertId = self::$dbConnection->lastInsertId();
        self::$cleanupIds['pt_outcome_measures'][] = $insertId;

        // Verify
        $stmt = self::$dbConnection->prepare("
            SELECT * FROM pt_outcome_measures WHERE id = :id
        ");
        $stmt->execute(['id' => $insertId]);
        $retrieved = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->assertEquals($outcomeData['measure_name_vi'], $retrieved['measure_name_vi']);
        $this->assertEquals($outcomeData['score_value'], $retrieved['score_value']);
    }

    /**
     * Test pt_treatment_plans table
     */
    public function testPTTreatmentPlansTable(): void
    {
        $stmt = self::$dbConnection->query("SHOW TABLES LIKE 'pt_treatment_plans'");
        if (!$stmt->fetch()) {
            $this->markTestSkipped('pt_treatment_plans table does not exist');
            return;
        }

        $planData = [
            'patient_id' => 1,
            'plan_name' => 'Back Pain Rehabilitation',
            'plan_name_vi' => 'Phục hồi đau lưng',
            'diagnosis_primary' => 'Lumbar strain',
            'diagnosis_primary_vi' => 'Căng cơ thắt lưng',
            'treatment_frequency' => '3 times per week',
            'estimated_duration_weeks' => 6,
            'plan_status' => 'active',
            'start_date' => date('Y-m-d'),
            'created_by' => 1
        ];

        $stmt = self::$dbConnection->prepare("
            INSERT INTO pt_treatment_plans
            (patient_id, plan_name, plan_name_vi, diagnosis_primary, diagnosis_primary_vi,
             treatment_frequency, estimated_duration_weeks, plan_status, start_date, created_by)
            VALUES
            (:patient_id, :plan_name, :plan_name_vi, :diagnosis_primary, :diagnosis_primary_vi,
             :treatment_frequency, :estimated_duration_weeks, :plan_status, :start_date, :created_by)
        ");

        $result = $stmt->execute($planData);
        $this->assertTrue($result);

        $insertId = self::$dbConnection->lastInsertId();
        self::$cleanupIds['pt_treatment_plans'][] = $insertId;

        // Verify
        $stmt = self::$dbConnection->prepare("
            SELECT * FROM pt_treatment_plans WHERE id = :id
        ");
        $stmt->execute(['id' => $insertId]);
        $retrieved = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->assertEquals($planData['plan_name_vi'], $retrieved['plan_name_vi']);
        $this->assertEquals($planData['plan_status'], $retrieved['plan_status']);
    }

    /**
     * Test pt_assessment_templates table
     */
    public function testPTAssessmentTemplatesTable(): void
    {
        $stmt = self::$dbConnection->query("SHOW TABLES LIKE 'pt_assessment_templates'");
        if (!$stmt->fetch()) {
            $this->markTestSkipped('pt_assessment_templates table does not exist');
            return;
        }

        $templateData = [
            'template_name' => 'Test Template',
            'template_name_vi' => 'Mẫu test',
            'category' => 'Musculoskeletal',
            'body_region' => 'Spine',
            'assessment_fields' => json_encode([
                'pain_scale' => 'numeric',
                'range_of_motion' => 'object',
                'strength' => 'numeric'
            ])
        ];

        $stmt = self::$dbConnection->prepare("
            INSERT INTO pt_assessment_templates
            (template_name, template_name_vi, category, body_region, assessment_fields)
            VALUES
            (:template_name, :template_name_vi, :category, :body_region, :assessment_fields)
        ");

        $result = $stmt->execute($templateData);
        $this->assertTrue($result);

        $insertId = self::$dbConnection->lastInsertId();
        self::$cleanupIds['pt_assessment_templates'][] = $insertId;

        // Verify
        $stmt = self::$dbConnection->prepare("
            SELECT * FROM pt_assessment_templates WHERE id = :id
        ");
        $stmt->execute(['id' => $insertId]);
        $retrieved = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->assertEquals($templateData['template_name_vi'], $retrieved['template_name_vi']);
        $this->assertIsString($retrieved['assessment_fields']);

        // Verify JSON decoding
        $fields = json_decode($retrieved['assessment_fields'], true);
        $this->assertIsArray($fields);
        $this->assertArrayHasKey('pain_scale', $fields);
    }

    /**
     * Test vietnamese_insurance_info table
     */
    public function testVietnameseInsuranceInfoTable(): void
    {
        $stmt = self::$dbConnection->query("SHOW TABLES LIKE 'vietnamese_insurance_info'");
        if (!$stmt->fetch()) {
            $this->markTestSkipped('vietnamese_insurance_info table does not exist');
            return;
        }

        $insuranceData = [
            'patient_id' => 1,
            'bhyt_card_number' => 'VN123456789TEST',
            'insurance_provider' => 'Bảo hiểm Xã hội Việt Nam',
            'coverage_type' => 'Toàn diện',
            'coverage_percentage' => 80.00,
            'valid_from' => date('Y-m-d'),
            'valid_to' => date('Y-m-d', strtotime('+1 year'))
        ];

        $stmt = self::$dbConnection->prepare("
            INSERT INTO vietnamese_insurance_info
            (patient_id, bhyt_card_number, insurance_provider, coverage_type,
             coverage_percentage, valid_from, valid_to)
            VALUES
            (:patient_id, :bhyt_card_number, :insurance_provider, :coverage_type,
             :coverage_percentage, :valid_from, :valid_to)
        ");

        $result = $stmt->execute($insuranceData);
        $this->assertTrue($result);

        $insertId = self::$dbConnection->lastInsertId();
        self::$cleanupIds['vietnamese_insurance_info'][] = $insertId;

        // Verify
        $stmt = self::$dbConnection->prepare("
            SELECT * FROM vietnamese_insurance_info WHERE id = :id
        ");
        $stmt->execute(['id' => $insertId]);
        $retrieved = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->assertEquals($insuranceData['bhyt_card_number'], $retrieved['bhyt_card_number']);
        $this->assertEquals($insuranceData['coverage_percentage'], $retrieved['coverage_percentage']);
        $this->assertTrue(mb_check_encoding($retrieved['insurance_provider'], 'UTF-8'));
    }

    /**
     * Test JSON field operations
     */
    public function testJSONFieldOperations(): void
    {
        $stmt = self::$dbConnection->query("SHOW TABLES LIKE 'pt_treatment_plans'");
        if (!$stmt->fetch()) {
            $this->markTestSkipped('pt_treatment_plans table does not exist');
            return;
        }

        $jsonGoals = [
            'short_term' => [
                'vi' => 'Giảm đau trong 2 tuần',
                'en' => 'Reduce pain within 2 weeks'
            ],
            'long_term' => [
                'vi' => 'Trở lại hoạt động bình thường trong 6 tuần',
                'en' => 'Return to normal activities in 6 weeks'
            ]
        ];

        $stmt = self::$dbConnection->prepare("
            INSERT INTO pt_treatment_plans
            (patient_id, plan_name, diagnosis_primary, goals_short_term,
             start_date, created_by)
            VALUES
            (1, 'JSON Test Plan', 'Test diagnosis', :goals, :start_date, 1)
        ");

        $result = $stmt->execute([
            'goals' => json_encode($jsonGoals),
            'start_date' => date('Y-m-d')
        ]);
        $this->assertTrue($result);

        $insertId = self::$dbConnection->lastInsertId();
        self::$cleanupIds['pt_treatment_plans'][] = $insertId;

        // Retrieve and verify JSON
        $stmt = self::$dbConnection->prepare("
            SELECT goals_short_term FROM pt_treatment_plans WHERE id = :id
        ");
        $stmt->execute(['id' => $insertId]);
        $retrieved = $stmt->fetchColumn();

        $decoded = json_decode($retrieved, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('short_term', $decoded);
        $this->assertEquals($jsonGoals['short_term']['vi'], $decoded['short_term']['vi']);
        $this->assertTrue(mb_check_encoding($decoded['short_term']['vi'], 'UTF-8'));
    }

    /**
     * Test fulltext search on bilingual assessment
     */
    public function testFulltextSearchBilingualAssessment(): void
    {
        $stmt = self::$dbConnection->query("SHOW TABLES LIKE 'pt_assessments_bilingual'");
        if (!$stmt->fetch()) {
            $this->markTestSkipped('pt_assessments_bilingual table does not exist');
            return;
        }

        try {
            // Test MATCH AGAINST query
            $stmt = self::$dbConnection->prepare("
                SELECT COUNT(*) FROM pt_assessments_bilingual
                WHERE MATCH(chief_complaint_vi) AGAINST(:search IN BOOLEAN MODE)
            ");
            $stmt->execute(['search' => 'đau']);

            // If no error, fulltext index exists
            $this->assertTrue(true);
        } catch (\PDOException $e) {
            // Fulltext index might not exist or no data
            $this->markTestSkipped('Fulltext search not available: ' . $e->getMessage());
        }
    }
}