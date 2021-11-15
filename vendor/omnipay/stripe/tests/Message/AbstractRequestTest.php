<?php

namespace Omnipay\Stripe\Message;

use GuzzleHttp\Psr7\Request;
use Mockery;
use Omnipay\Tests\TestCase;

class AbstractRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = Mockery::mock('\Omnipay\Stripe\Message\AbstractRequest')->makePartial();
        $this->request->initialize();
    }

    public function testCardReference()
    {
        $this->assertSame($this->request, $this->request->setCardReference('abc123'));
        $this->assertSame('abc123', $this->request->getCardReference());
    }

    public function testCardToken()
    {
        $this->assertSame($this->request, $this->request->setToken('abc123'));
        $this->assertSame('abc123', $this->request->getToken());
    }

    public function testSource()
    {
        $this->assertSame($this->request, $this->request->setSource('abc123'));
        $this->assertSame('abc123', $this->request->getSource());
    }

    public function testCardData()
    {
        $card = $this->getValidCard();
        $this->request->setCard($card);
        $data = $this->request->getCardData();

        $this->assertSame($card['number'], $data['number']);
        $this->assertSame($card['cvv'], $data['cvc']);
    }

    public function testCardDataEmptyCvv()
    {
        $card = $this->getValidCard();
        $card['cvv'] = '';
        $this->request->setCard($card);
        $data = $this->request->getCardData();

        $this->assertTrue(empty($data['cvv']));
    }

    public function testMetadata()
    {
        $this->assertSame($this->request, $this->request->setMetadata(array('foo' => 'bar')));
        $this->assertSame(array('foo' => 'bar'), $this->request->getMetadata());
    }

    public function testIdempotencyKey()
    {
        $this->request->setIdempotencyKeyHeader('UUID');

        $this->assertSame('UUID', $this->request->getIdempotencyKeyHeader());

        $headers = $this->request->getHeaders();

        $this->assertArrayHasKey('Idempotency-Key', $headers);
        $this->assertSame('UUID', $headers['Idempotency-Key']);

        $httpRequest = new Request(
            'GET',
            '/',
            $headers
        );

        $this->assertTrue($httpRequest->hasHeader('Idempotency-Key'));
    }

    public function testStripeVersion()
    {
        $this->request->setStripeVersion('2019-05-16');

        $this->assertSame('2019-05-16', $this->request->getStripeVersion());

        $headers = $this->request->getHeaders();

        $this->assertArrayHasKey('Stripe-Version', $headers);
        $this->assertSame('2019-05-16', $headers['Stripe-Version']);

        $httpRequest = new Request(
            'GET',
            '/',
            $headers
        );

        $this->assertTrue($httpRequest->hasHeader('Stripe-Version'));
    }


    public function testConnectedStripeAccount()
    {
        $this->request->setConnectedStripeAccountHeader('ACCOUNT_ID');

        $this->assertSame('ACCOUNT_ID', $this->request->getConnectedStripeAccountHeader());

        $headers = $this->request->getHeaders();

        $this->assertArrayHasKey('Stripe-Account', $headers);
        $this->assertSame('ACCOUNT_ID', $headers['Stripe-Account']);

        $httpRequest = new Request(
            'GET',
            '/',
            $headers
        );

        $this->assertTrue($httpRequest->hasHeader('Stripe-Account'));
    }
}
