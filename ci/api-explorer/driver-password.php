<?php

/**
 * oe-module-api-explorer CI password-grant smoke driver.
 *
 * Companion to driver.php. Runs inside the openemr container (via
 * `openemr-cmd e "php .../driver-password.php"`). Exercises the OAuth2
 * password grant end to end from a real third-party client:
 *
 *   1. POST /oauth2/default/registration  — RFC 7591 DCR (same endpoint
 *      the explorer's client_register.php uses) requesting an openid +
 *      api:oemr + api:fhir + user/Patient.read confidential client.
 *   2. UPDATE oauth_clients SET grant_types='password', is_enabled=1 —
 *      DCR silently drops the password grant from grant_types because
 *      it is admin-only (see ClientGrantTypePolicy::ADMIN_ONLY). The
 *      explorer's four shipped clients never ask for password grant,
 *      and the explorer's own Scope Lab lane also flips the DB row
 *      post-registration (scope_lab_lib.php). The flip is run through
 *      the mariadb client against the dev-easy mysql service exactly
 *      like seed-globals.sql.
 *   3. POST /oauth2/default/token with grant_type=password +
 *      user_role=users + admin/pass. Asserts a 200 with an access_token.
 *   4. GET /apis/default/fhir/Patient with that bearer token. Asserts
 *      a FHIR Bundle with total >= 1 (demo data seeded by drid).
 *
 * The explorer upstream (sjpadgett/oe-module-api-explorer) does expose
 * a password-grant lane through its Scope Lab panel (scope_lab.php's
 * action=run with grant=password), but driving that lane from a CLI
 * driver requires seeding the explorer's per-session CSRF token via
 * oeApiExplorer.php, discovery via scope_lab.php?action=discover, and
 * posting a profile-shaped payload to scope_lab.php?action=run. That
 * exercises scope-matrix plumbing more than the password grant itself,
 * and the token-endpoint contract is what this smoke needs to cover.
 * The explorer's four shipped built-in clients (confidential auth-code,
 * public PKCE, JWT client-credentials, SMART EHR launch -- see
 * client_register.php) do not include password. So we drive the token
 * endpoint directly here, mirroring driver.php's Guzzle style and the
 * same DCR + DB-flip pattern used by tests/Tests/Api/PasswordGrant*.
 *
 * Password grant is OFF by default in OpenEMR; the dev-easy compose
 * sets OPENEMR_SETTING_oauth_password_grant=3 (both user_role=users
 * and user_role=patient enabled). seed-globals.sql also sets the
 * global for defence in depth after drid runs.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code <noreply@anthropic.com>
 * @copyright Copyright (c) 2026 OpenEMR
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

// Defensive scheme gate: the token POST below carries admin credentials
// in the request body. Refuse to proceed if someone overrode
// EXPLORER_BASE_URL to a plain http:// endpoint. verify=false is kept
// on the Guzzle client because this smoke targets the docker-network
// alias https://openemr with the dev-easy self-signed cert — same
// pattern as driver.php / driver-browser.php.
if (!str_starts_with(strtolower($baseUrl), 'https://')) {
    fwrite(STDERR, "FAIL EXPLORER_BASE_URL must be https:// (got: {$baseUrl})\n");
    exit(2);
}

echo "oe-module-api-explorer password-grant smoke\n";
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

// -------------------------------------------------------------------------
// 1. Dynamic Client Registration (RFC 7591).
//    DCR will drop the password grant from grant_types (admin-only); we
//    accept that and flip the DB row in step 2.
// -------------------------------------------------------------------------
echo "[1/4] Registering OAuth2 client (DCR)...\n";
$clientName = 'ApiExplorerSmoke-password-' . bin2hex(random_bytes(4));
try {
    $regResponse = $http->post('/oauth2/default/registration', [
        'headers' => [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ],
        'json' => [
            'application_type' => 'private',
            'client_name' => $clientName,
            'token_endpoint_auth_method' => 'client_secret_post',
            'redirect_uris' => ['https://openemr/devtools/oe-module-api-explorer/oauth_client.php'],
            'contacts' => ['smoke@test.example'],
            'scope' => 'openid api:oemr api:fhir user/Patient.read',
        ],
        // Credentials-bearing request: refuse to forward body on a redirect.
        // OpenEMR's /oauth2/default/registration does not redirect, but the
        // explicit false keeps the gate defensive.
        'allow_redirects' => false,
    ]);
} catch (GuzzleException $e) {
    fwrite(STDERR, "FAIL DCR request errored: " . $e->getMessage() . "\n");
    exit(2);
}
if ($regResponse->getStatusCode() !== 200) {
    fwrite(STDERR, "FAIL DCR returned HTTP {$regResponse->getStatusCode()}\n");
    fwrite(STDERR, substr((string) $regResponse->getBody(), 0, 2000) . "\n");
    exit(3);
}
$regBody = json_decode((string) $regResponse->getBody(), true);
if (!is_array($regBody) || !is_string($regBody['client_id'] ?? null) || !is_string($regBody['client_secret'] ?? null)) {
    fwrite(STDERR, "FAIL DCR response missing client_id or client_secret\n");
    fwrite(STDERR, substr((string) $regResponse->getBody(), 0, 2000) . "\n");
    exit(4);
}
$clientId = $regBody['client_id'];
$clientSecret = $regBody['client_secret'];
echo "       OK  (client_id = {$clientId})\n\n";

// -------------------------------------------------------------------------
// 2. Flip the client row to grant_types='password' and is_enabled=1.
//    DCR dropped password from the stored grant_types; a human admin
//    would do this through Admin > System > API Clients. We use the
//    mariadb client the same way run.sh seeds globals.
// -------------------------------------------------------------------------
echo "[2/4] Flipping stored grant_types to password + is_enabled=1...\n";
$sql = sprintf(
    "UPDATE oauth_clients SET grant_types = %s, is_enabled = 1 WHERE client_id = %s",
    quoteMysqlLiteral('password'),
    quoteMysqlLiteral($clientId)
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
    exit(5);
}
echo "       OK\n\n";

// -------------------------------------------------------------------------
// 3. POST /oauth2/default/token with grant_type=password + user_role=users.
// -------------------------------------------------------------------------
echo "[3/4] Exchanging admin credentials for a user_role=users token...\n";
try {
    $tokenResponse = $http->post('/oauth2/default/token', [
        'form_params' => [
            'grant_type' => 'password',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'scope' => 'openid api:oemr api:fhir user/Patient.read',
            'user_role' => 'users',
            'username' => 'admin',
            'password' => 'pass',
        ],
        // Credentials-bearing request: the form body carries admin/pass +
        // client_secret. Refuse to forward on a redirect (Guzzle strips
        // Authorization headers on cross-origin redirects but does not
        // strip form_params bodies).
        'allow_redirects' => false,
    ]);
} catch (GuzzleException $e) {
    fwrite(STDERR, "FAIL token request errored: " . $e->getMessage() . "\n");
    exit(6);
}
if ($tokenResponse->getStatusCode() !== 200) {
    fwrite(STDERR, "FAIL token endpoint returned HTTP {$tokenResponse->getStatusCode()}\n");
    fwrite(STDERR, substr((string) $tokenResponse->getBody(), 0, 2000) . "\n");
    exit(7);
}
$tokenBody = json_decode((string) $tokenResponse->getBody(), true);
if (!is_array($tokenBody) || !is_string($tokenBody['access_token'] ?? null) || $tokenBody['access_token'] === '') {
    fwrite(STDERR, "FAIL token response missing access_token\n");
    fwrite(STDERR, substr((string) $tokenResponse->getBody(), 0, 2000) . "\n");
    exit(8);
}
$accessToken = $tokenBody['access_token'];
echo "       OK  (token_type = " . (string) ($tokenBody['token_type'] ?? '?') . ")\n\n";

// -------------------------------------------------------------------------
// 4. FHIR Patient read with the bearer token. Asserts a Bundle with
//    total >= 1 (demo data loaded by drid before this driver runs).
// -------------------------------------------------------------------------
echo "[4/4] Reading /apis/default/fhir/Patient with the bearer token...\n";
try {
    $fhirResponse = $http->get('/apis/default/fhir/Patient', [
        'headers' => [
            'Authorization' => 'Bearer ' . $accessToken,
            'Accept' => 'application/fhir+json',
        ],
    ]);
} catch (GuzzleException $e) {
    fwrite(STDERR, "FAIL FHIR request errored: " . $e->getMessage() . "\n");
    exit(9);
}
if ($fhirResponse->getStatusCode() !== 200) {
    fwrite(STDERR, "FAIL FHIR Patient read returned HTTP {$fhirResponse->getStatusCode()}\n");
    fwrite(STDERR, substr((string) $fhirResponse->getBody(), 0, 2000) . "\n");
    exit(10);
}
$fhirRaw = (string) $fhirResponse->getBody();
if (!preg_match('/"resourceType"\s*:\s*"Bundle"/', $fhirRaw)) {
    fwrite(STDERR, "FAIL FHIR response is not a Bundle\n");
    fwrite(STDERR, substr($fhirRaw, 0, 2000) . "\n");
    exit(11);
}
if (!preg_match('/"total"\s*:\s*([0-9]+)/', $fhirRaw, $m) || (int) $m[1] < 1) {
    fwrite(STDERR, "FAIL FHIR Bundle total < 1 (demo data not seeded?)\n");
    fwrite(STDERR, substr($fhirRaw, 0, 2000) . "\n");
    exit(12);
}
echo "       OK  (Bundle total = {$m[1]})\n\n";

echo "PASS  oe-module-api-explorer password-grant round-trip succeeded.\n";
exit(0);

/**
 * Quote a value for a MySQL statement sent as a single -e argument.
 * No user input flows into this driver -- values are either DCR
 * response fields or hard-coded strings -- but defence in depth: the
 * client_id is server-generated and known to be hex, and the literal
 * 'password' is a constant. Still, keep the quoting disciplined.
 */
function quoteMysqlLiteral(string $value): string
{
    return "'" . str_replace(['\\', "'"], ['\\\\', "\\'"], $value) . "'";
}
