<?php

/**
 * PatientAddTrait
 *
 * @package OpenEMR
 * @link    https://www.open-emr.org
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2024 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\Patient;

use OpenEMR\Tests\E2e\Base\BaseTrait;
use OpenEMR\Tests\E2e\Login\LoginTrait;
use OpenEMR\Tests\E2e\Xpaths\XpathsConstants;
use OpenEMR\Tests\E2e\Xpaths\XpathsConstantsPatientAddTrait;

trait PatientAddTrait
{
    use BaseTrait;
    use LoginTrait;

    /**
     * @depends testLoginAuthorized
     */
    public function testPatientAdd(): void
    {
        $this->base();
        try {
            $this->patientAddIfNotExist('firstname', 'lastname', '1994-03-01', 'Male');
        } catch (\Throwable $e) {
            // Close client
            $this->client->quit();
            // re-throw the exception
            throw $e;
        }
        // Close client
        $this->client->quit();
    }

    protected function PatientAddIfNotExist(string $firstname, string $lastname, string $dob, string $sex): void
    {
        // if patient already exists, then skip this
        if ($this->isPatientExist($firstname, $lastname, $dob, $sex)) {
            $this->markTestSkipped('New user test skipped because this patient already exists.');
        }

        // login
        $this->login('admin', 'pass');

        // go to add patient tab
        $this->goToMainMenuLink('Patient||New/Search');
        $this->assertActiveTab("Search or Add Patient");

        // add the patient
        $this->client->waitFor(XpathsConstants::PATIENT_IFRAME);
        $this->switchToIFrame(XpathsConstants::PATIENT_IFRAME);
        $this->client->waitFor(XpathsConstantsPatientAddTrait::CREATE_PATIENT_BUTTON_PATIENTADD_TRAIT);
        $this->crawler = $this->client->refreshCrawler();
        $newUser = $this->crawler->filterXPath(XpathsConstantsPatientAddTrait::CREATE_PATIENT_FORM_PATIENTADD_TRAIT)->form();
        $newUser['form_fname'] = $firstname;
        $newUser['form_lname'] = $lastname;
        $newUser['form_DOB'] = $dob;
        $newUser['form_sex'] = $sex;
        $this->client->waitFor(XpathsConstantsPatientAddTrait::CREATE_PATIENT_BUTTON_PATIENTADD_TRAIT);
        $this->crawler = $this->client->refreshCrawler();
        $this->crawler->filterXPath(XpathsConstantsPatientAddTrait::CREATE_PATIENT_BUTTON_PATIENTADD_TRAIT)->click();
        $this->client->switchTo()->defaultContent();
        $this->client->waitFor(XpathsConstantsPatientAddTrait::NEW_PATIENT_IFRAME_PATIENTADD_TRAIT);
        $this->switchToIFrame(XpathsConstantsPatientAddTrait::NEW_PATIENT_IFRAME_PATIENTADD_TRAIT);
        $this->client->waitFor(XpathsConstantsPatientAddTrait::CREATE_CONFIRM_PATIENT_BUTTON_PATIENTADD_TRAIT);
        $this->crawler = $this->client->refreshCrawler();
        $this->crawler->filterXPath(XpathsConstantsPatientAddTrait::CREATE_CONFIRM_PATIENT_BUTTON_PATIENTADD_TRAIT)->click();
        $this->client->switchTo()->defaultContent();

        sleep(10);

        // ensure the patient summary screen is shown
        $this->assertActiveTab('Dashboard');

        // ensure the patient was added
        $this->assertTrue($this->isPatientExist($firstname, $lastname, $dob, $sex), 'New patient is not in database, so FAILED');
    }

    protected function isPatientExist(string $firstname, string $lastname, string $dob, string $sex): bool
    {
        $patientDatabase = sqlQuery("SELECT `fname` FROM `patient_data` WHERE `fname` = ? AND `lname` = ? AND `DOB` = ? AND `sex` = ?", [$firstname, $lastname, $dob, $sex]);
        if (!empty($patientDatabase['fname']) && ($patientDatabase['fname'] == $firstname)) {
            return true;
        } else {
            return false;
        }

    }
}
