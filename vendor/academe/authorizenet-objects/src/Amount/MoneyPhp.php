<?php

namespace Academe\AuthorizeNet\Amount;

/**
 * Value object for the amount, wrapping the moneyphp/money package.
 * Both v1.3 and v3.x (in alpha) should work.
 *
 * moneyphp/money is an optional package, so must be required manually if you want
 * to use it.
 */

use Academe\AuthorizeNet\AmountInterface;
use Academe\AuthorizeNet\AbstractModel;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Currencies\ISOCurrencies;
use Money\Money;

class MoneyPhp extends AbstractModel implements AmountInterface
{
    protected $money;

    /**
     * MoneyAmount constructor.
     * @param Money $money
     */
    public function __construct(Money $money)
    {
        $this->setMoney($money);
    }

    /**
     * @return string Amount formatted as decimal major and minor units.
     */
    public function getFormatted()
    {
        $currencies = new ISOCurrencies();
        $moneyFormatter = new DecimalMoneyFormatter($currencies);

        return $moneyFormatter->format($this->money);
    }

    /**
     * @return mixed
     */
    public function getCurrencyCode()
    {
        $currency = $this->money->getCurrency();

        // To support Money ~1.x and ~3.x
        if (method_exists($currency, 'getCode')) {
            return $currency->getCode();
        } else {
            return $currency->getName();
        }
    }

    public function jsonSerialize()
    {
        return $this->getFormatted();
    }

    public function setMoney(Money $value)
    {
        $this->money = $value;
    }
}
