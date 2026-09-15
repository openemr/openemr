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
use OpenEMR\Tests\Acceptance\Support\UiSeedingTrait;
use PHPUnit\Framework\Attributes\Group;

/**
 * Panther-driven acceptance port of tests/Tests/E2e/BbCreateStaffTest.
 *
 * Phase 4f Medium-tier port (third, after Dd + Ff). Exercises the
 * staff-user creation flow: Admin → Users → Add User → fill new-user
 * modal → Save → assert the new user row appears in the users table.
 * Historically the flakiest test in the source-side E2E suite (see
 * UiSeedingTrait::addStaffUserViaUi docblock for the flake background
 * + acceptance-side mitigations).
 *
 * Seeding: no prior state required beyond an admin login (username
 * uniqueness comes from per-instance random suffix in
 * seedStaffUsername(), so multiple runs against the same DB — same
 * PR's fresh-install + post-upgrade phases, tests running in the same
 * phase, upgrade scenarios against an already-populated DB — each seed
 * a distinct staff user with no cross-run collision).
 *
 * Dual-tagged fresh-install + post-upgrade from the start.
 */
#[Group('fresh-install')]
#[Group('post-upgrade')]
final class BbCreateStaffAcceptanceTest extends PantherAcceptanceTestCase
{
    use UiSeedingTrait;

    /**
     * Verify a new staff user can be created via Admin → Users → Add
     * User and appears in the users table.
     *
     * Source: BbCreateStaffTest / UserAddTrait::testUserAdd.
     */
    public function testCreateStaffUser(): void
    {
        $this->client = BrowserSession::create();
        $this->performLoginAsAdmin();

        // Create the staff user. addStaffUserViaUi leaves the browser
        // in the admin iframe with the users table rendered and the
        // new user row visible; its final wait proves the create
        // succeeded end-to-end (form submit → server accept → modal
        // close → iframe reload → row visible).
        $this->addStaffUserViaUi();

        // addStaffUserViaUi's final wait already proves the row
        // rendered — this re-reads that same row and asserts on its
        // text so the test summary carries a real, phpstan-visible
        // assertion + guards against future changes that make the
        // wait's XPath more permissive than intended. Mirrors Dd's
        // and Ff's terminal re-read assertion pattern.
        $username = $this->seedStaffUsername();
        $userRow = $this->requireClient()->findElement(
            WebDriverBy::xpath("//table//a[text()='{$username}']"),
        )->getText();
        self::assertSame(
            $username,
            $userRow,
            'Users-table row link text should exactly match the seeded username',
        );
    }
}
