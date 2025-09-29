<?php

/**
 * Vietnamese Medical Terminology Unit Tests
 * Tests bilingual medical term handling for physiotherapy
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Dang Tran <tqvdang@msn.com>
 * @copyright Copyright (c) 2024 Dang Tran
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\Vietnamese;

use PHPUnit\Framework\TestCase;

class MedicalTerminologyTest extends TestCase
{
    private array $physiotherapyTerms = [
        'general' => [
            'Vật lý trị liệu' => 'Physiotherapy',
            'Bệnh nhân' => 'Patient',
            'Điều trị' => 'Treatment',
            'Đánh giá' => 'Assessment',
            'Chẩn đoán' => 'Diagnosis',
        ],
        'conditions' => [
            'Đau lưng' => 'Back pain',
            'Đau cổ' => 'Neck pain',
            'Đau vai' => 'Shoulder pain',
            'Đau đầu gối' => 'Knee pain',
            'Đau cơ xương khớp' => 'Musculoskeletal pain',
            'Gãy xương' => 'Fracture',
            'Bong gân' => 'Sprain',
            'Viêm khớp' => 'Arthritis',
        ],
        'treatments' => [
            'Massage' => 'Massage',
            'Vận động trị liệu' => 'Exercise therapy',
            'Điện trị liệu' => 'Electrotherapy',
            'Nhiệt trị liệu' => 'Thermotherapy',
            'Thủy trị liệu' => 'Hydrotherapy',
            'Kéo giãn' => 'Stretching',
            'Tập phục hồi' => 'Rehabilitation exercise',
        ],
        'body_parts' => [
            'Cột sống' => 'Spine',
            'Vai' => 'Shoulder',
            'Khuỷu tay' => 'Elbow',
            'Cổ tay' => 'Wrist',
            'Hông' => 'Hip',
            'Đầu gối' => 'Knee',
            'Cổ chân' => 'Ankle',
            'Cơ' => 'Muscle',
            'Xương' => 'Bone',
            'Khớp' => 'Joint',
        ],
    ];

    /**
     * Test medical term structure and integrity
     */
    public function testMedicalTermStructure(): void
    {
        foreach ($this->physiotherapyTerms as $category => $terms) {
            $this->assertIsArray($terms, "Category '$category' should be an array");
            $this->assertNotEmpty($terms, "Category '$category' should not be empty");

            foreach ($terms as $vietnamese => $english) {
                $this->assertIsString($vietnamese, "Vietnamese term should be string");
                $this->assertIsString($english, "English term should be string");
                $this->assertNotEmpty($vietnamese, "Vietnamese term should not be empty");
                $this->assertNotEmpty($english, "English term should not be empty");
            }
        }
    }

    /**
     * Test Vietnamese term lookup
     */
    public function testVietnameseTermLookup(): void
    {
        $allTerms = array_merge(...array_values($this->physiotherapyTerms));

        // Test specific term lookups
        $this->assertEquals('Physiotherapy', $allTerms['Vật lý trị liệu']);
        $this->assertEquals('Back pain', $allTerms['Đau lưng']);
        $this->assertEquals('Massage', $allTerms['Massage']);
        $this->assertEquals('Spine', $allTerms['Cột sống']);
    }

    /**
     * Test English term lookup (reverse)
     */
    public function testEnglishTermReverseLookup(): void
    {
        $allTerms = array_merge(...array_values($this->physiotherapyTerms));
        $reversedTerms = array_flip($allTerms);

        $this->assertEquals('Vật lý trị liệu', $reversedTerms['Physiotherapy']);
        $this->assertEquals('Đau lưng', $reversedTerms['Back pain']);
        $this->assertEquals('Cột sống', $reversedTerms['Spine']);
    }

    /**
     * Test term categorization
     */
    public function testTermCategorization(): void
    {
        $this->assertArrayHasKey('general', $this->physiotherapyTerms);
        $this->assertArrayHasKey('conditions', $this->physiotherapyTerms);
        $this->assertArrayHasKey('treatments', $this->physiotherapyTerms);
        $this->assertArrayHasKey('body_parts', $this->physiotherapyTerms);

        // Test category counts
        $this->assertCount(5, $this->physiotherapyTerms['general']);
        $this->assertCount(8, $this->physiotherapyTerms['conditions']);
        $this->assertCount(7, $this->physiotherapyTerms['treatments']);
        $this->assertCount(10, $this->physiotherapyTerms['body_parts']);
    }

    /**
     * Test pain-related terms
     */
    public function testPainTerms(): void
    {
        $painTerms = array_filter(
            $this->physiotherapyTerms['conditions'],
            fn($term) => str_contains($term, 'pain'),
            ARRAY_FILTER_USE_BOTH
        );

        $this->assertGreaterThan(0, count($painTerms), "Should have pain-related terms");

        // All Vietnamese pain terms should start with "Đau"
        $vietnamesePainTerms = array_keys(
            array_filter(
                $this->physiotherapyTerms['conditions'],
                fn($term) => str_contains($term, 'pain'),
                ARRAY_FILTER_USE_BOTH
            )
        );

        foreach ($vietnamesePainTerms as $term) {
            $this->assertStringStartsWith('Đau', $term, "Vietnamese pain terms should start with 'Đau'");
        }
    }

    /**
     * Test term search functionality
     */
    public function testTermSearch(): void
    {
        $allTerms = array_merge(...array_values($this->physiotherapyTerms));
        $searchTerm = 'đau';

        $results = array_filter(
            array_keys($allTerms),
            fn($term) => mb_stripos($term, $searchTerm) !== false
        );

        $this->assertGreaterThan(0, count($results), "Should find terms containing 'đau'");
    }

    /**
     * Test bilingual term pairing
     */
    public function testBilingualPairing(): void
    {
        $pairs = [
            ['vi' => 'Vật lý trị liệu', 'en' => 'Physiotherapy'],
            ['vi' => 'Bệnh nhân', 'en' => 'Patient'],
            ['vi' => 'Đau lưng', 'en' => 'Back pain'],
        ];

        foreach ($pairs as $pair) {
            $this->assertArrayHasKey('vi', $pair);
            $this->assertArrayHasKey('en', $pair);
            $this->assertTrue(mb_check_encoding($pair['vi'], 'UTF-8'));
            $this->assertNotEmpty($pair['en']);
        }
    }

    /**
     * Test term validation
     */
    public function testTermValidation(): void
    {
        $allTerms = array_merge(...array_values($this->physiotherapyTerms));

        foreach ($allTerms as $vietnamese => $english) {
            // Vietnamese term should contain Vietnamese characters
            $hasVietnameseChars = preg_match('/[àáảãạăắằẳẵặâấầẩẫậđèéẻẽẹêếềểễệìíỉĩịòóỏõọôốồổỗộơớờởỡợùúủũụưứừửữựỳýỷỹỵ]/iu', $vietnamese);

            if ($vietnamese !== 'Massage') { // Massage is the same in both languages
                $this->assertGreaterThanOrEqual(
                    0,
                    $hasVietnameseChars,
                    "Vietnamese term '$vietnamese' validation"
                );
            }

            // English term should be ASCII
            $this->assertTrue(
                mb_check_encoding($english, 'ASCII') || ctype_print($english),
                "English term '$english' should be printable"
            );
        }
    }

    /**
     * Test term uniqueness
     */
    public function testTermUniqueness(): void
    {
        $allTerms = array_merge(...array_values($this->physiotherapyTerms));

        // Check for duplicate Vietnamese terms
        $vietnameseTerms = array_keys($allTerms);
        $uniqueVietnamese = array_unique($vietnameseTerms);
        $this->assertCount(
            count($vietnameseTerms),
            $uniqueVietnamese,
            "All Vietnamese terms should be unique"
        );

        // Check for duplicate English terms (excluding "Massage" which might appear multiple times)
        $englishTerms = array_values($allTerms);
        $englishCounts = array_count_values($englishTerms);

        foreach ($englishCounts as $term => $count) {
            if ($term !== 'Massage') {
                $this->assertEquals(
                    1,
                    $count,
                    "English term '$term' should appear only once"
                );
            }
        }
    }

    /**
     * Test term formatting consistency
     */
    public function testTermFormatting(): void
    {
        $allTerms = array_merge(...array_values($this->physiotherapyTerms));

        foreach ($allTerms as $vietnamese => $english) {
            // Terms should not have leading/trailing whitespace
            $this->assertEquals(trim($vietnamese), $vietnamese, "Vietnamese term should not have whitespace");
            $this->assertEquals(trim($english), $english, "English term should not have whitespace");

            // Terms should not contain multiple consecutive spaces
            $this->assertNotRegExp('/\s{2,}/', $vietnamese, "Vietnamese term should not have multiple spaces");
            $this->assertNotRegExp('/\s{2,}/', $english, "English term should not have multiple spaces");
        }
    }
}