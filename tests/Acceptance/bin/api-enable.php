<?php

/**
 * Panther-driven bootstrap: enable OpenEMR's REST API globals + set
 * site_addr_oath to the acceptance-runner-visible URL, both via the
 * admin panel's Administration → Globals → Connectors tab.
 *
 * Fires between artifact boot and any acceptance test that needs the
 * API enabled with correct OAuth issuer configuration. Currently that
 * means Phase 4a-3's OAuth2 successful-flow tests (extended
 * OAuth2SmokeTest assertions) and the eventual authenticated
 * `/api/version` tests (Phase 4a-3 follow-up).
 *
 * Why the admin panel rather than direct SQL writes or install-time
 * env-var overrides:
 *   - The admin panel IS the user-facing API-enable path. Real
 *     admins flip these toggles from Administration → Globals. If
 *     the panel breaks (tab-render regression, form field renaming,
 *     save handler bug), no shipped artifact can enable its API —
 *     acceptance should catch that by using the same path.
 *   - install-helper.php + docker's auto_configure.php could set
 *     the globals directly (via SQL after Installer::quick_install),
 *     but that decouples the acceptance harness from the real
 *     admin-panel flow and would leave a regression there
 *     undetected.
 *
 * Env inputs:
 *   ACCEPTANCE_ARTIFACT_URL — the URL Panther points Chrome at. Also
 *                             used as site_addr_oath so the OAuth
 *                             issuer claim matches what tests hit.
 *   SELENIUM_USE_GRID       — see Support/BrowserSession.
 *   OPENEMR_ENABLE_API_BOOTSTRAP — opt-in guard. Set to 1 to run;
 *                             refuse without it. Same
 *                             defence-in-depth pattern
 *                             install-helper.php uses.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("api-enable.php is a CLI-only bootstrap; refusing non-CLI invocation\n");
}

if (!getenv('OPENEMR_ENABLE_API_BOOTSTRAP')) {
    fwrite(STDERR, "api-enable.php: refusing to run without OPENEMR_ENABLE_API_BOOTSTRAP=1\n");
    exit(1);
}

require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use OpenEMR\Tests\Acceptance\Support\ArtifactBrowser;
use OpenEMR\Tests\Acceptance\Support\BrowserSession;

$baseUrl = ArtifactBrowser::baseUrl();

echo "==> api-enable.php: bootstrapping API via admin panel\n";
echo "    Artifact URL: {$baseUrl}\n";
echo "    site_addr_oath target: {$baseUrl}\n";

$client = BrowserSession::create();
try {
    // Step 1: log in as admin.
    echo "==> Logging in as admin\n";
    $client->request('GET', $baseUrl . '/interface/login/login.php?site=default');
    $client->submitForm('login-button', ['authUser' => 'admin', 'clearPass' => 'pass']);
    $client->waitFor('#mainMenu', 30);

    // Step 2: load the globals editor.
    echo "==> Loading /interface/super/edit_globals.php\n";
    $client->request('GET', $baseUrl . '/interface/super/edit_globals.php');
    $client->waitFor('#theform', 30);

    // Step 3: look up each target field's form_N name by walking
    // the label text. Field names are indexed (form_0..form_N) based
    // on iteration order over the globals.inc.php metadata array, so
    // absolute indices shift when globals get added/removed. Label
    // text is the stable identifier.
    echo "==> Resolving field names by label\n";
    /** @var string $mappingJson */
    $mappingJson = $client->executeScript(<<<'JS_WRAP'
        function findInputByLabel(labelText) {
            const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
            let node;
            while (node = walker.nextNode()) {
                if (node.textContent.includes(labelText)) {
                    let el = node.parentElement;
                    for (let i = 0; i < 8; i++) {
                        const inputs = el.querySelectorAll('input, select, textarea');
                        if (inputs.length > 0) {
                            return { name: inputs[0].name, type: inputs[0].type };
                        }
                        el = el.parentElement;
                        if (!el) break;
                    }
                }
            }
            return null;
        }
        return JSON.stringify({
            site_addr_oath:   findInputByLabel('Site Address Override'),
            rest_api:         findInputByLabel('Enable OpenEMR Standard REST API'),
            rest_fhir_api:    findInputByLabel('Enable OpenEMR Standard FHIR REST API'),
            rest_portal_api:  findInputByLabel('Enable OpenEMR Patient Portal REST API'),
        });
    JS_WRAP);
    $mapping = json_decode($mappingJson, true, 512, JSON_THROW_ON_ERROR);
    foreach (['site_addr_oath', 'rest_api', 'rest_fhir_api', 'rest_portal_api'] as $key) {
        if (empty($mapping[$key])) {
            // Throw rather than exit() so the outer finally block runs
            // (Chrome subprocess gets $client->quit() and doesn't leak).
            throw new RuntimeException(
                "could not find '{$key}' input by label — admin panel structure may have regressed",
            );
        }
        echo "    {$key} → form field '{$mapping[$key]['name']}' (type={$mapping[$key]['type']})\n";
    }

    // Step 4: set the 3 checkbox toggles + the text field. Uses JS
    // rather than WebDriver's click()/sendKeys() because openemr's
    // edit_globals form has a very high field count (400+) with lots
    // of scrolling; JS assignment is faster and doesn't depend on
    // scrolling elements into view.
    echo "==> Setting field values\n";
    $client->executeScript(<<<JS
        const map = {$mappingJson};
        const url = arguments[0];
        for (const [key, target] of Object.entries({
            site_addr_oath: url,
            rest_api: true,
            rest_fhir_api: true,
            rest_portal_api: true,
        })) {
            const el = document.querySelector('[name="' + map[key].name + '"]');
            if (map[key].type === 'checkbox') {
                el.checked = target;
            } else {
                el.value = target;
            }
            el.dispatchEvent(new Event('change', {bubbles: true}));
        }
    JS, [$baseUrl]);

    // Step 5: submit form_save. Two anti-patterns to avoid here:
    //
    //  (a) button->click() -- unreliable in this Panther+ChromeDriver
    //      combo. Observed "0 POSTs in access logs after clicks that
    //      returned success" in the install-wizard flow that led
    //      InstallWizardUiTest to switch away from button->click()
    //      (see tests/Acceptance/Install/InstallWizardUiTest.php:155-
    //      158). Same silent-no-POST failure was observed here on
    //      api-enable-post-install cells across daily acceptance-
    //      docker + recovery-path-smoketest + rel-branch sync PR CIs.
    //
    //  (b) form->submit() (or the JS equivalent form.submit()) --
    //      superficially fixes (a) BUT drops the submitter button's
    //      name/value from the POST body. edit_globals.php's server-
    //      side handler gates on `$_POST['form_save']` being truthy
    //      (see interface/super/edit_globals.php line 221 for the
    //      admin-mode branch + line 165 for the user-mode branch),
    //      which requires the form_save button to have been the
    //      submitter. Without it, the whole POST body still arrives
    //      but the save handler branch is never entered, and the
    //      subsequent verify sees the pre-submit state -- exact same
    //      symptom as (a).
    //
    // Correct fix: JS `form.requestSubmit(button)`. Modern
    // HTMLFormElement API (WHATWG spec, all browsers Chrome 76+ /
    // Firefox 75+ / Safari 16+) that fires the submit event AS IF
    // the user had clicked the specified button. Includes the
    // submitter's name/value in the POST body (same as button-click)
    // AND is a direct JS API call rather than a synthesized DOM
    // click event, so it's not subject to the ChromeDriver click-
    // dispatch quirk that plagues (a).
    //
    // Grab a reference to the form BEFORE submit so we can use it
    // as a staleness anchor in step 6.
    echo "==> Submitting form (via form.requestSubmit(form_save button))\n";
    $form = $client->findElement(WebDriverBy::id('theform'));
    $client->executeScript(
        "document.getElementById('theform').requestSubmit("
        . "document.querySelector('[name=\"form_save\"]'));",
    );

    // Step 6: verify by re-reading the field values after reload.
    // edit_globals.php pre-populates from DB, so if our save took,
    // the reloaded page will show the new values.
    //
    // Two-phase wait: first wait for the OLD form reference to go
    // stale (the POST landed and the page navigated). Only then wait
    // for the NEW form to render. Without the staleness gate, the
    // waitFor('#theform') below would return immediately because
    // #theform is already present in the DOM (it's the form we just
    // submitted, pre-navigation), and the verify JS would read the
    // pre-navigation state. Same race the pre-fix version hit
    // intermittently even when the button-click DID fire the POST.
    echo "==> Waiting for post-save navigation (staleness of old form)\n";
    $client->wait(30)->until(WebDriverExpectedCondition::stalenessOf($form));
    echo "==> Waiting for new form to render\n";
    $client->waitFor('#theform', 30);
    /** @var string $verifyJson */
    $verifyJson = $client->executeScript(<<<JS_WRAP
        function findInputByLabel(labelText) {
            const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
            let node;
            while (node = walker.nextNode()) {
                if (node.textContent.includes(labelText)) {
                    let el = node.parentElement;
                    for (let i = 0; i < 8; i++) {
                        const inputs = el.querySelectorAll('input, select, textarea');
                        if (inputs.length > 0) {
                            const inp = inputs[0];
                            return { value: inp.value, checked: inp.checked ?? null };
                        }
                        el = el.parentElement;
                        if (!el) break;
                    }
                }
            }
            return null;
        }
        return JSON.stringify({
            site_addr_oath:   findInputByLabel('Site Address Override'),
            rest_api:         findInputByLabel('Enable OpenEMR Standard REST API'),
            rest_fhir_api:    findInputByLabel('Enable OpenEMR Standard FHIR REST API'),
            rest_portal_api:  findInputByLabel('Enable OpenEMR Patient Portal REST API'),
        });
    JS_WRAP);
    $verify = json_decode($verifyJson, true, 512, JSON_THROW_ON_ERROR);
    echo "==> Post-save state:\n";
    foreach ($verify as $key => $state) {
        echo "    {$key}: " . json_encode($state) . "\n";
    }
    $failed = [];
    if (($verify['site_addr_oath']['value'] ?? '') !== $baseUrl) {
        $failed[] = "site_addr_oath (expected '{$baseUrl}', got '" . ($verify['site_addr_oath']['value'] ?? '') . "')";
    }
    foreach (['rest_api', 'rest_fhir_api', 'rest_portal_api'] as $key) {
        if (($verify[$key]['checked'] ?? false) !== true) {
            $failed[] = $key . ' (expected checked=true)';
        }
    }
    if (!empty($failed)) {
        // Throw rather than exit() so the outer finally block runs
        // (Chrome subprocess gets $client->quit() and doesn't leak).
        throw new RuntimeException(
            'post-save verification failed for: ' . implode(', ', $failed),
        );
    }

    echo "==> Bootstrap complete: API enabled + site_addr_oath set\n";
} finally {
    $client->quit();
}
