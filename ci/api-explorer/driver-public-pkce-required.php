<?php

/**
 * oe-module-api-explorer CI public-client-must-use-PKCE negative.
 *
 * Mirror of driver-pkce-negative.php but for the omit-entirely case.
 * driver-pkce-negative tests "verifier doesn't match challenge";
 * this driver tests "no challenge at all" — the server must refuse
 * to issue an authorization code to a public client without PKCE.
 *
 * SMART App Launch 2.x § and OAuth 2.1 require PKCE for public
 * clients. A regression that silently downgraded the requirement
 * (e.g. a refactor that treated PKCE as optional for public
 * clients) would let an attacker who intercepted a /authorize URL
 * complete the token exchange with no code_verifier at all — a
 * strictly worse state than the wrong-verifier case the sibling
 * driver already covers.
 *
 *   1. DCR a public client (patient scope triggers public-client
 *      DCR branch — no client_secret issued).
 *   2. Enable client in DB.
 *   3. GET /authorize WITHOUT code_challenge / code_challenge_method.
 *      Server must refuse. Rejection shape may be either:
 *        - Immediate 4xx JSON at /authorize (validateAuthorizationRequest
 *          fires before login prompt)
 *        - 302 back to redirect_uri with ?error=invalid_request
 *      Both are acceptable OAuth-compliant rejections; the critical
 *      property is "no auth code gets issued without a challenge."
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

echo "oe-module-api-explorer public-client-must-use-PKCE negative smoke\n";
echo "  base = {$baseUrl}\n\n";

$http = new Client([
    'base_uri' => $baseUrl,
    'verify' => false,
    'http_errors' => false,
    'cookies' => new CookieJar(),
    'timeout' => 60,
    'allow_redirects' => false, // critical — need to see the raw /authorize response
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

// Patient-only scope → public-client DCR branch. No launch/patient so no
// standalone patient-select detour. Same shape driver-pkce-negative uses.
$scope = 'openid fhirUser patient/Patient.read';

echo "[1/3] DCR public client...\n";
try {
    $regResponse = $http->post('/oauth2/default/registration', [
        'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
        'json' => [
            'application_type' => 'web',
            'client_name' => 'ApiExplorerSmoke-public-pkce-req-' . bin2hex(random_bytes(4)),
            'token_endpoint_auth_method' => 'client_secret_post',
            'redirect_uris' => [$redirectUri],
            'contacts' => ['smoke@test.example'],
            'scope' => $scope,
        ],
    ]);
} catch (GuzzleException $e) {
    fwrite(STDERR, "FAIL DCR errored: " . $e->getMessage() . "\n");
    exit(3);
}
$regBody = json_decode((string) $regResponse->getBody(), true);
if (!is_array($regBody) || !is_string($regBody['client_id'] ?? null)) {
    fwrite(STDERR, "FAIL DCR response missing client_id\n");
    exit(4);
}
$clientId = $regBody['client_id'];
$cleanupClientIds[] = $clientId;

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
    fwrite(STDERR, "FAIL UPDATE returned {$rc}\n");
    exit(5);
}
echo "       OK  (client_id = {$clientId})\n\n";

// ---------------------------------------------------------------------------
// GET /authorize WITHOUT code_challenge.
// ---------------------------------------------------------------------------
echo "[2/3] GET /authorize WITHOUT code_challenge (public client) — expect refusal...\n";
$state = bin2hex(random_bytes(8));
$authParams = [
    'client_id' => $clientId,
    'redirect_uri' => $redirectUri,
    'response_type' => 'code',
    'scope' => $scope,
    'state' => $state,
    'nonce' => bin2hex(random_bytes(8)),
    // Deliberately NO code_challenge / code_challenge_method.
];
try {
    $authResp = $http->get('/oauth2/default/authorize?' . http_build_query($authParams));
} catch (GuzzleException $e) {
    fwrite(STDERR, "FAIL /authorize errored: " . $e->getMessage() . "\n");
    exit(6);
}
$authStatus = $authResp->getStatusCode();
$authBody = (string) $authResp->getBody();
$location = $authResp->getHeaderLine('Location');

// ---------------------------------------------------------------------------
// The server has two OAuth-compliant ways to refuse:
//   (a) 4xx JSON body with error=invalid_request (authorization endpoint
//       refuses pre-redirect because PKCE validation fires before the
//       user is identified).
//   (b) 302 to redirect_uri?error=invalid_request (post-redirect refusal
//       after the authorize request is otherwise valid but PKCE is
//       missing). Query must NOT carry a `code`.
//
// Either shape is acceptable; what MUST NOT happen is:
//   - 200 landing on the login page (would mean PKCE check hasn't fired yet;
//     login + consent would then issue a code without a challenge bound to it)
//   - 302 to redirect_uri with ?code=... (code issued without PKCE binding —
//     the regression this driver exists to catch)
// ---------------------------------------------------------------------------
echo "[3/3] Verify refusal shape — no code issued, no login page served...\n";

$sawCode = false;
if ($authStatus === 302 && str_starts_with($location, $redirectUri)) {
    parse_str((string) parse_url($location, PHP_URL_QUERY), $locQuery);
    if (isset($locQuery['code'])) {
        $sawCode = true;
    }
}

$sawLoginPage = str_contains($authBody, 'userLogin') || str_contains(strtolower($authBody), 'password');
// The login page always contains `userLogin` (form id). The post-redirect
// error page / JSON error body does not. If we got HTML 200 and the
// login form is present, PKCE check didn't fire — that's the regression.

if ($sawCode) {
    fwrite(STDERR, "FAIL /authorize issued an authorization code without code_challenge — PKCE enforcement regression\n");
    fwrite(STDERR, "HTTP {$authStatus}, Location: {$location}\n");
    exit(7);
}
if ($authStatus === 200 && $sawLoginPage) {
    fwrite(STDERR, "FAIL /authorize served the login page without PKCE challenge — public-client PKCE check didn't fire before user interaction\n");
    fwrite(STDERR, "HTTP {$authStatus}, body starts: " . substr($authBody, 0, 500) . "\n");
    exit(8);
}

// Accept only the two RFC-compliant refusal shapes. Rabbit flagged
// that a generic "else: pass" branch would silently accept server
// faults (HTTP 500, empty 200 with no login page) as if they were
// proper refusals — those shapes don't prove PKCE enforcement, they
// just prove the server crashed or returned unrelated content before
// reaching the auth code issuance.
if ($authStatus === 302 && str_starts_with($location, $redirectUri)) {
    // Branch A: 302 back to the client's redirect_uri with
    //           ?error=invalid_request in the query. RFC 6749
    //           §4.1.2.1 specifies invalid_request for missing
    //           required parameters; pinning to exactly that code
    //           (not accepting any error) catches a regression that
    //           starts returning a different code (e.g. invalid_client
    //           because the public-client check moved before the
    //           PKCE check) which would mean the gate is firing on
    //           a different code path than the one we meant to test.
    parse_str((string) parse_url($location, PHP_URL_QUERY), $locQuery);
    if (($locQuery['error'] ?? null) !== 'invalid_request') {
        fwrite(STDERR, "FAIL /authorize 302 error was not invalid_request — got: " . ($locQuery['error'] ?? '(none)') . "\n");
        fwrite(STDERR, "Location: {$location}\n");
        exit(9);
    }
    echo "       OK  (HTTP 302 → redirect_uri?error=invalid_request)\n\n";
} elseif ($authStatus >= 400 && $authStatus < 500) {
    // Branch B: 4xx with a JSON error body. Same pinning — require
    // error=invalid_request specifically.
    $body = json_decode($authBody, true);
    if (!is_array($body) || ($body['error'] ?? null) !== 'invalid_request') {
        fwrite(STDERR, "FAIL /authorize 4xx error was not invalid_request — got: " . (is_array($body) ? ($body['error'] ?? '(no error field)') : '(non-JSON body)') . "\n");
        fwrite(STDERR, substr($authBody, 0, 2000) . "\n");
        exit(10);
    }
    echo "       OK  (HTTP {$authStatus}, error=invalid_request)\n\n";
} else {
    // Any other shape (5xx, 3xx-not-to-redirect-uri, 200-without-
    // login-page) is NOT a proper OAuth refusal and should fail the
    // smoke so the actual response is surfaced for debugging.
    fwrite(
        STDERR,
        "FAIL /authorize returned unexpected shape — neither a 4xx JSON OAuth error nor a 302 to "
            . "the client's redirect_uri. HTTP {$authStatus}, Location header: "
            . ($location !== '' ? $location : '(none)') . "\n"
    );
    fwrite(STDERR, substr($authBody, 0, 2000) . "\n");
    exit(11);
}

echo "PASS  oe-module-api-explorer public-client-must-use-PKCE negative succeeded.\n";
exit(0);
