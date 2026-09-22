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

use OpenEMR\Tests\Acceptance\Support\BrowserSession;
use OpenEMR\Tests\Acceptance\Support\PantherAcceptanceTestCase;
use OpenEMR\Tests\Acceptance\Support\UiSeedingTrait;
use PHPUnit\Framework\Attributes\Group;

/**
 * Persist-through-upgrade check for the calendar + appointment +
 * Flow Board flow. Manual-QA-derived replacement for one slice of
 * the deferred Hh/Ii/Jj menu-link Large-tier ports.
 *
 * Two-phase test, one method per phase:
 *
 *   Phase 1 (post-install): Seed the shared persist-check patient
 *   and create the Office Visit appointment at 10:00 (outside-hours
 *   "Provider not available" native confirm() muzzled to return
 *   true so the save proceeds). Assert the appointment renders on
 *   Patient Flow Board for the target date. Post-install run
 *   creates fresh state that phase 2 will read back.
 *
 *   Phase 2 (post-upgrade): Booted from the DB volume the post-install
 *   phase populated. Assert the patient and the Flow Board still
 *   show the appointment -- WITHOUT running any seed helpers. A
 *   missing patient or a Flow Board row that no longer renders is
 *   a real upgrade-side persistence regression and the assertion
 *   fails loudly.
 *
 * Why two methods rather than one dual-tagged idempotent-seed method
 * -- see DocumentPersistenceAcceptanceTest for the full rationale.
 * Summary: the seed-if-missing pattern silently masks upgrade data
 * loss; per-scenario split makes absence a structural failure.
 *
 * Assertion cross-surface signal:
 *   - Calendar create → openemr_postcalendar_events row → Flow Board
 *     query. A regression in schema migration, calendar rendering,
 *     Flow Board report SQL, or any of the intermediate ORM layers
 *     surfaces as a missing row.
 *   - Persistence signal on top: post-upgrade specifically asserts
 *     data written by the from_tag artifact is still queryable by
 *     the to_tag artifact after all fsupgrade-N.sh + sql_upgrade.php
 *     passes complete. No other acceptance test covers this.
 */
final class AppointmentPersistenceAcceptanceTest extends PantherAcceptanceTestCase
{
    use UiSeedingTrait;

    /**
     * Post-install phase: seed patient + appointment, verify it
     * renders immediately on Flow Board.
     */
    #[Group('post-install')]
    public function testSeedsAndFlowBoardShowsAppointmentInPostInstall(): void
    {
        $this->client = BrowserSession::create();
        $this->performLoginAsAdmin();

        $pid = $this->seedPersistPatientIfMissing();
        // seedPersistAppointmentIfMissing is the single entry point:
        // if the appointment doesn't exist, it seeds BOTH the In
        // Office window slot AND the appointment. Post-install
        // context here means neither exists -- both get created.
        $this->seedPersistAppointmentIfMissing($pid);

        $this->assertPersistAppointmentOnFlowBoard();
    }

    /**
     * Post-upgrade phase: assert the patient and the appointment
     * seeded in the post-install phase are STILL visible on Flow
     * Board. Explicitly does NOT call seed helpers -- a missing
     * appointment is the real persistence regression this test
     * exists to catch, and a seed-fallback would mask it.
     */
    #[Group('post-upgrade')]
    public function testPersistAppointmentStillOnFlowBoardAfterUpgrade(): void
    {
        $this->client = BrowserSession::create();
        $this->performLoginAsAdmin();

        $this->assertPersistPatientExists(
            'Persist patient not found post-upgrade. The upgrade dropped the shared test-fixture patient that DocumentPersistenceAcceptanceTest and AppointmentPersistenceAcceptanceTest both depend on (both were seeded during the post-install phase of this same upgrade-scenario matrix cell). A patient row being dropped by fsupgrade-N.sh or sql_upgrade.php passes is a persistence regression that would silently be masked if this method called seedPersistPatientIfMissing.',
        );

        // Flow Board renders from openemr_postcalendar_events; a
        // missing appointment here means the upgrade dropped the
        // calendar-event row (or a schema migration regressed the
        // Flow Board report SQL). Real persistence regression that
        // would silently be masked if this method called
        // seedPersistAppointmentIfMissing.
        $this->assertPersistAppointmentOnFlowBoard();
    }
}
