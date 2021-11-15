<?php

namespace Academe\AuthorizeNet\Response\Model;

/**
 * Single Response message.
 * This is the top level of the response, not a message you would find
 * within a transacton response.
 */

use Academe\AuthorizeNet\Response\HasDataTrait;
use Academe\AuthorizeNet\AbstractModel;

class PrePaidCard extends AbstractModel
{
    use HasDataTrait;

    protected $requestedAmount;
    protected $approvedAmount;
    protected $balanceOnCard;

    public function __construct($data)
    {
        $this->setData($data);

        $this->setRequestedAmount($this->getDataValue('requestedAmount'));
        $this->setApprovedAmount($this->getDataValue('approvedAmount'));
        $this->setBalanceOnCard($this->getDataValue('balanceOnCard'));
    }

    public function jsonSerialize()
    {
        $data = [
            'requestedAmount' => $this->getRequestedAmount(),
            'approvedAmount' => $this->getApprovedAmount(),
            'balanceOnCard' => $this->getBalanceOnCard(),
        ];

        return $data;
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
