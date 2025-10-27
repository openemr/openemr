<?php

/*
 * FhirPatientServiceUSCore8Test.php
 *
 * Tests compliance with US Core 8.0.0 Patient Profile:
 * http://hl7.org/fhir/us/core/StructureDefinition/us-core-patient
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @copyright Elements marked with AI GENERATED CODE - are in the public domain
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPatient;
use OpenEMR\FHIR\R4\FHIRElement\FHIRExtension;
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
use OpenEMR\Services\FHIR\FhirPatientService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;

class FhirPatientServiceUSCore8Test extends TestCase
{
    // AI Generated code
    private FixtureManager $fixtureManager;
    private FhirPatientService $fhirPatientService;
    private array $compliantPatientData;
    private array $minimalPatientData;

    protected function setUp(): void
    {
        $this->fixtureManager = new FixtureManager();
        $this->fhirPatientService = new FhirPatientService();

        // US Core compliant patient data
        $this->compliantPatientData = [
            'uuid' => 'test-uuid-12345',
            'fname' => 'John',
            'lname' => 'Doe',
            'DOB' => '1980-01-01',
            'sex' => 'Male',                    // Birth sex
            'sex_identified' => 'Male',      // Administrative sex
            'race' => 'white',
            'ethnicity' => 'not_hisp_or_latin',
            'ss' => '123-45-6789',              // Required identifier
            'pubpid' => 'PUB123',
            'addresses' => [
                [
                    'line1' => '123 Main St',
                    'city' => 'Anytown',
                    'state' => 'NY',
                    'postal_code' => '12345',
                    'period_start' => '2020-01-01',
                    'period_end' => null
                ]
                ,[
                    'line1' => '456 Oak St',
                    'city' => 'Springfield',
                    'state' => 'IL',
                    'postal_code' => '62701',
                    'period_start' => '2018-01-01',
                    'period_end' => '2019-12-31'
                ]
            ],
            'postal_code' => '12345',
            'phone_home' => '555-1234',
            'email' => 'john.doe@example.com',
            'language' => 'english',
            'tribal_affiliations' => 'cherokee_nation',
            'interpreter_needed' => 'yes',
            'deceased_date' => null,
            'last_updated' => '2023-01-01 12:00:00'
        ];

        // Minimal US Core compliant data (required fields only)
        $this->minimalPatientData = [
            'uuid' => 'test-uuid-minimal',
            'fname' => 'Jane',
            'lname' => 'Smith',
            'administrative_sex' => 'Female',
            'ss' => '987-65-4321',
            'DOB' => '1980-01-01',
            'sex' => 'Male',
            'pubpid' => 'PUB123',
        ];
    }

    #[Test]
    public function testUSCoreProfileMetadata(): void
    {
        $patient = $this->fhirPatientService->parseOpenEMRRecord($this->compliantPatientData);

        // Test meta profile is set correctly
        $this->assertInstanceOf(FHIRPatient::class, $patient);
        $meta = $patient->getMeta();
        $this->assertNotNull($meta);

        // Could add profile validation here if meta profiles are implemented
        // $profiles = $meta->getProfile();
        // $this->assertContains(FhirPatientService::USCGI_PROFILE_URI, $profiles);
    }

    #[Test]
    public function testRequiredIdentifier(): void
    {
        $patient = $this->fhirPatientService->parseOpenEMRRecord($this->compliantPatientData);

        // US Core requires at least one identifier
        $identifiers = $patient->getIdentifier();
        $this->assertNotEmpty($identifiers, 'Patient must have at least one identifier');

        // Test identifier structure
        foreach ($identifiers as $identifier) {
            $this->assertNotNull($identifier->getSystem(), 'Identifier must have system');
            $this->assertNotNull($identifier->getValue(), 'Identifier must have value');
        }
    }

    #[Test]
    public function testRequiredName(): void
    {
        $patient = $this->fhirPatientService->parseOpenEMRRecord($this->compliantPatientData);

        // US Core requires at least one name
        $names = $patient->getName();
        $this->assertNotEmpty($names, 'Patient must have at least one name');

        // Test US Core constraint us-core-6: family and/or given name required
        $hasValidName = false;
        foreach ($names as $name) {
            if (!empty($name->getFamily()) || !empty($name->getGiven())) {
                $hasValidName = true;
                break;
            }
        }
        $this->assertTrue($hasValidName, 'Patient must have family and/or given name (us-core-6 constraint)');
    }

    #[Test]
    public function testRequiredGender(): void
    {
        $patient = $this->fhirPatientService->parseOpenEMRRecord($this->compliantPatientData);

        // US Core requires gender
        $gender = $patient->getGender();
        $this->assertNotNull($gender, 'Patient must have gender');
        $this->assertNotEmpty($gender->getValue(), 'Gender must have value');

        // Test valid gender values
        $validGenders = ['male', 'female', 'other', 'unknown'];
        $this->assertContains($gender->getValue(), $validGenders, 'Gender must be valid FHIR value');
    }

    #[Test]
    public function testBirthSexExtension(): void
    {
        $patient = $this->fhirPatientService->parseOpenEMRRecord($this->compliantPatientData);

        $birthSexExtension = $this->findExtensionByUrl(
            $patient,
            'http://hl7.org/fhir/us/core/StructureDefinition/us-core-birthsex'
        );

        $this->assertNotNull($birthSexExtension, 'Patient should have birth sex extension');

        // Test extension structure
        $valueCode = $birthSexExtension->getValueCode();
        $this->assertNotNull($valueCode, 'Birth sex extension must have valueCode');

        // Test valid birth sex codes
        $validCodes = ['M', 'F', 'UNK'];
        $this->assertContains((string)$valueCode, $validCodes);
    }

    #[Test]
    public function testSexExtension(): void
    {
        $options = ['Male' => '248152002', 'Female' => '248153007', 'nonbinary' => '33791000087105'
            , 'UNK' => 'unknown', 'asked-declined' => 'asked-declined'];
        $validCodes = array_values($options);
        foreach ($options as $optionId => $code) {
            $this->compliantPatientData['sex_identified'] = $optionId;
            $patient = $this->fhirPatientService->parseOpenEMRRecord($this->compliantPatientData);

            $sexExtension = $this->findExtensionByUrl(
                $patient,
                'http://hl7.org/fhir/us/core/StructureDefinition/us-core-sex'
            );

            $this->assertNotNull($sexExtension, 'Patient should have sex extension');

            // Test extension structure
            $valueCoding = $sexExtension->getValueCoding();
            $this->assertNotNull($valueCoding, 'Sex extension must have valueCoding');
            $this->assertNotEmpty($valueCoding->getCode(), 'Sex must have code');

            // Test valid sex codes
            $this->assertContains((string)$valueCoding->getCode(), $validCodes);

            if (in_array($valueCoding->getCode(), ['unknown', 'asked-declined'])) {
                $this->assertEquals(
                    FhirCodeSystemConstants::DATA_ABSENT_REASON_CODE_SYSTEM,
                    (string)$valueCoding->getSystem()
                );
            } else {
                $this->assertEquals(
                    FhirCodeSystemConstants::SNOMED_CT,
                    (string)$valueCoding->getSystem()
                );
            }
        }
    }

    #[Test]
    public function testTribalAffiliationExtension(): void
    {
        $patient = $this->fhirPatientService->parseOpenEMRRecord($this->compliantPatientData);

        $tribalExtensions = $this->findAllExtensionsByUrl(
            $patient,
            'http://hl7.org/fhir/us/core/StructureDefinition/us-core-tribal-affiliation'
        );

        $this->assertNotEmpty($tribalExtensions, 'Patient should have tribal affiliation extension');

        foreach ($tribalExtensions as $extension) {
            $subExtensions = $extension->getExtension();
            $this->assertNotEmpty($subExtensions, 'Tribal affiliation must have sub-extensions');

            $tribalValueFound = false;
            foreach ($subExtensions as $subExt) {
                if ((string)$subExt->getUrl() === 'tribalAffiliation') {
                    $tribalValueFound = true;
                    $valueCC = $subExt->getValueCodeableConcept();
                    $this->assertNotNull($valueCC, 'Tribal affiliation must have CodeableConcept');
                    break;
                }
            }
            $this->assertTrue($tribalValueFound, 'Tribal affiliation extension must have tribalAffiliation sub-extension');
        }
    }

    #[Test]
    public function testInterpreterNeededExtension(): void
    {
        $patient = $this->fhirPatientService->parseOpenEMRRecord($this->compliantPatientData);

        $options = ['no' => '373067005', 'yes' => '373066001', 'asked-unknown' => 'asked-unknown'
            , 'unknown' => 'unknown'];
        $validCodes = array_values($options);
        foreach ($options as $optionId => $code) {
            $this->compliantPatientData['interpreter_needed'] = $optionId;
            $patient = $this->fhirPatientService->parseOpenEMRRecord($this->compliantPatientData);

            $extension = $this->findExtensionByUrl(
                $patient,
                'http://hl7.org/fhir/us/core/StructureDefinition/us-core-interpreter-needed'
            );

            $this->assertNotNull($extension, 'Patient should have sex extension');

            // Test extension structure
            $valueCoding = $extension->getValueCoding();
            $this->assertNotNull($valueCoding, 'Interpreter extension must have valueCoding');
            $this->assertNotEmpty($valueCoding->getCode(), 'Intrepeter must have code');

            // Test valid codes
            $this->assertContains((string)$valueCoding->getCode(), $validCodes);

            if (in_array($valueCoding->getCode(), ['unknown', 'asked-unknown'])) {
                $this->assertEquals(
                    FhirCodeSystemConstants::DATA_ABSENT_REASON_CODE_SYSTEM,
                    (string)$valueCoding->getSystem()
                );
            } else {
                $this->assertEquals(
                    FhirCodeSystemConstants::SNOMED_CT,
                    (string)$valueCoding->getSystem(),
                    "Interpreter needed code must use SNOMED CT system for code " . $code
                );
            }
        }
    }

    #[Test]
    public function testDeceasedDateTime(): void
    {
        $deceasedPatientData = $this->compliantPatientData;
        $deceasedPatientData['deceased_date'] = '2023-06-15 14:30:00';

        $patient = $this->fhirPatientService->parseOpenEMRRecord($deceasedPatientData);

        $deceasedDateTime = $patient->getDeceasedDateTime();
        $this->assertNotNull($deceasedDateTime, 'Deceased patient should have deceasedDateTime');
        $this->assertNotEmpty($deceasedDateTime->getValue(), 'DeceasedDateTime must have value');

        // Test format (should be ISO 8601)
        $dateString = (string)$deceasedDateTime->getValue();
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $dateString);
    }

    #[Test]
    public function testSupportedFields(): void
    {
        $patient = $this->fhirPatientService->parseOpenEMRRecord($this->compliantPatientData);

        // US Core "MUST SUPPORT" fields

        // Contact details (telecom)
        $telecom = $patient->getTelecom();
        $this->assertNotEmpty($telecom, 'Patient should have contact details');

        // Birth date
        $birthDate = $patient->getBirthDate();
        $this->assertNotNull($birthDate, 'Patient should have birth date');

        // Address
        $addresses = $patient->getAddress();
        $this->assertNotEmpty($addresses, 'Patient should have address');

        // Communication
        $communications = $patient->getCommunication();
        $this->assertNotEmpty($communications, 'Patient should have communication');
    }

    #[Test]
    public function testAddressPeriodSupport(): void
    {
        $patientDataWithPeriod = $this->compliantPatientData;
        $patientDataWithPeriod['addresses'] = [[
            'line1' => '456 Oak St',
            'city' => 'Springfield',
            'state' => 'IL',
            'postal_code' => '62701',
            'period_start' => '2020-01-01',
            'period_end' => '2022-12-31'
        ]];

        $patient = $this->fhirPatientService->parseOpenEMRRecord($patientDataWithPeriod);

        $addresses = $patient->getAddress();
        $this->assertNotEmpty($addresses);

        $address = $addresses[0];
        $period = $address->getPeriod();
        $this->assertNotNull($period, 'Address should have period when provided');
        $this->assertNotNull($period->getStart(), 'Address period should have start');
        $this->assertNotNull($period->getEnd(), 'Address period should have end');
    }

    #[Test]
    public function testMinimalUSCoreCompliance(): void
    {
        $patient = $this->fhirPatientService->parseOpenEMRRecord($this->minimalPatientData);

        // Test minimal required fields are present
        $this->assertNotEmpty($patient->getIdentifier(), 'Minimal patient must have identifier');
        $this->assertNotEmpty($patient->getName(), 'Minimal patient must have name');
        $this->assertNotNull($patient->getGender(), 'Minimal patient must have gender');

        // Should not fail with minimal data
        $this->assertInstanceOf(FHIRPatient::class, $patient);
    }

    #[Test]
    public function testSearchParameterCompliance(): void
    {
        // Test that search parameters are properly defined
        $searchParams = $this->fhirPatientService->getSearchParams();

        // Required US Core search parameters
        $requiredParams = ['_id', 'identifier', 'name', 'birthdate', 'gender'];

        foreach ($requiredParams as $param) {
            $this->assertArrayHasKey($param, $searchParams, "Required search parameter '{$param}' must be supported");
        }

        // Test combined search requirements
        $this->assertArrayHasKey('birthdate', $searchParams, 'Must support birthdate+name search');
        $this->assertArrayHasKey('gender', $searchParams, 'Must support gender+name search');
    }

    #[Test]
    public function testFHIRResourceParsing(): void
    {
        // Test parsing FHIR resource back to OpenEMR format
        $patient = $this->fhirPatientService->parseOpenEMRRecord($this->compliantPatientData);
        $parsedData = $this->fhirPatientService->parseFhirResource($patient);

        // Test critical mappings
        $this->assertEquals($this->compliantPatientData['uuid'], $parsedData['uuid']);
        $this->assertEquals($this->compliantPatientData['fname'], $parsedData['fname']);
        $this->assertEquals($this->compliantPatientData['lname'], $parsedData['lname']);
        $this->assertEquals('Male', $parsedData['sex']); // Birth sex from extension
        $this->assertEquals('Male', $parsedData['sex_identified']); // From gender field

        // Test extension parsing
        $this->assertArrayHasKey('interpreter_needed', $parsedData);
        $this->assertEquals("yes", $parsedData['interpreter_needed']);
    }

    public static function invalidDataProvider(): array
    {
        return [
            'no identifier' => [
                [
                    'uuid' => 'test-uuid',
                    'fname' => 'John',
                    'lname' => 'Doe',
                    'administrative_sex' => 'Male'
                    // Missing identifier
                ]
            ],
            'no name' => [
                [
                    'uuid' => 'test-uuid',
                    'ss' => '123-45-6789',
                    'administrative_sex' => 'Male'
                    // Missing name
                ]
            ],
            'no gender' => [
                [
                    'uuid' => 'test-uuid',
                    'fname' => 'John',
                    'lname' => 'Doe',
                    'ss' => '123-45-6789'
                    // Missing gender
                ]
            ]
        ];
    }

    #[Test]
    #[DataProvider('invalidDataProvider')]
    public function testUSCoreValidationFailures(array $invalidData): void
    {
        // This test ensures that missing required US Core elements are handled properly
        // Note: The actual validation behavior depends on your implementation
        // You might want to test that warnings are generated or data-absent-reason extensions are added

        $patient = $this->fhirPatientService->parseOpenEMRRecord($invalidData);

        // The resource should still be created but should handle missing required elements
        $this->assertInstanceOf(FHIRPatient::class, $patient);

        // Add specific assertions based on how your service handles missing required data
        // For example, checking for data-absent-reason extensions
        // TODO: @adunsulag we should flesh this out more once we decide how to handle these cases
    }


    // Helper methods

    private function findExtensionByUrl(FHIRPatient $patient, string $url): ?FHIRExtension
    {
        $extensions = $patient->getExtension();
        foreach ($extensions as $extension) {
            if ((string)$extension->getUrl() === $url) {
                return $extension;
            }
        }
        return null;
    }

    private function findAllExtensionsByUrl(FHIRPatient $patient, string $url): array
    {
        $extensions = $patient->getExtension();
        $matching = [];
        foreach ($extensions as $extension) {
            if ((string)$extension->getUrl() === $url) {
                $matching[] = $extension;
            }
        }
        return $matching;
    }

    // END AI Generated code
    public function testHighestCompatibleVersion311_HasCorrectProfiles(): void
    {
        $this->fhirPatientService->setHighestCompatibleUSCoreProfileVersion(FhirPatientService::PROFILE_VERSION_3_1_1);
        $parsedResource = $this->fhirPatientService->parseOpenEMRRecord($this->compliantPatientData);
        $profiles = $parsedResource->getMeta()->getProfile();
        $this->assertCount(2, $profiles);
        $supportedVersions = FhirPatientService::PROFILE_VERSIONS_V1;
        foreach ($this->fhirPatientService->getProfileForVersions(FhirPatientService::USCGI_PROFILE_URI, $supportedVersions) as $profile) {
            $this->assertContains($profile, $profiles);
        }
        $notExpectedProfiles = $this->fhirPatientService->getProfileForVersions(FhirPatientService::USCGI_PROFILE_URI, [FhirPatientService::PROFILE_VERSION_7_0_0, FhirPatientService::PROFILE_VERSION_8_0_0]);
        foreach ($notExpectedProfiles as $profile) {
            $this->assertNotContains($profile, $profiles);
        }
    }
    public function testHighestCompatibleVersion7_0_HasCorrectProfiles(): void
    {
        $this->fhirPatientService->setHighestCompatibleUSCoreProfileVersion(FhirPatientService::PROFILE_VERSION_7_0_0);
        $parsedResource = $this->fhirPatientService->parseOpenEMRRecord($this->compliantPatientData);
        $profiles = $parsedResource->getMeta()->getProfile();
        $this->assertCount(3, $profiles);
        $supportedVersions = array_merge(FhirPatientService::PROFILE_VERSIONS_V1, [FhirPatientService::PROFILE_VERSION_7_0_0]);
        foreach ($this->fhirPatientService->getProfileForVersions(FhirPatientService::USCGI_PROFILE_URI, $supportedVersions) as $profile) {
            $this->assertContains($profile, $profiles);
        }
        $notExpectedProfiles = $this->fhirPatientService->getProfileForVersions(FhirPatientService::USCGI_PROFILE_URI, [FhirPatientService::PROFILE_VERSION_8_0_0]);
        foreach ($notExpectedProfiles as $profile) {
            $this->assertNotContains($profile, $profiles);
        }
    }

    public function testHighestCompatibleVersion8_0_HasCorrectProfiles(): void
    {
        $this->fhirPatientService->setHighestCompatibleUSCoreProfileVersion(FhirPatientService::PROFILE_VERSION_8_0_0);
        $parsedResource = $this->fhirPatientService->parseOpenEMRRecord($this->compliantPatientData);
        $profiles = $parsedResource->getMeta()->getProfile();
        $this->assertCount(3, $profiles);
        $supportedVersions = array_merge(FhirPatientService::PROFILE_VERSIONS_V1, [FhirPatientService::PROFILE_VERSION_8_0_0]);
        foreach ($this->fhirPatientService->getProfileForVersions(FhirPatientService::USCGI_PROFILE_URI, $supportedVersions) as $profile) {
            $this->assertContains($profile, $profiles);
        }
        $notExpectedProfiles = $this->fhirPatientService->getProfileForVersions(FhirPatientService::USCGI_PROFILE_URI, [FhirPatientService::PROFILE_VERSION_7_0_0]);
        foreach ($notExpectedProfiles as $profile) {
            $this->assertNotContains($profile, $profiles);
        }
    }

    public function testHighestCompatibleVersion7_0_HasCorrectSexExtension(): void
    {
        $this->fhirPatientService->setHighestCompatibleUSCoreProfileVersion(FhirPatientService::PROFILE_VERSION_7_0_0);
        $parsedResource = $this->fhirPatientService->parseOpenEMRRecord($this->compliantPatientData);
        $sexExtension = $this->findExtensionByUrl(
            $parsedResource,
            'http://hl7.org/fhir/us/core/StructureDefinition/us-core-sex'
        );
        $this->assertNotNull($sexExtension, 'Patient should have sex extension');
        // Test extension structure
        $valueCoding = $sexExtension->getValueCoding();
        $this->assertNull($valueCoding, "Sex extension must NOT have valueCoding for version 7.0.0");
        $this->assertNotNull($sexExtension->getValueCode(), "Sex extension should populate valueCode for version 7.0.0");
        $this->assertEquals('248152002', (string)$sexExtension->getValueCode(), "Sex code must have correct coding for Male for version 7.0.0");
    }
    public function testHighestCompatibleVersion311_HasCorrectSexExtension(): void
    {
        $this->fhirPatientService->setHighestCompatibleUSCoreProfileVersion(FhirPatientService::PROFILE_VERSION_3_1_1);
        $parsedResource = $this->fhirPatientService->parseOpenEMRRecord($this->compliantPatientData);
        $sexExtension = $this->findExtensionByUrl(
            $parsedResource,
            'http://hl7.org/fhir/us/core/StructureDefinition/us-core-sex'
        );
        $this->assertNotNull($sexExtension, 'Patient should have sex extension');
        // Test extension structure
        $valueCoding = $sexExtension->getValueCoding();
        $this->assertNull($valueCoding, "Sex extension must NOT have valueCoding for version 3.1.1");
        $this->assertNotNull($sexExtension->getValueCode(), "Sex extension should populate valueCode for version 3.1.1");
        $this->assertEquals('248152002', (string)$sexExtension->getValueCode(), "Sex code must have correct coding for Male for version 3.1.1");
    }

    public function testHighestCompatibleVersion8_0_HasCorrectSexExtension(): void
    {
        $this->fhirPatientService->setHighestCompatibleUSCoreProfileVersion(FhirPatientService::PROFILE_VERSION_8_0_0);
        $parsedResource = $this->fhirPatientService->parseOpenEMRRecord($this->compliantPatientData);
        $sexExtension = $this->findExtensionByUrl(
            $parsedResource,
            'http://hl7.org/fhir/us/core/StructureDefinition/us-core-sex'
        );
        $this->assertNotNull($sexExtension, 'Patient should have sex extension');
        // Test extension structure
        $valueCoding = $sexExtension->getValueCoding();
        $this->assertNotNull($valueCoding, "Sex extension must have valueCoding for version 8.0.0");
        $this->assertNotEmpty($valueCoding->getCode(), 'Sex must have code for version 8.0.0');
        $this->assertEquals(FhirCodeSystemConstants::SNOMED_CT, (string)$valueCoding->getSystem(), "Sex must use SNOMED CT system for version 8.0.0");
        $this->assertEquals('248152002', (string)$valueCoding->getCode(), "Sex coding.code must have correct coding for Male for version 8.0.0");
        $this->assertEquals("Male", (string)$valueCoding->getDisplay(), "Sex coding.display must have correct display");

        $this->assertNull($sexExtension->getValueCode(), "Sex extension should NOT populate valueCode for version 8.0.0");
    }
}
