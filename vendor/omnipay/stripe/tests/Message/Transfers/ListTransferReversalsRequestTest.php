<?php

namespace Omnipay\Stripe\Message\Transfers;

use Guzzle\Http\Message\Response;
use Omnipay\Tests\TestCase;

class ListTransferReversalsRequestTest extends TestCase
{
    /**
     * @var ListTransferReversalsRequest
     */
    protected $request;

    /**
     * @var string
     */
    protected $mockDir;

    public function setUp()
    {
        $this->mockDir = __DIR__.'/../../Mock/Transfers';
        $this->request = new ListTransferReversalsRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setTransferReference('tr_164xRv2eZvKYlo2CZxJZWm1E');
    }

    public function testEndpoint()
    {
        $this->assertSame(
            'https://api.stripe.com/v1/transfers/tr_164xRv2eZvKYlo2CZxJZWm1E/reversals',
            $this->request->getEndpoint()
        );
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse(
            array(\GuzzleHttp\Psr7\parse_response(file_get_contents($this->mockDir.'/ListTransferReversalsSuccess.txt')))
        );

        /** @var \Omnipay\Stripe\Message\Response $response */
        $response = $this->request->send();

        $data = $response->getData();

        $this->assertTrue($response->isSuccessful());
        $this->assertNotNull($response->getList());
        $this->assertNull($response->getMessage());
        $this->assertSame('/v1/transfers/tr_164xRv2eZvKYlo2CZxJZWm1E/reversals', $data['url']);
        $this->assertFalse($response->isRedirect());
    }

    public function testSendFailure()
    {
        $this->request->setTransferReference('NOTFOUND');

        $this->setMockHttpResponse(
            array(\GuzzleHttp\Psr7\parse_response(file_get_contents($this->mockDir.'/ListTransferReversalsFailure.txt')))
        );
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('No such transfer: NOTFOUND', $response->getMessage());
    }
}
