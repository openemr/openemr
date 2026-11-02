<?php

/**
 * OAuth2 login-form CSRF token verification.
 *
 * AuthorizationController::userLogin (src/RestControllers/AuthorizationController.php:931)
 * verifies the csrf_token_form field the login page emitted, and re-renders
 * the login form with an "Invalid CSRF" message if verification fails.
 * Without this test, a regression that dropped the CSRF check on the
 * OAuth login boundary would surface as a green test suite.
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

class OAuthLoginCsrfRejectionTest extends TestCase
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
    public function testOAuthLoginPostWithWrongCsrfTokenIsRejected(): void
    {
        $http = $this->buildClient();
        $clientId = $this->registerConfidentialClient($http);
        $this->clientId = $clientId;

        // GET the login form for a real /authorize flow.
        $authUrl = '/oauth2/default/authorize?' . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => self::REDIRECT_URI,
            'response_type' => 'code',
            'scope' => 'openid api:oemr',
            'state' => 'csrf-state',
        ]);
        $loginPage = $http->get($this->baseUrl . $authUrl);
        $this->assertSame(200, $loginPage->getStatusCode());

        $crawler = new Crawler((string) $loginPage->getBody());
        $forms = $crawler->filterXPath('//form[@id="userLogin"]');
        $this->assertGreaterThan(0, $forms->count(), 'login form should render');
        $action = (string) $forms->first()->attr('action');
        $this->assertNotSame('', $action);

        // POST to the login action with an OBVIOUSLY invalid csrf_token_form
        // value. Server should re-render the login form with an "Invalid
        // CSRF" indicator rather than proceeding to consent.
        $postLogin = $http->post($this->baseUrl . $action, [
            'form_params' => [
                'csrf_token_form' => 'this-is-not-a-real-csrf-token',
                'username' => 'admin',
                'password' => 'pass',
                'email' => '',
                'persist_login' => '0',
                'user_role' => 'api',
            ],
            'allow_redirects' => false,
        ]);
        $this->assertSame(
            200,
            $postLogin->getStatusCode(),
            'CSRF rejection re-renders the login form (200), it does not 302 to consent'
        );
        $body = (string) $postLogin->getBody();
        // Two positive signals:
        //  a) the "Invalid CSRF" flash is in the rendered form, or
        //  b) the response is another instance of the login form (not
        //     the consent page). Consent has proceed button; login has
        //     the username input.
        $rerenderedLoginForm = (new Crawler($body))
            ->filterXPath('//form[@id="userLogin"]')
            ->count() > 0;
        $consentButton = (new Crawler($body))
            ->filterXPath('//*[@name="proceed"]')
            ->count();
        $this->assertGreaterThan(
            0,
            (int) $rerenderedLoginForm,
            'Response should re-render the login form after CSRF rejection'
        );
        $this->assertSame(
            0,
            $consentButton,
            'CSRF rejection must not advance to the consent page. '
                . 'Consent-page proceed button was present in the response.'
        );
    }

    #[Test]
    public function testOAuthLoginPostWithMissingCsrfTokenIsRejected(): void
    {
        // The sibling test above sends a bogus csrf_token_form value. This
        // test exercises the "field omitted entirely" shape — ensures the
        // guard handles missing-key as rejection rather than falling
        // through to a null/empty comparison.
        $http = $this->buildClient();
        $clientId = $this->registerConfidentialClient($http);
        $this->clientId = $clientId;

        $authUrl = '/oauth2/default/authorize?' . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => self::REDIRECT_URI,
            'response_type' => 'code',
            'scope' => 'openid api:oemr',
            'state' => 'csrf-missing-state',
        ]);
        $loginPage = $http->get($this->baseUrl . $authUrl);
        $this->assertSame(200, $loginPage->getStatusCode());
        $crawler = new Crawler((string) $loginPage->getBody());
        $action = (string) $crawler->filterXPath('//form[@id="userLogin"]')->first()->attr('action');
        $this->assertNotSame('', $action);

        $postLogin = $http->post($this->baseUrl . $action, [
            'form_params' => [
                // csrf_token_form intentionally omitted
                'username' => 'admin',
                'password' => 'pass',
                'email' => '',
                'persist_login' => '0',
                'user_role' => 'api',
            ],
            'allow_redirects' => false,
        ]);
        $this->assertSame(
            200,
            $postLogin->getStatusCode(),
            'Missing csrf_token_form must re-render login (200), not proceed to consent'
        );
        $body = (string) $postLogin->getBody();
        $rerenderedLoginForm = (new Crawler($body))
            ->filterXPath('//form[@id="userLogin"]')
            ->count() > 0;
        $consentButton = (new Crawler($body))
            ->filterXPath('//*[@name="proceed"]')
            ->count();
        $this->assertGreaterThan(
            0,
            (int) $rerenderedLoginForm,
            'Response should re-render the login form after missing-CSRF rejection '
                . '(a generic error page would spuriously satisfy the consent-button check)'
        );
        $this->assertSame(
            0,
            $consentButton,
            'Missing CSRF token must not advance to consent page'
        );
    }

    #[Test]
    public function testOAuthLoginPostWithCsrfTokenFromDifferentSessionIsRejected(): void
    {
        // CsrfUtils builds tokens as hash_hmac over a per-session private
        // key. Session A's token must not satisfy Session B's verifier —
        // otherwise an attacker who captured a victim's csrf_token_form
        // in one session could reuse it in a parallel session. Lock the
        // session-binding invariant with a cross-session replay attempt.
        $httpA = $this->buildClient();
        $clientId = $this->registerConfidentialClient($httpA);
        $this->clientId = $clientId;

        // Session A: GET the login form and extract its csrf_token_form.
        $authUrl = '/oauth2/default/authorize?' . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => self::REDIRECT_URI,
            'response_type' => 'code',
            'scope' => 'openid api:oemr',
            'state' => 'session-A-state',
        ]);
        $loginPageA = $httpA->get($this->baseUrl . $authUrl);
        $this->assertSame(200, $loginPageA->getStatusCode());
        $crawlerA = new Crawler((string) $loginPageA->getBody());
        $tokenA = (string) $crawlerA
            ->filterXPath('//form[@id="userLogin"]//input[@name="csrf_token_form"]')
            ->first()
            ->attr('value');
        $this->assertNotSame('', $tokenA, 'Session A should expose a csrf_token_form');

        // Session B: fresh cookie jar, fresh /authorize GET, so Session B
        // has its own private key independent of A.
        $httpB = $this->buildClient();
        $loginPageB = $httpB->get($this->baseUrl . $authUrl);
        $this->assertSame(200, $loginPageB->getStatusCode());
        $crawlerB = new Crawler((string) $loginPageB->getBody());
        $action = (string) $crawlerB
            ->filterXPath('//form[@id="userLogin"]')
            ->first()
            ->attr('action');
        $this->assertNotSame('', $action);

        // Submit Session A's token through Session B's login form.
        $postB = $httpB->post($this->baseUrl . $action, [
            'form_params' => [
                'csrf_token_form' => $tokenA,
                'username' => 'admin',
                'password' => 'pass',
                'email' => '',
                'persist_login' => '0',
                'user_role' => 'api',
            ],
            'allow_redirects' => false,
        ]);
        $this->assertSame(
            200,
            $postB->getStatusCode(),
            'Cross-session CSRF replay must re-render login (200), not advance to consent'
        );
        $body = (string) $postB->getBody();
        $rerenderedLoginForm = (new Crawler($body))
            ->filterXPath('//form[@id="userLogin"]')
            ->count() > 0;
        $consentButton = (new Crawler($body))
            ->filterXPath('//*[@name="proceed"]')
            ->count();
        $this->assertGreaterThan(
            0,
            (int) $rerenderedLoginForm,
            'Response should re-render the login form after cross-session CSRF replay'
        );
        $this->assertSame(
            0,
            $consentButton,
            "Cross-session CSRF replay must not advance to the consent page"
        );
        // CsrfUtils rejection renders "Sorry. Invalid CSRF!" into the
        // login template's `invalid` slot; the failed-authentication
        // path renders a different message. Asserting the CSRF-specific
        // string distinguishes the rejection reason — a regression that
        // ran verifyLogin() with Session A's token (and then failed
        // because the credentials path rejects by chance) would present
        // the auth-failure message instead and this assertion would
        // catch it.
        $this->assertStringContainsString(
            'Invalid CSRF',
            $body,
            'Response should carry the CSRF-specific rejection message '
                . '("Invalid CSRF"), not a failed-authentication message'
        );
    }

    private function registerConfidentialClient(Client $http): string
    {
        $reg = $http->post($this->baseUrl . '/oauth2/default/registration', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'application_type' => 'private',
                'redirect_uris' => [self::REDIRECT_URI],
                'client_name' => 'OAuthLoginCsrfRejectionTest-' . bin2hex(random_bytes(3)),
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
