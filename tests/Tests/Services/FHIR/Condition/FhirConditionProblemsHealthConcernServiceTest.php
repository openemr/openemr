<?php

/*
 * FhirConditionProblemsHealthConcernServiceTest.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services\FHIR\Condition;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCondition;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical;
use OpenEMR\Services\FHIR\Condition\FhirConditionEncounterDiagnosisService;
use OpenEMR\Services\FHIR\Condition\FhirConditionProblemListItemService;
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
use OpenEMR\Services\FHIR\UtilsService;
use PHPUnit\Framework\TestCase;

class FhirConditionProblemsHealthConcernServiceTest extends TestCase
{
    private function getDefaultOpenEMRRecord()
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
                'Z59.00' => [
                    'code' => 'Z59.00',
                    'system' => FhirCodeSystemConstants::HL7_ICD10,
                    'description' => 'Homelessness, unspecified'
                ]
            ]
            ,'patient_id' => 1
            ,'user' => 'admin'
            ,'puuid' => 'patient-uuid-12345'
            ,'condition_uuid' => 'condition-uuid-12345'
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
        $conditionService = new FhirConditionProblemListItemService();
        $fhirResource = $conditionService->parseOpenEMRRecord($record);
        $this->assertInstanceOf(FHIRCondition::class, $fhirResource, "Expected FHIRCondition instance from parseOpenEMRRecord.");

        $this->assertEquals('condition-uuid-12345', $fhirResource->getId(), "Expected FHIRCondition ID to match OpenEMR condition UUID.");
        $profileValues = $fhirResource->getMeta()->getProfile();

        // US Core 6.1 Profile required items
        $this->assertContains(FhirConditionProblemListItemService::USCDI_PROFILE . "|6.1.0", $profileValues, "Expected FHIRCondition to have US Core 6.1 profile.");

        // clinicalStatus
        $this->assertEquals('active', $fhirResource->getClinicalStatus()->getCoding()[0]->getCode(), "Expected clinicalStatus to be 'active'.");

        // verificationStatus
        $this->assertEquals('unconfirmed', $fhirResource->getVerificationStatus()->getCoding()[0]->getCode(), "Expected verificationStatus to be 'confirmed'.");

        // category:us-core
        $this->assertEquals('problem-list-item', $fhirResource->getCategory()[0]->getCoding()[0]->getCode(), "Expected category code to be 'problem-list-item'.");
        $this->assertEquals(FhirCodeSystemConstants::HL7_CONDITION_CATEGORY, $fhirResource->getCategory()[0]->getCoding()[0]->getSystem(), "Expected category system to be 'http://hl7.org/fhir/us/core/ValueSet/us-core-problem-or-health-concern'.");
        $this->assertEquals('Problem List Item', $fhirResource->getCategory()[0]->getCoding()[0]->getDisplay(), "Expected category display to be 'Problem List Item'.");

        // for now we don't deal with the screening assessment until we handle the sdoh pieces
//        // category:screening-assessment
//        $this->assertEquals('sdoh', $fhirResource->getCategory()[1]->getCoding()[0]->getCode(), "Expected second category code to be 'sdoh' for category.");
//        $this->assertEquals('http://hl7.org/fhir/us/core/ValueSet/us-core-screening-assessment-condition-category', $fhirResource->getCategory()[1]->getSystem(), "Expected second category system to be 'http://hl7.org/fhir/us/core/ValueSet/us-core-screening-assessment-condition-category'.");

        // code
        $this->assertEquals('Z59.00', $fhirResource->getCode()->getCoding()[0]->getCode(), "Expected code to match OpenEMR health problem for SDOH condition homelessness.");
        $this->assertEquals(FhirCodeSystemConstants::HL7_ICD10, $fhirResource->getCode()->getCoding()[0]->getSystem(), "Expected code system to be 'ICD10'.");
        $this->assertEquals('Homelessness, unspecified', $fhirResource->getCode()->getCoding()[0]->getDisplay(), "Expected code display to be 'Homelessness, unspecified'.");

        // subject
        $this->assertNotEmpty($fhirResource->getSubject(), "Expected subject to be set in FHIRCondition resource.");
        $this->assertEquals('Patient/patient-uuid-12345', $fhirResource->getSubject()->getReference(), "Expected subject reference to match OpenEMR patient UUID.");

        // encounter
        $this->assertEmpty($fhirResource->getEncounter(), "Encounter should NOT be specified for this condition.");

        // onset[x] -> onsetDateTime
        $this->assertNotEmpty($fhirResource->getOnsetDateTime(), "Expected onsetDateTime to be set in FHIRCondition resource.");
        $this->assertEquals(UtilsService::getLocalDateAsUTC($record['begdate']), $fhirResource->getOnsetDateTime(), "Expected onsetDateTime to match OpenEMR begdate.");

        // abatement[x] -> abatementDateTime
        // when the condition is resolved, abatementDateTime should be set
        $this->assertNotEmpty($fhirResource->getAbatementDateTime(), "Expected abatementDateTime to be set in FHIRCondition resource.");
        $this->assertEquals(UtilsService::getLocalDateAsUTC($record['enddate']), $fhirResource->getAbatementDateTime(), "Expected abatementDateTime to match OpenEMR endate.");

        //  Date record was first recorded
        $this->assertNotEmpty($fhirResource->getRecordedDate(), "Expected recordedDate to be set in FHIRCondition resource.");
        $this->assertEquals(UtilsService::getLocalDateAsUTC($record['date']), $fhirResource->getRecordedDate(), "Expected recordedDate to match OpenEMR date.");

        // US Core 7.0 Profile required items
        $this->assertContains(FhirConditionProblemListItemService::USCDI_PROFILE . "|7.0.0", $profileValues, "Expected FHIRCondition to have US Core 7.0.0 profile.");
        $this->assertEquals(UtilsService::getLocalDateAsUTC($record['last_updated_time']), $fhirResource->getMeta()->getLastUpdated(), "Expected lastUpdated to match OpenEMR last updated time.");

        // Note US Core 7.0 profile added in assertedDate as an extension
        // check that assertedDate is there (only valid for 7.0 profile) -  Date the condition was first asserted
        $assertedDateExtension = $fhirResource->getExtension()[0];
        $this->assertEquals('http://hl7.org/fhir/StructureDefinition/condition-assertedDate', $assertedDateExtension->getUrl(), "Expected assertedDate extension URL to match FHIR definition.");
        $this->assertEquals(UtilsService::getLocalDateAsUTC($record['date']), $assertedDateExtension->getValueDateTime(), "Expected assertedDate to match OpenEMR date.");

        // US Core 8.0 Profile required items (none specific to this condition, but profile should be present)
        $this->assertContains(FhirConditionProblemListItemService::USCDI_PROFILE . "|8.0.0", $profileValues, "Expected FHIRCondition to have US Core 8.0.0 profile.");
    }
}
