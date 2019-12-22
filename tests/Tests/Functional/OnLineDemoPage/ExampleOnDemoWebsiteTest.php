<?php
declare(strict_types=1);
namespace OpenEMR\Tests\Functional\OnLineDemoPage;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Panther\Client;
class ExampleOnDemoWebsiteTest extends PantherTestCase
{
    /** @test */
    public function check_openEmr_demo_page(): void
    {
        $openEmrDemoPage = 'https://demo.openemr.io/openemr';
        // ok - PantherClient
        $client = static::createPantherClient(['external_base_uri' => $openEmrDemoPage]);
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
    public function url_without_token_should_redirect_to_login_page(): void
    {
        $openEmrDemoPage = 'https://demo.openemr.io/openemr';
        $client = static::createPantherClient(['external_base_uri' => $openEmrDemoPage]);
        $crawler = $client->request('GET', '/interface/main/tabs/main.php');
        self::assertTrue($client->isFollowingRedirects());
        // TITLE
        $title = $client->getTitle();
        $this->assertSame('OpenEMR Login', $title);
    }
    /** @test */
    public function visitor_with_valid_credential_can_be_authenticated(): void
    {
        $openEmrDemoPage = 'https://demo.openemr.io/openemr';
        // ok - PantherClient
        $client = static::createPantherClient(['external_base_uri' => $openEmrDemoPage]);
        $crawler = $client->request('GET', '/interface/login/login.php?site=default');
        $form = $crawler->filter('#login_form')->form();
        $form['authUser'] = 'admin';
        $form['clearPass'] = 'pass';
        $crawler = $client->submit($form);
        self::assertTrue($client->isFollowingRedirects());
        // TODO
        // page after login = https://demo.openemr.io/openemr/interface/main/tabs/main.php?token_main=6FjVhEmDA2jaIi9zSkSRt5BHISwJPTbDDHaP3kAt
        // write an assert un that page url
        // use assertEquals o assertContains
        // echo $client->getCurrentURL();
    }
}

