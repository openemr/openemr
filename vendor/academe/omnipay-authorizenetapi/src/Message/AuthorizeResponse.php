<?php

namespace Omnipay\AuthorizeNetApi\Message;

/**
 * The TransactionResponse can contain full details of a transaction
 * creation or fetch result, or errors.
 */

use Academe\AuthorizeNet\Response\Collections\TransactionMessages;
use Academe\AuthorizeNet\Response\Collections\Errors;
use Omnipay\Common\Message\RequestInterface;
use Academe\AuthorizeNet\Response\Response;
use Academe\AuthorizeNet\Response\Model\TransactionResponse as TransactionResponseModel;

class AuthorizeResponse extends AbstractResponse
{
    public function __construct(RequestInterface $request, $data)
    {
        // Parse the request into a structured object.
        parent::__construct($request, $data);
    }

    /**
     * Tells us whether the transaction is successful and complete.
     * There must be no overall error, and the transaction must be approved.
     */
    public function isSuccessful()
    {
        // Note the loose comparison because the API returns strings for
        // all numbers in the JSON response, but integers in the XML response (see
        // https://api.authorize.net/xml/v1/schema/AnetApiSchema.xsd where we have
        // <xs:element name="responseCode" type="xs:int"/>).
        // So I don't trust the data type we get back, and we will play loose and
        // fast with implicit conversions here.

        return parent::isSuccessful()
            && $this->getResponseCode() == TransactionResponseModel::RESPONSE_CODE_APPROVED;
    }

    /**
     * Tells us whether the transaction is pending or not.
     */
    public function isPending()
    {
        return $this->responseIsSuccessful()
            && $this->getResponseCode() == TransactionResponseModel::RESPONSE_CODE_PENDING;
    }

    /**
     * Get the transaction response code.
     * Expected values are one of TransactionResponseModel::RESPONSE_CODE_*
     */
    public function getResponseCode()
    {
        return $this->getValue($this->transactionIndex . '.responseCode');
    }

    /**
     * Collection of transaction message objects, or null if there are none.
     *
     * @returns TransactionMessages
     */
    public function getTransactionMessages()
    {
        return $this->getValue($this->transactionIndex . '.transactionMessages')
            ?: new TransactionMessages();
    }

    /**
     * Collection of transaction errors, or null if none.
     *
     * @returns Errors
     */
    public function getTransactionErrors()
    {
        return $this->getValue($this->transactionIndex . '.errors')
            ?: new Errors();
    }

    /**
     * Return the message code from the transaction if available,
     * or the response envelope.
     */
    public function getCode()
    {
        return $this->getTransactionCode() ?: parent::getCode();
    }

    /**
     * Get the transaction message text if available, falling back
     * to the response envelope.
     */
    public function getMessage()
    {
        return $this->getTransactionMessage() ?: parent::getMessage();
    }

    /**
     * @return string Six characters.
     */
    public function getAuthCode()
    {
        return $this->getValue($this->transactionIndex . '.authCode');
    }

    /**
     * @returns string Single letter; one of TransactionResponse::AVS_RESULT_CODE_*
     */
    public function getAvsResultCode()
    {
        return $this->getValue($this->transactionIndex . '.avsResultCode');
    }

    /**
     * @returns string Single letter; one of TransactionResponse::CVV_RESULT_CODE_*
     */
    public function getCvvResultCode()
    {
        return $this->getValue($this->transactionIndex . '.cvvResultCode');
    }

    /**
     * @returns int One of TransactionResponse::CAVV_RESULT_CODE_*
     */
    public function getCavvResultCode()
    {
        return $this->getValue($this->transactionIndex . '.cavvResultCode');
    }

    /**
     * Related transactionReference
     * @returns string Reference to previous transaction
     */
    public function getRefTransID()
    {
        return $this->getValue($this->transactionIndex . '.refTransID');
    }

    /**
     * @returns string Transaction hash, upper case MD5.
     */
    public function getTransHash()
    {
        return $this->getValue($this->transactionIndex . '.transHash');
    }

    /**
     * @returns string The last four digits of either the card number
     *  or bank account number used for the transaction in the format XXXX1234.
     */
    public function getAccountNumber()
    {
        return $this->getValue($this->transactionIndex . '.accountNumber');
    }

    /**
     * @returns string Either the credit card type or in the case of
     *  eCheck, the value is eCheck.
     */
    public function getAccountType()
    {
        return $this->getValue($this->transactionIndex . '.accountType');
    }
}
