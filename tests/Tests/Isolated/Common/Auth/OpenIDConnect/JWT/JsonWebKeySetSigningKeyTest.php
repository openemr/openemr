<?php

/**
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\OpenIDConnect\JWT;

use GuzzleHttp\Psr7\Response;
use OpenEMR\Common\Auth\OpenIDConnect\JWT\JsonWebKeySet;
use OpenEMR\Common\Auth\OpenIDConnect\JWT\JWKValidatorException;
use phpseclib3\Crypt\RSA;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\NullLogger;

final class JsonWebKeySetSigningKeyTest extends TestCase
{
    private const JWKS_URI = 'https://accounts.example.com/jwks';
    private const KID = 'test-kid-1';
    private const ROTATED_KID = 'rotated-kid-2';

    private QueueingHttpClient $httpClient;

    /** @var array{n: string, e: string} */
    private array $jwkComponents;

    protected function setUp(): void
    {
        $rsaKey = RSA::createKey(2048);
        /** @var \phpseclib3\Crypt\RSA\PublicKey $publicKey */
        $publicKey = $rsaKey->getPublicKey();
        /** @var string $publicKeyPem */
        $publicKeyPem = $publicKey->toString('PKCS8');

        $resource = openssl_pkey_get_public($publicKeyPem);
        self::assertNotFalse($resource);
        $details = openssl_pkey_get_details($resource);
        self::assertIsArray($details);
        self::assertArrayHasKey('rsa', $details);
        /** @var array{n: string, e: string} $rsa */
        $rsa = $details['rsa'];
        $this->jwkComponents = [
            'n' => rtrim(strtr(base64_encode($rsa['n']), '+/', '-_'), '='),
            'e' => rtrim(strtr(base64_encode($rsa['e']), '+/', '-_'), '='),
        ];

        $this->httpClient = new QueueingHttpClient();
    }

    /**
     * @param non-empty-string $kid
     * @param array<string, mixed> $extra Additional JWK fields to merge (alg, use, etc.).
     * @return non-empty-string
     */
    private function jwksJson(string $kid, array $extra = []): string
    {
        $key = array_merge([
            'kty' => 'RSA',
            'kid' => $kid,
            'n' => $this->jwkComponents['n'],
            'e' => $this->jwkComponents['e'],
        ], $extra);

        $json = json_encode(['keys' => [$key]]);
        self::assertIsString($json);
        self::assertNotEmpty($json);
        return $json;
    }

    public function testReturnsInMemoryPemForMatchingKidAndAlg(): void
    {
        $this->httpClient->setNextResponse(200, $this->jwksJson(self::KID, ['alg' => 'RS256']));
        $set = new JsonWebKeySet($this->httpClient, self::JWKS_URI, null, new NullLogger());

        $pem = $set->getSigningKeyAsPem(self::KID, 'RS256');

        self::assertStringContainsString('-----BEGIN PUBLIC KEY-----', $pem->contents());
    }

    public function testAcceptsJwkWithoutAlgField(): void
    {
        // JWK lacking "alg" — caller supplies the algorithm from the token header.
        $this->httpClient->setNextResponse(200, $this->jwksJson(self::KID));
        $set = new JsonWebKeySet($this->httpClient, self::JWKS_URI, null, new NullLogger());

        $pem = $set->getSigningKeyAsPem(self::KID, 'RS256');

        self::assertStringContainsString('-----BEGIN PUBLIC KEY-----', $pem->contents());
    }

    public function testRejectsJwkWithMismatchedAlg(): void
    {
        $this->httpClient->setNextResponse(200, $this->jwksJson(self::KID, ['alg' => 'RS512']));
        $this->httpClient->setNextResponse(200, $this->jwksJson(self::KID, ['alg' => 'RS512']));
        $set = new JsonWebKeySet($this->httpClient, self::JWKS_URI, null, new NullLogger());

        $this->expectException(JWKValidatorException::class);
        $this->expectExceptionMessage("No signing key found for kid '" . self::KID . "'");
        $set->getSigningKeyAsPem(self::KID, 'RS256');
    }

    public function testRejectsJwkWithEncUse(): void
    {
        $this->httpClient->setNextResponse(200, $this->jwksJson(self::KID, ['use' => 'enc']));
        $this->httpClient->setNextResponse(200, $this->jwksJson(self::KID, ['use' => 'enc']));
        $set = new JsonWebKeySet($this->httpClient, self::JWKS_URI, null, new NullLogger());

        $this->expectException(JWKValidatorException::class);
        $set->getSigningKeyAsPem(self::KID, 'RS256');
    }

    public function testRotationRefetchFindsNewKidAfterRefresh(): void
    {
        // Initial fetch returns KID only — subsequent refresh returns both KID and ROTATED_KID.
        $this->httpClient->setNextResponse(200, $this->jwksJson(self::KID));

        $rotatedJson = json_encode([
            'keys' => [
                [
                    'kty' => 'RSA',
                    'kid' => self::KID,
                    'n' => $this->jwkComponents['n'],
                    'e' => $this->jwkComponents['e'],
                ],
                [
                    'kty' => 'RSA',
                    'kid' => self::ROTATED_KID,
                    'n' => $this->jwkComponents['n'],
                    'e' => $this->jwkComponents['e'],
                ],
            ],
        ]);
        self::assertIsString($rotatedJson);
        $this->httpClient->setNextResponse(200, $rotatedJson);

        $set = new JsonWebKeySet($this->httpClient, self::JWKS_URI, null, new NullLogger());

        $pem = $set->getSigningKeyAsPem(self::ROTATED_KID, 'RS256');

        self::assertStringContainsString('-----BEGIN PUBLIC KEY-----', $pem->contents());
        self::assertSame(2, $this->httpClient->getRequestCount(), 'Should refetch once on kid miss');
    }

    public function testThrowsWhenKidStillMissingAfterRefresh(): void
    {
        $this->httpClient->setNextResponse(200, $this->jwksJson(self::KID));
        $this->httpClient->setNextResponse(200, $this->jwksJson(self::KID));

        $set = new JsonWebKeySet($this->httpClient, self::JWKS_URI, null, new NullLogger());

        $this->expectException(JWKValidatorException::class);
        $this->expectExceptionMessage("No signing key found for kid 'unknown-kid'");
        $set->getSigningKeyAsPem('unknown-kid', 'RS256');
    }

    public function testRejectsNonRsaKty(): void
    {
        $jwksJson = json_encode([
            'keys' => [[
                'kty' => 'EC',
                'kid' => self::KID,
                'crv' => 'P-256',
                'x' => 'not-a-real-x',
                'y' => 'not-a-real-y',
            ]],
        ]);
        self::assertIsString($jwksJson);
        $this->httpClient->setNextResponse(200, $jwksJson);
        $this->httpClient->setNextResponse(200, $jwksJson);

        $set = new JsonWebKeySet($this->httpClient, self::JWKS_URI, null, new NullLogger());

        $this->expectException(JWKValidatorException::class);
        $this->expectExceptionMessage('Unsupported JWK key type: EC');
        $set->getSigningKeyAsPem(self::KID, 'ES256');
    }

    public function testRejectsRsaJwkMissingModulusOrExponent(): void
    {
        $jwksJson = json_encode([
            'keys' => [[
                'kty' => 'RSA',
                'kid' => self::KID,
                // n and e deliberately omitted
            ]],
        ]);
        self::assertIsString($jwksJson);
        $this->httpClient->setNextResponse(200, $jwksJson);

        $set = new JsonWebKeySet($this->httpClient, self::JWKS_URI, null, new NullLogger());

        $this->expectException(JWKValidatorException::class);
        $this->expectExceptionMessage('RSA JWK missing "n" or "e" parameter');
        $set->getSigningKeyAsPem(self::KID, 'RS256');
    }

    public function testRefreshOnInlineJwksIsNoOp(): void
    {
        $set = new JsonWebKeySet($this->httpClient, null, $this->jwksJson(self::KID, ['alg' => 'RS256']), new NullLogger());

        // Inline-JWKS construction must not touch HTTP at all.
        self::assertSame(0, $this->httpClient->getRequestCount());

        $set->refresh();

        // refresh() is a no-op when there's no URI — still zero HTTP requests.
        self::assertSame(0, $this->httpClient->getRequestCount());

        // getSigningKeyAsPem should still work against the inline JWKS.
        $pem = $set->getSigningKeyAsPem(self::KID, 'RS256');
        self::assertStringContainsString('-----BEGIN PUBLIC KEY-----', $pem->contents());
    }
}

/**
 * Minimal PSR-18 fake that queues multiple responses — each sendRequest()
 * call returns the next queued response (or the last one if the queue is
 * drained). Needed for rotation tests that require a second distinct
 * response on refresh.
 *
 * @internal
 */
final class QueueingHttpClient implements ClientInterface
{
    /** @var list<array{int, string}> */
    private array $responses = [];

    private int $requestCount = 0;

    public function setNextResponse(int $statusCode, string $body): void
    {
        $this->responses[] = [$statusCode, $body];
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->requestCount++;
        $index = min($this->requestCount - 1, count($this->responses) - 1);
        if ($index < 0) {
            return new Response(200, [], '{}');
        }
        [$status, $body] = $this->responses[$index];
        return new Response($status, ['Content-Type' => 'application/json'], $body);
    }

    public function getRequestCount(): int
    {
        return $this->requestCount;
    }
}
