<?php

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\ui;

class ButtonTestElement
{
    public function clickById($session, $id)
    {
        $crawler = $session[0];
        $client = $session[1];
        $button = "//*[@id='$id']";
        $client->waitFor($button);
        $crawler->filterXPath($button)->click();
    }

    public function clickByText($session, $text)
    {
        $crawler = $session[0];
        $client = $session[1];
        $button = "//*[text()='$text']";
        $client->waitFor($button);
        $crawler->filterXPath($button)->click();
    }

    public function clickByTitle($session, $title)
    {
        $crawler = $session[0];
        $client = $session[1];
        $button = "//*[@title='$title']";
        $client->waitFor($button);
        $crawler->filterXPath($button)->click();
    }

    public function clickByValue($session, $value)
    {
        $crawler = $session[0];
        $client = $session[1];
        $button = "//*[@value='$value']";
        $client->waitFor($button);
        $crawler->filterXPath($button)->click();
    }
}