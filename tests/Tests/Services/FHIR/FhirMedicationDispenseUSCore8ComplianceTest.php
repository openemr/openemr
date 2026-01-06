<?php

/*
 * FhirMedicationDispenseComplianceTest.php
 *
 * Tests to ensure the MedicationDispense implementation complies with
 * US Core StructureDefinition requirements and constraints
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Claude.AI Assistant
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// AI-generated content begins
namespace OpenEMR\Tests\Services\FHIR;

use OpenEMR\Services\FHIR\FhirMedicationDispenseService;
use OpenEMR\Services\FHIR\MedicationDispense\FhirMedicationDispenseLocalDispensaryService;
use OpenEMR\Services\FHIR\MedicationDispense\FhirMedicationDispenseImmunizationService;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicationDispense;
use OpenEMR\Services\FHIR\UtilsService;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Tests\Fixtures\MedicationDispenseFixtureManager;
use PHPUnit\Framework\TestCase;

class FhirMedicationDispenseUSCore8ComplianceTest extends TestCase
{
    private $medicationDispenseService;
    private $localDispensaryService;
//    private $immunizationService;
    private $fixtureManager;

    protected function setUp(): void
    {
        $this->medicationDispenseService = new FhirMedicationDispenseService();
        $this->localDispensaryService = new FhirMedicationDispenseLocalDispensaryService();
//        $this->immunizationService = new FhirMedicationDispenseImmunizationService();
        $this->fixtureManager = new MedicationDispenseFixtureManager();
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removeFixtures();
    }

    /**
     * Test US Core Profile URI is correctly set
     */
    public function testUSCoreProfileURI(): void
    {
        $profileURIs = $this->medicationDispenseService->getProfileURIs();

        $expectedURI = 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-medicationdispense';
        $this->assertContains($expectedURI, $profileURIs);
    }

    /**
     * Test all mustSupport elements are present in drug sales dispense
     */
    public function testMustSupportElementsDrugSales(): void
    {
        $testRecord = $this->fixtureManager->createDrugSaleDispense([
            'trans_type' => 1,
            'quantity' => 30,
            'sale_date' => '2024-01-15 10:30:00'
        ]);

        $fhirResource = $this->localDispensaryService->parseOpenEMRRecord($testRecord);

        // Required mustSupport elements per US Core
        $this->assertNotNull($fhirResource->getStatus()); // status (required)
        $this->assertNotNull($fhirResource->getMedicationCodeableConcept()); // medication[x] (required)
        $this->assertNotNull($fhirResource->getSubject()); // subject (required)
        $this->assertNotNull($fhirResource->getType()); // type (mustSupport)
        $this->assertNotNull($fhirResource->getQuantity()); // quantity (mustSupport)
        $this->assertNotNull($fhirResource->getWhenHandedOver()); // whenHandedOver (mustSupport)

        // authorizingPrescription (mustSupport) - should be present if prescription exists
        if ($testRecord['prescription_id']) {
            $this->assertNotEmpty($fhirResource->getAuthorizingPrescription());
        }
    }

