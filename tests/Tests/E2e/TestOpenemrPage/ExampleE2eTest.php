<?php

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\TestOpenemrPage;

use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Panther\Client;

class ExampleE2eTest extends PantherTestCase
{
    /**
     * The base url used for e2e (end to end) browser testing.
     */
    private $e2eBaseUrl;

    protected function setUp(): void
    {
        $this->e2eBaseUrl = getenv("OPENEMR_BASE_URL_E2E", true) ?: "http://localhost";
    }

    /** @test */
    public function check_openEmr_login_page(): void
    {
        // ok - PantherClient
        $client = static::createPantherClient(['external_base_uri' => $this->e2eBaseUrl]);
        // ok - GoutteClient -> Goutte is not installed. Run "composer req fabpot/goutte".
        //$goutteClient = static::createGoutteClient();
        // ok ChromeClient
        //$client = Client::createChromeClient(null, null, [], $openEmrDemoPage);
        // not tested customSeleniumClient
        //$client = Client::createSeleniumClient('http://127.0.0.1:4444/wd/hub', null, $openEmrDemoPage); // Create a custom Selenium client
        // no - only for Symfony's functional test
        //$client = static::createClient(['external_base_uri' => $openEmrDemoPage]);
        $crawler = $client->request('GET', '/interface/login/login.php?site=default');
        // TITLE
        $title = $client->getTitle();
        $this->assertSame('OpenEMR Login', $title);
    }
    /** @test */
    /** TEMP REMOVING THIS TEST UNTIL FIX WHY IT IS ERRATICALLY NOT PASSING IN TRAVIS
    public function url_without_token_should_redirect_to_login_page(): void
    {
        $client = static::createPantherClient(['external_base_uri' => $this->e2eBaseUrl]);
        $crawler = $client->request('GET', '/interface/main/tabs/main.php');
        self::assertTrue($client->isFollowingRedirects());
        // TITLE
        $title = $client->getTitle();
        $this->assertSame('OpenEMR Login', $title);
    }
     */
    /** @test */
    public function visitor_with_valid_credential_can_be_authenticated(): void
    {
        $openEmrPage = 'http://localhost';
        // ok - PantherClient
        $client = static::createPantherClient(['external_base_uri' => $openEmrPage]);
        $crawler = $client->request('GET', '/interface/login/login.php?site=default');

        $form = $crawler->filter('#login_form')->form();
        $form['authUser'] = 'admin';
        $form['clearPass'] = 'pass';
        $crawler = $client->submit($form);
        self::assertTrue($client->isFollowingRedirects());
        $title = $client->getTitle();
        $this->assertSame('OpenEMR', $title);
    }
    /** @test */
    public function visitor_without_valid_credential_is_not_authenticated(): void
    {
        $openEmrPage = 'http://localhost';
        // ok - PantherClient
        $client = static::createPantherClient(['external_base_uri' => $openEmrPage]);
        $crawler = $client->request('GET', '/interface/login/login.php?site=default');

        $form = $crawler->filter('#login_form')->form();
        $form['authUser'] = 'admin';
        $form['clearPass'] = 'wrongpassword';
        $crawler = $client->submit($form);
        self::assertTrue($client->isFollowingRedirects());
        $title = $client->getTitle();
        $this->assertSame('OpenEMR Login', $title);
    }
}
