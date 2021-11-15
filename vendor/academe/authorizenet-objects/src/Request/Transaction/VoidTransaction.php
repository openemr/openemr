<?php

namespace Academe\AuthorizeNet\Request\Transaction;

/**
 * Void is pretty simplie: void the transaction and go. There is no other context.
 */

use Academe\AuthorizeNet\TransactionRequestInterface;
use Academe\AuthorizeNet\AbstractModel;

class VoidTransaction extends AbstractModel implements TransactionRequestInterface
{
    protected $objectName = 'transactionRequest';
    protected $transactionType = 'voidTransaction';

    protected $refTransId;

    /**
     *
     */
    public function __construct($refTransId)
    {
        parent::__construct();

        $this->setRefTransId($refTransId);
    }

    public function jsonSerialize()
    {
        $data = [];

        $data['transactionType'] = $this->getTransactionType();

        $data['refTransId'] = $this->getRefTransId();

        return $data;
    }

    /**
     * @param $value string Reference transaction ID
     */
    protected function setRefTransId($value)
    {
        $this->refTransId = $value;
    }
}