//    /**
//     * Test all mustSupport elements are present in immunization dispense
//     */
//    public function testMustSupportElementsImmunization(): void
//    {
//        $testRecord = $this->fixtureManager->createImmunizationDispense([
//            'cvx_code' => '140',
//            'amount_administered' => 0.5,
//            'administered_date' => '2024-01-15 14:30:00'
//        ]);
//
//        $fhirResource = $this->immunizationService->parseOpenEMRRecord($testRecord);
//
//        // Required mustSupport elements per US Core
//        $this->assertNotNull($fhirResource->getStatus());
//        $this->assertNotNull($fhirResource->getMedicationCodeableConcept());
//        $this->assertNotNull($fhirResource->getSubject());
//        $this->assertNotNull($fhirResource->getType());
//        $this->assertNotNull($fhirResource->getQuantity());
//        $this->assertNotNull($fhirResource->getWhenHandedOver());
//    }

    /**
     * Test status values are from required ValueSet
     */
    public function testStatusValueSet(): void
    {
        $validStatuses = [
            'preparation', 'in-progress', 'cancelled', 'on-hold',
            'completed', 'entered-in-error', 'stopped', 'declined', 'unknown'
        ];

        $testCases = [
            ['trans_type' => 1, 'expected' => 'completed'],
            ['trans_type' => 3, 'expected' => 'entered-in-error'],
            ['trans_type' => 4, 'expected' => 'preparation']
        ];

        foreach ($testCases as $testCase) {
            $testRecord = $this->fixtureManager->createDrugSaleDispense($testCase);
            $fhirResource = $this->localDispensaryService->parseOpenEMRRecord($testRecord);

            $status = $fhirResource->getStatus();
            $this->assertContains($status, $validStatuses);
            $this->assertEquals($testCase['expected'], $status);
        }
    }

    /**
     * Test medication coding system compliance
     */
    public function testMedicationCodingSystemCompliance(): void
    {
        // Test RxNorm coding
        $testRecord = $this->fixtureManager->createDrugSaleDispense([
            'rxnorm_code' => 'RXCUI:308192',
            'drug_name' => 'Amoxicillin 500mg'
        ]);

        $fhirResource = $this->localDispensaryService->parseOpenEMRRecord($testRecord);
        $medication = $fhirResource->getMedicationCodeableConcept();

        $codings = $medication->getCoding();
        $hasRxNorm = false;

        foreach ($codings as $coding) {
            if ($coding->getSystem() === 'http://www.nlm.nih.gov/research/umls/rxnorm') {
                $hasRxNorm = true;
                $this->assertNotEmpty($coding->getCode());
                break;
            }
        }

        $this->assertTrue($hasRxNorm, 'RxNorm coding should be present when available');
    }

