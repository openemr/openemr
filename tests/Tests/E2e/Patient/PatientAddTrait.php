<?php

/**
 * PatientAddTrait
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2024 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\Patient;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use OpenEMR\Tests\E2e\Base\BaseTrait;
use OpenEMR\Tests\E2e\Login\LoginTestData;
use OpenEMR\Tests\E2e\Login\LoginTrait;
use OpenEMR\Tests\E2e\Patient\PatientTestData;
use OpenEMR\Tests\E2e\Xpaths\XpathsConstants;
use OpenEMR\Tests\E2e\Xpaths\XpathsConstantsPatientAddTrait;
use PHPUnit\Framework\ExpectationFailedException;

trait PatientAddTrait
{
    use BaseTrait;
    use LoginTrait;

    private int $patientAddAttemptCounter = 1;
    private bool $passPatientAddIfNotExist = false;

    /**
     * @depends testLoginAuthorized
     */
    public function testPatientAdd(): void
    {
        $this->base();
        try {
            $this->patientAddIfNotExist(PatientTestData::FNAME, PatientTestData::LNAME, PatientTestData::DOB, PatientTestData::SEX);
        } catch (\Throwable $e) {
            // Close client
            $this->client->quit();
            // re-throw the exception
            throw $e;
        }
        // Close client
        $this->client->quit();
    }

    private function patientAddIfNotExist(string $firstname, string $lastname, string $dob, string $sex): void
    {
        // if patient already exists, then skip this
        if ($this->isPatientExist($firstname, $lastname, $dob, $sex)) {
            $this->markTestSkipped('New patient test skipped because this patient already exists.');
        }

        // login
        $this->login(LoginTestData::username, LoginTestData::password);

        // go to add patient tab
        $this->goToMainMenuLink('Patient||New/Search');
        $this->assertActiveTab("Search or Add Patient");

        // add the patient
        $this->client->waitFor(XpathsConstants::PATIENT_IFRAME);
        $this->switchToIFrame(XpathsConstants::PATIENT_IFRAME);
        $this->client->waitFor(XpathsConstantsPatientAddTrait::CREATE_PATIENT_BUTTON_PATIENTADD_TRAIT);
        $this->crawler = $this->client->refreshCrawler();
        $newPatient = $this->crawler->filterXPath(XpathsConstantsPatientAddTrait::CREATE_PATIENT_FORM_PATIENTADD_TRAIT)->form();
        $newPatient['form_fname'] = $firstname;
        $newPatient['form_lname'] = $lastname;
        $newPatient['form_DOB'] = $dob;
        $newPatient['form_sex'] = $sex;
        $this->client->waitFor(XpathsConstantsPatientAddTrait::CREATE_PATIENT_BUTTON_PATIENTADD_TRAIT);
        if (version_compare(phpversion(), '8.3.0', '>=')) {
            // Code to run on PHP 8.3 or greater
            $this->crawler = $this->client->refreshCrawler();
            $this->crawler->filterXPath(XpathsConstantsPatientAddTrait::CREATE_PATIENT_BUTTON_PATIENTADD_TRAIT)->click();
            $this->client->switchTo()->defaultContent();
            $this->client->waitFor(XpathsConstantsPatientAddTrait::NEW_PATIENT_IFRAME_PATIENTADD_TRAIT);
            $this->switchToIFrame(XpathsConstantsPatientAddTrait::NEW_PATIENT_IFRAME_PATIENTADD_TRAIT);
            $this->client->waitFor(XpathsConstantsPatientAddTrait::CREATE_CONFIRM_PATIENT_BUTTON_PATIENTADD_TRAIT);
            //   Note had to use the lower level webdriver directly to ensure button is elementToBeClickable for the click on this button to consistently work
            $this->client->getWebDriver()->wait()->until(
                WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::xpath(XpathsConstantsPatientAddTrait::CREATE_CONFIRM_PATIENT_BUTTON_PATIENTADD_TRAIT))
            );
            $this->crawler = $this->client->refreshCrawler();
            $this->crawler->filterXPath(XpathsConstantsPatientAddTrait::CREATE_CONFIRM_PATIENT_BUTTON_PATIENTADD_TRAIT)->click();
        } else {
            // Fallback for older versions prior to PHP 8.3
            //   For some reason, the click is not working like it should in PHP versions less than 8.3, so going to bypass the confirmation screen
            $this->crawler = $this->client->submit($newPatient);
        }
        // assert the new patient is in the database
        $this->assertPatientInDatabase($firstname, $lastname, $dob, $sex);
        // since this function is run recursively in above line, ensure only do the below block once
        if (!$this->passPatientAddIfNotExist) {
            // Note using lower level webdriver directly since seems like a more simple and more consistent way to check for the alert
            $alert = $this->client->getWebDriver()->wait(10)->until(
                WebDriverExpectedCondition::alertIsPresent()
            );
            $alert->accept();
            // ensure the patient summary screen is shown
            $this->client->switchTo()->defaultContent();
            $this->client->waitFor(XpathsConstants::PATIENT_IFRAME);
            $this->switchToIFrame(XpathsConstants::PATIENT_IFRAME);
            // below line will timeout if did not go to the patient summary screen for the new patient
            $this->client->waitFor('//*[text()="Medical Record Dashboard - ' . $firstname . " " . $lastname . '"]');
            $this->passPatientAddIfNotExist = true;
        }
    }

    private function assertPatientInDatabase(string $firstname, string $lastname, string $dob, string $sex): void
    {
        // assert the new patient is in the database (if this fails, then will try patientAddIfNotExist() up to
        // 3 times total before failing)
        try {
            $this->innerAssertPatientInDatabase($firstname, $lastname, $dob, $sex);
        } catch (ExpectationFailedException $e) {
            if ($this->patientAddAttemptCounter > 2) {
                // re-throw since have failed 3 tries
                throw $e;
            } else {
                // try again since not yet 3 tries
                $this->patientAddAttemptCounter++;
                echo "\n" . "TRY " . ($this->patientAddAttemptCounter) . " of 3 to add new patient to database" . "\n";
                $this->logOut();
                $this->patientAddIfNotExist($firstname, $lastname, $dob, $sex);
            }
        }
    }

    private function innerAssertPatientInDatabase(string $firstname, string $lastname, string $dob, string $sex): void
    {
        // assert the new patient is in the database (check 3 times with 5 second delay prior each check to
        // ensure allow enough time)
        $patientExistDatabase = false;
        $counter = 0;
        while (!$patientExistDatabase && $counter < 3) {
            if ($counter > 0) {
                echo "\n" . "TRY " . ($counter + 1) . " of 3 to see if new patient is in database" . "\n";
            }
            sleep(5);
            if ($this->isPatientExist($firstname, $lastname, $dob, $sex)) {
                $patientExistDatabase = true;
            }
            $counter++;
        }
        $this->assertTrue($patientExistDatabase, 'New patient is not in database, so FAILED');
    }
}
