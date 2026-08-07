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
 * Flow exercised end-to-end:
 *   1. Seed a fixed-identity persist-check patient (idempotent —
 *      shares the persist patient with AppointmentPersistenceTest).
 *   2. Upload a small PNG file into the Medical Record category
 *      (idempotent — skips upload if the fixture filename is
 *      already listed).
 *   3. Click the file link in the tree, assert the viewer panel
 *      renders content (proves the doc is retrievable AND
 *      displayable, not just present in the file system).
 *
 * Dual-tagged fresh-install + post-upgrade. Both phases run the same
 * code path — the idempotent upload helper skips creation on the
 * post-upgrade phase when it finds the file already in the tree
 * (persisted from the fresh-install phase of the same upgrade-
 * scenario matrix cell). The open-and-view assertion runs in both
 * phases, but only the post-upgrade run proves the document survived
 * the fsupgrade-N.sh + sql_upgrade.php passes and is still viewable.
 *
 * Signal covered that no other acceptance test covers:
 *   - Document blob storage / retrieval across upgrade
 *   - Documents controller URL routing (`/controller.php?document&...`)
 *     across upgrade — this URL scheme has changed shape historically
 *     and a mid-upgrade router config regression would surface here.
 *   - Documents category tree rendering after upgrade
 */
#[Group('fresh-install')]
#[Group('post-upgrade')]
final class DocumentPersistenceAcceptanceTest extends PantherAcceptanceTestCase
{
    use UiSeedingTrait;

    /**
     * Verify the persist-check document can be uploaded (or found
     * pre-existing) and clicking it renders viewer content.
     */
    public function testDocumentPersistsAndIsViewable(): void
    {
        $this->client = BrowserSession::create();
        $this->performLoginAsAdmin();

        $pid = $this->seedPersistPatientIfMissing();
        $this->seedPersistDocumentIfMissing($pid);

        $this->assertPersistDocumentOpenable($pid);
    }
}
