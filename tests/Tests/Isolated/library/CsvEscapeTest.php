<?php

/**
 * CsvEscapeTest
 *
 * Tests the csvEscape() function for CSV formula injection prevention.
 * Verifies that dangerous formula prefixes (=, +, -, @, \t, \r) are
 * properly neutralized in CSV cell values.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 * AI-Generated Code Notice: This file contains code generated with
 * assistance from Claude Code (Anthropic). The code has been reviewed
 * and tested by the contributor.
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\library;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CsvEscapeTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        $path = realpath(__DIR__ . '/../../../../library/htmlspecialchars.inc.php');
        if ($path === false) {
            self::markTestSkipped('htmlspecialchars.inc.php not found');
        }
        if (!function_exists('csvEscape')) {
            require_once $path;
        }
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function safeValueProvider(): array
    {
        return [
            'plain text' => ['Hello World', '"Hello World"'],
            'numeric value' => ['12345', '"12345"'],
            'date with hyphen' => ['2024-01-15', '"2024-01-15"'],
            'email in middle' => ['user@example.com', '"userexample.com"'], // leading @ stripped
            'empty string' => ['', '""'],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function formulaInjectionProvider(): array
    {
        return [
            'equals sign removed' => ['=CMD("calc")', '"CMD("calc")"'],
            'plus sign removed' => ['+CMD("calc")', '"CMD("calc")"'],
            'leading minus stripped' => ['-1+1', '"11"'], // - and + both removed
            'leading at stripped' => ['@SUM(A1:A10)', '"SUM(A1:A10)"'],
            'pipe removed' => ['|echo "test"', '"echo "test""'],
            'tab prefix stripped' => ["\t=1+1", '"11"'], // tab and = both removed
            'cr prefix stripped' => ["\r=1+1", '"11"'], // cr and = both removed
            'double quote removed' => ['"=1+1', '"11"'], // " and = both removed
        ];
    }

    #[DataProvider('safeValueProvider')]
    public function testSafeValues(string $input, string $expected): void
    {
        $this->assertSame($expected, csvEscape($input));
    }

    #[DataProvider('formulaInjectionProvider')]
    public function testFormulaInjectionPrevention(string $input, string $expected): void
    {
        $result = csvEscape($input);
        // Verify the result is quoted
        $this->assertStringStartsWith('"', $result);
        $this->assertStringEndsWith('"', $result);

        // Verify no dangerous prefixes remain after unquoting
        $unquoted = substr($result, 1, -1);
        if ($unquoted !== '') {
            $firstChar = $unquoted[0];
            $this->assertNotContains(
                $firstChar,
                ['=', '+', '-', '@', "\t", "\r"],
                "csvEscape() must neutralize leading formula injection characters, found: " . ord($firstChar)
            );
        }
    }

    public function testNullInputReturnsEmptyQuoted(): void
    {
        // csvEscape uses null coalescing on the input
        $this->assertSame('""', csvEscape(null));
    }

    /**
     * Verify that the tab character (\t) is handled as a formula injection vector.
     * This was a gap in the original implementation.
     */
    public function testTabPrefixIsStripped(): void
    {
        $result = csvEscape("\tmalicious");
        $unquoted = substr($result, 1, -1);
        $this->assertStringNotContainsString("\t", $unquoted[0] ?? '');
    }

    /**
     * Verify that the carriage return (\r) is handled as a formula injection vector.
     * This was a gap in the original implementation.
     */
    public function testCarriageReturnPrefixIsStripped(): void
    {
        $result = csvEscape("\rmalicious");
        $unquoted = substr($result, 1, -1);
        $this->assertFalse(str_starts_with($unquoted, "\r"));
    }
}
