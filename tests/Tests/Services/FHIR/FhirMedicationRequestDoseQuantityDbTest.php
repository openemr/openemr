<?php

/**
 * Prescriptions.size 0.5 must stay 0.5 mg through the FHIR search SQL, and
 * prescriptions.dosage 0.5 must stay 0.5 tablet through the QDM medication SQL
 * (#14703).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Subramani Nagaraj <yuvamani.mani@gmail.com>
 * @copyright Copyright (c) 2026 Subramani Nagaraj <yuvamani.mani@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\FHIR;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicationRequest;
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\FHIR\FhirMedicationRequestService;
use OpenEMR\Services\Qdm\QdmRecord;
use OpenEMR\Services\Qdm\QdmRequestSome;
use OpenEMR\Services\Qdm\Services\MedicationActiveService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class FhirMedicationRequestDoseQuantityDbTest extends TestCase
{
    private FixtureManager $fixtureManager;
    private FhirMedicationRequestService $fhirMedicationRequestService;
    private string $patientUuid;
    private int $patientId;

    /**
     * Install a fixture patient and bind the FHIR MedicationRequest service to it.
     */
    protected function setUp(): void
    {
        $this->fixtureManager = new FixtureManager();
        $this->fixtureManager->installPatientFixtures();
        $patients = $this->fixtureManager->getPatientFixtures();
        $patientFixture = $patients[0];
        $this->assertIsArray($patientFixture);
        $patientRecord = QueryUtils::querySingleRow(
            'SELECT pid, uuid FROM patient_data WHERE pubpid = ?',
            [$patientFixture['pubpid']]
        );
        $this->assertIsArray($patientRecord);
        $pid = $patientRecord['pid'] ?? null;
        $this->assertIsNumeric($pid);
        $this->patientId = (int)$pid;
        $this->patientUuid = UuidRegistry::uuidToString($patientRecord['uuid']);

        $this->fhirMedicationRequestService = new FhirMedicationRequestService();
        $this->fhirMedicationRequestService->setLogger($this->createMock(LoggerInterface::class));
    }

    /**
     * Remove the seeded prescription and fixture patient.
     */
    protected function tearDown(): void
    {
        $this->fixtureManager->removeMedicationRequestFixtures();
        $this->fixtureManager->removePatientFixtures();
    }

    /**
     * FHIR search SQL must keep prescriptions.size 0.5 as 0.5 mg, not intval() 0.
     */
    #[Test]
    public function testSearchKeepsAHalfMilligramDoseQuantity(): void
    {
        $uuid = $this->insertFractionalPrescription();
        $result = $this->fhirMedicationRequestService->getOne($uuid, $this->patientUuid);
        $this->assertTrue($result->isValid(), (string)json_encode($result->getValidationMessages()));
        $data = $result->getData();
        $this->assertIsArray($data);
        $this->assertArrayHasKey(0, $data);
        $resource = $data[0];
        $this->assertInstanceOf(FHIRMedicationRequest::class, $resource);
        $instructions = $resource->getDosageInstruction();
        $this->assertNotEmpty($instructions);
        $rates = $instructions[0]->getDoseAndRate();
        $this->assertNotEmpty($rates);
        $json = json_decode((string)json_encode($rates[0]->getDoseQuantity()), true);
        $this->assertIsArray($json);
        $this->assertEquals(0.5, $json['value'] ?? null);
        $this->assertNotEquals(0, $json['value'] ?? null, 'intval() would have sent 0 mg');
        $this->assertSame('mg', $json['unit'] ?? null);
    }

    /**
     * QDM SQL must keep prescriptions.dosage 0.5 as 0.5 tablet, not (int) 0 mg.
     */
    #[Test]
    public function testQdmSqlKeepsAHalfTabletWithTheFormUnit(): void
    {
        $this->insertFractionalPrescription();
        $service = new MedicationActiveService(new QdmRequestSome([$this->patientId]), new CodeTypesService());
        $row = QueryUtils::querySingleRow(
            $service->getSqlStatement() . ' WHERE P.patient_id = ?',
            [$this->patientId]
        );
        $this->assertIsArray($row);
        $this->assertSame('tablet', $row['drug_form']);
        $this->assertSame('mg', $row['drug_unit']);
        $model = $service->makeQdmModel(new QdmRecord($row, $this->patientId, 1));
        $json = json_decode(json_encode($model, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($json);
        $dosage = $json['dosage'] ?? null;
        $this->assertIsArray($dosage);
        $this->assertEquals(0.5, $dosage['value'] ?? null);
        $this->assertNotEquals(0, $dosage['value'] ?? null, '(int) would have sent 0');
        $this->assertSame('tablet', $dosage['unit']);
        $this->assertNotSame('mg', $dosage['unit']);
    }

    /**
     * Seed a 0.5 mg / 0.5 tablet prescription for the fixture patient.
     */
    private function insertFractionalPrescription(): string
    {
        $uuid = UuidRegistry::getRegistryForTable('prescriptions')->createUuid();
        QueryUtils::sqlInsert(
            'INSERT INTO prescriptions (
                uuid, patient_id, drug, rxnorm_drugcode, active, date_added, start_date, txDate,
                dosage, size, unit, form, usage_category_title, request_intent_title,
                drug_dosage_instructions
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $uuid,
                $this->patientId,
                'Levothyroxine 0.5 MG Oral Tablet',
                '966247',
                1,
                '2024-01-15 10:30:00',
                '2024-01-15',
                '2024-01-15',
                '0.5',
                '0.5',
                1,
                2,
                'Home/Community',
                'Order',
                'Take as directed',
            ]
        );

        return UuidRegistry::uuidToString($uuid);
    }
}
