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
class ApiTestClientTest extends TestCase
{
    private $testClient;

    /**
     * Configures the test client using environment variables and reasonable defaults
     */
    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->client = new ApiTestClient($baseUrl, false);
        $this->client->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
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
        $this->assertFalse($this->client->getConfig("http_errors"));
        $this->assertEquals(10, $this->client->getConfig("timeout"));
        $this->assertNotNull($this->client->getConfig("base_uri"));

        $actualHeaders = $this->client->getConfig("headers");
        $this->assertEquals("application/json", $actualHeaders["Accept"]);
        $this->assertArrayHasKey("User-Agent", $actualHeaders);
    }

    /**
     * Tests the automated testing when invalid credentials arguments are provided
     * @covers ::setAuthToken with invalid credential argument
     */
    public function testApiAuthInvalidArgs()
    {
        try {
            $this->client->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT, array("foo" => "bar"));
            $this->assertFalse(true, "expected InvalidArgumentException");
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }

        try {
            $this->client->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT, array("username" => "bar"));
            $this->assertFalse(true, "expected InvalidArgumentException");
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }
    /**
     * Tests OpenEMR OAuth when invalid client id is provided
     * @covers ::setAuthToken with invalid credentials
     */
    public function testApiAuthInvalidClientId()
    {
        $actualValue = $this->client->setAuthToken(
            ApiTestClient::OPENEMR_AUTH_ENDPOINT,
            ["client_id" => "123456789"]
        );
        $this->assertEquals(401, $actualValue->getStatusCode());
        $this->assertEquals('invalid_client', json_decode($actualValue->getBody())->error);
    }

    /**
     * Tests OpenEMR OAuth when invalid user credentials are provided
     * @covers ::setAuthToken with invalid credentials
     */
    public function testApiAuthInvalidUserCredentials()
    {
        $actualValue = $this->client->setAuthToken(
            ApiTestClient::OPENEMR_AUTH_ENDPOINT,
            array("username" => "bar", "password" => "boo")
        );
        $this->assertEquals(400, $actualValue->getStatusCode());
        $this->assertEquals('Failed Authentication', json_decode($actualValue->getBody())->hint);
    }

    /**
     * Tests OpenEMR API Auth for the REST and FHIR APIs
     * @cover ::setAuthToken
     * @cover ::removeAuthToken
     */
    public function testApiAuth()
    {
        $actualValue = $this->client->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
        $this->assertEquals(200, $actualValue->getStatusCode());

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
