<?php

/**
 * oe-module-api-explorer CI PKCE-mismatch negative driver.
 *
 * All five sibling drivers (driver.php, driver-browser.php,
 * driver-password.php, driver-fhir-writes.php, driver-portal.php)
 * exercise HAPPY paths only. driver-browser.php specifically
 * exercises public PKCE with a CORRECT code_verifier via the
 * explorer's UI. This driver fills the one-concrete-negative
 * slot that was open at the api-explorer tier: drive the full
 * public-client auth-code flow through /authorize + login +
 * consent to get a real authorization code, then POST /token
 * with a WRONG code_verifier and assert the server rejects
 * with `error=invalid_grant`.
 *
 * Why this matters as a negative: a regression that broke PKCE
 * S256 verifier-to-challenge check at the token endpoint (e.g.
 * a refactor that forgot to compare the sha256 of the verifier
 * against the stored challenge) would let any party who got hold
 * of an authorization code use it without having initiated the
 * flow. PKCE is the only credential a public client has. The in-
 * tree api tests exercise PKCE positive + a few verifier edge
 * cases; this driver provides the same assertion from the real
 * external-client angle (Guzzle, no phpunit, no OpenEMR test
 * namespace, no in-tree harness). Catches a regression that only
 * shows up when the authorization flow actually runs end-to-end.
 *
 * Flow:
 *   1. DCR a public client (application_type=web, patient-only
 *      scopes trigger OpenEMR's public-client DCR branch per
 *      Registrar convention — no client_secret issued).
 *   2. DB-flip is_enabled=1 (DCR registers disabled by default).
 *   3. Generate PKCE verifier A + S256 challenge A.
 *   4. Drive /authorize → login → consent, submitting challenge A.
 *   5. Extract the authorization code from the consent redirect.
 *   6. Generate verifier B (independent of A).
 *   7. POST /token with code + code_verifier=B. Assert HTTP 400 +
 *      `error=invalid_grant`.
 *
 * Exits nonzero on any step failure; the workflow dumps docker logs.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\GuzzleException;

if (php_sapi_name() !== 'cli') {
    echo 'Only php cli can execute a command';
    exit(1);
}

require_once __DIR__ . '/../../vendor/autoload.php';

$baseUrl = getenv('EXPLORER_BASE_URL') ?: 'https://openemr';
$mysqlHost = getenv('EXPLORER_MYSQL_HOST') ?: 'mysql';
$mysqlUser = getenv('EXPLORER_MYSQL_USER') ?: 'openemr';
$mysqlPass = getenv('EXPLORER_MYSQL_PASS') ?: 'openemr';
$mysqlDb = getenv('EXPLORER_MYSQL_DB') ?: 'openemr';
$redirectUri = 'https://openemr/devtools/oe-module-api-explorer/oauth_client.php';

if (!str_starts_with(strtolower($baseUrl), 'https://')) {
    fwrite(STDERR, "FAIL EXPLORER_BASE_URL must be https:// (got: {$baseUrl})\n");
    exit(2);
}

echo "oe-module-api-explorer PKCE-mismatch negative smoke\n";
echo "  base = {$baseUrl}\n";
echo "  db   = {$mysqlUser}@{$mysqlHost}/{$mysqlDb}\n\n";

$http = new Client([
    'base_uri' => $baseUrl,
    'verify' => false,
    'http_errors' => false,
    'cookies' => new CookieJar(),
    'timeout' => 60,
    'allow_redirects' => true,
]);

$cleanupClientIds = [];
register_shutdown_function(static function () use (&$cleanupClientIds, $mysqlHost, $mysqlUser, $mysqlPass, $mysqlDb): void {
    foreach ($cleanupClientIds as $cid) {
        $sql = sprintf("DELETE FROM oauth_clients WHERE client_id = %s", "'" . str_replace("'", "\\'", $cid) . "'");
        $cmd = sprintf(
            'mariadb -h %s -u %s -p%s %s -e %s 2>/dev/null',
            escapeshellarg($mysqlHost),
            escapeshellarg($mysqlUser),
            escapeshellarg($mysqlPass),
            escapeshellarg($mysqlDb),
            escapeshellarg($sql)
        );
        @exec($cmd);
    }
});

/**
 * RFC 7636 §4.1: PKCE code_verifier is 43–128 chars from the URL-safe
 * base64 alphabet. 32 random bytes → 43-char verifier.
 */
