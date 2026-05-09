<?php

/**
 * Direct isolated coverage for JWTClientAuthenticationService.
 *
 * The service was previously only exercised through the larger
 * CustomClientCredentialsGrantTest integration path. This file pins
 * each documented validation rule (RFC 7523 § 3, SMART Backend
 * Services, Aisle round-3 / round-4 fixes) as its own focused test
 * so a future regression on any single branch is unambiguous.
 *
 * Helpers at the bottom mirror CustomClientCredentialsGrantTest's
 * fixture pattern (same JWK fixture, same RSA keypair) so the two
 * tests share the JOSE setup and only diverge in what they assert.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Unit\Services;

use DateTimeImmutable;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha384;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Grant\CustomClientCredentialsGrant;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\JWTRepository;
use OpenEMR\Services\JWTClientAuthenticationService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

final class JWTClientAuthenticationServiceTest extends TestCase
{
    private const TEST_CLIENT_ID = 'gCz3kd1r322a8yffyNgVj-nglCBRU4yVwRsXq9ScEvo';
    private const AUDIENCE = 'http://example.com/oauth2/token';

    /** Matches the kid in tests/Tests/data/Unit/Common/Auth/Grant/jwk-public-valid.json. */
    private const FIXTURE_KID = '5c17409c-87f0-4713-814f-c864bfe876bc';

    private const FIXTURE_DIR = __DIR__ . '/../../data/Unit/Common/Auth/Grant';

    // -----------------------------------------------------------------
    // validateJWTClientAssertion — early gate guards
    // -----------------------------------------------------------------

    public function testRejectsDisabledClient(): void
    {
        // Disabled clients short-circuit before any JWT parsing — the
        // request body and JWKS state are irrelevant. Pin the contract
        // that no signal from a disabled client reaches the validator.
        $client = $this->buildClient(enabled: false, withJwks: true);
        $request = $this->buildRequest('not-a-real-jwt');
        $service = $this->buildService();

        $this->expectException(OAuthServerException::class);
        $this->expectExceptionMessage('Client authentication failed');

        $service->validateJWTClientAssertion($request, $client);
    }

    public function testRejectsClientWithoutJwksOrJwksUri(): void
    {
        // RFC 7523 §3 requires a registered key to verify against; a
        // client with neither inline JWKS nor a JWKS URI can never
        // produce a verifiable assertion. Reject before parsing.
        $client = $this->buildClient(enabled: true, withJwks: false);
        $request = $this->buildRequest('not-a-real-jwt');
        $service = $this->buildService();

        $this->expectException(OAuthServerException::class);
        $this->expectExceptionMessage('Client authentication failed');

        $service->validateJWTClientAssertion($request, $client);
    }

    public function testRejectsEmptyClientAssertionInRequestBody(): void
    {
        // Defensive guard for callers that route a JWT-shaped request
        // (correct assertion_type) but with the assertion field blank.
        $client = $this->buildClient();
        $request = $this->buildRequest('');
        $service = $this->buildService();

        $this->expectException(OAuthServerException::class);
        $this->expectExceptionMessage('Client authentication failed');

        $service->validateJWTClientAssertion($request, $client);
    }

    public function testRejectsClientWithEmptyIdentifier(): void
    {
        // Without a client_id the issuer/subject equality checks downstream
        // would compare against ''. Fail closed up front.
        $client = $this->buildClient();
        $client->setIdentifier('');
        $request = $this->buildRequest($this->buildJwt());
        $service = $this->buildService();

        $this->expectException(OAuthServerException::class);
        $this->expectExceptionMessage('Client authentication failed');

        $service->validateJWTClientAssertion($request, $client);
    }

    // -----------------------------------------------------------------
    // validateJWTClientAssertion — JWT header / kid resolution
    // -----------------------------------------------------------------

    public function testRejectsJwtMissingKidHeader(): void
    {
        // Aisle round-4 #1 (CWE-400). RFC 7515 §4.1.4 + SMART Backend
        // Services require kid so the verifier can pick the right JWK
        // out of a multi-key set. The previous implementation fell
        // through to a "first matching key" path that bypassed the
        // round-3 #5 size/use/alg caps. The hardened resolver throws
        // JWKValidatorException without a kid; the service catches and
        // remaps to invalidClient.
        $client = $this->buildClient();
        $request = $this->buildRequest($this->buildJwt(['kid' => false]));
        $service = $this->buildService();

        $this->expectException(OAuthServerException::class);
        $this->expectExceptionMessage('Client authentication failed');

        $service->validateJWTClientAssertion($request, $client);
    }

    public function testRejectsJwtWithKidNotInJwks(): void
    {
        // Round-3 #6 (strict kid match) regression. Even with a valid
        // RS384 signature, a kid that isn't in the JWKS must fail —
        // the resolver no longer falls back to "first key matching alg".
        $client = $this->buildClient();
        $request = $this->buildRequest($this->buildJwt([
            'kid' => 'a-kid-that-does-not-exist-in-the-fixture-jwks',
        ]));
        $service = $this->buildService();

        $this->expectException(OAuthServerException::class);
        $this->expectExceptionMessage('Client authentication failed');

        $service->validateJWTClientAssertion($request, $client);
    }

    // -----------------------------------------------------------------
    // performAdditionalValidations — claim-shape checks
    // -----------------------------------------------------------------

    public function testRejectsJwtSubClaimMismatchingClientId(): void
    {
        // RFC 7523 §3 requires `sub == client_id`. Mismatch means
        // either a misconfigured client or an attacker trying to
        // introduce confusion between asserted identity and registered
        // identity. The hint distinguishes this from iss mismatch.
        $client = $this->buildClient();
        $request = $this->buildRequest($this->buildJwt([
            'sub' => 'a-different-subject',
        ]));
        $service = $this->buildService();

        try {
            $service->validateJWTClientAssertion($request, $client);
            self::fail('Expected OAuthServerException');
        } catch (OAuthServerException $exception) {
            self::assertSame('invalid_request', $exception->getErrorType());
            self::assertSame('Invalid subject claim', $exception->getHint());
        }
    }

    public function testRejectsJwtIssClaimMismatchingClientId(): void
    {
        // SMART Backend Services requires `iss == client_id` (the
        // assertion is self-issued by the client). The lcobucci
        // IssuedBy constraint also enforces this, but performAdditional-
        // Validations runs BEFORE Validator::assert and produces a more
        // precise hint. This test pins which gate fires first.
        $client = $this->buildClient();
        $request = $this->buildRequest($this->buildJwt([
            'iss' => 'a-different-issuer',
        ]));
        $service = $this->buildService();

        try {
            $service->validateJWTClientAssertion($request, $client);
            self::fail('Expected OAuthServerException');
        } catch (OAuthServerException $exception) {
            self::assertSame('invalid_request', $exception->getErrorType());
            self::assertSame('Invalid issuer claim', $exception->getHint());
        }
    }

    public function testRejectsJwtMissingExpClaim(): void
    {
        // Aisle round-3 (CWE-294). Without `exp` the saved
        // jwt_grant_history row carries jti_exp = NULL, and the
        // replay-lookup filter `jti_exp > ?` evaluates NULL as not-true
        // — letting the same assertion be replayed forever. The service
        // rejects before saveJwtHistory() so the storage layer never
        // sees a NULL exp from this path.
        $client = $this->buildClient();
        $request = $this->buildRequest($this->buildJwt(['exp' => false]));
        $service = $this->buildService();

        try {
            $service->validateJWTClientAssertion($request, $client);
            self::fail('Expected OAuthServerException');
        } catch (OAuthServerException $exception) {
            self::assertSame('invalid_request', $exception->getErrorType());
            self::assertSame('Missing exp claim', $exception->getHint());
        }
    }

    public function testRejectsJwtExpExceedingTwentyFourHourCeiling(): void
    {
        // SMART spec ceiling on assertion lifetime (MAX_JWT_EXPIRATION_HOURS).
        // A 25-hour exp lets an attacker who lifts the JWT keep replaying
        // for a day — the ceiling caps that window. Boundary-tested at
        // 25h to land cleanly outside the 24h window, regardless of
        // sub-second timing variation between buildJwt() and the
        // validator's $maxExp computation.
        $client = $this->buildClient();
        $request = $this->buildRequest($this->buildJwt([
            'exp' => (new DateTimeImmutable())->modify('+25 hours'),
        ]));
        $service = $this->buildService();

        try {
            $service->validateJWTClientAssertion($request, $client);
            self::fail('Expected OAuthServerException');
        } catch (OAuthServerException $exception) {
            self::assertSame('invalid_request', $exception->getErrorType());
            self::assertSame('Token expiration exceeds maximum allowed time', $exception->getHint());
        }
    }

    public function testRejectsJwtIatTooFarInPast(): void
    {
        // 5-minute freshness window. An assertion issued more than
        // 5 minutes ago is either stale (clock-skewed client) or
        // attacker-replayed; either way reject. exp is bumped to keep
        // the assertion still "live" so the iat gate is the one that
        // fires, not the lcobucci LooseValidAt constraint.
        $client = $this->buildClient();
        $request = $this->buildRequest($this->buildJwt([
            'iat' => (new DateTimeImmutable())->modify('-10 minutes'),
            'exp' => (new DateTimeImmutable())->modify('+60 seconds'),
        ]));
        $service = $this->buildService();

        try {
            $service->validateJWTClientAssertion($request, $client);
            self::fail('Expected OAuthServerException');
        } catch (OAuthServerException $exception) {
            self::assertSame('invalid_request', $exception->getErrorType());
            self::assertSame('Token issued too far in the past', $exception->getHint());
        }
    }

    public function testRejectsJwtMissingJtiClaim(): void
    {
        // RFC 7523 §3 §7 — jti is required for replay prevention. The
        // service uses jti as the unique storage key; without one, the
        // replay gate cannot distinguish a fresh assertion from a
        // re-submitted one. UniqueID constraint also enforces this
        // downstream, but performAdditionalValidations rejects first
        // with a precise hint.
        $client = $this->buildClient();
        $request = $this->buildRequest($this->buildJwt(['jti' => false]));
        $service = $this->buildService();

        try {
            $service->validateJWTClientAssertion($request, $client);
            self::fail('Expected OAuthServerException');
        } catch (OAuthServerException $exception) {
            self::assertSame('invalid_request', $exception->getErrorType());
            self::assertSame('Missing jti claim', $exception->getHint());
        }
    }

    // -----------------------------------------------------------------
    // saveJwtHistory — replay-race victim signal
    // -----------------------------------------------------------------

    public function testRejectsReplayWhenSaveJwtHistoryReportsRaceVictim(): void
    {
        // Aisle (CWE-362). Even when UniqueID::assert()'s SELECT
        // pre-check finds nothing, a concurrent client-assertion
        // submission can already have inserted by the time
        // saveJwtHistory() runs. With the UNIQUE KEY uq_jti, the
        // second INSERT IGNORE skips silently and the repo returns
        // false. The service must translate that race-victim signal
        // into a 400 invalid_client response instead of silently
        // accepting the duplicate. (CustomClientCredentialsGrantTest
        // covers the same contract from the grant flow's perspective;
        // this pins it directly on the service.)
        $client = $this->buildClient();
        $request = $this->buildRequest($this->buildJwt());
        $service = $this->buildService(jwtRepo: $this->buildJwtRepo(insertSucceeds: false));

        try {
            $service->validateJWTClientAssertion($request, $client);
            self::fail('Expected OAuthServerException');
        } catch (OAuthServerException $exception) {
            self::assertSame('invalid_client', $exception->getErrorType());
            self::assertSame(400, $exception->getHttpStatusCode());
        }
    }

    // -----------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------

    /**
     * Build a JWT with sensible RFC 7523 / SMART defaults; opts override
     * specific claims/headers. Pass `false` to omit a claim entirely
     * (e.g. `['exp' => false]` exercises the missing-exp guard).
     *
     * Defaults are kept deliberately compatible with the JWK fixture
     * at tests/Tests/data/Unit/Common/Auth/Grant/jwk-public-valid.json
     * — same kid, same RS384 algorithm, same client identifier.
     *
     * @param array{
     *     iss?: non-empty-string,
     *     sub?: non-empty-string,
     *     aud?: non-empty-string,
     *     jti?: non-empty-string|false,
     *     iat?: DateTimeImmutable|false,
     *     exp?: DateTimeImmutable|false,
     *     kid?: non-empty-string|false,
     * } $opts
     */
    private function buildJwt(array $opts = []): string
    {
        $configuration = Configuration::forAsymmetricSigner(
            new Sha384(),
            InMemory::file(self::FIXTURE_DIR . '/openemr-rsa384-private.key'),
            InMemory::file(self::FIXTURE_DIR . '/openemr-rsa384-public.pem'),
        );

        $now = new DateTimeImmutable();
        $iss = $opts['iss'] ?? self::TEST_CLIENT_ID;
        $sub = $opts['sub'] ?? self::TEST_CLIENT_ID;
        $aud = $opts['aud'] ?? self::AUDIENCE;
        $jti = array_key_exists('jti', $opts) ? $opts['jti'] : 'test-jti-' . bin2hex(random_bytes(8));
        $iat = array_key_exists('iat', $opts) ? $opts['iat'] : $now;
        $exp = array_key_exists('exp', $opts) ? $opts['exp'] : $now->modify('+60 seconds');
        $kid = array_key_exists('kid', $opts) ? $opts['kid'] : self::FIXTURE_KID;

        $builder = $configuration->builder()
            ->issuedBy($iss)
            ->relatedTo($sub)
            ->permittedFor($aud)
            ->canOnlyBeUsedAfter($now);

        if ($iat instanceof DateTimeImmutable) {
            $builder = $builder->issuedAt($iat);
        }
        if ($jti !== false) {
            $builder = $builder->identifiedBy($jti);
        }
        if ($exp instanceof DateTimeImmutable) {
            $builder = $builder->expiresAt($exp);
        }
        if ($kid !== false) {
            $builder = $builder->withHeader('kid', $kid);
        }

        return $builder->getToken($configuration->signer(), $configuration->signingKey())->toString();
    }

    private function buildClient(bool $enabled = true, bool $withJwks = true): ClientEntity
    {
        $client = new ClientEntity();
        $client->setIdentifier(self::TEST_CLIENT_ID);
        $client->setIsConfidential(true);
        $client->setIsEnabled($enabled);

        if ($withJwks) {
            $jwks = file_get_contents(self::FIXTURE_DIR . '/jwk-public-valid.json');
            assert(is_string($jwks));
            $client->setJwks($jwks);
        }

        return $client;
    }

    private function buildRequest(string $jwt): ServerRequestInterface
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn([
            'client_assertion_type' => CustomClientCredentialsGrant::OAUTH_JWT_CLIENT_ASSERTION_TYPE,
            'client_assertion' => $jwt,
            'redirect_uri' => null,
        ]);
        return $request;
    }

    private function buildService(?JWTRepository $jwtRepo = null): JWTClientAuthenticationService
    {
        // The service's @param docblock declares the concrete
        // ClientRepository, even though the native typehint is the
        // PSR/League ClientRepositoryInterface. PHPStan respects the
        // docblock; cast via @var ClientRepository&MockObject to
        // satisfy it without changing source. Mirrors the pattern in
        // CustomClientCredentialsGrantTest.
        /** @var ClientRepository&MockObject $clientRepo */
        $clientRepo = $this->createMock(ClientRepositoryInterface::class);

        return new JWTClientAuthenticationService(
            self::AUDIENCE,
            $clientRepo,
            $jwtRepo ?? $this->buildJwtRepo(),
        );
    }

    /**
     * @return JWTRepository&\PHPUnit\Framework\MockObject\MockObject
     */
    private function buildJwtRepo(bool $insertSucceeds = true): JWTRepository
    {
        $repo = $this->createMock(JWTRepository::class);
        $repo->method('saveJwtHistory')->willReturn($insertSucceeds);
        // UniqueID's SELECT pre-check — return null so the constraint
        // sees "no prior row" and lets the assertion through to the
        // saveJwtHistory race-victim signal at the end of validation.
        $repo->method('getJwtGrantHistoryForJTI')->willReturn(null);
        return $repo;
    }
}
