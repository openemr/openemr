<?php

/**
 * Disabled OAuth2 client is rejected at the token endpoint.
 *
 * Each of the three grants that gates on `!$client->isEnabled()` in its
 * validateClient() override:
 *   - CustomAuthCodeGrant     (src/.../Grant/CustomAuthCodeGrant.php:236)
 *   - CustomRefreshTokenGrant (src/.../Grant/CustomRefreshTokenGrant.php:226)
 *   - CustomPasswordGrant     (src/.../Grant/CustomPasswordGrant.php:129)
 *
 * DCR sets `is_enabled = 0` when a client requests a scope set that
 * requires manual approval (ScopeRepository::hasScopesThatRequireManualApproval).
 * Once flipped off, any subsequent token exchange from that client must
 * be rejected. These tests DCR-register a client, flip the DB row to
 * disabled directly, then attempt the grant and assert 401 invalid_client.
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
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DomCrawler\Crawler;

class DisabledClientRejectionTest extends TestCase
{
    private string $baseUrl;
    private ?string $clientId = null;
    private mixed $originalPasswordGrantSetting = null;
    private bool $originalPasswordGrantSettingWasSet = false;
    private ?string $originalSiteAddrOath = null;
    private bool $siteAddrOathWasInserted = false;
    private ?string $originalPasswordGrantGlobalRow = null;
    private bool $passwordGrantGlobalRowWasInserted = false;

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

        // OAuth iss/aud + form-action URL generation reads site_addr_oath.
        // Snapshot + set so the server generates redirects at the same
        // origin the test client hits. Same pattern as
        // AuthorizationGrantJwtAssertionFlowTest.
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
                QueryUtils::sqlStatementThrowException(
                    'DELETE FROM `oauth_trusted_user` WHERE `client_id` = ?',
                    [$this->clientId]
                );
            }
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
            $this->restorePasswordGrantGlobal();
        } finally {
            $globals = OEGlobalsBag::getInstance();
            if ($this->originalPasswordGrantSettingWasSet) {
                $globals->set('oauth_password_grant', $this->originalPasswordGrantSetting);
            } else {
                $globals->remove('oauth_password_grant');
                unset($GLOBALS['oauth_password_grant']);
            }
        }
    }

    private function persistPasswordGrantEnabled(): void
    {
        // OEGlobalsBag only mutates the current PHPUnit process; the HTTP
        // server reads oauth_password_grant from the globals table on
        // each request. Snapshot + set the DB row so CustomPasswordGrant
        // is actually enabled server-side for the /token calls below.
        $current = QueryUtils::querySingleRow(
            'SELECT gl_value FROM `globals` WHERE gl_name = ?',
            ['oauth_password_grant']
        );
        if (is_array($current)) {
            $glValue = $current['gl_value'] ?? null;
            $this->originalPasswordGrantGlobalRow = is_string($glValue) ? $glValue : null;
            QueryUtils::sqlStatementThrowException(
                'UPDATE `globals` SET gl_value = ? WHERE gl_name = ?',
                ['3', 'oauth_password_grant']
            );
        } else {
            QueryUtils::sqlStatementThrowException(
                'INSERT INTO `globals` (`gl_name`, `gl_index`, `gl_value`) VALUES (?, 0, ?)',
                ['oauth_password_grant', '3']
            );
            $this->passwordGrantGlobalRowWasInserted = true;
        }
    }

    private function restorePasswordGrantGlobal(): void
    {
        if ($this->passwordGrantGlobalRowWasInserted) {
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM `globals` WHERE gl_name = ?',
                ['oauth_password_grant']
            );
        } elseif ($this->originalPasswordGrantGlobalRow !== null) {
            QueryUtils::sqlStatementThrowException(
                'UPDATE `globals` SET gl_value = ? WHERE gl_name = ?',
                [$this->originalPasswordGrantGlobalRow, 'oauth_password_grant']
            );
        }
    }

    #[Test]
    public function testAuthorizationCodeGrantRejectsDisabledClient(): void
    {
        $http = $this->buildClient();
        [$clientId, $clientSecret] = $this->registerConfidentialClient(
            $http,
            'openid api:oemr'
        );
        $this->clientId = $clientId;

        // Walk /authorize → login → consent to get an auth code first,
        // then disable the client, then attempt the token exchange.
        // This proves the rejection happens at /token (validateClient
        // in CustomAuthCodeGrant), not that /authorize is inaccessible
        // for disabled clients.
        $code = $this->obtainAuthCode($http, $clientId);
        $this->disableClient($clientId);

        $response = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => 'https://client.example/cb',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ],
        ]);
        $this->assertSame(
            401,
            $response->getStatusCode(),
            'Disabled client on authorization_code must return 401. '
                . 'Body: ' . (string) $response->getBody()
        );
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertSame('invalid_client', $body['error'] ?? null);
    }

    #[Test]
    public function testPasswordGrantRejectsDisabledClient(): void
    {
        $globals = OEGlobalsBag::getInstance();
        $this->originalPasswordGrantSettingWasSet = $globals->has('oauth_password_grant');
        $this->originalPasswordGrantSetting = $globals->get('oauth_password_grant');
        $globals->set('oauth_password_grant', 3);
        $this->persistPasswordGrantEnabled();

        $http = $this->buildClient();
        [$clientId, $clientSecret] = $this->registerConfidentialClient(
            $http,
            'openid api:oemr'
        );
        $this->clientId = $clientId;
        $this->disableClient($clientId);

        $response = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'scope' => 'openid api:oemr',
                'user_role' => 'users',
                'username' => 'admin',
                'password' => 'pass',
            ],
        ]);
        $this->assertSame(
            401,
            $response->getStatusCode(),
            'Disabled client on password grant must return 401. '
                . 'Body: ' . (string) $response->getBody()
        );
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertSame('invalid_client', $body['error'] ?? null);
    }

    #[Test]
    public function testRefreshGrantRejectsDisabledClient(): void
    {
        $globals = OEGlobalsBag::getInstance();
        $this->originalPasswordGrantSettingWasSet = $globals->has('oauth_password_grant');
        $this->originalPasswordGrantSetting = $globals->get('oauth_password_grant');
        $globals->set('oauth_password_grant', 3);
        $this->persistPasswordGrantEnabled();

        $http = $this->buildClient();
        [$clientId, $clientSecret] = $this->registerConfidentialClient(
            $http,
            'openid api:oemr offline_access'
        );
        $this->clientId = $clientId;

        // Mint a refresh_token via password grant while the client is
        // still enabled, then disable and try the refresh.
        $tokenResp = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'scope' => 'openid api:oemr offline_access',
                'user_role' => 'users',
                'username' => 'admin',
                'password' => 'pass',
            ],
        ]);
        $this->assertSame(
            200,
            $tokenResp->getStatusCode(),
            'Password grant should succeed while client is enabled. Body: '
                . (string) $tokenResp->getBody()
        );
        $tokens = json_decode((string) $tokenResp->getBody(), true);
        $this->assertIsArray($tokens);
        $this->assertArrayHasKey('refresh_token', $tokens);
        $this->assertIsString($tokens['refresh_token']);
        $refreshToken = $tokens['refresh_token'];

        $this->disableClient($clientId);

        $refreshResp = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ],
        ]);
        $this->assertSame(
            401,
            $refreshResp->getStatusCode(),
            'Disabled client on refresh_token must return 401. '
                . 'Body: ' . (string) $refreshResp->getBody()
        );
        $body = json_decode((string) $refreshResp->getBody(), true);
        $this->assertIsArray($body);
        $this->assertSame('invalid_client', $body['error'] ?? null);
    }

    /**
     * @return array{string, string} [client_id, client_secret]
     */
    private function registerConfidentialClient(Client $http, string $scope): array
    {
        $reg = $http->post($this->baseUrl . '/oauth2/default/registration', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'application_type' => 'private',
                'redirect_uris' => ['https://client.example/cb'],
                'client_name' => 'DisabledClientRejectionTest-' . bin2hex(random_bytes(3)),
                'token_endpoint_auth_method' => 'client_secret_post',
                'contacts' => ['e2e@test.example'],
                'scope' => $scope,
            ],
        ]);
        $this->assertSame(200, $reg->getStatusCode(), 'DCR should succeed');
        $data = json_decode((string) $reg->getBody(), true);
        $this->assertIsArray($data);
        $this->assertIsString($data['client_id']);
        $this->assertIsString($data['client_secret']);
        return [$data['client_id'], $data['client_secret']];
    }

    private function disableClient(string $clientId): void
    {
        QueryUtils::sqlStatementThrowException(
            'UPDATE `oauth_clients` SET `is_enabled` = 0 WHERE `client_id` = ?',
            [$clientId]
        );
    }

    private function obtainAuthCode(Client $http, string $clientId): string
    {
        $state = 'disabled-client-state-' . bin2hex(random_bytes(3));
        $authUrl = '/oauth2/default/authorize?' . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => 'https://client.example/cb',
            'response_type' => 'code',
            'scope' => 'openid api:oemr',
            'state' => $state,
        ]);
        $loginPage = $http->get($this->baseUrl . $authUrl);
        $this->assertSame(200, $loginPage->getStatusCode());
        [$loginCsrf, $loginAction] = $this->parseForm(
            new Crawler((string) $loginPage->getBody()),
            ['csrf_token_form', 'username', 'password', 'email', 'persist_login', 'user_role']
        );
        $postLogin = $http->post($this->baseUrl . $loginAction, [
            'form_params' => [
                'csrf_token_form' => $loginCsrf,
                'username' => 'admin',
                'password' => 'pass',
                'email' => '',
                'persist_login' => '0',
                'user_role' => 'api',
            ],
        ]);
        $this->assertSame(200, $postLogin->getStatusCode());
        $consent = new Crawler((string) $postLogin->getBody());
        [$consentCsrf, $consentAction] = $this->parseForm(
            $consent,
            ['csrf_token_form', 'proceed', 'scope[openid]', 'scope[api:oemr]']
        );
        $postConsent = $http->post($this->baseUrl . $consentAction, [
            'form_params' => [
                'csrf_token_form' => $consentCsrf,
                'proceed' => '1',
                'scope' => ['openid' => 'openid', 'api:oemr' => 'api:oemr'],
            ],
            'allow_redirects' => false,
        ]);
        $this->assertSame(302, $postConsent->getStatusCode());
        $callbackQueryString = parse_url($postConsent->getHeaderLine('Location'), PHP_URL_QUERY);
        $this->assertIsString($callbackQueryString);
        parse_str($callbackQueryString, $callbackQuery);
        $this->assertArrayHasKey('code', $callbackQuery);
        $this->assertIsString($callbackQuery['code']);
        return $callbackQuery['code'];
    }

    /**
     * XPath-based form scraper matching the shape used by
     * AuthorizationGrantJwtAssertionFlowTest and
     * AuthorizationLogoutFullFlowTest — the css-selector Symfony
     * component is not available in the api-suite runtime.
     *
     * @param list<string> $required
     *
     * @return array{string, string} [csrf_token, form_action]
     */
    private function parseForm(Crawler $crawler, array $required): array
    {
        $csrfInputs = $crawler->filterXPath('//input[@name="csrf_token_form"]');
        $this->assertGreaterThan(0, $csrfInputs->count(), 'csrf_token_form input not found');
        $csrf = (string) $csrfInputs->first()->attr('value');
        $this->assertNotSame('', $csrf, 'csrf_token_form value is empty');

        $forms = $crawler->filterXPath('//form[@id="userLogin"]');
        $this->assertGreaterThan(0, $forms->count(), 'no <form id="userLogin"> on page');
        $action = (string) $forms->first()->attr('action');
        $this->assertNotSame('', $action, 'form action is empty');

        foreach ($required as $name) {
            $matches = $forms->first()->filterXPath(
                './/input[@name="' . $name . '"]'
                . ' | .//button[@name="' . $name . '"]'
                . ' | .//select[@name="' . $name . '"]'
                . ' | .//textarea[@name="' . $name . '"]'
            );
            $this->assertGreaterThan(0, $matches->count(), 'form missing control: ' . $name);
        }
        return [$csrf, $action];
    }

    private function buildClient(): Client
    {
        return new Client([
            'verify' => false,
            'http_errors' => false,
            'cookies' => new CookieJar(),
            'allow_redirects' => [
                'max' => 10,
                'strict' => true,
                'referer' => true,
                'protocols' => ['http', 'https'],
            ],
            'timeout' => 15,
        ]);
    }
}
