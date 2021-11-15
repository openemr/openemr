<?php

namespace Omnipay\Stripe\Message\PaymentIntents;

use Omnipay\Tests\TestCase;

class DetachPaymentMethodRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new DetachPaymentMethodRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setPaymentMethod('pm_some_visa');
    }

    public function testEndpoint()
    {
        $this->assertSame('https://api.stripe.com/v1/payment_methods/pm_some_visa/detach', $this->request->getEndpoint());
    }

    /**
     * @expectedException \Omnipay\Common\Exception\InvalidRequestException
     * @expectedExceptionMessage The paymentMethod parameter is required
     */
    public function testMissingPaymentMethod()
    {
        $this->request->setPaymentMethod(null);
        $this->request->getData();
    }

    public function testData()
    {
        $this->assertEmpty($this->request->getData());
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('DetachPaymentMethodSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertSame('cus_3f2EpMK2kPm90g', $response->getCustomerReference());
        $this->assertSame('pm_1EUon32Tb35ankTnF6nuoRVE', $response->getCardReference());
        $this->assertNull($response->getMessage());
    }

    public function testSendFailure()
    {
        $this->setMockHttpResponse('DetachPaymentMethodFailure.txt');
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertNull($response->getCardReference());
        $this->assertSame('The payment method you provided is not attached to a customer so detachment is impossible.', $response->getMessage());
    }
}
