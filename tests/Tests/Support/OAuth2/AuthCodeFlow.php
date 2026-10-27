<?php

/**
 * Authorization-code grant mint helpers for api-tier tests.
 *
 * Drives the full /authorize → login → consent → /token flow over
 * pure HTTP via Guzzle, so a test file can mint a bearer via the
 * realistic client path (not just password grant) in one call.
 *
 * Separate from `tests/Acceptance/Support/OAuth2/AuthCodeFlow.php`,
 * which uses BrowserKit and does admin-panel navigation to enable
 * the client. This api-tier variant uses Guzzle (consistent with the
 * rest of `tests/Tests/Api/`) and enables the client via direct DB
 * (through `DcrClient::enableViaDb`), which is faster and more
 * predictable for internal tests that have DB access.
 *
 * Supports both user_role=api (staff via admin fixture) and
 * user_role=portal-api (patient via PortalPatientFixtureManager
 * fixture).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Support\OAuth2;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use PHPUnit\Framework\Assert;
use Symfony\Component\DomCrawler\Crawler;

class AuthCodeFlow
{
    public const REDIRECT_URI = 'https://client.example/cb';

    /**
     * Generate a PKCE code_verifier per RFC 7636: 43-128 chars from
     * the unreserved URL-safe base64 alphabet. 32 random bytes encoded
     * gives a 43-char verifier.
     */
    public static function generatePkceVerifier(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }

    /**
     * Derive the S256 code_challenge from a code_verifier per RFC
     * 7636 §4.2: BASE64URL-ENCODE(SHA256(ASCII(verifier))).
     */
    public static function deriveS256Challenge(string $verifier): string
    {
        return rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
    }

    /**
     * Mint a users-role bearer via auth-code + PKCE against a PUBLIC
     * client (no client_secret). PKCE is the only credential
     * presented at the token endpoint — the code_verifier matching
     * the /authorize code_challenge is what proves the client is the
     * one that initiated the flow. Suitable for mobile / SPA /
     * CLI-style clients that cannot keep a secret.
     *
     * Generates verifier + S256 challenge internally; caller can
     * access the verifier in the returned payload if they need it
     * (e.g. for a wrong-verifier-rejection test variant).
     *
     * @param ?list<string> $consentGrantsOverride see mintUserRoleToken docblock
     *
     * @return array{access_token: string, scopes_granted: list<string>, response_body: array<mixed>, client_id: string, code_verifier: string, code_challenge: string, nonce: string}
     */
    public static function mintUserRolePublicClient(
        string $baseUrl,
        string $clientName,
        string $scope,
        ?array $consentGrantsOverride = null,
    ): array {
        $clientId = DcrClient::registerPublic(
            $baseUrl,
            $clientName,
            $scope,
            'authorization_code',
            self::REDIRECT_URI,
        );
        try {
            $verifier = self::generatePkceVerifier();
            $challenge = self::deriveS256Challenge($verifier);
            $result = self::runFlow(
                $baseUrl,
                $clientId,
                null, // public client: no client_secret
                $scope,
                [
                    'username' => 'admin',
                    'password' => 'pass',
                    'email' => '',
                    'user_role' => 'api',
                ],
                $consentGrantsOverride,
                $challenge,
                $verifier,
            );
        } catch (\Throwable $e) {
            DcrClient::remove($clientId);
            throw $e;
        }
        $result['client_id'] = $clientId;
        $result['code_verifier'] = $verifier;
        $result['code_challenge'] = $challenge;
        return $result;
    }

    /**
     * Mint a users-role bearer via auth-code grant with the seeded
     * admin/pass credentials. For api-tier staff-side testing.
     *
     * The optional $consentGrantsOverride parameter lets a caller
     * submit only a subset of the /authorize-requested scopes at the
     * consent step — emulating a user unchecking some of the scope
     * checkboxes before clicking "proceed." Default (null) grants
     * every requested scope.
     *
     * @param ?list<string> $consentGrantsOverride
     *
     * @return array{access_token: string, scopes_granted: list<string>, response_body: array<mixed>, client_id: string, client_secret: string, nonce: string}
     */
    public static function mintUserRoleToken(
        string $baseUrl,
        string $clientName,
        string $scope,
        ?array $consentGrantsOverride = null,
    ): array {
        [$clientId, $clientSecret] = DcrClient::registerConfidential(
            $baseUrl,
            $clientName,
            $scope,
            'authorization_code',
            self::REDIRECT_URI,
        );
        try {
            $result = self::runFlow(
                $baseUrl,
                $clientId,
                $clientSecret,
                $scope,
                [
                    'username' => 'admin',
                    'password' => 'pass',
                    'email' => '',
                    'user_role' => 'api',
                ],
                $consentGrantsOverride,
            );
        } catch (\Throwable $e) {
            // runFlow throwing means the caller never receives
            // client_id — remove the orphaned row before rethrowing.
            // Same pattern as DcrClient::registerConfidential's
            // enableViaDb guard.
            DcrClient::remove($clientId);
            throw $e;
        }
        $result['client_id'] = $clientId;
        $result['client_secret'] = $clientSecret;
        return $result;
    }

    /**
     * Mint a patient-role bearer via auth-code grant with a
     * PortalPatientFixtureManager fixture. The fixture array must
     * carry `portal_login_username`, `plain_password`, and `email` —
     * matches installPortalPatient's return shape.
     *
     * Portal login POSTs user_role=portal-api (not user_role=patient),
     * matching what the templates/oauth2/oauth2-login.html.twig
     * "Patient Login" button submits.
     *
     * The optional $consentGrantsOverride parameter lets a caller
     * submit only a subset of the /authorize-requested scopes at the
     * consent step. See mintUserRoleToken for details.
     *
     * @param array<string, mixed> $fixture
     * @param ?list<string> $consentGrantsOverride
     *
     * @return array{access_token: string, scopes_granted: list<string>, response_body: array<mixed>, client_id: string, client_secret: string, nonce: string}
     */
    public static function mintPatientRoleToken(
        string $baseUrl,
        string $clientName,
        string $scope,
        array $fixture,
        ?array $consentGrantsOverride = null,
    ): array {
        Assert::assertIsString($fixture['portal_login_username'] ?? null);
        Assert::assertIsString($fixture['plain_password'] ?? null);
        Assert::assertIsString($fixture['email'] ?? null);
        [$clientId, $clientSecret] = DcrClient::registerConfidential(
            $baseUrl,
            $clientName,
            $scope,
            'authorization_code',
            self::REDIRECT_URI,
        );
        try {
            $result = self::runFlow(
                $baseUrl,
                $clientId,
                $clientSecret,
                $scope,
                [
                    'username' => $fixture['portal_login_username'],
                    'password' => $fixture['plain_password'],
                    'email' => $fixture['email'],
                    'user_role' => 'portal-api',
                ],
                $consentGrantsOverride,
            );
        } catch (\Throwable $e) {
            // Same orphan-row guard as mintUserRoleToken above.
            DcrClient::remove($clientId);
            throw $e;
        }
        $result['client_id'] = $clientId;
        $result['client_secret'] = $clientSecret;
        return $result;
    }

    /**
     * Drive /authorize + login up to (but not past) the consent page
     * and return the rendered HTML + hints a caller needs to continue
     * the flow or assert on the page shape.
     *
     * Used by:
     *   - the token-mint methods below (which continue to consent POST
     *     + token exchange)
     *   - consent-screen regression tests that assert on the rendered
     *     page shape without needing to complete the auth-code → token
     *     exchange
     *
     * @return array{client_id: string, client_secret: string, http: Client, consent_html: string, consent_csrf: string, consent_action: string, state: string, nonce: string}
     */
    public static function renderConsentPageForUserRole(
        string $baseUrl,
        string $clientName,
        string $scope,
    ): array {
        [$clientId, $clientSecret] = DcrClient::registerConfidential(
            $baseUrl,
            $clientName,
            $scope,
            'authorization_code',
            self::REDIRECT_URI,
        );
        try {
            $prep = self::runToConsentPage(
                $baseUrl,
                $clientId,
                $scope,
                [
                    'username' => 'admin',
                    'password' => 'pass',
                    'email' => '',
                    'user_role' => 'api',
                ],
            );
        } catch (\Throwable $e) {
            DcrClient::remove($clientId);
            throw $e;
        }
        $prep['client_id'] = $clientId;
        $prep['client_secret'] = $clientSecret;
        return $prep;
    }

    /**
     * Patient-role variant of renderConsentPageForUserRole. See the
     * mintPatientRoleToken docblock for the fixture-shape expectation.
     *
     * @param array<string, mixed> $fixture
     *
     * @return array{client_id: string, client_secret: string, http: Client, consent_html: string, consent_csrf: string, consent_action: string, state: string, nonce: string}
     */
    public static function renderConsentPageForPatientRole(
        string $baseUrl,
        string $clientName,
        string $scope,
        array $fixture,
    ): array {
        Assert::assertIsString($fixture['portal_login_username'] ?? null);
        Assert::assertIsString($fixture['plain_password'] ?? null);
        Assert::assertIsString($fixture['email'] ?? null);
        [$clientId, $clientSecret] = DcrClient::registerConfidential(
            $baseUrl,
            $clientName,
            $scope,
            'authorization_code',
            self::REDIRECT_URI,
        );
        try {
            $prep = self::runToConsentPage(
                $baseUrl,
                $clientId,
                $scope,
                [
                    'username' => $fixture['portal_login_username'],
                    'password' => $fixture['plain_password'],
                    'email' => $fixture['email'],
                    'user_role' => 'portal-api',
                ],
            );
        } catch (\Throwable $e) {
            DcrClient::remove($clientId);
            throw $e;
        }
        $prep['client_id'] = $clientId;
        $prep['client_secret'] = $clientSecret;
        return $prep;
    }

    /**
     * @param array{username: string, password: string, email: string, user_role: string} $loginParams
     * @param ?list<string> $consentGrantsOverride if non-null, only
     *     these scopes are submitted at the consent step (emulating a
     *     user unchecking others); if null, all $scope-requested
     *     scopes are submitted.
     * @param ?string $pkceCodeChallenge if non-null, submitted to
     *     /authorize as code_challenge + code_challenge_method=S256.
     *     Required for public clients; optional for confidential.
     * @param ?string $pkceCodeVerifier if non-null, submitted to
     *     /token as code_verifier. Must be the pre-image of
     *     $pkceCodeChallenge. Required when challenge is supplied.
     * @param ?string $clientSecret null → public-client (no client_secret
     *     in the token POST). Required: either $clientSecret is a
     *     string OR $pkceCodeVerifier is non-null.
     *
     * @return array{access_token: string, scopes_granted: list<string>, response_body: array<mixed>, nonce: string}
     */
    private static function runFlow(
        string $baseUrl,
        string $clientId,
        ?string $clientSecret,
        string $scope,
        array $loginParams,
        ?array $consentGrantsOverride = null,
        ?string $pkceCodeChallenge = null,
        ?string $pkceCodeVerifier = null,
    ): array {
        $prep = self::runToConsentPage(
            $baseUrl,
            $clientId,
            $scope,
            $loginParams,
            $pkceCodeChallenge,
        );
        $http = $prep['http'];
        $consentCsrf = $prep['consent_csrf'];
        $consentAction = $prep['consent_action'];
        $state = $prep['state'];

        // Step 3: POST consent with scope grants, DO NOT follow the
        // redirect — we need to extract `code` from the Location.
        // By default grant every requested scope; if the caller
        // supplied $consentGrantsOverride, submit only those (emulating
        // a user unchecking the omitted scope boxes).
        $scopeGrant = [];
        $scopesToGrant = $consentGrantsOverride ?? explode(' ', $scope);
        foreach ($scopesToGrant as $s) {
            if ($s !== '') {
                $scopeGrant[$s] = $s;
            }
        }
        $consentPostResp = $http->post(self::resolveActionUrl($baseUrl, $consentAction), [
            'form_params' => [
                'csrf_token_form' => $consentCsrf,
                'proceed' => '1',
                'scope' => $scopeGrant,
            ],
            'allow_redirects' => false,
        ]);
        Assert::assertSame(
            302,
            $consentPostResp->getStatusCode(),
            'Consent POST should 302 to the redirect_uri. Status '
                . $consentPostResp->getStatusCode()
                . ', Body: ' . (string) $consentPostResp->getBody()
        );
        $location = $consentPostResp->getHeaderLine('Location');
        Assert::assertStringStartsWith(
            self::REDIRECT_URI,
            $location,
            'Consent redirect must point at the registered redirect_uri'
        );
        $query = [];
        parse_str((string) parse_url($location, PHP_URL_QUERY), $query);
        Assert::assertArrayHasKey('code', $query, 'Callback URL must carry the authorization code');
        Assert::assertSame($state, $query['state'] ?? null, 'state must be preserved through the flow');
        Assert::assertIsString($query['code']);
        $code = $query['code'];

        // Step 4: POST /token with code + client credentials or PKCE.
        // A confidential client authenticates via client_secret_post
        // ($clientSecret non-null). A public client authenticates via
        // code_verifier only ($clientSecret null, $pkceCodeVerifier
        // non-null).
        $tokenForm = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => self::REDIRECT_URI,
            'client_id' => $clientId,
        ];
        if ($clientSecret !== null) {
            $tokenForm['client_secret'] = $clientSecret;
        }
        if ($pkceCodeVerifier !== null) {
            $tokenForm['code_verifier'] = $pkceCodeVerifier;
        }
        $tokenResp = $http->post($baseUrl . '/oauth2/default/token', [
            'form_params' => $tokenForm,
        ]);
        $tokenBodyRaw = (string) $tokenResp->getBody();
        Assert::assertSame(
            200,
            $tokenResp->getStatusCode(),
            'Token exchange must succeed. Body: ' . $tokenBodyRaw
        );
        $body = json_decode($tokenBodyRaw, true);
        Assert::assertIsArray($body);
        $token = $body['access_token'] ?? null;
        Assert::assertIsString($token);
        Assert::assertNotSame('', $token);
        $scopeValue = $body['scope'] ?? '';
        /** @var list<string> $granted */
        $granted = is_string($scopeValue) && $scopeValue !== '' ? explode(' ', $scopeValue) : [];
        return [
            'access_token' => $token,
            'scopes_granted' => $granted,
            'response_body' => $body,
            'nonce' => $prep['nonce'],
        ];
    }

    /**
     * Exchange a refresh_token for a new access_token.
     *
     * Supports both confidential (client_secret_post) and public
     * (no client_secret) clients — pass null for $clientSecret to
     * omit the field entirely from the token POST, matching the
     * public-client refresh contract where PKCE at the auth-code
     * exchange is the only client proof.
     *
     * Returns the raw token-endpoint response so a caller can assert
     * status + body shape — 200 for a successful exchange, 400 with
     * `error=invalid_scope` for a scope-upgrade attempt, etc.
     *
     * $scope is optional per RFC 6749 §6: when supplied, the client
     * is asking for a narrower (or equal) scope. Omitting it reuses
     * the refresh-token's bound scope set.
     *
     * @return array{status: int, body: array<mixed>, scopes_granted: list<string>, response_body: array<mixed>}
     */
    public static function exchangeRefreshToken(
        string $baseUrl,
        string $clientId,
        ?string $clientSecret,
        string $refreshToken,
        ?string $scope = null,
    ): array {
        $form = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => $clientId,
        ];
        if ($clientSecret !== null) {
            $form['client_secret'] = $clientSecret;
        }
        if ($scope !== null) {
            $form['scope'] = $scope;
        }
        $resp = self::buildClient()->post($baseUrl . '/oauth2/default/token', [
            'form_params' => $form,
        ]);
        $bodyRaw = (string) $resp->getBody();
        $body = json_decode($bodyRaw, true);
        if (!is_array($body)) {
            $body = ['_raw' => $bodyRaw];
        }
        $scopeValue = $body['scope'] ?? '';
        /** @var list<string> $granted */
        $granted = is_string($scopeValue) && $scopeValue !== '' ? explode(' ', $scopeValue) : [];
        return [
            'status' => $resp->getStatusCode(),
            'body' => $body,
            'scopes_granted' => $granted,
            'response_body' => $body,
        ];
    }

    /**
     * Drives steps 1-2 of the auth-code flow (GET /authorize → POST
     * login → arrive on consent page). Returns everything a caller
     * needs to either continue to consent-submit or assert on the
     * rendered consent page.
     *
     * @param array{username: string, password: string, email: string, user_role: string} $loginParams
     *
     * @return array{http: Client, consent_html: string, consent_csrf: string, consent_action: string, state: string, nonce: string}
     */
    private static function runToConsentPage(
        string $baseUrl,
        string $clientId,
        string $scope,
        array $loginParams,
        ?string $pkceCodeChallenge = null,
    ): array {
        $http = self::buildClient();

        // Step 1: GET /authorize, follow to login page.
        $state = bin2hex(random_bytes(8));
        $nonce = bin2hex(random_bytes(8));
        $authParams = [
            'client_id' => $clientId,
            'redirect_uri' => self::REDIRECT_URI,
            'response_type' => 'code',
            'scope' => $scope,
            'state' => $state,
            'nonce' => $nonce,
        ];
        if ($pkceCodeChallenge !== null) {
            $authParams['code_challenge'] = $pkceCodeChallenge;
            $authParams['code_challenge_method'] = 'S256';
        }
        $authUrl = $baseUrl . '/oauth2/default/authorize?' . http_build_query($authParams);
        $loginPageResp = $http->get($authUrl);
        Assert::assertSame(
            200,
            $loginPageResp->getStatusCode(),
            '/authorize GET must land on the login page (200). Body: '
                . (string) $loginPageResp->getBody()
        );
        $loginBody = (string) $loginPageResp->getBody();
        [$loginCsrf, $loginAction] = self::extractCsrfAndAction($loginBody, 'login page');

        // Step 2: POST login credentials with the role-specific
        // user_role value. Guzzle follows redirects so the final
        // response is the consent page.
        $loginPostResp = $http->post(self::resolveActionUrl($baseUrl, $loginAction), [
            'form_params' => [
                'csrf_token_form' => $loginCsrf,
                'username' => $loginParams['username'],
                'password' => $loginParams['password'],
                'email' => $loginParams['email'],
                'persist_login' => '0',
                'user_role' => $loginParams['user_role'],
            ],
            'allow_redirects' => true,
        ]);
        $consentBody = (string) $loginPostResp->getBody();
        Assert::assertStringContainsString(
            'proceed',
            $consentBody,
            'Expected to land on the consent page after login — the "proceed" control '
                . 'is the consent-page marker. If this fails, likely the login was '
                . 'rejected (wrong credentials / wrong user_role) or the server '
                . "took us elsewhere. Body start: " . substr($consentBody, 0, 500)
        );
        [$consentCsrf, $consentAction] = self::extractCsrfAndAction($consentBody, 'consent page');

        return [
            'http' => $http,
            'consent_html' => $consentBody,
            'consent_csrf' => $consentCsrf,
            'consent_action' => $consentAction,
            'state' => $state,
            'nonce' => $nonce,
        ];
    }

    /**
     * @return array{string, string} [csrf_token_form, form_action]
     */
    private static function extractCsrfAndAction(string $html, string $where): array
    {
        $crawler = new Crawler($html);
        $form = $crawler->filterXPath('//form[.//input[@name="csrf_token_form"]]')->first();
        Assert::assertGreaterThan(0, $form->count(), "No form with csrf_token_form on " . $where);
        $csrf = (string) $form->filterXPath('.//input[@name="csrf_token_form"]')->first()->attr('value');
        $action = (string) $form->attr('action');
        Assert::assertNotSame('', $csrf, "csrf_token_form was empty on " . $where);
        Assert::assertNotSame('', $action, "form action was empty on " . $where);
        return [$csrf, $action];
    }

    private static function resolveActionUrl(string $baseUrl, string $action): string
    {
        if (str_starts_with($action, 'http://') || str_starts_with($action, 'https://')) {
            return $action;
        }
        if (str_starts_with($action, '/')) {
            return $baseUrl . $action;
        }
        return $baseUrl . '/' . $action;
    }

    private static function buildClient(): Client
    {
        return new Client([
            'verify' => false,
            'http_errors' => false,
            'cookies' => new CookieJar(),
            'timeout' => 15,
        ]);
    }
}
