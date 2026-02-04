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

use Facebook\WebDriver\WebDriver;
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

    private function userAddIfNotExist(string $username): void
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
        // and triggers a reload of the admin iframe
        $this->client->wait(30)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(
                WebDriverBy::xpath(XpathsConstantsUserAddTrait::NEW_USER_IFRAME_USERADD_TRAIT)
            )
        );

        // assert the new user is in the database
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

        // Populate form fields using direct WebDriver sendKeys instead of
        // Panther's Form API. The Form API captures DOM element references at
        // a point in time; if JavaScript modifies the form structure after
        // capture, the stale references silently fail to set values.
        $driver = $this->client->getWebDriver();
        $this->clearAndType($driver, 'fname', UserTestData::FIRSTNAME);
        $this->clearAndType($driver, 'lname', UserTestData::LASTNAME);
        $this->clearAndType($driver, 'adminPass', LoginTestData::password);
        $this->clearAndType($driver, 'stiltskin', UserTestData::PASSWORD);
        // Set username last to ensure earlier field handlers cannot overwrite it
        $this->clearAndType($driver, 'rumple', $username);

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

    private function clearAndType(WebDriver $driver, string $fieldName, string $value): void
    {
        $field = $driver->findElement(WebDriverBy::name($fieldName));
        $field->clear();
        $field->sendKeys($value);
    }
}
