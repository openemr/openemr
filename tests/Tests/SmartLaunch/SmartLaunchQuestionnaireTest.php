<?php

/**
 * SMART launch end-to-end smoke against openemr/oe-smart-questionnaire.
 *
 * See openemr/openemr#14529 for the shape and ci/smart-launch/run.sh for
 * the orchestration (compose boot, app install, DCR, client enable) that
 * must run before this test.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\SmartLaunch;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use OpenEMR\FHIR\SMART\SMARTLaunchToken;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

final class SmartLaunchQuestionnaireTest extends PantherTestCase
{
    /**
     * The internal openemr service alias is reachable from both the
     * openemr container's own PHP (so the SMART app's server-side
     * token exchange works) and the selenium container's Chromium
     * (so the browser-side authorize/consent navigation works). Using
     * one hostname for both sides is the key design choice this smoke
     * depends on — see the issue body + brief for why acceptance-side
     * was rejected over the equivalent host/container URL-alignment
     * problem.
     */
    private const OPENEMR_BASE = 'https://openemr';
    private const APP_BASE = 'https://openemr/smart-questionnaire';
    private const FHIR_BASE = 'https://openemr/apis/default/fhir';

    /**
     * Title of the fixture Questionnaire run.sh's seed_smoke_questionnaire
     * inserts into questionnaire_repository. Kept in sync with the JSON
     * blob in run.sh — a drift there without updating this constant
     * fails the write-round-trip test on the picker-click step.
     */
    private const SEEDED_QUESTIONNAIRE_TITLE = 'SMART Launch Smoke Questionnaire';

    private Client $panther;

    protected function setUp(): void
    {
        parent::setUp();

        // Chrome via selenium:4444. acceptInsecureCerts +
        // --ignore-certificate-errors because the flex image serves
        // HTTPS with a self-signed dev cert. HTTPS (rather than
        // plain HTTP) is required: OpenEMR's authserverOpenEMR
        // session cookie is Secure+SameSite=None, which Chromium
        // only sends over HTTPS.
        $chromeOptions = new ChromeOptions();
        $chromeOptions->addArguments([
            '--headless=new',
            '--no-sandbox',
            '--disable-gpu',
            '--disable-dev-shm-usage',
            '--window-size=1920,1080',
            '--ignore-certificate-errors',
            '--allow-insecure-localhost',
        ]);
        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability(ChromeOptions::CAPABILITY, $chromeOptions);
        $capabilities->setCapability('acceptInsecureCerts', true);

        $seleniumHost = getenv('SELENIUM_HOST') ?: 'selenium';
        $this->panther = Client::createSeleniumClient(
            "http://{$seleniumHost}:4444/wd/hub",
            $capabilities,
            self::OPENEMR_BASE,
        );
    }

    protected function tearDown(): void
    {
        try {
            $this->panther->quit();
        } catch (WebDriverException) {
            // Session already closed / chromedriver subprocess already
            // exited — nothing to clean up. Narrower than catch(\Throwable)
            // (which forbiddenCatchType rejects) and covers every
            // WebDriver quit() failure mode this test could hit.
        }
        parent::tearDown();
    }

    /**
     * Drives the full standalone SMART launch flow against the
     * oe-smart-questionnaire app installed at /smart-questionnaire
     * by ci/smart-launch/run.sh:
     *
     *   1. GET /smart-questionnaire/launch.php?iss=<fhir base>
     *      — the app's PHP creates a per-launch state (PKCE verifier +
     *        state) and 302s to OpenEMR's /oauth2/default/authorize.
     *   2. OpenEMR renders the login form; test posts admin/pass.
     *   3. OpenEMR's SMART patient-select screen (triggered by the
     *      launch/patient scope); test picks the first demo patient.
     *   4. OpenEMR's consent screen; every scope checkbox is pre-checked
     *      by default — test clicks `proceed`.
     *   5. Browser is redirected back to <app>/callback.php?code=...;
     *      the app exchanges the code for a bearer token server-side
     *      and 302s to index.php?lid=<launch id>.
     *   6. index.php reads the launch state and fetches Patient (banner)
     *      + Questionnaire (picker) via the bearer token. Test asserts
     *      the demo patient's name appears in the banner — proof the
     *      bearer-token Patient read works end-to-end.
     *
     * The pre-checked default scopes on step 4 ARE the v1 scope profile
     * the app registered with — openid, fhirUser, launch/patient, plus
     * resource-level grants for Patient, Questionnaire, and
     * QuestionnaireResponse. Don't "fix" a future regression by flipping
     * the app's config to v2 (.rs + .cu); v2 fails by design against
     * current OpenEMR per the upstream README.
     */
    #[Test]
    public function testStandaloneLaunchReachesAppWithPatientContext(): void
    {
        $pickedPatientName = $this->driveStandaloneLaunchToAppIndex();

        $this->panther->waitFor('body', 10);
        $bodyText = $this->panther->findElement(WebDriverBy::tagName('body'))->getText();

        // Patient read: the banner DOB in YYYY-MM-DD form proves the
        // app made its initial bearer-token Patient GET against the
        // FHIR API. Matching the format rather than a specific demo-
        // data patient name keeps the assertion stable across demo-data
        // changes.
        $this->assertMatchesRegularExpression(
            '/\bDOB:\s*\d{4}-\d{2}-\d{2}\b/',
            $bodyText,
            'app index.php should render a patient banner with DOB — proof that bearer-token Patient read succeeded',
        );

        // Patient context survived the OAuth round-trip: the banner
        // name matches the patient we picked on the Stage-3 patient-
        // select screen. Catches "context switched mid-flow" regressions
        // (which the DOB-pattern match alone would miss — any patient's
        // DOB would satisfy the format).
        $this->assertStringContainsString(
            $pickedPatientName,
            $bodyText,
            "picked patient '{$pickedPatientName}' should appear in the app's banner — context dropped between patient-select and index.php",
        );

        // Questionnaire search: index.php renders a "Choose a
        // questionnaire" picker populated from a bearer-token
        // Questionnaire GET. ci/smart-launch/run.sh seeds a single
        // fixture Questionnaire so the picker is non-empty here —
        // asserting the seeded title in the picker catches both the
        // "FHIR search failed entirely" regression and the "search
        // returned but the app filtered our fixture out" regression.
        $this->assertStringContainsString(
            self::SEEDED_QUESTIONNAIRE_TITLE,
            $bodyText,
            'app index.php should list the seeded Questionnaire — proof that bearer-token Questionnaire search returned real data',
        );
    }

    /**
     * launch.php with an iss that was never registered via DCR must
     * reject the launch before contacting the issuer at all. Without
     * this check the app would 302 the user's browser to an attacker-
     * supplied authorize endpoint, happily sending credentials and
     * code to whoever named a URL in the `iss` query param. The
     * upstream app refuses in Config::clientFor and renders a 400
     * "Launch failed · This app is not registered with …" page.
     *
     * The page is rendered server-side; Panther can only see the
     * resulting DOM, not the HTTP status code. Title + body copy are
     * what we assert against instead.
     */
    #[Test]
    public function testLaunchWithUnknownIssuerIsRejectedByTheApp(): void
    {
        $unknownIssuer = 'https://unknown.example/apis/default/fhir';
        $this->panther->request(
            'GET',
            self::APP_BASE . '/launch.php?iss=' . rawurlencode($unknownIssuer),
        );
        $this->panther->waitFor('body', 10);

        $this->assertStringContainsString(
            'Launch failed',
            $this->panther->getTitle(),
            'launch.php with an unregistered iss should render the "Launch failed" error page',
        );
        $bodyText = $this->panther->findElement(WebDriverBy::tagName('body'))->getText();
        $this->assertStringContainsString(
            'This app is not registered with',
            $bodyText,
            'rejection page should include the "not registered" copy from Config::clientFor',
        );
        $this->assertStringContainsString(
            $unknownIssuer,
            $bodyText,
            'rejection page should echo the rejected iss so the operator sees what was passed',
        );
    }

    /**
     * launch.php with no params renders a picker of registered servers
     * for standalone launch. ci/smart-launch/run.sh registers one client
     * against the compose-internal openemr issuer, so the picker should
     * list exactly that issuer as a clickable link.
     *
     * This exercises the "clients registered" branch of launch.php's
     * no-iss handler — the complement to the "none registered" branch
     * the app shows before `bin/register.php` runs.
     */
    #[Test]
    public function testLaunchWithoutIssuerRendersRegisteredServerPicker(): void
    {
        $this->panther->request('GET', self::APP_BASE . '/launch.php');
        $this->panther->waitFor('body', 10);
        $bodyText = $this->panther->findElement(WebDriverBy::tagName('body'))->getText();

        $this->assertStringContainsString(
            'Choose a server for a standalone launch',
            $bodyText,
            'no-iss launch should render the server picker copy — missing it means we hit the "no clients registered" branch unexpectedly',
        );
        $this->assertStringContainsString(
            self::FHIR_BASE,
            $bodyText,
            'server picker should list the issuer run.sh registered (' . self::FHIR_BASE . ')',
        );
    }

    /**
     * Full OAuth round-trip plus FHIR Questionnaire write: launch →
     * authorize → login → patient-select → consent → index.php →
     * pick the seeded Questionnaire → Start new → fill the required
     * item → Complete. The app's api.php?action=save endpoint runs
     * server-side with the bearer token minted during the OAuth
     * exchange and POSTs a `QuestionnaireResponse` to OpenEMR's FHIR
     * API, so a successful save proves the bearer-token FHIR WRITE
     * path works end-to-end (the standalone-launch test only exercises
     * reads).
     *
     * Signals of a successful save we assert against:
     *   - #sq-status badge shows "completed"
     *   - #sq-alert banner contains "Saved as completed"
     *   - #sq-working line names the created QuestionnaireResponse
     *     by FHIR id (format: `QuestionnaireResponse/<uuid>`) —
     *     this is the strongest signal because it means the FHIR POST
     *     returned the stamped resource with a server-issued id
     */
    #[Test]
    public function testStandaloneLaunchCompletesQuestionnaireWriteRoundTrip(): void
    {
        $this->driveStandaloneLaunchToAppIndex();

        // Open the seeded Questionnaire from the picker.
        $this->panther->waitFor('a.list-group-item', 10);
        $picker = $this->panther->findElement(WebDriverBy::cssSelector('a.list-group-item'));
        $this->assertStringContainsString(
            self::SEEDED_QUESTIONNAIRE_TITLE,
            $picker->getText(),
            'seeded Questionnaire should appear as the first (only) picker item',
        );
        $picker->click();

        // Start a new response (switches the form from read-only to
        // editable state; required inputs are rendered live by the
        // bundled openemr_questionnaire.js runtime).
        $this->panther->waitFor('button#sq-new', 15);
        $this->panther->findElement(WebDriverBy::id('sq-new'))->click();
        $this->panther->waitFor('input.form-control', 10);

        // Fill the single required string item. sendKeys alone is not
        // enough — the runtime watches for input/change/blur events to
        // mark the field answered. Dispatch all three explicitly so the
        // response passes the client-side "required items filled" gate
        // on Complete.
        $input = $this->panther->findElement(WebDriverBy::cssSelector('input.form-control'));
        $input->sendKeys('Doing great');
        $this->panther->executeScript(
            'arguments[0].dispatchEvent(new Event("input", {bubbles: true}));'
            . 'arguments[0].dispatchEvent(new Event("change", {bubbles: true}));'
            . 'arguments[0].blur();',
            [$input],
        );

        // Click Complete — fires POST api.php?action=save which the
        // app's PHP forwards to OpenEMR's FHIR API as a POST
        // QuestionnaireResponse with the bearer token minted earlier.
        $this->panther->findElement(WebDriverBy::id('sq-complete'))->click();

        // Wait for the status badge to flip to "completed". The runtime
        // only updates the badge after the server returns 200 with the
        // stamped FHIR resource, so this is a terminal success signal.
        $this->panther->wait(20)->until(
            static fn (WebDriver $d): bool => str_contains(
                $d->findElement(WebDriverBy::id('sq-status'))->getText(),
                'completed',
            ),
        );

        $bodyText = $this->panther->findElement(WebDriverBy::tagName('body'))->getText();
        $this->assertStringContainsString(
            'Saved as completed',
            $bodyText,
            '#sq-alert should confirm the save landed — missing it means api.php returned non-2xx',
        );
        $this->assertMatchesRegularExpression(
            '~QuestionnaireResponse/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}~',
            $bodyText,
            '#sq-working should name the created QuestionnaireResponse by FHIR uuid — proof the POST returned a stamped resource',
        );
    }

    /**
     * EHR-launch variant: instead of the browser hitting launch.php
     * cold with an iss, the user is already signed into OpenEMR with
     * a patient open, clicks a "Launch SMART App" button from
     * `interface/patient_file/assessment/fhir_assessments.php`, and
     * OpenEMR mints a `launch=<opaque>` token encoding the patient
     * context. The SMART app's launch.php sees `launch=` set and
     * threads that through authorize/token so the token response
     * includes a `patient` field — meaning the SMART app skips its
     * standalone-launch patient-select UI entirely.
     *
     * The unique regression this test catches: a break anywhere in
     * SmartLaunchController's launch-token mint, the authorize
     * endpoint's launch-param handling, or the token endpoint's
     * patient-claim passthrough would surface as the SMART app
     * stopping at patient-select (because its PHP sees no patient
     * context in the token response) rather than going straight to
     * index.php. The assertion checks for both: no patient-select
     * screen, AND index.php lands with the preselected patient's
     * data in the banner.
     *
     * The flow bypasses the dlgopen() modal the fhir_assessments.php
     * UI uses by posting the `interface/smart/ehr-launch-client.php`
     * URL directly (same client_id + csrf_token + intent the dropdown
     * buttons use). Equivalent round-trip; cheaper DOM navigation.
     */
    #[Test]
    public function testEhrLaunchSkipsPatientSelectAndLandsOnApp(): void
    {
        // Sign into OpenEMR as admin.
        $this->panther->request('GET', self::OPENEMR_BASE . '/interface/login/login.php?site=default');
        $this->panther->waitFor('form', 10);
        $this->panther->findElement(WebDriverBy::name('authUser'))->sendKeys('admin');
        $this->panther->findElement(WebDriverBy::name('clearPass'))->sendKeys('pass');
        $this->panther->executeScript('document.forms[0].submit();');
        $this->panther->wait(15)->until(
            static fn (WebDriver $d): bool => str_contains($d->getCurrentURL(), '/interface/main/tabs/main.php'),
        );

        // Set a patient as the active chart context. demographics.php
        // honors ?set_pid=N and writes `pid` into $_SESSION. pid=1 is
        // the first demo patient the installer seeds via demoData.
        $this->panther->request(
            'GET',
            self::OPENEMR_BASE . '/interface/patient_file/summary/demographics.php?set_pid=1',
        );
        $this->panther->waitFor('body', 10);

        // Load fhir_assessments.php — it renders the Launch-SMART-App
        // buttons when smart_context_test_launches is on (run.sh
        // enables that global). Pull client_id + csrf from the first
        // patient-intent button.
        $this->panther->request(
            'GET',
            self::OPENEMR_BASE . '/interface/patient_file/assessment/fhir_assessments.php',
        );
        $this->panther->waitFor('.smart-context-launch', 10);
        $launchBtn = $this->panther->findElement(WebDriverBy::cssSelector('.smart-context-launch'));
        $clientId = $launchBtn->getAttribute('data-client-id');
        $intent = $launchBtn->getAttribute('data-intent');
        $this->assertNotEmpty(
            $clientId,
            'fhir_assessments.php .smart-context-launch button must expose data-client-id',
        );
        $this->assertSame(
            'patient.demographics.dialog',
            $intent,
            'first launch button should carry the patient-demographics intent',
        );
        // CSRF is set as a JS var at page scope — pulling it out of
        // the DOM source is simpler than extracting from a
        // DOMElement/attribute, and matches the shape the fhir_
        // assessments.php page uses when it posts to ehr-launch-client.
        if (preg_match('/smartLaunchCsrfToken\s*=\s*[\'"]([^\'"]+)/', $this->panther->getPageSource(), $m) !== 1) {
            $this->fail('smartLaunchCsrfToken not found in fhir_assessments.php page source');
        }
        $csrf = $m[1];

        // Fire the EHR launch. ehr-launch-client.php mints the opaque
        // launch token, 302s to the SMART app's launch_uri with
        // ?iss=&launch=; the SMART app threads the launch through
        // authorize/token so patient context is already set when the
        // browser lands on index.php.
        $this->panther->request('GET', self::OPENEMR_BASE . '/interface/smart/ehr-launch-client.php?' . http_build_query([
            'client_id' => $clientId,
            'csrf_token' => $csrf,
            'intent' => $intent,
        ]));

        // Authorize endpoint reauth: even though we're already signed
        // in as admin, OpenEMR re-prompts for the api user role.
        $this->panther->waitFor('form#userLogin', 15);
        $this->panther->findElement(WebDriverBy::name('username'))->sendKeys('admin');
        $this->panther->findElement(WebDriverBy::name('password'))->sendKeys('pass');
        $loginForm = $this->panther->findElement(WebDriverBy::id('userLogin'));
        $this->panther->executeScript(
            "document.getElementById('userLogin').requestSubmit("
            . "document.querySelector('form#userLogin button[type=\"submit\"], form#userLogin input[type=\"submit\"]'));",
        );
        $this->panther->wait(15)->until(WebDriverExpectedCondition::stalenessOf($loginForm));

        // Core EHR-launch assertion: patient-select never renders
        // because OpenEMR mints the launch token with patient already
        // set, so the token response includes `patient` and the SMART
        // app's standalone-launch picker is bypassed. The next page
        // after login should be scope-authorize-confirm, not
        // smart/patient-select.
        $this->assertStringNotContainsString(
            '/smart/patient-select',
            $this->panther->getCurrentURL(),
            'EHR launch should NOT prompt for patient-select (patient context should flow via launch token)',
        );
        $this->assertStringContainsString(
            '/scope-authorize-confirm',
            $this->panther->getCurrentURL(),
            'EHR launch should land directly on consent after login',
        );

        // Approve consent + wait for app landing.
        $this->panther->findElement(WebDriverBy::name('proceed'))->click();
        $this->panther->wait(20)->until(
            static fn (WebDriver $d): bool => str_contains($d->getCurrentURL(), '/smart-questionnaire/index.php?lid='),
        );

        // Note on no banner-DOB assertion: EHR-launched SMART apps per
        // SMART-App-Launch 2.x spec receive `need_patient_banner: false`
        // in the token response (the EHR already displays the patient
        // banner in its own chrome, so the app suppresses its own).
        // The SMART app honors that and skips the banner — proving the
        // read-the-DOB check that works for standalone launches fails
        // here even though patient context IS passing. Use the picker
        // + Questionnaire-read assertion instead, which exercises the
        // bearer-token FHIR path end-to-end.
        $this->panther->waitFor('body', 10);
        $bodyText = $this->panther->findElement(WebDriverBy::tagName('body'))->getText();
        $this->assertStringContainsString(
            self::SEEDED_QUESTIONNAIRE_TITLE,
            $bodyText,
            'EHR-launched app should list the seeded Questionnaire — proof the bearer token minted via launch-token path and Questionnaire search succeeded',
        );
    }

    /**
     * EHR-launch CSRF-rejection gate: ehr-launch-client.php is the
     * server-side launcher that mints the opaque launch token and 302s
     * to the SMART app. Any client-side form or button that POSTs to it
     * carries a session-scoped csrf_token. A missing or forged csrf_token
     * must be rejected with a 403 before the launch token is ever
     * generated — otherwise an attacker who can get an authenticated
     * admin to visit a URL under their control could trigger a legitimate
     * launch (and a chosen patient context) against a client of their
     * choosing.
     *
     * `SmartLaunchController::redirectAndLaunchSmartApp()` throws
     * `CsrfInvalidException` on a bad csrf; the top-level handler in
     * `interface/smart/ehr-launch-client.php` catches it and calls
     * `CsrfUtils::csrfNotVerified()`, which sets HTTP 403 and renders
     * `xlt('Authentication Error')`. The assertion matches on both the
     * status code and the rendered text so a regression that silently
     * drops either signal fails loudly.
     */
    #[Test]
    public function testEhrLaunchRejectsBadCsrfToken(): void
    {
        // Sign in + set patient context + grab the real client_id from
        // fhir_assessments.php. We need a legitimate client_id so the
        // request progresses past the empty-client guard and reaches
        // the csrf check (client-id check happens before csrf check
        // in redirectAndLaunchSmartApp — a bogus client_id would short-
        // circuit via a different error path).
        $this->panther->request('GET', self::OPENEMR_BASE . '/interface/login/login.php?site=default');
        $this->panther->waitFor('form', 10);
        $this->panther->findElement(WebDriverBy::name('authUser'))->sendKeys('admin');
        $this->panther->findElement(WebDriverBy::name('clearPass'))->sendKeys('pass');
        $this->panther->executeScript('document.forms[0].submit();');
        $this->panther->wait(15)->until(
            static fn (WebDriver $d): bool => str_contains($d->getCurrentURL(), '/interface/main/tabs/main.php'),
        );
        $this->panther->request(
            'GET',
            self::OPENEMR_BASE . '/interface/patient_file/summary/demographics.php?set_pid=1',
        );
        $this->panther->waitFor('body', 10);
        $this->panther->request(
            'GET',
            self::OPENEMR_BASE . '/interface/patient_file/assessment/fhir_assessments.php',
        );
        $this->panther->waitFor('.smart-context-launch', 10);
        $launchBtn = $this->panther->findElement(WebDriverBy::cssSelector('.smart-context-launch'));
        $clientId = $launchBtn->getAttribute('data-client-id');
        $intent = $launchBtn->getAttribute('data-intent');
        $this->assertNotEmpty($clientId);

        // Fire ehr-launch-client with a bogus csrf_token. The request
        // reaches redirectAndLaunchSmartApp, passes the client-id
        // lookup, then fails verifyCsrfToken.
        $this->panther->request('GET', self::OPENEMR_BASE . '/interface/smart/ehr-launch-client.php?' . http_build_query([
            'client_id' => $clientId,
            'csrf_token' => 'bogus-csrf-token-not-session-bound',
            'intent' => $intent,
        ]));
        $this->panther->waitFor('body', 10);
        $bodyText = $this->panther->findElement(WebDriverBy::tagName('body'))->getText();

        // The csrfNotVerified() renderer echoes `xlt('Authentication Error')`.
        // The body-text assertion catches a regression that silently
        // drops the renderer (e.g. a refactor that changes to a different
        // error page) — the renderer identity matters because that is
        // what tells the operator what rejected.
        $this->assertStringContainsString(
            'Authentication Error',
            $bodyText,
            'ehr-launch-client should emit csrfNotVerified() Authentication Error on a bad csrf',
        );
        // Belt-and-braces: ehr-launch-client should NOT 302 to any
        // app launch_uri when csrf fails. Stay on
        // /interface/smart/ehr-launch-client.php. A silent redirect to
        // the app's launch.php here would mean the launch token was
        // minted anyway and the CSRF guard was decorative.
        $this->assertStringContainsString(
            '/interface/smart/ehr-launch-client.php',
            $this->panther->getCurrentURL(),
            'bad csrf must NOT redirect to the SMART app — launch token should never have been minted',
        );
    }

    /**
     * EHR-launch unknown-client-id gate: a POST to ehr-launch-client.php
     * with a `client_id` that does not match any registered oauth_client
     * row must fail BEFORE csrf verification — the server has no way to
     * know the correct app's launch_uri to redirect to, and any legacy
     * behavior that falls through to a generic error page is still
     * meaningfully different from "launched the wrong app silently".
     *
     * `SmartLaunchController::redirectAndLaunchSmartApp()` throws a
     * plain `\Exception('Invalid client id')` when
     * `ClientRepository::getClientEntity()` returns empty; the top-level
     * `\Throwable` catch in `interface/smart/ehr-launch-client.php` logs
     * the trace and dies with the generic message `"Unknown system
     * error occurred"`. Assert on that message — the generic shape is
     * the contract. A regression that leaks client-id state back to the
     * browser (echoed id, SQL fragment, trace) would NOT contain this
     * exact string.
     */
    #[Test]
    public function testEhrLaunchRejectsUnknownClientId(): void
    {
        $this->panther->request('GET', self::OPENEMR_BASE . '/interface/login/login.php?site=default');
        $this->panther->waitFor('form', 10);
        $this->panther->findElement(WebDriverBy::name('authUser'))->sendKeys('admin');
        $this->panther->findElement(WebDriverBy::name('clearPass'))->sendKeys('pass');
        $this->panther->executeScript('document.forms[0].submit();');
        $this->panther->wait(15)->until(
            static fn (WebDriver $d): bool => str_contains($d->getCurrentURL(), '/interface/main/tabs/main.php'),
        );
        $this->panther->request(
            'GET',
            self::OPENEMR_BASE . '/interface/patient_file/summary/demographics.php?set_pid=1',
        );
        $this->panther->waitFor('body', 10);

        $this->panther->request('GET', self::OPENEMR_BASE . '/interface/smart/ehr-launch-client.php?' . http_build_query([
            'client_id' => 'nonexistent-client-id-smoke-test',
            // csrf_token irrelevant here — the client lookup fails first.
            'csrf_token' => 'does-not-matter',
            'intent' => 'patient.demographics.dialog',
        ]));
        $this->panther->waitFor('body', 10);
        $bodyText = $this->panther->findElement(WebDriverBy::tagName('body'))->getText();

        $this->assertStringContainsString(
            'Unknown system error occurred',
            $bodyText,
            'unknown client_id should die with the generic Throwable-catch message, not leak details',
        );
        $this->assertStringContainsString(
            '/interface/smart/ehr-launch-client.php',
            $this->panther->getCurrentURL(),
            'unknown client_id must NOT redirect anywhere — no legitimate launch_uri is known',
        );
        // Negative: the response body must NOT echo the attacker-supplied
        // client_id value, prove no information disclosure on this path.
        $this->assertStringNotContainsString(
            'nonexistent-client-id-smoke-test',
            $bodyText,
            'response body must not echo the submitted client_id — would be an information-disclosure regression',
        );
    }

    /**
     * Tampered-launch error-path gate: an app-launch request that arrives
     * at /oauth2/default/authorize carrying a `launch=` value that is
     * either not valid base64 or decodes to ciphertext that fails
     * decryption must be rejected before any user interaction. The
     * server-side guard lives in
     * `CustomAuthCodeGrant::validateAuthorizationRequest`, which calls
     * `SMARTLaunchToken::deserializeToken($launch)` inside a try/catch
     * and converts any `\JsonException` or `\InvalidArgumentException`
     * into an `OAuthServerException::invalidRequest('launch', ...)` with
     * the hint "launch parameter was incorrectly formatted or did not
     * originate from this server".
     *
     * The regression this catches: a refactor that silently swallows
     * the deserialization exception (or widens the catch to include
     * success-paths) would turn a maliciously supplied `launch=` value
     * into an accepted session-side token with attacker-chosen patient
     * UUIDs. The strongest signals we can assert against on the raw
     * JSON response are:
     *   - the `error` field is exactly `invalid_request`
     *   - the `hint` field names the launch param specifically — a
     *     generic "invalid request" (missing hint) means the guard
     *     fired on a different code path and the launch-token check
     *     may have been bypassed
     *
     * The authorize endpoint emits a JSON 400 directly (not a 302 back
     * to the client's redirect_uri with ?error=invalid_request),
     * because the exception is raised before the auth-request is fully
     * validated and `getRedirectUri()` isn't trusted yet. Chrome
     * renders the body as `<pre>{"error":"invalid_request", ...}` so
     * Panther sees the raw JSON in the body text — no need for a
     * separate HTTP-status probe.
     */
    #[Test]
    public function testStandaloneLaunchWithTamperedLaunchTokenIsRejectedByAuthorize(): void
    {
        $client = $this->readRegisteredSmartClient();
        // Build a REAL serialized launch token (correct version prefix,
        // correct base64 shape, correct ciphertext length), then corrupt
        // a byte of the inner encrypted blob so CryptoGen::decryptStandard
        // fails its HMAC check. A bare TAMPERED_INVALID_BASE64 placeholder
        // would also fail — but on the "base64 decode could not produce
        // valid ciphertext" path, not on the HMAC-validation path. A
        // regression that silently removed the HMAC check (while leaving
        // the base64 + length checks intact) would let the placeholder-
        // style tamper through and we would miss it. Flipping a byte of
        // the real encrypted blob keeps the shape intact and specifically
        // exercises the HMAC-reject path.
        $realLaunch = (new SMARTLaunchToken('smoke-patient-uuid', 'smoke-encounter-uuid'))->serialize();
        // The serialized form is a double base64:
        //   base64_encode("<3-byte-version-prefix>" + base64_encode("<48-byte-HMAC><16-byte-IV><ciphertext>"))
        // To exercise the HMAC-reject path specifically, flip a byte of
        // the CIPHERTEXT (not the outer base64 text, not the HMAC bytes
        // themselves, not the IV). The HMAC is computed over IV +
        // ciphertext, so a ciphertext-byte flip breaks the HMAC check
        // in CryptoGen::coreDecrypt while every structural check
        // (version prefix, outer base64, inner base64, length) passes.
        //
        // A naive byte-flip on the outer-decoded buffer would land in
        // the inner base64 text and would fail the base64-strict check
        // instead — a different code path from the HMAC check this
        // test is supposed to lock in. Rabbit found that regression in
        // the first version of this fixture.
        $decoded = base64_decode($realLaunch, true);
        self::assertNotFalse($decoded, 'real serialize() output must be strict-base64');
        self::assertGreaterThan(10, strlen($decoded), 'serialized token should be longer than its 3-byte version prefix');
        $versionPrefix = substr($decoded, 0, 3);
        $payload = base64_decode(substr($decoded, 3), true);
        self::assertNotFalse($payload, 'serialized token payload must be strict-base64');
        self::assertGreaterThan(
            64,
            strlen($payload),
            'payload must contain the 48-byte HMAC, the 16-byte IV, and at least one ciphertext byte',
        );
        // Offset 48 + 16 = first byte of ciphertext.
        $ciphertextOffset = 48 + 16;
        $payload[$ciphertextOffset] = chr(ord($payload[$ciphertextOffset]) ^ 0x01);
        $tamperedLaunch = base64_encode($versionPrefix . base64_encode($payload));

        $authorizeUrl = self::OPENEMR_BASE . '/oauth2/default/authorize?' . http_build_query([
            'response_type' => 'code',
            'client_id' => $client['client_id'],
            'redirect_uri' => $client['redirect_uri'],
            'scope' => 'openid fhirUser launch user/Patient.rs',
            'aud' => self::FHIR_BASE,
            'state' => 'smoke-tampered-launch',
            'launch' => $tamperedLaunch,
            'code_challenge_method' => 'S256',
            // 43-char URL-safe placeholder — the launch-token guard
            // fires before PKCE validation, so this value never has
            // to be a real PKCE challenge for this flow.
            'code_challenge' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQR',
        ]);

        $this->panther->request('GET', $authorizeUrl);
        $this->panther->waitFor('body', 10);
        $bodyText = $this->panther->findElement(WebDriverBy::tagName('body'))->getText();

        // Core assertion: the server-side `invalidRequest('launch', ...)`
        // rejection fired. A missing/renamed `error` field means the
        // response shape drifted; a value other than `invalid_request`
        // means the guard fired on a different error code path.
        $this->assertStringContainsString(
            '"error":"invalid_request"',
            $bodyText,
            'tampered launch token should surface as OAuthServerException::invalidRequest JSON',
        );
        // Hint-level assertion: the hint identifies WHICH guard rejected.
        // Catches the "guard moved / message changed" regression that a
        // generic error-code match would miss — a shift to a different
        // guard (e.g. aud/redirect_uri) could still carry
        // invalid_request but would signal the launch-token check was
        // bypassed.
        $this->assertStringContainsString(
            'launch parameter was incorrectly formatted',
            $bodyText,
            'the invalid_request hint should name the launch-token guard specifically',
        );
        // Belt-and-braces: the final URL stays on /authorize/, not
        // redirected to the client callback. A silent redirect to
        // callback with ?error=... would mean we leaked the state
        // param back to the SMART app's PHP, which should NOT happen
        // when the error fires pre-redirect-validation.
        $this->assertStringContainsString(
            '/oauth2/default/authorize',
            $this->panther->getCurrentURL(),
            'tampered launch should fail at the authorize endpoint, not redirect to the client callback',
        );
    }

    /**
     * Redirect-URI mismatch error-path gate: when the authorize request
     * names a `redirect_uri` that is not among the client's registered
     * redirect URIs, OpenEMR must reject before login so the attacker
     * never gets the chance to steer a legitimate code to an
     * uncontrolled callback. The guard lives in the league/oauth2
     * `RedirectUriValidator` and surfaces as a `401 invalid_client`
     * response (see `CustomAuthCodeGrant::validateRedirectUri` which
     * wraps the parent and logs on failure).
     *
     * The regression this catches: a refactor that loosens the
     * redirect_uri comparison (e.g. a `substr`/`strpos` instead of a
     * strict-equality match on the registered list) would re-open the
     * SMART open-redirect attack class in which an attacker-crafted
     * authorize URL silently routes the issued code to attacker-owned
     * origins. The response shape asserted here is the specific
     * `invalid_client` JSON the league library emits when
     * `redirect_uri` fails validation — distinct from
     * `invalid_request`, so a drift to the wrong error code fails
     * loudly.
     */
    #[Test]
    public function testStandaloneLaunchWithMismatchedRedirectUriIsRejectedByAuthorize(): void
    {
        $client = $this->readRegisteredSmartClient();
        $evilRedirect = 'https://evil.example/callback';
        $this->assertNotSame(
            $client['redirect_uri'],
            $evilRedirect,
            'test precondition: evil redirect must not accidentally equal the real registered one',
        );
        $authorizeUrl = self::OPENEMR_BASE . '/oauth2/default/authorize?' . http_build_query([
            'response_type' => 'code',
            'client_id' => $client['client_id'],
            'redirect_uri' => $evilRedirect,
            'scope' => 'openid fhirUser launch/patient user/Patient.rs',
            'aud' => self::FHIR_BASE,
            'state' => 'smoke-evil-redirect',
            'code_challenge_method' => 'S256',
            'code_challenge' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQR',
        ]);

        $this->panther->request('GET', $authorizeUrl);
        $this->panther->waitFor('body', 10);
        $bodyText = $this->panther->findElement(WebDriverBy::tagName('body'))->getText();

        // Error-code assertion: the league library emits
        // `invalid_client` specifically for redirect_uri mismatch
        // (not `invalid_request`). A drift to `invalid_request` here
        // would mean the validator short-circuited on a different
        // guard — still a rejection, but no longer the redirect-uri
        // one, which is the regression surface this test defends.
        $this->assertStringContainsString(
            '"error":"invalid_client"',
            $bodyText,
            'redirect_uri mismatch should surface as invalid_client — if this flips to invalid_request the redirect validator was bypassed',
        );
        // No 302 to the attacker-chosen redirect: the browser must
        // terminate on the openemr origin, not evil.example. parse_url
        // the current location rather than substring-matching the
        // whole URL — "evil.example" legitimately appears in the
        // request's query string as the rejected redirect_uri, so a
        // naive contains-check would false-positive even on a
        // correctly-rejecting server. The host/origin is the signal
        // that matters.
        $currentUrl = $this->panther->getCurrentURL();
        $landedHost = parse_url($currentUrl, PHP_URL_HOST);
        $this->assertSame(
            'openemr',
            $landedHost,
            'authorize must NOT follow the attacker-chosen redirect_uri — current host should still be openemr, not evil.example (open-redirect regression hallmark). Landed at: ' . $currentUrl,
        );
        $this->assertStringContainsString(
            '/oauth2/default/authorize',
            $currentUrl,
            'request should terminate at the authorize endpoint with no redirect at all',
        );
    }

    /**
     * Reads the oe-smart-questionnaire app's registered-clients file
     * (populated by `bin/register.php` during stack setup) to extract
     * the `client_id` + `redirect_uri` the SMART app registered with
     * OpenEMR. This mirrors how the app's own launch.php reads
     * clients.json for its server-side DCR state — using the same
     * source of truth avoids baking a stale client_id into the test.
     *
     * File path matches the layout ci/smart-launch/apache-alias.conf
     * sets up: `/opt/oe-smart-questionnaire/app/` with var/clients.json
     * holding one object keyed by issuer URL.
     *
     * @return array{client_id: string, redirect_uri: string}
     */
    private function readRegisteredSmartClient(): array
    {
        $clientsFile = '/opt/oe-smart-questionnaire/app/var/clients.json';
        $raw = @file_get_contents($clientsFile);
        if ($raw === false) {
            $this->fail("could not read SMART clients.json at {$clientsFile} — did ci/smart-launch/run.sh register a client?");
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded) || $decoded === []) {
            $this->fail("clients.json at {$clientsFile} is empty or malformed — register.php did not populate it");
        }
        $issuerKey = self::FHIR_BASE;
        $entry = $decoded[$issuerKey] ?? null;
        if (!is_array($entry)) {
            $this->fail("clients.json has no entry for issuer {$issuerKey} — register.php wrote a different issuer key");
        }
        $clientId = $entry['client_id'] ?? null;
        if (!is_string($clientId) || $clientId === '') {
            $this->fail("clients.json entry for {$issuerKey} is missing client_id");
        }
        // The app registers a single redirect_uri per site matching the
        // app_url + /callback.php — we rebuild it from APP_BASE rather
        // than requiring clients.json to carry it, because the file
        // historically has not persisted the redirect_uri field.
        return [
            'client_id' => $clientId,
            'redirect_uri' => self::APP_BASE . '/callback.php',
        ];
    }

    /**
     * Shared launch flow for the standalone tests: GET launch.php →
     * follow OpenEMR authorize → login as admin → pick the first demo
     * patient → approve the pre-checked consent → wait for
     * `index.php?lid=…` to render. Returns the picked patient's
     * display name so callers can assert the context survived.
     */
    private function driveStandaloneLaunchToAppIndex(): string
    {
        // Stage 1 — launch.php → authorize → login page.
        $this->panther->request(
            'GET',
            self::APP_BASE . '/launch.php?iss=' . rawurlencode(self::FHIR_BASE),
        );
        $this->panther->waitFor('form#userLogin', 15);
        $this->assertStringContainsString(
            '/oauth2/default/provider/login',
            $this->panther->getCurrentURL(),
            'launch.php should redirect through /oauth2/default/authorize to the OpenEMR login page',
        );

        // Stage 2 — POST login. requestSubmit (rather than button->click
        // or form.submit()) is the pattern tests/Acceptance/bin/api-enable.php
        // settled on after ChromeDriver click-dispatch flake was traced to
        // its root cause. See that file for the full history.
        $this->panther->findElement(WebDriverBy::name('username'))->sendKeys('admin');
        $this->panther->findElement(WebDriverBy::name('password'))->sendKeys('pass');
        $loginForm = $this->panther->findElement(WebDriverBy::id('userLogin'));
        $this->panther->executeScript(
            "document.getElementById('userLogin').requestSubmit("
            . "document.querySelector('form#userLogin button[type=\"submit\"], form#userLogin input[type=\"submit\"]'));",
        );
        $this->panther->wait(15)->until(WebDriverExpectedCondition::stalenessOf($loginForm));

        // Stage 3 — patient-select screen. The buttons set a hidden
        // patient_id input via JS and submit; a plain click is enough.
        $this->panther->waitFor('button.patient-btn', 15);
        $pickBtn = $this->panther->findElement(WebDriverBy::cssSelector('button.patient-btn'));
        $pickedPatientUuid = $pickBtn->getAttribute('data-patient-id');
        $this->assertNotEmpty(
            $pickedPatientUuid,
            'patient-select button must expose data-patient-id so the pick submission knows which patient',
        );
        // Capture the row's first <td> (patient name) for the caller.
        $pickedPatientName = trim($this->panther->findElement(WebDriverBy::xpath(
            '//button[@data-patient-id="' . $pickedPatientUuid . '"]/ancestor::tr/td[1]',
        ))->getText());
        $this->assertNotEmpty(
            $pickedPatientName,
            'patient-select row should have a non-empty name td — demo data regression?',
        );
        $pickBtn->click();
        $this->panther->wait(15)->until(WebDriverExpectedCondition::stalenessOf($pickBtn));

        // Stage 4 — consent screen. Every scope checkbox is pre-checked
        // by default; just submit `proceed`.
        $this->assertStringContainsString(
            '/scope-authorize-confirm',
            $this->panther->getCurrentURL(),
            'patient-select should hand off to the scope-authorize-confirm consent screen',
        );
        $proceed = $this->panther->findElements(WebDriverBy::name('proceed'));
        $this->assertNotCount(
            0,
            $proceed,
            'consent screen must expose a proceed control — missing means the page is not the OpenEMR consent screen',
        );
        $proceed[0]->click();
        $this->panther->wait(20)->until(
            static fn (WebDriver $d): bool => !str_contains($d->getCurrentURL(), '/oauth2/'),
        );

        // Stage 5 + 6 — server-side token exchange in callback.php
        // happens transparently; the final URL should be
        // /smart-questionnaire/index.php?lid=<launch id>.
        $this->panther->wait(20)->until(
            static fn (WebDriver $d): bool => str_contains($d->getCurrentURL(), '/smart-questionnaire/index.php?lid='),
        );
        $this->assertStringContainsString(
            '/smart-questionnaire/index.php?lid=',
            $this->panther->getCurrentURL(),
            'full flow should land on the SMART app index.php with a launch id',
        );

        return $pickedPatientName;
    }
}
