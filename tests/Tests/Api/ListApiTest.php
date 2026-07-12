<?php

/**
 * List API Endpoint Test Cases
 *
 * Covers GET /api/list/:list_name (Part of #12343).
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

class ListApiTest extends TestCase
{
    private const LIST_API_ENDPOINT = "/apis/default/api/list";

    private ApiTestClient $testClient;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
    }

    #[Test]
    public function testGetListOptions(): void
    {
        // "yesno" is a core list installed with every OpenEMR instance
        $response = $this->testClient->get(self::LIST_API_ENDPOINT . "/yesno");
        $this->assertEquals(200, $response->getStatusCode());

        /** @var array<int, array<string, mixed>> $body */
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertNotEmpty($body, "The yesno list should contain at least one option");

        foreach ($body as $option) {
            $this->assertArrayHasKey("option_id", $option);
            $this->assertArrayHasKey("title", $option);
            $this->assertEquals("yesno", $option["list_id"]);
        }
    }

    #[Test]
    public function testGetMissingListReturns404(): void
    {
        $response = $this->testClient->get(self::LIST_API_ENDPOINT . "/definitely-not-a-real-list");
        $this->assertEquals(404, $response->getStatusCode());
    }
}
