<?php

namespace Omnipay\Stripe\Message\Transfers;

use Guzzle\Http\Message\Response;
use Omnipay\Tests\TestCase;

class UpdateTransferReversalRequestTest extends TestCase
{
    /**
     * @var UpdateTransferReversalRequest
     */
    protected $request;

    /**
     * @var string
     */
    protected $mockDir;

    public function setUp()
    {
        $this->mockDir = __DIR__.'/../../Mock/Transfers';
        $this->request = new UpdateTransferReversalRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setTransferReference('tr_164xRv2eZvKYlo2CZxJZWm1E');
        $this->request->setReversalReference('trr_1ARKQ22eZvKYlo2Cv5APdtKF');
    }

    public function testEndpoint()
    {
        $this->assertSame(
            'https://api.stripe.com/v1/transfers/tr_164xRv2eZvKYlo2CZxJZWm1E/reversals/trr_1ARKQ22eZvKYlo2Cv5APdtKF',
            $this->request->getEndpoint()
        );
    }

    public function testData()
    {
        $this->request->setMetadata(array('field' => 'value'));
        $this->request->setDescription('This is a reversal becuase of that');

        $data = $this->request->getData();

        $this->assertSame('This is a reversal becuase of that', $data['description']);
        $this->assertArrayHasKey('field', $data['metadata']);
        $this->assertSame('value', $data['metadata']['field']);
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse(
            array(\GuzzleHttp\Psr7\parse_response(file_get_contents($this->mockDir.'/CreateTransferReversalRequestSuccess.txt')))
        );
        /** @var \Omnipay\Stripe\Message\Response $response */
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('trr_1ARKQ22eZvKYlo2Cv5APdtKF', $response->getTransferReversalReference());
        $this->assertNull($response->getMessage());
    }

    public function testSendFailure()
    {
        $this->setMockHttpResponse(
            array(\GuzzleHttp\Psr7\parse_response(file_get_contents($this->mockDir.'/FetchTransferReversalFailure.txt')))
        );
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('No such transfer reversal: trr_1ARKQ22eZvKYlo2Cv5APdtKF', $response->getMessage());
    }
}
