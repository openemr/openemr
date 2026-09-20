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
use RobThree\Auth\Providers\Qr\BaconQrCodeProvider;
use RobThree\Auth\TwoFactorAuth;
use Symfony\Component\BrowserKit\HttpBrowser;

/**
 * Web-login TOTP MFA acceptance test.
 *
 * Locks in two properties of `interface/main/main_screen.php` end to
 * end against a black-box artifact:
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
 * actually calls it. This acceptance test proves the wire-up end-to-end.
 *
 * Enrollment / cleanup happens via HTTP-driven admin UI (mfa_totp.php +
 * mfa_registrations.php) rather than direct DB access, matching the
 * black-box constraint of the acceptance harness and the same pattern
 * `AuthCodeFlow` uses to register + enable OAuth clients.
 */
#[Group('fresh-install')]
final class WebLoginTotpAcceptanceTest extends TestCase
{
    private ?HttpBrowser $adminBrowser = null;

    protected function tearDown(): void
    {
        if ($this->adminBrowser !== null) {
            $this->deleteTotpEnrollment($this->adminBrowser);
        }
    }

    public function testWebLoginWithValidTotpCompletesLogin(): void
    {
        $baseUrl = ArtifactBrowser::baseUrl();
        $secret = $this->enrollAdminTotp($baseUrl);

        // Fresh browser (no cookies from the enrollment session) — mimics
        // the situation of a user re-authenticating from scratch after
        // enrollment.
        $browser = ArtifactBrowser::create();
        $browser->followRedirects(false);
        $this->postLoginPassword($browser, $baseUrl);
        $this->assertTotpChallengeFormRendered(
            $browser,
            'After password submit with TOTP enrolled, server should render the TOTP challenge form'
        );

        // Submit a valid TOTP computed from the enrolled secret.
        $tfa = new TwoFactorAuth(new BaconQrCodeProvider(4, '#ffffff', '#000000', 'svg'));
        $validCode = $tfa->getCode($secret);
        $this->postTotpChallenge($browser, $baseUrl, $validCode);
        $response = $browser->getResponse();
        self::assertSame(
            302,
            $response->getStatusCode(),
            'Login POST with a valid TOTP should return 302 (redirect to the authenticated landing page). '
                . '200 with the TOTP form re-rendered means the code was rejected — either main_screen.php '
                . 'no longer delegates to MfaUtils, or the delegation is broken.'
        );
        $location = ResponseHeaders::location($response);
        self::assertStringContainsString(
            '/interface/main/tabs/main.php',
            $location,
            'Post-login redirect should target the authenticated landing page'
        );
        self::assertMatchesRegularExpression(
            '/token_main=[A-Za-z0-9]+/',
            $location,
            'Post-login redirect must carry a token_main — its absence means the session was not minted'
        );
    }

    public function testWebLoginRejectsReplayedTotpWithinAcceptanceWindow(): void
    {
        $baseUrl = ArtifactBrowser::baseUrl();
        $secret = $this->enrollAdminTotp($baseUrl);

        $tfa = new TwoFactorAuth(new BaconQrCodeProvider(4, '#ffffff', '#000000', 'svg'));
        $code = $tfa->getCode($secret);

        // First login consumes the valid code.
        $browser1 = ArtifactBrowser::create();
        $browser1->followRedirects(false);
        $this->postLoginPassword($browser1, $baseUrl);
        $this->postTotpChallenge($browser1, $baseUrl, $code);
        self::assertSame(
            302,
            $browser1->getResponse()->getStatusCode(),
            'First TOTP submission with a valid code should succeed (302). '
                . 'If this is not 302, the enrollment did not take effect.'
        );

        // Second login attempt with the SAME code from a fresh session —
        // must be rejected by the replay-protection state recorded on
        // login_mfa_registrations by the first login, even though
        // RobThree would still verify the code as valid within its 90s
        // acceptance window.
        $browser2 = ArtifactBrowser::create();
        $browser2->followRedirects(false);
        $this->postLoginPassword($browser2, $baseUrl);
        $this->postTotpChallenge($browser2, $baseUrl, $code);
        $r2 = $browser2->getResponse();
        self::assertSame(
            200,
            $r2->getStatusCode(),
            'Replaying the exact same TOTP code within its 90s acceptance window '
                . 'must be rejected (server re-renders the TOTP form, HTTP 200 — not 302). '
                . '302 here means main_screen.php is not going through MfaUtils::checkTOTP '
                . '(no replay protection) or the delegation is broken.'
        );
        $this->assertTotpChallengeFormRendered(
            $browser2,
            'On replay rejection the server should re-render the TOTP challenge form'
        );
    }

