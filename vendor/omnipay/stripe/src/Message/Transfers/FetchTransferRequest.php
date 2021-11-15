<?php

/**
 * Stripe Fetch Transfer Request (Connect only).
 */

namespace Omnipay\Stripe\Message\Transfers;

use Omnipay\Stripe\Message\AbstractRequest;

/**
 * Stripe Fetch Transfer Request.
 *
 * <code>
 *   // Once the transaction has been authorized, we can capture it for final payment.
 *   $transaction = $gateway->fetchTransfer([
 *       'transferReference'   => '{TRANSFER_ID}',
 *   ]);
 *   $response = $transaction->send();
 * </code>
 *
 * @link https://stripe.com/docs/api#retrieve_transfer
 */
class FetchTransferRequest extends AbstractRequest
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
    }

    /**
     * {@inheritdoc}
     */
    public function getEndpoint()
    {
        return $this->endpoint.'/transfers/'.$this->getTransferReference();
    }
}
