<?php

namespace Omnipay\Stripe\Message;

use Omnipay\Tests\TestCase;

class UpdateCustomerRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new UpdateCustomerRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setCustomerReference('cus_1MZSEtqSghKx99');
    }

    public function testEndpoint()
    {
        $this->assertSame('https://api.stripe.com/v1/customers/cus_1MZSEtqSghKx99', $this->request->getEndpoint());
    }

    public function testData()
    {
        $this->request->setEmail('customer@business.dom');
        $this->request->setDescription('New customer');
        $this->request->setMetadata(array('field' => 'value'));

        $data = $this->request->getData();

        $this->assertSame('customer@business.dom', $data['email']);
        $this->assertSame('New customer', $data['description']);
        $this->assertArrayHasKey('field', $data['metadata']);
        $this->assertSame('value', $data['metadata']['field']);
    }

    public function testDataWithToken()
    {
        $this->request->setToken('xyz');
        $data = $this->request->getData();

        $this->assertSame('xyz', $data['card']);
    }

    public function testDataWithCard()
    {
        $card = $this->getValidCard();
        $this->request->setCard($card);
        $data = $this->request->getData();

        $this->assertSame($card['number'], $data['card']['number']);
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('UpdateCustomerSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertSame('cus_1MZeNih5LdKxDq', $response->getCustomerReference());
        $this->assertNull($response->getMessage());
    }

    public function testSendFailure()
    {
        $this->setMockHttpResponse('UpdateCustomerFailure.txt');
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertNull($response->getCustomerReference());
        $this->assertSame('No such customer: cus_1MZeNih5LdKxDq', $response->getMessage());
    }
}
