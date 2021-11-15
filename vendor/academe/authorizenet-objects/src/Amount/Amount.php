<?php

namespace Academe\AuthorizeNet\Amount;

/**
 * Value object for the amount, in the appropriate currency.
 * This object does not use any third-party packages to represent the amount.
 */

use Academe\AuthorizeNet\AmountInterface;
use Academe\AuthorizeNet\AbstractModel;
use UnexpectedValueException;
use Exception;

class Amount extends AbstractModel implements AmountInterface
{
    /**
     * @var Integer value in the minor units
     */
    protected $amount;

    /**
     * @var ISO???? currency code.
     */
    protected $currencyCode;

    /**
     * @param string $currency Just a few currencies supported with this object.
     * @param int $amount Minor unit total amount, with no decimal part <-- FIXME support multiple formats.
     */
    public function __construct($currencyCode, $amount = 0)
    {
        parent::__construct();

        $this->setCurrencyCode($currencyCode);
        $this->setMinorUnit($amount);
    }

    /**
     * Allow the decimal notation of the currency to be supplied,
     * as a float or a string.
     *
     * @param float|string|int $amount Total amount as major units and fractions of major units
     *
     * @return Amount Clone of $this with a newamount set
     */
    protected function setMajorUnit($amount)
    {
        if (is_int($amount) || is_float($amount) || (is_string($amount) && preg_match('/^[0-9]*\.[0-9]*$/', $amount))) {
            // FIXME: don't go through the float intermediate value 'cause rounding errors.
            $amount = (float)$amount * pow(10, $this->getDecimals());

            if (floor($amount) != round($amount, 5)) {
                // Too many decimal digits for the currency.
                throw new UnexpectedValueException(sprintf(
                    'Amount has too many decimal places. Calculated minor unit %f should be an integer.',
                    $amount
                ));
            }

            $this->setMinorUnit((int)$amount);
        } else {
            throw new UnexpectedValueException(sprintf(
                'Major unit is not a number.'
            ));
        }
    }

    /**
     * Set the minot unit.
     *
     * @param int|string $amount An amount in minor units, with no decimal part
     */
    protected function setMinorUnit($amount)
    {
        if (is_int($amount) || (is_string($amount) && preg_match('/^[0-9]+$/', $amount))) {
            $this->amount = (int)$amount;
        } else {
            throw new UnexpectedValueException(sprintf(
                'Minor unit is not an integer.'
            ));
        }
    }

    /**
     * Magic method to support e.g. $amount = Amount::EUR(995)
     * equivalent to: new Amount(new Currency('EUR'), 995)
     *
     * @param string $name The three-letter ISO currency code
     * @param array $arguments [0] = required amount
     *
     * @return static New instance of an Amount
     *
     * @throws Exception
     */
    public static function __callStatic($name, array $arguments)
    {
        try {
            $currency = new Currency($name);
        } catch (UnexpectedValueException $e) {
            $trace = debug_backtrace();
            throw new Exception(sprintf(
                'Call to undefined method $class::%s() in %s on line %d',
                get_called_class(),
                $trace[0]['file'],
                $trace[0]['line']
            ));
        }

        if (isset($arguments[0])) {
            return new static($currency, $arguments[0]);
        } else {
            return new static($currency);
        }
    }

    public function jsonSerialize()
    {
        return $this->getFormatted();
    }

    /**
     * @return string The amount, in major units, zero-padded decimals
     */
    public function getFormatted()
    {
        // FIXME!!!
        return number_format($this->amount / (pow(10, $this->getDecimals())), $this->getDecimals(), '.', '');
    }

    public function setCurrencyCode($value)
    {
        $this->currencyCode = $value;
    }

    /**
     * @return string The currency three-character ISO code
     */
    public function getCurrencyCode()
    {
        return $this->currencyCode;
    }

    public function getDecimals()
    {
        // Just hack it for now.
        return 2;
    }
}
