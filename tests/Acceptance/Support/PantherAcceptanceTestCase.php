<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Acceptance\Support;

use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\JavaScriptExecutor;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverExpectedCondition;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Panther\Client;

/**
 * Base class for Panther-driven acceptance tests.
 *
 * Consolidates the client lifecycle + login + Knockout-ready gate +
 * Product-Registration modal dismissal + iframe-switch helpers that
 * every Small-tier 4e port (E2eCriticalPathTest, AaLoginAcceptanceTest,
 * GgUserMenuLinksAcceptanceTest, FrontPaymentCssContrastAcceptanceTest,
 * KkEncounterFormNavbarUrlAcceptanceTest) grew inline. Medium-tier
 * ports consume from day one instead of re-duplicating.
 *
 * Design notes:
 *
 *   - Extends PHPUnit's `TestCase` directly rather than a project base
 *     class; the source-side E2e BaseTrait/BaseCore split doesn't
 *     translate 1:1 to acceptance (no phpunit-suite ordering, no
 *     source-checkout preconditions, no per-run screenshot dir).
 *
 *   - Client lifecycle in tearDown() only. Every current test creates
 *     the Client inside the test method (not setUp) so a pre-navigation
 *     BrowserSession::create() failure surfaces as a test-method
 *     failure with the intended assertions in the stack trace rather
 *     than a generic setUp failure. Retain that pattern here.
 *
 *   - `performLoginAsAdmin()` is the FULL post-login gate: title →
 *     #mainMenu presence → Knockout-ready → optional modal dismissal.
 *     Every current caller wants all four; a subset-only variant would
 *     just re-duplicate the wait-chain.
 *
 *   - Modal dismissal is public (well, protected) so tests that don't
 *     invoke performLoginAsAdmin() can still opt in defensively if a
 *     future path lands them post-login without going through the
 *     standard gate.
 *
 *   - XPath / JS constants live on this class so tests reference them
 *     via `self::` rather than redefining them per-file. The
 *     release-image XPath discoveries (Patient label uses <div
 *     class="menuLabel">, not <a>) live here too.
 */
abstract class PantherAcceptanceTestCase extends TestCase
{
    /**
     * Post-login shell title — the SPA layer sets document.title to this
     * once the shell is loaded. Used as the primary "am I authenticated
     * yet" gate for the post-login redirect race.
     */
    protected const SHELL_TITLE = 'OpenEMR';

    /**
     * Login-page title — asserted before submitting credentials so a
     * broken login route surfaces with a clear signal rather than a
     * confusing downstream failure.
     */
    protected const LOGIN_TITLE = 'OpenEMR Login';

    /**
     * Top-right user icon in the post-login shell. Used by ports that
     * click into the user dropdown (GgUserMenuLinks…).
     */
    protected const XPATH_USER_ICON = '//i[@id="user_icon"]';

    /**
     * XPath for the currently-active tab in the post-login #tabs_div.
     * The `tabsNoHover` filter excludes hidden tabs — matches source-
     * side BaseTrait::assertActiveTab discipline.
     */
    protected const XPATH_ACTIVE_TAB
        = "//div[@id='tabs_div']/div/div[not(contains(concat(' ',normalize-space(@class),' '),' tabsNoHover '))]";

    /**
     * The username input on the login form. Its presence after a
     * logout / post-logout redirect is the "we're back on login" gate.
     */
    protected const XPATH_LOGIN_USERNAME_INPUT = '//input[@id="authUser"]';

    /**
     * Login form path — used by the initial GET before submitting
     * credentials.
     */
    protected const LOGIN_URL = '/interface/login/login.php?site=default';

    /**
     * Post-login landing path — the SPA main shell. Included as a
     * constant so tests that hit it directly (e.g. unauthenticated
     * redirect assertions) don't have to hardcode the string.
     */
    protected const MAIN_SHELL_URL = '/interface/main/tabs/main.php';

    /**
     * JS gate that returns true once Knockout has populated #mainMenu
     * with children (i.e. bindings applied, not just an empty shell).
     * Matches the source-side BaseTrait::waitForAppReady contract.
     */
    protected const JS_KNOCKOUT_READY
        = 'return document.getElementById("mainMenu")?.children.length > 0';

    protected ?Client $client = null;

