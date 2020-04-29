<?php

namespace OpenEMR\Tests\Api;

use PHPUnit\Framework\TestCase;
use OpenEMR\Tests\Api\ApiTestClient;

/**
 * Test cases for the OpenEMR Api Test Client
 * NOTE: currently disabled (by naming convention) until work is completed to support running as part of Travis CI
 * @coversDefaultClass OpenEMR\Tests\Api\ApiTestClient
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Dixon Whitmire <dixonwh@gmail.com>
 * @copyright Copyright (c) 2020 Dixon Whitmire <dixonwh@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */
class ApiTestClientTestDisabled extends TestCase
{
    private $testClient;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL", true) ?: "http://localhost";
        $this->client = new ApiTestClient($baseUrl);
    }

    /**
     * @cover ::getConfig with a null value
     */
    public function testGetConfigWithNull()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->client->getConfig(null);
    }

    /**
     * @cover ::getConfig for HTTP client settings
     */
    public function testGetConfig()
    {
        $this->assertTrue($this->client->getConfig("http_errors"));
        $this->assertEquals(10, $this->client->getConfig("timeout"));
        $this->assertNotNull($this->client->getConfig("base_uri"));

        $actualHeaders = $this->client->getConfig("headers");
        $this->assertEquals("application/json", $actualHeaders["Accept"]);
        $this->assertArrayHasKey("User-Agent", $actualHeaders);
    }

    /**
     * Tests OpenEMR API Auth
     * @cover ::setAuthToken
     * @cover ::removeAuthToken
     */
    public function testOpenEMRApiAuth()
    {
        $this->client->setAuthToken(ApiTestClient::OPENEMR_API_AUTH_ENDPOINT);
        
        $actualHeaders = $this->client->getConfig("headers");
        $this->assertArrayHasKey("Authorization", $actualHeaders);
        
        $authHeaderValue = substr($actualHeaders["Authorization"], 7);
        $this->assertGreaterThan(10, strlen($authHeaderValue));

        $this->client->removeAuthToken();
        $actualHeaders = $this->client->getConfig("headers");
        $this->assertArrayNotHasKey("Authorization", $actualHeaders);
    }

    /**
     * Tests OpenEMR FHIR API Auth
     * @cover ::setAuthToken
     * @cover ::removeAuthToken
     */
    public function testOpenEMRFhirApiAuth()
    {
        $this->client->setAuthToken(ApiTestClient::OPENEMR_FHIR_API_AUTH_ENDPOINT);
        
        $actualHeaders = $this->client->getConfig("headers");
        $this->assertArrayHasKey("Authorization", $actualHeaders);
        
        $authHeaderValue = substr($actualHeaders["Authorization"], 7);
        $this->assertGreaterThan(10, strlen($authHeaderValue));

        $this->client->removeAuthToken();
        $actualHeaders = $this->client->getConfig("headers");
        $this->assertArrayNotHasKey("Authorization", $actualHeaders);
    }

    /**
     * @cover ::removeAuthToken when an auth token is not present
     */
    public function testRemoveAuthTokenNoToken()
    {
        $this->client->removeAuthToken();
        $actualHeaders = $this->client->getConfig("headers");
        $this->assertArrayNotHasKey("Authorization", $actualHeaders);
    }
}
