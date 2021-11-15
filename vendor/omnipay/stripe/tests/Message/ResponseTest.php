<?php

namespace Omnipay\Stripe\Message;

use Omnipay\Tests\TestCase;

class ResponseTest extends TestCase
{
    public function testPurchaseSuccess()
    {
        $httpResponse = $this->getMockHttpResponse('PurchaseSuccess.txt');
        $response = new Response($this->getMockRequest(), (string) $httpResponse->getBody());

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('ch_1IU9gcUiNASROd', $response->getTransactionReference());
        $this->assertSame('card_16n3EU2baUhq7QENSrstkoN0', $response->getCardReference());
        $this->assertNull($response->getMessage());
        $this->assertInternalType('array', $response->getSource());
    }

    public function testPurchaseWithSourceSuccess()
    {
        $httpResponse = $this->getMockHttpResponse('PurchaseWithSourceSuccess.txt');
        $response = new Response($this->getMockRequest(), (string) $httpResponse->getBody());

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('ch_1IU9gcUiNASROd', $response->getTransactionReference());
        $this->assertSame('card_15WgqxIobxWFFmzdk5V9z3g9', $response->getCardReference());
        $this->assertNull($response->getMessage());
    }

    public function testPurchaseFailure()
    {
        $httpResponse = $this->getMockHttpResponse('PurchaseFailure.txt');
        $response = new Response($this->getMockRequest(), (string) $httpResponse->getBody());

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('ch_1IUAZQWFYrPooM', $response->getTransactionReference());
        $this->assertNull($response->getCardReference());
        $this->assertSame('Your card was declined', $response->getMessage());
        $this->assertNull($response->getSource());
    }

    public function testPurchaseFailureWithoutMessage()
    {
        $httpResponse = $this->getMockHttpResponse('PurchaseFailureWithoutMessage.txt');
        $response = new Response($this->getMockRequest(), (string) $httpResponse->getBody());

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('ch_1JEJGNWFYxAwgF', $response->getTransactionReference());
        $this->assertNull($response->getCardReference());
        $this->assertNull($response->getMessage());
        $this->assertNull($response->getSource());
    }

    public function testPurchaseFailureWithoutCode()
    {
        $httpResponse = $this->getMockHttpResponse('PurchaseFailureWithoutCode.txt');
        $response = new Response($this->getMockRequest(), (string) $httpResponse->getBody());

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('ch_1KGNWMAOUdAbbC', $response->getTransactionReference());
        $this->assertNull($response->getCardReference());
        $this->assertNull($response->getCode());
        $this->assertNull($response->getSource());
    }

    public function testCreateCustomerSuccess()
    {
        $httpResponse = $this->getMockHttpResponse('CreateCustomerSuccess.txt');
        $response = new Response($this->getMockRequest(), (string) $httpResponse->getBody());

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertSame('cus_1MZSEtqSghKx99', $response->getCustomerReference());
        $this->assertNull($response->getMessage());
    }

    public function testCreateCustomerFailure()
    {
        $httpResponse = $this->getMockHttpResponse('CreateCustomerFailure.txt');
        $response = new Response($this->getMockRequest(), (string) $httpResponse->getBody());

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertNull($response->getCustomerReference());
        $this->assertSame('You must provide an integer value for \'exp_year\'.', $response->getMessage());
    }

    public function testUpdateCustomerSuccess()
    {
        $httpResponse = $this->getMockHttpResponse('UpdateCustomerSuccess.txt');
        $response = new Response($this->getMockRequest(), (string) $httpResponse->getBody());

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertSame('cus_1MZeNih5LdKxDq', $response->getCustomerReference());
        $this->assertNull($response->getMessage());
    }

    public function testUpdateCustomerFailure()
    {
        $httpResponse = $this->getMockHttpResponse('UpdateCustomerFailure.txt');
        $response = new Response($this->getMockRequest(), (string) $httpResponse->getBody());

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertNull($response->getCustomerReference());
        $this->assertSame('No such customer: cus_1MZeNih5LdKxDq', $response->getMessage());
    }

    public function testDeleteCustomerSuccess()
    {
        $httpResponse = $this->getMockHttpResponse('DeleteCustomerSuccess.txt');
        $response = new Response($this->getMockRequest(), (string) $httpResponse->getBody());

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertNull($response->getCustomerReference());
        $this->assertNull($response->getMessage());
    }

    public function testDeleteCustomerFailure()
    {
        $httpResponse = $this->getMockHttpResponse('DeleteCustomerFailure.txt');
        $response = new Response($this->getMockRequest(), (string) $httpResponse->getBody());

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertNull($response->getCustomerReference());
        $this->assertSame('No such customer: cus_1MZeNih5LdKxDq', $response->getMessage());
    }

    public function testCreateCardSuccess()
    {
        $httpResponse = $this->getMockHttpResponse('CreateCardSuccess.txt');
        $response = new Response($this->getMockRequest(), (string) $httpResponse->getBody());

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertSame('card_15WgqxIobxWFFmzdk5V9z3g9', $response->getCardReference());
        $this->assertNull($response->getMessage());
    }

    public function testCreateCardFailure()
    {
        $httpResponse = $this->getMockHttpResponse('CreateCardFailure.txt');
        $response = new Response($this->getMockRequest(), (string) $httpResponse->getBody());

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertNull($response->getCardReference());
        $this->assertSame('You must provide an integer value for \'exp_year\'.', $response->getMessage());
    }

    public function testUpdateCardSuccess()
    {
        $httpResponse = $this->getMockHttpResponse('UpdateCardSuccess.txt');
        $response = new Response($this->getMockRequest(), (string) $httpResponse->getBody());

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertSame('cus_1MZeNih5LdKxDq', $response->getCardReference());
        $this->assertNull($response->getMessage());
    }

    public function testUpdateCardFailure()
    {
        $httpResponse = $this->getMockHttpResponse('UpdateCardFailure.txt');
        $response = new Response($this->getMockRequest(), (string) $httpResponse->getBody());

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertNull($response->getCardReference());
        $this->assertSame('No such customer: cus_1MZeNih5LdKxDq', $response->getMessage());
    }

    public function testDeleteCardSuccess()
    {
        $httpResponse = $this->getMockHttpResponse('DeleteCardSuccess.txt');
        $response = new Response($this->getMockRequest(), (string) $httpResponse->getBody());

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertNull($response->getCardReference());
        $this->assertNull($response->getMessage());
    }

    public function testDeleteCardFailure()
    {
        $httpResponse = $this->getMockHttpResponse('DeleteCardFailure.txt');
        $response = new Response($this->getMockRequest(), (string) $httpResponse->getBody());

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertNull($response->getCardReference());
        $this->assertSame('No such customer: cus_1MZeNih5LdKxDq', $response->getMessage());
    }
}
