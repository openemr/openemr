<?php

/**
 * HhMainMenuLinksTest class
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2024 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc. <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e;

use OpenEMR\Tests\E2e\Base\BaseTrait;
use OpenEMR\Tests\E2e\Login\LoginTestData;
use OpenEMR\Tests\E2e\Login\LoginTrait;
use OpenEMR\Tests\E2e\NavBar\NavBarTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Panther\PantherTestCase;

class HhMainMenuLinksTest extends PantherTestCase
{
    use BaseTrait;
    use LoginTrait;
    use NavBarTrait;

    private $crawler;

    #[DataProvider('menuLinkProvider')]
    #[Depends('testLoginAuthorized')]
    #[Test]
    public function testMainMenuLink(string $menuLink, string $expectedTabTitle, ?string $loading = ''): void
    {
        if ($expectedTabTitle == "Care Coordination" && !empty(getenv('UNABLE_SUPPORT_OPENEMR_NODEJS', true) ?? '')) {
            // Care Coordination page check will be skipped since this flag is set (which means the environment does not have
            //  a high enough version of nodejs)
            $this->markTestSkipped('Test skipped because this environment does not support high enough nodejs version.');
        }

        if (empty($loading)) {
            $loading = "Loading";
        }

        $this->base();
        try {
            $this->login(LoginTestData::username, LoginTestData::password);
            $this->goToMainMenuLink($menuLink);
            $this->assertActiveTab($expectedTabTitle, $loading);
        } catch (\Throwable $e) {
            // Close client
            $this->client->quit();
            // re-throw the exception
            throw $e;
        }
        // Close client
        $this->client->quit();
    }
}
