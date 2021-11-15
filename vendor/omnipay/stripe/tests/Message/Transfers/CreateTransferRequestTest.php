<?php

namespace Omnipay\Stripe\Message\Transfers;

use Guzzle\Http\Message\Response;
use Omnipay\Tests\TestCase;

class CreateTransferRequestTest extends TestCase
{
    /**
     * @var CreateTransferRequest
     */
    protected $request;

    /**
     * @var string
     */
    protected $mockDir;

    public function setUp()
    {
        $this->mockDir = __DIR__.'/../../Mock/Transfers';
        $this->request = new CreateTransferRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->initialize(
            array(
                'amount' => '12.00',
                'currency' => 'USD',
                'destination' => 'STRIPE_ACCOUNT_ID',
                'transferGroup' => 'Order42',
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
        $this->assertSame('usd', $data['currency']);
        $this->assertSame('STRIPE_ACCOUNT_ID', $data['destination']);
        $this->assertSame('Order42', $data['transfer_group']);
        $this->assertSame(array('foo' => 'bar'), $data['metadata']);
    }

    /**
     * @expectedException \Omnipay\Common\Exception\InvalidRequestException
     * @expectedExceptionMessage The sourceTransaction or transferGroup parameter is required
     */
    public function testReferenceRequired()
    {
        $this->request->setTransferGroup(null);
        $this->request->getData();
    }

    public function testDataWithSourceTransactionReference()
    {
        $this->request->setTransferGroup(null);
        $this->request->setSourceTransaction('abc');
        $data = $this->request->getData();

        $this->assertSame('abc', $data['source_transaction']);
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse(
            array(\GuzzleHttp\Psr7\parse_response(file_get_contents($this->mockDir.'/CreateTransferRequestSuccess.txt')))
        );
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('tr_164xRv2eZvKYlo2CZxJZWm1E', $response->getTransferReference());
        $this->assertNull($response->getMessage());
    }

    public function testSendError()
    {
        $this->setMockHttpResponse(
            array(\GuzzleHttp\Psr7\parse_response(file_get_contents($this->mockDir.'/CreateTransferRequestFailure.txt')))
        );
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('Transfer does not have available funds', $response->getMessage());
    }
}
