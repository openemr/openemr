<?php

namespace Omnipay\Stripe\Message;

use Omnipay\Tests\TestCase;

class UpdateSubscriptionRequestTest extends TestCase
{
    /**
     * @var UpdateSubscriptionRequest
     */
    protected $request;

    public function setUp()
    {
        $this->request = new UpdateSubscriptionRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setCustomerReference('cus_7lqqgOm33t4xSU');
        $this->request->setSubscriptionReference('sub_7uNSBwlTzGjYWw');
        $this->request->setPlan('basic');
    }

    public function testEndpoint()
    {
        $endpoint = 'https://api.stripe.com/v1/customers/cus_7lqqgOm33t4xSU/subscriptions/sub_7uNSBwlTzGjYWw';
        $this->assertSame($endpoint, $this->request->getEndpoint());
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('UpdateSubscriptionSuccess.txt');
        /** @var Response */
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('sub_7uNSBwlTzGjYWw', $response->getSubscriptionReference());
        $this->assertNotNull($response->getPlan());
        $this->assertNull($response->getMessage());
    }


    public function testSendError()
    {
        $this->setMockHttpResponse('UpdateSubscriptionFailure.txt');

        /** @var Response */
        $response = $this->request->send();
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getSubscriptionReference());
        $this->assertNull($response->getPlan());

        $customerReference = $this->request->getCustomerReference();
        $subscriptionReference = $this->request->getSubscriptionReference();

        $message = sprintf(
            'Customer %s does not have a subscription with ID %s',
            $customerReference,
            $subscriptionReference
        );
        $this->assertSame($message, $response->getMessage());
    }
}
