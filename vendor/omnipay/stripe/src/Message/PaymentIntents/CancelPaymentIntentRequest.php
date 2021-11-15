<?php

/**
 * Stripe Payment Intents Cancel Request.
 */
namespace Omnipay\Stripe\Message\PaymentIntents;

/**
 * Stripe Cancel Payment Intent Request.
 *
 * <code>
 *   $paymentIntent = $gateway->cancelPaymentIntent(array(
 *       'paymentIntentReference' => $paymentIntentReference,
 *   ));
 *
 *   $response = $paymentIntent->send();
 *
 *   if ($response->isCancelled()) {
 *     // All done
 *   }
 * </code>
 *
 * @link https://stripe.com/docs/api/payment_intents/cancel
 */
class CancelPaymentIntentRequest extends AbstractRequest
{
    /**
     * @inheritdoc
     */
    public function getData()
    {
        $this->validate('paymentIntentReference');

        return [];
    }

    /**
     * @inheritdoc
     */
    public function getEndpoint()
    {
        return $this->endpoint.'/payment_intents/' . $this->getPaymentIntentReference() . '/cancel';
    }

    /**
     * @inheritdoc
     */
    protected function createResponse($data, $headers = [])
    {
        return $this->response = new Response($this, $data, $headers);
    }
}
