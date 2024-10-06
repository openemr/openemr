<?php

/**
 * CreateStaffTest class
 *
 * @package OpenEMR
 * @link    https://www.open-emr.org
 * @auther  Bartosz Spyrko-Smietanko
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Bartosz Spyrko-Smietanko
 * @copyright Copyright (c) 2024 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e;

use Facebook\WebDriver\WebDriverBy;
use OpenEMR\Tests\E2e\Base\BaseTrait;
use OpenEMR\Tests\E2e\Login\LoginTrait;
use OpenEMR\Tests\E2e\Xpaths\XpathsConstants;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Panther\Client;

class CreateStaffTest extends PantherTestCase
{
    use BaseTrait;
    use LoginTrait;

    protected $client;
    protected $crawler;

    protected const NEW_USER_IFRAME = "//*[@id='modalframe']";
    protected const ADD_USER_BUTTON = "/html//a[text()='Add User']";
    protected const NEW_USER_BUTTON = "//form[@id='new_user']";
    protected const CREATE_USER_BUTTON = "//a[@id='form_save']";

    protected function setUp(): void
    {
        // clean up in case still left over from prior testing
        $this->cleanDatabase();
    }

    protected function tearDown(): void
    {
        $this->cleanDatabase();
    }

    /**
     * @depends testLoginAuthorized
     */
    public function testAddUser(): void
    {
        $this->base();
        try {
            // login
            $this->login('admin', 'pass');
            // add the user and then check that the user was added
            $this->openUsers();
            $this->assertActiveTab("User / Groups");
            $this->addUser('foobar');
            $this->assertUserPresent('foobar');
        } catch (\Throwable $e) {
            // Close client
            $this->client->quit();
            // re-throw the exception
            throw $e;
        }
        // Close client
        $this->client->quit();
    }

    protected function cleanDatabase(): void
    {
        // remove the created user
        $delete = "DELETE FROM users WHERE username = ?";
        sqlStatement($delete, array('foobar'));

        $delete = "DELETE FROM users_secure WHERE username = ?";
        sqlStatement($delete, array('foobar'));
    }

    protected function openUsers(): void
    {
        if ($this->crawler->filterXPath(XpathsConstants::COLLAPSED_MENU_BUTTON)->isDisplayed()) {
            $this->crawler->filterXPath(XpathsConstants::COLLAPSED_MENU_BUTTON)->click();
        }

        $this->client->waitFor(XpathsConstants::ADMINISTRATION_MENU);
        $this->crawler->filterXPath(XpathsConstants::ADMINISTRATION_MENU)->click();

        $this->client->waitFor(XpathsConstants::USERS_SUBMENU);
        $this->crawler->filterXPath(XpathsConstants::USERS_SUBMENU)->click();
    }

    protected function addUser($username): void
    {
        // need to switch to the iframe
        $this->client->waitFor(XpathsConstants::ADMIN_IFRAME);
        $this->switchToIFrame(WebDriverBy::xpath(XpathsConstants::ADMIN_IFRAME));
        $this->client->waitFor(self::ADD_USER_BUTTON);
        $this->crawler = $this->client->refreshCrawler();
        $this->crawler->filterXPath(self::ADD_USER_BUTTON)->click();

        $this->client->switchTo()->defaultContent();
        $this->client->waitFor(self::NEW_USER_IFRAME);
        $this->switchToIFrame(WebDriverBy::xpath(self::NEW_USER_IFRAME));
        $this->client->waitFor(self::NEW_USER_BUTTON);
        $this->crawler = $this->client->refreshCrawler();
        $newUser = $this->crawler->filterXPath(self::NEW_USER_BUTTON)->form();

        $newUser['rumple'] = $username;
        $newUser['stiltskin'] = 'Test12te$t';
        $newUser['fname'] = 'Foo';
        $newUser['lname'] = 'Bar';
        $newUser['adminPass'] = 'pass';

        $this->client->waitFor(self::CREATE_USER_BUTTON);
        $this->crawler = $this->client->refreshCrawler();
        $this->crawler->filterXPath(self::CREATE_USER_BUTTON)->click();

        $this->client->switchTo()->defaultContent();
    }

    protected function assertUserPresent($username): void
    {
        $this->client->waitFor(XpathsConstants::ADMIN_IFRAME);
        $this->switchToIFrame(WebDriverBy::xpath(XpathsConstants::ADMIN_IFRAME));

        // below will throw a timeout exception and fail if not there
        $this->client->waitFor("//table//a[text()='$username']");

        $this->client->switchTo()->defaultContent();

        // assert that new user is in database
        $usernameDatabase = sqlQuery("SELECT `username` FROM `users` WHERE `username` = ?", [$username]);
        $this->assertSame(($usernameDatabase['username'] ?? ''), $username, 'New user is not in database, so FAILED');
    }
}
