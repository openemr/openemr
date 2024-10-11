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

use OpenEMR\Tests\E2e\Base\BaseTrait;
use OpenEMR\Tests\E2e\Login\LoginTrait;
use OpenEMR\Tests\E2e\Xpaths\XpathsConstants;
use OpenEMR\Tests\E2e\Xpaths\XpathsConstantsUserAddTrait;

trait UserAddTrait
{
    use BaseTrait;
    use LoginTrait;

    /**
     * @depends testLoginAuthorized
     */
    public function testUserAdd(): void
    {
        $this->base();
        try {
            $this->userAddIfNotExist('foobar');
        } catch (\Throwable $e) {
            // Close client
            $this->client->quit();
            // re-throw the exception
            throw $e;
        }
        // Close client
        $this->client->quit();
    }

    protected function userAddIfNotExist(string $username): void
    {
        // if user already exists, then skip this
        $usernameDatabase = sqlQuery("SELECT `username` FROM `users` WHERE `username` = ?", [$username]);
        if (!empty($usernameDatabase['username']) && ($usernameDatabase['username'] == $username)) {
            $this->markTestSkipped('New user test skipped because this user already exists.');
        }

        // login
        $this->login('admin', 'pass');

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
        $newUser = $this->crawler->filterXPath(XpathsConstantsUserAddTrait::NEW_USER_BUTTON_USERADD_TRAIT)->form();
        $newUser['rumple'] = $username;
        $newUser['stiltskin'] = 'Test12te$t';
        $newUser['fname'] = 'Foo';
        $newUser['lname'] = 'Bar';
        $newUser['adminPass'] = 'pass';
        $this->client->waitFor(XpathsConstantsUserAddTrait::CREATE_USER_BUTTON_USERADD_TRAIT);
        $this->crawler = $this->client->refreshCrawler();
        $this->crawler->filterXPath(XpathsConstantsUserAddTrait::CREATE_USER_BUTTON_USERADD_TRAIT)->click();
        $this->client->switchTo()->defaultContent();
        // assert the new user has been added
        $this->client->waitFor(XpathsConstants::ADMIN_IFRAME);
        $this->switchToIFrame(XpathsConstants::ADMIN_IFRAME);
        // below line will throw a timeout exception and fail if the new user is not listed
        $this->client->waitFor("//table//a[text()='$username']");
        $this->client->switchTo()->defaultContent();
        $usernameDatabase = sqlQuery("SELECT `username` FROM `users` WHERE `username` = ?", [$username]);
        $this->assertSame(($usernameDatabase['username'] ?? ''), $username, 'New user is not in database, so FAILED');
    }
}
