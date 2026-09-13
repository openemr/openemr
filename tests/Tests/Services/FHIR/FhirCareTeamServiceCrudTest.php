<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Services\FHIR;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCareTeam;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\Services\FHIR\FhirCareTeamService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use OpenEMR\Tests\Fixtures\PractitionerFixtureManager;
use OpenEMR\Validators\ProcessingResult;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * FHIR CareTeam Service CRUD Tests
 *
 * CareTeam is patient-scoped (one team per patient). Practitioner participants
 * are resolved to users.id; non-Practitioner participants are not persisted on
 * write in this implementation.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Chris Dickman <chrisd@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FhirCareTeamServiceCrudTest extends TestCase
{
    private FixtureManager $fixtureManager;
    private PractitionerFixtureManager $practitionerFixtureManager;
    private FHIRCareTeam $fhirCareTeamFixture;
    private FhirCareTeamService $fhirCareTeamService;
    private string $patientUuid;
    private string $practitionerUuid;

    protected function setUp(): void
    {
        $this->fixtureManager = new FixtureManager();
        $this->practitionerFixtureManager = new PractitionerFixtureManager();

        $this->fixtureManager->installPatientFixtures();
        $patientFixture = $this->fixtureManager->getPatientFixtures()[0];
        $this->assertIsArray($patientFixture);
        $patientRecord = QueryUtils::querySingleRow(
            "SELECT uuid FROM patient_data WHERE pubpid = ?",
            [$patientFixture['pubpid']]
        );
        $this->assertIsArray($patientRecord);
        $this->patientUuid = UuidRegistry::uuidToString($patientRecord['uuid']);

        $this->practitionerFixtureManager->installPractitionerFixtures();
        $practitionerRow = QueryUtils::querySingleRow(
            "SELECT uuid FROM users WHERE fname LIKE 'test-fixture-%' LIMIT 1",
            []
        );
        $this->assertIsArray($practitionerRow);
        $this->practitionerUuid = UuidRegistry::uuidToString($practitionerRow['uuid']);

        $fixture = (array) $this->fixtureManager->getSingleFhirCareTeamFixture();
        $fixture['subject'] = ['reference' => 'Patient/' . $this->patientUuid];
        $participants = $fixture['participant'] ?? [];
        $this->assertIsArray($participants);
        $this->assertArrayHasKey(0, $participants);
        $this->assertIsArray($participants[0]);
        $participants[0]['member'] = ['reference' => 'Practitioner/' . $this->practitionerUuid];
        $fixture['participant'] = $participants;
        $this->fhirCareTeamFixture = new FHIRCareTeam($fixture);

        $this->fhirCareTeamService = new FhirCareTeamService();
        $this->fhirCareTeamService->setLogger($this->createMock(LoggerInterface::class));
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removeCareTeamFixtures();
        $this->fixtureManager->removePatientFixtures();
        $this->practitionerFixtureManager->removePractitionerFixtures();
    }

    #[Test]
    public function testInsert(): void
    {
        $this->fhirCareTeamFixture->setId(new FHIRId());
        $result = $this->fhirCareTeamService->insert($this->fhirCareTeamFixture);
        $this->assertTrue(
            $result->isValid(),
            'Insert should succeed: ' . json_encode($result->getValidationMessages())
        );

        $data = $this->firstDataRow($result);
        $this->assertArrayHasKey('uuid', $data);
        $this->assertIsString($data['uuid']);
    }

    #[Test]
    public function testInsertWithUnresolvableSubject(): void
    {
        $bogusUuid = UuidRegistry::uuidToString(
            (new UuidRegistry(['table_name' => 'patient_data']))->createUuid()
        );
        $this->fhirCareTeamFixture->setId(new FHIRId());
        $payload = $this->fhirCareTeamFixture->jsonSerialize();
        $payload['subject'] = ['reference' => 'Patient/' . $bogusUuid];
        $fixture = new FHIRCareTeam($payload);

        $result = $this->fhirCareTeamService->insert($fixture);
        $this->assertFalse($result->isValid());
        $this->assertSame([], $result->getData());
    }

    #[Test]
    public function testUpdate(): void
    {
        $this->fhirCareTeamFixture->setId(new FHIRId());
        $insertResult = $this->fhirCareTeamService->insert($this->fhirCareTeamFixture);
        $this->assertTrue(
            $insertResult->isValid(),
            'Insert should succeed: ' . json_encode($insertResult->getValidationMessages())
        );
        $fhirId = $this->firstDataRow($insertResult)['uuid'];
        $this->assertIsString($fhirId);

        $payload = $this->fhirCareTeamFixture->jsonSerialize();
        $payload['id'] = $fhirId;
        $payload['name'] = 'test-fixture Care Team Updated';
        $updated = new FHIRCareTeam($payload);

        $result = $this->fhirCareTeamService->update($fhirId, $updated);
        $this->assertTrue(
            $result->isValid(),
            'Update should succeed: ' . json_encode($result->getValidationMessages())
        );
        $this->assertNotEmpty($result->getData());

        // The payload above renames the team, so assert the rename actually landed. Without
        // this the test passes when update() ignores the body and hands back the stored team.
        $this->assertSame(
            'test-fixture Care Team Updated',
            self::digString($this->readBack($result), ['name']),
            'Update should return the renamed team'
        );
    }

    /**
     * The FHIR resource update() returns, as a plain array.
     *
     * Asserting on the serialized form rather than the getters keeps these checks independent
     * of whether a field comes back as a scalar or a wrapped FHIR primitive.
     *
     * @return array<mixed>
     */
    private function readBack(ProcessingResult $result): array
    {
        $data = $result->getData();
        $this->assertIsArray($data);
        $this->assertArrayHasKey(0, $data);
        $decoded = json_decode((string) json_encode($data[0]), true);
        $this->assertIsArray($decoded);

        return $decoded;
    }

    /**
     * Reads a nested string out of a decoded FHIR resource, or null when the path is absent.
     *
     * Chained offset reads on a json_decode() result are all `mixed` at level 10; walking the
     * path with a narrowing check keeps the assertions readable without casting.
     *
     * @param list<string|int> $path
     */
    private static function digString(mixed $source, array $path): ?string
    {
        $cursor = $source;
        foreach ($path as $key) {
            if (!is_array($cursor) || !array_key_exists($key, $cursor)) {
                return null;
            }
            $cursor = $cursor[$key];
        }

        return is_string($cursor) ? $cursor : null;
    }

    #[Test]
    public function testUpdateWithBadUuid(): void
    {
        $result = $this->fhirCareTeamService->update('bad-uuid', $this->fhirCareTeamFixture);
        $this->assertFalse($result->isValid());
        $this->assertSame([], $result->getData());
    }

    /**
     * Reads the first row of a ProcessingResult, asserting the shape as it goes so a
     * failed insert surfaces as a test failure rather than a type error downstream.
     *
     * @return array<mixed>
     */
    private function firstDataRow(ProcessingResult $result): array
    {
        $data = $result->getData();
        $this->assertIsArray($data);
        $this->assertArrayHasKey(0, $data);
        $row = $data[0];
        $this->assertIsArray($row);

        return $row;
    }
}
