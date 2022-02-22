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

class AdministrationLanguageTabTest extends PantherTestCase
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
    public function testAdministrationLanguageAddLanguageTabOpensLanguageForm()
    {
        $session = $this->start();
        (new TabTestElement)->open($session, 'Administration', 'System', 'Language');
        $session = (new TabTestElement)->focus($session, 'adm');
        (new ButtonTestElement)->clickByText($session, 'Add Language');
        $session = (new PageTestElement)->focusDefault($session);
        (new FormTestElement)->isDisplayed($session, 'lang_form');
        $session[1]->quit();
    }

    /** @test */
    public function testAdministrationLanguageAddLanguageTabOpensConstantForm()
    {
        $session = $this->start();
        (new TabTestElement)->open($session, 'Administration', 'System', 'Language');
        $session = (new TabTestElement)->focus($session, 'adm');
        (new ButtonTestElement)->clickByText($session, 'Add Constant');
        $session = (new PageTestElement)->focusDefault($session);
        (new FormTestElement)->isDisplayed($session, 'cons_form');
        $session[1]->quit();
    }
}