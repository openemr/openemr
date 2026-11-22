<?php

/**
 * oe-module-api-explorer CI refresh-token smoke driver.
 *
 * Fills the "no dedicated flow in this smoke" gap the ci/api-explorer
 * README calls out. All five sibling drivers (driver.php,
 * driver-browser.php, driver-password.php, driver-fhir-writes.php,
 * driver-portal.php) exercise initial token issuance only; none
 * exercises the refresh-token exchange. This driver closes that gap
 * from a real third-party client angle:
 *
 *   1. POST /oauth2/default/registration — RFC 7591 DCR (same pattern
 *      driver-password.php uses) requesting openid + offline_access +
 *      api:oemr + api:fhir + user/Patient.read, confidential auth.
 *   2. UPDATE oauth_clients SET grant_types='password|refresh_token',
 *      is_enabled=1 — DCR drops the admin-only password grant; flip
 *      the row in DB. offline_access tells OpenEMR to issue a
 *      refresh_token alongside the access_token.
 *   3. POST /oauth2/default/token grant_type=password admin/pass +
 *      scope including offline_access. Assert the response carries
 *      BOTH access_token AND refresh_token.
 *   4. POST /oauth2/default/token grant_type=refresh_token with the
 *      refresh_token from step 3. Assert a new access_token + a new
 *      refresh_token (single-use rotation). The returned bearer must
 *      authenticate against FHIR Patient.
 *   5. GET /apis/default/fhir/Patient with the NEW bearer — proves
 *      the refresh-issued token carries forward all scopes the
 *      original bearer had (a regression that strips scope on refresh
 *      would surface as a 401 here).
 *   6. (NEGATIVE) POST /oauth2/default/token grant_type=refresh_token
 *      with the ORIGINAL refresh_token from step 3. OpenEMR must
 *      reject (refresh tokens are single-use per CustomRefreshTokenGrant).
 *      A regression that re-enabled multiple-use refresh would let an
 *      intercepted refresh_token be replayed arbitrarily.
 *   7. (NEGATIVE) POST /oauth2/default/token grant_type=refresh_token
 *      with the step-4-rotated refresh_token BUT a DIFFERENT client's
 *      client_id + client_secret. OpenEMR must reject — refresh tokens
 *      are bound to the client that issued them. A regression that
 *      broke that binding would let a compromised client use any
 *      issued refresh token.
 *
 * Exits nonzero on any step failure; the workflow dumps docker logs
 * on exit. The two negative steps assert `error=invalid_grant` on the
 * token-endpoint response (the standard OAuth2 error code for revoked
 * / stolen / wrong-client refresh tokens).
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

// Defensive scheme gate — same rationale as driver-password.php.
if (!str_starts_with(strtolower($baseUrl), 'https://')) {
    fwrite(STDERR, "FAIL EXPLORER_BASE_URL must be https:// (got: {$baseUrl})\n");
    exit(2);
}

echo "oe-module-api-explorer refresh-token smoke\n";
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
 * DCR a confidential client + flip its grant_types + is_enabled.
 * Mirrors driver-password.php's shape.
 *
 * @return array{string, string}  [client_id, client_secret]
 */
$registerAndEnableClient = function (string $nameSuffix, string $scope, string $grantTypes) use ($http, $mysqlHost, $mysqlUser, $mysqlPass, $mysqlDb, &$cleanupClientIds): array {
    $clientName = 'ApiExplorerSmoke-refresh-' . $nameSuffix . '-' . bin2hex(random_bytes(3));
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
        fwrite(STDERR, "FAIL DCR request errored: " . $e->getMessage() . "\n");
        exit(3);
    }
    if ($regResponse->getStatusCode() !== 200) {
        fwrite(STDERR, "FAIL DCR returned HTTP {$regResponse->getStatusCode()}\n");
        fwrite(STDERR, substr((string) $regResponse->getBody(), 0, 2000) . "\n");
        exit(4);
    }
    $regBody = json_decode((string) $regResponse->getBody(), true);
    if (!is_array($regBody) || !is_string($regBody['client_id'] ?? null) || !is_string($regBody['client_secret'] ?? null)) {
        fwrite(STDERR, "FAIL DCR response missing client_id or client_secret\n");
        exit(5);
    }
    $clientId = $regBody['client_id'];
    $clientSecret = $regBody['client_secret'];
    $cleanupClientIds[] = $clientId;

    $sql = sprintf(
        "UPDATE oauth_clients SET grant_types = '%s', is_enabled = 1 WHERE client_id = '%s'",
        str_replace("'", "\\'", $grantTypes),
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
    return [$clientId, $clientSecret];
};

// ---------------------------------------------------------------------------
// 1 + 2. DCR + DB-flip the primary client. Scope includes offline_access so
//        the token endpoint issues a refresh_token.
// ---------------------------------------------------------------------------
$scope = 'openid offline_access api:oemr api:fhir user/Patient.read';
echo "[1/7] DCR + enable primary client (password + refresh_token grants)...\n";
[$clientId, $clientSecret] = $registerAndEnableClient('primary', $scope, 'password|refresh_token');
echo "       OK  (client_id = {$clientId})\n\n";

