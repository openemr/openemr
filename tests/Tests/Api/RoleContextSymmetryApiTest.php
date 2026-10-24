<?php

/**
 * Role-context symmetry regression gates across the route matrix.
 *
 * OpenEMR issues three token-role types: users-role (staff/admin), patient-
 * role (portal), and system-role (client_credentials). The dispatcher's
 * AuthorizationListener gates every REST request on the pairing of the
 * token's user_role and the route family it lands on. Mismatches must
 * reject.
 *
 * Known-covered points in the matrix (do NOT re-add here):
 *   - OAuth2AttackScenarioTest::testUserRoleTokenCannotReachPortalRoute
 *   - OAuth2AttackScenarioTest::testPatientRoleTokenCannotReachStandardApiRoute
 *
 * This suite extends the known-covered points into a representative sweep
 * across every family × mismatched-role cell so the matrix as a whole is
 * locked, not just two POINTS in it. For each cell we pick 1-3 routes and
 * assert the request does not succeed (status != 200). Representative
 * sampling, not exhaustive — the invariant being locked is dispatcher
 * behaviour, which is route-family-wide.
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
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Tests\Fixtures\PortalPatientFixtureManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @phpstan-type HttpResponse array{status: int, body: string, headers: array<array<string>>}
 */
class RoleContextSymmetryApiTest extends TestCase
{
    private string $baseUrl;
    /** @var list<string> */
    private array $registeredClientIds = [];
    /** @var list<BulkAPITestClient> */
    private array $bulkClients = [];
    private ?PortalPatientFixtureManager $portalFixtures = null;
    private mixed $originalPasswordGrantSetting = null;
    private bool $originalPasswordGrantSettingWasSet = false;
    private ?string $savedSiteAddrOath = null;

    protected function setUp(): void
    {
        $this->baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';

        $globals = OEGlobalsBag::getInstance();
        $this->originalPasswordGrantSettingWasSet = $globals->has('oauth_password_grant');
        $this->originalPasswordGrantSetting = $globals->get('oauth_password_grant');

        if (getenv('OPENEMR_ALLOW_OAUTH_HTTPS_SKIP') === '1') {
            $this->markTestSkipped('Skipping per OPENEMR_ALLOW_OAUTH_HTTPS_SKIP=1');
        }
        $probe = $this->buildClient()->get($this->baseUrl . '/');
        if ($probe->getHeaderLine('Server') === '') {
            if (getenv('CI') !== false) {
                self::fail('OAuth flow requires a real webserver (Apache/nginx) — hard failure in CI');
            }
            $this->markTestSkipped('OAuth flow requires a real webserver');
        }
        // Enable password grant for staff + portal tokens.
        $globals->set('oauth_password_grant', 3);

        // Align site_addr_oath with $baseUrl so the JWT client-assertion's
        // 'aud' claim matches what the server expects. Mirrors
        // GroupExportFhirApiTest::setUp — same reason: dev compose points
        // site_addr_oath at an external host:port, but tests run inside
        // the container where the oauth server is at https://localhost.
        $row = QueryUtils::querySingleRow("SELECT gl_value FROM globals WHERE gl_name = 'site_addr_oath'");
        $value = is_array($row) ? ($row['gl_value'] ?? null) : null;
        $this->savedSiteAddrOath = is_string($value) ? $value : null;
        QueryUtils::sqlStatementThrowException(
            "UPDATE globals SET gl_value = ? WHERE gl_name = 'site_addr_oath'",
            [$this->baseUrl]
        );
    }

