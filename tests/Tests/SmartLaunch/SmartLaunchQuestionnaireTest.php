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

        // Stage 3 — patient-select screen (shown because the v1 scope
        // profile requests `launch/patient`). The buttons set a hidden
        // patient_id input via JS and submit; a plain click is enough.
        $this->panther->waitFor('button.patient-btn', 15);
        $pickBtn = $this->panther->findElement(WebDriverBy::cssSelector('button.patient-btn'));
        $pickedPatientUuid = $pickBtn->getAttribute('data-patient-id');
        $this->assertNotEmpty(
            $pickedPatientUuid,
            'patient-select button must expose data-patient-id so the pick submission knows which patient',
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
        // Wait until we leave /oauth2/ paths — either landed on
        // /smart-questionnaire/callback.php mid-exchange or already
        // on index.php after the server-side code→token exchange.
        $this->panther->wait(20)->until(
            static fn (WebDriver $d): bool => !str_contains($d->getCurrentURL(), '/oauth2/'),
        );

        // Stage 5 + 6 — the server-side token exchange in callback.php
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

        // Pull the patient banner text to prove the app made its initial
        // bearer-token Patient read against the FHIR API. The banner
        // includes DOB in YYYY-MM-DD form — matching that pattern is
        // enough to confirm "we got a patient record back from FHIR,"
        // without coupling to any specific demo-data patient name.
        $this->panther->waitFor('body', 10);
        $bodyText = $this->panther->findElement(WebDriverBy::tagName('body'))->getText();
        $this->assertMatchesRegularExpression(
            '/\bDOB:\s*\d{4}-\d{2}-\d{2}\b/',
            $bodyText,
            'app index.php should render a patient banner with DOB — proof that bearer-token Patient read succeeded',
        );
    }
}
