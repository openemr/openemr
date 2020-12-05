<?php

namespace OpenEMR\Tests\Api;

use PHPUnit\Framework\TestCase;
use OpenEMR\Tests\Api\ApiTestClient;

/**
 * Capability FHIR Endpoint Test Cases.
 * @coversDefaultClass OpenEMR\Tests\Api\ApiTestClient
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018-2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */
class IntrospectionTest extends TestCase
{
    private $privateClient;
    private $publicClient;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";

        // set up private client
        $this->privateClient = new ApiTestClient($baseUrl, false);
        $actualValue = $this->privateClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
        $this->assertEquals(200, $actualValue->getStatusCode());
        $this->assertGreaterThan(10, strlen($this->privateClient->getIdToken()));
        $this->assertGreaterThan(10, strlen($this->privateClient->getAccessToken()));
        $this->assertGreaterThan(10, strlen($this->privateClient->getRefreshToken()));
        $actualHeaders = $this->privateClient->getConfig("headers");
        $this->assertArrayHasKey("Authorization", $actualHeaders);
        $authHeaderValue = substr($actualHeaders["Authorization"], 7);
        $this->assertGreaterThan(10, strlen($authHeaderValue));
        $this->privateClient->removeAuthToken();
        $actualHeaders = $this->privateClient->getConfig("headers");
        $this->assertArrayNotHasKey("Authorization", $actualHeaders);
        $this->privateClient->setHeaders(
            [
                "Accept" => "application/json",
                "Content-Type" => "application/x-www-form-urlencoded"
            ]
        );
        // ensure client_id and client_secret are set
        $this->assertGreaterThan(10, strlen($this->privateClient->getClientId()));
        $this->assertGreaterThan(10, strlen($this->privateClient->getClientSecret()));

