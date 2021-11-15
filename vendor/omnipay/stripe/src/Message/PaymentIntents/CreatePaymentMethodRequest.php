<?php

/**
 * Stripe Create Payment Method Request.
 */
namespace Omnipay\Stripe\Message\PaymentIntents;

/**
 * Stripe Create Payment Method Request.
 *
 * Stripe payment methods differs a little bit from creating a card.
 * When using the Payment Intent API, it is mandatory to use a payment method,
 * so a lot of times you'll be creating a payment method without an assigned customer.
 *
 * Another difference is that it's impossible to create a payment method and assign
 * it to a user in a single request. Instead, you create a payment method and then
 * attach it.
 *
 * ### Example
 *
 * <code>
 *   // Create a credit card object
 *   // This card can be used for testing.
 *   $new_card = new CreditCard([
 *       'firstName'     => 'Example',
 *       'lastName'      => 'Customer',
 *       'number'        => '5555555555554444',
 *       'expiryMonth'   => '01',
 *       'expiryYear'    => '2020',
 *       'cvv'           => '456',
 *       'email'             => 'customer@example.com',
 *       'billingAddress1'   => '1 Lower Creek Road',
 *       'billingCountry'    => 'AU',
 *       'billingCity'       => 'Upper Swan',
 *       'billingPostcode'   => '6999',
 *       'billingState'      => 'WA',
 *   ]);
 *
 *   // Do a create card transaction on the gateway
 *   $response = $gateway->createCard(['card' => $new_card])->send();
 *   if ($response->isSuccessful()) {
 *       echo "Gateway createCard was successful.\n";
 *       // Find the card ID
 *       $method_id = $response->getCardReference();
 *       echo "Method ID = " . $method_id . "\n";
 *   }
 * </code>
 *
 * @see \Omnipay\Stripe\Message\PaymentIntents\AttachPaymentMethodRequest
 * @see \Omnipay\Stripe\Message\PaymentIntents\DetachPaymentMethodRequest
 * @see \Omnipay\Stripe\Message\PaymentIntents\UpdatePaymentMethodRequest
 * @link https://stripe.com/docs/api/payment_methods/create
 */
class CreatePaymentMethodRequest extends AbstractRequest
{
    /**
     * @inheritdoc
     */
    public function getData()
    {
        $data = [];

        if ($this->getToken()) {
            $data['card'] = ['token' => $this->getToken()];
        } elseif ($this->getCard()) {
            $data['card'] = $this->getCardData();
        } else {
            // one of token or card is required
            $this->validate('card');
        }

        if ($this->getCard() && $billingDetails = $this->getBillingDetails()) {
            $data['billing_details'] = $billingDetails;
        }

        $data['type'] = 'card';

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function getEndpoint()
    {
        return $this->endpoint.'/payment_methods';
    }

    /**
     * @inheritdoc
     */
    public function getCardData()
    {
        $data = parent::getCardData();

        return [
            'exp_month' => $data['exp_month'],
            'exp_year' =>  $data['exp_year'],
            'number' =>  $data['number'],
            'cvc' =>  $data['cvc'],
        ];
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
