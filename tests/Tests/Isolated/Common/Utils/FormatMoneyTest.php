<?php

/**
 * Isolated FormatMoney Test
 *
 * Tests currency formatting with injectable globals.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Common\Utils;

use OpenEMR\Common\Utils\FormatMoney;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;

class FormatMoneyTest extends TestCase
{
    private function createGlobals(
        int $decimals = 2,
        string $decPoint = '.',
        string $thousandsSep = ',',
        string $symbol = '$'
    ): ParameterBag {
        return new ParameterBag([
            'currency_decimals' => $decimals,
            'currency_dec_point' => $decPoint,
            'currency_thousands_sep' => $thousandsSep,
            'gbl_currency_symbol' => $symbol,
        ]);
    }

    public function testGetFormattedMoneyWithDefaults(): void
    {
        $globals = $this->createGlobals();

        $result = FormatMoney::getFormattedMoney(1234.56, false, $globals);

        $this->assertSame('1,234.56', $result);
    }

    public function testGetFormattedMoneyWithSymbol(): void
    {
        $globals = $this->createGlobals();

        $result = FormatMoney::getFormattedMoney(1234.56, true, $globals);

        $this->assertSame('$ 1,234.56', $result);
    }

    public function testGetFormattedMoneyWithEuroFormat(): void
    {
        $globals = $this->createGlobals(
            decimals: 2,
            decPoint: ',',
            thousandsSep: '.',
            symbol: '€'
        );

        $result = FormatMoney::getFormattedMoney(1234.56, true, $globals);

        $this->assertSame('€ 1.234,56', $result);
    }

    public function testGetFormattedMoneyWithNoDecimals(): void
    {
        $globals = $this->createGlobals(decimals: 0);

        $result = FormatMoney::getFormattedMoney(1234.56, false, $globals);

        $this->assertSame('1,235', $result);
    }

    public function testGetFormattedMoneyWithThreeDecimals(): void
    {
        $globals = $this->createGlobals(decimals: 3);

        $result = FormatMoney::getFormattedMoney(1234.5678, false, $globals);

        $this->assertSame('1,234.568', $result);
    }

    public function testGetFormattedMoneyWithNoThousandsSeparator(): void
    {
        $globals = $this->createGlobals(thousandsSep: '');

        $result = FormatMoney::getFormattedMoney(1234567.89, false, $globals);

        $this->assertSame('1234567.89', $result);
    }

    public function testGetFormattedMoneyWithEmptySymbolDoesNotPrepend(): void
    {
        $globals = $this->createGlobals(symbol: '');

        $result = FormatMoney::getFormattedMoney(100.00, true, $globals);

        $this->assertSame('100.00', $result);
    }

    public function testGetFormattedMoneyWithZeroAmount(): void
    {
        $globals = $this->createGlobals();

        $result = FormatMoney::getFormattedMoney(0, false, $globals);

        $this->assertSame('0.00', $result);
    }

    public function testGetFormattedMoneyWithNegativeAmount(): void
    {
        $globals = $this->createGlobals();

        $result = FormatMoney::getFormattedMoney(-1234.56, false, $globals);

        $this->assertSame('-1,234.56', $result);
    }

    public function testGetFormattedMoneyWithNegativeAmountAndSymbol(): void
    {
        $globals = $this->createGlobals();

        $result = FormatMoney::getFormattedMoney(-1234.56, true, $globals);

        $this->assertSame('$ -1,234.56', $result);
    }

    public function testGetFormattedMoneyWithStringAmount(): void
    {
        $globals = $this->createGlobals();

        $result = FormatMoney::getFormattedMoney('1234.56', false, $globals);

        $this->assertSame('1,234.56', $result);
    }

    public function testGetFormattedMoneyWithNullAmount(): void
    {
        $globals = $this->createGlobals();

        $result = FormatMoney::getFormattedMoney(null, false, $globals);

        $this->assertSame('0.00', $result);
    }

    public function testGetFormattedMoneyWithEmptyStringAmount(): void
    {
        $globals = $this->createGlobals();

        $result = FormatMoney::getFormattedMoney('', false, $globals);

        $this->assertSame('0.00', $result);
    }

    public function testGetFormattedMoneyWithLargeAmount(): void
    {
        $globals = $this->createGlobals();

        $result = FormatMoney::getFormattedMoney(1234567890.12, true, $globals);

        $this->assertSame('$ 1,234,567,890.12', $result);
    }

    public function testGetFormattedMoneyWithSmallAmount(): void
    {
        $globals = $this->createGlobals();

        $result = FormatMoney::getFormattedMoney(0.01, false, $globals);

        $this->assertSame('0.01', $result);
    }

    public function testGetBucksWithAmount(): void
    {
        $globals = $this->createGlobals();

        $result = FormatMoney::getBucks(1234.56, $globals);

        $this->assertSame('1,234.56', $result);
    }

    public function testGetBucksWithZeroReturnEmpty(): void
    {
        $globals = $this->createGlobals();

        $result = FormatMoney::getBucks(0, $globals);

        $this->assertSame('', $result);
    }

    public function testGetBucksWithNullReturnEmpty(): void
    {
        $globals = $this->createGlobals();

        $result = FormatMoney::getBucks(null, $globals);

        $this->assertSame('', $result);
    }

    public function testGetBucksWithEmptyStringReturnEmpty(): void
    {
        $globals = $this->createGlobals();

        $result = FormatMoney::getBucks('', $globals);

        $this->assertSame('', $result);
    }

    public function testGetBucksWithFalseReturnEmpty(): void
    {
        $globals = $this->createGlobals();

        $result = FormatMoney::getBucks(false, $globals);

        $this->assertSame('', $result);
    }

    public function testGetBucksWithNegativeAmount(): void
    {
        $globals = $this->createGlobals();

        $result = FormatMoney::getBucks(-50.00, $globals);

        $this->assertSame('-50.00', $result);
    }

    public function testGetFormattedMoneyWithSwissFrancFormat(): void
    {
        // Swiss format: 1'234.56 CHF
        $globals = $this->createGlobals(
            decimals: 2,
            decPoint: '.',
            thousandsSep: "'",
            symbol: 'CHF'
        );

        $result = FormatMoney::getFormattedMoney(1234.56, true, $globals);

        $this->assertSame("CHF 1'234.56", $result);
    }

    public function testGetFormattedMoneyWithIndianFormat(): void
    {
        // Note: PHP's number_format doesn't support Indian grouping (12,34,567.89)
        // so this just tests standard comma grouping with rupee symbol
        $globals = $this->createGlobals(
            decimals: 2,
            decPoint: '.',
            thousandsSep: ',',
            symbol: '₹'
        );

        $result = FormatMoney::getFormattedMoney(1234567.89, true, $globals);

        $this->assertSame('₹ 1,234,567.89', $result);
    }
}