    protected function tearDown(): void
    {
        try {
            // Bulk API clients self-manage their DCR row through
            // cleanupClient(); call that for each system-token mint we
            // made during the test so the oauth_clients row is removed.
            foreach ($this->bulkClients as $bulk) {
                $bulk->cleanupRevokeAuth();
                $bulk->cleanupClient();
            }
            if ($this->portalFixtures !== null) {
                $this->portalFixtures->removePortalPatientFixtures();
            }
            foreach ($this->registeredClientIds as $clientId) {
                QueryUtils::sqlStatementThrowException(
                    'DELETE FROM `oauth_trusted_user` WHERE `client_id` = ?',
                    [$clientId]
                );
                QueryUtils::sqlStatementThrowException(
                    'DELETE FROM `api_token` WHERE `client_id` = ?',
                    [$clientId]
                );
                QueryUtils::sqlStatementThrowException(
                    'DELETE FROM `api_refresh_token` WHERE `client_id` = ?',
                    [$clientId]
                );
                QueryUtils::sqlStatementThrowException(
                    'DELETE FROM `oauth_clients` WHERE `client_id` = ?',
                    [$clientId]
                );
            }
        } finally {
            $globals = OEGlobalsBag::getInstance();
            if ($this->originalPasswordGrantSettingWasSet) {
                $globals->set('oauth_password_grant', $this->originalPasswordGrantSetting);
            } else {
                $globals->remove('oauth_password_grant');
                unset($GLOBALS['oauth_password_grant']);
            }
            if ($this->savedSiteAddrOath !== null) {
                QueryUtils::sqlStatementThrowException(
                    "UPDATE globals SET gl_value = ? WHERE gl_name = 'site_addr_oath'",
                    [$this->savedSiteAddrOath]
                );
                $this->savedSiteAddrOath = null;
            }
        }
    }

    // ===== Cell 1: patient-role token × standard /api routes =====
    //
    // Standard-api routes are gated at AuthorizationListener line 199:
    // requestUserRole === 'users' AND scopeType === 'user'. A patient-role
    // token satisfies neither. The known-covered point is /api/facility;
    // these three cover other families (patient demographics, appointment,
    // practitioner directory).

    #[Test]
    public function testPatientRoleTokenCannotReachStandardApiPatient(): void
    {
        $token = $this->mintPatientRoleToken('CellOnePatient', 'openid api:oemr api:port patient/Patient.read');
        $resp = $this->rawGet('/apis/default/api/patient', $this->bearerHeaders($token));
        $this->assertRoleContextDeny($resp, 'Patient-role token must not reach standard-api /api/patient');
    }

    #[Test]
    public function testPatientRoleTokenCannotReachStandardApiAppointment(): void
    {
        $token = $this->mintPatientRoleToken('CellOneAppointment', 'openid api:oemr api:port patient/Patient.read');
        $resp = $this->rawGet('/apis/default/api/appointment', $this->bearerHeaders($token));
        $this->assertRoleContextDeny($resp, 'Patient-role token must not reach standard-api /api/appointment');
    }

    #[Test]
    public function testPatientRoleTokenCannotReachStandardApiPractitioner(): void
    {
        $token = $this->mintPatientRoleToken('CellOnePractitioner', 'openid api:oemr api:port patient/Patient.read');
        $resp = $this->rawGet('/apis/default/api/practitioner', $this->bearerHeaders($token));
        $this->assertRoleContextDeny($resp, 'Patient-role token must not reach standard-api /api/practitioner');
    }

    // ===== Cell 2: user-role token × /portal routes =====
    //
    // Portal routes are gated at AuthorizationListener line 204:
    // requestUserRole === 'patient' AND scopeType === 'patient'. A users-
    // role token has user_role === 'users', so the gate rejects before
    // the dispatcher consults any scope. The known-covered point is
    // /portal/patient; these two cover the other portal families
    // (encounter, appointment).

    #[Test]
    public function testUserRoleTokenCannotReachPortalEncounter(): void
    {
        $token = $this->mintUserRoleTokenPasswordGrant('CellTwoEncounter', 'openid api:oemr api:port user/Patient.read');
        $resp = $this->rawGet('/apis/default/portal/patient/encounter', $this->bearerHeaders($token));
        $this->assertRoleContextDeny($resp, 'User-role token must not reach /portal/patient/encounter');
    }

