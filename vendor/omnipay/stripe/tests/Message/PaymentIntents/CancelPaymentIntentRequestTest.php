<?php

namespace Omnipay\Stripe\Message\PaymentIntents;

use Omnipay\Tests\TestCase;

class CancelPaymentIntentRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new CancelPaymentIntentRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setPaymentIntentReference('pi_valid_intent');
    }

    public function testEndpoint()
    {
        $this->assertSame('https://api.stripe.com/v1/payment_intents/pi_valid_intent/cancel', $this->request->getEndpoint());
    }

    /**
     * @expectedException \Omnipay\Common\Exception\InvalidRequestException
     * @expectedExceptionMessage The paymentIntentReference parameter is required
     */
    public function testPaymentIntent()
    {
        $this->request->setPaymentIntentReference(null);
        $this->request->getData();
    }

    public function testData()
    {
        $this->assertEmpty($this->request->getData());
    }

    public function testCancelSuccess()
    {
        $this->setMockHttpResponse('CancelPaymentIntentSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isCancelled());
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('ch_1F4OqH2okp6n5dKoWlN61H9w', $response->getTransactionReference());
        $this->assertSame('pm_1F4Oq02okp6n5dKoKfHmMyJN', $response->getCardReference());
        $this->assertNull($response->getMessage());
    }

    public function testCancelFailure()
    {
        $this->setMockHttpResponse('CancelPaymentIntentFailure.txt');
        $response = $this->request->send();

        $this->assertFalse($response->isCancelled());
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertSame('You cannot cancel this PaymentIntent because it has a status of canceled. Only a PaymentIntent with one of the following statuses may be canceled: requires_payment_method, requires_capture, requires_confirmation, requires_action.', $response->getMessage());
    }
}
