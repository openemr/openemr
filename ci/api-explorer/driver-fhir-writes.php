<?php

/**
 * oe-module-api-explorer CI FHIR write-path smoke driver.
 *
 * Companion to driver.php / driver-password.php. Runs inside the openemr
 * container (via `openemr-cmd e "php .../driver-fhir-writes.php"`). Closes
 * a §8.2 external-realism gap: the other three drivers exercise OAuth2
 * grants against a FHIR *read* (/fhir/Patient GET) only. Inferno's ONC G10
 * US Core 3.1.1 suite is also read-only. Nothing in CI drives a real
 * third-party client at a FHIR POST/PUT, so a scope-filtering or
 * write-path bearer-validation regression that only shows up on writes
 * could land silently.
 *
 * The driver reuses driver-password.php's DCR + DB-flip + password-grant
 * pattern (admin-only grant; token endpoint directly rather than through
 * the explorer's UI), then exercises three write-path verbs against
 * /apis/default/fhir/CareTeam:
 *
 *   1. POST /oauth2/default/registration  — RFC 7591 DCR requesting
 *      openid + api:oemr + api:fhir + user/Patient.read +
 *      user/CareTeam.write. DCR drops the password grant from
 *      grant_types (admin-only; ClientGrantTypePolicy::ADMIN_ONLY).
 *   2. UPDATE oauth_clients SET grant_types='password', is_enabled=1 —
 *      same DB flip driver-password.php and the in-tree
 *      PasswordGrantPatientRole api test perform.
 *   3. POST /oauth2/default/token with grant_type=password +
 *      user_role=users + admin/pass. Asserts a 200 with access_token.
 *   4. GET /apis/default/fhir/Patient?_count=1 to pick a subject uuid
 *      for the CareTeam. Asserts a Bundle with total >= 1 (demo data
 *      seeded by drid before this driver runs).
 *   5. POST /apis/default/fhir/CareTeam with a minimal CareTeam fixture
 *      (subject = Patient/<uuid>, participant = Practitioner/admin uuid).
 *      Asserts HTTP 201 and a response body carrying the new uuid.
 *   6. PUT /apis/default/fhir/CareTeam/<uuid> with name mutated. Asserts
 *      HTTP 200 and a FHIR CareTeam body echoing the uuid.
 *   7. GET /apis/default/fhir/CareTeam/<uuid> read-back for persistence.
 *      Asserts the mutated name landed.
 *
 * No DELETE: FHIR CareTeam does not expose a DELETE route (see
 * apis/routes/_rest_routes_fhir_r4_us_core_3_1_0.inc.php; only GET /
 * POST / PUT). POST + PUT + GET round-trip is sufficient to catch the
 * scope-filtering / write-path bearer drift this smoke targets.
 *
 * CareTeam over Patient as the resource under test: CareTeam has existing
 * api-tier write test coverage (tests/Tests/Api/CareTeamFhirWriteApiTest.php
 * documents the shape), is light to serialize, and exercises a subject-
 * reference + participant-reference binding on the write path that a bare
 * Patient POST would not.
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

// Defensive scheme gate: the token POST and FHIR write POST/PUT below
// carry admin credentials, a bearer, and resource bodies. Refuse to
// proceed if someone overrode EXPLORER_BASE_URL to a plain http://
// endpoint. verify=false is kept on the Guzzle client because this
// smoke targets the docker-network alias https://openemr with the
// dev-easy self-signed cert — same pattern as driver.php and
// driver-password.php.
if (!str_starts_with(strtolower($baseUrl), 'https://')) {
    fwrite(STDERR, "FAIL EXPLORER_BASE_URL must be https:// (got: {$baseUrl})\n");
    exit(2);
}

echo "oe-module-api-explorer FHIR write-path smoke\n";
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
// 1. Dynamic Client Registration (RFC 7591) with user/CareTeam.write scope.
// -------------------------------------------------------------------------
echo "[1/7] Registering OAuth2 client (DCR) with user/CareTeam.write scope...\n";
$clientName = 'ApiExplorerSmoke-fhir-writes-' . bin2hex(random_bytes(4));
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
            'scope' => 'openid api:oemr api:fhir user/Patient.read user/CareTeam.read user/CareTeam.write',
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
// -------------------------------------------------------------------------
echo "[2/7] Flipping stored grant_types to password + is_enabled=1...\n";
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
echo "[3/7] Exchanging admin credentials for a user_role=users token...\n";
$tokenScope = 'openid api:oemr api:fhir user/Patient.read user/CareTeam.read user/CareTeam.write';
try {
    $tokenResponse = $http->post('/oauth2/default/token', [
        'form_params' => [
            'grant_type' => 'password',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'scope' => $tokenScope,
            'user_role' => 'users',
            'username' => 'admin',
            'password' => 'pass',
        ],
        // Credentials-bearing request: refuse to forward on a redirect.
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
$grantedScope = is_string($tokenBody['scope'] ?? null) ? $tokenBody['scope'] : '';
echo "       OK  (token_type = " . (string) ($tokenBody['token_type'] ?? '?') . ")\n";
echo "            scope      = {$grantedScope}\n\n";
if (!str_contains($grantedScope, 'user/CareTeam.write')) {
    fwrite(STDERR, "FAIL granted scope missing user/CareTeam.write (write-path smoke requires it)\n");
    fwrite(STDERR, "    granted scope: {$grantedScope}\n");
    exit(9);
}

// -------------------------------------------------------------------------
// 4. Pick a subject uuid: GET /fhir/Patient?_count=1 with the bearer.
//    Also pull the admin user's uuid for the practitioner participant,
//    straight from the DB (users.uuid is formatted as hex binary(16)).
// -------------------------------------------------------------------------
echo "[4/7] Picking a subject Patient uuid via /fhir/Patient?_count=1...\n";
try {
    $patientResponse = $http->get('/apis/default/fhir/Patient', [
        'headers' => [
            'Authorization' => 'Bearer ' . $accessToken,
            'Accept' => 'application/fhir+json',
        ],
        'query' => ['_count' => '1'],
        // Bearer-authenticated: reject redirects to preserve the
        // HTTPS-only destination rule gated at startup. Guzzle strips
        // Authorization on cross-origin redirects but doesn't preserve
        // the scheme guard.
        'allow_redirects' => false,
    ]);
} catch (GuzzleException $e) {
    fwrite(STDERR, "FAIL Patient discovery errored: " . $e->getMessage() . "\n");
    exit(10);
}
if ($patientResponse->getStatusCode() !== 200) {
    fwrite(STDERR, "FAIL Patient discovery returned HTTP {$patientResponse->getStatusCode()}\n");
    fwrite(STDERR, substr((string) $patientResponse->getBody(), 0, 2000) . "\n");
    exit(11);
}
$patientBody = json_decode((string) $patientResponse->getBody(), true);
if (
    !is_array($patientBody)
    || ($patientBody['resourceType'] ?? null) !== 'Bundle'
    || !is_array($patientBody['entry'] ?? null)
    || $patientBody['entry'] === []
) {
    fwrite(STDERR, "FAIL Patient Bundle missing or empty (demo data not seeded?)\n");
    fwrite(STDERR, substr((string) $patientResponse->getBody(), 0, 2000) . "\n");
    exit(12);
}
$firstEntry = $patientBody['entry'][0];
if (!is_array($firstEntry) || !is_array($firstEntry['resource'] ?? null)) {
    fwrite(STDERR, "FAIL Patient Bundle first entry is malformed\n");
    exit(13);
}
$resource = $firstEntry['resource'];
if (!is_string($resource['id'] ?? null) || $resource['id'] === '') {
    fwrite(STDERR, "FAIL Patient Bundle first entry has no id\n");
    exit(14);
}
$patientUuid = $resource['id'];

// Pull admin user's uuid from the DB as hex and format into canonical uuid.
$selectAdminSql = "SELECT LOWER(CONCAT_WS('-', SUBSTR(HEX(uuid),1,8), SUBSTR(HEX(uuid),9,4), SUBSTR(HEX(uuid),13,4), SUBSTR(HEX(uuid),17,4), SUBSTR(HEX(uuid),21,12))) FROM users WHERE username = 'admin'";
$cmd = sprintf(
    'mariadb -h %s -u %s -p%s %s -N -B -e %s 2>&1',
    escapeshellarg($mysqlHost),
    escapeshellarg($mysqlUser),
    escapeshellarg($mysqlPass),
    escapeshellarg($mysqlDb),
    escapeshellarg($selectAdminSql)
);
$adminRows = [];
exec($cmd, $adminRows, $rc);
if ($rc !== 0 || $adminRows === []) {
    fwrite(STDERR, "FAIL mariadb SELECT for admin uuid returned {$rc}\n");
    fwrite(STDERR, implode("\n", $adminRows) . "\n");
    exit(15);
}
$practitionerUuid = trim($adminRows[0]);
if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $practitionerUuid)) {
    fwrite(STDERR, "FAIL admin uuid shape unexpected: '{$practitionerUuid}'\n");
    exit(16);
}
echo "       OK  (patient_uuid      = {$patientUuid})\n";
echo "            practitioner_uuid = {$practitionerUuid})\n\n";

// -------------------------------------------------------------------------
// 5. POST /fhir/CareTeam. Minimal fixture mirroring the shape in
//    tests/Tests/Fixtures/FHIR/care-team.json. Asserts HTTP 201 and a
//    response body carrying the new uuid.
// -------------------------------------------------------------------------
echo "[5/7] POST /apis/default/fhir/CareTeam...\n";
$careTeamName = 'ApiExplorerSmoke CareTeam ' . bin2hex(random_bytes(4));
$careTeamBody = [
    'resourceType' => 'CareTeam',
    'status' => 'active',
    'name' => $careTeamName,
    'subject' => [
        'reference' => 'Patient/' . $patientUuid,
    ],
    'participant' => [
        [
            'role' => [
                [
                    'coding' => [
                        [
                            'system' => 'http://snomed.info/sct',
                            'code' => '453231000124104',
                            'display' => 'Primary care provider',
                        ],
                    ],
                ],
            ],
            'member' => [
                'reference' => 'Practitioner/' . $practitionerUuid,
            ],
        ],
    ],
];
try {
    $postResponse = $http->post('/apis/default/fhir/CareTeam', [
        'headers' => [
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/fhir+json',
            'Accept' => 'application/fhir+json',
        ],
        'json' => $careTeamBody,
        // Credentials-bearing write: bearer in Authorization header is
        // stripped by Guzzle on cross-origin redirect, but the FHIR
        // write body is not. Refuse to follow redirects at all here.
        'allow_redirects' => false,
    ]);
} catch (GuzzleException $e) {
    fwrite(STDERR, "FAIL CareTeam POST errored: " . $e->getMessage() . "\n");
    exit(17);
}
if ($postResponse->getStatusCode() !== 201) {
    fwrite(STDERR, "FAIL CareTeam POST returned HTTP {$postResponse->getStatusCode()}\n");
    fwrite(STDERR, substr((string) $postResponse->getBody(), 0, 2000) . "\n");
    exit(18);
}
$postBody = json_decode((string) $postResponse->getBody(), true);
if (!is_array($postBody) || !is_string($postBody['uuid'] ?? null) || $postBody['uuid'] === '') {
    fwrite(STDERR, "FAIL CareTeam POST response missing uuid\n");
    fwrite(STDERR, substr((string) $postResponse->getBody(), 0, 2000) . "\n");
    exit(19);
}
$careTeamUuid = $postBody['uuid'];
echo "       OK  (uuid = {$careTeamUuid})\n\n";

// -------------------------------------------------------------------------
// 6. PUT /fhir/CareTeam/<uuid> with a mutated name. Asserts HTTP 200 and a
//    FHIR CareTeam body echoing the uuid.
// -------------------------------------------------------------------------
echo "[6/7] PUT /apis/default/fhir/CareTeam/{$careTeamUuid}...\n";
$mutatedName = $careTeamName . ' (updated)';
$updatedBody = $careTeamBody;
$updatedBody['id'] = $careTeamUuid;
$updatedBody['name'] = $mutatedName;
try {
    $putResponse = $http->put('/apis/default/fhir/CareTeam/' . rawurlencode($careTeamUuid), [
        'headers' => [
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/fhir+json',
            'Accept' => 'application/fhir+json',
        ],
        'json' => $updatedBody,
        'allow_redirects' => false,
    ]);
} catch (GuzzleException $e) {
    fwrite(STDERR, "FAIL CareTeam PUT errored: " . $e->getMessage() . "\n");
    exit(20);
}
if ($putResponse->getStatusCode() !== 200) {
    fwrite(STDERR, "FAIL CareTeam PUT returned HTTP {$putResponse->getStatusCode()}\n");
    fwrite(STDERR, substr((string) $putResponse->getBody(), 0, 2000) . "\n");
    exit(21);
}
$putBodyDecoded = json_decode((string) $putResponse->getBody(), true);
if (
    !is_array($putBodyDecoded)
    || ($putBodyDecoded['resourceType'] ?? null) !== 'CareTeam'
    || ($putBodyDecoded['id'] ?? null) !== $careTeamUuid
) {
    fwrite(STDERR, "FAIL CareTeam PUT response shape unexpected (resourceType/id mismatch)\n");
    fwrite(STDERR, substr((string) $putResponse->getBody(), 0, 2000) . "\n");
    exit(22);
}
echo "       OK\n\n";

// -------------------------------------------------------------------------
// 7. GET /fhir/CareTeam/<uuid> read-back for persistence. Asserts the
//    mutated name landed; this is what makes it a real write-path check
//    rather than "POST returned a uuid".
// -------------------------------------------------------------------------
echo "[7/7] GET /apis/default/fhir/CareTeam/{$careTeamUuid} (persistence read-back)...\n";
try {
    $getResponse = $http->get('/apis/default/fhir/CareTeam/' . rawurlencode($careTeamUuid), [
        'headers' => [
            'Authorization' => 'Bearer ' . $accessToken,
            'Accept' => 'application/fhir+json',
        ],
        // Bearer-authenticated: reject redirects (see Patient GET above).
        'allow_redirects' => false,
    ]);
} catch (GuzzleException $e) {
    fwrite(STDERR, "FAIL CareTeam GET errored: " . $e->getMessage() . "\n");
    exit(23);
}
if ($getResponse->getStatusCode() !== 200) {
    fwrite(STDERR, "FAIL CareTeam GET returned HTTP {$getResponse->getStatusCode()}\n");
    fwrite(STDERR, substr((string) $getResponse->getBody(), 0, 2000) . "\n");
    exit(24);
}
$getBody = json_decode((string) $getResponse->getBody(), true);
if (
    !is_array($getBody)
    || ($getBody['resourceType'] ?? null) !== 'CareTeam'
    || ($getBody['id'] ?? null) !== $careTeamUuid
) {
    fwrite(STDERR, "FAIL CareTeam GET response shape unexpected\n");
    fwrite(STDERR, substr((string) $getResponse->getBody(), 0, 2000) . "\n");
    exit(25);
}
if (($getBody['name'] ?? null) !== $mutatedName) {
    fwrite(STDERR, "FAIL CareTeam GET did not persist the PUT mutation\n");
    fwrite(STDERR, "    expected name: {$mutatedName}\n");
    fwrite(STDERR, "    got name:      " . (string) ($getBody['name'] ?? '(null)') . "\n");
    exit(26);
}
echo "       OK  (name persisted after PUT)\n\n";

echo "PASS  oe-module-api-explorer FHIR write-path round-trip succeeded.\n";
exit(0);

/**
 * Quote a value for a MySQL statement sent as a single -e argument.
 * No user input flows into this driver — values are either DCR response
 * fields or hard-coded strings — but defence in depth: keep the quoting
 * disciplined.
 */
function quoteMysqlLiteral(string $value): string
{
    return "'" . str_replace(['\\', "'"], ['\\\\', "\\'"], $value) . "'";
}
