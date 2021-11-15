<?php

namespace Omnipay\Stripe\Message\PaymentIntents;

use GuzzleHttp\Psr7\Request;
use Mockery;
use Omnipay\Tests\TestCase;

class AbstractRequestTest extends TestCase
{
    /** @var AbstractRequest */
    protected $request;

    public function setUp()
    {
        $this->request = Mockery::mock('\Omnipay\Stripe\Message\PaymentIntents\AbstractRequest')->makePartial();
        $this->request->initialize();
    }

    public function testPaymentIntentReference()
    {
        $this->assertSame($this->request, $this->request->setPaymentIntentReference('abc123'));
        $this->assertSame('abc123', $this->request->getPaymentIntentReference());
    }

    public function testPaymentMethodAlternatives()
    {
        $this->request->setCardReference('card_some_card');
        $this->assertSame('card_some_card', $this->request->getCardReference());
        $this->assertSame('card_some_card', $this->request->getPaymentMethod());
    }
}
