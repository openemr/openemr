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
 * Flow exercised end-to-end:
 *   1. Seed a fixed-identity persist-check patient (idempotent).
 *   2. Seed an "In Office" reserved slot for PERSIST_APPT_DATE
 *      covering 08:00-17:00 (idempotent).
 *   3. Seed an "Office Visit" appointment at 10:00 for the persist
 *      patient (idempotent — inside the in-office window, so no
 *      outside-hours prompt).
 *   4. Navigate to Patient Flow Board, filter to PERSIST_APPT_DATE,
 *      assert the appointment row appears.
 *
 * Dual-tagged fresh-install + post-upgrade. Both phases run the same
 * code path — the idempotent seed helpers create-if-missing so the
 * fresh-install phase does the actual seeding while the post-upgrade
 * phase (booted from a DB volume that was populated by the
 * fresh-install phase of the SAME upgrade-scenario matrix cell) finds
 * the pre-existing rows and just asserts they still render after the
 * migration.
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
#[Group('fresh-install')]
#[Group('post-upgrade')]
final class AppointmentPersistenceAcceptanceTest extends PantherAcceptanceTestCase
{
    use UiSeedingTrait;

    /**
     * Verify the persist-check appointment can be created (or found
     * pre-existing) and is visible on Patient Flow Board for the
     * target date.
     */
    public function testAppointmentPersistsAndFlowBoardShowsIt(): void
    {
        $this->client = BrowserSession::create();
        $this->performLoginAsAdmin();

        $pid = $this->seedPersistPatientIfMissing();
        $this->seedPersistInOfficeSlotIfMissing();
        $this->seedPersistAppointmentIfMissing($pid);

        $this->assertPersistAppointmentOnFlowBoard();
    }
}
