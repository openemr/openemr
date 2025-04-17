<?php

/**
 * IiPatientContextMainMenuLinksTest class
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2024 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e;

use OpenEMR\Tests\E2e\Base\BaseTrait;
use OpenEMR\Tests\E2e\Login\LoginTestData;
use OpenEMR\Tests\E2e\Login\LoginTrait;
use OpenEMR\Tests\E2e\Patient\PatientTestData;
use OpenEMR\Tests\E2e\Patient\PatientOpenTrait;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Panther\Client;

class IiPatientContextMainMenuLinksTest extends PantherTestCase
{
    use BaseTrait;
    use LoginTrait;
    use PatientOpenTrait;

    private $client;
    private $crawler;

    /**
     * @dataProvider menuLinkProvider
     * @depends testLoginAuthorized
     * @depends testPatientOpen
     */
    public function testPatientContextMainMenuLink(string $menuLink, string $expectedTabPopupTitle, bool $popup, ?string $loading, ?bool $clearAlert = false): void
    {
        if ($expectedTabPopupTitle == "Care Coordination" && !empty(getenv('UNABLE_SUPPORT_OPENEMR_NODEJS', true) ?? '')) {
            // Care Coordination page check will be skipped since this flag is set (which means the environment does not have
            //  a high enough version of nodejs)
            $this->markTestSkipped('Test skipped because this environment does not support high enough nodejs version.');
        }

        if (empty($loading)) {
            $loading = "Loading";
        }

        if (is_null($clearAlert)) {
            $clearAlert = false;
        }

        $counter = 0;
        $threwSomething = true;
        // below will basically allow 3 timeouts
        while ($threwSomething) {
            $threwSomething = false;
            $counter++;
            if ($counter > 1) {
                echo "\n" . "RE-attempt (" . $menuLink . ") number " . $counter . " of 3" . "\n";
            }
            $this->base();
            try {
                $this->login(LoginTestData::username, LoginTestData::password);
                $this->patientOpenIfExist(PatientTestData::FNAME, PatientTestData::LNAME, PatientTestData::DOB, PatientTestData::SEX, false);
                $this->goToMainMenuLink($menuLink);
                if ($popup) {
                    $this->assertActivePopup($expectedTabPopupTitle);
                } else {
                    $this->assertActiveTab($expectedTabPopupTitle, $loading, false, $clearAlert);
                }
            } catch (\Throwable $e) {
                // Close client
                $this->client->quit();
                if ($counter > 2) {
                    // re-throw since have failed 3 tries
                    throw $e;
                } else {
                    // try again since not yet 3 tries
                    $threwSomething = true;
                }
            }
            // Close client
            $this->client->quit();
        }
    }

    public static function menuLinkProvider()
    {
        return [
            'Patient -> Dashboard menu link' => ['Patient||Dashboard', 'Dashboard', false],
            'Patient -> Visits -> Create Visit menu link' => ['Patient||Visits||Create Visit', 'Patient Encounter', false, 'Visit History||Loading', true],
            'Patient -> Visits -> Visit History menu link' => ['Patient||Visits||Visit History', 'Visit History', false],
            'Patient -> Records -> Patient Record Request menu link' => ['Patient||Records||Patient Record Request', 'Patient Records Request', false, 'Visit History||Loading'],
            'Popups -> Issues menu link' => ['Popups||Issues', 'Issues', true],
            'Popups -> Export menu link' => ['Popups||Export', 'Export', true],
            'Popups -> Import menu link' => ['Popups||Import', 'Import', true],
            'Popups -> Appointments menu link' => ['Popups||Appointments', 'Appointments', true],
            'Popups -> Superbill menu link' => ['Popups||Superbill', 'Superbill', true],
            'Popups -> Letter menu link' => ['Popups||Letter', 'Letter', true],
            'Popups -> Chart Label menu link' => ['Popups||Chart Label', 'Chart Label', true],
            'Popups -> Barcode Label menu link' => ['Popups||Barcode Label', 'Barcode Label', true],
            'Popups -> Address Label menu link' => ['Popups||Address Label', 'Address Label', true]
        ];
    }
}
