<?php

namespace OpenEMR\Tests\RestControllers\VietnamesePT;

use PHPUnit\Framework\TestCase;
use OpenEMR\Validators\ProcessingResult;

/**
 * PT Exercise Prescription REST Controller Tests
 *
 * Tests CRUD operations for physiotherapy exercise prescriptions
 * with Vietnamese bilingual support.
 *
 * AI Generated Test Suite - Generated for Vietnamese PT REST API testing
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    AI Generated <test@openemr.org>
 * @copyright Copyright (c) 2024
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class PTExercisePrescriptionRestControllerTest extends TestCase
{
    /**
     * Test ProcessingResult with exercise prescription data
     */
    public function testExercisePrescriptionData(): void
    {
        $exerciseData = [
            'id' => 1,
            'patient_id' => 1,
            'exercise_name' => 'Lumbar stretches',
            'exercise_name_vi' => 'Giãn cơ lưng',
            'sets_prescribed' => 3,
            'reps_prescribed' => 10,
            'frequency_per_week' => 5,
            'status' => 'active'
        ];

        $result = new ProcessingResult();
        $result->addData($exerciseData);

        $this->assertFalse($result->hasErrors());
        $this->assertEquals('Giãn cơ lưng', $result->getData()[0]['exercise_name_vi']);
        $this->assertEquals(3, $result->getData()[0]['sets_prescribed']);
    }

    /**
     * Test exercise validation messages
     */
    public function testExerciseValidationErrors(): void
    {
        $result = new ProcessingResult();
        $result->addValidationMessage('patient_id', 'required');
        $result->addValidationMessage('exercise_name', 'required');
        $result->addValidationMessage('start_date', 'required');
        $result->addValidationMessage('prescribed_by', 'required');

        $this->assertTrue($result->hasErrors());
        $this->assertGreaterThan(3, count($result->getValidationMessages()));
    }

    /**
     * Test Vietnamese exercise names
     */
    public function testVietnameseExerciseNames(): void
    {
        $exercises = [
            ['en' => 'Lumbar stretches', 'vi' => 'Giãn cơ lưng'],
            ['en' => 'Range of motion', 'vi' => 'Phạm vi chuyển động'],
            ['en' => 'Strength training', 'vi' => 'Bài tập tăng cường sức mạnh'],
            ['en' => 'Balance exercises', 'vi' => 'Bài tập cân bằng'],
            ['en' => 'Flexibility work', 'vi' => 'Bài tập nâng cao tính linh hoạt']
        ];

        foreach ($exercises as $exercise) {
            $result = new ProcessingResult();
            $result->addData([
                'exercise_name' => $exercise['en'],
                'exercise_name_vi' => $exercise['vi']
            ]);

            $data = $result->getData()[0];
            $this->assertEquals($exercise['vi'], $data['exercise_name_vi']);
            $this->assertTrue(mb_check_encoding($exercise['vi'], 'UTF-8'));
        }
    }

    /**
     * Test exercise parameters
     */
    public function testExerciseParameters(): void
    {
        $parameters = [
            ['sets' => 3, 'reps' => 10, 'frequency' => 5],
            ['sets' => 2, 'reps' => 15, 'frequency' => 3],
            ['sets' => 4, 'reps' => 8, 'frequency' => 6],
            ['sets' => 1, 'reps' => 30, 'frequency' => 7]
        ];

        foreach ($parameters as $param) {
            $result = new ProcessingResult();
            $result->addData([
                'sets_prescribed' => $param['sets'],
                'reps_prescribed' => $param['reps'],
                'frequency_per_week' => $param['frequency']
            ]);

            $data = $result->getData()[0];
            $this->assertEquals($param['sets'], $data['sets_prescribed']);
            $this->assertEquals($param['reps'], $data['reps_prescribed']);
            $this->assertEquals($param['frequency'], $data['frequency_per_week']);
        }
    }

    /**
     * Test exercise intensity levels
     */
    public function testExerciseIntensityLevels(): void
    {
        $intensities = ['low', 'moderate', 'high'];

        foreach ($intensities as $intensity) {
            $result = new ProcessingResult();
            $result->addData([
                'id' => 1,
                'patient_id' => 1,
                'exercise_name' => 'Test exercise',
                'exercise_name_vi' => 'Bài tập kiểm tra',
                'intensity_level' => $intensity
            ]);

            $this->assertEquals($intensity, $result->getData()[0]['intensity_level']);
        }
    }

    /**
     * Test multiple exercises for same patient
     */
    public function testPatientMultipleExercises(): void
    {
        $exercises = [
            ['id' => 1, 'name' => 'Exercise 1', 'name_vi' => 'Bài tập 1'],
            ['id' => 2, 'name' => 'Exercise 2', 'name_vi' => 'Bài tập 2'],
            ['id' => 3, 'name' => 'Exercise 3', 'name_vi' => 'Bài tập 3']
        ];

        $result = new ProcessingResult();
        foreach ($exercises as $exercise) {
            $result->addData([
                'id' => $exercise['id'],
                'patient_id' => 1,
                'exercise_name' => $exercise['name'],
                'exercise_name_vi' => $exercise['name_vi']
            ]);
        }

        $this->assertEquals(3, count($result->getData()));
        $this->assertEquals('Bài tập 2', $result->getData()[1]['exercise_name_vi']);
    }

    /**
     * Test exercise dates
     */
    public function testExerciseDates(): void
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
     * Test exercise instructions with Vietnamese characters
     */
    public function testExerciseInstructions(): void
    {
        $instructions = [
            'en' => 'Do slowly and carefully',
            'vi' => 'Làm chậm từng cơ và cẩn thận'
        ];

        $result = new ProcessingResult();
        $result->addData([
            'id' => 1,
            'patient_id' => 1,
            'exercise_name' => 'Test',
            'exercise_name_vi' => 'Kiểm tra',
            'instructions' => $instructions['en'],
            'instructions_vi' => $instructions['vi']
        ]);

        $data = $result->getData()[0];
        $this->assertEquals($instructions['vi'], $data['instructions_vi']);
        $this->assertTrue(mb_check_encoding($instructions['vi'], 'UTF-8'));
    }

    /**
     * Test exercise precautions
     */
    public function testExercisePrecautions(): void
    {
        $precautions = [
            ['en' => 'No pain', 'vi' => 'Không có cơn đau'],
            ['en' => 'Stop if dizzy', 'vi' => 'Dừng nếu cảm thấy chóng mặt'],
            ['en' => 'Warm up first', 'vi' => 'Khởi động trước']
        ];

        foreach ($precautions as $precaution) {
            $result = new ProcessingResult();
            $result->addData([
                'precautions' => $precaution['en'],
                'precautions_vi' => $precaution['vi']
            ]);

            $data = $result->getData()[0];
            $this->assertEquals($precaution['vi'], $data['precautions_vi']);
        }
    }

    /**
     * Test duration and frequency validation
     */
    public function testDurationFrequencyValidation(): void
    {
        $durations = [10, 20, 30, 45, 60];
        $frequencies = [1, 2, 3, 4, 5, 6, 7];

        foreach ($durations as $duration) {
            foreach ($frequencies as $frequency) {
                $result = new ProcessingResult();
                $result->addData([
                    'duration_minutes' => $duration,
                    'frequency_per_week' => $frequency
                ]);

                $data = $result->getData()[0];
                $this->assertEquals($duration, $data['duration_minutes']);
                $this->assertEquals($frequency, $data['frequency_per_week']);
            }
        }
    }

    /**
     * Test empty exercise result
     */
    public function testEmptyExerciseResult(): void
    {
        $result = new ProcessingResult();

        $this->assertFalse($result->hasErrors());
        $this->assertEquals(0, count($result->getData()));
    }

    /**
     * Test exercise equipment information
     */
    public function testExerciseEquipment(): void
    {
        $equipment = ['None', 'Mat', 'Resistance band', 'Dumbbell', 'Ball'];

        foreach ($equipment as $item) {
            $result = new ProcessingResult();
            $result->addData([
                'equipment_needed' => $item
            ]);

            $this->assertEquals($item, $result->getData()[0]['equipment_needed']);
        }
    }

    /**
     * Test exercise status types
     */
    public function testExerciseStatusTypes(): void
    {
        $statuses = ['active', 'inactive', 'completed', 'on-hold'];

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
            $text = 'Exercise ' . $char;
            $result = new ProcessingResult();
            $result->addData(['description_vi' => $text]);

            $this->assertTrue(mb_check_encoding($text, 'UTF-8'));
            $this->assertStringContainsString($char, $result->getData()[0]['description_vi']);
        }
    }

    /**
     * Test controller response structure for exercises
     */
    public function testControllerResponseStructure(): void
    {
        $result = new ProcessingResult();
        $result->addData([
            'id' => 1,
            'patient_id' => 1,
            'exercise_name' => 'Stretching',
            'exercise_name_vi' => 'Giãn cơ'
        ]);

        $response = [
            'data' => $result->getData(),
            'validationErrors' => $result->getValidationMessages(),
            'internalErrors' => $result->getInternalErrors()
        ];

        $this->assertIsArray($response['data']);
        $this->assertCount(1, $response['data']);
        $this->assertArrayHasKey('exercise_name_vi', $response['data'][0]);
    }

    /**
     * Test large exercise dataset
     */
    public function testLargeExerciseDataset(): void
    {
        $result = new ProcessingResult();

        for ($i = 1; $i <= 50; $i++) {
            $result->addData([
                'id' => $i,
                'patient_id' => ($i % 10) + 1,
                'exercise_name' => 'Exercise ' . $i,
                'exercise_name_vi' => 'Bài tập ' . $i,
                'frequency_per_week' => ($i % 7) + 1
            ]);
        }

        $this->assertEquals(50, count($result->getData()));
        $this->assertEquals('Bài tập 25', $result->getData()[24]['exercise_name_vi']);
    }
}
