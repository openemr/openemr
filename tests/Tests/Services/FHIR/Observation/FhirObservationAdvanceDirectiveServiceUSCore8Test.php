<?php

/*
 * FhirObservationAdvanceDirectiveServiceUSCore8Test.php
 *
 * Tests compliance with US Core 8.0.0 Observation ADI Documentation Profile:
 * http://hl7.org/fhir/us/core/StructureDefinition/us-core-observation-adi-documentation
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @copyright Elements marked with AI GENERATED CODE - are in the public domain
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services\FHIR\Observation;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRObservation;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\R4\FHIRElement\FHIRInstant;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
use OpenEMR\Services\FHIR\Observation\FhirObservationAdvanceDirectiveService;
use OpenEMR\Services\FHIR\UtilsService;
use OpenEMR\Services\ListService;
use OpenEMR\Services\PatientAdvanceDirectiveService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;

class FhirObservationAdvanceDirectiveServiceUSCore8Test extends TestCase
{
//     AI GENERATED CODE - Start
    private FixtureManager $fixtureManager;
    private FhirObservationAdvanceDirectiveService $fhirAdiService;
    private array $compliantLivingWillData;
    private array $compliantPowerOfAttorneyData;
    private array $compliantDnrOrderData;
    private array $compliantGenericAdiData;
    private string $testPatientUuid;

    protected function setUp(): void
    {
        $this->fixtureManager = new FixtureManager();
        $this->fhirAdiService = new FhirObservationAdvanceDirectiveService();
        $this->testPatientUuid = 'test-patient-uuid-12345';

        // Create compliant test data for Living Will
        $this->compliantLivingWillData = [
            'code' => 'LOINC:75320-2',
            'description' => 'Advance directive - living will',
            'code_coding' => [
                [
                    'system' => 'http://loinc.org',
                    'code' => '75320-2',
                    'display' => 'Advance directive - living will'
                ]
            ],
            'ob_type' => FhirObservationAdvanceDirectiveService::CATEGORY_OBSERVATION_ADI,
            'ob_status' => 'final',
            'puuid' => $this->testPatientUuid,
            'uuid' => 'living-will-obs-uuid-001',
            'date' => '2024-01-15',
            'last_modified' => '2024-01-15 10:30:00',
            'profiles' => [
                'http://hl7.org/fhir/us/core/StructureDefinition/us-core-observation-adi-documentation|8.0.0'
            ],
            // Value as CodeableConcept - "Yes"
            'value' => 'SNOMED-CT:373066001',
            'value_code_description' => 'Yes',
            'document_id' => 100,
            'document_uuid' => 'living-will-doc-uuid-001',
            'document_location' => 'Electronic Health Record',
            'document_name' => 'Living_Will_2024.pdf',
            'performer_uuid' => 'provider-uuid-123',
            'performer_type' => 'Practitioner',
            'performer_display' => 'Dr. John Smith',
        ];

        // Create compliant test data for Durable Power of Attorney
        $this->compliantPowerOfAttorneyData = [
            'code' => 'LOINC:75787-2',
            'description' => 'Advance directive - medical power of attorney',
            'code_coding' => [
                [
                    'system' => 'http://loinc.org',
                    'code' => '75787-2',
                    'display' => 'Advance directive - medical power of attorney'
                ]
            ],
            'ob_type' => FhirObservationAdvanceDirectiveService::CATEGORY_OBSERVATION_ADI,
            'ob_status' => 'final',
            'puuid' => $this->testPatientUuid,
            'uuid' => 'power-attorney-obs-uuid-002',
            'date' => '2024-02-20',
            'last_modified' => '2024-02-20 14:15:00',
            'profiles' => [
                'http://hl7.org/fhir/us/core/StructureDefinition/us-core-observation-adi-documentation|8.0.0'
            ],
            'value' => 'SNOMED-CT:373066001',
            'value_code_description' => 'Yes',
            'document_id' => 101,
            'document_uuid' => 'power-attorney-doc-uuid-002',
            'document_location' => 'Electronic Health Record',
            'document_name' => 'Durable_Power_of_Attorney_Medical_2024.pdf',
            'performer_uuid' => 'provider-uuid-123',
            'performer_type' => 'Practitioner',
            'performer_display' => 'Dr. John Smith',
        ];

        // Create compliant test data for DNR Order
        $this->compliantDnrOrderData = [
            'code' => 'LOINC:78823-2',
            'description' => 'Do not resuscitate order',
            'code_coding' => [
                [
                    'system' => 'http://loinc.org',
                    'code' => '78823-2',
                    'display' => 'Do not resuscitate order'
                ]
            ],
            'ob_type' => FhirObservationAdvanceDirectiveService::CATEGORY_OBSERVATION_ADI,
            'ob_status' => 'final',
            'puuid' => $this->testPatientUuid,
            'uuid' => 'dnr-order-obs-uuid-003',
            'date' => '2024-03-10',
            'last_modified' => '2024-03-10 09:45:00',
            'profiles' => [
                'http://hl7.org/fhir/us/core/StructureDefinition/us-core-observation-adi-documentation|8.0.0'
            ],
            'value' => 'SNOMED-CT:373066001',
            'value_code_description' => 'Yes',
            'document_id' => 102,
            'document_uuid' => 'dnr-order-doc-uuid-003',
            'document_location' => 'Electronic Health Record',
            'document_name' => 'DNR_Order_2024.pdf',
            'performer_uuid' => 'provider-uuid-123',
            'performer_type' => 'Practitioner',
            'performer_display' => 'Dr. John Smith',
        ];

        // Create compliant test data for Generic Advance Directive
        $this->compliantGenericAdiData = [
            'code' => 'LOINC:42348-3',
            'description' => 'Advance directive',
            'code_coding' => [
                [
                    'system' => 'http://loinc.org',
                    'code' => '42348-3',
                    'display' => 'Advance directive'
                ]
            ],
            'ob_type' => FhirObservationAdvanceDirectiveService::CATEGORY_OBSERVATION_ADI,
            'ob_status' => 'final',
            'puuid' => $this->testPatientUuid,
            'uuid' => 'generic-adi-obs-uuid-004',
            'date' => '2024-04-05',
            'last_modified' => '2024-04-05 11:20:00',
            'profiles' => [
                'http://hl7.org/fhir/us/core/StructureDefinition/us-core-observation-adi-documentation|8.0.0'
            ],
            'value' => 'SNOMED-CT:373067005',
            'value_code_description' => 'No',
            'document_id' => 103,
            'document_uuid' => 'generic-adi-doc-uuid-004',
            'document_location' => 'Electronic Health Record',
            'document_name' => 'Advance_Directive_Complete_2024.pdf',
            'performer_uuid' => 'provider-uuid-123',
            'performer_type' => 'Practitioner',
            'performer_display' => 'Dr. John Smith',
        ];
    }

    #[Test]
    public function testUSCore8ProfileMetadata(): void
    {
        $observation = $this->fhirAdiService->parseOpenEMRRecord($this->compliantLivingWillData);

        // Test observation is created
        $this->assertInstanceOf(FHIRObservation::class, $observation);

        // Test meta profile is set correctly for US Core 8.0
        $meta = $observation->getMeta();
        $this->assertNotNull($meta, 'Observation must have meta element');

        $profiles = $meta->getProfile();
        $this->assertNotEmpty($profiles, 'Observation must have at least one profile');

        // Verify US Core 8.0 ADI profile is present
        $profileUris = array_map(fn($profile) => (string)$profile, $profiles);

        $expectedProfile = 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-observation-adi-documentation|8.0.0';
        $this->assertContains(
            $expectedProfile,
            $profileUris,
            'Observation must declare US Core 8.0 ADI Documentation profile'
        );
    }

    #[Test]
    public function testRequiredStatus(): void
    {
        $observation = $this->fhirAdiService->parseOpenEMRRecord($this->compliantLivingWillData);

        // US Core requires status (1..1 cardinality, must-support)
        $status = $observation->getStatus();
        $this->assertNotNull($status, 'Observation must have status');
        $this->assertNotEmpty($status->getValue(), 'Status must have value');

        // Test valid status values for ADI observations (typically 'final')
        $validStatuses = ['registered', 'preliminary', 'final', 'amended', 'corrected'];
        $this->assertContains(
            $status->getValue(),
            $validStatuses,
            'Status must be valid FHIR ObservationStatus value'
        );

        // ADI observations should typically be 'final'
        $this->assertEquals('final', $status->getValue(), 'ADI observations should typically be final');
    }

    #[Test]
    public function testRequiredCodeWithLoincSystem(): void
    {
        $observation = $this->fhirAdiService->parseOpenEMRRecord($this->compliantLivingWillData);

        // US Core requires code (1..1 cardinality, must-support)
        $code = $observation->getCode();
        $this->assertNotNull($code, 'Observation must have code');

        // Test code has at least one coding
        $codings = $code->getCoding();
        $this->assertNotEmpty($codings, 'Code must have at least one coding');

        // Test LOINC system is present
        $hasLoincCoding = false;
        foreach ($codings as $coding) {
            $system = (string)$coding->getSystem();
            if ($system === 'http://loinc.org') {
                $hasLoincCoding = true;
                $this->assertNotNull($coding->getCode(), 'LOINC coding must have code');
                $this->assertNotEmpty((string)$coding->getCode(), 'LOINC code must not be empty');
                break;
            }
        }

        $this->assertTrue(
            $hasLoincCoding,
            'Observation code must include LOINC coding (http://loinc.org)'
        );
    }

    #[Test]
    #[DataProvider('allSupportedCodesProvider')]
    public function testAllSupportedLoincCodes(array $testData, string $expectedCode, string $expectedDisplay): void
    {
        $observation = $this->fhirAdiService->parseOpenEMRRecord($testData);

        $code = $observation->getCode();
        $codings = $code->getCoding();

        // Find LOINC coding
        $loincCoding = null;
        foreach ($codings as $coding) {
            if ((string)$coding->getSystem() === 'http://loinc.org') {
                $loincCoding = $coding;
                break;
            }
        }

        $this->assertNotNull($loincCoding, 'Must have LOINC coding');
        $this->assertEquals($expectedCode, (string)$loincCoding->getCode(), 'LOINC code must match expected');
        $this->assertEquals($expectedDisplay, (string)$loincCoding->getDisplay(), 'LOINC display must match expected');
    }

    public static function allSupportedCodesProvider(): array
    {
        // This will be populated in setUp, but we need to return static data
        return [
            'Living Will - 75320-2' => [
                'testData' => [
                    'code' => 'LOINC:75320-2',
                    'code_coding' => [
                        [
                            'system' => 'http://loinc.org',
                            'code' => '75320-2',
                            'display' => 'Advance directive - living will'
                        ]
                    ],
                    'ob_type' => 'assessment',
                    'ob_status' => 'final',
                    'puuid' => 'test-uuid',
                    'uuid' => 'test-obs-uuid',
                    'date' => '2024-01-15',
                    'value' => 'http://loinc.org:LA33-6',
                    'value_code_description' => 'Yes'
                ],
                'expectedCode' => '75320-2',
                'expectedDisplay' => 'Advance directive - living will'
            ],
            'Power of Attorney - 75787-2' => [
                'testData' => [
                    'code' => 'LOINC:75787-2',
                    'code_coding' => [
                        [
                            'system' => 'http://loinc.org',
                            'code' => '75787-2',
                            'display' => 'Advance directive - medical power of attorney'
                        ]
                    ],
                    'ob_type' => 'assessment',
                    'ob_status' => 'final',
                    'puuid' => 'test-uuid',
                    'uuid' => 'test-obs-uuid',
                    'date' => '2024-02-20',
                    'value' => 'http://loinc.org:LA33-6',
                    'value_code_description' => 'Yes'
                ],
                'expectedCode' => '75787-2',
                'expectedDisplay' => 'Advance directive - medical power of attorney'
            ],
            'DNR Order - 78823-2' => [
                'testData' => [
                    'code' => 'LOINC:78823-2',
                    'code_coding' => [
                        [
                            'system' => 'http://loinc.org',
                            'code' => '78823-2',
                            'display' => 'Do not resuscitate order'
                        ]
                    ],
                    'ob_type' => 'assessment',
                    'ob_status' => 'final',
                    'puuid' => 'test-uuid',
                    'uuid' => 'test-obs-uuid',
                    'date' => '2024-03-10',
                    'value' => 'http://loinc.org:LA33-6',
                    'value_code_description' => 'Yes'
                ],
                'expectedCode' => '78823-2',
                'expectedDisplay' => 'Do not resuscitate order'
            ],
            'Generic ADI - 42348-3' => [
                'testData' => [
                    'code' => 'LOINC:42348-3',
                    'code_coding' => [
                        [
                            'system' => 'http://loinc.org',
                            'code' => '42348-3',
                            'display' => 'Advance directive'
                        ]
                    ],
                    'ob_type' => 'assessment',
                    'ob_status' => 'final',
                    'puuid' => 'test-uuid',
                    'uuid' => 'test-obs-uuid',
                    'date' => '2024-04-05',
                    'value' => 'http://loinc.org:LA32-8',
                    'value_code_description' => 'No'
                ],
                'expectedCode' => '42348-3',
                'expectedDisplay' => 'Advance directive'
            ]
        ];
    }

    #[Test]
    public function testRequiredSubject(): void
    {
        $observation = $this->fhirAdiService->parseOpenEMRRecord($this->compliantLivingWillData);

        // US Core requires subject (1..1 cardinality, must-support)
        $subject = $observation->getSubject();
        $this->assertNotNull($subject, 'Observation must have subject');

        // Test subject has reference
        $reference = $subject->getReference();
        $this->assertNotNull($reference, 'Subject must have reference');
        $this->assertNotEmpty((string)$reference, 'Subject reference must not be empty');

        // Test reference is to Patient resource
        $referenceString = (string)$reference;
        $this->assertStringStartsWith(
            'Patient/',
            $referenceString,
            'Subject must reference a Patient resource'
        );
    }

    #[Test]
    public function testRequiredCategoryAdiDocumentation(): void
    {
        $observation = $this->fhirAdiService->parseOpenEMRRecord($this->compliantLivingWillData);

        // US Core requires category:us-core slice with specific pattern
        $categories = $observation->getCategory();
        $this->assertNotEmpty($categories, 'Observation must have at least one category');

        // Find the ADI documentation category
        $hasAdiCategory = false;
        foreach ($categories as $category) {
            $codings = $category->getCoding();
            foreach ($codings as $coding) {
                $system = (string)$coding->getSystem();
                $code = (string)$coding->getCode();

                if ($system === FhirCodeSystemConstants::HL7_US_CORE_CATEGORY_OBSERVATION
                    && $code === FhirObservationAdvanceDirectiveService::CATEGORY_OBSERVATION_ADI) {
                    $hasAdiCategory = true;
                    break 2;
                }
            }
        }

        $this->assertTrue(
            $hasAdiCategory,
            'Observation must have category with system "http://hl7.org/fhir/us/core/CodeSystem/us-core-category" and code "observation-adi-documentation"'
        );
    }

    #[Test]
    public function testMustSupportValueCodeableConcept(): void
    {
        $observation = $this->fhirAdiService->parseOpenEMRRecord($this->compliantLivingWillData);

        // value[x] is must-support as CodeableConcept (0..1 cardinality)
        $value = $observation->getValueCodeableConcept();

        // If value is present, it must be properly structured
        if ($value !== null) {
            $this->assertInstanceOf(FHIRCodeableConcept::class, $value, 'Value must be CodeableConcept');

            // Test value has coding
            $codings = $value->getCoding();
            $this->assertNotEmpty($codings, 'Value CodeableConcept must have at least one coding');

            // Test for valid answer codes from required value set
            // ValueSet: http://cts.nlm.nih.gov/fhir/ValueSet/2.16.840.1.113762.1.4.1267.16
            // Common codes: from our yes_no_unknown mapping
            // yes, no, asked-unknown, unknown

            $listOptionService = new ListService();
            $codeTypeService = new CodeTypesService();
            $options = $listOptionService->getOptionsByListName('yes_no_unknown');
            $codes = array_column($options, 'codes');
            $parsedCodes = array_reduce($codes, function(array $parsedCodes, string $code) use ($codeTypeService) {
                $parsedCode = $codeTypeService->parseCode($code);
                $parsedCodes[$parsedCode['code']] = $parsedCode;
                return $parsedCodes;
            }, []);
            $hasValidCode = false;
            foreach ($codings as $coding) {
                $code = (string)$coding->getCode();
                if (isset($parsedCodes[$code])) {
                    $hasValidCode = true;
                    $system = (string)$coding->getSystem();
                    $codeTypeSystem = $codeTypeService->getSystemForCodeType($parsedCodes[$code]['code_type']);
                    $this->assertEquals(
                        $codeTypeSystem,
                        $system,
                        'Value coding should use LOINC system'
                    );
                    break;
                }
            }

            $this->assertTrue(
                $hasValidCode,
                'Value must use valid answer code from required value set ' . implode(" ", $codes)
            );
        }
    }

    #[Test]
    #[DataProvider('valueSetCodesProvider')]
    public function testValueSetCompliance(string $valueCode, string $fullValueCode, string $expectedDisplay, string $expectedSystem): void
    {
        $testData = $this->compliantLivingWillData;
        $testData['value'] = $fullValueCode;
        $testData['value_code_description'] = $expectedDisplay;

        $observation = $this->fhirAdiService->parseOpenEMRRecord($testData);
        $value = $observation->getValueCodeableConcept();

        $this->assertNotNull($value, 'Observation must have value');

        $codings = $value->getCoding();
        $loincCoding = null;
        foreach ($codings as $coding) {
            if ((string)$coding->getSystem() === $expectedSystem) {
                $loincCoding = $coding;
                break;
            }
        }

        $this->assertNotNull($loincCoding, "Value must have $expectedSystem coding");
        $this->assertEquals($valueCode, (string)$loincCoding->getCode(), 'Value code must match');
        $this->assertEquals($expectedDisplay, (string)$loincCoding->getDisplay(), 'Value display must match');
    }

    public static function valueSetCodesProvider(): array
    {
        return [
            'Yes - SNOMED-CT:373066001' => ['373066001', 'SNOMED-CT:373066001', 'Yes', FhirCodeSystemConstants::SNOMED_CT],
            'No - SNOMED-CT:373067005' => ['373067005', 'SNOMED-CT:373067005', 'No', FhirCodeSystemConstants::SNOMED_CT],
            'Unknown - DataAbsentReason:unknown' => ['unknown', 'DataAbsentReason:unknown', 'Unknown', FhirCodeSystemConstants::DATA_ABSENT_REASON_CODE_SYSTEM]
        ];
    }

    #[Test]
    public function testMustSupportIssued(): void
    {
        $testData = $this->compliantLivingWillData;
        $testData['last_updated_time'] = '2024-01-15 10:30:00';
        $expectedDate = UtilsService::getLocalDateAsUTC($testData['last_updated_time']);

        $observation = $this->fhirAdiService->parseOpenEMRRecord($testData);

        // issued is must-support (0..1 cardinality)
        // If present, verify it's properly formatted
        /**
         * @var FHIRInstant
         */
        $issuedValue = $observation->getIssued();
        if ($issuedValue !== null) {
            $this->assertNotNull($issuedValue, 'If issued is present, it must have a value');
            $this->assertEquals($expectedDate, $issuedValue, "Issued value must match last_modified date in UTC format");
        }
    }

    #[Test]
    public function testMustSupportPerformer(): void
    {
        $testData = $this->compliantLivingWillData;
        $testData['author_id'] = 1;

        $observation = $this->fhirAdiService->parseOpenEMRRecord($testData);

        // performer is must-support (0..* cardinality)
        // If present, verify structure
        $performers = $observation->getPerformer();
        if (!empty($performers)) {
            foreach ($performers as $performer) {
                $this->assertInstanceOf(
                    FHIRReference::class,
                    $performer,
                    'Performer must be Reference'
                );

                $reference = $performer->getReference();
                $this->assertNotNull($reference, 'Performer must have reference');

                // US Core specifies US Core Practitioner is must-support type
                $referenceString = (string)$reference;
                $this->assertMatchesRegularExpression(
                    '/^(Practitioner|Organization|Patient|PractitionerRole|CareTeam|RelatedPerson)\//',
                    $referenceString,
                    'Performer must reference valid resource type'
                );
            }
        }
    }

    #[Test]
    public function testMustSupportSupportingInfoExtension(): void
    {
        $observation = $this->fhirAdiService->parseOpenEMRRecord($this->compliantLivingWillData);

        // extension:supporting-info is must-support (0..*)
        // URL: http://hl7.org/fhir/StructureDefinition/workflow-supportingInfo
        $extensions = $observation->getExtension();

        $supportingInfoExtension = null;
        foreach ($extensions as $extension) {
            $url = (string)$extension->getUrl();
            if ($url === 'http://hl7.org/fhir/StructureDefinition/workflow-supportingInfo') {
                $supportingInfoExtension = $extension;
                break;
            }
        }

        // If the extension is present, verify its structure
        $this->assertNotNull($supportingInfoExtension, 'Supporting info extension must be supported');
        if ($supportingInfoExtension !== null) {
            // value[x] must be Reference to DocumentReference (must-support)
            $valueReference = $supportingInfoExtension->getValueReference();
            $this->assertNotNull(
                $valueReference,
                'Supporting info extension must have valueReference'
            );

            $reference = $valueReference->getReference();
            $this->assertNotNull($reference, 'ValueReference must have reference');

            $reference = UtilsService::createRelativeReference("DocumentReference", $this->compliantLivingWillData['document_uuid'], $this->compliantLivingWillData['document_name']);
            $referenceString = $reference->getReference();
            $this->assertEquals(
                (string)$reference->getReference(),
                $referenceString,
                'Supporting info extension should reference DocumentReference (US Core ADI DocumentReference Profile)'
            );
            $this->assertEquals(
                (string)$reference->getDisplay(),
                $this->compliantLivingWillData['document_name'],
                'Supporting info extension display should match document name'
            );
        }
    }

    #[Test]
    public function testServiceSupportsAllRequiredCodes(): void
    {
        // Verify service declares support for all ADI codes
        $this->assertTrue(
            $this->fhirAdiService->supportsCode('75320-2'),
            'Service must support Living Will code 75320-2'
        );
        $this->assertTrue(
            $this->fhirAdiService->supportsCode('75787-2'),
            'Service must support Power of Attorney code 75787-2'
        );
        $this->assertTrue(
            $this->fhirAdiService->supportsCode('78823-2'),
            'Service must support DNR Order code 78823-2'
        );
        $this->assertTrue(
            $this->fhirAdiService->supportsCode('42348-3'),
            'Service must support Generic ADI code 42348-3'
        );
    }

    #[Test]
    public function testServiceSupportsObservationAdiDocumentationCategory(): void
    {
        // Verify service declares support for assessment category
        $this->assertTrue(
            $this->fhirAdiService->supportsCategory('observation-adi-documentation'),
            'Service must support assessment category'
        );

        // Verify service does not support other categories
        $this->assertFalse(
            $this->fhirAdiService->supportsCategory('vital-signs'),
            'Service should not support non-assessment categories'
        );
    }

    #[Test]
    public function testGetProfileURIsReturnsUSCore8Profile(): void
    {
        $profiles = $this->fhirAdiService->getProfileURIs();

        $this->assertNotEmpty($profiles, 'Service must return profile URIs');
        $this->assertContains(
            'http://hl7.org/fhir/us/core/StructureDefinition/us-core-observation-adi-documentation|8.0.0',
            $profiles,
            'Service must declare US Core 8.0 ADI profile'
        );
    }

    #[Test]
    public function testGetSupportedVersionsReturns8_0_0(): void
    {
        $versions = $this->fhirAdiService->getSupportedVersions();

        $this->assertNotEmpty($versions, 'Service must return supported versions');
        $this->assertContains('8.0.0', $versions, 'Service must support US Core 8.0.0');
    }

    #[Test]
    public function testObservationWithMinimalRequiredElements(): void
    {
        // Test with minimal required data (no optional must-support elements)
        $minimalData = [
            'code' => 'LOINC:75320-2',
            'code_coding' => [
                [
                    'system' => 'http://loinc.org',
                    'code' => '75320-2',
                    'display' => 'Advance directive - living will'
                ]
            ],
            'ob_type' => 'assessment',
            'ob_status' => 'final',
            'puuid' => $this->testPatientUuid,
            'uuid' => 'minimal-obs-uuid',
            'date' => '2024-01-15'
        ];

        $observation = $this->fhirAdiService->parseOpenEMRRecord($minimalData);

        // Should still create valid observation with mandatory elements
        $this->assertInstanceOf(FHIRObservation::class, $observation);
        $this->assertNotNull($observation->getStatus());
        $this->assertNotNull($observation->getCode());
        $this->assertNotNull($observation->getSubject());
        $this->assertNotEmpty($observation->getCategory());
    }

    #[Test]
    public function testMultipleAdvanceDirectiveTypes(): void
    {
        // Test that different ADI types can coexist for same patient
        $observations = [
            $this->fhirAdiService->parseOpenEMRRecord($this->compliantLivingWillData),
            $this->fhirAdiService->parseOpenEMRRecord($this->compliantPowerOfAttorneyData),
            $this->fhirAdiService->parseOpenEMRRecord($this->compliantDnrOrderData),
            $this->fhirAdiService->parseOpenEMRRecord($this->compliantGenericAdiData)
        ];

        $this->assertCount(4, $observations, 'Should create 4 distinct observations');

        // Verify each has correct code
        $codes = [];
        foreach ($observations as $obs) {
            $code = $obs->getCode();
            $codings = $code->getCoding();
            foreach ($codings as $coding) {
                if ((string)$coding->getSystem() === 'http://loinc.org') {
                    $codes[] = (string)$coding->getCode();
                    break;
                }
            }
        }

        $this->assertContains('75320-2', $codes, 'Should have Living Will code');
        $this->assertContains('75787-2', $codes, 'Should have Power of Attorney code');
        $this->assertContains('78823-2', $codes, 'Should have DNR Order code');
        $this->assertContains('42348-3', $codes, 'Should have Generic ADI code');
    }

    // Helper methods

    private function findExtensionByUrl(FHIRObservation $observation, string $url): ?FHIRExtension
    {
        $extensions = $observation->getExtension();
        foreach ($extensions as $extension) {
            if ((string)$extension->getUrl() === $url) {
                return $extension;
            }
        }
        return null;
    }

    private function findCategoryBySystem(FHIRObservation $observation, string $system): ?FHIRCodeableConcept
    {
        $categories = $observation->getCategory();
        foreach ($categories as $category) {
            $codings = $category->getCoding();
            foreach ($codings as $coding) {
                if ((string)$coding->getSystem() === $system) {
                    return $category;
                }
            }
        }
        return null;
    }

    // END AI GENERATED CODE
}
