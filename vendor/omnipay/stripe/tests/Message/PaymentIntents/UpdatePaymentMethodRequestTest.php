<?php

namespace Omnipay\Stripe\Message\PaymentIntents;

use Omnipay\Tests\TestCase;

class UpdatePaymentMethodRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new UpdatePaymentMethodRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setPaymentMethod('pm_some_visa');
    }

    public function testEndpoint()
    {
        $this->assertSame('https://api.stripe.com/v1/payment_methods/pm_some_visa', $this->request->getEndpoint());
    }

    public function testDataWithCard()
    {
        $card = $this->getValidCard();
        $metaData = [
            'meta' => 'data',
            'other' => 'metaData'
        ];

        $this->request->setCard($card);
        $this->request->setMetadata($metaData);
        $data = $this->request->getData();

        $this->assertSame($card['billingAddress1'], $data['billing_details']['address']['line1']);
        $this->assertSame($card['firstName'] . ' ' . $card['lastName'], $data['billing_details']['name']);
        $this->assertSame($card['expiryYear'], $data['card']['exp_year']);
        $this->assertSame($metaData, $data['metadata']);
        $this->assertArrayNotHasKey('number', $data['card']);
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('UpdatePaymentMethodSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertSame('pm_1EUon32Tb35ankTnF6nuoRVE', $response->getCardReference());
        $this->assertNull($response->getMessage());
    }

    public function testSendFailure()
    {
        $this->setMockHttpResponse('UpdatePaymentMethodFailure.txt');
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertNull($response->getCardReference());
        $this->assertSame('You must save this PaymentMethod to a customer before you can update it.', $response->getMessage());
    }
}
