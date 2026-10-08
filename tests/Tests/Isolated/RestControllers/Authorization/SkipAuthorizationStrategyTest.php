<?php

namespace OpenEMR\Tests\Isolated\RestControllers\Authorization;

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

    // Tests below use the same path shape as the existing passing test:
    // the configured skip-route includes the full post-dispatch prefix
    // and the request path includes the same prefix. This isolates the
    // tests from Symfony's path-info rewriting and exercises only the
    // comparison direction + prefix-boundary logic.

    public function testAddSkipRouteExactPathMatches(): void
    {
        $skipAuthorizationStrategy = new SkipAuthorizationStrategy();
        $skipAuthorizationStrategy->addSkipRoute("/apis/default/fhir/metadata");
        $request = HttpRestRequest::create("/apis/default/fhir/metadata", "GET");
        $this->assertTrue(
            $skipAuthorizationStrategy->shouldProcessRequest($request),
            "An exact match on the configured skip-route should return true"
        );
    }

    public function testAddSkipRouteDeeperChildPathMatches(): void
    {
        $skipAuthorizationStrategy = new SkipAuthorizationStrategy();
        $skipAuthorizationStrategy->addSkipRoute("/apis/default/fhir/metadata");
        $request = HttpRestRequest::create("/apis/default/fhir/metadata/foo", "GET");
        $this->assertTrue(
            $skipAuthorizationStrategy->shouldProcessRequest($request),
            "A path under the configured skip-route prefix should return true"
        );
    }

    public function testAddSkipRouteShorterPrefixDoesNotMatch(): void
    {
        $skipAuthorizationStrategy = new SkipAuthorizationStrategy();
        $skipAuthorizationStrategy->addSkipRoute("/apis/default/fhir/metadata");
        // A path that is a shorter prefix of the configured skip-route must
        // not match. Locks the comparison direction so a future refactor
        // cannot reintroduce the inverted-argument behavior.
        $request = HttpRestRequest::create("/apis/default/fhir", "GET");
        $this->assertFalse(
            $skipAuthorizationStrategy->shouldProcessRequest($request),
            "A shorter prefix of the configured skip-route must not match"
        );

        $request = HttpRestRequest::create("/apis/default", "GET");
        $this->assertFalse(
            $skipAuthorizationStrategy->shouldProcessRequest($request),
            "An even shorter prefix must not match"
        );
    }

    public function testAddSkipRouteSiblingPathWithSharedPrefixDoesNotMatch(): void
    {
        $skipAuthorizationStrategy = new SkipAuthorizationStrategy();
        $skipAuthorizationStrategy->addSkipRoute("/apis/default/fhir/metadata");
        // A sibling path that shares a leading substring with the configured
        // skip-route must not match. /fhir/metadata-fake is not a child of
        // /fhir/metadata.
        $request = HttpRestRequest::create("/apis/default/fhir/metadata-fake", "GET");
        $this->assertFalse(
            $skipAuthorizationStrategy->shouldProcessRequest($request),
            "A sibling path sharing a leading substring must not match"
        );
    }

    public function testShouldProcessRequestWithNoConfiguredRoutes(): void
    {
        $skipAuthorizationStrategy = new SkipAuthorizationStrategy();
        $request = HttpRestRequest::create("/apis/default/fhir/metadata", "GET");
        $this->assertFalse(
            $skipAuthorizationStrategy->shouldProcessRequest($request),
            "No skip-routes configured means every request falls through to auth"
        );
    }

    public function testShouldProcessRequestWithUnrelatedPath(): void
    {
        $skipAuthorizationStrategy = new SkipAuthorizationStrategy();
        $skipAuthorizationStrategy->addSkipRoute("/apis/default/fhir/metadata");
        $request = HttpRestRequest::create("/apis/default/fhir/Patient", "GET");
        $this->assertFalse(
            $skipAuthorizationStrategy->shouldProcessRequest($request),
            "A path unrelated to any configured skip-route must not match"
        );
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
