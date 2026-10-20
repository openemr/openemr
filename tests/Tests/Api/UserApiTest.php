<?php

/**
 * User API Endpoint Test Cases
 *
 * Covers:
 *   - GET /api/user         (collection — admin.users ACL)
 *   - GET /api/user/{uuid}  (single + 404 shape)
 *
 * First-pass regression gate on the standard-api `user` surface. The
 * route authorizes on `user/user.read` which `ApiTestClient::ALL_SCOPES`
 * previously did not list — this PR adds that scope to the test-client
 * default so the api tier is reachable.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class UserApiTest extends TestCase
{
    private const USER_API_ENDPOINT = "/apis/default/api/user";

    private ApiTestClient $testClient;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthTokenOrFail(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
    }

    protected function tearDown(): void
    {
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    /**
     * UserService::search() excludes `username` from the response by
     * default (it's gated behind `toggleSensitiveFields` which the REST
     * controller does not call), so the public API never surfaces
     * usernames. Resolve admin's uuid directly from the DB so test
     * assertions match on uuid instead of username.
     */
    private function fetchAdminUuid(): string
    {
        $uuid = QueryUtils::fetchSingleValue(
            "SELECT uuid FROM users WHERE username = ?",
            'uuid',
            ['admin']
        );
        $this->assertIsString($uuid, 'admin user must exist on a stock install');
        return UuidRegistry::uuidToString($uuid);
    }

    #[Test]
    public function testGetAllReturnsUserList(): void
    {
        $adminUuid = $this->fetchAdminUuid();

        $response = $this->testClient->get(self::USER_API_ENDPOINT);
        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('data', $body);
        $this->assertArrayHasKey('validationErrors', $body);
        $this->assertArrayHasKey('internalErrors', $body);
        $this->assertSame([], $body['validationErrors']);
        $this->assertSame([], $body['internalErrors']);
        $this->assertIsArray($body['data']);

        // Admin is always present. Locate it by uuid (username is
        // deliberately excluded from the response by UserService's
        // sensitive-field toggle).
        $admin = null;
        foreach ($body['data'] as $row) {
            if (is_array($row) && ($row['uuid'] ?? null) === $adminUuid) {
                $admin = $row;
                break;
            }
        }
        $this->assertIsArray($admin, 'admin user must surface in the list');
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $admin['uuid']
        );
    }

    #[Test]
    public function testGetOneReturnsAdminUser(): void
    {
        $adminUuid = $this->fetchAdminUuid();

        $response = $this->testClient->getOne(self::USER_API_ENDPOINT, $adminUuid);
        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        // UserRestController::getOne calls handleProcessingResult without
        // the multi-result flag, so `data` is the single-row object, not
        // a one-element list (unlike getAll).
        $data = $body['data'];
        $this->assertIsArray($data);
        $this->assertEquals($adminUuid, $data['uuid'] ?? null);
    }

    #[Test]
    public function testGetOneWithUnknownUuidReturns404(): void
    {
        $response = $this->testClient->getOne(
            self::USER_API_ENDPOINT,
            '00000000-0000-4000-8000-000000000000'
        );
        $this->assertSame(404, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('data', $body);
        $this->assertSame([], $body['data']);
    }
}
