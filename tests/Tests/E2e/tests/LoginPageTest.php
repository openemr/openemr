<?php

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\tests;
use Symfony\Component\Panther\PantherTestCase;
use OpenEMR\Tests\E2e\pages\{LoginPage};

class LoginPageTest extends PantherTestCase
{
    private $e2eBaseUrl;

    protected function setUp(): void
    {
        $this->e2eBaseUrl = getenv("OPENEMR_BASE_URL_E2E", true) ?: "http://localhost";
    }

    public function start()
    {
        return static::createPantherClient(['external_base_uri' => $this->e2eBaseUrl]);
    }

    /** @test */
    public function testLoginTitle(): void       
    {
        $start = $this->start();
        $start->request('GET', '/interface/login/login.php?site=default');
        $title = $start->getTitle();
        $this->assertSame('OpenEMR Login', $title);
        $start->quit();
    }

    /** @test */
    public function testValidCredentialsIsAuthenticated(): void
    {
        $start = $this->start();
        $session = (new LoginPage)->login($start, $this);
        $client = $session[1];
        $title = $client->getTitle();
        $this->assertSame('OpenEMR', $title);
        $client->quit();
    }

    /** @test */
    public function testInvalidCredentialsIsNotAuthenticated(): void
    {
        $start = $this->start();
        $session = (new LoginPage)->login($start, $this, 'incorrect_username', 'incorrect_password');
        $client = $session[1];
        $title = $client->getTitle();
        $this->assertSame('OpenEMR Login', $title);
        $client->quit();
    }
}