<?php

namespace Academe\AuthorizeNet\Amount;

/**
 *
 */

use PHPUnit\Framework\TestCase;

use Money\Money;

class MoneyPhpTest extends TestCase
{
    /**
     * @dataProvider someFormats
     */
    public function testFormat($minorUnit, $expectedFormat)
    {
        $money = Money::GBP($minorUnit);

        $moneyPhp = new MoneyPhp($money);

        $this->assertSame($expectedFormat, $moneyPhp->getFormatted());
    }

    /**
     * Check the leading and traiing zeros especially, so all our monetary
     * amounts are formatted consistently.
     */
    public function someFormats()
    {
        return [
            [123,   '1.23'],
            [120,   '1.20'],
            [100,   '1.00'],
            [10,    '0.10'],
            [1,     '0.01'],
            [9999, '99.99'],
        ];
    }
}
