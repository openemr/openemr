<?php

namespace OpenEMR\Tests\RestControllers\SMART;

use Monolog\Level;
use OpenEMR\Common\Acl\AccessDeniedException;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Core\OEHttpKernel;
use OpenEMR\Events\Core\TemplatePageEvent;
use OpenEMR\FHIR\SMART\SmartLaunchController;
use OpenEMR\RestControllers\SMART\PatientContextSearchController;
use OpenEMR\RestControllers\SMART\SMARTAuthorizationController;
use OpenEMR\Services\LogoService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorageFactory;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Twig\Environment;
use Twig\TemplateWrapper;

class SMARTAuthorizationControllerTest extends TestCase
{
    const LOG_LEVEL = Level::Critical;

    const SMART_FINAL_REDIRECT_URL = "http://localhost:8080/smart/final_redirect";

    public function testGetSmartAuthorizationPath(): void
    {
        $this->markTestIncomplete("Test is incomplete");
    }

    private function getDefaultSMARTAuthorizationController(SessionInterface $session, HttpRestRequest $request)
    {
        CsrfUtils::setupCsrfKey($session);
        $request->request->set("csrf_token", CsrfUtils::collectCsrfToken("oauth2", $session)); // Simulate a CSRF token in the request
        $session->set("user_id", 1); // Simulate a user ID being set in the session
        $logger = new SystemLogger(self::LOG_LEVEL);
        $kernel = $this->createMock(OEHttpKernel::class);
        $kernel->method('getGlobalsBag')
            ->willReturn(new OEGlobalsBag([]));
        $kernel->method('getEventDispatcher')
            ->willReturn(new EventDispatcher());
        $kernel->method('getSystemLogger')
            ->willReturn($logger);
        return new SMARTAuthorizationController(
            $session,
            $kernel,
            "",
            self::SMART_FINAL_REDIRECT_URL,
            "",
            $this->createMock(Environment::class)
        );
    }
    public function testPatientSelectConfirm(): void
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

    public function testPatientSelectConfirmMissingUserIdThrowsException(): void
    {
        $request = HttpRestRequest::create("/apis/default/fhir/Patient", "GET");
        $session = $this->getMockSessionForRequest($request);
        $controller = $this->getDefaultSMARTAuthorizationController($session, $request);
        $session->clear(); // Clear the session to simulate missing user ID
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage("Unauthorized call");
        $controller->patientSelectConfirm($request);
    }
    public function testPatientSelectConfirmMissingCsrfFromPostThrowsException(): void
    {
        $request = HttpRestRequest::create("/apis/default/fhir/Patient", "POST");
        $session = $this->getMockSessionForRequest($request);
        $controller = $this->getDefaultSMARTAuthorizationController($session, $request);
        $request->request->remove("csrf_token"); // Remove CSRF token to simulate missing token
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage("Invalid CSRF token");
        $controller->patientSelectConfirm($request);
    }

    public function testPatientSelectConfirmPatientSearchExceptionHandled(): void
    {
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

    public function testPatientSelect(): void
    {
        $request = HttpRestRequest::create("/apis/default/fhir/Patient", "GET");
        $request->request->set("user_id", "123e4567-e89b-12d3-a456-426614174000"); // Simulate a user id in the request
        $request->request->set("search", [
            'lname' => 'Doe',
            'DOB' => '1980-01-01'
        ]);

        $session = $this->getMockSessionForRequest($request);
        CsrfUtils::setupCsrfKey($session);
        $request->request->set("csrf_token", CsrfUtils::collectCsrfToken("oauth2", $session)); // Simulate a CSRF token in the request
        $session->set("user_id", 1); // Simulate a user ID being set in the session
        $logger = new SystemLogger(self::LOG_LEVEL);

        $twigVars = ['action' => 'patient-select'];
        $twigName = 'smart/patient-select.twig.html';
        $dispatcher = $this->createMock(EventDispatcher::class);
        $dispatcher->method('dispatch')
            ->withAnyParameters()
            ->willReturn(new TemplatePageEvent("test-page-name", [], $twigName, $twigVars));

        $htmlContents = '<html><body>Patient Select Page</body></html>';
        $twig = $this->createMock(Environment::class);
        $twig->expects($this->once())
            ->method('render')
            ->with($twigName, $twigVars)
            ->willReturn($htmlContents);
        $kernel = $this->createMock(OEHttpKernel::class);
        $kernel->method('getGlobalsBag')
            ->willReturn(new OEGlobalsBag([]));
        $kernel->method('getEventDispatcher')
            ->willReturn($dispatcher);
        $kernel->method('getSystemLogger')
            ->willReturn(new SystemLogger());

        $controller = new SMARTAuthorizationController(
            $session,
            $kernel,
            "",
            self::SMART_FINAL_REDIRECT_URL,
            "",
            $twig
        );
        $patientContextSearchController = $this->createMock(PatientContextSearchController::class);
        $patientContextSearchController->method('searchPatients')
            ->willReturn(
                [
                    ['id' => '123e4567-e89b-12d3-a456-426614174000', 'lname' => 'Doe', 'fname' => 'John', 'DOB' => '1980-01-01']
                ]
            );
        $controller->setPatientContextSearchController($patientContextSearchController);
        $response = $controller->patientSelect($request);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode(), "Expected response status code to be 200 OK");
        $contents = $response->getBody()->getContents();
        $this->assertEquals($htmlContents, $contents, "Expected response body to match rendered HTML");
    }