    /**
     * Drive the mfa_totp.php enrollment flow as admin over HTTP and
     * return the base32 secret the server generated. Held as
     * $this->adminBrowser so tearDown can log back in and delete the
     * registration.
     */
    private function enrollAdminTotp(string $baseUrl): string
    {
        $admin = ArtifactBrowser::create();
        LoginFlow::loginAsAdmin($admin, $baseUrl, 'MFA enrollment');

        // Step reg1: fetch the password-prompt form to grab a CSRF token.
        $admin->request('GET', $baseUrl . '/interface/usergroup/mfa_totp.php?action=reg1');
        self::assertSame(
            200,
            $admin->getResponse()->getStatusCode(),
            'GET mfa_totp.php?action=reg1 should render the password-prompt form'
        );
        $reg1Csrf = $this->extractCsrfToken($admin, 'mfa_totp.php reg1');

        // Step reg2: POST password + action=reg2 → server generates the
        // secret + renders the QR / plain-text secret display.
        $admin->request(
            'POST',
            $baseUrl . '/interface/usergroup/mfa_totp.php',
            [
                'csrf_token_form' => $reg1Csrf,
                'action' => 'reg2',
                'clearPass' => 'pass',
                'error' => '',
            ],
        );
        self::assertSame(
            200,
            $admin->getResponse()->getStatusCode(),
            'POST mfa_totp.php reg2 should render the QR / secret page'
        );
        // The base32 secret is displayed as a plain-text <p> right after
        // the "Or paste in the following code" heading — see mfa_totp.php
        // line 188. Extract it directly rather than trying to decode
        // the QR image.
        $secret = $this->extractPlainSecret($admin);

        // Step reg3: POST action=reg3 → server pulls totpSecret from the
        // session (stashed during reg2) and INSERTs into
        // login_mfa_registrations.
        $reg2Csrf = $this->extractCsrfToken($admin, 'mfa_totp.php reg2');
        $admin->request(
            'POST',
            $baseUrl . '/interface/usergroup/mfa_totp.php',
            [
                'csrf_token_form' => $reg2Csrf,
                'action' => 'reg3',
                'error' => '',
            ],
        );
        self::assertSame(
            200,
            $admin->getResponse()->getStatusCode(),
            'POST mfa_totp.php reg3 should return 200 (renders <script> that redirects to mfa_registrations.php)'
        );

        $this->adminBrowser = $admin;
        return $secret;
    }

    /**
     * Delete the TOTP registration this test installed. tearDown safety
     * net so shared admin state survives across tests + suites.
     */
    private function deleteTotpEnrollment(HttpBrowser $admin): void
    {
        $baseUrl = ArtifactBrowser::baseUrl();
        $admin->request('GET', $baseUrl . '/interface/usergroup/mfa_registrations.php');
        // If the GET itself failed (session expired, etc.), skip
        // deletion — the fresh-install teardown will drop everything
        // when the artifact is torn down anyway.
        if ($admin->getResponse()->getStatusCode() !== 200) {
            return;
        }
        $csrfInputs = $admin->getCrawler()->filterXPath('//input[@name="csrf_token_form"]');
        if ($csrfInputs->count() === 0) {
            return;
        }
        $csrf = (string) $csrfInputs->first()->attr('value');
        $admin->request(
            'POST',
            $baseUrl . '/interface/usergroup/mfa_registrations.php',
            [
                'csrf_token_form' => $csrf,
                'form_delete_method' => 'TOTP',
                'form_delete_name' => 'App Based 2FA',
            ],
        );
    }

    private function postLoginPassword(HttpBrowser $browser, string $baseUrl): void
    {
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
        self::assertSame(
            200,
            $browser->getResponse()->getStatusCode(),
            'Login POST with TOTP enrolled should render the TOTP challenge form (200), not 302'
        );
    }

    private function postTotpChallenge(HttpBrowser $browser, string $baseUrl, string $totpCode): void
    {
        $browser->request(
            'POST',
            $baseUrl . '/interface/main/main_screen.php?auth=login&site=default',
            [
                'authUser' => 'admin',
                'clearPass' => 'pass',
                'languageChoice' => '1',
                'new_login_session_management' => '1',
                'totp' => $totpCode,
                'form_response' => 'true',
            ],
        );
    }

    private function assertTotpChallengeFormRendered(HttpBrowser $browser, string $why): void
    {
        self::assertGreaterThan(
            0,
            $browser->getCrawler()->filterXPath('//input[@name="totp"]')->count(),
            $why . ' — expected an input named "totp" in the response'
        );
    }

    private function extractCsrfToken(HttpBrowser $browser, string $where): string
    {
        $inputs = $browser->getCrawler()->filterXPath('//input[@name="csrf_token_form"]');
        self::assertGreaterThan(
            0,
            $inputs->count(),
            "csrf_token_form input not found on {$where}"
        );
        $token = (string) $inputs->first()->attr('value');
        self::assertNotSame('', $token, "csrf_token_form value is empty on {$where}");
        return $token;
    }

    /**
     * Extract the base32 secret displayed as plain text on the reg2
     * page. mfa_totp.php:188 renders it inside a `<p>` immediately
     * after the "Or paste in the following code" heading — filter on
     * that text to distinguish from the other `<p>` blocks.
     */
    private function extractPlainSecret(HttpBrowser $browser): string
    {
        // The secret <p> is the first <p> that contains only base32
        // characters and is between 16 and 64 chars long. Use a
        // straightforward regex scan against the response body rather
        // than a fragile XPath sibling selector.
        $body = (string) $browser->getResponse()->getContent();
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
}
