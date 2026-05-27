<?php

namespace OpenEMR\Tests\Services\FHIR;

use Monolog\Level;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRServiceRequest;
use OpenEMR\Services\FHIR\FhirServiceRequestService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * FHIR ServiceRequest Service CRUD Tests
 *
 * ServiceRequest maps to one procedure_order row + N procedure_order_code rows.
 * Insert/update are transactional (delete-then-insert for codes on update per
 * FHIR PUT replace semantics).
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    OpenEMR Contributors
 * @copyright Copyright (c) 2026 OpenEMR Contributors
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FhirServiceRequestServiceCrudTest extends TestCase
{
    private FixtureManager $fixtureManager;
    private FHIRServiceRequest $fhirServiceRequestFixture;
    private FhirServiceRequestService $fhirServiceRequestService;
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

        $fixture = (array) $this->fixtureManager->getSingleFhirServiceRequestFixture();
        $fixture['subject'] = ['reference' => 'Patient/' . $this->patientUuid];
        $this->fhirServiceRequestFixture = new FHIRServiceRequest($fixture);

        $this->fhirServiceRequestService = new FhirServiceRequestService();
        $this->fhirServiceRequestService->setSystemLogger(new SystemLogger(Level::Critical));
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removeServiceRequestFixtures();
        $this->fixtureManager->removePatientFixtures();
    }

    #[Test]
    public function testInsertCreatesOrderAndCodes(): void
    {
        $this->fhirServiceRequestFixture->setId(null);
        $result = $this->fhirServiceRequestService->insert($this->fhirServiceRequestFixture);
        $this->assertTrue(
            $result->isValid(),
            'Insert should succeed: ' . json_encode($result->getValidationMessages())
        );

        $data = $result->getData()[0];
        $this->assertArrayHasKey('uuid', $data);
        $this->assertIsString($data['uuid']);
        $this->assertGreaterThan(0, $data['procedure_order_id']);

        $codeCount = QueryUtils::fetchSingleValue(
            "SELECT COUNT(*) AS c FROM procedure_order_code WHERE procedure_order_id = ?",
            'c',
            [$data['procedure_order_id']]
        );
        $this->assertSame(1, (int) $codeCount);
    }

    #[Test]
    public function testInsertWithUnresolvableSubject(): void
    {
        $bogusUuid = UuidRegistry::uuidToString(
            (new UuidRegistry(['table_name' => 'patient_data']))->createUuid()
        );
        $this->fhirServiceRequestFixture->setId(null);
        $payload = $this->fhirServiceRequestFixture->jsonSerialize();
        $payload['subject'] = ['reference' => 'Patient/' . $bogusUuid];
        $fixture = new FHIRServiceRequest($payload);

        $result = $this->fhirServiceRequestService->insert($fixture);
        $this->assertFalse($result->isValid());
        $this->assertEquals(0, count($result->getData()));
    }

    #[Test]
    public function testUpdateReplacesCodes(): void
    {
        $this->fhirServiceRequestFixture->setId(null);
        $insertResult = $this->fhirServiceRequestService->insert($this->fhirServiceRequestFixture);
        $this->assertTrue(
            $insertResult->isValid(),
            'Insert should succeed: ' . json_encode($insertResult->getValidationMessages())
        );
        $fhirId = $insertResult->getData()[0]['uuid'];
        $orderId = $insertResult->getData()[0]['procedure_order_id'];

        // Update with TWO codings instead of one
        $payload = $this->fhirServiceRequestFixture->jsonSerialize();
        $payload['id'] = $fhirId;
        $payload['code']['coding'] = [
            [
                'system' => 'http://loinc.org',
                'code' => '5778-6',
                'display' => 'test-fixture-updated Glucose [Mass/volume] in Urine by Test strip',
            ],
            [
                'system' => 'http://loinc.org',
                'code' => '2345-7',
                'display' => 'test-fixture-updated Glucose [Mass/volume] in Serum or Plasma',
            ],
        ];
        $updated = new FHIRServiceRequest($payload);

        $result = $this->fhirServiceRequestService->update($fhirId, $updated);
        $this->assertTrue(
            $result->isValid(),
            'Update should succeed: ' . json_encode($result->getValidationMessages())
        );

        $codeCount = QueryUtils::fetchSingleValue(
            "SELECT COUNT(*) AS c FROM procedure_order_code WHERE procedure_order_id = ?",
            'c',
            [$orderId]
        );
        $this->assertSame(2, (int) $codeCount);
    }

    #[Test]
    public function testUpdateWithBadUuid(): void
    {
        $result = $this->fhirServiceRequestService->update('bad-uuid', $this->fhirServiceRequestFixture);
        $this->assertFalse($result->isValid());
        $this->assertEquals(0, count($result->getData()));
    }
}
