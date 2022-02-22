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

class RecallTabTest extends PantherTestCase
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
    public function testNewRecallOpensNewRecallTab()
    {
        $session = $this->start();
        (new TabTestElement)->open($session, 'Recall Board');
        $session = (new TabTestElement)->focus($session, 'rcb');
        (new ButtonTestElement)->clickByText($session, 'New Recall');
        $session = (new PageTestElement)->focusDefault($session);
        (new TabTestElement)->isDisplayed($session, 'New Recall');
        $session[1]->quit();
    }
}