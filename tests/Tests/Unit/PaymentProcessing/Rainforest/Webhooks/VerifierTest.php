<?php

/**
 * Tests for the Webhooks Verifier class
 *
 * @package   OpenEMR\Tests\Unit\PaymentProcessing\Rainforest\Webhooks
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR, Inc
 * @license   GNU General Public License 3
 * @link      https://www.open-emr.org
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Unit\PaymentProcessing\Rainforest\Webhooks;

use Nyholm\Psr7\Factory\Psr17Factory;
use OpenEMR\PaymentProcessing\Rainforest\Webhooks\Verifier;
use OpenEMR\PaymentProcessing\Rainforest\Webhooks\Webhook;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use UnexpectedValueException;

final class VerifierTest extends TestCase
{
    // A known secret in the format Svix uses (prefix_base64data)
    private const SECRET = 'whsec_' . 'MfKQ9r8GKYqrTwjUPD8ILPZIo2LaLaSw';

    private Psr17Factory $factory;

    protected function setUp(): void
    {
        $this->factory = new Psr17Factory();
    }

    // ------- Constructor tests -------

    public function testConstructorRejectsSecretWithoutUnderscore(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Invalid secret');
        new Verifier('nounderscore');
    }

    public function testConstructorAcceptsValidSecret(): void
    {
        $verifier = new Verifier(self::SECRET);
        $this->assertInstanceOf(Verifier::class, $verifier);
    }

    // ------- Missing header tests -------

    public function testVerifyThrowsWhenMissingSvixId(): void
    {
        $verifier = new Verifier(self::SECRET);
        $request = $this->buildRequest('{}', [
            'svix-timestamp' => '1234567890',
            'svix-signature' => 'v1,fake',
        ]);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('missing signing headers');
        $verifier->verify($request);
    }

    public function testVerifyThrowsWhenMissingSvixTimestamp(): void
    {
        $verifier = new Verifier(self::SECRET);
        $request = $this->buildRequest('{}', [
            'svix-id' => 'msg_abc123',
            'svix-signature' => 'v1,fake',
        ]);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('missing signing headers');
        $verifier->verify($request);
    }

    public function testVerifyThrowsWhenMissingSvixSignature(): void
    {
        $verifier = new Verifier(self::SECRET);
        $request = $this->buildRequest('{}', [
            'svix-id' => 'msg_abc123',
            'svix-timestamp' => '1234567890',
        ]);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('missing signing headers');
        $verifier->verify($request);
    }

    public function testVerifyThrowsWhenAllHeadersMissing(): void
    {
        $verifier = new Verifier(self::SECRET);
        $request = $this->buildRequest('{}', []);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('missing signing headers');
        $verifier->verify($request);
    }

    // ------- Signature verification tests -------

    public function testVerifyRejectsInvalidSignature(): void
    {
        $verifier = new Verifier(self::SECRET);
        $request = $this->buildRequest('{"event_type":"test","data":{}}', [
            'svix-id' => 'msg_abc123',
            'svix-timestamp' => '1234567890',
            'svix-signature' => 'v1,invalidsignaturedata',
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid signature');
        $verifier->verify($request);
    }

    public function testVerifyRejectsSignatureWithWrongVersion(): void
    {
        $verifier = new Verifier(self::SECRET);
        $body = '{"event_type":"test","data":{}}';
        $correctSig = $this->computeSignature('msg_abc123', '1234567890', $body);

        $request = $this->buildRequest($body, [
            'svix-id' => 'msg_abc123',
            'svix-timestamp' => '1234567890',
            'svix-signature' => 'v2,' . $correctSig, // wrong version prefix
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid signature');
        $verifier->verify($request);
    }

    public function testVerifyAcceptsValidSignature(): void
    {
        $verifier = new Verifier(self::SECRET);
        $body = '{"event_type":"payment.completed","data":{"merchant_id":"m_123"}}';
        $msgId = 'msg_abc123';
        $timestamp = '1706000000';
        $signature = $this->computeSignature($msgId, $timestamp, $body);

        $request = $this->buildRequest($body, [
            'svix-id' => $msgId,
            'svix-timestamp' => $timestamp,
            'svix-signature' => 'v1,' . $signature,
        ]);

        $webhook = $verifier->verify($request);

        $this->assertInstanceOf(Webhook::class, $webhook);
        $this->assertSame('payment.completed', $webhook->eventType);
        $this->assertSame('m_123', $webhook->getMerchantId());
    }

    public function testVerifyAcceptsCorrectSignatureAmongMultiple(): void
    {
        $verifier = new Verifier(self::SECRET);
        $body = '{"event_type":"refund.created","data":{"merchant_id":"m_456"}}';
        $msgId = 'msg_def456';
        $timestamp = '1706000001';
        $correctSig = $this->computeSignature($msgId, $timestamp, $body);

        // Multiple signatures separated by space, correct one is second
        $signatures = 'v1,incorrectsignature v1,' . $correctSig;

        $request = $this->buildRequest($body, [
            'svix-id' => $msgId,
            'svix-timestamp' => $timestamp,
            'svix-signature' => $signatures,
        ]);

        $webhook = $verifier->verify($request);

        $this->assertInstanceOf(Webhook::class, $webhook);
        $this->assertSame('refund.created', $webhook->eventType);
    }

    public function testVerifyRejectsWhenBodyTampered(): void
    {
        $verifier = new Verifier(self::SECRET);
        $originalBody = '{"event_type":"test","data":{"merchant_id":"m_1"}}';
        $tamperedBody = '{"event_type":"test","data":{"merchant_id":"m_2"}}';
        $msgId = 'msg_tamper';
        $timestamp = '1706000002';
        // Sign with original body but send tampered body
        $signature = $this->computeSignature($msgId, $timestamp, $originalBody);

        $request = $this->buildRequest($tamperedBody, [
            'svix-id' => $msgId,
            'svix-timestamp' => $timestamp,
            'svix-signature' => 'v1,' . $signature,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid signature');
        $verifier->verify($request);
    }

    public function testVerifyRejectsWhenTimestampTampered(): void
    {
        $verifier = new Verifier(self::SECRET);
        $body = '{"event_type":"test","data":{"merchant_id":"m_1"}}';
        $msgId = 'msg_ts';
        // Sign with one timestamp, send another
        $signature = $this->computeSignature($msgId, '1706000003', $body);

        $request = $this->buildRequest($body, [
            'svix-id' => $msgId,
            'svix-timestamp' => '1706000099',
            'svix-signature' => 'v1,' . $signature,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid signature');
        $verifier->verify($request);
    }

    public function testVerifyRejectsWhenMsgIdTampered(): void
    {
        $verifier = new Verifier(self::SECRET);
        $body = '{"event_type":"test","data":{"merchant_id":"m_1"}}';
        $timestamp = '1706000004';
        // Sign with one ID, send another
        $signature = $this->computeSignature('msg_original', $timestamp, $body);

        $request = $this->buildRequest($body, [
            'svix-id' => 'msg_tampered',
            'svix-timestamp' => $timestamp,
            'svix-signature' => 'v1,' . $signature,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid signature');
        $verifier->verify($request);
    }

    public function testVerifyThrowsOnInvalidJsonBody(): void
    {
        $verifier = new Verifier(self::SECRET);
        $body = 'not valid json';
        $msgId = 'msg_badjson';
        $timestamp = '1706000005';
        $signature = $this->computeSignature($msgId, $timestamp, $body);

        $request = $this->buildRequest($body, [
            'svix-id' => $msgId,
            'svix-timestamp' => $timestamp,
            'svix-signature' => 'v1,' . $signature,
        ]);

        $this->expectException(\JsonException::class);
        $verifier->verify($request);
    }

    // ------- Helpers -------

    /**
     * Compute the expected HMAC signature for given inputs using the test secret.
     */
    private function computeSignature(string $msgId, string $timestamp, string $body): string
    {
        [, $data] = explode('_', self::SECRET, 2);
        $secretBytes = base64_decode($data);
        $signedContent = sprintf('%s.%s.%s', $msgId, $timestamp, $body);
        $signature = hash_hmac('sha256', $signedContent, $secretBytes, true);
        return base64_encode($signature);
    }

    /**
     * Build a PSR-7 ServerRequest with the given body and headers.
     */
    private function buildRequest(string $body, array $headers): ServerRequestInterface
    {
        $request = $this->factory->createServerRequest('POST', '/webhooks/rainforest');
        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }
        $request = $request->withBody($this->factory->createStream($body));
        return $request;
    }
}
