<?php

namespace OpenEMR\Tests\Services\FHIR;

use Monolog\Level;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRRelatedPerson;
use OpenEMR\Services\FHIR\FhirRelatedPersonService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * FHIR RelatedPerson Service CRUD Tests
 *
 * RelatedPerson writes touch multiple tables atomically: person, contact (for the
 * related person AND the patient owner if missing), contact_telecom, contact_address,
 * addresses, and contact_relation. Cleanup in tearDown walks the same set.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    OpenEMR Contributors
 * @copyright Copyright (c) 2026 OpenEMR Contributors
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FhirRelatedPersonServiceCrudTest extends TestCase
{
    private FixtureManager $fixtureManager;
    private FHIRRelatedPerson $fhirRelatedPersonFixture;
    private FhirRelatedPersonService $fhirRelatedPersonService;
    private string $patientUuid;

    protected function setUp(): void
    {
        $this->fixtureManager = new FixtureManager();

        $this->fixtureManager->installPatientFixtures();
        $patientFixture = $this->fixtureManager->getPatientFixtures()[0];
        $patientRecord = QueryUtils::querySingleRow(
            "SELECT uuid FROM patient_data WHERE pubpid = ?",
            [$patientFixture['pubpid']]
        );
        $this->patientUuid = UuidRegistry::uuidToString($patientRecord['uuid']);

        $fixture = (array) $this->fixtureManager->getSingleFhirRelatedPersonFixture();
        $fixture['patient'] = ['reference' => 'Patient/' . $this->patientUuid];
        $this->fhirRelatedPersonFixture = new FHIRRelatedPerson($fixture);

        $this->fhirRelatedPersonService = new FhirRelatedPersonService();
        $this->fhirRelatedPersonService->setSystemLogger(new SystemLogger(Level::Critical));
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removeRelatedPersonFixtures();
        $this->fixtureManager->removePatientFixtures();
    }

    #[Test]
    public function testInsert(): void
    {
        $this->fhirRelatedPersonFixture->setId(null);
        $result = $this->fhirRelatedPersonService->insert($this->fhirRelatedPersonFixture);
        $this->assertTrue(
            $result->isValid(),
            'Insert should succeed: ' . json_encode($result->getValidationMessages())
        );

        $data = $result->getData()[0];
        $this->assertArrayHasKey('uuid', $data);
        $this->assertIsString($data['uuid']);
    }

    #[Test]
    public function testInsertWithUnresolvablePatient(): void
    {
        $bogusUuid = UuidRegistry::uuidToString(
            (new UuidRegistry(['table_name' => 'patient_data']))->createUuid()
        );
        $this->fhirRelatedPersonFixture->setId(null);
        $payload = $this->fhirRelatedPersonFixture->jsonSerialize();
        $payload['patient'] = ['reference' => 'Patient/' . $bogusUuid];
        $fixture = new FHIRRelatedPerson($payload);

        $result = $this->fhirRelatedPersonService->insert($fixture);
        $this->assertFalse($result->isValid());
        $this->assertEquals(0, count($result->getData()));
    }

    #[Test]
    public function testUpdate(): void
    {
        $this->fhirRelatedPersonFixture->setId(null);
        $insertResult = $this->fhirRelatedPersonService->insert($this->fhirRelatedPersonFixture);
        $this->assertTrue(
            $insertResult->isValid(),
            'Insert should succeed: ' . json_encode($insertResult->getValidationMessages())
        );
        $fhirId = $insertResult->getData()[0]['uuid'];

        $payload = $this->fhirRelatedPersonFixture->jsonSerialize();
        $payload['id'] = $fhirId;
        $payload['name'] = [[
            'use' => 'official',
            'family' => 'test-fixture-RelatedLastUpdated',
            'given' => ['test-fixture-RelatedFirstUpdated'],
        ]];
        $updated = new FHIRRelatedPerson($payload);

        $result = $this->fhirRelatedPersonService->update($fhirId, $updated);
        $this->assertTrue(
            $result->isValid(),
            'Update should succeed: ' . json_encode($result->getValidationMessages())
        );
        $this->assertNotEmpty($result->getData());
    }

    #[Test]
    public function testUpdateWithBadUuid(): void
    {
        $result = $this->fhirRelatedPersonService->update('bad-uuid', $this->fhirRelatedPersonFixture);
        $this->assertFalse($result->isValid());
        $this->assertEquals(0, count($result->getData()));
    }
}
