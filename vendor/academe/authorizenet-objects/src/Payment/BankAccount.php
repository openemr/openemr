<?php

namespace Academe\AuthorizeNet\Payment;

/**
 * TODO: protect the data from var_dump
 */

use Academe\AuthorizeNet\PaymentInterface;
use Academe\AuthorizeNet\AbstractModel;

class BankAccount extends AbstractModel implements PaymentInterface
{
    const ACCOUNT_TYPE_CHECKING = 'checking';
    const ACCOUNT_TYPE_SAVINGS = 'savings';
    const ACCOUNT_TYPE_BUSINESSCHECKING = 'businessChecking';

    const ECHECK_TYPE_PPD = 'PPD';
    const ECHECK_TYPE_WEB = 'WEB';
    const ECHECK_TYPE_CCD = 'CCD';
    const ECHECK_TYPE_TEL = 'TEL';
    const ECHECK_TYPE_ARC = 'ARC';
    const ECHECK_TYPE_BOC = 'BOC';

    // Masking character for account number and routing number.
    protected $masking_character = 'X';

    protected $accountType;
    protected $routingNumber;
    protected $accountNumber;
    protected $nameOnAccount;
    protected $echeckType;
    protected $bankName;
    protected $checkNumber;

    // FIXME: the API is reporting that the accountType, routingNumber, accountNumber
    // and nameOnAccount are all mandatory, but the specs do not mention this. So some
    // mandatory parameter may be coming to this constructor soon.
    public function __construct()
    {
        parent::__construct();
    }

    public function jsonSerialize()
    {
        $data = [];

        if ($this->hasAccountType()) {
            $data['accountType'] = $this->getAccountType();
        }

        if ($this->hasRoutingNumber()) {
            $data['routingNumber'] = $this->getRoutingNumber();
        }

        if ($this->hasAccountNumber()) {
            $data['accountNumber'] = $this->getAccountNumber();
        }

        if ($this->hasNameOnAccount()) {
            $data['nameOnAccount'] = $this->getNameOnAccount();
        }

        if ($this->hasEcheckType()) {
            $getEcheckType = $this->getEcheckType();

            $checkNumberRequired = (
                $getEcheckType == static::ECHECK_TYPE_ARC || $getEcheckType == static::ECHECK_TYPE_BOC
            );

            if ($checkNumberRequired && ! $this->hasCheckNumber()) {
                // The check number is missing when it is required.
                throw new \InvalidArgumentException(sprintf(
                    'The checkNumber is required when the echeckType is "%s"; the checkNumber is not set',
                    $getEcheckType
                ));
            }

            $data['echeckType'] = $getEcheckType;
        }

        if ($this->hasBankName()) {
            $data['bankName'] = $this->getBankName();
        }

        if ($this->hasCheckNumber()) {
            $data['checkNumber'] = $this->getCheckNumber();
        }

        return $data;
    }

    /**
     * The account number with all but the last four digits masked.
     */

    public function getAccountNumberMasked()
    {
        return str_repeat($this->masking_character, strlen($this->getAccountNumber()) - 4)
            . substr($this->getAccountNumber(), -4);
    }

    /**
     * The routing number with all but the last four digits masked.
     */

    public function getRoutingNumberMasked()
    {
        return str_repeat($this->masking_character, strlen($this->getRoutingNumber()) - 4)
            . substr($this->getRoutingNumber(), -4);
    }

    protected function setAccountType($value)
    {
        $this->assertValueAccountType($value);
        $this->accountType = $value;
    }

    protected function setRoutingNumber($value)
    {
        $this->routingNumber = $value;
    }

    protected function setAccountNumber($value)
    {
        $this->accountNumber = $value;
    }

    // 22-Character Maximum
    protected function setNameOnAccount($value)
    {
        $this->nameOnAccount = $value;
    }

    protected function setEcheckType($value)
    {
        $this->assertValueEcheckType($value);
        $this->echeckType = $value;
    }

    // required when echeckType is ARC or BOC
    protected function setCheckNumber($value)
    {
        $this->checkNumber = $value;
    }
}