    #[Test]
    public function testUserRoleTokenCannotReachPortalAppointment(): void
    {
        $token = $this->mintUserRoleTokenPasswordGrant('CellTwoAppointment', 'openid api:oemr api:port user/Patient.read');
        $resp = $this->rawGet('/apis/default/portal/patient/appointment', $this->bearerHeaders($token));
        $this->assertRoleContextDeny($resp, 'User-role token must not reach /portal/patient/appointment');
    }

    // ===== Cell 3: patient-role token × non-patient-compartment FHIR =====
    //
    // FHIR non-patient-compartment resources (Practitioner, Organization,
    // Location, PractitionerRole) have no patient/*.read scope mapping.
    // A patient-role token's patient/* scopes do not contain the user/*
    // scope the dispatcher builds for these routes. The invariant is
    // locked: patient tokens cannot read provider-directory FHIR data.

    #[Test]
    public function testPatientRoleTokenCannotReachFhirPractitioner(): void
    {
        $token = $this->mintPatientRoleToken('CellThreePractitioner', 'openid api:fhir patient/Patient.read');
        $resp = $this->rawGet('/apis/default/fhir/Practitioner', $this->bearerHeaders($token));
        $this->assertRoleContextDeny($resp, 'Patient-role token must not reach /fhir/Practitioner');
    }

    #[Test]
    public function testPatientRoleTokenCannotReachFhirOrganization(): void
    {
        $token = $this->mintPatientRoleToken('CellThreeOrganization', 'openid api:fhir patient/Patient.read');
        $resp = $this->rawGet('/apis/default/fhir/Organization', $this->bearerHeaders($token));
        $this->assertRoleContextDeny($resp, 'Patient-role token must not reach /fhir/Organization');
    }

    #[Test]
    public function testPatientRoleTokenCannotReachFhirLocation(): void
    {
        $token = $this->mintPatientRoleToken('CellThreeLocation', 'openid api:fhir patient/Patient.read');
        $resp = $this->rawGet('/apis/default/fhir/Location', $this->bearerHeaders($token));
        $this->assertRoleContextDeny($resp, 'Patient-role token must not reach /fhir/Location');
    }

    // ===== Cell 4: system-role token × standard /api routes =====
    //
    // System tokens are issued via client_credentials with a JWT client
    // assertion — no user context. AuthorizationListener's standard-api
    // gate requires requestUserRole === 'users', which a system token
    // never has. Even though the known-covered scope-downgrade tests
    // exercise FHIR $export deny paths, the standard-api cell for
    // system tokens is not covered in the attack suite.

    #[Test]
    public function testSystemRoleTokenCannotReachStandardApiFacility(): void
    {
        $token = $this->mintSystemRoleToken();
        $resp = $this->rawGet('/apis/default/api/facility', $this->bearerHeaders($token));
        $this->assertRoleContextDeny($resp, 'System-role token must not reach standard-api /api/facility');
    }

    #[Test]
    public function testSystemRoleTokenCannotReachStandardApiPatient(): void
    {
        $token = $this->mintSystemRoleToken();
        $resp = $this->rawGet('/apis/default/api/patient', $this->bearerHeaders($token));
        $this->assertRoleContextDeny($resp, 'System-role token must not reach standard-api /api/patient');
    }

    // ===== Cell 5: system-role token × /portal routes =====
    //
    // Portal routes require user_role === 'patient'. A system token has
    // no user context, so the dispatcher cannot derive a patient role
    // for it. The portal cell for system tokens is a distinct gap from
    // the standard-api cell above.

    #[Test]
    public function testSystemRoleTokenCannotReachPortalPatient(): void
    {
        $token = $this->mintSystemRoleToken();
        $resp = $this->rawGet('/apis/default/portal/patient', $this->bearerHeaders($token));
        $this->assertRoleContextDeny($resp, 'System-role token must not reach /portal/patient');
    }

