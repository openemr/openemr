<?php

namespace OpenEMR\Tests\RestControllers\Authorization;

use League\OAuth2\Server\CryptKey;
use Monolog\Level;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\AccessTokenEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ScopeEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\AccessTokenRepository;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\RestControllers\Authorization\BearerTokenAuthorizationStrategy;
use OpenEMR\Services\TrustedUserService;
use OpenEMR\Services\UserService;
use PHPUnit\Framework\TestCase;
use OpenEMR\Common\Auth\UuidUserAccount;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorageFactory;

class BearerTokenAuthorizationStrategyTest extends TestCase
{
    const TEST_CLIENT_ID = 'gCz3kd1r322a8yffyNgVj-nglCBRU4yVwRsXq9ScEvo';
    const ISSUER = 'http://example.com';
    const AUDIENCE = 'http://example.com/oauth2/token';

    const KEY_PATH_PUBLIC = __DIR__ . '/../../data/Unit/Common/Auth/Grant/openemr-rsa384-public.pem';
    const KEY_PATH_PRIVATE = __DIR__ . '/../../data/Unit/Common/Auth/Grant/openemr-rsa384-private.key';

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
        $logger = new SystemLogger(Level::Critical);
        $strategy = new BearerTokenAuthorizationStrategy(new OEGlobalsBag(), $auditLogger, $logger);
        return $strategy;
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
