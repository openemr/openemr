<?php

/**
 * HTTP search and read tests for the FHIR Coverage resource.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Malik Cole <colemalik555@gmail.com>
 * @copyright Copyright (c) 2026 Malik Cole
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

class CoverageFhirApiTest extends TestCase
{
    private const ENDPOINT = '/apis/default/fhir/Coverage';
    private const ABSENT_UUID = '11111111-1111-1111-1111-111111111111';

    private ApiTestClient $testClient;
    private FixtureManager $fixtureManager;
    private string $patientUuid;
    private string $insurerUuid;
    private string $coverageUuid;
    private string $subscriberId;

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
        $this->insurerUuid = $this->fixtureManager->installInsuranceCompanyFixture();
        $nextInsurerId = QueryUtils::fetchSingleValue(
            'SELECT COALESCE(MAX(id), 0) + 1 AS id FROM insurance_companies',
            'id'
        );
        $this->assertIsNumeric($nextInsurerId);
        QueryUtils::sqlStatementThrowException(
            'UPDATE insurance_companies SET id = ? WHERE uuid = ? AND id = 0',
            [$nextInsurerId, UuidRegistry::uuidToBytes($this->insurerUuid)]
        );

        $json = file_get_contents(__DIR__ . '/../Fixtures/FHIR/coverage.json');
        $this->assertIsString($json);
        $fixtures = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($fixtures);
        $fixture = $fixtures[0];
        $this->assertIsArray($fixture);
        $fixture['beneficiary'] = ['reference' => 'Patient/' . $this->patientUuid];
        $fixture['payor'] = [['reference' => 'Organization/' . $this->insurerUuid]];
        unset($fixture['id']);
        $subscriberId = $fixture['subscriberId'] ?? null;
        $this->assertIsString($subscriberId);
        $this->subscriberId = $subscriberId;
        $body = [];
        foreach ($fixture as $key => $value) {
            $this->assertIsString($key);
            $body[$key] = $value;
        }

        $response = $this->testClient->post(self::ENDPOINT, $body);
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $created = $this->decodeJson($response);
        $uuid = $created['uuid'] ?? null;
        $this->assertIsString($uuid);
        $this->coverageUuid = $uuid;
    }

    protected function tearDown(): void
    {
        try {
            if (isset($this->fixtureManager)) {
                try {
                    $this->fixtureManager->removeCoverageFixtures();
                } finally {
                    try {
                        $this->fixtureManager->removePatientFixtures();
                    } finally {
                        $this->fixtureManager->removeInsuranceCompanyFixtures();
                    }
                }
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

    public function testSearchByPatientIncludesSeededCoverage(): void
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
            $beneficiary = $resource['beneficiary'] ?? null;
            $this->assertIsArray($beneficiary);
            $this->assertSame('Patient/' . $this->patientUuid, $beneficiary['reference'] ?? null);
            if (($resource['id'] ?? null) === $this->coverageUuid) {
                $this->assertSeededCoverage($resource);
                $found = true;
            }
        }
        $this->assertTrue($found, 'Patient search must include the seeded Coverage');
    }

    public function testSearchByIdReturnsOnlySeededCoverage(): void
    {
        $bundle = $this->assertCollectionBundle(
            $this->testClient->get(self::ENDPOINT, ['_id' => $this->coverageUuid])
        );
        $entries = $bundle['entry'] ?? null;
        $this->assertIsArray($entries);
        $this->assertCount(1, $entries);
        $entry = $entries[0];
        $this->assertIsArray($entry);
        $resource = $entry['resource'] ?? null;
        $this->assertIsArray($resource);
        $this->assertSeededCoverage($resource);
    }

    public function testReadReturnsSeededCoverage(): void
    {
        $response = $this->testClient->getOne(self::ENDPOINT, $this->coverageUuid);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSeededCoverage($this->decodeJson($response));
    }

    public function testSearchWithNoMatchReturnsEmptyBundle(): void
    {
        $bundle = $this->assertCollectionBundle(
            $this->testClient->get(self::ENDPOINT, ['_id' => self::ABSENT_UUID])
        );
        $this->assertSame(0, $bundle['total'] ?? null);
        $this->assertSame([], $bundle['entry'] ?? []);
    }

    public function testReadMalformedUuidReturnsBadRequest(): void
    {
        $response = $this->testClient->getOne(self::ENDPOINT, 'not-a-uuid');
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertArrayHasKey('validationErrors', $this->decodeJson($response));
    }

    public function testReadAbsentUuidReturnsNotFound(): void
    {
        $response = $this->testClient->getOne(self::ENDPOINT, self::ABSENT_UUID);
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertSame([], $this->decodeJson($response));
    }

    public function testSearchWithoutTokenReturnsUnauthorized(): void
    {
        $client = new ApiTestClient(self::baseUrl(), false);
        $this->assertUnauthorized($client->get(self::ENDPOINT));
    }

    public function testReadWithoutTokenReturnsUnauthorized(): void
    {
        $client = new ApiTestClient(self::baseUrl(), false);
        $this->assertUnauthorized($client->getOne(self::ENDPOINT, $this->coverageUuid));
    }

    /** @param array<array-key, mixed> $resource */
    private function assertSeededCoverage(array $resource): void
    {
        $this->assertSame('Coverage', $resource['resourceType'] ?? null);
        $this->assertSame($this->coverageUuid, $resource['id'] ?? null);
        $this->assertSame('active', $resource['status'] ?? null);
        $this->assertSame($this->subscriberId, $resource['subscriberId'] ?? null);
        $beneficiary = $resource['beneficiary'] ?? null;
        $this->assertIsArray($beneficiary);
        $this->assertSame('Patient/' . $this->patientUuid, $beneficiary['reference'] ?? null);
        $payors = $resource['payor'] ?? null;
        $this->assertIsArray($payors);
        $this->assertCount(1, $payors);
        $payor = $payors[0];
        $this->assertIsArray($payor);
        $this->assertSame('Organization/' . $this->insurerUuid, $payor['reference'] ?? null);
    }

    /** @return array<array-key, mixed> */
    private function assertCollectionBundle(ResponseInterface $response): array
    {
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $bundle = $this->decodeJson($response);
        $this->assertSame('Bundle', $bundle['resourceType'] ?? null);
        $this->assertSame('collection', $bundle['type'] ?? null);
        return $bundle;
    }

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

    /** @return array<array-key, mixed> */
    private function decodeJson(ResponseInterface $response): array
    {
        $body = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($body);
        return $body;
    }

    private static function baseUrl(): string
    {
        $baseUrl = getenv('OPENEMR_BASE_URL_API', true);
        return is_string($baseUrl) && $baseUrl !== '' ? $baseUrl : 'https://localhost';
    }
}
