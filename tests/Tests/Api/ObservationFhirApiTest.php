<?php

/**
 * HTTP search and read tests for the FHIR Observation resource.
 *
 * ObservationFhirWriteApiTest covers POST and PUT; this covers GET /fhir/Observation and
 * GET /fhir/Observation/:uuid. A heart rate vital sign is seeded through the API in setUp,
 * the same way the write test does it, and is the observation every test looks for.
 *
 * Observation ids for vitals are mapped uuids: the FHIR service resolves them back to the
 * vitals row, category and code. Reading by id and searching by _id both go through that
 * mapping, so they are tested separately.
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
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIREncounter;
use OpenEMR\Services\FHIR\FhirEncounterService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

class ObservationFhirApiTest extends TestCase
{
    private const ENDPOINT = '/apis/default/fhir/Observation';
    private const ABSENT_UUID = '11111111-1111-1111-1111-111111111111';
    private const CATEGORY_SYSTEM = 'http://terminology.hl7.org/CodeSystem/observation-category';
    private const HEART_RATE_LOINC = '8867-4';

    private ApiTestClient $testClient;
    private FixtureManager $fixtureManager;
    private string $patientUuid;
    private string $encounterUuid;
    private string $observationUuid;

    /**
     * Seeds a patient, an encounter and a heart rate observation, and gets an API token.
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

        // Vitals are stored as an encounter form, so the seeded observation needs a real encounter.
        $encounterFixtures = json_decode(
            (string) file_get_contents(__DIR__ . '/../Fixtures/FHIR/encounter.json'),
            true
        );
        $this->assertIsArray($encounterFixtures);
        $encounterPayload = $encounterFixtures[0];
        $this->assertIsArray($encounterPayload);
        $encounterPayload['subject'] = ['reference' => 'Patient/' . $this->patientUuid];
        $encounterInsert = (new FhirEncounterService())->insert(new FHIREncounter($encounterPayload));
        $this->assertTrue(
            $encounterInsert->isValid(),
            'Encounter insert (setup) failed: ' . json_encode($encounterInsert->getValidationMessages())
        );
        $encounterData = $encounterInsert->getData();
        $this->assertIsArray($encounterData);
        $encounter = $encounterData[0];
        $this->assertIsArray($encounter);
        $this->assertIsString($encounter['euuid']);
        $this->encounterUuid = $encounter['euuid'];

        // The heart rate fixture: one value, one vitals column.
        $observationFixtures = json_decode(
            (string) file_get_contents(__DIR__ . '/../Fixtures/FHIR/observation.json'),
            true
        );
        $this->assertIsArray($observationFixtures);
        $fixture = $observationFixtures[1];
        $this->assertIsArray($fixture);
        $fixture['subject'] = ['reference' => 'Patient/' . $this->patientUuid];
        $fixture['encounter'] = ['reference' => 'Encounter/' . $this->encounterUuid];
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
            'Seeding the heart rate observation should return 201. Body: ' . $responseBody
        );
        $created = json_decode($responseBody, true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($created);
        $uuid = $created['uuid'] ?? null;
        $this->assertIsString($uuid);
        $this->observationUuid = $uuid;
    }

    /**
     * Removes the seeded observation, encounter and patient, then revokes the token.
     */
    protected function tearDown(): void
    {
        // PHPUnit runs tearDown() even when setUp() fails part way, so every property is
        // checked before it is used.
        try {
            if (isset($this->fixtureManager)) {
                $this->fixtureManager->removeObservationFixtures();
            }
            // This run's encounter only, so parallel workers keep theirs.
            if (isset($this->encounterUuid)) {
                QueryUtils::sqlStatementThrowException(
                    'DELETE FROM form_encounter WHERE uuid = ?',
                    [UuidRegistry::uuidToBytes($this->encounterUuid)]
                );
            }
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
     * A patient search returns the seeded observation.
     */
    public function testSearchByPatientIncludesSeededObservation(): void
    {
        $bundle = $this->assertCollectionBundle(
            $this->testClient->get(self::ENDPOINT, ['patient' => $this->patientUuid])
        );
        $this->assertBundleContainsSeededObservation($bundle);
    }

    /**
     * A patient search narrowed to vital signs returns the seeded observation.
     */
    public function testSearchByPatientAndVitalSignsCategoryIncludesSeededObservation(): void
    {
        $bundle = $this->assertCollectionBundle(
            $this->testClient->get(self::ENDPOINT, [
                'patient' => $this->patientUuid,
                'category' => 'vital-signs',
            ])
        );
        $this->assertBundleContainsSeededObservation($bundle);
    }

    /**
     * A patient search narrowed to laboratory results leaves the seeded vital sign out.
     */
    public function testSearchByPatientAndLaboratoryCategoryExcludesSeededObservation(): void
    {
        $bundle = $this->assertCollectionBundle(
            $this->testClient->get(self::ENDPOINT, [
                'patient' => $this->patientUuid,
                'category' => 'laboratory',
            ])
        );
        $entries = $bundle['entry'] ?? [];
        $this->assertIsArray($entries);
        foreach ($entries as $entry) {
            $this->assertIsArray($entry);
            $resource = $entry['resource'] ?? null;
            $this->assertIsArray($resource);
            $this->assertNotSame(
                $this->observationUuid,
                $resource['id'] ?? null,
                'A laboratory search must not return the seeded vital sign'
            );
        }
    }

    /**
     * An _id search returns exactly the seeded observation.
     */
    public function testSearchByIdReturnsOnlySeededObservation(): void
    {
        $bundle = $this->assertCollectionBundle(
            $this->testClient->get(self::ENDPOINT, ['_id' => $this->observationUuid])
        );
        $entries = $bundle['entry'] ?? null;
        $this->assertIsArray($entries);
        $this->assertCount(1, $entries);
        $entry = $entries[0];
        $this->assertIsArray($entry);
        $resource = $entry['resource'] ?? null;
        $this->assertIsArray($resource);
        $this->assertSeededObservation($resource);
    }

    /**
     * Reading by uuid returns the seeded observation.
     */
    public function testReadReturnsSeededObservation(): void
    {
        $response = $this->testClient->getOne(self::ENDPOINT, $this->observationUuid);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSeededObservation($this->decodeJson($response));
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
        $this->assertUnauthorized($client->getOne(self::ENDPOINT, $this->observationUuid));
    }

    /**
     * Asserts that the Bundle holds only Observations and includes the seeded one.
     *
     * @param array<array-key, mixed> $bundle
     */
    private function assertBundleContainsSeededObservation(array $bundle): void
    {
        $entries = $bundle['entry'] ?? null;
        $this->assertIsArray($entries);
        $found = false;
        foreach ($entries as $entry) {
            $this->assertIsArray($entry);
            $resource = $entry['resource'] ?? null;
            $this->assertIsArray($resource);
            $this->assertSame('Observation', $resource['resourceType'] ?? null);
            if (($resource['id'] ?? null) === $this->observationUuid) {
                $this->assertSeededObservation($resource);
                $found = true;
            }
        }
        $this->assertTrue($found, 'The search must include the seeded heart rate observation');
    }

    /**
     * Asserts the seeded heart rate: id, status, subject, category, LOINC code and value.
     *
     * @param array<array-key, mixed> $resource
     */
    private function assertSeededObservation(array $resource): void
    {
        $this->assertSame('Observation', $resource['resourceType'] ?? null);
        $this->assertSame($this->observationUuid, $resource['id'] ?? null);
        $this->assertSame('final', $resource['status'] ?? null);

        $subject = $resource['subject'] ?? null;
        $this->assertIsArray($subject);
        $this->assertSame('Patient/' . $this->patientUuid, $subject['reference'] ?? null);

        $categories = $resource['category'] ?? null;
        $this->assertIsArray($categories);
        $categoryCodes = [];
        foreach ($categories as $category) {
            $this->assertIsArray($category);
            $codings = $category['coding'] ?? [];
            $this->assertIsArray($codings);
            foreach ($codings as $coding) {
                $this->assertIsArray($coding);
                if (($coding['system'] ?? null) === self::CATEGORY_SYSTEM) {
                    $categoryCodes[] = $coding['code'] ?? null;
                }
            }
        }
        $this->assertContains('vital-signs', $categoryCodes);

        $code = $resource['code'] ?? null;
        $this->assertIsArray($code);
        $codings = $code['coding'] ?? null;
        $this->assertIsArray($codings);
        $loincCodes = [];
        foreach ($codings as $coding) {
            $this->assertIsArray($coding);
            if (($coding['system'] ?? null) === 'http://loinc.org') {
                $loincCodes[] = $coding['code'] ?? null;
            }
        }
        $this->assertContains(self::HEART_RATE_LOINC, $loincCodes);

        $quantity = $resource['valueQuantity'] ?? null;
        $this->assertIsArray($quantity);
        $value = $quantity['value'] ?? null;
        $this->assertIsNumeric($value);
        $this->assertEqualsWithDelta(72, (float) $value, 0.001);
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
