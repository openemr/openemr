<?php

/**
 * OAuth2 login-form user_role whitelist.
 *
 * AuthorizationController::userLogin (src/RestControllers/AuthorizationController.php)
 * rejects any user_role outside {'api', 'portal-api'} before running the
 * MFA branches — otherwise a login POST with an unexpected user_role value
 * would authenticate on password alone (AuthUtils' `otherAuth` mode
 * accepts the credentials) and fall through to consent without the MFA
 * gate firing, since every MFA block was keyed on `user_role === 'api'`.
 *
 * Shape of these tests mirrors OAuthLoginCsrfRejectionTest's post-login
 * form re-render assertion: a rejected submission comes back as the
 * login form (200 with #userLogin), not as the consent page (no
 * proceed button).
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

class OAuthLoginUserRoleWhitelistTest extends TestCase
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
    public function testOAuthLoginPostWithUnexpectedUserRoleIsRejected(): void
    {
        // The login form server-renders user_role as 'api' or 'portal-api'.
        // A POST carrying any other value (e.g. 'login' — AuthUtils'
        // own name for the web-login mode) must not reach the consent
        // page, otherwise it authenticates against users_secure via
        // the otherAuth branch and bypasses the user_role === 'api'
        // MFA gates in userLogin().
        [$http, $action, $csrf] = $this->primeLoginForm();

        $postLogin = $http->post($this->baseUrl . $action, [
            'form_params' => [
                'csrf_token_form' => $csrf,
                'username' => 'admin',
                'password' => 'pass',
                'email' => '',
                'persist_login' => '0',
                'user_role' => 'login',
            ],
            'allow_redirects' => false,
        ]);
        $this->assertSame(
            200,
            $postLogin->getStatusCode(),
            'Unexpected user_role must re-render login (200), not 302 to consent'
        );
        $this->assertLoginReRendered(
            (string) $postLogin->getBody(),
            'Unexpected user_role must land back on the login form'
        );
    }

    #[Test]
    public function testOAuthLoginPostWithEmptyUserRoleIsRejected(): void
    {
        // Defensive variant: user_role present but empty. Empty string
        // is not in the whitelist either, so this must reject the same
        // way the unexpected-value test does. Guards against a future
        // refactor that gates on `!empty($user_role)` instead of
        // explicit allowlist membership.
        [$http, $action, $csrf] = $this->primeLoginForm();

        $postLogin = $http->post($this->baseUrl . $action, [
            'form_params' => [
                'csrf_token_form' => $csrf,
                'username' => 'admin',
                'password' => 'pass',
                'email' => '',
                'persist_login' => '0',
                'user_role' => '',
            ],
            'allow_redirects' => false,
        ]);
        $this->assertSame(
            200,
            $postLogin->getStatusCode(),
            'Empty user_role must re-render login (200)'
        );
        $this->assertLoginReRendered(
            (string) $postLogin->getBody(),
            'Empty user_role must land back on the login form'
        );
    }

    /**
     * Shared setup: register a fresh client, GET /authorize to land on
     * the login form, extract the form action + csrf_token_form value.
     *
     * @return array{Client, string, string} [http, formAction, csrfToken]
     */
    private function primeLoginForm(): array
    {
        $http = $this->buildClient();
        $clientId = $this->registerConfidentialClient($http);
        $this->clientId = $clientId;

        $authUrl = '/oauth2/default/authorize?' . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => self::REDIRECT_URI,
            'response_type' => 'code',
            'scope' => 'openid api:oemr',
            'state' => 'user-role-whitelist-state',
        ]);
        $loginPage = $http->get($this->baseUrl . $authUrl);
        $this->assertSame(200, $loginPage->getStatusCode());

        $crawler = new Crawler((string) $loginPage->getBody());
        $form = $crawler->filterXPath('//form[@id="userLogin"]')->first();
        $this->assertGreaterThan(0, $form->count(), 'login form should render');
        $action = (string) $form->attr('action');
        $this->assertNotSame('', $action);

        $csrf = (string) $crawler
            ->filterXPath('//form[@id="userLogin"]//input[@name="csrf_token_form"]')
            ->first()
            ->attr('value');
        $this->assertNotSame('', $csrf, 'login form should carry a csrf_token_form');

        return [$http, $action, $csrf];
    }

    private function assertLoginReRendered(string $body, string $message): void
    {
        $rerenderedLoginForm = (new Crawler($body))
            ->filterXPath('//form[@id="userLogin"]')
            ->count() > 0;
        $consentButton = (new Crawler($body))
            ->filterXPath('//*[@name="proceed"]')
            ->count();
        $this->assertGreaterThan(
            0,
            (int) $rerenderedLoginForm,
            $message . ' — response should re-render the login form'
        );
        $this->assertSame(
            0,
            $consentButton,
            $message . ' — response must not carry the consent proceed control'
        );
    }

    private function registerConfidentialClient(Client $http): string
    {
        $reg = $http->post($this->baseUrl . '/oauth2/default/registration', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'application_type' => 'private',
                'redirect_uris' => [self::REDIRECT_URI],
                'client_name' => 'OAuthLoginUserRoleWhitelistTest-' . bin2hex(random_bytes(3)),
                'token_endpoint_auth_method' => 'client_secret_post',
                'contacts' => ['e2e@test.example'],
                'scope' => 'openid api:oemr',
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
