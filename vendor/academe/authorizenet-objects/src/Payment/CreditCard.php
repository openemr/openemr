<?php

namespace Academe\AuthorizeNet\Payment;

/**
 * TODO: protect the data from var_dump
 */

use Academe\AuthorizeNet\PaymentInterface;
use Academe\AuthorizeNet\AbstractModel;

class CreditCard extends AbstractModel implements PaymentInterface
{
    protected $cardNumber;
    protected $expirationDate;
    protected $cardCode;

    public function __construct($cardNumber, $expirationDate, $cardCode = null)
    {
        parent::__construct();

        $this->setCardNumber($cardNumber);
        $this->setExpirationDate($expirationDate);
        $this->setCardCode($cardCode);
    }

    public function jsonSerialize()
    {
        $data = [
            'cardNumber' => $this->getCardNumber(),
            'expirationDate' => $this->getExpirationDate(),
        ];

        if ($this->hasCardCode()) {
            $data['cardCode'] = $this->getCardCode();
        }

        return $data;
    }

    /**
     * Return just the last four digits of the credit card number.
     */

    public function getLastFourDigits()
    {
        return substr($this->getCardNumber(), -4);
    }

    // TODO: these setters can include validation.

    protected function setCardNumber($value)
    {
        $this->cardNumber = $value;
    }

    /**
     * Set the CC expiration date.
     * TODO: support setting and getting the year and month as separate values.
     * TOOD: accept DateTime object.
     *
     * @param string $value Date in any format: MMYY, MM/YY, MM-YY, MMYYYY, MM/YYYY, MM-YYYY
     */
    protected function setExpirationDate($value)
    {
        $this->expirationDate = $value;
    }

    protected function setCardCode($value)
    {
        $this->cardCode = $value;
    }
}
