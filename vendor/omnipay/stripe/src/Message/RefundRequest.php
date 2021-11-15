<?php

/**
 * Stripe Refund Request.
 */

namespace Omnipay\Stripe\Message;

/**
 * Stripe Refund Request.
 *
 * When you create a new refund, you must specify a
 * charge to create it on.
 *
 * Creating a new refund will refund a charge that has
 * previously been created but not yet refunded. Funds will
 * be refunded to the credit or debit card that was originally
 * charged. The fees you were originally charged are also
 * refunded.
 *
 * You can optionally refund only part of a charge. You can
 * do so as many times as you wish until the entire charge
 * has been refunded.
 *
 * Once entirely refunded, a charge can't be refunded again.
 * This method will return an error when called on an
 * already-refunded charge, or when trying to refund more
 * money than is left on a charge.
 *
 * Example -- note this example assumes that the purchase has been successful
 * and that the transaction ID returned from the purchase is held in $sale_id.
 * See PurchaseRequest for the first part of this example transaction:
 *
 * <code>
 *   // Do a refund transaction on the gateway
 *   $transaction = $gateway->refund(array(
 *       'amount'                   => '10.00',
 *       'transactionReference'     => $sale_id,
 *   ));
 *   $response = $transaction->send();
 *   if ($response->isSuccessful()) {
 *       echo "Refund transaction was successful!\n";
 *       $refund_id = $response->getTransactionReference();
 *       echo "Transaction reference = " . $refund_id . "\n";
 *   }
 * </code>
 *
 * @see PurchaseRequest
 * @see \Omnipay\Stripe\Gateway
 * @link https://stripe.com/docs/api#create_refund
 */
class RefundRequest extends AbstractRequest
{
    /**
     * @return bool Whether the application fee should be refunded
     */
    public function getRefundApplicationFee()
    {
        return $this->getParameter('refundApplicationFee');
    }

    /**
     * Whether to refund the application fee associated with a charge.
     *
     * From the {@link https://stripe.com/docs/api#create_refund Stripe docs}:
     * Boolean indicating whether the application fee should be refunded
     * when refunding this charge. If a full charge refund is given, the
     * full application fee will be refunded. Else, the application fee
     * will be refunded with an amount proportional to the amount of the
     * charge refunded. An application fee can only be refunded by the
     * application that created the charge.
     *
     * @param bool $value Whether the application fee should be refunded
     *
     * @return AbstractRequest
     */
    public function setRefundApplicationFee($value)
    {
        return $this->setParameter('refundApplicationFee', $value);
    }

    /**
     * @return bool Whether the transfer should be reversed
     */
    public function getReverseTransfer()
    {
        return $this->getParameter('reverseTransfer');
    }

    /**
     * Whether to refund the application fee associated with a charge.
     *
     * From the {@link https://stripe.com/docs/connect/destination-charges#issuing-refunds Stripe docs}:
     * Charges created on the platform account can be refunded using the
     * platform account's secret key. When refunding a charge that has a
     * `destination[account]`, by default the destination account keeps the
     * funds that were transferred to it, leaving the platform account to
     * cover the negative balance from the refund. To pull back the funds
     * from the connected account to cover the refund, set the
     * `reverse_transfer` parameter to true when creating the refund
     *
     * @param bool $value Whether the transfer should be refunded
     *
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setReverseTransfer($value)
    {
        return $this->setParameter('reverseTransfer', $value);
    }

    public function getData()
    {
        $this->validate('transactionReference', 'amount');

        $data = array();
        $data['amount'] = $this->getAmountInteger();

        if ($this->getRefundApplicationFee()) {
            $data['refund_application_fee'] = 'true';
        }

        if ($this->getReverseTransfer()) {
            $data['reverse_transfer'] = 'true';
        }

        return $data;
    }

    public function getEndpoint()
    {
        return $this->endpoint.'/charges/'.$this->getTransactionReference().'/refund';
    }
}
