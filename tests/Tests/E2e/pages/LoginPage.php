<?php

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\pages;
use Symfony\Component\Panther\PantherTestCase;

class LoginPage extends PantherTestCase
{
    private $e2eBaseUrl;

    protected function setUp(): void
    {
        $this->e2eBaseUrl = getenv("OPENEMR_BASE_URL_E2E", true) ?: "http://localhost";
    }

    public function login($client, $test, $username = null, $password = null)
    {
        $password = is_null($password) ? 'pass' : $password;
        $username = is_null($username) ? 'admin' : $username;

        $crawler = $client->request('GET', '/interface/login/login.php?site=default');
        $form = $crawler->filter('#login_form')->form();
        $form['authUser'] = $username;
        $form['clearPass'] = $password;
        $crawler = $client->submit($form);
        self::assertTrue($client->isFollowingRedirects());

        return [$crawler, $client, $test];
    }
}