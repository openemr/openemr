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

use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\JavaScriptExecutor;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverExpectedCondition;
use OpenEMR\Tests\Acceptance\Support\BrowserSession;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Panther\Client;

/**
 * Panther-driven acceptance port of tests/Tests/E2e/GgUserMenuLinksTest.
 *
 * Phase 4e-2 (second Small warmup of Phase 4e — reuse tests/Tests/E2e/*
 * flows against the SHIPPED docker release image booted by
 * tests/Acceptance/bin/boot-docker.sh). Extends the pattern established
 * by AaLoginAcceptanceTest (#13336): Panther client via BrowserSession
 * factory, base URI resolved from ACCEPTANCE_ARTIFACT_URL, JS-redirect
 * anti-flake pattern via typed WebDriverExpectedCondition (never a raw
 * closure — phpstan level 10 rejects the closure form because $driver
 * types as mixed).
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
final class GgUserMenuLinksAcceptanceTest extends TestCase
{
    private ?Client $client = null;

    protected function tearDown(): void
    {
        if ($this->client !== null) {
            // Panther leaves a Chrome subprocess + ChromeDriver session
            // dangling if not explicitly quit. Same discipline as
            // E2eCriticalPathTest and AaLoginAcceptanceTest.
            $this->client->quit();
            $this->client = null;
        }
    }

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
        $this->loginAsAdminAndWaitForMenu();

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
            $client->waitFor('//input[@id="authUser"]');
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
     * Log in as admin/pass and wait for the Knockout-rendered #mainMenu
     * to actually populate — same two-gate discipline as
     * E2eCriticalPathTest, so the click sequence below can address
     * menu elements without racing the SPA boot.
     */
    private function loginAsAdminAndWaitForMenu(): void
    {
        $client = $this->requireClient();
        $client->request('GET', '/interface/login/login.php?site=default');

        self::assertSame(
            'OpenEMR Login',
            $client->getTitle(),
            'Login page must render before submitting credentials — a different title means the login route itself is broken',
        );

        $client->submitForm('login-button', [
            'authUser' => 'admin',
            'clearPass' => 'pass',
        ]);

        // Post-login redirect is client-side / async — same race the
        // source-side LoginTrait::performLogin handles by waiting for
        // getTitle() === 'OpenEMR'. Use the same wait-for-title
        // pattern here so downstream user-menu clicks don't race the
        // shell load.
        $client->wait(10)->until(
            static fn(WebDriver $driver): bool => $driver->getTitle() === 'OpenEMR',
        );

        try {
            $client->waitFor('#mainMenu', 30);
        } catch (TimeoutException) {
            self::fail(
                'Post-login #mainMenu element never appeared (30s timeout). '
                . 'Landing URL: ' . $client->getCurrentURL() . '. '
                . 'Title: ' . $client->getTitle() . '. '
                . 'This likely means the login POST did not authenticate, or '
                . 'the post-login shell (interface/main/tabs/main.php) never rendered.'
            );
        }

        // Knockout-ready gate — mirrors E2eCriticalPathTest so a broken
        // JS build (menu shell but empty children) fails here rather
        // than later on an obscure "user icon not clickable" message.
        try {
            $client->wait(30)->until(fn(JavaScriptExecutor $driver): bool => (bool) $driver->executeScript(
                'return document.getElementById("mainMenu")?.children.length > 0'
            ));
        } catch (TimeoutException) {
            self::fail(
                'Knockout main menu never populated (30s timeout after #mainMenu appeared). '
                . 'This means the SPA shell loaded but the JS framework (Knockout) either did '
                . 'not load or did not apply bindings — symptom of a broken webpack build, '
                . 'missing JS asset, or template regression.'
            );
        }

        $this->dismissProductRegistrationModalIfPresent();
    }

    /**
     * The shipped release image renders a "Product Registration" modal
     * on first login that intercepts clicks on the top-right user icon
     * (position 1841,31 in a 1920x1080 viewport is inside the modal's
     * hit area). The source-side dev checkout doesn't trigger this
     * modal in the same way, so it's an acceptance-side-only gotcha.
     *
     * Dismiss it via the modal's own "Ask again later" button when
     * visible; do nothing (don't fail) when it isn't there, so this
     * stays robust if a future release image tweaks the trigger
     * heuristic or drops the modal entirely. Short poll — the modal
     * either appears within the SPA-ready window above or it isn't
     * going to at all.
     */
    private function dismissProductRegistrationModalIfPresent(): void
    {
        $client = $this->requireClient();
        try {
            // The modal is triggered by an async XHR fired from
            // product_reg.js's document.ready — that XHR resolves and
            // calls `.modal('toggle')` some time after main.php
            // finishes rendering. Wait for the modal to be fully
            // shown (both the `show` class AND display:block) so
            // Bootstrap has committed the show-transition; calling
            // `.modal('hide')` mid-transition is a no-op that gets
            // clobbered when the show-side of the animation completes.
            $client->wait(15)->until(fn(JavaScriptExecutor $driver): bool => (bool) $driver->executeScript(
                <<<'JS_WRAP'
                    var modal = document.querySelector('.product-registration-modal.show');
                    return modal !== null && modal.style.display === 'block';
                JS_WRAP,
            ));
            // Small settle after the show transition — Bootstrap's
            // internal state machine ignores hide() until the fade-in
            // completes. Empirically ~150ms is the fade-in duration;
            // 500ms gives comfortable margin without lengthening the
            // test noticeably.
            usleep(500_000);
            $client->executeScript(
                'window.jQuery(".product-registration-modal").modal("hide");',
            );
            // Wait for the fade-out to complete so the subsequent
            // user-icon click isn't intercepted by the modal
            // backdrop mid-transition.
            $client->wait(10)->until(fn(JavaScriptExecutor $driver): bool => (bool) $driver->executeScript(
                <<<'JS_WRAP'
                    var modal = document.querySelector('.product-registration-modal.show');
                    var backdrop = document.querySelector('.modal-backdrop');
                    return modal === null && backdrop === null;
                JS_WRAP,
            ));
        } catch (TimeoutException) {
            // No modal appeared within the poll window — that's fine
            // (a re-run against the same artifact won't re-show the
            // modal, and future release-image changes may drop it
            // entirely); proceed with the user-menu interactions.
        }
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
            WebDriverBy::xpath('//i[@id="user_icon"]'),
            'top-right user icon',
        );
        $this->waitAndClick(
            WebDriverBy::xpath(
                '//ul[@id="userdropdown"]//i[contains(@class, "' . $menuTreeIcon . '")]',
            ),
            "user-menu entry with icon class {$menuTreeIcon}",
        );
    }

    /**
     * Wait for an element matched by $by to be clickable, then click
     * it. Narrows WebDriverWait::until()'s `mixed` return through an
     * instanceof check so phpstan level 10 accepts the ->click() call
     * without inline @var casts.
     */
    private function waitAndClick(WebDriverBy $by, string $description): void
    {
        $client = $this->requireClient();
        $element = $client->wait(10)->until(
            WebDriverExpectedCondition::elementToBeClickable($by),
        );
        if (!$element instanceof WebDriverElement) {
            self::fail(
                "elementToBeClickable returned a non-element for {$description}; got "
                . get_debug_type($element),
            );
        }
        $element->click();
    }

    /**
     * Wait for the currently-active tab in #tabs_div to display the
     * expected title, with the "Loading" placeholder resolved. Mirrors
     * the source-side BaseTrait::assertActiveTab shape but simplified
     * for the single-string case used by every user-menu entry
     * (no '||'-separated loading indicators, no loose match).
     */
    private function assertActiveTab(string $expectedTitle): void
    {
        $client = $this->requireClient();
        $activeTabXpath = "//div[@id='tabs_div']/div/div[not(contains(concat(' ',normalize-space(@class),' '),' tabsNoHover '))]";

        // Wait for the active tab element to exist, then wait for the
        // "Loading" placeholder to be replaced with the real title. Two
        // waits because the tab node is created before the tab content
        // resolves — reading the text between those events races the
        // async tab render.
        $client->waitFor($activeTabXpath, 30);
        $client->wait(30)->until(fn(JavaScriptExecutor $driver): bool => (bool) $driver->executeScript(
            <<<'JS_WRAP'
                var el = document.evaluate(
                    arguments[0],
                    document,
                    null,
                    XPathResult.FIRST_ORDERED_NODE_TYPE,
                    null,
                ).singleNodeValue;
                if (!el) { return false; }
                var text = (el.textContent || '').trim();
                return text.length > 0 && text.indexOf('Loading') === -1;
            JS_WRAP,
            [$activeTabXpath],
        ));

        $activeTab = $client->findElement(WebDriverBy::xpath($activeTabXpath));
        self::assertSame(
            $expectedTitle,
            trim($activeTab->getText()),
            "[{$expectedTitle}] user-menu link did not open the expected tab — "
            . 'the click reached the app but the resulting tab title differs, which '
            . 'means either the menu entry routes somewhere unexpected or the tab '
            . 'label regressed',
        );
    }

    /**
     * Narrow the nullable $this->client for phpstan and for a clearer
     * failure mode if a helper is somehow called before setUp has run.
     */
    private function requireClient(): Client
    {
        if ($this->client === null) {
            self::fail('BrowserSession client not initialized — helper called before create()');
        }
        return $this->client;
    }
}
