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
use OpenEMR\Common\Auth\Oidc\Identity\StandardClaimMapper;
use OpenEMR\Common\Auth\Oidc\Token\OidcTokenValidationException;
use OpenEMR\Common\Auth\Oidc\Token\OidcTokenValidator;
use OpenEMR\Common\Auth\Oidc\Token\OidcValidationParameters;
use OpenEMR\Common\Auth\Oidc\Token\TokenRevocationCheckerInterface;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\JWTRepository;
use OpenEMR\Tests\Isolated\Common\Auth\Oidc\Discovery\FakeHttpClient;
use phpseclib3\Crypt\RSA;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;

final class OidcTokenValidatorTest extends TestCase
{
    private const ISSUER = 'https://accounts.example.com';
    private const AUDIENCE = 'my-client-id';
    private const JWKS_URI = 'https://accounts.example.com/jwks';
    private const KID = 'test-key-1';

    private FakeHttpClient $httpClient;
    private Psr16Cache $cache;
    private string $cacheDir;
    private OidcTokenValidator $validator;
    private OidcValidationParameters $params;
    private FakeClock $clock;
    private InMemoryJwtRepository $jwtRepository;
    private InMemoryTokenRevocationChecker $revocationChecker;

    /** @var InMemory */
    private InMemory $privateKey;

    /** @var array{n: string, e: string} Base64url-encoded RSA key components */
    private array $jwkComponents;

