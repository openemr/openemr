<?php

namespace OpenEMR\Tests\RestControllers\Authorization;

use Monolog\Level;
use OpenEMR\Common\Auth\UuidUserAccount;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\RestControllers\Authorization\LocalApiAuthorizationController;
use OpenEMR\Services\UserService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorageFactory;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class LocalApiAuthorizationControllerTest extends TestCase
{
    // Define the log level for the tests, can change this in order to debug issues
    const LOG_LEVEL = Level::Critical;

    private function getLocalApiAuthorizationController(): LocalApiAuthorizationController
    {
        $systemLogger = new SystemLogger(self::LOG_LEVEL);
        return new LocalApiAuthorizationController($systemLogger, new OEGlobalsBag());
    }
    public function testShouldProcessRequest(): void
    {
        $controller = $this->getLocalApiAuthorizationController();
        $request = new HttpRestRequest();
        $request->headers->set("APICSRFTOKEN", "test-token");

        $this->assertTrue($controller->shouldProcessRequest($request), "Expected shouldProcessRequest to return true for local API request");
    }

    public function testShouldProcessRequestFailsWithoutToken(): void
    {
        $controller = $this->getLocalApiAuthorizationController();
        $request = new HttpRestRequest();

        $this->assertFalse($controller->shouldProcessRequest($request), "Expected shouldProcessRequest to return false without APICSRFTOKEN header");
    }

    public function testAuthorizeRequest(): void
    {
        $globalsBag = new OEGlobalsBag();
        $controller = new LocalApiAuthorizationController(new SystemLogger(self::LOG_LEVEL), $globalsBag);
        $uuid = '123e4567-e89b-12d3-a456-426614174000'; // Example UUID
        $userId = 1;
        $userUsername = "testuser";
        $userArray = [
            'id' => $userId,
            'username' => $userUsername,
            'uuid' => $uuid,
        ];
        $mockUserService = $this->createMock(UserService::class);
        $mockUserService->expects($this->once())
            ->method('getUser')
            ->willReturn($userArray);
        $controller->setUserService($mockUserService);
        $request = new HttpRestRequest();

        // Simulate a session with a user
        $sessionFactory = new MockFileSessionStorageFactory();
        $sessionStorage = $sessionFactory->createStorage($request);
        $session = new Session($sessionStorage);
        $session->start();
        CsrfUtils::setupCsrfKey($session);
        $session->set("authUserID", $userId);
        $request->setSession($session);
        $request->headers->set("APICSRFTOKEN", CsrfUtils::collectCsrfToken('api', $session));

        $this->assertTrue($controller->authorizeRequest($request), "Expected authorizeRequest to return true for valid local API request");
        $this->assertTrue($request->attributes->has('userId'), "Expected request to have userId attribute set");
        $this->assertTrue($request->attributes->has('clientId'), "Expected request to have clientId attribute set");
        $this->assertTrue($request->attributes->has('tokenId'), "Expected request to have tokenId attribute set");
        $this->assertEquals(true, $globalsBag->get("is_local_api", null), "Expected is_local_api to be set in globals bag");
        $this->assertEquals($uuid, $request->attributes->get('userId'), "Expected userId attribute to match session userId");
        $this->assertEquals(UuidUserAccount::USER_ROLE_USERS, $request->getRequestUserRole(), "Expected request user role to be 'users'");
        $this->assertEquals($userArray, $request->getRequestUser(), "Expected request user to match session userId");
    }

    public function testAuthorizeRequestWithMissingCSRFHeader(): void
    {
        $request = new HttpRestRequest();
        $sessionFactory = new MockFileSessionStorageFactory();
        $sessionStorage = $sessionFactory->createStorage($request);
        $session = new Session($sessionStorage);
        $session->start();
        $request->setSession($session);
        $controller = $this->getLocalApiAuthorizationController();

        $this->expectException(UnauthorizedHttpException::class);
        $controller->authorizeRequest($request);
    }

    public function testAuthorizeRequestWithInvalidCSRFHeader(): void
    {
        $request = new HttpRestRequest();
        $sessionFactory = new MockFileSessionStorageFactory();
        $sessionStorage = $sessionFactory->createStorage($request);
        $session = new Session($sessionStorage);
        $session->start();
        CsrfUtils::setupCsrfKey($session);
        $request->setSession($session);
        $request->headers->set("APICSRFTOKEN", "test-token");
        $controller = $this->getLocalApiAuthorizationController();

        $this->expectException(UnauthorizedHttpException::class);
        $controller->authorizeRequest($request);
    }
}
