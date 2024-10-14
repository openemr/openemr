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
        // was having issues with this click, so needed to use the lower level webdriver directly to:
        //  click
        //  wait for the patient add iframe to go away
        //  wait for the patient summary alert to show up (and then ok it)
        $button = $this->client->getWebDriver()->wait()->until(
            WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::xpath(XpathsConstantsPatientAddTrait::CREATE_CONFIRM_PATIENT_BUTTON_PATIENTADD_TRAIT))
        );
        //$button->click();
        $this->crawler = $this->client->refreshCrawler();
        $this->crawler->filterXPath(XpathsConstantsPatientAddTrait::CREATE_CONFIRM_PATIENT_BUTTON_PATIENTADD_TRAIT)->click();
        $this->client->switchTo()->defaultContent();
        $this->client->getWebDriver()->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::xpath(XpathsConstantsPatientAddTrait::NEW_PATIENT_IFRAME_PATIENTADD_TRAIT))
        );
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

        // ensure the patient was added
        $this->assertTrue($this->isPatientExist($firstname, $lastname, $dob, $sex), 'New patient is not in database, so FAILED');
    }
}
