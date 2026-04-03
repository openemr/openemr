<?php

/**
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\Oidc\Token;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use OpenEMR\Common\Auth\Oidc\Cache\FilesystemCache;
use OpenEMR\Common\Auth\Oidc\Identity\StandardClaimMapper;
use OpenEMR\Common\Auth\Oidc\Token\JwksClient;
use OpenEMR\Common\Auth\Oidc\Token\OidcTokenValidationException;
use OpenEMR\Common\Auth\Oidc\Token\OidcTokenValidator;
use OpenEMR\Common\Auth\Oidc\Token\OidcValidationParameters;
use OpenEMR\Common\Auth\Oidc\Token\ValidatedToken;
use OpenEMR\Tests\Isolated\Common\Auth\Oidc\Discovery\FakeHttpClient;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use phpseclib3\Crypt\RSA;

#[CoversClass(OidcTokenValidator::class)]
#[CoversClass(ValidatedToken::class)]
#[CoversClass(OidcValidationParameters::class)]
final class OidcTokenValidatorTest extends TestCase
{
    private const ISSUER = 'https://accounts.example.com';
    private const AUDIENCE = 'my-client-id';
    private const JWKS_URI = 'https://accounts.example.com/jwks';
    private const KID = 'test-key-1';

    private FakeHttpClient $httpClient;
    private FilesystemCache $cache;
    private string $cacheDir;
    private OidcTokenValidator $validator;
    private OidcValidationParameters $params;
    private FakeClock $clock;

    /** @var InMemory */
    private InMemory $privateKey;

    /** @var array{n: string, e: string} Base64url-encoded RSA key components */
    private array $jwkComponents;

    protected function setUp(): void
    {
        // Generate RSA key pair for test signing
        $rsaKey = RSA::createKey(2048);
        $privateKeyPem = $rsaKey->toString('PKCS8');
        $publicKeyPem = $rsaKey->getPublicKey()->toString('PKCS8');

        $this->privateKey = InMemory::plainText($privateKeyPem);

        // Extract n and e for JWKS response
        $publicKeyDetails = openssl_pkey_get_details(openssl_pkey_get_public($publicKeyPem));
        assert(is_array($publicKeyDetails) && isset($publicKeyDetails['rsa']));
        $this->jwkComponents = [
            'n' => rtrim(strtr(base64_encode($publicKeyDetails['rsa']['n']), '+/', '-_'), '='),
            'e' => rtrim(strtr(base64_encode($publicKeyDetails['rsa']['e']), '+/', '-_'), '='),
        ];

        $this->httpClient = new FakeHttpClient();
        $this->cacheDir = sys_get_temp_dir() . '/oidc_validator_test_' . bin2hex(random_bytes(8));
        mkdir($this->cacheDir, 0o755, true);
        $this->cache = new FilesystemCache($this->cacheDir);

        $jwksClient = new JwksClient($this->httpClient, $this->cache);
        $this->clock = new FakeClock(new \DateTimeImmutable('2026-01-15T12:00:00Z'));

        $this->validator = new OidcTokenValidator(
            $jwksClient,
            new StandardClaimMapper(),
            $this->clock,
        );

        $this->params = new OidcValidationParameters(
            expectedIssuer: self::ISSUER,
            expectedAudience: self::AUDIENCE,
        );

        $this->setJwksResponse();
    }

    protected function tearDown(): void
    {
        $this->cache->clear();
        @rmdir($this->cacheDir);
    }

    private function setJwksResponse(): void
    {
        $this->httpClient->setNextResponse(200, json_encode([
            'keys' => [
                [
                    'kty' => 'RSA',
                    'kid' => self::KID,
                    'alg' => 'RS256',
                    'use' => 'sig',
                    'n' => $this->jwkComponents['n'],
                    'e' => $this->jwkComponents['e'],
                ],
            ],
        ], JSON_THROW_ON_ERROR));
    }

    private function buildToken(
        ?string $issuer = self::ISSUER,
        ?string $audience = self::AUDIENCE,
        ?string $kid = self::KID,
        ?\DateTimeImmutable $issuedAt = null,
        ?\DateTimeImmutable $expiresAt = null,
        ?string $subject = 'user-123',
        ?string $email = 'user@example.com',
        ?string $name = 'Test User',
        ?string $jti = null,
    ): string {
        $now = $this->clock->now();
        $iat = $issuedAt ?? $now;
        $exp = $expiresAt ?? $now->modify('+1 hour');

        $config = Configuration::forAsymmetricSigner(
            new Sha256(),
            $this->privateKey,
            InMemory::plainText('unused'),
        );

        $builder = $config->builder()
            ->issuedAt($iat)
            ->expiresAt($exp)
            ->canOnlyBeUsedAfter($iat)
            ->withHeader('kid', $kid);

        if ($issuer !== null) {
            $builder = $builder->issuedBy($issuer);
        }

        if ($audience !== null) {
            $builder = $builder->permittedFor($audience);
        }

        if ($subject !== null) {
            $builder = $builder->relatedTo($subject);
        }

        if ($jti !== null) {
            $builder = $builder->identifiedBy($jti);
        }

        if ($email !== null) {
            $builder = $builder->withClaim('email', $email);
        }

        if ($name !== null) {
            $builder = $builder->withClaim('name', $name);
        }

        $builder = $builder->withClaim('email_verified', true);

        $token = $builder->getToken($config->signer(), $config->signingKey());

        return $token->toString();
    }

    // -- Success cases --

    public function testValidTokenReturnsValidatedToken(): void
    {
        $jwt = $this->buildToken();

        $result = $this->validator->validate($jwt, self::JWKS_URI, $this->params);

        self::assertInstanceOf(ValidatedToken::class, $result);
        self::assertSame('user-123', $result->identity->externalId);
        self::assertSame(self::ISSUER, $result->identity->issuer);
        self::assertSame('user@example.com', $result->identity->email);
        self::assertSame('Test User', $result->identity->displayName);
        self::assertTrue($result->identity->emailVerified);
    }

    public function testValidTokenPreservesRawClaims(): void
    {
        $jwt = $this->buildToken(jti: 'unique-id-123');

        $result = $this->validator->validate($jwt, self::JWKS_URI, $this->params);

        self::assertSame('unique-id-123', $result->jti);
        self::assertSame('user@example.com', $result->claims['email']);
        self::assertSame('user-123', $result->claims['sub']);
    }

    public function testValidTokenSetsExpiresAt(): void
    {
        $exp = $this->clock->now()->modify('+2 hours');
        $jwt = $this->buildToken(expiresAt: $exp);

        $result = $this->validator->validate($jwt, self::JWKS_URI, $this->params);

        self::assertSame($exp->getTimestamp(), $result->expiresAt->getTimestamp());
    }

    public function testTokenWithinClockSkewIsAccepted(): void
    {
        // Token expires 20 seconds ago, but clock skew is 30 seconds
        $exp = $this->clock->now()->modify('-20 seconds');
        $iat = $this->clock->now()->modify('-1 hour');
        $jwt = $this->buildToken(issuedAt: $iat, expiresAt: $exp);

        $result = $this->validator->validate($jwt, self::JWKS_URI, $this->params);

        self::assertSame('user-123', $result->identity->externalId);
    }

    // -- Failure cases --

    public function testRejectsGarbageToken(): void
    {
        $this->expectException(OidcTokenValidationException::class);
        $this->expectExceptionMessage('Failed to parse');

        $this->validator->validate('not.a.jwt', self::JWKS_URI, $this->params);
    }

    public function testRejectsDisallowedAlgorithm(): void
    {
        // Build a token with RS384 but params only allow RS256
        $config = Configuration::forAsymmetricSigner(
            new \Lcobucci\JWT\Signer\Rsa\Sha384(),
            $this->privateKey,
            InMemory::plainText('unused'),
        );

        $token = $config->builder()
            ->issuedBy(self::ISSUER)
            ->permittedFor(self::AUDIENCE)
            ->issuedAt($this->clock->now())
            ->expiresAt($this->clock->now()->modify('+1 hour'))
            ->relatedTo('user-123')
            ->withHeader('kid', self::KID)
            ->withClaim('email', 'user@example.com')
            ->withClaim('name', 'Test User')
            ->withClaim('email_verified', true)
            ->getToken($config->signer(), $config->signingKey());

        $this->expectException(OidcTokenValidationException::class);
        $this->expectExceptionMessage('algorithm');

        $this->validator->validate($token->toString(), self::JWKS_URI, $this->params);
    }

    public function testRejectsMissingKid(): void
    {
        // Build a token without kid header
        $config = Configuration::forAsymmetricSigner(
            new Sha256(),
            $this->privateKey,
            InMemory::plainText('unused'),
        );

        $token = $config->builder()
            ->issuedBy(self::ISSUER)
            ->permittedFor(self::AUDIENCE)
            ->issuedAt($this->clock->now())
            ->expiresAt($this->clock->now()->modify('+1 hour'))
            ->relatedTo('user-123')
            ->withClaim('email', 'user@example.com')
            ->withClaim('name', 'Test User')
            ->withClaim('email_verified', true)
            ->getToken($config->signer(), $config->signingKey());

        $this->expectException(OidcTokenValidationException::class);
        $this->expectExceptionMessage('kid');

        $this->validator->validate($token->toString(), self::JWKS_URI, $this->params);
    }

    public function testRejectsWrongIssuer(): void
    {
        $jwt = $this->buildToken(issuer: 'https://evil.example.com');

        $this->expectException(OidcTokenValidationException::class);
        $this->expectExceptionMessage('Token validation failed');

        $this->validator->validate($jwt, self::JWKS_URI, $this->params);
    }

    public function testRejectsWrongAudience(): void
    {
        $jwt = $this->buildToken(audience: 'wrong-client-id');

        $this->expectException(OidcTokenValidationException::class);
        $this->expectExceptionMessage('Token validation failed');

        $this->validator->validate($jwt, self::JWKS_URI, $this->params);
    }

    public function testRejectsExpiredToken(): void
    {
        $jwt = $this->buildToken(
            issuedAt: $this->clock->now()->modify('-2 hours'),
            expiresAt: $this->clock->now()->modify('-1 hour'),
        );

        $this->expectException(OidcTokenValidationException::class);
        $this->expectExceptionMessage('Token validation failed');

        $this->validator->validate($jwt, self::JWKS_URI, $this->params);
    }

    public function testRejectsTooOldIat(): void
    {
        // iat is 25 hours ago (max is 24)
        $jwt = $this->buildToken(
            issuedAt: $this->clock->now()->modify('-25 hours'),
            expiresAt: $this->clock->now()->modify('+1 hour'),
        );

        $this->expectException(OidcTokenValidationException::class);
        $this->expectExceptionMessage('iat claim is too far in the past');

        $this->validator->validate($jwt, self::JWKS_URI, $this->params);
    }

    public function testRejectsWrongSignature(): void
    {
        // Build a token signed with a different key
        $otherKey = RSA::createKey(2048);
        $otherPrivateKey = InMemory::plainText($otherKey->toString('PKCS8'));

        $config = Configuration::forAsymmetricSigner(
            new Sha256(),
            $otherPrivateKey,
            InMemory::plainText('unused'),
        );

        $token = $config->builder()
            ->issuedBy(self::ISSUER)
            ->permittedFor(self::AUDIENCE)
            ->issuedAt($this->clock->now())
            ->expiresAt($this->clock->now()->modify('+1 hour'))
            ->relatedTo('user-123')
            ->withHeader('kid', self::KID)
            ->withClaim('email', 'user@example.com')
            ->withClaim('name', 'Test User')
            ->withClaim('email_verified', true)
            ->getToken($config->signer(), $config->signingKey());

        $this->expectException(OidcTokenValidationException::class);
        $this->expectExceptionMessage('Token validation failed');

        $this->validator->validate($token->toString(), self::JWKS_URI, $this->params);
    }

    public function testRejectsUnknownKid(): void
    {
        $jwt = $this->buildToken(kid: 'nonexistent-kid');

        // Set JWKS response for the refetch attempt
        $this->setJwksResponse();

        $this->expectException(OidcTokenValidationException::class);
        $this->expectExceptionMessage('signing key');

        $this->validator->validate($jwt, self::JWKS_URI, $this->params);
    }

    public function testRejectsTokenWithoutSub(): void
    {
        $jwt = $this->buildToken(subject: null, email: 'user@example.com');

        $this->expectException(OidcTokenValidationException::class);
        $this->expectExceptionMessage('does not support this token');

        $this->validator->validate($jwt, self::JWKS_URI, $this->params);
    }

    public function testAllowedAlgorithmsAreConfigurable(): void
    {
        $params = new OidcValidationParameters(
            expectedIssuer: self::ISSUER,
            expectedAudience: self::AUDIENCE,
            allowedAlgorithms: ['RS256', 'RS384'],
        );

        // RS256 token should still work
        $jwt = $this->buildToken();
        $result = $this->validator->validate($jwt, self::JWKS_URI, $params);

        self::assertSame('user-123', $result->identity->externalId);
    }

    // -- Security audit tests (Phase 5.1) --

    public function testRejectsAlgorithmNone(): void
    {
        // Craft a token with alg: none by manually building a JWT
        $header = base64_encode(json_encode(['alg' => 'none', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode([
            'iss' => self::ISSUER,
            'aud' => self::AUDIENCE,
            'sub' => 'user-123',
            'iat' => $this->clock->now()->getTimestamp(),
            'exp' => $this->clock->now()->modify('+1 hour')->getTimestamp(),
        ]));
        $unsignedToken = $header . '.' . $payload . '.';

        $this->expectException(OidcTokenValidationException::class);

        $this->validator->validate($unsignedToken, self::JWKS_URI, $this->params);
    }

    public function testRejectsSymmetricAlgorithmHS256(): void
    {
        $params = new OidcValidationParameters(
            expectedIssuer: self::ISSUER,
            expectedAudience: self::AUDIENCE,
            allowedAlgorithms: ['HS256'],
        );

        $jwt = $this->buildToken();

        // RS256-signed token with HS256 in allowed list should fail
        // because the token's alg header is RS256 which won't match HS256
        $this->expectException(OidcTokenValidationException::class);
        $this->expectExceptionMessage('algorithm');

        $this->validator->validate($jwt, self::JWKS_URI, $params);
    }

    public function testRejectsEmptyAllowedAlgorithms(): void
    {
        $params = new OidcValidationParameters(
            expectedIssuer: self::ISSUER,
            expectedAudience: self::AUDIENCE,
            allowedAlgorithms: [],
        );

        $jwt = $this->buildToken();

        $this->expectException(OidcTokenValidationException::class);
        $this->expectExceptionMessage('algorithm');

        $this->validator->validate($jwt, self::JWKS_URI, $params);
    }

    public function testSignerForAlgorithmRejectsHS256(): void
    {
        // Even if someone bypasses the allowlist, signerForAlgorithm rejects non-RSA
        $params = new OidcValidationParameters(
            expectedIssuer: self::ISSUER,
            expectedAudience: self::AUDIENCE,
            allowedAlgorithms: ['RS256', 'HS256'],
        );

        // Build a token that claims to be HS256 — should be rejected
        // because signerForAlgorithm has no HS256 signer
        $header = rtrim(strtr(base64_encode(json_encode([
            'alg' => 'HS256',
            'typ' => 'JWT',
            'kid' => self::KID,
        ])), '+/', '-_'), '=');
        $payload = rtrim(strtr(base64_encode(json_encode([
            'iss' => self::ISSUER,
            'aud' => self::AUDIENCE,
            'sub' => 'user-123',
            'iat' => $this->clock->now()->getTimestamp(),
            'exp' => $this->clock->now()->modify('+1 hour')->getTimestamp(),
        ])), '+/', '-_'), '=');
        // Fake signature
        $sig = rtrim(strtr(base64_encode('fake-signature'), '+/', '-_'), '=');
        $fakeJwt = $header . '.' . $payload . '.' . $sig;

        $this->expectException(OidcTokenValidationException::class);

        $this->validator->validate($fakeJwt, self::JWKS_URI, $params);
    }

    public function testExceptionMessagesDoNotLeakTokenContent(): void
    {
        // A malformed token should produce a generic error, not echo back token contents
        $sensitiveToken = 'eyJhbGciOiJSUzI1NiJ9.SECRET_PAYLOAD.SIGNATURE';

        try {
            $this->validator->validate($sensitiveToken, self::JWKS_URI, $this->params);
            self::fail('Expected exception');
        } catch (OidcTokenValidationException $e) {
            self::assertStringNotContainsString('SECRET_PAYLOAD', $e->getMessage());
        }
    }

    public function testRejectsTokenWithFutureNbf(): void
    {
        $jwt = $this->buildToken(
            issuedAt: $this->clock->now()->modify('+1 hour'),
            expiresAt: $this->clock->now()->modify('+2 hours'),
        );

        $this->expectException(OidcTokenValidationException::class);
        $this->expectExceptionMessage('Token validation failed');

        $this->validator->validate($jwt, self::JWKS_URI, $this->params);
    }
}
