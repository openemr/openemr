<?php

/**
 * End-to-end mint-level coverage for public-client PKCE.
 *
 * `AuthorizationGrantPublicClientPkceTest` has 3 api tests at the
 * /authorize level (missing code_challenge, method downgrade,
 * verifier mismatch) but no full-flow mint via a public client.
 *
 * Scope-system audit (2026-10-17) flagged this gap: Inferno uses
 * only confidential clients, so public-client PKCE is NOT part of
 * the cert surface. Adding mint-level coverage here closes the
 * public-client leg of the auth-code grant matrix.
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

class PublicClientPkceMintTest extends TestCase
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
    public function testPublicClientMintSucceedsWithValidPkce(): void
    {
        // DCR a public client (application_type=web → no client_secret
        // issued; PKCE required), run the full auth-code flow with
        // S256 PKCE, and assert the access_token comes back with the
        // requested scope set. The verifier presented at /token proves
        // the client is the one that initiated at /authorize.
        $scope = 'openid patient/Patient.read';
        $mint = AuthCodeFlow::mintUserRolePublicClient(
            $this->baseUrl,
            'PublicClientPkceMintTest-' . bin2hex(random_bytes(3)),
            $scope,
        );
        $this->clientsToRemove[] = $mint['client_id'];

        $this->assertNotSame('', $mint['access_token']);

        $granted = $this->filterArtifacts($mint['scopes_granted']);
        $this->assertContains('openid', $granted);
        $this->assertContains(
            'patient/Patient.read',
            $granted,
            'Public-client PKCE mint must return a token with the requested '
                . 'resource scope. Granted: ' . json_encode($granted)
        );

        // No client_secret present in the mint result — this is a
        // public client. Confirm the shape.
        $this->assertArrayNotHasKey(
            'client_secret',
            $mint,
            'Public client should not have a client_secret'
        );
    }

    #[Test]
    public function testPublicClientMintNarrowsPerConsentOverride(): void
    {
        // Combine public-client PKCE + consent-scope-narrowing in one
        // flow. Requests three resource scopes, grants one, confirms
        // the public-client leg of the matrix behaves identically to
        // the confidential-client leg (ConsentScopeNarrowingTest):
        // narrowed set in the token, upgrades absent.
        $scope = 'openid patient/Patient.read patient/Encounter.read patient/Observation.read';
        $mint = AuthCodeFlow::mintUserRolePublicClient(
            $this->baseUrl,
            'PublicClientPkceMintTest-narrow-' . bin2hex(random_bytes(3)),
            $scope,
            ['openid', 'patient/Patient.read'],
        );
        $this->clientsToRemove[] = $mint['client_id'];

        $granted = $this->filterArtifacts($mint['scopes_granted']);
        $this->assertContains('patient/Patient.read', $granted);
        $this->assertNotContains(
            'patient/Encounter.read',
            $granted,
            'Public-client consent narrowing must drop unchecked scopes same as confidential. '
                . 'Granted: ' . json_encode($granted)
        );
        $this->assertNotContains(
            'patient/Observation.read',
            $granted,
            'Public-client consent narrowing must drop unchecked scopes same as confidential. '
                . 'Granted: ' . json_encode($granted)
        );
    }

    #[Test]
    public function testPublicClientScopeDowngradeOnRefreshIsHonored(): void
    {
        // RFC 6749 §6 allows a client to request a NARROWER scope on
        // refresh via the `scope` parameter. A public-client mint
        // issues a refresh token (offline_access scope); the refresh
        // exchange with a narrower scope= must succeed and return a
        // token with only that narrower scope.
        $requestScope = 'openid offline_access patient/Patient.read patient/Encounter.read';
        $mint = AuthCodeFlow::mintUserRolePublicClient(
            $this->baseUrl,
            'PublicClientPkceMintTest-downgrade-' . bin2hex(random_bytes(3)),
            $requestScope,
        );
        $this->clientsToRemove[] = $mint['client_id'];
        $refreshToken = $mint['response_body']['refresh_token'] ?? null;
        $this->assertIsString(
            $refreshToken,
            'Public-client mint with offline_access must return refresh_token. '
                . 'Response body: ' . json_encode($mint['response_body'])
        );

        // Public clients don't use client_secret on refresh — null
        // omits the field entirely from the token POST (RFC 6749 §6
        // + §2.3 — public clients have no credentials beyond
        // client_id). Request a narrower scope.
        $refreshed = AuthCodeFlow::exchangeRefreshToken(
            $this->baseUrl,
            $mint['client_id'],
            null,
            $refreshToken,
            'openid offline_access patient/Patient.read',
        );
        $this->assertSame(
            200,
            $refreshed['status'],
            'Public-client refresh (no client_secret) with narrower scope must succeed. '
                . 'Status: ' . $refreshed['status']
                . ', Body: ' . json_encode($refreshed['body'])
        );
        $downgradedScopes = $this->filterArtifacts($refreshed['scopes_granted']);
        $this->assertNotContains(
            'patient/Encounter.read',
            $downgradedScopes,
            'Downgrade on refresh must drop the scope that was omitted. '
                . 'Granted: ' . json_encode($downgradedScopes)
        );
        $this->assertContains('patient/Patient.read', $downgradedScopes);
    }

    /**
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
