<?php

/**
 * KkEncounterFormNavbarUrlTest — E2E test for #10844 fix.
 *
 * Verifies that the encounter form navbar renders form links with pid and
 * encounter URL parameters. Before the fix, these URLs omitted the params,
 * causing load_form.php to rely on the session, which could be stale.
 *
 * This test is intentionally self-contained (no shared traits) so that it
 * does not inherit PHPStan baseline debt from the trait chain.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenEMR Contributors
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenEMR Contributors
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e;

use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\JavaScriptExecutor;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use OpenEMR\Tests\E2e\Login\LoginTestData;
use OpenEMR\Tests\E2e\Patient\PatientTestData;
use OpenEMR\Tests\E2e\Xpaths\XpathsConstants;
use OpenEMR\Tests\E2e\Xpaths\XpathsConstantsEncounterOpenTrait;
use OpenEMR\Tests\E2e\Xpaths\XpathsConstantsPatientOpenTrait;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

class KkEncounterFormNavbarUrlTest extends PantherTestCase
{
    private Client $client;

    /**
     * Verify that form navbar dropdown links include pid and encounter URL params.
     *
     * Regression test for #10844: without these params, forms relied on the
     * session encounter which could be 0, causing new.php to fall back to
     * date("Ymd") as a bogus encounter ID and triggering a 404 on redirect.
     */
    #[Test]
    public function testFormNavbarUrlsContainEncounterAndPid(): void
    {
        $this->initClient();
        try {
            $this->doLogin();
            $this->openPatient();
            $this->openEncounter();

            // Navigate into the encounter forms iframe where the navbar lives
            $this->client->switchTo()->defaultContent();
            $this->client->waitFor(XpathsConstants::ENCOUNTER_IFRAME);
            $this->switchToIFrame(XpathsConstants::ENCOUNTER_IFRAME);
            $this->client->waitFor(XpathsConstants::ENCOUNTER_FORMS_IFRAME);
            $this->switchToIFrame(XpathsConstants::ENCOUNTER_FORMS_IFRAME);

            // Wait for the navbar to render
            $this->client->waitFor('//span[@id="navbarEncounterTitle"]');

            // Use JavaScript to extract all onclick attributes from navbar dropdown items.
            // Each form link has onclick="openNewForm('...load_form.php?formname=X&pid=Y&encounter=Z', ...)"
            /** @var list<string> $onclickValues */
            $onclickValues = (array) $this->client->executeScript(<<<'JS'
                var items = document.querySelectorAll('.dropdown-menu .dropdown-item');
                var results = [];
                items.forEach(function(item) {
                    var onclick = item.getAttribute('onclick');
                    if (onclick && onclick.indexOf('load_form.php') !== -1) {
                        results.push(onclick);
                    }
                });
                return results;
            JS);

            // There should be at least one form link in the navbar
            $this->assertNotEmpty(
                $onclickValues,
                'Expected at least one form link with load_form.php in the navbar'
            );

            // Every load_form.php URL must include pid= and encounter= params
            foreach ($onclickValues as $onclick) {
                $onclickStr = (string) $onclick;
                $this->assertStringContainsString(
                    'pid=',
                    $onclickStr,
                    "Form link missing pid param: {$onclickStr}"
                );
                $this->assertStringContainsString(
                    'encounter=',
                    $onclickStr,
                    "Form link missing encounter param: {$onclickStr}"
                );
                // The encounter value must not be 0 (the stale-session scenario)
                $this->assertDoesNotMatchRegularExpression(
                    '/encounter=0(?:[^0-9]|$)/',
                    $onclickStr,
                    "Form link has encounter=0 (stale session bug): {$onclickStr}"
                );
            }
        } finally {
            $this->client->quit();
        }
    }

    private function initClient(): void
    {
        $useGrid = getenv("SELENIUM_USE_GRID", true);
        if ($useGrid === false) {
            $useGrid = "false";
        }

        if ($useGrid === "true") {
            $seleniumHost = getenv("SELENIUM_HOST", true) ?: "selenium";
            $e2eBaseUrl = getenv("SELENIUM_BASE_URL", true) ?: "http://openemr";
            $implicitWait = (int) (getenv("SELENIUM_IMPLICIT_WAIT") ?: 0);
            $pageLoadTimeout = (int) (getenv("SELENIUM_PAGE_LOAD_TIMEOUT") ?: 60);

            $capabilities = DesiredCapabilities::chrome();
            $capabilities->setCapability('goog:chromeOptions', [
                'args' => [
                    '--window-size=1920,1080',
                    '--no-sandbox',
                    '--disable-dev-shm-usage',
                    '--disable-gpu',
                ],
            ]);
            $capabilities->setCapability('unhandledPromptBehavior', 'accept');
            $capabilities->setCapability('pageLoadStrategy', 'normal');

            $seleniumUrl = "http://{$seleniumHost}:4444/wd/hub";
            $this->client = Client::createSeleniumClient($seleniumUrl, $capabilities, $e2eBaseUrl);
            $this->client->manage()->timeouts()->implicitlyWait($implicitWait);
            $this->client->manage()->timeouts()->pageLoadTimeout($pageLoadTimeout);
        } else {
            $this->client = static::createPantherClient(['external_base_uri' => "http://localhost"]);
            $this->client->manage()->window()->maximize();
        }
    }

    /**
     * Log in and wait for the application shell to initialize.
     *
     * Mirrors LoginTrait::login(): when the shell's scripts fail to load,
     * waiting longer does not help, so detect that quickly and retry once
     * with a fresh browser session before giving up.
     */
    private function doLogin(): void
    {
        $this->submitLoginForm();
        if ($this->mainMenuRendered(5)) {
            return;
        }

        $this->client->quit();
        $this->initClient();
        $this->submitLoginForm();
        if (!$this->mainMenuRendered(30)) {
            $this->fail('Main menu "#mainMenu" did not render within 30s after login, even with a fresh session: the JavaScript application failed to initialize');
        }
    }

    /**
     * Submit the login form and wait for the post-login redirect to land.
     *
     * Mirrors LoginTrait::performLogin().
     */
    private function submitLoginForm(): void
    {
        $this->client->request('GET', '/interface/login/login.php?site=default&testing_mode=1');

        // filter() snapshots the DOM at one instant, and under CI load that
        // instant can precede the login page finishing its render. Wait for
        // the form explicitly, with a message, because a bare waitFor() would
        // time out with an empty TimeoutException.
        $this->client->wait(10)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#login_form')),
            "Login form '#login_form' did not appear within 10s: page still rendering under load, or the webserver served an error page instead of login.php"
        );
        $form = $this->client->refreshCrawler()->filter('#login_form')->form();
        $form['authUser'] = LoginTestData::username;
        $form['clearPass'] = LoginTestData::password;
        $this->client->submit($form);

        // The post-login redirect is asynchronous: submit() returns once the
        // POST responds, but the browser still has to follow the redirect and
        // load the main shell before document.title changes from
        // 'OpenEMR Login' to 'OpenEMR'. Under CI load that lag is long enough
        // that reading getTitle() immediately races the redirect. Wait for the
        // transition first; on timeout fall through so the assertion below
        // reports the actual title.
        try {
            $this->client->wait(10)->until(
                static fn(WebDriver $driver): bool => $driver->getTitle() === 'OpenEMR'
            );
        } catch (TimeoutException) {
            // Fall through to the assertion for a diagnostic message.
        }
        $this->assertSame('OpenEMR', $this->client->getTitle(), 'Login FAILED');
    }

    /**
     * Wait for the Knockout-rendered main menu so the patient search that
     * follows never runs against a shell whose JS has not initialized.
     *
     * Mirrors BaseTrait::waitForAppReady().
     */
    private function mainMenuRendered(int $timeout): bool
    {
        try {
            $this->client->wait($timeout)->until(
                static fn(JavaScriptExecutor $driver): bool => $driver->executeScript(
                    'return document.getElementById("mainMenu")?.children.length > 0'
                ) === true
            );
        } catch (TimeoutException) {
            return false;
        }

        return true;
    }

    private function openPatient(): void
    {
        $this->client->waitFor(XpathsConstantsPatientOpenTrait::ANYSEARCHBOX_FORM_PATIENTOPEN_TRAIT);
        $crawler = $this->client->refreshCrawler();
        $searchForm = $crawler->filterXPath(XpathsConstantsPatientOpenTrait::ANYSEARCHBOX_FORM_PATIENTOPEN_TRAIT)->form();
        $searchForm['anySearchBox'] = PatientTestData::LNAME;
        $this->client->waitFor(XpathsConstantsPatientOpenTrait::ANYSEARCHBOX_CLICK_PATIENTOPEN_TRAIT);
        $crawler = $this->client->refreshCrawler();
        $crawler->filterXPath(XpathsConstantsPatientOpenTrait::ANYSEARCHBOX_CLICK_PATIENTOPEN_TRAIT)->click();

        $patientLink = '//a[text()="' . PatientTestData::LNAME . ', ' . PatientTestData::FNAME . '"]';
        $this->client->waitFor(XpathsConstants::PATIENT_FINDER_IFRAME);
        $this->switchToIFrame(XpathsConstants::PATIENT_FINDER_IFRAME);
        $this->client->waitFor($patientLink);
        $crawler = $this->client->refreshCrawler();
        $crawler->filterXPath($patientLink)->click();

        $this->client->switchTo()->defaultContent();
        $this->client->waitFor(XpathsConstants::PATIENT_IFRAME);
        $this->switchToIFrame(XpathsConstants::PATIENT_IFRAME);
        $dashboardXpath = '//*[text()="Medical Record Dashboard - '
            . PatientTestData::FNAME . ' ' . PatientTestData::LNAME . '"]';
        $this->client->waitFor($dashboardXpath);
    }

    private function openEncounter(): void
    {
        $this->client->switchTo()->defaultContent();
        $this->client->waitFor(XpathsConstantsEncounterOpenTrait::SELECT_ENCOUNTER_BUTTON_ENCOUNTEROPEN_TRAIT);
        $crawler = $this->client->refreshCrawler();
        $crawler->filterXPath(XpathsConstantsEncounterOpenTrait::SELECT_ENCOUNTER_BUTTON_ENCOUNTEROPEN_TRAIT)->click();
        $this->client->waitFor(XpathsConstantsEncounterOpenTrait::SELECT_A_ENCOUNTER_ENCOUNTEROPEN_TRAIT);
        $crawler = $this->client->refreshCrawler();
        $crawler->filterXPath(XpathsConstantsEncounterOpenTrait::SELECT_A_ENCOUNTER_ENCOUNTEROPEN_TRAIT)->click();

        $this->client->waitFor(XpathsConstants::ENCOUNTER_IFRAME);
        $this->switchToIFrame(XpathsConstants::ENCOUNTER_IFRAME);
        $this->client->waitFor(XpathsConstants::ENCOUNTER_FORMS_IFRAME);
        $this->switchToIFrame(XpathsConstants::ENCOUNTER_FORMS_IFRAME);
        $encounterTitle = '//span[@id="navbarEncounterTitle" and contains(text(), "Encounter for '
            . PatientTestData::FNAME . ' ' . PatientTestData::LNAME . '")]';
        $this->client->waitFor($encounterTitle);
    }

    private function switchToIFrame(string $xpath): void
    {
        $iframe = $this->client->findElement(WebDriverBy::xpath($xpath));
        $this->client->switchTo()->frame($iframe);
    }
}
