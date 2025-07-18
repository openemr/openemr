<?php

namespace OpenEMR\Tests\RestControllers\Authorization;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha384;
use League\OAuth2\Server\CryptKey;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\AccessTokenEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ScopeEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\AccessTokenRepository;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\RestControllers\Authorization\BearerTokenAuthorizationStrategy;
use OpenEMR\Services\TrustedUserService;
use OpenEMR\Services\UserService;
use PHPUnit\Framework\TestCase;
use OpenEMR\Common\Auth\UuidUserAccount;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ServerBag;
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
    public function testIs_api_request()
    {
        $this->assertTrue(BearerTokenAuthorizationStrategy::is_api_request("/apis/default/api/Patient"), "Expected is_api_request to return true for portal API path");
        $this->assertFalse(BearerTokenAuthorizationStrategy::is_api_request("/apis/default/fhir/Patient"), "Expected is_api_request to return false for fhir API path");
    }

    public function testShouldProcessRequest()
    {
        $strategy = new BearerTokenAuthorizationStrategy();
        $this->assertTrue($strategy->shouldProcessRequest($this->createMock(HttpRestRequest::class)), "Expected shouldProcessRequest to return true for any request");
    }

    public function testIs_portal_request()
    {
        $this->assertTrue(BearerTokenAuthorizationStrategy::is_portal_request("/default/apis/portal/Patient"), "Expected is_api_request to return true for portal API path");
        $this->assertFalse(BearerTokenAuthorizationStrategy::is_portal_request("/default/apis/fhir/Patient"), "Expected is_api_request to return false for fhir API path");
    }

    private function createMockAccessTokenEntity(string $tokenId)
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
            ->identifiedBy($tokenId)
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
    }

    public function testAuthorizeRequest()
    {
        // TODO: refactor BearerTokenAuthorizationStrategy This class is doing too much as evidenced by ALL of the dependency mocking
        $userUuid = '123e4567-e89b-12d3-a456-426614174000';
        $testClient = new ClientEntity();
        $testClient->setIdentifier(self::TEST_CLIENT_ID);
        $testClient->setUserId($userUuid);

        $tokenId = "some-valid-token-id";
        $accessToken = new AccessTokenEntity();
        $accessToken->setIdentifier($tokenId);
        $accessToken->setPrivateKey(new CryptKey(self::KEY_PATH_PRIVATE));
        $accessToken->setClient($testClient);
        $accessToken->setExpiryDateTime(new \DateTimeImmutable('+1 hour'));
        $accessToken->setUserIdentifier($userUuid);
        // need to create a fake token id

        $scopes = ['openid', 'profile', 'email', 'api:oemr'];
        foreach($scopes as $scope) {
            $entity = new ScopeEntity();
            $entity->setIdentifier($scope);
            $accessToken->addScope($entity);
        }
        $context = [];
        $accessTokenRepository = $this->createMock(AccessTokenRepository::class);
//        $accessTokenRepository->expects($this->once())
//            ->method('getTokenByToken')
//            ->with($tokenId)
//            ->willReturn([
//                'id' => 1
//                ,'user_id' => $userUuid
//                ,'expiry' => date("Y-m-d H:i:s", strtotime("+1 hour"))
//                ,'client_id' => 'test-client-id'
//                ,'scope' => json_encode($scopes)
//                ,'revoked' => 0
//                ,'context' => json_encode($context)
//            ]); // Simulating a valid token with user_id 1
        $accessTokenRepository // ->expects($this->once())
            ->method('getTokenExpiration')
            ->with($tokenId, $testClient->getIdentifier(), $accessToken->getUserIdentifier())
            ->willReturn(date("Y-m-d H:i:s", strtotime("+1 hour"))); // Simulating a valid token expiration time
        $strategy = new BearerTokenAuthorizationStrategy();
        $request = new HttpRestRequest([], [], [], [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $accessToken, // Simulating a request with a valid token
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_CONTENT_TYPE' => 'application/json',
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/apis/default/api/Patient',
            'SERVER_NAME' => 'example.com',

        ]);
        $session = $this->getMockSessionForRequest($request);
        $request->setSession($session);
        $strategy->setAccessTokenRepository($accessTokenRepository);
        $cryptKey = new CryptKey(self::KEY_PATH_PUBLIC, null, false);
        $strategy->setPublicKey($cryptKey);

        $trustedUserService = $this->createMock(TrustedUserService::class);
        $trustedUserService->expects($this->once())->method('isTrustedUser')
            ->with($testClient->getIdentifier(), $accessToken->getUserIdentifier())
            ->willReturn(true); // Simulating that the user is trusted
        $strategy->setTrustedUserService($trustedUserService);

        $userUsername = 'testuser';
        $strategy->setUuidUserAccountFactory(function() use ($userUsername) {
            $mock = $this->createMock(UuidUserAccount::class);
            $mock->method('getUserAccount')->willReturn([
                    'id' => 1,
                    'username' => $userUsername,
                    'uuid' => '123e4567-e89b-12d3-a456-426614174000',
                ]);
            $mock->method('getUserRole')->willReturn('users');
            return $mock;
        });

        $mockUserService = $this->createMock(UserService::class);
        $mockUserService->expects($this->once())
            ->method('getAuthGroupForUser')
            ->with($userUsername)
            ->willReturn("Default");
        $strategy->setUserService($mockUserService);

        // Simulating a request with a valid token
        $this->assertTrue($strategy->authorizeRequest($request));
    }

    public function testGetUuidUserAccountFactory()
    {
        $strategy = new BearerTokenAuthorizationStrategy();
        $uuidUserAccountClass = $strategy->getUuidUserAccountFactory()(1);
        $this->assertInstanceOf(UuidUserAccount::class, $uuidUserAccountClass, "Expected UuidUserAccount instance");
    }
}
