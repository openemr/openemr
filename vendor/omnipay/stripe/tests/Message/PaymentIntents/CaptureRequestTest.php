<?php

namespace Omnipay\Stripe\Message\PaymentIntents;

use Omnipay\Tests\TestCase;

class CaptureRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new CaptureRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setPaymentIntentReference('pi_valid_intent');
    }

    public function testEndpoint()
    {
        $this->assertSame('https://api.stripe.com/v1/payment_intents/pi_valid_intent/capture', $this->request->getEndpoint());
    }

    public function testAmount()
    {
        // default is no amount
        $this->assertArrayNotHasKey('amount', $this->request->getData());

        $this->request->setAmount('10.00');

        $data = $this->request->getData();
        $this->assertSame(1000, $data['amount_to_capture']);
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('CaptureSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('ch_1EZvfwFSbr6xR4YAWulsIcYV', $response->getTransactionReference());
        $this->assertSame('pm_1EZvfYFSbr6xR4YAGMpD5hNj', $response->getCardReference());
        $this->assertNull($response->getMessage());
    }

    public function testSendError()
    {
        $this->setMockHttpResponse('CaptureFailure.txt');
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertNull($response->getCardReference());
        $this->assertSame('This PaymentIntent could not be captured because it has already been captured.', $response->getMessage());
    }
}
