<?php

/**
 * EncounterOpenTrait
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
use OpenEMR\Tests\E2e\Encounter\EncounterAddTrait;
use OpenEMR\Tests\E2e\Login\LoginTestData;
use OpenEMR\Tests\E2e\Login\LoginTrait;
use OpenEMR\Tests\E2e\Patient\PatientOpenTrait;
use OpenEMR\Tests\E2e\Patient\PatientTestData;
use OpenEMR\Tests\E2e\Xpaths\XpathsConstants;
use OpenEMR\Tests\E2e\Xpaths\XpathsConstantsEncounterOpenTrait;

trait EncounterOpenTrait
{
    use BaseTrait;
    use LoginTrait;
    use PatientOpenTrait;
    use EncounterAddTrait;

    /**
     * @depends testLoginAuthorized
     * @depends testPatientOpen
     */
    public function testEncounterOpen(): void
    {
        $this->base();
        try {
            $this->encounterOpenIfExist(PatientTestData::FNAME, PatientTestData::LNAME, PatientTestData::DOB, PatientTestData::SEX);
        } catch (\Throwable $e) {
            // Close client
            $this->client->quit();
            // re-throw the exception
            throw $e;
        }
        // Close client
        $this->client->quit();
    }

    private function encounterOpenIfExist(string $firstname, string $lastname, string $dob, string $sex, bool $login = true, bool $openPatient = true): void
    {
        // if patient does not already exists, then fail
        if (!$this->isEncounterExist($firstname, $lastname, $dob, $sex)) {
            $this->fail('Encounter does not exist so FAIL');
        }

        if ($login) {
            $this->login(LoginTestData::username, LoginTestData::password);
        }
        if ($openPatient) {
            $this->patientOpenIfExist(PatientTestData::FNAME, PatientTestData::LNAME, PatientTestData::DOB, PatientTestData::SEX, false);
        }

        // open encounter
        $this->client->switchTo()->defaultContent();
        $this->client->waitFor(XpathsConstantsEncounterOpenTrait::SELECT_ENCOUNTER_BUTTON_ENCOUNTEROPEN_TRAIT);
        $this->crawler = $this->client->refreshCrawler();
        $this->crawler->filterXPath(XpathsConstantsEncounterOpenTrait::SELECT_ENCOUNTER_BUTTON_ENCOUNTEROPEN_TRAIT)->click();
        $this->client->waitFor(XpathsConstantsEncounterOpenTrait::SELECT_A_ENCOUNTER_ENCOUNTEROPEN_TRAIT);
        $this->crawler = $this->client->refreshCrawler();
        $this->crawler->filterXPath(XpathsConstantsEncounterOpenTrait::SELECT_A_ENCOUNTER_ENCOUNTEROPEN_TRAIT)->click();

        // ensure the encounter screen is shown
        $this->client->waitFor(XpathsConstants::ENCOUNTER_IFRAME);
        $this->switchToIFrame(XpathsConstants::ENCOUNTER_IFRAME);
        $this->client->waitFor(XpathsConstants::ENCOUNTER_FORMS_IFRAME);
        $this->switchToIFrame(XpathsConstants::ENCOUNTER_FORMS_IFRAME);
        // below line will timeout if did not go to the encounter screen for the new encounter
        $this->client->waitFor('//span[@id="navbarEncounterTitle" and contains(text(), "Encounter for ' . $firstname . " " . $lastname . '")]');
    }
}
