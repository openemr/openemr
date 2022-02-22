<?php

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\tests;
use Symfony\Component\Panther\PantherTestCase;
use OpenEMR\Tests\E2e\pages\{LoginPage};
use OpenEMR\Tests\E2e\ui\
    {
        TabTestElement, 
        ButtonTestElement,
        FormTestElement,
        PageTestElement,
};

class MiscellaneousBatchCommunicationToolTabTest extends PantherTestCase
{
    private $e2eBaseUrl;

    protected function setUp(): void
    {
        $this->e2eBaseUrl = getenv('OPENEMR_BASE_URL_E2E', true) ?: 'http://localhost';
    }

    public function start()
    {      
        $driver = static::createPantherClient(['external_base_uri' => $this->e2eBaseUrl]);

        return (new LoginPage)->login($driver, $this);
    }

    /** @test */
    public function testSMSOnMiscellaneousBCTTabOpensSMSTab()
    {
        $session = $this->start();
        (new TabTestElement)->open($session, 'Miscellaneous', 'Batch Communication Tool');
        $session = (new TabTestElement)->focus($session, 'msc');
        (new ButtonTestElement)->clickByTitle($session, 'SMS Notification');
        $session = (new PageTestElement)->focusDefault($session);
        (new FormTestElement)->isDisplayed($session, 'select_form');
        $session[1]->quit();
    }

    /** @test */
    public function testEmailNotificationOnMiscellaneousBCTTabOpensEmailNotificationsTab()
    {
        $session = $this->start();
        (new TabTestElement)->open($session, 'Miscellaneous', 'Batch Communication Tool');
        $session = (new TabTestElement)->focus($session, 'msc');
        (new ButtonTestElement)->clickByTitle($session, 'Email Notification');
        $session = (new PageTestElement)->focusDefault($session);
        (new FormTestElement)->isDisplayed($session, 'select_form');
        $session[1]->quit();
    }
    /** @test */
    public function testSMSEmailAlertSettingsOnMiscellaneousBCTTabOpensNotificationsTab()
    {
        $session = $this->start();
        (new TabTestElement)->open($session, 'Miscellaneous', 'Batch Communication Tool');
        $session = (new TabTestElement)->focus($session, 'msc');
        (new ButtonTestElement)->clickByTitle($session, 'SMS/Email Alert Settings');
        $session = (new PageTestElement)->focusDefault($session);
        (new FormTestElement)->isDisplayed($session, 'select_form');
        $session[1]->quit();
    }
}