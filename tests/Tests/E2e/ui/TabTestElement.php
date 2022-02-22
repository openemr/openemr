<?php

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\ui;
use Facebook\WebDriver\WebDriverBy;

class TabTestElement
{
    public function open($session, ...$menuItems)
    {
        $crawler = $session[0];
        $client = $session[1];
        (new NavbarTestElement)->hamburger($session);

        for ($i = 0; $i < count($menuItems); $i++) {
            (new NavbarTestElement)->click($i, $menuItems, $crawler, $client);
        }
    }
    
    public function isActive($session, $name)
    {
        $crawler = $session[0];
        $test = $session[2];      
        $activeTab = "//div[@id='tabs_div']/div/div[not(contains(concat(' ',normalize-space(@class),' '),' tabsNoHover '))]";

        $startTime = (int) (microtime(true) * 1000);
        while ("Loading..." == $crawler->filterXPath($activeTab)->text()) {
            if (($startTime + 5000) < ((int) (microtime(true) * 1000))) {
                $test->fail("Timeout waiting for tab [$name]");
            }
            usleep(100);
        }

        $test->assertSame($name, $crawler->filterXPath($activeTab)->text());
    }

    public function focus($session, $name)
    {
        $crawler = $session[0];
        $client = $session[1];
        $iframe= "//*[@id='framesDisplay']//iframe[@name='$name']";
        $focus = $client->findElement(WebDriverBy::xpath($iframe));
        $client->switchTo()->frame($focus);
        $crawler = $client->refreshCrawler();
        
        return [$crawler, $client, null];
    }

    public function click($session, $name)
    {
        $crawler = $session[0];
        $client = $session[1];
        $tab = "//span[text()='$name']";
        $client->waitFor($tab);
        $crawler->filterXPath($tab)->click();
    }

    public function isDisplayed($session, $name)
    {
        $crawler = $session[0];

        try {
            $crawler->filterXPath("//*[@id='framesDisplay']//iframe[@name='$name']")->isDisplayed();
        } catch (\Exception $e) {
            return false;
        }
    }
}