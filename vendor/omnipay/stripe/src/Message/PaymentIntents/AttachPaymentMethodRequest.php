<?php

/**
 * Stripe Attach Payment Method Request.
 */
namespace Omnipay\Stripe\Message\PaymentIntents;

/**
 * Stripe Attach Payment Method Request.
 *
 * This request is used to attach an existing payment method to an existing customer.
 * The `attachCard` method *will not work* on the Charge gateway.
 *
 * ### Example
 *
 * This example assumes that you have already created both a customer and a
 * payment method and that the data is stored in $customerId and $paymentMethodId, respectively.
 *
 * <code>
 *   // Do an attach card transaction on the gateway
 *   $response = $gateway->attachCard(array(
 *       'paymentMethod'     => $paymentMethodId,
 *       'customerReference' => $customerId,
 *   ))->send();
 *   if ($response->isSuccessful()) {
 *       echo "Gateway attachCard was successful.\n";
 *       // Find the card ID
 *       $methodId = $response->getCardReference();
 *       echo "Method ID = " . $methodId . "\n";
 *   }
 * </code>
 *
 * @see \Omnipay\Stripe\Message\PaymentIntents\CreatePaymentMethodRequest
 * @see \Omnipay\Stripe\Message\PaymentIntents\CreateCustomerRequest
 * @see \Omnipay\Stripe\Message\PaymentIntents\DetachPaymentMethodRequest
 * @see \Omnipay\Stripe\Message\PaymentIntents\UpdatePaymentMethodRequest
 * @link https://stripe.com/docs/api/payment_methods/attach
 */
class AttachPaymentMethodRequest extends AbstractRequest
{
    public function getData()
    {
        $data = [];

        $this->validate('customerReference');
        $this->validate('paymentMethod');

        $data['customer'] = $this->getCustomerReference();

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function getEndpoint()
    {
        return $this->endpoint.'/payment_methods/' . $this->getPaymentMethod() . '/attach';
    }

    /**
     * @inheritdoc
     */
    protected function createResponse($data, $headers = [])
    {
        return $this->response = new Response($this, $data, $headers);
    }
}
