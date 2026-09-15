<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Acceptance\Support\OAuth2;

use OpenEMR\Tests\Acceptance\Support\ArtifactBrowser;
use OpenEMR\Tests\Acceptance\Support\LoginFlow;
use OpenEMR\Tests\Acceptance\Support\ResponseHeaders;
use PHPUnit\Framework\Assert;
use Symfony\Component\BrowserKit\HttpBrowser;

/**
 * DCR + admin client-enable + authorization-code + token-exchange flow,
 * all over pure HTTP via BrowserKit's HttpBrowser (no headless browser
 * needed — every form on the path renders + submits without JS).
 *
 * Ports the HTTP shape from `tests/Tests/Api/AuthorizationLogoutFullFlowTest`
 * (openemr/openemr#13175), which is the source-side test that proved
 * the OAuth flow works end-to-end via Guzzle. This acceptance-side
 * version uses BrowserKit for consistency with the rest of Support/
 * (ArtifactBrowser, LoginFlow) and adds an admin-panel client-enable
 * step that source-side tests don't need (source seeds is_enabled=1
 * directly in the DB; acceptance runs against a black-box artifact
 * and has no DB access).
 *
 * The admin-approval step is required whenever the requested scope
 * list includes any `user/` or `system/` scope — OpenEMR's
 * ScopeRepository::hasScopesThatRequireManualApproval flags those as
 * needing admin sign-off, and the client is inserted disabled. Without
 * the approval, /token returns invalid_client. See
 * src/Common/Auth/OpenIDConnect/Repositories/ClientRepository.php and
 * src/Common/Auth/OpenIDConnect/Repositories/ScopeRepository.php for
 * the exact rules.
 *
 * Prerequisites for this helper to succeed on the artifact:
 *  1. API is enabled (rest_api / rest_fhir_api globals) — handled by
 *     tests/Acceptance/bin/api-enable.php (Phase 4a-3 first slice).
 *  2. `site_addr_oath` matches the acceptance URL — also handled by
 *     api-enable.php.
 * The api-enable.php Panther bootstrap runs before any
 * #[Group('api-enabled')] test executes.
 */
final class AuthCodeFlow
{
    /**
     * Made-up client redirect_uri. Kept on a reserved-example TLD so
     * BrowserKit's `followRedirects(false)` on the consent POST doesn't
     * accidentally hit anything real if a future refactor drops that
     * setting — the request would just fail DNS instead of leaking a
     * code fragment to a live host.
     */
    private const REDIRECT_URI = 'https://acceptance-harness.example/callback';

    /**
     * Run the full flow — DCR → admin approval → /authorize → login →
     * consent → /token — and return the minted access_token.
     *
     * $scope must include "openid" (drives id_token minting + puts
     * the OIDC path through the pipeline) plus whatever additional
     * scopes the caller wants on the token. `api:oemr` is needed to
     * pass BearerTokenAuthorizationStrategy::isValidRequestForUserRole
     * on any standard REST route; `user/<resource>.<op>` SMART scopes
     * are needed to pass onRestApiSecurityCheck. See ApiSmokeTest for
     * the concrete scope used against /api/facility.
     */
    public static function mintAccessToken(string $scope): string
    {
        Assert::assertStringContainsString(
            'openid',
            $scope,
            'AuthCodeFlow requires openid in the scope — without it the OIDC branch never fires and no id_token is minted',
        );

        $baseUrl = ArtifactBrowser::baseUrl();

        // Step 1: DCR — register a fresh client.
        // application_type='private' is openemr's non-spec extension
        // that returns a client_secret (see the client_role logic in
        // AuthorizationController::registerClient); OIDC-spec 'web'
        // and 'native' yield public clients with no secret, which
        // client_secret_post auth on /token can't use.
        $clientName = 'AcceptanceAuthCode-' . bin2hex(random_bytes(4));
        [$clientId, $clientSecret] = self::registerClient($baseUrl, $clientName, $scope);

        // Step 2: admin-approve the DCR client. Required whenever the
        // requested scope contains any user/ or system/ scope. Cheap
        // to always run (no-op if already enabled) so we don't
        // conditionally skip — cleaner than replicating openemr's
        // internal manual-approval rules on the harness side and
        // risking drift.
        self::enableClient($baseUrl, $clientId, $clientName);

        // Step 3-6: the actual OAuth authorization-code flow.
        return self::runAuthCodeFlow($baseUrl, $clientId, $clientSecret, $scope);
    }

