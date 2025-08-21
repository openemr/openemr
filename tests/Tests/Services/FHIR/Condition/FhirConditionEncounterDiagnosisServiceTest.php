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
use PHPUnit\Framework\TestCase;

class FhirConditionEncounterDiagnosisServiceTest extends TestCase {

    private function getDefaultOpenEMRRecord() {
        return [
            'id' => '12345'
            ,'uuid' => 'condition-uuid-12345'
            ,'date' => '2025-01-01T01:00:00Z'
            ,'type' => 'medical_problem'
            ,'title' => 'Test Condition'
            ,'begdate' => '2025-01-01T02:00:00Z'
            ,'endate' => '2025-01-02T00:00:00Z'
            ,'diagnosis' => 'ICD10:A01.1'
            ,'patient_id' => 1
            ,'user' => 'admin'
            ,'puuid' => 'patient-uuid-12345'
            ,'condition_uuid' => 'condition-uuid-12345'
            ,'last_updated_time' => '2025-01-02T30:00:00Z'
            ,'verification_title' => ''
            ,'provider_id' => 1
            ,'provider_npi' => '1234567890'
            ,'provider_uuid' => 'provider-uuid-12345'
            ,'provider_username' => 'admin'
        ];
    }
    public function testParseOpenEMRRecord()
    {
        $record = $this->getDefaultOpenEMRRecord();
        $diagnosticService = new FhirConditionEncounterDiagnosisService();
        $fhirResource = $diagnosticService->parseOpenEMRRecord($record);
        $this->assertInstanceOf(FHIRCondition::class, $fhirResource, "Expected FHIRCondition instance from parseOpenEMRRecord.");

        $this->assertEquals('condition-uuid-12345', $fhirResource->getId(), "Expected FHIRCondition ID to match OpenEMR condition UUID.");
        $profiles = $fhirResource->getMeta()->getProfile();
        $profileValues = array_map(fn(FHIRCanonical $canonical) => $canonical->getValue(), $profiles);

        // US Core 6.1 Profile required items
        $this->assertContains("http://hl7.org/fhir/us/core/StructureDefinition/us-core-condition-encounter-diagnosis|6.1.0", $profileValues, "Expected FHIRCondition to have US Core 6.1 profile.");

        // clinicalStatus
        $this->assertEquals('active', $fhirResource->getClinicalStatus()->getCoding()[0]->getCode()->getValue(), "Expected clinicalStatus to be 'active'.");

        // verificationStatus
        $this->assertEquals('confirmed', $fhirResource->getVerificationStatus()->getCoding()[0]->getCode()->getValue(), "Expected verificationStatus to be 'confirmed'.");

        // category:us-core
        $this->assertEquals('encounter-diagnosis', $fhirResource->getCategory()[0]->getCode(), "Expected category code to be 'encounter-diagnosis'.");
        $this->assertEquals('http://terminology.hl7.org/CodeSystem/condition-category', $fhirResource->getCategory()[0]->getSystem(), "Expected category system to be 'http://terminology.hl7.org/CodeSystem/condition-category'.");
        $this->assertEquals('Encounter Diagnosis', $fhirResource->getCategory()[0]->getDisplay(), "Expected category display to be 'Encounter Diagnosis'.");

        // code
        $this->assertEquals('A01.1', $fhirResource->getCode()->getCoding()[0]->getCode(), "Expected code to match OpenEMR diagnosis.");
        $this->assertEquals(FhirCodeSystemConstants::HL7_ICD10, $fhirResource->getCode()->getCoding()[0]->getSystem()->getValue(), "Expected code system to be 'ICD10'.");

        // subject
        $this->assertNotEmpty($fhirResource->getSubject(), "Expected subject to be set in FHIRCondition resource.");
        $this->assertEquals('Patient/patient-uuid-12345', $fhirResource->getSubject()->getReference()->getValue(), "Expected subject reference to match OpenEMR patient UUID.");

        // encounter
        $this->assertNotEmpty($fhirResource->getEncounter(), "Expected encounter to be set in FHIRCondition resource.");
        $this->assertEquals('Encounter/encounter-uuid-12345', $fhirResource->getEncounter()->getReference()->getValue(), "Expected encounter reference to match OpenEMR encounter UUID.");

        // onset[x] -> onsetDateTime
        $this->assertNotEmpty($fhirResource->getOnsetDateTime(), "Expected onsetDateTime to be set in FHIRCondition resource.");
        $this->assertEquals('2025-01-01T02:00:00Z', $fhirResource->getOnsetDateTime()->getValue(), "Expected onsetDateTime to match OpenEMR begdate.");

        // abatement[x] -> abatementDateTime
        // when the condition is resolved, abatementDateTime should be set
        $this->assertNotEmpty($fhirResource->getAbatementDateTime(), "Expected abatementDateTime to be set in FHIRCondition resource.");
        $this->assertEquals('2025-01-02T00:00:00Z', $fhirResource->getAbatementDateTime()->getValue(), "Expected abatementDateTime to match OpenEMR endate.");

        // 	Date record was first recorded
        $this->assertNotEmpty($fhirResource->getRecordedDate(), "Expected recordedDate to be set in FHIRCondition resource.");
        $this->assertEquals('2025-01-01T01:00:00Z', $fhirResource->getRecordedDate()->getValue(), "Expected recordedDate to match OpenEMR date.");

        // US Core 7.0 Profile required items
        $this->assertContains("http://hl7.org/fhir/us/core/StructureDefinition/us-core-condition-encounter-diagnosis|7.0.0", $profileValues, "Expected FHIRCondition to have US Core 7.0.0 profile.");

        // Note US Core 7.0 profile added in assertedDate as an extension
        // check that assertedDate is there (only valid for 7.0 profile) - 	Date the condition was first asserted
        $this->assertCount(1, $fhirResource->getExtension(), "Expected assertedDate extension to be present.");
        $assertedDateExtension = $fhirResource->getExtension()[0];
        $this->assertEquals('http://hl7.org/fhir/StructureDefinition/condition-assertedDate', $assertedDateExtension->getUrl(), "Expected assertedDate extension URL to match FHIR definition.");
        $this->assertEquals('2025-01-01T01:00:00Z', $assertedDateExtension->getValueDateTime()->getValue(), "Expected assertedDate to match OpenEMR date.");

        // US Core 8.0 Profile required items
        $this->assertContains("http://hl7.org/fhir/us/core/StructureDefinition/us-core-condition-encounter-diagnosis|8.0.0", $profileValues, "Expected FHIRCondition to have US Core 8.0.0 profile.");

        // note US Core 8.0 profile took out assertedDate as a required field
        // 	Who recorded the condition
        $this->assertNotEmpty($fhirResource->getRecorder(), "Expected recorder to be set in FHIRCondition resource.");
        $this->assertEquals('Practitioner/provider-uuid-12345', $fhirResource->getRecorder()->getReference()->getValue(), "Expected recorder reference to match OpenEMR provider UUID.");
    }

    public function testSupportsCategory()
    {
        $this->markTestIncomplete("This test has not been implemented yet.");
    }
}
