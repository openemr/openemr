<?php

/**
 * PKCE deep-coverage regression gates (RFC 7636).
 *
 * `AuthorizationGrantPublicClientPkceTest` + `PublicClientPkceMintTest`
 * cover the mint-level happy path and three deny paths (missing
 * verifier, wrong verifier, challenge-as-verifier under S256). This
 * file widens the matrix along four axes RFC 7636 enumerates but the
 * existing tests do not yet lock:
 *
 *   1. Plain code_challenge_method at /authorize — SMART forbids it
 *      and OpenEMR's `CustomAuthCodeGrant::validateCodeChallengeMethod`
 *      allows only S256; the submitted-at-authorize `plain` must be
 *      rejected before any code is issued.
 *
 *   2. Code_challenge submitted with no explicit method — league's
 *      default is `plain`, so the override's S256-only enforcement must
 *      reject the implicit-plain shape too.
 *
 *   3. Malformed challenge (RFC 7636 §4.2: 43-128 chars from the
 *      unreserved URL-safe base64 alphabet) — league's regex gate must
 *      reject syntactically-invalid challenges at /authorize.
 *
 *   4. Verifier-reuse across public clients — code is bound to the
 *      client that initiated the flow (RFC 6749 §4.1.3). Presenting
 *      client A's code + verifier at client B's token endpoint must
 *      fail even though both are public clients with valid PKCE
 *      material of their own.
 *
 *   5. Refresh grant must not require the PKCE verifier — RFC 7636
 *      scopes PKCE to the auth-code → token exchange only; refreshing
 *      an access token must succeed without resubmitting the verifier.
 *
 * These are regression gates: a future change that silently relaxed
 * any of them would surface here.
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
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Tests\Support\OAuth2\AuthCodeFlow;
use OpenEMR\Tests\Support\OAuth2\DcrClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DomCrawler\Crawler;

class PkceDeepCoverageApiTest extends TestCase
{
    private const REDIRECT_URI = 'https://client.example/cb';
    private const SCOPE = 'openid patient/Patient.read';
    private const STATE = 'pkce-deep-state-abc';
    private const NONCE = 'pkce-deep-nonce-xyz';

    private string $baseUrl;

    private bool $siteAddrOathCaptured = false;
    private ?string $originalSiteAddrOathGlobalRow = null;
    private bool $siteAddrOathGlobalRowWasInserted = false;

    /** @var list<string> */
    private array $clientsToRemove = [];
    /** @var list<string> */
    private array $tearDownFailures = [];

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
        if (stripos($probe->getHeaderLine('Server'), 'nginx') !== false) {
            $this->markTestSkipped('PKCE flow tests skipped on nginx config');
        }

        try {
            $this->siteAddrOathCaptured = true;
            $this->persistGlobal(
                'site_addr_oath',
                $this->baseUrl,
                $this->originalSiteAddrOathGlobalRow,
                $this->siteAddrOathGlobalRowWasInserted
            );
        } catch (\Throwable $setUpFailure) {
            try {
                $this->tearDown();
            } catch (\Throwable $rollbackFailure) {
                throw new \RuntimeException(
                    'setUp failed; rollback via tearDown also failed: ' . $rollbackFailure->getMessage(),
                    0,
                    $setUpFailure
                );
            }
            throw $setUpFailure;
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->clientsToRemove as $cid) {
            $this->safeRun(function () use ($cid): void {
                DcrClient::remove($cid);
                QueryUtils::sqlStatementThrowException(
                    'DELETE FROM `oauth_trusted_user` WHERE `client_id` = ?',
                    [$cid]
                );
            });
        }
        $this->safeRun(function (): void {
            if (!$this->siteAddrOathCaptured) {
                return;
            }
            $this->restoreGlobal(
                'site_addr_oath',
                $this->originalSiteAddrOathGlobalRow,
                $this->siteAddrOathGlobalRowWasInserted
            );
        });

        if ($this->tearDownFailures !== []) {
            $failures = $this->tearDownFailures;
            $this->tearDownFailures = [];
            self::fail(
                "tearDown cleanup had " . count($failures) . " failure(s); "
                . "shared state may be stale for later tests:\n  - "
                . implode("\n  - ", $failures)
            );
        }
    }

    #[Test]
    public function testPlainMethodAtAuthorizeIsRejected(): void
    {
        // SMART-on-FHIR forbids `plain` and OpenEMR's
        // CustomAuthCodeGrant::validateCodeChallengeMethod only allows
        // S256. Explicitly submitting `code_challenge_method=plain`
        // with a plain challenge value must be rejected at /authorize
        // before any code is issued. Distinct from the
        // "challenge-value-as-verifier" test in
        // AuthorizationGrantPublicClientPkceTest — that one registers
        // S256 at authorize and submits a plain-shaped verifier at
        // /token; this one tries to register plain at /authorize
        // outright.
        $clientId = $this->registerPublicClient('PkceDeepCoverage-plain-method');
        $this->clientsToRemove[] = $clientId;

        $plainChallenge = AuthCodeFlow::generatePkceVerifier();
        $http = $this->buildClient();
        $resp = $http->get($this->baseUrl . '/oauth2/default/authorize?' . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => self::REDIRECT_URI,
            'response_type' => 'code',
            'scope' => self::SCOPE,
            'state' => self::STATE,
            'nonce' => self::NONCE,
            'code_challenge' => $plainChallenge,
            'code_challenge_method' => 'plain',
        ]), ['allow_redirects' => false]);

        $this->assertRejectedAtAuthorize(
            $resp,
            'code_challenge_method=plain must be rejected at /authorize per '
                . 'SMART + OpenEMR override — only S256 is accepted.'
        );
    }

    #[Test]
    public function testMissingChallengeMethodDefaultsToPlainAndIsRejected(): void
    {
        // league/oauth2-server defaults an omitted code_challenge_method
        // to `plain` (AuthCodeGrant line 314). OpenEMR's override reads
        // that default and rejects it — same S256-only enforcement.
        // Lock that the default resolution is `plain` (not S256), so a
        // future change that silently flipped the default to S256
        // (which would make this test pass with a 302) would surface
        // and prompt a review.
        $clientId = $this->registerPublicClient('PkceDeepCoverage-no-method');
        $this->clientsToRemove[] = $clientId;

        $verifier = AuthCodeFlow::generatePkceVerifier();
        $challenge = AuthCodeFlow::deriveS256Challenge($verifier);
        $http = $this->buildClient();
        $resp = $http->get($this->baseUrl . '/oauth2/default/authorize?' . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => self::REDIRECT_URI,
            'response_type' => 'code',
            'scope' => self::SCOPE,
            'state' => self::STATE,
            'nonce' => self::NONCE,
            'code_challenge' => $challenge,
            // code_challenge_method omitted on purpose
        ]), ['allow_redirects' => false]);

        $this->assertRejectedAtAuthorize(
            $resp,
            'code_challenge submitted without explicit code_challenge_method '
                . 'defaults to plain (per league) and must be rejected per '
                . 'OpenEMR S256-only override.'
        );
    }

    #[Test]
    public function testMalformedChallengeAtAuthorizeIsRejected(): void
    {
        // RFC 7636 §4.2: code_challenge must be 43-128 chars from the
        // unreserved URL-safe set [A-Za-z0-9-._~]. league's regex
        // (AuthCodeGrant line 330) enforces this at /authorize. Submit
        // a too-short challenge; must reject with invalid_request on
        // the `code_challenge` field.
        $clientId = $this->registerPublicClient('PkceDeepCoverage-malformed');
        $this->clientsToRemove[] = $clientId;

        $http = $this->buildClient();
        $resp = $http->get($this->baseUrl . '/oauth2/default/authorize?' . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => self::REDIRECT_URI,
            'response_type' => 'code',
            'scope' => self::SCOPE,
            'state' => self::STATE,
            'nonce' => self::NONCE,
            // 20 chars — under the 43-char minimum.
            'code_challenge' => 'short-not-43-chars-x',
            'code_challenge_method' => 'S256',
        ]), ['allow_redirects' => false]);

        $this->assertRejectedAtAuthorize(
            $resp,
            'Malformed code_challenge (shorter than 43 chars) must be rejected '
                . 'per RFC 7636 §4.2 syntax rule enforced by league.'
        );
    }

    #[Test]
    public function testVerifierReuseAcrossPublicClientsIsRejected(): void
    {
        // Two separate public clients: originator + receiver. Each
        // has its own PKCE material. The originator obtains a code
        // via its own flow; an attacker then presents the originator's
        // code + the originator's verifier to the token endpoint using
        // the receiver's client_id. RFC 6749 §4.1.3 binds the
        // authorization code to the client it was issued to, so the
        // exchange must fail even if the receiver is a valid, enabled
        // public client with its own perfectly good PKCE material.
        //
        // Distinct from AuthorizationGrantFlowTest::
        // testTokenExchangeRejectsCodeFromDifferentClient which
        // exercises the CONFIDENTIAL-client leg (Basic-auth header,
        // client_secret). This test covers the PUBLIC-client leg: the
        // only credential presented is client_id + code_verifier —
        // no secret — so the server has to reject based on the code's
        // client-binding alone.
        $originatorClient = $this->registerPublicClient('PkceDeepCoverage-xreuse-orig');
        $this->clientsToRemove[] = $originatorClient;
        $receiverClient = $this->registerPublicClient('PkceDeepCoverage-xreuse-recv');
        $this->clientsToRemove[] = $receiverClient;

        $originatorVerifier = AuthCodeFlow::generatePkceVerifier();
        $originatorChallenge = AuthCodeFlow::deriveS256Challenge($originatorVerifier);
        $http = $this->buildClient();
        $originatorCode = $this->runFlowThroughConsent($http, $originatorClient, $originatorChallenge);

        // Exchange with the receiver's client_id, the originator's
        // code, and the originator's verifier.
        $tokenResp = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'code' => $originatorCode,
                'redirect_uri' => self::REDIRECT_URI,
                'client_id' => $receiverClient,
                'code_verifier' => $originatorVerifier,
            ],
        ]);
        $this->assertGreaterThanOrEqual(
            400,
            $tokenResp->getStatusCode(),
            'Receiver presenting the originator\'s code must be rejected '
                . '(code is client-bound). Body: ' . (string) $tokenResp->getBody()
        );
        $this->assertLessThan(
            500,
            $tokenResp->getStatusCode(),
            'Rejection must be a 4xx client-error, not a 5xx server fault. '
                . 'Body: ' . (string) $tokenResp->getBody()
        );
    }

    #[Test]
    public function testRefreshDoesNotRequirePkceVerifier(): void
    {
        // RFC 7636 §1.1: PKCE is defined for the authorization-code
        // → access-token exchange. The refresh-token grant has no
        // code_challenge/code_verifier semantics. Lock that OpenEMR's
        // refresh endpoint does NOT accidentally require (or silently
        // begin requiring) the verifier on refresh. A regression here
        // would break legitimate public-client sessions as soon as
        // their initial access token expired.
        //
        // Mint with an extra scope (patient/Encounter.read) that we
        // can drop on refresh. Mirrors PublicClientPkceMintTest::
        // testPublicClientScopeDowngradeOnRefreshIsHonored's pattern —
        // the §8.5 "nonce artifact" quirk means the same-set-refresh
        // path trips on the bound nonce pseudo-scope, so requesting a
        // narrower explicit scope on refresh is the shape that works.
        // The invariant this test locks is "no verifier required," not
        // "scope omission works" or "same-set works."
        $mintScope = 'openid offline_access patient/Patient.read patient/Encounter.read';
        $refreshScope = 'openid offline_access patient/Patient.read';
        $mint = AuthCodeFlow::mintUserRolePublicClient(
            $this->baseUrl,
            'PkceDeepCoverage-refresh-' . bin2hex(random_bytes(3)),
            $mintScope,
        );
        $this->clientsToRemove[] = $mint['client_id'];
        $refreshToken = $mint['response_body']['refresh_token'] ?? null;
        $this->assertIsString(
            $refreshToken,
            'Public-client mint with offline_access must return a refresh_token. '
                . 'Response body: ' . json_encode($mint['response_body'])
        );

        // Exchange refresh_token without any code_verifier. For a
        // public client this is the only shape the client can produce
        // on refresh — there is no client_secret and PKCE verifier
        // material is scoped to the exchanged auth code.
        $refreshed = AuthCodeFlow::exchangeRefreshToken(
            $this->baseUrl,
            $mint['client_id'],
            null,
            $refreshToken,
            $refreshScope,
        );
        $this->assertSame(
            200,
            $refreshed['status'],
            'Public-client refresh must succeed without a PKCE verifier — '
                . 'PKCE is scoped to the auth-code exchange, not refresh. '
                . 'Status: ' . $refreshed['status']
                . ', Body: ' . json_encode($refreshed['body'])
        );
        $this->assertIsString(
            $refreshed['body']['access_token'] ?? null,
            'Refresh response must carry a new access_token'
        );
    }

    /**
     * Assert the /authorize response rejected the request with a 4xx
     * client-error. OpenEMR surfaces authorize-level validation errors
     * two ways: a 400/401/403 response, or (less often) a 302 redirect
     * back to redirect_uri with error=invalid_request / error=invalid_grant
     * in the query string per RFC 6749 §4.1.2.1. Accept either shape.
     */
    private function assertRejectedAtAuthorize(
        \Psr\Http\Message\ResponseInterface $resp,
        string $message
    ): void {
        $status = $resp->getStatusCode();
        if ($status === 302) {
            $location = $resp->getHeaderLine('Location');
            $this->assertNotSame('', $location, 'Redirect must have a Location: ' . $message);
            $parts = parse_url($location);
            $this->assertIsArray($parts);
            if (isset($parts['query'])) {
                $query = [];
                parse_str((string) $parts['query'], $query);
                $this->assertArrayHasKey(
                    'error',
                    $query,
                    'Rejection redirect must carry error= parameter. Location: '
                        . $location . ' — ' . $message
                );
                return;
            }
            self::fail('Rejection redirect had no query string: ' . $location . ' — ' . $message);
        }
        $this->assertGreaterThanOrEqual(
            400,
            $status,
            $message . ' Status: ' . $status . ', Body: ' . substr((string) $resp->getBody(), 0, 400)
        );
        $this->assertLessThan(
            500,
            $status,
            'Rejection must be a 4xx client-error, not a 5xx. ' . $message
                . ' Status: ' . $status . ', Body: ' . substr((string) $resp->getBody(), 0, 400)
        );
    }

    /**
     * Register a public (application_type=web) client with the
     * authorization_code grant. Caller is responsible for pushing the
     * client_id onto $clientsToRemove for teardown.
     */
    private function registerPublicClient(string $namePrefix): string
    {
        return DcrClient::registerPublic(
            $this->baseUrl,
            $namePrefix . '-' . bin2hex(random_bytes(3)),
            self::SCOPE,
            'authorization_code',
            self::REDIRECT_URI,
        );
    }

    /**
     * Drive the /authorize → login → consent flow for a specific
     * public client + challenge and return the authorization code.
     * Modelled on AuthorizationGrantPublicClientPkceTest::
     * runFlowThroughConsent but with an explicit client_id parameter
     * so cross-client tests can run two flows against two clients.
     */
    private function runFlowThroughConsent(Client $http, string $clientId, string $codeChallenge): string
    {
        $authParams = [
            'client_id' => $clientId,
            'redirect_uri' => self::REDIRECT_URI,
            'response_type' => 'code',
            'scope' => self::SCOPE,
            'state' => self::STATE,
            'nonce' => self::NONCE,
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
        ];
        $loginPage = $http->get($this->baseUrl . '/oauth2/default/authorize?' . http_build_query($authParams));
        $this->assertSame(200, $loginPage->getStatusCode());
        [$loginCsrf, $loginAction] = $this->parseForm(
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
        [$consentCsrf, $consentAction] = $this->parseForm($consentCrawler, 'consent page');
        $postConsent = $http->post($this->baseUrl . $consentAction, [
            'form_params' => [
                'csrf_token_form' => $consentCsrf,
                'proceed' => '1',
                'scope' => ['openid' => 'openid', 'patient/Patient.read' => 'patient/Patient.read'],
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
     * @return array{string, string}
     */
    private function parseForm(Crawler $crawler, string $where): array
    {
        $csrfInputs = $crawler->filterXPath('//input[@name="csrf_token_form"]');
        $this->assertGreaterThan(0, $csrfInputs->count(), "No csrf_token_form on {$where}");
        $csrf = (string) $csrfInputs->first()->attr('value');
        $forms = $crawler->filterXPath('//form[.//input[@name="csrf_token_form"]]');
        $this->assertGreaterThan(0, $forms->count(), "No form with csrf_token_form on {$where}");
        $action = (string) $forms->first()->attr('action');
        return [$csrf, $action];
    }

    private function persistGlobal(
        string $name,
        string $value,
        ?string &$originalCapture,
        bool &$insertedCapture
    ): void {
        $current = QueryUtils::querySingleRow(
            'SELECT gl_value FROM `globals` WHERE gl_name = ?',
            [$name]
        );
        if (is_array($current)) {
            $glValue = $current['gl_value'] ?? null;
            $originalCapture = is_string($glValue) ? $glValue : null;
            QueryUtils::sqlStatementThrowException(
                'UPDATE `globals` SET gl_value = ? WHERE gl_name = ?',
                [$value, $name]
            );
        } else {
            QueryUtils::sqlStatementThrowException(
                'INSERT INTO `globals` (`gl_name`, `gl_index`, `gl_value`) VALUES (?, 0, ?)',
                [$name, $value]
            );
            $insertedCapture = true;
        }
    }

    private function restoreGlobal(string $name, ?string $originalRow, bool $wasInserted): void
    {
        if ($wasInserted) {
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM `globals` WHERE gl_name = ?',
                [$name]
            );
        } elseif ($originalRow !== null) {
            QueryUtils::sqlStatementThrowException(
                'UPDATE `globals` SET gl_value = ? WHERE gl_name = ?',
                [$originalRow, $name]
            );
        }
    }

    private function safeRun(callable $cleanup): void
    {
        try {
            $cleanup();
        } catch (SqlQueryException $e) {
            $this->tearDownFailures[] = $e->getMessage();
            fwrite(STDERR, '[' . static::class . "::tearDown] cleanup step failed: " . $e->getMessage() . "\n");
        }
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
