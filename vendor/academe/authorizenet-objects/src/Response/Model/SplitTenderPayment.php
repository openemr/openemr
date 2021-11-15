<?php

namespace Academe\AuthorizeNet\Response\Model;

/**
 * Single SplitTenderPayment message.
 */

use Academe\AuthorizeNet\Response\HasDataTrait;
use Academe\AuthorizeNet\AbstractModel;

class SplitTenderPayment extends AbstractModel
{
    use HasDataTrait;

    // TODO: Most of these constants will be defined elsewhere, so can be moved.

    const ACCOUNT_TYPE_VISA             = 'Visa';
    const ACCOUNT_TYPE_MASTERCARD       = 'Mastercard';
    const ACCOUNT_TYPE_DISCOVER         = 'Discover';
    const ACCOUNT_TYPE_AMERICANEXPRESS  = 'AmericanExpress';
    const ACCOUNT_TYPE_DINERSCLUB       = 'DinersClub';
    const ACCOUNT_TYPE_JCB              = 'JCB';

    /**
     * The overall transaction response codes.
     * PEDNING is "Held for Review".
     */
    const RESPONSE_CODE_APPROVED    = 1;
    const RESPONSE_CODE_DECLINED    = 2;
    const RESPONSE_CODE_ERROR       = 3;
    const RESPONSE_CODE_PENDING     = 4;

    protected $transId;
    protected $responseCode;
    protected $responseToCustomer;
    protected $authCode;
    protected $accountNumber;
    protected $accountType;
    protected $requestedAmount;
    protected $approvedAmount;
    protected $balanceOnCard;

    public function __construct($data)
    {
        $this->setData($data);

        $this->setTransId($this->getDataValue('transId'));
        $this->setResponseCode($this->getDataValue('responseCode'));
        $this->setResponseToCustomer($this->getDataValue('responseToCustomer'));
        $this->setAuthCode($this->getDataValue('authCode'));
        $this->setAccountNumber($this->getDataValue('accountNumber'));
        $this->setAccountType($this->getDataValue('accountType'));
        $this->setRequestedAmount($this->getDataValue('requestedAmount'));
        $this->setApprovedAmount($this->getDataValue('approvedAmount'));
        $this->setBalanceOnCard($this->getDataValue('balanceOnCard'));
    }

    public function jsonSerialize()
    {
        $data = [
            'transId' => $this->getTransId(),
            'responseCode' => $this->getResponseCode(),
            'responseToCustomer' => $this->getResponseToCustomer(),
            'authCode' => $this->getAuthCode(),
            'accountNumber' => $this->getAccountNumber(),
            'accountType' => $this->getAccountType(),
        ];

        if ($this->hasRequestedAmount()) {
            $data['requestedAmount'] = $this->getRequestedAmount();
        }

        if ($this->hasApprovedAmount()) {
            $data['approvedAmount'] = $this->getApprovedAmount();
        }

        if ($this->hasBalanceOnCard()) {
            $data['balanceOnCard'] = $this->getBalanceOnCard();
        }

        return $data;
    }

    protected function setTransId($value)
    {
        $this->transId = $value;
    }

    protected function setResponseCode($value)
    {
        $this->responseCode = $value;
    }

    protected function setResponseToCustomer($value)
    {
        $this->responseToCustomer = $value;
    }

    protected function setAuthCode($value)
    {
        $this->authCode = $value;
    }

    protected function setAccountNumber($value)
    {
        $this->accountNumber = $value;
    }

    protected function setAccountType($value)
    {
        $this->accountType = $value;
    }

    protected function setRequestedAmount($value)
    {
        $this->requestedAmount = $value;
    }

    protected function setApprovedAmount($value)
    {
        $this->approvedAmount = $value;
    }

    protected function setBalanceOnCard($value)
    {
        $this->balanceOnCard = $value;
    }
}
