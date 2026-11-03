<?php

/**
 * Positive-shape coverage for GET /portal/patient/encounter/{euuid}.
 *
 * PR #14591's PortalApiSmokeTest covers the list route + an unknown-UUID
 * 404 shape. The single-UUID positive path (patient has an encounter
 * fixture; GET by that encounter's UUID returns 200 + the encounter
 * data) was deferred — it needs an encounter row tied to the portal
 * patient's pid. This file closes that gap.
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
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Tests\Fixtures\PortalPatientFixtureManager;
use OpenEMR\Tests\Support\OAuth2\DcrClient;
use OpenEMR\Tests\Support\OAuth2\PasswordGrant;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PortalApiEncounterPositiveTest extends TestCase
{
    private const PORTAL_SCOPE = 'openid api:port patient/patient.read patient/encounter.read';
    private const ENCOUNTER_REASON_PREFIX = 'test-fixture-portal-encounter-';

    private string $baseUrl;
    private ?string $clientId = null;
    /** @var array<string, mixed>|null */
    private ?array $fixture = null;
    private ?PortalPatientFixtureManager $portalFixtures = null;
    private ?string $accessToken = null;
    private ?string $encounterUuid = null;
    private int $encounterId = 0;

    private bool $passwordGrantBagCaptured = false;
    private mixed $originalPasswordGrantBagSetting = null;
    private bool $originalPasswordGrantBagWasSet = false;
    private ?string $originalPasswordGrantGlobalRow = null;
    private bool $passwordGrantGlobalRowWasInserted = false;

    private bool $portalApiBagCaptured = false;
    private mixed $originalPortalApiBagSetting = null;
    private bool $originalPortalApiBagWasSet = false;
    private ?string $originalPortalApiGlobalRow = null;
    private bool $portalApiGlobalRowWasInserted = false;

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
        // nginx test env doesn't route /apis/* to PHP — same reason the
        // sibling PortalApiSmokeTest skips there.
        if (stripos($probe->getHeaderLine('Server'), 'nginx') !== false) {
            $this->markTestSkipped(
                'Portal API routes not reachable under the nginx test config — '
                . 'apache lane covers these invariants.'
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

            $this->originalPortalApiBagWasSet = $globals->has('rest_portal_api');
            $this->originalPortalApiBagSetting = $globals->get('rest_portal_api');
            $this->portalApiBagCaptured = true;
            $globals->set('rest_portal_api', 1);
            $this->persistGlobal(
                'rest_portal_api',
                '1',
                $this->originalPortalApiGlobalRow,
                $this->portalApiGlobalRowWasInserted
            );

            $this->portalFixtures = new PortalPatientFixtureManager();
            $this->fixture = $this->portalFixtures->installPortalPatient(
                portalLoginUsername: 'portalenc-' . bin2hex(random_bytes(4)),
                plainPassword: 'PortalEncPositiveTestPassword1!',
                email: 'portalenc-' . bin2hex(random_bytes(4)) . '@test.example'
            );

            // Install an encounter fixture tied to this patient.
            // PortalPatientFixtureManager::installPortalPatient's
            // return type pins `pid` to int already — no runtime
            // narrow needed.
            $pid = $this->fixture['pid'];
            $this->encounterUuid = $this->installEncounterFixture($pid);

            [$cid, $csec] = DcrClient::registerConfidential(
                $this->baseUrl,
                'PortalApiEncounterPositiveTest-' . bin2hex(random_bytes(3)),
                self::PORTAL_SCOPE,
                'password',
            );
            $this->clientId = $cid;
            $mint = PasswordGrant::mintPatientRoleToken(
                $this->baseUrl,
                $cid,
                $csec,
                self::PORTAL_SCOPE,
                $this->fixture,
            );
            $this->accessToken = $mint['access_token'];
        } catch (\Throwable $setUpFailure) {
            // Catch Throwable (not just SqlQueryException) because
            // PHPUnit does not call tearDown() when setUp() throws.
            // DcrClient / PasswordGrant assertion failures and Guzzle
            // network errors would otherwise leave fixture state
            // (patient, encounter row, globals, oauth client) behind.
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
        $this->safeRun(function (): void {
            if ($this->encounterId > 0) {
                QueryUtils::sqlStatementThrowException(
                    'DELETE FROM form_encounter WHERE id = ?',
                    [$this->encounterId]
                );
            }
        });
        $this->safeRun(function (): void {
            if ($this->encounterUuid !== null) {
                QueryUtils::sqlStatementThrowException(
                    "DELETE FROM uuid_registry WHERE table_name = 'form_encounter' AND uuid = ?",
                    [UuidRegistry::uuidToBytes($this->encounterUuid)]
                );
            }
        });
        $this->safeRun(function (): void {
            if ($this->clientId !== null) {
                DcrClient::remove($this->clientId);
            }
        });
        $this->safeRun(function (): void {
            $this->portalFixtures?->removePortalPatientFixtures();
        });
        $this->safeRun(function (): void {
            $this->restoreGlobal(
                'rest_portal_api',
                $this->originalPortalApiGlobalRow,
                $this->portalApiGlobalRowWasInserted
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
            if (!$this->portalApiBagCaptured) {
                return;
            }
            $globals = OEGlobalsBag::getInstance();
            if ($this->originalPortalApiBagWasSet) {
                $globals->set('rest_portal_api', $this->originalPortalApiBagSetting);
            } else {
                $globals->remove('rest_portal_api');
                unset($GLOBALS['rest_portal_api']);
            }
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
    public function testGetPortalEncounterByKnownUuidReturnsEncounterShape(): void
    {
        $encounterUuid = $this->encounterUuid;
        $this->assertIsString($encounterUuid);
        $resp = $this->rawGet('/apis/default/portal/patient/encounter/' . $encounterUuid);
        $this->assertSame(
            200,
            $resp['status'],
            'GET /portal/patient/encounter/{known-uuid} must return 200 '
                . 'for an encounter owned by the authenticated patient. Body: '
                . $resp['body']
        );
        $body = json_decode($resp['body'], true);
        $this->assertIsArray($body);
        // Narrow to the returned data record so the marker assertion
        // proves the ENCOUNTER row was actually in the response, not
        // just somewhere in the top-level envelope (headers, pagination
        // links, etc).
        $this->assertArrayHasKey('data', $body);
        $this->assertIsArray($body['data']);
        $dataJson = json_encode($body['data'], JSON_THROW_ON_ERROR);
        $this->assertStringContainsString(
            self::ENCOUNTER_REASON_PREFIX,
            $dataJson,
            'Response data must contain the fixture encounter marker, '
                . 'confirming the encounter row was actually returned'
        );
    }

    private function installEncounterFixture(int $pid): string
    {
        // Reserve a uuid via the registry so EncounterService lookups
        // resolve it correctly. createUuid() writes the uuid_registry
        // row immediately, so record the string on the instance here
        // (not just at the return site) so teardown can clean up the
        // registry row if any later step in this method throws.
        $registry = new UuidRegistry(['table_name' => 'form_encounter']);
        $uuidBytes = $registry->createUuid();
        $uuidString = UuidRegistry::uuidToString($uuidBytes);
        $this->encounterUuid = $uuidString;

        // Pick any seeded facility — any valid facility id will do; the
        // route handler returns whichever encounter is bound to the
        // patient regardless of facility.
        $facilityRow = QueryUtils::querySingleRow(
            'SELECT id FROM facility ORDER BY id LIMIT 1'
        );
        $this->assertIsArray($facilityRow);
        $facilityIdRaw = $facilityRow['id'];
        $this->assertIsNumeric($facilityIdRaw);
        $facilityId = (int) $facilityIdRaw;

        // encounter column: just pick a high unique integer.
        $encounterNumber = time() + random_int(0, 999);
        $reason = self::ENCOUNTER_REASON_PREFIX . bin2hex(random_bytes(4));

        // Capture the ID at the insert boundary via sqlInsert's
        // return value. Doing a follow-up LAST_INSERT_ID() query
        // leaves a window where encounterId stays 0 if the lookup
        // fails — teardown would then skip deleting the inserted row.
        $this->encounterId = QueryUtils::sqlInsert(
            'INSERT INTO form_encounter '
                . '(pid, uuid, encounter, date, reason, facility_id, facility, pc_catid, class_code, sensitivity, provider_id, billing_facility, last_level_billed, last_level_closed, stmt_count, invoice_refno, referral_source, shift, voucher_number) '
                . "VALUES (?, ?, ?, NOW(), ?, ?, '', 5, 'AMB', 'normal', 0, 0, 0, 0, 0, '', '', '', '')",
            [$pid, $uuidBytes, $encounterNumber, $reason, $facilityId]
        );
        return $uuidString;
    }

    /**
     * @return array{status: int, body: string}
     */
    private function rawGet(string $path): array
    {
        $resp = $this->buildClient()->get($this->baseUrl . $path, [
            'headers' => ['Authorization' => 'Bearer ' . (string) $this->accessToken],
        ]);
        return [
            'status' => $resp->getStatusCode(),
            'body' => (string) $resp->getBody(),
        ];
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
