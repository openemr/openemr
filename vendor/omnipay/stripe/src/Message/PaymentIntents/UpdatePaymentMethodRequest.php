<?php

/**
 * Stripe Update Payment Method Request.
 */
namespace Omnipay\Stripe\Message\PaymentIntents;

/**
 * Stripe Update Payment Method Request.
 *
 * If you need to update only some payment method details, like the billing
 * address or expiration date, you can do so, however it is impossible to change the
 * card number or the cvc code for a payment method. Stripe also works directly
 * with card networks so that your customers can continue using your service without
 * interruption.
 *
 * Stripe will automatically validate the payment method on update.
 * The payment method must be attached to a customer to be updated.
 *
 * This requires a paymentMethod.
 *
 * @see \Omnipay\Stripe\Message\PaymentIntents\CreatePaymentMethodRequest
 * @see \Omnipay\Stripe\Message\PaymentIntents\CreateCustomerRequest
 * @see \Omnipay\Stripe\Message\PaymentIntents\DetachPaymentMethodRequest
 * @see \Omnipay\Stripe\Message\PaymentIntents\AttachPaymentMethodRequest
 * @link https://stripe.com/docs/api/payment_methods/update
 */
class UpdatePaymentMethodRequest extends AbstractRequest
{
    public function getData()
    {
        $this->validate('paymentMethod');

        $data = [];

        if ($this->getCard()) {
            $data['card'] = $this->getCardData();
            $data['billing_details'] = $this->getBillingDetails();
        } else {
            return array();
        }

        if ($metadata = $this->getMetadata()) {
            $data['metadata'] = $metadata;
        }

        return $data;
    }

    public function getEndpoint()
    {
        return $this->endpoint.'/payment_methods/'.$this->getPaymentMethod();
    }

    /**
     * Get the card data.
     *
     * This request uses a slightly different format for card data to
     * the other requests and does not require the card data to be
     * complete in full (or valid).
     *
     * @return array
     */
    protected function getCardData()
    {
        $data = array();
        $card = $this->getCard();
        if (!empty($card)) {
            if ($card->getExpiryMonth()) {
                $data['exp_month'] = $card->getExpiryMonth();
            }
            if ($card->getExpiryYear()) {
                $data['exp_year'] = $card->getExpiryYear();
            }
        }

        return $data;
    }


    /**
     * Return an array of the billing details.
     */
    public function getBillingDetails()
    {
        $data = parent::getCardData();

        // Take care of optional data by filtering it out.
        return array_filter([
            'email' => $data['email'],
            'name' =>  $data['name'],
            'address' =>  array_filter([
                'city' => $data['address_city'],
                'country' => $data['address_country'],
                'line1' => $data['address_line1'],
                'line2' => $data['address_line2'],
                'postal_code' => $data['address_zip'],
                'state' => $data['address_state'],
            ]),
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function createResponse($data, $headers = [])
    {
        return $this->response = new Response($this, $data, $headers);
    }
}
