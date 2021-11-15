<?php

namespace Omnipay\Stripe\Message;

use Omnipay\Tests\TestCase;

class FetchInvoiceItemRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new FetchInvoiceItemRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setInvoiceItemReference('ii_17hC3JCryC4r2g4vLyzjN0n3');
    }

    public function testEndpoint()
    {
        $this->assertSame('https://api.stripe.com/v1/invoiceitems/ii_17hC3JCryC4r2g4vLyzjN0n3', $this->request->getEndpoint());
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('FetchInvoiceItemsSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('ii_17hC3JCryC4r2g4vLyzjN0n3', $response->getInvoiceItemReference());
        $this->assertNull($response->getMessage());
    }

    public function testSendFailure()
    {
        $this->setMockHttpResponse('FetchInvoiceItemsFailure.txt');
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getInvoiceItemReference());
        $this->assertSame('No such invoiceitem: ii_17hC3JCryC4r2g4vLyzjN0n3', $response->getMessage());
    }
}
