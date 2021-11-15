<?php

namespace Omnipay\Stripe\Message\Transfers;

use Guzzle\Http\Message\Response;
use Omnipay\Tests\TestCase;

class UpdateTransferRequestTest extends TestCase
{
    /**
     * @var UpdateTransferRequest
     */
    protected $request;

    /**
     * @var string
     */
    protected $mockDir;

    public function setUp()
    {
        $this->mockDir = __DIR__.'/../../Mock/Transfers';
        $this->request = new UpdateTransferRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setTransferReference('tr_164xRv2eZvKYlo2CZxJZWm1E');
    }

    public function testEndpoint()
    {
        $this->assertSame(
            'https://api.stripe.com/v1/transfers/tr_164xRv2eZvKYlo2CZxJZWm1E',
            $this->request->getEndpoint()
        );
    }

    public function testData()
    {
        $this->request->setMetadata(array('field' => 'value'));

        $data = $this->request->getData();

        $this->assertArrayHasKey('field', $data['metadata']);
        $this->assertSame('value', $data['metadata']['field']);
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse(
            array(\GuzzleHttp\Psr7\parse_response(file_get_contents($this->mockDir.'/CreateTransferRequestSuccess.txt')))
        );
        /** @var \Omnipay\Stripe\Message\Response $response */
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('tr_164xRv2eZvKYlo2CZxJZWm1E', $response->getTransferReference());
        $this->assertNull($response->getMessage());
    }

    public function testSendFailure()
    {
        $this->setMockHttpResponse(
            array(\GuzzleHttp\Psr7\parse_response(file_get_contents($this->mockDir.'/FetchTransferFailure.txt')))
        );
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('No such transfer: tr_164xRv2eZvKYlo2CZxJZWm1E', $response->getMessage());
    }
}
