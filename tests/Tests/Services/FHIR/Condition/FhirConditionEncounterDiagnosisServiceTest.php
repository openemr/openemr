<?php

/*
 * FhirConditionEncounterDiagnosisServiceTest.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\Services\FHIR\Condition;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCondition;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical;
use OpenEMR\Services\ConditionService;
use OpenEMR\Services\FHIR\Condition\FhirConditionEncounterDiagnosisService;
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
use OpenEMR\Services\FHIR\UtilsService;
use PHPUnit\Framework\TestCase;

class FhirConditionEncounterDiagnosisServiceTest extends TestCase
{
    private function getDefaultOpenEMRRecord(): array
    {
        return [
            'id' => '12345'
            ,'uuid' => 'condition-uuid-12345'
            ,'date' => '2025-01-01 01:00:00'
            ,'type' => 'medical_problem'
            ,'title' => 'Test Condition'
            ,'begdate' => '2025-01-01 02:00:00'
            ,'enddate' => date('Y-m-d H:i:s', strtotime("+1 HOUR"))
            ,'diagnosis' => [
                'A01.1' => [
                    'code' => 'A01.1',
                    'system' => FhirCodeSystemConstants::HL7_ICD10,
                    'description' => 'Typhoid meningitis'
                ]
            ]
            ,'patient_id' => 1
            ,'user' => 'admin'
            ,'puuid' => 'patient-uuid-12345'
            ,'condition_uuid' => 'condition-uuid-12345'
            ,'encounter_uuid' => 'encounter-uuid-12345'
            ,'last_updated_time' => '2025-01-02 12:00:00'
            ,'verification_title' => ''
            ,'creator_npi' => '1234567890'
            ,'creator_uuid' => 'provider-uuid-12345'
            ,'resolved' => 0
            ,'outcome' => 0
            ,'occurrence' => 0
        ];
    }
    public function testParseOpenEMRRecord(): void
    {
        $record = $this->getDefaultOpenEMRRecord();
        $diagnosticService = new FhirConditionEncounterDiagnosisService();
        $fhirResource = $diagnosticService->parseOpenEMRRecord($record);
        $this->assertConditionHasBaseRequirements($record, $fhirResource, 'active');
    }

    public function testParseWithResolvedRecord(): void
    {
        $record = $this->getDefaultOpenEMRRecord();
        $record['resolved'] = 1;
        $diagnosticService = new FhirConditionEncounterDiagnosisService();
        $fhirResource = $diagnosticService->parseOpenEMRRecord($record);
        $this->assertConditionHasBaseRequirements($record, $fhirResource, 'resolved');
        // abatement[x] -> abatementDateTime
        // when the condition is resolved, abatementDateTime should be set
        $this->assertNotEmpty($fhirResource->getAbatementDateTime(), "Expected abatementDateTime to be set in FHIRCondition resource.");
        $this->assertEquals(UtilsService::getLocalDateAsUTC($record['enddate']), $fhirResource->getAbatementDateTime(), "Expected abatementDateTime to match OpenEMR endate.");
    }

    protected function assertConditionHasBaseRequirements($record, $fhirResource, string $clinicalStatus): void
    {
        $this->assertInstanceOf(FHIRCondition::class, $fhirResource, "Expected FHIRCondition instance from parseOpenEMRRecord.");

        $this->assertEquals('condition-uuid-12345', $fhirResource->getId(), "Expected FHIRCondition ID to match OpenEMR condition UUID.");
        $profileValues = $fhirResource->getMeta()->getProfile();

        // US Core 6.1 Profile required items
        $this->assertContains("http://hl7.org/fhir/us/core/StructureDefinition/us-core-condition-encounter-diagnosis|6.1.0", $profileValues, "Expected FHIRCondition to have US Core 6.1 profile.");

        // clinicalStatus
        $this->assertEquals($clinicalStatus, $fhirResource->getClinicalStatus()->getCoding()[0]->getCode(), "Expected clinicalStatus to be '$clinicalStatus'.");

        // verificationStatus
        $this->assertEquals('confirmed', $fhirResource->getVerificationStatus()->getCoding()[0]->getCode(), "Expected verificationStatus to be 'confirmed'.");

        // category:us-core
        $this->assertEquals('encounter-diagnosis', $fhirResource->getCategory()[0]->getCoding()[0]->getCode(), "Expected category code to be 'encounter-diagnosis'.");
        $this->assertEquals('http://terminology.hl7.org/CodeSystem/condition-category', $fhirResource->getCategory()[0]->getCoding()[0]->getSystem(), "Expected category system to be 'http://terminology.hl7.org/CodeSystem/condition-category'.");
        $this->assertEquals('Encounter Diagnosis', $fhirResource->getCategory()[0]->getCoding()[0]->getDisplay(), "Expected category display to be 'Encounter Diagnosis'.");

        // code
        $this->assertNotEmpty($fhirResource->getCode(), "Expected code to be set in FHIRCondition resource.");
        $this->assertNotEmpty($fhirResource->getCode()->getCoding(), "Expected code.coding to be set in FHIRCondition resource.");
        $this->assertEquals('A01.1', $fhirResource->getCode()->getCoding()[0]->getCode(), "Expected code to match OpenEMR diagnosis.");
        $this->assertEquals(FhirCodeSystemConstants::HL7_ICD10, $fhirResource->getCode()->getCoding()[0]->getSystem(), "Expected code system to be 'ICD10'.");

        // subject
        $this->assertNotEmpty($fhirResource->getSubject(), "Expected subject to be set in FHIRCondition resource.");
        $this->assertEquals('Patient/patient-uuid-12345', $fhirResource->getSubject()->getReference(), "Expected subject reference to match OpenEMR patient UUID.");

        // encounter
        $this->assertNotEmpty($fhirResource->getEncounter(), "Expected encounter to be set in FHIRCondition resource.");
        $this->assertEquals('Encounter/encounter-uuid-12345', $fhirResource->getEncounter()->getReference(), "Expected encounter reference to match OpenEMR encounter UUID.");

        // onset[x] -> onsetDateTime
        $this->assertNotEmpty($fhirResource->getOnsetDateTime(), "Expected onsetDateTime to be set in FHIRCondition resource.");
        $this->assertEquals(UtilsService::getLocalDateAsUTC($record['begdate']), $fhirResource->getOnsetDateTime(), "Expected onsetDateTime to match OpenEMR begdate.");


        //  Date record was first recorded
        $this->assertNotEmpty($fhirResource->getRecordedDate(), "Expected recordedDate to be set in FHIRCondition resource.");
        $this->assertEquals(UtilsService::getLocalDateAsUTC($record['date']), $fhirResource->getRecordedDate(), "Expected recordedDate to match OpenEMR date.");

        // US Core 7.0 Profile required items
        $this->assertContains("http://hl7.org/fhir/us/core/StructureDefinition/us-core-condition-encounter-diagnosis|7.0.0", $profileValues, "Expected FHIRCondition to have US Core 7.0.0 profile.");

        // Note US Core 7.0 profile added in assertedDate as an extension
        // check that assertedDate is there (only valid for 7.0 profile) -  Date the condition was first asserted
        $this->assertCount(1, $fhirResource->getExtension(), "Expected assertedDate extension to be present.");
        $assertedDateExtension = $fhirResource->getExtension()[0];
        $this->assertEquals('http://hl7.org/fhir/StructureDefinition/condition-assertedDate', $assertedDateExtension->getUrl(), "Expected assertedDate extension URL to match FHIR definition.");
        $this->assertEquals(UtilsService::getLocalDateAsUTC($record['date']), $assertedDateExtension->getValueDateTime(), "Expected assertedDate to match OpenEMR date.");

        // US Core 8.0 Profile required items
        $this->assertContains("http://hl7.org/fhir/us/core/StructureDefinition/us-core-condition-encounter-diagnosis|8.0.0", $profileValues, "Expected FHIRCondition to have US Core 8.0.0 profile.");

        // note US Core 8.0 profile took out assertedDate as a required field
        //  Who recorded the condition
        $this->assertNotEmpty($fhirResource->getRecorder(), "Expected recorder to be set in FHIRCondition resource.");
        $this->assertEquals('Practitioner/provider-uuid-12345', $fhirResource->getRecorder()->getReference(), "Expected recorder reference to match OpenEMR provider UUID.");
    }
}
