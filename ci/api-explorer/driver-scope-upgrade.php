<?php

/**
 * oe-module-api-explorer CI scope-upgrade refusal negative.
 *
 * RFC 6749 §6 — a token refresh may request a NARROWER (or equal)
 * scope set than the originally-issued refresh token, but MUST NOT
 * request a broader one. A regression that stopped checking this
 * would let a client upgrade its own bearer by passing an inflated
 * scope list at refresh time — privilege escalation via the token
 * endpoint.
 *
 * Driven via the password grant + offline_access (same pattern as
 * driver-refresh.php) because that is the simplest server-side
 * server-to-server shape that issues a refresh token the external
 * client can present back. The scope-upgrade property fires at the
 * refresh step regardless of the initial grant that issued the
 * refresh token.
 *
 *   1. DCR + DB-flip to a password+refresh_token confidential client
 *      with a NARROW scope (only user/Patient.read).
 *   2. Mint access+refresh via password grant.
 *   3. (NEGATIVE) POST /token grant_type=refresh_token with a
 *      BROADER scope (adds user/CareTeam.read). Must be refused —
 *      expected error is invalid_scope (RFC 6749 §5.2). The server
 *      must NOT issue a new access_token carrying the upgraded
 *      scope set. Fall-back acceptable: server narrows back to the
 *      original scope (silent narrowing is also RFC-compliant per
 *      §6); checked below.
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

echo "oe-module-api-explorer scope-upgrade negative smoke\n";
echo "  base = {$baseUrl}\n\n";

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

// Narrow initial scope: Patient.read only.
$initialScope = 'openid offline_access api:oemr api:fhir user/Patient.read';
// Upgrade attempt: adds user/CareTeam.read. Any additional scope works;
// CareTeam picked because it's a US Core resource with a stable read path.
$upgradedScope = 'openid offline_access api:oemr api:fhir user/Patient.read user/CareTeam.read';

echo "[1/3] DCR + enable password+refresh_token client with NARROW scope...\n";
try {
    $regResponse = $http->post('/oauth2/default/registration', [
        'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
        'json' => [
            'application_type' => 'private',
            'client_name' => 'ApiExplorerSmoke-scope-upgrade-' . bin2hex(random_bytes(4)),
            'token_endpoint_auth_method' => 'client_secret_post',
            'redirect_uris' => ['https://openemr/devtools/oe-module-api-explorer/oauth_client.php'],
            'contacts' => ['smoke@test.example'],
            'scope' => $initialScope,
        ],
        'allow_redirects' => false,
    ]);
} catch (GuzzleException $e) {
    fwrite(STDERR, "FAIL DCR errored: " . $e->getMessage() . "\n");
    exit(3);
}
$regBody = json_decode((string) $regResponse->getBody(), true);
if (!is_array($regBody) || !is_string($regBody['client_id'] ?? null) || !is_string($regBody['client_secret'] ?? null)) {
    fwrite(STDERR, "FAIL DCR response missing client_id/client_secret\n");
    exit(4);
}
$clientId = $regBody['client_id'];
$clientSecret = $regBody['client_secret'];
$cleanupClientIds[] = $clientId;

$sql = sprintf(
    "UPDATE oauth_clients SET grant_types = 'password|refresh_token', is_enabled = 1 WHERE client_id = '%s'",
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
    fwrite(STDERR, "FAIL UPDATE returned {$rc}\n");
    exit(5);
}
echo "       OK  (client_id = {$clientId})\n\n";

// ---------------------------------------------------------------------------
// Mint access + refresh at the narrow scope.
// ---------------------------------------------------------------------------
echo "[2/3] Mint bearer + refresh token with narrow scope...\n";
try {
    $tokenResp = $http->post('/oauth2/default/token', [
        'form_params' => [
            'grant_type' => 'password',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'scope' => $initialScope,
            'user_role' => 'users',
            'username' => 'admin',
            'password' => 'pass',
        ],
        'allow_redirects' => false,
    ]);
} catch (GuzzleException $e) {
    fwrite(STDERR, "FAIL initial token errored: " . $e->getMessage() . "\n");
    exit(6);
}
if ($tokenResp->getStatusCode() !== 200) {
    fwrite(STDERR, "FAIL initial token returned HTTP {$tokenResp->getStatusCode()}\n");
    fwrite(STDERR, substr((string) $tokenResp->getBody(), 0, 2000) . "\n");
    exit(7);
}
$tokenBody = json_decode((string) $tokenResp->getBody(), true);
if (!is_array($tokenBody) || !is_string($tokenBody['refresh_token'] ?? null)) {
    fwrite(STDERR, "FAIL initial token response missing refresh_token\n");
    exit(8);
}
$refreshToken = $tokenBody['refresh_token'];
echo "       OK  (narrow bearer + refresh issued)\n\n";

// ---------------------------------------------------------------------------
// NEGATIVE — refresh with BROADER scope.
//
// Two RFC-compliant server behaviors are acceptable:
//   (a) Hard refuse with invalid_scope (RFC 6749 §5.2) + 4xx status.
//       This is the clearest signal and what most compliant servers
//       do. League OAuth2's built-in behavior.
//   (b) Silent narrowing — issue a new bearer with ONLY the originally-
//       authorized scope set (user/Patient.read). RFC 6749 §6 allows
//       this; §3.3 requires the token response's scope field to
//       accurately describe what was issued. Verify the issued scope
//       set is a subset of the original.
//
// What MUST NOT happen is (c): issue a new bearer with the upgraded
// scope set actually granted. That's the privilege-escalation
// regression this driver exists to catch.
// ---------------------------------------------------------------------------
echo "[3/3] NEGATIVE — refresh with BROADER scope must not upgrade the bearer...\n";
try {
    $refreshResp = $http->post('/oauth2/default/token', [
        'form_params' => [
            'grant_type' => 'refresh_token',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $refreshToken,
            'scope' => $upgradedScope,
        ],
        'allow_redirects' => false,
    ]);
} catch (GuzzleException $e) {
    fwrite(STDERR, "FAIL refresh errored: " . $e->getMessage() . "\n");
    exit(9);
}
$refreshStatus = $refreshResp->getStatusCode();
$refreshBody = json_decode((string) $refreshResp->getBody(), true);

if ($refreshStatus >= 400 && $refreshStatus < 500) {
    // Branch (a) — hard refuse. Must be invalid_scope specifically (RFC
    // 6749 §5.2). Rabbit flagged that accepting invalid_grant or
    // invalid_request would let an earlier-firing error (bad refresh
    // token, missing form field) masquerade as a scope-validation
    // rejection. If OpenEMR's League 8.5.5 ever starts mapping
    // scope-upgrade to one of those codes (§8.1a drift), this
    // assertion fails loudly and we re-evaluate rather than quietly
    // pass through a weaker property.
    if (!is_array($refreshBody) || ($refreshBody['error'] ?? null) !== 'invalid_scope') {
        fwrite(STDERR, "FAIL refresh rejected but not with invalid_scope — a different error fired before scope validation ran\n");
        fwrite(STDERR, "HTTP {$refreshStatus}\n");
        fwrite(STDERR, substr((string) $refreshResp->getBody(), 0, 2000) . "\n");
        exit(10);
    }
    echo "       OK  (HTTP {$refreshStatus}, invalid_scope — hard refuse of scope upgrade)\n\n";
} elseif ($refreshStatus === 200) {
    // Branch (b) — silent narrowing. Verify the issued scope is a subset
    // of the original (initial-scope-granted + no new upgraded scopes).
    if (!is_array($refreshBody) || !is_string($refreshBody['scope'] ?? null) || !is_string($refreshBody['access_token'] ?? null)) {
        fwrite(STDERR, "FAIL refresh 200 but response malformed (missing scope or access_token)\n");
        fwrite(STDERR, substr((string) $refreshResp->getBody(), 0, 2000) . "\n");
        exit(11);
    }
    $issued = array_filter(explode(' ', $refreshBody['scope']));
    $original = array_filter(explode(' ', $initialScope));
    $extras = array_diff($issued, $original);
    if ($extras !== []) {
        fwrite(STDERR, "FAIL refresh 200 issued a bearer with scopes NOT in the original grant — scope-upgrade regression\n");
        fwrite(STDERR, "Original: " . implode(',', $original) . "\n");
        fwrite(STDERR, "Issued:   " . implode(',', $issued) . "\n");
        fwrite(STDERR, "Extras:   " . implode(',', $extras) . "\n");
        exit(12);
    }
    echo "       OK  (HTTP 200, server silently narrowed back to original scope: " . substr($refreshBody['scope'], 0, 120) . ")\n\n";
} else {
    fwrite(STDERR, "FAIL refresh returned unexpected HTTP {$refreshStatus} (not 200 and not 4xx)\n");
    fwrite(STDERR, substr((string) $refreshResp->getBody(), 0, 2000) . "\n");
    exit(13);
}

echo "PASS  oe-module-api-explorer scope-upgrade negative succeeded.\n";
exit(0);
