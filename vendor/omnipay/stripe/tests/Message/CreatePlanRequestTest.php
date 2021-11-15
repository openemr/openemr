<?php

namespace Omnipay\Stripe\Message;

use Omnipay\Tests\TestCase;

class CreatePlanRequestTest extends TestCase
{
    /**
     * @var CreatePlanRequest
     */
    private $request;

    public function setUp()
    {
        $this->request = new CreatePlanRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setId('basic');
        $this->request->setAmount('19.00');
        $this->request->setCurrency('usd');
        $this->request->setInterval('month');
        $this->request->setName('Amazing Gold Plan');
        $this->request->setIntervalCount(1);
        $this->request->setStatementDescriptor('Omnipay Basic Plan');
        $this->request->setTrialPeriodDays(3);
    }

    public function testEndpoint()
    {
        $this->assertSame('https://api.stripe.com/v1/plans', $this->request->getEndpoint());
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('CreatePlanSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('basic', $response->getPlanId());
        $this->assertNotNull($response->getPlan());
        $this->assertNull($response->getMessage());
    }


    public function testSendError()
    {
        $this->setMockHttpResponse('CreatePlanFailure.txt');
        $response = $this->request->send();
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getPlan());
        $this->assertNull($response->getPlanId());
        $this->assertSame('Plan already exists.', $response->getMessage());
    }
}
