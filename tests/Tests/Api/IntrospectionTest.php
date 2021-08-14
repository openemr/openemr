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
    /**
     * @var ApiTestClient
     */
    private $client;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->client = new ApiTestClient($baseUrl, false);
    }

    public function tearDown(): void
    {
        $this->client->cleanupRevokeAuth();
        $this->client->cleanupClient();
    }

    private function setUpClient($type): void
    {
        if ($type == 'private') {
            // set up private client
            $actualValue = $this->client->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
        } else {
            // set up public client
            $actualValue = $this->client->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT, [], "public");
        }
        $this->assertEquals(200, $actualValue->getStatusCode(), "Client authorization returned the wrong status code");
        $this->assertGreaterThan(10, strlen($this->client->getIdToken()), "ID token was not sent via client authorization");
        $this->assertGreaterThan(10, strlen($this->client->getAccessToken()), "Acccess token was not sent via client authorization");
        if ($type == 'private') {
            $this->assertGreaterThan(10, strlen($this->client->getRefreshToken()), "Refresh token was not sent via client authorization for private client");
        }
        $actualHeaders = $this->client->getConfig("headers");
        $this->assertArrayHasKey("Authorization", $actualHeaders);
        $authHeaderValue = substr($actualHeaders["Authorization"], 7);
        $this->assertGreaterThan(10, strlen($authHeaderValue));
        $this->client->removeAuthToken();
        $actualHeaders = $this->client->getConfig("headers");
        $this->assertArrayNotHasKey("Authorization", $actualHeaders);
        $this->client->setHeaders(
            [
                "Accept" => "application/json",
                "Content-Type" => "application/x-www-form-urlencoded"
            ]
        );
        // ensure client_id and client_secret are set
        $this->assertGreaterThan(10, strlen($this->client->getClientId()));
        if ($type == 'private') {
            $this->assertGreaterThan(10, strlen($this->client->getClientSecret()));
        }
    }

    public function testPrivateClientWithAccessTokenWithHint()
    {
        $this->setUpClient('private');
        $introspectBody = [
            "token_type_hint" => "access_token",
            "client_id" => $this->client->getClientId(),
            "client_secret" => $this->client->getClientSecret(),
            "token" => $this->client->getAccessToken()
        ];
        $authResponse = $this->client->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(200, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals(true, $responseBody->active);
        $this->assertEquals('active', $responseBody->status);
        $this->assertEquals($this->client->getClientId(), $responseBody->client_id);
    }

    public function testPrivateClientWithAccessTokenWithHintWithRevoke()
    {
        $this->setUpClient('private');
        $introspectBody = [
            "token_type_hint" => "access_token",
            "client_id" => $this->client->getClientId(),
            "client_secret" => $this->client->getClientSecret(),
            "token" => $this->client->getAccessToken()
        ];
        $authResponse = $this->client->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(200, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals(true, $responseBody->active);
        $this->assertEquals('active', $responseBody->status);
        $this->assertEquals($this->client->getClientId(), $responseBody->client_id);

        $this->client->cleanupRevokeAuth();
        $authResponse = $this->client->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(200, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals(false, $responseBody->active);
        $this->assertEquals('revoked', $responseBody->status);
        $this->assertEquals($this->client->getClientId(), $responseBody->client_id);
    }

    public function testPrivateClientWithBadAccessTokenWithHint()
    {
        $this->setUpClient('private');
        $introspectBody = [
            "token_type_hint" => "access_token",
            "client_id" => $this->client->getClientId(),
            "client_secret" => $this->client->getClientSecret(),
            "token" => ApiTestClient::BOGUS_ACCESS_TOKEN
        ];
        $authResponse = $this->client->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(200, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals(false, $responseBody->active);
        $this->assertNull($responseBody->status ?? null);
    }

    public function testPrivateBadClientIdWithAccessTokenWithHint()
    {
        $this->setUpClient('private');
        $introspectBody = [
            "token_type_hint" => "access_token",
            "client_id" => ApiTestClient::BOGUS_CLIENTID,
            "client_secret" => $this->client->getClientSecret(),
            "token" => $this->client->getAccessToken()
        ];
        $authResponse = $this->client->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(401, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals('invalid_request', $responseBody->error);
        $this->assertEquals('Not a registered client', $responseBody->error_description);
    }

    public function testPrivateBadClientSecretWithAccessTokenWithHint()
    {
        $this->setUpClient('private');
        $introspectBody = [
            "token_type_hint" => "access_token",
            "client_id" => $this->client->getClientId(),
            "client_secret" => ApiTestClient::BOGUS_CLIENTSECRET,
            "token" => $this->client->getAccessToken()
        ];
        $authResponse = $this->client->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(401, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals('invalid_request', $responseBody->error);
        $this->assertEquals('Client failed security', $responseBody->error_description);
    }

    public function testPrivateMissingClientSecretWithAccessTokenWithHint()
    {
        $this->setUpClient('private');
        $introspectBody = [
            "token_type_hint" => "access_token",
            "client_id" => $this->client->getClientId(),
            "token" => $this->client->getAccessToken()
        ];
        $authResponse = $this->client->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(400, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals('invalid_request', $responseBody->error);
        $this->assertEquals('Invalid client app type', $responseBody->error_description);
    }

    public function testPrivateClientWithAccessTokenWithoutHint()
    {
        $this->setUpClient('private');
        $introspectBody = [
            "client_id" => $this->client->getClientId(),
            "client_secret" => $this->client->getClientSecret(),
            "token" => $this->client->getAccessToken()
        ];
        $authResponse = $this->client->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(200, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals(true, $responseBody->active);
        $this->assertEquals('active', $responseBody->status);
        $this->assertEquals($this->client->getClientId(), $responseBody->client_id);
    }

    public function testPrivateClientWithAccessTokenWithoutHintWithRevoke()
    {
        $this->setUpClient('private');
        $introspectBody = [
            "client_id" => $this->client->getClientId(),
            "client_secret" => $this->client->getClientSecret(),
            "token" => $this->client->getAccessToken()
        ];
        $authResponse = $this->client->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(200, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals(true, $responseBody->active);
        $this->assertEquals('active', $responseBody->status);
        $this->assertEquals($this->client->getClientId(), $responseBody->client_id);

        $this->client->cleanupRevokeAuth();
        $authResponse = $this->client->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(200, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals(false, $responseBody->active);
        $this->assertEquals('revoked', $responseBody->status);
        $this->assertEquals($this->client->getClientId(), $responseBody->client_id);
    }

    public function testPrivateClientWithBadAccessTokenWithoutHint()
    {
        $this->setUpClient('private');
        $introspectBody = [
            "client_id" => $this->client->getClientId(),
            "client_secret" => $this->client->getClientSecret(),
            "token" => ApiTestClient::BOGUS_ACCESS_TOKEN
        ];
        $authResponse = $this->client->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(200, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals(false, $responseBody->active);
        $this->assertNull($responseBody->status ?? null);
    }

    public function testPrivateBadClientIdWithAccessTokenWithoutHint()
    {
        $this->setUpClient('private');
        $introspectBody = [
            "client_id" => ApiTestClient::BOGUS_CLIENTID,
            "client_secret" => $this->client->getClientSecret(),
            "token" => $this->client->getAccessToken()
        ];
        $authResponse = $this->client->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(401, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals('invalid_request', $responseBody->error);
        $this->assertEquals('Not a registered client', $responseBody->error_description);
    }

    public function testPrivateBadClientSecretWithAccessTokenWithoutHint()
    {
        $this->setUpClient('private');
        $introspectBody = [
            "client_id" => $this->client->getClientId(),
            "client_secret" => ApiTestClient::BOGUS_CLIENTSECRET,
            "token" => $this->client->getAccessToken()
        ];
        $authResponse = $this->client->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(401, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals('invalid_request', $responseBody->error);
        $this->assertEquals('Client failed security', $responseBody->error_description);
    }

    public function testPrivateMissingClientSecretWithAccessTokenWithoutHint()
    {
        $this->setUpClient('private');
        $introspectBody = [
            "client_id" => $this->client->getClientId(),
            "token" => $this->client->getAccessToken()
        ];
        $authResponse = $this->client->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(400, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals('invalid_request', $responseBody->error);
        $this->assertEquals('Invalid client app type', $responseBody->error_description);
    }

    public function testPrivateClientWithRefreshTokenWithHint()
    {
        $this->setUpClient('private');
        $introspectBody = [
            "token_type_hint" => "refresh_token",
            "client_id" => $this->client->getClientId(),
            "client_secret" => $this->client->getClientSecret(),
            "token" => $this->client->getRefreshToken()
        ];
        $authResponse = $this->client->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(200, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals(true, $responseBody->active);
        $this->assertEquals('active', $responseBody->status);
        $this->assertEquals($this->client->getClientId(), $responseBody->client_id);
    }

    public function testPrivateClientWithRefreshTokenWithHintWithRevoke()
    {
        $this->setUpClient('private');
        $introspectBody = [
            "token_type_hint" => "refresh_token",
            "client_id" => $this->client->getClientId(),
            "client_secret" => $this->client->getClientSecret(),
            "token" => $this->client->getRefreshToken()
        ];
        $authResponse = $this->client->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(200, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals(true, $responseBody->active);
        $this->assertEquals('active', $responseBody->status);
        $this->assertEquals($this->client->getClientId(), $responseBody->client_id);

        $this->client->cleanupRevokeAuth();
        $authResponse = $this->client->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(200, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals(false, $responseBody->active);
        $this->assertEquals('revoked', $responseBody->status);
        $this->assertEquals($this->client->getClientId(), $responseBody->client_id);
    }

    public function testPrivateClientWithBadRefreshTokenWithHint()
    {
        $this->setUpClient('private');
        $introspectBody = [
            "token_type_hint" => "refresh_token",
            "client_id" => $this->client->getClientId(),
            "client_secret" => $this->client->getClientSecret(),
            "token" => ApiTestClient::BOGUS_REFRESH_TOKEN
        ];
        $authResponse = $this->client->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(400, $authResponse->getStatusCode());
    }

    public function testPrivateBadClientIdWithRefreshTokenWithHint()
    {
        $this->setUpClient('private');
        $introspectBody = [
            "token_type_hint" => "refresh_token",
            "client_id" => ApiTestClient::BOGUS_CLIENTID,
            "client_secret" => $this->client->getClientSecret(),
            "token" => $this->client->getRefreshToken()
        ];
        $authResponse = $this->client->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(401, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals('invalid_request', $responseBody->error);
        $this->assertEquals('Not a registered client', $responseBody->error_description);
    }

    public function testPrivateBadClientSecretWithRefreshTokenWithHint()
    {
        $this->setUpClient('private');
        $introspectBody = [
            "token_type_hint" => "refresh_token",
            "client_id" => $this->client->getClientId(),
            "client_secret" => ApiTestClient::BOGUS_CLIENTSECRET,
            "token" => $this->client->getRefreshToken()
        ];
        $authResponse = $this->client->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(401, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals('invalid_request', $responseBody->error);
        $this->assertEquals('Client failed security', $responseBody->error_description);
    }

    public function testPrivateMissingClientSecretWithRefreshTokenWithHint()
    {
        $this->setUpClient('private');
        $introspectBody = [
            "token_type_hint" => "refresh_token",
            "client_id" => $this->client->getClientId(),
            "token" => $this->client->getRefreshToken()
        ];
        $authResponse = $this->client->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(400, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals('invalid_request', $responseBody->error);
        $this->assertEquals('Invalid client app type', $responseBody->error_description);
    }

    public function testPrivateClientWithRefreshTokenWithoutHint()
    {
        $this->setUpClient('private');
        $introspectBody = [
            "client_id" => $this->client->getClientId(),
            "client_secret" => $this->client->getClientSecret(),
            "token" => $this->client->getRefreshToken()
        ];
        $authResponse = $this->client->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(200, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals(true, $responseBody->active);
        $this->assertEquals('active', $responseBody->status);
        $this->assertEquals($this->client->getClientId(), $responseBody->client_id);
    }

    public function testPrivateClientWithRefreshTokenWithoutHintWithRevoke()
    {
        $this->setUpClient('private');
        $introspectBody = [
            "client_id" => $this->client->getClientId(),
            "client_secret" => $this->client->getClientSecret(),
            "token" => $this->client->getRefreshToken()
        ];
        $authResponse = $this->client->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(200, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals(true, $responseBody->active);
        $this->assertEquals('active', $responseBody->status);
        $this->assertEquals($this->client->getClientId(), $responseBody->client_id);

        $this->client->cleanupRevokeAuth();
        $authResponse = $this->client->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(200, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals(false, $responseBody->active);
        $this->assertEquals('revoked', $responseBody->status);
        $this->assertEquals($this->client->getClientId(), $responseBody->client_id);
    }

    public function testPrivateClientWithBadRefreshTokenWithoutHint()
    {
        $this->setUpClient('private');
        $introspectBody = [
            "client_id" => $this->client->getClientId(),
            "client_secret" => $this->client->getClientSecret(),
            "token" => ApiTestClient::BOGUS_REFRESH_TOKEN
        ];
        $authResponse = $this->client->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(400, $authResponse->getStatusCode());
    }

    public function testPrivateBadClientIdWithRefreshTokenWithoutHint()
    {
        $this->setUpClient('private');
        $introspectBody = [
            "client_id" => ApiTestClient::BOGUS_CLIENTID,
            "client_secret" => $this->client->getClientSecret(),
            "token" => $this->client->getRefreshToken()
        ];
        $authResponse = $this->client->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(401, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals('invalid_request', $responseBody->error);
        $this->assertEquals('Not a registered client', $responseBody->error_description);
    }

    public function testPrivateBadClientSecretWithRefreshTokenWithoutHint()
    {
        $this->setUpClient('private');
        $introspectBody = [
            "client_id" => $this->client->getClientId(),
            "client_secret" => ApiTestClient::BOGUS_CLIENTSECRET,
            "token" => $this->client->getRefreshToken()
        ];
        $authResponse = $this->client->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(401, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals('invalid_request', $responseBody->error);
        $this->assertEquals('Client failed security', $responseBody->error_description);
    }

    public function testPrivateMissingClientSecretWithRefreshTokenWithoutHint()
    {
        $this->setUpClient('private');
        $introspectBody = [
            "client_id" => $this->client->getClientId(),
            "token" => $this->client->getRefreshToken()
        ];
        $authResponse = $this->client->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(400, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals('invalid_request', $responseBody->error);
        $this->assertEquals('Invalid client app type', $responseBody->error_description);
    }

    public function testPublicClientWithAccessTokenWithHint()
    {
        $this->setUpClient('public');
        $introspectBody = [
            "token_type_hint" => "access_token",
            "client_id" => $this->client->getClientId(),
            "token" => $this->client->getAccessToken()
        ];
        $authResponse = $this->client->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(200, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals(true, $responseBody->active);
        $this->assertEquals('active', $responseBody->status);
        $this->assertEquals($this->client->getClientId(), $responseBody->client_id);
    }

    public function testPublicClientWithAccessTokenWithHintWithRevoke()
    {
        $this->setUpClient('public');
        $introspectBody = [
            "token_type_hint" => "access_token",
            "client_id" => $this->client->getClientId(),
            "token" => $this->client->getAccessToken()
        ];
        $authResponse = $this->client->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(200, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals(true, $responseBody->active);
        $this->assertEquals('active', $responseBody->status);
        $this->assertEquals($this->client->getClientId(), $responseBody->client_id);

        $this->client->cleanupRevokeAuth();
        $authResponse = $this->client->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(200, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals(false, $responseBody->active);
        $this->assertEquals('revoked', $responseBody->status);
        $this->assertEquals($this->client->getClientId(), $responseBody->client_id);
    }

    public function testPublicClientWithBadAccessTokenWithHint()
    {
        $this->setUpClient('public');
        $introspectBody = [
            "token_type_hint" => "access_token",
            "client_id" => $this->client->getClientId(),
            "token" => ApiTestClient::BOGUS_ACCESS_TOKEN
        ];
        $authResponse = $this->client->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(200, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals(false, $responseBody->active);
        $this->assertNull($responseBody->status ?? null);
    }

    public function testPublicBadClientIdWithAccessTokenWithHint()
    {
        $this->setUpClient('public');
        $introspectBody = [
            "token_type_hint" => "access_token",
            "client_id" => ApiTestClient::BOGUS_CLIENTID,
            "token" => $this->client->getAccessToken()
        ];
        $authResponse = $this->client->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(401, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals('invalid_request', $responseBody->error);
        $this->assertEquals('Not a registered client', $responseBody->error_description);
    }

    public function testPublicClientWithAccessTokenWithoutHint()
    {
        $this->setUpClient('public');
        $introspectBody = [
            "client_id" => $this->client->getClientId(),
            "token" => $this->client->getAccessToken()
        ];
        $authResponse = $this->client->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(200, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals(true, $responseBody->active);
        $this->assertEquals('active', $responseBody->status);
        $this->assertEquals($this->client->getClientId(), $responseBody->client_id);
    }

    public function testPublicClientWithAccessTokenWithoutHintWithRevoke()
    {
        $this->setUpClient('public');
        $introspectBody = [
            "client_id" => $this->client->getClientId(),
            "token" => $this->client->getAccessToken()
        ];
        $authResponse = $this->client->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(200, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals(true, $responseBody->active);
        $this->assertEquals('active', $responseBody->status);
        $this->assertEquals($this->client->getClientId(), $responseBody->client_id);

        $this->client->cleanupRevokeAuth();
        $authResponse = $this->client->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(200, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals(false, $responseBody->active);
        $this->assertEquals('revoked', $responseBody->status);
        $this->assertEquals($this->client->getClientId(), $responseBody->client_id);
    }

    public function testPublicClientWithBadAccessTokenWithoutHint()
    {
        $this->setUpClient('public');
        $introspectBody = [
            "client_id" => $this->client->getClientId(),
            "token" => ApiTestClient::BOGUS_ACCESS_TOKEN
        ];
        $authResponse = $this->client->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(200, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals(false, $responseBody->active);
        $this->assertNull($responseBody->status ?? null);
    }

    public function testPublicBadClientIdWithAccessTokenWithoutHint()
    {
        $this->setUpClient('public');
        $introspectBody = [
            "client_id" => ApiTestClient::BOGUS_CLIENTID,
            "token" => $this->client->getAccessToken()
        ];
        $authResponse = $this->client->post(ApiTestClient::OAUTH_INTROSPECTION_ENDPOINT, $introspectBody, false);
        $this->assertEquals(401, $authResponse->getStatusCode());
        $responseBody = json_decode($authResponse->getBody());
        $this->assertEquals('invalid_request', $responseBody->error);
        $this->assertEquals('Not a registered client', $responseBody->error_description);
    }
}
