<?php

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\Pages;

use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Panther\Client;

class MainPage
{
    private const COLLAPSED_MENU_BUTTON = '//div[@id="mainBox"]/nav/button[@data-target="#mainMenu"]';
    private const ADMINISTRATION_MENU = '//div[@id="mainMenu"]//div[text()="Administration"]';
    private const USERS_SUBMENU = '//div[@id="mainMenu"]//div[text()="Users"]';

    private const ACTIVE_TAB = "//div[@id='tabs_div']/div/div[not(contains(concat(' ',normalize-space(@class),' '),' tabsNoHover '))]";

    private $crawler;
    private $client;
    private $test;

    public function __construct($crawler, $client, $test)
    {
        $this->crawler = $crawler;
        $this->client = $client;
        $this->test = $test;
    }

    public function openUsers(): void
    {
        if ($this->crawler->filterXPath(MainPage::COLLAPSED_MENU_BUTTON)->isDisplayed()) {
            $this->crawler->filterXPath(MainPage::COLLAPSED_MENU_BUTTON)->click();
        }

        $this->client->waitFor(MainPage::ADMINISTRATION_MENU);
        $this->crawler->filterXPath(MainPage::ADMINISTRATION_MENU)->click();

        $this->client->waitFor(MainPage::USERS_SUBMENU);
        $this->crawler->filterXPath(MainPage::USERS_SUBMENU)->click();
    }

    public function selectUsersTab(): UsersTab
    {
        // TODO: add clicking on tab if not selected yet, for now it's good enough
        return new UsersTab($this->crawler, $this->client, $this->test);
    }

    public function assertActiveTab($text)
    {
        $startTime = (int) (microtime(true) * 1000);
        while ("Loading..." == $this->crawler->filterXPath(MainPage::ACTIVE_TAB)->text()) {
            if (($startTime + 5000) < ((int) (microtime(true) * 1000))) {
                $this->test->fail("Timeout waiting for tab [$text]");
            }
            usleep(100);
        }
        $this->test->assertSame('User / Groups', $this->crawler->filterXPath(MainPage::ACTIVE_TAB)->text());
    }
}
