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
        $crawler->filterXPath(UsersTab::ADD_USER_BUTTON)->click();

        $this->client->switchTo()->defaultContent();
        $crawler = $this->switchToIFrame(WebDriverBy::xpath(UsersTab::NEW_USER_IFRAME));
        $newUser = $crawler->filterXPath(UsersTab::NEW_USER_BUTTON)->form();

        $newUser['rumple'] = $username;
        $newUser['stiltskin'] = 'Test12te$t';
        $newUser['fname'] = 'Foo';
        $newUser['lname'] = 'Bar';
        $newUser['adminPass'] = 'pass';

        $crawler->filterXPath(UsersTab::CREATE_USER_BUTTON)->click();

        // this will wait for 5 seconds to avoid the intermittent timeout in line 56 below
        $this->client->wait(5);

        $this->client->switchTo()->defaultContent();
        $crawler = $this->switchToIFrame(WebDriverBy::xpath(UsersTab::ADMIN_IFRAME));

        $this->client->waitFor("//table//a[text()='$username']");

        $this->client->switchTo()->defaultContent();
    }

    public function assertUserPresent($username): void
    {
        try {
            $crawler = $this->switchToIFrame(WebDriverBy::xpath(UsersTab::ADMIN_IFRAME));

            try {
                // a bit of a hack here - exception will be thrown if we can't find the user, catch it and emit assertion fail
                $crawler->filterXPath("//table//a[text()='$username']")->getSize();
            } catch (\InvalidArgumentException $e) {
                $this->test->fail("User with name $username not found in users list");
            }
        } finally {
            $this->client->switchTo()->defaultContent();
        }
    }

    private function switchToIFrame($selector)
    {
        $iframe = $this->client->findElement($selector);
        $this->client->switchTo()->frame($iframe);
        $crawler = $this->client->refreshCrawler();
        return $crawler;
    }
}
