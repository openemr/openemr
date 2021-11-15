<?php

namespace Omnipay\Stripe\Message;

use Omnipay\Tests\TestCase;

class FetchEventRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new FetchEventRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setEventReference('evt_17X23UCryC4r2g4vdolh6muI');
    }

    public function testEndpoint()
    {
        $this->assertSame('https://api.stripe.com/v1/events/evt_17X23UCryC4r2g4vdolh6muI', $this->request->getEndpoint());
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('FetchEventSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('evt_17X23UCryC4r2g4vdolh6muI', $response->getEventReference());
        $this->assertNull($response->getMessage());
    }

    public function testSendFailure()
    {
        $this->setMockHttpResponse('FetchEventFailure.txt');
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getEventReference());
        $this->assertSame('No such event: evt_17X23UCryC4r2g4vdolh6muI', $response->getMessage());
    }
}
