<?php

namespace Omnipay\Stripe\Message;

use Omnipay\Tests\TestCase;

class DeletePlanRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new DeletePlanRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setId('basic');
    }

    public function testEndpoint()
    {
        $this->assertSame('https://api.stripe.com/v1/plans/basic', $this->request->getEndpoint());
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('DeletePlanSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getPlan());
        $this->assertNull($response->getMessage());
    }

    public function testSendFailure()
    {
        $this->setMockHttpResponse('DeletePlanFailure.txt');
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getPlanId());
        $this->assertSame('No such plan: basico', $response->getMessage());
    }
}
