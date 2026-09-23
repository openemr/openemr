<?php

/**
 * Authorization-code grant with a public client + PKCE.
 *
 * Public clients (application_type=web) don't hold a shared secret,
 * so PKCE (code_challenge/code_verifier) is the only credential
 * protecting the code→token exchange. Verifies:
 *
 *   1. A public client running the full flow WITH a valid PKCE
 *      code_verifier matching the /authorize code_challenge is
 *      accepted at /token (200).
 *   2. The same flow WITHOUT the code_verifier (attacker who stole
 *      the code but doesn't know the verifier) is rejected.
 *   3. The same flow with a WRONG code_verifier is rejected.
 *
 * This locks in the PKCE gate for public clients — SMART/ONC requires
 * PKCE for anything that doesn't have a shared secret, and a
 * regression that stopped enforcing verifier match would let anyone
 * who observed the code_challenge + auth code redeem the token.
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

class AuthorizationGrantPublicClientPkceTest extends TestCase
{
    private const REDIRECT_URI = 'https://client.example/cb';
    private const STATE = 'pkce-state-abc';
    private const NONCE = 'pkce-nonce-xyz';

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
                QueryUtils::sqlStatementThrowException(
                    'DELETE FROM `oauth_trusted_user` WHERE `client_id` = ?',
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
    public function testPublicClientWithCorrectPkceVerifierSucceeds(): void
    {
        $verifier = self::generatePkceVerifier();
        $challenge = self::deriveS256Challenge($verifier);
        $http = $this->buildClient();
        $code = $this->runFlowThroughConsent($http, $challenge);

        $tokenResp = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => self::REDIRECT_URI,
                'client_id' => (string) $this->clientId,
                'code_verifier' => $verifier,
            ],
        ]);
        $this->assertSame(
            200,
            $tokenResp->getStatusCode(),
            'Public client with correct PKCE code_verifier should succeed — body: '
                . (string) $tokenResp->getBody()
        );
        $tokens = json_decode((string) $tokenResp->getBody(), true);
        $this->assertIsArray($tokens);
        $this->assertArrayHasKey('access_token', $tokens);
        $this->assertIsString($tokens['access_token']);
        $this->assertNotSame('', $tokens['access_token']);
    }

    #[Test]
    public function testPublicClientMissingPkceVerifierIsRejected(): void
    {
        $verifier = self::generatePkceVerifier();
        $challenge = self::deriveS256Challenge($verifier);
        $http = $this->buildClient();
        $code = $this->runFlowThroughConsent($http, $challenge);

        $tokenResp = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => self::REDIRECT_URI,
                'client_id' => (string) $this->clientId,
                // NO code_verifier — attacker who stole the code but does
                // not have the verifier that generated the challenge.
            ],
        ]);
        $this->assertContains(
            $tokenResp->getStatusCode(),
            [400, 401],
            'Public client that omits code_verifier after PKCE was used at '
                . '/authorize must be rejected. 200 here means PKCE is not '
                . 'actually gating the token exchange for public clients — '
                . 'the code becomes a bearer credential.'
        );
    }

    #[Test]
    public function testPublicClientWrongPkceVerifierIsRejected(): void
    {
        $verifier = self::generatePkceVerifier();
        $challenge = self::deriveS256Challenge($verifier);
        $http = $this->buildClient();
        $code = $this->runFlowThroughConsent($http, $challenge);

        $tokenResp = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => self::REDIRECT_URI,
                'client_id' => (string) $this->clientId,
                'code_verifier' => self::generatePkceVerifier(), // different from the one whose challenge was posted
            ],
        ]);
        $this->assertContains(
            $tokenResp->getStatusCode(),
            [400, 401],
            'Public client presenting a code_verifier that does not hash to '
                . 'the /authorize code_challenge must be rejected'
        );
    }

    private function runFlowThroughConsent(Client $http, string $codeChallenge): string
    {
        // DCR a public client (application_type=web). OpenEMR returns
        // is_confidential=0 for these — no client_secret in the response.
        $reg = $http->post($this->baseUrl . '/oauth2/default/registration', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'application_type' => 'web',
                'redirect_uris' => [self::REDIRECT_URI],
                'client_name' => 'AuthorizationGrantPublicClientPkceTest-' . bin2hex(random_bytes(3)),
                // OpenEMR DCR restricts token_endpoint_auth_method to
                // client_secret_basic | client_secret_post | private_key_jwt
                // even for public clients — see AuthorizationController
                // registerClient() validation. application_type=web is
                // what flips the client to is_confidential=0 (no secret).
                'token_endpoint_auth_method' => 'client_secret_post',
                'contacts' => ['e2e@test.example'],
                'scope' => 'openid fhirUser',
                'grant_types' => ['authorization_code'],
            ],
        ]);
        $this->assertSame(200, $reg->getStatusCode(), 'DCR should succeed for public client');
        $clientData = json_decode((string) $reg->getBody(), true);
        $this->assertIsArray($clientData);
        $this->assertIsString($clientData['client_id']);
        $this->clientId = $clientData['client_id'];

        $authUrl = '/oauth2/default/authorize?' . http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => self::REDIRECT_URI,
            'response_type' => 'code',
            'scope' => 'openid fhirUser',
            'state' => self::STATE,
            'nonce' => self::NONCE,
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
        ]);
        $loginPage = $http->get($this->baseUrl . $authUrl);
        $this->assertSame(200, $loginPage->getStatusCode());
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
            'Consent page should render after login'
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
        $this->assertSame(302, $postConsent->getStatusCode(), 'Consent POST should 302 back to client');
        $callbackUrl = $postConsent->getHeaderLine('Location');
        $callbackQueryString = parse_url($callbackUrl, PHP_URL_QUERY);
        $this->assertIsString($callbackQueryString);
        parse_str($callbackQueryString, $callbackQuery);
        $this->assertArrayHasKey('code', $callbackQuery);
        $this->assertIsString($callbackQuery['code']);
        return $callbackQuery['code'];
    }

    /**
     * Generate a spec-conforming code_verifier (43-128 chars from the
     * unreserved URI set — [A-Z] [a-z] [0-9] "-" "." "_" "~").
     */
    private static function generatePkceVerifier(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }

    /**
     * S256 challenge = base64url(sha256(verifier)) with padding stripped.
     */
    private static function deriveS256Challenge(string $verifier): string
    {
        return rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
    }

    /**
     * @return array{string, string}
     */
    private function parseLoginForm(Crawler $crawler, string $where): array
    {
        $csrfInputs = $crawler->filterXPath('//input[@name="csrf_token_form"]');
        $this->assertGreaterThan(0, $csrfInputs->count(), "No csrf_token_form on {$where}");
        $csrf = (string) $csrfInputs->first()->attr('value');
        $forms = $crawler->filterXPath('//form[@id="userLogin"]');
        $this->assertGreaterThan(0, $forms->count(), "No login form on {$where}");
        $action = (string) $forms->first()->attr('action');
        return [$csrf, $action];
    }

    private function buildClient(): Client
    {
        return new Client([
            'verify' => false,
            'http_errors' => false,
            'cookies' => new CookieJar(),
            'allow_redirects' => ['max' => 10, 'strict' => true, 'referer' => true, 'protocols' => ['http', 'https']],
            'timeout' => 15,
        ]);
    }
}
