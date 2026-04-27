<?php

namespace OpenEMR\Tests\Isolated\RestControllers\Authorization;

use OpenEMR\Common\Auth\UuidUserAccount;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\RestControllers\Authorization\LocalApiAuthorizationController;
use OpenEMR\Services\UserService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorageFactory;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class LocalApiAuthorizationControllerTest extends TestCase
{
    private function getLocalApiAuthorizationController(): LocalApiAuthorizationController
    {
        $mockLogger = $this->createMock(LoggerInterface::class);
        return new LocalApiAuthorizationController($mockLogger, new OEGlobalsBag());
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
        $mockLogger = $this->createMock(LoggerInterface::class);
        $controller = new LocalApiAuthorizationController($mockLogger, $globalsBag);
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
        $request->headers->set("APICSRFTOKEN", CsrfUtils::collectCsrfToken($session, 'api') ?: '');

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

    public function testAuthorizeRequestBootstrapsMissingUserUuid(): void
    {
        $userId = 1;
        $uuid = '123e4567-e89b-12d3-a456-426614174000';
        $userWithoutUuid = ['id' => $userId, 'username' => 'testuser', 'uuid' => null];
        $userWithUuid = ['id' => $userId, 'username' => 'testuser', 'uuid' => $uuid];

        $mockUserService = $this->createMock(UserService::class);
        // First call returns user with no UUID, second (after bootstrap) returns populated.
        $mockUserService->expects($this->exactly(2))
            ->method('getUser')
            ->willReturnOnConsecutiveCalls($userWithoutUuid, $userWithUuid);

        $bootstrapCalls = 0;
        $bootstrapUserIds = [];
        $controller = new class (
            $this->createMock(LoggerInterface::class),
            new OEGlobalsBag(),
            $bootstrapCalls,
            $bootstrapUserIds,
        ) extends LocalApiAuthorizationController {
            /**
             * @param list<int|string> $bootstrapUserIds
             */
            public function __construct(
                LoggerInterface $logger,
                OEGlobalsBag $globalsBag,
                public int &$bootstrapCalls,
                public array &$bootstrapUserIds,
            ) {
                parent::__construct($logger, $globalsBag);
            }
            protected function bootstrapMissingUserUuid(int|string $userId): void
            {
                $this->bootstrapCalls++;
                $this->bootstrapUserIds[] = $userId;
            }
        };
        $controller->setUserService($mockUserService);

        $request = $this->buildAuthenticatedRequest($userId);
        $this->assertTrue(
            $controller->authorizeRequest($request),
            "Expected authorizeRequest to succeed after bootstrapping missing UUID",
        );
        $this->assertSame(1, $bootstrapCalls, "Expected bootstrap to be invoked exactly once");
        $this->assertSame([$userId], $bootstrapUserIds, "Expected bootstrap to be scoped to the authenticated user");
        $this->assertSame($uuid, $request->attributes->get('userId'));
    }

    public function testAuthorizeRequestFailsWhenBootstrapCannotProduceUuid(): void
    {
        $userId = 1;
        $userWithoutUuid = ['id' => $userId, 'username' => 'testuser', 'uuid' => null];

        $mockUserService = $this->createMock(UserService::class);
        $mockUserService->expects($this->exactly(2))
            ->method('getUser')
            ->willReturn($userWithoutUuid);

        $controller = new class (
            $this->createMock(LoggerInterface::class),
            new OEGlobalsBag(),
        ) extends LocalApiAuthorizationController {
            protected function bootstrapMissingUserUuid(int|string $userId): void
            {
                // Simulate bootstrap running but failing to produce a UUID (e.g. DB issue).
            }
        };
        $controller->setUserService($mockUserService);

        $request = $this->buildAuthenticatedRequest($userId);
        $this->expectException(HttpException::class);
        $controller->authorizeRequest($request);
    }

    private function buildAuthenticatedRequest(int $userId): HttpRestRequest
    {
        $request = new HttpRestRequest();
        $sessionFactory = new MockFileSessionStorageFactory();
        $sessionStorage = $sessionFactory->createStorage($request);
        $session = new Session($sessionStorage);
        $session->start();
        CsrfUtils::setupCsrfKey($session);
        $session->set('authUserID', $userId);
        $request->setSession($session);
        $request->headers->set('APICSRFTOKEN', CsrfUtils::collectCsrfToken($session, 'api') ?: '');
        return $request;
    }
}