    public function testIsValidRoute(): void
    {
        $this->markTestIncomplete("Test is incomplete");
    }

    public function test__construct(): void
    {
        $this->markTestIncomplete("Test is incomplete");
    }

    public function testDispatchRoute(): void
    {
        $this->markTestIncomplete("Test is incomplete");
    }

    public function testNeedSMARTAuthorization(): void
    {
        $request = HttpRestRequest::create("/apis/default/fhir/Patient", "GET");
        $session = $this->getMockSessionForRequest($request);
        $session->set("scopes", "openid " . SmartLaunchController::CLIENT_APP_STANDALONE_LAUNCH_SCOPE);
        $logger = new SystemLogger(self::LOG_LEVEL);
        $kernel = $this->createMock(OEHttpKernel::class);
        $kernel->method('getGlobalsBag')
            ->willReturn(new OEGlobalsBag([]));
        $kernel->method('getEventDispatcher')
            ->willReturn($this->createMock(EventDispatcher::class));
        $kernel->method('getSystemLogger')
            ->willReturn($logger);
        $controller = new SMARTAuthorizationController(
            $session,
            $kernel,
            "",
            "",
            "",
            $this->createMock(Environment::class)
        );
        $this->assertTrue($controller->needSMARTAuthorization(), "SMART Authorization should be needed ");
    }

    public function testNeedSMARTAuthorizationNoScopes(): void
    {
        $request = HttpRestRequest::create("/apis/default/fhir/Patient", "GET");
        $session = $this->getMockSessionForRequest($request);
        $session->set("scopes", "");
        $logger = new SystemLogger(self::LOG_LEVEL);
        $kernel = $this->createMock(OEHttpKernel::class);
        $kernel->method("getSystemLogger")
            ->willReturn($logger);
        $kernel->method("getEventDispatcher")
            ->willReturn($this->createMock(EventDispatcherInterface::class));

        $controller = new SMARTAuthorizationController(
            $session,
            $kernel,
            "",
            "",
            "",
            $this->createMock(Environment::class)
        );
        $this->assertFalse($controller->needSMARTAuthorization(), "SMART Authorization should NOT be needed ");
    }

    public function testNeedSMARTAuthorizationWithPatientUuidShouldFail(): void
    {
        $request = HttpRestRequest::create("/apis/default/fhir/Patient", "GET");
        $session = $this->getMockSessionForRequest($request);
        $session->set("scopes", "openid " . SmartLaunchController::CLIENT_APP_STANDALONE_LAUNCH_SCOPE);
        $session->set("puuid", "123e4567-e89b-12d3-a456-426614174000");
        $logger = new SystemLogger(self::LOG_LEVEL);
        $kernel = $this->createMock(OEHttpKernel::class);
        $kernel->method('getGlobalsBag')
            ->willReturn(new OEGlobalsBag([]));
        $kernel->method('getEventDispatcher')
            ->willReturn($this->createMock(EventDispatcher::class));
        $kernel->method('getSystemLogger')
            ->willReturn($logger);
        $controller = new SMARTAuthorizationController(
            $session,
            $kernel,
            "",
            "",
            "",
            $this->createMock(Environment::class)
        );
        $this->assertFalse($controller->needSMARTAuthorization(), "SMART Authorization should NOT be needed ");
    }

