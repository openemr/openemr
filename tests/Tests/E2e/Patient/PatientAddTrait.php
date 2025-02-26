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

use Facebook\WebDriver\Exception\TimeoutException;
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
    private bool $closedClient = false;

    /**
     * @depends testLoginAuthorized
     */
    public function testPatientAdd(): void
    {
        $this->base();
        try {
            $this->patientAddIfNotExist(PatientTestData::FNAME, PatientTestData::LNAME, PatientTestData::DOB, PatientTestData::SEX);
        } catch (TimeoutException $e) {
            // Give it 3 tries, then re-throw the exception
            if ($this->patientAddAttemptCounter < 4) {
                echo "Patient add attempt " . $this->patientAddAttemptCounter . " failed. Retrying...\n";
                $this->patientAddAttemptCounter++;
                $this->client->quit();
                $this->testPatientAdd();
            } else {
                // Close client
                $this->client->quit();
                // re-throw the exception
                throw $e;
            }
        } catch (\Throwable $e) {
            // Close client
            $this->client->quit();
            // re-throw the exception
            throw $e;
        }
        // Close client (since this is a recurrent function, only close client if it's not already closed)
        if (!$this->closedClient) {
            $this->client->quit();
            $this->closedClient = true;
        }
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
        $this->client->wait(10)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::xpath(XpathsConstantsPatientAddTrait::NEW_PATIENT_FORM_FNAME_FIELD)
            )
        );
        $this->crawler = $this->client->refreshCrawler();
        $newPatient = $this->crawler->filterXPath(XpathsConstantsPatientAddTrait::CREATE_PATIENT_FORM_PATIENTADD_TRAIT)->form();
        $newPatient['form_fname'] = $firstname;
        $newPatient['form_lname'] = $lastname;
        $newPatient['form_DOB'] = $dob;
        $newPatient['form_sex'] = $sex;
        $this->client->waitFor(XpathsConstantsPatientAddTrait::CREATE_PATIENT_BUTTON_PATIENTADD_TRAIT);
        $this->crawler = $this->client->refreshCrawler();
        $this->crawler->filterXPath(XpathsConstantsPatientAddTrait::CREATE_PATIENT_BUTTON_PATIENTADD_TRAIT)->click();
        $this->client->switchTo()->defaultContent();
        $this->client->waitFor(XpathsConstantsPatientAddTrait::NEW_PATIENT_IFRAME_PATIENTADD_TRAIT);
        $this->switchToIFrame(XpathsConstantsPatientAddTrait::NEW_PATIENT_IFRAME_PATIENTADD_TRAIT);
        $this->client->waitFor(XpathsConstantsPatientAddTrait::CREATE_CONFIRM_PATIENT_BUTTON_PATIENTADD_TRAIT);
        $this->client->wait(10)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::xpath(XpathsConstantsPatientAddTrait::CREATE_CONFIRM_PATIENT_BUTTON_PATIENTADD_TRAIT)
            )
        );
        $this->crawler = $this->client->refreshCrawler();
        $this->crawler->filterXPath(XpathsConstantsPatientAddTrait::CREATE_CONFIRM_PATIENT_BUTTON_PATIENTADD_TRAIT)->click();

        // ensure the patient summary screen is shown
        $alert = $this->client->getWebDriver()->wait(10)->until(
            WebDriverExpectedCondition::alertIsPresent()
        );
        $alert->accept();
        $this->client->switchTo()->defaultContent();
        $this->client->waitFor(XpathsConstants::PATIENT_IFRAME);
        $this->switchToIFrame(XpathsConstants::PATIENT_IFRAME);
        // below line will timeout if did not go to the patient summary screen for the new patient
        $this->client->waitFor('//*[text()="Medical Record Dashboard - ' . $firstname . " " . $lastname . '"]');

        // assert the new patient is in the database
        $this->assertTrue($this->isPatientExist($firstname, $lastname, $dob, $sex), 'New patient is not in database, so FAILED');
    }
}
