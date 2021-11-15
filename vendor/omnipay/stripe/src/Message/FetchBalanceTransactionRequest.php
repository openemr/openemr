<?php
/**
 * Stripe Fetch Transaction Request
 */

namespace Omnipay\Stripe\Message;

/**
 * Stripe Fetch Balance Request
 *
 * Example -- note this example assumes that the purchase has been successful
 * and that the transaction balance ID returned from the purchase is held in $balanceTransactionId.
 * See PurchaseRequest for the first part of this example transaction:
 *
 * <code>
 *   // Fetch the balance to get information about the payment.
 *   $balance = $gateway->fetchBalanceTransaction();
 *   $balance->setBalanceTransactionReference($balance_transaction_id);
 *   $response = $balance->send();
 *   $data = $response->getData();
 *   echo "Gateway fetchBalance response data == " . print_r($data, true) . "\n";
 * </code>
 *
 * @see PurchaseRequest
 * @see Omnipay\Stripe\Gateway
 * @link https://stripe.com/docs/api#retrieve_balance_transaction
 */
class FetchBalanceTransactionRequest extends AbstractRequest
{
    /**
     * Get the transaction balance reference
     *
     * @return string
     */
    public function getBalanceTransactionReference()
    {
        return $this->getParameter('balanceTransactionReference');
    }

    /**
     * Set the transaction balance reference
     *
     * @return AbstractRequest provides a fluent interface.
     */
    public function setBalanceTransactionReference($value)
    {
        return $this->setParameter('balanceTransactionReference', $value);
    }

    public function getData()
    {
        $this->validate('balanceTransactionReference');

        $data = array();

        return $data;
    }

    public function getEndpoint()
    {
        return $this->endpoint.'/balance/history/'.$this->getBalanceTransactionReference();
    }

    public function getHttpMethod()
    {
        return 'GET';
    }
}
