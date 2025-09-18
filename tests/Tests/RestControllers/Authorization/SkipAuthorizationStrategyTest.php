<?php

namespace OpenEMR\Tests\RestControllers\Authorization;

use OpenEMR\Common\Auth\UuidUserAccount;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\RestControllers\Authorization\SkipAuthorizationStrategy;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorageFactory;

class SkipAuthorizationStrategyTest extends TestCase
{
    public function testShouldSkipOptionsMethod(): void
    {
        $request = HttpRestRequest::create("/apis/default/fhir/Patient", "OPTIONS");
        $skipAuthorizationStrategy = new SkipAuthorizationStrategy();
        $skipAuthorizationStrategy->shouldSkipOptionsMethod(false);
        // no routes so first we should skip
        $this->assertFalse($skipAuthorizationStrategy->shouldProcessRequest($request), "Options should NOT be skipped");
        $skipAuthorizationStrategy->shouldSkipOptionsMethod(true);
        $this->assertTrue($skipAuthorizationStrategy->shouldProcessRequest($request), "Options should be skipped when flag is set");
    }

    public function testAddSkipRoute(): void
    {
        $this->markTestIncomplete("Test is incomplete");
    }

    public function testShouldProcessRequest(): void
    {
        $this->markTestIncomplete("Test is incomplete");
    }

    public function testAuthorizeRequestWithValidSkippedPath(): void
    {
        $userId = 1;
        $request = HttpRestRequest::create("/apis/default/fhir/metadata", "GET");
        $session = $this->getMockSessionForRequest($request);
        $session->set("authUserId", $userId);
        $request->setSession($session);
        $skipAuthorizationStrategy = new SkipAuthorizationStrategy();
        $mockUserService = $this->createMock(\OpenEMR\Services\UserService::class);
        $userUuid = '123e4567-e89b-12d3-a456-426614174000';
        $mockUserService->expects($this->once())
            ->method('getUser')
            ->with($userId)
            ->willReturn(['id' => $userId, 'uuid' => $userUuid, 'username' => 'testuser']);
        $skipAuthorizationStrategy->setUserService($mockUserService);
        $skipAuthorizationStrategy->addSkipRoute("/apis/default/fhir/metadata");
        $this->assertTrue($skipAuthorizationStrategy->authorizeRequest($request), "Path should be authorized for skipped path");
        // now assert all the attributes that were populated.

        $this->assertEquals($userId, $request->getRequestUser()['id'], "Expected user ID to be set to 1 for skipped path");
        $this->assertEquals($userUuid, $request->getRequestUser()['uuid'], "Expected user uuid to be set for skipped path");
        $this->assertEquals(null, $request->attributes->get('clientId'), "Expected clientId to be null for skipped path");
        $this->assertEquals(null, $request->attributes->get('tokenId'), "Expected tokenId to be null for skipped path");
        $this->assertEquals(UuidUserAccount::USER_ROLE_USERS, $request->getRequestUserRole(), "Expected user role to be 'users' for skipped path");
        $this->assertEquals($userId, $request->attributes->get('userId'), "Expected userId attribute to be set to 1 for skipped path");
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
