<?php

namespace Omnipay\Stripe\Message;

use Omnipay\Tests\TestCase;

class FetchSubscriptionRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new FetchSubscriptionRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setSubscriptionReference('sub_7uWjWw96I3N8Yf');
        $this->request->setCustomerReference('cus_7twok4jHGpRWHs');
    }

    public function testEndpoint()
    {
        $endpoint = 'https://api.stripe.com/v1/customers/cus_7twok4jHGpRWHs/subscriptions/sub_7uWjWw96I3N8Yf';
        $this->assertSame($endpoint, $this->request->getEndpoint());
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('FetchSubscriptionSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('sub_7uWjWw96I3N8Yf', $response->getSubscriptionReference());
        $this->assertNull($response->getMessage());
    }

    public function testSendFailure()
    {
        $this->setMockHttpResponse('FetchSubscriptionFailure.txt');
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getSubscriptionReference());
        $message = 'Customer cus_7twok4jHGpRWHs does not have a subscription with ID sub_7uNSBwlTzGjYWw';
        $this->assertSame($message, $response->getMessage());
    }
}
