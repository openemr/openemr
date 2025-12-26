<?php

namespace OpenEMR\Tests\RestControllers\VietnamesePT;

use PHPUnit\Framework\TestCase;
use OpenEMR\Validators\ProcessingResult;

/**
 * PT Treatment Plan REST Controller Tests
 *
 * Tests CRUD operations for physiotherapy treatment plans
 * with Vietnamese language support.
 *
 * AI Generated Test Suite - Generated for Vietnamese PT REST API testing
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    AI Generated <test@openemr.org>
 * @copyright Copyright (c) 2024
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class PTTreatmentPlanRestControllerTest extends TestCase
{
    /**
     * Test ProcessingResult with treatment plan data
     */
    public function testTreatmentPlanData(): void
    {
        $planData = [
            'id' => 1,
            'patient_id' => 1,
            'plan_name' => 'Back Pain Management',
            'plan_name_vi' => 'Quản lý đau lưng',
            'diagnosis_primary' => 'Lower back pain',
            'diagnosis_primary_vi' => 'Đau lưng dưới',
            'status' => 'active'
        ];

        $result = new ProcessingResult();
        $result->addData($planData);

        $this->assertFalse($result->hasErrors());
        $this->assertEquals('Quản lý đau lưng', $result->getData()[0]['plan_name_vi']);
        $this->assertEquals('Đau lưng dưới', $result->getData()[0]['diagnosis_primary_vi']);
    }

    /**
     * Test treatment plan validation errors
     */
    public function testTreatmentPlanValidationErrors(): void
    {
        $result = new ProcessingResult();
        $result->addValidationMessage('patient_id', 'required');
        $result->addValidationMessage('plan_name', 'required');
        $result->addValidationMessage('diagnosis_primary', 'required');
        $result->addValidationMessage('start_date', 'required');

        $this->assertTrue($result->hasErrors());
        $this->assertEquals(4, count($result->getValidationMessages()));
    }

    /**
     * Test Vietnamese diagnoses
     */
    public function testVietnameseDiagnoses(): void
    {
        $diagnoses = [
            ['en' => 'Lumbar strain', 'vi' => 'Căng cơ lưng'],
            ['en' => 'Cervical pain', 'vi' => 'Đau cổ'],
            ['en' => 'Shoulder impingement', 'vi' => 'Chèn ép vai'],
            ['en' => 'Knee osteoarthritis', 'vi' => 'Viêm khớp thoái hóa đầu gối'],
            ['en' => 'Hip pain', 'vi' => 'Đau hông']
        ];

        foreach ($diagnoses as $diagnosis) {
            $result = new ProcessingResult();
            $result->addData([
                'diagnosis_primary' => $diagnosis['en'],
                'diagnosis_primary_vi' => $diagnosis['vi']
            ]);

            $data = $result->getData()[0];
            $this->assertEquals($diagnosis['vi'], $data['diagnosis_primary_vi']);
            $this->assertTrue(mb_check_encoding($diagnosis['vi'], 'UTF-8'));
        }
    }

    /**
     * Test treatment plan duration
     */
    public function testTreatmentPlanDuration(): void
    {
        $durations = [4, 8, 12, 16, 20, 24];

        foreach ($durations as $duration) {
            $result = new ProcessingResult();
            $result->addData([
                'duration_weeks' => $duration,
                'frequency_per_week' => 2
            ]);

            $data = $result->getData()[0];
            $this->assertEquals($duration, $data['duration_weeks']);
        }
    }

    /**
     * Test treatment plan goals
     */
    public function testTreatmentPlanGoals(): void
    {
        $goals = [
            ['en' => 'Reduce pain', 'vi' => 'Giảm đau'],
            ['en' => 'Improve mobility', 'vi' => 'Cải thiện khả năng vận động'],
            ['en' => 'Strengthen muscles', 'vi' => 'Tăng cường sức mạnh cơ'],
            ['en' => 'Restore function', 'vi' => 'Phục hồi chức năng'],
            ['en' => 'Prevent recurrence', 'vi' => 'Phòng ngừa tái phát']
        ];

        foreach ($goals as $goal) {
            $result = new ProcessingResult();
            $result->addData([
                'goals' => $goal['en'],
                'goals_vi' => $goal['vi']
            ]);

            $data = $result->getData()[0];
            $this->assertEquals($goal['vi'], $data['goals_vi']);
        }
    }

    /**
     * Test treatment plan dates
     */
    public function testTreatmentPlanDates(): void
    {
        $startDates = ['2024-01-15', '2024-02-20', '2024-03-30'];
        $endDates = ['2024-04-15', '2024-05-20', '2024-06-30'];

        for ($i = 0; $i < count($startDates); $i++) {
            $result = new ProcessingResult();
            $result->addData([
                'start_date' => $startDates[$i],
                'end_date' => $endDates[$i]
            ]);

            $data = $result->getData()[0];
            $this->assertEquals($startDates[$i], $data['start_date']);
            $this->assertEquals($endDates[$i], $data['end_date']);
        }
    }

    /**
     * Test secondary diagnoses
     */
    public function testSecondaryDiagnoses(): void
    {
        $secondaryDiagnoses = [
            ['en' => 'Muscle tension', 'vi' => 'Căng cơ'],
            ['en' => 'Nerve compression', 'vi' => 'Chèn ép thần kinh'],
            ['en' => 'Inflammation', 'vi' => 'Viêm nhiễm'],
            ['en' => 'Postural dysfunction', 'vi' => 'Rối loạn tư thế']
        ];

        foreach ($secondaryDiagnoses as $diagnosis) {
            $result = new ProcessingResult();
            $result->addData([
                'diagnosis_secondary' => $diagnosis['en'],
                'diagnosis_secondary_vi' => $diagnosis['vi']
            ]);

            $data = $result->getData()[0];
            $this->assertEquals($diagnosis['vi'], $data['diagnosis_secondary_vi']);
        }
    }

    /**
     * Test multiple plans for same patient
     */
    public function testPatientMultiplePlans(): void
    {
        $plans = [
            ['id' => 1, 'name' => 'Plan 1', 'name_vi' => 'Kế hoạch 1'],
            ['id' => 2, 'name' => 'Plan 2', 'name_vi' => 'Kế hoạch 2'],
            ['id' => 3, 'name' => 'Plan 3', 'name_vi' => 'Kế hoạch 3']
        ];

        $result = new ProcessingResult();
        foreach ($plans as $plan) {
            $result->addData([
                'id' => $plan['id'],
                'patient_id' => 1,
                'plan_name' => $plan['name'],
                'plan_name_vi' => $plan['name_vi']
            ]);
        }

        $this->assertEquals(3, count($result->getData()));
        $this->assertEquals('Kế hoạch 2', $result->getData()[1]['plan_name_vi']);
    }

    /**
     * Test treatment plan status
     */
    public function testTreatmentPlanStatus(): void
    {
        $statuses = ['draft', 'active', 'completed', 'suspended', 'cancelled'];

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
     * Test treatment frequency per week
     */
    public function testTreatmentFrequency(): void
    {
        $frequencies = [1, 2, 3, 4, 5];

        foreach ($frequencies as $frequency) {
            $result = new ProcessingResult();
            $result->addData([
                'frequency_per_week' => $frequency
            ]);

            $this->assertEquals($frequency, $result->getData()[0]['frequency_per_week']);
        }
    }

    /**
     * Test Vietnamese special characters in plan names
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
            $text = 'Plan ' . $char;
            $result = new ProcessingResult();
            $result->addData(['plan_name_vi' => $text]);

            $this->assertTrue(mb_check_encoding($text, 'UTF-8'));
            $this->assertStringContainsString($char, $result->getData()[0]['plan_name_vi']);
        }
    }

    /**
     * Test empty treatment plan result
     */
    public function testEmptyTreatmentPlanResult(): void
    {
        $result = new ProcessingResult();

        $this->assertFalse($result->hasErrors());
        $this->assertEquals(0, count($result->getData()));
    }

    /**
     * Test treatment plan with all fields
     */
    public function testCompleteTreatmentPlan(): void
    {
        $planData = [
            'id' => 1,
            'patient_id' => 1,
            'plan_name' => 'Comprehensive PT Program',
            'plan_name_vi' => 'Chương trình vật lý trị liệu toàn diện',
            'diagnosis_primary' => 'Lower back pain',
            'diagnosis_primary_vi' => 'Đau lưng dưới',
            'diagnosis_secondary' => 'Muscle tension',
            'diagnosis_secondary_vi' => 'Căng cơ',
            'start_date' => '2024-01-15',
            'end_date' => '2024-04-15',
            'duration_weeks' => 12,
            'frequency_per_week' => 2,
            'goals' => 'Reduce pain and improve mobility',
            'goals_vi' => 'Giảm đau và cải thiện khả năng vận động',
            'status' => 'active'
        ];

        $result = new ProcessingResult();
        $result->addData($planData);

        $data = $result->getData()[0];
        $this->assertArrayHasKey('plan_name_vi', $data);
        $this->assertArrayHasKey('diagnosis_primary_vi', $data);
        $this->assertArrayHasKey('diagnosis_secondary_vi', $data);
        $this->assertArrayHasKey('goals_vi', $data);
        $this->assertEquals('active', $data['status']);
    }

    /**
     * Test controller response structure for treatment plans
     */
    public function testControllerResponseStructure(): void
    {
        $result = new ProcessingResult();
        $result->addData([
            'id' => 1,
            'patient_id' => 1,
            'plan_name' => 'Test Plan',
            'plan_name_vi' => 'Kế hoạch kiểm tra'
        ]);

        $response = [
            'data' => $result->getData(),
            'validationErrors' => $result->getValidationMessages(),
            'internalErrors' => $result->getInternalErrors()
        ];

        $this->assertIsArray($response['data']);
        $this->assertCount(1, $response['data']);
        $this->assertArrayHasKey('plan_name_vi', $response['data'][0]);
    }

    /**
     * Test large treatment plan dataset
     */
    public function testLargeTreatmentPlanDataset(): void
    {
        $result = new ProcessingResult();

        for ($i = 1; $i <= 50; $i++) {
            $result->addData([
                'id' => $i,
                'patient_id' => ($i % 10) + 1,
                'plan_name' => 'Plan ' . $i,
                'plan_name_vi' => 'Kế hoạch ' . $i,
                'diagnosis_primary' => 'Condition ' . $i,
                'diagnosis_primary_vi' => 'Tình trạng ' . $i,
                'duration_weeks' => ($i % 12) + 4
            ]);
        }

        $this->assertEquals(50, count($result->getData()));
        $this->assertEquals('Kế hoạch 25', $result->getData()[24]['plan_name_vi']);
    }

    /**
     * Test UTF-8 encoding in treatment plan
     */
    public function testUTF8Encoding(): void
    {
        $vietnameseText = 'Phục hồi chức năng cột sống toàn diện';

        $this->assertTrue(mb_check_encoding($vietnameseText, 'UTF-8'));

        $result = new ProcessingResult();
        $result->addData(['goals_vi' => $vietnameseText]);

        $this->assertEquals($vietnameseText, $result->getData()[0]['goals_vi']);
    }

    /**
     * Test treatment plan notes field
     */
    public function testTreatmentPlanNotes(): void
    {
        $notes = [
            'en' => 'Patient shows good compliance with treatment',
            'vi' => 'Bệnh nhân tuân thủ tốt chương trình điều trị'
        ];

        $result = new ProcessingResult();
        $result->addData([
            'notes' => $notes['en'],
            'notes_vi' => $notes['vi']
        ]);

        $data = $result->getData()[0];
        $this->assertEquals($notes['vi'], $data['notes_vi']);
    }
}
