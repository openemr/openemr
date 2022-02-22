<?php

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\tests;
use Symfony\Component\Panther\PantherTestCase;
use OpenEMR\Tests\E2e\pages\{LoginPage};
use OpenEMR\Tests\E2e\ui\
    {
        TabTestElement, 
        ButtonTestElement,
        PageTestElement,
};

class AdministrationRulesTabTest extends PantherTestCase
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
    public function testGoOnAdministrationRulesTabOpensPlanRulesTab()
    {
        $session = $this->start();
        (new TabTestElement)->open($session, 'Administration', 'Practice', 'Rules');
        $session = (new TabTestElement)->focus($session, 'adm');
        (new ButtonTestElement)->clickByText($session, 'Go');
        $session = (new PageTestElement)->focusDefault($session);
        (new TabTestElement)->isDisplayed($session, 'View Plan Rules');
        $session[1]->quit();
    }

    /** @test */
    public function testAddNewOnAdministrationRulesTabOpensRuleAddTab()
    {
        $session = $this->start();
        (new TabTestElement)->open($session, 'Administration', 'Practice', 'Rules');
        $session = (new TabTestElement)->focus($session, 'adm');
        (new ButtonTestElement)->clickByText($session, 'Add new');
        $session = (new PageTestElement)->focusDefault($session);
        (new TabTestElement)->isDisplayed($session, 'Rule Add');
        $session[1]->quit();
    }

    /** @test */
    public function testCancelOnAdministrationRulesTabOpensRuleAddTab()
    {
        $session = $this->start();
        (new TabTestElement)->open($session, 'Administration', 'Practice', 'Rules');
        $session = (new TabTestElement)->focus($session, 'adm');
        (new ButtonTestElement)->clickByText($session, 'Add new');
        $session = (new PageTestElement)->focusDefault($session);
        (new TabTestElement)->isDisplayed($session, 'Rule Add');
        (new PageTestElement)->refresh($session);
        $session = (new TabTestElement)->focus($session, 'adm');
        (new ButtonTestElement)->clickByText($session, 'Cancel');
        (new TabTestElement)->isDisplayed($session, 'Plans Configuration');
        $session[1]->quit();
    }
}