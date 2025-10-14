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
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;

trait UserAddTrait
{
    use BaseTrait;
    use LoginTrait;

    private int $userAddAttemptCounter = 1;

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

        sleep(2); // wait for the form to be ready

        $this->populateUserFormReliably($username);

        sleep(2); // wait for the populated form to be ready

        $this->crawler = $this->client->refreshCrawler();
        $this->crawler->filterXPath(XpathsConstantsUserAddTrait::CREATE_USER_BUTTON_USERADD_TRAIT)->click();

        sleep(2); // wait for the form submission to be complete

        // assert the new user is in the database
        $this->assertUserInDatabase($username);

        // assert the new user can be seen in the gui
        $this->client->switchTo()->defaultContent();
        $this->client->waitFor(XpathsConstants::ADMIN_IFRAME);
        $this->switchToIFrame(XpathsConstants::ADMIN_IFRAME);
        // below line will throw a timeout exception and fail if the new user is not listed
        $this->client->waitFor("//table//a[text()='$username']");
    }

    private function assertUserInDatabase(string $username): void
    {
        // assert the new user is in the database (check 3 times with 5 second delay prior each check to
        // ensure allow enough time)
        $userExistDatabase = false;
        $counter = 0;
        while (!$userExistDatabase && $counter < 3) {
            if ($counter > 0) {
                echo "\n" . "TRY " . ($counter + 1) . " of 3 to see if new user is in database" . "\n";
            }
            sleep(2);
            if ($this->isUserExist($username)) {
                $userExistDatabase = true;
            }
            $counter++;
        }
        $this->assertTrue($userExistDatabase, 'New user is not in database, so FAILED');
    }

    private function populateUserFormReliably($username): void
    {
        // Simple but effective approach - wait for page to be ready
        $this->client->waitFor('//input[@name="stiltskin"]');
        $this->client->wait(1000000); // 1 second for all JS to initialize

        $this->crawler = $this->client->refreshCrawler();
        $newUser = $this->crawler->filterXPath(XpathsConstantsUserAddTrait::NEW_USER_BUTTON_USERADD_TRAIT)->form();

        $newUser['rumple'] = $username;
        $newUser['fname'] = UserTestData::FIRSTNAME;
        $newUser['lname'] = UserTestData::LASTNAME;
        $newUser['adminPass'] = LoginTestData::password;

        // Retry logic for password field
        $this->setPasswordWithRetry($newUser);

        $this->client->waitFor(XpathsConstantsUserAddTrait::CREATE_USER_BUTTON_USERADD_TRAIT);
    }

    private function setPasswordWithRetry($newUser): void
    {
        for ($attempt = 1; $attempt <= 3; $attempt++) {
            // Set password
            $newUser['stiltskin'] = UserTestData::PASSWORD;
            $this->client->wait(300000); // 0.3 seconds

            // Verify it worked
            try {
                $passwordField = $this->client->findElement(WebDriverBy::name('stiltskin'));
                $value = $passwordField->getAttribute('value');

                if ($value === UserTestData::PASSWORD) {
                    return; // Success!
                }
            } catch (Exception) {
                // Field not found or other error
            }

            if ($attempt === 3) {
                throw new Exception("Failed to populate password field after 3 attempts");
            }

            // Wait before retry
            $this->client->wait(500000); // 0.5 seconds
        }
    }
}
