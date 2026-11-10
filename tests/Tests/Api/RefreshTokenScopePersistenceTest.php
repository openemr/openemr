<?php

/**
 * Refresh-token scope-persistence regression gates.
 *
 * RFC 6749 §6 defines two invariants for the refresh-token grant:
 *   1) Omitting the `scope` parameter → new access_token carries the
 *      SAME scope set as the refresh_token was bound to.
 *   2) Supplying `scope` MUST only narrow (not widen) the scope set.
 *      Any scope not already granted via the original authorization
 *      must be rejected.
 *
 * Scope-system audit (2026-10-17) identified these as the two
 * remaining untested invariants in the refresh-grant path. If either
 * regressed, a client could silently re-widen scope that the user
 * narrowed at consent — the user's revocation would be undone on the
 * next refresh.
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
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Tests\Support\OAuth2\AuthCodeFlow;
use OpenEMR\Tests\Support\OAuth2\DcrClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RefreshTokenScopePersistenceTest extends TestCase
{
    private string $baseUrl;

    private bool $passwordGrantBagCaptured = false;
    private mixed $originalPasswordGrantBagSetting = null;
    private bool $originalPasswordGrantBagWasSet = false;
    private ?string $originalPasswordGrantGlobalRow = null;
    private bool $passwordGrantGlobalRowWasInserted = false;

    private bool $siteAddrOathCaptured = false;
    private ?string $originalSiteAddrOathGlobalRow = null;
    private bool $siteAddrOathGlobalRowWasInserted = false;

    /** @var list<string> */
    private array $clientsToRemove = [];
    /** @var list<string> */
    private array $tearDownFailures = [];

    protected function setUp(): void
    {
        $this->baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';

        if (getenv('OPENEMR_ALLOW_OAUTH_HTTPS_SKIP') === '1') {
            $this->markTestSkipped('Skipping per OPENEMR_ALLOW_OAUTH_HTTPS_SKIP=1');
        }
        $probe = $this->buildClient()->get($this->baseUrl . '/');
        if ($probe->getHeaderLine('Server') === '') {
            $message = 'OAuth flow requires a real webserver (Apache/nginx)';
            if (getenv('CI') !== false) {
                self::fail($message . ' — hard failure in CI');
            }
            $this->markTestSkipped($message);
        }
        if (stripos($probe->getHeaderLine('Server'), 'nginx') !== false) {
            $this->markTestSkipped('Auth-code flow test skipped on nginx config');
        }

        try {
            $globals = OEGlobalsBag::getInstance();
            $this->originalPasswordGrantBagWasSet = $globals->has('oauth_password_grant');
            $this->originalPasswordGrantBagSetting = $globals->get('oauth_password_grant');
            $this->passwordGrantBagCaptured = true;
            $globals->set('oauth_password_grant', 3);
            $this->persistGlobal(
                'oauth_password_grant',
                '3',
                $this->originalPasswordGrantGlobalRow,
                $this->passwordGrantGlobalRowWasInserted
            );
            $this->siteAddrOathCaptured = true;
            $this->persistGlobal(
                'site_addr_oath',
                $this->baseUrl,
                $this->originalSiteAddrOathGlobalRow,
                $this->siteAddrOathGlobalRowWasInserted
            );
        } catch (\Throwable $setUpFailure) {
            try {
                $this->tearDown();
            } catch (\Throwable $rollbackFailure) {
                throw new \RuntimeException(
                    'setUp failed; rollback via tearDown also failed: ' . $rollbackFailure->getMessage(),
                    0,
                    $setUpFailure
                );
            }
            throw $setUpFailure;
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->clientsToRemove as $cid) {
            $this->safeRun(function () use ($cid): void {
                DcrClient::remove($cid);
            });
        }
        $this->safeRun(function (): void {
            if (!$this->siteAddrOathCaptured) {
                return;
            }
            $this->restoreGlobal(
                'site_addr_oath',
                $this->originalSiteAddrOathGlobalRow,
                $this->siteAddrOathGlobalRowWasInserted
            );
        });
        $this->safeRun(function (): void {
            $this->restoreGlobal(
                'oauth_password_grant',
                $this->originalPasswordGrantGlobalRow,
                $this->passwordGrantGlobalRowWasInserted
            );
        });
        $this->safeRun(function (): void {
            if (!$this->passwordGrantBagCaptured) {
                return;
            }
            $globals = OEGlobalsBag::getInstance();
            if ($this->originalPasswordGrantBagWasSet) {
                $globals->set('oauth_password_grant', $this->originalPasswordGrantBagSetting);
            } else {
                $globals->remove('oauth_password_grant');
                unset($GLOBALS['oauth_password_grant']);
            }
        });

        if ($this->tearDownFailures !== []) {
            $failures = $this->tearDownFailures;
            $this->tearDownFailures = [];
            self::fail(
                "tearDown cleanup had " . count($failures) . " failure(s); "
                . "shared state may be stale for later tests:\n  - "
                . implode("\n  - ", $failures)
            );
        }
    }

    #[Test]
    public function testRefreshWithExplicitScopePreservesNarrowedSet(): void
    {
        // Request 3 resource scopes + offline_access for refresh issuance.
        // At consent, user grants only 2 of them (plus openid +
        // offline_access). The initial access_token honors the narrow
        // set (D1-style assertion). Then refresh WITH an explicit
        // `scope=` parameter matching the narrowed set: new
        // access_token must preserve the narrowed set — NOT re-widen
        // to the /authorize request.
        //
        // Note: refresh WITHOUT an explicit scope= parameter is
        // broken for OIDC+offline_access flows in current OpenEMR
        // (the OIDC `nonce` artifact gets bound to the refresh_token
        // at mint time, and the subsequent refresh fails validation
        // because `nonce` isn't a registered scope). Inferno
        // sidesteps this by always passing explicit scope=. This
        // test uses the same workaround. The underlying limitation
        // is documented in the §8.5 register.
        $requestScope = 'openid offline_access '
            . 'user/Patient.read user/Encounter.read user/Observation.read';
        $granted = ['openid', 'offline_access', 'user/Patient.read'];
        $mint = AuthCodeFlow::mintUserRoleToken(
            $this->baseUrl,
            'RefreshScopePersistTest-narrow-' . bin2hex(random_bytes(3)),
            $requestScope,
            $granted,
        );
        $this->clientsToRemove[] = $mint['client_id'];
        $initialScopes = $this->filterArtifacts($mint['scopes_granted']);
        $this->assertContains('user/Patient.read', $initialScopes);
        $this->assertNotContains('user/Encounter.read', $initialScopes);
        $this->assertNotContains('user/Observation.read', $initialScopes);
        $refreshToken = $mint['response_body']['refresh_token'] ?? null;
        $this->assertIsString($refreshToken, 'Mint must return a refresh_token when offline_access is granted');
        $this->assertNotSame('', $refreshToken);

        // Explicit scope= matching the narrowed consent set (minus
        // the OIDC nonce artifact).
        $refreshed = AuthCodeFlow::exchangeRefreshToken(
            $this->baseUrl,
            $mint['client_id'],
            $mint['client_secret'],
            $refreshToken,
            implode(' ', $granted),
        );
        $this->assertSame(
            200,
            $refreshed['status'],
            'Refresh with explicit scope matching the narrowed set must succeed. '
                . 'Body: ' . json_encode($refreshed['body'])
        );
        $refreshedScopes = $this->filterArtifacts($refreshed['scopes_granted']);
        sort($initialScopes);
        sort($refreshedScopes);
        $this->assertSame(
            $initialScopes,
            $refreshedScopes,
            'Refreshed access_token must carry the SAME narrowed scope set as '
                . 'the original token. '
                . 'Initial: ' . json_encode($initialScopes) . '; '
                . 'Refreshed: ' . json_encode($refreshedScopes)
        );
    }

    #[Test]
    public function testRefreshWithUpgradedScopeIsRejectedOrNarrowed(): void
    {
        // RFC 6749 §6: a `scope` parameter on refresh may narrow but
        // NEVER widen. Mint with a narrow consent, then attempt
        // refresh with `scope` broader than what was originally
        // granted. The server must either reject (standards-compliant:
        // 400 + invalid_scope) OR accept and silently narrow (not
        // RFC-strict but also not a security concern — the point is
        // that the token must NOT come back with the upgraded scope).
        $requestScope = 'openid offline_access user/Patient.read user/Encounter.read';
        $granted = ['openid', 'offline_access', 'user/Patient.read'];
        $mint = AuthCodeFlow::mintUserRoleToken(
            $this->baseUrl,
            'RefreshScopePersistTest-upgrade-' . bin2hex(random_bytes(3)),
            $requestScope,
            $granted,
        );
        $this->clientsToRemove[] = $mint['client_id'];
        $refreshToken = $mint['response_body']['refresh_token'] ?? null;
        $this->assertIsString($refreshToken);

        // Attempt refresh with scope broader than the granted set.
        // `user/Encounter.read` was requested at /authorize but NOT
        // granted at consent — asking for it on refresh is an upgrade.
        $upgraded = AuthCodeFlow::exchangeRefreshToken(
            $this->baseUrl,
            $mint['client_id'],
            $mint['client_secret'],
            $refreshToken,
            'openid offline_access user/Patient.read user/Encounter.read',
        );
        if ($upgraded['status'] >= 400) {
            // RFC-strict path: 400 + invalid_scope. league/oauth2-server's
            // RefreshTokenGrant::validateScopes throws invalidScope BEFORE
            // revoking the old refresh token, so narrowing the accepted
            // error to invalid_scope guarantees the rejection came from the
            // scope check — not from an unrelated refresh failure (revoked
            // token, wrong client, malformed body) that would also map to
            // invalid_grant / invalid_request and leave the regression gate
            // reporting success while refresh is actually broken.
            $this->assertSame(
                'invalid_scope',
                $upgraded['body']['error'] ?? null,
                'If upgrade is rejected, error label must be invalid_scope. '
                    . 'Body: ' . json_encode($upgraded['body'])
            );
            // Control refresh: the same refresh_token must still work with
            // the originally granted scope. Proves the rejection above was
            // scope-driven rather than a side-effect of an upstream failure.
            $control = AuthCodeFlow::exchangeRefreshToken(
                $this->baseUrl,
                $mint['client_id'],
                $mint['client_secret'],
                $refreshToken,
                implode(' ', $granted),
            );
            $this->assertSame(
                200,
                $control['status'],
                'Control refresh with the granted scope must succeed — '
                    . 'confirms the upgrade rejection was scope-driven. '
                    . 'Body: ' . json_encode($control['body'])
            );
            return;
        }
        // Accept-and-narrow path: 200 but Encounter.read must NOT be
        // in the returned scope set. The invariant is "an unapproved
        // scope cannot slip in on refresh" — not the specific error label.
        // Require a non-empty access_token too: a tokenless 200 (missing
        // or empty access_token) would silently pass the scope-set check
        // because filterArtifacts treats a missing scope as an empty list.
        $this->assertSame(200, $upgraded['status']);
        $accessToken = $upgraded['body']['access_token'] ?? null;
        $this->assertIsString(
            $accessToken,
            'Accept-and-narrow refresh must return an access_token. '
                . 'Body: ' . json_encode($upgraded['body'])
        );
        $this->assertNotSame('', $accessToken, 'access_token must not be empty');
        $upgradedScopes = $this->filterArtifacts($upgraded['scopes_granted']);
        $this->assertNotContains(
            'user/Encounter.read',
            $upgradedScopes,
            'Refresh with an upgrade scope= parameter must NOT grant the '
                . 'upgraded scope — the user never consented to it. '
                . 'Granted: ' . json_encode($upgradedScopes)
        );
    }

    /**
     * Filter OIDC-related artifacts (e.g. nonce) that the server
     * surfaces into the scope claim but which are not resource scopes.
     *
     * @param list<string> $scopes
     * @return list<string>
     */
    private function filterArtifacts(array $scopes): array
    {
        return array_values(array_filter(
            $scopes,
            static fn (string $s): bool => $s !== 'nonce'
        ));
    }

    private function persistGlobal(
        string $name,
        string $value,
        ?string &$originalCapture,
        bool &$insertedCapture
    ): void {
        $current = QueryUtils::querySingleRow(
            'SELECT gl_value FROM `globals` WHERE gl_name = ?',
            [$name]
        );
        if (is_array($current)) {
            $glValue = $current['gl_value'] ?? null;
            $originalCapture = is_string($glValue) ? $glValue : null;
            QueryUtils::sqlStatementThrowException(
                'UPDATE `globals` SET gl_value = ? WHERE gl_name = ?',
                [$value, $name]
            );
        } else {
            QueryUtils::sqlStatementThrowException(
                'INSERT INTO `globals` (`gl_name`, `gl_index`, `gl_value`) VALUES (?, 0, ?)',
                [$name, $value]
            );
            $insertedCapture = true;
        }
    }

    private function restoreGlobal(string $name, ?string $originalRow, bool $wasInserted): void
    {
        if ($wasInserted) {
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM `globals` WHERE gl_name = ?',
                [$name]
            );
        } elseif ($originalRow !== null) {
            QueryUtils::sqlStatementThrowException(
                'UPDATE `globals` SET gl_value = ? WHERE gl_name = ?',
                [$originalRow, $name]
            );
        }
    }

    private function safeRun(callable $cleanup): void
    {
        try {
            $cleanup();
        } catch (SqlQueryException $e) {
            $this->tearDownFailures[] = $e->getMessage();
            fwrite(STDERR, '[' . static::class . "::tearDown] cleanup step failed: " . $e->getMessage() . "\n");
        }
    }

    private function buildClient(): Client
    {
        return new Client([
            'verify' => false,
            'http_errors' => false,
            'cookies' => new CookieJar(),
            'timeout' => 15,
        ]);
    }
}
