<?php

/**
 * Isolated StringUtils Test
 *
 * Tests string manipulation methods in StringUtils.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Utils;

use OpenEMR\Common\Utils\StringUtils;
use PHPUnit\Framework\TestCase;

class StringUtilsTest extends TestCase
{
    /**
     * @dataProvider trimExcessWhitespaceProvider
     */
    public function testTrimExcessWhitespace(mixed $input, string $expected): void
    {
        $this->assertSame($expected, StringUtils::trimExcessWhitespace($input));
    }

    /**
     * @return array<string, array{mixed, string}>
     */
    public static function trimExcessWhitespaceProvider(): array
    {
        return [
            'simple string no change' => ['hello world', 'hello world'],
            'leading space' => [' hello', 'hello'],
            'trailing space' => ['hello ', 'hello'],
            'leading and trailing spaces' => ['  hello  ', 'hello'],
            'multiple internal spaces' => ['hello    world', 'hello world'],
            'tabs' => ["hello\tworld", 'hello world'],
            'newlines' => ["hello\nworld", 'hello world'],
            'carriage return' => ["hello\rworld", 'hello world'],
            'mixed whitespace' => ["  hello  \t\n  world  ", 'hello world'],
            'multiple words with excess spaces' => ['one   two   three', 'one two three'],
            'empty string' => ['', ''],
            'only whitespace' => ['   ', ''],
            'null value' => [null, ''],
            'single word' => ['hello', 'hello'],
            'single character' => ['a', 'a'],
        ];
    }

    public function testTrimExcessWhitespaceWithNumericInput(): void
    {
        // The method casts to string, so numeric input should work
        $this->assertSame('123', StringUtils::trimExcessWhitespace(123));
        $this->assertSame('12.34', StringUtils::trimExcessWhitespace(12.34));
    }

    public function testTrimExcessWhitespacePreservesSingleSpaces(): void
    {
        $input = 'The quick brown fox jumps over the lazy dog';
        $this->assertSame($input, StringUtils::trimExcessWhitespace($input));
    }

    public function testTrimExcessWhitespaceWithUnicodeSpaces(): void
    {
        // Test with non-breaking space (U+00A0)
        // Note: preg_replace with \s+ does NOT match non-breaking spaces by default
        // This documents the current behavior - non-breaking spaces are preserved
        $input = "hello\u{00A0}\u{00A0}world";
        $result = StringUtils::trimExcessWhitespace($input);
        // Non-breaking spaces are NOT collapsed (this may or may not be desired)
        $this->assertSame("hello\u{00A0}\u{00A0}world", $result);
    }

    public function testTrimExcessWhitespaceWithMixedContent(): void
    {
        // Test typical use case - messy user input
        $input = "   John    Smith   Jr.   ";
        $expected = 'John Smith Jr.';
        $this->assertSame($expected, StringUtils::trimExcessWhitespace($input));
    }
}
