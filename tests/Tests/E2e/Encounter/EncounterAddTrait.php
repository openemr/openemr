<?php

/**
 * EncounterAddTrait
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2024 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\Encounter;

use OpenEMR\Tests\E2e\Base\BaseTrait;
use OpenEMR\Tests\E2e\Encounter\EncounterTestData;
use OpenEMR\Tests\E2e\Login\LoginTestData;
use OpenEMR\Tests\E2e\Login\LoginTrait;
use OpenEMR\Tests\E2e\Patient\PatientOpenTrait;
use OpenEMR\Tests\E2e\Patient\PatientTestData;
use OpenEMR\Tests\E2e\Xpaths\XpathsConstants;
use OpenEMR\Tests\E2e\Xpaths\XpathsConstantsEncounterAddTrait;

trait EncounterAddTrait
{
    use BaseTrait;
    use LoginTrait;
    use PatientOpenTrait;

    /**
     * @depends testLoginAuthorized
     * @depends testPatientOpen
     */
    public function testEncounterAdd(): void
    {
        $this->base();
        try {
            $this->encounterAddIfNotExist(PatientTestData::FNAME, PatientTestData::LNAME, PatientTestData::DOB, PatientTestData::SEX);
        } catch (\Throwable $e) {
            // Close client
            $this->client->quit();
            // re-throw the exception
            throw $e;
        }
        // Close client
        $this->client->quit();
    }

    private function encounterAddIfNotExist(string $firstname, string $lastname, string $dob, string $sex): void
    {
        // if patient already exists, then skip this
        if ($this->isEncounterExist($firstname, $lastname, $dob, $sex)) {
            $this->markTestSkipped('New encounter test skipped because this encounter already exists.');
        }

        // login and open patient
        $this->login(LoginTestData::username, LoginTestData::password);
        $this->patientOpenIfExist(PatientTestData::FNAME, PatientTestData::LNAME, PatientTestData::DOB, PatientTestData::SEX);

        // add new encounter
        $this->client->switchTo()->defaultContent();
        $this->client->waitFor(XpathsConstantsEncounterAddTrait::CREATE_ENCOUNTER_BUTTON_ENCOUNTERADD_TRAIT);
        $this->crawler = $this->client->refreshCrawler();
        $this->crawler->filterXPath(XpathsConstantsEncounterAddTrait::CREATE_ENCOUNTER_BUTTON_ENCOUNTERADD_TRAIT)->click();
        $this->client->waitFor(XpathsConstants::ENCOUNTER_IFRAME);
        $this->switchToIFrame(XpathsConstants::ENCOUNTER_IFRAME);
        $this->client->waitFor(XpathsConstantsEncounterAddTrait::SAVE_ENCOUNTER_BUTTON_ENCOUNTERADD_TRAIT);
        $this->crawler = $this->client->refreshCrawler();
        $newEncounter = $this->crawler->filterXPath(XpathsConstantsEncounterAddTrait::SAVE_ENCOUNTER_BUTTON_ENCOUNTERADD_TRAIT)->form();
        $newEncounter['pc_catid'] = EncounterTestData::CATID;
        $newEncounter['reason'] = EncounterTestData::REASON;
        $this->client->waitFor(XpathsConstantsEncounterAddTrait::SAVE_ENCOUNTER_BUTTON_ENCOUNTERADD_TRAIT);
        $this->crawler = $this->client->refreshCrawler();
        $this->crawler->filterXPath(XpathsConstantsEncounterAddTrait::SAVE_ENCOUNTER_BUTTON_ENCOUNTERADD_TRAIT)->click();

        // ensure the encounter screen is shown
        $this->client->switchTo()->defaultContent();
        $this->client->waitFor(XpathsConstants::ENCOUNTER_IFRAME);
        $this->switchToIFrame(XpathsConstants::ENCOUNTER_IFRAME);
        $this->client->waitFor(XpathsConstants::ENCOUNTER_FORMS_IFRAME);
        $this->switchToIFrame(XpathsConstants::ENCOUNTER_FORMS_IFRAME);
        // below line will timeout if did not go to the encounter screen for the new encounter
        $this->client->waitFor('//span[@id="navbarEncounterTitle" and contains(text(), "Encounter for ' . $firstname . " " . $lastname . '")]');

        // ensure the encounter was added
        $this->assertTrue($this->isEncounterExist($firstname, $lastname, $dob, $sex), 'New encounter is not in database, so FAILED');
    }
}
