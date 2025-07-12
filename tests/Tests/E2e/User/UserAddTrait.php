<?php

/**
 * UserAddTrait
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @auther    Bartosz Spyrko-Smietanko
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Bartosz Spyrko-Smietanko
 * @copyright Copyright (c) 2024 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\User;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use OpenEMR\Tests\E2e\Base\BaseTrait;
use OpenEMR\Tests\E2e\Login\LoginTestData;
use OpenEMR\Tests\E2e\Login\LoginTrait;
use OpenEMR\Tests\E2e\User\UserTestData;
use OpenEMR\Tests\E2e\Xpaths\XpathsConstants;
use OpenEMR\Tests\E2e\Xpaths\XpathsConstantsUserAddTrait;
use PHPUnit\Framework\ExpectationFailedException;

trait UserAddTrait
{
    use BaseTrait;
    use LoginTrait;

    private int $userAddAttemptCounter = 1;
    private bool $passUserAddIfNotExist = false;

    /**
     * @depends testLoginAuthorized
     */
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
        $this->client->waitFor(XpathsConstantsUserAddTrait::ADD_USER_BUTTON_USERADD_TRAIT);
        $this->crawler = $this->client->refreshCrawler();
        $this->crawler->filterXPath(XpathsConstantsUserAddTrait::ADD_USER_BUTTON_USERADD_TRAIT)->click();
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

        $this->populateUserFormWithRetry($username);

        sleep(5); // wait for the form to be ready

        $this->crawler = $this->client->refreshCrawler();
        $this->crawler->filterXPath(XpathsConstantsUserAddTrait::CREATE_USER_BUTTON_USERADD_TRAIT)->click();

        // assert the new user is in the database
        $this->assertUserInDatabase($username);
        // since this function is run recursively in above line, ensure only do the below block once
        if (!$this->passUserAddIfNotExist) {
            // assert the new user can be seen in the gui
            $this->client->switchTo()->defaultContent();
            $this->client->waitFor(XpathsConstants::ADMIN_IFRAME);
            $this->switchToIFrame(XpathsConstants::ADMIN_IFRAME);
            // below line will throw a timeout exception and fail if the new user is not listed
            $this->client->waitFor("//table//a[text()='$username']");
            $this->passUserAddIfNotExist = true;
        }
    }

    private function assertUserInDatabase(string $username): void
    {
        // assert the new user is in the database (if this fails, then will try userAddIfNotExist() up to
        // 3 times total before failing)
        try {
            $this->innerAssertUserInDatabase($username);
        } catch (ExpectationFailedException $e) {
            if ($this->userAddAttemptCounter > 2) {
                // re-throw since have failed 3 tries
                throw $e;
            } else {
                // try again since not yet 3 tries
                $this->userAddAttemptCounter++;
                echo "\n" . "TRY " . ($this->userAddAttemptCounter) . " of 3 to add new user to database" . "\n";
                $this->logOut();
                $this->userAddIfNotExist($username);
            }
        }
    }

    private function innerAssertUserInDatabase(string $username): void
    {
        // assert the new user is in the database (check 3 times with 5 second delay prior each check to
        // ensure allow enough time)
        $userExistDatabase = false;
        $counter = 0;
        while (!$userExistDatabase && $counter < 3) {
            if ($counter > 0) {
                echo "\n" . "TRY " . ($counter + 1) . " of 3 to see if new user is in database" . "\n";
            }
            sleep(5);
            if ($this->isUserExist($username)) {
                $userExistDatabase = true;
            }
            $counter++;
        }
        $this->assertTrue($userExistDatabase, 'New user is not in database, so FAILED');
    }

    private function populateUserFormWithRetry($username): void
    {
        // Wait for JavaScript to fully load
        $this->client->wait()->until(
            WebDriverExpectedCondition::jsCondition("return typeof checkPasswordStrength !== 'undefined'")
        );

        $this->crawler = $this->client->refreshCrawler();
        $newUser = $this->crawler->filterXPath(XpathsConstantsUserAddTrait::NEW_USER_BUTTON_USERADD_TRAIT)->form();

        $newUser['rumple'] = $username;
        $newUser['fname'] = UserTestData::FIRSTNAME;
        $newUser['lname'] = UserTestData::LASTNAME;
        $newUser['adminPass'] = LoginTestData::password;

        // Retry logic for password field
        for ($i = 0; $i < 3; $i++) {
            $newUser['stiltskin'] = UserTestData::PASSWORD;
            $this->client->wait(200000); // Wait for any JS processing

            // Verify it worked
            $passwordField = $this->client->findElement('//input[@name="stiltskin"]');
            if ($passwordField->getAttribute('value') === UserTestData::PASSWORD) {
                break; // Success!
            }

            if ($i === 2) {
                throw new Exception("Failed to populate password field after 3 attempts");
            }
        }

        $this->client->waitFor(XpathsConstantsUserAddTrait::CREATE_USER_BUTTON_USERADD_TRAIT);
    }
}
