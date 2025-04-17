<?php

/**
 * AaLoginTest class
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @auther    zerai
 * @author    Dixon Whitmire
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 zerai
 * @copyright Copyright (c) 2020 Dixon Whitmire
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

class AaLoginTest extends PantherTestCase
{
    use BaseTrait;
    use LoginTrait;

    private $client;
    private $crawler;

    public function testGoToOpenemrLoginPage(): void
    {
        $this->base();
        try {
            $this->loginPage();
        } catch (\Throwable $e) {
            // Close client
            $this->client->quit();
            // re-throw the exception
            throw $e;
        }
        // Close client
        $this->client->quit();
    }

    public function testLoginUnauthorized(): void
    {
        $this->base();
        try {
            $this->login(LoginTestData::username, LoginTestData::password . "1", false);
        } catch (\Throwable $e) {
            // Close client
            $this->client->quit();
            // re-throw the exception
            throw $e;
        }
        // Close client
        $this->client->quit();
    }

    /** @test */
    public function testurlWithoutTokenShouldRedirectToLoginPage(): void
    {
        $this->base();
        try {
            $this->crawler = $this->client->request('GET', '/interface/main/tabs/main.php?site=default');
            $title = $this->client->getTitle();
            $this->assertSame('OpenEMR Login', $title, 'FAILED to redirect to login page');
        } catch (\Throwable $e) {
            // Close client
            $this->client->quit();
            // re-throw the exception
            throw $e;
        }
        // Close client
        $this->client->quit();
    }

    private function loginPage(): void
    {
        $this->crawler = $this->client->request('GET', '/interface/login/login.php?site=default');
        $title = $this->client->getTitle();
        $this->assertSame('OpenEMR Login', $title, 'FAILED to show login page');
    }
}
