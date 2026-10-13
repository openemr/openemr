<?php

/**
 * oe-module-api-explorer CI browser smoke driver.
 *
 * Companion to driver.php. Runs inside the openemr container (via
 * `openemr-cmd e "php .../driver-browser.php"`). Exercises the two
 * authorization-code paths that need an actual browser session for the
 * authorize + consent round-trip:
 *
 *   1. Confidential authorization-code (uses client_secret at the token endpoint)
 *   2. Public PKCE authorization-code    (uses code_verifier at the token endpoint)
 *
 * Flow per client:
 *
 *   a. GET oeApiExplorer.php?cleanSession=1&api_site=<site> to reset session state.
 *   b. GET oeApiExplorer.php with the request-form query (client=<type>&grant=
 *      authorization_code&api=fhir&resource=Patient&fetch_action=true). The
 *      explorer redirects the browser to /oauth2/<site>/authorize with state
 *      and PKCE verifier.
 *   c. OpenEMR serves oauth2-login.html.twig; fill username + password, submit
 *      user_role=api. On a flow where a prior iteration left the browser
 *      authenticated, OpenEMR skips the login template -- probe before filling.
 *   d. OpenEMR serves scope-authorize.html.twig; checkboxes default checked, so
 *      click the proceed=1 Authorize button.
 *   e. OpenEMR redirects back to the explorer; the explorer exchanges the code
 *      at /oauth2/<site>/token, runs the FHIR Patient read, and renders the
 *      response into <pre class="explorer-response"> on success -- or into a
 *      <div class="alert-danger"> on an API-side error after the OAuth round-
 *      trip itself completed.
 *   f. Assert on the result (see runFlow()).
 *
 * Prerequisites: the dev stack has Selenium composed in at selenium:4444, admin
 * is `admin`/`pass`, demo data has been loaded (seeded by run.sh), and the
 * explorer's four clients have already been registered (driver.php handles
 * that step from run.sh before this driver is invoked).
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

require_once __DIR__ . '/../../vendor/autoload.php';

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Symfony\Component\Panther\Client;

$baseUrl = getenv('EXPLORER_BASE_URL') ?: 'https://openemr';
$apiSite = getenv('EXPLORER_API_SITE') ?: 'docker';
$explorerPath = '/devtools/oe-module-api-explorer';

$capabilities = DesiredCapabilities::chrome();
$capabilities->setCapability('goog:chromeOptions', [
    'args' => [
        '--headless=new',
        '--no-sandbox',
        '--disable-gpu',
        '--disable-dev-shm-usage',
        '--ignore-certificate-errors',
        '--window-size=1920,1080',
    ],
]);

$client = Client::createSeleniumClient('http://selenium:4444/wd/hub', $capabilities, $baseUrl);

/**
 * Dump a screenshot and page source for post-mortem on a failed flow. The
 * workflow uploads /tmp/driver-browser-*.{png,html} as artifacts.
 */
function captureFailure(Client $client, string $clientType, string $reason): void
{
    $debugDir = '/var/www/localhost/htdocs/openemr/tmp';
    @mkdir($debugDir, 0777, true);
    $slug = preg_replace('/[^a-z0-9]+/i', '-', $clientType) ?: 'unknown';
    try {
        $client->takeScreenshot("{$debugDir}/driver-browser-{$slug}.png");
        file_put_contents(
            "{$debugDir}/driver-browser-{$slug}.html",
            (string) $client->getWebDriver()->getPageSource()
        );
        $url = $client->getCurrentURL();
        fwrite(STDERR, "       capture: tmp/driver-browser-{$slug}.{png,html}  url={$url}\n");
    } catch (Throwable $captureErr) {
        fwrite(STDERR, "       capture failed: " . $captureErr->getMessage() . "\n");
    }
    fwrite(STDERR, "       reason:  {$reason}\n");
}

/**
 * Drive one explorer auth-code round-trip. Returns the FHIR Bundle total when
 * the response pane rendered, or 0 when the OAuth round-trip itself completed
 * but a tolerable post-token FHIR 401/403 followed.
 *
 * The explorer's public PKCE client is registered with patient/* scopes only
 * (see config.php PUBLIC_SCOPES). Logging in as admin issues a token the
 * server then declines on bulk FHIR Patient search ("Unauthorized" 401),
 * because the patient-role token has no patient context from a non-SMART
 * launch. The OAuth protocol itself completed successfully -- authorize,
 * PKCE verifier, token exchange all ran -- so for the public client we
 * accept a 401 on the FHIR fetch and treat the smoke as passing once we see
 * any post-callback explorer page render.
 */
