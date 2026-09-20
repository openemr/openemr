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

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Tests\Acceptance\Support\ArtifactBrowser;
use OpenEMR\Tests\Acceptance\Support\ResponseHeaders;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use RobThree\Auth\Providers\Qr\BaconQrCodeProvider;
use RobThree\Auth\TwoFactorAuth;

/**
 * Web-login TOTP MFA acceptance test.
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
 * verifier (as it did historically). A regression that reverted to the
 * inline verifier — or accidentally removed the delegation call — would
 * fail #1 (login broken) or #2 (replay-window silently permissive)
 * before this file existed.
 *
 * MfaUtils has unit-level replay coverage; that test proves the helper
 * behaves correctly in isolation but does NOT prove main_screen.php
 * actually calls it. This acceptance test proves the wire-up end-to-end.
 */
#[Group('fresh-install')]
final class WebLoginTotpAcceptanceTest extends TestCase
{
    private const TOTP_SECRET = 'JBSWY3DPEHPK3PXP';

    private int $adminUserId = 0;
    /** @var list<array<string, mixed>> */
    private array $originalAdminMfaRows = [];

    protected function setUp(): void
    {
        $adminRow = QueryUtils::querySingleRow(
            "SELECT id FROM users WHERE username = 'admin' AND active = 1"
        );
        $adminId = $adminRow['id'] ?? null;
        if (!is_numeric($adminId) || (int) $adminId === 0) {
            $this->markTestSkipped('admin user not present in this test environment');
        }
        $this->adminUserId = (int) $adminId;

        // Snapshot admin MFA rows so tearDown restores exactly whatever
        // was there before this test — same pattern used by the
        // service-layer PasswordGrantHardeningTest.
        /** @var list<array<string, mixed>> $rows */
        $rows = QueryUtils::fetchRecords(
            "SELECT user_id, name, method, var1, var2, last_challenge, last_used_token "
                . "FROM login_mfa_registrations WHERE user_id = ?",
            [$this->adminUserId]
        );
        $this->originalAdminMfaRows = $rows;

        // Install a known TOTP registration so we can compute valid codes
        // deterministically. Encrypt the secret with the standard crypto
        // key so MfaUtils::checkTOTP's decrypt path finds it (no
        // password-based-crypto fallback needed).
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM login_mfa_registrations WHERE user_id = ?",
            [$this->adminUserId]
        );
        $encryptedSecret = ServiceContainer::getCrypto()->encryptForDatabase(self::TOTP_SECRET);
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO login_mfa_registrations "
                . "(user_id, name, method, var1, var2, last_challenge, last_used_token) "
                . "VALUES (?, 'test-web-login', 'TOTP', ?, '', NULL, NULL)",
            [$this->adminUserId, $encryptedSecret]
        );
    }

    protected function tearDown(): void
    {
        // Restore admin's original MFA rows exactly.
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM login_mfa_registrations WHERE user_id = ?",
            [$this->adminUserId]
        );
        foreach ($this->originalAdminMfaRows as $row) {
            QueryUtils::sqlStatementThrowException(
                "INSERT INTO login_mfa_registrations "
                    . "(user_id, name, method, var1, var2, last_challenge, last_used_token) "
                    . "VALUES (?, ?, ?, ?, ?, ?, ?)",
                [
                    $row['user_id'],
                    $row['name'],
                    $row['method'],
                    $row['var1'],
                    $row['var2'],
                    $row['last_challenge'],
                    $row['last_used_token'],
                ]
            );
        }
    }

    public function testWebLoginWithValidTotpCompletesLogin(): void
    {
        $browser = ArtifactBrowser::create();
        $baseUrl = ArtifactBrowser::baseUrl();

        // Step 1: POST username + password. With TOTP enrolled the server
        // renders the TOTP challenge form (200), instead of the 302
        // redirect that a MFA-free login would return.
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
            'Login POST with TOTP enrolled should render the TOTP challenge form (200), not 302',
        );
        self::assertGreaterThan(
            0,
            $browser->getCrawler()->filterXPath('//input[@name="totp"]')->count(),
            'TOTP challenge form should contain an input named "totp" — its absence means the MFA form did not render',
        );

        // Step 2: submit a valid TOTP. Compute from the same secret we
        // enrolled in setUp; deterministic per current 30s slot.
        $tfa = new TwoFactorAuth(new BaconQrCodeProvider(4, '#ffffff', '#000000', 'svg'));
        $validCode = $tfa->getCode(self::TOTP_SECRET);
        $browser->request(
            'POST',
            $baseUrl . '/interface/main/main_screen.php?auth=login&site=default',
            [
                'authUser' => 'admin',
                'clearPass' => 'pass',
                'languageChoice' => '1',
                'new_login_session_management' => '1',
                'totp' => $validCode,
                'form_response' => 'true',
            ],
        );
        $response = $browser->getResponse();
        self::assertSame(
            302,
            $response->getStatusCode(),
            'Login POST with a valid TOTP should return 302 (redirect to the authenticated landing page). '
                . '200 with the TOTP form re-rendered means the code was rejected — either main_screen.php '
                . 'no longer delegates to MfaUtils, or the delegation is broken.',
        );
        $location = ResponseHeaders::location($response);
        self::assertStringContainsString(
            '/interface/main/tabs/main.php',
            $location,
            'Post-login redirect should target the authenticated landing page',
        );
        self::assertMatchesRegularExpression(
            '/token_main=[A-Za-z0-9]+/',
            $location,
            'Post-login redirect must carry a token_main — its absence means the session was not minted',
        );
    }

    public function testWebLoginRejectsReplayedTotpWithinAcceptanceWindow(): void
    {
        // First login consumes a valid TOTP.
        $tfa = new TwoFactorAuth(new BaconQrCodeProvider(4, '#ffffff', '#000000', 'svg'));
        $code = $tfa->getCode(self::TOTP_SECRET);

        $browser1 = ArtifactBrowser::create();
        $baseUrl = ArtifactBrowser::baseUrl();
        $browser1->request(
            'POST',
            $baseUrl . '/interface/main/main_screen.php?auth=login&site=default',
            [
                'authUser' => 'admin',
                'clearPass' => 'pass',
                'languageChoice' => '1',
                'new_login_session_management' => '1',
                'totp' => $code,
                'form_response' => 'true',
            ],
        );
        self::assertSame(
            302,
            $browser1->getResponse()->getStatusCode(),
            'First TOTP submission with a valid code should succeed (302). '
                . 'If this is not 302, the setUp TOTP enrollment did not take effect.',
        );

        // Second login attempt with the SAME code from a fresh session.
        // The replay-protection state (login_mfa_registrations.last_used_token
        // + last_challenge) recorded by the first login must reject this
        // one — even though RobThree would still verify the code as
        // valid within its 90s acceptance window.
        $browser2 = ArtifactBrowser::create();
        $browser2->request(
            'POST',
            $baseUrl . '/interface/main/main_screen.php?auth=login&site=default',
            [
                'authUser' => 'admin',
                'clearPass' => 'pass',
                'languageChoice' => '1',
                'new_login_session_management' => '1',
                'totp' => $code,
                'form_response' => 'true',
            ],
        );
        $response = $browser2->getResponse();
        self::assertSame(
            200,
            $response->getStatusCode(),
            'Replaying the exact same TOTP code within its 90s acceptance window '
                . 'must be rejected (server re-renders the TOTP form, HTTP 200 — not 302). '
                . '302 here means main_screen.php is not going through MfaUtils::checkTOTP '
                . '(no replay protection) or the delegation is broken.',
        );
        self::assertGreaterThan(
            0,
            $browser2->getCrawler()->filterXPath('//input[@name="totp"]')->count(),
            'On replay rejection the server should re-render the TOTP challenge form',
        );
    }
}
