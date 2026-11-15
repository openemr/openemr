<?php

/**
 * oe-module-api-explorer CI wrong-user_role password-grant negative.
 *
 * The password grant's `user_role` form parameter (OpenEMR extension
 * on RFC 6749) tells the server which identity table to look up the
 * presented credentials against — `users` → admin/staff table,
 * `patient` → portal patient. The expected rejection matrix:
 *
 *   admin credentials + user_role=users   → ACCEPTED (driver-password)
 *   patient credentials + user_role=patient (portal-api on /oauth2)
 *                                         → ACCEPTED (driver-portal)
 *   admin credentials + user_role=patient → REJECTED (this driver)
 *   patient credentials + user_role=users → REJECTED (not tested —
 *                                           needs a portal fixture which
 *                                           belongs in driver-portal)
 *
 * A regression that stopped honoring the user_role claim (e.g. a
 * refactor that always looked admin credentials up against the
 * users table regardless of what role was requested) would let an
 * admin session be minted under a role the issuer never intended.
 *
 *   1. DCR + DB-flip password-grant confidential client.
 *   2. Positive control — password grant with user_role=users +
 *      admin/pass must succeed (end-to-end precondition).
 *   3. (NEGATIVE) Same admin credentials + user_role=patient must
 *      be rejected. Accept any 4xx with error ∈ {invalid_grant,
 *      invalid_request}. Critical property is the 2xx-vs-rejection
 *      distinction.
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

echo "oe-module-api-explorer wrong-user_role negative smoke\n";
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

$scope = 'openid api:oemr api:fhir user/Patient.read';

echo "[1/3] DCR + enable password-grant client...\n";
try {
    $regResponse = $http->post('/oauth2/default/registration', [
        'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
        'json' => [
            'application_type' => 'private',
            'client_name' => 'ApiExplorerSmoke-wrong-role-' . bin2hex(random_bytes(4)),
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

// -------------------------------------------------------------------------
// Positive control — user_role=users + admin creds must mint. Catches a
// broader regression (eg password grant disabled globally) that would
// otherwise confuse "users accepted, patient rejected" with "nothing
// accepted".
// -------------------------------------------------------------------------
echo "[2/3] Positive control — admin creds + user_role=users must mint...\n";
try {
    $positiveResp = $http->post('/oauth2/default/token', [
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
    fwrite(STDERR, "FAIL positive-control errored: " . $e->getMessage() . "\n");
    exit(6);
}
if ($positiveResp->getStatusCode() !== 200) {
    fwrite(STDERR, "FAIL positive control did not mint (HTTP {$positiveResp->getStatusCode()})\n");
    fwrite(STDERR, substr((string) $positiveResp->getBody(), 0, 2000) . "\n");
    exit(7);
}
echo "       OK\n\n";

// -------------------------------------------------------------------------
// NEGATIVE — admin creds + user_role=patient must NOT mint. The admin
// user exists in the `users` table but not as a portal patient; looking
// up admin/pass against the portal patient table has to come back empty.
// -------------------------------------------------------------------------
echo "[3/3] NEGATIVE — admin creds + user_role=patient must be rejected...\n";
try {
    $wrongRoleResp = $http->post('/oauth2/default/token', [
        'form_params' => [
            'grant_type' => 'password',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'scope' => $scope,
            'user_role' => 'patient',
            'username' => 'admin',
            'password' => 'pass',
        ],
        'allow_redirects' => false,
    ]);
} catch (GuzzleException $e) {
    fwrite(STDERR, "FAIL wrong-role request errored: " . $e->getMessage() . "\n");
    exit(8);
}
if ($wrongRoleResp->getStatusCode() === 200) {
    $wrongBody = json_decode((string) $wrongRoleResp->getBody(), true);
    if (is_array($wrongBody) && is_string($wrongBody['access_token'] ?? null)) {
        fwrite(STDERR, "FAIL admin credentials + user_role=patient minted a bearer — role-honoring regression\n");
        fwrite(STDERR, substr((string) $wrongRoleResp->getBody(), 0, 2000) . "\n");
        exit(9);
    }
}
$wrongBody = json_decode((string) $wrongRoleResp->getBody(), true);
if (!is_array($wrongBody) || !in_array($wrongBody['error'] ?? null, ['invalid_grant', 'invalid_request'], true)) {
    fwrite(STDERR, "FAIL wrong-role rejected with unexpected error\n");
    fwrite(STDERR, "HTTP " . $wrongRoleResp->getStatusCode() . "\n");
    fwrite(STDERR, substr((string) $wrongRoleResp->getBody(), 0, 2000) . "\n");
    exit(10);
}
echo "       OK  ({$wrongBody['error']} as expected)\n\n";

echo "PASS  oe-module-api-explorer wrong-user_role negative succeeded.\n";
exit(0);
