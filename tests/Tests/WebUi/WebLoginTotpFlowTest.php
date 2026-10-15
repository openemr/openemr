<?php

/**
 * Web-login TOTP MFA flow.
 *
 * Locks in two properties of `interface/main/main_screen.php`:
 *
 *   1. A user with TOTP enrolled who submits the correct 6-digit code
 *      completes the login (302 back to the authenticated landing page).
 *   2. Submitting the same TOTP code twice within its 90-second
 *      acceptance window rejects the second attempt (replay protection).
 *
 * Both properties depend on main_screen.php delegating TOTP verification
 * to `MfaUtils::checkTOTP` rather than inlining its own copy of the
 * verifier (as it did historically for 8 years). A regression that
 * reverted to the inline verifier — or accidentally removed the
 * delegation call — would fail #1 (login broken) or #2 (replay-window
 * silently permissive) before this file existed.
 *
 * MfaUtils has unit-level replay coverage; that test proves the helper
 * behaves correctly in isolation but does NOT prove main_screen.php
 * actually calls it. This test proves the wire-up end-to-end by driving
 * the actual /interface/main/main_screen.php login form over HTTP the
 * same way a real browser would.
 *
 * Per the webui suite's HTTP-only convention (see
 * tests/Tests/WebUi/README.md), MFA enrollment happens by driving the
 * mfa_totp.php admin UI form flow — not by direct QueryUtils INSERT.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\WebUi;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RobThree\Auth\Providers\Qr\BaconQrCodeProvider;
use RobThree\Auth\TwoFactorAuth;
use Symfony\Component\DomCrawler\Crawler;

class WebLoginTotpFlowTest extends TestCase
{
    private string $baseUrl;
    /** Admin session used for the mfa_totp.php enrollment + tearDown deletion. */
    private ?Client $adminHttp = null;

    protected function setUp(): void
    {
        $this->baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';

        if (getenv('OPENEMR_ALLOW_OAUTH_HTTPS_SKIP') === '1') {
            $this->markTestSkipped('Skipping per OPENEMR_ALLOW_OAUTH_HTTPS_SKIP=1');
        }
        $probe = $this->buildHttp()->get($this->baseUrl . '/');
        if ($probe->getHeaderLine('Server') === '') {
            $message = 'Web login requires a real webserver (Apache/nginx) that serves session cookies with Secure';
            if (getenv('CI') !== false) {
                self::fail($message . ' — hard failure in CI');
            }
            $this->markTestSkipped($message);
        }
    }

    protected function tearDown(): void
    {
        if ($this->adminHttp !== null) {
            $this->deleteAdminTotpEnrollment($this->adminHttp);
        }
    }

    #[Test]
    public function testWebLoginWithValidTotpCompletesLogin(): void
    {
        $secret = $this->enrollAdminTotp();

        // Fresh HTTP client (no admin session cookies) — mimics a user
        // logging in from scratch after enrollment.
        $http = $this->buildHttp(allowRedirects: false);

        // Step 1: POST username + password. With TOTP enrolled the server
        // renders the TOTP challenge form (200), not the 302 redirect
        // that a MFA-free login would return.
        $formResp = $http->post($this->baseUrl . '/interface/main/main_screen.php?auth=login&site=default', [
            'form_params' => [
                'authUser' => 'admin',
                'clearPass' => 'pass',
                'languageChoice' => '1',
                'new_login_session_management' => '1',
            ],
        ]);
        $this->assertSame(
            200,
            $formResp->getStatusCode(),
            'Login POST with TOTP enrolled should render the TOTP challenge form (200), not 302'
        );
        $crawler = new Crawler((string) $formResp->getBody());
        $this->assertGreaterThan(
            0,
            $crawler->filterXPath('//input[@name="totp"]')->count(),
            'TOTP challenge form should contain an input named "totp" — its absence means the MFA form did not render'
        );

        // Step 2: submit a valid TOTP computed from the enrolled secret.
        $tfa = new TwoFactorAuth(new BaconQrCodeProvider(4, '#ffffff', '#000000', 'svg'));
        $validCode = $tfa->getCode($secret);
        $totpResp = $http->post($this->baseUrl . '/interface/main/main_screen.php?auth=login&site=default', [
            'form_params' => [
                'authUser' => 'admin',
                'clearPass' => 'pass',
                'languageChoice' => '1',
                'new_login_session_management' => '1',
                'totp' => $validCode,
                'form_response' => 'true',
            ],
        ]);
        $this->assertSame(
            302,
            $totpResp->getStatusCode(),
            'Login POST with a valid TOTP should return 302 (redirect to the authenticated landing page). '
                . '200 with the TOTP form re-rendered means the code was rejected — either main_screen.php '
                . 'no longer delegates to MfaUtils, or the delegation is broken.'
        );
        $location = $totpResp->getHeaderLine('Location');
        $this->assertStringContainsString(
            '/interface/main/tabs/main.php',
            $location,
            'Post-login redirect should target the authenticated landing page'
        );
        $this->assertMatchesRegularExpression(
            '/token_main=[A-Za-z0-9]+/',
            $location,
            'Post-login redirect must carry a token_main — its absence means the session was not minted'
        );
    }

    #[Test]
    public function testWebLoginRejectsReplayedTotpWithinAcceptanceWindow(): void
    {
        $secret = $this->enrollAdminTotp();

        $tfa = new TwoFactorAuth(new BaconQrCodeProvider(4, '#ffffff', '#000000', 'svg'));
        $code = $tfa->getCode($secret);

        // First login consumes the valid code.
        $http1 = $this->buildHttp(allowRedirects: false);
        $r1 = $http1->post($this->baseUrl . '/interface/main/main_screen.php?auth=login&site=default', [
            'form_params' => [
                'authUser' => 'admin',
                'clearPass' => 'pass',
                'languageChoice' => '1',
                'new_login_session_management' => '1',
                'totp' => $code,
                'form_response' => 'true',
            ],
        ]);
        $this->assertSame(
            302,
            $r1->getStatusCode(),
            'First TOTP submission with a valid code should succeed (302). '
                . 'If this is not 302, the enrollment did not take effect.'
        );

        // Second login attempt with the SAME code from a fresh session.
        // The replay-protection state recorded by the first login must
        // reject this one — even though RobThree would still verify
        // the code as valid within its 90s acceptance window.
        $http2 = $this->buildHttp(allowRedirects: false);
        $r2 = $http2->post($this->baseUrl . '/interface/main/main_screen.php?auth=login&site=default', [
            'form_params' => [
                'authUser' => 'admin',
                'clearPass' => 'pass',
                'languageChoice' => '1',
                'new_login_session_management' => '1',
                'totp' => $code,
                'form_response' => 'true',
            ],
        ]);
        $this->assertSame(
            200,
            $r2->getStatusCode(),
            'Replaying the exact same TOTP code within its 90s acceptance window '
                . 'must be rejected (server re-renders the TOTP form, HTTP 200 — not 302). '
                . '302 here means main_screen.php is not going through MfaUtils::checkTOTP '
                . '(no replay protection) or the delegation is broken.'
        );
        $crawler = new Crawler((string) $r2->getBody());
        $this->assertGreaterThan(
            0,
            $crawler->filterXPath('//input[@name="totp"]')->count(),
            'On replay rejection the server should re-render the TOTP challenge form'
        );
    }

    /**
     * Drive mfa_totp.php's reg1 -> reg2 -> reg3 enrollment flow as admin
     * over HTTP and return the base32 secret the server generated.
     * $this->adminHttp holds the enrollment session for tearDown to
     * delete the registration after each test.
     */
    private function enrollAdminTotp(): string
    {
        $admin = $this->buildHttp();
        // Log in as admin via the standard main_screen.php login (no MFA
        // yet — we're the ones about to enroll it).
        $loginResp = $admin->post($this->baseUrl . '/interface/main/main_screen.php?auth=login&site=default', [
            'form_params' => [
                'authUser' => 'admin',
                'clearPass' => 'pass',
                'languageChoice' => '1',
                'new_login_session_management' => '1',
            ],
        ]);
        // Defensive guard: if admin already has TOTP enrolled (shared
        // test env leftover, prior interrupted test run, or a real
        // pre-existing enrollment), the login lands on the TOTP
        // challenge form — the enrollment flow below would either
        // fail confusingly or delete the pre-existing registration in
        // tearDown. Skip is the right answer for local dev
        // environments where a developer may have real TOTP enrolled.
        // But in CI a silent skip would let the whole webui suite
        // green-pass without actually exercising the valid-login and
        // replay assertions this file exists to lock in — a real
        // regression that stopped issuing TOTP challenges could ride
        // in unnoticed. Hard-fail on CI so the ambiguity surfaces.
        // Bail before assigning $this->adminHttp so tearDown does
        // nothing in the skip case.
        if (
            $loginResp->getStatusCode() === 200
            && (new Crawler((string) $loginResp->getBody()))
                ->filterXPath('//input[@name="totp"]')
                ->count() > 0
        ) {
            $message = 'Shared admin already has TOTP enrolled — skipping to avoid clobbering pre-existing registration';
            if (getenv('CI') !== false) {
                self::fail(
                    $message
                    . '. In CI this is a hard failure — a clean runner should not have TOTP pre-enrolled for admin. '
                    . 'If a CI job is intentionally sharing a stateful env, use a dedicated non-admin CI account for '
                    . 'the enrollment flow instead of clearing admin state each run.'
                );
            }
            $this->markTestSkipped($message);
        }

        // Step reg1: fetch the password-prompt form to grab a CSRF token.
        $reg1 = $admin->get($this->baseUrl . '/interface/usergroup/mfa_totp.php?action=reg1');
        $this->assertSame(200, $reg1->getStatusCode(), 'GET mfa_totp.php?action=reg1 should render');
        $reg1Csrf = $this->extractCsrfToken((string) $reg1->getBody(), 'mfa_totp.php reg1');

        // Step reg2: POST password + action=reg2 → server generates the
        // secret + renders the QR / plain-text secret display, stashes
        // the secret in the session.
        $reg2 = $admin->post($this->baseUrl . '/interface/usergroup/mfa_totp.php', [
            'form_params' => [
                'csrf_token_form' => $reg1Csrf,
                'action' => 'reg2',
                'clearPass' => 'pass',
                'error' => '',
            ],
        ]);
        $this->assertSame(200, $reg2->getStatusCode(), 'POST mfa_totp.php reg2 should render the secret page');
        $reg2Body = (string) $reg2->getBody();
        $secret = $this->extractPlainSecret($reg2Body);

        // Step reg3: POST action=reg3 → server pulls totpSecret from the
        // session (stashed during reg2) and INSERTs into
        // login_mfa_registrations server-side.
        $reg2Csrf = $this->extractCsrfToken($reg2Body, 'mfa_totp.php reg2');
        $admin->post($this->baseUrl . '/interface/usergroup/mfa_totp.php', [
            'form_params' => [
                'csrf_token_form' => $reg2Csrf,
                'action' => 'reg3',
                'error' => '',
            ],
        ]);

        $this->adminHttp = $admin;
        return $secret;
    }

    /**
     * Delete the TOTP registration this test installed. tearDown safety
     * net so admin state doesn't leak across tests + suites.
     */
    private function deleteAdminTotpEnrollment(Client $admin): void
    {
        $listResp = $admin->get($this->baseUrl . '/interface/usergroup/mfa_registrations.php');
        if ($listResp->getStatusCode() !== 200) {
            return;
        }
        $listBody = (string) $listResp->getBody();
        // If the session expired we cannot cleanly delete; skip and let
        // the container tear-down handle it.
        if (!preg_match('/csrf_token_form"\s+value="([a-f0-9]+)"/', $listBody, $matches)) {
            return;
        }
        $admin->post($this->baseUrl . '/interface/usergroup/mfa_registrations.php', [
            'form_params' => [
                'csrf_token_form' => $matches[1],
                'form_delete_method' => 'TOTP',
                'form_delete_name' => 'App Based 2FA',
            ],
        ]);
    }

    private function extractCsrfToken(string $body, string $where): string
    {
        self::assertMatchesRegularExpression(
            '/name="csrf_token_form"\s+value="([a-f0-9]+)"/',
            $body,
            "csrf_token_form input not found on {$where}"
        );
        preg_match('/name="csrf_token_form"\s+value="([a-f0-9]+)"/', $body, $matches);
        self::assertArrayHasKey(1, $matches, "csrf_token_form regex should have captured the token on {$where}");
        return $matches[1];
    }

    /**
     * mfa_totp.php:188 renders the base32 secret as a plain-text `<p>`
     * (16-32 uppercase base32 chars) so users who can't scan the QR
     * can paste it into their authenticator app. Scrape it directly.
     */
    private function extractPlainSecret(string $body): string
    {
        self::assertMatchesRegularExpression(
            '/<p>\s*[A-Z2-7]{16,64}\s*<\/p>/',
            $body,
            'reg2 response should contain a <p> with the base32 secret. '
                . 'If this fails, the mfa_totp.php reg2 template changed shape.'
        );
        preg_match('/<p>\s*([A-Z2-7]{16,64})\s*<\/p>/', $body, $matches);
        self::assertArrayHasKey(1, $matches, 'preg_match should have captured the secret group');
        return $matches[1];
    }

    private function buildHttp(bool $allowRedirects = true): Client
    {
        return new Client([
            'verify' => false,
            'http_errors' => false,
            'cookies' => new CookieJar(),
            'allow_redirects' => $allowRedirects,
            'timeout' => 15,
        ]);
    }
}
