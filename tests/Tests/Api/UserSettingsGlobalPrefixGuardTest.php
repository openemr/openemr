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
    /** @var list<string> labels of user_settings rows this test created or may have created */
    private array $labelsToCleanUp = [];

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
        foreach ($this->labelsToCleanUp as $label) {
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM `user_settings` WHERE `setting_user` = ? AND `setting_label` = ?",
                [$this->adminUserId, $label]
            );
        }
        $this->labelsToCleanUp = [];
    }

    #[Test]
    public function testUppercaseGlobalPrefixIsRejectedOutsideAllowlist(): void
    {
        $csrf = $this->loginAndExtractCsrf();
        $uppercaseLabel = 'GLOBAL:gbl_ldap_host';
        // Covers the case-equivalent labels the DB's case-insensitive
        // collation would merge the uppercase write into.
        $this->labelsToCleanUp[] = $uppercaseLabel;
        $this->labelsToCleanUp[] = 'global:gbl_ldap_host';

        $before = $this->countLabelsLike('global:gbl_ldap_host');

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

        $after = $this->countLabelsLike('global:gbl_ldap_host');
        $this->assertSame(
            $before,
            $after,
            'An uppercase `GLOBAL:` prefix must be rejected by the write-side guard; '
                . 'no row with a case-equivalent setting_label may be created'
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
        $this->labelsToCleanUp[] = $allowlistedLabel;

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

    private function countLabelsLike(string $pattern): int
    {
        $row = QueryUtils::querySingleRow(
            "SELECT COUNT(*) AS n FROM `user_settings` "
                . "WHERE `setting_user` = ? AND `setting_label` LIKE ?",
            [$this->adminUserId, $pattern]
        );
        if (!is_array($row) || !isset($row['n']) || !is_numeric($row['n'])) {
            return 0;
        }
        return (int) $row['n'];
    }
}
