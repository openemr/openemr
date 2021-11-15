<?php

namespace Academe\AuthorizeNet\Response\Model;

/**
 * Fields TODO:
 * [ ] batch (batch object)
 * [ ] FDSFilterAction
 * [ ] FDSFilters - collection of filters
 * [ ] order (invoice number and description and purchaseOrderNumber - model in request)
 * [ ] requestedAmount
 * [ ] authAmount - this is provided as a float!
 * [ ] settleAmount - this is provided as a float!
 * [x] lineitems - model in the request
 * [ ] taxExempt - boolean
 * [ ] payment - card/expiry/type model
 * [ ] tax (amount)
 * [ ] duty (amount)
 * [ ] shipping (amount)
 * [ ] prepaidBalanceRemaining
 * [ ] taxExempt
 * [ ] customer (type, id, email)
 * [ ] billTo
 * [ ] shipTo
 * [ ] recurringBilling - boolean
 * [ ] returnedItems - collection of item models
 * [ ] customerIP
 * [ ] product
 * [ ] marketType - list of values in the request
 * [ ] solution (model)
 * [ ] subscription (model)
 * [ ] mobileDeviceId
 * [ ] profile
 */

use Academe\AuthorizeNet\Response\Collections\LineItems;
use Academe\AuthorizeNet\Amount\Simple as SimpleAmount;

class Transaction extends TransactionResponse
{
    /**
     * transactionStatus values
     * The status of the transaction.
     */
    const TRANSACTION_STATUS_AUTHORIZEDPENDINGCAPTURE   = 'authorizedPendingCapture';
    const TRANSACTION_STATUS_CAPTUREDPENDINGSETTLEMENT  = 'capturedPendingSettlement';
    const TRANSACTION_STATUS_COMMUNICATIONERROR         = 'communicationError';
    const TRANSACTION_STATUS_REFUNDSETTLEDSUCCESSFULLY  = 'refundSettledSuccessfully';
    const TRANSACTION_STATUS_REFUNDPENDINGSETTLEMENT    = 'refundPendingSettlement';
    const TRANSACTION_STATUS_APPROVEDREVIEW             = 'approvedReview';
    const TRANSACTION_STATUS_DECLINED                   = 'declined';
    const TRANSACTION_STATUS_COULDNOTVOID               = 'couldNotVoid';
    const TRANSACTION_STATUS_EXPIRED                    = 'expired';
    const TRANSACTION_STATUS_GENERALERROR               = 'generalError';
    const TRANSACTION_STATUS_FAILEDREVIEW               = 'failedReview';
    const TRANSACTION_STATUS_SETTLEDSUCCESSFULLY        = 'settledSuccessfully';
    const TRANSACTION_STATUS_SETTLEMENTERROR            = 'settlementError';
    const TRANSACTION_STATUS_UNDERREVIEW                = 'underReview';
    const TRANSACTION_STATUS_VOIDED                     = 'voided';
    const TRANSACTION_STATUS_FDSPENDINGREVIEW           = 'FDSPendingReview';
    const TRANSACTION_STATUS_FDSAUTHORIZEDPENDINGREVIEW = 'FDSAuthorizedPendingReview';
    const TRANSACTION_STATUS_RETURNEDITEM               = 'returnedItem';

    protected $submitTimeUTC;
    protected $submitTimeLocal;
    protected $transactionType;
    protected $transactionStatus;
    protected $responseReasonCode;
    protected $responseReasonDescription;
    protected $avsResponse;
    protected $cardCodeResponse;
    protected $cavvResponse;
    protected $lineItems;

    public function __construct($data)
    {
        parent::__construct($data);

        if ($submitTimeUTC = $this->getDataValue('submitTimeUTC')) {
            $this->setSubmitTimeUTC($submitTimeUTC);
        }

        if ($submitTimeLocal = $this->getDataValue('submitTimeLocal')) {
            $this->setSubmitTimeLocal($submitTimeLocal);
        }

        if ($transactionType = $this->getDataValue('transactionType')) {
            $this->setTransactionType($transactionType);
        }

        if ($transactionStatus = $this->getDataValue('transactionStatus')) {
            $this->setTransactionStatus($transactionStatus);
        }

        if ($responseReasonCode = $this->getDataValue('responseReasonCode')) {
            $this->setResponseReasonCode($responseReasonCode);
        }

        if ($responseReasonDescription = $this->getDataValue('responseReasonDescription')) {
            $this->setResponseReasonDescription($responseReasonDescription);
        }

        if ($avsResponse = $this->getDataValue('AVSResponse')) {
            $this->setAvsResponse($avsResponse);
        }

        if ($cardCodeResponse = $this->getDataValue('cardCodeResponse')) {
            $this->setCardCodeResponse($cardCodeResponse);
        }

        if ($cavvResponse = $this->getDataValue('cavvResponse')) {
            $this->setCavvResponse($cavvResponse);
        }

        if ($lineItemsData = $this->getDataValue('lineItems')) {
            $lineItems = new LineItems();

            // Now, here is a quandry. The unitPrice is expected as a Money
            // value, which needs a currency to be complete.
            // The transaction does not have a currency in its results (really)
            // so we don't *really* know what currency was involved.

            foreach ($lineItemsData as $key => $lineItemData) {
                $lineItem = new LineItem(
                    $this->getDataValue("lineItems.{$key}.itemId"),
                    $this->getDataValue("lineItems.{$key}.name"),
                    $this->getDataValue("lineItems.{$key}.description"),
                    $this->getDataValue("lineItems.{$key}.quantity"),
                    new SimpleAmount($this->getDataValue("lineItems.{$key}.unitPrice")),
                    $this->getDataValue("lineItems.{$key}.taxable")
                );

                $lineItems->push($lineItem);
            }

            $this->setLineItems($lineItems);
        }
    }

