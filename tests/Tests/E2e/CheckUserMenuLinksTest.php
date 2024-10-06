<?php

/**
 * CheckUserMenuLinksTest class
 *
 * @package OpenEMR
 * @link    https://www.open-emr.org
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2024 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e;

use OpenEMR\Tests\E2e\Base\BaseTrait;
use OpenEMR\Tests\E2e\Login\LoginTrait;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Panther\Client;

class CheckUserMenuLinksTest extends PantherTestCase
{
    use BaseTrait;
    use LoginTrait;

    protected $client;
    protected $crawler;

    /**
     * @dataProvider menuLinkProvider
     * @depends testLoginAuthorized
     */
    public function testCheckUserMenuLink(string $menutreeicon, string $menuLinkItem, string $expectedTabTitle): void
    {
        $this->base();
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
                $this->assertActiveTab($expectedTabTitle);
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
}
