<?php

namespace OpenEMR\Tests\Services\FHIR;

use Monolog\Level;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCareTeam;
use OpenEMR\Services\FHIR\FhirCareTeamService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use OpenEMR\Tests\Fixtures\PractitionerFixtureManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * FHIR CareTeam Service CRUD Tests
 *
 * CareTeam is patient-scoped (one team per patient). Practitioner participants
 * are resolved to users.id; non-Practitioner participants are not persisted on
 * write in this implementation.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    OpenEMR Contributors
 * @copyright Copyright (c) 2026 OpenEMR Contributors
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
        $patientRecord = QueryUtils::querySingleRow(
            "SELECT uuid FROM patient_data WHERE pubpid = ?",
            [$patientFixture['pubpid']]
        );
        $this->patientUuid = UuidRegistry::uuidToString($patientRecord['uuid']);

        $this->practitionerFixtureManager->installPractitionerFixtures();
        $practitionerRow = QueryUtils::querySingleRow(
            "SELECT uuid FROM users WHERE fname LIKE 'test-fixture-%' LIMIT 1",
            []
        );
        $this->practitionerUuid = UuidRegistry::uuidToString($practitionerRow['uuid']);

        $fixture = (array) $this->fixtureManager->getSingleFhirCareTeamFixture();
        $fixture['subject'] = ['reference' => 'Patient/' . $this->patientUuid];
        $fixture['participant'][0]['member']['reference'] = 'Practitioner/' . $this->practitionerUuid;
        $this->fhirCareTeamFixture = new FHIRCareTeam($fixture);

        $this->fhirCareTeamService = new FhirCareTeamService();
        $this->fhirCareTeamService->setSystemLogger(new SystemLogger(Level::Critical));
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
        $this->fhirCareTeamFixture->setId(null);
        $result = $this->fhirCareTeamService->insert($this->fhirCareTeamFixture);
        $this->assertTrue(
            $result->isValid(),
            'Insert should succeed: ' . json_encode($result->getValidationMessages())
        );

        $data = $result->getData()[0];
        $this->assertArrayHasKey('uuid', $data);
        $this->assertIsString($data['uuid']);
    }

    #[Test]
    public function testInsertWithUnresolvableSubject(): void
    {
        $bogusUuid = UuidRegistry::uuidToString(
            (new UuidRegistry(['table_name' => 'patient_data']))->createUuid()
        );
        $this->fhirCareTeamFixture->setId(null);
        $payload = $this->fhirCareTeamFixture->jsonSerialize();
        $payload['subject'] = ['reference' => 'Patient/' . $bogusUuid];
        $fixture = new FHIRCareTeam($payload);

        $result = $this->fhirCareTeamService->insert($fixture);
        $this->assertFalse($result->isValid());
        $this->assertEquals(0, count($result->getData()));
    }

    #[Test]
    public function testUpdate(): void
    {
        $this->fhirCareTeamFixture->setId(null);
        $insertResult = $this->fhirCareTeamService->insert($this->fhirCareTeamFixture);
        $this->assertTrue(
            $insertResult->isValid(),
            'Insert should succeed: ' . json_encode($insertResult->getValidationMessages())
        );
        $fhirId = $insertResult->getData()[0]['uuid'];

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
    }

    #[Test]
    public function testUpdateWithBadUuid(): void
    {
        $result = $this->fhirCareTeamService->update('bad-uuid', $this->fhirCareTeamFixture);
        $this->assertFalse($result->isValid());
        $this->assertEquals(0, count($result->getData()));
    }
}
