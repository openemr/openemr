<?php

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\ui;

class PageTestElement
{
    public function refresh($session)
    {
        $client = $session[1];
        $client->navigate()->refresh();
        $client->switchTo()->alert()->dismiss();
        $client->switchTo()->defaultContent();
    }

    public function focusDefault($session)
    {
        $crawler = $session[0];
        $client = $session[1];
        $client->switchTo()->defaultContent();

        return [$crawler, $client, null];
    }
}