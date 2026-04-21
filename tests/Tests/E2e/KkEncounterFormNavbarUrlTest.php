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

use Facebook\WebDriver\WebDriverBy;
use OpenEMR\Tests\E2e\Base\BaseTrait;
use OpenEMR\Tests\E2e\Login\LoginTrait;
use OpenEMR\Tests\E2e\Patient\PatientTestData;
use OpenEMR\Tests\E2e\Xpaths\XpathsConstants;
use OpenEMR\Tests\E2e\Xpaths\XpathsConstantsEncounterOpenTrait;
use OpenEMR\Tests\E2e\Xpaths\XpathsConstantsPatientOpenTrait;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Panther\PantherTestCase;

class KkEncounterFormNavbarUrlTest extends PantherTestCase
{
    use BaseTrait;
    use LoginTrait;

    /**
     * Verify that form navbar dropdown links include pid and encounter URL params.
     *
     * Regression test for #10844: without these params, forms relied on the
     * session encounter which could be 0, causing new.php to fall back to
     * date("Ymd") as a bogus encounter ID and triggering a 404 on redirect.
     */
    #[Depends('testLoginAuthorized')]
    #[Test]
    public function testFormNavbarUrlsContainEncounterAndPid(): void
    {
        try {
            $this->openPatient();
            $this->openEncounter();

            // Navigate into the encounter forms iframe where the navbar lives
            $this->crawler->switchTo()->defaultContent();
            $this->crawler->waitFor(XpathsConstants::ENCOUNTER_IFRAME);
            $this->switchToIFrame(XpathsConstants::ENCOUNTER_IFRAME);
            $this->crawler->waitFor(XpathsConstants::ENCOUNTER_FORMS_IFRAME);
            $this->switchToIFrame(XpathsConstants::ENCOUNTER_FORMS_IFRAME);

            // Wait for the navbar to render
            $this->crawler->waitFor('//span[@id="navbarEncounterTitle"]');

            // Use JavaScript to extract all onclick attributes from navbar dropdown items.
            // Each form link has onclick="openNewForm('...load_form.php?formname=X&pid=Y&encounter=Z', ...)"
            /** @var list<string> $onclickValues */
            $onclickValues = (array) $this->crawler->executeScript(<<<'JS'
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
            $this->crawler->quit();
        }
    }

    private function openPatient(): void
    {
        $this->crawler->waitFor(XpathsConstantsPatientOpenTrait::ANYSEARCHBOX_FORM_PATIENTOPEN_TRAIT);
        $crawler = $this->crawler->refreshCrawler();
        $searchForm = $crawler->filterXPath(XpathsConstantsPatientOpenTrait::ANYSEARCHBOX_FORM_PATIENTOPEN_TRAIT)->form();
        $searchForm['anySearchBox'] = PatientTestData::LNAME;
        $this->crawler->waitFor(XpathsConstantsPatientOpenTrait::ANYSEARCHBOX_CLICK_PATIENTOPEN_TRAIT);
        $crawler = $this->crawler->refreshCrawler();
        $crawler->filterXPath(XpathsConstantsPatientOpenTrait::ANYSEARCHBOX_CLICK_PATIENTOPEN_TRAIT)->click();

        $patientLink = '//a[text()="' . PatientTestData::LNAME . ', ' . PatientTestData::FNAME . '"]';
        $this->crawler->waitFor(XpathsConstants::PATIENT_FINDER_IFRAME);
        $this->switchToIFrame(XpathsConstants::PATIENT_FINDER_IFRAME);
        $this->crawler->waitFor($patientLink);
        $crawler = $this->crawler->refreshCrawler();
        $crawler->filterXPath($patientLink)->click();

        $this->crawler->switchTo()->defaultContent();
        $this->crawler->waitFor(XpathsConstants::PATIENT_IFRAME);
        $this->switchToIFrame(XpathsConstants::PATIENT_IFRAME);
        $dashboardXpath = '//*[text()="Medical Record Dashboard - '
            . PatientTestData::FNAME . ' ' . PatientTestData::LNAME . '"]';
        $this->crawler->waitFor($dashboardXpath);
    }

    private function openEncounter(): void
    {
        $this->crawler->switchTo()->defaultContent();
        $this->crawler->waitFor(XpathsConstantsEncounterOpenTrait::SELECT_ENCOUNTER_BUTTON_ENCOUNTEROPEN_TRAIT);
        $crawler = $this->crawler->refreshCrawler();
        $crawler->filterXPath(XpathsConstantsEncounterOpenTrait::SELECT_ENCOUNTER_BUTTON_ENCOUNTEROPEN_TRAIT)->click();
        $this->crawler->waitFor(XpathsConstantsEncounterOpenTrait::SELECT_A_ENCOUNTER_ENCOUNTEROPEN_TRAIT);
        $crawler = $this->crawler->refreshCrawler();
        $crawler->filterXPath(XpathsConstantsEncounterOpenTrait::SELECT_A_ENCOUNTER_ENCOUNTEROPEN_TRAIT)->click();

        $this->crawler->waitFor(XpathsConstants::ENCOUNTER_IFRAME);
        $this->switchToIFrame(XpathsConstants::ENCOUNTER_IFRAME);
        $this->crawler->waitFor(XpathsConstants::ENCOUNTER_FORMS_IFRAME);
        $this->switchToIFrame(XpathsConstants::ENCOUNTER_FORMS_IFRAME);
        $encounterTitle = '//span[@id="navbarEncounterTitle" and contains(text(), "Encounter for '
            . PatientTestData::FNAME . ' ' . PatientTestData::LNAME . '")]';
        $this->crawler->waitFor($encounterTitle);
    }

    private function switchToIFrame(string $xpath): void
    {
        $iframe = $this->crawler->findElement(WebDriverBy::xpath($xpath));
        $this->crawler->switchTo()->frame($iframe);
    }
}