$genVerifier = static fn (): string => rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');

/**
 * RFC 7636 §4.2: S256 challenge = BASE64URL-ENCODE(SHA256(verifier)).
 */
$deriveChallenge = static fn (string $v): string => rtrim(strtr(base64_encode(hash('sha256', $v, true)), '+/', '-_'), '=');

/**
 * Extract CSRF token + form action from an HTML page via regex. Both
 * the login and consent pages carry `<input name="csrf_token_form"
 * value="...">` inside a single `<form ... action="...">`. The HTML
 * is OpenEMR's own and the shape is stable. Regex rather than
 * DomCrawler (symfony/css-selector is not a project dep; dom-crawler
 * alone only supports XPath, which is awkward for two-attribute
 * extraction).
 *
 * @return array{string, string} [csrf_token_form value, form action URL]
 */
$extractCsrfAndAction = static function (string $html, string $label): array {
    if (preg_match('/<input[^>]*name=["\']csrf_token_form["\'][^>]*value=["\']([^"\']+)["\']/', $html, $csrfMatch) !== 1) {
        if (preg_match('/<input[^>]*value=["\']([^"\']+)["\'][^>]*name=["\']csrf_token_form["\']/', $html, $csrfMatch) !== 1) {
            fwrite(STDERR, "FAIL no csrf_token_form found on {$label}\n");
            fwrite(STDERR, substr($html, 0, 2000) . "\n");
            exit(30);
        }
    }
    $csrf = $csrfMatch[1];
    // Find the form containing this csrf_token_form input. OpenEMR's
    // login + consent pages each have a single <form>; the first match
    // is the right one.
    if (preg_match('/<form[^>]*action=["\']([^"\']+)["\']/', $html, $formMatch) !== 1) {
        fwrite(STDERR, "FAIL no <form action=...> found on {$label}\n");
        exit(31);
    }
    $action = $formMatch[1];
    return [$csrf, $action];
};

// ---------------------------------------------------------------------------
// 1. DCR a public client. Patient-only scopes trigger OpenEMR's public-
//    client branch (no client_secret issued, PKCE-only auth at /token).
// ---------------------------------------------------------------------------
// Omit launch/patient from the scope — that triggers the SMART standalone
// patient-select screen (/oauth2/default/smart/patient-select), which the
// public-client flow needs to answer before reaching consent. For a PKCE
// negative we just need a valid authorization code at the consent step,
// and patient/Patient.read alone is enough to trigger OpenEMR's public-
// client DCR branch (no client_secret issued) per the Registrar
// convention noted in docs-oauth2-fhir-api-test-coverage memory.
$scope = 'openid fhirUser patient/Patient.read';
echo "[1/7] DCR a public client (patient scopes trigger public-client branch)...\n";
$clientName = 'ApiExplorerSmoke-pkce-neg-' . bin2hex(random_bytes(4));
try {
    $regResponse = $http->post('/oauth2/default/registration', [
        'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
        'json' => [
            'application_type' => 'web',
            'client_name' => $clientName,
            'token_endpoint_auth_method' => 'client_secret_post',
            'redirect_uris' => [$redirectUri],
            'contacts' => ['smoke@test.example'],
            'scope' => $scope,
        ],
        'allow_redirects' => false,
    ]);
} catch (GuzzleException $e) {
    fwrite(STDERR, "FAIL DCR errored: " . $e->getMessage() . "\n");
    exit(3);
}
if ($regResponse->getStatusCode() !== 200) {
    fwrite(STDERR, "FAIL DCR returned HTTP {$regResponse->getStatusCode()}\n");
    fwrite(STDERR, substr((string) $regResponse->getBody(), 0, 2000) . "\n");
    exit(4);
}
$regBody = json_decode((string) $regResponse->getBody(), true);
if (!is_array($regBody) || !is_string($regBody['client_id'] ?? null)) {
    fwrite(STDERR, "FAIL DCR response missing client_id\n");
    exit(5);
}
$clientId = $regBody['client_id'];
$cleanupClientIds[] = $clientId;
echo "       OK  (client_id = {$clientId})\n\n";

