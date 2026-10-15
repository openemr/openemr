<?php

/**
 * Authorization-code grant with private_key_jwt client authentication.
 *
 * Sibling of AuthorizationLogoutFullFlowTest that exercises the same
 * DCR → /authorize → login → consent → code → /token chain, but with
 * token_endpoint_auth_method=private_key_jwt instead of the traditional
 * client_secret_post transport. Locks in the ClientRepository +
 * CustomAuthCodeGrant contract for confidential clients that
 * authenticate at /token with a signed JWT client_assertion rather
 * than a shared secret — the SMART-on-FHIR "asymmetric client
 * authentication" pattern.
 *
 * This test guards against future regressions in the null-client_secret
 * pathway through ClientRepository::validateClient(). Nothing else in
 * the api suite exercises private_key_jwt on authorization_code —
 * AuthorizationLogoutFullFlowTest and AuthorizationGrantFlowTest both
 * use client_secret_post, and BulkAPITestClient uses JWT assertions
 * but only with client_credentials. That coverage gap previously
 * allowed a refactor to reject the null-secret call path at
 * ClientRepository::validateClient, which would have broken every
 * confidential client using private_key_jwt on authorization_code.
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
use Lcobucci\JWT\Signer\Key\InMemory;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Tools\OAuth2\ClientCredentialsAssertionGenerator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DomCrawler\Crawler;

class AuthorizationGrantJwtAssertionFlowTest extends TestCase
{
    private const REDIRECT_URI = 'https://client.example/cb';
    private const STATE = 'jwtflow-state-abc';
    private const NONCE = 'jwtflow-nonce-xyz';

    private string $baseUrl;
    private ?string $originalSiteAddrOath = null;
    private bool $siteAddrOathWasInserted = false;
    private ?string $clientId = null;

    protected function setUp(): void
    {
        $this->baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';

        // Match AuthorizationLogoutFullFlowTest's runner-probe logic: skip
        // only on runners that explicitly opt out of the Secure-cookie
        // OAuth flow; hard-fail on any other runner that silently drops
        // its Server header (would otherwise turn regressions into
        // green-skipped tests).
        if (getenv('OPENEMR_ALLOW_OAUTH_HTTPS_SKIP') === '1') {
            $this->markTestSkipped(
                'Skipping per OPENEMR_ALLOW_OAUTH_HTTPS_SKIP=1 — this runner is known to be'
                . ' unable to serve OpenEMR\'s Secure-cookie OAuth session (typically php -S over HTTP).'
            );
        }
        $probe = $this->buildClient()->get($this->baseUrl . '/');
        if ($probe->getHeaderLine('Server') === '') {
            $message = 'OAuth flow requires a webserver that carries session cookies with the Secure flag'
                . ' (Apache/nginx). Server did not respond with a Server header, so this looks like'
                . ' PHP\'s built-in webserver (php -S) or similar stripped-down setup.';
            if (getenv('CI') !== false) {
                self::fail($message . ' In CI this is a hard failure — a real webserver runner unexpectedly'
                    . ' stopped emitting the Server header, which would silently disable this test if we only'
                    . ' skipped. If this runner is intentionally limited, set OPENEMR_ALLOW_OAUTH_HTTPS_SKIP=1'
                    . ' in its workflow to opt out explicitly.');
            }
            $this->markTestSkipped($message);
        }

        // OAuth iss/aud validation requires site_addr_oath to match the
        // URL clients hit — same setup as AuthorizationLogoutFullFlowTest.
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
    public function testAuthCodeGrantWithJwtAssertionAuthentication(): void
    {
        $http = $this->buildClient();
        [$privateKey, $publicKey, $code] = $this->registerJwtClientAndObtainCode($http);

        // Token exchange with JWT client_assertion instead of client_secret.
        // The signed assertion carries iss=sub=client_id, aud=/token URL,
        // and short exp; JwtAuthenticationService validates the signature
        // against the client's registered JWKS. After that succeeds,
        // ClientRepository::validateClient($clientId, null,
        // 'authorization_code') is called — this test locks in that the
        // null-secret call returns true rather than rejecting the client.
        $assertion = ClientCredentialsAssertionGenerator::generateAssertion(
            $privateKey,
            $publicKey,
            $this->baseUrl . '/oauth2/default/token',
            (string) $this->clientId,
        );
        $tokenResp = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => self::REDIRECT_URI,
                'client_id' => $this->clientId,
                'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
                'client_assertion' => $assertion,
            ],
        ]);
        $this->assertSame(
            200,
            $tokenResp->getStatusCode(),
            'Token exchange with JWT client_assertion should succeed. '
            . '401 invalid_client typically means the JWT signature failed validation, '
            . 'the client was registered without a jwks, or ClientRepository::validateClient '
            . 'rejected the null client_secret after the JWT authenticated the client. '
            . '400 usually means the code was invalid/expired.'
        );

        $tokens = json_decode((string) $tokenResp->getBody(), true);
        $this->assertIsArray($tokens);
        $this->assertArrayHasKey('access_token', $tokens, 'Token response should include access_token');
        $this->assertIsString($tokens['access_token']);
        $this->assertNotSame('', $tokens['access_token'], 'access_token must not be empty');
        $this->assertArrayHasKey('id_token', $tokens, 'Token response should include id_token when openid scope is granted');
        $this->assertIsString($tokens['id_token']);
        // Prove the id_token was actually issued to this client (not e.g. a
        // stale token pulled from an earlier session with a different aud).
        $tokenParts = explode('.', $tokens['id_token']);
        $this->assertCount(3, $tokenParts, 'id_token should be a JWT with three segments');
        $payload = json_decode((string) base64_decode(strtr($tokenParts[1], '-_', '+/'), true), true);
        $this->assertIsArray($payload);
        $this->assertSame($this->clientId, $payload['aud'] ?? '', 'id_token aud should match client_id');
        $this->assertSame(self::NONCE, $payload['nonce'] ?? '', 'id_token nonce should match the value sent to /authorize');
    }

    #[Test]
    public function testAuthCodeGrantRejectsTamperedJwtAssertion(): void
    {
        // Companion to the success test: the /token endpoint must reject
        // a client_assertion whose signature does not verify against the
        // registered JWKS. Without this, JwtAuthenticationService could
        // silently accept any assertion-shaped string and confidential-
        // client identity via private_key_jwt would not actually be
        // enforced. Runs on every api-job PR (the sibling acceptance
        // test only runs on tests/Acceptance/**-touching PRs + nightly).
        $http = $this->buildClient();
        [$privateKey, $publicKey, $code] = $this->registerJwtClientAndObtainCode($http);

        $assertion = ClientCredentialsAssertionGenerator::generateAssertion(
            $privateKey,
            $publicKey,
            $this->baseUrl . '/oauth2/default/token',
            (string) $this->clientId,
        );
        // Replace the signature segment with a valid-base64url string of
        // the same length. 'A' decodes to six zero bits, so the segment
        // is well-formed base64url (server reaches the RSA verify step)
        // but decodes to all-zero bytes — a well-formed but definitely
        // wrong signature.
        $parts = explode('.', $assertion);
        $parts[2] = str_repeat('A', strlen($parts[2]));
        $tampered = implode('.', $parts);

        $tokenResp = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => self::REDIRECT_URI,
                'client_id' => $this->clientId,
                'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
                'client_assertion' => $tampered,
            ],
        ]);
        $this->assertContains(
            $tokenResp->getStatusCode(),
            [400, 401],
            'A JWT client_assertion whose signature does not verify against the '
                . 'registered JWKS must be rejected with 400 or 401 (League returns '
                . 'either depending on the specific rejection path — both are OAuth2 '
                . 'spec-conformant for invalid_client). 200 here means the JWT '
                . 'validation is not actually gating client authentication.'
        );
        $body = json_decode((string) $tokenResp->getBody(), true);
        $this->assertIsArray($body);
        $this->assertSame(
            'invalid_client',
            $body['error'] ?? null,
            'Rejection error code should be OAuth2 invalid_client'
        );
    }

    #[Test]
    public function testRefreshGrantRejectsTamperedJwtAssertion(): void
    {
        // Companion negative for the JWT refresh happy path below. The
        // auth_code path has testAuthCodeGrantRejectsTamperedJwtAssertion
        // — the refresh path with private_key_jwt goes through the same
        // JWTClientAuthenticationService::validateJWTClientAssertion but
        // enters via CustomRefreshTokenGrant::validateClient rather than
        // CustomAuthCodeGrant. Locks in that a tampered signature is
        // rejected on both entry points, not just auth_code.
        $http = $this->buildClient();
        [$privateKey, $publicKey, $code] = $this->registerJwtClientAndObtainCode($http);

        // Mint a refresh_token via the normal auth_code + JWT exchange.
        $codeAssertion = ClientCredentialsAssertionGenerator::generateAssertion(
            $privateKey,
            $publicKey,
            $this->baseUrl . '/oauth2/default/token',
            (string) $this->clientId,
        );
        $codeResp = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => self::REDIRECT_URI,
                'client_id' => $this->clientId,
                'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
                'client_assertion' => $codeAssertion,
            ],
        ]);
        $this->assertSame(200, $codeResp->getStatusCode(), 'auth_code exchange should succeed');
        $tokens = json_decode((string) $codeResp->getBody(), true);
        $this->assertIsArray($tokens);
        $this->assertArrayHasKey('refresh_token', $tokens);
        $this->assertIsString($tokens['refresh_token']);

        // Fresh assertion for the refresh attempt, then rewrite its
        // signature segment to a well-formed base64url string that
        // decodes to all-zero bytes — reaches the RSA verify step but
        // fails the check. Matches the shape used in
        // testAuthCodeGrantRejectsTamperedJwtAssertion.
        $refreshAssertion = ClientCredentialsAssertionGenerator::generateAssertion(
            $privateKey,
            $publicKey,
            $this->baseUrl . '/oauth2/default/token',
            (string) $this->clientId,
        );
        $parts = explode('.', $refreshAssertion);
        $parts[2] = str_repeat('A', strlen($parts[2]));
        $tampered = implode('.', $parts);

        $refreshResp = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => $tokens['refresh_token'],
                'scope' => 'openid fhirUser offline_access',
                'client_id' => $this->clientId,
                'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
                'client_assertion' => $tampered,
            ],
        ]);
        $this->assertContains(
            $refreshResp->getStatusCode(),
            [400, 401],
            'Refresh with a JWT client_assertion whose signature does not verify against '
                . 'the registered JWKS must be rejected. Body: ' . (string) $refreshResp->getBody()
        );
        $body = json_decode((string) $refreshResp->getBody(), true);
        $this->assertIsArray($body);
        $this->assertSame(
            'invalid_client',
            $body['error'] ?? null,
            'Rejection error code should be OAuth2 invalid_client'
        );
    }

    #[Test]
    public function testRefreshGrantSucceedsWithValidJwtAssertion(): void
    {
        // Regression pin for the refresh-grant path with private_key_jwt.
        // CustomRefreshTokenGrant overrides respondToAccessTokenRequest
        // to do its own validateClient early, then delegates to the
        // League parent which validates the client again. With JWT
        // client authentication the assertion carries a one-time JTI —
        // if validateJWTClientAssertion ran twice for the same request,
        // the second call rejected the JTI as replay and returned
        // 401 invalid_client on every JWT-authenticated refresh. The
        // grant now memoizes the ClientEntity by spl_object_id of the
        // request so the second internal call short-circuits.
        //
        // This test also exercises a second refresh from the same
        // client with a fresh assertion, to prove the memo is scoped
        // to one request object and doesn't leak across the process.
        $http = $this->buildClient();
        [$privateKey, $publicKey, $code] = $this->registerJwtClientAndObtainCode($http);

        // Initial auth_code → tokens exchange using a JWT assertion,
        // as the existing testAuthCodeGrantSucceedsWithValidJwtAssertion
        // covers. The response should include a refresh_token because
        // offline_access is in the granted scope.
        $codeAssertion = ClientCredentialsAssertionGenerator::generateAssertion(
            $privateKey,
            $publicKey,
            $this->baseUrl . '/oauth2/default/token',
            (string) $this->clientId,
        );
        $codeResp = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => self::REDIRECT_URI,
                'client_id' => $this->clientId,
                'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
                'client_assertion' => $codeAssertion,
            ],
        ]);
        $this->assertSame(200, $codeResp->getStatusCode(), 'auth_code exchange should succeed');
        $tokens = json_decode((string) $codeResp->getBody(), true);
        $this->assertIsArray($tokens);
        $this->assertArrayHasKey('refresh_token', $tokens, 'refresh_token expected since offline_access was granted');
        $this->assertIsString($tokens['refresh_token']);
        $refreshToken = $tokens['refresh_token'];

        // First JWT-authenticated refresh. Under the pre-memo code
        // path this returned 401 invalid_client because the JWT
        // assertion's JTI was consumed by the first internal
        // validateClient call and rejected by the second.
        $refreshAssertion1 = ClientCredentialsAssertionGenerator::generateAssertion(
            $privateKey,
            $publicKey,
            $this->baseUrl . '/oauth2/default/token',
            (string) $this->clientId,
        );
        $refreshResp = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
                'scope' => 'openid fhirUser offline_access',
                'client_id' => $this->clientId,
                'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
                'client_assertion' => $refreshAssertion1,
            ],
        ]);
        $this->assertSame(
            200,
            $refreshResp->getStatusCode(),
            'Refresh with JWT client_assertion should succeed. 401 invalid_client here typically means '
            . 'validateClient was called twice for the same request and the JWT JTI dedupe rejected the second call.'
        );
        $refreshed = json_decode((string) $refreshResp->getBody(), true);
        $this->assertIsArray($refreshed);
        $this->assertArrayHasKey('access_token', $refreshed);
        $this->assertIsString($refreshed['access_token']);
        $this->assertNotSame(
            $tokens['access_token'],
            $refreshed['access_token'],
            'Refreshed access_token should be a new token, not the original'
        );
        $this->assertArrayHasKey('refresh_token', $refreshed);
        $this->assertIsString($refreshed['refresh_token']);
        $nextRefreshToken = $refreshed['refresh_token'];

        // Second refresh from the same client, brand-new JWT
        // assertion (fresh JTI), rotated refresh_token from the
        // previous response. Proves the memo is per-request and
        // doesn't leak across the process.
        $refreshAssertion2 = ClientCredentialsAssertionGenerator::generateAssertion(
            $privateKey,
            $publicKey,
            $this->baseUrl . '/oauth2/default/token',
            (string) $this->clientId,
        );
        $refreshResp2 = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => $nextRefreshToken,
                'scope' => 'openid fhirUser offline_access',
                'client_id' => $this->clientId,
                'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
                'client_assertion' => $refreshAssertion2,
            ],
        ]);
        $this->assertSame(
            200,
            $refreshResp2->getStatusCode(),
            'A second refresh from the same JWT client should also succeed. '
            . 'Failure here would suggest the validateClient memo is leaking across requests.'
        );
    }

    /**
     * Shared DCR + /authorize + login + consent → code path. Returns
     * [$privateKey, $publicKey, $code] so both tests can then do their
     * own /token exchange with whatever client_assertion they want.
     * Sets $this->clientId as a side effect so tearDown cleans up.
     *
     * @return array{\Lcobucci\JWT\Signer\Key, \Lcobucci\JWT\Signer\Key, string}
     */
    private function registerJwtClientAndObtainCode(Client $http): array
    {
        // Load the pre-generated RSA test key pair that BulkAPITestClient
        // also uses. Keeps all "test client identity" material in one place
        // so a key rotation is a single-file change.
        $keyLocation = __DIR__ . '/../data/Unit/Common/Auth/Grant/';
        $jwks = json_decode((string) file_get_contents($keyLocation . 'jwk-public-valid.json'));
        $privateKey = InMemory::file($keyLocation . 'openemr-rsa384-private.key');
        $publicKey = InMemory::file($keyLocation . 'openemr-rsa384-public.pem');

        // DCR: register a confidential client that authenticates at /token
        // with a signed JWT rather than a shared secret. Scope set is
        // deliberately kept to just openid/fhirUser/offline_access so
        // ScopeRepository::hasScopesThatRequireManualApproval does not
        // trigger — client comes back auto-enabled, no admin dance
        // needed (matches AuthorizationLogoutFullFlowTest's approach).
        $reg = $http->post($this->baseUrl . '/oauth2/default/registration', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'application_type' => 'private',
                'redirect_uris' => [self::REDIRECT_URI],
                'client_name' => 'AuthorizationGrantJwtAssertionFlowTest-' . bin2hex(random_bytes(3)),
                'token_endpoint_auth_method' => 'private_key_jwt',
                'contacts' => ['e2e@test.example'],
                'scope' => 'openid fhirUser offline_access',
                'jwks' => $jwks,
            ],
        ]);
        $this->assertSame(200, $reg->getStatusCode(), 'DCR registration should succeed');
        $clientData = json_decode((string) $reg->getBody(), true);
        $this->assertIsArray($clientData);
        $this->assertArrayHasKey('client_id', $clientData);
        $this->assertIsString($clientData['client_id']);
        $this->clientId = $clientData['client_id'];

        // /authorize → login → consent → code, identical to the
        // client_secret_post variant (the JWT-vs-secret variance is
        // entirely on the /token step).
        $authUrl = '/oauth2/default/authorize?' . http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => self::REDIRECT_URI,
            'response_type' => 'code',
            'scope' => 'openid fhirUser offline_access',
            'state' => self::STATE,
            'nonce' => self::NONCE,
        ]);
        $loginPage = $http->get($this->baseUrl . $authUrl);
        $this->assertSame(200, $loginPage->getStatusCode(), 'Authorize should redirect to login page');
        [$loginCsrf, $loginAction] = $this->parseLoginForm(
            new Crawler((string) $loginPage->getBody()),
            'login page',
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
        $consentCrawler = new Crawler((string) $postLogin->getBody());
        $this->assertGreaterThan(
            0,
            $consentCrawler->filterXPath('//*[@name="proceed"]')->count(),
            'After login, the consent page (with proceed button) should render'
        );
        [$consentCsrf, $consentAction] = $this->parseLoginForm(
            $consentCrawler,
            'consent page',
            ['csrf_token_form', 'proceed', 'scope[openid]', 'scope[fhirUser]', 'scope[offline_access]']
        );

        $postConsent = $http->post($this->baseUrl . $consentAction, [
            'form_params' => [
                'csrf_token_form' => $consentCsrf,
                'proceed' => '1',
                'scope' => [
                    'openid' => 'openid',
                    'fhirUser' => 'fhirUser',
                    'offline_access' => 'offline_access',
                ],
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

        return [$privateKey, $publicKey, $callbackQuery['code']];
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
     * Copy of AuthorizationLogoutFullFlowTest::parseLoginForm — kept
     * inline rather than extracted so this test remains self-contained
     * and does not create a new shared-trait surface that would need
     * its own tests. The two tests exercise different code paths on
     * the server; sharing form-scraping helpers between them is a
     * low-value coupling.
     *
     * @param list<string> $expectedFields  named form-control fields the
     *        caller is about to POST — asserted as declared inputs so
     *        template drift becomes a clear failure rather than a silent
     *        POST to fields the server no longer reads.
     * @return array{string, string} [csrf_token_form value, form action URL]
     */
    private function parseLoginForm(Crawler $crawler, string $where, array $expectedFields = []): array
    {
        $csrfInputs = $crawler->filterXPath('//input[@name="csrf_token_form"]');
        $this->assertGreaterThan(0, $csrfInputs->count(), "No csrf_token_form input found on $where");
        $csrf = (string) $csrfInputs->first()->attr('value');
        $this->assertNotSame('', $csrf, "Empty csrf_token_form value on $where");

        $forms = $crawler->filterXPath('//form[@id="userLogin"]');
        $this->assertGreaterThan(0, $forms->count(), "No <form id=\"userLogin\"> on $where");
        $action = (string) $forms->first()->attr('action');
        $this->assertNotSame('', $action, "Empty form action on $where");

        foreach ($expectedFields as $field) {
            $matches = $forms->first()->filterXPath(
                './/input[@name="' . $field . '"]'
                . ' | .//button[@name="' . $field . '"]'
                . ' | .//select[@name="' . $field . '"]'
                . ' | .//textarea[@name="' . $field . '"]'
            );
            $this->assertGreaterThan(
                0,
                $matches->count(),
                "Form on $where does not declare a submittable control named '$field'"
            );
        }

        return [$csrf, $action];
    }
}
