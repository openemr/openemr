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

    /**
     * @dataProvider menuLinkProvider
     */
    public function testCheckUserLink(string $menutreeicon, string $menuLinkItem, string $expectedTabTitle): void
    {
        $openEmrPage = $this->e2eBaseUrl;
        $client = static::createPantherClient(['external_base_uri' => $openEmrPage]);
        $client->manage()->window()->maximize();
        try {
            // login
            $crawler = $client->request('GET', '/interface/login/login.php?site=default');
            $form = $crawler->filter('#login_form')->form();
            $form['authUser'] = 'admin';
            $form['clearPass'] = 'pass';
            $crawler = $client->submit($form);

            // got to and click the user menu link
            $menuLink = '//i[@id="user_icon"]';
            $menuLink2 = '//ul[@id="userdropdown"]//i[contains(@class, "' . $menutreeicon . '")]';
            $client->waitFor($menuLink);
            $crawler = $client->refreshCrawler();
            $crawler->filterXPath($menuLink)->click();
            $client->waitFor($menuLink2);
            $crawler = $client->refreshCrawler();
            $crawler->filterXPath($menuLink2)->click();

            // wait for the tab title to be shown
            if ($menuLinkItem == 'Logout') {
                // special case for Logout
                $client->waitFor('//input[@id="authUser"]');
                $title = $client->getTitle();
                $this->assertSame('OpenEMR Login', $title);
            } else {
                $client->waitForElementToContain("//div[@id='tabs_div']/div/div[not(contains(concat(' ',normalize-space(@class),' '),' tabsNoHover '))]", $expectedTabTitle);
                // Perform the final assertion
                $this->assertSame($expectedTabTitle, $crawler->filterXPath("//div[@id='tabs_div']/div/div[not(contains(concat(' ',normalize-space(@class),' '),' tabsNoHover '))]")->text());
            }
        } catch (\Throwable $e) {
            // Close client
            $client->quit();
            // re-throw the exception
            throw $e;
        }
        // Close client
        $client->quit();
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
}
