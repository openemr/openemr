<?php

/**
 * Insufficient-scope deny-path regression gates.
 *
 * The scope-system isolated and unit tiers (ScopeEntityTest,
 * ScopePermissionObjectTest) cover scope parsing and permission matching
 * primitives. End-to-end api-tier wiring, however, defaults to
 * ApiTestClient::ALL_SCOPES, which is broad enough to satisfy every
 * route — a regression that silently accepted an insufficient-scope
 * token would not be caught by the existing suite.
 *
 * Each test here mints a token with a specifically-narrow scope list
 * (missing the exact permission the endpoint requires per
 * HttpRestRouteHandler::checkSecurity: POST->c, PUT/PATCH->u, DELETE->d,
 * single-GET->r, list-GET->s) and asserts the server rejects the
 * mismatched route. Every test also includes a positive assertion
 * against a route the token's scope legitimately permits — this guards
 * against a stuck-token false positive (token mint failed, token
 * expired, wrong endpoint, etc. would otherwise read as "deny
 * working" when actually the token was never usable).
 *
 * Covers V1 (.read/.write) and V2 (CRUDS) permission axes. Resource-
 * permission axis equivalent of OAuth2AttackScenarioTest's
 * context-role-axis deny gates (testUserRoleTokenCannotReachPortalRoute,
 * testPatientRoleTokenCannotReachStandardApiRoute).
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
use OpenEMR\Tests\Fixtures\AppointmentFixtureManager;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class InsufficientScopeDenyApiTest extends TestCase
{
    private string $baseUrl;
    /** @var list<string> */
    private array $registeredClientIds = [];
    private mixed $originalPasswordGrantSetting = null;
    private bool $originalPasswordGrantSettingWasSet = false;
    private ?string $originalPasswordGrantGlobalRow = null;
    private bool $passwordGrantGlobalRowWasInserted = false;
    private ?FixtureManager $patientFixtureManager = null;
    private ?AppointmentFixtureManager $appointmentFixtureManager = null;
    /** @var array<string, int|string> */
    private array $seededPatient = [];
    private ?int $seededAppointmentEid = null;

    protected function setUp(): void
    {
        $this->baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';
        $globals = OEGlobalsBag::getInstance();
        $this->originalPasswordGrantSettingWasSet = $globals->has('oauth_password_grant');
        $this->originalPasswordGrantSetting = $globals->get('oauth_password_grant');

        if (getenv('OPENEMR_ALLOW_OAUTH_HTTPS_SKIP') === '1') {
            $this->markTestSkipped('Skipping per OPENEMR_ALLOW_OAUTH_HTTPS_SKIP=1');
        }
        // Guzzle throws ConnectException on a hard network failure (no DNS
        // resolution, connection refused, etc). Treat it the same as a probe
        // response with no Server header so a local run without a webserver
        // routes through the existing skip logic rather than failing hard
        // with an uncaught Guzzle exception.
        $probeServer = '';
        try {
            $probe = $this->buildClient()->get($this->baseUrl . '/');
            $probeServer = $probe->getHeaderLine('Server');
        } catch (\GuzzleHttp\Exception\ConnectException) {
            $probeServer = '';
        }
        if ($probeServer === '') {
            if (getenv('CI') !== false) {
                self::fail('OAuth flow requires a real webserver (Apache/nginx) — hard failure in CI');
            }
            $this->markTestSkipped('OAuth flow requires a real webserver');
        }
        $globals->set('oauth_password_grant', 3);
        $this->persistPasswordGrantEnabled();
    }

    protected function tearDown(): void
    {
        try {
            if ($this->appointmentFixtureManager !== null) {
                $this->appointmentFixtureManager->removeFixtures();
            }
            if ($this->patientFixtureManager !== null) {
                $this->patientFixtureManager->removeAllergyIntoleranceFixtures();
            }
            $this->restorePasswordGrantGlobal();
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
        }
    }

    // ---------- V1 permission-axis deny gates ----------

    #[Test]
    public function testV1AppointmentReadScopeCannotPost(): void
    {
        // user/appointment.read expands to r+s (v1Read). POST derives
        // the required permission as 'c' (HttpRestRouteHandler::checkSecurity
        // POST->c branch), which the token lacks.
        $this->seedAppointmentFixture();
        $patientId = $this->seededPatient['pid'] ?? null;
        $this->assertIsInt($patientId);

        $accessToken = $this->mintNarrowScopeUserToken(
            'V1AppointmentReadNoPost',
            'openid api:oemr user/appointment.read'
        );

        // Positive: scope legitimately permits list GET
        $listResp = $this->rawGet('/apis/default/api/appointment', [
            'Authorization' => 'Bearer ' . $accessToken,
        ]);
        $this->assertSame(
            200,
            $listResp['status'],
            'user/appointment.read must satisfy GET /api/appointment (search). Body: ' . $listResp['body']
        );

        // Negative: POST requires .c
        $facilityId = $this->seededAppointmentFacilityId();
        $payload = [
            'pc_catid' => 5,
            'pc_title' => AppointmentFixtureManager::FIXTURE_PREFIX . '-deny-probe',
            'pc_duration' => 900,
            'pc_hometext' => AppointmentFixtureManager::FIXTURE_PREFIX . ' deny probe notes',
            'pc_apptstatus' => '-',
            'pc_eventDate' => date('Y-m-d'),
            'pc_startTime' => '11:00',
            'pc_facility' => $facilityId,
            'pc_billing_location' => $facilityId,
        ];
        $postResp = $this->rawPostJson(
            '/apis/default/api/patient/' . $patientId . '/appointment',
            $payload,
            ['Authorization' => 'Bearer ' . $accessToken]
        );
        $this->assertDenied(
            $postResp,
            'POST /api/patient/:pid/appointment must deny a user/appointment.read-only token (missing .c)'
        );
    }

    #[Test]
    public function testV1AppointmentReadScopeCannotDelete(): void
    {
        $this->seedAppointmentFixture();
        $patientId = $this->seededPatient['pid'] ?? null;
        $this->assertIsInt($patientId);
        $this->assertIsInt($this->seededAppointmentEid);

        $accessToken = $this->mintNarrowScopeUserToken(
            'V1AppointmentReadNoDelete',
            'openid api:oemr user/appointment.read'
        );

        // Positive: scope legitimately permits single GET
        $getResp = $this->rawGet(
            '/apis/default/api/appointment/' . $this->seededAppointmentEid,
            ['Authorization' => 'Bearer ' . $accessToken]
        );
        $this->assertSame(
            200,
            $getResp['status'],
            'user/appointment.read must satisfy GET /api/appointment/:eid (read). Body: ' . $getResp['body']
        );

        // Negative: DELETE requires .d
        $deleteResp = $this->rawDelete(
            '/apis/default/api/patient/' . $patientId . '/appointment/' . $this->seededAppointmentEid,
            ['Authorization' => 'Bearer ' . $accessToken]
        );
        $this->assertDenied(
            $deleteResp,
            'DELETE /api/patient/:pid/appointment/:eid must deny a user/appointment.read-only token (missing .d)'
        );
    }

    #[Test]
    public function testV1AppointmentWriteScopeCannotList(): void
    {
        // user/appointment.write expands to c+u+d (v1Write) — no 's'.
        // GET /api/appointment (list) requires .s, which the token lacks.
        $this->seedAppointmentFixture();
        $patientId = $this->seededPatient['pid'] ?? null;
        $this->assertIsInt($patientId);

        $accessToken = $this->mintNarrowScopeUserToken(
            'V1AppointmentWriteNoList',
            'openid api:oemr user/appointment.write'
        );

        // Positive: scope legitimately permits DELETE
        $this->assertIsInt($this->seededAppointmentEid);
        $deleteResp = $this->rawDelete(
            '/apis/default/api/patient/' . $patientId . '/appointment/' . $this->seededAppointmentEid,
            ['Authorization' => 'Bearer ' . $accessToken]
        );
        $this->assertSame(
            200,
            $deleteResp['status'],
            'user/appointment.write must satisfy DELETE /api/patient/:pid/appointment/:eid. Body: ' . $deleteResp['body']
        );

        // Negative: list GET requires .s
        $listResp = $this->rawGet('/apis/default/api/appointment', [
            'Authorization' => 'Bearer ' . $accessToken,
        ]);
        $this->assertDenied(
            $listResp,
            'GET /api/appointment (list) must deny a user/appointment.write-only token — V1 .write expands to cud and lacks .s'
        );
    }

    // ---------- V2 permission-axis deny gates ----------

    #[Test]
    public function testV2AllergyRsScopeCannotPost(): void
    {
        // user/allergy.rs covers r+s (read, search). POST requires 'c'.
        $patientUuid = $this->seedPatientAndReturnUuid();

        $accessToken = $this->mintNarrowScopeUserToken(
            'V2AllergyRsNoPost',
            'openid api:oemr user/allergy.rs'
        );

        // Positive: scope legitimately permits list GET
        $listResp = $this->rawGet(
            '/apis/default/api/patient/' . $patientUuid . '/allergy',
            ['Authorization' => 'Bearer ' . $accessToken]
        );
        $this->assertSame(
            200,
            $listResp['status'],
            'user/allergy.rs must satisfy GET /api/patient/:puuid/allergy (search). Body: ' . $listResp['body']
        );

        $allergyPayload = [
            'title' => 'Penicillin',
            'begdate' => '2020-01-01',
            'type' => 'allergy',
        ];
        $postResp = $this->rawPostJson(
            '/apis/default/api/patient/' . $patientUuid . '/allergy',
            $allergyPayload,
            ['Authorization' => 'Bearer ' . $accessToken]
        );
        $this->assertDenied(
            $postResp,
            'POST /api/patient/:puuid/allergy must deny a user/allergy.rs token (missing .c)'
        );
    }

    #[Test]
    public function testV2AllergyRScopeCannotList(): void
    {
        // user/allergy.r is read-only (single); GET-list requires .s.
        $patientUuid = $this->seedPatientAndReturnUuid();

        $accessToken = $this->mintNarrowScopeUserToken(
            'V2AllergyRNoList',
            'openid api:oemr user/allergy.r'
        );

        // Negative: list GET requires .s
        $listResp = $this->rawGet(
            '/apis/default/api/patient/' . $patientUuid . '/allergy',
            ['Authorization' => 'Bearer ' . $accessToken]
        );
        $this->assertDenied(
            $listResp,
            'GET /api/patient/:puuid/allergy (search) must deny a user/allergy.r-only token (missing .s)'
        );

        // Positive: single-GET on a bogus but syntactically-valid id
        // exercises the scope-permission branch (which authorizes the
        // request before service-level resolution happens). Any non-401
        // status proves the scope satisfied the gate — a 404 or 200
        // means the server reached the controller rather than denying
        // at the scope layer.
        $singleResp = $this->rawGet(
            '/apis/default/api/allergy/00000000-0000-0000-0000-000000000000',
            ['Authorization' => 'Bearer ' . $accessToken]
        );
        $this->assertNotContains(
            $singleResp['status'],
            [401, 403],
            'user/allergy.r must satisfy GET /api/allergy/:auuid (read) at the scope layer. Body: ' . $singleResp['body']
        );
    }

    #[Test]
    public function testV2AllergyCScopeCannotRead(): void
    {
        // user/allergy.c is create-only; single-GET requires .r.
        $patientUuid = $this->seedPatientAndReturnUuid();

        $accessToken = $this->mintNarrowScopeUserToken(
            'V2AllergyCNoRead',
            'openid api:oemr user/allergy.c'
        );

        // Negative: single-GET requires .r
        $singleResp = $this->rawGet(
            '/apis/default/api/allergy/00000000-0000-0000-0000-000000000000',
            ['Authorization' => 'Bearer ' . $accessToken]
        );
        $this->assertDenied(
            $singleResp,
            'GET /api/allergy/:auuid (read) must deny a user/allergy.c-only token (missing .r)'
        );

        // Positive: POST exercises .c, which the token has. The payload
        // may fail service-level validation but the request must reach
        // the controller — i.e. not be denied at the scope layer.
        $allergyPayload = [
            'title' => 'Penicillin',
            'begdate' => '2020-01-01',
            'type' => 'allergy',
        ];
        $postResp = $this->rawPostJson(
            '/apis/default/api/patient/' . $patientUuid . '/allergy',
            $allergyPayload,
            ['Authorization' => 'Bearer ' . $accessToken]
        );
        $this->assertNotContains(
            $postResp['status'],
            [401, 403],
            'user/allergy.c must satisfy POST /api/patient/:puuid/allergy at the scope layer. Body: ' . $postResp['body']
        );
    }

    #[Test]
    public function testV2AllergyRsScopeCannotDelete(): void
    {
        // user/allergy.rs covers r+s; DELETE requires .d.
        $patientUuid = $this->seedPatientAndReturnUuid();

        $accessToken = $this->mintNarrowScopeUserToken(
            'V2AllergyRsNoDelete',
            'openid api:oemr user/allergy.rs'
        );

        // Negative: DELETE requires .d
        $deleteResp = $this->rawDelete(
            '/apis/default/api/patient/' . $patientUuid . '/allergy/00000000-0000-0000-0000-000000000000',
            ['Authorization' => 'Bearer ' . $accessToken]
        );
        $this->assertDenied(
            $deleteResp,
            'DELETE /api/patient/:puuid/allergy/:auuid must deny a user/allergy.rs token (missing .d)'
        );

        // Positive: single-GET exercises .r, which the token has.
        $singleResp = $this->rawGet(
            '/apis/default/api/allergy/00000000-0000-0000-0000-000000000000',
            ['Authorization' => 'Bearer ' . $accessToken]
        );
        $this->assertNotContains(
            $singleResp['status'],
            [401, 403],
            'user/allergy.rs must satisfy GET /api/allergy/:auuid (read) at the scope layer. Body: ' . $singleResp['body']
        );
    }

    #[Test]
    public function testV2AllergyRsScopeCannotPut(): void
    {
        // user/allergy.rs covers r+s; PUT requires .u.
        $patientUuid = $this->seedPatientAndReturnUuid();

        $accessToken = $this->mintNarrowScopeUserToken(
            'V2AllergyRsNoPut',
            'openid api:oemr user/allergy.rs'
        );

        $putResp = $this->rawPutJson(
            '/apis/default/api/patient/' . $patientUuid . '/allergy/00000000-0000-0000-0000-000000000000',
            ['title' => 'Penicillin'],
            ['Authorization' => 'Bearer ' . $accessToken]
        );
        $this->assertDenied(
            $putResp,
            'PUT /api/patient/:puuid/allergy/:auuid must deny a user/allergy.rs token (missing .u)'
        );

        // Positive: list GET exercises .s.
        $listResp = $this->rawGet(
            '/apis/default/api/patient/' . $patientUuid . '/allergy',
            ['Authorization' => 'Bearer ' . $accessToken]
        );
        $this->assertSame(
            200,
            $listResp['status'],
            'user/allergy.rs must satisfy GET /api/patient/:puuid/allergy (search). Body: ' . $listResp['body']
        );
    }

    // ---------- Shared helpers ----------

    /**
     * DCR + is_enabled=1 + grant_types=password + mint password-grant
     * token for the specified scope list. Returns the access token.
     */
    private function mintNarrowScopeUserToken(string $tag, string $scope): string
    {
        $http = $this->buildClient();
        $reg = $http->post($this->baseUrl . '/oauth2/default/registration', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'application_type' => 'private',
                'redirect_uris' => ['https://insufficient-scope-test.example/cb'],
                'client_name' => 'InsufficientScopeDenyApiTest-' . $tag . '-' . bin2hex(random_bytes(3)),
                'token_endpoint_auth_method' => 'client_secret_post',
                'contacts' => ['insufficient-scope@test.example'],
                'scope' => $scope,
            ],
        ]);
        $this->assertSame(200, $reg->getStatusCode(), 'DCR must succeed. Body: ' . (string) $reg->getBody());
        $data = json_decode((string) $reg->getBody(), true);
        $this->assertIsArray($data);
        $this->assertIsString($data['client_id'] ?? null);
        $this->assertIsString($data['client_secret'] ?? null);
        /** @var string $clientId */
        $clientId = $data['client_id'];
        /** @var string $clientSecret */
        $clientSecret = $data['client_secret'];
        $this->registeredClientIds[] = $clientId;

        // user/* scopes require admin approval before the client is
        // enabled. Flip is_enabled=1 and add password grant.
        QueryUtils::sqlStatementThrowException(
            'UPDATE `oauth_clients` SET `grant_types` = ?, `is_enabled` = 1 WHERE `client_id` = ?',
            ['authorization_code|password', $clientId]
        );

        $tokenResp = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'scope' => $scope,
                'user_role' => 'users',
                'username' => 'admin',
                'password' => 'pass',
            ],
        ]);
        $this->assertSame(
            200,
            $tokenResp->getStatusCode(),
            'Password grant for narrow-scope token must succeed. Body: ' . (string) $tokenResp->getBody()
        );
        $tokens = json_decode((string) $tokenResp->getBody(), true);
        $this->assertIsArray($tokens);
        $this->assertIsString($tokens['access_token'] ?? null);
        $this->assertNotSame('', $tokens['access_token']);
        return $tokens['access_token'];
    }

    private function seedPatientAndReturnUuid(): string
    {
        if ($this->patientFixtureManager === null) {
            $this->patientFixtureManager = new FixtureManager();
            $this->patientFixtureManager->installPatientFixtures();
        }
        $row = QueryUtils::querySingleRow(
            'SELECT `uuid` FROM `patient_data` WHERE `pubpid` LIKE ? LIMIT 1',
            [FixtureManager::PATIENT_FIXTURE_PUBPID_PREFIX . '%']
        );
        $this->assertIsArray($row);
        $uuidBinary = $row['uuid'] ?? null;
        $this->assertNotNull($uuidBinary);
        return \OpenEMR\Common\Uuid\UuidRegistry::uuidToString($uuidBinary);
    }

    /**
     * Seed a patient + facility + appointment for appointment tests.
     * Uses an ALL_SCOPES ApiTestClient to create the appointment (test
     * fixture setup happens outside the scope-under-test).
     */
    private function seedAppointmentFixture(): void
    {
        $this->appointmentFixtureManager = new AppointmentFixtureManager();
        $deps = $this->appointmentFixtureManager->installDependencies();
        $this->seededPatient = ['pid' => $deps['pid'], 'facility_id' => $deps['facility_id']];

        $apptClient = new ApiTestClient($this->baseUrl, false);
        // Register the client id BEFORE asserting the auth response so a
        // non-200 token response still leaves cleanup able to drop the row.
        // ApiTestClient::setAuthToken returns the token-endpoint response
        // regardless of status, and getClientId() reflects the DCR state
        // which happens before the token call.
        $authResp = $apptClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
        $seedClientId = $apptClient->getClientId();
        $this->assertNotNull($seedClientId, 'DCR must return a client id for cleanup.');
        $this->registeredClientIds[] = $seedClientId;
        $this->assertSame(
            200,
            $authResp->getStatusCode(),
            'Seeding client auth must succeed. Body: ' . (string) $authResp->getBody()
        );

        $apptData = $this->appointmentFixtureManager->getSingleAppointmentFixture($deps['facility_id']);
        $createResp = $apptClient->post(
            '/apis/default/api/patient/' . $deps['pid'] . '/appointment',
            $apptData
        );
        $this->assertSame(
            200,
            $createResp->getStatusCode(),
            'Seeding appointment fixture must succeed. Body: ' . (string) $createResp->getBody()
        );
        /** @var array{id: int|string} $body */
        $body = json_decode((string) $createResp->getBody(), true);
        $this->seededAppointmentEid = (int) $body['id'];
    }

    private function seededAppointmentFacilityId(): int
    {
        $facilityId = $this->seededPatient['facility_id'] ?? null;
        $this->assertIsInt($facilityId);
        return $facilityId;
    }

    private function persistPasswordGrantEnabled(): void
    {
        // OEGlobalsBag only mutates the current PHPUnit process; the HTTP
        // server reads oauth_password_grant from the globals table on
        // each request. Mirror DisabledClientRejectionTest's pattern.
        $current = QueryUtils::querySingleRow(
            'SELECT gl_value FROM `globals` WHERE gl_name = ?',
            ['oauth_password_grant']
        );
        if (is_array($current)) {
            $glValue = $current['gl_value'] ?? null;
            $this->originalPasswordGrantGlobalRow = is_string($glValue) ? $glValue : null;
            QueryUtils::sqlStatementThrowException(
                'UPDATE `globals` SET gl_value = ? WHERE gl_name = ?',
                ['3', 'oauth_password_grant']
            );
        } else {
            QueryUtils::sqlStatementThrowException(
                'INSERT INTO `globals` (`gl_name`, `gl_index`, `gl_value`) VALUES (?, 0, ?)',
                ['oauth_password_grant', '3']
            );
            $this->passwordGrantGlobalRowWasInserted = true;
        }
    }

    private function restorePasswordGrantGlobal(): void
    {
        if ($this->passwordGrantGlobalRowWasInserted) {
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM `globals` WHERE gl_name = ?',
                ['oauth_password_grant']
            );
        } elseif ($this->originalPasswordGrantGlobalRow !== null) {
            QueryUtils::sqlStatementThrowException(
                'UPDATE `globals` SET gl_value = ? WHERE gl_name = ?',
                [$this->originalPasswordGrantGlobalRow, 'oauth_password_grant']
            );
        }
    }

    /**
     * Assert the server denied the request. Accepts both 401
     * (AccessDeniedException maps to HTTP_UNAUTHORIZED in
     * HttpRestRouteHandler::dispatch) and 403 — mirrors the shape of
     * OAuth2AttackScenarioTest::testUserRoleTokenCannotReachPortalRoute
     * so a future change in the response-code mapping does not force a
     * sweeping test rewrite. The invariant locked here is "deny" at the
     * scope layer, not a specific HTTP code.
     *
     * @param array{status: int, body: string, headers: array<array<string>>} $resp
     */
    private function assertDenied(array $resp, string $context): void
    {
        $this->assertContains(
            $resp['status'],
            [401, 403],
            $context . ' — expected 401/403 but got ' . $resp['status'] . '. Body: ' . $resp['body']
        );
    }

    /**
     * @param array<string, string> $headers
     * @return array{status: int, body: string, headers: array<array<string>>}
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
     * @param array<string, mixed> $body
     * @param array<string, string> $headers
     * @return array{status: int, body: string, headers: array<array<string>>}
     */
    private function rawPostJson(string $path, array $body, array $headers): array
    {
        $response = $this->buildClient()->post($this->baseUrl . $path, [
            'headers' => array_merge(['Content-Type' => 'application/json'], $headers),
            'body' => (string) json_encode($body),
        ]);
        return [
            'status' => $response->getStatusCode(),
            'body' => (string) $response->getBody(),
            'headers' => $response->getHeaders(),
        ];
    }

    /**
     * @param array<string, mixed> $body
     * @param array<string, string> $headers
     * @return array{status: int, body: string, headers: array<array<string>>}
     */
    private function rawPutJson(string $path, array $body, array $headers): array
    {
        $response = $this->buildClient()->put($this->baseUrl . $path, [
            'headers' => array_merge(['Content-Type' => 'application/json'], $headers),
            'body' => (string) json_encode($body),
        ]);
        return [
            'status' => $response->getStatusCode(),
            'body' => (string) $response->getBody(),
            'headers' => $response->getHeaders(),
        ];
    }

    /**
     * @param array<string, string> $headers
     * @return array{status: int, body: string, headers: array<array<string>>}
     */
    private function rawDelete(string $path, array $headers): array
    {
        $response = $this->buildClient()->delete($this->baseUrl . $path, ['headers' => $headers]);
        return [
            'status' => $response->getStatusCode(),
            'body' => (string) $response->getBody(),
            'headers' => $response->getHeaders(),
        ];
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
