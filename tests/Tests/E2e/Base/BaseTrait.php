<?php

/**
 * BaseTrait trait
 *
 * @package OpenEMR
 * @link    https://www.open-emr.org
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2024 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\Base;

use Facebook\WebDriver\WebDriverBy;
use OpenEMR\Tests\E2e\Xpaths\XpathsConstants;

trait BaseTrait
{
    protected function base(): void
    {
        $e2eBaseUrl = getenv("OPENEMR_BASE_URL_E2E", true) ?: "http://localhost";
        $this->client = static::createPantherClient(['external_base_uri' => $e2eBaseUrl]);
        $this->client->manage()->window()->maximize();
    }

    protected function switchToIFrame(string $xpath): void
    {
        $selector = WebDriverBy::xpath($xpath);
        $iframe = $this->client->findElement($selector);
        $this->client->switchTo()->frame($iframe);
        $this->crawler = $this->client->refreshCrawler();
    }

    protected function assertActiveTab($text): void
    {
        $startTime = (int) (microtime(true) * 1000);
        while (strpos($this->crawler->filterXPath(XpathsConstants::ACTIVE_TAB)->text(), "Loading") === 0) {
            if (($startTime + 10000) < ((int) (microtime(true) * 1000))) {
                $this->fail("Timeout waiting for tab [$text]");
            }
            usleep(100);
        }
        $this->assertSame($text, $this->crawler->filterXPath(XpathsConstants::ACTIVE_TAB)->text(), "[$text] tab load FAILED");
    }

    protected function goToMainMenuLink(string $menuLink): void
    {
        // check if the menu cog is showing. if so, then click it.
        if ($this->crawler->filterXPath(XpathsConstants::COLLAPSED_MENU_BUTTON)->isDisplayed()) {
            $this->crawler->filterXPath(XpathsConstants::COLLAPSED_MENU_BUTTON)->click();
        }
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

    protected function goToUserMenuLink(string $menuTreeIcon): void
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
