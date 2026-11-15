<?php

/**
 * oe-module-api-explorer CI scope-insufficient operation negative.
 *
 * Proves scope gating at the FHIR layer from a real external
 * client: a bearer issued with `user/QuestionnaireResponse.rs`
 * only must be refused on a QuestionnaireResponse POST (which
 * requires `.cu`). All five sibling drivers' write operations
 * use correctly-scoped bearers; a regression that stopped
 * enforcing scope-vs-operation at the FHIR endpoint would let
 * a read-only bearer mutate data. The in-tree ScopeConstraintFilter
 * tests cover this at the unit tier; this driver validates it
 * from a Guzzle-driven third-party-client angle.
 *
 *   1. DCR + DB-flip password-grant confidential client with
 *      `user/QuestionnaireResponse.rs` scope (read + search only,
 *      NO create/update).
 *   2. Enable fhir_patient_write_mode globals — wait, those
 *      gate PATIENT writes (via patient_review_request hold).
 *      user/ writes on a staff-scoped bearer land directly in
 *      the chart with no mode-gate. So this smoke needs no
 *      extra globals beyond what install_configure + seed-
 *      globals.sql set.
 *   3. Positive control — GET /fhir/QuestionnaireResponse with
 *      the bearer. Should succeed (scope grants .r).
 *   4. (NEGATIVE) POST a minimal QuestionnaireResponse with
 *      the same bearer. Scope gate must refuse — HTTP 401 or
 *      403, response NOT 2xx. Catches a regression in
 *      ScopeConstraintFilter or AccessTokenAuthorization.
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

echo "oe-module-api-explorer scope-insufficient negative smoke\n";
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

// READ-ONLY scope set (.rs = read + search per SMART v2 action letters;
// see ScopePermissionParser::ACTION_LABELS). Deliberately no .cu —
// the bearer the server issues here must be unable to POST
// QuestionnaireResponse. GET FHIR search needs .s, so .r alone would
// also fail the positive control; .rs is the minimum read-only shape.
$scope = 'openid api:fhir user/QuestionnaireResponse.rs';

echo "[1/4] DCR + enable read-only password-grant client...\n";
try {
    $regResponse = $http->post('/oauth2/default/registration', [
        'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
        'json' => [
            'application_type' => 'private',
            'client_name' => 'ApiExplorerSmoke-scope-insuff-' . bin2hex(random_bytes(4)),
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
$regBody = json_decode((string) $regResponse->getBody(), true);
if (!is_array($regBody) || !is_string($regBody['client_id'] ?? null) || !is_string($regBody['client_secret'] ?? null)) {
    fwrite(STDERR, "FAIL DCR response missing client_id/client_secret\n");
    fwrite(STDERR, substr((string) $regResponse->getBody(), 0, 2000) . "\n");
    exit(4);
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
    fwrite(STDERR, "FAIL UPDATE returned {$rc}\n");
    exit(5);
}
echo "       OK  (client_id = {$clientId})\n\n";

echo "[2/4] Mint a bearer with user/QuestionnaireResponse.rs scope...\n";
try {
    $tokenResp = $http->post('/oauth2/default/token', [
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
    fwrite(STDERR, "FAIL token errored: " . $e->getMessage() . "\n");
    exit(6);
}
if ($tokenResp->getStatusCode() !== 200) {
    fwrite(STDERR, "FAIL token did not mint (HTTP {$tokenResp->getStatusCode()})\n");
    fwrite(STDERR, substr((string) $tokenResp->getBody(), 0, 2000) . "\n");
    exit(7);
}
$tokenBody = json_decode((string) $tokenResp->getBody(), true);
if (!is_array($tokenBody) || !is_string($tokenBody['access_token'] ?? null)) {
    fwrite(STDERR, "FAIL token response missing access_token\n");
    exit(8);
}
$bearer = $tokenBody['access_token'];
// Verify the server narrowed the issued scope to the requested read-only
// set — if OpenEMR accidentally issued .cu scopes the client didn't ask
// for, the negative test below would be testing the wrong property.
$grantedScope = $tokenBody['scope'] ?? '';
if (!is_string($grantedScope) || str_contains($grantedScope, 'QuestionnaireResponse.c') || str_contains($grantedScope, 'QuestionnaireResponse.u')) {
    fwrite(STDERR, "FAIL issued bearer unexpectedly carries .c or .u on QuestionnaireResponse (got scope: {$grantedScope})\n");
    exit(9);
}
echo "       OK  (bearer scope: " . substr($grantedScope, 0, 120) . ")\n\n";

// -------------------------------------------------------------------------
// Positive control — the bearer CAN read. If the server-side scope filter
// has regressed so hard that read also fails, we'd exit here and surface
// the broader regression before confusing it with the scope-insufficient
// signal we actually want to test.
// -------------------------------------------------------------------------
echo "[3/4] Positive control — GET QuestionnaireResponse with .r bearer...\n";
try {
    $readResp = $http->get('/apis/default/fhir/QuestionnaireResponse', [
        'headers' => [
            'Authorization' => 'Bearer ' . $bearer,
            'Accept' => 'application/fhir+json',
        ],
    ]);
} catch (GuzzleException $e) {
    fwrite(STDERR, "FAIL read errored: " . $e->getMessage() . "\n");
    exit(10);
}
if ($readResp->getStatusCode() !== 200) {
    fwrite(STDERR, "FAIL read with .r bearer returned HTTP {$readResp->getStatusCode()} — scope-filter regression?\n");
    fwrite(STDERR, substr((string) $readResp->getBody(), 0, 2000) . "\n");
    exit(11);
}
echo "       OK  (read succeeded)\n\n";

// -------------------------------------------------------------------------
// NEGATIVE — same bearer, write operation. Must be refused.
// -------------------------------------------------------------------------
echo "[4/4] NEGATIVE — POST QuestionnaireResponse with .r-only bearer must be refused...\n";
$minimalQr = json_encode([
    'resourceType' => 'QuestionnaireResponse',
    'status' => 'completed',
    'item' => [[
        'linkId' => '1',
        'answer' => [['valueString' => 'scope-insufficient-negative-smoke']],
    ]],
], JSON_THROW_ON_ERROR);
try {
    $writeResp = $http->post('/apis/default/fhir/QuestionnaireResponse', [
        'headers' => [
            'Authorization' => 'Bearer ' . $bearer,
            'Content-Type' => 'application/fhir+json',
            'Accept' => 'application/fhir+json',
        ],
        'body' => $minimalQr,
    ]);
} catch (GuzzleException $e) {
    fwrite(STDERR, "FAIL write errored: " . $e->getMessage() . "\n");
    exit(12);
}
$writeStatus = $writeResp->getStatusCode();
if ($writeStatus >= 200 && $writeStatus < 300) {
    fwrite(STDERR, "FAIL write with .r-only bearer was ACCEPTED (HTTP {$writeStatus}) — scope-vs-operation gate regression\n");
    fwrite(STDERR, substr((string) $writeResp->getBody(), 0, 2000) . "\n");
    exit(13);
}
if ($writeStatus !== 401 && $writeStatus !== 403) {
    fwrite(STDERR, "FAIL write refused with unexpected HTTP {$writeStatus} (expected 401 or 403)\n");
    fwrite(STDERR, substr((string) $writeResp->getBody(), 0, 2000) . "\n");
    exit(14);
}
echo "       OK  (HTTP {$writeStatus} refusal as expected)\n\n";

echo "PASS  oe-module-api-explorer scope-insufficient negative succeeded.\n";
exit(0);
