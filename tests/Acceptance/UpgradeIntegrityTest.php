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

use OpenEMR\Tests\Acceptance\Support\ArtifactBrowser;
use OpenEMR\Tests\Acceptance\Support\LoginFlow;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Post-upgrade acceptance test.
 *
 * Fires after acceptance-docker.yml's upgrade scenario has:
 *   1. Booted the from_tag artifact (auto-installed via env vars)
 *   2. Run --group=fresh-install successfully
 *   3. `docker compose down` (preserved named volumes)
 *   4. Swapped `image:` to to_tag
 *   5. Re-booted -- the entrypoint detected the existing installation
 *      via /var/www/localhost/htdocs/openemr/sites/ persistence and
 *      ran the auto-upgrade path: fsupgrade-<N>.sh (filesystem
 *      migrations) then sql_upgrade.php (schema migrations)
 *   6. Healthcheck asserted the login-page redirect target -- so the
 *      upgraded stack is at least serving the post-install response
 *
 * This test picks up from there and verifies the upgraded stack is
 * actually functional, not just "responding to HTTP." Phase 2 MVP:
 * admin can still log in against the upgraded artifact. That covers
 * the load-bearing sanity path -- session storage survived the
 * upgrade, admin credentials still work, the authenticated landing
 * page renders. If sql_upgrade.php broke the users table or
 * fsupgrade-<N>.sh corrupted session state, that surfaces here.
 *
 * Later phases expand:
 *   - Phase 4 adds pre-upgrade data seeding (patient/encounter/user
 *     creation via API or DB) with post-upgrade readback verification.
 *     That requires DataSeed helpers under Support/DataSeed/ which
 *     don't exist yet -- deferring until we actually need them.
 *   - ApiSmokeTest / FhirSmokeTest (Phase 4) will also fire against
 *     the post-upgrade artifact once they exist.
 */
#[Group('post-upgrade')]
final class UpgradeIntegrityTest extends TestCase
{
    public function testAdminLoginStillWorksAfterUpgrade(): void
    {
        // Same happy-path login as InstallTest — shared implementation
        // in Support/LoginFlow keeps both scenarios in lockstep on the
        // exact login form contract. The failure-context prefix routes
        // any assertion failure back at the post-upgrade scenario so
        // it's clear this is an upgrade regression, not a fresh-install
        // regression.
        LoginFlow::loginAsAdmin(
            ArtifactBrowser::create(),
            ArtifactBrowser::baseUrl(),
            'post-upgrade',
        );
    }
}
