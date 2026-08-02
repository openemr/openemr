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
use Facebook\WebDriver\WebDriverBy;
use OpenEMR\Tests\Acceptance\Support\BrowserSession;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Panther\Client;

/**
 * Browser-driven end-to-end acceptance test.
 *
 * First test in the acceptance suite that drives an actual headless
 * Chrome via Panther/WebDriver rather than HTTP-only via BrowserKit.
 * Every earlier acceptance test (InstallTest, UpgradeIntegrityTest,
 * FhirSmokeTest, OAuth2SmokeTest) hits openemr as an HTTP client
 * with no JS runtime — so those tests would still pass even if
 * webpack builds broken bundles, if a JS asset 404s, or if Knockout
 * bindings fail to apply. This test catches that class of regression:
 * the full post-login SPA loads, JS runs, the Knockout-templated
 * main menu renders.
 *
 * Test scope kept intentionally small for this first Panther-in-
 * acceptance PR — one flow: admin login → wait for the main menu to
 * render. Adding more critical-path steps (patient add, encounter
 * start) is straightforward once the plumbing is proven in CI.
 *
 * Runs in both fresh-install and post-upgrade phases — the upgrade
 * path doesn't rebuild the post-login SPA, but a broken menu-render
 * after upgrade still signals a regression worth catching.
 */
#[Group('fresh-install')]
#[Group('post-upgrade')]
final class E2eCriticalPathTest extends TestCase
{
    private ?Client $client = null;

    protected function tearDown(): void
    {
        if ($this->client !== null) {
            // Panther leaves a Chrome subprocess + ChromeDriver session
            // dangling if not explicitly quit. Local runs accumulate
            // zombies; CI runners recycle so it doesn't matter there,
            // but tearDown discipline keeps local dev clean.
            $this->client->quit();
            $this->client = null;
        }
    }

    public function testAdminCanLogInAndKnockoutMainMenuRenders(): void
    {
        $this->client = BrowserSession::create();
        // Panther's request() takes either an absolute URL or a path
        // relative to `external_base_uri` (set to ACCEPTANCE_ARTIFACT_URL
        // in the BrowserSession factory).
        $this->client->request('GET', '/interface/login/login.php?site=default');

        self::assertSame(
            'OpenEMR Login',
            $this->client->getTitle(),
            'Login page title should be "OpenEMR Login" — a different title means the login route rendered something unexpected (setup wizard, error page, blank shell)',
        );

        // Fill and submit the login form. openemr's login form
        // requires two hidden fields (languageChoice + new_login_session_management)
        // in addition to the visible authUser/clearPass — those are
        // pre-populated by the form template, so submitting the
        // form element (rather than reconstructing a POST) picks
        // them up automatically.
        $this->client->submitForm('login-button', [
            'authUser' => 'admin',
            'clearPass' => 'pass',
        ]);

        // Post-login lands on /interface/main/tabs/main.php, which
        // loads the Knockout-templated main menu asynchronously.
        // waitForVisibility on #mainMenu confirms the DOM node
        // exists; the executeScript check below confirms Knockout
        // actually populated it with children (i.e. bindings
        // applied, not just an empty shell).
        try {
            $this->client->waitFor('#mainMenu', 30);
        } catch (TimeoutException) {
            self::fail(
                'Post-login #mainMenu element never appeared (30s timeout). '
                . 'Landing URL: ' . $this->client->getCurrentURL() . '. '
                . 'Title: ' . $this->client->getTitle() . '. '
                . 'This likely means the login POST did not authenticate, or '
                . 'the post-login shell (interface/main/tabs/main.php) never rendered.'
            );
        }

        // Knockout-ready gate: bindings have applied, the menu template
        // ran, so #mainMenu has child nodes. Matches the source-side
        // BaseTrait's waitForAppReady contract. Without this check we
        // could pass on a broken JS build that renders the SPA shell
        // but leaves the menu empty.
        //
        // Type-hint the closure's $driver as JavaScriptExecutor so
        // phpstan can resolve executeScript() (the base WebDriver
        // interface types the callable arg as `mixed`; source-side
        // E2E tests baseline this on principle, but we prefer to
        // type-narrow at the source instead).
        try {
            $this->client->wait(30)->until(fn(JavaScriptExecutor $driver): bool => (bool) $driver->executeScript(
                'return document.getElementById("mainMenu")?.children.length > 0'
            ));
        } catch (TimeoutException) {
            $rawDiagnostics = $this->client->executeScript(<<<'JS_WRAP'
                return JSON.stringify({
                    url: location.href,
                    title: document.title,
                    knockoutLoaded: typeof ko !== "undefined",
                    mainMenuChildCount: document.getElementById("mainMenu")?.children.length ?? 0,
                });
            JS_WRAP);
            $diagnostics = is_string($rawDiagnostics) ? $rawDiagnostics : 'unavailable';
            self::fail(
                'Knockout main menu never populated (30s timeout after #mainMenu appeared). '
                . "Page state: {$diagnostics}. This means the SPA shell loaded but the "
                . 'JS framework (Knockout) either did not load or did not apply bindings — '
                . 'symptom of a broken webpack build, missing JS asset, or template regression.'
            );
        }

        // At this point the app is fully booted — assert one visible
        // menu label to prove the menu isn't just full of empty nodes.
        // "Calendar" is the first top-level entry in the openemr menu
        // and is essentially unrenameable (it's the landing icon in
        // every skin). If this ever fails, the failure message points
        // at the specific expectation being wrong rather than a vague
        // "menu is broken."
        $calendarMenu = $this->client->findElements(
            WebDriverBy::xpath('//div[@id="mainMenu"]//div[text()="Calendar"]'),
        );
        self::assertNotEmpty(
            $calendarMenu,
            'The Knockout-rendered main menu should contain a "Calendar" entry — '
            . 'a missing entry means the menu rendered but with wrong content (bad menu template, '
            . 'localization regression, or ACL filtering the admin user out of a menu they should always see)',
        );
    }
}
