<?php

/**
 * AaLoginTest class
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    zerai
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
use Symfony\Component\Process\Process;

class AaLoginTest extends PantherTestCase
{
    use BaseTrait;
    use LoginTrait;

    private $crawler;

    #[Test]
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

    #[Test]
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

    #[Test]
    public function testurlWithoutTokenShouldRedirectToLoginPage(): void
    {
        $this->base();
        try {
            $this->crawler = $this->client->request('GET', '/interface/main/tabs/main.php?site=default&testing_mode=1');
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

    #[Test]
    public function testAdminPageDisabledByDefault(): void
    {
        $this->base();
        try {
            $this->client->request('GET', '/admin.php');
            $source = $this->client->getPageSource();
            $this->assertStringContainsString(
                'admin.php is disabled by default',
                $source,
                'admin.php should return the disabled-by-default message when no env var is set'
            );
            $title = $this->client->getTitle();
            $this->assertNotSame(
                'OpenEMR Site Administration',
                $title,
                'admin.php dashboard should NOT render without the enable env var'
            );
        } catch (\Throwable $e) {
            $this->client->quit();
            throw $e;
        }
        $this->client->quit();
    }

    #[Test]
    public function testAdminPageEnabledWithEnvVar(): void
    {
        $openemrRoot = dirname(__DIR__, 3);
        $process = new Process(
            ['php', $openemrRoot . '/admin.php'],
            null,
            ['OPENEMR_ADMIN_PHP_ENABLED' => '1']
        );
        $process->run();
        $this->assertTrue(
            $process->isSuccessful(),
            'php admin.php CLI invocation should succeed'
        );
        $output = $process->getOutput();
        $this->assertStringContainsString(
            'OpenEMR Site Administration',
            $output,
            'admin.php dashboard should render when OPENEMR_ADMIN_PHP_ENABLED=1 is set'
        );
        $this->assertStringNotContainsString(
            'disabled by default',
            $output,
            'admin.php should not emit the disabled-by-default message when env var is set'
        );
    }

    private function loginPage(): void
    {
        $this->crawler = $this->client->request('GET', '/interface/login/login.php?site=default&testing_mode=1');
        $title = $this->client->getTitle();
        $this->assertSame('OpenEMR Login', $title, 'FAILED to show login page');
    }
}
