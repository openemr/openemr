<?php

namespace OpenEMR\Tests\Api;

use OpenEMR\Tests\Api\ApiTestClient;
use PHPUnit\Framework\TestCase;

/**
 * Capability FHIR Endpoint Test Cases.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class SmartConfigurationTest extends TestCase
{
    const SMART_CONFIG_ENDPOINT = "/apis/default/fhir/.well-known/smart-configuration";

    /**
     * @var ApiTestClient
     */
    private $testClient;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * Base url endpoint for oauth2 capability uris
     * @var string
     */
    private $oauthBaseUrl;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
    }

    public function tearDown(): void
    {
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    public function testInvalidPathGet(): void
    {
        $actualResponse = $this->testClient->get(self::SMART_CONFIG_ENDPOINT . "ss");
        $this->assertEquals(401, $actualResponse->getStatusCode());
    }

    public function testGet(): void
    {
        $actualResponse = $this->testClient->get(self::SMART_CONFIG_ENDPOINT);
        $this->assertEquals(200, $actualResponse->getStatusCode());
    }

    /**
     * scopes_supported is the scope list itself, not an array wrapping it.
     */
    public function testScopesSupportedIsAFlatListOfScopes(): void
    {
        $scopes = $this->fetchConfig()['scopes_supported'] ?? null;

        $this->assertIsList($scopes, 'scopes_supported should be a JSON array');
        foreach ($scopes as $scope) {
            $this->assertIsString($scope, 'every entry of scopes_supported should be a scope string');
        }
        $this->assertContains('openid', $scopes);
    }

    /**
     * grant_types_supported lists the password grant when oauth_password_grant is on.
     */
    public function testGrantTypesSupportedIncludesThePasswordGrant(): void
    {
        // the API test environment enables oauth_password_grant: ApiTestClient gets its tokens with it
        $this->assertSame(
            ['client_credentials', 'authorization_code', 'password'],
            $this->fetchConfig()['grant_types_supported'] ?? null
        );
    }

    /**
     * token_endpoint_auth_methods_supported lists client_secret_post, which the token endpoint accepts.
     */
    public function testTokenEndpointAuthMethodsSupportedIncludesClientSecretPost(): void
    {
        $this->assertSame(
            ['client_secret_basic', 'client_secret_post', 'private_key_jwt'],
            $this->fetchConfig()['token_endpoint_auth_methods_supported'] ?? null
        );
    }

    /**
     * Fetches the SMART configuration document and decodes it.
     *
     * @return array<mixed>
     */
    private function fetchConfig(): array
    {
        $response = $this->testClient->get(self::SMART_CONFIG_ENDPOINT);
        $this->assertSame(200, $response->getStatusCode());
        $config = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($config, 'the SMART configuration should be a JSON object');
        return $config;
    }
}
