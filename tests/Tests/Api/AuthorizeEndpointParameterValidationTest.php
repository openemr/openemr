<?php

/**
 * OAuth2 authorize-endpoint parameter validation negatives.
 *
 * CustomAuthCodeGrant::validateAuthorizationRequest rejects three
 * specific malformed inputs before the login form renders:
 *
 *   - code_challenge_method not in the SMART-approved set
 *     (SMART forbids `plain`; only `S256` is accepted)
 *     src/.../Grant/CustomAuthCodeGrant.php:260
 *   - launch parameter that fails SMARTLaunchToken::deserializeToken
 *     src/.../Grant/CustomAuthCodeGrant.php:117
 *   - aud parameter that does not match the authorized server, in a
 *     launch scenario
 *     src/.../Grant/CustomAuthCodeGrant.php:102
 *
 * Each test hits /authorize with one bad param on top of an otherwise
 * valid request and asserts the response either redirects back to
 * redirect_uri with error=invalid_request or returns a non-200 status.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class AuthorizeEndpointParameterValidationTest extends TestCase
{
    private const REDIRECT_URI = 'https://client.example/cb';

    private string $baseUrl;
    private ?string $clientId = null;
    private ?string $originalSiteAddrOath = null;
    private bool $siteAddrOathWasInserted = false;

    protected function setUp(): void
    {
        $this->baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';

        if (getenv('OPENEMR_ALLOW_OAUTH_HTTPS_SKIP') === '1') {
            $this->markTestSkipped('Skipping per OPENEMR_ALLOW_OAUTH_HTTPS_SKIP=1');
        }
        $probe = $this->buildClient()->get($this->baseUrl . '/');
        if ($probe->getHeaderLine('Server') === '') {
            $message = 'OAuth flow requires a real webserver (Apache/nginx)';
            if (getenv('CI') !== false) {
                self::fail($message . ' — hard failure in CI');
            }
            $this->markTestSkipped($message);
        }

        $current = QueryUtils::querySingleRow(
            'SELECT gl_value FROM `globals` WHERE gl_name = ?',
            ['site_addr_oath']
        );
        if (is_array($current)) {
            $glValue = $current['gl_value'] ?? null;
            $this->originalSiteAddrOath = is_string($glValue) ? $glValue : null;
            if ($this->originalSiteAddrOath !== $this->baseUrl) {
                QueryUtils::sqlStatementThrowException(
                    'UPDATE `globals` SET gl_value = ? WHERE gl_name = ?',
                    [$this->baseUrl, 'site_addr_oath']
                );
            }
        } else {
            QueryUtils::sqlStatementThrowException(
                'INSERT INTO `globals` (`gl_name`, `gl_index`, `gl_value`) VALUES (?, 0, ?)',
                ['site_addr_oath', $this->baseUrl]
            );
            $this->siteAddrOathWasInserted = true;
        }
    }

    protected function tearDown(): void
    {
        try {
            if ($this->clientId !== null) {
                QueryUtils::sqlStatementThrowException(
                    'DELETE FROM `oauth_clients` WHERE `client_id` = ?',
                    [$this->clientId]
                );
            }
        } finally {
            if ($this->siteAddrOathWasInserted) {
                QueryUtils::sqlStatementThrowException(
                    'DELETE FROM `globals` WHERE gl_name = ?',
                    ['site_addr_oath']
                );
            } elseif ($this->originalSiteAddrOath !== null && $this->originalSiteAddrOath !== $this->baseUrl) {
                QueryUtils::sqlStatementThrowException(
                    'UPDATE `globals` SET gl_value = ? WHERE gl_name = ?',
                    [$this->originalSiteAddrOath, 'site_addr_oath']
                );
            }
        }
    }

    #[Test]
    public function testAuthorizeRejectsUnsupportedCodeChallengeMethod(): void
    {
        // SMART forbids `plain`; OpenEMR only accepts S256. Sending a
        // plain code_challenge should be rejected before the login form
        // renders.
        $http = $this->buildClient();
        $clientId = $this->registerPublicClient($http);
        $this->clientId = $clientId;

        $url = '/oauth2/default/authorize?' . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => self::REDIRECT_URI,
            'response_type' => 'code',
            'scope' => 'openid api:oemr',
            'state' => 'ccm-state',
            'code_challenge' => str_repeat('a', 43),
            'code_challenge_method' => 'plain',
        ]);
        $response = $http->get($this->baseUrl . $url, ['allow_redirects' => false]);
        $this->assertRejectsWithInvalidRequest($response, 'code_challenge_method');
    }

    #[Test]
    public function testAuthorizeRejectsMalformedLaunchToken(): void
    {
        // launch param that isn't a deserializable SMARTLaunchToken
        // (SMARTLaunchToken::deserializeToken throws JsonException).
        // aud must actually match one of the entries in expectedAudience
        // (built from site_addr_oath + webroot + "/apis/<site>/api")
        // so the aud check at CustomAuthCodeGrant:100 passes and we
        // reach the launch-deserialize check at :117.
        $http = $this->buildClient();
        $clientId = $this->registerPublicClient($http);
        $this->clientId = $clientId;

        $url = '/oauth2/default/authorize?' . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => self::REDIRECT_URI,
            'response_type' => 'code',
            'scope' => 'openid launch api:oemr',
            'state' => 'launch-state',
            'aud' => $this->baseUrl . '/apis/default/api',
            'launch' => 'not-a-real-launch-token',
        ]);
        $response = $http->get($this->baseUrl . $url, ['allow_redirects' => false]);
        $this->assertRejectsWithInvalidRequest($response, 'launch');
    }

    #[Test]
    public function testAuthorizeRejectsMismatchedAudInLaunchScenario(): void
    {
        // With launch present, aud MUST match this server's URL. Send a
        // launch (empty JSON object base64-encoded is a valid shape) plus
        // a bogus aud → rejected at line 102.
        $http = $this->buildClient();
        $clientId = $this->registerPublicClient($http);
        $this->clientId = $clientId;

        $url = '/oauth2/default/authorize?' . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => self::REDIRECT_URI,
            'response_type' => 'code',
            'scope' => 'openid launch api:oemr',
            'state' => 'aud-state',
            'aud' => 'https://not-this-server.example',
            // Any string here is fine — the aud check fires first, before
            // launch deserialization is even attempted.
            'launch' => 'ignored-because-aud-fails-first',
        ]);
        $response = $http->get($this->baseUrl . $url, ['allow_redirects' => false]);
        $this->assertRejectsWithInvalidRequest($response, 'aud');
    }

    /**
     * League's exception->generateHttpResponse() either:
     *   - 302 to redirect_uri with ?error=invalid_request&… (when the
     *     client and redirect_uri were validated first), or
     *   - 4xx JSON body with {"error":"invalid_request",…} when the
     *     redirect target isn't yet known.
     * Either shape counts as "rejected before login rendered".
     */
    private function assertRejectsWithInvalidRequest(
        ResponseInterface $response,
        string $expectedParamHint,
    ): void {
        $status = $response->getStatusCode();
        if ($status === 302) {
            $location = $response->getHeaderLine('Location');
            $this->assertStringStartsWith(
                self::REDIRECT_URI,
                $location,
                'Rejection should redirect back to the registered redirect_uri'
            );
            $queryString = parse_url($location, PHP_URL_QUERY);
            $this->assertIsString($queryString);
            parse_str($queryString, $query);
            $this->assertSame(
                'invalid_request',
                $query['error'] ?? null,
                'Redirect should carry error=invalid_request. Location: ' . $location
            );
            $description = $query['error_description'] ?? $query['hint'] ?? '';
            $this->assertIsString($description);
            $this->assertStringContainsString(
                $expectedParamHint,
                $description,
                'error_description or hint should name the rejected parameter. Location: ' . $location
            );
            return;
        }
        $this->assertContains(
            $status,
            [400, 401],
            '/authorize with a malformed parameter must either 302 back to '
                . 'redirect_uri with error= or 4xx. Got ' . $status . '. '
                . 'Body preview: ' . substr((string) $response->getBody(), 0, 300)
        );
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertSame('invalid_request', $body['error'] ?? null);
    }

    private function registerPublicClient(Client $http): string
    {
        // Public client per the AuthorizationGrantPublicClientPkceTest
        // convention: application_type=web (flips is_confidential=0);
        // token_endpoint_auth_method must still be one of the DCR-allowed
        // values (client_secret_basic | client_secret_post | private_key_jwt)
        // per AuthorizationController::clientRegistration validation.
        $reg = $http->post($this->baseUrl . '/oauth2/default/registration', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'application_type' => 'web',
                'redirect_uris' => [self::REDIRECT_URI],
                'client_name' => 'AuthorizeParamValidationTest-' . bin2hex(random_bytes(3)),
                'token_endpoint_auth_method' => 'client_secret_post',
                'contacts' => ['e2e@test.example'],
                'scope' => 'openid launch api:oemr',
            ],
        ]);
        $this->assertSame(200, $reg->getStatusCode(), 'DCR should succeed');
        $data = json_decode((string) $reg->getBody(), true);
        $this->assertIsArray($data);
        $this->assertIsString($data['client_id']);
        return $data['client_id'];
    }

    private function buildClient(): Client
    {
        return new Client([
            'verify' => false,
            'http_errors' => false,
            'cookies' => new CookieJar(),
            'timeout' => 15,
        ]);
    }
}
