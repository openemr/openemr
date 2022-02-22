<?php

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\ui;
use Symfony\Component\Panther\PantherTestCase;

class NavbarTestElement extends PantherTestCase
{
    public function hamburger($session)
    {
        $crawler = $session[0];   
        $hamburger = '//div[@id="mainBox"]/nav/button[@data-target="#mainMenu"]';

        try {
            $crawler->filterXPath($hamburger)->click();
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function click($index, $menuItems, $crawler = null, $client = null)
    {
        if ($index == 0) {
            $item = "//div[@id='mainMenu']//div[text()='$menuItems[0]']";
        } else if ($index == 1) {
            $item = "//div[@id='mainMenu']//div[text()='$menuItems[0]']/following-sibling::ul[1]/li//div[text()='$menuItems[1]']";
        } else {
            $item = "//div[@id='mainMenu']//div[text()='$menuItems[0]']/following-sibling::ul[1]/li//div[text()='$menuItems[1]']/following-sibling::ul[1]//div[text()='$menuItems[2]']";
        }

        $client->waitFor($item);
        $crawler->filterXPath($item)->click();
    }
}