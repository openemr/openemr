<?php

/**
 * Regression gate for the `global:*` prefix match in user_settings.php.
 *
 * The write side (`library/ajax/user_settings.php`) must case-insensitively
 * match the `global:` prefix because the read side's `setting_label LIKE
 * 'global:%'` is case-insensitive under `utf8mb4_general_ci`. A future
 * refactor that swaps `stripos` for `str_starts_with` would let mixed-case
 * `GLOBAL:*` keys write to `user_settings` outside the per-user allowlist;
 * this test pins that invariant.
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
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class UserSettingsGlobalPrefixGuardTest extends TestCase
{
    private string $baseUrl = '';
    private Client $http;
    private int $adminUserId = 0;
    /**
     * Pre-test snapshots keyed by `<setting_user>|<setting_label>`.
     * Value is the original `setting_value` string if the row existed,
     * or `null` if the row was absent before the test (in which case
     * tearDown should DELETE anything the test inserted at that key).
     * Admin's own setting_label is case-literal; `setting_user=0`
     * default rows are tracked separately when `setUserSetting()`
     * created one during the test.
     *
     * @var array<string, ?string>
     */
    private array $userSettingsSnapshot = [];

    protected function setUp(): void
    {
        if (getenv('OPENEMR_ALLOW_OAUTH_HTTPS_SKIP') === '1') {
            $this->markTestSkipped('Skipping per OPENEMR_ALLOW_OAUTH_HTTPS_SKIP=1');
        }
        $this->baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';
        $this->http = new Client([
            'verify' => false,
            'http_errors' => false,
            'cookies' => new CookieJar(),
            'allow_redirects' => true,
            'timeout' => 30,
        ]);
        if ($this->http->get($this->baseUrl . '/')->getHeaderLine('Server') === '') {
            $message = 'user_settings guard test requires a real webserver (Apache/nginx)';
            if (getenv('CI') !== false) {
                self::fail($message . ' — hard failure in CI');
            }
            $this->markTestSkipped($message);
        }

        $adminRow = QueryUtils::querySingleRow(
            "SELECT `id` FROM `users` WHERE `username` = 'admin' AND `active` = 1"
        );
        $rawId = is_array($adminRow) ? ($adminRow['id'] ?? null) : null;
        if (!is_numeric($rawId) || (int) $rawId === 0) {
            $this->markTestSkipped('admin user not present in this test environment');
        }
        $this->adminUserId = (int) $rawId;
    }

    protected function tearDown(): void
    {
        foreach ($this->userSettingsSnapshot as $key => $originalValue) {
            [$userIdStr, $label] = explode('|', $key, 2);
            $userId = (int) $userIdStr;
            if ($originalValue === null) {
                // Row was absent before the test — delete anything the
                // test (or the write path's side-effect default insert)
                // left behind at this (user_id, label).
                QueryUtils::sqlStatementThrowException(
                    "DELETE FROM `user_settings` "
                        . "WHERE `setting_user` = ? AND `setting_label` = ?",
                    [$userId, $label]
                );
            } else {
                // Row existed — restore its exact pre-test value.
                QueryUtils::sqlStatementThrowException(
                    "UPDATE `user_settings` SET `setting_value` = ? "
                        . "WHERE `setting_user` = ? AND `setting_label` = ?",
                    [$originalValue, $userId, $label]
                );
            }
        }
        $this->userSettingsSnapshot = [];
    }

    #[Test]
    public function testUppercaseGlobalPrefixIsRejectedOutsideAllowlist(): void
    {
        $csrf = $this->loginAndExtractCsrf();
        $uppercaseLabel = 'GLOBAL:gbl_ldap_host';

        // Explicitly register the two keys the write path (admin + the
        // setting_user=0 default) could land at for absent-state tracking
        // — if the fix regresses and a forbidden row gets inserted, the
        // assertion fails and tearDown must still clean up the stray row.
        // Without these snapshots, an all-absent-pre-test regression would
        // leave a dirty DB.
        $this->snapshotUserSetting($this->adminUserId, $uppercaseLabel);
        $this->snapshotUserSetting(0, $uppercaseLabel);
        // Snapshot every case-equivalent existing row too so we can detect
        // both an inserted row AND a value-mutated existing row.
        $caseEquivalentRows = $this->snapshotLabelsLike('global:gbl_ldap_host');

        $response = $this->http->post($this->baseUrl . '/library/ajax/user_settings.php', [
            'form_params' => [
                'csrf_token_form' => $csrf,
                'lab' => $uppercaseLabel,
                'val' => 'ldap://attacker.invalid:389',
            ],
            'allow_redirects' => false,
        ]);
        $this->assertSame(
            200,
            $response->getStatusCode(),
            'user_settings.php returns 200 on both allowed and silently-rejected keys — assert on the DB, not the status'
        );

        $afterRows = $this->readLabelsLike('global:gbl_ldap_host');
        $this->assertSame(
            $caseEquivalentRows,
            $afterRows,
            'An uppercase `GLOBAL:` prefix must be rejected by the write-side guard — '
                . 'no case-equivalent row may be inserted AND no existing row may be mutated. '
                . 'A regression that admits `GLOBAL:*` may update an existing row without '
                . 'changing row count, so both the shape and values are compared.'
        );
    }

    #[Test]
    public function testLowercaseAllowlistedPerUserKeyIsStored(): void
    {
        // Positive control: an allowlisted plain per-user key (no `global:`
        // prefix) must still write as a normal per-user setting — the
        // case-insensitive prefix guard applies only to the `global:*`
        // branch and must not break legitimate per-user writes.
        $csrf = $this->loginAndExtractCsrf();
        $allowlistedLabel = 'theme_tabs_layout';

        // Snapshot both admin's row (what the test asserts on) and the
        // setting_user=0 default that `setUserSetting()`'s createDefault
        // side-effect may insert if absent. Both are restored on tearDown.
        $this->snapshotUserSetting($this->adminUserId, $allowlistedLabel);
        $this->snapshotUserSetting(0, $allowlistedLabel);

        $expectedValue = 'test-value-' . bin2hex(random_bytes(4));

        $response = $this->http->post($this->baseUrl . '/library/ajax/user_settings.php', [
            'form_params' => [
                'csrf_token_form' => $csrf,
                'lab' => $allowlistedLabel,
                'val' => $expectedValue,
            ],
            'allow_redirects' => false,
        ]);
        $this->assertSame(200, $response->getStatusCode());

        $row = QueryUtils::querySingleRow(
            "SELECT `setting_value` FROM `user_settings` "
                . "WHERE `setting_user` = ? AND `setting_label` = ?",
            [$this->adminUserId, $allowlistedLabel]
        );
        $this->assertIsArray(
            $row,
            'A non-prefixed allowlisted key must be written as a normal per-user setting'
        );
        $this->assertSame($expectedValue, $row['setting_value'] ?? null);
    }

    /**
     * Login as admin via the main_screen web-login endpoint, fetch a page
     * that carries a csrf_token in the rendered body, extract and return
     * that token. The token is session-bound and valid for the
     * csrf_token_form field on POSTs within the same session.
     */
    private function loginAndExtractCsrf(): string
    {
        $this->http->get($this->baseUrl . '/interface/login/login.php?site=default');
        $this->http->post(
            $this->baseUrl . '/interface/main/main_screen.php?auth=login&site=default',
            [
                'form_params' => [
                    'authUser' => 'admin',
                    'clearPass' => 'pass',
                    'languageChoice' => '1',
                    'new_login_session_management' => '1',
                ],
            ]
        );
        // /interface/language/language.php renders csrf_token_form with the
        // default CSRF subject — the subject user_settings.php verifies
        // against. Pages that use a non-default subject (e.g. admin-client
        // which scopes its token as 'ClientAdminController') won't match.
        $body = (string) $this->http->get($this->baseUrl . '/interface/language/language.php')->getBody();
        $this->assertSame(
            1,
            preg_match('/name="csrf_token_form"\s+value="([0-9a-f]+)"/', $body, $match),
            'logged-in page should expose a csrf_token_form input bound to the default subject'
        );
        return $match[1];
    }

    /**
     * Read every row whose setting_label is case-insensitively equal to
     * `$pattern`, across all setting_user rows (admin + the setting_user=0
     * default), and register each result for restoration in tearDown. The
     * returned array is keyed `<setting_user>|<setting_label>` with the
     * `setting_value` string — same shape {@see self::readLabelsLike()}
     * returns post-mutation, so two captures compare directly with
     * assertSame.
     *
     * @return array<string, string>
     */
    private function snapshotLabelsLike(string $pattern): array
    {
        $rows = $this->readLabelsLike($pattern);
        foreach ($rows as $key => $value) {
            if (!array_key_exists($key, $this->userSettingsSnapshot)) {
                $this->userSettingsSnapshot[$key] = $value;
            }
        }
        return $rows;
    }

    /**
     * @return array<string, string>
     */
    private function readLabelsLike(string $pattern): array
    {
        $rows = QueryUtils::fetchRecords(
            "SELECT `setting_user`, `setting_label`, `setting_value` "
                . "FROM `user_settings` WHERE `setting_label` LIKE ?",
            [$pattern]
        );
        $out = [];
        foreach ($rows as $row) {
            $userId = isset($row['setting_user']) && is_numeric($row['setting_user'])
                ? (int) $row['setting_user']
                : 0;
            $label = isset($row['setting_label']) && is_string($row['setting_label'])
                ? $row['setting_label']
                : '';
            $value = isset($row['setting_value']) && is_string($row['setting_value'])
                ? $row['setting_value']
                : '';
            $out[$userId . '|' . $label] = $value;
        }
        return $out;
    }

    /**
     * Snapshot a specific (setting_user, setting_label) row so tearDown
     * can restore its value (or delete if absent). Idempotent — repeated
     * calls in the same test do not overwrite the initial snapshot.
     */
    private function snapshotUserSetting(int $settingUser, string $label): void
    {
        $key = $settingUser . '|' . $label;
        if (array_key_exists($key, $this->userSettingsSnapshot)) {
            return;
        }
        $row = QueryUtils::querySingleRow(
            "SELECT `setting_value` FROM `user_settings` "
                . "WHERE `setting_user` = ? AND `setting_label` = ?",
            [$settingUser, $label]
        );
        $this->userSettingsSnapshot[$key] = (is_array($row) && isset($row['setting_value']) && is_string($row['setting_value']))
            ? $row['setting_value']
            : null;
    }
}
