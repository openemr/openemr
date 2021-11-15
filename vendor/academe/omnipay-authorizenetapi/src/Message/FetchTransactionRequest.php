<?php

namespace Omnipay\AuthorizeNetApi\Message;

/**
 * Fetch a transaction by transactionReference.
 */

use Academe\AuthorizeNet\Request\GetTransactionDetails;

class FetchTransactionRequest extends AbstractRequest
{
    /**
     * @returns GetTransactionDetails
     */
    public function getData()
    {
        $request = new GetTransactionDetails(
            $this->getAuth(),
            $this->getTransactionReference()
        );

        if ($this->getTransactionId()) {
            $request = $request->withRefId($this->getTransactionId());
        }

        return $request;
    }

    /**
     * Accept a transaction and sends it as a request.
     *
     * @param $data TransactionRequestInterface
     * @returns TransactionResponse
     */
    public function sendData($data)
    {
        // Send the request to the gateway.
        $response_data = $this->sendMessage($data);

        // We should be getting a transactino back.
        return new FetchTransactionResponse($this, $response_data);
    }
}
