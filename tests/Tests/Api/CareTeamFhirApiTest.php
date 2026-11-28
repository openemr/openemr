<?php

/**
 * FHIR CareTeam API search test.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    HoangNam18
 * @copyright Copyright (c) 2026 HoangNam18
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

final class CareTeamFhirApiTest extends TestCase
{
    private const ENDPOINT = '/apis/default/fhir/CareTeam';

    private ApiTestClient $testClient;
    private FixtureManager $fixtures;

    protected function setUp(): void
    {
        $baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthTokenOrFail(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $this->fixtures = new FixtureManager();
    }

    protected function tearDown(): void
    {
        try {
            if (isset($this->fixtures)) {
                $this->fixtures->removeCareTeamFixtures();
                $this->fixtures->removePatientFixtures();
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

    public function testSearchByPatientReturnsOnlyThatPatientsCareTeam(): void
    {
        $patientUuid = $this->installPatient(0);
        $careTeamUuid = $this->createCareTeam($patientUuid);
        $otherCareTeamUuid = $this->createCareTeam($this->installPatient(1));
        self::assertNotSame($careTeamUuid, $otherCareTeamUuid);

        $response = $this->testClient->get(self::ENDPOINT, ['patient' => $patientUuid]);
        $body = (string) $response->getBody();
        self::assertSame(Response::HTTP_OK, $response->getStatusCode(), $body);

        $bundle = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
        self::assertIsArray($bundle);
        self::assertSame('Bundle', $bundle['resourceType'] ?? null);
        self::assertSame('collection', $bundle['type'] ?? null);
        $entries = $bundle['entry'] ?? null;
        self::assertIsList($entries);
        self::assertCount(1, $entries, 'The patient filter should exclude the other CareTeam.');
        $entry = $entries[0];
        self::assertIsArray($entry);
        $careTeam = $entry['resource'] ?? null;
        self::assertIsArray($careTeam);
        self::assertSame('CareTeam', $careTeam['resourceType'] ?? null);
        self::assertSame($careTeamUuid, $careTeam['id'] ?? null);
        $subject = $careTeam['subject'] ?? null;
        self::assertIsArray($subject);
        self::assertSame('Patient/' . $patientUuid, $subject['reference'] ?? null);
    }

    private function installPatient(int $fixtureIndex): string
    {
        $patient = $this->fixtures->getPatientFixtures()[$fixtureIndex] ?? null;
        self::assertIsArray($patient);
        $pubpid = $patient['pubpid'] ?? null;
        self::assertIsString($pubpid);
        $this->fixtures->installSinglePatientFixture($patient);

        $record = QueryUtils::querySingleRow('SELECT uuid FROM patient_data WHERE pubpid = ?', [$pubpid]);
        self::assertIsArray($record);
        $uuid = $record['uuid'] ?? null;
        self::assertIsString($uuid);

        return UuidRegistry::uuidToString($uuid);
    }

    private function createCareTeam(string $patientUuid): string
    {
        $response = $this->testClient->post(self::ENDPOINT, [
            'resourceType' => 'CareTeam',
            'status' => 'active',
            'name' => 'test-fixture CareTeam search',
            'subject' => ['reference' => 'Patient/' . $patientUuid],
        ]);
        $body = (string) $response->getBody();
        self::assertSame(Response::HTTP_CREATED, $response->getStatusCode(), $body);

        $created = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
        self::assertIsArray($created);
        $uuid = $created['uuid'] ?? null;
        self::assertIsString($uuid);
        self::assertNotSame('', $uuid);

        return $uuid;
    }
}
