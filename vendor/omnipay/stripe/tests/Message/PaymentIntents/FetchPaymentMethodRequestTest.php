<?php

namespace Omnipay\Stripe\Message\PaymentIntents;

use Omnipay\Tests\TestCase;

class FetchPaymentMethodRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new FetchPaymentMethodRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setPaymentMethod('pm_valid_method');
    }

    public function testEndpoint()
    {
        $this->assertSame('https://api.stripe.com/v1/payment_methods/pm_valid_method', $this->request->getEndpoint());
    }

    public function testHttpMethod()
    {
        $this->assertSame('GET', $this->request->getHttpMethod());
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('FetchPaymentMethodSuccess.txt');
        $response = $this->request->send();
        $this->assertTrue($response->isSuccessful());
        $this->assertSame('pm_1F55vt2okp6n5dXo2WxJfirJ', $response->getCardReference());
        $this->assertSame('cus_FaCqpKDSJvFSsC', $response->getCustomerReference());
    }

    public function testSendFailure()
    {
        $this->setMockHttpResponse('FetchPaymentMethodFailure.txt');
        $response = $this->request->send();
        $this->assertFalse($response->isSuccessful());
        $this->assertNull($response->getCardReference());
        $this->assertNull($response->getCustomerReference());
        $this->assertSame('No such payment_method: pm_1F52R22okp6n5dKoGSAKgKUX', $response->getMessage());
    }
}
