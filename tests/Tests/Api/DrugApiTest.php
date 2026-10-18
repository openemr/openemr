<?php

/**
 * Drug API Endpoint Test Cases
 *
 * Covers:
 *   - GET /api/drug         (collection)
 *   - GET /api/drug/{uuid}  (single + 404 shape)
 *
 * First-pass regression gate on the standard-api `drug` surface — the
 * §6.2 route catalog showed 13 test files vs. 96 standard routes, and
 * `/api/drug/*` had no dedicated api-tier coverage. See the
 * oauth2-fhir-api-test-coverage private audit doc §8.2 "Standard-API
 * coverage expansion" entry.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DrugApiTest extends TestCase
{
    private const DRUG_API_ENDPOINT = "/apis/default/api/drug";

    private ApiTestClient $testClient;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
    }

    #[Test]
    public function testGetAll(): void
    {
        $response = $this->testClient->get(self::DRUG_API_ENDPOINT);
        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey("data", $body);
        $this->assertArrayHasKey("validationErrors", $body);
        $this->assertArrayHasKey("internalErrors", $body);
        $this->assertSame([], $body["validationErrors"]);
        $this->assertSame([], $body["internalErrors"]);
        $this->assertIsArray($body["data"]);
    }

    #[Test]
    public function testGetOneWithUnknownUuidReturns404(): void
    {
        // Format-valid UUID that is extremely unlikely to exist as a
        // drug.uuid in demo data. Asserts both the HTTP status
        // (`DrugRestController::getOne` calls `handleProcessingResult(…,
        // 404)` when the service returns zero rows) AND the body shape
        // (empty data array). Catches two distinct regression classes:
        //   - a listener/controller regression that drops the 404
        //     status (ViewRendererListener preserves http_response_code
        //     under this PR — before this fix, the response was 200);
        //   - a routing regression that returns an arbitrary drug for
        //     a bogus UUID (data array still has to be empty).
        $response = $this->testClient->getOne(self::DRUG_API_ENDPOINT, "00000000-0000-4000-8000-000000000000");
        $this->assertSame(404, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey("data", $body);
        $this->assertSame([], $body["data"], "unknown-UUID lookup must not return any drug");
    }
}
