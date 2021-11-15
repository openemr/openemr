<?php

namespace Academe\AuthorizeNet\Request;

/**
 * This is the most commonly used message, which will carry many types of
 * transactions, including authorisation, capture, void and refund.
 * The transactions can all be found under Academe\AuthorizeNet\Request\Transaction
 */

use Academe\AuthorizeNet\TransactionRequestInterface;
use Academe\AuthorizeNet\Auth\MerchantAuthentication;

class CreateTransaction extends AbstractRequest
{
    protected $refId;
    protected $transactionRequest;

    public function __construct(
        MerchantAuthentication $merchantAuthentication,
        TransactionRequestInterface $transactionRequest
    ) {
        parent::__construct($merchantAuthentication);

        $this->setTransactionRequest($transactionRequest);
    }

    public function jsonSerialize()
    {
        $data = [];

        // Start with the authentication details.
        $data[$this->getMerchantAuthentication()->getObjectName()] = $this->getMerchantAuthentication();

        // Then the optional merchant site reference ID (will be returned in the response,
        // useful for multithreaded applications).
        if ($this->hasRefId()) {
            $data['refId'] = $this->getRefId();
        }

        // Add the expanded tranasation.
        $data[$this->getTransactionRequest()->getObjectName()] = $this->transactionRequest;

        // Wrap it all up in a single element.
        // The JSON structure mimics the XML structure, so all the messages will be
        // in an object with a single property.
        return [
            $this->getObjectName() => $data,
        ];
    }

    // TODO: these setters can include validation.

    protected function setRefId($value)
    {
        $this->refId = $value;
    }

    protected function setTransactionRequest(TransactionRequestInterface $value)
    {
        $this->transactionRequest = $value;
    }
}
