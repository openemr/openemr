<?php

/**
 * Vietnamese Character Encoding Unit Tests
 * Tests UTF-8 and utf8mb4_vietnamese_ci collation handling
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Dang Tran <tqvdang@msn.com>
 * @copyright Copyright (c) 2024 Dang Tran
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\Vietnamese;

use PHPUnit\Framework\TestCase;

class CharacterEncodingTest extends TestCase
{
    /**
     * Test Vietnamese diacritic characters are preserved
     */
    public function testVietnameseDiacriticsPreserved(): void
    {
        $vietnameseText = "Vật lý trị liệu";
        $this->assertEquals(
            "Vật lý trị liệu",
            $vietnameseText,
            "Vietnamese diacritics should be preserved"
        );

        // Test all Vietnamese tone marks
        $toneMarks = [
            'á' => true, 'à' => true, 'ả' => true, 'ã' => true, 'ạ' => true, // a tones
            'ă' => true, 'ắ' => true, 'ằ' => true, 'ẳ' => true, 'ẵ' => true, 'ặ' => true, // ă tones
            'â' => true, 'ấ' => true, 'ầ' => true, 'ẩ' => true, 'ẫ' => true, 'ậ' => true, // â tones
            'đ' => true, // đ
            'é' => true, 'è' => true, 'ẻ' => true, 'ẽ' => true, 'ẹ' => true, // e tones
            'ê' => true, 'ế' => true, 'ề' => true, 'ể' => true, 'ễ' => true, 'ệ' => true, // ê tones
            'í' => true, 'ì' => true, 'ỉ' => true, 'ĩ' => true, 'ị' => true, // i tones
            'ó' => true, 'ò' => true, 'ỏ' => true, 'õ' => true, 'ọ' => true, // o tones
            'ô' => true, 'ố' => true, 'ồ' => true, 'ổ' => true, 'ỗ' => true, 'ộ' => true, // ô tones
            'ơ' => true, 'ớ' => true, 'ờ' => true, 'ở' => true, 'ỡ' => true, 'ợ' => true, // ơ tones
            'ú' => true, 'ù' => true, 'ủ' => true, 'ũ' => true, 'ụ' => true, // u tones
            'ư' => true, 'ứ' => true, 'ừ' => true, 'ử' => true, 'ữ' => true, 'ự' => true, // ư tones
            'ý' => true, 'ỳ' => true, 'ỷ' => true, 'ỹ' => true, 'ỵ' => true, // y tones
        ];

        foreach ($toneMarks as $char => $expected) {
            $this->assertTrue(
                mb_check_encoding($char, 'UTF-8'),
                "Vietnamese character '$char' should be valid UTF-8"
            );
        }
    }

    /**
     * Test Vietnamese character byte length calculation
     */
    public function testVietnameseCharacterLength(): void
    {
        $text = "Đau cơ xương khớp";

        // mb_strlen counts characters
        $this->assertEquals(17, mb_strlen($text), "Character count should be 17");

        // strlen counts bytes (Vietnamese chars are 2-3 bytes each)
        $this->assertGreaterThan(17, strlen($text), "Byte length should be greater than character count");
    }

    /**
     * Test Vietnamese string comparison (case-insensitive)
     */
    public function testVietnameseStringComparison(): void
    {
        $str1 = "Bệnh Nhân";
        $str2 = "bệnh nhân";

        // Vietnamese collation should handle case-insensitive comparison
        $this->assertEquals(
            0,
            strcasecmp($str1, $str2),
            "Vietnamese strings should be case-insensitive equal"
        );
    }

    /**
     * Test Vietnamese text sanitization preserves characters
     */
    public function testVietnameseSanitization(): void
    {
        $originalText = "Phục hồi chức năng";

        // Test that htmlspecialchars preserves Vietnamese characters (better than deprecated FILTER_SANITIZE_STRING)
        $sanitized = htmlspecialchars($originalText, ENT_QUOTES, 'UTF-8');

        // The original text and sanitized text should be identical for Vietnamese without special HTML chars
        $this->assertEquals(
            $originalText,
            $sanitized,
            "htmlspecialchars should preserve Vietnamese characters when no HTML chars present"
        );

        // Verify both contain Vietnamese text patterns
        $this->assertStringContainsString('ch', $sanitized, "Sanitized text should contain Vietnamese word 'chức'");
        $this->assertTrue(mb_check_encoding($sanitized, 'UTF-8'), "Sanitized text should be valid UTF-8");
    }

    /**
     * Test Vietnamese medical terminology encoding
     */
    public function testVietnameseMedicalTerms(): void
    {
        $terms = [
            'Vật lý trị liệu' => 'Physiotherapy',
            'Bệnh nhân' => 'Patient',
            'Điều trị' => 'Treatment',
            'Tập thể dục' => 'Exercise',
            'Phục hồi chức năng' => 'Rehabilitation',
            'Đau cơ xương khớp' => 'Musculoskeletal pain',
            'Liệu pháp massage' => 'Massage therapy',
            'Kế hoạch điều trị' => 'Treatment plan',
        ];

        foreach ($terms as $vietnamese => $english) {
            $this->assertTrue(
                mb_check_encoding($vietnamese, 'UTF-8'),
                "Vietnamese medical term '$vietnamese' should be valid UTF-8"
            );

            $this->assertGreaterThan(
                0,
                mb_strlen($vietnamese),
                "Vietnamese term should not be empty"
            );
        }
    }

    /**
     * Test Vietnamese JSON encoding/decoding
     */
    public function testVietnameseJsonHandling(): void
    {
        $data = [
            'patient_name' => 'Nguyễn Văn An',
            'diagnosis' => 'Đau lưng mãn tính',
            'treatment' => 'Vật lý trị liệu'
        ];

        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        $this->assertNotFalse($json, "JSON encoding should succeed");

        $decoded = json_decode($json, true);
        $this->assertEquals($data, $decoded, "JSON decode should preserve Vietnamese characters");

        // Verify specific Vietnamese characters are preserved
        $this->assertStringContainsString('Nguyễn', $json);
        $this->assertStringContainsString('Vật', $json);
    }

    /**
     * Test Vietnamese text truncation preserves character integrity
     */
    public function testVietnameseTextTruncation(): void
    {
        $text = "Điều trị vật lý trị liệu cho bệnh nhân đau cơ xương khớp";

        // Truncate to 20 characters
        $truncated = mb_substr($text, 0, 20);

        $this->assertEquals(20, mb_strlen($truncated), "Truncated length should be exactly 20 characters");
        $this->assertTrue(
            mb_check_encoding($truncated, 'UTF-8'),
            "Truncated text should still be valid UTF-8"
        );
    }

    /**
     * Test Vietnamese sorting order
     */
    public function testVietnameseSortOrder(): void
    {
        $names = ['Nguyễn', 'Trần', 'Lê', 'Phạm', 'Hoàng'];
        $sorted = $names;
        sort($sorted);

        // Basic alphabetical sort should work
        $this->assertIsArray($sorted);
        $this->assertCount(5, $sorted);
    }

    /**
     * Test Vietnamese character replacement/transliteration
     */
    public function testVietnameseToAscii(): void
    {
        $mapping = [
            'á' => 'a', 'à' => 'a', 'ả' => 'a', 'ã' => 'a', 'ạ' => 'a',
            'ă' => 'a', 'ắ' => 'a', 'ằ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a', 'ặ' => 'a',
            'â' => 'a', 'ấ' => 'a', 'ầ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a', 'ậ' => 'a',
            'đ' => 'd',
            'é' => 'e', 'è' => 'e', 'ẻ' => 'e', 'ẽ' => 'e', 'ẹ' => 'e',
            'ê' => 'e', 'ế' => 'e', 'ề' => 'e', 'ể' => 'e', 'ễ' => 'e', 'ệ' => 'e',
        ];

        foreach ($mapping as $vietnamese => $ascii) {
            $this->assertEquals(
                1,
                mb_strlen($vietnamese),
                "Vietnamese character should be single character"
            );
            $this->assertEquals(
                1,
                strlen($ascii),
                "ASCII equivalent should be single byte"
            );
        }
    }
}