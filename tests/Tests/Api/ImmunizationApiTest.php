<?php

/**
 * Immunization API Endpoint Test Cases
 *
 * Covers:
 *   - GET /api/immunization         (collection)
 *   - GET /api/immunization/{uuid}  (single + 404 shape)
 *
 * First-pass regression gate on the standard-api `immunization` surface
 * — the §6.2 route catalog showed 13 test files vs. 96 standard routes,
 * and `/api/immunization/*` had no dedicated api-tier coverage. See the
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

class ImmunizationApiTest extends TestCase
{
    private const IMMUNIZATION_API_ENDPOINT = "/apis/default/api/immunization";

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
        $response = $this->testClient->get(self::IMMUNIZATION_API_ENDPOINT);
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
        // See DrugApiTest::testGetOneWithUnknownUuidReturns404 for the
        // rationale. Asserts both the 404 status (preserved by the
        // ViewRendererListener fix in this PR) and the empty-data body.
        $response = $this->testClient->getOne(self::IMMUNIZATION_API_ENDPOINT, "00000000-0000-4000-8000-000000000000");
        $this->assertSame(404, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey("data", $body);
        $this->assertSame([], $body["data"], "unknown-UUID lookup must not return any immunization");
    }
}
