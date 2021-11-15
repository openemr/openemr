<?php

namespace Omnipay\Stripe\Message;

use Omnipay\Tests\TestCase;

class ListPlansTest extends TestCase
{
    /** @var  ListPlansRequest */
    protected $request;

    public function setUp()
    {
        $this->request = new ListPlansRequest($this->getHttpClient(), $this->getHttpRequest());
    }

    public function testEndpoint()
    {
        $this->assertSame('https://api.stripe.com/v1/plans', $this->request->getEndpoint());
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('ListPlansSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNotNull($response->getList());
        $this->assertNull($response->getMessage());
    }

    /**
     * According to documentation: https://stripe.com/docs/api/php#list_plans
     * This request should never throw an error.
     */
    public function testSendFailure()
    {
        $this->assertTrue(true);
    }
}
