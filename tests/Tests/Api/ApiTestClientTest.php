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
        $baseUrl = getenv("OPENEMR_BASE_URL", true) ?: "http://localhost";
        $this->client = new ApiTestClient($baseUrl, false);
    }

    /**
     * @return "array of arrays" of URLs used as a PHPUnit "DataProvider" for parametrized testing
     */
    public function baseUrlDataProvider()
    {
        return array(
            array("OpenEMRBaseApiUrl" => ApiTestClient::OPENEMR_API_AUTH_ENDPOINT),
            array("OpenEMRBaseFhirApiUrl" => ApiTestClient::OPENEMR_FHIR_API_AUTH_ENDPOINT)
        );
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
     * Tests OpenEMR REST and FHIR APIs when invalid credentials arguments are provided
     * @covers ::setAuthToken with invalid credential argument
     * @dataProvider baseUrlDataProvider
     */
    public function testApiAuthInvalidArgs($baseUrl)
    {
        try {
            $this->client->setAuthToken($baseUrl, array("foo" => "bar"));
            $this->assertFalse(true, "expected InvalidArgumentException");
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }

        try {
            $this->client->setAuthToken($baseUrl, array("username" => "bar"));
            $this->assertFalse(true, "expected InvalidArgumentException");
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    /**
     * Tests OpenEMR REST and FHIR APIs when invalid credentials are provided
     * @covers ::setAuthToken with invalid credentials
     * @dataProvider baseUrlDataProvider
     */
    public function testApiAuthInvalidCredentials($baseUrl)
    {
        $actualValue = $this->client->setAuthToken(
            $baseUrl,
            array("username" => "bar", "password" => "boo")
        );
        $this->assertEquals(401, $actualValue->getStatusCode());
    }

    /**
     * Tests OpenEMR API Auth for the REST and FHIR APIs
     * @cover ::setAuthToken
     * @cover ::removeAuthToken
     * @dataProvider baseUrlDataProvider
     */
    public function testApiAuth($baseUrl)
    {
        $actualValue = $this->client->setAuthToken($baseUrl);
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