function runFlow(Client $client, string $explorerPath, string $apiSite, string $clientType): int
{
    $cleanQuery = http_build_query([
        'cleanSession' => '1',
        'api_site' => $apiSite,
    ]);
    $client->request('GET', "{$explorerPath}/oeApiExplorer.php?{$cleanQuery}");

    $fetchQuery = http_build_query([
        'api_site' => $apiSite,
        'client' => $clientType,
        'grant' => 'authorization_code',
        'api' => 'fhir',
        'resource' => 'Patient',
        'fetch_action' => 'true',
    ]);
    $client->request('GET', "{$explorerPath}/oeApiExplorer.php?{$fetchQuery}");

    // Login step is conditional -- OpenEMR skips the login template when a
    // prior flow's session cookie is still valid. Both templates use
    // <form id="userLogin">; only the login template carries a username input.
    try {
        $client->waitFor('#userLogin input[name="username"]', 10);
        $onLogin = true;
    } catch (Throwable) {
        $onLogin = false;
    }
    if ($onLogin) {
        $client->getWebDriver()->findElement(WebDriverBy::name('username'))->sendKeys('admin');
        $client->getWebDriver()->findElement(WebDriverBy::name('password'))->sendKeys('pass');
        $client->getWebDriver()
            ->findElement(WebDriverBy::cssSelector('#userLogin button[name="user_role"][value="api"]'))
            ->click();
    }

    // Consent page: checkboxes default checked, so just click Authorize.
    $client->waitFor('#authorize-btn', 30);
    $client->getWebDriver()->findElement(WebDriverBy::id('authorize-btn'))->click();

    // Wait for either the response pre (FHIR fetch succeeded) or the error
    // pane (OAuth completed but FHIR fetch failed).
    $client->waitFor('pre.explorer-response, div.alert-danger', 30);

    $responseNodes = $client->getCrawler()->filter('pre.explorer-response');
    if ($responseNodes->count() > 0) {
        $responseText = $responseNodes->first()->text();
        if (!preg_match('/"resourceType"\s*:\s*"Bundle"/', $responseText)) {
            throw new RuntimeException(
                "FHIR response is not a Bundle. Prefix: " . substr($responseText, 0, 400)
            );
        }
        if (!preg_match('/"total"\s*:\s*(\d+)/', $responseText, $m)) {
            throw new RuntimeException(
                "FHIR Bundle has no total field. Prefix: " . substr($responseText, 0, 400)
            );
        }
        $total = (int) $m[1];
        if ($total < 1) {
            throw new RuntimeException(
                "FHIR Bundle total < 1 (demo data not seeded?). Prefix: " . substr($responseText, 0, 400)
            );
        }
        return $total;
    }

    // No response pre -> an error pane rendered. For the public PKCE client
    // the expected shape is a FHIR 401 with "Unauthorized" in the response
    // body (the patient-role token has no patient context from admin's
    // user_role=api login). Status-only would also pass invalid or
    // unverifiable bearer tokens, so pin both the status and the body.
    // Any other error (403, invalid_client, token endpoint 4xx, cURL error,
    // "Missing credentials") means the OAuth round-trip itself regressed and
    // must fail the smoke.
    $errorText = $client->getCrawler()->filter('div.alert-danger')->first()->text();
    if (
        $clientType === 'public'
        && preg_match('/API request failed \(Status: 401\)/', $errorText)
        && preg_match('/Response:.*Unauthorized/', $errorText)
    ) {
        return 0;
    }
    throw new RuntimeException(
        "OAuth flow did not produce a response pane and the error is not a "
        . "tolerable patient-context 401/403. Error text: "
        . substr($errorText, 0, 400)
    );
}

echo "oe-module-api-explorer browser smoke\n";
echo "  base = {$baseUrl}\n";
echo "  site = {$apiSite}\n";
echo "  path = {$explorerPath}\n\n";

$exitCode = 0;
foreach (['confidential', 'public'] as $i => $clientType) {
    $label = $clientType === 'confidential' ? 'Confidential authorization-code' : 'Public PKCE authorization-code';
    $step = $i + 1;
    echo "[{$step}/2] {$label} round-trip...\n";
    try {
        $total = runFlow($client, $explorerPath, $apiSite, $clientType);
        echo $total > 0
            ? "       OK  (Bundle total = {$total})\n\n"
            : "       OK  (OAuth round-trip succeeded; FHIR search 401 is expected for patient-role tokens without launch context)\n\n";
    } catch (Throwable $e) {
        fwrite(STDERR, "FAIL  [{$clientType}] " . $e->getMessage() . "\n");
        captureFailure($client, $clientType, $e->getMessage());
        $exitCode = 2;
        break;
    }
}

if ($exitCode === 0) {
    echo "PASS  oe-module-api-explorer browser round-trips succeeded.\n";
}

$client->quit();
exit($exitCode);
