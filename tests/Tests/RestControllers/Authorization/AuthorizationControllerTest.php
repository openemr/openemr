<?php

namespace OpenEMR\Tests\RestControllers\Authorization;

use League\OAuth2\Server\Exception\OAuthServerException;
use Monolog\Level;
use Nyholm\Psr7\Stream;
use OAuthException;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ScopeRepository;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\RestControllers\AuthorizationController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ServerBag;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorageFactory;

class AuthorizationControllerTest extends TestCase
{
    private function getMockRequest(): HttpRestRequest
    {
        $request = $this->createMock(HttpRestRequest::class);
        $request->request = new InputBag();
        $request->server = new ServerBag();
        $request->headers = new HeaderBag();
        $request->files = new FileBag();
        $request->cookies = new InputBag();
        $request->query = new InputBag();
        $request->attributes = new AttributeBag();
        $request->method('getContent')->willReturn(Stream::create(''));
        return $request;
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
    public function getDefaultAuthorizationControllerForRequest(HttpRestRequest $request) : AuthorizationController {
        $request = $this->getMockRequest();
        $session = $this->getMockSessionForRequest($request);
        $authorizationController = new AuthorizationController($session, new OEGlobalsBag([]));
        $authorizationController->setSystemLogger(new SystemLogger(Level::Emergency));
        return $authorizationController;
    }
    public function testOauthAuthorizationFlowMissingResponseType() {
        $request = $this->getMockRequest();
        $authorizationController = $this->getDefaultAuthorizationControllerForRequest($request);
        $response = $authorizationController->oauthAuthorizationFlow($request);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode(), "Expected 400 Bad Request response");
    }
    public function testOauthAuthorizationFlowMissingClientId() {
        $request = $this->getMockRequest();
        $request->query->set('response_type', 'code');
        $authorizationController = $this->getDefaultAuthorizationControllerForRequest($request);
        $response = $authorizationController->oauthAuthorizationFlow($request);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode(), "Expected 400 Bad Request response");
    }

    public function testOauthAuthorizationFlowWithInvalidClientId() {
        $request = $this->getMockRequest();
        $request->query->set('response_type', 'code');
        $request->query->set('client_id', 'test_client_id');
        $authorizationController = $this->getDefaultAuthorizationControllerForRequest($request);
        $response = $authorizationController->oauthAuthorizationFlow($request);
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode(), "Expected 401 Unauthorized response for invalid client id");
    }
    public function testOauthAuthorizationFlowWithConfidentialClientWillRedirect() {
        $clientId = 'test_client_id';
        $redirect_uri = 'https://example.com/fhir';
        $request = $this->getMockRequest();
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
        $authorizationController->setSystemLogger(new SystemLogger(Level::Debug));
        $response = $authorizationController->oauthAuthorizationFlow($request);
        $this->assertEquals(Response::HTTP_TEMPORARY_REDIRECT, $response->getStatusCode(), "Expected 407 location redirect");
    }
//    public function testScopeAuthorizeConfirm() {
//        $request = $this->createMock(HttpRestRequest::class);
//        $session = $this->createMock(SessionInterface::class);
//        $scopes = "openid api:fhir user/Patient.rs";
//        $client_id = "test_client_id";
//        $user_id = 1;
//        $session->method("get")
//            ->willReturn($scopes);
////            ->willReturnMap([
////                ['scopes', '', $scopes],
////                ['client_id', $client_id],
////                ['user_id', $user_id]
////            ]);
//        $request->setSession($session);
//        $scopeMap = [
//            ['openid', 'api:fhir', 'user/Patient.rs'],
//            ['Open ID description', 'FHIR API Usage', 'Patient read and search']
//        ];
//        var_dump($session->get('scopes'));
//        var_dump($request->getSession()->get('scopes'));
//        die();
//        $scopeRepository = $this->createMock(ScopeRepository::class);
//        $scopeRepository->method('fhirRequiredSmartScopes')
//            ->willReturn(['openid', 'api:fhir']);
//
//        $scopeRepository->method('lookupDescriptionForScope')
//            ->willReturnMap($scopeMap);
//        $authController = new AuthorizationController();
//        $authController->setScopeRepository($scopeRepository);
//        $response = $authController->scopeAuthorizeConfirm($request);
//
//        var_dump($session->get('scopes', ''));
//        die();
//        $this->assertInstanceOf(Response::class, $response);
//        $this->assertEquals(200, $response->getStatusCode(), "Expected 200 OK response");
//        $content = $response->getContent();
//        $this->assertContains("OpenEMR Authorization", $content, "Expected content to contain 'OpenEMR Authorization'");
//
//        // all the content should be placed, not sure how we want to differentiate between Other and regular scopes.
//        foreach ($scopeMap[1] as $scopeDescription) {
//            $this->assertContains($scopeDescription, $content, "Expected content to contain scope description: $scopeDescription");
//        }
//    }
}
