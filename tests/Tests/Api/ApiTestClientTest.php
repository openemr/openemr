<?php

namespace OpenEMR\Tests\Api;

use OpenEMR\Tests\Api\ApiTestClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Test cases for the OpenEMR Api Test Client
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Dixon Whitmire <dixonwh@gmail.com>
 * @copyright Copyright (c) 2020 Dixon Whitmire <dixonwh@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class ApiTestClientTest extends TestCase
{
    const EXAMPLE_API_ENDPOINT = "/apis/default/api/facility";
    const EXAMPLE_API_ENDPOINT_INVALID_SITE = "/apis/baddefault/api/facility";
    const EXAMPLE_API_ENDPOINT_SCOPE = "user/facility.read";
    const API_ROUTE_SCOPE = "api:oemr";

    /**
     * @var ApiTestClient
     */
    private $client;

    /**
     * Configures the test client using environment variables and reasonable defaults
     */
    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->client = new ApiTestClient($baseUrl, false);
    }

    public function testGetConfigWithNull(): void
    {
        $this->client->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
        $this->expectException(\InvalidArgumentException::class);
        $this->client->getConfig(null);

        $this->client->cleanupRevokeAuth();
        $this->client->cleanupClient();
    }

    public function testGetConfig(): void
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
     */
    public function testApiAuthInvalidArgs(): void
    {
        try {
            $this->client->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT, ["foo" => "bar"]);
            $this->assertFalse(true, "expected InvalidArgumentException");
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }

        $this->client->cleanupClient();

        try {
            $this->client->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT, ["username" => "bar"]);
            $this->assertFalse(true, "expected InvalidArgumentException");
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }

        $this->client->cleanupClient();
    }
    /**
     * Tests OpenEMR OAuth when invalid client id is provided
     */
    public function testApiAuthInvalidClientId(): void
    {
        $actualValue = $this->client->setAuthToken(
            ApiTestClient::OPENEMR_AUTH_ENDPOINT,
            ["client_id" => ApiTestClient::BOGUS_CLIENTID]
        );
        $this->assertEquals(401, $actualValue->getStatusCode());
        $this->assertEquals('invalid_client', json_decode($actualValue->getBody())->error);

        $this->client->cleanupClient();
    }

    /**
     * Tests OpenEMR OAuth when invalid user credentials are provided
     */
    public function testApiAuthInvalidUserCredentials(): void
    {
        $actualValue = $this->client->setAuthToken(
            ApiTestClient::OPENEMR_AUTH_ENDPOINT,
            ["username" => "bar", "password" => "boo"]
        );
        $this->assertEquals(400, $actualValue->getStatusCode());
        $this->assertEquals('Failed Authentication', json_decode($actualValue->getBody())->hint);

        $this->client->cleanupClient();
    }

    /**
     * Tests OpenEMR API Auth for the REST and FHIR APIs
     */
    public function testApiAuth(): void
    {
        $actualValue = $this->client->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
        $this->assertEquals(200, $actualValue->getStatusCode());
        $this->assertGreaterThan(10, strlen($this->client->getIdToken()));
        $this->assertGreaterThan(10, strlen($this->client->getAccessToken()));
        $this->assertGreaterThan(10, strlen($this->client->getRefreshToken()));

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
     * Tests OpenEMR API Auth for the REST and FHIR APIs (test refresh request after the auth)
     */
    public function testApiAuthThenRefresh(): void
    {
        $actualValue = $this->client->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
        $this->assertEquals(200, $actualValue->getStatusCode());
        $this->assertGreaterThan(10, strlen($this->client->getIdToken()));
        $this->assertGreaterThan(10, strlen($this->client->getAccessToken()));
        $this->assertGreaterThan(10, strlen($this->client->getRefreshToken()));

        $actualHeaders = $this->client->getConfig("headers");
        $this->assertArrayHasKey("Authorization", $actualHeaders);

        $authHeaderValue = substr($actualHeaders["Authorization"], 7);
        $this->assertGreaterThan(10, strlen($authHeaderValue));

        $this->client->removeAuthToken();
        $actualHeaders = $this->client->getConfig("headers");
        $this->assertArrayNotHasKey("Authorization", $actualHeaders);

        $refreshBody = [
            "grant_type" => "refresh_token",
            "client_id" => $this->client->getClientId(),
            "refresh_token" => $this->client->getRefreshToken()
        ];
        $this->client->setHeaders(
            [
            "Accept" => "application/json",
            "Content-Type" => "application/x-www-form-urlencoded"
            ]
        );
        $authResponse = $this->client->post(ApiTestClient::OAUTH_TOKEN_ENDPOINT, $refreshBody, false);
        // set headers back to default
        $this->client->setHeaders(
            [
            "Accept" => "application/json",
            "Content-Type" => "application/json"
            ]
        );
        $this->assertEquals(200, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertGreaterThan(10, strlen($responseBody->id_token));
        $this->assertGreaterThan(10, strlen($responseBody->access_token));
        $this->assertGreaterThan(10, strlen($responseBody->refresh_token));

        $this->client->cleanupRevokeAuth();
        $this->client->cleanupClient();
    }

    /**
     * Tests OpenEMR API Auth for the REST and FHIR APIs (test refresh request after the auth with bad refresh token)
     */
    public function testApiAuthThenBadRefresh(): void
    {
        $actualValue = $this->client->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
        $this->assertEquals(200, $actualValue->getStatusCode());
        $this->assertGreaterThan(10, strlen($this->client->getIdToken()));
        $this->assertGreaterThan(10, strlen($this->client->getAccessToken()));
        $this->assertGreaterThan(10, strlen($this->client->getRefreshToken()));

        $actualHeaders = $this->client->getConfig("headers");
        $this->assertArrayHasKey("Authorization", $actualHeaders);

        $authHeaderValue = substr($actualHeaders["Authorization"], 7);
        $this->assertGreaterThan(10, strlen($authHeaderValue));

        $this->client->removeAuthToken();
        $actualHeaders = $this->client->getConfig("headers");
        $this->assertArrayNotHasKey("Authorization", $actualHeaders);

        $refreshBody = [
            "grant_type" => "refresh_token",
            "client_id" => $this->client->getClientId(),
            "refresh_token" => ApiTestClient::BOGUS_REFRESH_TOKEN
        ];
        $this->client->setHeaders(
            [
                "Accept" => "application/json",
                "Content-Type" => "application/x-www-form-urlencoded"
            ]
        );
        $authResponse = $this->client->post(ApiTestClient::OAUTH_TOKEN_ENDPOINT, $refreshBody, false);
        // set headers back to default
        $this->client->setHeaders(
            [
                "Accept" => "application/json",
                "Content-Type" => "application/json"
            ]
        );
        $this->assertEquals(401, $authResponse->getStatusCode());

        $this->client->cleanupRevokeAuth();
        $this->client->cleanupClient();
    }

    /**
     * Tests OpenEMR API Example Endpoint After Getting Auth for the REST and FHIR APIs
     */
    public function testApiAuthExampleUse(): void
    {
        $actualValue = $this->client->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
        $this->assertEquals(200, $actualValue->getStatusCode());
        $this->assertGreaterThan(10, strlen($this->client->getIdToken()));
        $this->assertGreaterThan(10, strlen($this->client->getAccessToken()));
        $this->assertGreaterThan(10, strlen($this->client->getRefreshToken()));

        $actualResponse = $this->client->get(self::EXAMPLE_API_ENDPOINT);
        $this->assertEquals(200, $actualResponse->getStatusCode());
        $this->client->removeAuthToken();
        $actualHeaders = $this->client->getConfig("headers");
        $this->assertArrayNotHasKey("Authorization", $actualHeaders);

        $this->client->cleanupRevokeAuth();
        $this->client->cleanupClient();
    }

    /**
     * Tests OpenEMR API Example Endpoint After Getting Auth for the REST and FHIR APIs (also does a
     *  token refresh and use with new token)
     */
    public function testApiAuthExampleUseThenRefreshThenUse(): void
    {
        $actualValue = $this->client->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
        $this->assertEquals(200, $actualValue->getStatusCode());
        $this->assertGreaterThan(10, strlen($this->client->getIdToken()));
        $this->assertGreaterThan(10, strlen($this->client->getAccessToken()));
        $this->assertGreaterThan(10, strlen($this->client->getRefreshToken()));

        $actualResponse = $this->client->get(self::EXAMPLE_API_ENDPOINT);
        $this->assertEquals(200, $actualResponse->getStatusCode());
        $this->client->removeAuthToken();
        $actualHeaders = $this->client->getConfig("headers");
        $this->assertArrayNotHasKey("Authorization", $actualHeaders);

        $refreshBody = [
            "grant_type" => "refresh_token",
            "client_id" => $this->client->getClientId(),
            "refresh_token" => $this->client->getRefreshToken()
        ];
        $this->client->setHeaders(
            [
                "Accept" => "application/json",
                "Content-Type" => "application/x-www-form-urlencoded"
            ]
        );
        $authResponse = $this->client->post(ApiTestClient::OAUTH_TOKEN_ENDPOINT, $refreshBody, false);
        // set headers back to default
        $this->client->setHeaders(
            [
                "Accept" => "application/json",
                "Content-Type" => "application/json"
            ]
        );
        $this->assertEquals(200, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertGreaterThan(10, strlen($responseBody->id_token));
        $this->assertGreaterThan(10, strlen($responseBody->access_token));
        $this->assertGreaterThan(10, strlen($responseBody->refresh_token));
        $this->client->setBearer($responseBody->access_token);

        $actualResponse = $this->client->get(self::EXAMPLE_API_ENDPOINT);
        $this->assertEquals(200, $actualResponse->getStatusCode());
        $this->client->removeAuthToken();
        $actualHeaders = $this->client->getConfig("headers");
        $this->assertArrayNotHasKey("Authorization", $actualHeaders);

        $this->client->cleanupRevokeAuth();
        $this->client->cleanupClient();
    }

    /**
     * Tests OpenEMR API Example Endpoint After Getting Auth for the REST and FHIR APIs (also does a
     *  token refresh and use with new token) with missing route scope
     */
    public function testApiAuthExampleUseThenRefreshThenUseWithMissingRouteScope(): void
    {
        $actualValue = $this->client->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
        $this->assertEquals(200, $actualValue->getStatusCode());
        $this->assertGreaterThan(10, strlen($this->client->getIdToken()));
        $this->assertGreaterThan(10, strlen($this->client->getAccessToken()));
        $this->assertGreaterThan(10, strlen($this->client->getRefreshToken()));

        $actualResponse = $this->client->get(self::EXAMPLE_API_ENDPOINT);
        $this->assertEquals(200, $actualResponse->getStatusCode());
        $this->client->removeAuthToken();
        $actualHeaders = $this->client->getConfig("headers");
        $this->assertArrayNotHasKey("Authorization", $actualHeaders);

        // remove the route scope
        $scopeCustom = str_replace(
            self::API_ROUTE_SCOPE,
            '',
            implode(' ', ApiTestClient::ALL_SCOPES)
        );

        $refreshBody = [
            "grant_type" => "refresh_token",
            "client_id" => $this->client->getClientId(),
            "scope" => $scopeCustom,
            "refresh_token" => $this->client->getRefreshToken()
        ];
        $this->client->setHeaders(
            [
                "Accept" => "application/json",
                "Content-Type" => "application/x-www-form-urlencoded"
            ]
        );
        $authResponse = $this->client->post(ApiTestClient::OAUTH_TOKEN_ENDPOINT, $refreshBody, false);
        // set headers back to default
        $this->client->setHeaders(
            [
                "Accept" => "application/json",
                "Content-Type" => "application/json"
            ]
        );
        $this->assertEquals(200, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertGreaterThan(10, strlen($responseBody->id_token));
        $this->assertGreaterThan(10, strlen($responseBody->access_token));
        $this->assertGreaterThan(10, strlen($responseBody->refresh_token));
        $this->client->setBearer($responseBody->access_token);

        $actualResponse = $this->client->get(self::EXAMPLE_API_ENDPOINT);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $actualResponse->getStatusCode());
        $this->client->removeAuthToken();
        $actualHeaders = $this->client->getConfig("headers");
        $this->assertArrayNotHasKey("Authorization", $actualHeaders);

        $this->client->cleanupRevokeAuth();
        $this->client->cleanupClient();
    }

    /**
     * Tests OpenEMR API Example Endpoint After Getting Auth for the REST and FHIR APIs (also does a
     *  token refresh and use with new token) with missing endpoint scope
     */
    public function testApiAuthExampleUseThenRefreshThenUseWithMissingEndpointScope(): void
    {
        $actualValue = $this->client->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
        $this->assertEquals(200, $actualValue->getStatusCode());
        $this->assertGreaterThan(10, strlen($this->client->getIdToken()));
        $this->assertGreaterThan(10, strlen($this->client->getAccessToken()));
        $this->assertGreaterThan(10, strlen($this->client->getRefreshToken()));

        $actualResponse = $this->client->get(self::EXAMPLE_API_ENDPOINT);
        $this->assertEquals(200, $actualResponse->getStatusCode());
        $this->client->removeAuthToken();
        $actualHeaders = $this->client->getConfig("headers");
        $this->assertArrayNotHasKey("Authorization", $actualHeaders);

        // remove the endpoint scope
        $scopeCustom = str_replace(
            self::EXAMPLE_API_ENDPOINT_SCOPE,
            '',
            implode(' ', ApiTestClient::ALL_SCOPES)
        );

        $refreshBody = [
            "grant_type" => "refresh_token",
            "client_id" => $this->client->getClientId(),
            "scope" => $scopeCustom,
            "refresh_token" => $this->client->getRefreshToken()
        ];
        $this->client->setHeaders(
            [
                "Accept" => "application/json",
                "Content-Type" => "application/x-www-form-urlencoded"
            ]
        );
        $authResponse = $this->client->post(ApiTestClient::OAUTH_TOKEN_ENDPOINT, $refreshBody, false);
        // set headers back to default
        $this->client->setHeaders(
            [
                "Accept" => "application/json",
                "Content-Type" => "application/json"
            ]
        );
        $this->assertEquals(200, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertGreaterThan(10, strlen($responseBody->id_token));
        $this->assertGreaterThan(10, strlen($responseBody->access_token));
        $this->assertGreaterThan(10, strlen($responseBody->refresh_token));
        $this->client->setBearer($responseBody->access_token);

        $actualResponse = $this->client->get(self::EXAMPLE_API_ENDPOINT);
        $this->assertEquals(401, $actualResponse->getStatusCode());
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
    public function testApiAuthExampleUseThenRevoke(): void
    {
        $actualValue = $this->client->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
        $this->assertEquals(Response::HTTP_OK, $actualValue->getStatusCode());
        $this->assertGreaterThan(10, strlen($this->client->getIdToken()));
        $this->assertGreaterThan(10, strlen($this->client->getAccessToken()));
        $this->assertGreaterThan(10, strlen($this->client->getRefreshToken()));

        $actualResponse = $this->client->get(self::EXAMPLE_API_ENDPOINT);
        $this->assertEquals(Response::HTTP_OK, $actualResponse->getStatusCode());
        $id_token = json_decode($actualValue->getBody())->id_token;
        $this->assertGreaterThan(10, strlen($id_token));

        $actualResponse = $this->client->cleanupRevokeAuth();
        $this->assertEquals(Response::HTTP_OK, $actualResponse->getStatusCode());
        $this->assertEquals("You have been signed out. Thank you.", $actualResponse->getBody());

        $actualResponse = $this->client->cleanupRevokeAuth();
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $actualResponse->getStatusCode());
        $responseBody = json_decode($actualResponse->getBody(), true);
        $this->assertEquals("You are currently not signed in.", $responseBody['message']);

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
    public function testApiAuthExampleUseBadSite(): void
    {
        $actualValue = $this->client->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
        $this->assertEquals(Response::HTTP_OK, $actualValue->getStatusCode());
        $this->assertGreaterThan(10, strlen($this->client->getIdToken()));
        $this->assertGreaterThan(10, strlen($this->client->getAccessToken()));
        $this->assertGreaterThan(10, strlen($this->client->getRefreshToken()));

        $actualResponse = $this->client->get(self::EXAMPLE_API_ENDPOINT_INVALID_SITE);
        $this->assertEquals(400, $actualResponse->getStatusCode());
        $this->client->removeAuthToken();
        $actualHeaders = $this->client->getConfig("headers");
        $this->assertArrayNotHasKey("Authorization", $actualHeaders);

        $this->client->cleanupRevokeAuth();
        $this->client->cleanupClient();
    }

    /**
     * Tests OpenEMR API Example Endpoint After Getting Auth With Bad Bearer Token for the REST and FHIR APIs
     */
    public function testApiAuthExampleUseBadToken(): void
    {
        $actualValue = $this->client->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
        $this->assertEquals(Response::HTTP_OK, $actualValue->getStatusCode());
        $this->assertGreaterThan(10, strlen($this->client->getIdToken()));
        $this->assertGreaterThan(10, strlen($this->client->getAccessToken()));
        $this->assertGreaterThan(10, strlen($this->client->getRefreshToken()));

        $actualResponse = $this->client->get(self::EXAMPLE_API_ENDPOINT);
        $this->assertEquals(Response::HTTP_OK, $actualResponse->getStatusCode());
        $this->client->removeAuthToken();
        $actualHeaders = $this->client->getConfig("headers");
        $this->assertArrayNotHasKey("Authorization", $actualHeaders);

        $this->client->setBearer(ApiTestClient::BOGUS_ACCESS_TOKEN);
        $actualResponse = $this->client->get(self::EXAMPLE_API_ENDPOINT);
        $this->assertEquals(401, $actualResponse->getStatusCode());

        $this->client->cleanupRevokeAuth();
        $this->client->cleanupClient();
    }

    /**
     * Tests OpenEMR API Example Endpoint After Getting Auth With Empty Bearer Token for the REST and FHIR APIs
     */
    public function testApiAuthExampleUseEmptyToken(): void
    {
        $actualValue = $this->client->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
        $this->assertEquals(Response::HTTP_OK, $actualValue->getStatusCode());
        $this->assertGreaterThan(10, strlen($this->client->getIdToken()));
        $this->assertGreaterThan(10, strlen($this->client->getAccessToken()));
        $this->assertGreaterThan(10, strlen($this->client->getRefreshToken()));

        $actualResponse = $this->client->get(self::EXAMPLE_API_ENDPOINT);
        $this->assertEquals(Response::HTTP_OK, $actualResponse->getStatusCode());
        $this->client->removeAuthToken();
        $actualHeaders = $this->client->getConfig("headers");
        $this->assertArrayNotHasKey("Authorization", $actualHeaders);

        $actualResponse = $this->client->get(self::EXAMPLE_API_ENDPOINT);
        $this->assertEquals(401, $actualResponse->getStatusCode());

        $this->client->cleanupRevokeAuth();
        $this->client->cleanupClient();
    }

    public function testRemoveAuthTokenNoToken(): void
    {
        $this->client->removeAuthToken();
        $actualHeaders = $this->client->getConfig("headers");
        $this->assertArrayNotHasKey("Authorization", $actualHeaders);
    }

    public function testApiAuthPublicClientDoesNotReturnRefreshToken(): void
    {
        $actualValue = $this->client->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT, [], 'public');
        $this->assertEquals(Response::HTTP_OK, $actualValue->getStatusCode(), "public client authorization should return valid status code");
        $this->assertNull($this->client->getRefreshToken(), "Refresh token should be empty for public client");
        $this->assertNotNull($this->client->getAccessToken(), "Access token should be populated");
        $this->assertNotNull($this->client->getIdToken(), "Id token should be populated");
    }
}