//    /**
//     * Test CVX coding system for immunizations
//     */
//    public function testCVXCodingSystemCompliance(): void
//    {
//        $testRecord = $this->fixtureManager->createImmunizationDispense([
//            'cvx_code' => '140'
//        ]);
//
//        $fhirResource = $this->immunizationService->parseOpenEMRRecord($testRecord);
//        $medication = $fhirResource->getMedicationCodeableConcept();
//
//        $codings = $medication->getCoding();
//        $hasCVX = false;
//
//        foreach ($codings as $coding) {
//            if ($coding->getSystem() === 'http://hl7.org/fhir/sid/cvx') {
//                $hasCVX = true;
//                $this->assertEquals('140', $coding->getCode());
//                break;
//            }
//        }
//
//        $this->assertTrue($hasCVX, 'CVX coding should be present for immunizations');
//    }

    /**
     * Test subject reference points to US Core Patient
     */
    public function testSubjectReferenceCompliance(): void
    {
        $testRecord = $this->fixtureManager->createDrugSaleDispense();
        $fhirResource = $this->localDispensaryService->parseOpenEMRRecord($testRecord);

        $subject = $fhirResource->getSubject();
        $this->assertNotNull($subject);
        $this->assertEquals('Patient', $subject->getType());
        $this->assertStringContainsString('Patient/', $subject->getReference());
        $this->assertStringContainsString($testRecord['patient_uuid'], $subject->getReference());
    }

    /**
     * Test context reference points to US Core Encounter when available
     */
    public function testContextReferenceCompliance(): void
    {
        $testRecord = $this->fixtureManager->createDrugSaleDispense([
            'encounter' => 0 // make sure an encounter is created and linked
        ]);

        $fhirResource = $this->localDispensaryService->parseOpenEMRRecord($testRecord);
        $context = $fhirResource->getContext();

        if ($context) {
            $this->assertEquals('Encounter', $context->getType());
            $this->assertStringContainsString('Encounter/', $context->getReference());
            $this->assertStringContainsString($testRecord['encounter_uuid'], $context->getReference());
        }
    }

    /**
     * Test authorizingPrescription reference points to US Core MedicationRequest
     */
    public function testAuthorizingPrescriptionReferenceCompliance(): void
    {
        $testRecord = $this->fixtureManager->createDrugSaleDispense([
            'prescription_id' => 0 // ensure a prescription is created and linked
        ]);

        $fhirResource = $this->localDispensaryService->parseOpenEMRRecord($testRecord);
        $authorizingPrescriptions = $fhirResource->getAuthorizingPrescription();

        if (!empty($authorizingPrescriptions)) {
            $prescription = $authorizingPrescriptions[0];
            $this->assertEquals('MedicationRequest', $prescription->getType());
            $this->assertStringContainsString('MedicationRequest/', $prescription->getReference());
            $this->assertStringContainsString($testRecord['prescription_uuid'], $prescription->getReference());
        }
    }

    /**
     * Test type coding from required ValueSet
     */
    public function testTypeValueSetCompliance(): void
    {
        $testRecord = $this->fixtureManager->createDrugSaleDispense([
            'dispense_type' => 'FF'
        ]);

        $fhirResource = $this->localDispensaryService->parseOpenEMRRecord($testRecord);
        $type = $fhirResource->getType();

        $this->assertNotNull($type);
        $codings = $type->getCoding();
        $this->assertNotEmpty($codings);

        $coding = $codings[0];
        $this->assertEquals('http://terminology.hl7.org/ValueSet/v3-ActPharmacySupplyType', $coding->getSystem());
        $this->assertEquals('FF', $coding->getCode());
    }

    /**
     * Test quantity uses UCUM units
     */
    public function testQuantityUCUMCompliance(): void
    {
        $testRecord = $this->fixtureManager->createDrugSaleDispense([
            'quantity' => 30,
            'unit' => 'tablet'
        ]);

        $fhirResource = $this->localDispensaryService->parseOpenEMRRecord($testRecord);
        $quantity = $fhirResource->getQuantity();

        $this->assertNotNull($quantity);
        $this->assertEquals(30, $quantity->getValue());

        // Unit system should be UCUM when possible
        $system = $quantity->getSystem();
        if ($system) {
            $this->assertEquals('http://unitsofmeasure.org', $system);
        }
    }

    /**
     * Test whenHandedOver is a valid FHIR dateTime
     */
    public function testWhenHandedOverDateTimeCompliance(): void
    {
        $testRecord = $this->fixtureManager->createDrugSaleDispense([
            'sale_date' => '2024-01-15 10:30:00'
        ]);

        $fhirResource = $this->localDispensaryService->parseOpenEMRRecord($testRecord);
        $whenHandedOver = $fhirResource->getWhenHandedOver();

        $this->assertNotNull($whenHandedOver);

        $dateTimeValue = $whenHandedOver->getValue();
        $this->assertNotEmpty($dateTimeValue);

        $dateTime = \DateTime::createFromFormat(DATE_ATOM, $dateTimeValue);
        $this->assertNotFalse($dateTime, "whenHandedOver should be valid FHIR dateTime format");
    }

//    /**
//     * Test dosageInstruction route uses proper ValueSet
//     */
//    public function testDosageInstructionRouteValueSet(): void
//    {
//        $testRecord = $this->fixtureManager->createImmunizationDispense([
//            'route' => 'intramuscular'
//        ]);
//
//        $fhirResource = $this->immunizationService->parseOpenEMRRecord($testRecord);
//        $dosageInstructions = $fhirResource->getDosageInstruction();
//
//        if (!empty($dosageInstructions)) {
//            $dosage = $dosageInstructions[0];
//            $route = $dosage->getRoute();
//
//            if ($route) {
//                $codings = $route->getCoding();
//                if (!empty($codings)) {
//                    $coding = $codings[0];
//                    // Should use SNOMED CT route codes per US Core guidance
//                    $this->assertNotEmpty($coding->getSystem());
//                    $this->assertNotEmpty($coding->getCode());
//                }
//            }
//        }
//    }

    /**
     * Test resource meta includes US Core profile
     */
    public function testResourceMetaProfile(): void
    {
        $testRecord = $this->fixtureManager->createDrugSaleDispense();
        $fhirResource = $this->localDispensaryService->parseOpenEMRRecord($testRecord);

        $meta = $fhirResource->getMeta();
        if ($meta) {
            $profiles = $meta->getProfile();
            $hasUSCoreProfile = false;

            foreach ($profiles as $profile) {
                if (str_contains((string) $profile, 'us-core-medicationdispense')) {
                    $hasUSCoreProfile = true;
                    break;
                }
            }

            $this->assertTrue($hasUSCoreProfile, 'Resource should declare US Core profile');
        }
    }

    /**
     * Test mandatory search parameters are supported
     */
    public function testMandatorySearchParametersSupported(): void
    {
        $searchParams = $this->medicationDispenseService->getSearchParams();

        // Patient is mandatory per US Core
        $this->assertArrayHasKey('patient', $searchParams);

        $patientParam = $searchParams['patient'];
        $this->assertEquals('patient', $patientParam->getName());
        $this->assertEquals(SearchFieldType::REFERENCE, $patientParam->getType());
    }

    /**
     * Test optional search parameters are supported
     */
    public function testOptionalSearchParametersSupported(): void
    {
        $searchParams = $this->medicationDispenseService->getSearchParams();

        // These should be supported per US Core
        $this->assertArrayHasKey('status', $searchParams);
        $this->assertArrayHasKey('type', $searchParams);

        $statusParam = $searchParams['status'];
        $this->assertEquals('status', $statusParam->getName());
        $this->assertEquals(SearchFieldType::TOKEN, $statusParam->getType());

        $typeParam = $searchParams['type'];
        $this->assertEquals('type', $typeParam->getName());
        $this->assertEquals(SearchFieldType::TOKEN, $typeParam->getType());
    }