    public function jsonSerialize()
    {
        $data = parent::jsonSerialize() ?: [];

        if ($submitTimeUTC = $this->getSubmitTimeUTC()) {
            $data['submitTimeUTC'] = $submitTimeUTC;
        }

        if ($submitTimeLocal = $this->getSubmitTimeLocal()) {
            $data['submitTimeLocal'] = $submitTimeLocal;
        }

        if ($transactionType = $this->getTransactionType()) {
            $data['transactionType'] = $transactionType;
        }

        if ($transactionStatus = $this->getTransactionStatus()) {
            $data['transactionStatus'] = $transactionStatus;
        }

        if ($responseReasonCode = $this->getResponseReasonCode()) {
            $data['responseReasonCode'] = $responseReasonCode;
        }

        if ($responseReasonDescription = $this->getResponseReasonDescription()) {
            $data['responseReasonDescription'] = $responseReasonDescription;
        }

        if ($avsResponse = $this->getAvsResponse()) {
            $data['avsResponse'] = $avsResponse;
        }

        if ($cardCodeResponse = $this->getCardCodeResponse()) {
            $data['cardCodeResponse'] = $cardCodeResponse;
        }

        if ($cavvResponse = $this->getCavvResponse()) {
            $data['cavvResponse'] = $cavvResponse;
        }

        if ($lineItems = $this->getLineItems()) {
            $data['lineItems'] = $lineItems;
        }

        return $data;
    }

    /**
     * Date and time the transaction was submitted.
     * The T character separates the date from the time.
     * This element returns the time as Universal Time (UTC).
     *
     * TODO: convert this is a DateTime or Carbon object
     */
    protected function setSubmitTimeUTC($value)
    {
        $this->submitTimeUTC = $value;
    }

    /**
     * Date and time the transaction was submitted.
     * The T character separates the date from the time.
     * This element returns the time in the merchant’s local time
     * zone as set in the Merchant Interface.
     * Format: YYYY-MM-DDThh:mm:ss
     */
    protected function setSubmitTimeLocal($value)
    {
        $this->submitTimeLocal = $value;
    }

    /**
     * The type of transaction that was originally submitted.
     * Either authCaptureTransaction, authOnlyTransaction,
     * captureOnlyTransaction, or refundTransaction.
     */
    protected function setTransactionType($value)
    {
        $this->transactionType = $value;
    }

    /**
     * The overall status of the transaction.
     * One of static::TRANSACTION_STATUS_*
     */
    protected function setTransactionStatus($value)
    {
        $this->transactionStatus = $value;
    }

    protected function setResponseReasonCode($value)
    {
        $this->responseReasonCode = $value;
    }

    protected function setResponseReasonDescription($value)
    {
        $this->responseReasonDescription = $value;
    }

    /**
     * This is avsResultCode in the original transaction creation response.
     * There are a few fields like this that change name between APIs.
     */
    protected function setAvsResponse($value)
    {
        $this->avsResponse = $value;
    }

    /**
     * An alias for the AvsResultCode provides some consistency.
     */
    protected function getAvsResultCode()
    {
        return $this->getAvsResponse();
    }

    /**
     * This is cvvResultCode in the original transaction creation response.
     * There are a few fields like this that change name between APIs.
     */
    protected function setCardCodeResponse($value)
    {
        $this->cardCodeResponse = $value;
    }

    /**
     * An alias for the cvvResultCode provides some consistency.
     */
    protected function getCvvResultCode()
    {
        return $this->getCardCodeResponse();
    }

    /**
     * This is cavvResultCode in the original transaction creation response.
     * There are a few fields like this that change name between APIs.
     */
    protected function setCavvResponse($value)
    {
        $this->cavvResponse = $value;
    }

    /**
     * An alias for the cavvResultCode provides some consistency.
     */
    protected function getCavvResultCode()
    {
        return $this->getCavvResponse();
    }

    protected function setLineItems(LineItems $value)
    {
        $this->lineItems = $value;
    }
}
