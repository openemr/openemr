<?php

namespace Omnipay\Stripe\Message\Transfers;

use Guzzle\Http\Message\Response;
use Omnipay\Tests\TestCase;

class ListTransfersRequestTest extends TestCase
{
    /**
     * @var ListTransfersRequest
     */
    protected $request;

    /**
     * @var string
     */
    protected $mockDir;

    public function setUp()
    {
        $this->mockDir = __DIR__.'/../../Mock/Transfers';
        $this->request = new ListTransfersRequest($this->getHttpClient(), $this->getHttpRequest());
    }

    public function testEndpoint()
    {
        $this->assertSame('https://api.stripe.com/v1/transfers', $this->request->getEndpoint());
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse(
            array(\GuzzleHttp\Psr7\parse_response(file_get_contents($this->mockDir.'/ListTransfersSuccess.txt')))
        );

        /** @var \Omnipay\Stripe\Message\Response $response */
        $response = $this->request->send();

        $data = $response->getData();

        $this->assertTrue($response->isSuccessful());
        $this->assertNotNull($response->getList());
        $this->assertNull($response->getMessage());
        $this->assertSame('/v1/transfers', $data['url']);
        $this->assertFalse($response->isRedirect());
    }

    public function testSendFailure()
    {
        $this->request->setTransferGroup('NOTFOUND');

        $this->setMockHttpResponse(
            array(\GuzzleHttp\Psr7\parse_response(file_get_contents($this->mockDir.'/ListTransfersFailure.txt')))
        );
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('No such transfer group: NOTFOUND', $response->getMessage());
    }
}
