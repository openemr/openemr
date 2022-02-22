<?php

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\tests;
use Symfony\Component\Panther\PantherTestCase;
use OpenEMR\Tests\E2e\pages\{LoginPage};
use OpenEMR\Tests\E2e\ui\
    {
        TabTestElement, 
        ButtonTestElement, 
        ModalTestElement,
    };

class AdministrationAddressBookTabTest extends PantherTestCase
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
    public function testAddCalendarEventOpensForm()
    {
        $session = $this->start();
        (new TabTestElement)->open($session, 'Administration', 'Address Book');
        $session = (new TabTestElement)->focus($session, 'adm');
        (new ButtonTestElement)->clickByValue($session, 'Add New');
        (new ModalTestElement)->focus($session, 'modalframe');
        $session[1]->quit();
    }

}