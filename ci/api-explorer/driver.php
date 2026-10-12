<?php

/**
 * oe-module-api-explorer CI smoke driver.
 *
 * Runs inside the openemr container (via `openemr-cmd e "php .../driver.php"`).
 * Exercises the explorer's JWT client-credentials path end to end:
 *
 *   1. GET oeApiExplorer.php?api_site=<site>     — initialize PHP session + site selection.
 *   2. GET client_register.php?regen=1&api_site=<site> — DCR all four clients, enable in DB.
 *   3. GET oeApiExplorer.php?...&fetch_action=true — JWT assertion → token → FHIR Patient read.
 *
 * The explorer renders responses in the page HTML, so steps 2 and 3 assert on text markers:
 * step 2 looks for "All clients registered successfully"; step 3 looks for a FHIR Bundle with
 * at least one entry. Exits 0 on success, nonzero on any step failure (prints the response
 * body prefix for the failing step).
 *
 * No Panther / Selenium required — the explorer sets $ignoreAuth=true so its pages work
 * without an OpenEMR login, and the JWT client-credentials grant is server-to-server inside
 * the explorer's PHP, so no browser auth or consent click-through is needed.
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
$apiSite = getenv('EXPLORER_API_SITE') ?: 'docker';
$explorerPath = '/devtools/oe-module-api-explorer';

$http = new Client([
    'base_uri' => $baseUrl,
    'verify' => false,
    'http_errors' => false,
    'cookies' => new CookieJar(),
    'timeout' => 60,
    'allow_redirects' => true,
]);

echo "oe-module-api-explorer CI smoke\n";
echo "  base    = {$baseUrl}\n";
echo "  site    = {$apiSite}\n";
echo "  path    = {$explorerPath}\n\n";

/**
 * @param array<string, string> $query
 */
function fetchExplorer(Client $http, string $explorerPath, string $file, array $query): string
{
    $qs = $query !== [] ? ('?' . http_build_query($query)) : '';
    try {
        $response = $http->get("{$explorerPath}/{$file}{$qs}");
    } catch (GuzzleException $e) {
        fwrite(STDERR, "FAIL fetching {$file}: " . $e->getMessage() . "\n");
        exit(2);
    }
    $status = $response->getStatusCode();
    $body = (string) $response->getBody();
    if ($status < 200 || $status >= 400) {
        fwrite(STDERR, "FAIL {$file} returned HTTP {$status}\n");
        fwrite(STDERR, substr($body, 0, 2000) . "\n");
        exit(3);
    }
    return $body;
}

// -------------------------------------------------------------------------
// 1. Initialize session + site selection.
// -------------------------------------------------------------------------
echo "[1/3] Initializing explorer session...\n";
$initBody = fetchExplorer($http, $explorerPath, 'oeApiExplorer.php', ['api_site' => $apiSite]);
if (!str_contains($initBody, 'api_site')) {
    fwrite(STDERR, "FAIL oeApiExplorer.php did not render the site selector\n");
    fwrite(STDERR, substr($initBody, 0, 2000) . "\n");
    exit(4);
}
echo "       OK\n\n";

// -------------------------------------------------------------------------
// 2. Register all four OAuth2 clients via the explorer's DCR flow.
// -------------------------------------------------------------------------
echo "[2/3] Registering OAuth2 clients (DCR)...\n";
$registerBody = fetchExplorer($http, $explorerPath, 'client_register.php', [
    'regen' => '1',
    'api_site' => $apiSite,
]);
if (!str_contains($registerBody, 'All clients registered successfully')) {
    fwrite(STDERR, "FAIL client_register.php did not report successful registration\n");
    fwrite(STDERR, substr($registerBody, 0, 4000) . "\n");
    exit(5);
}
echo "       OK\n\n";

// -------------------------------------------------------------------------
// 3. Drive the JWT client-credentials path at a FHIR Patient read.
//    The form is method=get, so GET with the same query params the UI would submit
//    is equivalent to clicking Fetch in the browser.
// -------------------------------------------------------------------------
echo "[3/3] Fetching FHIR Patient bundle via JWT client-credentials...\n";
$fetchBody = fetchExplorer($http, $explorerPath, 'oeApiExplorer.php', [
    'api_site' => $apiSite,
    'client' => 'JWT',
    'grant' => 'client_credentials',
    'api' => 'fhir',
    'resource' => 'Patient',
    'fetch_action' => 'true',
]);
if (!preg_match('/"resourceType"\s*:\s*"Bundle"/', $fetchBody)) {
    fwrite(STDERR, "FAIL FHIR response is not a Bundle\n");
    fwrite(STDERR, substr($fetchBody, 0, 4000) . "\n");
    exit(6);
}
if (!preg_match('/"total"\s*:\s*([0-9]+)/', $fetchBody, $m) || (int) $m[1] < 1) {
    fwrite(STDERR, "FAIL FHIR Bundle total < 1 (demo data not seeded?)\n");
    fwrite(STDERR, substr($fetchBody, 0, 4000) . "\n");
    exit(7);
}
echo "       OK  (Bundle total = {$m[1]})\n\n";

echo "PASS  oe-module-api-explorer round-trip succeeded.\n";
exit(0);
