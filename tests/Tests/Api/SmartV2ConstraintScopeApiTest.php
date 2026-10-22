<?php

/**
 * SMART v2 constraint-scope end-to-end coverage at the api tier.
 *
 * ServerScopeListEntity::fhirResourceScopesV2() advertises SMART v2
 * category-restricted scopes such as
 *   user/Condition.rs?category=http://hl7.org/fhir/us/core/CodeSystem/condition-category|health-concern
 *   user/Observation.rs?category=http://terminology.hl7.org/CodeSystem/observation-category|vital-signs
 *   user/DocumentReference.rs?category=http://hl7.org/fhir/us/core/CodeSystem/us-core-documentreference-category|clinical-note
 * across Condition, Observation, and DocumentReference.
 *
 * Isolated tier covers the primitives: ResourceConstraintFilterer unit
 * tests pin the filter semantics (constraint keys AND'd, values OR'd,
 * cross-scope union never merged), and
 * ScopeConstraintEnforcementWiringIsolatedTest confirms the two FHIR
 * controllers that invoke the filterer still invoke it.
 *
 * This test closes the end-to-end gap: it mints a token with ONLY one
 * constrained scope string, drives GET /fhir/<Resource>, and asserts
 * the returned Bundle contains the matching-category resource but
 * NOT a sibling resource owned by the same patient in a different
 * category. A paired baseline uses the unconstrained scope and
 * verifies both resources appear -- so a test that returns an empty
 * bundle still distinguishes "filter working" from "token stuck /
 * patient-binding broken".
 *
 * Covers one Condition pair (medical_problem -> problem-list-item vs
 * health_concern -> health-concern) and one Observation case
 * (vital-signs via the POST /fhir/Observation seed path). The eight
 * remaining advertised constraints share the same filter path through
 * ResourceConstraintFilterer::canAccessResource(); each would exercise
 * the same chain with different seed data and category coding, which
 * the isolated unit tier already pins per-type.
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
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIREncounter;
use OpenEMR\Services\FHIR\FhirEncounterService;
use OpenEMR\Tests\Fixtures\ConditionFixtureManager;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SmartV2ConstraintScopeApiTest extends TestCase
{
    private string $baseUrl;

    /** @var list<string> clientIds registered during a test; cleaned in tearDown */
    private array $registeredClientIds = [];

    private ?ConditionFixtureManager $conditionFixtureManager = null;
    private ?FixtureManager $fixtureManager = null;

    /** @var list<string> uuid-strings of patients seeded via createTestPatient; cleaned in tearDown */
    private array $seededPatientUuids = [];

    /** @var list<string> uuid-strings of encounters created for Observation seeding; cleaned in tearDown */
    private array $seededEncounterUuids = [];

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
        $probeServer = '';
        try {
            $probe = $this->buildHttpClient()->get($this->baseUrl . '/');
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

        // Pin site_addr_oath to the test base URL so form-action URLs and
        // iss claim line up with the client's origin. Mirror the snapshot
        // + set pattern from ApiScopeSchemeV2EquivalenceTest so a parallel
        // test using a different base URL does not clobber this one.
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

        // Enable password grant both in-process and in the persisted
        // globals row (apache reads the row per request so an in-process
        // toggle alone is not enough for /token).
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
        // fixture-removal, etc) throws. Pattern lifted from
        // ApiScopeSchemeV2EquivalenceTest for symmetry across the V2
        // scope-system api tests.
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

                // Clean seeded encounters first (they reference patient_data
                // via pid; patient cleanup via ConditionFixtureManager would
                // orphan them otherwise).
                foreach ($this->seededEncounterUuids as $encounterUuid) {
                    QueryUtils::sqlStatementThrowException(
                        'DELETE FROM `form_encounter` WHERE `uuid` = ?',
                        [UuidRegistry::uuidToBytes($encounterUuid)]
                    );
                }
                $this->seededEncounterUuids = [];

                // Form rows for vitals observations hang off form_encounter;
                // if the encounter is gone, orphan vitals rows must be
                // explicitly cleaned to avoid cross-test leakage. The
                // observation POST path writes to form_vitals keyed by pid,
                // so sweep by the seeded patients' pids.
                foreach ($this->seededPatientUuids as $patientUuid) {
                    $pidRow = QueryUtils::querySingleRow(
                        'SELECT pid FROM `patient_data` WHERE `uuid` = ?',
                        [UuidRegistry::uuidToBytes($patientUuid)]
                    );
                    if (is_array($pidRow) && isset($pidRow['pid'])) {
                        QueryUtils::sqlStatementThrowException(
                            'DELETE FROM `form_vitals` WHERE `pid` = ?',
                            [$pidRow['pid']]
                        );
                        QueryUtils::sqlStatementThrowException(
                            'DELETE FROM `forms` WHERE `pid` = ?',
                            [$pidRow['pid']]
                        );
                    }
                }

                $this->conditionFixtureManager?->removeFixtures();
                $this->conditionFixtureManager = null;
                // FixtureManager::installPatientFixtures() seeds through
                // the PATIENT_FIXTURE_PUBPID_PREFIX pattern. removePatientFixtures
                // cleans every row with that prefix, so it also catches
                // patients installed by the vitals + docref seed helpers.
                $this->fixtureManager?->removePatientFixtures();
                $this->fixtureManager = null;
                $this->seededPatientUuids = [];
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

    // ---------------------------------------------------------------------
    // Condition: health-concern constrained scope
    // ---------------------------------------------------------------------

    #[Test]
    public function testConditionHealthConcernConstraintFiltersBundle(): void
    {
        $seeded = $this->seedConditionPair();

        $scope = 'openid api:oemr user/Condition.rs'
            . '?category=http://hl7.org/fhir/us/core/CodeSystem/condition-category|health-concern';
        $tokens = $this->mintTokenWithScopes($scope);
        $client = $this->buildBearerClient($tokens['access_token']);

        $response = $client->get(
            $this->baseUrl . '/apis/default/fhir/Condition?patient=' . $seeded['patientUuid']
        );
        $this->assertSame(
            200,
            $response->getStatusCode(),
            'Constrained scope must still answer 200 on a resource it does admit. Body: '
                . (string) $response->getBody()
        );
        $resourceIds = $this->extractBundleResourceIds((string) $response->getBody());

        // health-concern constraint rejects problem-list-item: the one Condition
        // left in the bundle is the health-concern one.
        $this->assertContains(
            $seeded['healthConcernUuid'],
            $resourceIds,
            'Bundle must contain the health-concern Condition. Got ids: ' . implode(', ', $resourceIds)
        );
        $this->assertNotContains(
            $seeded['problemListItemUuid'],
            $resourceIds,
            'Bundle must NOT contain the problem-list-item Condition under a health-concern-constrained scope.'
                . ' Got ids: ' . implode(', ', $resourceIds)
        );
    }

    #[Test]
    public function testConditionProblemListItemConstraintFiltersBundle(): void
    {
        // Mirror of the health-concern case with the categories swapped so a
        // one-sided mis-wiring (filter admits one direction but not the other)
        // is still caught.
        $seeded = $this->seedConditionPair();

        $scope = 'openid api:oemr user/Condition.rs'
            . '?category=http://terminology.hl7.org/CodeSystem/condition-category|problem-list-item';
        $tokens = $this->mintTokenWithScopes($scope);
        $client = $this->buildBearerClient($tokens['access_token']);

        $response = $client->get(
            $this->baseUrl . '/apis/default/fhir/Condition?patient=' . $seeded['patientUuid']
        );
        $this->assertSame(
            200,
            $response->getStatusCode(),
            'Constrained scope must still answer 200. Body: ' . (string) $response->getBody()
        );
        $resourceIds = $this->extractBundleResourceIds((string) $response->getBody());

        $this->assertContains(
            $seeded['problemListItemUuid'],
            $resourceIds,
            'Bundle must contain the problem-list-item Condition. Got ids: ' . implode(', ', $resourceIds)
        );
        $this->assertNotContains(
            $seeded['healthConcernUuid'],
            $resourceIds,
            'Bundle must NOT contain the health-concern Condition under a problem-list-item-constrained scope.'
                . ' Got ids: ' . implode(', ', $resourceIds)
        );
    }

    #[Test]
    public function testConditionUnconstrainedScopeReturnsAllCategories(): void
    {
        // Baseline: same seed, unconstrained scope, both Conditions appear.
        // Guards against "no bundle entries" false-positive on the two
        // constrained-scope tests above (seeded patient has nothing visible
        // -> both filter tests pass trivially).
        $seeded = $this->seedConditionPair();

        $tokens = $this->mintTokenWithScopes('openid api:oemr user/Condition.rs');
        $client = $this->buildBearerClient($tokens['access_token']);

        $response = $client->get(
            $this->baseUrl . '/apis/default/fhir/Condition?patient=' . $seeded['patientUuid']
        );
        $this->assertSame(
            200,
            $response->getStatusCode(),
            'Unconstrained scope must answer 200. Body: ' . (string) $response->getBody()
        );
        $resourceIds = $this->extractBundleResourceIds((string) $response->getBody());

        $this->assertContains(
            $seeded['healthConcernUuid'],
            $resourceIds,
            'Unconstrained bundle must include the health-concern Condition. Got ids: '
                . implode(', ', $resourceIds)
        );
        $this->assertContains(
            $seeded['problemListItemUuid'],
            $resourceIds,
            'Unconstrained bundle must include the problem-list-item Condition. Got ids: '
                . implode(', ', $resourceIds)
        );
    }

    // ---------------------------------------------------------------------
    // Observation: vital-signs constrained scope
    // ---------------------------------------------------------------------

    #[Test]
    public function testObservationVitalSignsConstraintFiltersBundle(): void
    {
        $seeded = $this->seedPatientAndVitalObservation();

        $scope = 'openid api:oemr user/Observation.rs'
            . '?category=http://terminology.hl7.org/CodeSystem/observation-category|vital-signs';
        $tokens = $this->mintTokenWithScopes($scope);
        $client = $this->buildBearerClient($tokens['access_token']);

        $response = $client->get(
            $this->baseUrl . '/apis/default/fhir/Observation?patient=' . $seeded['patientUuid']
                . '&category=vital-signs'
        );
        $this->assertSame(
            200,
            $response->getStatusCode(),
            'Constrained vital-signs scope must answer 200 on a vital-signs search. Body: '
                . (string) $response->getBody()
        );

        $bundle = $this->decodeJson((string) $response->getBody());
        $entries = is_array($bundle['entry'] ?? null) ? array_values($bundle['entry']) : [];
        $categoryCodes = $this->extractObservationCategoryCodes($entries);

        // The resources returned must all admit the vital-signs constraint,
        // and the seeded Observation must be among them (seeding uses the
        // vital-signs category coding).
        $this->assertNotEmpty($categoryCodes, 'Vital-signs bundle must not be empty after seeding.');
        foreach ($categoryCodes as $entryCodes) {
            $this->assertContains(
                'vital-signs',
                $entryCodes,
                'Every bundle entry must carry the vital-signs category code under the constrained scope.'
                    . ' Entry categories: ' . implode(', ', $entryCodes)
            );
        }
    }

    #[Test]
    public function testObservationLaboratoryConstraintReturnsEmptyBundleWhenNoLabs(): void
    {
        // The seed path installs vital-signs Observations only. A
        // laboratory-constrained scope must *not* surface them -- the
        // bundle is 200 + empty entries, not 200 + the vital-signs
        // Observation leaking through a different category constraint.
        $seeded = $this->seedPatientAndVitalObservation();

        $scope = 'openid api:oemr user/Observation.rs'
            . '?category=http://terminology.hl7.org/CodeSystem/observation-category|laboratory';
        $tokens = $this->mintTokenWithScopes($scope);
        $client = $this->buildBearerClient($tokens['access_token']);

        $response = $client->get(
            $this->baseUrl . '/apis/default/fhir/Observation?patient=' . $seeded['patientUuid']
                . '&category=vital-signs'
        );
        $this->assertSame(
            200,
            $response->getStatusCode(),
            'Laboratory-constrained scope on a patient with no labs must answer 200. Body: '
                . (string) $response->getBody()
        );
        $bundle = $this->decodeJson((string) $response->getBody());
        $entries = is_array($bundle['entry'] ?? null) ? array_values($bundle['entry']) : [];
        // Any entry that slipped through must carry the laboratory category,
        // not vital-signs. (In practice no entries should slip through at all
        // because the seeded Observation is vital-signs, but we assert the
        // category rather than count=0 so a parallel-test leak -- another
        // test seeding a lab under the same base URL -- cannot false-positive
        // this gate.)
        $categoryCodes = $this->extractObservationCategoryCodes($entries);
        foreach ($categoryCodes as $entryCodes) {
            // Require the laboratory category positively AND reject
            // vital-signs. Without the positive check, a broken filter that
            // returned an uncategorized Observation (an empty [] per-entry
            // from extractObservationCategoryCodes after the uncategorized-
            // normalization fix) would silently pass the vital-signs
            // rejection.
            $this->assertContains(
                'laboratory',
                $entryCodes,
                'Every returned Observation under a laboratory-constrained'
                    . ' scope must carry the laboratory category code.'
                    . ' Entry categories: ' . implode(', ', $entryCodes)
            );
            $this->assertNotContains(
                'vital-signs',
                $entryCodes,
                'A laboratory-constrained scope must never return a vital-signs Observation.'
                    . ' Entry categories: ' . implode(', ', $entryCodes)
            );
        }
    }

    // ---------------------------------------------------------------------
    // DocumentReference: clinical-note constrained scope
    // ---------------------------------------------------------------------

    #[Test]
    public function testDocumentReferenceClinicalNoteConstraintAnswers200(): void
    {
        // DocumentReference seeding is substantial (clinical-note storage
        // involves form_clinical_notes + encounter wiring) and not exercised
        // elsewhere in the api suite. This test closes the smaller end of
        // the loop: DCR + token-mint + endpoint 200 for the advertised
        // clinical-note constrained scope. A seeded-pair contains-check
        // for DocumentReference would mirror the Condition shape above and
        // can land in a follow-up.
        //
        // The chain-integrity half still catches real regressions:
        //  - DCR rejecting the advertised constrained-scope string
        //  - finalizeScopes() silently dropping the ?category= query
        //  - FhirDocumentReferenceRestController returning 403 instead of
        //    filtering (the response code gate)
        $this->fixtureManager ??= new FixtureManager();
        $this->fixtureManager->installPatientFixtures();
        $patients = $this->fixtureManager->getPatientFixtures();
        $firstPatient = $patients[0];
        $this->assertIsArray($firstPatient);
        $patientRecord = QueryUtils::querySingleRow(
            'SELECT uuid FROM patient_data WHERE pubpid = ?',
            [$firstPatient['pubpid']]
        );
        $this->assertIsArray($patientRecord);
        $patientUuid = UuidRegistry::uuidToString($patientRecord['uuid']);
        $this->seededPatientUuids[] = $patientUuid;

        $scope = 'openid api:oemr user/DocumentReference.rs'
            . '?category=http://hl7.org/fhir/us/core/CodeSystem/us-core-documentreference-category|clinical-note';
        $tokens = $this->mintTokenWithScopes($scope);
        $client = $this->buildBearerClient($tokens['access_token']);

        $response = $client->get(
            $this->baseUrl . '/apis/default/fhir/DocumentReference?patient=' . $patientUuid
        );
        $this->assertSame(
            200,
            $response->getStatusCode(),
            'Constrained clinical-note scope must answer 200. Body: ' . (string) $response->getBody()
        );

        // Any entries returned must carry the clinical-note category code,
        // not a different category -- the constraint must prune anything
        // not matching through FhirDocumentReferenceRestController's
        // filterByScopeConstraints().
        $bundle = $this->decodeJson((string) $response->getBody());
        $entries = is_array($bundle['entry'] ?? null) ? $bundle['entry'] : [];
        foreach ($entries as $entry) {
            if (!is_array($entry)) {
                continue;
            }
            $resource = $entry['resource'] ?? null;
            if (!is_array($resource)) {
                continue;
            }
            // Normalize an uncategorized entry to an empty array so the
            // foundClinicalNote assertion still runs. Previously
            // `continue`-ing let a broken filter that returned an
            // uncategorized DocumentReference pass this check silently.
            $categories = $resource['category'] ?? null;
            if (!is_array($categories)) {
                $categories = [];
            }
            $foundClinicalNote = false;
            foreach ($categories as $concept) {
                if (!is_array($concept)) {
                    continue;
                }
                $codings = $concept['coding'] ?? null;
                if (!is_array($codings)) {
                    continue;
                }
                foreach ($codings as $coding) {
                    if (!is_array($coding)) {
                        continue;
                    }
                    if (($coding['code'] ?? null) === 'clinical-note') {
                        $foundClinicalNote = true;
                        break 2;
                    }
                }
            }
            $this->assertTrue(
                $foundClinicalNote,
                'Every DocumentReference entry under a clinical-note-constrained scope'
                    . ' must carry the clinical-note category code. Got: '
                    . json_encode($categories)
            );
        }
    }

    // ---------------------------------------------------------------------
    // Seeding helpers
    // ---------------------------------------------------------------------

    /**
     * Seed one patient + two Conditions: one that FhirConditionService
     * routes to the problem-list-item category (type='medical_problem') and
     * one it routes to the health-concern category (type='health_concern').
     *
     * @return array{patientUuid: string, problemListItemUuid: string, healthConcernUuid: string}
     */
    private function seedConditionPair(): array
    {
        $this->conditionFixtureManager ??= new ConditionFixtureManager();
        $patient = $this->conditionFixtureManager->createTestPatient();
        $this->assertIsString($patient['uuid'] ?? null);
        $this->seededPatientUuids[] = $patient['uuid'];

        $problem = $this->conditionFixtureManager->createTestCondition($patient, [
            'type' => 'medical_problem',
            'title' => 'Essential hypertension (SmartV2Constraint-pli)',
        ]);
        $this->assertIsString($problem['lists_uuid'] ?? null);

        $concern = $this->conditionFixtureManager->createTestCondition($patient, [
            'type' => 'health_concern',
            'title' => 'Food insecurity (SmartV2Constraint-hc)',
        ]);
        $this->assertIsString($concern['lists_uuid'] ?? null);

        return [
            'patientUuid' => $patient['uuid'],
            'problemListItemUuid' => $problem['lists_uuid'],
            'healthConcernUuid' => $concern['lists_uuid'],
        ];
    }

    /**
     * Seed one patient + one encounter + one vital-signs Observation
     * (heart rate), returning the patient UUID. Observations seed through
     * POST /fhir/Observation under ALL_SCOPES, exercising the same write
     * path as ObservationFhirWriteApiTest.
     *
     * @return array{patientUuid: string, observationUuid: string}
     */
    private function seedPatientAndVitalObservation(): array
    {
        $this->fixtureManager ??= new FixtureManager();
        $this->fixtureManager->installPatientFixtures();
        $patients = $this->fixtureManager->getPatientFixtures();
        $firstPatient = $patients[0];
        $this->assertIsArray($firstPatient);
        $patientRecord = QueryUtils::querySingleRow(
            'SELECT uuid FROM patient_data WHERE pubpid = ?',
            [$firstPatient['pubpid']]
        );
        $this->assertIsArray($patientRecord);
        $patientUuid = UuidRegistry::uuidToString($patientRecord['uuid']);
        $this->seededPatientUuids[] = $patientUuid;

        // Create an encounter via FhirEncounterService — the vitals write
        // needs a real encounter row to key form_vitals against.
        $encounterRaw = json_decode(
            (string) file_get_contents(__DIR__ . '/../Fixtures/FHIR/encounter.json'),
            true
        );
        $this->assertIsArray($encounterRaw);
        $encounterPayload = $encounterRaw[0];
        $this->assertIsArray($encounterPayload);
        $encounterPayload['subject'] = ['reference' => 'Patient/' . $patientUuid];

        $encounterService = new FhirEncounterService();
        $encounterInsert = $encounterService->insert(new FHIREncounter($encounterPayload));
        $this->assertTrue(
            $encounterInsert->isValid(),
            'Encounter insert (setup) failed: ' . json_encode($encounterInsert->getValidationMessages())
        );
        $encounterInsertData = $encounterInsert->getData();
        $this->assertIsArray($encounterInsertData);
        $encounterData = $encounterInsertData[0];
        $this->assertIsArray($encounterData);
        $this->assertIsString($encounterData['euuid']);
        $encounterUuid = $encounterData['euuid'];
        $this->seededEncounterUuids[] = $encounterUuid;

        // POST the heart rate fixture (fixture index 1) through the API
        // under an ALL_SCOPES admin token. Reuses ApiTestClient's auth path
        // so the oauth client used for the seed is distinct from the one
        // used for the constrained-scope test and is cleaned by its own
        // revoke/cleanup inline.
        $observationFixture = json_decode(
            (string) file_get_contents(__DIR__ . '/../Fixtures/FHIR/observation.json'),
            true
        );
        $this->assertIsArray($observationFixture);
        $fixture = $observationFixture[1];
        $this->assertIsArray($fixture);
        $fixture['subject'] = ['reference' => 'Patient/' . $patientUuid];
        $fixture['encounter'] = ['reference' => 'Encounter/' . $encounterUuid];
        unset($fixture['id']);
        // Rebuild as a <string, mixed> map so ApiTestClient::post()'s
        // declared parameter type matches without a cast.
        $stringKeyedFixture = [];
        foreach ($fixture as $key => $value) {
            $this->assertIsString($key);
            $stringKeyedFixture[$key] = $value;
        }

        $adminClient = new ApiTestClient($this->baseUrl, false);
        $adminClient->setAuthTokenOrFail(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
        $this->registeredClientIds[] = (string) $adminClient->getClientId();

        $postResp = $adminClient->post('/apis/default/fhir/Observation', $stringKeyedFixture);
        $this->assertSame(
            201,
            $postResp->getStatusCode(),
            'Seed vital-signs Observation POST must return 201. Body: '
                . (string) $postResp->getBody()
        );
        $created = $this->decodeJson((string) $postResp->getBody());
        $this->assertIsString($created['uuid'] ?? null);

        return [
            'patientUuid' => $patientUuid,
            'observationUuid' => $created['uuid'],
        ];
    }

    // ---------------------------------------------------------------------
    // OAuth helpers
    // ---------------------------------------------------------------------

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
                'client_name' => 'SmartV2ConstraintScopeApiTest-' . bin2hex(random_bytes(3)),
                'token_endpoint_auth_method' => 'client_secret_post',
                'contacts' => ['v2-constraint@test.example'],
                'scope' => $scopeString,
            ],
        ]);
        $this->assertSame(
            200,
            $reg->getStatusCode(),
            'DCR with a V2 constrained scope must succeed. Body: ' . (string) $reg->getBody()
        );
        $data = $this->decodeJson((string) $reg->getBody());
        $this->assertIsString($data['client_id'] ?? null);
        $this->assertIsString($data['client_secret'] ?? null);
        $clientId = $data['client_id'];
        $clientSecret = $data['client_secret'];
        $this->registeredClientIds[] = $clientId;

        // user/* scopes land disabled by default; flip is_enabled=1 and
        // add the password grant (DCR does not grant it).
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
            'Password grant with V2 constrained scope must succeed. Body: '
                . (string) $tokenResp->getBody()
        );
        $tokens = $this->decodeJson((string) $tokenResp->getBody());
        $this->assertIsString($tokens['access_token'] ?? null);
        // If the requested scope includes a `?category=` constraint, verify
        // the mint response echoes EACH requested constrained token verbatim
        // in the space-separated issued scope. A substring check on
        // `?category=` would still pass if finalizeScopes() silently rewrote
        // the system or code (health-concern -> problem-list-item) or
        // reattached the constraint to a different resource scope.
        if (str_contains($scopeString, '?category=')) {
            $issuedScope = is_string($tokens['scope'] ?? null) ? $tokens['scope'] : '';
            $issuedTokens = explode(' ', $issuedScope);
            foreach (explode(' ', $scopeString) as $requested) {
                if (!str_contains($requested, '?category=')) {
                    continue;
                }
                $this->assertContains(
                    $requested,
                    $issuedTokens,
                    'Token mint response must echo the V2 constrained scope verbatim. Got: ' . $issuedScope
                );
            }
        }
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

    // ---------------------------------------------------------------------
    // Bundle decoding helpers
    // ---------------------------------------------------------------------

    /**
     * Pull the FHIR id out of each Bundle.entry.resource. Missing or
     * non-string ids are skipped rather than failing the extraction so a
     * malformed entry does not short-circuit the "neither contains nor
     * does not contain" assertion the caller is about to run on the list.
     *
     * @return list<string>
     */
    private function extractBundleResourceIds(string $rawJson): array
    {
        $bundle = $this->decodeJson($rawJson);
        $entries = is_array($bundle['entry'] ?? null) ? $bundle['entry'] : [];
        $ids = [];
        foreach ($entries as $entry) {
            if (!is_array($entry)) {
                continue;
            }
            $resource = $entry['resource'] ?? null;
            if (!is_array($resource)) {
                continue;
            }
            $id = $resource['id'] ?? null;
            if (is_string($id)) {
                $ids[] = $id;
            }
        }
        return $ids;
    }

    /**
     * For each Bundle entry, flatten resource.category[*].coding[*].code
     * into a list of codes seen in that entry. Shape:
     *   [ ['vital-signs'], ['laboratory', 'exam'], ... ]
     *
     * @param list<mixed> $entries
     * @return list<list<string>>
     */
    private function extractObservationCategoryCodes(array $entries): array
    {
        $perEntry = [];
        foreach ($entries as $entry) {
            if (!is_array($entry)) {
                continue;
            }
            $resource = $entry['resource'] ?? null;
            if (!is_array($resource)) {
                continue;
            }
            $categories = $resource['category'] ?? null;
            if (!is_array($categories)) {
                // Record an empty code list so an uncategorized entry still
                // reaches the per-entry assertion. Previously `continue`-ing
                // let a broken filter that returned uncategorized entries
                // pass the "every bundle entry must carry this category"
                // check silently.
                $perEntry[] = [];
                continue;
            }
            $codes = [];
            foreach ($categories as $concept) {
                if (!is_array($concept)) {
                    continue;
                }
                $codings = $concept['coding'] ?? null;
                if (!is_array($codings)) {
                    continue;
                }
                foreach ($codings as $coding) {
                    if (!is_array($coding)) {
                        continue;
                    }
                    $code = $coding['code'] ?? null;
                    if (is_string($code)) {
                        $codes[] = $code;
                    }
                }
            }
            $perEntry[] = $codes;
        }
        return $perEntry;
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
