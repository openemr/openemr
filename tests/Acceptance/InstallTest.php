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
        $location = $this->locationHeader($response);
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
        // Full happy-path login: POST admin/pass to the login endpoint,
        // assert we get the post-login redirect (302 → /interface/main/
        // tabs/main.php?token_main=...). token_main is a per-session
        // anti-CSRF token; its presence in the redirect URL is the
        // definitive signal that the credentials were accepted and a
        // session was minted.
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
            'Login POST should return 302 with the authenticated-landing redirect; 200 with the login form re-rendered = credentials rejected',
        );

        $location = $this->locationHeader($response);
        self::assertStringContainsString(
            '/interface/main/tabs/main.php',
            $location,
            'Login should redirect to the authenticated landing page',
        );
        self::assertMatchesRegularExpression(
            '/token_main=[A-Za-z0-9]+/',
            $location,
            'The post-login redirect must carry a per-session token_main anti-CSRF token — its presence proves the session was minted',
        );

        // Actually FOLLOW the redirect on the same BrowserKit instance
        // (session cookie carried automatically) and verify the landing
        // page actually loads. A 401/403 here would mean tabs/main.php
        // rejected the session for some reason (token_main mismatch,
        // session storage broken) — the location-header assertions alone
        // wouldn't catch that.
        $landingUrl = str_starts_with($location, 'http')
            ? $location
            : ArtifactBrowser::baseUrl() . '/' . ltrim($location, '/');
        $browser->request('GET', $landingUrl);
        $landingResponse = $browser->getResponse();
        self::assertSame(
            200,
            $landingResponse->getStatusCode(),
            'GET on the authenticated landing URL must return 200; 401/403 would indicate the session was rejected despite the login redirect',
        );
    }

    /**
     * Symfony BrowserKit's Response::getHeader() returns array|string|null
     * depending on the header's arity and presence. Normalize to a plain
     * string here (empty when absent) so assertions can operate on it
     * without narrowing dance at every call site.
     */
    private function locationHeader(\Symfony\Component\BrowserKit\Response $response): string
    {
        /** @var array<int, string>|string|null $value */
        $value = $response->getHeader('Location');
        if (is_array($value)) {
            return $value[0] ?? '';
        }
        return $value ?? '';
    }
}
