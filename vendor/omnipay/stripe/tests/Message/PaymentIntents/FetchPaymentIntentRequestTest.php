<?php

namespace Omnipay\Stripe\Message\PaymentIntents;

use Omnipay\Tests\TestCase;

class FetchPaymentIntentRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new FetchPaymentIntentRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setPaymentIntentReference('pi_valid_intent');
    }

    public function testEndpoint()
    {
        $this->assertSame('https://api.stripe.com/v1/payment_intents/pi_valid_intent', $this->request->getEndpoint());
    }

    public function testHttpMethod()
    {
        $this->assertSame('GET', $this->request->getHttpMethod());
    }

    /**
     * Manually fetching an intent would most likely occur after 3DS authentication
     */
    public function test3dsSuccess()
    {
        $this->setMockHttpResponse('FetchIntentReadyToConfirm.txt');
        $response = $this->request->send();
        $this->assertTrue($response->requiresConfirmation());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertSame('requires_confirmation', $response->getStatus());
        $this->assertSame('pi_1Ev1ezFSbr6xR4YAtM76y2kZ', $response->getPaymentIntentReference());
    }

    /**
     * Most common case would be failed 3DS authentication.
     */
    public function testPaymentMethodRequired()
    {
        $this->setMockHttpResponse('FetchIntentPaymentMethodRequired.txt');
        $response = $this->request->send();
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getCustomerReference());
        $this->assertSame('requires_payment_method', $response->getStatus());
        $this->assertSame('pi_1Ev1ZFFSbr6xR4YAlcdtdqGH', $response->getPaymentIntentReference());
    }

    public function testSendFailure()
    {
        $this->setMockHttpResponse('FetchIntentFailure.txt');
        $response = $this->request->send();
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertNull($response->getCustomerReference());
        $this->assertSame('No such payment_intent: pi_1EUon12Tb35ankTnZyvC3sSdE', $response->getMessage());
    }
}
