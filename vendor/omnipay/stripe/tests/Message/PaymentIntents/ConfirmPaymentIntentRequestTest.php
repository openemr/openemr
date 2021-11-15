<?php

namespace Omnipay\Stripe\Message\PaymentIntents;

use Omnipay\Tests\TestCase;

class ConfirmPaymentIntentRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new ConfirmPaymentIntentRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setPaymentIntentReference('pi_valid_intent');
        $this->request->setReturnUrl('complete-payment-page');
    }

    public function testEndpoint()
    {
        $this->assertSame('https://api.stripe.com/v1/payment_intents/pi_valid_intent/confirm', $this->request->getEndpoint());
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

    public function testRedirectUrl()
    {
        $data = $this->request->getData();

        $this->assertSame('complete-payment-page', $data['return_url']);
    }

    public function testConfirmSuccess()
    {
        $this->setMockHttpResponse('ConfirmIntentSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('ch_1Ev0a3FSbr6xR4YApjrlyFGi', $response->getTransactionReference());
        $this->assertSame('pm_1Ev0ZyFSbr6xR4YAX3vLBqEC', $response->getCardReference());
        $this->assertNull($response->getMessage());
    }

  public function testConfirmMissingRedirect()
    {
        $this->setMockHttpResponse('ConfirmIntentMissingRedirect.txt');
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('pm_1Ev1CcFSbr6xR4YAuKuJgwEs', $response->getCardReference());
    }

  public function testConfirm3dsRedirect()
    {
        $this->setMockHttpResponse('ConfirmIntent3dsRedirect.txt');
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $redirectUrl = 'https://hooks.stripe.com/3d_secure_2_eap/begin_test/src_1Ev1M5FSbr6xR4YAg5qdBN6B/src_client_secret_FPr4a6wAiVNi6YrnuI7vah6H';
        $this->assertSame($redirectUrl, $response->getRedirectUrl());
        $this->assertSame('pm_1Ev1LzFSbr6xR4YA0TZ8jta0', $response->getCardReference());
    }

}
