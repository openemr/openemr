<?php

/**
 * oe-module-api-explorer CI invalid-client_secret negative driver.
 *
 * Fundamental OAuth2 property: a confidential client's /token request
 * with the wrong client_secret must be rejected with `invalid_client`.
 * No other driver exercises this today — driver-password.php, driver-
 * fhir-writes.php, and driver-portal.php all submit the CORRECT secret.
 *
 * A regression that broke client-secret validation at the token
 * endpoint (e.g. a refactor that compared the submitted secret
 * length-only, or always-returned true from ClientRepository's
 * validateConfidentialClientSecret) would let anyone who DCR'd a
 * client (or stole its client_id) mint bearers against it.
 *
 *   1. DCR a confidential client (+ enable password grant via DB flip).
 *   2. Mint a bearer with the CORRECT secret — positive control, proves
 *      the client is set up correctly before the negative test.
 *   3. POST /oauth2/default/token with the SAME client_id but a
 *      random wrong secret. Assert HTTP 400 or 401 + `error=invalid_client`.
 *   4. POST /token again with an EMPTY secret. Assert same rejection
 *      — a regression that treated empty-string as "skip the check"
 *      would be caught here that length-matching wrong-secret wouldn't.
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

echo "oe-module-api-explorer invalid-client_secret negative smoke\n";
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
echo "[1/4] DCR + enable password-grant confidential client...\n";
try {
    $regResponse = $http->post('/oauth2/default/registration', [
        'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
        'json' => [
            'application_type' => 'private',
            'client_name' => 'ApiExplorerSmoke-invalid-secret-' . bin2hex(random_bytes(4)),
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
$realSecret = $regBody['client_secret'];
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
// Positive control — mint with the CORRECT secret. Proves the client is
// set up end-to-end before we exercise the negative path.
// -------------------------------------------------------------------------
echo "[2/4] Positive control — mint bearer with CORRECT secret...\n";
try {
    $positiveResp = $http->post('/oauth2/default/token', [
        'form_params' => [
            'grant_type' => 'password',
            'client_id' => $clientId,
            'client_secret' => $realSecret,
            'scope' => $scope,
            'user_role' => 'users',
            'username' => 'admin',
            'password' => 'pass',
        ],
        'allow_redirects' => false,
    ]);
} catch (GuzzleException $e) {
    fwrite(STDERR, "FAIL positive-control token errored: " . $e->getMessage() . "\n");
    exit(6);
}
if ($positiveResp->getStatusCode() !== 200) {
    fwrite(STDERR, "FAIL positive control did not mint (HTTP {$positiveResp->getStatusCode()}) — client misconfigured\n");
    fwrite(STDERR, substr((string) $positiveResp->getBody(), 0, 2000) . "\n");
    exit(7);
}
echo "       OK\n\n";

/**
 * Assert /token rejects with invalid_client given a wrong/empty secret.
 *
 * Shared between the wrong-secret and empty-secret cases below.
 */
$assertRejected = static function (Client $http, string $clientId, string $wrongSecret, string $scope, string $label, int $exitBase): void {
    try {
        $resp = $http->post('/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $clientId,
                'client_secret' => $wrongSecret,
                'scope' => $scope,
                'user_role' => 'users',
                'username' => 'admin',
                'password' => 'pass',
            ],
            'allow_redirects' => false,
        ]);
    } catch (GuzzleException $e) {
        fwrite(STDERR, "FAIL {$label} request errored: " . $e->getMessage() . "\n");
        exit($exitBase);
    }
    $status = $resp->getStatusCode();
    // Tightened per rabbit — require status in 400-401 AND the OAuth
    // error code. A pure "not 200" check would let a 5xx server fault
    // or a stray 201 pass as if it were a proper OAuth reject.
    if ($status !== 400 && $status !== 401) {
        fwrite(STDERR, "FAIL {$label} returned HTTP {$status} (expected 400 or 401)\n");
        fwrite(STDERR, substr((string) $resp->getBody(), 0, 2000) . "\n");
        exit($exitBase + 1);
    }
    $body = json_decode((string) $resp->getBody(), true);
    // RFC 6749 §5.2: invalid_client is the standard error for failed
    // client authentication. Some servers return invalid_request for
    // the empty-secret case (treating absence as malformed rather
    // than wrong); accept either here.
    if (!is_array($body) || !in_array($body['error'] ?? null, ['invalid_client', 'invalid_request'], true)) {
        fwrite(STDERR, "FAIL {$label} rejected with unexpected error (expected invalid_client)\n");
        fwrite(STDERR, "HTTP {$status}\n");
        fwrite(STDERR, substr((string) $resp->getBody(), 0, 2000) . "\n");
        exit($exitBase + 2);
    }
    echo "       OK  (HTTP {$status}, {$body['error']} as expected)\n\n";
};

echo "[3/4] NEGATIVE — wrong client_secret must be rejected...\n";
// Derive from the real secret with one char flipped so the lengths match.
// A random bin2hex(32) would be 64 chars; AuthorizationController::
// generateClientSecret emits base64url-encoded 64 random bytes (~86 chars).
// A length-only comparison regression would reject the random on length
// alone, defeating the point of the test. Flipping one char keeps same
// length and guarantees inequality. Rabbit-flagged on the first pass.
$wrongSecret = $realSecret;
$wrongSecret[0] = $wrongSecret[0] === 'A' ? 'B' : 'A';
$assertRejected($http, $clientId, $wrongSecret, $scope, 'wrong-secret', 10);

echo "[4/4] NEGATIVE — empty client_secret must be rejected...\n";
$assertRejected($http, $clientId, '', $scope, 'empty-secret', 20);

echo "PASS  oe-module-api-explorer invalid-client_secret negative succeeded.\n";
exit(0);
