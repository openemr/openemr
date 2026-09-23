<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Services\FHIR;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIREncounter;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRObservation;
use OpenEMR\Services\FHIR\FhirEncounterService;
use OpenEMR\Services\FHIR\FhirObservationService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use OpenEMR\Validators\ProcessingResult;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * FHIR Observation (vital signs) Service CRUD Tests
 *
 * Vitals are the one Observation category that is N resources to one row: every vital
 * sign taken at one moment shares a single form_vitals record, and each gets its own
 * FHIR id through uuid_mapping. These tests pin that down -- several Observations
 * collapsing onto one row, a partial update leaving its siblings alone, and the
 * categories that have no writable store being rejected rather than dropped.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FhirObservationVitalsServiceCrudTest extends TestCase
{
    private FixtureManager $fixtureManager;
    private FhirObservationService $fhirObservationService;
    private string $patientUuid;
    private string $encounterUuid;
    private int $pid;

    protected function setUp(): void
    {
        $this->fixtureManager = new FixtureManager();
        $this->fixtureManager->installPatientFixtures();

        $patientFixture = $this->fixtureManager->getPatientFixtures()[0];
        $this->assertIsArray($patientFixture);
        $patientRecord = QueryUtils::querySingleRow(
            "SELECT uuid, pid FROM patient_data WHERE pubpid = ?",
            [$patientFixture['pubpid']]
        );
        $this->assertIsArray($patientRecord);
        $this->patientUuid = UuidRegistry::uuidToString($patientRecord['uuid']);
        $this->pid = (int) $this->floatValue($patientRecord['pid']);

        // form_vitals rows are encounter forms, so a write needs a real encounter to hang
        // the form off of.
        $raw = file_get_contents(__DIR__ . '/../../Fixtures/FHIR/encounter.json');
        $this->assertIsString($raw);
        $encounterRaw = json_decode($raw, true);
        $this->assertIsArray($encounterRaw);
        $encounterPayload = $encounterRaw[0];
        $this->assertIsArray($encounterPayload);
        $encounterPayload['subject'] = ['reference' => 'Patient/' . $this->patientUuid];

        $encounterService = new FhirEncounterService();
        $encounterService->setLogger($this->createMock(LoggerInterface::class));
        $encounterInsert = $encounterService->insert(new FHIREncounter($encounterPayload));
        $this->assertTrue(
            $encounterInsert->isValid(),
            'Encounter insert (setup) failed: ' . json_encode($encounterInsert->getValidationMessages())
        );
        $encounterUuid = $this->firstDataRow($encounterInsert)['euuid'];
        $this->assertIsString($encounterUuid);
        $this->encounterUuid = $encounterUuid;

        $this->fhirObservationService = new FhirObservationService();
        $this->fhirObservationService->setLogger($this->createMock(LoggerInterface::class));
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removeObservationFixtures();
        // This run's encounter only -- a LIKE sweep would also delete the encounters of any
        // other worker running this suite and fail them with rows that vanished mid-test.
        if (isset($this->encounterUuid)) {
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM form_encounter WHERE uuid = ?",
                [UuidRegistry::uuidToBytes($this->encounterUuid)]
            );
        }
        $this->fixtureManager->removePatientFixtures();
    }

    /**
     * Narrowing helpers. The service layer hands back mixed, and PHPStan runs at level 10
     * here, so each read is funnelled through an assertion that both fails the test and
     * narrows the type.
     *
     * @return array<array-key, mixed>
     */
    private function arrayValue(mixed $value): array
    {
        if (!is_array($value)) {
            $this->fail('expected an array, got ' . get_debug_type($value));
        }

        return $value;
    }

    private function stringValue(mixed $value): string
    {
        if (!is_string($value)) {
            $this->fail('expected a string, got ' . get_debug_type($value));
        }

        return $value;
    }

    private function floatValue(mixed $value): float
    {
        if (!is_numeric($value)) {
            $this->fail('expected a numeric value, got ' . get_debug_type($value));
        }

        return (float) $value;
    }

    /**
     * @return array<string, mixed>
     */
    private function firstDataRow(ProcessingResult $result): array
    {
        $data = $this->arrayValue($result->getData());
        $this->assertNotEmpty($data, 'expected the processing result to carry a record');
        $row = $this->arrayValue($data[0] ?? null);
        $stringKeyed = [];
        foreach ($row as $key => $value) {
            $stringKeyed[(string) $key] = $value;
        }

        return $stringKeyed;
    }

    /**
     * Builds one of the observation.json fixtures bound to this test's patient/encounter.
     *
     * @return array<string, mixed>
     */
    private function observationPayload(string $loincCode, string $effectiveDateTime = '2026-03-04T09:30:00-05:00'): array
    {
        $fixtures = $this->fixtureManager->getFhirObservationFixtures();
        foreach ($fixtures as $fixture) {
            $coding = $this->arrayValue($this->arrayValue($fixture['code'] ?? null)['coding'] ?? null);
            if (($this->arrayValue($coding[0] ?? null)['code'] ?? null) !== $loincCode) {
                continue;
            }
            $fixture['subject'] = ['reference' => 'Patient/' . $this->patientUuid];
            $fixture['encounter'] = ['reference' => 'Encounter/' . $this->encounterUuid];
            $fixture['effectiveDateTime'] = $effectiveDateTime;

            return $fixture;
        }

        $this->fail('no observation fixture for LOINC code ' . $loincCode);
    }

    private function insertObservation(string $loincCode, string $effectiveDateTime = '2026-03-04T09:30:00-05:00'): string
    {
        $result = $this->fhirObservationService->insert(
            new FHIRObservation($this->observationPayload($loincCode, $effectiveDateTime))
        );
        $this->assertTrue(
            $result->isValid(),
            'Observation insert failed for ' . $loincCode . ': ' . json_encode($result->getValidationMessages())
        );
        return $this->stringValue($this->firstDataRow($result)['uuid'] ?? null);
    }

    private function countVitalsRows(): int
    {
        return (int) $this->floatValue(QueryUtils::fetchSingleValue(
            "SELECT COUNT(*) AS total FROM form_vitals WHERE pid = ?",
            'total',
            [$this->pid]
        ));
    }

    #[Test]
    public function testMultipleVitalsForOneReadingCollapseOntoASingleRow(): void
    {
        $weightUuid = $this->insertObservation('29463-7');
        $pulseUuid = $this->insertObservation('8867-4');
        $bpUuid = $this->insertObservation('85354-9');

        $this->assertSame(1, $this->countVitalsRows(), 'three vitals for one reading should share one form_vitals row');

        // Each vital still gets its own FHIR id, which is what the read side resolves.
        $this->assertNotSame($weightUuid, $pulseUuid);
        $this->assertNotSame($pulseUuid, $bpUuid);

        $row = $this->arrayValue(QueryUtils::querySingleRow(
            "SELECT weight, pulse, bps, bpd FROM form_vitals WHERE pid = ?",
            [$this->pid]
        ));
        // 70 kg stored as pounds: form_vitals is a USA-units table and VitalsService converts
        // on the way out, so the write has to convert on the way in.
        $this->assertEqualsWithDelta(154.3236, $this->floatValue($row['weight'] ?? null), 0.001);
        $this->assertEqualsWithDelta(72, $this->floatValue($row['pulse'] ?? null), 0.001);
        $this->assertEqualsWithDelta(120, $this->floatValue($row['bps'] ?? null), 0.001);
        $this->assertEqualsWithDelta(80, $this->floatValue($row['bpd'] ?? null), 0.001);
    }

    #[Test]
    public function testAWrittenVitalSignReadsBackThroughItsOwnId(): void
    {
        $pulseUuid = $this->insertObservation('8867-4');

        $readResult = $this->fhirObservationService->getOne($pulseUuid);
        $this->assertTrue($readResult->isValid());
        $resource = $this->arrayValue($readResult->getData())[0] ?? null;
        $this->assertInstanceOf(FHIRObservation::class, $resource);

        // jsonSerialize() hands back the FHIR element objects, not plain arrays, so the
        // resource is round-tripped through JSON to read it the way a client would.
        $json = $this->arrayValue(json_decode((string) json_encode($resource), true));
        $this->assertSame($pulseUuid, $this->stringValue($json['id'] ?? null));
        $coding = $this->arrayValue($this->arrayValue($json['code'] ?? null)['coding'] ?? null);
        $this->assertSame('8867-4', $this->arrayValue($coding[0] ?? null)['code'] ?? null);
        $this->assertEqualsWithDelta(
            72,
            $this->floatValue($this->arrayValue($json['valueQuantity'] ?? null)['value'] ?? null),
            0.001
        );
    }

    #[Test]
    public function testAReadingAtADifferentTimeStartsItsOwnRow(): void
    {
        $this->insertObservation('8867-4', '2026-03-04T09:30:00-05:00');
        $this->insertObservation('8867-4', '2026-03-04T14:00:00-05:00');

        // Same encounter, different reading: a triage pulse and a reassessment pulse are
        // two readings, and collapsing them would overwrite the first.
        $this->assertSame(2, $this->countVitalsRows());
    }

    #[Test]
    public function testUpdatingOneVitalSignLeavesItsSiblingsAlone(): void
    {
        $this->insertObservation('29463-7');
        $pulseUuid = $this->insertObservation('8867-4');

        $payload = $this->observationPayload('8867-4');
        $payload['id'] = $pulseUuid;
        $quantity = $this->arrayValue($payload['valueQuantity'] ?? null);
        $quantity['value'] = 88;
        $payload['valueQuantity'] = $quantity;

        $updateResult = $this->fhirObservationService->update($pulseUuid, new FHIRObservation($payload));
        $this->assertTrue(
            $updateResult->isValid(),
            'Observation update failed: ' . json_encode($updateResult->getValidationMessages())
        );

        $row = $this->arrayValue(QueryUtils::querySingleRow(
            "SELECT weight, pulse FROM form_vitals WHERE pid = ?",
            [$this->pid]
        ));
        $this->assertEqualsWithDelta(88, $this->floatValue($row['pulse'] ?? null), 0.001);
        $this->assertEqualsWithDelta(
            154.3236,
            $this->floatValue($row['weight'] ?? null),
            0.001,
            'the sibling weight must survive the update'
        );
        $this->assertSame(1, $this->countVitalsRows());
    }

    #[Test]
    public function testUpdateRejectsACodeThatDoesNotMatchTheId(): void
    {
        $pulseUuid = $this->insertObservation('8867-4');

        // The id names one code on one row; letting the code change would move the value
        // into a different column while keeping the same id.
        $payload = $this->observationPayload('29463-7');
        $payload['id'] = $pulseUuid;

        $result = $this->fhirObservationService->update($pulseUuid, new FHIRObservation($payload));
        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('code', $this->arrayValue($result->getValidationMessages()));
    }

    #[Test]
    public function testUpdateRejectsAnIdBelongingToAnotherPatient(): void
    {
        $pulseUuid = $this->insertObservation('8867-4');

        $otherPatient = $this->fixtureManager->getPatientFixtures()[1] ?? null;
        $this->assertIsArray($otherPatient, 'a second patient fixture is required for this test');
        $otherRecord = QueryUtils::querySingleRow(
            "SELECT uuid FROM patient_data WHERE pubpid = ?",
            [$otherPatient['pubpid']]
        );
        $this->assertIsArray($otherRecord);

        $payload = $this->observationPayload('8867-4');
        $payload['id'] = $pulseUuid;
        $payload['subject'] = ['reference' => 'Patient/' . UuidRegistry::uuidToString($otherRecord['uuid'])];

        $result = $this->fhirObservationService->update($pulseUuid, new FHIRObservation($payload));
        $this->assertFalse($result->isValid(), 'a leaked Observation id must not write into another chart');
    }

    #[Test]
    public function testInsertRejectsAnEncounterBelongingToAnotherPatient(): void
    {
        $otherPatient = $this->fixtureManager->getPatientFixtures()[1] ?? null;
        $this->assertIsArray($otherPatient, 'a second patient fixture is required for this test');
        $otherRecord = QueryUtils::querySingleRow(
            "SELECT uuid FROM patient_data WHERE pubpid = ?",
            [$otherPatient['pubpid']]
        );
        $this->assertIsArray($otherRecord);

        $payload = $this->observationPayload('8867-4');
        $payload['subject'] = ['reference' => 'Patient/' . UuidRegistry::uuidToString($otherRecord['uuid'])];

        $result = $this->fhirObservationService->insert(new FHIRObservation($payload));
        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('encounter', $this->arrayValue($result->getValidationMessages()));
        $this->assertSame(0, $this->countVitalsRows());
    }

    #[Test]
    public function testAnObservationWhoseCategoryHasNoWritableStoreIsRejected(): void
    {
        // Smoking status is a social history observation: it is a view over the patient
        // history record, so there is nothing to write back through. It must be refused
        // rather than accepted and dropped -- the next GET would reconstruct the old value
        // from the untouched history row and the client would never know.
        $payload = $this->observationPayload('8867-4');
        $payload['code'] = [
            'coding' => [['system' => 'http://loinc.org', 'code' => '72166-2', 'display' => 'Tobacco smoking status']],
        ];
        $payload['category'] = [
            ['coding' => [[
                'system' => 'http://terminology.hl7.org/CodeSystem/observation-category',
                'code' => 'social-history',
            ]]],
        ];
        unset($payload['valueQuantity']);

        $result = $this->fhirObservationService->insert(new FHIRObservation($payload));
        $this->assertFalse($result->isValid());
        $messages = $this->arrayValue($result->getValidationMessages());
        $this->assertArrayHasKey('code', $messages);
        $this->assertStringContainsString('cannot be written', $this->stringValue($messages['code'] ?? null));
        $this->assertSame(0, $this->countVitalsRows());
    }

    #[Test]
    public function testADerivedVitalSignIsRejectedRatherThanStored(): void
    {
        $payload = $this->observationPayload('8867-4');
        $payload['code'] = [
            'coding' => [['system' => 'http://loinc.org', 'code' => '39156-5', 'display' => 'Body mass index']],
        ];
        $payload['valueQuantity'] = ['value' => 22.5, 'unit' => 'kg/m2', 'code' => 'kg/m2'];

        $result = $this->fhirObservationService->insert(new FHIRObservation($payload));
        $this->assertFalse($result->isValid());
        $this->assertStringContainsString(
            'derived from height and weight',
            $this->stringValue($this->arrayValue($result->getValidationMessages())['code'] ?? null)
        );
    }
}
