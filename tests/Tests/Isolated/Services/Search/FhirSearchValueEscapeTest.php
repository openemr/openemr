<?php

/**
 * Isolated tests for FhirSearchValueEscape
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    oldrobotdev <hi@oldrobot.us>
 * @copyright Copyright (c) 2026 oldrobotdev
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Search;

use OpenEMR\Services\Search\FhirSearchValueEscape;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FhirSearchValueEscapeTest extends TestCase
{
    /**
     * @return array<string, array{string, string, list<string>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function splitProvider(): array
    {
        return [
            'no separator' => ['abc', ',', ['abc']],
            'empty value' => ['', ',', ['']],
            'two values' => ['a,b', ',', ['a', 'b']],
            'empty values around the separators' => [',a,', ',', ['', 'a', '']],
            'escaped separator stays in the value' => ['a\,b', ',', ['a\,b']],
            'escaped backslash before a separator' => ['a\\\\,b', ',', ['a\\\\', 'b']],
            'other escapes are kept' => ['a\|b,c\$d', ',', ['a\|b', 'c\$d']],
            'pipe separator' => ['urn:x|a\|b', '|', ['urn:x', 'a\|b']],
            'trailing backslash' => ['a,b\\', ',', ['a', 'b\\']],
        ];
    }

    /**
     * @param list<string> $expected
     */
    #[DataProvider('splitProvider')]
    public function testSplit(string $value, string $separator, array $expected): void
    {
        $this->assertSame($expected, FhirSearchValueEscape::split($value, $separator));
    }

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function unescapeProvider(): array
    {
        return [
            'nothing to unescape' => ['abc', 'abc'],
            'comma' => ['a\,b', 'a,b'],
            'dollar' => ['a\$b', 'a$b'],
            'pipe' => ['a\|b', 'a|b'],
            'backslash' => ['a\\\\b', 'a\\b'],
            'escaped backslash before a comma' => ['a\\\\\,b', 'a\\,b'],
            'backslash before another character is kept' => ['a\b', 'a\b'],
            'trailing backslash is kept' => ['a\\', 'a\\'],
        ];
    }

    #[DataProvider('unescapeProvider')]
    public function testUnescape(string $value, string $expected): void
    {
        $this->assertSame($expected, FhirSearchValueEscape::unescape($value));
    }
}
