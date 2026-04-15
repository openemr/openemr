<?php

namespace OpenEMR\Tests\RestControllers\Authorization;

use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Core\Kernel;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Core\OEHttpKernel;
use OpenEMR\RestControllers\AuthorizationController;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorageFactory;

class AuthorizationControllerTest extends TestCase
{
    private function getMockRequest(): HttpRestRequest
    {
        return HttpRestRequest::create('/test');
    }
    private function getMockSessionForRequest(HttpRestRequest $request): SessionInterface
    {
        $sessionFactory = new MockFileSessionStorageFactory();
        $sessionStorage = $sessionFactory->createStorage($request);
        $session = new Session($sessionStorage);
        $session->start();
        $session->set("site_id", "default");
        return $session;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function createJsonRequest(string $path, array $payload): HttpRestRequest
    {
        return HttpRestRequest::create($path, 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($payload, JSON_THROW_ON_ERROR));
    }

    /**
     * @param HttpRestRequest $request
     * @param array $globalValues
     * @return AuthorizationController
     * @throws \League\OAuth2\Server\Exception\OAuthServerException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function getDefaultAuthorizationControllerForRequest(HttpRestRequest $request, array $globalValues = []): AuthorizationController
    {
        $request = $this->getMockRequest();
        $session = $this->getMockSessionForRequest($request);
        $request->setSession($session);
        $coreKernel = $this->createMock(Kernel::class);
        $coreKernel->method('getEventDispatcher')
            ->willReturn(new EventDispatcher());
        $coreKernel->method('getProjectDir')->willReturn(dirname(__DIR__, 4));
        $coreKernel->method('getWebRoot')->willReturn('');
        /** @var array<string, mixed> $globalParams */
        $globalParams = array_merge([
            'kernel' => $coreKernel,
        ], $globalValues);
        $globalsBag = new OEGlobalsBag($globalParams);
        $kernel = $this->createMock(OEHttpKernel::class);
        $kernel->method("getEventDispatcher")
            ->willReturn(new EventDispatcher());
        $kernel->method("getGlobalsBag")->willReturn($globalsBag);
        $authorizationController = new AuthorizationController($session, $kernel);
        $authorizationController->setSystemLogger($this->createMock(LoggerInterface::class));
        return $authorizationController;
    }
    public function testOauthAuthorizationFlowMissingResponseType(): void
    {
        $request = $this->getMockRequest();
        $session = $this->getMockSessionForRequest($request);
        $request->setSession($session);
        $authorizationController = $this->getDefaultAuthorizationControllerForRequest($request);
        $response = $authorizationController->oauthAuthorizationFlow($request);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode(), "Expected 400 Bad Request response");
    }

    public function testOauthAuthorizationFlowMissingClientId(): void
    {
        $request = $this->getMockRequest();
        $session = $this->getMockSessionForRequest($request);
        $request->setSession($session);
        $request->query->set('response_type', 'code');
        $authorizationController = $this->getDefaultAuthorizationControllerForRequest($request);
        $response = $authorizationController->oauthAuthorizationFlow($request);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode(), "Expected 400 Bad Request response");
    }

    public function testOauthAuthorizationFlowWithInvalidClientId(): void
    {
        $request = $this->getMockRequest();
        $session = $this->getMockSessionForRequest($request);
        $request->setSession($session);
        $request->query->set('response_type', 'code');
        $request->query->set('client_id', 'test_client_id');
        $authorizationController = $this->getDefaultAuthorizationControllerForRequest($request);
        $response = $authorizationController->oauthAuthorizationFlow($request);
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode(), "Expected 401 Unauthorized response for invalid client id");
    }
    public function testOauthAuthorizationFlowWithConfidentialClientWillRedirect(): void
    {
        $clientId = 'test_client_id';
        $redirect_uri = 'https://example.com/fhir';
        $request = $this->getMockRequest();
        $session = $this->getMockSessionForRequest($request);
        $request->setSession($session);
        $request->query->set('response_type', 'code');
        $request->query->set('client_id', $clientId);
        $request->query->set('redirect_uri', $redirect_uri);
        $request->query->set('scope', 'openid api:fhir user/Patient.rs');
        $clientRepository = $this->createMock(ClientRepository::class);
        $clientEntity = new ClientEntity();
        $clientEntity->setIdentifier($clientId);
        $clientEntity->setRedirectUri($redirect_uri);
        $clientEntity->setIsEnabled(true);
        $clientEntity->setIsConfidential(true);
        $clientRepository->method('getClientEntity')->willReturn($clientEntity);
        $authorizationController = $this->getDefaultAuthorizationControllerForRequest($request);
        $authorizationController->setClientRepository($clientRepository);
        $response = $authorizationController->oauthAuthorizationFlow($request);
        $this->assertEquals(Response::HTTP_TEMPORARY_REDIRECT, $response->getStatusCode(), "Expected 407 location redirect");
    }

    public function testOauthAuthorizationFlowWithPostData(): void {
        $clientId = 'test_client_id';
        $redirect_uri = 'https://example.com/fhir';
        $request = $this->getMockRequest();
        $session = $this->getMockSessionForRequest($request);
        $request->setSession($session);
        $request->request->set('response_type', 'code');
        $request->request->set('client_id', $clientId);
        $request->request->set('redirect_uri', $redirect_uri);
        $request->request->set('scope', 'openid api:fhir user/Patient.rs');
        $clientRepository = $this->createMock(ClientRepository::class);
        $clientEntity = new ClientEntity();
        $clientEntity->setIdentifier($clientId);
        $clientEntity->setRedirectUri($redirect_uri);
        $clientEntity->setIsEnabled(true);
        $clientEntity->setIsConfidential(true);
        $clientRepository->method('getClientEntity')->willReturn($clientEntity);
        $authorizationController = $this->getDefaultAuthorizationControllerForRequest($request);
        $authorizationController->setClientRepository($clientRepository);
        $response = $authorizationController->oauthAuthorizationFlow($request);
        $this->assertEquals(Response::HTTP_TEMPORARY_REDIRECT, $response->getStatusCode(), "Expected 407 location redirect");
    }

    public function testClientRegistrationRejectsUnsupportedTokenEndpointAuthMethod(): void
    {
        $request = $this->createJsonRequest('/oauth2/default/register', [
            'application_type' => 'private',
            'redirect_uris' => ['https://example.com/callback'],
            'client_name' => 'Test Client',
            'token_endpoint_auth_method' => 'client_secret_invalid',
            'contacts' => ['test@open-emr.org'],
            'scope' => 'openid',
        ]);
        $session = $this->getMockSessionForRequest($request);
        $request->setSession($session);

        $authorizationController = $this->getDefaultAuthorizationControllerForRequest($request);
        $response = $authorizationController->clientRegistration($request);

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertStringContainsString('Unsupported token_endpoint_auth_method value', $response->getBody()->getContents());
    }

    public function testClientRegistrationEncodesJwksArrayBeforeRedirectValidation(): void
    {
        $request = $this->createJsonRequest('/oauth2/default/register', [
            'application_type' => 'private',
            'client_name' => 'Test Client',
            'token_endpoint_auth_method' => 'client_secret_post',
            'contacts' => ['test@open-emr.org'],
            'scope' => 'openid',
            'jwks' => [
                'keys' => [
                    ['kty' => 'RSA', 'kid' => 'test-key'],
                ],
            ],
        ]);
        $session = $this->getMockSessionForRequest($request);
        $request->setSession($session);

        $authorizationController = $this->getDefaultAuthorizationControllerForRequest($request);
        $response = $authorizationController->clientRegistration($request);

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertStringContainsString('redirect_uris is invalid', $response->getBody()->getContents());
    }

    public function testClientRegistrationRejectsNonStringRedirectUris(): void
    {
        $request = $this->createJsonRequest('/oauth2/default/register', [
            'application_type' => 'private',
            'redirect_uris' => ['https://example.com/callback', 123],
            'client_name' => 'Test Client',
            'token_endpoint_auth_method' => 'client_secret_post',
            'contacts' => ['test@open-emr.org'],
            'scope' => 'openid',
        ]);
        $session = $this->getMockSessionForRequest($request);
        $request->setSession($session);

        $authorizationController = $this->getDefaultAuthorizationControllerForRequest($request);
        $response = $authorizationController->clientRegistration($request);

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertStringContainsString('redirect_uris is invalid', $response->getBody()->getContents());
    }
}
