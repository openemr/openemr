<?php

/**
 * Tests for the EDI history date/money formatting helper.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing\EdiHistory;

use OpenEMR\Billing\EdiHistory\EdiFormat;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class EdiFormatTest extends TestCase
{
    #[DataProvider('dateProvider')]
    public function testDate(string $input, string $pref, string $expected): void
    {
        self::assertSame($expected, EdiFormat::date($input, $pref));
    }

    /**
     * @return array<string, array{string, string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function dateProvider(): array
    {
        return [
            // Eight-digit input: reordered, no century expansion.
            'eight-digit ISO' => ['20240115', 'Y-m-d', '2024-01-15'],
            'eight-digit US' => ['20240115', 'US', '01/15/2024'],
            // Six-digit input is expanded to eight using the current century
            // (20xx until the year 2100), then reordered.
            'six-digit ISO' => ['240115', 'Y-m-d', '2024-01-15'],
            // Non-digit characters are stripped before formatting, so an
            // already-separated date round-trips through the ISO path.
            'separators stripped' => ['2024/01/15', 'Y-m-d', '2024-01-15'],
            // Any pref other than the literal 'US' takes the ISO branch.
            'unknown pref is ISO' => ['20240115', 'anything', '2024-01-15'],
            // Legacy quirk preserved from the original edih_format_date():
            // a six-digit value under the US preference expands with the
            // wrong digit ordering. Locked here so the refactor stays
            // behavior-preserving, not because the result is meaningful.
            'six-digit US legacy quirk' => ['240115', 'US', '24/01/2015'],
        ];
    }

    public function testDateDefaultPrefIsIso(): void
    {
        self::assertSame('2024-01-15', EdiFormat::date('20240115'));
    }

    public function testDateSixDigitUsesCurrentCentury(): void
    {
        $century = substr(date('Ymd'), 0, 2);
        self::assertSame($century . '24-01-15', EdiFormat::date('240115'));
    }

    #[DataProvider('moneyProvider')]
    public function testMoney(string $input, string $expected): void
    {
        self::assertSame($expected, EdiFormat::money($input));
    }

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function moneyProvider(): array
    {
        return [
            'empty passes through' => ['', ''],
            'zero formats' => ['0', '$0.00'],
            'one decimal place padded' => ['150.5', '$150.50'],
            'integer amount' => ['42', '$42.00'],
            'rounds to two places' => ['1234.567', '$1234.57'],
            'negative amount' => ['-5', '$-5.00'],
        ];
    }

    public function testConstructorIsPrivate(): void
    {
        $ctor = (new \ReflectionClass(EdiFormat::class))->getConstructor();
        self::assertNotNull($ctor);
        self::assertTrue($ctor->isPrivate());
    }
}
