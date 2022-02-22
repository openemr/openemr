<?php

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\ui;
use Facebook\WebDriver\WebDriverBy;

class ModalTestElement
{
    public function focus($session, $id)
    {
        $crawler = $session[0];
        $client = $session[1];
        $iframe = "//*[@id='$id']";
        $client->switchTo()->defaultContent();
        $focus = $client->findElement(WebDriverBy::xpath($iframe));
        $client->switchTo()->frame($focus);
        $crawler = $client->refreshCrawler();
        
        return [$crawler, $client, null];
    }
}