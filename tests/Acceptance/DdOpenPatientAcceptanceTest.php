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
 * Panther-driven acceptance port of tests/Tests/E2e/DdOpenPatientTest.
 *
 * Phase 4f Medium-tier port (first). Exercises the patient-open flow:
 * type lastname into the shell's always-visible anySearchBox
 * (frm_search_globals), submit, click the matching entry in the
 * patient-finder iframe, land on the patient's Medical Record
 * Dashboard. That flow is a distinct user journey from the
 * addPatient flow already covered by KkEncounterFormNavbarUrl
 * (which lands on the dashboard as a side effect of creation) —
 * DdOpenPatient specifically validates the search + click-through
 * path.
 *
 * Seeding note: the source-side ordered E2E suite depends on
 * CcCreatePatientTest having run first (`#[Depends('testLoginAuthorized')]`
 * chained with #[Depends('testPatientOpen')] elsewhere). The
 * acceptance harness is order-agnostic, so this test seeds the
 * patient inline via addPatientViaUi() before invoking the open
 * flow via openPatientViaUi(). Per-instance random seed identity
 * (from #13351 seed-identity refactor) means multiple runs against
 * the same DB (upgrade scenario's post-upgrade phase, tests running
 * in the same phase) each create + open a distinct patient — no
 * cross-run collision.
 *
 * Dual-tagged fresh-install + post-upgrade from the start.
 */
#[Group('fresh-install')]
#[Group('post-upgrade')]
final class DdOpenPatientAcceptanceTest extends PantherAcceptanceTestCase
{
    use UiSeedingTrait;

    /**
     * Verify a seeded patient can be found via the shell's global
     * search box and opened to their Medical Record Dashboard.
     *
     * Source: DdOpenPatientTest / PatientOpenTrait::testPatientOpen.
     */
    public function testOpenSeededPatient(): void
    {
        $this->client = BrowserSession::create();
        $this->performLoginAsAdmin();

        // Seed the patient the open flow will find. addPatientViaUi
        // leaves the browser on the just-created patient's Medical
        // Record Dashboard — a no-op re-open of the same dashboard
        // would trivially pass, so openPatientViaUi's first step
        // (switching to default content + interacting with the shell
        // anySearchBox) exercises the search flow genuinely.
        $this->addPatientViaUi();

        $this->openPatientViaUi(
            $this->seedPatientFname(),
            $this->seedPatientLname(),
        );

        // openPatientViaUi's final wait already proves the dashboard
        // rendered — this re-reads that same header and asserts on
        // its text so the test summary carries a real, phpstan-visible
        // assertion + guards against future changes that make the
        // wait's XPath more permissive than intended.
        $dashboardHeader = $this->requireClient()->findElement(
            WebDriverBy::xpath(
                '//*[text()="Medical Record Dashboard - '
                . $this->seedPatientFname() . ' ' . $this->seedPatientLname() . '"]',
            ),
        )->getText();
        self::assertStringContainsString(
            $this->seedPatientLname(),
            $dashboardHeader,
            'Dashboard header should reference the seeded patient lastname',
        );
    }
}
