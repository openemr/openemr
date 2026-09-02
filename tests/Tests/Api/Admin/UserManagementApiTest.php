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

    /**
     * Builds an authenticated API client for each test.
     */
    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
    }

    /**
     * No per-test cleanup is required; created users are removed in tearDownAfterClass().
     */
    protected function tearDown(): void
    {
    }

    /**
     * Removes group rows and deactivates every user created by this test class.
     */
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

    /**
     * Reads a global's effective value, falling back to the compiled default when the
     * `globals` row is absent.
     *
     * The password checks these tests rely on are each gated by a global, and the gating
     * values differ between a fresh install and a database upgraded from an older release
     * (the upgrade-from-5.0.0 CI config has `secure_password` off). Reading the value lets a
     * test skip where its precondition does not hold, rather than asserting a behaviour the
     * install has deliberately turned off.
     */
    private function globalValue(string $name, string $default): string
    {
        $row = QueryUtils::querySingleRow("SELECT gl_value FROM globals WHERE gl_name = ?", [$name]);
        $value = is_array($row) ? ($row['gl_value'] ?? null) : null;
        if (is_string($value)) {
            return $value;
        }

        return is_int($value) ? (string) $value : $default;
    }

    /**
     * The list endpoint returns users with the admin-only fields (username, authorized, acl_groups, uuid).
     */
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

    /**
     * A username filter narrows the list to matching users only.
     */
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

    /**
     * _limit bounds the number of records returned.
     */
    #[Test]
    public function testGetAllRespectsLimit(): void
    {
        $response = $this->testClient->get(self::API_ENDPOINT, ["_limit" => "1"]);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $body = $this->decodeResponse($response);
        /** @var list<array<string, mixed>> $data */
        $data = $body["data"] ?? [];
        $this->assertCount(1, $data);
    }

    /**
     * _count is honoured as an alias for _limit, as advertised by the pagination links.
     */
    #[Test]
    public function testGetAllAcceptsCountAliasForLimit(): void
    {
        // QueryPagination::getLinks() advertises _count in the first/next links, so a client
        // following those links must get the same paging it asked for via _limit.
        // Without more than one user available, asserting a single result would also pass
        // against an endpoint that ignored _count entirely.
        $unpagedBody = $this->decodeResponse($this->testClient->get(self::API_ENDPOINT));
        /** @var list<array<string, mixed>> $unpagedData */
        $unpagedData = $unpagedBody["data"] ?? [];
        if (count($unpagedData) < 2) {
            $this->markTestSkipped("Requires at least two users to verify _count paging");
        }

        $response = $this->testClient->get(self::API_ENDPOINT, ["_count" => "1"]);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $body = $this->decodeResponse($response);
        /** @var list<array<string, mixed>> $data */
        $data = $body["data"] ?? [];
        $this->assertCount(1, $data);
    }

    /**
     * _offset walks to a distinct record and a next link is offered while more remain.
     */
    #[Test]
    public function testGetAllPagesWithLimitAndOffset(): void
    {
        $unpagedBody = $this->decodeResponse($this->testClient->get(self::API_ENDPOINT));
        /** @var list<array<string, mixed>> $unpagedData */
        $unpagedData = $unpagedBody["data"] ?? [];
        if (count($unpagedData) < 2) {
            $this->markTestSkipped("Requires at least two users to verify offset paging");
        }

        $firstBody = $this->decodeResponse(
            $this->testClient->get(self::API_ENDPOINT, ["_limit" => "1", "_offset" => "0"])
        );
        $secondBody = $this->decodeResponse(
            $this->testClient->get(self::API_ENDPOINT, ["_limit" => "1", "_offset" => "1"])
        );

        /** @var list<array<string, mixed>> $firstPage */
        $firstPage = $firstBody["data"] ?? [];
        /** @var list<array<string, mixed>> $secondPage */
        $secondPage = $secondBody["data"] ?? [];
        $this->assertCount(1, $firstPage);
        $this->assertCount(1, $secondPage);
        $this->assertNotEquals($firstPage[0]["uuid"], $secondPage[0]["uuid"]);

        // more records remain past the first page, so a next link must be offered
        /** @var array<string, mixed> $links */
        $links = $firstBody["links"] ?? [];
        $this->assertArrayHasKey("next", $links);
    }

    // ----------------------------------------------------------------
    // Happy path: GET one
    // ----------------------------------------------------------------

    /**
     * The detail endpoint returns a single user for a known UUID.
     */
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

    /**
     * A valid payload creates the user, returns 201 with a UUID, and the user is then listable with its ACL group.
     */
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

    /**
     * ACL groups are reported for a user whose ARO is stored under a different case.
     *
     * gacl resolves an ARO with a plain equality compare, so under the install default
     * collation an ARO stored as `SMITH` still enforces its groups for the `smith` user row.
     * The enrichment must agree with that: a case-sensitive match reports no groups while
     * the user is in fact granted them, which under-reports privileges in the admin view.
     */
    #[Test]
    public function testGetReportsAclGroupsWhenAroCaseDiffers(): void
    {
        $adminPass = getenv("OE_PASS", true) ?: "pass";
        $username = "phpunit_aclcase_" . bin2hex(random_bytes(4));

        $response = $this->testClient->post(self::API_ENDPOINT, [
            "username" => $username,
            "password" => "TestPass123!strong",
            "admin_password" => $adminPass,
            "fname" => "PHPUnit",
            "lname" => "AclCase",
            "access_group" => ["Physicians"],
        ]);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        self::$createdUsernames[] = $username;

        // Store the ARO under a different case than the users row, which is the state gacl
        // itself tolerates: the unique key on (section_value, value) is case-insensitive, so
        // this is an update of the same row rather than a second ARO.
        QueryUtils::sqlStatementThrowException(
            "UPDATE gacl_aro SET value = ? WHERE section_value = 'users' AND value = ?",
            [strtoupper($username), $username]
        );

        try {
            $listResponse = $this->testClient->get(self::API_ENDPOINT, ["username" => $username]);
            $listBody = $this->decodeResponse($listResponse);
            /** @var list<array<string, mixed>> $listData */
            $listData = $listBody["data"] ?? [];
            $this->assertCount(1, $listData);
            /** @var list<string> $aclGroups */
            $aclGroups = $listData[0]["acl_groups"] ?? [];
            $this->assertContains("Physicians", $aclGroups);
        } finally {
            QueryUtils::sqlStatementThrowException(
                "UPDATE gacl_aro SET value = ? WHERE section_value = 'users' AND value = ?",
                [$username, strtoupper($username)]
            );
        }
    }

    /**
     * Optional profile fields supplied on create are persisted.
     */
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

    /**
     * A well-formed UUID that matches no user returns 404.
     */
    #[Test]
    public function testGetOneNotFoundReturns404(): void
    {
        // Valid UUID format but non-existent
        $fakeUuid = "00000000-0000-0000-0000-000000000000";
        $response = $this->testClient->getOne(self::API_ENDPOINT, $fakeUuid);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    /**
     * An unknown ACL group title is rejected with 400 before any write, leaving no user behind.
     */
    #[Test]
    public function testPostUnknownAccessGroupReturns400(): void
    {
        $adminPass = getenv("OE_PASS", true) ?: "pass";
        $username = "phpunit_badacl_" . bin2hex(random_bytes(4));
        // Registered up front: if the pre-transaction guard ever regresses and the user is
        // persisted anyway, teardown still knows to clean it up.
        self::$createdUsernames[] = $username;
        $response = $this->testClient->post(self::API_ENDPOINT, [
            "username" => $username,
            "password" => "TestPass123!strong",
            "admin_password" => $adminPass,
            "fname" => "Bad",
            "lname" => "AclGroup",
            "access_group" => ["NoSuchAclGroup"],
        ]);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $body = $this->decodeResponse($response);
        /** @var array<string, mixed> $validationErrors */
        $validationErrors = $body["validationErrors"] ?? [];
        $this->assertArrayHasKey('access_group', $validationErrors);

        // The check runs before any write, so the user must not exist afterwards. Assert the
        // lookup itself succeeded first, otherwise a failed request would also yield an
        // empty list and the absence assertion would prove nothing.
        $listResponse = $this->testClient->get(self::API_ENDPOINT, ["username" => $username]);
        $this->assertEquals(Response::HTTP_OK, $listResponse->getStatusCode());
        $listBody = $this->decodeResponse($listResponse);
        /** @var array<int, mixed> $listValidationErrors */
        $listValidationErrors = $listBody["validationErrors"] ?? [];
        /** @var array<int, mixed> $listInternalErrors */
        $listInternalErrors = $listBody["internalErrors"] ?? [];
        $this->assertCount(0, $listValidationErrors);
        $this->assertCount(0, $listInternalErrors);
        /** @var list<array<string, mixed>> $listData */
        $listData = $listBody["data"] ?? [];
        $this->assertCount(0, $listData);
    }

    /**
     * A malformed UUID returns 400 with a uuid validation error rather than a server error.
     */
    #[Test]
    public function testGetOneMalformedUuidReturnsError(): void
    {
        $response = $this->testClient->getOne(self::API_ENDPOINT, "not-a-uuid");

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $body = $this->decodeResponse($response);
        /** @var array<string, mixed> $validationErrors */
        $validationErrors = $body["validationErrors"] ?? [];
        $this->assertArrayHasKey('uuid', $validationErrors);
    }

    // ----------------------------------------------------------------
    // Sad path: POST create
    // ----------------------------------------------------------------

    /**
     * A payload missing the required fields is rejected.
     */
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

    /**
     * A payload without a username is rejected with a username validation error.
     */
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

    /**
     * Creating a user with an existing username is rejected.
     */
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

    /**
     * A username that differs from an existing one only by case is rejected.
     *
     * The ARO namespace a create writes into is case-insensitive, so admitting `SMITH`
     * alongside an existing `smith` would let AclExtended::setUserAro() resolve the existing
     * user's ARO and replace its group memberships -- an update of another account's ACL from
     * a create-only endpoint. The legacy user admin UI refuses the same create.
     */
    #[Test]
    public function testPostCaseVariantUsernameReturns400(): void
    {
        $adminPass = getenv("OE_PASS", true) ?: "pass";
        $username = "phpunit_case_" . bin2hex(random_bytes(4));

        $created = $this->testClient->post(self::API_ENDPOINT, [
            "username" => $username,
            "password" => "TestPass123!strong",
            "admin_password" => $adminPass,
            "fname" => "Case",
            "lname" => "Original",
            "access_group" => ["Physicians"],
        ]);
        $this->assertEquals(Response::HTTP_CREATED, $created->getStatusCode());
        self::$createdUsernames[] = $username;

        $response = $this->testClient->post(self::API_ENDPOINT, [
            "username" => strtoupper($username),
            "password" => "TestPass123!strong",
            "admin_password" => $adminPass,
            "fname" => "Case",
            "lname" => "Variant",
            "access_group" => ["Administrators"],
        ]);
        self::$createdUsernames[] = strtoupper($username);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $body = $this->decodeResponse($response);
        /** @var array<string, mixed> $validationErrors */
        $validationErrors = $body["validationErrors"] ?? [];
        $this->assertArrayHasKey('username', $validationErrors);

        // The original user's ACL memberships are untouched.
        $listBody = $this->decodeResponse($this->testClient->get(self::API_ENDPOINT, ["username" => $username]));
        /** @var list<array<string, mixed>> $listData */
        $listData = $listBody["data"] ?? [];
        $this->assertCount(1, $listData);
        /** @var list<string> $aclGroups */
        $aclGroups = $listData[0]["acl_groups"] ?? [];
        $this->assertSame(["Physicians"], $aclGroups);
    }

    /**
     * A username longer than the gacl_aro.value column is rejected.
     *
     * users.username is varchar(255) but the ARO the create registers is varchar(150), and
     * sql_mode = '' truncates rather than errors. Accepting a longer name would create a user
     * whose ARO lookup never matches, silently dropping every requested ACL group.
     */
    #[Test]
    public function testPostOverlongUsernameReturns400(): void
    {
        $adminPass = getenv("OE_PASS", true) ?: "pass";
        $username = "phpunit_" . str_repeat("a", 143);
        $response = $this->testClient->post(self::API_ENDPOINT, [
            "username" => $username,
            "password" => "TestPass123!strong",
            "admin_password" => $adminPass,
            "fname" => "Overlong",
            "lname" => "Username",
            "access_group" => ["Physicians"],
        ]);
        // Registered before the assertion: if the guard regresses the user is actually created,
        // and a failing test must not strand it in the shared database.
        self::$createdUsernames[] = $username;

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $body = $this->decodeResponse($response);
        /** @var array<string, mixed> $validationErrors */
        $validationErrors = $body["validationErrors"] ?? [];
        $this->assertArrayHasKey('username', $validationErrors);
    }

    /**
     * Name parts that are individually valid but too long combined are rejected.
     *
     * setUserAro() registers "fname [mname] lname" as gacl_aro.name, and add_object() refuses a
     * name of 255 or more, so without this bound the create would return 201 for a user with no
     * ARO and therefore none of its requested ACL groups.
     */
    #[Test]
    public function testPostOverlongCombinedNameReturns400(): void
    {
        $adminPass = getenv("OE_PASS", true) ?: "pass";
        $username = "phpunit_name_" . bin2hex(random_bytes(4));
        $response = $this->testClient->post(self::API_ENDPOINT, [
            "username" => $username,
            "password" => "TestPass123!strong",
            "admin_password" => $adminPass,
            "fname" => str_repeat("a", 130),
            "lname" => str_repeat("b", 130),
            "access_group" => ["Physicians"],
        ]);
        self::$createdUsernames[] = $username;

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $body = $this->decodeResponse($response);
        /** @var array<string, mixed> $validationErrors */
        $validationErrors = $body["validationErrors"] ?? [];
        $this->assertArrayHasKey('lname', $validationErrors);
    }

    /**
     * A name at the combined bound is still accepted and its ACL group is registered.
     */
    #[Test]
    public function testPostNameAtCombinedBoundSucceeds(): void
    {
        $adminPass = getenv("OE_PASS", true) ?: "pass";
        $username = "phpunit_name2_" . bin2hex(random_bytes(4));
        $response = $this->testClient->post(self::API_ENDPOINT, [
            "username" => $username,
            "password" => "TestPass123!strong",
            "admin_password" => $adminPass,
            "fname" => str_repeat("a", 126),
            "lname" => str_repeat("b", 127),
            "access_group" => ["Physicians"],
        ]);
        self::$createdUsernames[] = $username;

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        // The ARO was actually registered, so the requested group comes back.
        $listBody = $this->decodeResponse($this->testClient->get(self::API_ENDPOINT, ["username" => $username]));
        /** @var list<array<string, mixed>> $listData */
        $listData = $listBody["data"] ?? [];
        $this->assertCount(1, $listData);
        /** @var list<string> $aclGroups */
        $aclGroups = $listData[0]["acl_groups"] ?? [];
        $this->assertContains("Physicians", $aclGroups);
    }

    /**
     * Omitting taxonomy leaves the column default in place rather than blanking it.
     */
    #[Test]
    public function testPostWithoutTaxonomyKeepsColumnDefault(): void
    {
        $adminPass = getenv("OE_PASS", true) ?: "pass";
        $username = "phpunit_tax_" . bin2hex(random_bytes(4));

        $response = $this->testClient->post(self::API_ENDPOINT, [
            "username" => $username,
            "password" => "TestPass123!strong",
            "admin_password" => $adminPass,
            "fname" => "No",
            "lname" => "Taxonomy",
            "access_group" => ["Physicians"],
        ]);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        self::$createdUsernames[] = $username;

        $row = QueryUtils::querySingleRow("SELECT taxonomy FROM users WHERE username = ?", [$username]);
        $this->assertIsArray($row);
        $this->assertSame('207Q00000X', $row['taxonomy']);
    }

    /**
     * A supplied taxonomy is persisted rather than being replaced by the column default.
     */
    #[Test]
    public function testPostWithTaxonomyPersistsSuppliedValue(): void
    {
        $adminPass = getenv("OE_PASS", true) ?: "pass";
        $username = "phpunit_tax2_" . bin2hex(random_bytes(4));

        $response = $this->testClient->post(self::API_ENDPOINT, [
            "username" => $username,
            "password" => "TestPass123!strong",
            "admin_password" => $adminPass,
            "fname" => "With",
            "lname" => "Taxonomy",
            "taxonomy" => "208D00000X",
            "access_group" => ["Physicians"],
        ]);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        self::$createdUsernames[] = $username;

        $row = QueryUtils::querySingleRow("SELECT taxonomy FROM users WHERE username = ?", [$username]);
        $this->assertIsArray($row);
        $this->assertSame('208D00000X', $row['taxonomy']);
    }

    /**
     * A username containing disallowed characters is rejected.
     */
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

    /**
     * An empty access_group array is rejected.
     */
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

    /**
     * Creation fails when the acting admin's own password is wrong.
     */
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

    /**
     * A new-user password that exceeds the maximum length is reported against `password`.
     *
     * AuthUtils validates the new user's password and the acting admin's credential in the
     * same call and surfaces both through one error string, so an unmapped message defaults
     * to `admin_password` and tells the caller their admin credential is wrong.
     */
    #[Test]
    public function testPostTooLongPasswordReportsPasswordField(): void
    {
        if ($this->globalValue('gbl_maximum_password_length', '72') === '0') {
            $this->markTestSkipped('gbl_maximum_password_length is "No Maximum" on this install.');
        }

        $adminPass = getenv("OE_PASS", true) ?: "pass";
        $username = "phpunit_longpw_" . bin2hex(random_bytes(4));
        $response = $this->testClient->post(self::API_ENDPOINT, [
            "username" => $username,
            "password" => str_repeat("aB3!", 25),
            "admin_password" => $adminPass,
            "fname" => "Long",
            "lname" => "Password",
            "access_group" => ["Physicians"],
        ]);
        self::$createdUsernames[] = $username;

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $body = $this->decodeResponse($response);
        /** @var array<string, mixed> $validationErrors */
        $validationErrors = $body["validationErrors"] ?? [];
        $this->assertArrayHasKey('password', $validationErrors);
        $this->assertArrayNotHasKey('admin_password', $validationErrors);
    }

    /**
     * A new-user password that fails the strength check is reported against `password`.
     */
    #[Test]
    public function testPostWeakPasswordReportsPasswordField(): void
    {
        if (in_array($this->globalValue('secure_password', '1'), ['', '0'], true)) {
            $this->markTestSkipped('secure_password is disabled on this install.');
        }

        $adminPass = getenv("OE_PASS", true) ?: "pass";
        $username = "phpunit_weakpw_" . bin2hex(random_bytes(4));
        $response = $this->testClient->post(self::API_ENDPOINT, [
            "username" => $username,
            "password" => "aaaaaaaaaaaa",
            "admin_password" => $adminPass,
            "fname" => "Weak",
            "lname" => "Password",
            "access_group" => ["Physicians"],
        ]);
        self::$createdUsernames[] = $username;

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $body = $this->decodeResponse($response);
        /** @var array<string, mixed> $validationErrors */
        $validationErrors = $body["validationErrors"] ?? [];
        $this->assertArrayHasKey('password', $validationErrors);
        $this->assertArrayNotHasKey('admin_password', $validationErrors);
    }

    /**
     * A facility_id that matches no facility is rejected.
     */
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

    /**
     * A billing_facility_id that matches no facility is rejected.
     */
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

    /**
     * A valid facility_id is accepted and the user is created.
     */
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

    /**
     * The detail and list endpoints return the same field shape for the same user.
     */
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

    /**
     * Requests without a bearer token are rejected with 401.
     */
    #[Test]
    public function testUnauthenticatedRequestReturns401(): void
    {
        $this->testClient->removeAuthToken();
        $response = $this->testClient->get(self::API_ENDPOINT);
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    /**
     * A user without the admin/users ACL cannot read the admin user list.
     */
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

    /**
     * A user without the admin/super ACL cannot create users.
     */
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

    /**
     * The pre-existing /api/user endpoint is unaffected by the admin routes.
     */
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
