<?php

namespace Omnipay\Stripe\Message;

use Omnipay\Tests\TestCase;

class UpdateInvoiceItemRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new CreateInvoiceItemRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setInvoiceItemReference('ii_17hCVWCry4L0tg4v2hLQvxrX');
        $this->request->setCustomerReference('cus_7vX2emm98A7crY');
        $this->request->setAmount(1500);
    }

    public function testEndpoint()
    {
        $this->assertSame('https://api.stripe.com/v1/invoiceitems/ii_17hCVWCry4L0tg4v2hLQvxrX', $this->request->getEndpoint());
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('CreateInvoiceItemSuccess.txt');
        $response = $this->request->send();
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('ii_17hCVWCry4L0tg4v2hLQvxrX', $response->getInvoiceItemReference());
        $this->assertNull($response->getMessage());
    }


    public function testSendError()
    {
        $this->setMockHttpResponse('CreateInvoiceItemFailure.txt');
        $response = $this->request->send();
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getInvoiceItemReference());
        $this->assertSame('No such customer: cus_7vX2emm98A7YcrY', $response->getMessage());
    }
}
