<?php

namespace Omnipay\Stripe\Message\PaymentIntents;

use Omnipay\Tests\TestCase;

class AttachPaymentMethodRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new AttachPaymentMethodRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setCustomerReference('someCustomer');
        $this->request->setPaymentMethod('pm_some_visa');
    }

    public function testEndpoint()
    {
        $this->assertSame('https://api.stripe.com/v1/payment_methods/pm_some_visa/attach', $this->request->getEndpoint());
    }

    /**
     * @expectedException \Omnipay\Common\Exception\InvalidRequestException
     * @expectedExceptionMessage The customerReference parameter is required
     */
    public function testMissingCustomer()
    {
        $this->request->setCustomerReference(null);
        $this->request->setPaymentMethod(null);
        $this->request->getData();
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
        $data = $this->request->getData();

        $this->assertSame('someCustomer', $data['customer']);
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('AttachPaymentMethodSuccess.txt');
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
        $this->setMockHttpResponse('AttachPaymentMethodFailure.txt');
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertNull($response->getCardReference());
        $this->assertSame('This PaymentMethod was previously used without being attached to a Customer or was detached from a Customer, and may not be used again.', $response->getMessage());
    }
}
