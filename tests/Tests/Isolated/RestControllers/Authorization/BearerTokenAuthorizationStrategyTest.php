<?php

namespace OpenEMR\Tests\Isolated\RestControllers\Authorization;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use League\OAuth2\Server\CryptKey;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\AccessTokenEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ScopeEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\AccessTokenRepository;
use OpenEMR\Common\Auth\UuidUserAccount;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\RestControllers\Authorization\BearerTokenAuthorizationStrategy;
use OpenEMR\Services\TrustedUserService;
use OpenEMR\Services\UserService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorageFactory;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BearerTokenAuthorizationStrategyTest extends TestCase
{
    const TEST_CLIENT_ID = 'gCz3kd1r322a8yffyNgVj-nglCBRU4yVwRsXq9ScEvo';
    const ISSUER = 'http://example.com';
    const AUDIENCE = 'http://example.com/oauth2/token';

    const KEY_PATH_PUBLIC = __DIR__ . '/../../../data/Unit/Common/Auth/Grant/openemr-rsa384-public.pem';
    const KEY_PATH_PRIVATE = __DIR__ . '/../../../data/Unit/Common/Auth/Grant/openemr-rsa384-private.key';

    private function getMockSessionForRequest(HttpRestRequest $request): SessionInterface
    {
        $sessionFactory = new MockFileSessionStorageFactory();
        $sessionStorage = $sessionFactory->createStorage($request);
        $session = new Session($sessionStorage);
        $session->start();
        $session->set("site_id", "default");
        return $session;
    }
    public function testIs_api_request(): void
    {
        $this->assertTrue(BearerTokenAuthorizationStrategy::is_api_request("/apis/default/api/Patient"), "Expected is_api_request to return true for portal API path");
        $this->assertFalse(BearerTokenAuthorizationStrategy::is_api_request("/apis/default/fhir/Patient"), "Expected is_api_request to return false for fhir API path");
    }

    /**
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testShouldProcessRequest(): void
    {
        $request = $this->createMock(HttpRestRequest::class);
        $strategy = $this->getBearerTokenAuthorizationStrategy($request);
        $this->assertTrue($strategy->shouldProcessRequest($request), "Expected shouldProcessRequest to return true for any request");
    }

    public function testIs_portal_request(): void
    {
        $this->assertTrue(BearerTokenAuthorizationStrategy::is_portal_request("/default/apis/portal/Patient"), "Expected is_api_request to return true for portal API path");
        $this->assertFalse(BearerTokenAuthorizationStrategy::is_portal_request("/default/apis/fhir/Patient"), "Expected is_api_request to return false for fhir API path");
    }

    private function getTestClientEntityForUser(string $userUuid): ClientEntity
    {
        $testClient = new ClientEntity();
        $testClient->setIdentifier(self::TEST_CLIENT_ID);
        $testClient->setUserId($userUuid);
        return $testClient;
    }
    private function getAccessTokenForUser(string $tokenId, string $userUuid, ClientEntity $testClient): AccessTokenEntity
    {
        $accessToken = new AccessTokenEntity();
        $accessToken->setIdentifier($tokenId);
        $accessToken->setIssuer(self::ISSUER);
        $accessToken->setPrivateKey(new CryptKey(self::KEY_PATH_PRIVATE));
        $accessToken->setClient($testClient);
        $accessToken->setExpiryDateTime(new \DateTimeImmutable('+1 hour'));
        $accessToken->setUserIdentifier($userUuid);
        return $accessToken;
    }

    private function getAuthorizeHttpRestRequestForUrlAndToken(AccessTokenEntity $accessToken, string $uri): HttpRestRequest
    {
        $request = new HttpRestRequest([], [], [], [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $accessToken, // Simulating a request with a valid token
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_CONTENT_TYPE' => 'application/json',
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => $uri,
            'SERVER_NAME' => 'example.com',

        ]);
        $session = $this->getMockSessionForRequest($request);
        $request->setSession($session);
        return $request;
    }

    private function getMockAccessTokenRepository(AccessTokenEntity $accessToken, $scopes): AccessTokenRepository
    {
        foreach ($scopes as $scope) {
            $entity = new ScopeEntity();
            $entity->setIdentifier($scope);
            $accessToken->addScope($entity);
        }
        $accessTokenRepository = $this->createMock(AccessTokenRepository::class);
        $accessTokenRepository // ->expects($this->once())
        ->method('getTokenExpiration')
            ->with($accessToken->getIdentifier(), $accessToken->getClient()->getIdentifier(), $accessToken->getUserIdentifier())
            ->willReturn(date("Y-m-d H:i:s", strtotime("+1 hour"))); // Simulating a valid token expiration time
        return $accessTokenRepository;
    }

    /**
     * @param array $user
     * @return UserService
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    private function getMockUserServiceForUser(array $user): UserService
    {
        $mockUserService = $this->createMock(UserService::class);
        $mockUserService->expects($this->once())
            ->method('getAuthGroupForUser')
            ->with($user['username'])
            ->willReturn("Default");
        return $mockUserService;
    }

    public function getBearerTokenAuthorizationStrategy(HttpRestRequest $request): BearerTokenAuthorizationStrategy
    {
        // TODO: @adunsulag do we want to assert any of the audit logger events?
        $auditLogger = $this->createMock(EventAuditLogger::class);
        $mockLogger = $this->createMock(LoggerInterface::class);
        $strategy = new BearerTokenAuthorizationStrategy(new OEGlobalsBag(), $auditLogger, $mockLogger);
        return $strategy;
    }

    /**
     * Mints a signed access token whose iat/nbf are offset from now, mirroring the claim set
     * OpenEMR's AccessTokenEntity::convertToJWT() produces.
     *
     * @param list<string> $scopes
     */
    private function mintTokenIssuedAt(
        \DateTimeImmutable $issuedAt,
        string $tokenId,
        string $userUuid,
        string $clientId,
        array $scopes
    ): string {
        $config = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::file(self::KEY_PATH_PRIVATE),
            InMemory::plainText('empty', 'empty')
        );

        return $config->builder()
            ->permittedFor($clientId)
            ->identifiedBy($tokenId)
            ->issuedAt($issuedAt)
            ->canOnlyBeUsedAfter($issuedAt)
            ->expiresAt($issuedAt->modify('+1 hour'))
            ->relatedTo($userUuid)
            ->withClaim('scopes', $scopes)
            ->issuedBy(self::ISSUER)
            ->getToken($config->signer(), $config->signingKey())
            ->toString();
    }

    private function requestForRawToken(string $jwt, string $uri): HttpRestRequest
    {
        $request = new HttpRestRequest([], [], [], [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $jwt,
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_CONTENT_TYPE' => 'application/json',
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => $uri,
            'SERVER_NAME' => 'example.com',
        ]);
        $request->setSession($this->getMockSessionForRequest($request));
        return $request;
    }

    /**
     * @param list<string> $scopes
     */
    private function authorizeTokenIssuedAt(\DateTimeImmutable $issuedAt, array $scopes = ['api:oemr']): bool
    {
        $userUuid = '123e4567-e89b-12d3-a456-426614174000';
        $tokenId = 'clock-skew-token-id';
        $user = ['id' => 1, 'username' => 'testuser', 'role' => 'users'];

        $jwt = $this->mintTokenIssuedAt($issuedAt, $tokenId, $userUuid, self::TEST_CLIENT_ID, $scopes);
        $request = $this->requestForRawToken($jwt, '/apis/default/api/patient');
        $strategy = $this->getBearerTokenAuthorizationStrategy($request);

        $accessTokenRepository = $this->createMock(AccessTokenRepository::class);
        $accessTokenRepository->method('getTokenExpiration')->willReturn(date('Y-m-d H:i:s', strtotime('+1 hour')));
        $accessTokenRepository->method('isAccessTokenRevokedInDatabase')->willReturn(false);
        $strategy->setAccessTokenRepository($accessTokenRepository);
        $strategy->setPublicKey(new CryptKey(self::KEY_PATH_PUBLIC, null, false));

        $trustedUserService = $this->createMock(TrustedUserService::class);
        $trustedUserService->method('isTrustedUser')->willReturn(true);
        $strategy->setTrustedUserService($trustedUserService);

        $strategy->setUuidUserAccountFactory(function () use ($user) {
            $mock = $this->createMock(UuidUserAccount::class);
            $mock->method('getUserAccount')->willReturn($user);
            $mock->method('getUserRole')->willReturn('users');
            return $mock;
        });
        $mockUserService = $this->createMock(UserService::class);
        $mockUserService->method('getAuthGroupForUser')->willReturn('Default');
        $strategy->setUserService($mockUserService);

        return $strategy->authorizeRequest($request);
    }

    /**
     * lcobucci/jwt's LooseValidAt compares iat/nbf/exp against the clock, and League's default
     * BearerTokenValidator supplies no leeway. A token presented in the same second it was issued
     * in is then rejected as "The token was issued in the future" whenever the host clock steps
     * backwards between the two requests -- which surfaces as an intermittent, endpoint-agnostic
     * 401 carrying OAuth's generic access-denied message.
     */
    public function testTokenIssuedSlightlyInTheFutureIsAcceptedWithinClockSkewLeeway(): void
    {
        $this->assertTrue(
            $this->authorizeTokenIssuedAt(new \DateTimeImmutable('+5 seconds')),
            'A token issued 5 seconds ahead of this clock must survive; that is ordinary host clock skew'
        );
    }

    /**
     * The leeway is a tolerance, not an open door: a token issued far in the future is still
     * rejected.
     */
    public function testTokenIssuedFarInTheFutureIsStillRejected(): void
    {
        $this->expectException(HttpException::class);
        $this->authorizeTokenIssuedAt(new \DateTimeImmutable('+30 minutes'));
    }

    public function testTokenIssuedInThePastIsAccepted(): void
    {
        $this->assertTrue($this->authorizeTokenIssuedAt(new \DateTimeImmutable('-5 seconds')));
    }

    public function testAuthorizeRequest(): void
    {
        // TODO: refactor BearerTokenAuthorizationStrategy This class is doing too much as evidenced by ALL of the dependency mocking
        $user = [
            'id' => 1,
            'username' => 'testuser',
            'role' => 'users',
        ];
        $userUuid = '123e4567-e89b-12d3-a456-426614174000';
        $testClient = $this->getTestClientEntityForUser($userUuid);
        $tokenId = "some-valid-token-id";
        $accessToken = $this->getAccessTokenForUser($tokenId, $userUuid, $testClient);
        $accessToken->addScope(ScopeEntity::createFromString("api:oemr"));
        $request = $this->getAuthorizeHttpRestRequestForUrlAndToken($accessToken, '/apis/default/api/patient');
        $strategy = $this->getBearerTokenAuthorizationStrategy($request);
        $accessTokenRepository = $this->getMockAccessTokenRepository($accessToken, ['openid', 'profile', 'email', 'api:oemr']);
        $strategy->setAccessTokenRepository($accessTokenRepository);
        $cryptKey = new CryptKey(self::KEY_PATH_PUBLIC, null, false);
        $strategy->setPublicKey($cryptKey);

        $trustedUserService = $this->createMock(TrustedUserService::class);
        $trustedUserService->expects($this->once())->method('isTrustedUser')
            ->with($testClient->getIdentifier(), $accessToken->getUserIdentifier())
            ->willReturn(true); // Simulating that the user is trusted
        $strategy->setTrustedUserService($trustedUserService);


        $strategy->setUuidUserAccountFactory(function () use ($user) {
            $mock = $this->createMock(UuidUserAccount::class);
            $mock->method('getUserAccount')->willReturn($user);
            $mock->method('getUserRole')->willReturn('users');
            return $mock;
        });

        $strategy->setUserService($this->getMockUserServiceForUser($user));

        // Simulating a request with a valid token
        $this->assertTrue($strategy->authorizeRequest($request));

        $session = $request->getSession();
        $this->assertEquals($userUuid, $session->get('userId'), "Expected user UUID to be set in session");
    }

    public function testAuthorizeRequestWithFhirRequest(): void
    {
        $user = [
            'id' => 1,
            'username' => 'testuser',
            'uuid' => '123e4567-e89b-12d3-a456-426614174000',
            'role' => 'users',
        ];
        $testClient = $this->getTestClientEntityForUser($user['uuid']);
        $tokenId = "some-valid-token-id";
        $accessToken = $this->getAccessTokenForUser($tokenId, $user['uuid'], $testClient);

        $request = $this->getAuthorizeHttpRestRequestForUrlAndToken($accessToken, '/apis/default/fhir/Patient');
        $strategy = $this->getBearerTokenAuthorizationStrategy($request);
        $accessTokenRepository = $this->getMockAccessTokenRepository($accessToken, ['openid', 'profile', 'email', 'api:oemr']);
        $strategy->setAccessTokenRepository($accessTokenRepository);
        $cryptKey = new CryptKey(self::KEY_PATH_PUBLIC, null, false);
        $strategy->setPublicKey($cryptKey);

        $trustedUserService = $this->createMock(TrustedUserService::class);
        $trustedUserService->expects($this->once())->method('isTrustedUser')
            ->with($testClient->getIdentifier(), $accessToken->getUserIdentifier())
            ->willReturn(true); // Simulating that the user is trusted
        $strategy->setTrustedUserService($trustedUserService);


        $strategy->setUuidUserAccountFactory(function () use ($user) {
            $mock = $this->createMock(UuidUserAccount::class);
            $mock->method('getUserAccount')->willReturn($user);
            $mock->method('getUserRole')->willReturn('users');
            return $mock;
        });

        $strategy->setUserService($this->getMockUserServiceForUser($user));

        // Simulating a request with a valid token
        $this->assertTrue($strategy->authorizeRequest($request));
    }

    public function testGetUuidUserAccountFactory(): void
    {
        $request = $this->createMock(HttpRestRequest::class);
        $strategy = $this->getBearerTokenAuthorizationStrategy($request);
        $uuidUserAccountClass = $strategy->getUuidUserAccountFactory()(1);
        $this->assertInstanceOf(UuidUserAccount::class, $uuidUserAccountClass, "Expected UuidUserAccount instance");
    }
}
