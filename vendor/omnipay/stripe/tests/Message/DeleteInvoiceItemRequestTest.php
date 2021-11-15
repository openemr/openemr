<?php

namespace Omnipay\Stripe\Message;

use Omnipay\Tests\TestCase;

class DeleteInvoiceItemRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new DeleteInvoiceItemRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setInvoiceItemReference('ii_17hC3JCryC4r2g4vLyzjN0n3');
    }

    public function testEndpoint()
    {
        $this->assertSame('https://api.stripe.com/v1/invoiceitems/ii_17hC3JCryC4r2g4vLyzjN0n3', $this->request->getEndpoint());
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('DeleteInvoiceItemSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getMessage());
        $this->assertNull($response->getInvoiceItemReference());
    }

    public function testSendFailure()
    {
        $this->setMockHttpResponse('DeleteInvoiceItemFailure.txt');
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getInvoiceItemReference());
        $this->assertSame('No such invoiceitem: ii_17hC3JCryC4r2g4vLyzjN0n3', $response->getMessage());
    }
}
