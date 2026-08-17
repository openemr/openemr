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
 * Panther-driven acceptance port of tests/Tests/E2e/FfOpenEncounterTest.
 *
 * Phase 4f Medium-tier port (second, after DdOpenPatient #13354).
 * Exercises the encounter-open flow: seed a patient + encounter,
 * navigate back to the patient dashboard, click the shell's Past
 * Encounters dropdown, pick the first Office Visit entry, land inside
 * the encounter forms iframe with the encounter navbar rendered. That
 * flow is a distinct user journey from the addEncounter flow already
 * covered by KkEncounterFormNavbarUrlAcceptanceTest (which lands
 * inside the encounter navbar as a side effect of creation) — Ff
 * specifically validates the pastEncounters dropdown → select →
 * open path.
 *
 * Seeding: source-side test relies on CcCreatePatientTest +
 * EeCreateEncounterTest having seeded via the source-side ordered
 * suite. Acceptance harness is order-agnostic, so this test seeds
 * patient + encounter inline via addPatientViaUi + addEncounterViaUi
 * before invoking the open flow via openPatientViaUi (to navigate
 * back to the dashboard) + openEncounterViaUi. Per-instance random
 * seed identity (from #13351 seed-identity refactor) means multiple
 * runs against the same DB (upgrade scenario's post-upgrade phase,
 * tests running in the same phase) each seed a distinct
 * patient/encounter — no cross-run collision.
 *
 * Dual-tagged fresh-install + post-upgrade from the start.
 */
#[Group('fresh-install')]
#[Group('post-upgrade')]
final class FfOpenEncounterAcceptanceTest extends PantherAcceptanceTestCase
{
    use UiSeedingTrait;

    /**
     * Verify a seeded encounter for a seeded patient can be re-opened
     * from the patient dashboard's Past Encounters dropdown.
     *
     * Source: FfOpenEncounterTest / EncounterOpenTrait::testEncounterOpen.
     */
    public function testOpenSeededEncounter(): void
    {
        $this->client = BrowserSession::create();
        $this->performLoginAsAdmin();

        // Seed: create the patient + encounter this test will re-open.
        // addPatientViaUi leaves the browser on the patient's Medical
        // Record Dashboard; addEncounterViaUi navigates into the
        // encounter and leaves the browser inside the encounter forms
        // iframe with the navbar rendered. A no-op "encounter already
        // open" would trivially pass the final assertion, so
        // openPatientViaUi below navigates back to the patient
        // dashboard to force the pastEncounters dropdown path.
        $this->addPatientViaUi();
        $this->addEncounterViaUi();

        // Navigate back to the patient's Medical Record Dashboard so
        // the shell's Past Encounters dropdown is reachable.
        // openPatientViaUi exercises the search + finder-click path
        // that Dd validates independently, so a genuine open here
        // gates that flow as an implicit dependency.
        $this->openPatientViaUi(
            $this->seedPatientFname(),
            $this->seedPatientLname(),
        );

        // The open-encounter flow itself: click Past Encounters →
        // click first Office Visit entry → wait for the encounter
        // navbar for THIS specific patient. openEncounterViaUi's
        // final wait proves the encounter is open (and, incidentally,
        // that the assertion caller doesn't need to re-navigate).
        $this->openEncounterViaUi();

        // openEncounterViaUi's final wait already proves the navbar
        // rendered — this re-reads that same header and asserts on
        // its text so the test summary carries a real, phpstan-visible
        // assertion + guards against future changes that make the
        // wait's XPath more permissive than intended. Mirrors Dd's
        // dashboard-header re-read pattern.
        $navbarTitle = $this->requireClient()->findElement(
            WebDriverBy::xpath('//span[@id="navbarEncounterTitle"]'),
        )->getText();
        self::assertStringContainsString(
            'Encounter for ' . $this->seedPatientFname() . ' ' . $this->seedPatientLname(),
            $navbarTitle,
            'Encounter navbar title should reference the seeded patient',
        );
    }
}
