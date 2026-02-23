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
 * @copyright Copyright (c) 2026 OpenEMR Contributors
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
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
                    '/encounter=0[^0-9]/',
                    $onclickStr,
                    "Form link has encounter=0 (stale session bug): {$onclickStr}"
                );
            }
        } catch (\Throwable $e) {
            $this->client->quit();
            throw $e;
        }
        $this->client->quit();
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

    private function doLogin(): void
    {
        $crawler = $this->client->request('GET', '/interface/login/login.php?site=default&testing_mode=1');
        $form = $crawler->filter('#login_form')->form();
        $form['authUser'] = LoginTestData::username;
        $form['clearPass'] = LoginTestData::password;
        $this->client->submit($form);
        $title = $this->client->getTitle();
        $this->assertSame('OpenEMR', $title, 'Login FAILED');
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
