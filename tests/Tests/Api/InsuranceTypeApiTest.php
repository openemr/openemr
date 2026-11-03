<?php

/**
 * Insurance Type API Endpoint Test Cases
 *
 * Covers:
 *   - GET /api/insurance_type  (collection — admin.bill ACL)
 *
 * Fills a post-batch audit gap — the single `/api/insurance_type` route
 * had zero api-tier coverage. Backed by `InsuranceCompanyService::getInsuranceTypes`
 * which pulls from the `list_options` list_id='Insurance_Type_Code'.
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

class InsuranceTypeApiTest extends TestCase
{
    private const INSURANCE_TYPE_API_ENDPOINT = "/apis/default/api/insurance_type";

    private ApiTestClient $testClient;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthTokenOrFail(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
    }

    protected function tearDown(): void
    {
        try {
            $this->testClient->cleanupRevokeAuth();
        } finally {
            $this->testClient->cleanupClient();
        }
    }

    #[Test]
    public function testGetAllReturnsInsuranceTypesList(): void
    {
        $response = $this->testClient->get(self::INSURANCE_TYPE_API_ENDPOINT);
        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        // The service returns the `Insurance_Type_Code` list_options
        // directly (no ProcessingResult envelope).
        $this->assertNotEmpty($body, 'stock install must have at least one insurance type seeded');
    }
}
