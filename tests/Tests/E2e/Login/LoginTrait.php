<?php

/**
 * LoginTrait trait
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2024 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\Login;

use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\WebDriver;
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
     * Submit the login form.
     */
    private function performLogin(string $name, string $password, bool $goalPass): void
    {
        $this->crawler = $this->client->request('GET', '/interface/login/login.php?site=default&testing_mode=1');
        try {
            $form = $this->crawler->filter('#login_form')->form();
        } catch (\Throwable $e) {
            // TEMPORARY debug hook (issue #12423): the login page has been
            // returning ~225 bytes with no #login_form after upgrade from
            // 5.0.0 on PHP 8.2, and the failure only reproduces in CI.
            // Dump the response body so we can see what the truncated page
            // actually contains. Remove once root cause is identified.
            $body = $this->client->getWebDriver()->getPageSource();
            fwrite(STDERR, sprintf(
                "\n[LoginTrait DEBUG] #login_form missing, response body length=%d\n---\n%s\n[/LoginTrait DEBUG]\n",
                strlen((string) $body),
                $body
            ));
            throw $e;
        }
        $form['authUser'] = $name;
        $form['clearPass'] = $password;
        $this->crawler = $this->client->submit($form);
        if ($goalPass) {
            // The post-login redirect is asynchronous: submit() returns
            // once the POST responds, but the browser still needs to
            // follow the redirect and load the main shell before
            // document.title transitions from 'OpenEMR Login' to
            // 'OpenEMR'. Under CI load the lag is long enough that
            // reading getTitle() immediately races the redirect and
            // fails with "Expected 'OpenEMR'; actual 'OpenEMR Login'".
            try {
                $this->client->wait(10)->until(
                    static fn(WebDriver $driver): bool => $driver->getTitle() === 'OpenEMR'
                );
            } catch (TimeoutException) {
                // Fall through so the assertSame below reports the
                // actual title for diagnostic purposes.
            }
        }
        $title = $this->client->getTitle();
        if ($goalPass) {
            $this->assertSame('OpenEMR', $title, 'Login FAILED');
        } else {
            $this->assertSame('OpenEMR Login', $title, 'Login was successful, but should have FAILED');
        }
    }
}
