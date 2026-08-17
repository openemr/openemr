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
use OpenEMR\Tests\Acceptance\Support\ResponseHeaders;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Phase 1 exit-criterion test: verifies the openemr artifact under test
 * completed its install cleanly and that the default admin credentials
 * (from OE_USER/OE_PASS env vars in docker/production/docker-compose.yml)
 * work end-to-end through the login flow.
 *
 * Runs against ACCEPTANCE_ARTIFACT_URL (default http://localhost:8580).
 * Boot the artifact first with tests/Acceptance/bin/boot-docker.sh <tag>.
 *
 * The test suite is intentionally tag-agnostic: it targets whatever
 * artifact is booted, whether that's `latest`, `next`, a locally-built
 * PR image (Phase 2.5), or a tarball-mounted stack (Phase 3). Same test
 * class, different artifact endpoint.
 *
 * Group tags: `fresh-install` maps to Phase 2's matrix job that runs
 * this test against a freshly-booted artifact (both from_tag and
 * to_tag scenarios); no other group runs it, since the login flow
 * assertions are only valid on a fresh install (not on a post-upgrade
 * stack where UpgradeIntegrityTest takes over).
 */
#[Group('fresh-install')]
final class InstallTest extends TestCase
{
    public function testHomepageRedirectsToLoginAfterInstall(): void
    {
        // A freshly-installed openemr artifact should serve `/` as a 302
        // redirect to the login page. If the install didn't complete
        // (env-var auto-install failed, container crashed, DB not
        // reachable), the redirect target would be setup.php or a
        // 500-class error instead — either surfaces here.
        $browser = ArtifactBrowser::create();
        $browser->request('GET', ArtifactBrowser::baseUrl() . '/');
        $response = $browser->getResponse();

        self::assertSame(302, $response->getStatusCode(), 'GET / should redirect (302) on an installed artifact');
        $location = ResponseHeaders::location($response);
        // openemr's redirect target is relative ("interface/login/login.php?..."),
        // no leading slash. Assert on the trailing path fragment.
        self::assertStringContainsString(
            'interface/login/login.php',
            $location,
            'GET / should redirect to the login page; a redirect to setup.php would mean install did not complete',
        );
    }

    public function testAdminCanLogInAndReachAuthenticatedLandingPage(): void
    {
        // Full happy-path login: POST admin/pass, assert the 302 has
        // token_main in the Location, follow the redirect, assert 200.
        // Implementation lives in Support/LoginFlow — shared with
        // UpgradeIntegrityTest and any future authenticated tests so
        // the exact-form-field-shape (authUser + clearPass +
        // languageChoice + new_login_session_management) stays in one
        // place.
        LoginFlow::loginAsAdmin(
            ArtifactBrowser::create(),
            ArtifactBrowser::baseUrl(),
            'fresh-install',
        );
    }
}
