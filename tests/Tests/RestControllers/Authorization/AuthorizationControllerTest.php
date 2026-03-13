<?php

namespace OpenEMR\Tests\RestControllers\Authorization;

use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Common\Http\HttpRestRequest;
use Psr\Log\LoggerInterface;
use OpenEMR\Core\Kernel;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Core\OEHttpKernel;
use OpenEMR\RestControllers\AuthorizationController;
use PHPUnit\Framework\TestCase;
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
        $globalsBag = new OEGlobalsBag(
            array_merge([
            'kernel' => $coreKernel,
            ], $globalValues));
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
}
