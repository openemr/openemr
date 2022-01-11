<?php

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\Pages;

use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Panther\Client;
use Facebook\WebDriver\WebDriverBy;

class UsersTab
{
    private const ADMIN_IFRAME = "//*[@id='framesDisplay']//iframe[@name='adm']";
    private const NEW_USER_IFRAME = "//*[@id='modalframe']";

    private const ADD_USER_BUTTON = "/html//a[text()='Add User']";
    private const NEW_USER_BUTTON = "//form[@id='new_user']";
    private const CREATE_USER_BUTTON = "//a[@id='form_save']";

    private $crawler;
    private $client;
    private $test;

    public function __construct($crawler, $client, $test)
    {
        $this->crawler = $crawler;
        $this->client = $client;
        $this->test = $test;
    }

    public function addUser($username): void
    {
        // need to switch to the iframe
        $crawler = $this->switchToIFrame(WebDriverBy::xpath(UsersTab::ADMIN_IFRAME));
        $this->client->waitFor(UsersTab::ADD_USER_BUTTON);
        $crawler->filterXPath(UsersTab::ADD_USER_BUTTON)->click();

        $this->client->switchTo()->defaultContent();
        $crawler = $this->switchToIFrame(WebDriverBy::xpath(UsersTab::NEW_USER_IFRAME));
        $this->client->waitFor(UsersTab::NEW_USER_BUTTON);
        $newUser = $crawler->filterXPath(UsersTab::NEW_USER_BUTTON)->form();

        $newUser['rumple'] = $username;
        $newUser['stiltskin'] = 'Test12te$t';
        $newUser['fname'] = 'Foo';
        $newUser['lname'] = 'Bar';
        $newUser['adminPass'] = 'pass';

        $this->client->waitFor(UsersTab::CREATE_USER_BUTTON);
        $crawler->filterXPath(UsersTab::CREATE_USER_BUTTON)->click();

        $this->client->switchTo()->defaultContent();
    }

    public function assertUserPresent($username): void
    {
        $crawler = $this->switchToIFrame(WebDriverBy::xpath(UsersTab::ADMIN_IFRAME));
        try {
            $this->client->waitFor("//table//a[text()='$username']");
        } catch (\Facebook\WebDriver\Exception\TimeoutException $e) {
            // see if the issue is screen refresh too fast or if the new user really didn't get added to the database
            $usernameDatabase = sqlQuery("SELECT `username` FROM `users` WHERE `username` = ?", [$username]);
            if (!empty($usernameDatabase['username'])) {
                $this->test->fail("FAIL, User with name $username not found in displayed users list, however the new user was found in database.");
            } else {
                $this->test->fail("FAIL, User with name $username not found in displayed users list and not found in the database.");
            }
        }
        $this->client->switchTo()->defaultContent();

        // assert that new user is in database
        $usernameDatabase = sqlQuery("SELECT `username` FROM `users` WHERE `username` = ?", [$username]);
        $this->test->assertSame(($usernameDatabase['username'] ?? ''), $username);
    }

    private function switchToIFrame($selector)
    {
        $iframe = $this->client->findElement($selector);
        $this->client->switchTo()->frame($iframe);
        $crawler = $this->client->refreshCrawler();
        return $crawler;
    }
}
