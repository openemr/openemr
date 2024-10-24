<?php

/**
 * BaseTrait trait
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2024 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\Base;

use Facebook\WebDriver\WebDriverBy;
use OpenEMR\Tests\E2e\Xpaths\XpathsConstants;

trait BaseTrait
{
    private function base(): void
    {
        $e2eBaseUrl = getenv("OPENEMR_BASE_URL_E2E", true) ?: "http://localhost";
        $this->client = static::createPantherClient(['external_base_uri' => $e2eBaseUrl]);
        $this->client->manage()->window()->maximize();
    }

    private function switchToIFrame(string $xpath): void
    {
        $selector = WebDriverBy::xpath($xpath);
        $iframe = $this->client->findElement($selector);
        $this->client->switchTo()->frame($iframe);
        $this->crawler = $this->client->refreshCrawler();
    }

    private function assertActiveTab(string $text, string $loading = "Loading", bool $looseTabTitle = false, bool $clearAlert = false): void
    {
        if ($clearAlert) {
            // ok the alert (example case of this is when open the Create Visit link since there is already an encounter on same day)
            $this->client->wait(10)->until(function ($driver) {
                try {
                    $alert = $driver->switchTo()->alert();
                    $alert->accept();
                    return true; // Alert is present and has been cleared
                } catch (\Exception $e) {
                    return false; // Alert is not present
                }
            });
        }
        $startTime = (int) (microtime(true) * 1000);
        if (str_contains($loading, "||")) {
            // have 2 $loading to check
            $loading = explode("||", $loading);
            while (
                str_contains($this->crawler->filterXPath(XpathsConstants::ACTIVE_TAB)->text(), $loading[0]) ||
                   str_contains($this->crawler->filterXPath(XpathsConstants::ACTIVE_TAB)->text(), $loading[1])
            ) {
                if (($startTime + 10000) < ((int)(microtime(true) * 1000))) {
                    $this->fail("Timeout waiting for tab [$text]");
                }
                usleep(100);
            }
        } else {
            // only have 1 $loading to check
            while (str_contains($this->crawler->filterXPath(XpathsConstants::ACTIVE_TAB)->text(), $loading)) {
                if (($startTime + 10000) < ((int)(microtime(true) * 1000))) {
                    $this->fail("Timeout waiting for tab [$text]");
                }
                usleep(100);
            }
        }
        if ($looseTabTitle) {
            $this->assertTrue(str_contains($this->crawler->filterXPath(XpathsConstants::ACTIVE_TAB)->text(), $text), "[$text] tab load FAILED");
        } else {
            $this->assertSame($text, $this->crawler->filterXPath(XpathsConstants::ACTIVE_TAB)->text(), "[$text] tab load FAILED");
        }
    }

    private function assertActivePopup(string $text): void
    {
        $this->client->waitFor(XpathsConstants::MODAL_TITLE);
        $this->crawler = $this->client->refreshCrawler();
        $startTime = (int) (microtime(true) * 1000);
        while (empty($this->crawler->filterXPath(XpathsConstants::MODAL_TITLE)->text())) {
            if (($startTime + 10000) < ((int) (microtime(true) * 1000))) {
                $this->fail("Timeout waiting for popup [$text]");
            }
            usleep(100);
        }
        $this->assertSame($text, $this->crawler->filterXPath(XpathsConstants::MODAL_TITLE)->text(), "[$text] popup load FAILED");
    }

    private function goToMainMenuLink(string $menuLink): void
    {
        // ensure on main page (ie. not in an iframe)
        $this->client->switchTo()->defaultContent();
        // got to and click the menu link
        $menuLinkSequenceArray = explode('||', $menuLink);
        $counter = 0;
        foreach ($menuLinkSequenceArray as $menuLinkItem) {
            if ($counter == 0) {
                if (count($menuLinkSequenceArray) > 1) {
                    // start clicking through a dropdown/nested menu item
                    $menuLink = '//div[@id="mainMenu"]/div/div/div/div[text()="' . $menuLinkItem . '"]';
                } else {
                    // just clicking a simple/single menu item
                    $menuLink = '//div[@id="mainMenu"]/div/div/div[text()="' . $menuLinkItem . '"]';
                }
            } elseif ($counter == 1) {
                if (count($menuLinkSequenceArray) == 2) {
                    // click the nested menu item
                    $menuLink = '//div[@id="mainMenu"]/div/div/div/div[text()="' . $menuLinkSequenceArray[0] . '"]/../ul/li/div[text()="' . $menuLinkItem . '"]';
                } else {
                    // continue clicking through a dropdown/nested menu item
                    $menuLink = '//div[@id="mainMenu"]/div/div/div/div[text()="' . $menuLinkSequenceArray[0] . '"]/../ul/li/div/div[text()="' . $menuLinkItem . '"]';
                }
            } else { // $counter > 1
                // click the nested menu item
                $menuLink = '//div[@id="mainMenu"]/div/div/div/div[text()="' . $menuLinkSequenceArray[0] . '"]/../ul/li/div/div[text()="' . $menuLinkSequenceArray[1] . '"]/../ul/li/div[text()="' . $menuLinkItem . '"]';
            }

            $this->client->waitFor($menuLink);
            $this->crawler = $this->client->refreshCrawler();
            $this->crawler->filterXPath($menuLink)->click();
            $counter++;
        }
    }

    private function goToUserMenuLink(string $menuTreeIcon): void
    {
        $menuLink = XpathsConstants::USER_MENU_ICON;
        $menuLink2 = '//ul[@id="userdropdown"]//i[contains(@class, "' . $menuTreeIcon . '")]';
        $this->client->waitFor($menuLink);
        $this->crawler = $this->client->refreshCrawler();
        $this->crawler->filterXPath($menuLink)->click();
        $this->client->waitFor($menuLink2);
        $this->crawler = $this->client->refreshCrawler();
        $this->crawler->filterXPath($menuLink2)->click();
    }

    private function isUserExist(string $username): bool
    {
        $usernameDatabase = sqlQuery("SELECT `username` FROM `users` WHERE `username` = ?", [$username]);
        if (($usernameDatabase['username'] ?? '') == $username) {
            return true;
        } else {
            return false;
        }
    }

    private function isPatientExist(string $firstname, string $lastname, string $dob, string $sex): bool
    {
        $patientDatabase = sqlQuery("SELECT `fname` FROM `patient_data` WHERE `fname` = ? AND `lname` = ? AND `DOB` = ? AND `sex` = ?", [$firstname, $lastname, $dob, $sex]);
        if (!empty($patientDatabase['fname']) && ($patientDatabase['fname'] == $firstname)) {
            return true;
        } else {
            return false;
        }
    }

    private function isEncounterExist(string $firstname, string $lastname, string $dob, string $sex): bool
    {
        $patientDatabase = sqlQuery("SELECT `patient_data`.`fname`
                                     FROM `patient_data`
                                     INNER JOIN `form_encounter`
                                     ON `patient_data`.`pid` = `form_encounter`.`pid`
                                     WHERE `patient_data`.`fname` = ? AND `patient_data`.`lname` = ? AND `patient_data`.`DOB` = ? AND `patient_data`.`sex` = ?", [$firstname, $lastname, $dob, $sex]);
        if (!empty($patientDatabase['fname']) && ($patientDatabase['fname'] == $firstname)) {
            return true;
        } else {
            return false;
        }
    }

    private function logOut(): void
    {
        $this->client->switchTo()->defaultContent();
        $this->goToUserMenuLink('fa-sign-out-alt');
        $this->client->waitFor('//input[@id="authUser"]');
        $title = $this->client->getTitle();
        $this->assertSame('OpenEMR Login', $title, 'Logout FAILED');
    }
}