// ---------------------------------------------------------------------------
// 3. Initial token issuance via password grant. Asserts the response
//    carries BOTH access_token AND refresh_token.
// ---------------------------------------------------------------------------
echo "[2/7] Initial password-grant token with offline_access...\n";
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
    fwrite(STDERR, "FAIL initial token request errored: " . $e->getMessage() . "\n");
    exit(7);
}
if ($tokenResponse->getStatusCode() !== 200) {
    fwrite(STDERR, "FAIL initial token returned HTTP {$tokenResponse->getStatusCode()}\n");
    fwrite(STDERR, substr((string) $tokenResponse->getBody(), 0, 2000) . "\n");
    exit(8);
}
$tokenBody = json_decode((string) $tokenResponse->getBody(), true);
if (!is_array($tokenBody) || !is_string($tokenBody['access_token'] ?? null) || $tokenBody['access_token'] === '') {
    fwrite(STDERR, "FAIL initial token response missing access_token\n");
    exit(9);
}
if (!is_string($tokenBody['refresh_token'] ?? null) || $tokenBody['refresh_token'] === '') {
    fwrite(STDERR, "FAIL initial token response missing refresh_token — offline_access scope regression?\n");
    fwrite(STDERR, substr((string) $tokenResponse->getBody(), 0, 2000) . "\n");
    exit(10);
}
$originalAccessToken = $tokenBody['access_token'];
$originalRefreshToken = $tokenBody['refresh_token'];
echo "       OK  (access + refresh issued)\n\n";

// ---------------------------------------------------------------------------
// 4. Refresh-token exchange. The rotated refresh_token replaces the first.
// ---------------------------------------------------------------------------
echo "[3/7] Exchanging refresh_token for a new access_token...\n";
try {
    $refreshResponse = $http->post('/oauth2/default/token', [
        'form_params' => [
            'grant_type' => 'refresh_token',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $originalRefreshToken,
            'scope' => $scope,
        ],
        'allow_redirects' => false,
    ]);
} catch (GuzzleException $e) {
    fwrite(STDERR, "FAIL refresh request errored: " . $e->getMessage() . "\n");
    exit(11);
}
if ($refreshResponse->getStatusCode() !== 200) {
    fwrite(STDERR, "FAIL refresh returned HTTP {$refreshResponse->getStatusCode()}\n");
    fwrite(STDERR, substr((string) $refreshResponse->getBody(), 0, 2000) . "\n");
    exit(12);
}
$refreshBody = json_decode((string) $refreshResponse->getBody(), true);
if (!is_array($refreshBody) || !is_string($refreshBody['access_token'] ?? null) || $refreshBody['access_token'] === '') {
    fwrite(STDERR, "FAIL refresh response missing access_token\n");
    exit(13);
}
if (!is_string($refreshBody['refresh_token'] ?? null) || $refreshBody['refresh_token'] === '') {
    fwrite(STDERR, "FAIL refresh response missing refresh_token (rotation)\n");
    exit(14);
}
if ($refreshBody['refresh_token'] === $originalRefreshToken) {
    fwrite(STDERR, "FAIL refresh_token was not rotated — single-use enforcement would be broken by this\n");
    exit(15);
}
$rotatedAccessToken = $refreshBody['access_token'];
$rotatedRefreshToken = $refreshBody['refresh_token'];
echo "       OK  (new access + rotated refresh issued)\n\n";

// ---------------------------------------------------------------------------
// 5. FHIR Patient read with the ROTATED access_token. Proves the refresh-
//    issued bearer carries the scope set the original did.
// ---------------------------------------------------------------------------
echo "[4/7] FHIR Patient read with the rotated access_token...\n";
try {
    $fhirResponse = $http->get('/apis/default/fhir/Patient', [
        'headers' => [
            'Authorization' => 'Bearer ' . $rotatedAccessToken,
            'Accept' => 'application/fhir+json',
        ],
    ]);
} catch (GuzzleException $e) {
    fwrite(STDERR, "FAIL FHIR request errored: " . $e->getMessage() . "\n");
    exit(16);
}
if ($fhirResponse->getStatusCode() !== 200) {
    fwrite(STDERR, "FAIL FHIR Patient read with rotated token returned HTTP {$fhirResponse->getStatusCode()}\n");
    fwrite(STDERR, substr((string) $fhirResponse->getBody(), 0, 2000) . "\n");
    exit(17);
}
$fhirRaw = (string) $fhirResponse->getBody();
if (!preg_match('/"resourceType"\s*:\s*"Bundle"/', $fhirRaw)) {
    fwrite(STDERR, "FAIL FHIR response is not a Bundle\n");
    exit(18);
}
if (!preg_match('/"total"\s*:\s*([0-9]+)/', $fhirRaw, $m) || (int) $m[1] < 1) {
    fwrite(STDERR, "FAIL FHIR Bundle total < 1\n");
    exit(19);
}
echo "       OK  (Bundle total = {$m[1]})\n\n";

