<?php

/**
 * TranslationDisabledPathTest
 *
 * Tests xl() when disable_translation is enabled.
 *
 * The disable_translation path should still run xlCleanup() so that
 * {{context}} markers, newlines, carriage returns, and unsafe quotes
 * are stripped/normalized — matching the behavior of the enabled path.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\library;

use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[Small]
#[BackupGlobals(true)]
class TranslationDisabledPathTest extends TestCase
{
    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function contextMarkerProvider(): array
    {
        return [
            'day-of-week header' => ['S{{Sunday}}', 'S'],
            'day-of-week header Monday' => ['M{{Monday}}', 'M'],
            'button label' => ['Find Available{{Provider}}', 'Find Available'],
            'tab label' => ['Dashboard{{patient file}}', 'Dashboard'],
            'dataTables footer' => ['Showing 1 to{{range}}', 'Showing 1 to'],
        ];
    }

    #[DataProvider('contextMarkerProvider')]
    public function testXlStripsContextMarkersWhenTranslationDisabled(
        string $input,
        string $expected
    ): void {
        // With disable_translation = true, xl() should skip the cache/DB lookup
        // but still call xlCleanup(), which strips {{…}} markers.
        $GLOBALS['disable_translation'] = true;

        $this->assertSame($expected, xl($input));
    }

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function quoteNormalizationProvider(): array
    {
        return [
            'double quote' => ['He said "hello"', 'He said `hello`'],
            'apostrophe' => ['It\'s working', 'It`s working'],
            'both quotes and apostrophe' => ['He said "it\'s fine"', 'He said `it`s fine`'],
        ];
    }

    #[DataProvider('quoteNormalizationProvider')]
    public function testXlNormalizesQuotesWhenTranslationDisabled(
        string $input,
        string $expected
    ): void {
        // With translate_no_safe_apostrophe = false (default), xl() converts
        // double quotes and apostrophes to safe backticks.
        $GLOBALS['disable_translation'] = true;
        // Explicitly ensure translate_no_safe_apostrophe is NOT set so the
        // "convert to safe apostrophe" branch runs.
        unset($GLOBALS['translate_no_safe_apostrophe']);

        $this->assertSame($expected, xl($input));
    }

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function noSafeApostropheProvider(): array
    {
        return [
            'double quote preserved' => ['He said "hello"', 'He said "hello"'],
            'apostrophe preserved' => ['It\'s working', 'It\'s working'],
            'both preserved' => ['He said "it\'s fine"', 'He said "it\'s fine"'],
        ];
    }

    #[DataProvider('noSafeApostropheProvider')]
    public function testXlPreservesQuotesWhenNoSafeApostrophe(
        string $input,
        string $expected
    ): void {
        // With translate_no_safe_apostrophe = true, xl() does NOT convert
        // quotes/apostrophes — they pass through unchanged.
        $GLOBALS['disable_translation'] = true;
        $GLOBALS['translate_no_safe_apostrophe'] = true;

        $this->assertSame($expected, xl($input));
    }

    public function testXlStripsNewlinesAndCRWhenTranslationDisabled(): void
    {
        $GLOBALS['disable_translation'] = true;
        unset($GLOBALS['translate_no_safe_apostrophe']);

        $input = "line1\r\nline2\nline3";
        $this->assertSame('line1 line2 line3', xl($input));
    }
}
