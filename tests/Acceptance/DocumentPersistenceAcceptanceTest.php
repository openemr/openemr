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
 * Persist-through-upgrade check for the patient document upload +
 * view flow. Manual-QA-derived replacement for one slice of the
 * deferred Hh/Ii/Jj menu-link Large-tier ports.
 *
 * Two-phase test, one method per phase (see below for why the split
 * matters):
 *
 *   Phase 1 (post-install): Seed a fixed-identity persist-check
 *   patient (shares the persist patient with AppointmentPersistenceTest),
 *   upload a small PNG file into the Medical Record category, and
 *   verify the upload can be viewed. Post-install run creates
 *   fresh state that phase 2 will read back.
 *
 *   Phase 2 (post-upgrade): Booted from the DB volume the post-install
 *   phase populated. Assert the patient AND the document still exist
 *   AND the document still opens -- WITHOUT running any seed helpers.
 *   A missing patient or document is a real upgrade-side persistence
 *   regression and the assertion fails loudly.
 *
 * Why two methods rather than one dual-tagged idempotent-seed method:
 * the "seed-if-missing then verify" pattern silently masks upgrade
 * data loss. If the upgrade drops the document, an idempotent seed
 * helper would just re-create it and the viewer assertion would pass
 * -- the bug being tested for gets papered over by the test's own
 * setup code. Splitting into a post-install seed method and a
 * post-upgrade verify-only method makes the failure mode structural:
 * the post-upgrade method has no path to create anything, so absence
 * = assertion failure.
 *
 * Signal covered that no other acceptance test covers:
 *   - Document blob storage / retrieval across upgrade
 *   - Documents controller URL routing (`/controller.php?document&...`)
 *     across upgrade — this URL scheme has changed shape historically
 *     and a mid-upgrade router config regression would surface here.
 *   - Documents category tree rendering after upgrade
 */
final class DocumentPersistenceAcceptanceTest extends PantherAcceptanceTestCase
{
    use UiSeedingTrait;

    /**
     * Post-install phase: seed patient + document, verify the
     * upload is immediately viewable.
     */
    #[Group('post-install')]
    public function testSeedsAndViewsPersistDocumentInPostInstall(): void
    {
        $this->client = BrowserSession::create();
        $this->performLoginAsAdmin();

        $pid = $this->seedPersistPatientIfMissing();
        $this->seedPersistDocumentIfMissing($pid);

        $this->assertPersistDocumentOpenable($pid);
    }

    /**
     * Post-upgrade phase: assert the patient and document created
     * in the post-install phase are STILL present and viewable.
     * Explicitly does NOT call seed helpers -- a missing patient or
     * document is the real persistence regression this test exists
     * to catch, and a seed-fallback would mask it.
     */
    #[Group('post-upgrade')]
    public function testPersistDocumentStillPresentAfterUpgrade(): void
    {
        $this->client = BrowserSession::create();
        $this->performLoginAsAdmin();

        $pid = $this->assertPersistPatientExists(
            'Persist patient not found post-upgrade. The upgrade dropped the shared test-fixture patient that DocumentPersistenceAcceptanceTest and AppointmentPersistenceAcceptanceTest both depend on (both were seeded during the post-install phase of this same upgrade-scenario matrix cell). A patient row being dropped by fsupgrade-N.sh or sql_upgrade.php passes is a persistence regression that would silently be masked if this method called seedPersistPatientIfMissing.',
        );

        $this->assertPersistDocumentExists(
            $pid,
            'Persist document not found post-upgrade despite the patient still existing. The upgrade dropped either the document blob (filesystem or storage backend) or its DB metadata (documents / categories_to_documents rows). A real persistence regression that would silently be masked if this method called seedPersistDocumentIfMissing.',
        );

        $this->assertPersistDocumentOpenable($pid);
    }
}
