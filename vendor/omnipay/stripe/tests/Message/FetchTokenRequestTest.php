<?php

namespace Omnipay\Stripe\Message;

use Omnipay\Tests\TestCase;

class FetchTokenRequestTest extends TestCase
{
    /**
     * @var FetchTokenRequest
     */
    private $request;

    public function setUp()
    {
        $this->request = new FetchTokenRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setToken('tok_15Kuns2eZvKYlo2CDt9wRdzS');
    }

    public function testEndpoint()
    {
        $this->assertSame('https://api.stripe.com/v1/tokens/tok_15Kuns2eZvKYlo2CDt9wRdzS', $this->request->getEndpoint());
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('FetchTokenSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('tok_15Kuns2eZvKYlo2CDt9wRdzS', $response->getToken());
        $this->assertInternalType('array', $response->getCard());
        $this->assertNull($response->getMessage());
    }

    public function testSendError()
    {
        $this->setMockHttpResponse('FetchTokenFailure.txt');
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getToken());
        $this->assertNull($response->getCard());
        $this->assertSame('No such token: tok_15Kuns2eZvKYlo2CDt9wRdzS', $response->getMessage());
    }
}
