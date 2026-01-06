<?php

/**
 * Health Endpoint Tests
 *
 * Verifies that health check endpoints return proper JSON responses
 * without requiring authentication (no login redirect).
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Api;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

class HealthEndpointTest extends TestCase
{
    private Client $client;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->client = new Client([
            "verify" => false,
            "base_uri" => $baseUrl,
            "timeout" => 10,
            "http_errors" => false,
            "allow_redirects" => false, // Don't follow redirects - we want to detect them
        ]);
    }

    /**
     * Test that /meta/health/livez returns JSON without authentication
     */
    public function testLivezReturnsJsonWithoutAuth(): void
    {
        $response = $this->client->get('/meta/health/livez');

        $this->assertEquals(200, $response->getStatusCode(), 'livez should return 200 OK');

        $contentType = $response->getHeaderLine('Content-Type');
        $this->assertStringContainsString('application/json', $contentType, 'livez should return JSON content type');

        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body, 'livez response should be valid JSON');
        $this->assertArrayHasKey('status', $body, 'livez response should have status key');
        $this->assertEquals('alive', $body['status'], 'livez status should be "alive"');
    }

    /**
     * Test that /meta/health/readyz returns JSON without authentication
     *
     * This is the critical test for issue #10115 - the readyz endpoint was
     * redirecting to login instead of returning health status.
     */
    public function testReadyzReturnsJsonWithoutAuth(): void
    {
        $response = $this->client->get('/meta/health/readyz');

        // Should NOT be a redirect (3xx status)
        $statusCode = $response->getStatusCode();
        $this->assertNotEquals(302, $statusCode, 'readyz should not redirect (issue #10115)');
        $this->assertNotEquals(301, $statusCode, 'readyz should not redirect');
        $this->assertLessThan(300, $statusCode, 'readyz should return 2xx status, not redirect');
        $this->assertEquals(200, $statusCode, 'readyz should return 200 OK');

        // Should return JSON, not HTML/JavaScript redirect
        $contentType = $response->getHeaderLine('Content-Type');
        $this->assertStringContainsString('application/json', $contentType, 'readyz should return JSON content type');

        $bodyRaw = (string) $response->getBody();
        $this->assertStringNotContainsString('<script>', $bodyRaw, 'readyz should not return JavaScript redirect');
        $this->assertStringNotContainsString('login_screen.php', $bodyRaw, 'readyz should not redirect to login');

        $body = json_decode($bodyRaw, true);
        $this->assertIsArray($body, 'readyz response should be valid JSON');
        $this->assertArrayHasKey('status', $body, 'readyz response should have status key');
        $this->assertContains($body['status'], ['ready', 'setup_required', 'error'], 'readyz status should be valid');
    }

    /**
     * Test that readyz returns proper health check structure when installed
     */
    public function testReadyzReturnsHealthChecks(): void
    {
        $response = $this->client->get('/meta/health/readyz');
        $body = json_decode((string) $response->getBody(), true);

        // If status is 'ready', we should have checks
        if ($body['status'] === 'ready') {
            $this->assertArrayHasKey('checks', $body, 'readyz response should have checks when ready');
            $this->assertIsArray($body['checks'], 'checks should be an array');
        }
    }
}
