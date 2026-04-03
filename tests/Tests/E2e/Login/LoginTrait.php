<?php

/**
 * LoginTrait trait
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @author    Luis M. Santos, MD <lsantos@medicalmasses.com>
 * @copyright Copyright (c) 2024 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc. <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\Login;

use OpenEMR\Tests\E2e\Base\BaseTrait;

trait LoginTrait
{
    use BaseTrait;

    private string $mainScreenUrl = "/interface/main/tabs/main.php";
    private string $loginScreenUrl = "/interface/login/login.php";

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
        $this->performLogin($name, $password, $goalPass);

        if ($goalPass) {
            // Wait for the JavaScript application to fully initialize
            // (Knockout.js bindings applied, menu rendered). Without
            // this, tests that immediately navigate menus or click UI
            // elements can fail because the page HTML loaded but the
            // JS framework hasn't finished rendering.
            //
            // Use a short initial timeout to detect failure quickly.
            // If JS doesn't initialize within 5s, it's likely a CI
            // environment issue where scripts failed to load. In that
            // case, retry with a fresh session rather than waiting the
            // full 30s timeout.
            if (!$this->waitForAppReady(5)) {
                // JS failed to initialize - retry with fresh session
                fwrite(STDERR, "[E2E] JS failed to initialize after login, retrying with fresh session...\n");
                $this->client->quit();
                $this->base();
                $this->performLogin($name, $password, $goalPass);

                // Use longer timeout on retry - if this fails too,
                // it's probably a real issue, not a transient failure
                if (!$this->waitForAppReady(30)) {
                    throw $this->createAppReadyTimeoutException();
                }
            }
        }
    }

    /**
     * Runs a lambda after performing basic setup and login operations.
     *
     * Simplifies reusable logic seen elsewhere to minimize code duplication.
     *
     * @param $testFunction
     * @return void
     * @throws \Throwable
     */
    private function runTest($testFunction): void {
        $this->base();
        try {
            $this->login(LoginTestData::username, LoginTestData::password);
            $testFunction();
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
     * Submit the login form.
     */
    private function performLogin(string $name, string $password, bool $goalPass): void
    {
        $this->crawler = $this->client->request('GET', '/interface/login/login.php?site=default&testing_mode=1');
        $form = $this->crawler->filter('#login_form')->form();
        $form['authUser'] = $name;
        $form['clearPass'] = $password;
        $this->crawler = $this->client->submit($form);
        $url = $this->client->getCurrentURL();
        if ($goalPass) {
            $this->assertTrue(str_contains($url, $this->mainScreenUrl), 'Login FAILED');
        } else {
            $this->assertFalse(str_contains($url, $this->loginScreenUrl), 'Login was successful, but should have FAILED');
        }
    }

    private function logOut(): void
    {
        $this->client->switchTo()->defaultContent();
        $this->goToUserMenuLink('fa-sign-out-alt');
        $this->client->waitFor('//input[@id="authUser"]');
        $url = $this->client->getCurrentURL();
        $this->assertFalse(str_contains($url, $this->loginScreenUrl), 'Logout FAILED');
    }
}
