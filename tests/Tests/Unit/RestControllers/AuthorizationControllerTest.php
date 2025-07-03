<?php

namespace OpenEMR\Tests\Unit\RestControllers;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use League\OAuth2\Server\AuthorizationServer;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\UserEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\UserRepository;
use OpenEMR\RestControllers\AuthorizationController;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthorizationControllerTest extends TestCase
{
    /**
     * @var AuthorizationController
     */
    private $authorizationController;

    /**
     * @var MockObject|ClientRepository
     */
    private $clientRepository;

    /**
     * @var MockObject|UserRepository
     */
    private $userRepository;

    /**
     * @var MockObject|AuthorizationServer
     */
    private $authorizationServer;

    /**
     * @var MockObject|ServerRequestInterface
     */
    private $request;

    /**
     * @var MockObject|ResponseInterface
     */
    private $response;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clientRepository = $this->createMock(ClientRepository::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->authorizationServer = $this->createMock(AuthorizationServer::class);
        $this->request = $this->createMock(ServerRequestInterface::class);
        $this->response = $this->createMock(ResponseInterface::class);

        $this->authorizationController = $this->getMockBuilder(AuthorizationController::class)
            ->onlyMethods(['getAuthorizationServer', 'createServerRequest', 'createServerResponse', 'getHttpClient'])
            ->getMock();

        $this->authorizationController->method('getAuthorizationServer')->willReturn($this->authorizationServer);
        $this->authorizationController->method('createServerRequest')->willReturn($this->request);
        $this->authorizationController->method('createServerResponse')->willReturn($this->response);
    }

    public function testRedirectToGoogle()
    {
        // Arrange
        $client = new ClientEntity();
        $client->setIdentifier('test-client');
        $client->setIdentityProvider('google');
        $client->setGoogleClientId('google-client-id');

        $this->clientRepository->method('getClientEntity')->willReturn($client);

        $this->request->method('getQueryParams')->willReturn([
            'client_id' => 'test-client',
            'redirect_uri' => 'http://localhost:3000/callback',
            'scope' => 'openid profile email',
            'state' => '12345',
            'nonce' => 'abcde',
        ]);

        // Act
        $this->authorizationController->oauthAuthorizationFlow();

        // Assert
        // Check that the header function was called with the correct location
        $this->assertContains(
            'Location: https://accounts.google.com/o/oauth2/v2/auth',
            xdebug_get_headers()
        );
    }

    public function testHandleGoogleCallback()
    {
        // Arrange
        $client = new ClientEntity();
        $client->setIdentifier('test-client');
        $client->setIdentityProvider('google');
        $client->setGoogleClientId('google-client-id');
        $client->setGoogleClientSecret('google-client-secret');

        $user = new UserEntity();
        $user->setIdentifier('test-user');

        $this->clientRepository->method('getClientEntity')->willReturn($client);
        $this->userRepository->method('getUserEntityByEmail')->willReturn($user);

        $this->request->method('getQueryParams')->willReturn([
            'code' => 'test-code',
            'state' => '12345',
        ]);

        $_SESSION['authRequestSerial'] = json_encode([
            'outer' => [
                'state' => '12345',
            ],
            'client' => [
                'identifier' => 'test-client',
            ],
            'nonce' => 'abcde',
        ]);

        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'id_token' => 'header.' . base64_encode(json_encode([
                    'aud' => 'google-client-id',
                    'nonce' => 'abcde',
                    'email' => 'test@example.com',
                ])) . '.signature',
            ])),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);

        $this->authorizationController->method('getHttpClient')->willReturn($httpClient);

        // Act
        $this->authorizationController->handleGoogleCallback();

        // Assert
        $this->authorizationServer->expects($this->once())->method('completeAuthorizationRequest');
    }
}
