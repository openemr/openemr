<?php

namespace Omnipay\AuthorizeNetApi\Message;

/**
 * Retrieval of a single transaction.
 */

use Academe\AuthorizeNet\Response\Model\TransactionResponse as TransactionResponseModel;
use Academe\AuthorizeNet\Response\Collections\TransactionMessages;
use Academe\AuthorizeNet\Response\Collections\Errors;

class FetchTransactionResponse extends AuthorizeResponse
{
    /**
     * The property the transaction can be found in
     */
    protected $transactionIndex = 'transaction';

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

        return $this->getResponseCode() == TransactionResponseModel::RESPONSE_CODE_APPROVED;
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
     * Tells us whether the transaction is pending or not.
     */
    public function isPending()
    {
        return $this->getResponseCode() == TransactionResponseModel::RESPONSE_CODE_PENDING;
    }

    public function getTransactionType()
    {
        return $this->getValue($this->transactionIndex . '.transactionType');
    }
}
