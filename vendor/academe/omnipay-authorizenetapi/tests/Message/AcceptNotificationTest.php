<?php

namespace Omnipay\AuthorizeNetApi\Message;

/**
 * Some tests still worth doing:
 * - invalid signature on trying to fetch the result asserts exception.
 * - missing signature header counts as invalid signature.
 */

use Omnipay\Tests\TestCase;
use Omnipay\Common\CreditCard;
use Academe\AuthorizeNet\Request\Model\NameAddress;

class AcceptNotificationTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $httpRequest = $this->getHttpRequest();

        // Omnipay needs a way to quickly set these up.
        // 'AcceptNotificationSuccess.txt'

        $payload = '{"notificationId":"701bf27d-d46f-4c3b-82f2-066448e2901e","eventType":"net.authorize.payment.authorization.created","eventDate":"2019-01-31T14:38:42.6937313Z","webhookId":"e6b3764d-5677-4fb1-a929-2e25a02f3073","payload":{"responseCode":1,"authCode":"P1XHLC","avsResponse":"Y","authAmount":7.67,"entityName":"transaction","id":"60116007277"}}';

        // Should actually be a POST, but can't work out how to do that.
        // Send headers through server parameter.

        $httpRequest->initialize(
            [],
            [],
            [],
            [],
            [],
            [
                'HTTP_X-Anet-Signature' => 'sha512=13151697A33C77CB102AAA060CD58FB2394696BAEA981047A31830A8A1853A4C8D454E696871A38A68B6940B8746FC096111334D584579F6792F568A622A0373',
                'HTTP_Content-Type' => 'application/json',
            ],
            $payload
        );
        //var_dump((string)$httpRequest);

        $this->request = new AcceptNotification(
            $this->getHttpClient(),
            $httpRequest
        );
        //var_dump($this->request);

        $this->request->initialize([
            'signatureKey' => '339E42F5D962293A925C244313E8C3546FD765AD202BFF3805F4C8459193E2AA1C106F476D6907C7A85714CCD69BB7BF184D17ECCDD546CED7EF69DB4C4AD723',
        ]);
    }

    public function testSuccess()
    {
        $this->assertSame('60116007277', $this->request->getTransactionReference());

        $this->assertSame('completed', $this->request->getTransactionStatus());
        $this->assertSame('', $this->request->getMessage());

        $this->assertSame('payment', $this->request->getEventTarget());
        $this->assertSame('authorization', $this->request->getEventSubTarget());
        $this->assertSame('created', $this->request->getEventAction());

        $this->assertTrue($this->request->isSignatureValid());
        $this->assertNull($this->request->assertSignature());
    }

    /**
     * @expectedException \Omnipay\Common\Exception\InvalidRequestException
     * @expectedExceptionMessage Invalid or missing signature
     */
    public function testInvalidSignature()
    {
        // Mix up the key.
        $this->request->setSignatureKey(str_shuffle($this->request->getSignatureKey()));

        $this->assertFalse($this->request->isSignatureValid());
        $this->request->assertSignature();
    }

    /**
     * Signature assertion can be suppressed - it still shows as
     * invalid if the application checks, but does not thrown an exception.
     */
    public function testInvalidSignatureSuppressed()
    {
        // Mix up the key.
        $this->request->setSignatureKey(str_shuffle($this->request->getSignatureKey()));
        $this->request->setDisableWebhookSignature(true);

        $this->assertFalse($this->request->isSignatureValid());
        $this->assertNull($this->request->assertSignature());
    }
}
