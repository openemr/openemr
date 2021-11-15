<?php

namespace Omnipay\Stripe\Message;

use Omnipay\Tests\TestCase;

class ListInvoicesRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new ListInvoicesRequest($this->getHttpClient(), $this->getHttpRequest());
    }

    public function testEndpoint()
    {
        $this->assertSame('https://api.stripe.com/v1/invoices', $this->request->getEndpoint());
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('ListInvoicesSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNotNull($response->getList());
        $this->assertNull($response->getMessage());
    }

    public function testSendFailure()
    {
        $this->setMockHttpResponse('ListInvoicesFailure.txt');
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getList());
        $this->assertSame('Invalid API Key provided: sk_test_1234567890ABCDEFlfQ0', $response->getMessage());
    }

    public function testEndpointWithCustomerReference()
    {
        $this->request->setCustomerReference('cus_7zdKilofy4RbZk');
        $this->assertSame('https://api.stripe.com/v1/invoices?customer=cus_7zdKilofy4RbZk', $this->request->getEndpoint());
    }

    public function testSendWithCustomerReferenceSuccess()
    {
        $this->setMockHttpResponse('ListInvoicesSuccess.txt');
        $this->request->setCustomerReference('cus_7zdKilofy4RbZk');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNotNull($response->getList());
        $this->assertNull($response->getMessage());

        $invoices = $response->getList();
        $this->assertSame('cus_7zdKilofy4RbZk', $invoices[0]['customer']);

    }

    public function testSendWithCustomerReferenceFailure()
    {
        $this->setMockHttpResponse('ListInvoicesWithCustomerReferenceFailure.txt');
        $this->request->setCustomerReference('cus_1MZSEtqSghKx99');
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getList());
        $this->assertSame('No such customer: cus_1MZSEtqSghKx99', $response->getMessage());
    }

}