    #[Test]
    public function testSystemRoleTokenCannotReachPortalEncounter(): void
    {
        $token = $this->mintSystemRoleToken();
        $resp = $this->rawGet('/apis/default/portal/patient/encounter', $this->bearerHeaders($token));
        $this->assertRoleContextDeny($resp, 'System-role token must not reach /portal/patient/encounter');
    }

    // ===== Cell 6: user-role token × non-patient-compartment FHIR =====
    //
    // Positive-shape control. A user-role token with user/Practitioner.read
    // must reach /fhir/Practitioner (status 200). Without this control, the
    // suite's deny-asserts could pass vacuously if the dispatcher silently
    // rejected everything (route mis-mounted, scope broken, etc.). The 200
    // here proves the dispatcher distinguishes between legitimate and
    // mismatched roles, not that it rejects both.

    #[Test]
    public function testUserRoleTokenReachesFhirPractitionerAsPositiveControl(): void
    {
        $token = $this->mintUserRoleTokenPasswordGrant(
            'CellSixPositiveControl',
            'openid api:fhir user/Practitioner.read'
        );
        $resp = $this->rawGet('/apis/default/fhir/Practitioner', $this->bearerHeaders($token));
        $this->assertSame(
            200,
            $resp['status'],
            'Positive control: user-role token with user/Practitioner.read MUST reach /fhir/Practitioner. '
            . 'Status: ' . $resp['status'] . ', Body: ' . substr($resp['body'], 0, 500)
        );
    }

    // ===== Cell 7: patient-role token × non-exported FHIR writes =====
    //
    // Patient-role tokens are blocked from FHIR writes at
    // AuthorizationListener line 193-195 before scope-type gating runs.
    // Representative sample: PUT on CareTeam (an administrative resource
    // whose writes have no patient-compartment semantics).

    #[Test]
    public function testPatientRoleTokenCannotWriteFhirCareTeam(): void
    {
        // Patient-role write-denial has two independent layers of
        // defense:
        //   Layer 1 (scope registry) — ServerScopeListEntity does not
        //     register ANY patient/*.cud variant (see getV2ApiScopes()
        //     "No patient/ variant" comment). DCR refuses to grant
        //     patient-write scopes with invalid_scope.
        //   Layer 2 (AuthorizationListener lines 193-195) —
        //     isPatientWriteRequest() rejects any FHIR write under
        //     user_role=patient regardless of scope. Backstop in case
        //     a future DCR change permits patient-write scopes.
        //
        // In practice, Layer 1 fires first — a patient-role token
        // simply cannot carry a CareTeam write scope. This test
        // exercises Layer 1 by minting with a non-write patient scope
        // and asserting the POST is rejected (either at the token
        // level because the scope is insufficient, or at Layer 2 if
        // somehow a write scope leaked through). Rabbit's suggestion
        // to grant patient/CareTeam.cruds would fail at DCR with
        // invalid_scope so we cannot reach Layer 2 via normal mint.
        $token = $this->mintPatientRoleToken(
            'CellSevenCareTeamWrite',
            'openid api:fhir patient/Patient.read'
        );
        $resp = $this->rawPostJson(
            '/apis/default/fhir/CareTeam',
            ['resourceType' => 'CareTeam'],
            $this->bearerHeaders($token)
        );
        $this->assertRoleContextDeny($resp, 'Patient-role token must not write /fhir/CareTeam');
    }

    // ===== Shared helpers =====