    /**
     * @return array{string, string} [client_id, client_secret]
     */
    private static function registerClient(string $baseUrl, string $clientName, string $scope): array
    {
        $browser = ArtifactBrowser::create();
        $browser->followRedirects(false);
        $regBody = json_encode([
            'application_type' => 'private',
            'redirect_uris' => [self::REDIRECT_URI],
            'client_name' => $clientName,
            'token_endpoint_auth_method' => 'client_secret_post',
            'contacts' => ['acceptance-harness@openemr.example'],
            'scope' => $scope,
        ], JSON_THROW_ON_ERROR);
        $browser->request(
            'POST',
            $baseUrl . '/oauth2/default/registration',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $regBody,
        );
        Assert::assertSame(
            200,
            $browser->getResponse()->getStatusCode(),
            'DCR should return 200 — 404 means the OAuth server is unreachable, 400 means the registration body was rejected',
        );
        $client = json_decode($browser->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        Assert::assertIsArray($client);
        Assert::assertArrayHasKey('client_id', $client, 'DCR response must include client_id');
        Assert::assertArrayHasKey('client_secret', $client, 'DCR response must include client_secret — empty secret usually means application_type was not "private"');
        Assert::assertIsString($client['client_id']);
        Assert::assertIsString($client['client_secret']);
        Assert::assertNotSame('', $client['client_secret'], 'client_secret must not be empty');
        return [$client['client_id'], $client['client_secret']];
    }

    private static function enableClient(string $baseUrl, string $clientId, string $clientName): void
    {
        $admin = ArtifactBrowser::create();
        LoginFlow::loginAsAdmin($admin, $baseUrl, "AuthCodeFlow enable-client for {$clientName}");

        // The client-admin page has one CSRF token per session, embedded
        // in every action URL as ?csrf_token=<t>. Load the list page
        // once and scrape it.
        $admin->request('GET', $baseUrl . '/interface/smart/admin-client.php');
        Assert::assertSame(
            200,
            $admin->getResponse()->getStatusCode(),
            'GET admin-client.php should return 200 for an authenticated admin — 302 to login = session got dropped, 403 = admin ACL missing',
        );
        $body = $admin->getResponse()->getContent();
        if (preg_match('/csrf_token=([a-f0-9]+)/', $body, $m) !== 1) {
            Assert::fail('admin-client.php did not contain a csrf_token — the enable action URL cannot be built without it');
        }
        $csrf = $m[1];

        // Action URL format: admin-client.php?action=edit/<clientId>/enable&csrf_token=<t>
        // The ClientAdminController parses `action` by exploding on '/'
        // then match-ing on [main, subject, sub-action]. On success it
        // 307-redirects to the list page; on failure it renders the 404
        // template with 200. Follow the redirect and assert on the
        // final rendered page — that's where we can look for the
        // Enabled indicator.
        $action = 'edit/' . $clientId . '/enable';
        $enableUrl = $baseUrl . '/interface/smart/admin-client.php'
            . '?action=' . rawurlencode($action)
            . '&csrf_token=' . rawurlencode($csrf);
        $admin->followRedirects(true);
        $admin->request('GET', $enableUrl);
        Assert::assertSame(
            200,
            $admin->getResponse()->getStatusCode(),
            "Enable action for client {$clientName} should end on the client list page (200 after redirect) — non-200 usually means CSRF was rejected or the client no longer exists",
        );
        // Post-enable, the client's row on the list page shows a
        // "Disable Client" action (because it's now enabled). Look for
        // the client's ID plus that verb to confirm the enable stuck —
        // "Enabled" alone appears on many other rows and would be a
        // false positive if the enable action silently no-oped.
        $listBody = $admin->getResponse()->getContent();
        Assert::assertStringNotContainsString(
            'not found',
            strtolower($listBody),
            "Enable action rendered the 404 page — client {$clientName} may not have been registered",
        );
        Assert::assertStringContainsString(
            $clientId,
            $listBody,
            "Client list page after enable does not mention client id {$clientId} — the enable action redirected somewhere unexpected",
        );
        Assert::assertMatchesRegularExpression(
            '/' . preg_quote($clientId, '/') . '.*Disable Client/is',
            $listBody,
            "Client row for {$clientId} does not show a Disable Client action — the enable action silently no-oped (row present but is_enabled still 0)",
        );
    }

    private static function runAuthCodeFlow(
        string $baseUrl,
        string $clientId,
        string $clientSecret,
        string $scope,
    ): string {
        $browser = ArtifactBrowser::create();

        // Step 3: GET /authorize with `followRedirects(true)` so we
        // land on the login page (rather than the 302 to it). The
        // session cookie set on this response binds the /authorize
        // request into the session — subsequent login + consent posts
        // continue that same OAuth authorization.
        $state = bin2hex(random_bytes(8));
        $nonce = bin2hex(random_bytes(8));
        $authUrl = $baseUrl . '/oauth2/default/authorize?' . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => self::REDIRECT_URI,
            'response_type' => 'code',
            'scope' => $scope,
            'state' => $state,
            'nonce' => $nonce,
        ]);
        $browser->followRedirects(true);
        $browser->request('GET', $authUrl);
        Assert::assertSame(
            200,
            $browser->getResponse()->getStatusCode(),
            '/authorize should render the login page (final 200 after redirect follow) — 400/500 means the OAuth server rejected the request parameters',
        );