// ---------------------------------------------------------------------------
// 6. (NEGATIVE) The ORIGINAL refresh_token must no longer work — single-use
//    enforcement.
//
//    Sequential replay only. A concurrent-redemption case (two refreshes
//    firing against the same unused token before the first commits its
//    invalidation) would strengthen this against a TOCTOU regression in
//    CustomRefreshTokenGrant, but the server's single-use is DB-guarded at
//    the row level and a timing-dependent test would introduce CI flake
//    without materially improving regression detection. Rabbit flagged this
//    as Trivial + Heavy lift — the trade-off doesn't pay off at the smoke
//    tier. The in-tree PasswordGrantMfaRefreshFlowTest covers refresh-token
//    single-use with a more controlled test harness.
// ---------------------------------------------------------------------------
echo "[5/7] NEGATIVE — reuse of the original refresh_token must be rejected...\n";
try {
    $replayResponse = $http->post('/oauth2/default/token', [
        'form_params' => [
            'grant_type' => 'refresh_token',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $originalRefreshToken,
            'scope' => $scope,
        ],
        'allow_redirects' => false,
    ]);
} catch (GuzzleException $e) {
    fwrite(STDERR, "FAIL replay request errored: " . $e->getMessage() . "\n");
    exit(20);
}
if ($replayResponse->getStatusCode() === 200) {
    fwrite(STDERR, "FAIL original refresh_token was accepted a second time — single-use enforcement regression\n");
    fwrite(STDERR, substr((string) $replayResponse->getBody(), 0, 2000) . "\n");
    exit(21);
}
$replayBody = json_decode((string) $replayResponse->getBody(), true);
// RFC 6749 §5.2 specifies invalid_grant for a reused/revoked token.
// OpenEMR's League 8.5.5 vendor-copy maps this to invalid_request (a
// documented spec non-compliance tracked in §8.1a of the OAuth2 audit
// doc — see also the planned CustomAuthCodeGrant fix). Accept either
// code; the critical property is "rejected, not accepted" which the
// status-code check above already verified.
if (!is_array($replayBody) || !in_array($replayBody['error'] ?? null, ['invalid_grant', 'invalid_request'], true)) {
    fwrite(STDERR, "FAIL replay rejected with unexpected error code (expected invalid_grant or invalid_request)\n");
    fwrite(STDERR, substr((string) $replayResponse->getBody(), 0, 2000) . "\n");
    exit(22);
}
echo "       OK  ({$replayBody['error']} as expected)\n\n";

// ---------------------------------------------------------------------------
// 7. DCR a second client, then try to refresh the FIRST client's rotated
//    refresh_token using the second client's credentials. Must be rejected —
//    refresh tokens are bound to the client that issued them. Catches a
//    regression that broke the client-vs-token binding check in
//    CustomRefreshTokenGrant::validateOldRefreshToken.
// ---------------------------------------------------------------------------
echo "[6/7] DCR + enable a second client for wrong-client refresh test...\n";
[$otherClientId, $otherClientSecret] = $registerAndEnableClient('other', $scope, 'password|refresh_token');
echo "       OK  (other client_id = {$otherClientId})\n\n";

echo "[7/7] NEGATIVE — refresh with wrong client credentials must be rejected...\n";
try {
    $wrongClientResponse = $http->post('/oauth2/default/token', [
        'form_params' => [
            'grant_type' => 'refresh_token',
            'client_id' => $otherClientId,
            'client_secret' => $otherClientSecret,
            'refresh_token' => $rotatedRefreshToken,
            'scope' => $scope,
        ],
        'allow_redirects' => false,
    ]);
} catch (GuzzleException $e) {
    fwrite(STDERR, "FAIL wrong-client request errored: " . $e->getMessage() . "\n");
    exit(23);
}
if ($wrongClientResponse->getStatusCode() === 200) {
    fwrite(STDERR, "FAIL refresh_token was accepted with a different client's credentials — client-binding regression\n");
    fwrite(STDERR, substr((string) $wrongClientResponse->getBody(), 0, 2000) . "\n");
    exit(24);
}
$wrongClientBody = json_decode((string) $wrongClientResponse->getBody(), true);
// Same League 8.5.5 quirk as the replay case above — accept either error
// code; the critical property is rejection.
if (!is_array($wrongClientBody) || !in_array($wrongClientBody['error'] ?? null, ['invalid_grant', 'invalid_request', 'invalid_client'], true)) {
    fwrite(STDERR, "FAIL wrong-client refresh rejected with unexpected error code\n");
    fwrite(STDERR, substr((string) $wrongClientResponse->getBody(), 0, 2000) . "\n");
    exit(25);
}
echo "       OK  ({$wrongClientBody['error']} as expected)\n\n";

echo "PASS  oe-module-api-explorer refresh-token round-trip + 2 negatives succeeded.\n";
exit(0);