// ---------------------------------------------------------------------------
// 2. Enable the client in DB. DCR registers with is_enabled=0.
// ---------------------------------------------------------------------------
echo "[2/7] Enable the client in DB...\n";
$sql = sprintf("UPDATE oauth_clients SET is_enabled = 1 WHERE client_id = '%s'", str_replace("'", "\\'", $clientId));
$cmd = sprintf(
    'mariadb -h %s -u %s -p%s %s -e %s 2>&1',
    escapeshellarg($mysqlHost),
    escapeshellarg($mysqlUser),
    escapeshellarg($mysqlPass),
    escapeshellarg($mysqlDb),
    escapeshellarg($sql)
);
exec($cmd, $output, $rc);
if ($rc !== 0) {
    fwrite(STDERR, "FAIL mariadb UPDATE returned {$rc}\n");
    fwrite(STDERR, implode("\n", $output) . "\n");
    exit(6);
}
echo "       OK\n\n";

// ---------------------------------------------------------------------------
// 3. Generate PKCE verifier A + derive challenge A. Verifier A will be
//    submitted at /authorize (as challenge A); verifier B — DIFFERENT
//    verifier — will be submitted at /token to prove the server checks.
// ---------------------------------------------------------------------------
echo "[3/7] Generating PKCE verifier A + S256 challenge A + separate verifier B...\n";
$verifierA = $genVerifier();
$challengeA = $deriveChallenge($verifierA);
$verifierB = $genVerifier();
// Belt-and-braces: random_bytes collisions are astronomically unlikely
// but if $verifierB happens to collide with $verifierA the test becomes
// a tautology (would test positive-path rather than negative).
if ($verifierA === $verifierB) {
    fwrite(STDERR, "FAIL verifier A and B collided — regenerate\n");
    exit(7);
}
echo "       OK\n\n";

// ---------------------------------------------------------------------------
// 4. Drive /authorize → login → consent. Panther/Selenium not needed —
//    Guzzle with the cookie jar follows redirects and scrapes forms from
//    the HTML. Same pattern tests/Tests/Support/OAuth2/AuthCodeFlow.php
//    uses at the api tier.
// ---------------------------------------------------------------------------
echo "[4/7] GET /authorize with challenge A → land on login form...\n";
$state = bin2hex(random_bytes(8));
$nonce = bin2hex(random_bytes(8));
$authParams = [
    'client_id' => $clientId,
    'redirect_uri' => $redirectUri,
    'response_type' => 'code',
    'scope' => $scope,
    'state' => $state,
    'nonce' => $nonce,
    'code_challenge' => $challengeA,
    'code_challenge_method' => 'S256',
];
try {
    $loginPageResp = $http->get('/oauth2/default/authorize?' . http_build_query($authParams));
} catch (GuzzleException $e) {
    fwrite(STDERR, "FAIL /authorize errored: " . $e->getMessage() . "\n");
    exit(8);
}
if ($loginPageResp->getStatusCode() !== 200) {
    fwrite(STDERR, "FAIL /authorize returned HTTP {$loginPageResp->getStatusCode()}\n");
    fwrite(STDERR, substr((string) $loginPageResp->getBody(), 0, 2000) . "\n");
    exit(9);
}
$loginHtml = (string) $loginPageResp->getBody();
[$loginCsrf, $loginAction] = $extractCsrfAndAction($loginHtml, 'login page');
echo "       OK\n\n";

echo "[5/7] POST login as admin → land on consent form...\n";
$loginActionUrl = str_starts_with($loginAction, 'http') ? $loginAction : $baseUrl . (str_starts_with($loginAction, '/') ? '' : '/') . $loginAction;
try {
    $consentPageResp = $http->post($loginActionUrl, [
        'form_params' => [
            'csrf_token_form' => $loginCsrf,
            'username' => 'admin',
            'password' => 'pass',
            'email' => '',
            'persist_login' => '0',
            'user_role' => 'api',
        ],
        'allow_redirects' => true,
    ]);
} catch (GuzzleException $e) {
    fwrite(STDERR, "FAIL login POST errored: " . $e->getMessage() . "\n");
    exit(10);
}
$consentHtml = (string) $consentPageResp->getBody();
if (!str_contains($consentHtml, 'proceed')) {
    fwrite(STDERR, "FAIL login did not land on consent page (no 'proceed' control)\n");
    fwrite(STDERR, substr($consentHtml, 0, 2000) . "\n");
    exit(11);
}
[$consentCsrf, $consentAction] = $extractCsrfAndAction($consentHtml, 'consent page');
echo "       OK\n\n";

