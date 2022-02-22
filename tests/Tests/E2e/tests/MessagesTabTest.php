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

class MessagesTabTest extends PantherTestCase
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
        (new TabTestElement)->open($session, 'Messages');
        $session = (new TabTestElement)->focus($session, 'msg');
        (new ButtonTestElement)->clickByText($session, 'Add New');
        $session = (new PageTestElement)->focusDefault($session);
        (new FormTestElement)->isDisplayed($session, 'new_note');
        $session[1]->quit();
    }
}