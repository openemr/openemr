<?php

/**
 * oe-module-api-explorer CI portal-api smoke driver.
 *
 * Companion to driver.php / driver-password.php / driver-fhir-writes.php.
 * Runs inside the openemr container (via
 * `openemr-cmd e "php .../driver-portal.php"`). Closes the §8.2 Portal
 * API external-realism gap: the other three drivers all target
 * `/apis/default/fhir/*` (user-role tokens, FHIR surface). Nothing in CI
 * exercises `/apis/default/portal/*` (patient-role tokens, portal surface)
 * from a real third-party client; the in-tree PortalApi* tests cover
 * that leg internally. This driver closes the external-client realism
 * axis.
 *
 * Four steps against the running stack:
 *
 *   1. Install a portal-enabled patient fixture via
 *      PortalPatientFixtureManager::installPortalPatient — the demo-data
 *      patient seeded by drid does NOT have portal credentials, so a
 *      fresh fixture is seeded with a known plaintext password and
 *      cleaned up at the end. OE bootstrap diverges from the three
 *      sibling drivers (which are pure HTTP) because
 *      PortalPatientFixtureManager uses AuthHash::passwordHash and
 *      QueryUtils — call them through the normal interface/globals.php
 *      path with $ignoreAuth, same way tests/bootstrap.php does.
 *   2. POST /oauth2/default/registration — RFC 7591 DCR requesting
 *      openid + api:port + patient/patient.read +
 *      patient/encounter.read + offline_access. DCR silently drops
 *      the password grant from grant_types (admin-only via
 *      ClientGrantTypePolicy::ADMIN_ONLY); we accept that and flip
 *      the DB row in step 3.
 *   3. UPDATE oauth_clients SET grant_types='password', is_enabled=1
 *      via the mariadb client — same DB-flip pattern driver-password.php
 *      and the in-tree PortalApiSmokeTest use.
 *   4. POST /oauth2/default/token with grant_type=password +
 *      user_role=patient + fixture credentials. Asserts a 200 with
 *      an access_token and that the response `scope` contains
 *      patient/patient.read (fail-fast on scope-filter drift at the
 *      portal-grant leg).
 *   5. GET /apis/default/portal/patient with the bearer. Asserts a
 *      200 + non-empty JSON array whose shape identifies the fixture
 *      patient (email matches). The portal endpoint returns a plain
 *      JSON array, not a FHIR Bundle.
 *
 * Cleanup at the end removes the fixture rows (patient_data +
 * patient_access_onsite + uuid_registry) and the oauth_clients row
 * for the smoke-registered client.
 *
 * Hardening matches driver-password.php and driver-fhir-writes.php:
 * HTTPS scheme gate at startup, allow_redirects=false on
 * credentials-bearing requests, verify=false kept because this smoke
 * targets the docker-network alias https://openemr with the dev-easy
 * self-signed cert.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code <noreply@anthropic.com>
 * @copyright Copyright (c) 2026 OpenEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

if (php_sapi_name() !== 'cli') {
    echo 'Only php cli can execute a command';
    exit(1);
}

// -------------------------------------------------------------------------
// OE bootstrap for the fixture-install step. Same shape tests/bootstrap.php
// uses — $ignoreAuth short-circuits the auth gate in interface/globals.php
// so we can call QueryUtils / AuthHash from CLI. Three sibling drivers do
// NOT do this (pure HTTP); portal is different because the patient
// fixture needs AuthHash::passwordHash + direct schema inserts that are
// awkward to replicate as raw SQL.
// -------------------------------------------------------------------------
$_GET['site'] = 'default';
$ignoreAuth = true;
require_once __DIR__ . '/../../interface/globals.php';

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\GuzzleException;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Tests\Fixtures\PortalPatientFixtureManager;

$baseUrl = getenv('EXPLORER_BASE_URL') ?: 'https://openemr';
$mysqlHost = getenv('EXPLORER_MYSQL_HOST') ?: 'mysql';
$mysqlUser = getenv('EXPLORER_MYSQL_USER') ?: 'openemr';
$mysqlPass = getenv('EXPLORER_MYSQL_PASS') ?: 'openemr';
$mysqlDb = getenv('EXPLORER_MYSQL_DB') ?: 'openemr';

if (!str_starts_with(strtolower($baseUrl), 'https://')) {
    fwrite(STDERR, "FAIL EXPLORER_BASE_URL must be https:// (got: {$baseUrl})\n");
    exit(2);
}

echo "oe-module-api-explorer portal-api smoke\n";
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

$fixtures = new PortalPatientFixtureManager();
$fixture = null;
$registeredClientId = null;

// -------------------------------------------------------------------------
// 1. Install the portal-enabled patient fixture. Random username/password
//    so re-runs on the same stack don't collide with a prior fixture row.
// -------------------------------------------------------------------------
try {
    echo "[1/5] Installing portal patient fixture...\n";
    $portalUsername = 'portal-smoke-' . bin2hex(random_bytes(4));
    $portalPassword = 'Pass-' . bin2hex(random_bytes(8));
    $fixture = $fixtures->installPortalPatient(
        $portalUsername,
        $portalPassword,
        'portal-smoke-' . bin2hex(random_bytes(3)) . '@test.example',
    );
    echo "       OK  (pid = {$fixture['pid']}, portal_username = {$fixture['portal_login_username']})\n\n";

    // -------------------------------------------------------------------------
    // 2. Dynamic Client Registration. Scopes mirror PortalApiSmokeTest's
    //    PORTAL_SCOPE constant so the token-endpoint shape matches the
    //    api-tier regression tests.
    // -------------------------------------------------------------------------
    echo "[2/5] Registering OAuth2 client (DCR)...\n";
    $clientName = 'ApiExplorerSmoke-portal-' . bin2hex(random_bytes(4));
    $scope = 'openid api:port patient/patient.read patient/encounter.read offline_access';
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
                'scope' => $scope,
            ],
            // Credentials-bearing request: refuse to forward body on a redirect.
            'allow_redirects' => false,
        ]);
    } catch (GuzzleException $e) {
        fwrite(STDERR, "FAIL DCR request errored: " . $e->getMessage() . "\n");
        throw new \RuntimeException('', 3);
    }
    if ($regResponse->getStatusCode() !== 200) {
        fwrite(STDERR, "FAIL DCR returned HTTP {$regResponse->getStatusCode()}\n");
        fwrite(STDERR, substr((string) $regResponse->getBody(), 0, 2000) . "\n");
        throw new \RuntimeException('', 4);
    }
    $regBody = json_decode((string) $regResponse->getBody(), true);
    if (!is_array($regBody) || !is_string($regBody['client_id'] ?? null) || !is_string($regBody['client_secret'] ?? null)) {
        fwrite(STDERR, "FAIL DCR response missing client_id or client_secret\n");
        fwrite(STDERR, substr((string) $regResponse->getBody(), 0, 2000) . "\n");
        throw new \RuntimeException('', 5);
    }
    $registeredClientId = $regBody['client_id'];
    $clientSecret = $regBody['client_secret'];
    echo "       OK  (client_id = {$registeredClientId})\n\n";

    // -------------------------------------------------------------------------
    // 3. Flip grant_types + is_enabled via mariadb client. Password is
    //    admin-only at DCR time; see ClientGrantTypePolicy::ADMIN_ONLY.
    // -------------------------------------------------------------------------
    echo "[3/5] Flipping stored grant_types to password + is_enabled=1...\n";
    $sql = sprintf(
        "UPDATE oauth_clients SET grant_types = %s, is_enabled = 1 WHERE client_id = %s",
        quoteMysqlLiteral('password'),
        quoteMysqlLiteral($registeredClientId)
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
        throw new \RuntimeException('', 6);
    }
    echo "       OK\n\n";

    // -------------------------------------------------------------------------
    // 4. POST /oauth2/default/token with grant_type=password +
    //    user_role=patient + fixture credentials.
    // -------------------------------------------------------------------------
    echo "[4/5] Exchanging portal patient credentials for a user_role=patient token...\n";
    try {
        $tokenResponse = $http->post('/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $registeredClientId,
                'client_secret' => $clientSecret,
                'scope' => $scope,
                'user_role' => 'patient',
                'username' => $fixture['portal_login_username'],
                'password' => $portalPassword,
                'email' => $fixture['email'],
            ],
            // Credentials-bearing: form body carries portal creds + client_secret.
            'allow_redirects' => false,
        ]);
    } catch (GuzzleException $e) {
        fwrite(STDERR, "FAIL token request errored: " . $e->getMessage() . "\n");
        throw new \RuntimeException('', 7);
    }
    if ($tokenResponse->getStatusCode() !== 200) {
        fwrite(STDERR, "FAIL token endpoint returned HTTP {$tokenResponse->getStatusCode()}\n");
        fwrite(STDERR, substr((string) $tokenResponse->getBody(), 0, 2000) . "\n");
        throw new \RuntimeException('', 8);
    }
    $tokenBody = json_decode((string) $tokenResponse->getBody(), true);
    if (!is_array($tokenBody) || !is_string($tokenBody['access_token'] ?? null) || $tokenBody['access_token'] === '') {
        fwrite(STDERR, "FAIL token response missing access_token\n");
        fwrite(STDERR, substr((string) $tokenResponse->getBody(), 0, 2000) . "\n");
        throw new \RuntimeException('', 9);
    }
    $accessToken = $tokenBody['access_token'];
    $grantedScope = is_string($tokenBody['scope'] ?? null) ? $tokenBody['scope'] : '';
    if (!str_contains($grantedScope, 'patient/patient.read')) {
        fwrite(STDERR, "FAIL granted scope missing patient/patient.read (scope-filter drift?)\n");
        fwrite(STDERR, "       granted = {$grantedScope}\n");
        throw new \RuntimeException('', 10);
    }
    echo "       OK  (token_type = " . (string) ($tokenBody['token_type'] ?? '?') . ", scope = {$grantedScope})\n\n";

    // -------------------------------------------------------------------------
    // 5. GET /apis/default/portal/patient. Portal returns a plain JSON
    //    array (not a FHIR Bundle). Assert non-empty + the fixture's
    //    email appears somewhere in the body — proves the authenticated-
    //    patient resolution works and routes the right row back.
    // -------------------------------------------------------------------------
    echo "[5/5] Reading /apis/default/portal/patient with the bearer token...\n";
    try {
        $portalResponse = $http->get('/apis/default/portal/patient', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Accept' => 'application/json',
            ],
            // Bearer-authenticated: reject redirects to preserve the
            // HTTPS-only destination rule gated at startup.
            'allow_redirects' => false,
        ]);
    } catch (GuzzleException $e) {
        fwrite(STDERR, "FAIL portal request errored: " . $e->getMessage() . "\n");
        throw new \RuntimeException('', 11);
    }
    if ($portalResponse->getStatusCode() !== 200) {
        fwrite(STDERR, "FAIL portal /patient returned HTTP {$portalResponse->getStatusCode()}\n");
        fwrite(STDERR, substr((string) $portalResponse->getBody(), 0, 2000) . "\n");
        throw new \RuntimeException('', 12);
    }
    $portalRaw = (string) $portalResponse->getBody();
    $portalBody = json_decode($portalRaw, true);
    if (!is_array($portalBody) || $portalBody === []) {
        fwrite(STDERR, "FAIL portal /patient must return a non-empty JSON array\n");
        fwrite(STDERR, substr($portalRaw, 0, 2000) . "\n");
        throw new \RuntimeException('', 13);
    }
    if (!str_contains($portalRaw, $fixture['email'])) {
        fwrite(STDERR, "FAIL portal /patient response does not identify the fixture patient (email missing)\n");
        fwrite(STDERR, substr($portalRaw, 0, 2000) . "\n");
        throw new \RuntimeException('', 14);
    }
    echo "       OK  (response identifies fixture patient by email)\n\n";

    echo "PASS  oe-module-api-explorer portal-api round-trip succeeded.\n";
    $exitCode = 0;
} catch (\Throwable $e) {
    // Expected-failure paths throw RuntimeException with a nonzero
    // exit code; the detailed FAIL message was written to STDERR at
    // the throw site. Unexpected throws (code 0) get the trace dump
    // and are mapped to exit 20. Both paths run the finally block —
    // the previous exit() calls skipped it, leaving the fixture row
    // and oauth_clients row behind on failure (coderabbit 2026-10-18).
    if ($e->getCode() === 0) {
        fwrite(STDERR, "FAIL unexpected error: " . $e->getMessage() . "\n");
        fwrite(STDERR, $e->getTraceAsString() . "\n");
    }
    $exitCode = $e->getCode() ?: 20;
} finally {
    // Cleanup: fixture rows + the smoke-registered oauth client. Safe
    // to call even on partial-failure paths (removePortalPatientFixtures
    // works by prefix; the client DELETE is a single-row no-op if the
    // id was never set).
    try {
        $fixtures->removePortalPatientFixtures();
    } catch (\Throwable $e) {
        fwrite(STDERR, "WARN cleanup: fixture removal failed: " . $e->getMessage() . "\n");
    }
    if ($registeredClientId !== null) {
        try {
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM oauth_clients WHERE client_id = ?',
                [$registeredClientId]
            );
        } catch (\Throwable $e) {
            fwrite(STDERR, "WARN cleanup: oauth_clients delete failed: " . $e->getMessage() . "\n");
        }
    }
}

exit($exitCode);

/**
 * Quote a value for a MySQL statement sent as a single -e argument.
 */
function quoteMysqlLiteral(string $value): string
{
    return "'" . str_replace(['\\', "'"], ['\\\\', "\\'"], $value) . "'";
}
