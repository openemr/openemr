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
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
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
        // getUnregisteredUuid(), not UuidRegistry::createUuid(): createUuid() inserts a
        // uuid_registry row, and this test never creates the patient_data row that would carry
        // it, so teardown -- which finds rows through their targets -- cannot see it and
        // the registry row survives every run. An unregistered uuid is just as
        // unresolvable, which is all the test needs.
        $bogusUuid = $this->fixtureManager->getUnregisteredUuid();
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

        $updatedName = 'test-fixture Care Team Updated';
        $payload = $this->fhirCareTeamFixture->jsonSerialize();
        $payload['id'] = $fhirId;
        $payload['name'] = $updatedName;
        $updated = new FHIRCareTeam($payload);

        $result = $this->fhirCareTeamService->update($fhirId, $updated);
        $this->assertTrue(
            $result->isValid(),
            'Update should succeed: ' . json_encode($result->getValidationMessages())
        );
        $this->assertNotEmpty($result->getData());

        // The renamed team is read back rather than trusted from update()'s own answer: a
        // service that accepted the write and stored nothing satisfies every assertion above.
        $readBack = $this->fhirCareTeamService->getOne($fhirId);
        $this->assertTrue($readBack->isValid(), 'Read-back should succeed');
        $readRecords = $readBack->getData();
        $this->assertIsArray($readRecords);
        $this->assertArrayHasKey(0, $readRecords);
        $serialized = json_decode((string) json_encode($readRecords[0]), true);
        $this->assertIsArray($serialized);
        $this->assertSame($updatedName, $serialized['name'] ?? null, 'CareTeam.name should be updated');
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
