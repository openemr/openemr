<?php

/*
 * FhirDocumentReferenceAdvanceCareDirectiveServiceUSCore8Test.php
 *
 * Tests compliance with US Core 8.0.0 DocumentReference ADI Profile:
 * http://hl7.org/fhir/us/core/StructureDefinition/us-core-adi-documentreference
 *
 * This test class validates that FhirDocumentReferenceAdvanceCareDirectiveService
 * properly implements:
 * - All REQUIRED elements (min > 0) from the US Core profile
 * - All MUST SUPPORT elements from the US Core profile
 * - Proper use of FhirDocumentReferenceTrait for common functionality
 * - Service-specific overrides (populateType, populateCategories, populateAuthenticator)
 * - All five ADI document types from DocumentReferenceAdvancedDirectiveCodeEnum
 *
 * Key Implementation Notes:
 * - FhirDocumentReferenceAdvanceCareDirectiveService extends FhirServiceBase
 * - Uses FhirDocumentReferenceTrait for common DocumentReference functionality
 * - Overrides populateType() to use category_codes instead of code field
 * - Overrides populateCategories() to ALWAYS add mandatory ADI category (LOINC:42348-3)
 * - Overrides populateAuthenticator() and populateAuthenticationTime() for ADI-specific logic
 * - Calls PatientAdvanceDirectiveService for data retrieval
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @copyright Elements marked with AI GENERATED CODE - are in the public domain
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Discover and Change, Inc <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services\FHIR\DocumentReference;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRDocumentReference;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDocumentReference\FHIRDocumentReferenceContent;
use OpenEMR\Services\FHIR\DocumentReference\Enum\DocumentReferenceCategoryEnum;
use OpenEMR\Services\FHIR\DocumentReference\Enum\DocumentReferenceAdvancedDirectiveCodeEnum;
use OpenEMR\Services\FHIR\DocumentReference\FhirDocumentReferenceAdvanceCareDirectiveService;
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
use OpenEMR\Services\FHIR\UtilsService;
use OpenEMR\Services\PatientAdvanceDirectiveService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use Ramsey\Uuid\Rfc4122\UuidV4;
use Ramsey\Uuid\Uuid;

// AI GENERATED CODE - Start
class FhirDocumentReferenceAdvanceCareDirectiveServiceUSCore8Test extends TestCase
{
    private FixtureManager $fixtureManager;
    private FhirDocumentReferenceAdvanceCareDirectiveService $fhirAdiDocService;
    private array $compliantLivingWillData;
    private array $compliantPowerOfAttorneyData;
    private array $compliantDnrOrderData;
    private array $compliantMentalHealthDirectiveData;
    private array $compliantGenericAdiData;
    private string $testPatientUuid;

    protected function setUp(): void
    {
        $this->fixtureManager = new FixtureManager();
        $this->fhirAdiDocService = new FhirDocumentReferenceAdvanceCareDirectiveService();
        $this->testPatientUuid = 'test-patient-uuid-12345';

        // Create compliant test data for Living Will DocumentReference
        $this->compliantLivingWillData = [
            'id' => 100,
            'uuid' => 'living-will-doc-uuid-001',
            'puuid' => $this->testPatientUuid,
            'name' => 'Living_Will_2024.pdf',
            'type' => 'Living Will',
            'status' => 'current',
            'effective_date' => '2024-01-15',
            'created_date' => '2024-01-15 10:30:00',
            'date' => '2024-01-15 10:30:00',
            'last_modified' => '2024-01-15 10:30:00',
            'location' => 'Electronic Health Record',
            'mimetype' => 'application/pdf',
            'hash' => 'abc123hash',
            'category_name' => 'Advance Directive',
            'category_id' => 10,
            'category_codes' => 'LOINC:86533-7',
            'encounter_id' => 101,
            'euuid' => 'encounter-uuid-001',
            'encounter_date' => '2024-01-15',
            'foreign_reference_id' => 1,
            'user_uuid' => 'provider-uuid-123',
            'user_npi' => '1234567890',
            'author' => [
                'user_id' => 5,
                'uuid' => 'provider-uuid-123',
                'first_name' => 'John',
                'last_name' => 'Smith',
                'npi' => '1234567890'
            ],
            'authenticator' => [
                'user_id' => 5,
                'uuid' => 'provider-uuid-123',
                'first_name' => 'John',
                'last_name' => 'Smith',
                'npi' => '1234567890',
                'date_reviewed' => '2024-01-15 11:00:00'
            ],
            'provenance' => [
                'author_id' => 5,
                'uuid' => 'provider-uuid-123',
                'time' => '2024-01-15 10:30:00'
            ]
        ];

        // Create compliant test data for Durable Power of Attorney
        $this->compliantPowerOfAttorneyData = [
            'id' => 101,
            'uuid' => 'power-attorney-doc-uuid-002',
            'puuid' => $this->testPatientUuid,
            'name' => 'Durable_Power_of_Attorney_Medical_2024.pdf',
            'type' => 'Durable Power of Attorney',
            'status' => 'current',
            'effective_date' => '2024-02-20',
            'created_date' => '2024-02-20 14:15:00',
            'date' => '2024-02-20 14:15:00',
            'last_modified' => '2024-02-20 14:15:00',
            'location' => 'Electronic Health Record',
            'mimetype' => 'application/pdf',
            'hash' => 'def456hash',
            'category_name' => 'Advance Directive',
            'category_id' => 10,
            'category_codes' => 'LOINC:64298-3',
            'author' => [
                'user_id' => 5,
                'uuid' => 'provider-uuid-123',
                'first_name' => 'John',
                'last_name' => 'Smith',
                'npi' => '1234567890'
            ],
            'provenance' => [
                'author_id' => 5,
                'uuid' => 'provider-uuid-123',
                'time' => '2024-02-20 14:15:00'
            ]
        ];

        // Create compliant test data for DNR Order
        $this->compliantDnrOrderData = [
            'id' => 102,
            'uuid' => 'dnr-order-doc-uuid-003',
            'puuid' => $this->testPatientUuid,
            'name' => 'DNR_Order_2024.pdf',
            'type' => 'Do Not Resuscitate Order',
            'status' => 'current',
            'effective_date' => '2024-03-10',
            'created_date' => '2024-03-10 09:45:00',
            'date' => '2024-03-10 09:45:00',
            'last_modified' => '2024-03-10 09:45:00',
            'location' => 'Electronic Health Record',
            'mimetype' => 'application/pdf',
            'hash' => 'ghi789hash',
            'category_name' => 'Advance Directive',
            'category_id' => 10,
            'category_codes' => 'LOINC:84095-9',
            'encounter_id' => 102,
            'euuid' => 'encounter-uuid-002',
            'encounter_date' => '2024-03-10',
            'author' => [
                'user_id' => 5,
                'uuid' => 'provider-uuid-123',
                'first_name' => 'John',
                'last_name' => 'Smith',
                'npi' => '1234567890'
            ],
            'authenticator' => [
                'user_id' => 6,
                'uuid' => 'provider-uuid-456',
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'npi' => '0987654321',
                'date_reviewed' => '2024-03-10 10:00:00'
            ],
            'provenance' => [
                'author_id' => 5,
                'uuid' => 'provider-uuid-123',
                'time' => '2024-03-10 09:45:00'
            ]
        ];

        // Create compliant test data for Mental Health Advance Directive
        $this->compliantMentalHealthDirectiveData = [
            'id' => 103,
            'uuid' => 'mental-health-doc-uuid-004',
            'puuid' => $this->testPatientUuid,
            'name' => 'Mental_Health_Advance_Directive_2024.pdf',
            'type' => 'Mental Health Advance Directive',
            'status' => 'current',
            'effective_date' => '2024-04-05',
            'created_date' => '2024-04-05 11:20:00',
            'date' => '2024-04-05 11:20:00',
            'last_modified' => '2024-04-05 11:20:00',
            'location' => 'Electronic Health Record',
            'mimetype' => 'application/pdf',
            'hash' => 'jkl012hash',
            'category_name' => 'Advance Directive',
            'category_id' => 10,
            'category_codes' => 'LOINC:104144-1',
            'author' => [
                'user_id' => 5,
                'uuid' => 'provider-uuid-123',
                'first_name' => 'John',
                'last_name' => 'Smith',
                'npi' => '1234567890'
            ],
            'provenance' => [
                'author_id' => 5,
                'uuid' => 'provider-uuid-123',
                'time' => '2024-04-05 11:20:00'
            ]
        ];

        // Create compliant test data for Generic Advance Directive
        $this->compliantGenericAdiData = [
            'id' => 104,
            'uuid' => 'generic-adi-doc-uuid-005',
            'puuid' => $this->testPatientUuid,
            'name' => 'Advance_Directive_Complete_2024.pdf',
            'type' => 'Advance Directive',
            'status' => 'current',
            'effective_date' => '2024-05-12',
            'created_date' => '2024-05-12 13:30:00',
            'date' => '2024-05-12 13:30:00',
            'last_modified' => '2024-05-12 13:30:00',
            'location' => 'Electronic Health Record',
            'mimetype' => 'application/pdf',
            'hash' => 'mno345hash',
            'category_name' => 'Advance Directive',
            'category_id' => 10,
            'category_codes' => 'LOINC:42348-3',
            'author' => [
                'user_id' => 5,
                'uuid' => 'provider-uuid-123',
                'first_name' => 'John',
                'last_name' => 'Smith',
                'npi' => '1234567890'
            ],
            'provenance' => [
                'author_id' => 5,
                'uuid' => 'provider-uuid-123',
                'time' => '2024-05-12 13:30:00'
            ]
        ];
    }

    #[Test]
    public function testUSCore8ProfileMetadata(): void
    {
        $docReference = $this->fhirAdiDocService->parseOpenEMRRecord($this->compliantLivingWillData);

        // Test DocumentReference is created
        $this->assertInstanceOf(FHIRDocumentReference::class, $docReference);

        // Test meta profile is set correctly for US Core 8.0
        $meta = $docReference->getMeta();
        $this->assertNotNull($meta, 'DocumentReference must have meta element');

        $profiles = $meta->getProfile();
        $this->assertNotEmpty($profiles, 'DocumentReference must have at least one profile');

        // Verify US Core 8.0 ADI profile is present
        $profileUris = array_map(fn($profile) => (string)$profile, $profiles);

        $expectedProfile = 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-adi-documentreference';
        $this->assertContains(
            $expectedProfile,
            $profileUris,
            'DocumentReference must declare US Core 8.0 ADI DocumentReference profile'
        );
    }

    #[Test]
    public function testRequiredElementStatus(): void
    {
        $docReference = $this->fhirAdiDocService->parseOpenEMRRecord($this->compliantLivingWillData);

        // Status is required (min=1, max=1)
        $statusValue = $docReference->getStatus();
        $this->assertNotNull($statusValue, 'DocumentReference.status is required');
        $validStatuses = ['current', 'superseded', 'entered-in-error'];
        $this->assertContains(
            $statusValue,
            $validStatuses,
            'DocumentReference.status must be one of: current, superseded, entered-in-error'
        );
    }

    #[Test]
    public function testRequiredElementType(): void
    {
        $docReference = $this->fhirAdiDocService->parseOpenEMRRecord($this->compliantLivingWillData);

        // Type is required (min=1, max=1) and must-support
        $type = $docReference->getType();
        $this->assertNotNull($type, 'DocumentReference.type is required');

        $codings = $type->getCoding();
        $this->assertNotEmpty($codings, 'DocumentReference.type must have at least one coding');

        // Verify LOINC coding is present
        $loincCoding = null;
        foreach ($codings as $coding) {
            if ((string)$coding->getSystem() === FhirCodeSystemConstants::LOINC) {
                $loincCoding = $coding;
                break;
            }
        }

        $this->assertNotNull($loincCoding, 'DocumentReference.type must include LOINC coding');

        // CRITICAL: Verify code is from valid ADI value set defined in DocumentReferenceAdvancedDirectiveCodeEnum
        $code = (string)$loincCoding->getCode();
        $validCodes = array_map(
            fn($enum) => $enum->value,
            DocumentReferenceAdvancedDirectiveCodeEnum::cases()
        );

        $this->assertContains(
            $code,
            $validCodes,
            sprintf(
                'DocumentReference.type code must be from ADI value set. Got: %s, Valid codes: %s',
                $code,
                implode(', ', $validCodes)
            )
        );

        // Verify the code matches the expected code from test data (86533-7 for Living Will)
        $this->assertEquals(
            '86533-7',
            $code,
            'Living Will document should have LOINC code 86533-7'
        );
    }

    #[Test]
    public function testTypeCodeEnumValidation(): void
    {
        // Verify all enum codes are valid LOINC codes from the ADI value set
        $expectedCodes = [
            '104144-1' => 'Mental Health Advance Directive',
            '86533-7' => 'Patient Living will',
            '64298-3' => 'Power of attorney',
            '84095-9' => 'Do not resuscitate',
            '42348-3' => 'Advance directive',
        ];

        foreach (DocumentReferenceAdvancedDirectiveCodeEnum::cases() as $enumCase) {
            $this->assertArrayHasKey(
                $enumCase->value,
                $expectedCodes,
                "Enum value {$enumCase->value} should be in expected ADI codes"
            );

            $this->assertEquals(
                FhirCodeSystemConstants::LOINC,
                $enumCase->getSystem(),
                "All ADI codes should use LOINC system"
            );

            $this->assertNotEmpty(
                $enumCase->getDescription(),
                "Each enum should have a description"
            );
        }
    }

    #[Test]
    public function testRequiredElementCategory(): void
    {
        $docReference = $this->fhirAdiDocService->parseOpenEMRRecord($this->compliantLivingWillData);

        // Category is required (min=1, max=*) and must-support
        $categories = $docReference->getCategory();
        var_dump($categories);
        $this->assertNotEmpty($categories, 'DocumentReference.category is required and must have at least one category');

        // CRITICAL: Must include ADI category (LOINC 42348-3)
        // The FhirDocumentReferenceAdvanceCareDirectiveService ALWAYS adds this category
        // via its overridden populateCategories() method, regardless of input data
        $hasAdiCategory = false;
        foreach ($categories as $category) {
            $codings = $category->getCoding();
            foreach ($codings as $coding) {
                if ((string)$coding->getSystem() === FhirCodeSystemConstants::LOINC &&
                    (string)$coding->getCode() === '42348-3') {
                    $hasAdiCategory = true;

                    // Verify the display text
                    $display = $coding->getDisplay();
                    if ($display !== null) {
                        $this->assertEquals(
                            'Advance healthcare directives',
                            (string)$display,
                            'ADI category display should match expected text'
                        );
                    }
                    break 2;
                }
            }
        }

        $this->assertTrue(
            $hasAdiCategory,
            'DocumentReference.category MUST ALWAYS include ADI category (LOINC:42348-3) - this is mandatory for US Core ADI profile'
        );
    }

    #[Test]
    public function testCategoryAlwaysPresentEvenWithMinimalData(): void
    {
        // Test that ADI category is ALWAYS added, even with minimal data
        $minimalData = [
            'uuid' => 'minimal-doc-uuid',
            'puuid' => $this->testPatientUuid,
            'name' => 'minimal.pdf',
            'status' => 'current',
            'date' => '2024-01-01',
            'mimetype' => 'application/pdf',
            // NOTE: No category_codes provided in data
        ];

        $docReference = $this->fhirAdiDocService->parseOpenEMRRecord($minimalData);

        // Even without category data, the service MUST add the mandatory ADI category
        $categories = $docReference->getCategory();
        $this->assertNotEmpty($categories, 'Category must be present even with minimal data');

        $hasAdiCategory = false;
        foreach ($categories as $category) {
            $codings = $category->getCoding();
            foreach ($codings as $coding) {
                if ((string)$coding->getSystem() === FhirCodeSystemConstants::LOINC &&
                    (string)$coding->getCode() === '42348-3') {
                    $hasAdiCategory = true;
                    break 2;
                }
            }
        }

        $this->assertTrue(
            $hasAdiCategory,
            'ADI category (LOINC:42348-3) must be present even when no category data is in the input'
        );
    }

    #[Test]
    public function testRequiredElementSubject(): void
    {
        $docReference = $this->fhirAdiDocService->parseOpenEMRRecord($this->compliantLivingWillData);

        // Subject is required (min=1, max=1) and must-support
        $subject = $docReference->getSubject();
        $this->assertNotNull($subject, 'DocumentReference.subject is required');

        $reference = $subject->getReference();
        $this->assertNotNull($reference, 'DocumentReference.subject must have reference');

        $referenceString = (string)$reference;
        $this->assertStringContainsString(
            'Patient/',
            $referenceString,
            'DocumentReference.subject must reference a Patient'
        );

        $this->assertStringContainsString(
            $this->testPatientUuid,
            $referenceString,
            'DocumentReference.subject must reference the correct patient UUID'
        );
    }

    #[Test]
    public function testRequiredElementContent(): void
    {
        $docReference = $this->fhirAdiDocService->parseOpenEMRRecord($this->compliantLivingWillData);

        // Content is required (min=1, max=*) and must-support
        $contents = $docReference->getContent();
        $this->assertNotEmpty($contents, 'DocumentReference.content is required');

        // Each content must have attachment (min=1, max=1)
        foreach ($contents as $content) {
            $attachment = $content->getAttachment();
            $this->assertNotNull(
                $attachment,
                'DocumentReference.content.attachment is required'
            );

            // Must have either data or url (but at least one)
            $data = $attachment->getData();
            $url = $attachment->getUrl();

            $this->assertTrue(
                $data !== null || $url !== null,
                'DocumentReference.content.attachment must have either data or url'
            );
        }
    }

    #[Test]
    public function testMustSupportElementIdentifier(): void
    {
        $docReference = $this->fhirAdiDocService->parseOpenEMRRecord($this->compliantLivingWillData);

        // Identifier is must-support (min=0, max=*)
        // If present, should be properly formatted
        $identifiers = $docReference->getIdentifier();

        if (!empty($identifiers)) {
            foreach ($identifiers as $identifier) {
                $this->assertInstanceOf(
                    FHIRIdentifier::class,
                    $identifier,
                    'DocumentReference.identifier must be valid Identifier'
                );

                // Should have value or system
                $value = $identifier->getValue();
                $system = $identifier->getSystem();

                $this->assertTrue(
                    $value !== null || $system !== null,
                    'DocumentReference.identifier must have value or system'
                );
            }
        }
    }

    #[Test]
    public function testMustSupportElementDate(): void
    {
        $docReference = $this->fhirAdiDocService->parseOpenEMRRecord($this->compliantLivingWillData);

        // Date is must-support (min=0, max=1)
        $date = $docReference->getDate();

        // Date should be present when created_date is provided
        $this->assertNotNull($date, 'DocumentReference.date should be present when created_date is available');

        // Verify date format
        $dateString = (string)$date;
        $this->assertNotEmpty($dateString, 'DocumentReference.date must have a value');
    }

    #[Test]
    public function testMustSupportElementAuthor(): void
    {
        $docReference = $this->fhirAdiDocService->parseOpenEMRRecord($this->compliantLivingWillData);

        // Author is must-support (min=0, max=*)
        $authors = $docReference->getAuthor();

        // Author should be present when author data is provided
        $this->assertNotEmpty($authors, 'DocumentReference.author should be present when author data is available');

        foreach ($authors as $author) {
            $this->assertInstanceOf(
                FHIRReference::class,
                $author,
                'DocumentReference.author must be valid Reference'
            );

            $reference = $author->getReference();
            $this->assertNotNull(
                $reference,
                'DocumentReference.author must have reference'
            );

            // Should reference Practitioner or Organization
            $referenceString = (string)$reference;
            $validTypes = ['Practitioner', 'PractitionerRole', 'Organization', 'Device', 'Patient', 'RelatedPerson'];
            $hasValidType = false;
            foreach ($validTypes as $type) {
                if (str_contains($referenceString, $type . '/')) {
                    $hasValidType = true;
                    break;
                }
            }

            $this->assertTrue(
                $hasValidType,
                'DocumentReference.author must reference valid resource type'
            );
        }
    }

    #[Test]
    public function testMustSupportElementAuthenticator(): void
    {
        $docReference = $this->fhirAdiDocService->parseOpenEMRRecord($this->compliantLivingWillData);

        // Authenticator is must-support (min=0, max=1)
        $authenticator = $docReference->getAuthenticator();

        // Should be present when authenticator data is provided
        $this->assertNotNull(
            $authenticator,
            'DocumentReference.authenticator should be present when authenticator data is available'
        );

        if ($authenticator !== null) {
            $this->assertInstanceOf(
                FHIRReference::class,
                $authenticator,
                'DocumentReference.authenticator must be valid Reference'
            );

            $reference = $authenticator->getReference();
            $this->assertNotNull(
                $reference,
                'DocumentReference.authenticator must have reference'
            );

            // Should reference Practitioner, PractitionerRole, or Organization
            $referenceString = (string)$reference;
            $this->assertTrue(
                str_contains($referenceString, 'Practitioner') ||
                str_contains($referenceString, 'Organization'),
                'DocumentReference.authenticator must reference Practitioner or Organization'
            );
        }
    }

    #[Test]
    public function testMustSupportExtensionAuthenticationTime(): void
    {
        $docReference = $this->fhirAdiDocService->parseOpenEMRRecord($this->compliantLivingWillData);

        // authenticationTime extension is must-support (min=0, max=1)
        $authTimeExtension = $this->findExtensionByUrl(
            $docReference,
            'http://hl7.org/fhir/us/core/StructureDefinition/us-core-authentication-time'
        );

        // Should be present when authenticator with date_reviewed is provided
        $this->assertNotNull(
            $authTimeExtension,
            'us-core-authentication-time extension should be present when authenticator has review date'
        );

        if ($authTimeExtension !== null) {
            $valueDateTime = $authTimeExtension->getValueDateTime();
            $this->assertNotNull(
                $valueDateTime,
                'us-core-authentication-time extension must have valueDateTime'
            );

            // Verify format
            $dateTimeString = (string)$valueDateTime;
            $this->assertNotEmpty(
                $dateTimeString,
                'us-core-authentication-time valueDateTime must have a value'
            );
        }
    }

    #[Test]
    public function testMustSupportContentAttachmentContentType(): void
    {
        $docReference = $this->fhirAdiDocService->parseOpenEMRRecord($this->compliantLivingWillData);

        $contents = $docReference->getContent();
        $this->assertNotEmpty($contents, 'DocumentReference must have content');

        foreach ($contents as $content) {
            $attachment = $content->getAttachment();

            // contentType is must-support (min=0, max=1)
            $contentType = $attachment->getContentType();

            // Should be present when mimetype is provided
            $this->assertNotNull(
                $contentType,
                'DocumentReference.content.attachment.contentType should be present when mimetype is available'
            );

            if ($contentType !== null) {
                $contentTypeString = (string)$contentType;
                $this->assertNotEmpty(
                    $contentTypeString,
                    'DocumentReference.content.attachment.contentType must have a value'
                );

                // Should be valid MIME type
                $this->assertMatchesRegularExpression(
                    '/^[a-z]+\/[a-z0-9\-\+\.]+$/i',
                    $contentTypeString,
                    'DocumentReference.content.attachment.contentType must be valid MIME type'
                );
            }
        }
    }

    #[Test]
    public function testMustSupportContentAttachmentUrl(): void
    {
        $docReference = $this->fhirAdiDocService->parseOpenEMRRecord($this->compliantLivingWillData);

        $contents = $docReference->getContent();
        $this->assertNotEmpty($contents, 'DocumentReference must have content');

        foreach ($contents as $content) {
            $attachment = $content->getAttachment();

            // url is must-support (min=0, max=1)
            $url = $attachment->getUrl();

            // For documents in EHR, url should be present
            if ($url !== null) {
                $urlString = (string)$url;
                $this->assertNotEmpty(
                    $urlString,
                    'DocumentReference.content.attachment.url must have a value if present'
                );
            }
        }
    }

    #[Test]
    public function testMustSupportContentFormat(): void
    {
        $docReference = $this->fhirAdiDocService->parseOpenEMRRecord($this->compliantLivingWillData);

        $contents = $docReference->getContent();
        $this->assertNotEmpty($contents, 'DocumentReference must have content');

        foreach ($contents as $content) {
            // format is must-support (min=0, max=1)
            $format = $content->getFormat();

            // Format may be present
            if ($format !== null) {
                $this->assertInstanceOf(
                    FHIRCodeableConcept::class,
                    $format,
                    'DocumentReference.content.format must be valid CodeableConcept'
                );
            }
        }
    }

    #[Test]
    public function testContextElementFromTrait(): void
    {
        // Test with encounter data (should have context)
        $docReference = $this->fhirAdiDocService->parseOpenEMRRecord($this->compliantLivingWillData);

        // Context element is populated by FhirDocumentReferenceTrait when euuid is present
        $context = $docReference->getContext();

        $this->assertNotNull(
            $context,
            'DocumentReference.context should be present when encounter (euuid) data is available'
        );

        if ($context !== null) {
            // Verify encounter reference
            $encounters = $context->getEncounter();
            $this->assertNotEmpty(
                $encounters,
                'DocumentReference.context.encounter should be present when euuid is provided'
            );

            $encounterRef = $encounters[0];
            $reference = $encounterRef->getReference();
            $this->assertNotNull($reference, 'Encounter reference must have reference value');

            $referenceString = (string)$reference;
            $this->assertStringContainsString(
                'Encounter/',
                $referenceString,
                'Context encounter must reference an Encounter resource'
            );
            $this->assertStringContainsString(
                $this->compliantLivingWillData['euuid'],
                $referenceString,
                'Context encounter must reference the correct encounter UUID'
            );

            // Verify period if encounter_date is present
            if (!empty($this->compliantLivingWillData['encounter_date'])) {
                $period = $context->getPeriod();
                $this->assertNotNull(
                    $period,
                    'DocumentReference.context.period should be present when encounter_date is provided'
                );

                $start = $period->getStart();
                $this->assertNotNull(
                    $start,
                    'DocumentReference.context.period.start should be set'
                );
            }
        }
    }

    #[Test]
    public function testContextAbsentWhenNoEncounter(): void
    {
        // Test with data that has no encounter
        $docReference = $this->fhirAdiDocService->parseOpenEMRRecord($this->compliantPowerOfAttorneyData);

        // Context should be null/absent when no euuid is present
        $context = $docReference->getContext();

        // This is valid - context is optional and only populated when encounter data exists
        $this->assertNull(
            $context,
            'DocumentReference.context should be absent when no encounter (euuid) data is available'
        );
    }

    #[Test]
    public function testServiceSupportsAdvanceCareDirectiveCategory(): void
            {
                // Verify service declares support for advance-care-directive category
                $this->assertTrue(
                    $this->fhirAdiDocService->supportsCategory('advance-care-directive'),
                    'Service must support advance-care-directive category'
                );

                $this->assertTrue(
                    $this->fhirAdiDocService->supportsCategory(DocumentReferenceCategoryEnum::ADVANCE_CARE_DIRECTIVE->value),
                    'Service must support ADVANCE_CARE_DIRECTIVE enum value'
                );
    }

    #[Test]
    public function testGetProfileUrlReturnsUSCore8Profile(): void
            {
                $profileUrl = $this->fhirAdiDocService->getProfileUrl();

                $this->assertEquals(
                    'http://hl7.org/fhir/us/core/StructureDefinition/us-core-adi-documentreference',
                    $profileUrl,
                    'Service must return US Core 8.0 ADI DocumentReference profile URL'
                );
    }

    #[Test]
    #[DataProvider('advanceDirectiveDataProvider')]
    public function testMultipleAdvanceDirectiveTypes(array $adiData, string $expectedCode): void
            {
                $docReference = $this->fhirAdiDocService->parseOpenEMRRecord($adiData);

                // Verify DocumentReference is created
                $this->assertInstanceOf(FHIRDocumentReference::class, $docReference);

                // Verify correct type code
                $type = $docReference->getType();
                $this->assertNotNull($type, 'Type must be present');

                $codings = $type->getCoding();
                $foundCode = false;
        foreach ($codings as $coding) {
            if ((string)$coding->getSystem() === FhirCodeSystemConstants::LOINC &&
                (string)$coding->getCode() === $expectedCode) {
                $foundCode = true;
                break;
            }
        }

                $this->assertTrue(
                    $foundCode,
                    "DocumentReference.type must have LOINC code: $expectedCode"
                );
    }

    public static function advanceDirectiveDataProvider(): array
            {
                // Note: This will be called before setUp(), so we create minimal data here
                return [
                    'Living Will' => [
                        [
                            'uuid' => 'test-uuid-1',
                            'puuid' => 'patient-uuid-1',
                            'name' => 'living_will.pdf',
                            'category_codes' => 'LOINC:86533-7',
                            'status' => 'current',
                            'created_date' => '2024-01-01',
                            'location' => 'EHR',
                            'mimetype' => 'application/pdf',
                        ],
                        '86533-7'
                    ],
                    'Power of Attorney' => [
                        [
                            'uuid' => 'test-uuid-2',
                            'puuid' => 'patient-uuid-1',
                            'name' => 'poa.pdf',
                            'category_codes' => 'LOINC:64298-3',
                            'status' => 'current',
                            'created_date' => '2024-01-01',
                            'location' => 'EHR',
                            'mimetype' => 'application/pdf',
                        ],
                        '64298-3'
                    ],
                    'DNR Order' => [
                        [
                            'uuid' => 'test-uuid-3',
                            'puuid' => 'patient-uuid-1',
                            'name' => 'dnr.pdf',
                            'category_codes' => 'LOINC:84095-9',
                            'status' => 'current',
                            'created_date' => '2024-01-01',
                            'location' => 'EHR',
                            'mimetype' => 'application/pdf',
                        ],
                        '84095-9'
                    ],
                    'Mental Health Directive' => [
                        [
                            'uuid' => 'test-uuid-4',
                            'puuid' => 'patient-uuid-1',
                            'name' => 'mental_health.pdf',
                            'category_codes' => 'LOINC:104144-1',
                            'status' => 'current',
                            'created_date' => '2024-01-01',
                            'location' => 'EHR',
                            'mimetype' => 'application/pdf',
                        ],
                        '104144-1'
                    ],
                    'Generic Advance Directive' => [
                        [
                            'uuid' => 'test-uuid-5',
                            'puuid' => 'patient-uuid-1',
                            'name' => 'advance_directive.pdf',
                            'category_codes' => 'LOINC:42348-3',
                            'status' => 'current',
                            'created_date' => '2024-01-01',
                            'location' => 'EHR',
                            'mimetype' => 'application/pdf',
                        ],
                        '42348-3'
                    ],
                ];
    }

    #[Test]
    public function testDocumentReferenceWithMinimalRequiredElements(): void
            {
                // Test with minimal required data (no optional must-support elements)
                $minimalData = [
                    'uuid' => 'minimal-doc-uuid',
                    'puuid' => $this->testPatientUuid,
                    'name' => 'minimal_directive.pdf',
                    'category_codes' => 'LOINC:42348-3',
                    'status' => 'current',
                    'created_date' => '2024-01-15',
                    'location' => 'EHR',
                    'mimetype' => 'application/pdf',
                ];

                $docReference = $this->fhirAdiDocService->parseOpenEMRRecord($minimalData);

                // Should still create valid DocumentReference with mandatory elements
                $this->assertInstanceOf(FHIRDocumentReference::class, $docReference);
                $this->assertNotNull($docReference->getStatus());
                $this->assertNotNull($docReference->getType());
                $this->assertNotNull($docReference->getSubject());
                $this->assertNotEmpty($docReference->getCategory());
                $this->assertNotEmpty($docReference->getContent());
    }

    #[Test]
    public function testDocumentReferenceWithoutAuthenticator(): void
            {
                // Test that documents without authenticator still validate
                $docReference = $this->fhirAdiDocService->parseOpenEMRRecord($this->compliantPowerOfAttorneyData);

                $this->assertInstanceOf(FHIRDocumentReference::class, $docReference);

                // Authenticator and authentication time are optional
                $authenticator = $docReference->getAuthenticator();
                // May be null, which is valid

                $authTimeExtension = $this->findExtensionByUrl(
                    $docReference,
                    'http://hl7.org/fhir/us/core/StructureDefinition/us-core-authentication-time'
                );
                // May be null, which is valid
    }

    #[Test]
    public function testEnteredInErrorDocumentStatus(): void
            {
                // Test superseded status
                $deletedData = $this->compliantLivingWillData;
                $deletedData['deleted'] = 1;

                $docReference = $this->fhirAdiDocService->parseOpenEMRRecord($deletedData);

                $status = $docReference->getStatus();
                $this->assertNotNull($status);
                $this->assertEquals(
                    'entered-in-error',
                    $status,
                    'DocumentReference should support entered-in-error status'
                );
    }

    #[Test]
    public function testSearchParametersSupport(): void {
                // Verify service supports required search parameters, an exception will be thrown if the search parameters are invalid
                // US Core 8.0 requires these search parameters

                $requiredParams = [
                    'patient' => UuidV4::uuid4()->toString()
                    ,'_id' => UuidV4::uuid4()->toString()
                    , '_lastUpdated' => UtilsService::getDateFormattedAsUTC()
                    , 'category' => DocumentReferenceAdvancedDirectiveCodeEnum::ADVANCE_DIRECTIVE->getSearchValue()
                    , 'date' => UtilsService::getDateFormattedAsUTC()
                ];
                $processingResult = $this->fhirAdiDocService->getAll($requiredParams);
                $this->assertTrue($processingResult->isValid(), "Search with required parameters should be valid");
    }

    #[Test]
    public function testCategoryCodesValidation(): void
            {
                // Verify all valid ADI category codes are supported
                $validCodes = [
                    '104144-1', // Mental Health Advance Directive
                    '86533-7',  // Living Will
                    '64298-3',  // Power of Attorney
                    '84095-9',  // DNR Order
                    '42348-3',  // Generic Advance Directive
                ];

                foreach ($validCodes as $code) {
                    $found = false;
                    foreach (DocumentReferenceAdvancedDirectiveCodeEnum::cases() as $enum) {
                        if ($enum->value === $code) {
                            $found = true;
                            break;
                        }
                    }

                    $this->assertTrue(
                        $found,
                        "Valid ADI code $code must be in DocumentReferenceAdvancedDirectiveCodeEnum"
                    );
                }
    }

    // Helper methods

    private function findExtensionByUrl(FHIRDocumentReference $docReference, string $url): ?FHIRExtension
            {
                $extensions = $docReference->getExtension();
        foreach ($extensions as $extension) {
            if ((string)$extension->getUrl() === $url) {
                return $extension;
            }
        }
                return null;
    }

    private function findCategoryByCode(FHIRDocumentReference $docReference, string $code): ?FHIRCodeableConcept
            {
                $categories = $docReference->getCategory();
        foreach ($categories as $category) {
            $codings = $category->getCoding();
            foreach ($codings as $coding) {
                if ((string)$coding->getCode() === $code) {
                    return $category;
                }
            }
        }
                return null;
    }
}
// END AI GENERATED CODE
