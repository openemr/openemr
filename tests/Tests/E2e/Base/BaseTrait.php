<?php

/**
 * BaseTrait trait
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2024 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2025-2026 OpenCoreEMR Inc. <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\Base;

use Facebook\WebDriver\Exception\Internal\UnexpectedResponseException;
use Facebook\WebDriver\Exception\StaleElementReferenceException;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\Exception\UnexpectedAlertOpenException;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use OpenEMR\Tests\E2e\Xpaths\XpathsConstants;
use Symfony\Component\Panther\Client;

trait BaseTrait
{
    private Client $client;

    private function base(): void
    {
        $useGrid = getenv("SELENIUM_USE_GRID", true) ?? "false";

        if ($useGrid === "true") {
            // Use Selenium Grid (consistent testing environment with goal of stability)
            $seleniumHost = getenv("SELENIUM_HOST", true) ?? "selenium";
            $e2eBaseUrl = getenv("SELENIUM_BASE_URL", true) ?: "http://openemr";
            $forceHeadless = getenv("SELENIUM_FORCE_HEADLESS", true) ?? "false";
            // Implicit wait must be 0 when using explicit waits (waitFor,
            // waitForVisibility, wait()->until()). A non-zero implicit wait
            // causes each findElement() call inside an explicit wait condition
            // to block for the full implicit wait duration before throwing,
            // consuming the entire explicit wait timeout in a single attempt
            // instead of retrying.
            $implicitWait = (int)(getenv("SELENIUM_IMPLICIT_WAIT") ?: 0);
            $pageLoadTimeout = (int)(getenv("SELENIUM_PAGE_LOAD_TIMEOUT") ?: 60);

            $capabilities = DesiredCapabilities::chrome();

            $chromeArgs = [
                '--window-size=1920,1080',  // Matches SE_SCREEN_WIDTH/HEIGHT
                '--no-sandbox',
                '--disable-dev-shm-usage',
                '--disable-gpu'
            ];

            // Add headless if forced (but VNC won't work in headless mode)
            if ($forceHeadless === "true") {
                $chromeArgs[] = '--headless';
            }

            $capabilities->setCapability('goog:chromeOptions', [
                'args' => $chromeArgs
            ]);

            $capabilities->setCapability('unhandledPromptBehavior', 'accept');
            $capabilities->setCapability('pageLoadStrategy', 'normal');

            $seleniumUrl = "http://$seleniumHost:4444/wd/hub";
            $this->client = Client::createSeleniumClient($seleniumUrl, $capabilities, $e2eBaseUrl);

            $this->client->manage()->timeouts()->implicitlyWait($implicitWait);
            $this->client->manage()->timeouts()->pageLoadTimeout($pageLoadTimeout);
        } else {
            // Use local ChromeDriver (not a consistent testing environment, which is thus not stable, good luck :) )
            $this->client = static::createPantherClient(['external_base_uri' => "http://localhost"]);
            $this->client->manage()->window()->maximize();
        }
    }

    /**
     * Wait for the application to be fully initialized after login.
     *
     * Verifies Knockout.js has applied bindings by checking that the
     * #mainMenu div has children (rendered by the menu template).
     * Without this gate, tests that immediately navigate menus can
     * fail because the page HTML loaded but the JS framework hasn't
     * finished rendering.
     *
     * @param int $timeout Seconds to wait before giving up
     * @return bool True if app initialized, false if timeout
     */
    private function waitForAppReady(int $timeout = 30): bool
    {
        try {
            $this->client->wait($timeout)->until(fn($driver) => $driver->executeScript(
                'return document.getElementById("mainMenu")?.children.length > 0'
            ));
            // Log state on success to verify hypothesis that koAvailable
            // is always true when the menu renders successfully
            $state = $this->client->executeScript(<<<'JS_WRAP'
                return JSON.stringify({
                    koAvailable: typeof ko !== 'undefined',
                    mainMenuChildren: document.getElementById('mainMenu')?.children.length ?? 0
                });
            JS_WRAP);
            fwrite(STDERR, "[E2E] waitForAppReady succeeded: {$state}\n");
            return true;
        } catch (TimeoutException) {
            return false;
        }
    }

    /**
     * Create a TimeoutException with diagnostic information about the page state.
     *
     * Call this after waitForAppReady() returns false to get a detailed exception
     * with information about why the app didn't initialize.
     */
    private function createAppReadyTimeoutException(): TimeoutException
    {
        try {
            $diagnostics = (string) $this->client->executeScript(<<<'JS_WRAP'
                return JSON.stringify({
                    url: location.href,
                    readyState: document.readyState,
                    title: document.title,
                    koAvailable: typeof ko !== 'undefined',
                    mainMenuExists: document.getElementById('mainMenu') !== null,
                    mainMenuChildren: document.getElementById('mainMenu')?.children.length ?? 0,
                    bodyLength: document.body?.innerHTML?.length ?? 0
                });
            JS_WRAP);
        } catch (\Throwable) {
            $diagnostics = 'unable to gather diagnostics (executeScript failed)';
        }
        return new TimeoutException(
            "waitForAppReady() timed out after retry. Page state: {$diagnostics}"
        );
    }

    private function switchToIFrame(string $xpath): void
    {
        $selector = WebDriverBy::xpath($xpath);
        $iframe = $this->client->findElement($selector);
        $this->client->switchTo()->frame($iframe);
        $this->crawler = $this->client->refreshCrawler();
    }

    private function assertActiveTab(string $text, string $loading = "Loading", bool $looseTabTitle = false): void
    {
        // Retry loop to handle page transitions that can cause the active tab
        // element to become stale. After accepting a JS alert dialog (e.g.,
        // "Create Visit" when a visit already exists), the page may reload and
        // replace the active tab element during waitForElementToNotContain.
        $maxRetries = 3;
        $lastException = null;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                // Wait for the active tab element to exist (handles page transitions)
                $this->client->waitFor(XpathsConstants::ACTIVE_TAB);

                // Wait for each loading indicator to disappear from the live DOM
                foreach (explode('||', $loading) as $loadingText) {
                    $this->client->waitForElementToNotContain(XpathsConstants::ACTIVE_TAB, $loadingText);
                }

                // Success - exit retry loop
                $lastException = null;
                break;
            } catch (UnexpectedResponseException | StaleElementReferenceException $e) {
                // Element became stale during the page transition - retry
                $lastException = $e;
                if ($attempt < $maxRetries) {
                    usleep(500_000); // 500ms before retry
                }
            } catch (UnexpectedAlertOpenException $e) {
                // An alert appeared after the goToMainMenuLink() wait window.
                // Accept it and retry (the page may reload after accepting).
                try {
                    $this->client->getWebDriver()->switchTo()->alert()->accept();
                } catch (\Throwable) {
                    // Alert already dismissed
                }
                $lastException = $e;
                if ($attempt < $maxRetries) {
                    usleep(500_000); // 500ms before retry
                }
            }
        }

        if ($lastException !== null) {
            throw $lastException;
        }

        $this->crawler = $this->client->refreshCrawler();
        if ($looseTabTitle) {
            $this->assertTrue(str_contains($this->crawler->filterXPath(XpathsConstants::ACTIVE_TAB)->text(), $text), "[$text] tab load FAILED");
        } else {
            $this->assertSame($text, $this->crawler->filterXPath(XpathsConstants::ACTIVE_TAB)->text(), "[$text] tab load FAILED");
        }
    }

    private function assertActivePopup(string $text): void
    {
        $this->client->waitForElementToContain(XpathsConstants::MODAL_TITLE, $text);
        $this->crawler = $this->client->refreshCrawler();
        $this->assertSame($text, $this->crawler->filterXPath(XpathsConstants::MODAL_TITLE)->text(), "[$text] popup load FAILED");
    }

    private function goToMainMenuLink(string $menuLink, bool $acceptAlert = false): void
    {
        // ensure on main page (ie. not in an iframe)
        $this->client->switchTo()->defaultContent();
        // go to and click the menu link
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

            // Use elementToBeClickable + direct WebDriver click instead of
            // Panther's refreshCrawler/filterXPath/click, which can fail
            // with stale DOM references if the page updates between the
            // crawler snapshot and the click
            $element = $this->client->wait(30)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::xpath($menuLink)
                )
            );
            $element->click();
            $counter++;
        }

        if ($acceptAlert) {
            // Accept any JavaScript alert/confirm that appears after clicking.
            // Some menu items (e.g., "Create Visit") show a confirm dialog if
            // a visit already exists for the patient today. Handle immediately
            // after clicking to prevent the alert from blocking subsequent
            // WebDriver operations.
            try {
                $this->client->wait(2)->until(function ($driver) {
                    try {
                        $driver->switchTo()->alert()->accept();
                        return true;
                    } catch (\Throwable) {
                        return false;
                    }
                });
            } catch (TimeoutException) {
                // No alert appeared, which is fine
            }
        }
    }

    private function goToUserMenuLink(string $menuTreeIcon): void
    {
        $menuLink = XpathsConstants::USER_MENU_ICON;
        $menuLink2 = '//ul[@id="userdropdown"]//i[contains(@class, "' . $menuTreeIcon . '")]';
        $element = $this->client->wait(10)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::xpath($menuLink)
            )
        );
        $element->click();
        $element2 = $this->client->wait(10)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::xpath($menuLink2)
            )
        );
        $element2->click();
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
