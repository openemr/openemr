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
     * @param HttpRestRequest $request
     * @param array $globalValues
     * @return AuthorizationController
     * @throws \League\OAuth2\Server\Exception\OAuthServerException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function getDefaultAuthorizationControllerForRequest(HttpRestRequest $request, array $globalValues = []): AuthorizationController
    {
        // Reuse the session the caller already attached to the request.
        // The previous shape created a throwaway session and passed it
        // to the controller constructor — controller writes to
        // `$this->session` then went to that throwaway, invisible to
        // tests that assert on session state. The status-code-only
        // tests above didn't notice because the response object
        // doesn't depend on session writes.
        $session = $request->getSession();
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

    /**
     * Critical OAuth2 security check: the request's redirect_uri must
     * match exactly what the client registered. A mismatch is an
     * open-redirect attempt — the League library rejects via
     * OAuthServerException::invalidClient and the controller's catch
     * converts that to an HTTP error response. Currently uncovered by
     * the existing tests, which only exercise no-client / no-redirect_uri.
     */
    public function testOauthAuthorizationFlowRejectsMismatchedRedirectUri(): void
    {
        $clientId = 'test_client_id';
        $registeredRedirectUri = 'https://registered.example.com/callback';
        $attackerRedirectUri = 'https://attacker.example/steal';

        $request = $this->getMockRequest();
        $session = $this->getMockSessionForRequest($request);
        $request->setSession($session);
        $request->query->set('response_type', 'code');
        $request->query->set('client_id', $clientId);
        $request->query->set('redirect_uri', $attackerRedirectUri);
        $request->query->set('scope', 'openid api:fhir user/Patient.rs');

        $clientRepository = $this->createMock(ClientRepository::class);
        $clientEntity = new ClientEntity();
        $clientEntity->setIdentifier($clientId);
        $clientEntity->setRedirectUri($registeredRedirectUri);
        $clientEntity->setIsEnabled(true);
        $clientEntity->setIsConfidential(true);
        $clientRepository->method('getClientEntity')->willReturn($clientEntity);

        $authorizationController = $this->getDefaultAuthorizationControllerForRequest($request);
        $authorizationController->setClientRepository($clientRepository);

        $response = $authorizationController->oauthAuthorizationFlow($request);

        // 401 Unauthorized — same shape as invalid_client_id, since
        // the League library's RedirectUri::validate() throws
        // invalidClient on a mismatch. The point is: not 307 (no
        // login form rendered), not 200 (no consent shown).
        $this->assertEquals(
            Response::HTTP_UNAUTHORIZED,
            $response->getStatusCode(),
            'Expected 401 — open-redirect attempt must be rejected before any auth flow',
        );
    }

    /**
     * OpenID Connect Core §3 limits the authorize endpoint's
     * `response_type` to `code` (authorization code flow) and `id_token`
     * variants. The deprecated implicit grant (`response_type=token`)
     * must not be accepted. The League library's grant configuration
     * rejects unsupported response types via
     * OAuthServerException::unsupportedResponseType.
     */
    public function testOauthAuthorizationFlowRejectsUnsupportedResponseType(): void
    {
        $clientId = 'test_client_id';
        $redirect_uri = 'https://example.com/fhir';

        $request = $this->getMockRequest();
        $session = $this->getMockSessionForRequest($request);
        $request->setSession($session);
        // `token` is the deprecated implicit-grant response_type. We
        // never enable the implicit grant; the League library should
        // reject before it even consults the client.
        $request->query->set('response_type', 'token');
        $request->query->set('client_id', $clientId);
        $request->query->set('redirect_uri', $redirect_uri);
        $request->query->set('scope', 'openid');

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

        // Anything in the 4xx family proves rejection — not 307 (login
        // form) and not 200 (consent screen).
        $this->assertGreaterThanOrEqual(
            400,
            $response->getStatusCode(),
            'Implicit-grant response_type must be rejected with a 4xx error',
        );
        $this->assertLessThan(500, $response->getStatusCode());
    }

    /**
     * Round-4 finding #2 (CWE-532) regression. The fix moved the
     * authorize-flow log payload from raw `$session->all()` to a
     * sanitized fingerprint built by `OAuthLogContext::forSession()`.
     * That helper depends on the controller actually populating these
     * specific session keys (csrf, scopes, client_id) — if a refactor
     * stops populating them, the helper still produces output but the
     * `has_csrf:false` flags become misleading (looks like "no flow in
     * progress" rather than "flow in progress, but the controller
     * stopped writing this slot").
     *
     * Pin the contract: after a successful authorize-flow validation,
     * those three keys are in the session.
     */
    public function testOauthAuthorizationFlowSetsExpectedSessionKeysOnSuccess(): void
    {
        $clientId = 'test_client_id';
        $redirect_uri = 'https://example.com/fhir';
        $scope = 'openid api:fhir user/Patient.rs';

        $request = $this->getMockRequest();
        $session = $this->getMockSessionForRequest($request);
        $request->setSession($session);
        $request->query->set('response_type', 'code');
        $request->query->set('client_id', $clientId);
        $request->query->set('redirect_uri', $redirect_uri);
        $request->query->set('scope', $scope);
        $request->query->set('state', 'csrf-token-from-client');

        $clientRepository = $this->createMock(ClientRepository::class);
        $clientEntity = new ClientEntity();
        $clientEntity->setIdentifier($clientId);
        $clientEntity->setRedirectUri($redirect_uri);
        $clientEntity->setIsEnabled(true);
        $clientEntity->setIsConfidential(true);
        $clientRepository->method('getClientEntity')->willReturn($clientEntity);

        $authorizationController = $this->getDefaultAuthorizationControllerForRequest($request);
        $authorizationController->setClientRepository($clientRepository);

        $authorizationController->oauthAuthorizationFlow($request);

        // The controller writes these three slots after
        // validateAuthorizationRequest succeeds. OAuthLogContext::forSession()
        // checks `has_*` flags on each — emptying any of them makes the
        // round-4 #2 redaction misleading without the test suite noticing.
        $this->assertSame('csrf-token-from-client', $session->get('csrf'), 'csrf populated from request state');
        $this->assertSame($scope, $session->get('scopes'), 'scopes populated from request');
        $this->assertSame($clientId, $session->get('client_id'), 'client_id populated from request');
    }

}
