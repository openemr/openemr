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
 * verifier (as it did historically). A regression that reverted to the
 * inline verifier — or accidentally removed the delegation call —
 * would fail #1 (login broken) or #2 (replay-window silently permissive)
 * before this file existed.
 *
 * MfaUtils has unit-level replay coverage; that test proves the helper
 * behaves correctly in isolation but does NOT prove main_screen.php
 * actually calls it. This api-suite test proves the wire-up end-to-end
 * by driving the actual /interface/main/main_screen.php login form over
 * HTTP the same way a real browser would.
 *
 * Lives in tests/Tests/Api/ (not tests/Acceptance/) because the setUp
 * seeds an MFA row into login_mfa_registrations via QueryUtils, which
 * requires in-container DB access. Acceptance tests run against a
 * black-box artifact with no DB adapter.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RobThree\Auth\Providers\Qr\BaconQrCodeProvider;
use RobThree\Auth\TwoFactorAuth;
use Symfony\Component\DomCrawler\Crawler;

class WebLoginTotpFlowTest extends TestCase
{
    private const TOTP_SECRET = 'JBSWY3DPEHPK3PXP';

    private string $baseUrl;
    private int $adminUserId = 0;
    /** @var list<array<string, mixed>> */
    private array $originalAdminMfaRows = [];

    protected function setUp(): void
    {
        $this->baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';

        if (getenv('OPENEMR_ALLOW_OAUTH_HTTPS_SKIP') === '1') {
            $this->markTestSkipped('Skipping per OPENEMR_ALLOW_OAUTH_HTTPS_SKIP=1');
        }
        $probe = $this->buildClient()->get($this->baseUrl . '/');
        if ($probe->getHeaderLine('Server') === '') {
            $message = 'Web login requires a real webserver (Apache/nginx) that serves session cookies with Secure';
            if (getenv('CI') !== false) {
                self::fail($message . ' — hard failure in CI');
            }
            $this->markTestSkipped($message);
        }

        $adminRow = QueryUtils::querySingleRow(
            "SELECT id FROM users WHERE username = 'admin' AND active = 1"
        );
        $adminId = $adminRow['id'] ?? null;
        if (!is_numeric($adminId) || (int) $adminId === 0) {
            $this->markTestSkipped('admin user not present in this test environment');
        }
        $this->adminUserId = (int) $adminId;

        // Snapshot admin MFA rows so tearDown restores exactly whatever
        // was there before this test.
        /** @var list<array<string, mixed>> $rows */
        $rows = QueryUtils::fetchRecords(
            "SELECT user_id, name, method, var1, var2, last_challenge, last_used_token "
                . "FROM login_mfa_registrations WHERE user_id = ?",
            [$this->adminUserId]
        );
        $this->originalAdminMfaRows = $rows;

        // Install a known TOTP registration so we can compute valid codes
        // deterministically. Encrypt with the standard crypto key so
        // MfaUtils::checkTOTP's decrypt path finds it without the
        // password-based-crypto fallback.
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

    #[Test]
    public function testWebLoginWithValidTotpCompletesLogin(): void
    {
        // Step 1: POST username + password. With TOTP enrolled the server
        // renders the TOTP challenge form (200), instead of the 302
        // redirect that a MFA-free login would return.
        $http = $this->buildClient(allowRedirects: false);
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

        // Step 2: submit a valid TOTP. Compute from the same secret we
        // enrolled in setUp; deterministic per current 30s slot.
        $tfa = new TwoFactorAuth(new BaconQrCodeProvider(4, '#ffffff', '#000000', 'svg'));
        $validCode = $tfa->getCode(self::TOTP_SECRET);
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
        $tfa = new TwoFactorAuth(new BaconQrCodeProvider(4, '#ffffff', '#000000', 'svg'));
        $code = $tfa->getCode(self::TOTP_SECRET);

        // First login consumes a valid TOTP.
        $http1 = $this->buildClient(allowRedirects: false);
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
                . 'If this is not 302, the setUp TOTP enrollment did not take effect.'
        );

        // Second login attempt with the SAME code from a fresh session.
        // The replay-protection state recorded by the first login must
        // reject this one — even though RobThree would still verify
        // the code as valid within its 90s acceptance window.
        $http2 = $this->buildClient(allowRedirects: false);
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

    private function buildClient(bool $allowRedirects = true): Client
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
