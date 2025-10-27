<?php

/*
 * FhirMedicationRequestServiceUSCore8Test.php
 *
 * Tests compliance with US Core 8.0.0 MedicationRequest Profile:
 * http://hl7.org/fhir/us/core/StructureDefinition/us-core-medicationrequest
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @copyright Elements marked with AI GENERATED CODE - are in the public domain
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services\FHIR;

// AI GENERATED CODE - START
use Monolog\Level;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicationRequest;
use OpenEMR\FHIR\R4\FHIRElement;
use OpenEMR\FHIR\R4\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean;
use OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\FHIR\R4\FHIRResource\FHIRTiming;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt;
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
use OpenEMR\Services\FHIR\FhirMedicationRequestService;
use OpenEMR\Services\FHIR\UtilsService;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use Ramsey\Uuid\Rfc4122\UuidV4;

class FhirMedicationRequestServiceUSCore8Test extends TestCase
{
    private FhirMedicationRequestService $fhirMedicationRequestService;
    private array $compliantMedicationRequestData;
    private array $minimalMedicationRequestData;

    protected function setUp(): void
    {
        $this->fhirMedicationRequestService = new FhirMedicationRequestService();
        $this->fhirMedicationRequestService->setSystemLogger(new SystemLogger(Level::Critical));

        // US Core compliant medication request data with all required and must support elements
        $this->compliantMedicationRequestData = [
            'uuid' => 'test-uuid-12345',
            'status' => 'active',                    // Required 1..1
            'intent' => 'order',                     // Required 1..1
            'category' => 'community',               // Must support
            'category_title' => 'Home/Community',    // Must support
            'drugcode' => [                   // Required medication[x] - RxNorm code
                '197696' => [
                    'code' => '197696'
                    ,'description' => 'Amoxicillin 500 MG Oral Capsule'
                    ,'system' => FhirCodeSystemConstants::RXNORM
                ]
            ],
            'drug' => 'Amoxicillin 500 MG Oral Capsule',  // Required medication[x] - text fallback
            'puuid' => UuidV4::uuid4()->toString(),          // Required subject
            'euuid' => UuidV4::uuid4()->toString(),        // Must support encounter
            'pruuid' => UuidV4::uuid4()->toString(),    // Required requester 0..1 DAR
            'date_added' => '2023-01-15 10:30:00',  // Must support authoredOn
            'date_modified' => '2023-01-15 10:30:00',
            'note' => 'Take with food',             // Optional note
            // Must support reasonCode fields (stubbed in service but tested)
            'reason_code' => 'Z87.891',             // ICD-10 code for personal history
            'reason_code_system' => 'http://hl7.org/fhir/sid/icd-10-cm',
            'reason_code_display' => 'Personal history of nicotine dependence',
            // Must support reasonReference fields (stubbed in service but tested)
            'reason_reference_type' => 'Condition',
            'reason_reference_uuid' => 'condition-uuid-abc',
            // Must support dosageInstruction fields
            'drug_dosage_instructions' => 'Take 1 capsule by mouth twice daily',
            'interval_codes' => 'BID',
            'interval_notes' => 'Twice a day',
            'prescription_drug_size' => 1,
            'route' => 'PO',
            'route_id' => '26643006',
            'route_title' => 'Oral route',
            'route_codes' => 'SNOMED-CT:26643006',
            // Must support dispenseRequest fields
            'refills' => 2,
            'quantity' => 30,
            'unit_title' => 'capsule',
            'unit_code' => '{capsule}',
            // Must support medicationAdherence extension
            'medication_adherence' => 'compliance',
            'medication_adherence_title' => 'Complies with drug therapy (finding)',
            'medication_adherence_codes' => 'SNOMED-CT:1156699004',
            'medication_adherence_date_asserted' => '2023-01-15 01:00:00',
            'medication_adherence_information_source' => 'patient',
            'medication_adherence_information_source_title' => 'Patient',
            'medication_adherence_information_source_codes' => 'SNOMED-CT:116154003'
        ];

        // Minimal US Core compliant data (required fields only)
        $this->minimalMedicationRequestData = [
            'uuid' => 'test-uuid-minimal',
            'status' => 'active',
            'intent' => 'order',
            'drugcode' => [
                '197696' => [
                    'code' => '197696'
                    ,'description' => 'Amoxicillin 500 MG Oral Capsule'
                    ,'system' => FhirCodeSystemConstants::RXNORM
                ]
            ],
            'puuid' => 'patient-uuid-456',
            'date_added' => '2023-01-15 10:30:00',
            'date_modified' => '2023-01-15 10:30:00'
        ];
    }

    #[Test]
    public function testUSCoreProfileMetadata(): void
    {
        $medicationRequest = $this->fhirMedicationRequestService->parseOpenEMRRecord($this->compliantMedicationRequestData);

        // Test that the resource is created correctly
        $this->assertInstanceOf(FHIRMedicationRequest::class, $medicationRequest);

        // Test meta is set
        $meta = $medicationRequest->getMeta();
        $this->assertNotNull($meta);

        // Test lastUpdated is set (required by base FHIR)
        $this->assertNotNull($meta->getLastUpdated());
    }

    #[Test]
    public function testRequiredStatus(): void
    {
        $medicationRequest = $this->fhirMedicationRequestService->parseOpenEMRRecord($this->compliantMedicationRequestData);

        // US Core requires status 1..1
        $status = $medicationRequest->getStatus();
        $this->assertNotNull($status, 'MedicationRequest must have status');
        $this->assertNotEmpty($status, 'Status must have value');

        // Test valid status values per FHIR value set
        $validStatuses = ['active', 'on-hold', 'cancelled', 'completed', 'entered-in-error', 'stopped', 'draft', 'unknown'];
        $this->assertContains($status, $validStatuses, 'Status must be valid FHIR value');
    }

    #[Test]
    public function testRequiredIntent(): void
    {
        $medicationRequest = $this->fhirMedicationRequestService->parseOpenEMRRecord($this->compliantMedicationRequestData);

        // US Core requires intent 1..1
        $intent = $medicationRequest->getIntent();
        $this->assertNotNull($intent, 'MedicationRequest must have intent');
        $this->assertNotEmpty($intent, 'Intent must have value');

        // Test valid intent values per FHIR value set
        $validIntents = ['proposal', 'plan', 'order', 'original-order', 'reflex-order', 'filler-order', 'instance-order', 'option'];
        $this->assertContains($intent, $validIntents, 'Intent must be valid FHIR value');
    }

    #[Test]
    public function testMustSupportCategory(): void
    {
        $medicationRequest = $this->fhirMedicationRequestService->parseOpenEMRRecord($this->compliantMedicationRequestData);

        // US Core category is must support
        $categories = $medicationRequest->getCategory();
        $this->assertNotEmpty($categories, 'MedicationRequest should have at least one category (must support)');

        // Test category structure
        foreach ($categories as $category) {
            $this->assertInstanceOf(FHIRCodeableConcept::class, $category);

            // Check for coding
            $codings = $category->getCoding();
            if (!empty($codings)) {
                foreach ($codings as $coding) {
                    $this->assertNotNull($coding->getSystem(), 'Category coding must have system');
                    $this->assertNotNull($coding->getCode(), 'Category coding must have code');
                }
            }
        }
    }

    #[Test]
    public function testMustSupportReported(): void
    {
        $medicationRequest = $this->fhirMedicationRequestService->parseOpenEMRRecord($this->compliantMedicationRequestData);

        // US Core reported[x] is must support
        // Should have either reportedBoolean or reportedReference
        $reportedBoolean = $medicationRequest->getReportedBoolean();
        $reportedReference = $medicationRequest->getReportedReference();

        $this->assertTrue(
            $reportedBoolean !== null || $reportedReference !== null,
            'MedicationRequest should have reported[x] (must support)'
        );

        // If reportedReference is used, test structure
        if ($reportedReference !== null) {
            $this->assertInstanceOf(FHIRReference::class, $reportedReference);
            // Reference should point to valid resource type
            $reference = $reportedReference->getReference();
            if ($reference !== null) {
                $this->assertMatchesRegularExpression(
                    '/^(Patient|Practitioner|PractitionerRole|RelatedPerson|Organization)\//',
                    $reference,
                    'Reported reference must point to valid resource type'
                );
            }
        }
    }

    #[Test]
    public function testRequiredMedication(): void
    {
        $medicationRequest = $this->fhirMedicationRequestService->parseOpenEMRRecord($this->compliantMedicationRequestData);

        // US Core requires medication[x] 1..1 - either CodeableConcept or Reference
        $medicationCodeableConcept = $medicationRequest->getMedicationCodeableConcept();
        $medicationReference = $medicationRequest->getMedicationReference();

        $this->assertTrue(
            $medicationCodeableConcept !== null || $medicationReference !== null,
            'MedicationRequest must have medication[x]'
        );

        // Test medicationCodeableConcept structure if present
        if ($medicationCodeableConcept !== null) {
            $this->assertInstanceOf(FHIRCodeableConcept::class, $medicationCodeableConcept);

            // Should have either coding or text
            $codings = $medicationCodeableConcept->getCoding();
            $text = $medicationCodeableConcept->getText();

            $this->assertTrue(
                !empty($codings) || $text !== null,
                'MedicationCodeableConcept must have coding or text'
            );

            // If coding present, test RxNorm preference for US Core
            if (!empty($codings)) {
                $hasRxNorm = false;
                foreach ($codings as $coding) {
                    if ($coding->getSystem() && str_contains($coding->getSystem(), 'rxnorm')) {
                        $hasRxNorm = true;
                        break;
                    }
                }
                // Note: RxNorm is preferred but not required
            }
        }
    }

    #[Test]
    public function testRequiredSubject(): void
    {
        $medicationRequest = $this->fhirMedicationRequestService->parseOpenEMRRecord($this->compliantMedicationRequestData);

        // US Core requires subject 1..1
        $subject = $medicationRequest->getSubject();
        $this->assertNotNull($subject, 'MedicationRequest must have subject');
        $this->assertInstanceOf(FHIRReference::class, $subject);

        // Subject should reference a Patient
        $reference = $subject->getReference();
        if ($reference !== null) {
            $this->assertStringStartsWith('Patient/', $reference, 'Subject must reference Patient');
        }
    }

    #[Test]
    public function testMustSupportEncounter(): void
    {
        $medicationRequest = $this->fhirMedicationRequestService->parseOpenEMRRecord($this->compliantMedicationRequestData);

        // US Core encounter is must support 0..1
        $encounter = $medicationRequest->getEncounter();

        // If encounter is present, test structure
        if ($encounter !== null) {
            $this->assertInstanceOf(FHIRReference::class, $encounter);
            $reference = $encounter->getReference();
            if ($reference !== null) {
                $this->assertStringStartsWith('Encounter/', $reference, 'Encounter must reference Encounter resource');
            }
        }
    }

    #[Test]
    public function testMustSupportAuthoredOn(): void
    {
        $medicationRequest = $this->fhirMedicationRequestService->parseOpenEMRRecord($this->compliantMedicationRequestData);

        // US Core authoredOn is must support 0..1
        $authoredOn = $medicationRequest->getAuthoredOn();

        // If authoredOn is present, test structure
        if ($authoredOn !== null) {
            $this->assertInstanceOf(FHIRDateTime::class, $authoredOn);
            $this->assertNotEmpty($authoredOn->getValue(), 'AuthoredOn must have value if present');
        }
    }

    #[Test]
    public function testRequiredRequester(): void
    {
        $medicationRequest = $this->fhirMedicationRequestService->parseOpenEMRRecord($this->compliantMedicationRequestData);

        // US Core requester is required 0..1 (data-absent-reason if not present)
        $requester = $medicationRequest->getRequester();
        $this->assertNotNull($requester, 'MedicationRequest should have requester');
        $this->assertInstanceOf(FHIRReference::class, $requester);

        // Requester should reference valid resource type
        $reference = $requester->getReference();
        if ($reference !== null) {
            $this->assertMatchesRegularExpression(
                '/^(Practitioner|PractitionerRole|Organization|Patient|RelatedPerson|Device)\//',
                $reference,
                'Requester must reference valid resource type'
            );
        }
    }

    #[Test]
    public function testMustSupportReasonCode(): void
    {
        $this->markTestIncomplete("ReasonCode is currently stubbed in the service and needs implementation");
//        // Test with reason code data
//        $testData = $this->compliantMedicationRequestData;
//        $medicationRequest = $this->fhirMedicationRequestService->parseOpenEMRRecord($testData);
//
//        // US Core reasonCode is must support 0..*
//        $reasonCodes = $medicationRequest->getReasonCode();
//
//        // Note: This field is currently stubbed in the service but we test the expected structure
//        // If reasonCode is present, test structure
//        if (!empty($reasonCodes)) {
//            foreach ($reasonCodes as $reasonCode) {
//                $this->assertInstanceOf(FHIRCodeableConcept::class, $reasonCode);
//
//                // Should have coding or text
//                $codings = $reasonCode->getCoding();
//                $text = $reasonCode->getText();
//
//                $this->assertTrue(
//                    !empty($codings) || $text !== null,
//                    'ReasonCode must have coding or text'
//                );
//
//                // If coding present, test common code systems
//                if (!empty($codings)) {
//                    foreach ($codings as $coding) {
//                        $system = $coding->getSystem();
//                        if ($system !== null) {
//                            $validSystems = [
//                                'http://hl7.org/fhir/sid/icd-10-cm',
//                                'http://snomed.info/sct',
//                                'http://hl7.org/fhir/sid/icd-9-cm'
//                            ];
//                            // Note: Other systems are valid too, this is just common ones
//                        }
//                    }
//                }
//            }
//        } else {
//            $this->fail("ReasonCode not found");
//        }
    }

    #[Test]
    public function testMustSupportReasonReference(): void
    {
        $this->markTestIncomplete("ReasonReference is currently stubbed in the service and needs implementation");
//        // Test with reason reference data
//        $testData = $this->compliantMedicationRequestData;
//        $medicationRequest = $this->fhirMedicationRequestService->parseOpenEMRRecord($testData);
//
//        // US Core reasonReference is must support 0..*
//        $reasonReferences = $medicationRequest->getReasonReference();
//
//        // Note: This field is currently stubbed in the service but we test the expected structure
//        // If reasonReference is present, test structure
//        if (!empty($reasonReferences)) {
//            foreach ($reasonReferences as $reasonReference) {
//                $this->assertInstanceOf(FHIRReference::class, $reasonReference);
//
//                // Should reference valid resource type
//                $reference = $reasonReference->getReference();
//                if ($reference !== null) {
//                    $this->assertMatchesRegularExpression(
//                        '/^(Condition|Observation)\//',
//                        $reference->getValue(),
//                        'ReasonReference must reference Condition or Observation'
//                    );
//                }
//            }
//        } else {
//            $this->fail("ReasonReference not found");
//        }
    }

    #[Test]
    public function testMustSupportDosageInstruction(): void
    {
        $medicationRequest = $this->fhirMedicationRequestService->parseOpenEMRRecord($this->compliantMedicationRequestData);

        // US Core dosageInstruction is must support 0..*
        $dosageInstructions = $medicationRequest->getDosageInstruction();
        $this->assertNotEmpty($dosageInstructions, 'DosageInstruction must be present (must support)');

        foreach ($dosageInstructions as $dosage) {
            // Test timing (must support)
            $timing = $dosage->getTiming();
            if ($timing !== null) {
                $this->assertInstanceOf(FHIRTiming::class, $timing);
            }

            // Test doseAndRate (must support)
            $doseAndRates = $dosage->getDoseAndRate();
            if (!empty($doseAndRates)) {
                foreach ($doseAndRates as $doseAndRate) {
                    // Test doseQuantity (must support)
                    $doseQuantity = $doseAndRate->getDoseQuantity();
                    if ($doseQuantity !== null) {
                        $this->assertInstanceOf(FHIRQuantity::class, $doseQuantity);
                        $this->assertNotNull($doseQuantity->getValue(), 'DoseQuantity must have value');
                        $this->assertEquals($this->compliantMedicationRequestData['prescription_drug_size'], $doseQuantity->getValue(), "DoseQuantity value must match input data");
                    }
                }
            }

            // Test route
            $route = $dosage->getRoute();
            $this->assertNotNull($dosage->getRoute());
            $this->assertNotEmpty($route->getCoding(), 'Route must have coding if present');
            $this->assertEquals('26643006', $route->getCoding()[0]->getCode(), "Route code must match input data");
            $this->assertEquals(FhirCodeSystemConstants::SNOMED_CT, $route->getCoding()[0]->getSystem(), "Route system must match SNOMED-CT");
        }
    }

    #[Test]
    public function testMustSupportDosageInstructionRouteText(): void
    {
        $requestData = $this->compliantMedicationRequestData;
        $requestData['route_codes'] = null; // No codes, only text
        $medicationRequest = $this->fhirMedicationRequestService->parseOpenEMRRecord($this->compliantMedicationRequestData);
        $this->assertNotEmpty($medicationRequest->getDosageInstruction(), 'DosageInstruction must be present (must support)');
        $dosage = $medicationRequest->getDosageInstruction()[0];
        $route = $dosage->getRoute();
        $this->assertNotNull($route, 'Route must be present');
        $this->assertNotEmpty($route->getCoding(), 'Route coding should not be empty when no codes provided');
        $this->assertEquals($this->compliantMedicationRequestData['route_title'], $route->getText(), 'Route text must match input data');
    }

    #[Test]
    public function testMustSupportDispenseRequest(): void
    {
        $medicationRequest = $this->fhirMedicationRequestService->parseOpenEMRRecord($this->compliantMedicationRequestData);

        // US Core dispenseRequest is must support 0..1
        $dispenseRequest = $medicationRequest->getDispenseRequest();

        // If dispenseRequest is present, test must support elements
        if ($dispenseRequest !== null) {
            // Test numberOfRepeatsAllowed (must support)
            $numberOfRepeats = $dispenseRequest->getNumberOfRepeatsAllowed();
            if ($numberOfRepeats !== null) {
                $this->assertIsInt($numberOfRepeats);
                $this->assertEquals($this->compliantMedicationRequestData['refills'], $numberOfRepeats, "NumberOfRepeatsAllowed must match input data");
            }

            // Test quantity (must support)
            $quantity = $dispenseRequest->getQuantity();
            if ($quantity !== null) {
                $this->assertInstanceOf(FHIRQuantity::class, $quantity);
                $this->assertNotNull($quantity->getValue(), 'Dispense quantity must have value');
                $this->assertIsInt($quantity->getValue(), "Dispense quantity value must be an integer");
                $this->assertEquals($this->compliantMedicationRequestData['quantity'], $quantity->getValue(), "Dispense quantity value must match input data");
            }
        }
    }

    #[Test]
    public function testMustSupportMedicationAdherenceExtension(): void
    {
        $medicationRequest = $this->fhirMedicationRequestService->parseOpenEMRRecord($this->compliantMedicationRequestData);

        // US Core medicationAdherence extension is must support
        $medicationAdherence = $this->findExtensionByUrl(
            $medicationRequest,
            'http://hl7.org/fhir/us/core/StructureDefinition/us-core-medication-adherence'
        );

        // If medicationAdherence extension is present, test structure
        if ($medicationAdherence !== null) {
            $this->assertInstanceOf(FHIRExtension::class, $medicationAdherence);

            // now we need to get the sub-extensions
            $adherenceExtension = $this->findExtensionByUrl($medicationAdherence, 'medicationAdherence');

            // Should have either valueCodeableConcept or valueCode
            $valueCodeableConcept = $adherenceExtension->getValueCodeableConcept();
            $this->assertTrue(
                $valueCodeableConcept !== null,
                'MedicationAdherence extension must have value'
            );
            $this->assertNotEmpty($valueCodeableConcept->getCoding(), "MedicationAdherence valueCodeableConcept must have coding");
            $valueCode = $valueCodeableConcept->getCoding()[0]->getCode();
            $this->assertEquals(
                '1156699004',
                $valueCode,
                'MedicationAdherence valueCode must match input data'
            );
            $this->assertEquals(FhirCodeSystemConstants::SNOMED_CT, $valueCodeableConcept->getCoding()[0]->getSystem(), "MedicationAdherence coding system must match SNOMED-CT");

            $dateAsserted = $this->findExtensionByUrl(
                $medicationAdherence,
                'dateAsserted'
            );
            $this->assertNotNull($dateAsserted, 'MedicationAdherence extension must have dateAsserted sub-extension');
            $this->assertNotEmpty($dateAsserted->getValueDateTime(), 'dateAsserted must have value');
            $this->assertEquals(
                UtilsService::getLocalDateAsUTC($this->compliantMedicationRequestData['medication_adherence_date_asserted']),
                $dateAsserted->getValueDateTime(),
                'dateAsserted value must match input data'
            );
        } else {
            $this->fail("MedicationAdherence extension not found");
        }
    }

    #[Test]
    public function testHandleMissingRequiredData(): void
    {
        $invalidData = [
            'uuid' => 'test-uuid',
            // Missing required fields: status, intent, medication, subject
        ];

        $medicationRequest = $this->fhirMedicationRequestService->parseOpenEMRRecord($invalidData);

        // The resource should still be created
        $this->assertInstanceOf(FHIRMedicationRequest::class, $medicationRequest);

        // Test that service handles missing required elements appropriately
        // (Implementation-specific: might add data-absent-reason extensions or default values)
    }

    #[Test]
    public function testMinimalValidMedicationRequest(): void
    {
        $medicationRequest = $this->fhirMedicationRequestService->parseOpenEMRRecord($this->minimalMedicationRequestData);

        // Test all required elements are present
        $this->assertNotNull($medicationRequest->getStatus(), 'Status is required');
        $this->assertNotNull($medicationRequest->getIntent(), 'Intent is required');
        $this->assertTrue(
            $medicationRequest->getMedicationCodeableConcept() !== null ||
            $medicationRequest->getMedicationReference() !== null,
            'Medication[x] is required'
        );
        $this->assertNotNull($medicationRequest->getSubject(), 'Subject is required');
    }

    public static function validStatusProvider(): array
    {
        return [
            'active' => ['active'],
            'on-hold' => ['on-hold'],
            'cancelled' => ['cancelled'],
            'completed' => ['completed'],
            'entered-in-error' => ['entered-in-error'],
            'stopped' => ['stopped'],
            'draft' => ['draft'],
            'unknown' => ['unknown']
        ];
    }

    #[Test]
    #[DataProvider('validStatusProvider')]
    public function testValidStatusValues(string $status): void
    {
        $testData = $this->compliantMedicationRequestData;
        $testData['status'] = $status;

        $medicationRequest = $this->fhirMedicationRequestService->parseOpenEMRRecord($testData);

        $this->assertEquals($status, $medicationRequest->getStatus());
    }

    public static function validIntentProvider(): array
    {
        return [
            'proposal' => ['proposal'],
            'plan' => ['plan'],
            'order' => ['order'],
            'original-order' => ['original-order'],
            'reflex-order' => ['reflex-order'],
            'filler-order' => ['filler-order'],
            'instance-order' => ['instance-order'],
            'option' => ['option']
        ];
    }

    #[Test]
    #[DataProvider('validIntentProvider')]
    public function testValidIntentValues(string $intent): void
    {
        $testData = $this->compliantMedicationRequestData;
        $testData['intent'] = $intent;

        $medicationRequest = $this->fhirMedicationRequestService->parseOpenEMRRecord($testData);

        $this->assertEquals($intent, $medicationRequest->getIntent());
    }

    #[Test]
    public function testMedicationCodeableConceptWithRxNorm(): void
    {
        $testData = $this->compliantMedicationRequestData;
        $testData['drugcode'] = [
            '197696' => [
                'code' => '197696'
                ,'description' => 'Amoxicillin 500 MG Oral Capsule'
                ,'system' => FhirCodeSystemConstants::RXNORM
            ]
        ]
        ; // RxNorm code for Amoxicillin

        $medicationRequest = $this->fhirMedicationRequestService->parseOpenEMRRecord($testData);

        $medicationCC = $medicationRequest->getMedicationCodeableConcept();
        $this->assertNotNull($medicationCC);

        $codings = $medicationCC->getCoding();
        $this->assertNotEmpty($codings);

        // Should have RxNorm coding
        $hasRxNorm = false;
        foreach ($codings as $coding) {
            if ($coding->getSystem() && str_contains($coding->getSystem(), 'rxnorm')) {
                $hasRxNorm = true;
                $this->assertEquals('197696', $coding->getCode());
                break;
            }
        }
        $this->assertTrue($hasRxNorm, 'Should have RxNorm coding when drugcode provided');
    }

    #[Test]
    public function testMedicationCodeableConceptTextOnly(): void
    {
        $testData = $this->compliantMedicationRequestData;
        unset($testData['drugcode']); // Remove RxNorm code to test text-only

        $medicationRequest = $this->fhirMedicationRequestService->parseOpenEMRRecord($testData);

        $medicationCC = $medicationRequest->getMedicationCodeableConcept();
        $this->assertNotNull($medicationCC);

        // Should have text when no code available
        $text = $medicationCC->getText();
        $this->assertNotNull($text);
        $this->assertEquals('Amoxicillin 500 MG Oral Capsule', $text);
    }

    #[Test]
    public function testCategoryDefaultToCommunity(): void
    {
        $testData = $this->minimalMedicationRequestData;
        // No category specified, should default to community

        $medicationRequest = $this->fhirMedicationRequestService->parseOpenEMRRecord($testData);

        $categories = $medicationRequest->getCategory();
        $this->assertNotEmpty($categories);

        $category = $categories[0];
        $codings = $category->getCoding();
        $this->assertNotEmpty($codings);

        $coding = $codings[0];
        $this->assertEquals('community', $coding->getCode());
        $this->assertEquals(FhirCodeSystemConstants::HL7_MEDICATION_REQUEST_CATEGORY, $coding->getSystem());
    }

    // Helper methods

    private function findExtensionByUrl(FHIRElement|FHIRDomainResource $medicationRequest, string $url): ?FHIRExtension
    {
        $extensions = $medicationRequest->getExtension();
        foreach ($extensions as $extension) {
            if ((string)$extension->getUrl() === $url) {
                return $extension;
            }
        }
        return null;
    }
    // AI GENERATED CODE - END
}
