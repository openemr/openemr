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
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Panther\Client;

class AaLoginTest extends PantherTestCase
{
    use BaseTrait;
    use LoginTrait;

    private $client;
    private $crawler;

    #[Test]
    public function testGoToOpenemrLoginPage(): void
    {
        $this->base();
        try {
            $this->annotateVideo('TEST START: Login Page Load', 2000, '#4CAF50');
            $this->loginPage();
            $this->annotateVideo('TEST COMPLETE: Login page verified', 2000, '#4CAF50');
        } catch (\Throwable $e) {
            $this->annotateVideo('TEST FAILED: ' . $e->getMessage(), 3000, '#F44336');
            // Close client
            $this->client->quit();
            // re-throw the exception
            throw $e;
        }
        // Close client
        $this->client->quit();
    }

    #[Test]
    public function testLoginUnauthorized(): void
    {
        $this->base();
        try {
            $this->annotateVideo('TEST START: Login Unauthorized', 2000, '#4CAF50');
            $this->login(LoginTestData::username, LoginTestData::password . "1", false);
            $this->annotateVideo('TEST COMPLETE: Unauthorized login rejected', 2000, '#4CAF50');
        } catch (\Throwable $e) {
            $this->annotateVideo('TEST FAILED: ' . $e->getMessage(), 3000, '#F44336');
            // Close client
            $this->client->quit();
            // re-throw the exception
            throw $e;
        }
        // Close client
        $this->client->quit();
    }

    #[Test]
    public function testurlWithoutTokenShouldRedirectToLoginPage(): void
    {
        $this->base();
        try {
            $this->annotateVideo('TEST START: URL Redirect to Login', 2000, '#4CAF50');
            $this->crawler = $this->client->request('GET', '/interface/main/tabs/main.php?site=default&testing_mode=1');
            $title = $this->client->getTitle();
            $this->assertSame('OpenEMR Login', $title, 'FAILED to redirect to login page');
            $this->annotateVideo('TEST COMPLETE: Redirect verified', 2000, '#4CAF50');
        } catch (\Throwable $e) {
            $this->annotateVideo('TEST FAILED: ' . $e->getMessage(), 3000, '#F44336');
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
        $this->crawler = $this->client->request('GET', '/interface/login/login.php?site=default&testing_mode=1');
        $title = $this->client->getTitle();
        $this->assertSame('OpenEMR Login', $title, 'FAILED to show login page');
    }
}
