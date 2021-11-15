<?php

/**
 * Stripe Capture Request.
 */
namespace Omnipay\Stripe\Message\PaymentIntents;

/**
 * Stripe Capture Request.
 *
 * Use this request to capture and process a previously created authorization.
 *
 * Example -- note this example assumes that the authorization has been successful
 * and that the payment intent that performed the authorization is held in $paymentIntent.
 * See AuthorizeRequest for the first part of this example transaction:
 *
 * <code>
 *   // Once the transaction has been authorized, we can capture it for final payment.
 *   $transaction = $gateway->capture(array(
 *       'amount'        => '10.00',
 *       'currency'      => 'AUD',
 *   ));
 *   $transaction->setPaymentMethod($paymentMethod);
 *   $response = $transaction->send();
 * </code>
 *
 * @see AuthorizeRequest
 * @link https://stripe.com/docs/api/payment_intents/capture
 */
class CaptureRequest extends AbstractRequest
{
    public function getData()
    {
        $this->validate('paymentIntentReference');

        $data = array();

        if ($amount = $this->getAmountInteger()) {
            $data['amount_to_capture'] = $amount;
        }

        return $data;
    }

    public function getEndpoint()
    {
        return $this->endpoint.'/payment_intents/'.$this->getPaymentIntentReference().'/capture';
    }

    /**
     * @inheritdoc
     */
    protected function createResponse($data, $headers = [])
    {
        return $this->response = new Response($this, $data, $headers);
    }
}
