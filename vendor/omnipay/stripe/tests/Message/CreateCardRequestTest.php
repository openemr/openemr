<?php

namespace Omnipay\Stripe\Message;

use Omnipay\Tests\TestCase;

class CreateCardRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new CreateCardRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setCard($this->getValidCard());
    }

    public function testEndpoint()
    {
        $this->request->setCustomerReference('');
        $this->assertSame('https://api.stripe.com/v1/customers', $this->request->getEndpoint());
        $this->request->setCustomerReference('cus_1MZSEtqSghKx99');
        $this->assertSame('https://api.stripe.com/v1/customers/cus_1MZSEtqSghKx99/cards', $this->request->getEndpoint());
    }

    /**
     * @expectedException \Omnipay\Common\Exception\InvalidRequestException
     * @expectedExceptionMessage The source parameter is required
     */
    public function testCard()
    {
        $this->request->setCard(null);
        $this->request->getData();
    }

    public function testDataWithToken()
    {
        $this->request->setToken('xyz');
        $data = $this->request->getData();

        $this->assertSame('xyz', $data['source']);
    }

    public function testDataWithCardReference()
    {
        $this->request->setCard(null);
        $this->request->setCardReference('xyz');
        $data = $this->request->getData();

        $this->assertSame('xyz', $data['source']);
    }

    public function testDataWithSource()
    {
        $this->request->setCard(null);
        $this->request->setSource('xyz');
        $data = $this->request->getData();

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
        $this->setMockHttpResponse('CreateCardSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertSame('cus_5i75ZdvSgIgLdW', $response->getCustomerReference());
        $this->assertSame('card_15WgqxIobxWFFmzdk5V9z3g9', $response->getCardReference());
        $this->assertNull($response->getMessage());
    }

    public function testSendFailure()
    {
        $this->setMockHttpResponse('CreateCardFailure.txt');
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertNull($response->getCardReference());
        $this->assertSame('You must provide an integer value for \'exp_year\'.', $response->getMessage());
    }

    public function testCardWithoutEmail()
    {
        $card = $this->getValidCard();
        $this->request->setCard($card);
        $data = $this->request->getData();

        $this->assertArrayNotHasKey('email', $card);
    }
}