    /**
     * Register a confidential client via DCR + enable password grant.
     *
     * @param array<string, mixed> $overrides
     * @return array{0: string, 1: string}
     */
    private function dcrConfidentialPasswordGrantClient(string $tag, array $overrides = []): array
    {
        $payload = array_merge([
            'application_type' => 'private',
            'redirect_uris' => ['https://role-context-symmetry.example/cb'],
            'client_name' => 'RoleContextSymmetryApiTest-' . $tag . '-' . bin2hex(random_bytes(3)),
            'token_endpoint_auth_method' => 'client_secret_post',
            'contacts' => ['role-context-symmetry@test.example'],
            'scope' => 'openid api:oemr offline_access',
        ], $overrides);
        $http = $this->buildClient();
        $response = $http->post($this->baseUrl . '/oauth2/default/registration', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $payload,
        ]);
        $this->assertSame(200, $response->getStatusCode(), 'DCR must succeed. Body: ' . (string) $response->getBody());
        $data = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($data);
        $this->assertIsString($data['client_id'] ?? null);
        $this->assertIsString($data['client_secret'] ?? null);
        $clientId = $data['client_id'];
        $clientSecret = $data['client_secret'];
        $this->registeredClientIds[] = $clientId;
        QueryUtils::sqlStatementThrowException(
            'UPDATE `oauth_clients` SET `grant_types` = ?, `is_enabled` = 1 WHERE `client_id` = ?',
            ['password', $clientId]
        );
        return [$clientId, $clientSecret];
    }

    /**
     * Mint a user-role access_token via password grant.
     */
    private function mintUserRoleTokenPasswordGrant(string $tag, string $scope): string
    {
        [$cid, $csec] = $this->dcrConfidentialPasswordGrantClient($tag, ['scope' => $scope]);
        // Match ApiTestClient's convention: honor OE_USER / OE_PASS
        // overrides from the environment so a non-default seed admin
        // doesn't fail the password-grant before any role-context
        // assertion runs. admin/pass is the stock drid default.
        $username = (string) (getenv('OE_USER') ?: 'admin');
        $password = (string) (getenv('OE_PASS') ?: 'pass');
        $resp = $this->buildClient()->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $cid,
                'client_secret' => $csec,
                'scope' => $scope,
                'user_role' => 'users',
                'username' => $username,
                'password' => $password,
            ],
        ]);
        $raw = (string) $resp->getBody();
        $this->assertSame(200, $resp->getStatusCode(), 'User-role password-grant must succeed. Body: ' . $raw);
        $body = json_decode($raw, true);
        $this->assertIsArray($body);
        $this->assertIsString($body['access_token'] ?? null);
        $this->assertNotSame('', $body['access_token']);
        return $body['access_token'];
    }

    /**
     * Install a portal patient fixture + mint a patient-role access_token
     * via password grant. Reuses a single fixture across all patient-role
     * tests in the current test method so cleanup stays simple.
     */
    private function mintPatientRoleToken(string $tag, string $scope): string
    {
        $this->portalFixtures ??= new PortalPatientFixtureManager();
        $fixture = $this->portalFixtures->installPortalPatient(
            portalLoginUsername: 'rolectx-' . bin2hex(random_bytes(4)),
            plainPassword: 'RoleContextSymmetryPassword1!',
            email: 'rolectx-' . bin2hex(random_bytes(4)) . '@test.example'
        );
        [$cid, $csec] = $this->dcrConfidentialPasswordGrantClient($tag, ['scope' => $scope]);
        $resp = $this->buildClient()->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $cid,
                'client_secret' => $csec,
                'scope' => $scope,
                'user_role' => 'patient',
                'username' => $fixture['portal_login_username'],
                'password' => $fixture['plain_password'],
                'email' => $fixture['email'],
            ],
        ]);
        $raw = (string) $resp->getBody();
        $this->assertSame(200, $resp->getStatusCode(), 'Patient-role password-grant must succeed. Body: ' . $raw);
        $body = json_decode($raw, true);
        $this->assertIsArray($body);
        $this->assertIsString($body['access_token'] ?? null);
        $this->assertNotSame('', $body['access_token']);
        return $body['access_token'];
    }

    /**
     * Mint a system-role access_token via client_credentials + JWT
     * client assertion. Reuses BulkAPITestClient which already handles
     * the DCR + JWT assertion + token exchange plumbing.
     */
    private function mintSystemRoleToken(): string
    {
        $client = new BulkAPITestClient($this->baseUrl, false);
        $authResp = $client->setAuthToken(BulkAPITestClient::OPENEMR_AUTH_ENDPOINT);
        $this->assertSame(
            200,
            $authResp->getStatusCode(),
            'System-role client_credentials grant must succeed. Body: ' . (string) $authResp->getBody()
        );
        $token = $client->getAccessToken();
        $this->assertIsString($token);
        $this->assertNotSame('', $token);
        // Track the BulkAPITestClient so tearDown can call its
        // self-managed cleanupClient()/cleanupRevokeAuth() — those
        // reach the protected client_id property we cannot touch
        // from here.
        $this->bulkClients[] = $client;
        return $token;
    }

    /**
     * @return array{Authorization: string, Accept: string}
     */
    private function bearerHeaders(string $accessToken): array
    {
        return [
            'Authorization' => 'Bearer ' . $accessToken,
            'Accept' => 'application/json',
        ];
    }

    /**
     * @param array<string, string> $headers
     * @return HttpResponse
     */
    private function rawGet(string $path, array $headers): array
    {
        $response = $this->buildClient()->get($this->baseUrl . $path, ['headers' => $headers]);
        return [
            'status' => $response->getStatusCode(),
            'body' => (string) $response->getBody(),
            'headers' => $response->getHeaders(),
        ];
    }

    /**
     * @param array<string, mixed> $json
     * @param array<string, string> $headers
     * @return HttpResponse
     */
    private function rawPostJson(string $path, array $json, array $headers): array
    {
        $response = $this->buildClient()->post($this->baseUrl . $path, [
            'headers' => array_merge($headers, ['Content-Type' => 'application/fhir+json']),
            'json' => $json,
        ]);
        return [
            'status' => $response->getStatusCode(),
            'body' => (string) $response->getBody(),
            'headers' => $response->getHeaders(),
        ];
    }

    /**
     * Assert a role-mismatch deny shape. The AuthorizationListener throws
     * AccessDeniedException on mismatch, which the framework converts to
     * a 401 or 403 response. 404 is acceptable too for routes the test
     * webserver does not mount under the api prefix (nginx config edge
     * case; the invariant locked here is "did NOT return 200," not the
     * exact HTTP code). Any 200 is a role-matrix leak and must fail.
     *
     * @param HttpResponse $response
     */
    private function assertRoleContextDeny(array $response, string $context): void
    {
        $this->assertNotSame(
            200,
            $response['status'],
            $context . ' — role-context gate must reject (got 200). Body: ' . substr($response['body'], 0, 500)
        );
        // Narrow the accepted deny-shape to {401, 403}. Routes mounted
        // on the dispatcher pass through AuthorizationListener which
        // throws AccessDeniedException and the exception handler maps
        // that to HTTP 401. The server may also return 403 for
        // "authenticated but insufficient permissions" on some paths
        // (matching OAuth2AttackScenarioTest::assertDenied precedent).
        // Rejecting 400 and 404 catches regressions where a reject
        // bypasses the role-context gate — a 400 would mean a
        // validator short-circuited before the gate, a 404 would mean
        // the dispatcher didn't match the route at all.
        $this->assertContains(
            $response['status'],
            [401, 403],
            $context . ' — expected 401 or 403 from the role-context gate (not 400 validator short-circuit, not 404 route-not-matched). Status: '
            . $response['status'] . ', Body: ' . substr($response['body'], 0, 500)
        );
    }

    private function buildClient(): Client
    {
        return new Client([
            'verify' => false,
            'http_errors' => false,
            'timeout' => 15,
        ]);
    }
}
