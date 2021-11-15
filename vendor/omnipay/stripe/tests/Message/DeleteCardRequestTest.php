<?php

namespace Omnipay\Stripe\Message;

use Omnipay\Tests\TestCase;

class DeleteCardRequestTest extends TestCase
{
    /**
     * @var DeleteCardRequest
     */
    private $request;

    public function setUp()
    {
        $this->request = new DeleteCardRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setCardReference('cus_1MZSEtqSghKx99');
    }

    public function testEndpoint()
    {
        $this->request->setCustomerReference('');
        $this->request->setCardReference('cus_1MZSEtqSghKx99');
        $this->assertSame('https://api.stripe.com/v1/customers/cus_1MZSEtqSghKx99', $this->request->getEndpoint());
        $this->request->setCustomerReference('cus_1MZSEtqSghKx99');
        $this->request->setCardReference('card_15Wg7vIobxWFFmzdvC5fVY67');
        $this->assertSame('https://api.stripe.com/v1/customers/cus_1MZSEtqSghKx99/cards/card_15Wg7vIobxWFFmzdvC5fVY67', $this->request->getEndpoint());
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('DeleteCardSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertNull($response->getCardReference());
        $this->assertNull($response->getMessage());
    }

    public function testSendFailure()
    {
        $this->setMockHttpResponse('DeleteCardFailure.txt');
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertNull($response->getCardReference());
        $this->assertSame('No such customer: cus_1MZeNih5LdKxDq', $response->getMessage());
    }
}
