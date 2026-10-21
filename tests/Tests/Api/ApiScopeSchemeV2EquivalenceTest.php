<?php

/**
 * V2 scope-set equivalence coverage at the api tier.
 *
 * Isolated and unit tiers already cover V2 scope parsing (ScopeEntityTest,
 * ScopePermissionObjectTest) and the containsScope() expansion rules that
 * resolve a CRUDS actionString against a single-char endpoint requirement.
 * End-to-end wiring was previously only exercised with V1 scopes via the
 * ApiTestClient::ALL_SCOPES catch-all (`.read` / `.write`). This test
 * closes that gap by minting tokens that carry ONLY V2 scope identifiers
 * (no V1 fallback) and asserting equivalent access across a representative
 * resource × permission-axis matrix:
 *
 *   user/allergy.rs        — read + search only
 *   user/allergy.cud       — create + update + delete only (no read, no search)
 *   user/appointment.cruds — full CRUD + search
 *   user/patient.crus      — read + search + create + update (patient has no hard-delete)
 *   user/insurance.rs      — read + search only (patient-compartment)
 *
 * Each test registers a confidential client via DCR with the narrow V2
 * scope set, flips `is_enabled = 1` server-side (user/* scopes require
 * manual approval — same seam DisabledClientRejectionTest uses), mints a
 * token via the password grant, and exercises the matching routes. The
 * token-mint response's own `scope` field is inspected to confirm the
 * V2 identifiers survived finalizeScopes() verbatim.
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
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Tests\Fixtures\AppointmentFixtureManager;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ApiScopeSchemeV2EquivalenceTest extends TestCase
{
    private string $baseUrl;

    /** @var list<string> clientIds registered during a test; cleaned in tearDown */
    private array $registeredClientIds = [];

    private ?FixtureManager $fixtureManager = null;
    private ?AppointmentFixtureManager $appointmentFixtureManager = null;

    private mixed $originalPasswordGrantSetting = null;
    private bool $originalPasswordGrantSettingWasSet = false;
    private ?string $originalPasswordGrantGlobalRow = null;
    private bool $passwordGrantGlobalRowWasInserted = false;
    private ?string $originalSiteAddrOath = null;
    private bool $siteAddrOathWasInserted = false;

    protected function setUp(): void
    {
        $this->baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';

        if (getenv('OPENEMR_ALLOW_OAUTH_HTTPS_SKIP') === '1') {
            $this->markTestSkipped('Skipping per OPENEMR_ALLOW_OAUTH_HTTPS_SKIP=1');
        }
        $probe = $this->buildHttpClient()->get($this->baseUrl . '/');
        if ($probe->getHeaderLine('Server') === '') {
            $message = 'OAuth flow requires a real webserver (Apache/nginx)';
            if (getenv('CI') !== false) {
                self::fail($message . ' — hard failure in CI');
            }
            $this->markTestSkipped($message);
        }

        // iss/aud + form-action URL generation reads site_addr_oath.
        // Mirror DisabledClientRejectionTest's snapshot + set pattern so
        // redirects line up with the test client's origin.
        $current = QueryUtils::querySingleRow(
            'SELECT gl_value FROM `globals` WHERE gl_name = ?',
            ['site_addr_oath']
        );
        if (is_array($current)) {
            $glValue = $current['gl_value'] ?? null;
            $this->originalSiteAddrOath = is_string($glValue) ? $glValue : null;
            if ($this->originalSiteAddrOath !== $this->baseUrl) {
                QueryUtils::sqlStatementThrowException(
                    'UPDATE `globals` SET gl_value = ? WHERE gl_name = ?',
                    [$this->baseUrl, 'site_addr_oath']
                );
            }
        } else {
            QueryUtils::sqlStatementThrowException(
                'INSERT INTO `globals` (`gl_name`, `gl_index`, `gl_value`) VALUES (?, 0, ?)',
                ['site_addr_oath', $this->baseUrl]
            );
            $this->siteAddrOathWasInserted = true;
        }

        // Password grant: enable in-process + persist to the globals row
        // (apache reads the row per request so a process-only toggle is
        // not enough for /token).
        $globals = OEGlobalsBag::getInstance();
        $this->originalPasswordGrantSettingWasSet = $globals->has('oauth_password_grant');
        $this->originalPasswordGrantSetting = $globals->get('oauth_password_grant');
        $globals->set('oauth_password_grant', 3);
        $this->persistPasswordGrantEnabled();
    }

    protected function tearDown(): void
    {
        // Nested try/finally so the DB-persisted globals restoration always
        // runs even if an earlier cleanup step (DELETE on oauth_clients,
        // fixture-removal, etc) throws. Order:
        //   inner try   = fixture + oauth client cleanup
        //   inner finally = site_addr_oath + oauth_password_grant row restores
        //   outer finally = in-memory oauth_password_grant restore
        // Without this split, a failure in the inner try would skip the
        // persisted restores and leak oauth_password_grant=3 (or any
        // test-inserted site_addr_oath row) into the next test run.
        try {
            try {
                foreach ($this->registeredClientIds as $clientId) {
                    QueryUtils::sqlStatementThrowException(
                        'DELETE FROM `oauth_clients` WHERE `client_id` = ?',
                        [$clientId]
                    );
                    QueryUtils::sqlStatementThrowException(
                        'DELETE FROM `oauth_trusted_user` WHERE `client_id` = ?',
                        [$clientId]
                    );
                    QueryUtils::sqlStatementThrowException(
                        'DELETE FROM `api_token` WHERE `client_id` = ?',
                        [$clientId]
                    );
                }
                $this->registeredClientIds = [];

                if ($this->appointmentFixtureManager !== null) {
                    $this->appointmentFixtureManager->removeFixtures();
                    $this->appointmentFixtureManager = null;
                }
                if ($this->fixtureManager !== null) {
                    // Order matters: both removeCoverageFixtures and
                    // removeAllergyIntoleranceFixtures scope their deletes by
                    // pid-of-fixture-patient, and the latter *also* calls
                    // removePatientFixtures internally. If patients disappear
                    // first, insurance_data rows can no longer be identified as
                    // test rows and leak into the next test run, where the next
                    // POST hits a DUPLICATE_RECORD on (pid, type, date).
                    $this->fixtureManager->removeCoverageFixtures();
                    $this->fixtureManager->removeAllergyIntoleranceFixtures();
                    $this->fixtureManager->removeInsuranceCompanyFixtures();
                    $this->fixtureManager->removePatientFixtures();
                    $this->fixtureManager = null;
                }
            } finally {
                if ($this->siteAddrOathWasInserted) {
                    QueryUtils::sqlStatementThrowException(
                        'DELETE FROM `globals` WHERE gl_name = ?',
                        ['site_addr_oath']
                    );
                } elseif ($this->originalSiteAddrOath !== null && $this->originalSiteAddrOath !== $this->baseUrl) {
                    QueryUtils::sqlStatementThrowException(
                        'UPDATE `globals` SET gl_value = ? WHERE gl_name = ?',
                        [$this->originalSiteAddrOath, 'site_addr_oath']
                    );
                }
                $this->restorePasswordGrantGlobal();
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

    #[Test]
    public function testTokenMintReturnsV2ScopesVerbatim(): void
    {
        // IdTokenSMARTResponse::getScopeString strips `api:*` scopes from the
        // echoed scope list (ONC compatibility), so the V2 forms are the only
        // thing we assert on here. `api:oemr` is still granted inside the
        // access token even though it is not advertised in the response.
        $scopes = 'openid api:oemr user/allergy.rs';
        $tokens = $this->mintTokenWithScopes($scopes);
        $grantedScopes = explode(' ', $tokens['scope']);
        $this->assertContains(
            'user/allergy.rs',
            $grantedScopes,
            'V2 scope identifier must survive finalizeScopes() unchanged. Got: ' . $tokens['scope']
        );
        $this->assertContains('openid', $grantedScopes);
        // V1 forms must not appear — the client never registered them.
        $this->assertNotContains('user/allergy.read', $grantedScopes);
        $this->assertNotContains('user/allergy.write', $grantedScopes);
    }

    #[Test]
    public function testAllergyRsScopeGrantsReadAndSearch(): void
    {
        // Seed one allergy for a fresh fixture patient using an admin token
        // (ALL_SCOPES) so the V2 flow has data to retrieve.
        $patientUuid = $this->seedPatientWithAllergy('Penicillin-V2RS');

        // Mint a V2-only token with just read+search access.
        $tokens = $this->mintTokenWithScopes('openid api:oemr user/allergy.rs');
        $client = $this->buildBearerClient($tokens['access_token']);

        // GET /api/patient/{puuid}/allergy — search permission ('s')
        $listPerPatient = $client->get(
            $this->baseUrl . '/apis/default/api/patient/' . $patientUuid . '/allergy'
        );
        $this->assertSame(
            200,
            $listPerPatient->getStatusCode(),
            'user/allergy.rs must grant per-patient list. Body: ' . (string) $listPerPatient->getBody()
        );
        $perPatientBody = $this->decodeJson((string) $listPerPatient->getBody());
        $this->assertIsArray($perPatientBody['data'] ?? null);
        $this->assertGreaterThan(0, count($perPatientBody['data']));

        // Pull the seeded allergy's uuid out of the list for the single-get.
        $allergyUuid = null;
        foreach ($perPatientBody['data'] as $row) {
            if (is_array($row) && ($row['title'] ?? null) === 'Penicillin-V2RS') {
                $allergyUuid = $row['uuid'] ?? null;
                break;
            }
        }
        $this->assertIsString($allergyUuid, 'Seeded allergy must appear in V2 per-patient list.');

        // GET /api/patient/{puuid}/allergy/{auuid} — read permission ('r')
        $singlePerPatient = $client->get(
            $this->baseUrl . '/apis/default/api/patient/' . $patientUuid . '/allergy/' . $allergyUuid
        );
        $this->assertSame(
            200,
            $singlePerPatient->getStatusCode(),
            'user/allergy.rs must grant per-patient single-get. Body: ' . (string) $singlePerPatient->getBody()
        );

        // GET /api/allergy — top-level search permission ('s')
        $topList = $client->get($this->baseUrl . '/apis/default/api/allergy');
        $this->assertSame(
            200,
            $topList->getStatusCode(),
            'user/allergy.rs must grant top-level list. Body: ' . (string) $topList->getBody()
        );

        // GET /api/allergy/{auuid} — top-level read permission ('r')
        $topSingle = $client->get($this->baseUrl . '/apis/default/api/allergy/' . $allergyUuid);
        $this->assertSame(
            200,
            $topSingle->getStatusCode(),
            'user/allergy.rs must grant top-level single-get. Body: ' . (string) $topSingle->getBody()
        );
    }

    #[Test]
    public function testAllergyRsScopeRejectsWrite(): void
    {
        // Same V2 read-only token must NOT satisfy the create permission
        // (POST → endpoint requires 'c', scope only has 'r' and 's').
        $patientUuid = $this->seedPatient();

        $tokens = $this->mintTokenWithScopes('openid api:oemr user/allergy.rs');
        $client = $this->buildBearerClient($tokens['access_token']);

        $postResponse = $client->post(
            $this->baseUrl . '/apis/default/api/patient/' . $patientUuid . '/allergy',
            [
                'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
                'body' => json_encode([
                    'title' => 'ShouldNotPersist',
                    'type' => 'allergy',
                    'reaction' => 'hives',
                    'verification' => 'confirmed',
                    'begdate' => '2023-01-01',
                ]),
            ]
        );
        $this->assertSame(
            401,
            $postResponse->getStatusCode(),
            'user/allergy.rs must not satisfy POST (create). Body: ' . (string) $postResponse->getBody()
        );
    }

    #[Test]
    public function testAllergyCudScopeGrantsWriteButNotRead(): void
    {
        // user/allergy.cud → create+update+delete only. The actionString
        // parser (ScopePermissionObject::createFromString) treats each
        // single character as a permission flag, so this intentionally
        // does NOT contain 'r' or 's':
        //   POST   (c) → allowed
        //   PUT    (u) → allowed
        //   DELETE (d) → allowed
        //   GET list (s) → denied
        //   GET single (r) → denied
        //
        // Note the sister actionString `.crud` *does* grant read because
        // the 'r' character is in it. V2 does not reserve a shorthand for
        // "write without read"; .cud is the correct spelling for that.
        $patientUuid = $this->seedPatient();

        $tokens = $this->mintTokenWithScopes('openid api:oemr user/allergy.cud');
        $client = $this->buildBearerClient($tokens['access_token']);

        // POST — must succeed (we have 'c').
        $postResponse = $client->post(
            $this->baseUrl . '/apis/default/api/patient/' . $patientUuid . '/allergy',
            [
                'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
                'body' => json_encode([
                    'title' => 'Latex-V2CUD',
                    'type' => 'allergy',
                    'reaction' => 'contact dermatitis',
                    'verification' => 'confirmed',
                    'begdate' => '2023-05-10',
                ]),
            ]
        );
        $this->assertSame(
            201,
            $postResponse->getStatusCode(),
            'user/allergy.cud must satisfy POST. Body: ' . (string) $postResponse->getBody()
        );
        $createdBody = $this->decodeJson((string) $postResponse->getBody());
        $this->assertIsArray($createdBody['data'] ?? null);
        $this->assertIsString($createdBody['data']['uuid'] ?? null);
        $allergyUuid = $createdBody['data']['uuid'];

        // GET list — must be denied (no 's').
        $listResponse = $client->get(
            $this->baseUrl . '/apis/default/api/patient/' . $patientUuid . '/allergy'
        );
        $this->assertSame(
            401,
            $listResponse->getStatusCode(),
            'user/allergy.cud must not satisfy GET list. Body: ' . (string) $listResponse->getBody()
        );

        // GET single — must be denied (no 'r').
        $singleResponse = $client->get(
            $this->baseUrl . '/apis/default/api/patient/' . $patientUuid . '/allergy/' . $allergyUuid
        );
        $this->assertSame(
            401,
            $singleResponse->getStatusCode(),
            'user/allergy.cud must not satisfy GET single. Body: ' . (string) $singleResponse->getBody()
        );

        // PUT — must succeed (we have 'u').
        $putResponse = $client->put(
            $this->baseUrl . '/apis/default/api/patient/' . $patientUuid . '/allergy/' . $allergyUuid,
            [
                'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
                'body' => json_encode([
                    'title' => 'Latex-V2CUD-Updated',
                    'begdate' => '2023-05-10',
                ]),
            ]
        );
        $this->assertSame(
            200,
            $putResponse->getStatusCode(),
            'user/allergy.cud must satisfy PUT. Body: ' . (string) $putResponse->getBody()
        );

        // DELETE — must succeed (we have 'd').
        $deleteResponse = $client->delete(
            $this->baseUrl . '/apis/default/api/patient/' . $patientUuid . '/allergy/' . $allergyUuid
        );
        $this->assertSame(
            200,
            $deleteResponse->getStatusCode(),
            'user/allergy.cud must satisfy DELETE. Body: ' . (string) $deleteResponse->getBody()
        );
    }

    #[Test]
    public function testAppointmentCrudsScopeGrantsFullMatrix(): void
    {
        // user/appointment.cruds → full CRUD + search. Covers every verb at
        // once so the top-level list, per-patient list, single-get, create,
        // delete all resolve through one V2 token.
        $this->appointmentFixtureManager = new AppointmentFixtureManager();
        $dependencies = $this->appointmentFixtureManager->installDependencies();
        $pid = $dependencies['pid'];
        $facilityId = $dependencies['facility_id'];

        $tokens = $this->mintTokenWithScopes('openid api:oemr user/appointment.cruds');
        $client = $this->buildBearerClient($tokens['access_token']);

        // POST — create a fixture appointment.
        $appointmentFixture = $this->appointmentFixtureManager->getSingleAppointmentFixture($facilityId);
        $postResponse = $client->post(
            $this->baseUrl . '/apis/default/api/patient/' . $pid . '/appointment',
            [
                'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
                'body' => json_encode($appointmentFixture),
            ]
        );
        $this->assertSame(
            200,
            $postResponse->getStatusCode(),
            'user/appointment.cruds must satisfy POST. Body: ' . (string) $postResponse->getBody()
        );
        $created = $this->decodeJson((string) $postResponse->getBody());
        $this->assertIsNumeric($created['id'] ?? null);
        $eid = (int) $created['id'];

        // GET list (top-level) — search permission.
        $topList = $client->get($this->baseUrl . '/apis/default/api/appointment');
        $this->assertSame(
            200,
            $topList->getStatusCode(),
            'user/appointment.cruds must satisfy top-level list. Body: ' . (string) $topList->getBody()
        );

        // GET single — read permission.
        $single = $client->get($this->baseUrl . '/apis/default/api/appointment/' . $eid);
        $this->assertSame(
            200,
            $single->getStatusCode(),
            'user/appointment.cruds must satisfy single-get. Body: ' . (string) $single->getBody()
        );

        // GET per-patient list — search permission in patient compartment.
        $perPatient = $client->get(
            $this->baseUrl . '/apis/default/api/patient/' . $pid . '/appointment'
        );
        $this->assertSame(
            200,
            $perPatient->getStatusCode(),
            'user/appointment.cruds must satisfy per-patient list. Body: ' . (string) $perPatient->getBody()
        );

        // DELETE — delete permission.
        $deleteResponse = $client->delete(
            $this->baseUrl . '/apis/default/api/patient/' . $pid . '/appointment/' . $eid
        );
        $this->assertSame(
            200,
            $deleteResponse->getStatusCode(),
            'user/appointment.cruds must satisfy DELETE. Body: ' . (string) $deleteResponse->getBody()
        );
    }

    #[Test]
    public function testPatientCruScopeGrantsReadSearchCreateUpdate(): void
    {
        // Note: the V2 getV2ApiScopes() map declares patient as ['c','r','u','s'] —
        // no 'd' because patient records cannot be hard-deleted through this api.
        // The compact actionString therefore is 'crus'. We test that identifier
        // against the full matrix.
        $tokens = $this->mintTokenWithScopes('openid api:oemr user/patient.crus');
        $client = $this->buildBearerClient($tokens['access_token']);

        // POST — create a brand new patient and capture its uuid.
        $fixture = $this->getFixtureManager()->getSinglePatientFixture();
        $postResponse = $client->post(
            $this->baseUrl . '/apis/default/api/patient',
            [
                'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
                'body' => json_encode($fixture),
            ]
        );
        $this->assertSame(
            201,
            $postResponse->getStatusCode(),
            'user/patient.crus must satisfy POST /api/patient. Body: ' . (string) $postResponse->getBody()
        );
        $createdBody = $this->decodeJson((string) $postResponse->getBody());
        $this->assertIsArray($createdBody['data'] ?? null);
        $this->assertIsString($createdBody['data']['uuid'] ?? null);
        $patientUuid = $createdBody['data']['uuid'];

        // GET list — search permission.
        $listResponse = $client->get($this->baseUrl . '/apis/default/api/patient');
        $this->assertSame(
            200,
            $listResponse->getStatusCode(),
            'user/patient.crus must satisfy GET /api/patient. Body: ' . (string) $listResponse->getBody()
        );

        // GET single — read permission.
        $singleResponse = $client->get(
            $this->baseUrl . '/apis/default/api/patient/' . $patientUuid
        );
        $this->assertSame(
            200,
            $singleResponse->getStatusCode(),
            'user/patient.crus must satisfy GET /api/patient/{puuid}. Body: ' . (string) $singleResponse->getBody()
        );

        // PUT — update permission.
        $putResponse = $client->put(
            $this->baseUrl . '/apis/default/api/patient/' . $patientUuid,
            [
                'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
                'body' => json_encode(['fname' => 'V2CRUSupdated']),
            ]
        );
        $this->assertSame(
            200,
            $putResponse->getStatusCode(),
            'user/patient.crus must satisfy PUT /api/patient/{puuid}. Body: ' . (string) $putResponse->getBody()
        );
    }

    #[Test]
    public function testInsuranceRsScopeGrantsPatientCompartmentReads(): void
    {
        // user/insurance.rs → read + search on the patient-compartment
        // insurance routes. Seed one coverage policy for a fresh patient
        // under an admin token first, then exercise the V2 read token.
        $context = $this->seedPatientWithInsurancePolicy();

        $tokens = $this->mintTokenWithScopes('openid api:oemr user/insurance.rs');
        $client = $this->buildBearerClient($tokens['access_token']);

        $listResponse = $client->get(
            $this->baseUrl . '/apis/default/api/patient/' . $context['patientUuid'] . '/insurance'
        );
        $this->assertSame(
            200,
            $listResponse->getStatusCode(),
            'user/insurance.rs must satisfy insurance list. Body: ' . (string) $listResponse->getBody()
        );
        $listBody = $this->decodeJson((string) $listResponse->getBody());
        $this->assertIsArray($listBody['data'] ?? null);
        $this->assertGreaterThan(0, count($listBody['data']));

        $singleResponse = $client->get(
            $this->baseUrl . '/apis/default/api/patient/' . $context['patientUuid']
            . '/insurance/' . $context['policyUuid']
        );
        $this->assertSame(
            200,
            $singleResponse->getStatusCode(),
            'user/insurance.rs must satisfy insurance single-get. Body: ' . (string) $singleResponse->getBody()
        );
    }

    /**
     * Register a confidential client that advertises the given scope set,
     * flip is_enabled=1 (user/* scopes trigger manual approval during DCR),
     * allow the password grant, then mint a token with the same scope set.
     *
     * @return array{access_token: string, scope: string, token_type: string}
     */
    private function mintTokenWithScopes(string $scopeString): array
    {
        $http = $this->buildHttpClient();
        $reg = $http->post($this->baseUrl . '/oauth2/default/registration', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'application_type' => 'private',
                'redirect_uris' => ['https://client.example/cb'],
                'client_name' => 'ApiScopeSchemeV2EquivalenceTest-' . bin2hex(random_bytes(3)),
                'token_endpoint_auth_method' => 'client_secret_post',
                'contacts' => ['v2-equiv@test.example'],
                'scope' => $scopeString,
            ],
        ]);
        $this->assertSame(200, $reg->getStatusCode(), 'DCR should succeed. Body: ' . (string) $reg->getBody());
        $data = $this->decodeJson((string) $reg->getBody());
        $this->assertIsString($data['client_id'] ?? null);
        $this->assertIsString($data['client_secret'] ?? null);
        $clientId = $data['client_id'];
        $clientSecret = $data['client_secret'];
        $this->registeredClientIds[] = $clientId;

        // user/* scopes land disabled by default; flip is_enabled=1 and
        // allow the password grant (DCR cannot grant it).
        QueryUtils::sqlStatementThrowException(
            'UPDATE `oauth_clients` SET `is_enabled` = 1, `grant_types` = ? WHERE `client_id` = ?',
            ['authorization_code|password', $clientId]
        );

        $tokenResp = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'scope' => $scopeString,
                'user_role' => 'users',
                'username' => 'admin',
                'password' => 'pass',
            ],
        ]);
        $this->assertSame(
            200,
            $tokenResp->getStatusCode(),
            'Password grant with V2 scope must succeed. Body: ' . (string) $tokenResp->getBody()
        );
        $tokens = $this->decodeJson((string) $tokenResp->getBody());
        $this->assertIsString($tokens['access_token'] ?? null);
        return [
            'access_token' => $tokens['access_token'],
            'scope' => is_string($tokens['scope'] ?? null) ? $tokens['scope'] : '',
            'token_type' => is_string($tokens['token_type'] ?? null) ? $tokens['token_type'] : 'Bearer',
        ];
    }

    private function buildHttpClient(): Client
    {
        return new Client([
            'verify' => false,
            'http_errors' => false,
            'cookies' => new CookieJar(),
            'allow_redirects' => [
                'max' => 10,
                'strict' => true,
                'referer' => true,
                'protocols' => ['http', 'https'],
            ],
            'timeout' => 15,
        ]);
    }

    private function buildBearerClient(string $accessToken): Client
    {
        return new Client([
            'verify' => false,
            'http_errors' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Accept' => 'application/json',
            ],
            'timeout' => 15,
        ]);
    }

    private function getFixtureManager(): FixtureManager
    {
        $this->fixtureManager ??= new FixtureManager();
        return $this->fixtureManager;
    }

    /**
     * Create a fresh patient via the standard-api POST under an admin token,
     * return its uuid. Admin token uses ALL_SCOPES so this path is covered
     * by existing V1 tests — only the V2 verification afterwards is new.
     */
    private function seedPatient(): string
    {
        $adminClient = new ApiTestClient($this->baseUrl, false);
        $adminClient->setAuthTokenOrFail(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
        $this->registeredClientIds[] = (string) $adminClient->getClientId();

        $fixture = $this->getFixtureManager()->getSinglePatientFixture();
        $response = $adminClient->post('/apis/default/api/patient', $fixture);
        $this->assertSame(201, $response->getStatusCode(), 'Seed patient must POST ok. Body: ' . (string) $response->getBody());
        $body = $this->decodeJson((string) $response->getBody());
        $this->assertIsArray($body['data'] ?? null);
        $this->assertIsString($body['data']['uuid'] ?? null);
        return $body['data']['uuid'];
    }

    private function seedPatientWithAllergy(string $title): string
    {
        $adminClient = new ApiTestClient($this->baseUrl, false);
        $adminClient->setAuthTokenOrFail(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
        $this->registeredClientIds[] = (string) $adminClient->getClientId();

        $fixture = $this->getFixtureManager()->getSinglePatientFixture();
        $patientResp = $adminClient->post('/apis/default/api/patient', $fixture);
        $this->assertSame(201, $patientResp->getStatusCode(), 'Seed patient must POST ok. Body: ' . (string) $patientResp->getBody());
        $patientBody = $this->decodeJson((string) $patientResp->getBody());
        $this->assertIsArray($patientBody['data'] ?? null);
        $this->assertIsString($patientBody['data']['uuid'] ?? null);
        $patientUuid = $patientBody['data']['uuid'];

        $allergyResp = $adminClient->post(
            '/apis/default/api/patient/' . $patientUuid . '/allergy',
            [
                'title' => $title,
                'type' => 'allergy',
                'reaction' => 'hives',
                'verification' => 'confirmed',
                'begdate' => '2024-01-01',
            ]
        );
        $this->assertContains(
            $allergyResp->getStatusCode(),
            [200, 201],
            'Seed allergy must POST ok. Body: ' . (string) $allergyResp->getBody()
        );
        return $patientUuid;
    }

    /**
     * @return array{patientUuid: string, policyUuid: string}
     */
    private function seedPatientWithInsurancePolicy(): array
    {
        $fixtures = $this->getFixtureManager();
        $fixtures->installPatientFixtures();
        $fixtures->installInsuranceCompanyFixture();

        $puuidBytes = QueryUtils::fetchSingleValue(
            'SELECT uuid FROM patient_data WHERE pubpid LIKE ? ORDER BY pid DESC LIMIT 1',
            'uuid',
            [FixtureManager::PATIENT_FIXTURE_PUBPID_PREFIX . '%']
        );
        $this->assertIsString($puuidBytes);
        $patientUuid = \OpenEMR\Common\Uuid\UuidRegistry::uuidToString($puuidBytes);

        $insurerId = QueryUtils::fetchSingleValue(
            'SELECT id FROM insurance_companies WHERE name = ? ORDER BY id DESC LIMIT 1',
            'id',
            ['test-fixture-insurer']
        );
        $this->assertIsNumeric($insurerId);

        $adminClient = new ApiTestClient($this->baseUrl, false);
        $adminClient->setAuthTokenOrFail(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
        $this->registeredClientIds[] = (string) $adminClient->getClientId();

        $policyResp = $adminClient->post(
            '/apis/default/api/patient/' . $patientUuid . '/insurance',
            [
                'type' => 'primary',
                'provider' => (string) (int) $insurerId,
                'policy_number' => 'V2-POLICY-' . uniqid(),
                'subscriber_lname' => 'FixtureLast',
                'subscriber_fname' => 'FixtureFirst',
                'subscriber_relationship' => 'self',
                'subscriber_DOB' => '1980-01-01',
                'subscriber_street' => '123 Fixture Street',
                'subscriber_postal_code' => '62701',
                'subscriber_city' => 'Springfield',
                'subscriber_state' => 'IL',
                'subscriber_sex' => 'Male',
                'accept_assignment' => 'TRUE',
                'date' => '2026-01-01',
                'date_end' => null,
            ]
        );
        $this->assertSame(200, $policyResp->getStatusCode(), 'Seed insurance must POST ok. Body: ' . (string) $policyResp->getBody());
        $policyBody = $this->decodeJson((string) $policyResp->getBody());
        $this->assertIsArray($policyBody['data'] ?? null);
        $this->assertIsString($policyBody['data']['uuid'] ?? null);

        return [
            'patientUuid' => $patientUuid,
            'policyUuid' => $policyBody['data']['uuid'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJson(string $raw): array
    {
        $decoded = json_decode($raw, true);
        $this->assertIsArray($decoded, 'Response body must decode as JSON: ' . $raw);
        /** @var array<string, mixed> $decoded */
        return $decoded;
    }

    private function persistPasswordGrantEnabled(): void
    {
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
}
