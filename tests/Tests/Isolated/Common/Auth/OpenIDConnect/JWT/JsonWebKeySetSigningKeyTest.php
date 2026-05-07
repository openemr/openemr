<?php

/**
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\OpenIDConnect\JWT;

use GuzzleHttp\Psr7\Response;
use OpenEMR\Common\Auth\Oidc\Discovery\OidcUrlValidator;
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

    public function testValidatorRejectsUnsafeJwksUri(): void
    {
        // Strict policy must reject http:// jwks_uri before the HTTP client is touched.
        $this->expectException(JWKValidatorException::class);
        $this->expectExceptionMessage('unsafe jwks_uri');

        try {
            new JsonWebKeySet(
                $this->httpClient,
                'http://accounts.example.com/jwks',
                null,
                new NullLogger(),
                urlValidator: new OidcUrlValidator(),
            );
        } finally {
            self::assertSame(0, $this->httpClient->getRequestCount(), 'No HTTP request should be made for an unsafe URL');
        }
    }

    /**
     * Aisle finding (CWE-248) regression. Before the fix,
     * json_decode of malformed content returned null and the
     * property_exists($jwks, 'keys') call hit PHP 8's TypeError
     * ("argument must be of type object|string, null given") — which
     * downstream catches don't handle, leaking as an unhandled fatal.
     * Now the constructor throws JWKValidatorException, which the
     * existing OidcTokenValidator and JWTClientAuthenticationService
     * catches translate to invalid_client / token-validation-failed.
     */
    public function testRejectsMalformedJsonInJwksContent(): void
    {
        $this->expectException(JWKValidatorException::class);
        $this->expectExceptionMessage('Malformed JWKS: invalid JSON');

        new JsonWebKeySet($this->httpClient, null, 'not json at all', new NullLogger());
    }

    public function testRejectsJwksDocumentWithoutKeysProperty(): void
    {
        // Valid JSON object but no `keys` field. Catches IdP responses
        // that return an envelope or error shape instead of JWKS.
        $this->expectException(JWKValidatorException::class);
        $this->expectExceptionMessage('Malformed JWKS: missing or invalid keys');

        new JsonWebKeySet($this->httpClient, null, '{"foo": "bar"}', new NullLogger());
    }

    public function testRejectsJwksDocumentWithNonArrayKeysProperty(): void
    {
        // `keys` exists but isn't an array. Without this check, extractKeys()
        // would silently return an empty list and the bad shape would only
        // surface much later as "no signing key found for kid".
        $this->expectException(JWKValidatorException::class);
        $this->expectExceptionMessage('Malformed JWKS: missing or invalid keys');

        new JsonWebKeySet($this->httpClient, null, '{"keys": "not_an_array"}', new NullLogger());
    }

    public function testRejectsJwksDocumentWithEmptyKeysArray(): void
    {
        // Same shape problem as above but with an actual array — empty.
        // Fail closed at construction so the broken state doesn't propagate.
        $this->expectException(JWKValidatorException::class);
        $this->expectExceptionMessage('Malformed JWKS: no valid keys');

        new JsonWebKeySet($this->httpClient, null, '{"keys": []}', new NullLogger());
    }

    /**
     * Aisle round-3 finding #5 (CWE-400) regression. Server is honest
     * about the body size via Content-Length — bail before touching the
     * stream so a hostile endpoint can't drive memory exhaustion just by
     * advertising a huge response.
     */
    public function testRejectsJwksBodyAdvertisingHugeContentLength(): void
    {
        $this->httpClient->setNextResponse(200, '{}', ['Content-Length' => '9999999']);

        $this->expectException(JWKValidatorException::class);
        $this->expectExceptionMessage('JWKS response too large (Content-Length');

        new JsonWebKeySet($this->httpClient, self::JWKS_URI, null, new NullLogger());
    }

    /**
     * Same finding, chunked / lying-server path: Content-Length absent or
     * misreported, but the actual body exceeds the cap. The streaming
     * read loop must detect overflow and fail closed.
     */
    public function testRejectsJwksBodyExceedingMaxBytesViaStreamRead(): void
    {
        // 300 KiB > 256 KiB cap. Body is just padding — never decoded as
        // JSON because the read loop bails first.
        $oversized = str_repeat('x', 300_000);
        $this->httpClient->setNextResponse(200, $oversized);

        $this->expectException(JWKValidatorException::class);
        $this->expectExceptionMessage('JWKS response too large');

        new JsonWebKeySet($this->httpClient, self::JWKS_URI, null, new NullLogger());
    }

    /**
     * Round-3 #5 — too many keys. Real-world JWKS documents publish 1-5;
     * 51 is pathological. Reject before iterating to bound cumulative
     * per-key parsing cost.
     */
    public function testRejectsJwksWithTooManyKeys(): void
    {
        // Synthetic dummy keys — content doesn't matter; the count check
        // runs before per-key extraction.
        $keys = [];
        for ($i = 0; $i < 51; $i++) {
            $keys[] = [
                'kty' => 'RSA',
                'kid' => 'key-' . $i,
                'n' => $this->jwkComponents['n'],
                'e' => $this->jwkComponents['e'],
            ];
        }
        $json = json_encode(['keys' => $keys]);
        self::assertIsString($json);

        $this->expectException(JWKValidatorException::class);
        $this->expectExceptionMessage('JWKS contains too many keys');

        new JsonWebKeySet($this->httpClient, null, $json, new NullLogger());
    }

    /**
     * Round-3 #5 — RSA modulus exceeds permitted size. Caps the input
     * BEFORE phpseclib's BigInteger / PublicKeyLoader::load runs; both
     * scale poorly with input size.
     */
    public function testRejectsJwkWithOversizedRsaModulus(): void
    {
        // 2000 chars > 1366-char base64url cap (which corresponds to
        // 8192-bit RSA, well above the 4096-bit common ceiling).
        $oversizedN = str_repeat('A', 2000);
        $jwksJson = json_encode([
            'keys' => [[
                'kty' => 'RSA',
                'kid' => self::KID,
                'n' => $oversizedN,
                'e' => $this->jwkComponents['e'],
            ]],
        ]);
        self::assertIsString($jwksJson);
        // Need two queued responses: rotation refetch fires on kid miss.
        $this->httpClient->setNextResponse(200, $jwksJson);
        $this->httpClient->setNextResponse(200, $jwksJson);

        $set = new JsonWebKeySet($this->httpClient, self::JWKS_URI, null, new NullLogger());

        $this->expectException(JWKValidatorException::class);
        $this->expectExceptionMessage('RSA JWK parameters exceed permitted size');
        $set->getSigningKeyAsPem(self::KID, 'RS256');
    }

    /**
     * Round-3 #5 — RSA exponent exceeds permitted size. Real exponents
     * are 3 bytes (65537); 100 base64url chars is pathological.
     */
    public function testRejectsJwkWithOversizedRsaExponent(): void
    {
        $oversizedE = str_repeat('A', 100); // > 44-char cap
        $jwksJson = json_encode([
            'keys' => [[
                'kty' => 'RSA',
                'kid' => self::KID,
                'n' => $this->jwkComponents['n'],
                'e' => $oversizedE,
            ]],
        ]);
        self::assertIsString($jwksJson);
        $this->httpClient->setNextResponse(200, $jwksJson);
        $this->httpClient->setNextResponse(200, $jwksJson);

        $set = new JsonWebKeySet($this->httpClient, self::JWKS_URI, null, new NullLogger());

        $this->expectException(JWKValidatorException::class);
        $this->expectExceptionMessage('RSA JWK parameters exceed permitted size');
        $set->getSigningKeyAsPem(self::KID, 'RS256');
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
    /** @var list<array{int, string, array<string, string>}> */
    private array $responses = [];

    private int $requestCount = 0;

    /**
     * @param array<string, string> $extraHeaders Additional response headers
     *   merged with the default Content-Type. Used by size-cap tests that
     *   need to assert behavior under specific Content-Length values.
     */
    public function setNextResponse(int $statusCode, string $body, array $extraHeaders = []): void
    {
        $this->responses[] = [$statusCode, $body, $extraHeaders];
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->requestCount++;
        $index = min($this->requestCount - 1, count($this->responses) - 1);
        if ($index < 0) {
            return new Response(200, [], '{}');
        }
        [$status, $body, $extraHeaders] = $this->responses[$index];
        $headers = array_merge(['Content-Type' => 'application/json'], $extraHeaders);
        return new Response($status, $headers, $body);
    }

    public function getRequestCount(): int
    {
        return $this->requestCount;
    }
}
