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
use OpenEMR\Tests\Acceptance\Support\BrowserSession;
use OpenEMR\Tests\Acceptance\Support\PantherAcceptanceTestCase;
use PHPUnit\Framework\Attributes\Group;

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
final class E2eCriticalPathTest extends PantherAcceptanceTestCase
{
    public function testAdminCanLogInAndKnockoutMainMenuRenders(): void
    {
        $this->client = BrowserSession::create();
        // performLoginAsAdmin encapsulates the full login + Knockout-
        // ready gate discipline (title change → #mainMenu appears →
        // Knockout populates children) with diagnostic self::fail
        // messages on each timeout. That's the exact scope this test
        // asserts on — the post-login-shell-boots-cleanly signal —
        // so the assertion here just checks the visible menu content
        // after the base-class gate lands.
        $this->performLoginAsAdmin();

        // At this point the app is fully booted — assert one visible
        // menu label to prove the menu isn't just full of empty nodes.
        // "Calendar" is the first top-level entry in the openemr menu
        // and is essentially unrenameable (it's the landing icon in
        // every skin). If this ever fails, the failure message points
        // at the specific expectation being wrong rather than a vague
        // "menu is broken."
        $client = $this->requireClient();
        $calendarMenu = $client->findElements(
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
