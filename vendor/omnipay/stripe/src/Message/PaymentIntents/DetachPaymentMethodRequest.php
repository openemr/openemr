<?php

/**
 * Stripe Attach Payment Method Request.
 */
namespace Omnipay\Stripe\Message\PaymentIntents;

/**
 * Stripe Attach Payment Method Request.
 *
 * This request is used to detach an existing payment method from a customer.
 *
 * ### Example
 *
 * This example assumes that you have already created both a customer and a
 * payment method and that the data is stored in $customerId and $paymentMethodId, respectively.
 *
 * <code>
 *   // Do an attach card transaction on the gateway
 *   $response = $gateway->deleteCard(array(
 *       'paymentMethod'     => $paymentMethodId,
 *   ))->send();
 *   if ($response->isSuccessful()) {
 *       echo "Gateway detachCard was successful.\n";
 *       // Find the card ID
 *       $methodId = $response->getCardReference();
 *       echo "Method ID = " . $methodId . "\n";
 *   }
 * </code>
 *
 * @see \Omnipay\Stripe\Message\PaymentIntents\CreatePaymentMethodRequest
 * @see \Omnipay\Stripe\Message\PaymentIntents\AttachPaymentMethodRequest
 * @see \Omnipay\Stripe\Message\PaymentIntents\UpdatePaymentMethodRequest
 * @link https://stripe.com/docs/api/payment_methods/detach
 */
class DetachPaymentMethodRequest extends AbstractRequest
{
    public function getData()
    {
        $this->validate('paymentMethod');

        return [];
    }

    /**
     * @inheritdoc
     */
    public function getEndpoint()
    {
        return $this->endpoint.'/payment_methods/' . $this->getPaymentMethod() . '/detach';
    }

    /**
     * @inheritdoc
     */
    protected function createResponse($data, $headers = [])
    {
        return $this->response = new Response($this, $data, $headers);
    }
}
