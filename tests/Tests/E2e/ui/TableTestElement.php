<?php

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\ui;

class TableTestElement
{
    public function find($session, $text)
    {
        $client = $session[1];  
        $client->waitFor("//table//a[text()='$text']");
    }

    public function click($session, $text)
    {
        $crawler = $session[0];
        $client = $session[1];
        $link = "//table//a[text()='$text']";   
        $client->waitFor($link);
        $crawler->filterXPath($link)->click();
    }
}