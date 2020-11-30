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
    const EXAMPLE_API_ENDPOINT = "/apis/default/api/facility";
    const EXAMPLE_API_ENDPOINT_INVALID_SITE = "/apis/baddefault/api/facility";

    private $client;

    /**
     * Configures the test client using environment variables and reasonable defaults
     */
    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->client = new ApiTestClient($baseUrl, false);
    }

    /**
     * @cover ::getConfig with a null value
     */
    public function testGetConfigWithNull()
    {
        $this->client->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
        $this->expectException(\InvalidArgumentException::class);
        $this->client->getConfig(null);

        $this->client->cleanupRevokeAuth();
        $this->client->cleanupClient();
    }

    /**
     * @cover ::getConfig for HTTP client settings
     */
    public function testGetConfig()
    {
        $this->client->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
        $this->assertFalse($this->client->getConfig("http_errors"));
        $this->assertEquals(10, $this->client->getConfig("timeout"));
        $this->assertNotNull($this->client->getConfig("base_uri"));

        $actualHeaders = $this->client->getConfig("headers");
        $this->assertEquals("application/json", $actualHeaders["Accept"]);
        $this->assertArrayHasKey("User-Agent", $actualHeaders);

        $this->client->cleanupRevokeAuth();
        $this->client->cleanupClient();
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

        $this->client->cleanupClient();

        try {
            $this->client->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT, array("username" => "bar"));
            $this->assertFalse(true, "expected InvalidArgumentException");
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }

        $this->client->cleanupClient();
    }
    /**
     * Tests OpenEMR OAuth when invalid client id is provided
     * @covers ::setAuthToken with invalid credentials
     */
    public function testApiAuthInvalidClientId()
    {
        $actualValue = $this->client->setAuthToken(
            ApiTestClient::OPENEMR_AUTH_ENDPOINT,
            ["client_id" => "ugk_IdaC2szz-k0vIqhE6DYIjevkYo41neRGGpZvYfsgg"]
        );
        $this->assertEquals(401, $actualValue->getStatusCode());
        $this->assertEquals('invalid_client', json_decode($actualValue->getBody())->error);

        $this->client->cleanupClient();
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

        $this->client->cleanupClient();
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

        $this->client->cleanupRevokeAuth();
        $this->client->cleanupClient();
    }

    /**
     * Tests OpenEMR API Example Endpoint After Getting Auth for the REST and FHIR APIs
     */
    public function testApiAuthExampleUse()
    {
        $actualValue = $this->client->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
        $this->assertEquals(200, $actualValue->getStatusCode());
        $actualResponse = $this->client->get(self::EXAMPLE_API_ENDPOINT);
        $this->assertEquals(200, $actualResponse->getStatusCode());
        $this->client->removeAuthToken();
        $actualHeaders = $this->client->getConfig("headers");
        $this->assertArrayNotHasKey("Authorization", $actualHeaders);

        $this->client->cleanupRevokeAuth();
        $this->client->cleanupClient();
    }

    /**
     * Tests OpenEMR API Example Endpoint After Getting Auth for the REST and FHIR APIs
     *  Then test revoking user
     */
    public function testApiAuthExampleUseThenRevoke()
    {
        $actualValue = $this->client->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
        $this->assertEquals(200, $actualValue->getStatusCode());
        $actualResponse = $this->client->get(self::EXAMPLE_API_ENDPOINT);
        $this->assertEquals(200, $actualResponse->getStatusCode());
        $id_token = json_decode($actualValue->getBody())->id_token;
        $this->assertGreaterThan(10, strlen($id_token));

        $actualResponse = $this->client->cleanupRevokeAuth();
        $this->assertEquals(200, $actualResponse->getStatusCode());
        $this->assertEquals("You have been signed out. Thank you.", $actualResponse->getBody());

        $actualResponse = $this->client->cleanupRevokeAuth();
        $this->assertEquals(200, $actualResponse->getStatusCode());
        $this->assertEquals("You are currently not signed in.", $actualResponse->getBody());

        $actualResponse = $this->client->get(self::EXAMPLE_API_ENDPOINT);
        $this->assertEquals(400, $actualResponse->getStatusCode());

        $this->client->removeAuthToken();
        $actualHeaders = $this->client->getConfig("headers");
        $this->assertArrayNotHasKey("Authorization", $actualHeaders);

        $this->client->cleanupClient();
    }

    /**
     * Tests OpenEMR API Example Endpoint with Invalid Site After Getting Auth for the REST and FHIR APIs
     */
    public function testApiAuthExampleUseBadSite()
    {
        $actualValue = $this->client->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
        $this->assertEquals(200, $actualValue->getStatusCode());
        $actualResponse = $this->client->get(self::EXAMPLE_API_ENDPOINT_INVALID_SITE);
        $this->assertEquals(400, $actualResponse->getStatusCode());
        $this->client->removeAuthToken();
        $actualHeaders = $this->client->getConfig("headers");
        $this->assertArrayNotHasKey("Authorization", $actualHeaders);

        $this->client->cleanupRevokeAuth();
        $this->client->cleanupClient();
    }

    /**
     * Tests OpenEMR API Example Endpoint After Getting Auth With Empty Bearer Token for the REST and FHIR APIs
     */
    public function testApiAuthExampleUseEmptyToken()
    {
        $actualValue = $this->client->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
        $this->assertEquals(200, $actualValue->getStatusCode());
        $actualResponse = $this->client->get(self::EXAMPLE_API_ENDPOINT);
        $this->assertEquals(200, $actualResponse->getStatusCode());
        $this->client->removeAuthToken();
        $actualHeaders = $this->client->getConfig("headers");
        $this->assertArrayNotHasKey("Authorization", $actualHeaders);
        $actualResponse = $this->client->get(self::EXAMPLE_API_ENDPOINT);
        $this->assertEquals(401, $actualResponse->getStatusCode());

        $this->client->cleanupRevokeAuth();
        $this->client->cleanupClient();
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
