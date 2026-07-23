<?php

/**
 * Full OIDC RP-initiated logout end-to-end test
 *
 * Exercises: DCR client registration → OAuth login → consent → authorization
 * code exchange → logout with the real signed id_token. Verifies the logout
 * endpoint redirects to the client's registered post_logout_redirect_uri with
 * the state parameter preserved.
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
use Symfony\Component\DomCrawler\Crawler;

class AuthorizationLogoutFullFlowTest extends TestCase
{
    private const REDIRECT_URI = 'https://client.example/cb';
    private const LOGOUT_URI = 'https://client.example/logged-out';
    private const STATE = 'testflow-state-abc';
    private const NONCE = 'testflow-nonce-xyz';

    private string $baseUrl;
    private ?string $originalSiteAddrOath = null;
    private ?string $clientId = null;

    protected function setUp(): void
    {
        $this->baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';

        $current = QueryUtils::querySingleRow(
            'SELECT gl_value FROM `globals` WHERE gl_name = ?',
            ['site_addr_oath']
        );
        $glValue = is_array($current) ? ($current['gl_value'] ?? null) : null;
        $this->originalSiteAddrOath = is_string($glValue) ? $glValue : null;

        if ($this->originalSiteAddrOath !== $this->baseUrl) {
            QueryUtils::sqlStatementThrowException(
                'UPDATE `globals` SET gl_value = ? WHERE gl_name = ?',
                [$this->baseUrl, 'site_addr_oath']
            );
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
        } finally {
            if ($this->originalSiteAddrOath !== null && $this->originalSiteAddrOath !== $this->baseUrl) {
                QueryUtils::sqlStatementThrowException(
                    'UPDATE `globals` SET gl_value = ? WHERE gl_name = ?',
                    [$this->originalSiteAddrOath, 'site_addr_oath']
                );
            }
        }
    }

    #[Test]
    public function testFullGrantToLogoutFlow(): void
    {
        $http = $this->buildClient();

        $reg = $http->post($this->baseUrl . '/oauth2/default/registration', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'application_type' => 'private',
                'redirect_uris' => [self::REDIRECT_URI],
                'post_logout_redirect_uris' => [self::LOGOUT_URI],
                'client_name' => 'AuthorizationLogoutFullFlowTest',
                'token_endpoint_auth_method' => 'client_secret_post',
                'contacts' => ['e2e@test.example'],
                'scope' => 'openid fhirUser',
            ],
        ]);
        $this->assertSame(200, $reg->getStatusCode(), 'DCR registration should succeed');
        $clientData = json_decode((string) $reg->getBody(), true);
        $this->assertIsArray($clientData);
        $this->assertArrayHasKey('client_id', $clientData);
        $this->assertArrayHasKey('client_secret', $clientData);
        $this->assertIsString($clientData['client_id']);
        $this->assertIsString($clientData['client_secret']);
        $this->clientId = $clientData['client_id'];
        $clientSecret = $clientData['client_secret'];

        $authUrl = '/oauth2/default/authorize?' . http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => self::REDIRECT_URI,
            'response_type' => 'code',
            'scope' => 'openid fhirUser',
            'state' => self::STATE,
            'nonce' => self::NONCE,
        ]);
        $loginPage = $http->get($this->baseUrl . $authUrl);
        $this->assertSame(200, $loginPage->getStatusCode(), 'Authorize should redirect to login page');
        [$loginCsrf, $loginAction] = $this->parseLoginForm(
            new Crawler((string) $loginPage->getBody()),
            'login page'
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
        $consentCrawler = new Crawler((string) $postLogin->getBody());
        $this->assertGreaterThan(
            0,
            $consentCrawler->filterXPath('//*[@name="proceed"]')->count(),
            'After login, the consent page (with proceed button) should render'
        );
        [$consentCsrf, $consentAction] = $this->parseLoginForm($consentCrawler, 'consent page');

        $postConsent = $http->post($this->baseUrl . $consentAction, [
            'form_params' => [
                'csrf_token_form' => $consentCsrf,
                'proceed' => '1',
                'scope' => ['openid' => 'openid', 'fhirUser' => 'fhirUser'],
            ],
            'allow_redirects' => false,
        ]);
        $this->assertSame(302, $postConsent->getStatusCode(), 'Consent POST should redirect back to client');
        $callbackUrl = $postConsent->getHeaderLine('Location');
        $this->assertStringStartsWith(self::REDIRECT_URI, $callbackUrl, 'Callback should be at registered redirect_uri');
        $callbackQueryString = parse_url($callbackUrl, PHP_URL_QUERY);
        $this->assertIsString($callbackQueryString);
        parse_str($callbackQueryString, $callbackQuery);
        $this->assertArrayHasKey('code', $callbackQuery, 'Callback URL should contain authorization code');
        $this->assertSame(self::STATE, $callbackQuery['state'] ?? '', 'Callback should preserve state');
        $this->assertIsString($callbackQuery['code']);
        $code = $callbackQuery['code'];

        $tokenResp = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => self::REDIRECT_URI,
                'client_id' => $this->clientId,
                'client_secret' => $clientSecret,
            ],
        ]);
        $this->assertSame(200, $tokenResp->getStatusCode(), 'Token exchange should succeed');
        $tokens = json_decode((string) $tokenResp->getBody(), true);
        $this->assertIsArray($tokens);
        $this->assertArrayHasKey('id_token', $tokens, 'Token response should include id_token when openid scope is granted');
        $this->assertIsString($tokens['id_token']);
        $idToken = $tokens['id_token'];

        $tokenParts = explode('.', $idToken);
        $this->assertCount(3, $tokenParts, 'id_token should be a JWT with three segments');
        $payload = json_decode((string) base64_decode(strtr($tokenParts[1], '-_', '+/'), true), true);
        $this->assertIsArray($payload);
        $this->assertSame($this->clientId, $payload['aud'] ?? '', 'id_token aud should match client_id');
        $this->assertSame(self::NONCE, $payload['nonce'] ?? '', 'id_token nonce should match the value sent to /authorize');

        $logoutResp = $http->get($this->baseUrl . '/oauth2/default/logout?' . http_build_query([
            'id_token_hint' => $idToken,
            'post_logout_redirect_uri' => self::LOGOUT_URI,
            'state' => self::STATE,
        ]), [
            'allow_redirects' => false,
        ]);
        $this->assertSame(307, $logoutResp->getStatusCode(), 'Logout with valid id_token + registered URI should redirect');
        $this->assertSame(
            self::LOGOUT_URI . '?state=' . rawurlencode(self::STATE),
            $logoutResp->getHeaderLine('Location'),
            'Logout Location should be the registered post_logout_redirect_uri with state preserved'
        );
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

    /**
     * @return array{string, string} [csrf_token_form value, form action URL]
     */
    private function parseLoginForm(Crawler $crawler, string $where): array
    {
        $csrfInputs = $crawler->filterXPath('//input[@name="csrf_token_form"]');
        $this->assertGreaterThan(0, $csrfInputs->count(), "No csrf_token_form input found on $where");
        $csrf = (string) $csrfInputs->first()->attr('value');
        $this->assertNotSame('', $csrf, "Empty csrf_token_form value on $where");

        $forms = $crawler->filterXPath('//form[@id="userLogin"]');
        $this->assertGreaterThan(0, $forms->count(), "No <form id=\"userLogin\"> on $where");
        $action = (string) $forms->first()->attr('action');
        $this->assertNotSame('', $action, "Empty form action on $where");

        return [$csrf, $action];
    }
}
