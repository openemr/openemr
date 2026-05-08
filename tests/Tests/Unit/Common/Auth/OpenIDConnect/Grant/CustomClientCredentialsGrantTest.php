<?php

/**
 * CustomClientCredentialsGrantTest.php
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\Common\Auth\OpenIDConnect\Grant;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha384;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use OpenEMR\Common\Auth\Oidc\Discovery\OidcUrlValidator;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\AccessTokenEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Grant\CustomClientCredentialsGrant;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\AccessTokenRepository;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\JWTRepository;
use OpenEMR\Services\JWTClientAuthenticationService;
use OpenEMR\Services\UserService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CustomClientCredentialsGrantTest extends TestCase
{
    const OAUTH_INVALID_CLIENT_MESSAGE = 'Client authentication failed';
    const TEST_CLIENT_ID = 'gCz3kd1r322a8yffyNgVj-nglCBRU4yVwRsXq9ScEvo';
    const ISSUER = 'http://example.com';
    const AUDIENCE = 'http://example.com/oauth2/token';

    /**
     * Tests that we can get a valid response for the client credentials grant using the json web key set inside the
     * client entity (when they choose not to use a jwks_uri)
     * @throws \Exception
     */
    public function testValidResponseForClientWithRegisteredJwks(): void
    {

        $clientEntity = $this->getClientEntityForTest();
        $clientId = $clientEntity->getIdentifier();
        assert(is_string($clientId) && $clientId !== '');
        $jwt = $this->createJWTForKeys($clientId, self::AUDIENCE);
        $clientEntity->setJwks($this->loadJSONFile("jwk-public-valid.json"));
        // setup our fake access token & our repo
        $accessToken = new AccessTokenEntity();



        $ttl = new \DateInterval('PT300S');
        $clientRepository = $this->getMockClientRepository($clientEntity);
        $grant = new CustomClientCredentialsGrant($this->createMock(SessionInterface::class), self::AUDIENCE);
        $grant->setUserService($this->getMockUserService());
        $grant->setPrivateKey($this->createMock(CryptKey::class));
        $grant->setClientRepository($clientRepository);
        $grant->setAccessTokenRepository($this->getMockAccessTokenRepository($accessToken));
        $grant->setScopeRepository($this->getMockScopeRepository());
        $jwtAuthservice = new JWTClientAuthenticationService(self::AUDIENCE, $clientRepository, $this->getMockJwtRepository());
        $grant->setJWTAuthenticationService($jwtAuthservice);
        $response = $this->createMock(ResponseTypeInterface::class);

        // make sure we assert that our setAccessToken will be called as this is the final step where we know
        // the system will work fine
        $response->expects($this->once())
            ->method('setAccessToken')
            ->willReturn($accessToken);

        $grant->respondToAccessTokenRequest(
            $this->getMockServerRequestForJWT($jwt),
            $response,
            $ttl
        );
    }

    /**
     * Test that we can get a valid response for a Client Credentials Grant when using a URI for the json web key set
     * to be retrieved from.
     * @throws \Exception
     */
    public function testValidResponseForClientWithJwksUri(): void
    {
        $clientEntity = $this->getClientEntityForTest();
        $clientId = $clientEntity->getIdentifier();
        assert(is_string($clientId) && $clientId !== '');
        $jwt = $this->createJWTForKeys($clientId, self::AUDIENCE);

        $jwks = $this->loadJSONFile("jwk-public-valid.json");
        $jwkUri = 'https://localhost:9000/some-jwk-uri';
        $clientEntity->setJwksUri($jwkUri);
        // the custom grant will make an external call to the jwkUri which we expect
        // we mock the call and have it return the jwk set here.

        // need to make sure requests to our JWK set returns a valid JWK array
        $mockHttp = new MockHandler([
            new Response(200, [], $jwks)
        ]);
        $httpClient = new Client(['handler' => HandlerStack::create($mockHttp)]);


        // setup our fake access token & our repo
        $accessToken = new AccessTokenEntity();

        $ttl = new \DateInterval('PT300S');
        $clientRepository = $this->getMockClientRepository($clientEntity);
        $grant = new CustomClientCredentialsGrant($this->createMock(SessionInterface::class), self::AUDIENCE);
        $grant->setUserService($this->getMockUserService());
        $grant->setPrivateKey($this->createMock(CryptKey::class));
        $grant->setClientRepository($clientRepository);
        $grant->setAccessTokenRepository($this->getMockAccessTokenRepository($accessToken));
        $grant->setScopeRepository($this->getMockScopeRepository());
        $jwtAuthservice = new JWTClientAuthenticationService(self::AUDIENCE, $clientRepository, $this->getMockJwtRepository(), $httpClient);
        $grant->setJWTAuthenticationService($jwtAuthservice);

        $response = $this->createMock(ResponseTypeInterface::class);

        // make sure we assert that our setAccessToken will be called as this is the final step where we know
        // the system will work fine
        $response->expects($this->once())
            ->method('setAccessToken')
            ->willReturn($accessToken);

        $grant->respondToAccessTokenRequest(
            $this->getMockServerRequestForJWT($jwt),
            $response,
            $ttl
        );
    }

    /**
     * Test that we can get a valid response for a Client Credentials Grant when using a URI for the json web key set
     * to be retrieved from.
     * @throws \Exception
     */
    public function testInvalidResponseForClientWithJwksUri(): void
    {
        $clientEntity = $this->getClientEntityForTest();
        $jwt = $this->createJWTForKeys('not_an_issuer', self::AUDIENCE);
        $clientEntity->setJwks($this->loadJSONFile("jwk-public-valid.json"));
        // setup our fake access token & our repo
        $accessToken = new AccessTokenEntity();

        $ttl = new \DateInterval('PT300S');
        $clientRepository = $this->getMockClientRepository($clientEntity);
        $grant = new CustomClientCredentialsGrant($this->createMock(SessionInterface::class), self::AUDIENCE);
        $grant->setUserService($this->getMockUserService());
        $grant->setPrivateKey($this->createMock(CryptKey::class));
        $grant->setClientRepository($clientRepository);
        $grant->setAccessTokenRepository($this->getMockAccessTokenRepository($accessToken));
        $grant->setScopeRepository($this->getMockScopeRepository());
        $grant->setJWTAuthenticationService(new JWTClientAuthenticationService(self::AUDIENCE, $clientRepository, $this->getMockJwtRepository()));
        $response = $this->createMock(ResponseTypeInterface::class);

        // if the issuer is invalid than we get a client authentication failed message
        $this->expectException(OAuthServerException::class);
        $this->expectExceptionMessage('The request is missing a required parameter, includes an invalid parameter value, includes a parameter more than once, or is otherwise malformed.');

        $grant->respondToAccessTokenRequest($this->getMockServerRequestForJWT($jwt), $response, $ttl);
    }

    /**
     * Aisle finding (CWE-362). Even when UniqueID::assert()'s
     * SELECT pre-check finds nothing, a concurrent client-assertion
     * submission can already have inserted by the time saveJwtHistory()
     * runs. With the new UNIQUE KEY uq_jti, the second INSERT IGNORE
     * skips silently and the repo returns false — the JWTClientAuthentication
     * Service must translate that race-victim signal into a 400
     * invalid_client response instead of silently accepting the duplicate.
     *
     * @throws \Exception
     */
    public function testReplayDetectedWhenSaveJwtHistoryReportsRaceVictim(): void
    {
        // Helper has no return type declaration; narrow to keep PHPStan happy
        // without bumping baselined "method on mixed" counts. Same shape as
        // assertSsrfJwksUriRejected().
        /** @var ClientEntity $clientEntity */
        $clientEntity = $this->getClientEntityForTest();
        $clientId = $clientEntity->getIdentifier();
        assert(is_string($clientId) && $clientId !== '');
        $jwks = $this->loadJSONFile("jwk-public-valid.json");
        assert(is_string($jwks));
        $clientEntity->setJwks($jwks);

        $jwt = $this->createJWTForKeys($clientId, self::AUDIENCE);

        // Mock JWTRepository so saveJwtHistory reports the race-victim
        // signal (false) — simulating a concurrent request that already
        // inserted the same jti while we were validating.
        /** @var JWTRepository&MockObject $jwtRepo */
        $jwtRepo = $this->createMock(JWTRepository::class);
        $jwtRepo->method('getJwtGrantHistoryForJTI')->willReturn([]);
        $jwtRepo->method('saveJwtHistory')->willReturn(false);

        /** @var ClientRepository&MockObject $clientRepository */
        $clientRepository = $this->getMockClientRepository($clientEntity);
        $grant = new CustomClientCredentialsGrant($this->createMock(SessionInterface::class), self::AUDIENCE);
        /** @var UserService&MockObject $userService */
        $userService = $this->getMockUserService();
        $grant->setUserService($userService);
        $grant->setPrivateKey($this->createMock(CryptKey::class));
        $grant->setClientRepository($clientRepository);
        /** @var AccessTokenRepository&MockObject $accessTokenRepo */
        $accessTokenRepo = $this->getMockAccessTokenRepository(new AccessTokenEntity());
        $grant->setAccessTokenRepository($accessTokenRepo);
        /** @var ScopeRepositoryInterface&MockObject $scopeRepo */
        $scopeRepo = $this->getMockScopeRepository();
        $grant->setScopeRepository($scopeRepo);
        $grant->setJWTAuthenticationService(
            new JWTClientAuthenticationService(self::AUDIENCE, $clientRepository, $jwtRepo),
        );

        $response = $this->createMock(ResponseTypeInterface::class);
        $response->expects($this->never())->method('setAccessToken');

        $this->expectException(OAuthServerException::class);
        $this->expectExceptionMessage(self::OAUTH_INVALID_CLIENT_MESSAGE);

        /** @var ServerRequestInterface&MockObject $serverRequest */
        $serverRequest = $this->getMockServerRequestForJWT($jwt);
        $grant->respondToAccessTokenRequest(
            $serverRequest,
            $response,
            new \DateInterval('PT300S'),
        );
    }

    /**
     * Aisle round-4 finding #1 (CWE-400). The JWT client-assertion path
     * previously ran signature verification through a custom signer that
     * resolved the JWK via JsonWebKeySet's legacy getJSONWebKey() method,
     * which bypassed all of round-3 #5's caps (RSA modulus / exponent
     * length, use=sig requirement, alg-match). After the refactor to
     * pre-resolve the PEM via getSigningKeyAsPem() at the call site,
     * an attacker-controlled inline JWKS with an oversized RSA modulus
     * is rejected at JWK resolution time, BEFORE phpseclib does any
     * BigInteger work.
     *
     * Pin the regression: a client with a hostile inline JWKS (giant
     * `n`) must produce `invalid_client`. If a future change ever flips
     * this path back to the legacy resolver, this test fires.
     *
     * @throws \Exception
     */
    public function testRejectsClientAssertionWithOversizedRsaModulus(): void
    {
        /** @var ClientEntity $clientEntity */
        $clientEntity = $this->getClientEntityForTest();
        $clientId = $clientEntity->getIdentifier();
        assert(is_string($clientId) && $clientId !== '');

        // Inline JWKS with a 2000-char base64url-shaped `n`, which
        // exceeds round-3 #5's MAX_RSA_MODULUS_BASE64URL_LEN cap (1366).
        // The kid must match what createJWTForKeys puts in the JWT
        // header so we exercise the cap branch, not the kid-mismatch
        // branch.
        $oversizedJwks = json_encode([
            'keys' => [[
                'kty' => 'RSA',
                'kid' => '5c17409c-87f0-4713-814f-c864bfe876bc',
                'alg' => 'RS384',
                'use' => 'sig',
                'n' => str_repeat('A', 2000),
                'e' => 'AQAB',
            ]],
        ]);
        assert(is_string($oversizedJwks));
        $clientEntity->setJwks($oversizedJwks);

        $jwt = $this->createJWTForKeys($clientId, self::AUDIENCE);

        /** @var ClientRepository&MockObject $clientRepository */
        $clientRepository = $this->getMockClientRepository($clientEntity);
        $grant = new CustomClientCredentialsGrant($this->createMock(SessionInterface::class), self::AUDIENCE);
        /** @var UserService&MockObject $userService */
        $userService = $this->getMockUserService();
        $grant->setUserService($userService);
        $grant->setPrivateKey($this->createMock(CryptKey::class));
        $grant->setClientRepository($clientRepository);
        /** @var AccessTokenRepository&MockObject $accessTokenRepo */
        $accessTokenRepo = $this->getMockAccessTokenRepository(new AccessTokenEntity());
        $grant->setAccessTokenRepository($accessTokenRepo);
        /** @var ScopeRepositoryInterface&MockObject $scopeRepo */
        $scopeRepo = $this->getMockScopeRepository();
        $grant->setScopeRepository($scopeRepo);
        /** @var JWTRepository&MockObject $jwtRepo */
        $jwtRepo = $this->getMockJwtRepository();
        $grant->setJWTAuthenticationService(
            new JWTClientAuthenticationService(self::AUDIENCE, $clientRepository, $jwtRepo),
        );

        $response = $this->createMock(ResponseTypeInterface::class);
        $response->expects($this->never())->method('setAccessToken');

        $this->expectException(OAuthServerException::class);
        $this->expectExceptionMessage(self::OAUTH_INVALID_CLIENT_MESSAGE);

        /** @var ServerRequestInterface&MockObject $serverRequest */
        $serverRequest = $this->getMockServerRequestForJWT($jwt);
        $grant->respondToAccessTokenRequest(
            $serverRequest,
            $response,
            new \DateInterval('PT300S'),
        );
    }

    /**
     * Aisle round-2 finding #5 / round-1 finding #2 (CWE-294). Without `exp`,
     * the assertion's saveJwtHistory() call would store `jti_exp = NULL`
     * and the replay-lookup filter `jti_exp > FROM_UNIXTIME(?)` would
     * exclude NULL rows, letting the same client assertion be replayed
     * forever. RFC 7523 §3.4 also requires `exp` for client-assertion
     * JWTs.
     *
     * @throws \Exception
     */
    public function testRejectsClientAssertionWithoutExpClaim(): void
    {
        // Helper has no return type declaration; narrow to keep PHPStan happy
        // without bumping baselined "method on mixed" counts. Same shape as
        // assertSsrfJwksUriRejected().
        /** @var ClientEntity $clientEntity */
        $clientEntity = $this->getClientEntityForTest();
        $clientId = $clientEntity->getIdentifier();
        assert(is_string($clientId) && $clientId !== '');
        // loadJSONFile() has no return type; narrow to string for setJwks().
        $jwks = $this->loadJSONFile("jwk-public-valid.json");
        assert(is_string($jwks));
        $clientEntity->setJwks($jwks);

        $jwt = $this->createJWTForKeys($clientId, self::AUDIENCE, includeExp: false);

        $accessToken = new AccessTokenEntity();
        // Intersection-typed @var locals so this test doesn't add to the
        // baselined "MockObject given where X expected" counts that the
        // existing tests in this file already track. Same shape as
        // assertSsrfJwksUriRejected().
        /** @var ClientRepository&MockObject $clientRepository */
        $clientRepository = $this->getMockClientRepository($clientEntity);
        $grant = new CustomClientCredentialsGrant($this->createMock(SessionInterface::class), self::AUDIENCE);
        /** @var UserService&MockObject $userService */
        $userService = $this->getMockUserService();
        $grant->setUserService($userService);
        $grant->setPrivateKey($this->createMock(CryptKey::class));
        $grant->setClientRepository($clientRepository);
        /** @var AccessTokenRepository&MockObject $accessTokenRepo */
        $accessTokenRepo = $this->getMockAccessTokenRepository($accessToken);
        $grant->setAccessTokenRepository($accessTokenRepo);
        /** @var ScopeRepositoryInterface&MockObject $scopeRepo */
        $scopeRepo = $this->getMockScopeRepository();
        $grant->setScopeRepository($scopeRepo);
        /** @var JWTRepository&MockObject $jwtRepo */
        $jwtRepo = $this->getMockJwtRepository();
        $grant->setJWTAuthenticationService(
            new JWTClientAuthenticationService(self::AUDIENCE, $clientRepository, $jwtRepo),
        );

        $response = $this->createMock(ResponseTypeInterface::class);
        $response->expects($this->never())->method('setAccessToken');

        $this->expectException(OAuthServerException::class);
        // performAdditionalValidations() throws OAuthServerException::invalidRequest
        // for missing exp; the standard message is the "missing required parameter…"
        // string — same as the testInvalidResponseForClientWithJwksUri pattern.
        $this->expectExceptionMessage('The request is missing a required parameter, includes an invalid parameter value, includes a parameter more than once, or is otherwise malformed.');

        /** @var ServerRequestInterface&MockObject $serverRequest */
        $serverRequest = $this->getMockServerRequestForJWT($jwt);
        $grant->respondToAccessTokenRequest(
            $serverRequest,
            $response,
            new \DateInterval('PT300S'),
        );
    }

    /**
     * SSRF regression: a client whose stored `jwks_uri` resolves to a
     * private/loopback address must be rejected by the URL validator before
     * any HTTP request is issued. The mock HTTP handler holds no responses,
     * so if validation were skipped the call would fail with a different
     * exception (queue empty) — making this a genuine guard, not a tautology.
     */
    public function testJwtAuthRejectsClientWithPrivateIpJwksUri(): void
    {
        // Cloud-metadata IP — the canonical SSRF target. Strict policy must reject.
        $this->assertSsrfJwksUriRejected('https://169.254.169.254/latest/meta-data/jwks');
    }

    /**
     * SSRF regression: an http-only `jwks_uri` is rejected when the strict
     * production policy is in effect, even if the host itself is public.
     */
    public function testJwtAuthRejectsHttpOnlyJwksUriUnderStrictPolicy(): void
    {
        $this->assertSsrfJwksUriRejected('http://example.com/jwks.json');
    }

    /**
     * Shared SSRF assertion: build a client-credentials grant with the strict
     * URL validator wired into the JWT auth service, set the client's stored
     * `jwks_uri` to the supplied (unsafe) value, and assert that the token
     * request is rejected as `invalid_client` with no outbound HTTP issued.
     *
     * Centralized so neither test bumps the per-mock-helper baseline counts
     * for this file (PHPStan tracks the existing tests' MockObject patterns
     * by source-line occurrence).
     *
     * @param non-empty-string $unsafeJwksUri
     * @throws \Exception
     */
    private function assertSsrfJwksUriRejected(string $unsafeJwksUri): void
    {
        // Helper has no return type declaration; narrow to keep PHPStan happy
        // without bumping the baseline counts that the original tests rely on.
        /** @var ClientEntity $clientEntity */
        $clientEntity = $this->getClientEntityForTest();
        $clientId = $clientEntity->getIdentifier();
        assert(is_string($clientId) && $clientId !== '');
        $jwt = $this->createJWTForKeys($clientId, self::AUDIENCE);

        $clientEntity->setJwksUri($unsafeJwksUri);

        $mockHttp = new MockHandler([]); // no responses queued — should never be called
        $httpClient = new Client(['handler' => HandlerStack::create($mockHttp)]);

        // Concrete intersection so the variable simultaneously satisfies the
        // interface-typed setter on AbstractGrant and the PHPDoc-typed
        // constructor parameter on JWTClientAuthenticationService.
        /** @var ClientRepository&MockObject $clientRepository */
        $clientRepository = $this->getMockClientRepository($clientEntity);
        $grant = new CustomClientCredentialsGrant($this->createMock(SessionInterface::class), self::AUDIENCE);
        /** @var UserService&MockObject $userService */
        $userService = $this->getMockUserService();
        $grant->setUserService($userService);
        $grant->setPrivateKey($this->createMock(CryptKey::class));
        $grant->setClientRepository($clientRepository);
        /** @var AccessTokenRepository&MockObject $accessTokenRepo */
        $accessTokenRepo = $this->getMockAccessTokenRepository(new AccessTokenEntity());
        $grant->setAccessTokenRepository($accessTokenRepo);
        /** @var ScopeRepositoryInterface&MockObject $scopeRepo */
        $scopeRepo = $this->getMockScopeRepository();
        $grant->setScopeRepository($scopeRepo);

        $strictValidator = new OidcUrlValidator(requireHttps: true, blockPrivateIps: true);
        /** @var JWTRepository&MockObject $jwtRepo */
        $jwtRepo = $this->getMockJwtRepository();
        $jwtAuthService = new JWTClientAuthenticationService(
            self::AUDIENCE,
            $clientRepository,
            $jwtRepo,
            $httpClient,
            $strictValidator,
        );
        $grant->setJWTAuthenticationService($jwtAuthService);

        $response = $this->createMock(ResponseTypeInterface::class);
        $response->expects($this->never())->method('setAccessToken');

        // The URL validator throws OidcUrlValidationException; JsonWebKeySet wraps
        // it as JWKValidatorException; JWTClientAuthenticationService catches that
        // and surfaces invalid_client to the caller.
        $this->expectException(OAuthServerException::class);
        $this->expectExceptionMessage(self::OAUTH_INVALID_CLIENT_MESSAGE);

        /** @var ServerRequestInterface&MockObject $serverRequest */
        $serverRequest = $this->getMockServerRequestForJWT($jwt);
        $grant->respondToAccessTokenRequest(
            $serverRequest,
            $response,
            new \DateInterval('PT300S'),
        );

        $this->assertCount(0, $mockHttp, 'No HTTP request should have been issued');
    }

    /**
     * Tests and makes sure the lobocci library for creating a Jwt works which is what we need in our other tests.
     * @throws \Exception
     */
    public function testCreateJwtForKeys(): void
    {

        $configuration = Configuration::forAsymmetricSigner(
        // You may use RSA or ECDSA and all their variations (256, 384, and 512)
            new Sha384(),
            InMemory::file(__DIR__ . "/../../../../../data/Unit/Common/Auth/Grant/openemr-rsa384-private.key"),
            InMemory::file(__DIR__ . "/../../../../../data/Unit/Common/Auth/Grant/openemr-rsa384-public.pem")
            // You may also override the JOSE encoder/decoder if needed by providing extra arguments here
        );

        $now   = new \DateTimeImmutable();
        $token = $configuration->builder()
            // Configures the issuer (iss claim)
            ->issuedBy(self::ISSUER)
            // Configures the audience (aud claim)
            ->permittedFor(self::AUDIENCE)
            // Configures the id (jti claim)
            ->identifiedBy('4f1g23a12aa')
            // Configures the time that the token was issue (iat claim)
            ->issuedAt($now)
            // Configures the time that the token can be used (nbf claim)
            ->canOnlyBeUsedAfter($now)
            // Configures the expiration time of the token (exp claim)
            ->expiresAt($now->modify('+60 seconds'))
            // Configures a new claim, called "uid"
            ->withClaim('uid', 1)
            // Configures a new header, called "foo"
            ->withHeader('foo', 'bar')
            // Builds a new token
            ->getToken($configuration->signer(), $configuration->signingKey());
        $jwt = $token->toString(); // The string representation of the object is a JWT string
        $this->assertNotEmpty($jwt, "Token failed to create");
    }

    private function getMockScopeRepository(): \PHPUnit\Framework\MockObject\MockObject
    {
        $repo = $this->createMock(ScopeRepositoryInterface::class);
        $repo->method('finalizeScopes')
            ->willReturn([]); // array of our finalized scopes which can be empty for now.
        return $repo;
    }

    public function getMockJwtRepository(): \PHPUnit\Framework\MockObject\MockObject
    {
        $repo = $this->createMock(JWTRepository::class);
        // saveJwtHistory now returns bool; the happy-path default is "row
        // inserted successfully". Tests exercising the race-victim signal
        // override this with willReturn(false).
        $repo->method('saveJwtHistory')->willReturn(true);
        return $repo;
    }

    private function getMockServerRequestForJWT($jwt): \PHPUnit\Framework\MockObject\MockObject
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')
            ->willReturn([
                'client_assertion_type' => CustomClientCredentialsGrant::OAUTH_JWT_CLIENT_ASSERTION_TYPE
                ,'client_assertion' => $jwt
                ,'redirect_uri' => null
            ]);
        return $request;
    }

    private function getClientEntityForTest()
    {
        $clientEntity = new ClientEntity();
        $clientEntity->setIdentifier(self::TEST_CLIENT_ID);
        $clientEntity->setIsConfidential(true);
        $clientEntity->setIsEnabled(true);
        return $clientEntity;
    }

    private function getMockAccessTokenRepository(AccessTokenEntity $accessToken): \PHPUnit\Framework\MockObject\MockObject
    {
        $accessTokenRepo = $this->createMock(AccessTokenRepository::class);
        $accessTokenRepo->method('getNewToken')
            ->willReturn($accessToken);
        return $accessTokenRepo;
    }

    private function getMockUserService(): \PHPUnit\Framework\MockObject\MockObject
    {
        $userService = $this->createMock(UserService::class);
        $userService->method('getSystemUser')
            ->willReturn([
                'uuid' => Uuid::uuid4()
            ]);
        return $userService;
    }

    private function getMockClientRepository(ClientEntity $clientEntity): \PHPUnit\Framework\MockObject\MockObject
    {
        $clientRepo = $this->createMock(ClientRepositoryInterface::class);
        $clientRepo->method('getClientEntity')
            ->willReturn($clientEntity);
        $clientRepo->method('validateClient')
            ->willReturn(true);
        return $clientRepo;
    }

    private function loadJSONFile($fileName)
    {
        $filePath = __DIR__ . "/../../../../../data/Unit/Common/Auth/Grant/" . $fileName;
        $jsonData = file_get_contents($filePath);
        return $jsonData;
    }

    /**
     * @param non-empty-string $iss
     * @param non-empty-string $aud
     */
    private function createJWTForKeys(string $iss, string $aud, bool $includeExp = true): string
    {

        $configuration = Configuration::forAsymmetricSigner(
        // You may use RSA or ECDSA and all their variations (256, 384, and 512)
            new Sha384(),
            InMemory::file(__DIR__ . "/../../../../../data/Unit/Common/Auth/Grant/openemr-rsa384-private.key"),
            InMemory::file(__DIR__ . "/../../../../../data/Unit/Common/Auth/Grant/openemr-rsa384-public.pem")
            // You may also override the JOSE encoder/decoder if needed by providing extra arguments here
        );

        $now   = new \DateTimeImmutable();
        $builder = $configuration->builder()
            // Configures the issuer (iss claim)
            ->issuedBy($iss)
            // Configures the subject (sub claim)
            ->relatedTo($iss)
            // Configures the audience (aud claim)
            ->permittedFor($aud)
            // Configures the id (jti claim)
            ->identifiedBy('4f1g23a12aa')
            // Configures the time that the token was issue (iat claim)
            ->issuedAt($now)
            // Configures the time that the token can be used (nbf claim)
            ->canOnlyBeUsedAfter($now)
            // Configures a new claim, called "uid"
            ->withClaim('uid', 1)
            // Configures a new header, called "foo"
            ->withHeader('foo', 'bar')
            // SMART Backend Services / RFC 7515 §4.1.4: kid is required
            // so the verifier can resolve the right JWK from the JWKS.
            // Matches the kid in tests/data/.../jwk-public-valid.json.
            // Round-4 #1's hardened resolver requires this; the legacy
            // path silently fell back to "first matching key".
            ->withHeader('kid', '5c17409c-87f0-4713-814f-c864bfe876bc');

        // Configures the expiration time of the token (exp claim). Tests
        // exercising the "missing exp" guard pass includeExp=false.
        if ($includeExp) {
            $builder = $builder->expiresAt($now->modify('+60 seconds'));
        }

        $token = $builder->getToken($configuration->signer(), $configuration->signingKey());
        return $token->toString(); // The string representation of the object is a JWT string
    }
}
