<?php

/**
 * CustomClientCredentialsGrantTest.php
 * @package openemr
 * @link      http://www.open-emr.org
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
use Lcobucci\JWT\Signer\Key\LocalFileReference;
use Lcobucci\JWT\Signer\Rsa\Sha384;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\AccessTokenEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Grant\CustomClientCredentialsGrant;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\AccessTokenRepository;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\JWTRepository;
use OpenEMR\Services\UserService;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;

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
    public function testValidResponseForClientWithRegisteredJwks()
    {

        $clientEntity = $this->getClientEntityForTest();
        $jwt = $this->createJWTForKeys($clientEntity->getIdentifier(), self::AUDIENCE);
        $clientEntity->setJwks($this->loadJSONFile("jwk-public-valid.json"));
        // setup our fake access token & our repo
        $accessToken = new AccessTokenEntity();



        $ttl = new \DateInterval('PT300S');
        $grant = new CustomClientCredentialsGrant(self::AUDIENCE);
        $grant->setUserService($this->getMockUserService());
        $grant->setPrivateKey($this->createMock(CryptKey::class));
        $grant->setClientRepository($this->getMockClientRepository($clientEntity));
        $grant->setHttpClient(new Client());
        $grant->setAccessTokenRepository($this->getMockAccessTokenRepository($accessToken));
        $grant->setScopeRepository($this->getMockScopeRepository());
        $grant->setJwtRepository($this->getMockJwtRepository());

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
    public function testValidResponseForClientWithJwksUri()
    {
        $clientEntity = $this->getClientEntityForTest();
        $jwt = $this->createJWTForKeys($clientEntity->getIdentifier(), self::AUDIENCE);

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
        $grant = new CustomClientCredentialsGrant(self::AUDIENCE);
        $grant->setUserService($this->getMockUserService());
        $grant->setPrivateKey($this->createMock(CryptKey::class));
        $grant->setClientRepository($this->getMockClientRepository($clientEntity));
        $grant->setHttpClient($httpClient);
        $grant->setAccessTokenRepository($this->getMockAccessTokenRepository($accessToken));
        $grant->setScopeRepository($this->getMockScopeRepository());
        $grant->setJwtRepository($this->getMockJwtRepository());

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
    public function testInvalidResponseForClientWithJwksUri()
    {
        $clientEntity = $this->getClientEntityForTest();
        $jwt = $this->createJWTForKeys('not_an_issuer', self::AUDIENCE);
        $clientEntity->setJwks($this->loadJSONFile("jwk-public-valid.json"));
        // setup our fake access token & our repo
        $accessToken = new AccessTokenEntity();

        $ttl = new \DateInterval('PT300S');
        $grant = new CustomClientCredentialsGrant(self::AUDIENCE);
        $grant->setUserService($this->getMockUserService());
        $grant->setPrivateKey($this->createMock(CryptKey::class));
        $grant->setClientRepository($this->getMockClientRepository($clientEntity));
        $grant->setHttpClient(new Client());
        $grant->setAccessTokenRepository($this->getMockAccessTokenRepository($accessToken));
        $grant->setScopeRepository($this->getMockScopeRepository());

        $response = $this->createMock(ResponseTypeInterface::class);

        // if the issuer is invalid than we get a client authentication failed message
        $this->expectException(OAuthServerException::class);
        $this->expectExceptionMessage('Client authentication failed');

        $grant->respondToAccessTokenRequest($this->getMockServerRequestForJWT($jwt), $response, $ttl);
    }

    /**
     * Tests and makes sure the lobocci library for creating a Jwt works which is what we need in our other tests.
     * @throws \Exception
     */
    public function testCreateJwtForKeys()
    {

        $configuration = Configuration::forAsymmetricSigner(
        // You may use RSA or ECDSA and all their variations (256, 384, and 512)
            new Sha384(),
            LocalFileReference::file(__DIR__ . "/../../../../../data/Unit/Common/Auth/Grant/openemr-rsa384-private.key"),
            LocalFileReference::file(__DIR__ . "/../../../../../data/Unit/Common/Auth/Grant/openemr-rsa384-public.pem")
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

    private function getMockScopeRepository()
    {
        $repo = $this->createMock(ScopeRepositoryInterface::class);
        $repo->method('finalizeScopes')
            ->willReturn([]); // array of our finalized scopes which can be empty for now.
        return $repo;
    }

    public function getMockJwtRepository()
    {
        $repo = $this->createMock(JWTRepository::class);
        return $repo;
    }

    private function getMockServerRequestForJWT($jwt)
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

    private function getMockAccessTokenRepository(AccessTokenEntity $accessToken)
    {
        $accessTokenRepo = $this->createMock(AccessTokenRepository::class);
        $accessTokenRepo->method('getNewToken')
            ->willReturn($accessToken);
        return $accessTokenRepo;
    }

    private function getMockUserService()
    {
        $userService = $this->createMock(UserService::class);
        $userService->method('getSystemUser')
            ->willReturn([
                'uuid' => Uuid::uuid4()
            ]);
        return $userService;
    }

    private function getMockClientRepository(ClientEntity $clientEntity)
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
        $filePath = dirname(__FILE__) . "/../../../../../data/Unit/Common/Auth/Grant/" . $fileName;
        $jsonData = file_get_contents($filePath);
        return $jsonData;
    }

    private function createJWTForKeys($iss, $aud)
    {

        $configuration = Configuration::forAsymmetricSigner(
        // You may use RSA or ECDSA and all their variations (256, 384, and 512)
            new Sha384(),
            LocalFileReference::file(__DIR__ . "/../../../../../data/Unit/Common/Auth/Grant/openemr-rsa384-private.key"),
            LocalFileReference::file(__DIR__ . "/../../../../../data/Unit/Common/Auth/Grant/openemr-rsa384-public.pem")
            // You may also override the JOSE encoder/decoder if needed by providing extra arguments here
        );

        $now   = new \DateTimeImmutable();
        $token = $configuration->builder()
            // Configures the issuer (iss claim)
            ->issuedBy($iss)
            // Configures the audience (aud claim)
            ->permittedFor($aud)
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
        return $token->toString(); // The string representation of the object is a JWT string
    }
}
