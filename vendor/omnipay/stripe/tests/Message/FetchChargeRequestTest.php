<?php

namespace Omnipay\Stripe\Message;

use Omnipay\Tests\TestCase;

class FetchChargeRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new FetchChargeRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setChargeReference('ch_180ZdUCryC0oikg4v4lc4F59D');
    }

    public function testEndpoint()
    {
        $this->assertSame('https://api.stripe.com/v1/charges/ch_180ZdUCryC0oikg4v4lc4F59D', $this->request->getEndpoint());
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('FetchChargeSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('ch_180ZdUCryC0oikg4v4lc4F59D', $response->getChargeReference());
        $this->assertInternalType('array', $response->getSource());
        $this->assertNull($response->getMessage());
    }

    public function testSendFailure()
    {
        $this->setMockHttpResponse('FetchChargeFailure.txt');
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getChargeReference());
        $this->assertNull($response->getSource());
        $this->assertSame('No such charge: ch_180ZdUCryC0oikg4v4lc4F59D', $response->getMessage());
    }
}
