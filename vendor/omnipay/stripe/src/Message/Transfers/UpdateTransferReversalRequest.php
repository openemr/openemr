<?php

/**
 *  Stripe Update Transfer Reversal Request (Connect only).
 */

namespace Omnipay\Stripe\Message\Transfers;

use Omnipay\Stripe\Message\AbstractRequest;

/**
 * Stripe Update Transfer Reversal Request.
 *
 * Updates the specified reversal by setting the values of the parameters passed.
 * Any parameters not provided will be left unchanged.
 *
 * This request only accepts metadata and description as arguments.
 *
 * <code>
 *   // Once the transaction has been authorized, we can capture it for final payment.
 *   $transaction = $gateway->updateTransfer(array(
 *       'transferReference' => '{TRANSFER_ID}',
 *       'reversalReference' => '{REVERSAL_ID}',
 *       'metadata'          => [],
 *   ));
 *   $response = $transaction->send();
 * </code>
 *
 * @link https://stripe.com/docs/api#update_transfer_reversal
 */
class UpdateTransferReversalRequest extends AbstractRequest
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

    /**
     * @return array
     */
    public function getData()
    {
        $this->validate('reversalReference', 'transferReference');

        $data = array();

        if ($this->getMetadata()) {
            $data['metadata'] = $this->getMetadata();
        }

        if ($this->getDescription()) {
            $data['description'] = $this->getDescription();
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return $this->endpoint.'/transfers/'.$this->getTransferReference().'/reversals/'.$this->getReversalReference();
    }
}
