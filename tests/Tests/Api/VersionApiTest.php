<?php

/**
 * Version API Endpoint Test Cases
 *
 * Covers GET /api/version (Part of #12343).
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

class VersionApiTest extends TestCase
{
    private const VERSION_API_ENDPOINT = "/apis/default/api/version";

    private ApiTestClient $testClient;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
    }

    #[Test]
    public function testGetVersion(): void
    {
        $response = $this->testClient->get(self::VERSION_API_ENDPOINT);
        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);

        foreach (["v_major", "v_minor", "v_patch", "v_realpatch", "v_database", "v_acl"] as $key) {
            $this->assertArrayHasKey($key, $body);
            $this->assertIsInt($body[$key]);
        }

        $this->assertArrayHasKey("v_tag", $body);
        $this->assertIsString($body["v_tag"]);

        $this->assertGreaterThan(0, $body["v_major"]);
    }
}