    protected function tearDown(): void
    {
        if ($this->client !== null) {
            // Panther leaves a Chrome subprocess + ChromeDriver session
            // dangling if not explicitly quit. Local runs accumulate
            // zombies; CI runners recycle so it doesn't matter there,
            // but tearDown discipline keeps local dev clean.
            //
            // finally: if quit() throws (driver session already dead),
            // still null out the client so a bare Throwable doesn't
            // mask the real test outcome as a teardown error.
            try {
                $this->client->quit();
            } catch (WebDriverException) {
                // Driver session already gone; nothing left to clean up.
                // Narrowed from \Throwable per openemr.forbiddenCatchType
                // rule — genuine PHP errors should still propagate.
            } finally {
                $this->client = null;
            }
        }
    }

    /**
     * Narrow the nullable $this->client for phpstan and for a clearer
     * failure mode if a helper is somehow called before create().
     */
    protected function requireClient(): Client
    {
        if ($this->client === null) {
            self::fail('BrowserSession client not initialized — helper called before create()');
        }
        return $this->client;
    }

    /**
     * Log in as admin/pass and wait for the Knockout-rendered
     * #mainMenu to actually populate. Two-gate discipline:
     *
     *   1. Title change to "OpenEMR" — proves the login POST
     *      authenticated and the post-login redirect fired.
     *   2. `#mainMenu` element present in DOM — proves the shell
     *      rendered.
     *   3. Knockout applied bindings so #mainMenu has children —
     *      proves the JS framework loaded and applied to the menu
     *      template (catches webpack-broken builds that render the
     *      shell but leave the menu empty).
     *
     * Then dismiss the Product Registration modal if it appeared, so
     * downstream clicks aren't intercepted by the modal backdrop.
     *
     * All three waits wrap TimeoutException in a diagnostic self::fail
     * that includes the observed landing URL + title — a failed login
     * is by far the most likely cause of a timeout at these gates, and
     * knowing WHICH page the browser actually landed on cuts triage
     * time to zero.
     */
    protected function performLoginAsAdmin(): void
    {
        $client = $this->requireClient();
        $client->request('GET', self::LOGIN_URL);

        self::assertSame(
            self::LOGIN_TITLE,
            $client->getTitle(),
            'Login page must render before submitting credentials — a different title means the login route itself is broken',
        );

        $client->submitForm('login-button', [
            'authUser' => 'admin',
            'clearPass' => 'pass',
        ]);

        // Post-login redirect is client-side / async — same race the
        // source-side LoginTrait::performLogin handles by waiting for
        // getTitle() === 'OpenEMR'.
        try {
            $client->wait(10)->until(
                static fn(WebDriver $driver): bool => $driver->getTitle() === self::SHELL_TITLE,
            );
        } catch (TimeoutException) {
            self::fail(
                'Post-login shell title never became "' . self::SHELL_TITLE . '" (10s timeout). '
                . 'Landing URL: ' . $client->getCurrentURL() . '. '
                . 'Title: ' . $client->getTitle() . '. '
                . 'This usually means the login POST did not authenticate '
                . '(credentials wrong, POST rejected, or login handler broke).'
            );
        }

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

        // Knockout-ready gate: bindings have applied, the menu template
        // ran, so #mainMenu has child nodes. Without this check we
        // could pass on a broken JS build that renders the SPA shell
        // but leaves the menu empty.
        //
        // Type-hint the closure's $driver as JavaScriptExecutor so
        // phpstan can resolve executeScript() (the base WebDriver
        // interface types the callable arg as `mixed`).
        try {
            $client->wait(30)->until(fn(JavaScriptExecutor $driver): bool => (bool) $driver->executeScript(
                self::JS_KNOCKOUT_READY
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
     * on first login whose backdrop can intercept clicks on elements
     * outside its own hit area. Dismiss it via the modal's own hide()
     * when visible; do nothing (don't fail) when absent, so this stays
     * robust if a future release image tweaks the trigger heuristic or
     * drops the modal entirely.
     *
     * Try/catch scoped ONLY to the presence wait: TimeoutException here
     * is benign ("no modal appeared, nothing to dismiss") and should
     * not be conflated with a dismissal failure below. If the modal
     * DID appear but the dismissal below fails, the fade-out wait's
     * TimeoutException propagates as a real failure — otherwise a
     * leftover backdrop would intercept the next click and cause a
     * downstream test failure with no useful signal.
     */
    protected function dismissProductRegistrationModalIfPresent(): void
    {
        $client = $this->requireClient();
        // 5s poll (not 15s): product_reg.js's XHR fires during
        // document.ready which has already completed by the time
        // the Knockout-ready gate above passes, so the modal
        // either shows within the first few hundred ms of this
        // poll or it isn't going to. Longer window costs the full
        // wait × N scenarios × M matrix cells for zero signal gain
        // when the modal doesn't appear.
        try {
            $client->wait(5)->until(fn(JavaScriptExecutor $driver): bool => (bool) $driver->executeScript(
                <<<'JS_WRAP'
                    var modal = document.querySelector('.product-registration-modal.show');
                    return modal !== null && modal.style.display === 'block';
                JS_WRAP,
            ));
        } catch (TimeoutException) {
            // No modal appeared within the poll window — that's fine
            // (a re-run against the same artifact won't re-show the
            // modal, and future release-image changes may drop it
            // entirely); proceed.
            return;
        }
        // Modal detected — from here down, failures propagate so a
        // failed dismissal surfaces loudly rather than getting hidden
        // by a broad catch upstream.

        // Small settle after the show transition — Bootstrap's
        // internal state machine ignores hide() until the fade-in
        // completes. Empirically ~150ms is the fade-in duration;
        // 500ms gives comfortable margin.
        usleep(500_000);
        // Guard the jQuery call — if jQuery is under noConflict or
        // absent from window entirely, calling `window.jQuery(...)`
        // throws a WebDriver JS error. If the guard hits (jQuery
        // missing on a shipped OpenEMR release image would itself be
        // a real problem to surface), the modal stays visible and
        // the fade-out wait below times out — that TimeoutException
        // propagates and fails the test with a clear signal that
        // dismissal didn't complete, rather than silently letting
        // the backdrop intercept the next user-icon click.
        $client->executeScript(
            <<<'JS_WRAP'
                if (window.jQuery) {
                    window.jQuery(".product-registration-modal").modal("hide");
                }
            JS_WRAP,
        );
        // Wait for the fade-out to complete so the subsequent
        // click isn't intercepted by the modal backdrop
        // mid-transition. No try/catch — a timeout here
        // means we detected a modal but couldn't hide it, which
        // is a real failure the test must surface.
        $client->wait(10)->until(fn(JavaScriptExecutor $driver): bool => (bool) $driver->executeScript(
            <<<'JS_WRAP'
                var modal = document.querySelector('.product-registration-modal.show');
                var backdrop = document.querySelector('.modal-backdrop');
                return modal === null && backdrop === null;
            JS_WRAP,
        ));
    }

    /**
     * Switch the Panther client into an iframe located by XPath.
     * Mirrors the source-side BaseTrait::switchToIFrame helper shape.
     * Made available on the base class (not the seeding trait) because
     * even non-seeding flows can end up inside iframes (front-payment,
     * menu-tab surfaces).
     */
    protected function switchToIFrame(string $xpath): void
    {
        $client = $this->requireClient();
        $iframe = $client->findElement(WebDriverBy::xpath($xpath));
        $client->switchTo()->frame($iframe);
    }

    /**
     * Wait for an element matched by $by to be clickable, then click
     * it. Narrows WebDriverWait::until()'s `mixed` return through an
     * instanceof check so phpstan level 10 accepts the ->click() call
     * without inline @var casts. Default 15s matches the widest
     * previously-used timeout across the 5 ports (10s in menu-link
     * clicks, 15s in seeding); override via $timeoutSeconds if needed.
     */
    protected function waitAndClick(WebDriverBy $by, string $description, int $timeoutSeconds = 15): void
    {
        $client = $this->requireClient();
        $element = $client->wait($timeoutSeconds)->until(
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
     * expected title, with the "Loading" placeholder resolved. Two
     * waits: the tab node is created before the tab content resolves,
     * so reading text between those events races the async render.
     * Mirrors the source-side BaseTrait::assertActiveTab shape,
     * simplified for the single-string case (no loose match, no
     * '||'-separated loading indicators).
     */
    protected function assertActiveTab(string $expectedTitle): void
    {
        $client = $this->requireClient();

        $client->waitFor(self::XPATH_ACTIVE_TAB, 30);
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
            [self::XPATH_ACTIVE_TAB],
        ));

        $activeTab = $client->findElement(WebDriverBy::xpath(self::XPATH_ACTIVE_TAB));
        self::assertSame(
            $expectedTitle,
            trim($activeTab->getText()),
            "[{$expectedTitle}] menu action did not open the expected tab — "
            . 'the click reached the app but the resulting tab title differs, '
            . 'which means either the menu entry routes somewhere unexpected '
            . 'or the tab label regressed',
        );
    }
}