    protected function setUp(): void
    {
        // Generate RSA key pair for test signing
        $rsaKey = RSA::createKey(2048);
        /** @var string $privateKeyPem */
        $privateKeyPem = $rsaKey->toString('PKCS8');
        /** @var \phpseclib3\Crypt\RSA\PublicKey $publicKey */
        $publicKey = $rsaKey->getPublicKey();
        /** @var string $publicKeyPem */
        $publicKeyPem = $publicKey->toString('PKCS8');

        self::assertNotEmpty($privateKeyPem, 'RSA private key PEM must not be empty');
        $this->privateKey = InMemory::plainText($privateKeyPem);

        // Extract n and e for JWKS response
        $publicKeyResource = openssl_pkey_get_public($publicKeyPem);
        self::assertNotFalse($publicKeyResource, 'Failed to load public key');
        $publicKeyDetails = openssl_pkey_get_details($publicKeyResource);
        self::assertIsArray($publicKeyDetails, 'Failed to get public key details');
        self::assertArrayHasKey('rsa', $publicKeyDetails);
        /** @var array{n: string, e: string} $rsaDetails */
        $rsaDetails = $publicKeyDetails['rsa'];
        $this->jwkComponents = [
            'n' => rtrim(strtr(base64_encode($rsaDetails['n']), '+/', '-_'), '='),
            'e' => rtrim(strtr(base64_encode($rsaDetails['e']), '+/', '-_'), '='),
        ];

        $this->httpClient = new FakeHttpClient();
        $this->cacheDir = sys_get_temp_dir() . '/oidc_validator_test_' . bin2hex(random_bytes(8));
        mkdir($this->cacheDir, 0o755, true);
        $this->cache = new Psr16Cache(new FilesystemAdapter('', 0, $this->cacheDir));

        $this->clock = new FakeClock(new \DateTimeImmutable('2026-01-15T12:00:00Z'));
        $this->jwtRepository = new InMemoryJwtRepository();
        $this->revocationChecker = new InMemoryTokenRevocationChecker();

        $this->validator = new OidcTokenValidator(
            $this->httpClient,
            new StandardClaimMapper(),
            $this->clock,
            $this->jwtRepository,
            $this->revocationChecker,
            $this->cache,
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

    /**
     * @param non-empty-string|null $issuer
     * @param non-empty-string|null $audience
     * @param non-empty-string|null $kid
     * @param non-empty-string|null $subject
     * @param non-empty-string|null $email
     * @param non-empty-string|null $name
     * @param non-empty-string|null $jti
     */
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
        bool $includeNbf = true,
        bool $includeIat = true,
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
            ->expiresAt($exp)
            ->withHeader('kid', $kid);

        if ($includeIat) {
            $builder = $builder->issuedAt($iat);
        }

        if ($includeNbf) {
            $builder = $builder->canOnlyBeUsedAfter($iat);
        }

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
        $otherPem = $otherKey->toString('PKCS8');
        self::assertNotEmpty($otherPem, 'RSA private key PEM must not be empty');
        $otherPrivateKey = InMemory::plainText($otherPem);

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

        // The replay-key step now rejects sub-less tokens before reaching the
        // claim mapper, since the synthetic key needs (iss, sub, iat) when no
        // jti is present.
        $this->expectException(OidcTokenValidationException::class);
        $this->expectExceptionMessage('missing iss or sub');

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
        $header = base64_encode((string) json_encode(['alg' => 'none', 'typ' => 'JWT']));
        $payload = base64_encode((string) json_encode([
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
        $header = rtrim(strtr(base64_encode((string) json_encode([
            'alg' => 'HS256',
            'typ' => 'JWT',
            'kid' => self::KID,
        ])), '+/', '-_'), '=');
        $payload = rtrim(strtr(base64_encode((string) json_encode([
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

    /**
     * Firebase/GCIP tokens do not include the "nbf" (Not Before) claim.
     * The validator must accept tokens with only "iat" and "exp".
     */
    public function testAcceptsTokenWithoutNbfClaim(): void
    {
        $jwt = $this->buildToken(includeNbf: false);

        $result = $this->validator->validate($jwt, self::JWKS_URI, $this->params);

        self::assertSame('user-123', $result->identity->externalId);
        self::assertSame(self::ISSUER, $result->identity->issuer);
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

    public function testSecondValidationOfSameJtiIsRejectedAsReplay(): void
    {
        $jwt = $this->buildToken(jti: 'unique-jti-1');

        // First call succeeds and records the jti.
        $this->validator->validate($jwt, self::JWKS_URI, $this->params);

        // Second call with the same token must be rejected.
        $this->expectException(OidcTokenValidationException::class);
        $this->expectExceptionMessage('replay detected');
        $this->validator->validate($jwt, self::JWKS_URI, $this->params);
    }

    public function testReplayProtectionTriggersFallbackKeyWhenJtiIsMissing(): void
    {
        // Token without jti — validator should synthesize a replay key from iss+sub+iat.
        $jwt = $this->buildToken();

        $this->validator->validate($jwt, self::JWKS_URI, $this->params);

        $this->expectException(OidcTokenValidationException::class);
        $this->expectExceptionMessage('replay detected');
        $this->validator->validate($jwt, self::JWKS_URI, $this->params);
    }

    public function testTokenWithoutJtiAndIatIsRejected(): void
    {
        // Without jti AND without iat the synthetic key would collide for every
        // token issued to the same (iss, sub), letting one presented token lock
        // the user out of subsequent logins. Refuse to validate instead.
        $jwt = $this->buildToken(includeNbf: false, includeIat: false);

        $this->expectException(OidcTokenValidationException::class);
        $this->expectExceptionMessage('cannot compute replay key');

        $this->validator->validate($jwt, self::JWKS_URI, $this->params);
    }

    public function testDifferentTokensFromSameUserAreIndependent(): void
    {
        // Two distinct tokens (different iat / jti) for the same user must both succeed;
        // the replay store must not treat "same user" as a collision.
        $jwt1 = $this->buildToken(jti: 'jti-a');
        $jwt2 = $this->buildToken(jti: 'jti-b');

        $this->validator->validate($jwt1, self::JWKS_URI, $this->params);
        // No exception for the second, distinct token.
        $result = $this->validator->validate($jwt2, self::JWKS_URI, $this->params);

        self::assertSame('user-123', $result->identity->externalId);
    }

    public function testReplayStoreRecordsIssuerAndExpiration(): void
    {
        $jwt = $this->buildToken(jti: 'unique-jti-record-check');

        $this->validator->validate($jwt, self::JWKS_URI, $this->params);

        $records = $this->jwtRepository->recordsFor('unique-jti-record-check');
        self::assertCount(1, $records);
        self::assertSame(self::ISSUER, $records[0]['client_id']);
        self::assertIsInt($records[0]['jti_exp']);
        self::assertGreaterThan($this->clock->now()->getTimestamp(), $records[0]['jti_exp']);
    }

    public function testRevokedJtiIsRejected(): void
    {
        $jwt = $this->buildToken(jti: 'revoked-jti-1');
        $this->revocationChecker->revoke('revoked-jti-1');

        $this->expectException(OidcTokenValidationException::class);
        $this->expectExceptionMessage('Token has been revoked');

        $this->validator->validate($jwt, self::JWKS_URI, $this->params);
    }

    public function testRevokedTokenDoesNotPolluteReplayHistory(): void
    {
        // A revoked token must fail before we record it as "seen", otherwise a
        // legitimate-but-later revoked token could not be re-issued under the
        // same jti by the IdP (defense in depth — replay records aren't the
        // primary concern, but we shouldn't leak state on a rejected request).
        $jwt = $this->buildToken(jti: 'revoked-jti-2');
        $this->revocationChecker->revoke('revoked-jti-2');

        try {
            $this->validator->validate($jwt, self::JWKS_URI, $this->params);
            self::fail('Expected revoked token to be rejected');
        } catch (OidcTokenValidationException) {
            // expected
        }

        self::assertSame([], $this->jwtRepository->recordsFor('revoked-jti-2'));
    }

    public function testTokenWithoutJtiIsNotAffectedByRevocationCheck(): void
    {
        // No jti claim → revocation check is skipped (documented behavior).
        // Validation proceeds; replay protection still kicks in via the
        // synthetic key.
        $jwt = $this->buildToken();

        $result = $this->validator->validate($jwt, self::JWKS_URI, $this->params);

        self::assertNull($result->jti);
        self::assertSame('user-123', $result->identity->externalId);
    }
}

/**
 * Minimal in-memory TokenRevocationCheckerInterface — keeps isolated tests
 * off the database.
 *
 * @internal
 */
final class InMemoryTokenRevocationChecker implements TokenRevocationCheckerInterface
{
    /** @var array<string, true> */
    private array $revoked = [];

    public function revoke(string $jti): void
    {
        $this->revoked[$jti] = true;
    }

    public function isRevoked(string $jti): bool
    {
        return isset($this->revoked[$jti]);
    }
}

/**
 * In-memory JWTRepository fake — isolated tests must not hit the database.
 *
 * Mirrors the "jti_exp > ?" filter semantics of the concrete repository:
 * when a non-null expiration threshold is given, only records whose stored
 * jti_exp is strictly greater are returned.
 *
 * @internal
 */
final class InMemoryJwtRepository extends JWTRepository
{
    /** @var array<string, list<array{jti: string, client_id: string, jti_exp: int|null}>> */
    private array $history = [];

    /**
     * @param mixed $jti
     * @param mixed $expiration
     * @return list<array{jti: string, client_id: string, jti_exp: int|null}>
     */
    public function getJwtGrantHistoryForJTI($jti, $expiration = null)
    {
        if (!is_string($jti)) {
            return [];
        }
        $records = $this->history[$jti] ?? [];
        if (!is_int($expiration) || $expiration <= 0) {
            return $records;
        }
        return array_values(array_filter(
            $records,
            static fn(array $r): bool => $r['jti_exp'] !== null && $r['jti_exp'] > $expiration,
        ));
    }

    /**
     * @param mixed $jti
     * @param mixed $client_id
     * @param mixed $expiration
     */
    public function saveJwtHistory($jti, $client_id, $expiration): void
    {
        if (!is_string($jti) || !is_string($client_id)) {
            return;
        }
        $this->history[$jti][] = [
            'jti' => $jti,
            'client_id' => $client_id,
            'jti_exp' => is_int($expiration) ? $expiration : null,
        ];
    }

    /**
     * @return list<array{jti: string, client_id: string, jti_exp: int|null}>
     */
    public function recordsFor(string $jti): array
    {
        return $this->history[$jti] ?? [];
    }
}
