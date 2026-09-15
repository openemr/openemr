<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Acceptance\Support;

use PHPUnit\Framework\Assert;
use Symfony\Component\BrowserKit\HttpBrowser;

/**
 * Shared admin-login flow used by every acceptance test that needs an
 * authenticated session against the artifact under test.
 *
 * Extracted from the identical implementations in InstallTest (Phase 1)
 * and UpgradeIntegrityTest (Phase 2) so future authenticated tests
 * (Phase 4a-2 API/FHIR OAuth-adjacent tests, wizard walkthroughs in
 * 4c, etc.) don't hand-roll a fifth copy.
 *
 * The success signal is unchanged from the original inline versions:
 * login POST returns 302 with `token_main` in the Location header,
 * and a GET on that Location returns 200 — that combination is the
 * definitive proof the credentials were accepted, session was minted,
 * and the authenticated landing page renders.
 */
final class LoginFlow
{
    /**
     * Log in as `admin`/`pass`, follow the post-login redirect on the
     * same browser instance (session cookie carried automatically), and
     * assert each step succeeded. Callers can immediately follow up
     * with authenticated requests on the returned browser.
     *
     * `$failureContext` is prepended to every assertion message so a
     * failure points back at the calling test's scenario ("post-upgrade
     * login" is clearer than a bare "login failed" when both
     * InstallTest and UpgradeIntegrityTest use the same helper).
     */
    public static function loginAsAdmin(
        HttpBrowser $browser,
        string $baseUrl,
        string $failureContext = '',
    ): void {
        $prefix = $failureContext !== '' ? "[{$failureContext}] " : '';

        $browser->request(
            'POST',
            $baseUrl . '/interface/main/main_screen.php?auth=login&site=default',
            [
                'authUser' => 'admin',
                'clearPass' => 'pass',
                'languageChoice' => '1',
                'new_login_session_management' => '1',
            ],
        );
        $response = $browser->getResponse();

        Assert::assertSame(
            302,
            $response->getStatusCode(),
            $prefix . 'Login POST should return 302; 200 with the login form re-rendered = credentials rejected',
        );

        $location = ResponseHeaders::location($response);
        Assert::assertStringContainsString(
            '/interface/main/tabs/main.php',
            $location,
            $prefix . 'Login should redirect to the authenticated landing page',
        );
        Assert::assertMatchesRegularExpression(
            '/token_main=[A-Za-z0-9]+/',
            $location,
            $prefix . 'Post-login redirect must carry a per-session token_main anti-CSRF token — its absence indicates the session was not minted',
        );

        // Actually FOLLOW the redirect on the same BrowserKit instance
        // and verify the landing page loads. Guards against the case
        // where the 302 looks valid but the actual landing page 401s
        // due to session-state regression that the header alone
        // wouldn't catch.
        $landingUrl = str_starts_with($location, 'http')
            ? $location
            : $baseUrl . '/' . ltrim($location, '/');
        $browser->request('GET', $landingUrl);
        $landingResponse = $browser->getResponse();
        Assert::assertSame(
            200,
            $landingResponse->getStatusCode(),
            $prefix . 'GET on the authenticated landing URL must return 200; 401/403 = session accepted by login endpoint but rejected by main.php',
        );
    }
}