    public function testEhrLaunchAutoSubmit(): void
    {
        $request = HttpRestRequest::create("/apis/default/fhir/Patient", "GET");
        $session = $this->getMockSessionForRequest($request);
        $logger = new SystemLogger(self::LOG_LEVEL);
        $twigVars = ['action' => 'patient-select'];
        $twigName = 'smart/ehr-launch-auto-submit.twig.json';
        $dispatcher = $this->createMock(EventDispatcher::class);
        $dispatcher->method('dispatch')
            ->withAnyParameters()
            ->willReturn(new TemplatePageEvent("test-page-name", [], $twigName, $twigVars));

        $contents = json_encode(['action' => 'ehr-launch-auto-submit', 'patient_id' => '123e4567-e89b-12d3-a456-426614174000']);

        $twig = $this->createMock(Environment::class);
        $twig->expects($this->once())
            ->method('render')
            ->with($twigName, $twigVars)
            ->willReturn($contents);
        $kernel = $this->createMock(OEHttpKernel::class);
        $kernel->method('getGlobalsBag')
            ->willReturn(new OEGlobalsBag([]));
        $kernel->method('getEventDispatcher')
            ->willReturn($dispatcher);
        $kernel->method('getSystemLogger')
            ->willReturn($logger);
        $controller = new SMARTAuthorizationController(
            $session,
            $kernel,
            "",
            self::SMART_FINAL_REDIRECT_URL,
            "",
            $twig
        );
        $response = $controller->ehrLaunchAutoSubmit($request);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode(), "Expected response status code to be 200 OK");
        $contents = $response->getBody()->getContents();
        $this->assertEquals($contents, $contents, "Expected response body to match rendered json");
    }

    public function testSmartAppStyles(): void
    {
        $this->markTestIncomplete("Having problems mocking the templates so leaving this incomplete for now");
//        $request = HttpRestRequest::create("/apis/default/fhir/Patient", "GET");
//        $expectedTwigName = 'oauth2/authorize/smart-style-style_light.json.twig';
//        $session = $this->getMockSessionForRequest($request);
//        $logger = new SystemLogger(self::LOG_LEVEL);
//        $twigVars = ['action' => 'patient-select'];
//        $dispatcher = $this->createMock(EventDispatcher::class);
//        $dispatcher->method('dispatch')
//            ->withAnyParameters()
//            ->willReturn(new TemplatePageEvent("test-page-name", [], $expectedTwigName, $twigVars));
//
//        $contents = json_encode(['action' => 'smart-app-styles', 'logo' => 'logo.png']);
//
//        $twig = $this->createMock(Environment::class);
//        $twig->expects($this->once())
//            ->method('resolveTemplate')
//            ->with(['oauth2/authorize/smart-style-style_light.json.twig'])
//            ->willReturn($expectedTwigName);
//
//        $twig->expects($this->once())
//            ->method('render')
//            ->with($expectedTwigName, $twigVars)
//            ->willReturn($contents);
//
//        $globalsBag = new OEGlobalsBag([
//            'site_addr_oath' => 'http://localhost:8080'
//            ,'web_root' => 'openemr'
//            ,'css_header' => 'theme_light.css'
//        ]);
//        $kernel = $this->createMock(OEHttpKernel::class);
//        $kernel->method('getGlobalsBag')
//            ->willReturn($globalsBag);
//        $kernel->method('getEventDispatcher')
//            ->willReturn($dispatcher);
//        $kernel->method('getSystemLogger')
//            ->willReturn($logger);
//        $controller = new SMARTAuthorizationController($session, $kernel, "", self::SMART_FINAL_REDIRECT_URL
//            , "", $twig);
//
//        // have to mock this regardless of the fact it doesn't make it to the twig, we just want to exercise the code path
//        $logoService = $this->createMock(LogoService::class);
//        $logoService->expects($this->once())
//            ->method('getLogo')
//            ->with('core/login/primary')
//            ->willReturn('logo.png');
//        $controller->setLogoService($logoService);
//
//        $response = $controller->smartAppStyles($request);
//        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode(), "Expected response status code to be 200 OK");
//        $contents = $response->getBody()->getContents();
//        $this->assertEquals($contents, $contents, "Expected response body to match rendered json");
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
