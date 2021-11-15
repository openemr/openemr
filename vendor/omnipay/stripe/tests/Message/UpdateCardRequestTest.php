<?php

namespace Omnipay\Stripe\Message;

use Omnipay\Tests\TestCase;

class UpdateCardRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new UpdateCardRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setCustomerReference('cus_1MZSEtqSghKx99');
        $this->request->setCardReference('card_15Wg7vIobxWFFmzdvC5fVY67');
    }

    public function testEndpoint()
    {
        $this->assertSame('https://api.stripe.com/v1/customers/cus_1MZSEtqSghKx99/cards/card_15Wg7vIobxWFFmzdvC5fVY67', $this->request->getEndpoint());
    }

    public function testDataWithCard()
    {
        $card = $this->getValidCard();
        $this->request->setCard($card);
        $data = $this->request->getData();

        $this->assertSame($card['billingAddress1'], $data['address_line1']);
        $this->assertSame($card['number'], $data['number']);
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('UpdateCardSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertSame('cus_1MZeNih5LdKxDq', $response->getCardReference());
        $this->assertNull($response->getMessage());
    }

    public function testSendFailure()
    {
        $this->setMockHttpResponse('UpdateCardFailure.txt');
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertNull($response->getCardReference());
        $this->assertSame('No such customer: cus_1MZeNih5LdKxDq', $response->getMessage());
    }
}