//
//    /**
//     * Test data absent reason extension when required data is missing
//     */
//    public function testDataAbsentReasonExtension(): void
//    {
//        $testRecord = $this->fixtureManager->createDrugSaleDispense([
//            'patient_id' => null,
//            'prescription_id' => 1, // ensure other data is present
//            'encounter' => 1
//        ]);
//
//        $fhirResource = $this->localDispensaryService->parseOpenEMRRecord($testRecord);
//        $subject = $fhirResource->getSubject();
//
//        $this->assertNotNull($subject);
//
//        // Should have data-absent-reason extension when required data is missing
//        $extensions = $subject->getExtension();
//        var_dump($extensions);
//        $hasDataAbsentReason = false;
//
//        foreach ($extensions as $extension) {
//            if (strpos($extension->getUrl(), 'data-absent-reason') !== false) {
//                $hasDataAbsentReason = true;
//                break;
//            }
//        }
//
//        $this->assertTrue($hasDataAbsentReason, 'Should have data-absent-reason extension when required data is missing');
//    }

    /**
     * Test constraint us-core-20 (whenHandedOver required when status is completed)
     */
    public function testConstraintUSCore20(): void
    {
        $testRecord = $this->fixtureManager->createDrugSaleDispense([
            'trans_type' => 1, // completed status
            'sale_date' => '2024-01-15 10:30:00'
        ]);

        $fhirResource = $this->localDispensaryService->parseOpenEMRRecord($testRecord);

        if ($fhirResource->getStatus() === 'completed') {
            $whenHandedOver = $fhirResource->getWhenHandedOver();
            $this->assertNotNull($whenHandedOver, 'whenHandedOver is required when status is completed');
        }
    }

    /**
     * Test resource validation against US Core constraints
     */
    public function testResourceValidationAgainstConstraints(): void
    {
        $testRecord = $this->fixtureManager->createDrugSaleDispense([
            'trans_type' => 1,
            'quantity' => 30,
            'sale_date' => '2024-01-15 10:30:00',
            'drug_name' => 'Amoxicillin 500mg',
            'rxnorm_code' => '308192'
        ]);

        $fhirResource = $this->localDispensaryService->parseOpenEMRRecord($testRecord);

        // Perform basic validation
        $this->assertInstanceOf(FHIRMedicationDispense::class, $fhirResource);
        $this->assertEquals('MedicationDispense', $fhirResource->get_fhirElementName());

        // Required elements should be present
        $this->assertNotNull($fhirResource->getStatus());
        $this->assertNotNull($fhirResource->getMedicationCodeableConcept());
        $this->assertNotNull($fhirResource->getSubject());

        // mustSupport elements should be present when data is available
        $this->assertNotNull($fhirResource->getQuantity());
        $this->assertNotNull($fhirResource->getWhenHandedOver());
    }
}
// AI-generated content ends
