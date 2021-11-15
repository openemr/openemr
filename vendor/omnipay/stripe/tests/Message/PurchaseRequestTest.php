<?php

namespace Omnipay\Stripe\Message;

use Omnipay\Tests\TestCase;

class PurchaseRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->initialize(
            array(
                'amount' => '10.00',
                'currency' => 'USD',
                'card' => $this->getValidCard(),
            )
        );
    }

    public function testCaptureIsTrue()
    {
        $data = $this->request->getData();
        $this->assertSame('true', $data['capture']);
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('PurchaseSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('ch_1IU9gcUiNASROd', $response->getTransactionReference());
        $this->assertSame('card_16n3EU2baUhq7QENSrstkoN0', $response->getCardReference());
        $this->assertNull($response->getMessage());
    }

    public function testSendWithSourceSuccess()
    {
        $this->setMockHttpResponse('PurchaseWithSourceSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('ch_1IU9gcUiNASROd', $response->getTransactionReference());
        $this->assertSame('card_15WgqxIobxWFFmzdk5V9z3g9', $response->getCardReference());
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
