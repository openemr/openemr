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

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use OpenEMR\Tests\Acceptance\Support\BrowserSession;
use OpenEMR\Tests\Acceptance\Support\PantherAcceptanceTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * Panther-driven acceptance port of tests/Tests/E2e/GgUserMenuLinksTest.
 *
 * Phase 4e-2 (second Small warmup of Phase 4e — reuse tests/Tests/E2e/*
 * flows against the SHIPPED docker release image booted by
 * tests/Acceptance/bin/boot-docker.sh). Extends PantherAcceptanceTestCase
 * for the shared login + Knockout-ready gate + Product Registration
 * modal dismissal. Closures inside `wait()->until(...)` MUST explicitly
 * type their `$driver` parameter as `WebDriver` (or
 * `JavaScriptExecutor` when calling executeScript) — otherwise phpstan
 * level 10 infers `mixed` and rejects downstream method calls.
 * `WebDriverExpectedCondition` carries its own types so plain-
 * `until(WebDriverExpectedCondition::...)` needs no annotation.
 *
 * Full port of the source-side menuLinkProvider — all five user-menu
 * links exercise post-login UI surface (not admin.php / not any surface
 * removed by docker/release/openemr.sh's post-configure cleanup), so
 * every scenario carries meaningful signal against a booted release
 * artifact and none duplicate the coverage in E2eCriticalPathTest
 * (login + menu render only) or AaLoginAcceptanceTest (wrong-password
 * + unauthenticated-deep-link only):
 *
 *   - Settings       → 'User Settings' tab      (PORTED)
 *   - Change Password → 'Change Password' tab    (PORTED)
 *   - MFA Management → 'Manage Multi Factor Authentication' tab (PORTED)
 *   - About OpenEMR  → 'About OpenEMR' tab      (PORTED)
 *   - Logout         → returns to OpenEMR Login (PORTED)
 *
 * The source-side GgUserMenuLinksTest continues to run against the dev
 * stack unchanged — this class is purely additive against the release
 * artifact.
 */
#[Group('fresh-install')]
#[Group('post-upgrade')]
final class GgUserMenuLinksAcceptanceTest extends PantherAcceptanceTestCase
{
    /**
     * Each user-menu link should navigate to (or land on) the expected
     * post-click surface — the four in-app entries open a named tab
     * inside #tabs_div, and Logout returns the browser to the login
     * page.
     *
     * Source: GgUserMenuLinksTest::testUserMenuLink (data-provider
     * driven, one test per menu entry).
     */
    #[DataProvider('menuLinkProvider')]
    public function testUserMenuLink(string $menuTreeIcon, string $expectedTabTitle): void
    {
        $this->client = BrowserSession::create();
        $this->performLoginAsAdmin();

        if ($menuTreeIcon === 'fa-sign-out-alt') {
            // Logout is a distinct assertion shape — clicking the icon
            // exits the shell rather than opening a tab inside it, so
            // the post-click check is "we're back on the login page"
            // rather than "the named tab loaded". Mirrors the source-
            // side LogoutTrait::logOut discipline: click the icon,
            // wait for the login input to render, assert the title.
            $client = $this->requireClient();
            $this->clickUserMenuIcon($menuTreeIcon);
            $client->wait(10)->until(
                WebDriverExpectedCondition::urlContains('/interface/login/login.php'),
            );
            $client->waitFor(self::XPATH_LOGIN_USERNAME_INPUT);
            self::assertSame(
                $expectedTabTitle,
                $client->getTitle(),
                'Logout via user menu should return the browser to the login page — a different title means the artifact either failed to invalidate the session or landed on an unexpected route',
            );
            return;
        }

        $this->clickUserMenuIcon($menuTreeIcon);
        $this->assertActiveTab($expectedTabTitle);
    }

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function menuLinkProvider(): array
    {
        return [
            'Settings user menu link' => ['fa-cog', 'User Settings'],
            'Change Password user menu link' => ['fa-lock', 'Change Password'],
            'MFA Management user menu link' => ['fa-key', 'Manage Multi Factor Authentication'],
            'About OpenEMR user menu link' => ['fa-info', 'About OpenEMR'],
            'Logout user menu link' => ['fa-sign-out-alt', 'OpenEMR Login'],
        ];
    }

    /**
     * Click the top-right user icon, then click the child user-menu
     * entry identified by its Font Awesome icon class. Mirrors the
     * source-side BaseTrait::goToUserMenuLink XPath sequence
     * exactly — the user-menu markup is the same whether the artifact
     * came from a dev checkout or the release image.
     */
    private function clickUserMenuIcon(string $menuTreeIcon): void
    {
        $client = $this->requireClient();
        // Ensure we're not inside an iframe (defensive — matches
        // source-side goToUserMenuLink discipline).
        $client->switchTo()->defaultContent();

        $this->waitAndClick(
            WebDriverBy::xpath(self::XPATH_USER_ICON),
            'top-right user icon',
            10,
        );
        $this->waitAndClick(
            WebDriverBy::xpath(
                '//ul[@id="userdropdown"]//i[contains(@class, "' . $menuTreeIcon . '")]',
            ),
            "user-menu entry with icon class {$menuTreeIcon}",
            10,
        );
    }
}
