<?php

/**
 * Stripe Reverse Transfer Request (Connect only).
 */

namespace Omnipay\Stripe\Message\Transfers;

use Omnipay\Stripe\Message\RefundRequest;

/**
 * Stripe Transfer Reversal Request.
 *
 * When you create a new reversal, you must specify a transfer to create it on.
 *
 * When reversing transfers, you can optionally reverse part of the transfer.
 * You can do so as many times as you wish until the entire transfer has been
 * reversed.
 *
 * Once entirely reversed, a transfer can't be reversed again. This method will
 * return an error when called on an already-reversed transfer, or when trying
 * to reverse more money than is left on a transfer.
 *
 * <code>
 *   // Once the transaction has been authorized, we can capture it for final payment.
 *   $transaction = $gateway->reverseTransfer(array(
 *       'transferReference' => '{TRANSFER_ID}',
 *       'description'       => 'Had to reverse this transfer because of things'
 *   ));
 *   $response = $transaction->send();
 * </code>
 *
 * @link https://stripe.com/docs/api#create_transfer_reversal
 */
class CreateTransferReversalRequest extends RefundRequest
{
    /**
     * @return mixed
     */
    public function getTransferReference()
    {
        return $this->getParameter('transferReference');
    }

    /**
     * @param string $value
     *
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setTransferReference($value)
    {
        return $this->setParameter('transferReference', $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $this->validate('transferReference');

        $data = array();

        // If no amount is passed, then the entire transfer is reversed
        if ($this->getAmountInteger()) {
            $data['amount'] = $this->getAmountInteger();
        }

        if ($this->getMetadata()) {
            $data['metadata'] = $this->getMetadata();
        }

        if ($this->getDescription()) {
            $data['description'] = $this->getDescription();
        }

        if ($this->getRefundApplicationFee()) {
            $data['refund_application_fee'] = 'true';
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getEndpoint()
    {
        return $this->endpoint.'/transfers/'.$this->getTransferReference().'/reversals';
    }
}
