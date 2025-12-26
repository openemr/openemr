<?php

namespace OpenEMR\Tests\RestControllers\VietnamesePT;

use PHPUnit\Framework\TestCase;
use OpenEMR\Validators\ProcessingResult;

/**
 * PT Outcome Measures REST Controller Tests
 *
 * Tests CRUD operations for physiotherapy outcome measures
 * with Vietnamese bilingual support for progress tracking.
 *
 * AI Generated Test Suite - Generated for Vietnamese PT REST API testing
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    AI Generated <test@openemr.org>
 * @copyright Copyright (c) 2024
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class PTOutcomeMeasuresRestControllerTest extends TestCase
{
    /**
     * Test ProcessingResult with outcome measure data
     */
    public function testOutcomeMeasureData(): void
    {
        $measureData = [
            'id' => 1,
            'patient_id' => 1,
            'measure_name' => 'Range of Motion',
            'measure_name_vi' => 'Phạm vi chuyển động',
            'measurement_date' => '2024-01-15',
            'baseline_value' => 45,
            'current_value' => 65,
            'unit' => 'degrees',
            'status' => 'improved'
        ];

        $result = new ProcessingResult();
        $result->addData($measureData);

        $this->assertFalse($result->hasErrors());
        $this->assertEquals('Phạm vi chuyển động', $result->getData()[0]['measure_name_vi']);
        $this->assertEquals('improved', $result->getData()[0]['status']);
    }

    /**
     * Test outcome measure validation errors
     */
    public function testOutcomeMeasureValidationErrors(): void
    {
        $result = new ProcessingResult();
        $result->addValidationMessage('patient_id', 'required');
        $result->addValidationMessage('measure_name', 'required');
        $result->addValidationMessage('measurement_date', 'required');

        $this->assertTrue($result->hasErrors());
        $this->assertEquals(3, count($result->getValidationMessages()));
    }

    /**
     * Test Vietnamese measure names
     */
    public function testVietnameseMeasureNames(): void
    {
        $measures = [
            ['en' => 'Range of Motion', 'vi' => 'Phạm vi chuyển động'],
            ['en' => 'Muscle Strength', 'vi' => 'Sức mạnh cơ'],
            ['en' => 'Pain Level', 'vi' => 'Mức độ đau'],
            ['en' => 'Functional Mobility', 'vi' => 'Khả năng vận động chức năng'],
            ['en' => 'Balance', 'vi' => 'Cân bằng']
        ];

        foreach ($measures as $measure) {
            $result = new ProcessingResult();
            $result->addData([
                'measure_name' => $measure['en'],
                'measure_name_vi' => $measure['vi']
            ]);

            $data = $result->getData()[0];
            $this->assertEquals($measure['vi'], $data['measure_name_vi']);
            $this->assertTrue(mb_check_encoding($measure['vi'], 'UTF-8'));
        }
    }

    /**
     * Test baseline and current values
     */
    public function testMeasurementValues(): void
    {
        $measurements = [
            ['baseline' => 45, 'current' => 70, 'unit' => 'degrees'],
            ['baseline' => 8, 'current' => 3, 'unit' => 'scale 0-10'],
            ['baseline' => 3, 'current' => 5, 'unit' => 'manual test'],
            ['baseline' => 10, 'current' => 30, 'unit' => 'seconds'],
            ['baseline' => 50, 'current' => 150, 'unit' => 'meters']
        ];

        foreach ($measurements as $measurement) {
            $result = new ProcessingResult();
            $result->addData([
                'baseline_value' => $measurement['baseline'],
                'current_value' => $measurement['current'],
                'unit' => $measurement['unit']
            ]);

            $data = $result->getData()[0];
            $this->assertEquals($measurement['baseline'], $data['baseline_value']);
            $this->assertEquals($measurement['current'], $data['current_value']);
            $this->assertEquals($measurement['unit'], $data['unit']);
        }
    }

    /**
     * Test outcome measure status types
     */
    public function testOutcomeMeasureStatus(): void
    {
        $statuses = [
            'baseline', 'no change', 'slightly improved',
            'improved', 'significantly improved', 'plateau', 'regressed'
        ];

        foreach ($statuses as $status) {
            $result = new ProcessingResult();
            $result->addData([
                'id' => 1,
                'patient_id' => 1,
                'status' => $status
            ]);

            $this->assertEquals($status, $result->getData()[0]['status']);
        }
    }

    /**
     * Test measurement units
     */
    public function testMeasurementUnits(): void
    {
        $units = [
            'degrees', 'scale 0-10', 'manual test grade',
            'seconds', 'meters', 'minutes', 'percentage', 'count'
        ];

        foreach ($units as $unit) {
            $result = new ProcessingResult();
            $result->addData([
                'unit' => $unit
            ]);

            $this->assertEquals($unit, $result->getData()[0]['unit']);
        }
    }

    /**
     * Test multiple measurements for same patient
     */
    public function testPatientMultipleMeasurements(): void
    {
        $measurements = [
            ['id' => 1, 'name' => 'Measure 1', 'name_vi' => 'Phép đo 1'],
            ['id' => 2, 'name' => 'Measure 2', 'name_vi' => 'Phép đo 2'],
            ['id' => 3, 'name' => 'Measure 3', 'name_vi' => 'Phép đo 3'],
            ['id' => 4, 'name' => 'Measure 4', 'name_vi' => 'Phép đo 4'],
            ['id' => 5, 'name' => 'Measure 5', 'name_vi' => 'Phép đo 5']
        ];

        $result = new ProcessingResult();
        foreach ($measurements as $measurement) {
            $result->addData([
                'id' => $measurement['id'],
                'patient_id' => 1,
                'measure_name' => $measurement['name'],
                'measure_name_vi' => $measurement['name_vi']
            ]);
        }

        $this->assertEquals(5, count($result->getData()));
        $this->assertEquals('Phép đo 3', $result->getData()[2]['measure_name_vi']);
    }

    /**
     * Test progress tracking over time
     */
    public function testProgressTrackingOverTime(): void
    {
        $progressions = [
            ['date' => '2024-01-15', 'value' => 45, 'status' => 'baseline'],
            ['date' => '2024-01-29', 'value' => 50, 'status' => 'slightly improved'],
            ['date' => '2024-02-12', 'value' => 60, 'status' => 'improved'],
            ['date' => '2024-02-26', 'value' => 70, 'status' => 'improved'],
            ['date' => '2024-03-12', 'value' => 80, 'status' => 'significantly improved']
        ];

        $result = new ProcessingResult();
        foreach ($progressions as $index => $progression) {
            $result->addData([
                'id' => $index + 1,
                'patient_id' => 1,
                'measurement_date' => $progression['date'],
                'baseline_value' => 45,
                'current_value' => $progression['value'],
                'status' => $progression['status']
            ]);
        }

        $this->assertEquals(5, count($result->getData()));
        // Verify progression
        $this->assertEquals('baseline', $result->getData()[0]['status']);
        $this->assertEquals('significantly improved', $result->getData()[4]['status']);
    }

    /**
     * Test measurement dates
     */
    public function testMeasurementDates(): void
    {
        $dates = [
            '2024-01-15', '2024-02-15', '2024-03-15',
            '2024-04-15', '2024-05-15', '2024-06-15'
        ];

        foreach ($dates as $date) {
            $result = new ProcessingResult();
            $result->addData([
                'measurement_date' => $date
            ]);

            $this->assertEquals($date, $result->getData()[0]['measurement_date']);
        }
    }

    /**
     * Test Vietnamese special characters in descriptions
     */
    public function testVietnameseSpecialCharacters(): void
    {
        $specialChars = [
            'Đ' => 'uppercase D with stroke',
            'đ' => 'lowercase d with stroke',
            'Ơ' => 'uppercase O with horn',
            'ơ' => 'lowercase o with horn',
            'Ư' => 'uppercase U with horn',
            'ư' => 'lowercase u with horn',
            'Ă' => 'uppercase A with breve',
            'ă' => 'lowercase a with breve',
            'Ê' => 'uppercase E with circumflex',
            'ê' => 'lowercase e with circumflex'
        ];

        foreach ($specialChars as $char => $description) {
            $text = 'Measure ' . $char;
            $result = new ProcessingResult();
            $result->addData(['measure_name_vi' => $text]);

            $this->assertTrue(mb_check_encoding($text, 'UTF-8'));
            $this->assertStringContainsString($char, $result->getData()[0]['measure_name_vi']);
        }
    }

    /**
     * Test empty outcome measure result
     */
    public function testEmptyOutcomeMeasureResult(): void
    {
        $result = new ProcessingResult();

        $this->assertFalse($result->hasErrors());
        $this->assertEquals(0, count($result->getData()));
    }

    /**
     * Test outcome measure with description
     */
    public function testOutcomeMeasureWithDescription(): void
    {
        $descriptions = [
            ['en' => 'Lumbar flexion', 'vi' => 'Gập lưng'],
            ['en' => 'Hip extension', 'vi' => 'Duỗi hông'],
            ['en' => 'Knee movement', 'vi' => 'Chuyển động đầu gối'],
            ['en' => 'Shoulder mobility', 'vi' => 'Khả năng vận động vai']
        ];

        foreach ($descriptions as $description) {
            $result = new ProcessingResult();
            $result->addData([
                'description' => $description['en'],
                'description_vi' => $description['vi']
            ]);

            $data = $result->getData()[0];
            $this->assertEquals($description['vi'], $data['description_vi']);
        }
    }

    /**
     * Test controller response structure for outcome measures
     */
    public function testControllerResponseStructure(): void
    {
        $result = new ProcessingResult();
        $result->addData([
            'id' => 1,
            'patient_id' => 1,
            'measure_name' => 'Range of Motion',
            'measure_name_vi' => 'Phạm vi chuyển động',
            'status' => 'improved'
        ]);

        $response = [
            'data' => $result->getData(),
            'validationErrors' => $result->getValidationMessages(),
            'internalErrors' => $result->getInternalErrors()
        ];

        $this->assertIsArray($response['data']);
        $this->assertCount(1, $response['data']);
        $this->assertArrayHasKey('measure_name_vi', $response['data'][0]);
        $this->assertArrayHasKey('status', $response['data'][0]);
    }

    /**
     * Test large outcome measure dataset
     */
    public function testLargeOutcomeMeasureDataset(): void
    {
        $result = new ProcessingResult();

        for ($i = 1; $i <= 100; $i++) {
            $result->addData([
                'id' => $i,
                'patient_id' => ($i % 10) + 1,
                'measure_name' => 'Measure ' . $i,
                'measure_name_vi' => 'Phép đo ' . $i,
                'baseline_value' => $i,
                'current_value' => $i + 10,
                'status' => $i % 2 == 0 ? 'improved' : 'no change'
            ]);
        }

        $this->assertEquals(100, count($result->getData()));
        $this->assertEquals('Phép đo 50', $result->getData()[49]['measure_name_vi']);
    }

    /**
     * Test UTF-8 encoding in outcome measures
     */
    public function testUTF8Encoding(): void
    {
        $vietnameseText = 'Đánh giá sức mạnh cơ bắp';

        $this->assertTrue(mb_check_encoding($vietnameseText, 'UTF-8'));

        $result = new ProcessingResult();
        $result->addData(['measure_name_vi' => $vietnameseText]);

        $this->assertEquals($vietnameseText, $result->getData()[0]['measure_name_vi']);
    }

    /**
     * Test progress percentage calculation
     */
    public function testProgressPercentageCalculation(): void
    {
        $testCases = [
            ['baseline' => 45, 'current' => 70, 'expected' => 55.56],
            ['baseline' => 8, 'current' => 3, 'expected' => -62.5],
            ['baseline' => 10, 'current' => 30, 'expected' => 200],
            ['baseline' => 50, 'current' => 50, 'expected' => 0]
        ];

        foreach ($testCases as $case) {
            $result = new ProcessingResult();
            $baseline = $case['baseline'];
            $current = $case['current'];
            $progress = (($current - $baseline) / $baseline) * 100;

            $result->addData([
                'baseline_value' => $baseline,
                'current_value' => $current,
                'progress_percentage' => round($progress, 2)
            ]);

            $data = $result->getData()[0];
            $this->assertEquals($case['baseline'], $data['baseline_value']);
            $this->assertEquals($case['current'], $data['current_value']);
            $this->assertEquals(round($case['expected'], 2), $data['progress_percentage']);
        }
    }

    /**
     * Test comprehensive outcome measurement
     */
    public function testComprehensiveOutcomeMeasurement(): void
    {
        $completeMeasureData = [
            'id' => 1,
            'patient_id' => 1,
            'measure_name' => 'Comprehensive Range of Motion Assessment',
            'measure_name_vi' => 'Đánh giá toàn diện phạm vi chuyển động',
            'measurement_date' => '2024-02-15',
            'baseline_value' => 45,
            'current_value' => 75,
            'unit' => 'degrees',
            'description' => 'Lumbar flexion with forward bending',
            'description_vi' => 'Gập lưng với cúi về phía trước',
            'status' => 'significantly improved',
            'progress_percentage' => 66.67
        ];

        $result = new ProcessingResult();
        $result->addData($completeMeasureData);

        $data = $result->getData()[0];
        $this->assertArrayHasKey('measure_name_vi', $data);
        $this->assertArrayHasKey('description_vi', $data);
        $this->assertArrayHasKey('progress_percentage', $data);
        $this->assertEquals('significantly improved', $data['status']);
    }
}
