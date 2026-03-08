<?php

/**
 * UserAddTrait
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Bartosz Spyrko-Smietanko
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2020 Bartosz Spyrko-Smietanko
 * @copyright Copyright (c) 2024 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc. <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\User;

use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use OpenEMR\Tests\E2e\Base\BaseTrait;
use OpenEMR\Tests\E2e\Login\LoginTestData;
use OpenEMR\Tests\E2e\Login\LoginTrait;
use OpenEMR\Tests\E2e\User\UserTestData;
use OpenEMR\Tests\E2e\Xpaths\XpathsConstants;
use OpenEMR\Tests\E2e\Xpaths\XpathsConstantsUserAddTrait;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;

trait UserAddTrait
{
    use BaseTrait;
    use LoginTrait;

    #[Depends('testLoginAuthorized')]
    #[Test]
    public function testUserAdd(): void
    {
        $this->base();
        try {
            $this->userAddIfNotExist(UserTestData::USERNAME);
        } catch (\Throwable $e) {
            // Close client
            $this->client->quit();
            // re-throw the exception
            throw $e;
        }
        // Close client
        $this->client->quit();
    }

    private function userAddIfNotExist(string $username, bool $isRetry = false): void
    {
        // if user already exists, then skip this
        if ($this->isUserExist($username)) {
            $this->markTestSkipped('New user test skipped because this user already exists.');
        }

        // login
        $this->login(LoginTestData::username, LoginTestData::password);

        // go to admin -> users tab
        $this->goToMainMenuLink('Admin||Users');
        $this->assertActiveTab("User / Groups");

        // add the user
        $this->client->waitFor(XpathsConstants::ADMIN_IFRAME);
        $this->switchToIFrame(XpathsConstants::ADMIN_IFRAME);
        // Use elementToBeClickable + direct WebDriver click instead of
        // Panther's refreshCrawler/filterXPath/click pattern
        $addUserBtn = $this->client->wait(30)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::xpath(XpathsConstantsUserAddTrait::ADD_USER_BUTTON_USERADD_TRAIT)
            )
        );
        $addUserBtn->click();
        $this->client->switchTo()->defaultContent();
        $this->client->waitFor(XpathsConstantsUserAddTrait::NEW_USER_IFRAME_USERADD_TRAIT);
        $this->switchToIFrame(XpathsConstantsUserAddTrait::NEW_USER_IFRAME_USERADD_TRAIT);
        $this->client->waitFor(XpathsConstantsUserAddTrait::NEW_USER_BUTTON_USERADD_TRAIT);
        $this->crawler = $this->client->refreshCrawler();
        $this->client->wait(10)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::xpath(XpathsConstantsUserAddTrait::NEW_USER_FORM_RUMPLE_FIELD)
            )
        );

        // Wait for the form's submitform() JS function to be defined
        $this->client->wait(10)->until(fn($driver) => $driver->executeScript('return typeof submitform === "function";'));

        $this->populateUserFormReliably($username);

        // Use direct WebDriver click instead of Panther's crawler click,
        // which can fail with stale DOM references
        $createBtn = $this->client->wait(10)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::xpath(XpathsConstantsUserAddTrait::CREATE_USER_BUTTON_USERADD_TRAIT)
            )
        );
        $createBtn->click();

        // Switch to default content to properly detect modal state changes
        $this->client->switchTo()->defaultContent();

        // Wait for the modal iframe to disappear (dialog closes on successful user creation)
        // The dialog calls dlgclose('reload', false) on success, which closes the modal
        // and triggers a reload of the admin iframe.
        //
        // Use a short initial timeout to detect failure quickly. If the modal
        // doesn't close, gather diagnostics and retry once with a fresh session.
        if (!$this->waitForModalClose(10)) {
            // Modal didn't close - gather diagnostics before retrying
            $diagnostics = $this->gatherModalDiagnostics($username);
            fwrite(STDERR, "[E2E] Modal failed to close after user creation. Diagnostics: {$diagnostics}\n");

            // Check if user was actually created despite modal not closing
            if ($this->isUserExist($username)) {
                fwrite(STDERR, "[E2E] User exists in database despite modal not closing - possible JS/UI issue\n");
                // Force close by refreshing the page - modal state is broken but data is saved
                $this->client->request('GET', '/interface/main/main_screen.php');
                $this->waitForAppReady(10);
            } elseif ($isRetry) {
                // Already retried once - fail with diagnostics
                throw new TimeoutException(
                    "Modal failed to close after user creation (retry also failed). Diagnostics: {$diagnostics}"
                );
            } else {
                // User not created - retry with fresh session
                fwrite(STDERR, "[E2E] User not in database, retrying with fresh session...\n");
                $this->client->quit();
                $this->base();
                $this->userAddIfNotExist($username, true);
                return;
            }
        }

        // Assert the new user is in the database
        $this->assertUserInDatabase($username);

        // Wait for the admin iframe to be ready (it reloads after dialog closes)
        $this->client->waitFor(XpathsConstants::ADMIN_IFRAME);
        $this->switchToIFrame(XpathsConstants::ADMIN_IFRAME);

        // Wait for the Add User button to be visible again (indicates the iframe has fully reloaded)
        $this->client->waitFor(XpathsConstantsUserAddTrait::ADD_USER_BUTTON_USERADD_TRAIT);

        // Now wait for the new user to appear in the table
        // This will throw a timeout exception and fail if the new user is not listed
        $this->client->waitFor("//table//a[text()='$username']");
    }

    private function assertUserInDatabase(string $username): void
    {
        // Poll the database for the new user (up to 10s, checking every 500ms)
        $this->client->wait(10, 500)->until(fn() => $this->isUserExist($username));
    }

    private function populateUserFormReliably(string $username): void
    {
        // Wait for password field and Create User button to be ready
        $this->client->waitFor('//input[@name="stiltskin"]');
        $this->client->wait(10)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::xpath(XpathsConstantsUserAddTrait::CREATE_USER_BUTTON_USERADD_TRAIT)
            )
        );

        // Populate form fields using JavaScript value assignment (see
        // clearAndType). Panther's Form API and WebDriver sendKeys() both
        // have reliability issues under CI resource pressure.
        $this->clearAndType('fname', UserTestData::FIRSTNAME);
        $this->clearAndType('lname', UserTestData::LASTNAME);
        $this->clearAndType('adminPass', LoginTestData::password);
        $this->clearAndType('stiltskin', UserTestData::PASSWORD);
        // Set username last to ensure earlier field handlers cannot overwrite it
        $this->clearAndType('rumple', $username);

        // Verify all form fields accepted their values. If a field was
        // silently cleared by JavaScript, the form submission will fail
        // server-side and the modal won't close, causing a timeout.
        $this->client->wait(10)->until(function ($driver) use ($username) {
            $rumple = $driver->findElement(WebDriverBy::name('rumple'));
            $stiltskin = $driver->findElement(WebDriverBy::name('stiltskin'));
            $fname = $driver->findElement(WebDriverBy::name('fname'));
            $lname = $driver->findElement(WebDriverBy::name('lname'));
            return $rumple->getAttribute('value') === $username
                && $stiltskin->getAttribute('value') === UserTestData::PASSWORD
                && $fname->getAttribute('value') === UserTestData::FIRSTNAME
                && $lname->getAttribute('value') === UserTestData::LASTNAME;
        });

        $this->client->waitFor(XpathsConstantsUserAddTrait::CREATE_USER_BUTTON_USERADD_TRAIT);
    }

    /**
     * Set a form field's value using JavaScript instead of WebDriver
     * sendKeys(). Under CI resource pressure, sendKeys() dispatches key
     * events one-by-one and Chrome can drop keystrokes. JavaScript
     * value assignment is atomic and reliable. Input and change events
     * are dispatched so any listeners (e.g. password strength meter)
     * still fire.
     */
    private function clearAndType(string $fieldName, string $value): void
    {
        $this->client->executeScript(
            'var f = document.getElementsByName(arguments[0])[0];'
            . 'f.value = "";'
            . 'f.value = arguments[1];'
            . 'f.dispatchEvent(new Event("input", {bubbles: true}));'
            . 'f.dispatchEvent(new Event("change", {bubbles: true}));',
            [$fieldName, $value]
        );
    }

    /**
     * Wait for the modal iframe to close.
     *
     * @param int $timeout Seconds to wait
     * @return bool True if modal closed, false if timeout
     */
    private function waitForModalClose(int $timeout): bool
    {
        try {
            $this->client->wait($timeout)->until(
                WebDriverExpectedCondition::invisibilityOfElementLocated(
                    WebDriverBy::xpath(XpathsConstantsUserAddTrait::NEW_USER_IFRAME_USERADD_TRAIT)
                )
            );
            return true;
        } catch (TimeoutException) {
            return false;
        }
    }

    /**
     * Gather diagnostic information when the modal fails to close.
     *
     * Captures:
     * - Whether the user exists in the database
     * - Modal iframe content (error messages, form state)
     * - Any visible alert text
     *
     * @param string $username The username being created
     * @return string JSON-encoded diagnostics
     */
    private function gatherModalDiagnostics(string $username): string
    {
        try {
            $userExists = $this->isUserExist($username);

            // Check if modal iframe is still present
            $modalVisible = false;
            $iframeContent = '';
            try {
                $iframe = $this->client->findElement(
                    WebDriverBy::xpath(XpathsConstantsUserAddTrait::NEW_USER_IFRAME_USERADD_TRAIT)
                );
                $modalVisible = $iframe->isDisplayed();

                // Switch to iframe to capture its content
                if ($modalVisible) {
                    $this->client->switchTo()->frame($iframe);
                    $iframeContent = (string) $this->client->executeScript(
                        'return document.body ? document.body.innerText.substring(0, 500) : "no body"'
                    );
                    $this->client->switchTo()->defaultContent();
                }
            } catch (\Throwable) {
                // Modal not found or not accessible
            }

            return json_encode([
                'userExistsInDb' => $userExists,
                'modalVisible' => $modalVisible,
                'iframeContentPreview' => $iframeContent,
            ], JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            return json_encode(['error' => 'Failed to gather diagnostics: ' . $e->getMessage()]);
        }
    }
}
