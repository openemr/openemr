<?php

/**
 * HTTP search and read tests for the FHIR Immunization resource.
 *
 * ImmunizationFhirWriteApiTest covers POST and PUT; this covers GET /fhir/Immunization and
 * GET /fhir/Immunization/:uuid. An influenza immunization is seeded through the API in setUp,
 * the same way the write test does it, and is the immunization every test looks for.
 *
 * Reading by id and searching by _id both go through the uuid column, so they are tested
 * separately.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Fariha Batool <farihabatool2023@gmail.com>
 * @copyright Copyright (c) 2026 Fariha Batool <farihabatool2023@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

class ImmunizationFhirApiTest extends TestCase
{
    private const ENDPOINT = '/apis/default/fhir/Immunization';
    private const ABSENT_UUID = '11111111-1111-1111-1111-111111111111';
    private const CVX_SYSTEM = 'http://hl7.org/fhir/sid/cvx';
    private const INFLUENZA_CVX = '197';

    private ApiTestClient $testClient;
    private FixtureManager $fixtureManager;
    private string $patientUuid;
    private string $immunizationUuid;

    /**
     * Additional immunization uuids seeded by tests beyond setUp (e.g. the
     * cross-patient-exclusion test). tearDown sweeps every entry so a
     * second-patient immunization does not leak between test runs.
     *
     * @var list<string>
     */
    private array $extraImmunizationUuids = [];

    /**
     * Seeds a patient and an influenza immunization, and gets an API token.
     */
    protected function setUp(): void
    {
        $this->testClient = new ApiTestClient(self::baseUrl(), false);
        $this->testClient->setAuthTokenOrFail(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $this->fixtureManager = new FixtureManager();
        $this->fixtureManager->installPatientFixtures();
        $patients = $this->fixtureManager->getPatientFixtures();
        $patient = $patients[0];
        $this->assertIsArray($patient);
        $record = QueryUtils::querySingleRow(
            'SELECT uuid FROM patient_data WHERE pubpid = ?',
            [$patient['pubpid']]
        );
        $this->assertIsArray($record);
        $this->patientUuid = UuidRegistry::uuidToString($record['uuid']);

        $fixtures = json_decode(
            (string) file_get_contents(__DIR__ . '/../Fixtures/FHIR/immunization.json'),
            true
        );
        $this->assertIsArray($fixtures);
        $fixture = $fixtures[0];
        $this->assertIsArray($fixture);
        $fixture['patient'] = ['reference' => 'Patient/' . $this->patientUuid];
        unset($fixture['id']);
        $body = [];
        foreach ($fixture as $key => $value) {
            $this->assertIsString($key);
            $body[$key] = $value;
        }

        $response = $this->testClient->post(self::ENDPOINT, $body);
        $responseBody = $response->getBody()->getContents();
        $this->assertSame(
            Response::HTTP_CREATED,
            $response->getStatusCode(),
            'Seeding the immunization should return 201. Body: ' . $responseBody
        );
        $created = json_decode($responseBody, true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($created);
        $uuid = $created['uuid'] ?? null;
        $this->assertIsString($uuid);
        $this->immunizationUuid = $uuid;
    }

    /**
     * Removes the seeded immunization and patient, then revokes the token.
     */
    protected function tearDown(): void
    {
        // PHPUnit runs tearDown() even when setUp() fails part way, so every property is
        // checked before it is used.
        try {
            // This run's immunizations only, so parallel workers keep theirs.
            // Each immunization row has a matching uuid_registry entry that
            // must be removed first; otherwise the registry row outlives
            // the immunization and tightens future UUID collisions.
            if (isset($this->immunizationUuid)) {
                $this->deleteImmunizationAndUuid($this->immunizationUuid);
            }
            foreach ($this->extraImmunizationUuids as $extraUuid) {
                $this->deleteImmunizationAndUuid($extraUuid);
            }
            $this->extraImmunizationUuids = [];
            if (isset($this->fixtureManager)) {
                $this->fixtureManager->removePatientFixtures();
            }
        } finally {
            if (isset($this->testClient)) {
                try {
                    $this->testClient->cleanupRevokeAuth();
                } finally {
                    $this->testClient->cleanupClient();
                }
            }
        }
    }

    /**
     * A patient search returns the seeded immunization.
     */
    public function testSearchByPatientIncludesSeededImmunization(): void
    {
        $bundle = $this->assertCollectionBundle(
            $this->testClient->get(self::ENDPOINT, ['patient' => $this->patientUuid])
        );
        $entries = $bundle['entry'] ?? null;
        $this->assertIsArray($entries);
        $found = false;
        foreach ($entries as $entry) {
            $this->assertIsArray($entry);
            $resource = $entry['resource'] ?? null;
            $this->assertIsArray($resource);
            $this->assertSame('Immunization', $resource['resourceType'] ?? null);
            if (($resource['id'] ?? null) === $this->immunizationUuid) {
                $this->assertSeededImmunization($resource);
                $found = true;
            }
        }
        $this->assertTrue($found, 'The search must include the seeded immunization');
    }

    /**
     * Searching by patient A must not return an immunization owned by patient B.
     *
     * The other search tests above only install one patient-with-immunization,
     * so a server that silently ignored `?patient=` would still match —
     * the seeded immunization would be the only row either way. This
     * test seeds an immunization for patient A AND for a different
     * patient B, then asserts patient A's `?patient=` search returns
     * A's immunization but not B's.
     */
    public function testSearchByPatientExcludesOtherPatientsImmunization(): void
    {
        $patientBUuid = $this->resolveSecondPatientUuid();
        $immunizationBUuid = $this->seedImmunizationForPatient($patientBUuid);
        $this->assertNotSame(
            $this->immunizationUuid,
            $immunizationBUuid,
            'The two seeded immunizations must have distinct uuids'
        );

        $bundle = $this->assertCollectionBundle(
            $this->testClient->get(self::ENDPOINT, ['patient' => $this->patientUuid])
        );
        $entries = $bundle['entry'] ?? null;
        $this->assertIsArray($entries);
        $ids = [];
        foreach ($entries as $entry) {
            $this->assertIsArray($entry);
            $resource = $entry['resource'] ?? null;
            $this->assertIsArray($resource);
            $id = $resource['id'] ?? null;
            $this->assertIsString($id);
            $ids[] = $id;
        }
        $this->assertContains(
            $this->immunizationUuid,
            $ids,
            'Patient A ?patient= search must include patient A immunization'
        );
        $this->assertNotContains(
            $immunizationBUuid,
            $ids,
            'Patient A ?patient= search must NOT include patient B immunization'
        );
    }

    /**
     * An _id search returns only the seeded immunization.
     *
     * The fixture's site code is LA, which is also an option_id in the state and
     * nationality_with_country lists. A site join without a list_id repeated the row once per match.
     * This test also creates a same-code CVX/CPT4 pair so a code-only vaccine join is deterministic.
     */
    public function testSearchByIdReturnsOnlySeededImmunization(): void
    {
        $cvxType = QueryUtils::querySingleRow(
            'SELECT ct_id FROM code_types WHERE ct_key = ?',
            ['CVX']
        );
        $this->assertIsArray($cvxType);
        $rawCvxTypeId = $cvxType['ct_id'] ?? null;
        $this->assertIsNumeric($rawCvxTypeId);
        $otherType = QueryUtils::querySingleRow(
            'SELECT ct_id FROM code_types WHERE ct_key = ?',
            ['CPT4']
        );
        $this->assertIsArray($otherType);
        $rawOtherTypeId = $otherType['ct_id'] ?? null;
        $this->assertIsNumeric($rawOtherTypeId);
        $cvxTypeId = (int) $rawCvxTypeId;
        $otherTypeId = (int) $rawOtherTypeId;
        $this->assertNotSame($cvxTypeId, $otherTypeId);

        // Give this test its own CVX code and a same-code row in another system.
        // A code-only join then duplicates the immunization; a CVX-scoped join does not.
        $collisionCode = 'T' . substr(str_replace('-', '', $this->immunizationUuid), 0, 16);
        $cvxCodeText = 'immunization-api-test-' . $this->immunizationUuid . '-cvx';
        $otherCodeText = 'immunization-api-test-' . $this->immunizationUuid . '-cpt4';

        try {
            QueryUtils::sqlStatementThrowException(
                'INSERT INTO codes (code_text, code, code_type) VALUES (?, ?, ?)',
                [$cvxCodeText, $collisionCode, $cvxTypeId]
            );
            QueryUtils::sqlStatementThrowException(
                'INSERT INTO codes (code_text, code, code_type) VALUES (?, ?, ?)',
                [$otherCodeText, $collisionCode, $otherTypeId]
            );
            QueryUtils::sqlStatementThrowException(
                'UPDATE immunizations SET cvx_code = ? WHERE uuid = ?',
                [$collisionCode, UuidRegistry::uuidToBytes($this->immunizationUuid)]
            );

            $bundle = $this->assertCollectionBundle(
                $this->testClient->get(self::ENDPOINT, ['_id' => $this->immunizationUuid])
            );
            $entries = $bundle['entry'] ?? null;
            $this->assertIsArray($entries);
            $this->assertCount(1, $entries);
            $entry = $entries[0];
            $this->assertIsArray($entry);
            $resource = $entry['resource'] ?? null;
            $this->assertIsArray($resource);
            $this->assertSeededImmunization($resource, $collisionCode);
        } finally {
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM codes WHERE code_text = ? AND code = ? AND code_type = ?',
                [$cvxCodeText, $collisionCode, $cvxTypeId]
            );
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM codes WHERE code_text = ? AND code = ? AND code_type = ?',
                [$otherCodeText, $collisionCode, $otherTypeId]
            );
        }
    }

    /**
     * Reading by uuid returns the seeded immunization.
     */
    public function testReadReturnsSeededImmunization(): void
    {
        $response = $this->testClient->getOne(self::ENDPOINT, $this->immunizationUuid);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSeededImmunization($this->decodeJson($response));
    }

    /**
     * An _id search for an absent uuid returns an empty collection Bundle.
     */
    public function testSearchWithNoMatchReturnsEmptyBundle(): void
    {
        $bundle = $this->assertCollectionBundle(
            $this->testClient->get(self::ENDPOINT, ['_id' => self::ABSENT_UUID])
        );
        $this->assertSame([], $bundle['entry'] ?? []);
    }

    /**
     * Reading an absent uuid returns 404 with an empty JSON array.
     */
    public function testReadAbsentUuidReturnsNotFound(): void
    {
        $response = $this->testClient->getOne(self::ENDPOINT, self::ABSENT_UUID);
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertSame([], $this->decodeJson($response));
    }

    /**
     * Searching without a token returns 401.
     */
    public function testSearchWithoutTokenReturnsUnauthorized(): void
    {
        $client = new ApiTestClient(self::baseUrl(), false);
        $this->assertUnauthorized($client->get(self::ENDPOINT));
    }

    /**
     * Reading without a token returns 401.
     */
    public function testReadWithoutTokenReturnsUnauthorized(): void
    {
        $client = new ApiTestClient(self::baseUrl(), false);
        $this->assertUnauthorized($client->getOne(self::ENDPOINT, $this->immunizationUuid));
    }

    /**
     * Remove an immunization row and its uuid_registry entry in the order
     * that keeps the registry consistent: the registry row is deleted
     * first, while the immunizations row still carries the uuid, then
     * the immunizations row itself.
     */
    private function deleteImmunizationAndUuid(string $uuid): void
    {
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM uuid_registry WHERE table_name = 'immunizations' AND uuid = ?",
            [UuidRegistry::uuidToBytes($uuid)]
        );
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM immunizations WHERE uuid = ?',
            [UuidRegistry::uuidToBytes($uuid)]
        );
    }

    /**
     * Resolve a second fixture patient's FHIR uuid. installPatientFixtures()
     * in setUp registers every patient listed in patients.php; the second
     * entry gives us a distinct patient to seed an immunization against
     * for the cross-patient-exclusion test.
     */
    private function resolveSecondPatientUuid(): string
    {
        $patients = $this->fixtureManager->getPatientFixtures();
        $this->assertGreaterThan(
            1,
            count($patients),
            'patients.php must seed at least two patients for the cross-patient test'
        );
        $secondPatient = $patients[1];
        $this->assertIsArray($secondPatient);
        $pubpid = $secondPatient['pubpid'] ?? null;
        $this->assertIsString($pubpid, 'Second patient fixture must carry a pubpid');

        $record = QueryUtils::querySingleRow(
            'SELECT uuid FROM patient_data WHERE pubpid = ?',
            [$pubpid]
        );
        $this->assertIsArray($record);
        return UuidRegistry::uuidToString($record['uuid']);
    }

    /**
     * POST a fixture Immunization bound to the given patient, record its
     * uuid for tearDown sweep, and return it. Mirrors the setUp seed
     * flow, scoped to the per-test second-patient scenario.
     */
    private function seedImmunizationForPatient(string $patientUuid): string
    {
        $fixtures = json_decode(
            (string) file_get_contents(__DIR__ . '/../Fixtures/FHIR/immunization.json'),
            true
        );
        $this->assertIsArray($fixtures);
        $fixture = $fixtures[0];
        $this->assertIsArray($fixture);
        $fixture['patient'] = ['reference' => 'Patient/' . $patientUuid];
        unset($fixture['id']);
        $body = [];
        foreach ($fixture as $key => $value) {
            $this->assertIsString($key);
            $body[$key] = $value;
        }

        $response = $this->testClient->post(self::ENDPOINT, $body);
        $responseBody = $response->getBody()->getContents();
        $this->assertSame(
            Response::HTTP_CREATED,
            $response->getStatusCode(),
            'Seeding immunization for second patient should return 201. Body: ' . $responseBody
        );
        $created = json_decode($responseBody, true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($created);
        $uuid = $created['uuid'] ?? null;
        $this->assertIsString($uuid);
        $this->extraImmunizationUuids[] = $uuid;
        return $uuid;
    }

    /**
     * Asserts the seeded influenza immunization: id, status, patient, CVX code, date, lot,
     * expiration and note.
     *
     * @param array<array-key, mixed> $resource
     */
    private function assertSeededImmunization(
        array $resource,
        string $expectedCvxCode = self::INFLUENZA_CVX
    ): void
    {
        $this->assertSame('Immunization', $resource['resourceType'] ?? null);
        $this->assertSame($this->immunizationUuid, $resource['id'] ?? null);
        $this->assertSame('completed', $resource['status'] ?? null);

        $patient = $resource['patient'] ?? null;
        $this->assertIsArray($patient);
        $this->assertSame('Patient/' . $this->patientUuid, $patient['reference'] ?? null);

        $vaccineCode = $resource['vaccineCode'] ?? null;
        $this->assertIsArray($vaccineCode);
        $codings = $vaccineCode['coding'] ?? null;
        $this->assertIsArray($codings);
        $cvxCodes = [];
        foreach ($codings as $coding) {
            $this->assertIsArray($coding);
            if (($coding['system'] ?? null) === self::CVX_SYSTEM) {
                $cvxCodes[] = $coding['code'] ?? null;
            }
        }
        $this->assertContains($expectedCvxCode, $cvxCodes);

        $occurrence = $resource['occurrenceDateTime'] ?? null;
        $this->assertIsString($occurrence);
        $this->assertStringStartsWith('2021-01-10', $occurrence);

        $this->assertSame('AAJN11K', $resource['lotNumber'] ?? null);

        $expiration = $resource['expirationDate'] ?? null;
        $this->assertIsString($expiration);
        $this->assertStringStartsWith('2099-02-15', $expiration);

        $notes = $resource['note'] ?? null;
        $this->assertIsArray($notes);
        $note = $notes[0] ?? null;
        $this->assertIsArray($note);
        $this->assertSame('test-fixture immunization for influenza', $note['text'] ?? null);
    }

    /**
     * Asserts a 200 collection Bundle and returns it decoded.
     *
     * @return array<array-key, mixed>
     */
    private function assertCollectionBundle(ResponseInterface $response): array
    {
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $bundle = $this->decodeJson($response);
        $this->assertSame('Bundle', $bundle['resourceType'] ?? null);
        $this->assertSame('collection', $bundle['type'] ?? null);
        return $bundle;
    }

    /**
     * Asserts the 401 body the OAuth layer returns, not a FHIR OperationOutcome.
     */
    private function assertUnauthorized(ResponseInterface $response): void
    {
        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $body = $this->decodeJson($response);
        $this->assertArrayHasKey('error', $body);
        $message = $body['message'] ?? null;
        $this->assertIsString($message);
        $this->assertStringContainsString('denied the request', $message);
        $this->assertArrayNotHasKey('resourceType', $body);
    }

    /**
     * Decodes a JSON response body into an array.
     *
     * @return array<array-key, mixed>
     */
    private function decodeJson(ResponseInterface $response): array
    {
        $body = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($body);
        return $body;
    }

    /**
     * Returns the API base URL from OPENEMR_BASE_URL_API, or https://localhost.
     */
    private static function baseUrl(): string
    {
        $baseUrl = getenv('OPENEMR_BASE_URL_API', true);
        return is_string($baseUrl) && $baseUrl !== '' ? $baseUrl : 'https://localhost';
    }
}
