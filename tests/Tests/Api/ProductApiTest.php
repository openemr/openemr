<?php

/**
 * Product Registration API Endpoint Test Cases
 *
 * Covers GET /api/product (Part of #12343).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Nick To <tonick2310@gmail.com>
 * @copyright Copyright (c) 2026 Nick To <tonick2310@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ProductApiTest extends TestCase
{
    private const PRODUCT_API_ENDPOINT = "/apis/default/api/product";

    private ApiTestClient $testClient;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
    }

    #[Test]
    public function testGetProductRegistrationStatus(): void
    {
        $response = $this->testClient->get(self::PRODUCT_API_ENDPOINT);
        $this->assertEquals(200, $response->getStatusCode());

        /** @var array<string, mixed> $body */
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey("status", $body);
        $this->assertContains(
            $body["status"],
            ["REGISTERED", "UNREGISTERED", "OPT_OUT"],
            "Product registration status should be a known value"
        );
    }
}