        // Step 4: POST login credentials. Login form has csrf_token_form
        // as a hidden input; scrape it from the crawler. `user_role=api`
        // is the "users" openemr user role, which is what the standard
        // REST API + FHIR expect (as opposed to portal-api, which is
        // patient-portal-scoped).
        $loginCsrf = self::extractCsrfToken($browser, 'login page');
        $loginAction = self::extractFormAction($browser, 'login page');
        $browser->request(
            'POST',
            self::resolveActionUrl($baseUrl, $loginAction),
            [
                'csrf_token_form' => $loginCsrf,
                'username' => 'admin',
                'password' => 'pass',
                'email' => '',
                'persist_login' => '0',
                'user_role' => 'api',
            ],
        );
        Assert::assertSame(
            200,
            $browser->getResponse()->getStatusCode(),
            'Login POST + redirect should land on the consent page (200) — 200 with the login form re-rendered = wrong credentials, 500 = login handler broke',
        );

        // Step 5: consent page → POST proceed with scopes granted.
        // Each requested scope is submitted as scope[<name>]=<name>
        // which PHP form parsing groups into a scope=[name=>name, ...]
        // array. The server intersects these against the client's
        // registered scopes AND the /authorize-requested scopes, so
        // submitting extras is a no-op — cheaper to over-submit than
        // to try to mirror openemr's structuredScopes / otherScopes /
        // hiddenScopes categorization on the harness side.
        $consentCsrf = self::extractCsrfToken($browser, 'consent page');
        $consentAction = self::extractFormAction($browser, 'consent page');
        Assert::assertGreaterThan(
            0,
            $browser->getCrawler()->filterXPath('//*[@name="proceed"]')->count(),
            'Consent page should have a proceed control — missing means we did NOT land on the consent page after login (probably still on the login page because credentials were rejected)',
        );
        $scopeGrant = [];
        foreach (explode(' ', $scope) as $s) {
            if ($s !== '') {
                $scopeGrant[$s] = $s;
            }
        }
        $browser->followRedirects(false);
        $browser->request(
            'POST',
            self::resolveActionUrl($baseUrl, $consentAction),
            [
                'csrf_token_form' => $consentCsrf,
                'proceed' => '1',
                'scope' => $scopeGrant,
            ],
        );
        Assert::assertSame(
            302,
            $browser->getResponse()->getStatusCode(),
            'Consent POST should redirect back to the client (302) — 200 means the consent form re-rendered (CSRF failure, scope mismatch, or session lost), 500 means the OAuth server failed to serialize the authorization response',
        );

        // Step 6: extract the code from the redirect Location.
        $callbackUrl = ResponseHeaders::location($browser->getResponse());
        Assert::assertStringStartsWith(
            self::REDIRECT_URI,
            $callbackUrl,
            'Callback URL should be at the registered redirect_uri — anywhere else means the server treated this as an error redirect',
        );
        $callbackQuery = [];
        parse_str(parse_url($callbackUrl, PHP_URL_QUERY) ?: '', $callbackQuery);
        Assert::assertArrayHasKey('code', $callbackQuery, 'Callback URL must carry the authorization code — its absence means the server treated the flow as denied');
        Assert::assertSame(
            $state,
            $callbackQuery['state'] ?? null,
            'Callback must preserve the state value we sent to /authorize — mismatch would indicate CSRF-defense-through-state is broken',
        );
        Assert::assertIsString($callbackQuery['code']);
        $code = $callbackQuery['code'];

        // Step 7: token exchange — POST /token with the code.
        $browser->request(
            'POST',
            $baseUrl . '/oauth2/default/token',
            [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => self::REDIRECT_URI,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ],
        );
        Assert::assertSame(
            200,
            $browser->getResponse()->getStatusCode(),
            'Token exchange should return 200 — 401 invalid_client means the client-enable step did not stick (or client_secret is wrong), 400 means the code was invalid/expired/reused',
        );
        $tokens = json_decode($browser->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        Assert::assertIsArray($tokens);
        Assert::assertArrayHasKey('access_token', $tokens, 'Token response must include access_token');
        Assert::assertIsString($tokens['access_token']);
        Assert::assertNotSame('', $tokens['access_token'], 'access_token must not be empty');

        return $tokens['access_token'];
    }

    private static function extractCsrfToken(HttpBrowser $browser, string $where): string
    {
        $inputs = $browser->getCrawler()->filterXPath('//input[@name="csrf_token_form"]');
        Assert::assertGreaterThan(
            0,
            $inputs->count(),
            "csrf_token_form input not found on {$where} — form structure regressed?",
        );
        $token = (string) $inputs->first()->attr('value');
        Assert::assertNotSame('', $token, "csrf_token_form value is empty on {$where}");
        return $token;
    }

    private static function extractFormAction(HttpBrowser $browser, string $where): string
    {
        $forms = $browser->getCrawler()->filterXPath('//form[@id="userLogin"]');
        Assert::assertGreaterThan(
            0,
            $forms->count(),
            "<form id=\"userLogin\"> not found on {$where}",
        );
        $action = (string) $forms->first()->attr('action');
        Assert::assertNotSame('', $action, "Form action is empty on {$where}");
        return $action;
    }

    private static function resolveActionUrl(string $baseUrl, string $action): string
    {
        if (str_starts_with($action, 'http://') || str_starts_with($action, 'https://')) {
            return $action;
        }
        return $baseUrl . '/' . ltrim($action, '/');
    }
}
