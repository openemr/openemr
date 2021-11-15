<?php

namespace Omnipay\Stripe\Message\PaymentIntents;

use Omnipay\Tests\TestCase;

class ResponseTest extends TestCase
{
    public function testStatus()
    {
        $httpResponse = $this->getMockHttpResponse('AuthorizeSuccess.txt');
        $response = new Response($this->getMockRequest(), (string) $httpResponse->getBody());

        $this->assertSame('requires_confirmation', $response->getStatus());
    }

    public function testRequiresConfirmation()
    {
        $httpResponse = $this->getMockHttpResponse('AuthorizeSuccess.txt');
        $response = new Response($this->getMockRequest(), (string) $httpResponse->getBody());

        $this->assertTrue($response->requiresConfirmation());
    }

    public function testGetCardReference()
    {
        $httpResponse = $this->getMockHttpResponse('AuthorizeSuccess.txt');
        $response = new Response($this->getMockRequest(), (string) $httpResponse->getBody());

        $this->assertSame('pm_1Euf5RFSbr6xR4YAwZ5fP28B', $response->getCardReference());

        $httpResponse = $this->getMockHttpResponse('CreatePaymentMethodSuccess.txt');
        $response = new Response($this->getMockRequest(), (string) $httpResponse->getBody());

        $this->assertSame('pm_1EUon32Tb35ankTnF6nuoRVE', $response->getCardReference());
    }

    public function testGetCustomerReference()
    {
        $httpResponse = $this->getMockHttpResponse('AuthorizeSuccess.txt');
        $response = new Response($this->getMockRequest(), (string) $httpResponse->getBody());

        $this->assertSame('cus_F1UMEEnT2OBgMg', $response->getCustomerReference());

        $httpResponse = $this->getMockHttpResponse('ConfirmIntent3dsRedirect.txt');
        $response = new Response($this->getMockRequest(), (string) $httpResponse->getBody());

        $this->assertSame('cus_Q8sHn93nAzgdn1', $response->getCustomerReference());
    }

    public function testGetCaptureMethod()
    {
        $httpResponse = $this->getMockHttpResponse('AuthorizeSuccess.txt');
        $response = new Response($this->getMockRequest(), (string) $httpResponse->getBody());

        $this->assertSame('manual', $response->getCaptureMethod());

        $httpResponse = $this->getMockHttpResponse('PurchaseSuccess.txt');
        $response = new Response($this->getMockRequest(), (string) $httpResponse->getBody());

        $this->assertSame('automatic', $response->getCaptureMethod());
    }

    public function testGetTransactionReference()
    {
        $httpResponse = $this->getMockHttpResponse('AuthorizeSuccess.txt');
        $response = new Response($this->getMockRequest(), (string) $httpResponse->getBody());

        $this->assertNull($response->getTransactionReference());

        $httpResponse = $this->getMockHttpResponse('PurchaseSuccess.txt');
        $response = new Response($this->getMockRequest(), (string) $httpResponse->getBody());

        $this->assertSame('ch_1EW0FmFSbr6xR4YAZyURWxQe', $response->getTransactionReference());
    }

    public function testRedirectsAndSuccess()
    {
        $httpResponse = $this->getMockHttpResponse('ConfirmIntent3dsRedirect.txt');
        $response = new Response($this->getMockRequest(), (string) $httpResponse->getBody());

        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertSame('https://hooks.stripe.com/3d_secure_2_eap/begin_test/src_1Ev1M5FSbr6xR4YAg5qdBN6B/src_client_secret_FPr4a6wAiVNi6YrnuI7vah6H', $response->getRedirectUrl());

        $httpResponse = $this->getMockHttpResponse('AuthorizeFailure.txt');
        $response = new Response($this->getMockRequest(), (string) $httpResponse->getBody());

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getRedirectUrl());
    }

    public function testCancelled()
    {
        $httpResponse = $this->getMockHttpResponse('CancelPaymentIntentSuccess.txt');
        $response = new Response($this->getMockRequest(), (string) $httpResponse->getBody());

        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isCancelled());

        $httpResponse = $this->getMockHttpResponse('ConfirmIntentSuccess.txt');
        $response = new Response($this->getMockRequest(), (string) $httpResponse->getBody());

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isCancelled());
    }
}
