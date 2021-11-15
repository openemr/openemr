<?php

namespace Omnipay\Stripe\Message\Transfers;

use Guzzle\Http\Message\Response;
use Omnipay\Tests\TestCase;

class CreateTransferReversalRequestTest extends TestCase
{
    /**
     * @var CreateTransferReversalRequest
     */
    protected $request;

    /**
     * @var string
     */
    protected $mockDir;

    public function setUp()
    {
        $this->mockDir = __DIR__.'/../../Mock/Transfers';
        $this->request = new CreateTransferReversalRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->initialize(
            array(
                'transferReference' => 'REVERSAL_ID',
                'amount' => '12.00',
                'description' => 'Reversing Order 42',
                'metadata' => array(
                    'foo' => 'bar',
                ),
            )
        );
    }

    public function testGetData()
    {
        $data = $this->request->getData();

        $this->assertSame(1200, $data['amount']);
        $this->assertSame('Reversing Order 42', $data['description']);
        $this->assertSame(array('foo' => 'bar'), $data['metadata']);
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
