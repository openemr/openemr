<?php

/**
 * JjEncounterContextMainMenuLinksTest class
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
use OpenEMR\Tests\E2e\Encounter\EncounterOpenTrait;
use OpenEMR\Tests\E2e\Login\LoginTestData;
use OpenEMR\Tests\E2e\Login\LoginTrait;
use OpenEMR\Tests\E2e\Patient\PatientTestData;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Panther\Client;

class JjEncounterContextMainMenuLinksTest extends PantherTestCase
{
    use BaseTrait;
    use LoginTrait;
    use EncounterOpenTrait;

    private $client;
    private $crawler;

    /**
     * @dataProvider menuLinkProvider
     * @depends testLoginAuthorized
     * @depends testPatientOpen
     * @depends testEncounterOpen
     */
    public function testEncounterContextMainMenuLink(string $menuLink, string $expectedTabPopupTitle, bool $popup, ?bool $looseTabTitle = false, ?string $loading = 'Loading'): void
    {
        if ($expectedTabPopupTitle == "Care Coordination" && !empty(getenv('UNABLE_SUPPORT_OPENEMR_NODEJS', true) ?? '')) {
            // Care Coordination page check will be skipped since this flag is set (which means the environment does not have
            //  a high enough version of nodejs)
            $this->markTestSkipped('Test skipped because this environment does not support high enough nodejs version.');
        }

        if (is_null($loading)) {
            $loading = 'Loading';
        }

        if (is_null($looseTabTitle)) {
            $looseTabTitle = false;
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
                $this->encounterOpenIfExist(PatientTestData::FNAME, PatientTestData::LNAME, PatientTestData::DOB, PatientTestData::SEX, false, false);
                $this->goToMainMenuLink($menuLink);
                if ($popup) {
                    $this->assertActivePopup($expectedTabPopupTitle);
                } else {
                    $this->assertActiveTab($expectedTabPopupTitle, $loading, $looseTabTitle);
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
            'Patient -> Visits -> Current menu link' => ['Patient||Visits||Current', 'Encounter', false, true],
            'Fees -> Fee Sheet menu link' => ['Fees||Fee Sheet', 'Encounter', false, true],
            'Fees -> Payment menu link' => ['Fees||Payment', 'Record Payment', false, false, 'Encounter'],
            'Fees -> Checkout menu link' => ['Fees||Checkout', 'Receipt for Payment', false, false, 'Encounter'],
            'Popups -> Payment link' => ['Popups||Payment', 'Payment', true]
        ];
    }
}
