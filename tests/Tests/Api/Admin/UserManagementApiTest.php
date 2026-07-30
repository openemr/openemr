<?php

/**
 * Admin User Management API Endpoint Tests.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api\Admin;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Tests\Api\ApiTestClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class UserManagementApiTest extends TestCase
{
    private const API_ENDPOINT = "/apis/default/api/admin/users";
    private const USER_ENDPOINT = "/apis/default/api/user";

    private ApiTestClient $testClient;

    /** @var list<string> Usernames of users created during tests, for cleanup */
    private static array $createdUsernames = [];

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
    }

    protected function tearDown(): void
    {
    }

    public static function tearDownAfterClass(): void
    {
        // Clean up test users created during this test run
        foreach (self::$createdUsernames as $username) {
            QueryUtils::sqlStatementThrowException("DELETE FROM `groups` WHERE `user` = ?", [$username]);
            QueryUtils::sqlStatementThrowException("UPDATE `users` SET `active` = 0 WHERE BINARY `username` = ?", [$username]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeResponse(\Psr\Http\Message\ResponseInterface $response): array
    {
        /** @var array<string, mixed> $body */
        $body = json_decode((string) $response->getBody(), true);
        return $body;
    }

    // ----------------------------------------------------------------
    // Happy path: GET list
    // ----------------------------------------------------------------

    #[Test]
    public function testGetAllReturnsUsers(): void
    {
        $response = $this->testClient->get(self::API_ENDPOINT);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $body = $this->decodeResponse($response);
        /** @var array<int, mixed> $validationErrors */
        $validationErrors = $body["validationErrors"] ?? [];
        /** @var array<int, mixed> $internalErrors */
        $internalErrors = $body["internalErrors"] ?? [];
        /** @var list<array<string, mixed>> $data */
        $data = $body["data"] ?? [];
        $this->assertCount(0, $validationErrors);
        $this->assertCount(0, $internalErrors);
        $this->assertNotEmpty($data);

        // Verify admin-specific fields are present
        $firstUser = $data[0];
        $this->assertArrayHasKey('username', $firstUser);
        $this->assertArrayHasKey('authorized', $firstUser);
        $this->assertArrayHasKey('acl_groups', $firstUser);
        $this->assertArrayHasKey('uuid', $firstUser);
    }

    #[Test]
    public function testGetAllWithFilter(): void
    {
        $response = $this->testClient->get(self::API_ENDPOINT, ["username" => "admin"]);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $body = $this->decodeResponse($response);
        /** @var array<int, mixed> $validationErrors */
        $validationErrors = $body["validationErrors"] ?? [];
        /** @var array<int, mixed> $internalErrors */
        $internalErrors = $body["internalErrors"] ?? [];
        /** @var list<array<string, mixed>> $data */
        $data = $body["data"] ?? [];
        $this->assertCount(0, $validationErrors);
        $this->assertCount(0, $internalErrors);
        $this->assertNotEmpty($data);

        foreach ($data as $user) {
            $this->assertEquals("admin", $user["username"]);
        }
    }

    // ----------------------------------------------------------------
    // Happy path: GET one
    // ----------------------------------------------------------------

    #[Test]
    public function testGetOneReturnsUser(): void
    {
        // First get the list to capture a valid UUID
        $listResponse = $this->testClient->get(self::API_ENDPOINT);
        $listBody = $this->decodeResponse($listResponse);
        /** @var list<array<string, mixed>> $listData */
        $listData = $listBody["data"] ?? [];
        /** @var string $uuid */
        $uuid = $listData[0]["uuid"];

        $response = $this->testClient->getOne(self::API_ENDPOINT, $uuid);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $body = $this->decodeResponse($response);
        /** @var array<int, mixed> $validationErrors */
        $validationErrors = $body["validationErrors"] ?? [];
        /** @var array<int, mixed> $internalErrors */
        $internalErrors = $body["internalErrors"] ?? [];
        /** @var array<string, mixed> $data */
        $data = $body["data"] ?? [];
        $this->assertCount(0, $validationErrors);
        $this->assertCount(0, $internalErrors);
        $this->assertEquals($uuid, $data["uuid"]);
        $this->assertArrayHasKey('username', $data);
        $this->assertArrayHasKey('authorized', $data);
        $this->assertArrayHasKey('acl_groups', $data);
    }

    // ----------------------------------------------------------------
    // Happy path: POST create
    // ----------------------------------------------------------------

    #[Test]
    public function testPostCreatesUser(): void
    {
        $adminPass = getenv("OE_PASS", true) ?: "pass";
        $username = "phpunit_test_" . bin2hex(random_bytes(4));
        $userData = [
            "username" => $username,
            "password" => "TestPass123!strong",
            "admin_password" => $adminPass,
            "fname" => "PHPUnit",
            "lname" => "TestUser",
            "access_group" => ["Physicians"],
        ];

        $response = $this->testClient->post(self::API_ENDPOINT, $userData);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $body = $this->decodeResponse($response);
        /** @var array<int, mixed> $validationErrors */
        $validationErrors = $body["validationErrors"] ?? [];
        /** @var array<int, mixed> $internalErrors */
        $internalErrors = $body["internalErrors"] ?? [];
        /** @var array<string, mixed> $data */
        $data = $body["data"] ?? [];
        $this->assertCount(0, $validationErrors);
        $this->assertCount(0, $internalErrors);
        $this->assertNotEmpty($data["uuid"]);
        $this->assertEquals($username, $data["username"]);

        self::$createdUsernames[] = $username;

        // Verify user appears in GET list with filter
        $listResponse = $this->testClient->get(self::API_ENDPOINT, ["username" => $username]);
        $listBody = $this->decodeResponse($listResponse);
        /** @var list<array<string, mixed>> $listData */
        $listData = $listBody["data"] ?? [];
        $this->assertCount(1, $listData);
        $this->assertEquals($username, $listData[0]["username"]);
        /** @var list<string> $aclGroups */
        $aclGroups = $listData[0]["acl_groups"] ?? [];
        $this->assertContains("Physicians", $aclGroups);
    }

    #[Test]
    public function testPostWithOptionalFields(): void
    {
        $adminPass = getenv("OE_PASS", true) ?: "pass";
        $username = "phpunit_opts_" . bin2hex(random_bytes(4));
        $userData = [
            "username" => $username,
            "password" => "TestPass123!strong",
            "admin_password" => $adminPass,
            "fname" => "Optional",
            "lname" => "Fields",
            "mname" => "M",
            "email" => "test@example.com",
            "authorized" => 1,
            "npi" => "1234567890",
            "specialty" => "Internal Medicine",
            "access_group" => ["Physicians"],
        ];

        $response = $this->testClient->post(self::API_ENDPOINT, $userData);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $body = $this->decodeResponse($response);
        /** @var array<int, mixed> $internalErrors */
        $internalErrors = $body["internalErrors"] ?? [];
        /** @var array<string, mixed> $data */
        $data = $body["data"] ?? [];
        $this->assertCount(0, $internalErrors);
        $this->assertNotEmpty($data["uuid"]);

        self::$createdUsernames[] = $username;
    }

    // ----------------------------------------------------------------
    // Sad path: GET one
    // ----------------------------------------------------------------

    #[Test]
    public function testGetOneNotFoundReturns404(): void
    {
        // Valid UUID format but non-existent
        $fakeUuid = "00000000-0000-0000-0000-000000000000";
        $response = $this->testClient->getOne(self::API_ENDPOINT, $fakeUuid);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[Test]
    public function testGetOneMalformedUuidReturnsError(): void
    {
        $response = $this->testClient->getOne(self::API_ENDPOINT, "not-a-uuid");

        // Invalid UUID may return 400, 404, or 500 depending on how deep the parsing goes
        $this->assertNotEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertNotEquals(Response::HTTP_CREATED, $response->getStatusCode());
    }

    // ----------------------------------------------------------------
    // Sad path: POST create
    // ----------------------------------------------------------------

    #[Test]
    public function testPostMissingRequiredFieldsReturns400(): void
    {
        // Missing username, password, fname, lname, access_group
        $response = $this->testClient->post(self::API_ENDPOINT, [
            "email" => "incomplete@example.com",
        ]);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $body = $this->decodeResponse($response);
        /** @var array<string, mixed> $validationErrors */
        $validationErrors = $body["validationErrors"] ?? [];
        /** @var list<mixed> $data */
        $data = $body["data"] ?? [];
        $this->assertNotEmpty($validationErrors);
        $this->assertEmpty($data);
    }

    #[Test]
    public function testPostMissingUsernameReturns400(): void
    {
        $adminPass = getenv("OE_PASS", true) ?: "pass";
        $response = $this->testClient->post(self::API_ENDPOINT, [
            "password" => "TestPass123!strong",
            "admin_password" => $adminPass,
            "fname" => "NoUsername",
            "lname" => "User",
            "access_group" => ["Physicians"],
        ]);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $body = $this->decodeResponse($response);
        /** @var array<string, mixed> $validationErrors */
        $validationErrors = $body["validationErrors"] ?? [];
        $this->assertArrayHasKey('username', $validationErrors);
    }

    #[Test]
    public function testPostDuplicateUsernameReturns400(): void
    {
        $adminPass = getenv("OE_PASS", true) ?: "pass";

        // "admin" user always exists in the dev environment
        $response = $this->testClient->post(self::API_ENDPOINT, [
            "username" => "admin",
            "password" => "TestPass123!strong",
            "admin_password" => $adminPass,
            "fname" => "Duplicate",
            "lname" => "Admin",
            "access_group" => ["Physicians"],
        ]);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $body = $this->decodeResponse($response);
        /** @var array<string, mixed> $validationErrors */
        $validationErrors = $body["validationErrors"] ?? [];
        $this->assertNotEmpty($validationErrors);
    }

    #[Test]
    public function testPostInvalidUsernameFormatReturns400(): void
    {
        $adminPass = getenv("OE_PASS", true) ?: "pass";
        $response = $this->testClient->post(self::API_ENDPOINT, [
            "username" => "invalid user!@#",
            "password" => "TestPass123!strong",
            "admin_password" => $adminPass,
            "fname" => "Bad",
            "lname" => "Username",
            "access_group" => ["Physicians"],
        ]);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $body = $this->decodeResponse($response);
        /** @var array<string, mixed> $validationErrors */
        $validationErrors = $body["validationErrors"] ?? [];
        $this->assertNotEmpty($validationErrors);
    }

    #[Test]
    public function testPostEmptyAccessGroupReturns400(): void
    {
        $adminPass = getenv("OE_PASS", true) ?: "pass";
        $response = $this->testClient->post(self::API_ENDPOINT, [
            "username" => "phpunit_empty_acl_" . bin2hex(random_bytes(4)),
            "password" => "TestPass123!strong",
            "admin_password" => $adminPass,
            "fname" => "Empty",
            "lname" => "ACL",
            "access_group" => [],
        ]);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $body = $this->decodeResponse($response);
        /** @var array<string, mixed> $validationErrors */
        $validationErrors = $body["validationErrors"] ?? [];
        $this->assertNotEmpty($validationErrors);
    }

    #[Test]
    public function testPostWrongAdminPasswordReturns400(): void
    {
        $response = $this->testClient->post(self::API_ENDPOINT, [
            "username" => "phpunit_badpass_" . bin2hex(random_bytes(4)),
            "password" => "TestPass123!strong",
            "admin_password" => "wrong_admin_password",
            "fname" => "Bad",
            "lname" => "AdminPass",
            "access_group" => ["Physicians"],
        ]);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $body = $this->decodeResponse($response);
        /** @var array<string, mixed> $validationErrors */
        $validationErrors = $body["validationErrors"] ?? [];
        $this->assertNotEmpty($validationErrors);
        $this->assertArrayHasKey('admin_password', $validationErrors);
    }

    #[Test]
    public function testPostInvalidFacilityIdReturns400(): void
    {
        $adminPass = getenv("OE_PASS", true) ?: "pass";
        $response = $this->testClient->post(self::API_ENDPOINT, [
            "username" => "phpunit_badfac_" . bin2hex(random_bytes(4)),
            "password" => "TestPass123!strong",
            "admin_password" => $adminPass,
            "fname" => "Bad",
            "lname" => "Facility",
            "facility_id" => "999999",
            "access_group" => ["Physicians"],
        ]);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $body = $this->decodeResponse($response);
        /** @var array<string, mixed> $validationErrors */
        $validationErrors = $body["validationErrors"] ?? [];
        $this->assertNotEmpty($validationErrors);
        $this->assertArrayHasKey('facility_id', $validationErrors);
    }

    #[Test]
    public function testPostInvalidBillingFacilityIdReturns400(): void
    {
        $adminPass = getenv("OE_PASS", true) ?: "pass";
        $response = $this->testClient->post(self::API_ENDPOINT, [
            "username" => "phpunit_badbfac_" . bin2hex(random_bytes(4)),
            "password" => "TestPass123!strong",
            "admin_password" => $adminPass,
            "fname" => "Bad",
            "lname" => "BillingFacility",
            "billing_facility_id" => "999999",
            "access_group" => ["Physicians"],
        ]);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $body = $this->decodeResponse($response);
        /** @var array<string, mixed> $validationErrors */
        $validationErrors = $body["validationErrors"] ?? [];
        $this->assertNotEmpty($validationErrors);
        $this->assertArrayHasKey('billing_facility_id', $validationErrors);
    }

    #[Test]
    public function testPostWithValidFacilityIdSucceeds(): void
    {
        /** @var array{id: int|string}|false $facility */
        $facility = QueryUtils::querySingleRow("SELECT id FROM facility ORDER BY id LIMIT 1");
        if ($facility === false) {
            self::markTestSkipped('No facility records available in the test database');
        }
        $facilityId = (string) $facility['id'];

        $adminPass = getenv("OE_PASS", true) ?: "pass";
        $username = "phpunit_fac_" . bin2hex(random_bytes(4));
        $response = $this->testClient->post(self::API_ENDPOINT, [
            "username" => $username,
            "password" => "TestPass123!strong",
            "admin_password" => $adminPass,
            "fname" => "Facility",
            "lname" => "User",
            "facility_id" => $facilityId,
            "access_group" => ["Physicians"],
        ]);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $body = $this->decodeResponse($response);
        /** @var array<string, mixed> $data */
        $data = $body["data"] ?? [];
        $this->assertNotEmpty($data["uuid"]);

        self::$createdUsernames[] = $username;
    }

    #[Test]
    public function testGetOneAndGetListReturnConsistentFields(): void
    {
        // Get the list and pick the first user
        $listResponse = $this->testClient->get(self::API_ENDPOINT);
        $listBody = $this->decodeResponse($listResponse);
        /** @var list<array<string, mixed>> $listData */
        $listData = $listBody["data"] ?? [];
        $this->assertNotEmpty($listData);

        $listUser = $listData[0];
        /** @var string $uuid */
        $uuid = $listUser["uuid"];

        // Get the same user via the detail endpoint (returns unwrapped single object)
        $detailResponse = $this->testClient->getOne(self::API_ENDPOINT, $uuid);
        $detailBody = $this->decodeResponse($detailResponse);
        /** @var array<string, mixed> $detailUser */
        $detailUser = $detailBody["data"] ?? [];
        $this->assertNotEmpty($detailUser);

        // Both endpoints should return the same set of keys
        $listKeys = array_keys($listUser);
        $detailKeys = array_keys($detailUser);
        sort($listKeys);
        sort($detailKeys);
        $this->assertEquals($listKeys, $detailKeys, 'GET list and GET detail should return the same field set');
    }

    // ----------------------------------------------------------------
    // Sad path: Authentication / Authorization
    // ----------------------------------------------------------------

    #[Test]
    public function testUnauthenticatedRequestReturns401(): void
    {
        $this->testClient->removeAuthToken();
        $response = $this->testClient->get(self::API_ENDPOINT);
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    #[Test]
    public function testNonAdminUserGetReturns403(): void
    {
        $adminPass = getenv("OE_PASS", true) ?: "pass";
        $nonAdminPassword = "NonAdmin123!strong";
        $nonAdminUsername = "phpunit_nonadmin_" . bin2hex(random_bytes(4));

        // Create a non-admin user with "Clinicians" ACL group (no admin/users permission)
        $createResponse = $this->testClient->post(self::API_ENDPOINT, [
            "username" => $nonAdminUsername,
            "password" => $nonAdminPassword,
            "admin_password" => $adminPass,
            "fname" => "NonAdmin",
            "lname" => "User",
            "access_group" => ["Clinicians"],
        ]);
        $this->assertEquals(Response::HTTP_CREATED, $createResponse->getStatusCode());
        self::$createdUsernames[] = $nonAdminUsername;

        // Authenticate as the non-admin user
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $nonAdminClient = new ApiTestClient($baseUrl, false);
        $nonAdminClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT, [
            "username" => $nonAdminUsername,
            "password" => $nonAdminPassword,
        ]);

        // GET /api/admin/users should return 403 (authenticated but not authorized)
        $response = $nonAdminClient->get(self::API_ENDPOINT);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    #[Test]
    public function testNonAdminUserPostReturns403(): void
    {
        $adminPass = getenv("OE_PASS", true) ?: "pass";
        $nonAdminPassword = "NonAdmin123!strong";
        $nonAdminUsername = "phpunit_nonadmin2_" . bin2hex(random_bytes(4));

        // Create a non-admin user
        $createResponse = $this->testClient->post(self::API_ENDPOINT, [
            "username" => $nonAdminUsername,
            "password" => $nonAdminPassword,
            "admin_password" => $adminPass,
            "fname" => "NonAdmin",
            "lname" => "User2",
            "access_group" => ["Clinicians"],
        ]);
        $this->assertEquals(Response::HTTP_CREATED, $createResponse->getStatusCode());
        self::$createdUsernames[] = $nonAdminUsername;

        // Authenticate as the non-admin user
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $nonAdminClient = new ApiTestClient($baseUrl, false);
        $nonAdminClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT, [
            "username" => $nonAdminUsername,
            "password" => $nonAdminPassword,
        ]);

        // POST /api/admin/users should return 403 (requires admin/super ACL)
        $response = $nonAdminClient->post(self::API_ENDPOINT, [
            "username" => "should_not_be_created",
            "password" => "TestPass123!strong",
            "admin_password" => $nonAdminPassword,
            "fname" => "Should",
            "lname" => "Fail",
            "access_group" => ["Clinicians"],
        ]);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    // ----------------------------------------------------------------
    // Regression: existing /api/user endpoint still works
    // ----------------------------------------------------------------

    #[Test]
    public function testExistingUserEndpointStillWorks(): void
    {
        $response = $this->testClient->get(self::USER_ENDPOINT);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $body = $this->decodeResponse($response);
        /** @var array<int, mixed> $validationErrors */
        $validationErrors = $body["validationErrors"] ?? [];
        /** @var list<mixed> $data */
        $data = $body["data"] ?? [];
        $this->assertCount(0, $validationErrors);
        $this->assertNotEmpty($data);
    }
}
