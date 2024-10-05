<?php

declare(strict_types=1);

namespace OpenEMR\Tests\E2e;

use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Panther\Client;

class CheckUserMenuLinksTest extends PantherTestCase
{
    /**
     * The base url used for e2e (end to end) browser testing.
     */
    private $e2eBaseUrl;

    protected function setUp(): void
    {
        $this->e2eBaseUrl = getenv("OPENEMR_BASE_URL_E2E", true) ?: "http://localhost";
    }

    public function testLogin(): void
    {
        $openEmrPage = $this->e2eBaseUrl;
        $this->client = static::createPantherClient(['external_base_uri' => $openEmrPage]);
        $this->client->manage()->window()->maximize();
        try {
            $this->login('admin', 'pass');
        } catch (\Throwable $e) {
            // Close client
            $this->client->quit();
            // re-throw the exception
            throw $e;
        }
        // Close client
        $this->client->quit();
    }

    /**
     * @dataProvider menuLinkProvider
     * @depends testLogin
     */
    public function testCheckUserMenuLink(string $menutreeicon, string $menuLinkItem, string $expectedTabTitle): void
    {
        $openEmrPage = $this->e2eBaseUrl;
        $this->client = static::createPantherClient(['external_base_uri' => $openEmrPage]);
        $this->client->manage()->window()->maximize();
        try {
            $this->login('admin', 'pass');
            // got to and click the user menu link
            $menuLink = '//i[@id="user_icon"]';
            $menuLink2 = '//ul[@id="userdropdown"]//i[contains(@class, "' . $menutreeicon . '")]';
            $this->client->waitFor($menuLink);
            $this->crawler = $this->client->refreshCrawler();
            $this->crawler->filterXPath($menuLink)->click();
            $this->client->waitFor($menuLink2);
            $this->crawler = $this->client->refreshCrawler();
            $this->crawler->filterXPath($menuLink2)->click();

            // wait for the tab title to be shown
            if ($menuLinkItem == 'Logout') {
                // special case for Logout
                $this->client->waitFor('//input[@id="authUser"]');
                $title = $this->client->getTitle();
                $this->assertSame('OpenEMR Login', $title, 'Logout FAILED');
            } else {
                $this->client->waitForElementToContain("//div[@id='tabs_div']/div/div[not(contains(concat(' ',normalize-space(@class),' '),' tabsNoHover '))]", $expectedTabTitle);
                // Perform the final assertion
                $this->assertSame($expectedTabTitle, $this->crawler->filterXPath("//div[@id='tabs_div']/div/div[not(contains(concat(' ',normalize-space(@class),' '),' tabsNoHover '))]")->text(), 'Page load FAILED');
            }
        } catch (\Throwable $e) {
            // Close client
            $this->client->quit();
            // re-throw the exception
            throw $e;
        }
        // Close client
        $this->client->quit();
    }

    public static function menuLinkProvider()
    {
        return [
            'Settings user menu link' => ['fa-cog', 'Settings', 'User Settings'],
            'Change Password user menu link' => ['fa-lock', 'Change Password', 'Change Password'],
            'MFA Management user menu link' => ['fa-key', 'MFA Management', 'Manage Multi Factor Authentication'],
            'About OpenEMR user menu link' => ['fa-info', 'About OpenEMR', 'About OpenEMR'],
            'Logout user menu link' => ['fa-sign-out-alt', 'Logout', 'OpenEMR Login']
        ];
    }

    private function login(string $name, string $password): void
    {
        // login
        $this->crawler = $this->client->request('GET', '/interface/login/login.php?site=default');
        $form = $this->crawler->filter('#login_form')->form();
        $form['authUser'] = $name;
        $form['clearPass'] = $password;
        $this->crawler = $this->client->submit($form);
        $title = $this->client->getTitle();
        $this->assertSame('OpenEMR', $title, 'Login FAILED');
    }

}
