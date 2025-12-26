<?php

/**
 * ServicePerformanceTest - Performance benchmarks for Vietnamese PT Services
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code (AI Assistant)
 * @copyright Copyright (c) 2025 OpenEMR <dev@open-emr.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 * AI-GENERATED CODE - START
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Performance\VietnamesePT;

use PHPUnit\Framework\TestCase;
use OpenEMR\Services\VietnamesePT\PTAssessmentService;
use OpenEMR\Services\VietnamesePT\PTExercisePrescriptionService;
use OpenEMR\Services\VietnamesePT\PTTreatmentPlanService;
use OpenEMR\Services\VietnamesePT\PTOutcomeMeasuresService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;

#[Group('vietnamese-pt')]
#[Group('performance')]
class ServicePerformanceTest extends TestCase
{
    private const THRESHOLD_SINGLE_RECORD = 50; // milliseconds
    private const THRESHOLD_LIST_10_RECORDS = 100; // milliseconds
    private const THRESHOLD_SEARCH = 200; // milliseconds
    private const THRESHOLD_COMPLEX_QUERY = 500; // milliseconds

    private PTAssessmentService $assessmentService;
    private PTExercisePrescriptionService $exerciseService;
    private PTTreatmentPlanService $planService;
    private PTOutcomeMeasuresService $outcomeService;

    protected function setUp(): void
    {
        $this->assessmentService = new PTAssessmentService();
        $this->exerciseService = new PTExercisePrescriptionService();
        $this->planService = new PTTreatmentPlanService();
        $this->outcomeService = new PTOutcomeMeasuresService();
    }

    #[Test]
    public function testAssessmentCreationPerformance(): void
    {
        $assessmentData = [
            'patient_id' => 1,
            'encounter_id' => 1,
            'chief_complaint_en' => 'Test complaint',
            'chief_complaint_vi' => 'Triệu chứng thử nghiệm',
            'pain_level' => 5
        ];

        $startTime = microtime(true);
        $result = $this->assessmentService->insert($assessmentData);
        $endTime = microtime(true);

        $duration = ($endTime - $startTime) * 1000; // Convert to milliseconds

        $this->assertLessThan(
            self::THRESHOLD_SINGLE_RECORD,
            $duration,
            "Assessment creation took {$duration}ms, should be < " . self::THRESHOLD_SINGLE_RECORD . "ms"
        );

        // Cleanup
        if ($result->hasData()) {
            $this->assessmentService->delete($result->getData()['id']);
        }
    }

    #[Test]
    public function testPatientHistoryRetrievalPerformance(): void
    {
        // Create 10 test assessments
        $createdIds = [];
        for ($i = 0; $i < 10; $i++) {
            $assessmentData = [
                'patient_id' => 1,
                'chief_complaint_en' => "Test complaint $i",
                'chief_complaint_vi' => "Triệu chứng $i",
                'pain_level' => $i
            ];
            $result = $this->assessmentService->insert($assessmentData);
            if ($result->hasData()) {
                $createdIds[] = $result->getData()['id'];
            }
        }

        // Measure retrieval time
        $startTime = microtime(true);
        $result = $this->assessmentService->getAll(['patient_id' => 1]);
        $endTime = microtime(true);

        $duration = ($endTime - $startTime) * 1000;

        $this->assertLessThan(
            self::THRESHOLD_LIST_10_RECORDS,
            $duration,
            "Retrieving 10 records took {$duration}ms, should be < " . self::THRESHOLD_LIST_10_RECORDS . "ms"
        );

        // Cleanup
        foreach ($createdIds as $id) {
            $this->assessmentService->delete($id);
        }
    }

    #[Test]
    public function testVietnameseTextSearchPerformance(): void
    {
        // Create records with Vietnamese text
        $createdIds = [];
        $vietnameseTexts = [
            'Đau lưng',
            'Đau khớp gối',
            'Đau vai',
            'Hạn chế vận động',
            'Sưng phù'
        ];

        foreach ($vietnameseTexts as $text) {
            $assessmentData = [
                'patient_id' => 1,
                'chief_complaint_vi' => $text,
                'chief_complaint_en' => 'Test',
                'pain_level' => 5
            ];
            $result = $this->assessmentService->insert($assessmentData);
            if ($result->hasData()) {
                $createdIds[] = $result->getData()['id'];
            }
        }

        // Measure search time
        $startTime = microtime(true);
        $result = $this->assessmentService->search(['chief_complaint_vi' => 'Đau']);
        $endTime = microtime(true);

        $duration = ($endTime - $startTime) * 1000;

        $this->assertLessThan(
            self::THRESHOLD_SEARCH,
            $duration,
            "Vietnamese text search took {$duration}ms, should be < " . self::THRESHOLD_SEARCH . "ms"
        );

        // Cleanup
        foreach ($createdIds as $id) {
            $this->assessmentService->delete($id);
        }
    }

    #[Test]
    public function testBulkExercisePrescriptionPerformance(): void
    {
        $createdIds = [];
        $startTime = microtime(true);

        for ($i = 0; $i < 20; $i++) {
            $exerciseData = [
                'patient_id' => 1,
                'exercise_name_en' => "Exercise $i",
                'exercise_name_vi' => "Bài tập $i",
                'sets' => 3,
                'reps' => 10
            ];
            $result = $this->exerciseService->insert($exerciseData);
            if ($result->hasData()) {
                $createdIds[] = $result->getData()['id'];
            }
        }

        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000;

        $avgPerRecord = $duration / 20;

        $this->assertLessThan(
            self::THRESHOLD_SINGLE_RECORD,
            $avgPerRecord,
            "Average bulk creation time per record: {$avgPerRecord}ms, should be < " . self::THRESHOLD_SINGLE_RECORD . "ms"
        );

        // Cleanup
        foreach ($createdIds as $id) {
            $this->exerciseService->delete($id);
        }
    }

    #[Test]
    public function testComplexQueryPerformance(): void
    {
        // Create test data
        $createdIds = [];
        for ($i = 0; $i < 15; $i++) {
            $planData = [
                'patient_id' => 1,
                'diagnosis_en' => "Diagnosis $i",
                'diagnosis_vi' => "Chẩn đoán $i",
                'status' => ($i % 3 === 0) ? 'Active' : 'Completed',
                'start_date' => date('Y-m-d', strtotime("-$i days"))
            ];
            $result = $this->planService->insert($planData);
            if ($result->hasData()) {
                $createdIds[] = $result->getData()['id'];
            }
        }

        // Measure complex query with multiple filters
        $startTime = microtime(true);
        $result = $this->planService->getAll([
            'patient_id' => 1,
            'status' => 'Active',
            'date_from' => date('Y-m-d', strtotime('-30 days'))
        ]);
        $endTime = microtime(true);

        $duration = ($endTime - $startTime) * 1000;

        $this->assertLessThan(
            self::THRESHOLD_COMPLEX_QUERY,
            $duration,
            "Complex query took {$duration}ms, should be < " . self::THRESHOLD_COMPLEX_QUERY . "ms"
        );

        // Cleanup
        foreach ($createdIds as $id) {
            $this->planService->delete($id);
        }
    }

    #[Test]
    public function testOutcomeMeasureCalculationPerformance(): void
    {
        $createdIds = [];
        $startTime = microtime(true);

        for ($i = 0; $i < 10; $i++) {
            $outcomeData = [
                'patient_id' => 1,
                'measure_type' => 'ROM',
                'baseline_value' => 45,
                'current_value' => 60 + $i,
                'target_value' => 120
            ];
            $result = $this->outcomeService->insert($outcomeData);
            if ($result->hasData()) {
                $createdIds[] = $result->getData()['id'];
            }
        }

        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000;

        $avgPerRecord = $duration / 10;

        $this->assertLessThan(
            self::THRESHOLD_SINGLE_RECORD,
            $avgPerRecord,
            "Outcome measure with calculation: {$avgPerRecord}ms/record, should be < " . self::THRESHOLD_SINGLE_RECORD . "ms"
        );

        // Cleanup
        foreach ($createdIds as $id) {
            $this->outcomeService->delete($id);
        }
    }

    #[Test]
    public function testMemoryUsageDuringOperations(): void
    {
        $initialMemory = memory_get_usage();

        // Create 50 records
        $createdIds = [];
        for ($i = 0; $i < 50; $i++) {
            $assessmentData = [
                'patient_id' => 1,
                'chief_complaint_en' => str_repeat("Test $i ", 100),
                'chief_complaint_vi' => str_repeat("Thử nghiệm $i ", 100),
                'pain_level' => $i % 10
            ];
            $result = $this->assessmentService->insert($assessmentData);
            if ($result->hasData()) {
                $createdIds[] = $result->getData()['id'];
            }
        }

        $peakMemory = memory_get_peak_usage();
        $memoryIncrease = ($peakMemory - $initialMemory) / 1024 / 1024; // Convert to MB

        $this->assertLessThan(
            50,
            $memoryIncrease,
            "Memory increase during 50 record creation: {$memoryIncrease}MB, should be < 50MB"
        );

        // Cleanup
        foreach ($createdIds as $id) {
            $this->assessmentService->delete($id);
        }
    }

    #[Test]
    public function testDatabaseConnectionPooling(): void
    {
        $iterations = 100;
        $startTime = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $this->assessmentService->getOne(1);
        }

        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000;
        $avgPerQuery = $duration / $iterations;

        $this->assertLessThan(
            10,
            $avgPerQuery,
            "Average query time with connection pooling: {$avgPerQuery}ms, should be < 10ms"
        );
    }
}

/**
 * AI-GENERATED CODE - END
 */
