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
use OpenEMR\Tests\Acceptance\Support\ResponseHeaders;
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
        // Reuses the exact login flow InstallTest exercises against
        // a fresh artifact. Success signal is identical: 302 to
        // `/interface/main/tabs/main.php?token_main=<hex>`, then a
        // GET on the landing page returns 200. If sql_upgrade.php
        // broke the users table, sessions, or the token_main
        // machinery, the assertions fail here.
        $browser = ArtifactBrowser::create();
        $browser->request(
            'POST',
            ArtifactBrowser::baseUrl() . '/interface/main/main_screen.php?auth=login&site=default',
            [
                'authUser' => 'admin',
                'clearPass' => 'pass',
                'languageChoice' => '1',
                'new_login_session_management' => '1',
            ],
        );
        $response = $browser->getResponse();

        self::assertSame(
            302,
            $response->getStatusCode(),
            'Login POST should return 302 after upgrade; 200 with login form re-rendered = users table or auth-token machinery broken by the upgrade',
        );

        $location = ResponseHeaders::location($response);
        self::assertStringContainsString(
            '/interface/main/tabs/main.php',
            $location,
            'Post-upgrade login should redirect to the authenticated landing page',
        );
        self::assertMatchesRegularExpression(
            '/token_main=[A-Za-z0-9]+/',
            $location,
            'Post-upgrade token_main must still be minted -- absence indicates the session machinery regressed',
        );

        // Follow through: guards against the case where the redirect
        // looks valid but the actual landing page 401s/403s due to
        // a session-state regression the header alone wouldn't catch.
        $landingUrl = str_starts_with($location, 'http')
            ? $location
            : ArtifactBrowser::baseUrl() . '/' . ltrim($location, '/');
        $browser->request('GET', $landingUrl);
        $landingResponse = $browser->getResponse();
        self::assertSame(
            200,
            $landingResponse->getStatusCode(),
            'GET on the authenticated landing URL must return 200 after upgrade; 401/403 = session accepted by login endpoint but rejected by main.php',
        );
    }
}
