<?php

/**
 * Stripe Transfer Request (Connect only).
 */

namespace Omnipay\Stripe\Message\Transfers;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Stripe\Message\AuthorizeRequest;

/**
 * Stripe Transfer Request.
 *
 * To send funds from your Stripe account to a connected account, you create
 * a new transfer object. Your Stripe balance must be able to cover the
 * transfer amount, or you'll receive an "Insufficient Funds" error.
 *
 * Example -- note this example assumes that the original charge was successful
 *
 * <code>
 *   // Create the transfer object when moving funds between Stripe accounts
 *   $transaction = $gateway->transfer(array(
 *       'amount'        => '10.00',
 *       'currency'      => 'AUD',
 *       'transferGroup' => '{ORDER10}',
 *       'destination'   => '{CONNECTED_STRIPE_ACCOUNT_ID}',
 *   ));
 *   $response = $transaction->send();
 * </code>
 *
 * @see  AuthorizeRequest
 * @link https://stripe.com/docs/connect/charges-transfers
 */
class CreateTransferRequest extends AuthorizeRequest
{
    /**
     * @return mixed
     */
    public function getSourceTransaction()
    {
        return $this->getParameter('sourceTransaction');
    }

    /**
     * When creating separate charges and transfers, your platform can
     * inadvertently attempt a transfer without having a sufficient
     * available balance. Doing so raises an error and the transfer
     * attempt fails. If you’re commonly experiencing this problem, you
     * can use the `source_transaction` parameter to tie a transfer to an
     * existing charge. By using `source_transaction`, the transfer
     * request succeeds regardless of your available balance and the
     * transfer itself only occurs once the charge’s funds become available.
     *
     * @param string $value
     *
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setSourceTransaction($value)
    {
        return $this->setParameter('sourceTransaction', $value);
    }

    public function getData()
    {
        $this->validate('amount', 'currency', 'destination');

        $data = array(
            'amount' => $this->getAmountInteger(),
            'currency' => strtolower($this->getCurrency()),
            'destination' => $this->getDestination(),
        );

        if ($this->getMetadata()) {
            $data['metadata'] = $this->getMetadata();
        }

        if ($this->getTransferGroup()) {
            $data['transfer_group'] = $this->getTransferGroup();
        } elseif ($this->getSourceTransaction()) {
            $data['source_transaction'] = $this->getSourceTransaction();
        } else {
            throw new InvalidRequestException("The sourceTransaction or transferGroup parameter is required");
        }

        return $data;
    }

    public function getEndpoint()
    {
        return $this->endpoint.'/transfers';
    }
}
