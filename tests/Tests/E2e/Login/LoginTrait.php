<?php

/**
 * LoginTrait trait
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

namespace OpenEMR\Tests\E2e\Login;

use OpenEMR\Tests\E2e\Base\BaseTrait;
use OpenEMR\Tests\E2e\Login\LoginTestData;

trait LoginTrait
{
    use BaseTrait;

    public function testLoginAuthorized(): void
    {
        $this->base();
        try {
            $this->login(LoginTestData::username, LoginTestData::password);
        } catch (\Throwable $e) {
            // Close client
            $this->client->quit();
            // re-throw the exception
            throw $e;
        }
        // Close client
        $this->client->quit();
    }

    private function login(string $name, string $password, bool $goalPass = true): void
    {
        // login
        $this->crawler = $this->client->request('GET', '/interface/login/login.php?site=default&testing_mode=1');
        $form = $this->crawler->filter('#login_form')->form();
        $form['authUser'] = $name;
        $form['clearPass'] = $password;
        $this->crawler = $this->client->submit($form);
        $title = $this->client->getTitle();
        if ($goalPass) {
            $this->assertSame('OpenEMR', $title, 'Login FAILED');
            // Wait for the JavaScript application to fully initialize
            // (Knockout.js bindings applied, menu rendered). Without
            // this, tests that immediately navigate menus or click UI
            // elements can fail because the page HTML loaded but the
            // JS framework hasn't finished rendering.
            $this->waitForAppReady();
        } else {
            $this->assertSame('OpenEMR Login', $title, 'Login was successful, but should have FAILED');
        }
    }
}