// ---------------------------------------------------------------------------
// 5. POST consent, do NOT follow the redirect — the authorization code is
//    in the Location header's query string.
// ---------------------------------------------------------------------------
echo "[6/7] POST consent (proceed) → extract authorization code from redirect...\n";
$consentActionUrl = str_starts_with($consentAction, 'http') ? $consentAction : $baseUrl . (str_starts_with($consentAction, '/') ? '' : '/') . $consentAction;
$scopeGrant = [];
foreach (explode(' ', $scope) as $s) {
    if ($s !== '') {
        $scopeGrant[$s] = $s;
    }
}
try {
    $consentPostResp = $http->post($consentActionUrl, [
        'form_params' => [
            'csrf_token_form' => $consentCsrf,
            'proceed' => '1',
            'scope' => $scopeGrant,
        ],
        'allow_redirects' => false,
    ]);
} catch (GuzzleException $e) {
    fwrite(STDERR, "FAIL consent POST errored: " . $e->getMessage() . "\n");
    exit(12);
}
if ($consentPostResp->getStatusCode() !== 302) {
    fwrite(STDERR, "FAIL consent POST returned HTTP {$consentPostResp->getStatusCode()}, expected 302\n");
    fwrite(STDERR, substr((string) $consentPostResp->getBody(), 0, 2000) . "\n");
    exit(13);
}
$location = $consentPostResp->getHeaderLine('Location');
if (!str_starts_with($location, $redirectUri)) {
    fwrite(STDERR, "FAIL consent redirect does not point at registered redirect_uri (got: {$location})\n");
    exit(14);
}
$query = [];
parse_str((string) parse_url($location, PHP_URL_QUERY), $query);
if (!is_string($query['code'] ?? null)) {
    fwrite(STDERR, "FAIL consent redirect missing ?code — attribute likely in fragment or redirect_uri mismatch\n");
    fwrite(STDERR, "Location: {$location}\n");
    exit(15);
}
$authorizationCode = $query['code'];
echo "       OK  (code captured)\n\n";

// ---------------------------------------------------------------------------
// 6. POST /token with the WRONG verifier. The server computes SHA256 of
//    verifier B and compares against stored challenge A → mismatch →
//    invalid_grant. A regression that skipped the check would return 200
//    with an access_token.
// ---------------------------------------------------------------------------
echo "[7/7] NEGATIVE — POST /token with WRONG verifier B must be rejected...\n";
try {
    $tokenResp = $http->post('/oauth2/default/token', [
        'form_params' => [
            'grant_type' => 'authorization_code',
            'code' => $authorizationCode,
            'redirect_uri' => $redirectUri,
            'client_id' => $clientId,
            'code_verifier' => $verifierB,
        ],
        'allow_redirects' => false,
    ]);
} catch (GuzzleException $e) {
    fwrite(STDERR, "FAIL /token errored: " . $e->getMessage() . "\n");
    exit(16);
}
if ($tokenResp->getStatusCode() === 200) {
    fwrite(STDERR, "FAIL /token accepted a wrong code_verifier — PKCE S256 check regression\n");
    fwrite(STDERR, substr((string) $tokenResp->getBody(), 0, 2000) . "\n");
    exit(17);
}
$tokenBody = json_decode((string) $tokenResp->getBody(), true);
if (!is_array($tokenBody) || ($tokenBody['error'] ?? null) !== 'invalid_grant') {
    fwrite(STDERR, "FAIL /token rejected wrong verifier with wrong error code (expected invalid_grant)\n");
    fwrite(STDERR, "HTTP " . $tokenResp->getStatusCode() . "\n");
    fwrite(STDERR, substr((string) $tokenResp->getBody(), 0, 2000) . "\n");
    exit(18);
}
echo "       OK  (invalid_grant as expected)\n\n";

echo "PASS  oe-module-api-explorer PKCE-mismatch negative succeeded.\n";
exit(0);
