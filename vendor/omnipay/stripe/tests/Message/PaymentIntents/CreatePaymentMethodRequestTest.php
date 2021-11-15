<?php

namespace Omnipay\Stripe\Message\PaymentIntents;

use Omnipay\Tests\TestCase;

class CreatePaymentMethodRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new CreatePaymentMethodRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setCard($this->getValidCard());
    }

    public function testEndpoint()
    {
        $this->assertSame('https://api.stripe.com/v1/payment_methods', $this->request->getEndpoint());
    }

    /**
     * @expectedException \Omnipay\Common\Exception\InvalidRequestException
     * @expectedExceptionMessage The card parameter is required
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

        $this->assertSame('xyz', $data['card']['token']);
    }

    /**
     * Impossible to use a card reference.
     *
     * @expectedException \Omnipay\Common\Exception\InvalidRequestException
     * @expectedExceptionMessage The card parameter is required
     */
    public function testDataWithCardReference()
    {
        $this->request->setCard(null);
        $this->request->setCardReference('xyz');
        $data = $this->request->getData();

        $this->assertSame('xyz', $data['source']);
    }

    /**
     * Impossible to use a source reference.
     *
     * @expectedException \Omnipay\Common\Exception\InvalidRequestException
     * @expectedExceptionMessage The card parameter is required
     */
    public function testDataWithSource()
    {
        $this->request->setCard(null);
        $this->request->setSource('xyz');
        $data = $this->request->getData();

        $this->assertSame('xyz', $data['source']);
    }

    public function testNoBillingDetails()
    {
        $this->request->setCard(null);
        $this->request->setToken('xyz');
        $this->request->getData();
    }

    public function testDataWithCard()
    {
        $card = $this->getValidCard();
        $this->request->setCard($card);
        $data = $this->request->getData();

        $this->assertSame($card['number'], $data['card']['number']);
        $this->assertSame($card['billingAddress1'], $data['billing_details']['address']['line1']);
        $this->assertSame($card['firstName'] . ' ' . $card['lastName'], $data['billing_details']['name']);
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('CreatePaymentMethodSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertSame(null, $response->getCustomerReference());
        $this->assertSame('pm_1EUon32Tb35ankTnF6nuoRVE', $response->getCardReference());
        $this->assertNull($response->getMessage());
    }

    public function testSendFailure()
    {
        $this->setMockHttpResponse('CreatePaymentMethodFailure.txt');
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertNull($response->getCardReference());
        $this->assertSame('Invalid integer: xyz', $response->getMessage());
    }

    public function testCardWithoutEmail()
    {
        $card = $this->getValidCard();
        $this->request->setCard($card);
        $data = $this->request->getData();

        $this->assertArrayNotHasKey('email', $data['billing_details']);
    }
}
