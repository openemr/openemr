<?php

namespace Omnipay\Stripe\Message;

use Omnipay\Tests\TestCase;

class CancelSubscriptionRequestTest extends TestCase
{
    /**
     * @var CancelSubscriptionRequest
     */
    private $request;

    public function setUp()
    {
        $this->request = new CancelSubscriptionRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setCustomerReference('cus_7lfqk3Om3t4xSU');
        $this->request->setSubscriptionReference('sub_7mU0FokE8GQZFW');
        $this->request->setAtPeriodEnd(true);
    }

    public function testEndpoint()
    {
        $this->assertSame('https://api.stripe.com/v1/customers/cus_7lfqk3Om3t4xSU/subscriptions/sub_7mU0FokE8GQZFW', $this->request->getEndpoint());
        $this->assertSame(true, $this->request->getAtPeriodEnd());

        $data = $this->request->getData();
        $this->assertSame('true', $data['at_period_end']);
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('CancelSubscriptionSuccess.txt');
        $response = $this->request->send();
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('sub_7mU0FokE8GQZFW', $response->getSubscriptionReference());
        $this->assertNotNull($response->getPlan());
        $this->assertNull($response->getMessage());
    }


    public function testSendError()
    {
        $this->setMockHttpResponse('CancelSubscriptionFailure.txt');
        $response = $this->request->send();
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getSubscriptionReference());
        $this->assertNull($response->getPlan());
        $this->assertSame('Customer cus_7lqqgOm33t4xSU does not have a subscription with ID sub_7mU0DonX8GQZFW', $response->getMessage());
    }
}
