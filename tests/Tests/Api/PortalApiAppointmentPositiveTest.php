<?php

/**
 * Positive-shape coverage for GET /portal/patient/appointment{,/:auuid}.
 *
 * Companion to the AppointmentRestController fix in this PR:
 * previously `getAllForPatientByUuid` returned a bare array that
 * RestControllerHelper::responseHandler mapped to 404 whenever the
 * list was empty, so a fresh portal patient with no appointments
 * got a wrong 404 instead of the correct 200 + empty-list response.
 * Fixed via a listResponse() helper that preserves the 404-on-falsy
 * single-resource semantic while treating [] as 200 for list routes.
 *
 * Tests here lock the fix:
 *   - empty-list → 200 + "[]"
 *   - populated list → 200 + the fixture appointment
 *   - single resource unknown UUID → 404 (preserves the single-
 *     resource not-found semantic unchanged)
 *
 * Deferred (not covered here, see inline comment near the test slot):
 *   - single resource known UUID → 200 — needs AppointmentService
 *     search-field plumbing to resolve a fixture row via
 *     getOneForPatient's FhirSearchWhereClauseBuilder path. Follow-up.
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

class PortalApiAppointmentPositiveTest extends TestCase
{
    private const PORTAL_SCOPE = 'openid api:port patient/patient.read patient/appointment.read';
    private const BOGUS_UUID = '00000000-0000-4000-8000-000000000000';
    private const APPT_TITLE_PREFIX = 'test-fixture-portal-appt-';

    private string $baseUrl;
    private ?string $clientId = null;
    /** @var array<string, mixed>|null */
    private ?array $fixture = null;
    private ?PortalPatientFixtureManager $portalFixtures = null;
    private ?string $accessToken = null;
    private ?string $appointmentUuid = null;
    private int $appointmentEventId = 0;

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
        if (stripos($probe->getHeaderLine('Server'), 'nginx') !== false) {
            $this->markTestSkipped(
                'Portal API routes not reachable under the nginx test config'
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
                portalLoginUsername: 'portalappt-' . bin2hex(random_bytes(4)),
                plainPassword: 'PortalApptPositiveTestPassword1!',
                email: 'portalappt-' . bin2hex(random_bytes(4)) . '@test.example'
            );

            [$cid, $csec] = DcrClient::registerConfidential(
                $this->baseUrl,
                'PortalApiAppointmentPositiveTest-' . bin2hex(random_bytes(3)),
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
            if ($this->appointmentEventId > 0) {
                QueryUtils::sqlStatementThrowException(
                    'DELETE FROM openemr_postcalendar_events WHERE pc_eid = ?',
                    [$this->appointmentEventId]
                );
            }
        });
        $this->safeRun(function (): void {
            if ($this->appointmentUuid !== null) {
                QueryUtils::sqlStatementThrowException(
                    "DELETE FROM uuid_registry WHERE table_name = 'openemr_postcalendar_events' AND uuid = ?",
                    [UuidRegistry::uuidToBytes($this->appointmentUuid)]
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
    public function testGetPortalAppointmentListReturnsEmpty200WhenNoAppointments(): void
    {
        // The regression this test locks: previously, a fresh portal
        // patient with no appointments got a 404 from the appointment
        // list route because responseHandler treated [] as "not found."
        // Fixed in this PR via AppointmentRestController::listResponse —
        // empty list is now 200 + "[]".
        $resp = $this->rawGet('/apis/default/portal/patient/appointment');
        $this->assertSame(
            200,
            $resp['status'],
            'GET /portal/patient/appointment with zero appointments must return 200, '
                . 'not 404. Body: ' . $resp['body']
        );
        $this->assertSame(
            '[]',
            trim($resp['body']),
            'Empty appointment list body must be "[]" (preserves the pre-fix '
                . 'top-level indexed-array shape for existing API consumers).'
        );
    }

    #[Test]
    public function testGetPortalAppointmentListReturnsPopulated200WhenAppointmentsExist(): void
    {
        // Install an appointment tied to the portal patient, assert
        // the list route returns 200 + a response containing the
        // fixture marker.
        $fixture = $this->fixture;
        $this->assertIsArray($fixture);
        $pid = $fixture['pid'];
        $this->assertIsInt($pid);
        $this->appointmentUuid = $this->installAppointmentFixture($pid);

        $resp = $this->rawGet('/apis/default/portal/patient/appointment');
        $this->assertSame(
            200,
            $resp['status'],
            'GET /portal/patient/appointment with 1 appointment must return 200. Body: '
                . $resp['body']
        );
        $this->assertStringContainsString(
            self::APPT_TITLE_PREFIX,
            $resp['body'],
            'Populated appointment list must contain the fixture marker.'
        );
    }

    // Known-UUID single-resource positive is intentionally not
    // covered here. getOneForPatient routes through
    // AppointmentService::search() with pc_uuid + puuid parameters
    // via FhirSearchWhereClauseBuilder; wiring the fixture row so
    // that search resolves it requires more plumbing than the plain
    // insert used by the list-coverage tests above. Follow-up can
    // layer that on using the helper library + a dedicated search-
    // field investigation. The unknown-UUID 404 test below covers
    // the single-resource shape invariant that this PR's fix must
    // not regress.

    #[Test]
    public function testGetPortalAppointmentByUnknownUuidReturns404(): void
    {
        // Single-resource lookups preserve the 404-on-not-found
        // semantic unchanged — only list routes were adjusted.
        $resp = $this->rawGet('/apis/default/portal/patient/appointment/' . self::BOGUS_UUID);
        $this->assertSame(
            404,
            $resp['status'],
            'GET /portal/patient/appointment/{unknown-uuid} must return 404. '
                . 'Status: ' . $resp['status'] . ', Body: ' . $resp['body']
        );
    }

    private function installAppointmentFixture(int $pid): string
    {
        $registry = new UuidRegistry(['table_name' => 'openemr_postcalendar_events']);
        $uuidBytes = $registry->createUuid();
        $uuidString = UuidRegistry::uuidToString($uuidBytes);

        $title = self::APPT_TITLE_PREFIX . bin2hex(random_bytes(4));

        QueryUtils::sqlStatementThrowException(
            'INSERT INTO openemr_postcalendar_events '
                . '(pc_pid, uuid, pc_title, pc_eventDate, pc_startTime, pc_duration, pc_catid, pc_eventstatus, pc_apptstatus, pc_facility, pc_billing_location) '
                . "VALUES (?, ?, ?, DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY), '10:00:00', 900, 5, 1, '-', 0, 0)",
            [$pid, $uuidBytes, $title]
        );
        $idRow = QueryUtils::querySingleRow('SELECT LAST_INSERT_ID() AS id');
        $this->assertIsArray($idRow);
        $idRaw = $idRow['id'];
        $this->assertIsNumeric($idRaw);
        $this->appointmentEventId = (int) $idRaw;
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
