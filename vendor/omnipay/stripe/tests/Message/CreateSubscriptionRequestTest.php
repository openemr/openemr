<?php

namespace Omnipay\Stripe\Message;

use Omnipay\Tests\TestCase;

class CreateSubscriptionRequestTest extends TestCase
{
    /** @var CreateSubscriptionRequest */
    protected $request;

    public function setUp()
    {
        $this->request = new CreateSubscriptionRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setCustomerReference('cus_7lqqgOm33t4xSU');
        $this->request->setPlan('basic');
    }

    public function testData()
    {
        $this->request->setTaxPercent(14);
        $this->request->setMetadata(array('field' => 'value'));

        $data = $this->request->getData();

        $this->assertSame(14.0, $data['tax_percent']);
        $this->assertArrayHasKey('field', $data['metadata']);
        $this->assertSame('value', $data['metadata']['field']);
    }

    public function testZeroPercentData()
    {
        $this->request->setTaxPercent(0);

        $data = $this->request->getData();

        $this->assertSame(0.0, $data['tax_percent']);
    }

    public function testZeroPercentStringData()
    {
        $this->request->setTaxPercent('0');

        $data = $this->request->getData();

        $this->assertSame(0.0, $data['tax_percent']);
    }

    public function testEndpoint()
    {
        $this->assertSame(
            'https://api.stripe.com/v1/customers/cus_7lqqgOm33t4xSU/subscriptions',
            $this->request->getEndpoint()
        );
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('CreateSubscriptionSuccess.txt');
        $response = $this->request->send();
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('sub_7mUtC70CqhYYMX', $response->getSubscriptionReference());
        $this->assertNotNull($response->getPlan());
        $this->assertNull($response->getMessage());
    }


    public function testSendError()
    {
        $this->setMockHttpResponse('CreateSubscriptionFailure.txt');
        $response = $this->request->send();
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getSubscriptionReference());
        $this->assertNull($response->getPlan());
        $this->assertSame('No such plan: basico', $response->getMessage());
    }
}
