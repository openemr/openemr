<?php

namespace Omnipay\Stripe\Message;

use Omnipay\Tests\TestCase;

class FetchBalanceTransactionRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new FetchBalanceTransactionRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setBalanceTransactionReference('txn_1044bu4CmsDZ3Zk6BGg97VUU');
    }

    public function testEndpoint()
    {
        $this->assertSame('https://api.stripe.com/v1/balance/history/txn_1044bu4CmsDZ3Zk6BGg97VUU', $this->request->getEndpoint());
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('FetchBalanceTransactionSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('txn_1044bu4CmsDZ3Zk6BGg97VUU', $response->getBalanceTransactionReference());
        $this->assertNull($response->getCardReference());
        $this->assertNull($response->getMessage());
    }

    public function testSendError()
    {
        $this->setMockHttpResponse('FetchBalanceTransactionFailure.txt');
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getBalanceTransactionReference());
        $this->assertNull($response->getCardReference());
        $this->assertSame('No such balance: txn_1044bu4CmsDZ3Zk6BGg97VUUfake', $response->getMessage());
    }
}
