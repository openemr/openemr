<?php

/**
 * Isolated tests for pure functions in CAMOS content_parser.php.
 *
 * Defines CAMOS_CONTENT_PARSER_SKIP_INCLUDES to prevent the file from
 * loading framework dependencies, then requires it normally to get the
 * pure function definitions (content_parser, remove_comments, patient_age).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Forms\CAMOS;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

#[RunTestsInSeparateProcesses]
class ContentParserFunctionsTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        if (!function_exists('content_parser')) {
            define('CAMOS_CONTENT_PARSER_SKIP_INCLUDES', true);
            require_once __DIR__ . '/../../../../../interface/forms/CAMOS/content_parser.php';
        }
    }
    // ── patient_age() ──────────────────────────────────────────────

    #[DataProvider('patientAgeProvider')]
    public function testPatientAge(string $birthday, string $date, int $expected): void
    {
        $this->assertSame($expected, \patient_age($birthday, $date));
    }

    /** @return array<string, array{string, string, int}> */
    public static function patientAgeProvider(): array
    {
        return [
            'simple years' => ['1990-06-15', '2026-03-30', 35],
            'birthday today' => ['1990-03-30', '2026-03-30', 36],
            'birthday tomorrow' => ['1990-03-31', '2026-03-30', 35],
            'birthday yesterday' => ['1990-03-29', '2026-03-30', 36],
            'same date' => ['2000-01-01', '2000-01-01', 0],
            'newborn' => ['2026-03-30', '2026-03-30', 0],
            'one day old' => ['2026-03-29', '2026-03-30', 0],
            'just turned one' => ['2025-03-30', '2026-03-30', 1],
            'almost one' => ['2025-03-31', '2026-03-30', 0],
            'leap year birthday on non-leap year' => ['2000-02-29', '2025-02-28', 24],
            'end of year' => ['1985-12-31', '2026-01-01', 40],
        ];
    }

    // ── remove_comments() ──────────────────────────────────────────

    public function testRemoveCommentsSingleLine(): void
    {
        $this->assertSame('hello  world', \remove_comments('hello /* comment */ world'));
    }

    public function testRemoveCommentsMultiLine(): void
    {
        $input = "before\n/* multi\nline\ncomment */\nafter";
        $this->assertSame("before\n\nafter", \remove_comments($input));
    }

    public function testRemoveCommentsNoComments(): void
    {
        $this->assertSame('no comments here', \remove_comments('no comments here'));
    }

    public function testRemoveCommentsMultipleComments(): void
    {
        $input = '/* one */ text /* two */ more /* three */';
        $this->assertSame(' text  more ', \remove_comments($input));
    }

    public function testRemoveCommentsEmptyString(): void
    {
        $this->assertSame('', \remove_comments(''));
    }

    public function testRemoveCommentsNestedDelimiters(): void
    {
        // The regex is non-greedy, so /* a /* b */ stops at the first */
        $input = '/* a /* b */ c';
        $this->assertSame(' c', \remove_comments($input));
    }

    // ── content_parser() ───────────────────────────────────────────

    public function testContentParserPassesThrough(): void
    {
        $input = "Hello world\nSecond line";
        $this->assertSame($input, \content_parser($input));
    }

    public function testContentParserReducesBlankLinesLF(): void
    {
        $input = "line1\n\n\n\n\nline2";
        $result = \content_parser($input);
        // The regex /([^\r]\n[^\r]){2,}/ matches "char\nchar" units, so it
        // consumes the trailing char of the previous line and leading char of
        // the next. This is a known quirk of the legacy implementation.
        $this->assertSame("line\n\nline2", $result);
    }

    public function testContentParserReducesBlankLinesCRLF(): void
    {
        $input = "line1\r\n\r\n\r\n\r\nline2";
        $result = \content_parser($input);
        $this->assertSame("line1\r\n\r\nline2", $result);
    }

    public function testContentParserEmptyString(): void
    {
        $this->assertSame('', \content_parser(''));
    }

    public function testContentParserSingleNewline(): void
    {
        $input = "line1\nline2";
        $this->assertSame($input, \content_parser($input));
    }
}
