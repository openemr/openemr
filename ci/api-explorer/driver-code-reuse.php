<?php

/**
 * oe-module-api-explorer CI auth-code-reuse negative driver.
 *
 * Sibling to driver-refresh.php's single-use enforcement on
 * refresh tokens. This driver covers the same property for
 * authorization codes: an auth code can be exchanged ONCE at
 * /token and the server must reject every subsequent attempt
 * (RFC 6749 §4.1.2 / §10.5). A regression that forgot the
 * single-use enforcement would turn an intercepted auth code
 * (e.g. logged redirect-URI query) into a replayable credential.
 *
 *   1. DCR a public client (patient scope triggers public DCR
 *      branch — no client_secret; PKCE at /token is the only
 *      client proof).
 *   2. Enable client in DB.
 *   3. Generate PKCE verifier + S256 challenge.
 *   4. Drive /authorize → login → consent via Guzzle + regex
 *      form-parsing, extract authorization code from the
 *      consent redirect.
 *   5. POST /token with code + correct verifier. Expect 200 +
 *      access_token. This is the positive-path exchange.
 *   6. (NEGATIVE) POST /token again with the SAME code + same
 *      verifier. Expect rejection — either invalid_grant (RFC
 *      6749 §5.2) or invalid_request (OpenEMR's League 8.5.5
 *      quirk tracked in §8.1a of the audit doc). Critical
 *      property is the second exchange must NOT succeed.
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

echo "oe-module-api-explorer auth-code-reuse negative smoke\n";
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

$genVerifier = static fn (): string => rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
$deriveChallenge = static fn (string $v): string => rtrim(strtr(base64_encode(hash('sha256', $v, true)), '+/', '-_'), '=');

$extractCsrfAndAction = static function (string $html, string $label): array {
    if (preg_match('/<input[^>]*name=["\']csrf_token_form["\'][^>]*value=["\']([^"\']+)["\']/', $html, $csrfMatch) !== 1) {
        if (preg_match('/<input[^>]*value=["\']([^"\']+)["\'][^>]*name=["\']csrf_token_form["\']/', $html, $csrfMatch) !== 1) {
            fwrite(STDERR, "FAIL no csrf_token_form on {$label}\n");
            fwrite(STDERR, substr($html, 0, 2000) . "\n");
            exit(30);
        }
    }
    if (preg_match('/<form[^>]*action=["\']([^"\']+)["\']/', $html, $formMatch) !== 1) {
        fwrite(STDERR, "FAIL no <form action=...> on {$label}\n");
        exit(31);
    }
    return [$csrfMatch[1], $formMatch[1]];
};

// Keep scope at patient/Patient.read (no launch/patient) so OpenEMR's
// standalone SMART patient-select screen doesn't gate the flow — same
// pattern driver-pkce-negative.php uses.
$scope = 'openid fhirUser patient/Patient.read';

echo "[1/6] DCR public client...\n";
try {
    $regResponse = $http->post('/oauth2/default/registration', [
        'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
        'json' => [
            'application_type' => 'web',
            'client_name' => 'ApiExplorerSmoke-code-reuse-' . bin2hex(random_bytes(4)),
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

echo "[2/6] Generate PKCE verifier + S256 challenge...\n";
$verifier = $genVerifier();
$challenge = $deriveChallenge($verifier);
echo "       OK\n\n";

echo "[3/6] GET /authorize → login form...\n";
$state = bin2hex(random_bytes(8));
$authParams = [
    'client_id' => $clientId,
    'redirect_uri' => $redirectUri,
    'response_type' => 'code',
    'scope' => $scope,
    'state' => $state,
    'nonce' => bin2hex(random_bytes(8)),
    'code_challenge' => $challenge,
    'code_challenge_method' => 'S256',
];
try {
    $loginPageResp = $http->get('/oauth2/default/authorize?' . http_build_query($authParams));
} catch (GuzzleException $e) {
    fwrite(STDERR, "FAIL /authorize errored: " . $e->getMessage() . "\n");
    exit(6);
}
[$loginCsrf, $loginAction] = $extractCsrfAndAction((string) $loginPageResp->getBody(), 'login page');
echo "       OK\n\n";

echo "[4/6] POST login as admin → consent form...\n";
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
    fwrite(STDERR, "FAIL login errored: " . $e->getMessage() . "\n");
    exit(7);
}
$consentHtml = (string) $consentPageResp->getBody();
if (!str_contains($consentHtml, 'proceed')) {
    fwrite(STDERR, "FAIL login did not land on consent page\n");
    fwrite(STDERR, substr($consentHtml, 0, 2000) . "\n");
    exit(8);
}
[$consentCsrf, $consentAction] = $extractCsrfAndAction($consentHtml, 'consent page');
echo "       OK\n\n";

echo "[5/6] POST consent → extract authorization code...\n";
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
    fwrite(STDERR, "FAIL consent errored: " . $e->getMessage() . "\n");
    exit(9);
}
if ($consentPostResp->getStatusCode() !== 302) {
    fwrite(STDERR, "FAIL consent did not 302 (got HTTP {$consentPostResp->getStatusCode()})\n");
    fwrite(STDERR, substr((string) $consentPostResp->getBody(), 0, 2000) . "\n");
    exit(10);
}
$location = $consentPostResp->getHeaderLine('Location');
$query = [];
parse_str((string) parse_url($location, PHP_URL_QUERY), $query);
if (!is_string($query['code'] ?? null)) {
    fwrite(STDERR, "FAIL consent redirect missing ?code\n");
    exit(11);
}
$authorizationCode = $query['code'];
echo "       OK  (code captured)\n\n";

// -------------------------------------------------------------------------
// First exchange — positive control. Proves the code + verifier combination
// is valid; the second exchange must then be rejected on single-use grounds,
// not because the code was never valid in the first place.
// -------------------------------------------------------------------------
echo "[6/6] First /token exchange → bearer; second exchange with SAME code must be rejected...\n";
try {
    $firstResp = $http->post('/oauth2/default/token', [
        'form_params' => [
            'grant_type' => 'authorization_code',
            'code' => $authorizationCode,
            'redirect_uri' => $redirectUri,
            'client_id' => $clientId,
            'code_verifier' => $verifier,
        ],
        'allow_redirects' => false,
    ]);
} catch (GuzzleException $e) {
    fwrite(STDERR, "FAIL first /token errored: " . $e->getMessage() . "\n");
    exit(12);
}
if ($firstResp->getStatusCode() !== 200) {
    fwrite(STDERR, "FAIL first /token did not succeed (HTTP {$firstResp->getStatusCode()}) — single-use test needs a valid first exchange\n");
    fwrite(STDERR, substr((string) $firstResp->getBody(), 0, 2000) . "\n");
    exit(13);
}
$firstBody = json_decode((string) $firstResp->getBody(), true);
if (!is_array($firstBody) || !is_string($firstBody['access_token'] ?? null)) {
    fwrite(STDERR, "FAIL first /token response missing access_token\n");
    exit(14);
}

// Replay the SAME code. Single-use enforcement must reject.
try {
    $secondResp = $http->post('/oauth2/default/token', [
        'form_params' => [
            'grant_type' => 'authorization_code',
            'code' => $authorizationCode,
            'redirect_uri' => $redirectUri,
            'client_id' => $clientId,
            'code_verifier' => $verifier,
        ],
        'allow_redirects' => false,
    ]);
} catch (GuzzleException $e) {
    fwrite(STDERR, "FAIL second /token errored: " . $e->getMessage() . "\n");
    exit(15);
}
if ($secondResp->getStatusCode() === 200) {
    fwrite(STDERR, "FAIL authorization code was accepted a second time — single-use enforcement regression\n");
    fwrite(STDERR, substr((string) $secondResp->getBody(), 0, 2000) . "\n");
    exit(16);
}
$secondBody = json_decode((string) $secondResp->getBody(), true);
// Accept either invalid_grant (RFC 6749 §5.2) or invalid_request (League
// 8.5.5 maps isAuthCodeRevoked=true to invalid_request — tracked in
// §8.1a of the audit doc).
if (!is_array($secondBody) || !in_array($secondBody['error'] ?? null, ['invalid_grant', 'invalid_request'], true)) {
    fwrite(STDERR, "FAIL second /token rejected with unexpected error (expected invalid_grant or invalid_request)\n");
    fwrite(STDERR, "HTTP " . $secondResp->getStatusCode() . "\n");
    fwrite(STDERR, substr((string) $secondResp->getBody(), 0, 2000) . "\n");
    exit(17);
}
echo "       OK  ({$secondBody['error']} as expected)\n\n";

echo "PASS  oe-module-api-explorer auth-code reuse negative succeeded.\n";
exit(0);
