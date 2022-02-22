<?php

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\ui;
use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\{WebDriverExpectedCondition};

class AlertTestElement extends TestCase
{
    public function messageIs($session, $expectedMessage)
    {
        $client = $session[1];
        $client->wait()->until(WebDriverExpectedCondition::alertIsPresent());
        $alertMessage = $client->switchTo()->alert()->getText();
        $this->assertStringContainsString($expectedMessage, $alertMessage);
        $client->switchTo()->alert()->dismiss();
    }
}