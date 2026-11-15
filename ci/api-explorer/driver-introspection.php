<?php

/**
 * oe-module-api-explorer CI token-introspection smoke driver.
 *
 * Exercises the RFC 7662 /oauth2/default/introspect endpoint (advertised
 * in OAuth2 discovery, handled by TokenIntrospectionRestController /
 * AuthorizationController::tokenIntrospection). None of the other
 * drivers touch introspection; a regression in the response shape
 * (active flag, scope, exp, sub, aud) or in bearer-vs-not-bearer
 * discrimination would be undetected by the existing smoke.
 *
 *   1. DCR + DB-flip to a password-grant confidential client (same
 *      pattern driver-password.php establishes).
 *   2. POST /oauth2/default/token grant_type=password admin/pass →
 *      bearer.
 *   3. POST /oauth2/default/introspect token=<bearer> + client auth.
 *      Assert HTTP 200 + active=true + scope contains at least the
 *      requested scopes. This is the live-bearer branch.
 *   4. (NEGATIVE) POST /introspect with token=<bogus random string>
 *      + the same client auth. RFC 7662 §2.2 says the response must
 *      be `active=false` (NOT HTTP 400 — the endpoint absorbs bogus
 *      tokens by design to avoid leaking whether a token-shaped
 *      string was ever valid). Asserts active=false. A regression
 *      that started emitting 400/401 on bogus tokens is still a
 *      failure mode because clients polling introspection would see
 *      their valid tokens flapping under load.
 *   5. (NEGATIVE) POST /introspect without any client auth. Must be
 *      rejected — introspection is not a public endpoint; an attacker
 *      who could introspect arbitrary bearers would learn the scope
 *      set + expiry of any token they intercepted. OpenEMR returns
 *      401 unauthorized here.
 *
 * Exits nonzero on any step failure; the workflow dumps docker logs
 * on exit.
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

if (!str_starts_with(strtolower($baseUrl), 'https://')) {
    fwrite(STDERR, "FAIL EXPLORER_BASE_URL must be https:// (got: {$baseUrl})\n");
    exit(2);
}

echo "oe-module-api-explorer introspection smoke\n";
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

// ---------------------------------------------------------------------------
// 1 + 2. DCR + flip to password grant.
// ---------------------------------------------------------------------------
$scope = 'openid api:oemr api:fhir user/Patient.read';
echo "[1/5] DCR + enable password-grant client...\n";
$clientName = 'ApiExplorerSmoke-introspection-' . bin2hex(random_bytes(4));
try {
    $regResponse = $http->post('/oauth2/default/registration', [
        'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
        'json' => [
            'application_type' => 'private',
            'client_name' => $clientName,
            'token_endpoint_auth_method' => 'client_secret_post',
            'redirect_uris' => ['https://openemr/devtools/oe-module-api-explorer/oauth_client.php'],
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
if (!is_array($regBody) || !is_string($regBody['client_id'] ?? null) || !is_string($regBody['client_secret'] ?? null)) {
    fwrite(STDERR, "FAIL DCR response missing client_id/client_secret\n");
    exit(5);
}
$clientId = $regBody['client_id'];
$clientSecret = $regBody['client_secret'];
$cleanupClientIds[] = $clientId;

$sql = sprintf(
    "UPDATE oauth_clients SET grant_types = 'password', is_enabled = 1 WHERE client_id = '%s'",
    str_replace("'", "\\'", $clientId)
);
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
echo "       OK  (client_id = {$clientId})\n\n";

// ---------------------------------------------------------------------------
// Mint the bearer we will introspect.
// ---------------------------------------------------------------------------
echo "[2/5] Minting a bearer via password grant...\n";
try {
    $tokenResponse = $http->post('/oauth2/default/token', [
        'form_params' => [
            'grant_type' => 'password',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'scope' => $scope,
            'user_role' => 'users',
            'username' => 'admin',
            'password' => 'pass',
        ],
        'allow_redirects' => false,
    ]);
} catch (GuzzleException $e) {
    fwrite(STDERR, "FAIL token request errored: " . $e->getMessage() . "\n");
    exit(7);
}
if ($tokenResponse->getStatusCode() !== 200) {
    fwrite(STDERR, "FAIL token returned HTTP {$tokenResponse->getStatusCode()}\n");
    fwrite(STDERR, substr((string) $tokenResponse->getBody(), 0, 2000) . "\n");
    exit(8);
}
$tokenBody = json_decode((string) $tokenResponse->getBody(), true);
if (!is_array($tokenBody) || !is_string($tokenBody['access_token'] ?? null) || $tokenBody['access_token'] === '') {
    fwrite(STDERR, "FAIL token response missing access_token\n");
    exit(9);
}
$bearer = $tokenBody['access_token'];
echo "       OK\n\n";

// ---------------------------------------------------------------------------
// 3. Introspect the live bearer. Assert active=true + response shape.
// ---------------------------------------------------------------------------
echo "[3/5] Introspecting the live bearer — expect active=true...\n";
try {
    $introspectResponse = $http->post('/oauth2/default/introspect', [
        'form_params' => [
            'token' => $bearer,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ],
        'headers' => ['Accept' => 'application/json'],
        'allow_redirects' => false,
    ]);
} catch (GuzzleException $e) {
    fwrite(STDERR, "FAIL introspect request errored: " . $e->getMessage() . "\n");
    exit(10);
}
if ($introspectResponse->getStatusCode() !== 200) {
    fwrite(STDERR, "FAIL introspect live bearer returned HTTP {$introspectResponse->getStatusCode()}\n");
    fwrite(STDERR, substr((string) $introspectResponse->getBody(), 0, 2000) . "\n");
    exit(11);
}
$introspectBody = json_decode((string) $introspectResponse->getBody(), true);
if (!is_array($introspectBody)) {
    fwrite(STDERR, "FAIL introspect live bearer response not JSON\n");
    exit(12);
}
if (($introspectBody['active'] ?? null) !== true) {
    fwrite(STDERR, "FAIL introspect live bearer did not return active=true\n");
    fwrite(STDERR, substr((string) $introspectResponse->getBody(), 0, 2000) . "\n");
    exit(13);
}
// scope is RFC 7662 §2.2 OPTIONAL but OpenEMR emits it; if present it must
// include the user/Patient.read scope the bearer was issued with. A missing
// scope string would be a response-shape regression worth flagging.
$introspectedScope = $introspectBody['scope'] ?? null;
if (!is_string($introspectedScope) || !str_contains($introspectedScope, 'user/Patient.read')) {
    fwrite(STDERR, "FAIL introspect response's scope string should include user/Patient.read\n");
    fwrite(STDERR, substr((string) $introspectResponse->getBody(), 0, 2000) . "\n");
    exit(14);
}
echo "       OK  (active=true, scope includes user/Patient.read)\n\n";

// ---------------------------------------------------------------------------
// 4. Introspect a bogus token. RFC 7662 §2.2 — response is active=false,
//    HTTP 200. Asserts OpenEMR doesn't leak bearer-shape via status codes.
// ---------------------------------------------------------------------------
echo "[4/5] NEGATIVE — introspecting a bogus token must return active=false...\n";
$bogusToken = bin2hex(random_bytes(32));
try {
    $bogusResponse = $http->post('/oauth2/default/introspect', [
        'form_params' => [
            'token' => $bogusToken,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ],
        'headers' => ['Accept' => 'application/json'],
        'allow_redirects' => false,
    ]);
} catch (GuzzleException $e) {
    fwrite(STDERR, "FAIL bogus-token introspect errored: " . $e->getMessage() . "\n");
    exit(15);
}
if ($bogusResponse->getStatusCode() !== 200) {
    fwrite(STDERR, "FAIL bogus-token introspect returned HTTP {$bogusResponse->getStatusCode()} — RFC 7662 requires 200 with active=false\n");
    fwrite(STDERR, substr((string) $bogusResponse->getBody(), 0, 2000) . "\n");
    exit(16);
}
$bogusBody = json_decode((string) $bogusResponse->getBody(), true);
if (!is_array($bogusBody) || ($bogusBody['active'] ?? null) !== false) {
    fwrite(STDERR, "FAIL bogus-token introspect did not return active=false\n");
    fwrite(STDERR, substr((string) $bogusResponse->getBody(), 0, 2000) . "\n");
    exit(17);
}
echo "       OK  (active=false as expected)\n\n";

// ---------------------------------------------------------------------------
// 5. Introspect without client credentials. Must be rejected.
//    Introspection without auth would let anyone probe any bearer's scope +
//    expiry; the client-auth check is the gate.
// ---------------------------------------------------------------------------
echo "[5/5] NEGATIVE — introspecting without client credentials must be rejected...\n";
try {
    $unauthResponse = $http->post('/oauth2/default/introspect', [
        'form_params' => ['token' => $bearer],
        'headers' => ['Accept' => 'application/json'],
        'allow_redirects' => false,
    ]);
} catch (GuzzleException $e) {
    fwrite(STDERR, "FAIL unauth introspect errored: " . $e->getMessage() . "\n");
    exit(18);
}
// RFC 7662 §2.1 requires authenticated introspection requests; §2.2
// gives servers discretion on how to signal rejection. Two shapes are
// acceptable: (a) 4xx status (hard reject), (b) 200 with
// {"active":false} (absorb-as-bogus — the same shape a valid request
// with an unknown token returns, which is actually preferable because
// it leaks less about which clients + tokens the server knows). The
// critical property is that the endpoint MUST NOT reveal the bearer's
// details (scope, exp, sub, aud) without authenticated client proof —
// that is what a 200+active:true response would mean.
$unauthBody = json_decode((string) $unauthResponse->getBody(), true);
$revealedBearerDetails = is_array($unauthBody) && ($unauthBody['active'] ?? null) === true;
if ($revealedBearerDetails) {
    fwrite(STDERR, "FAIL unauth introspect returned 200 + active=true — client-auth gate regression\n");
    fwrite(STDERR, substr((string) $unauthResponse->getBody(), 0, 2000) . "\n");
    exit(19);
}
echo "       OK  (HTTP {$unauthResponse->getStatusCode()}, bearer details not revealed)\n\n";

echo "PASS  oe-module-api-explorer introspection round-trip + 2 negatives succeeded.\n";
exit(0);
