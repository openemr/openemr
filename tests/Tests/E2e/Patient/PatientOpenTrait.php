<?php

/**
 * PatientOpenTrait
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2024 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\Patient;

use OpenEMR\Tests\E2e\Base\BaseTrait;
use OpenEMR\Tests\E2e\Login\LoginTestData;
use OpenEMR\Tests\E2e\Login\LoginTrait;
use OpenEMR\Tests\E2e\Patient\PatientAddTrait;
use OpenEMR\Tests\E2e\Patient\PatientTestData;
use OpenEMR\Tests\E2e\Xpaths\XpathsConstants;
use OpenEMR\Tests\E2e\Xpaths\XpathsConstantsPatientOpenTrait;

trait PatientOpenTrait
{
    use BaseTrait;
    use LoginTrait;
    use PatientAddTrait;

    /**
     * @depends testLoginAuthorized
     */
    public function testPatientOpen(): void
    {
        $this->base();
        try {
            $this->patientOpenIfExist(PatientTestData::FNAME, PatientTestData::LNAME, PatientTestData::DOB, PatientTestData::SEX);
        } catch (\Throwable $e) {
            // Close client
            $this->client->quit();
            // re-throw the exception
            throw $e;
        }
        // Close client
        $this->client->quit();
    }

    private function patientOpenIfExist(string $firstname, string $lastname, string $dob, string $sex, bool $login = true): void
    {
        // if patient does not already exists, then fail
        if (!$this->isPatientExist($firstname, $lastname, $dob, $sex)) {
            $this->fail('Patient does not exist so FAIL');
        }

        if ($login) {
            // login
            $this->login(LoginTestData::username, LoginTestData::password);
        }

        // search for last name via anySearchBox
        $this->client->waitFor(XpathsConstantsPatientOpenTrait::ANYSEARCHBOX_FORM_PATIENTOPEN_TRAIT);
        $this->crawler = $this->client->refreshCrawler();
        $searchForm = $this->crawler->filterXPath(XpathsConstantsPatientOpenTrait::ANYSEARCHBOX_FORM_PATIENTOPEN_TRAIT)->form();
        $searchForm['anySearchBox'] = $lastname;
        $this->client->waitFor(XpathsConstantsPatientOpenTrait::ANYSEARCHBOX_CLICK_PATIENTOPEN_TRAIT);
        $this->crawler = $this->client->refreshCrawler();
        $this->crawler->filterXPath(XpathsConstantsPatientOpenTrait::ANYSEARCHBOX_CLICK_PATIENTOPEN_TRAIT)->click();

        // click on the name in the patient list
        $this->client->waitFor(XpathsConstants::PATIENT_FINDER_IFRAME);
        $this->switchToIFrame(XpathsConstants::PATIENT_FINDER_IFRAME);
        $this->client->waitFor('//a[text()="' . $lastname . ", " . $firstname . '"]');
        $this->crawler = $this->client->refreshCrawler();
        $this->crawler->filterXPath('//a[text()="' . $lastname . ", " . $firstname . '"]')->click();

        // ensure the patient summary screen is shown
        $this->client->switchTo()->defaultContent();
        $this->client->waitFor(XpathsConstants::PATIENT_IFRAME);
        $this->switchToIFrame(XpathsConstants::PATIENT_IFRAME);
        // below line will timeout if did not go to the patient summary screen for the opened patient
        $this->client->waitFor('//*[text()="Medical Record Dashboard - ' . $firstname . " " . $lastname . '"]');
    }
}
