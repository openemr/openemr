<?php

namespace Academe\AuthorizeNet\Response;

/**
 * Generic response class that any response data can be thrown into.
 *
 * TODO: fields:
 * [ ] clientId
 */

use Academe\AuthorizeNet\Response\Collections\Messages;
use Academe\AuthorizeNet\Response\Model\TransactionResponse;
use Academe\AuthorizeNet\Response\Model\Transaction;
use Academe\AuthorizeNet\AbstractModel;

class Response extends AbstractModel
{
    use HasDataTrait;

    /**
     * Top-level response result code values.
     */
    const RESULT_CODE_OK    = 'Ok';
    const RESULT_CODE_ERROR = 'Error';

    protected $refId;
    protected $messages;
    protected $transaction;
    protected $transactionResponse;
    protected $token;

    // TODO: for "Decrypt Visa Checkout Data":
    // shippingInfo
    // billingInfo
    // cardInfo
    // paymentDetails
    //
    // TODO: for getUnsettledTransactionListResponse
    // transactions (collection of Transaction models)
    //
    // TODO: for Create a Subscription
    // subscriptionId (single ID)
    // profile (class, but several different forms to merge)
    // subscription (class)
    // status
    //
    // totalNumInResultSet
    // subscriptionDetails (collection of subscriptionDetails)
    //
    // customerProfileId
    // customerPaymentProfileIdList (collection)
    // customerShippingAddressIdList (collection)
    // validationDirectResponseList (collection)
    //
    // ids (collection of customer profile IDs)
    //
    // customerPaymentProfileId
    // validationDirectResponse (string)
    // defaultPaymentProfile (boolean)
    //
    // paymentProfile (single)
    // subscriptionIds (collection)
    // paymentProfiles (collection)
    //
    // directResponse
    //
    // oh, and it goes on, for page after page of copy-paste documentation

    /**
     * The overall response result code.
     * 'Ok' or 'Error'.
     */
    protected $resultCode;

    /**
     * Feed in the raw data structure (array or nested objects).
     */
    public function __construct($data)
    {
        $this->setData($data);

        $this->setRefId($this->getDataValue('refId'));

        // There is one top-level result code, but dropped one
        // level down into the messages.
        $this->setResultCode($this->getDataValue('messages.resultCode'));

        // Messages should always be at the top level.
        if ($messages = $this->getDataValue('messages')) {
            $this->setMessages(new Messages($messages));
        }

        // Response to creating an authorisation (authOnly), purchase (authCapture)
        // or capture (priorAuthCapture).
        if ($transactionResponse = $this->getDataValue('transactionResponse')) {
            $this->setTransactionResponse(new TransactionResponse($transactionResponse));
        }

        if ($transaction = $this->getDataValue('transaction')) {
            $this->setTransaction(new Transaction($transaction));
        }

        // Response to the Hosted Payment Page Request.
        if ($token = $this->getDataValue('token')) {
            $this->setToken($token);
        }
    }

    /**
     * Note this does not attempt to rebuild the response data in its
     * original form, but instead aims to collect all the data in the
     * class structure for logging.
     */
    public function jsonSerialize()
    {
        $data = [
            'refId' => $this->getRefId(),
            'resultCode' => $this->getResultCode(),
        ];

        if ($messages = $this->getMessages()) {
            $data['messages'] = $messages;
        }

        if ($transactionResponse = $this->getTransactionResponse()) {
            $data['transactionResponse'] = $transactionResponse;
        }

        if ($transaction = $this->getTransaction()) {
            $data['transaction'] = $transaction;
        }

        if ($token = $this->getToken()) {
            $data['token'] = $token;
        }

        return $data;
    }

    protected function setRefId($value)
    {
        $this->refId = $value;
    }

    protected function setMessages(Messages $value)
    {
        $this->messages = $value;
    }

    protected function setTransactionResponse(TransactionResponse $value)
    {
        $this->transactionResponse = $value;
    }

    protected function setTransaction(Transaction $value)
    {
        $this->transaction = $value;
    }

    public function setResultCode($value)
    {
        $this->resultCode = $value;
    }

    /**
     * The token identifies a Hosted Page.
     * Will be valid for 15 minutes from creation.
     */
    public function setToken($value)
    {
        $this->token = $value;
    }
}
