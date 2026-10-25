<?php

/**
 * Behavioral-depth regression gates for the portal api patient routes.
 *
 * Companion to the existing portal api coverage:
 *   - PortalApiSmokeTest             — list/single shape, scope-set
 *                                      equivalence, email-marker
 *                                      isolation on /portal/patient
 *   - PortalApiEncounterPositiveTest — known-UUID encounter positive
 *   - PortalApiAppointmentPositiveTest + PortalApiAppointmentSinglePositiveTest
 *                                    — appointment list/single positive
 *
 * The depth-of-test gap this file closes: cross-patient resource
 * lookups by UUID, encounter-list cross-contamination, and scope-narrowing
 * at the consent step.
 *
 * Deferred scenarios: response-shape stability across token re-mints,
 * and bogus-uuid symmetry across patients. Both exercise the same
 * invariants from the body side, but each requires more mints per
 * test than the shared setUp currently supports (setUp eagerly mints
 * two patient-role tokens per test; adding further mints at the body
 * level cumulatively trips the password-grant attempt counter at the
 * fifth test's setUp). Can be revisited by lazifying setUp.
 *
 * All five portal routes have token-level isolation enforced by
 * $request->getPatientUUIDString() — the controller's puuidBind
 * argument restricts the search layer to the authenticated patient.
 * These tests exercise that path with concrete fixture rows so a
 * future regression that drops puuidBind (or loosens its binding to
 * ignore the token's bound patient) surfaces as a test failure
 * rather than a silent data leak.
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

class PortalApiBehavioralDepthApiTest extends TestCase
{
    private const PORTAL_SCOPE =
        'openid api:port patient/patient.read patient/encounter.read patient/appointment.read';
    private const PORTAL_SCOPE_PATIENT_ONLY =
        'openid api:port patient/patient.read';
    private const ENCOUNTER_REASON_PREFIX = 'test-fixture-portal-depth-enc-';
    private const APPT_TITLE_PREFIX = 'test-fixture-portal-depth-appt-';

    private string $baseUrl;

    // Patient A: setUp-installed; bearer minted via password grant.
    /** @var array<string, mixed>|null */
    private ?array $fixtureA = null;
    private ?string $clientIdA = null;
    private ?string $accessTokenA = null;
    private ?string $encounterUuidA = null;
    private int $encounterIdA = 0;
    private ?string $appointmentUuidA = null;
    private int $appointmentEventIdA = 0;

    // Patient B: installed inline in the isolation test; cleaned up
    // alongside patient A via removePortalPatientFixtures (pubpid
    // prefix match).
    /** @var array<string, mixed>|null */
    private ?array $fixtureB = null;
    private ?string $clientIdB = null;
    private ?string $accessTokenB = null;
    private ?string $encounterUuidB = null;
    private int $encounterIdB = 0;
    private ?string $appointmentUuidB = null;
    private int $appointmentEventIdB = 0;

    private ?PortalPatientFixtureManager $portalFixtures = null;

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

    private bool $siteAddrOathCaptured = false;
    private ?string $originalSiteAddrOathGlobalRow = null;
    private bool $siteAddrOathGlobalRowWasInserted = false;

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

            $this->siteAddrOathCaptured = true;
            $this->persistGlobal(
                'site_addr_oath',
                $this->baseUrl,
                $this->originalSiteAddrOathGlobalRow,
                $this->siteAddrOathGlobalRowWasInserted
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

            // Patient A — fixture + encounter + appointment + password-
            // grant bearer. The encounter + appointment are referenced
            // by patient B's bearer in the cross-patient isolation
            // tests, so install them in setUp (not inline per-test)
            // so the shared references stay valid regardless of test
            // ordering.
            $this->fixtureA = $this->portalFixtures->installPortalPatient(
                portalLoginUsername: 'portaldepthA-' . bin2hex(random_bytes(4)),
                plainPassword: 'PortalDepthPatientAPassword1!',
                email: 'portaldepthA-' . bin2hex(random_bytes(4)) . '@test.example'
            );
            $pidA = $this->fixtureA['pid'];
            $this->encounterUuidA = $this->installEncounterFixture(
                $pidA,
                $this->encounterIdA
            );
            $this->appointmentUuidA = $this->installAppointmentFixture(
                $pidA,
                $this->appointmentEventIdA
            );

            [$cidA, $csecA] = DcrClient::registerConfidential(
                $this->baseUrl,
                'PortalApiBehavioralDepthApiTest-A-' . bin2hex(random_bytes(3)),
                self::PORTAL_SCOPE,
                'password',
            );
            $this->clientIdA = $cidA;
            $mintA = PasswordGrant::mintPatientRoleToken(
                $this->baseUrl,
                $cidA,
                $csecA,
                self::PORTAL_SCOPE,
                $this->fixtureA,
            );
            $this->accessTokenA = $mintA['access_token'];

            // Patient B — fixture + encounter + appointment + bearer.
            // Minted eagerly so every test can rely on both patients
            // being resolved; tests that only need patient A still
            // benefit from the full dual-patient setup for parity.
            $this->fixtureB = $this->portalFixtures->installPortalPatient(
                portalLoginUsername: 'portaldepthB-' . bin2hex(random_bytes(4)),
                plainPassword: 'PortalDepthPatientBPassword1!',
                email: 'portaldepthB-' . bin2hex(random_bytes(4)) . '@test.example'
            );
            $pidB = $this->fixtureB['pid'];
            $this->encounterUuidB = $this->installEncounterFixture(
                $pidB,
                $this->encounterIdB
            );
            $this->appointmentUuidB = $this->installAppointmentFixture(
                $pidB,
                $this->appointmentEventIdB
            );

            [$cidB, $csecB] = DcrClient::registerConfidential(
                $this->baseUrl,
                'PortalApiBehavioralDepthApiTest-B-' . bin2hex(random_bytes(3)),
                self::PORTAL_SCOPE,
                'password',
            );
            $this->clientIdB = $cidB;
            $mintB = PasswordGrant::mintPatientRoleToken(
                $this->baseUrl,
                $cidB,
                $csecB,
                self::PORTAL_SCOPE,
                $this->fixtureB,
            );
            $this->accessTokenB = $mintB['access_token'];
        } catch (\Throwable $setUpFailure) {
            try {
                $this->tearDown();
            } catch (\Throwable $rollbackFailure) {
                throw new \RuntimeException(
                    'setUp failed; rollback via tearDown also failed: '
                        . $rollbackFailure->getMessage(),
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
            foreach ([$this->appointmentEventIdA, $this->appointmentEventIdB] as $eventId) {
                if ($eventId > 0) {
                    QueryUtils::sqlStatementThrowException(
                        'DELETE FROM openemr_postcalendar_events WHERE pc_eid = ?',
                        [$eventId]
                    );
                }
            }
        });
        $this->safeRun(function (): void {
            foreach ([$this->appointmentUuidA, $this->appointmentUuidB] as $uuid) {
                if ($uuid !== null) {
                    QueryUtils::sqlStatementThrowException(
                        "DELETE FROM uuid_registry "
                            . "WHERE table_name = 'openemr_postcalendar_events' AND uuid = ?",
                        [UuidRegistry::uuidToBytes($uuid)]
                    );
                }
            }
        });
        $this->safeRun(function (): void {
            foreach ([$this->encounterIdA, $this->encounterIdB] as $encounterId) {
                if ($encounterId > 0) {
                    QueryUtils::sqlStatementThrowException(
                        'DELETE FROM form_encounter WHERE id = ?',
                        [$encounterId]
                    );
                }
            }
        });
        $this->safeRun(function (): void {
            foreach ([$this->encounterUuidA, $this->encounterUuidB] as $uuid) {
                if ($uuid !== null) {
                    QueryUtils::sqlStatementThrowException(
                        "DELETE FROM uuid_registry "
                            . "WHERE table_name = 'form_encounter' AND uuid = ?",
                        [UuidRegistry::uuidToBytes($uuid)]
                    );
                }
            }
        });
        $this->safeRun(function (): void {
            foreach ([$this->clientIdA, $this->clientIdB] as $clientId) {
                if ($clientId !== null) {
                    DcrClient::remove($clientId);
                }
            }
        });
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
    public function testPatientBBearerCannotReadPatientAEncounterByKnownUuid(): void
    {
        // Core cross-patient isolation invariant for the single-
        // resource encounter route. Patient A's encounter uuid is a
        // valid uuid in the system; patient B's bearer presenting it
        // must NOT return patient A's encounter data. The service
        // layer binds the search to the token's authenticated
        // patient uuid (see EncounterService::getEncounter's
        // $puuidBind parameter plumbed from
        // $request->getPatientUUIDString()), so a correctly scoped
        // lookup resolves to zero rows → 404 via the controller's
        // handleProcessingResult(…, 404) shape.
        $encounterUuidA = $this->encounterUuidA;
        $this->assertIsString($encounterUuidA);
        $accessTokenB = (string) $this->accessTokenB;

        $resp = $this->rawGetWithToken(
            '/apis/default/portal/patient/encounter/' . $encounterUuidA,
            $accessTokenB
        );
        $this->assertSame(
            404,
            $resp['status'],
            'Patient B bearer must NOT return patient A encounter data '
                . 'for patient A encounter uuid. Expected 404, got: '
                . $resp['status'] . '. Body: ' . $resp['body']
        );
        // Belt-and-suspenders: even if a future regression changed
        // the 404 to a 200 + empty-shape, assert the fixture marker
        // from patient A's encounter is NOT present in patient B's
        // response. One-sided check: a marker leak is the
        // regression we care about.
        $this->assertStringNotContainsString(
            self::ENCOUNTER_REASON_PREFIX,
            $resp['body'],
            'Patient B response body must not contain patient A encounter '
                . 'fixture marker. Body: ' . $resp['body']
        );
    }

    #[Test]
    public function testPatientBBearerCannotReadPatientAAppointmentByKnownUuid(): void
    {
        // Parallel cross-patient isolation gate on the appointment
        // single-resource route. AppointmentRestController::getOneForPatient
        // passes both the auuid AND the token's patient uuid to
        // AppointmentService::search as isUuid=true TokenSearchFields —
        // the search layer binds the match to pce.uuid = ? AND
        // patient_data.uuid = ?. A regression that dropped the patient
        // uuid parameter would surface here as patient B getting back
        // the fixture title marker from patient A's appointment.
        $appointmentUuidA = $this->appointmentUuidA;
        $this->assertIsString($appointmentUuidA);
        $accessTokenB = (string) $this->accessTokenB;

        $resp = $this->rawGetWithToken(
            '/apis/default/portal/patient/appointment/' . $appointmentUuidA,
            $accessTokenB
        );
        $this->assertSame(
            404,
            $resp['status'],
            'Patient B bearer must NOT return patient A appointment data '
                . 'for patient A appointment uuid. Expected 404, got: '
                . $resp['status'] . '. Body: ' . $resp['body']
        );
        $this->assertStringNotContainsString(
            self::APPT_TITLE_PREFIX,
            $resp['body'],
            'Patient B response body must not contain patient A appointment '
                . 'fixture title marker. Body: ' . $resp['body']
        );
    }

    #[Test]
    public function testEncounterListIsolatesAcrossPatients(): void
    {
        // List-route companion to the single-resource gates above.
        // Each patient's /portal/patient/encounter response must
        // contain only their own encounter markers. Confirms the
        // encounter list route also honours $puuidBind — a regression
        // that passed null here would return every patient's
        // encounters to every bearer.
        $encounterUuidA = $this->encounterUuidA;
        $encounterUuidB = $this->encounterUuidB;
        $this->assertIsString($encounterUuidA);
        $this->assertIsString($encounterUuidB);

        $respA = $this->rawGetWithToken(
            '/apis/default/portal/patient/encounter',
            (string) $this->accessTokenA
        );
        $respB = $this->rawGetWithToken(
            '/apis/default/portal/patient/encounter',
            (string) $this->accessTokenB
        );

        $this->assertSame(
            200,
            $respA['status'],
            'Patient A encounter list must 200 (patient has 1 encounter). '
                . 'Body: ' . $respA['body']
        );
        $this->assertSame(
            200,
            $respB['status'],
            'Patient B encounter list must 200 (patient has 1 encounter). '
                . 'Body: ' . $respB['body']
        );

        // Each bearer sees its own encounter uuid. The service writes
        // the encounter uuid into the response as the `euuid` field;
        // a plain substring check catches the raw 36-char form
        // regardless of envelope shape.
        $this->assertStringContainsString(
            $encounterUuidA,
            $respA['body'],
            'Patient A list must contain patient A encounter uuid'
        );
        $this->assertStringNotContainsString(
            $encounterUuidB,
            $respA['body'],
            'Patient A list must NOT contain patient B encounter uuid '
                . '(cross-patient encounter leak on list route)'
        );
        $this->assertStringContainsString(
            $encounterUuidB,
            $respB['body'],
            'Patient B list must contain patient B encounter uuid'
        );
        $this->assertStringNotContainsString(
            $encounterUuidA,
            $respB['body'],
            'Patient B list must NOT contain patient A encounter uuid '
                . '(cross-patient encounter leak on list route)'
        );
    }

    #[Test]
    public function testEncounterRouteDeniedWhenScopeOmittedAtMint(): void
    {
        // Scope-narrowing gate: mint a bearer whose granted scope set
        // excludes patient/encounter.read. The portal encounter route
        // key is patient/encounter.read, so AuthorizationListener must
        // reject the request with the AccessDeniedException →
        // HTTP 401 path (see HttpRestRouteHandler's catch).
        //
        // Narrowing the scope at MINT time (not consent time) uses the
        // DCR scope allow-list and the authorize/token-grant pipeline
        // directly — the token's `scope` claim ends up strictly less
        // than the full self::PORTAL_SCOPE set. The /portal/patient
        // route (which only needs patient/patient.read) still works
        // with the same bearer, which proves the deny-path is scoped
        // to the specific resource rather than blanket-denying the
        // whole portal surface.
        $fixtureA = $this->fixtureA;
        $this->assertIsArray($fixtureA);

        [$cidNarrow, $csecNarrow] = DcrClient::registerConfidential(
            $this->baseUrl,
            'PortalApiBehavioralDepthApiTest-Narrow-' . bin2hex(random_bytes(3)),
            self::PORTAL_SCOPE_PATIENT_ONLY,
            'password',
        );
        try {
            $mintNarrow = PasswordGrant::mintPatientRoleToken(
                $this->baseUrl,
                $cidNarrow,
                $csecNarrow,
                self::PORTAL_SCOPE_PATIENT_ONLY,
                $fixtureA,
            );
            $narrowToken = $mintNarrow['access_token'];
            $grantedScopes = $mintNarrow['scopes_granted'];
            // Precondition: narrowed token must not carry
            // patient/encounter.read. If the mint path inadvertently
            // widened the scope back, the deny-path assertion below
            // would be vacuous — surface it as an early fail with a
            // clear message.
            $this->assertNotContains(
                'patient/encounter.read',
                $grantedScopes,
                'Narrowed token should not carry patient/encounter.read; '
                    . 'actual scopes_granted: ' . implode(' ', $grantedScopes)
            );

            // Negative case: encounter route rejected.
            $respEncounter = $this->rawGetWithToken(
                '/apis/default/portal/patient/encounter',
                $narrowToken
            );
            $this->assertSame(
                401,
                $respEncounter['status'],
                'Narrowed token (missing patient/encounter.read) hitting '
                    . '/portal/patient/encounter must 401 via '
                    . 'AccessDeniedException. Got: ' . $respEncounter['status']
                    . ', Body: ' . $respEncounter['body']
            );

            // Positive control: /portal/patient still works with the
            // same bearer because patient/patient.read is granted.
            // Catches a regression where the narrow-mint path
            // broke the whole bearer rather than one resource.
            $respPatient = $this->rawGetWithToken(
                '/apis/default/portal/patient',
                $narrowToken
            );
            $this->assertSame(
                200,
                $respPatient['status'],
                'Narrowed token with patient/patient.read must still reach '
                    . '/portal/patient — the deny-path should be scoped to the '
                    . 'omitted resource, not the whole portal api. '
                    . 'Got: ' . $respPatient['status']
                    . ', Body: ' . $respPatient['body']
            );
        } finally {
            DcrClient::remove($cidNarrow);
        }
    }


    /**
     * Insert an encounter row owned by the given patient and return
     * its uuid-string. $outId is set to the inserted encounter id so
     * teardown can delete the row deterministically.
     */
    private function installEncounterFixture(int $pid, int &$outId): string
    {
        $registry = new UuidRegistry(['table_name' => 'form_encounter']);
        $uuidBytes = $registry->createUuid();
        $uuidString = UuidRegistry::uuidToString($uuidBytes);

        $facilityRow = QueryUtils::querySingleRow(
            'SELECT id FROM facility ORDER BY id LIMIT 1'
        );
        $this->assertIsArray($facilityRow);
        $facilityIdRaw = $facilityRow['id'];
        $this->assertIsNumeric($facilityIdRaw);
        $facilityId = (int) $facilityIdRaw;

        $encounterNumber = time() + random_int(0, 999999);
        $reason = self::ENCOUNTER_REASON_PREFIX . bin2hex(random_bytes(4));

        $outId = QueryUtils::sqlInsert(
            'INSERT INTO form_encounter '
                . '(pid, uuid, encounter, date, reason, facility_id, facility, pc_catid, '
                . 'class_code, sensitivity, provider_id, billing_facility, last_level_billed, '
                . 'last_level_closed, stmt_count, invoice_refno, referral_source, shift, voucher_number) '
                . "VALUES (?, ?, ?, NOW(), ?, ?, '', 5, 'AMB', 'normal', 0, 0, 0, 0, 0, '', '', '', '')",
            [$pid, $uuidBytes, $encounterNumber, $reason, $facilityId]
        );
        return $uuidString;
    }

    /**
     * Insert an appointment row owned by the given patient and return
     * its uuid-string. $outEventId is set to the inserted pc_eid so
     * teardown can delete the row deterministically.
     */
    private function installAppointmentFixture(int $pid, int &$outEventId): string
    {
        $registry = new UuidRegistry(['table_name' => 'openemr_postcalendar_events']);
        $uuidBytes = $registry->createUuid();
        $uuidString = UuidRegistry::uuidToString($uuidBytes);

        $title = self::APPT_TITLE_PREFIX . bin2hex(random_bytes(4));

        $outEventId = QueryUtils::sqlInsert(
            'INSERT INTO openemr_postcalendar_events '
                . '(pc_pid, uuid, pc_title, pc_eventDate, pc_startTime, pc_duration, '
                . 'pc_catid, pc_eventstatus, pc_apptstatus, pc_facility, pc_billing_location) '
                . "VALUES (?, ?, ?, DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY), '10:00:00', 900, "
                . "5, 1, '-', 0, 0)",
            [$pid, $uuidBytes, $title]
        );
        return $uuidString;
    }

    /**
     * @return array{status: int, body: string}
     */
    private function rawGetWithToken(string $path, string $accessToken): array
    {
        $resp = $this->buildClient()->get($this->baseUrl . $path, [
            'headers' => ['Authorization' => 'Bearer ' . $accessToken],
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
            fwrite(
                STDERR,
                '[' . static::class . "::tearDown] cleanup step failed: " . $e->getMessage() . "\n"
            );
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
