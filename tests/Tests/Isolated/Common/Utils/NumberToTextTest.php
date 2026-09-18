<?php

/**
 * Tests for NumberToText.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Utils;

use NumberToText;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class NumberToTextTest extends TestCase
{
    public function testZero(): void
    {
        self::assertSame('zero', (new NumberToText(0))->convert());
    }

    #[DataProvider('smallNumbersProvider')]
    public function testSmallNumbers(int $input, string $expected): void
    {
        self::assertSame($expected, (new NumberToText($input))->convert());
    }

    /**
     * @return array<string, array{int, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function smallNumbersProvider(): array
    {
        return [
            'one' => [1, 'one'],
            'nineteen' => [19, 'nineteen'],
            'twenty' => [20, 'twenty'],
            'twenty-five' => [25, 'twenty-five'],
            'one hundred' => [100, 'one hundred'],
            'one hundred twenty-five' => [125, 'one hundred twenty-five'],
            'one thousand' => [1000, 'one thousand'],
            'one million' => [1000000, 'one million'],
        ];
    }

    public function testNegative(): void
    {
        self::assertSame('negative five', (new NumberToText(-5))->convert());
    }

    public function testCapitalize(): void
    {
        self::assertSame(
            'Twenty-five',
            (new NumberToText(25, false, true))->convert()
        );
    }

    public function testCurrency(): void
    {
        $result = (new NumberToText('5.25', true))->convert();
        self::assertStringContainsString('dollars', $result);
        self::assertStringContainsString('cents', $result);
    }

    public function testSingularDollar(): void
    {
        $result = (new NumberToText('1.00', true))->convert();
        self::assertStringContainsString('dollar', $result);
        self::assertStringNotContainsString('dollars', $result);
    }

    public function testAndConnector(): void
    {
        $result = (new NumberToText(101, false, false, true))->convert();
        self::assertStringContainsString('and', $result);
    }

    public function testHugeNumberRecursion(): void
    {
        // Number large enough to exceed the 'big' names list (vigintillion = 10^63),
        // exercising the recursive `(new self(...))->convert()` branch.
        $huge = '1' . str_repeat('0', 66);
        $result = (new NumberToText($huge))->convert();
        self::assertStringContainsString('vigintillion', $result);
    }

    public function testDecimalWithoutCurrency(): void
    {
        $result = (new NumberToText('3.14'))->convert();
        self::assertStringContainsString('point', $result);
        self::assertStringContainsString('one', $result);
        self::assertStringContainsString('four', $result);
    }
}
