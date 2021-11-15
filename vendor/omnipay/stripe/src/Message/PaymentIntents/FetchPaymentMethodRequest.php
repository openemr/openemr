<?php

/**
 * Stripe Fetch Payment Method Request.
 */
namespace Omnipay\Stripe\Message\PaymentIntents;

/**
 * Stripe Fetch Payment Method Request.
 *
 * <code>
 *   $paymentMethod = $gateway->fetchPaymentMethod(array(
 *       'paymentMethodId' => $paymentMethodId,
 *   ));
 *
 *   $response = $paymentMethod->send();
 *
 *   if ($response->isSuccessful()) {
 *     // All done
 *   }
 * </code>
 *
 * @link https://stripe.com/docs/api/payment_methods/retrieve
 */
class FetchPaymentMethodRequest extends AbstractRequest
{
    /**
     * @inheritdoc
     */
    public function getData()
    {
        $this->validate('paymentMethod');

        return [];
    }

    /**
     * @inheritdoc
     */
    public function getHttpMethod()
    {
        return 'GET';
    }

    /**
     * @inheritdoc
     */
    public function getEndpoint()
    {
        return $this->endpoint . '/payment_methods/' . $this->getPaymentMethod();
    }

    /**
     * @inheritdoc
     */
    protected function createResponse($data, $headers = [])
    {
        return $this->response = new Response($this, $data, $headers);
    }
}
