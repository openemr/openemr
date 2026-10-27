<?php

/**
 * Consent-page scope-narrowing invariant.
 *
 * OpenEMR's consent page (/oauth2/{site}/scope-authorize-confirm)
 * renders a checkbox per requested scope. The user can uncheck
 * individual scopes before clicking "proceed." The resulting access
 * token must carry only the scopes the user actually granted — a
 * regression here would mean an unchecked scope ends up in the
 * token anyway (user intent ignored, server defaults to the full
 * requested set).
 *
 * Covers both staff (user_role=api) and patient (user_role=portal-api)
 * flows since both share the same AuthorizationController consent
 * handler.
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
use OpenEMR\Tests\Fixtures\PortalPatientFixtureManager;
use OpenEMR\Tests\Support\OAuth2\AuthCodeFlow;
use OpenEMR\Tests\Support\OAuth2\DcrClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ConsentScopeNarrowingTest extends TestCase
{
    private string $baseUrl;
    /** @var array<string, mixed>|null */
    private ?array $fixture = null;
    private ?PortalPatientFixtureManager $portalFixtures = null;

    private bool $passwordGrantBagCaptured = false;
    private mixed $originalPasswordGrantBagSetting = null;
    private bool $originalPasswordGrantBagWasSet = false;
    private ?string $originalPasswordGrantGlobalRow = null;
    private bool $passwordGrantGlobalRowWasInserted = false;

    private bool $siteAddrOathCaptured = false;
    private ?string $originalSiteAddrOathGlobalRow = null;
    private bool $siteAddrOathGlobalRowWasInserted = false;

    /** @var list<string> client_ids registered during tests for cleanup */
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
            $this->markTestSkipped(
                'Auth-code flow test skipped on nginx config'
            );
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

            // Point site_addr_oath at the container-reachable baseUrl so
            // the auth-code flow's server-emitted redirects resolve.
            $this->siteAddrOathCaptured = true;
            $this->persistGlobal(
                'site_addr_oath',
                $this->baseUrl,
                $this->originalSiteAddrOathGlobalRow,
                $this->siteAddrOathGlobalRowWasInserted
            );

            $this->portalFixtures = new PortalPatientFixtureManager();
            $this->fixture = $this->portalFixtures->installPortalPatient(
                portalLoginUsername: 'consent-' . bin2hex(random_bytes(4)),
                plainPassword: 'ConsentScopeNarrowingPassword1!',
                email: 'consent-' . bin2hex(random_bytes(4)) . '@test.example'
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
            $this->portalFixtures?->removePortalPatientFixtures();
        });
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
    public function testUserRoleTokenNarrowsScopesPerConsentSelection(): void
    {
        // Request three resource scopes; grant only Patient.read at
        // consent. Note: api:* scopes are intentionally stripped
        // from the response-body `scope` field by
        // IdTokenSMARTResponse::getScopeString (ONC-cert
        // workaround), so the test uses user/* scopes which surface
        // normally.
        $scope = 'openid user/Patient.read user/Encounter.read user/Observation.read';
        $mint = AuthCodeFlow::mintUserRoleToken(
            $this->baseUrl,
            'ConsentScopeNarrowingTest-user-' . bin2hex(random_bytes(3)),
            $scope,
            ['openid', 'user/Patient.read'],
        );
        $this->clientsToRemove[] = $mint['client_id'];
        $granted = $this->filterNonSemanticArtifacts($mint['scopes_granted']);

        $this->assertContains('openid', $granted, 'openid was granted; must appear in token');
        $this->assertContains(
            'user/Patient.read',
            $granted,
            'user/Patient.read was granted; must appear in token'
        );
        $this->assertNotContains(
            'user/Encounter.read',
            $granted,
            'user/Encounter.read was requested at /authorize but NOT granted at consent — '
                . 'must NOT appear in the issued token. Granted: ' . json_encode($granted)
        );
        $this->assertNotContains(
            'user/Observation.read',
            $granted,
            'user/Observation.read was requested but NOT granted at consent — '
                . 'must NOT appear in the issued token. Granted: ' . json_encode($granted)
        );
    }

    #[Test]
    public function testPatientRoleTokenNarrowsScopesPerConsentSelection(): void
    {
        $fixture = $this->fixture;
        $this->assertIsArray($fixture);

        // Request two resource scopes; grant only one at consent.
        $scope = 'openid api:port patient/patient.read patient/encounter.read';
        $mint = AuthCodeFlow::mintPatientRoleToken(
            $this->baseUrl,
            'ConsentScopeNarrowingTest-patient-' . bin2hex(random_bytes(3)),
            $scope,
            $fixture,
            ['openid', 'api:port', 'patient/patient.read'],
        );
        $this->clientsToRemove[] = $mint['client_id'];
        $granted = $this->filterNonSemanticArtifacts($mint['scopes_granted']);

        $this->assertContains('openid', $granted);
        $this->assertContains('patient/patient.read', $granted, 'patient/patient.read was granted; must appear');
        $this->assertNotContains(
            'patient/encounter.read',
            $granted,
            'patient/encounter.read was requested at /authorize but NOT granted at consent — '
                . 'must NOT appear in the issued token. Granted: ' . json_encode($granted)
        );
    }

    #[Test]
    public function testFullConsentGrantsAllRequestedScopes(): void
    {
        // Positive-control: when the user DOES check every box (the
        // default behavior AuthCodeFlow emulates when no override),
        // every requested scope must appear in the token. If a future
        // refactor broke the consent intersection, this test would
        // regress by failing the "should-be-there" assertion.
        // Same api:* strip caveat as above — use user/* scopes.
        $scope = 'openid user/Patient.read user/Encounter.read';
        $mint = AuthCodeFlow::mintUserRoleToken(
            $this->baseUrl,
            'ConsentScopeNarrowingTest-full-' . bin2hex(random_bytes(3)),
            $scope,
        );
        $this->clientsToRemove[] = $mint['client_id'];
        $granted = $this->filterNonSemanticArtifacts($mint['scopes_granted']);

        $this->assertContains('openid', $granted);
        $this->assertContains('user/Patient.read', $granted);
        $this->assertContains('user/Encounter.read', $granted);
    }

    /**
     * Filter OIDC-related artifacts the server surfaces into the
     * scope claim (e.g. nonce) so the test asserts against the
     * resource-scope semantic set only.
     *
     * @param list<string> $scopes
     * @return list<string>
     */
    private function filterNonSemanticArtifacts(array $scopes): array
    {
        return array_values(array_filter($scopes, static fn (string $s): bool => $s !== 'nonce'));
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
