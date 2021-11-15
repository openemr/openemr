<?php

namespace Omnipay\Stripe\Message;

use Omnipay\Tests\TestCase;

class AuthorizeRequestTest extends TestCase
{
    /**
     * @var AuthorizeRequest
     */
    private $request;

    public function setUp()
    {
        $this->request = new AuthorizeRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->initialize(
            array(
                'amount' => '12.00',
                'currency' => 'USD',
                'card' => $this->getValidCard(),
                'description' => 'Order #42',
                'metadata' => array(
                    'foo' => 'bar',
                ),
                'applicationFee' => '1.00'
            )
        );
    }

    public function testGetData()
    {
        $data = $this->request->getData();

        $this->assertSame(1200, $data['amount']);
        $this->assertSame('usd', $data['currency']);
        $this->assertSame('Order #42', $data['description']);
        $this->assertSame('false', $data['capture']);
        $this->assertSame(array('foo' => 'bar'), $data['metadata']);
        $this->assertSame(100, $data['application_fee']);
    }

    /**
     * @expectedException \Omnipay\Common\Exception\InvalidRequestException
     * @expectedExceptionMessage The source parameter is required
     */
    public function testCardRequired()
    {
        $this->request->setCard(null);
        $this->request->getData();
    }

    public function testDataWithCustomerReference()
    {
        $this->request->setCard(null);
        $this->request->setCustomerReference('abc');
        $data = $this->request->getData();

        $this->assertSame('abc', $data['customer']);
    }

    public function testDataWithCardReference()
    {
        $this->request->setCustomerReference('abc');
        $this->request->setCardReference('xyz');
        $data = $this->request->getData();

        $this->assertSame('abc', $data['customer']);
        $this->assertSame('xyz', $data['source']);
    }

    public function testDataWithStatementDescriptor()
    {
        $this->request->setStatementDescriptor('OMNIPAY');
        $data = $this->request->getData();

        $this->assertSame('OMNIPAY', $data['statement_descriptor']);
    }

    public function testDataWithSourceAndDestination()
    {
        $this->request->setSource('abc');
        $this->request->setDestination('xyz');
        $data = $this->request->getData();

        $this->assertSame('abc', $data['source']);
        $this->assertSame('xyz', $data['destination']);
    }

    public function testDataWithToken()
    {
        $this->request->setCustomerReference('abc');
        $this->request->setToken('xyz');
        $data = $this->request->getData();

        $this->assertSame('abc', $data['customer']);
        $this->assertSame('xyz', $data['source']);
    }

    public function testDataWithCard()
    {
        $card = $this->getValidCard();
        $this->request->setCard($card);
        $data = $this->request->getData();

        $this->assertSame($card['number'], $data['source']['number']);
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('PurchaseSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('ch_1IU9gcUiNASROd', $response->getTransactionReference());
        $this->assertSame('card_16n3EU2baUhq7QENSrstkoN0', $response->getCardReference());
        $this->assertSame('req_8PDHeZazN2LwML', $response->getRequestId());
        $this->assertNull($response->getMessage());
    }

    public function testSendError()
    {
        $this->setMockHttpResponse('PurchaseFailure.txt');
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('ch_1IUAZQWFYrPooM', $response->getTransactionReference());
        $this->assertNull($response->getCardReference());
        $this->assertSame('Your card was declined', $response->getMessage());
    }
}
