<?php

namespace OpenEMR\Tests\RestControllers\SMART;

use Monolog\Level;
use OpenEMR\Common\Acl\AccessDeniedException;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\FHIR\SMART\SmartLaunchController;
use OpenEMR\RestControllers\SMART\PatientContextSearchController;
use OpenEMR\RestControllers\SMART\SMARTAuthorizationController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorageFactory;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Twig\Environment;

class SMARTAuthorizationControllerTest extends TestCase {

    const LOG_LEVEL = Level::Critical;

    const SMART_FINAL_REDIRECT_URL = "http://localhost:8080/smart/final_redirect";

    public function testGetSmartAuthorizationPath()
    {
        $this->markTestIncomplete("Test is incomplete");
    }

    private function getDefaultSMARTAuthorizationController(SessionInterface $session, HttpRestRequest $request)
    {
        CsrfUtils::setupCsrfKey($session);
        $request->request->set("csrf_token", CsrfUtils::collectCsrfToken("oauth2", $session)); // Simulate a CSRF token in the request
        $session->set("user_id", 1); // Simulate a user ID being set in the session
        $logger = new SystemLogger(self::LOG_LEVEL);
        return new SMARTAuthorizationController($session, $logger, "", self::SMART_FINAL_REDIRECT_URL
            , "", $this->createMock(Environment::class), $this->createMock(EventDispatcher::class));
    }
    public function testPatientSelectConfirm()
    {
        $request = HttpRestRequest::create("/apis/default/fhir/Patient", "GET");
        $request->request->set("patient_id", "123e4567-e89b-12d3-a456-426614174000"); // Simulate a patient ID in the request
        $session = $this->getMockSessionForRequest($request);
        $controller = $this->getDefaultSMARTAuthorizationController($session, $request);
        $patientContextSearchController = $this->createMock(PatientContextSearchController::class);
        $patientContextSearchController->method('getPatientForUser')
            ->willReturn(['id' => '123e4567-e89b-12d3-a456-426614174000', 'name' => 'John Doe']);
        $controller->setPatientContextSearchController($patientContextSearchController);
        $response = $controller->patientSelectConfirm($request);
        $this->assertEquals(Response::HTTP_TEMPORARY_REDIRECT, $response->getStatusCode(), "Expected response status code to be 307 Temporary Redirect");
        $this->assertNotEmpty($response->getHeader("Location"), "Expected a redirect location header to be set");
        $this->assertStringStartsWith(self::SMART_FINAL_REDIRECT_URL, $response->getHeader("Location")[0], "Expected redirect to the final redirect URL");
    }

    public function testPatientSelectConfirmMissingUserIdThrowsException() {
        $request = HttpRestRequest::create("/apis/default/fhir/Patient", "GET");
        $session = $this->getMockSessionForRequest($request);
        $controller = $this->getDefaultSMARTAuthorizationController($session, $request);
        $session->clear(); // Clear the session to simulate missing user ID
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage("Unauthorized call");
        $controller->patientSelectConfirm($request);
    }
    public function testPatientSelectConfirmMissingCsrfFromPostThrowsException() {
        $request = HttpRestRequest::create("/apis/default/fhir/Patient", "POST");
        $session = $this->getMockSessionForRequest($request);
        $controller = $this->getDefaultSMARTAuthorizationController($session, $request);
        $request->request->remove("csrf_token"); // Remove CSRF token to simulate missing token
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage("Invalid CSRF token");
        $controller->patientSelectConfirm($request);
    }

