<?php

/**
 * Invariant test for proposal point 8: "Single mode — no hybrid auth."
 *
 * When OIDC is configured as the authentication method for an install,
 * no credential path may yield an authenticated session. The proposal's
 * security justification: users removed from the IdP must not be able to
 * continue authenticating against OpenEMR's local user store through a
 * parallel path.
 *
 * The invariant is expressed as behavior, not as a check against any
 * particular flag shape, so it survives implementation changes. Today
 * there are two globals (`oidc_enabled`, `oidc_local_login_disabled`)
 * and the hybrid configuration `(1, 0)` accepts credential POSTs; under
 * point 8 the `oidc_local_login_disabled` flag disappears and "OIDC is
 * configured" alone means local login is off.
 *
 * Test shape: apply the minimal OIDC configuration point 8 prescribes
 * (`oidc_enabled=1` plus issuer and client id) and actively clear the
 * legacy `oidc_local_login_disabled` flag so the DB matches the point-8
 * shape (flag does not exist). POST `authUser`/`clearPass` to the login
 * endpoint with a valid admin credential and read the response. OpenEMR
 * returns 302 to `/interface/main/tabs/main.php?token_main=...` for a
 * successful login and 200 (login page re-rendered) for a rejected one;
 * the 302-with-authenticated-target signal is what the test asserts must
 * not occur.
 *
 * Expected trajectory:
 *   - Red today: hybrid mode allows the POST to establish a session.
 *   - Green once point 8 collapses the modes.
 *   - Stays green after `oidc_local_login_disabled` is removed.
 *
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Integration\Common\Auth\Oidc;

use GuzzleHttp\Client;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\TestCase;

final class OidcModeExcludesCredentialAuthTest extends TestCase
{
    private const LOGIN_POST_PATH = '/interface/main/main_screen.php?auth=login&site=default';
    /**
     * Path fragment the login endpoint redirects to on successful
     * authentication ("/interface/main/tabs/main.php?token_main=..."). A
     * rejected POST returns 200 and re-renders the login form, so seeing
     * this fragment in the Location header is an unambiguous "session was
     * established" signal.
     */
    private const AUTHENTICATED_REDIRECT_FRAGMENT = '/interface/main/tabs/main.php';
    private const ADMIN_USERNAME = 'admin';
    private const ADMIN_PASSWORD = 'pass';
    private const OIDC_ISSUER = 'http://oidc-mock:9400';
    private const OIDC_CLIENT_ID = 'openemr-point-8-invariant';

    /**
     * Minimal `module_gcip_config` values that mean "OIDC is configured."
     * Firebase-specific keys are intentionally omitted: the invariant is
     * phrased at the OIDC layer, not the Firebase-renderer layer.
     *
     * @var array<string, string>
     */
    private const GCIP_CONFIG = [
        'gcip_issuer' => self::OIDC_ISSUER,
        'gcip_client_id' => self::OIDC_CLIENT_ID,
    ];

    private Client $http;
    private ?string $originalOidcEnabled = null;
    private ?string $originalOidcLocalLoginDisabled = null;
    /** @var array<string, ?string> */
    private array $originalGcipConfig = [];

    protected function setUp(): void
    {
        if (getenv('DISABLE_DATABASE') === '1') {
            self::markTestSkipped('Integration test requires database');
        }

        $baseUrl = getenv('OPENEMR_BASE_URL_API') ?: 'https://localhost';
        $this->http = new Client([
            'base_uri' => $baseUrl,
            'verify' => false,
            'http_errors' => false,
            'allow_redirects' => false,
            'timeout' => 10,
        ]);

        $this->originalOidcEnabled = $this->getGlobal('oidc_enabled');
        $this->originalOidcLocalLoginDisabled = $this->getGlobal('oidc_local_login_disabled');
        foreach (array_keys(self::GCIP_CONFIG) as $key) {
            $this->originalGcipConfig[$key] = $this->getGcipConfig($key);
        }

        $this->setGlobal('oidc_enabled', '1');
        // Actively clear `oidc_local_login_disabled` so the test exercises the
        // point-8-shaped config (the flag does not exist). Under the current
        // PR's hybrid-aware code this is the configuration that permits
        // credential POSTs; under point 8 the flag is gone and the clear is a
        // noop. Either way the test expresses: "OIDC configured -> no
        // credential path to a session," independent of this legacy flag.
        $this->clearGlobal('oidc_local_login_disabled');
        foreach (self::GCIP_CONFIG as $key => $value) {
            $this->setGcipConfig($key, $value);
        }
    }

    protected function tearDown(): void
    {
        if (getenv('DISABLE_DATABASE') === '1') {
            return;
        }

        $this->restoreGlobal('oidc_enabled', $this->originalOidcEnabled);
        $this->restoreGlobal('oidc_local_login_disabled', $this->originalOidcLocalLoginDisabled);
        foreach ($this->originalGcipConfig as $key => $originalValue) {
            $this->restoreGcipConfig($key, $originalValue);
        }
    }

    public function testCredentialPostDoesNotYieldSessionWhenOidcIsConfigured(): void
    {
        $response = $this->http->post(self::LOGIN_POST_PATH, [
            'form_params' => [
                'authUser' => self::ADMIN_USERNAME,
                'clearPass' => self::ADMIN_PASSWORD,
                'new_login_session_management' => '1',
                'languageChoice' => '1',
            ],
        ]);

        // The login endpoint's response shape is binary: 302 to the
        // authenticated tabs route on success, 200 (login page re-render)
        // on failure. A 302 whose Location targets the authenticated tabs
        // route means the credential POST established a session.
        $status = $response->getStatusCode();
        $location = $response->getHeaderLine('Location');
        $authenticated = $status === 302
            && str_contains($location, self::AUTHENTICATED_REDIRECT_FRAGMENT);

        self::assertFalse(
            $authenticated,
            sprintf(
                'Credential POST to %s yielded an authenticated session even'
                . ' though OIDC is configured. Proposal point 8 requires'
                . ' single-mode auth: when OIDC is configured, no credential'
                . ' path may produce a session (otherwise users removed from'
                . ' the IdP retain local access through the parallel path).'
                . ' Response status: %d. Location: %s.',
                self::LOGIN_POST_PATH,
                $status,
                $location !== '' ? $location : '(none)',
            ),
        );
    }

    private function getGlobal(string $name): ?string
    {
        $rows = QueryUtils::fetchRecords(
            'SELECT gl_value FROM globals WHERE gl_name = ? LIMIT 1',
            [$name],
        );
        $value = $rows[0]['gl_value'] ?? null;
        return is_string($value) ? $value : null;
    }

    private function setGlobal(string $name, string $value): void
    {
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO globals (gl_name, gl_value) VALUES (?, ?)'
            . ' ON DUPLICATE KEY UPDATE gl_value = VALUES(gl_value)',
            [$name, $value],
        );
    }

    private function clearGlobal(string $name): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM globals WHERE gl_name = ?',
            [$name],
        );
    }

    private function restoreGlobal(string $name, ?string $originalValue): void
    {
        if ($originalValue === null) {
            $this->clearGlobal($name);
            return;
        }

        $this->setGlobal($name, $originalValue);
    }

    private function getGcipConfig(string $key): ?string
    {
        $rows = QueryUtils::fetchRecords(
            'SELECT config_value FROM module_gcip_config WHERE config_key = ? LIMIT 1',
            [$key],
        );
        $value = $rows[0]['config_value'] ?? null;
        return is_string($value) ? $value : null;
    }

    private function setGcipConfig(string $key, string $value): void
    {
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO module_gcip_config (config_key, config_value) VALUES (?, ?)'
            . ' ON DUPLICATE KEY UPDATE config_value = VALUES(config_value)',
            [$key, $value],
        );
    }

    private function restoreGcipConfig(string $key, ?string $originalValue): void
    {
        if ($originalValue === null) {
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM module_gcip_config WHERE config_key = ?',
                [$key],
            );
            return;
        }

        $this->setGcipConfig($key, $originalValue);
    }
}
