<?php

/**
 * Stripe Fetch Transfer Reversal Request (Connect only).
 */

namespace Omnipay\Stripe\Message\Transfers;

use Omnipay\Stripe\Message\AbstractRequest;

/**
 * Stripe Fetch Transfer Reversal Request.
 *
 * <code>
 *   // Once the transaction has been authorized, we can capture it for final payment.
 *   $transaction = $gateway->fetchTransferReversal([
 *       'transferReference' => '{TRANSFER_ID}',
 *       'reversalReference' => '{REVERSAL_ID}',
 *   ]);
 *   $response = $transaction->send();
 * </code>
 *
 *
 * @link https://stripe.com/docs/api#retrieve_transfer_reversal
 */
class FetchTransferReversalRequest extends AbstractRequest
{
    /**
     * @return mixed
     */
    public function getReversalReference()
    {
        return $this->getParameter('reversalReference');
    }

    /**
     * @param string $value
     *
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setReversalReference($value)
    {
        return $this->setParameter('reversalReference', $value);
    }

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

    public function getData()
    {
        $this->validate('reversalReference', 'transferReference');
    }

    public function getEndpoint()
    {
        return $this->endpoint.'/transfers/'.$this->getTransferReference().'/reversals/'.$this->getReversalReference();
    }
}