    public function testPatientSelectConfirmPatientSearchExceptionHandled() {
        $clientId = "test_client_id";
        $clientRedirectUri = "http://localhost:8080/redirect";
        $request = HttpRestRequest::create("/apis/default/fhir/Patient", "POST");
        $session = $this->getMockSessionForRequest($request);
        $session->set("client_id", $clientId); // Set a client ID in the session
        $controller = $this->getDefaultSMARTAuthorizationController($session, $request);

        $clientEntity = new ClientEntity();
        $clientEntity->setIdentifier($clientId);
        $clientEntity->setRedirectUri($clientRedirectUri);
        $mockClientRepository = $this->createMock(ClientRepository::class);
        $mockClientRepository->expects($this->once())
            ->method('getClientEntity')
            ->with($clientId)
            ->willReturn($clientEntity);

        $mockSearchController = $this->createMock(PatientContextSearchController::class);
        $mockSearchController->method('getPatientForUser')
            ->willThrowException(new AccessDeniedException("patient", "demo", "Accessed denied to patient data"));
        $controller->setPatientContextSearchController($mockSearchController);
        $controller->setClientRepository($mockClientRepository);

        // Call the method that should trigger the exception
        $response = $controller->patientSelectConfirm($request);

        $this->assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
        $this->assertNotEmpty($response->getHeader("Location"), "Expected a redirect location header to be set");
        $this->assertStringStartsWith($clientRedirectUri, $response->getHeader("Location")[0], "Expected redirect to the client's redirect URI");

        // need to populate the session client_id and fake the ClientRepository
        $this->assertEmpty($session->all(), "Expected session to be empty after redirect");
    }

    public function testPatientSelect()
    {
        $this->markTestIncomplete("Test is incomplete");
    }

    public function testIsValidRoute()
    {
        $this->markTestIncomplete("Test is incomplete");
    }

    public function test__construct()
    {
        $this->markTestIncomplete("Test is incomplete");
    }

    public function testEmitResponse()
    {
        $this->markTestIncomplete("Test is incomplete");
    }

    public function testDispatchRoute()
    {
        $this->markTestIncomplete("Test is incomplete");
    }

    public function testNeedSMARTAuthorization()
    {
        $request = HttpRestRequest::create("/apis/default/fhir/Patient", "GET");
        $session = $this->getMockSessionForRequest($request);
        $session->set("scopes", "openid " . SmartLaunchController::CLIENT_APP_STANDALONE_LAUNCH_SCOPE);
        $logger = new SystemLogger(self::LOG_LEVEL);
        $controller = new SMARTAuthorizationController($session, $logger, "", ""
            , "", $this->createMock(Environment::class), $this->createMock(EventDispatcher::class));
        $this->assertTrue($controller->needSMARTAuthorization(), "SMART Authorization should be needed ");
    }

    public function testNeedSMARTAuthorizationNoScopes()
    {
        $request = HttpRestRequest::create("/apis/default/fhir/Patient", "GET");
        $session = $this->getMockSessionForRequest($request);
        $session->set("scopes", "");
        $logger = new SystemLogger(self::LOG_LEVEL);
        $controller = new SMARTAuthorizationController($session, $logger, "", ""
            , "", $this->createMock(Environment::class), $this->createMock(EventDispatcher::class));
        $this->assertFalse($controller->needSMARTAuthorization(), "SMART Authorization should NOT be needed ");
    }

    public function testNeedSMARTAuthorizationWithPatientUuidShouldFail()
    {
        $request = HttpRestRequest::create("/apis/default/fhir/Patient", "GET");
        $session = $this->getMockSessionForRequest($request);
        $session->set("scopes", "openid " . SmartLaunchController::CLIENT_APP_STANDALONE_LAUNCH_SCOPE);
        $session->set("puuid", "123e4567-e89b-12d3-a456-426614174000");
        $logger = new SystemLogger(self::LOG_LEVEL);
        $controller = new SMARTAuthorizationController($session, $logger, "", ""
            , "", $this->createMock(Environment::class), $this->createMock(EventDispatcher::class));
        $this->assertFalse($controller->needSMARTAuthorization(), "SMART Authorization should NOT be needed ");
    }

    public function testEhrLaunchAutoSubmit()
    {
        $this->markTestIncomplete("Test is incomplete");
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
}