        // set up public client
        $this->publicClient = new ApiTestClient($baseUrl, false);
        $actualValue = $this->publicClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT, [], "public");
        $this->assertEquals(200, $actualValue->getStatusCode());
        $this->assertGreaterThan(10, strlen($this->publicClient->getIdToken()));
        $this->assertGreaterThan(10, strlen($this->publicClient->getAccessToken()));
        $this->assertGreaterThan(10, strlen($this->publicClient->getRefreshToken()));
        $actualHeaders = $this->publicClient->getConfig("headers");
        $this->assertArrayHasKey("Authorization", $actualHeaders);
        $authHeaderValue = substr($actualHeaders["Authorization"], 7);
        $this->assertGreaterThan(10, strlen($authHeaderValue));
        $this->publicClient->removeAuthToken();
        $actualHeaders = $this->publicClient->getConfig("headers");
        $this->assertArrayNotHasKey("Authorization", $actualHeaders);
        $this->publicClient->setHeaders(
            [
                "Accept" => "application/json",
                "Content-Type" => "application/x-www-form-urlencoded"
            ]
        );
        // ensure client_id is set
        $this->assertGreaterThan(10, strlen($this->publicClient->getClientId()));
    }

    public function tearDown(): void
    {
        $this->privateClient->cleanupRevokeAuth();
        $this->privateClient->cleanupClient();

        $this->publicClient->cleanupRevokeAuth();
        $this->publicClient->cleanupClient();
    }

    public function testPrivateClientWithAccessTokenWithHint()
    {
        $introspectBody = [
            "token_type_hint" => "access_token",
            "client_id" => $this->privateClient->getClientId(),
            "client_secret" => $this->privateClient->getClientSecret(),
            "token" => $this->privateClient->getAccessToken()
        ];
        $authResponse = $this->privateClient->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(200, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals(true, $responseBody->active);
        $this->assertEquals('active', $responseBody->status);
        $this->assertEquals($this->privateClient->getClientId(), $responseBody->client_id);
    }

    public function testPrivateClientWithBadAccessTokenWithHint()
    {
        $introspectBody = [
            "token_type_hint" => "access_token",
            "client_id" => $this->privateClient->getClientId(),
            "client_secret" => $this->privateClient->getClientSecret(),
            "token" => ApiTestClient::BOGUS_ACCESS_TOKEN
        ];
        $authResponse = $this->privateClient->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(200, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals(false, $responseBody->active);
        $this->assertNull($responseBody->status ?? null);
    }

    public function testPrivateBadClientIdWithAccessTokenWithHint()
    {
        $introspectBody = [
            "token_type_hint" => "access_token",
            "client_id" => ApiTestClient::BOGUS_CLIENTID,
            "client_secret" => $this->privateClient->getClientSecret(),
            "token" => $this->privateClient->getAccessToken()
        ];
        $authResponse = $this->privateClient->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(401, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals('invalid_request', $responseBody->error);
        $this->assertEquals('Not a registered client', $responseBody->error_description);
    }

    public function testPrivateBadClientSecretWithAccessTokenWithHint()
    {
        $introspectBody = [
            "token_type_hint" => "access_token",
            "client_id" => $this->privateClient->getClientId(),
            "client_secret" => ApiTestClient::BOGUS_CLIENTSECRET,
            "token" => $this->privateClient->getAccessToken()
        ];
        $authResponse = $this->privateClient->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(401, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals('invalid_request', $responseBody->error);
        $this->assertEquals('Client failed security', $responseBody->error_description);
    }

    public function testPrivateMissingClientSecretWithAccessTokenWithHint()
    {
        $introspectBody = [
            "token_type_hint" => "access_token",
            "client_id" => $this->privateClient->getClientId(),
            "token" => $this->privateClient->getAccessToken()
        ];
        $authResponse = $this->privateClient->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(400, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals('invalid_request', $responseBody->error);
        $this->assertEquals('Invalid client app type', $responseBody->error_description);
    }

    public function testPrivateClientWithAccessTokenWithoutHint()
    {
        $introspectBody = [
            "client_id" => $this->privateClient->getClientId(),
            "client_secret" => $this->privateClient->getClientSecret(),
            "token" => $this->privateClient->getAccessToken()
        ];
        $authResponse = $this->privateClient->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(200, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals(true, $responseBody->active);
        $this->assertEquals('active', $responseBody->status);
        $this->assertEquals($this->privateClient->getClientId(), $responseBody->client_id);
    }

    public function testPrivateClientWithBadAccessTokenWithoutHint()
    {
        $introspectBody = [
            "client_id" => $this->privateClient->getClientId(),
            "client_secret" => $this->privateClient->getClientSecret(),
            "token" => ApiTestClient::BOGUS_ACCESS_TOKEN
        ];
        $authResponse = $this->privateClient->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(200, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals(false, $responseBody->active);
        $this->assertNull($responseBody->status ?? null);
    }

    public function testPrivateBadClientIdWithAccessTokenWithoutHint()
    {
        $introspectBody = [
            "client_id" => ApiTestClient::BOGUS_CLIENTID,
            "client_secret" => $this->privateClient->getClientSecret(),
            "token" => $this->privateClient->getAccessToken()
        ];
        $authResponse = $this->privateClient->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(401, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals('invalid_request', $responseBody->error);
        $this->assertEquals('Not a registered client', $responseBody->error_description);
    }

    public function testPrivateBadClientSecretWithAccessTokenWithoutHint()
    {
        $introspectBody = [
            "client_id" => $this->privateClient->getClientId(),
            "client_secret" => ApiTestClient::BOGUS_CLIENTSECRET,
            "token" => $this->privateClient->getAccessToken()
        ];
        $authResponse = $this->privateClient->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(401, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals('invalid_request', $responseBody->error);
        $this->assertEquals('Client failed security', $responseBody->error_description);
    }

    public function testPrivateMissingClientSecretWithAccessTokenWithoutHint()
    {
        $introspectBody = [
            "client_id" => $this->privateClient->getClientId(),
            "token" => $this->privateClient->getAccessToken()
        ];
        $authResponse = $this->privateClient->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(400, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals('invalid_request', $responseBody->error);
        $this->assertEquals('Invalid client app type', $responseBody->error_description);
    }




    public function testPrivateClientWithRefreshTokenWithHint()
    {
        $introspectBody = [
            "token_type_hint" => "refresh_token",
            "client_id" => $this->privateClient->getClientId(),
            "client_secret" => $this->privateClient->getClientSecret(),
            "token" => $this->privateClient->getRefreshToken()
        ];
        $authResponse = $this->privateClient->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(200, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals(true, $responseBody->active);
        $this->assertEquals('active', $responseBody->status);
        $this->assertEquals($this->privateClient->getClientId(), $responseBody->client_id);
    }

    public function testPrivateClientWithBadRefreshTokenWithHint()
    {
        $introspectBody = [
            "token_type_hint" => "refresh_token",
            "client_id" => $this->privateClient->getClientId(),
            "client_secret" => $this->privateClient->getClientSecret(),
            "token" => ApiTestClient::BOGUS_REFRESH_TOKEN
        ];
        $authResponse = $this->privateClient->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(400, $authResponse->getStatusCode());
    }

    public function testPrivateBadClientIdWithRefreshTokenWithHint()
    {
        $introspectBody = [
            "token_type_hint" => "refresh_token",
            "client_id" => ApiTestClient::BOGUS_CLIENTID,
            "client_secret" => $this->privateClient->getClientSecret(),
            "token" => $this->privateClient->getRefreshToken()
        ];
        $authResponse = $this->privateClient->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(401, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals('invalid_request', $responseBody->error);
        $this->assertEquals('Not a registered client', $responseBody->error_description);
    }

    public function testPrivateBadClientSecretWithRefreshTokenWithHint()
    {
        $introspectBody = [
            "token_type_hint" => "refresh_token",
            "client_id" => $this->privateClient->getClientId(),
            "client_secret" => ApiTestClient::BOGUS_CLIENTSECRET,
            "token" => $this->privateClient->getRefreshToken()
        ];
        $authResponse = $this->privateClient->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(401, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals('invalid_request', $responseBody->error);
        $this->assertEquals('Client failed security', $responseBody->error_description);
    }

    public function testPrivateMissingClientSecretWithRefreshTokenWithHint()
    {
        $introspectBody = [
            "token_type_hint" => "refresh_token",
            "client_id" => $this->privateClient->getClientId(),
            "token" => $this->privateClient->getRefreshToken()
        ];
        $authResponse = $this->privateClient->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(400, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals('invalid_request', $responseBody->error);
        $this->assertEquals('Invalid client app type', $responseBody->error_description);
    }

    public function testPrivateClientWithRefreshTokenWithoutHint()
    {
        $introspectBody = [
            "client_id" => $this->privateClient->getClientId(),
            "client_secret" => $this->privateClient->getClientSecret(),
            "token" => $this->privateClient->getRefreshToken()
        ];
        $authResponse = $this->privateClient->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(200, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals(true, $responseBody->active);
        $this->assertEquals('active', $responseBody->status);
        $this->assertEquals($this->privateClient->getClientId(), $responseBody->client_id);
    }

    public function testPrivateClientWithBadRefreshTokenWithoutHint()
    {
        $introspectBody = [
            "client_id" => $this->privateClient->getClientId(),
            "client_secret" => $this->privateClient->getClientSecret(),
            "token" => ApiTestClient::BOGUS_REFRESH_TOKEN
        ];
        $authResponse = $this->privateClient->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(400, $authResponse->getStatusCode());
    }

    public function testPrivateBadClientIdWithRefreshTokenWithoutHint()
    {
        $introspectBody = [
            "client_id" => ApiTestClient::BOGUS_CLIENTID,
            "client_secret" => $this->privateClient->getClientSecret(),
            "token" => $this->privateClient->getRefreshToken()
        ];
        $authResponse = $this->privateClient->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(401, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals('invalid_request', $responseBody->error);
        $this->assertEquals('Not a registered client', $responseBody->error_description);
    }

    public function testPrivateBadClientSecretWithRefreshTokenWithoutHint()
    {
        $introspectBody = [
            "client_id" => $this->privateClient->getClientId(),
            "client_secret" => ApiTestClient::BOGUS_CLIENTSECRET,
            "token" => $this->privateClient->getRefreshToken()
        ];
        $authResponse = $this->privateClient->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(401, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals('invalid_request', $responseBody->error);
        $this->assertEquals('Client failed security', $responseBody->error_description);
    }

    public function testPrivateMissingClientSecretWithRefreshTokenWithoutHint()
    {
        $introspectBody = [
            "client_id" => $this->privateClient->getClientId(),
            "token" => $this->privateClient->getRefreshToken()
        ];
        $authResponse = $this->privateClient->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(400, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals('invalid_request', $responseBody->error);
        $this->assertEquals('Invalid client app type', $responseBody->error_description);
    }

    public function testPublicClientWithAccessTokenWithHint()
    {
        $introspectBody = [
            "token_type_hint" => "access_token",
            "client_id" => $this->publicClient->getClientId(),
            "token" => $this->publicClient->getAccessToken()
        ];
        $authResponse = $this->publicClient->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(200, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals(true, $responseBody->active);
        $this->assertEquals('active', $responseBody->status);
        $this->assertEquals($this->publicClient->getClientId(), $responseBody->client_id);
    }

    public function testPublicClientWithBadAccessTokenWithHint()
    {
        $introspectBody = [
            "token_type_hint" => "access_token",
            "client_id" => $this->publicClient->getClientId(),
            "token" => ApiTestClient::BOGUS_ACCESS_TOKEN
        ];
        $authResponse = $this->publicClient->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(200, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals(false, $responseBody->active);
        $this->assertNull($responseBody->status ?? null);
    }

    public function testPublicBadClientIdWithAccessTokenWithHint()
    {
        $introspectBody = [
            "token_type_hint" => "access_token",
            "client_id" => ApiTestClient::BOGUS_CLIENTID,
            "token" => $this->publicClient->getAccessToken()
        ];
        $authResponse = $this->publicClient->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(401, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals('invalid_request', $responseBody->error);
        $this->assertEquals('Not a registered client', $responseBody->error_description);
    }

    public function testPublicClientWithAccessTokenWithoutHint()
    {
        $introspectBody = [
            "client_id" => $this->publicClient->getClientId(),
            "token" => $this->publicClient->getAccessToken()
        ];
        $authResponse = $this->publicClient->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(200, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals(true, $responseBody->active);
        $this->assertEquals('active', $responseBody->status);
        $this->assertEquals($this->publicClient->getClientId(), $responseBody->client_id);
    }

    public function testPublicClientWithBadAccessTokenWithoutHint()
    {
        $introspectBody = [
            "client_id" => $this->publicClient->getClientId(),
            "token" => ApiTestClient::BOGUS_ACCESS_TOKEN
        ];
        $authResponse = $this->publicClient->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(200, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals(false, $responseBody->active);
        $this->assertNull($responseBody->status ?? null);
    }

    public function testPublicBadClientIdWithAccessTokenWithoutHint()
    {
        $introspectBody = [
            "client_id" => ApiTestClient::BOGUS_CLIENTID,
            "token" => $this->publicClient->getAccessToken()
        ];
        $authResponse = $this->publicClient->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(401, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals('invalid_request', $responseBody->error);
        $this->assertEquals('Not a registered client', $responseBody->error_description);
    }

    public function testPublicClientWithRefreshTokenWithHint()
    {
        $introspectBody = [
            "token_type_hint" => "refresh_token",
            "client_id" => $this->publicClient->getClientId(),
            "token" => $this->publicClient->getRefreshToken()
        ];
        $authResponse = $this->publicClient->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(200, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals(true, $responseBody->active);
        $this->assertEquals('active', $responseBody->status);
        $this->assertEquals($this->publicClient->getClientId(), $responseBody->client_id);
    }

    public function testPublicClientWithBadRefreshTokenWithHint()
    {
        $introspectBody = [
            "token_type_hint" => "refresh_token",
            "client_id" => $this->publicClient->getClientId(),
            "token" => ApiTestClient::BOGUS_REFRESH_TOKEN
        ];
        $authResponse = $this->publicClient->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(400, $authResponse->getStatusCode());
    }

    public function testPublicBadClientIdWithRefreshTokenWithHint()
    {
        $introspectBody = [
            "token_type_hint" => "refresh_token",
            "client_id" => ApiTestClient::BOGUS_CLIENTID,
            "token" => $this->publicClient->getRefreshToken()
        ];
        $authResponse = $this->publicClient->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(401, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals('invalid_request', $responseBody->error);
        $this->assertEquals('Not a registered client', $responseBody->error_description);
    }

    public function testPublicClientWithRefreshTokenWithoutHint()
    {
        $introspectBody = [
            "client_id" => $this->publicClient->getClientId(),
            "token" => $this->publicClient->getRefreshToken()
        ];
        $authResponse = $this->publicClient->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(200, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals(true, $responseBody->active);
        $this->assertEquals('active', $responseBody->status);
        $this->assertEquals($this->publicClient->getClientId(), $responseBody->client_id);
    }

    public function testPublicClientWithBadRefreshTokenWithoutHint()
    {
        $introspectBody = [
            "client_id" => $this->publicClient->getClientId(),
            "token" => ApiTestClient::BOGUS_REFRESH_TOKEN
        ];
        $authResponse = $this->publicClient->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(400, $authResponse->getStatusCode());
    }

    public function testPublicBadClientIdWithRefreshTokenWithoutHint()
    {
        $introspectBody = [
            "client_id" => ApiTestClient::BOGUS_CLIENTID,
            "token" => $this->publicClient->getRefreshToken()
        ];
        $authResponse = $this->publicClient->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(401, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals('invalid_request', $responseBody->error);
        $this->assertEquals('Not a registered client', $responseBody->error_description);
    }
}
