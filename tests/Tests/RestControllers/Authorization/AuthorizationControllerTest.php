<?php

namespace OpenEMR\Tests\RestControllers\Authorization;

use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Utils\HttpUtils;
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
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthorizationControllerTest extends TestCase
{
    private const LOGOUT_TEST_CLIENT_ID = 'test-logout-client-id';

    protected function tearDown(): void
    {
        QueryUtils::sqlStatementThrowException("DELETE FROM `oauth_clients` WHERE `client_id` = ?", [self::LOGOUT_TEST_CLIENT_ID], true);
    }

    private function getMockRequest(): HttpRestRequest
    {
        return HttpRestRequest::create('/test');
    }

    /**
     * Registers an oauth_clients row with the given pipe-delimited logout_redirect_uris
     * so userSessionLogout() has something to look up.
     */
    private function insertLogoutTestClient(string $logoutRedirectUris): void
    {
        QueryUtils::sqlInsert(
            "INSERT INTO `oauth_clients` (`client_id`, `client_name`, `logout_redirect_uris`) VALUES (?, ?, ?)",
            [self::LOGOUT_TEST_CLIENT_ID, 'Logout Test Client', $logoutRedirectUris]
        );
    }

    /**
     * Builds an id_token_hint shaped like a real JWT (header.payload.signature). The controller
     * never verifies the signature, it only reads the payload segment, so the header and
     * signature segments just need to be present.
     */
    private function getIdTokenHint(string $subject): string
    {
        $payload = HttpUtils::base64url_encode(json_encode([
            'aud' => self::LOGOUT_TEST_CLIENT_ID,
            'sub' => $subject,
        ], JSON_THROW_ON_ERROR));
        return "header.$payload.signature";
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
        $coreKernel->method('getProjectDir')->willReturn(dirname(__DIR__, 4));
        $coreKernel->method('getWebRoot')->willReturn('');
        /** @var array<string, mixed> $globalParams */
        $globalParams = array_merge([
            'kernel' => $coreKernel,
        ], $globalValues);
        $globalsBag = new OEGlobalsBag($globalParams);
        $kernel = $this->createMock(OEHttpKernel::class);
        $kernel->method("getEventDispatcher")->willReturn(new EventDispatcher());
        $kernel->method("getGlobalsBag")->willReturn($globalsBag);$authorizationController = new AuthorizationController(
        session: $session,
        kernel: $kernel,
        logger: $this->createMock(LoggerInterface::class)
        );
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

    public function testUserSessionLogoutRedirectsToRegisteredLogoutUri(): void
    {
        $this->insertLogoutTestClient('https://example.com/logged-out|https://example.com/other-logout');
        $request = $this->getMockRequest();
        $session = $this->getMockSessionForRequest($request);
        $request->setSession($session);
        $request->query->set('id_token_hint', $this->getIdTokenHint('test-subject'));
        $request->query->set('post_logout_redirect_uri', 'https://example.com/logged-out');
        $request->query->set('state', 'xyz');
        $authorizationController = $this->getDefaultAuthorizationControllerForRequest($request);
        $response = $authorizationController->userSessionLogout($request);
        $this->assertEquals(Response::HTTP_TEMPORARY_REDIRECT, $response->getStatusCode(), "Expected redirect for a registered logout uri");
        $this->assertEquals('https://example.com/logged-out?state=xyz', $response->getHeaderLine('Location'));
    }

    public function testUserSessionLogoutRejectsUnregisteredLogoutUri(): void
    {
        $this->insertLogoutTestClient('https://example.com/logged-out');
        $request = $this->getMockRequest();
        $session = $this->getMockSessionForRequest($request);
        $request->setSession($session);
        $request->query->set('id_token_hint', $this->getIdTokenHint('test-subject'));
        $request->query->set('post_logout_redirect_uri', 'https://attacker.example/phish');
        $request->query->set('state', 'xyz');
        $authorizationController = $this->getDefaultAuthorizationControllerForRequest($request);
        try {
            $authorizationController->userSessionLogout($request);
            $this->fail('Expected an HttpException for an unregistered logout uri');
        } catch (HttpException $e) {
            $this->assertEquals(Response::HTTP_UNAUTHORIZED, $e->getStatusCode(), "Expected 401, not a redirect, for an unregistered logout uri");
        }
    }
}
