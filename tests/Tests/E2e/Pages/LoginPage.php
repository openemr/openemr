<?php

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\Pages;

use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Panther\Client;

class LoginPage
{

    private $crawler;
    private $client;
    private $test;

    public function __construct($client, $test)
    {
        $this->client = $client;
        $this->test = $test;
    }

    public function login($username, $password): MainPage
    {
        $this->crawler = $this->client->request('GET', '/interface/login/login.php?site=default');

        $form = $this->crawler->filter('#login_form')->form();
        $form['authUser'] = $username;
        $form['clearPass'] = $password;
        $crawler = $this->client->submit($form);
        $this->test::assertTrue($this->client->isFollowingRedirects());
        $title = $this->client->getTitle();
        $this->test->assertSame('OpenEMR', $title);

        return new MainPage($crawler, $this->client, $this->test);
    }
}
