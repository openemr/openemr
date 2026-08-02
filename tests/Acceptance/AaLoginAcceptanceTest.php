<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Acceptance;

use Facebook\WebDriver\WebDriverExpectedCondition;
use OpenEMR\Tests\Acceptance\Support\BrowserSession;
use OpenEMR\Tests\Acceptance\Support\PantherAcceptanceTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * Panther-driven acceptance port of the non-overlapping scenarios in
 * tests/Tests/E2e/AaLoginTest.php.
 *
 * Phase 4e-1 (first Small warmup of Phase 4e — reuse tests/Tests/E2e/*
 * flows against the SHIPPED docker release image booted by
 * tests/Acceptance/bin/boot-docker.sh). Extends PantherAcceptanceTestCase
 * for BrowserSession lifecycle discipline, though the negative-auth
 * flows here don't invoke performLoginAsAdmin() — they intentionally
 * stay pre-login.
 *
 * Deliberately partial port — the source-side AaLoginTest has five
 * scenarios; only two of them add signal over what already ships in
 * the acceptance suite:
 *
 *   - testGoToOpenemrLoginPage        (SKIPPED — duplicate of
 *     E2eCriticalPathTest's login-page-load assertion)
 *   - testLoginUnauthorized           (PORTED — negative-auth signal;
 *     nothing else in the acceptance suite asserts that a wrong
 *     password stays on the login page instead of authenticating)
 *   - testurlWithoutTokenShouldRedirectToLoginPage (PORTED — asserts
 *     unauthenticated deep-links redirect to the login page; distinct
 *     from the happy-path login-redirect chain E2eCriticalPathTest
 *     covers)
 *   - testAdminPageDisabledByDefault  (SKIPPED — docker/release/
 *     openemr.sh removes admin.php from the release image after
 *     configuration, so this scenario is meaningless against a booted
 *     release artifact; the disabled-by-default guard is exercised
 *     source-side against the dev checkout instead)
 *   - testAdminPageEnabledWithEnvVar  (SKIPPED — spawns a php CLI
 *     against a dev-checkout admin.php path; no analog in the black-
 *     box artifact context and admin.php is removed from the image
 *     anyway)
 *
 * The source-side AaLoginTest continues to run against the dev stack
 * unchanged — this class is purely additive against the release
 * artifact.
 */
#[Group('fresh-install')]
#[Group('post-upgrade')]
final class AaLoginAcceptanceTest extends PantherAcceptanceTestCase
{
    /**
     * Wrong password should NOT authenticate; the login page stays put.
     *
     * Source: AaLoginTest::testLoginUnauthorized (LoginTrait::login
     * with $goalPass=false).
     */
    public function testLoginWithWrongPasswordStaysOnLoginPage(): void
    {
        $this->client = BrowserSession::create();
        $client = $this->requireClient();
        $client->request('GET', self::LOGIN_URL);

        self::assertSame(
            self::LOGIN_TITLE,
            $client->getTitle(),
            'Login page must render before submitting bad credentials — a different title means the login route itself is broken',
        );

        // Submit the login form with a deliberately wrong password.
        // Same admin username, password with a "1" suffix — matches
        // the source-side LoginTestData::password . "1" pattern.
        $client->submitForm('login-button', [
            'authUser' => 'admin',
            'clearPass' => 'pass1',
        ]);

        // OpenEMR's authLoginScreen() redirects via `w.top.location
        // .href = ...` (client-side JS), so reading the title
        // immediately after submitForm can race the redirect. Wait
        // for the URL to settle on /interface/login/login.php before
        // asserting the title. Anti-flake sync — Panther-standard
        // pattern for JS-driven redirects.
        $client->wait(10)->until(
            WebDriverExpectedCondition::urlContains('/interface/login/login.php'),
        );

        // Post-redirect the browser is still on the login page. The
        // release image's login handler re-renders login.php on
        // failure rather than redirecting to an error page, so the
        // title stays "OpenEMR Login" (a successful auth would
        // transition it to "OpenEMR").
        self::assertSame(
            self::LOGIN_TITLE,
            $client->getTitle(),
            'Login with a wrong password must NOT authenticate — a title change to "OpenEMR" means the artifact accepted invalid credentials',
        );
    }

    /**
     * An unauthenticated request to a post-login deep-link should
     * redirect back to the login page (not 500, not silently serve
     * privileged content).
     *
     * Source: AaLoginTest::testurlWithoutTokenShouldRedirectToLoginPage.
     */
    public function testUnauthenticatedDeepLinkRedirectsToLoginPage(): void
    {
        $this->client = BrowserSession::create();
        $client = $this->requireClient();
        // Hit the post-login main shell without ever having authenticated.
        // testing_mode=1 mirrors the source-side flow — it suppresses
        // some prod-only redirects that would otherwise complicate the
        // assertion (matches the source-side E2e's flag choice).
        $client->request(
            'GET',
            self::MAIN_SHELL_URL . '?site=default&testing_mode=1',
        );

        // Same JS-redirect race as testLoginRejectsWrongPassword:
        // the unauthenticated main.php entry-point kicks the user
        // back to login.php via a client-side redirect, so a title
        // read immediately after request() can race the redirect.
        // Wait for the URL to reach /interface/login/login.php first.
        $client->wait(10)->until(
            WebDriverExpectedCondition::urlContains('/interface/login/login.php'),
        );

        self::assertSame(
            self::LOGIN_TITLE,
            $client->getTitle(),
            'Unauthenticated GET on /interface/main/tabs/main.php should land on the login page — a different title means the artifact either exposed post-login content or served an error page instead of the expected auth redirect',
        );
    }
}
