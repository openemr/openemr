<?php

namespace Academe\AuthorizeNet\Request;

/**
 * Request to fetch the details of an existing transaction.
 */

use Academe\AuthorizeNet\Auth\MerchantAuthentication;

class GetTransactionDetails extends AbstractRequest
{
    protected $refId;
    protected $transId;

    /**
     * @param MerchantAuthentication $merchantAuthentication
     * @param string transId The gateway ID of the transaction we want to fetch.
     */
    public function __construct(
        MerchantAuthentication $merchantAuthentication,
        $transId
    ) {
        parent::__construct($merchantAuthentication);

        $this->setTransId($transId);
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

        // The transId is the unique key to identify the transaction to fetch.
        $data['transId'] = $this->getTransId();

        // Wrap it all up in a single element.
        // The JSON structure mimics the XML structure, so all the messages will be
        // in an object with a single property.
        return [
            $this->getObjectName() => $data,
        ];
    }

    /**
     * @param string $refId Merchant-assigned reference ID for the request.
     */
    protected function setRefId($value)
    {
        $this->refId = $value;
    }

    /**
     * If included in the request, this value is included in the response.
     * This feature might be especially useful for multi-threaded applications.
     *
     * @param string $transId The Authorize.Net assigned identification number for a transaction.
     */
    protected function setTransId($value)
    {
        $this->transId = $value;
    }
}
