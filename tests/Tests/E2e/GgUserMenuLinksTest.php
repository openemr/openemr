<?php

/**
 * GgUserMenuLinksTest class
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2024 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e;

use OpenEMR\Tests\E2e\Base\BaseTrait;
use OpenEMR\Tests\E2e\Login\LoginTestData;
use OpenEMR\Tests\E2e\Login\LoginTrait;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Panther\Client;

class GgUserMenuLinksTest extends PantherTestCase
{
    use BaseTrait;
    use LoginTrait;

    private $client;
    private $crawler;

    /**
     * @dataProvider menuLinkProvider
     * @depends testLoginAuthorized
     * need above so this complicated test is not considered risky
     */
    public function testUserMenuLink(string $menuTreeIcon, string $menuLinkItem, string $expectedTabTitle): void
    {
        $counter = 0;
        $threwSomething = true;
        // below will basically allow 3 timeouts
        while ($threwSomething) {
            $threwSomething = false;
            $counter++;
            if ($counter > 1) {
                echo "\n" . "RE-attempt (" . $menuTreeIcon . ") number " . $counter . " of 3" . "\n";
            }
            $this->base();
            try {
                $this->login(LoginTestData::username, LoginTestData::password);
                if ($menuLinkItem == 'Logout') {
                    // special case for Logout
                    $this->logOut();
                } else {
                    $this->goToUserMenuLink($menuTreeIcon);
                    $this->assertActiveTab($expectedTabTitle);
                }
            } catch (\Throwable $e) {
                // Close client
                $this->client->quit();
                if ($counter > 2) {
                    // re-throw since have failed 3 tries
                    throw $e;
                } else {
                    // try again since not yet 3 tries
                    $threwSomething = true;
                }
            }
            // Close client
            $this->client->quit();
        }
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
