<?php

/*
 * FhirCareTeamServiceTest.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Public Domain This file was generated through the use of Claude.AI on 2024-06-10.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCareTeam;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRResource\FHIRCareTeam\FHIRCareTeamParticipant;
use OpenEMR\Services\FHIR\FhirCareTeamService;
use OpenEMR\Services\FHIR\IPatientCompartmentResourceService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldType;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;

class FhirCareTeamServiceUSCore8Test extends TestCase
{
    // AI GENERATED CODE - Start
    private FhirCareTeamService $fhirCareTeamService;
    private array $compliantCareTeamData;
    private array $compliantCareTeamWithProvidersData;
    private array $compliantCareTeamWithFacilitiesData;
    private array $compliantCareTeamWithContactsData;
    private string $testPatientUuid;

    // US Core 8.0 Profile URI
    private const USCGI_PROFILE_URI = 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-careteam';
    private const SNOMED_CT_SYSTEM = 'http://snomed.info/sct';

    protected function setUp(): void
    {
        $this->fhirCareTeamService = new FhirCareTeamService();
        $this->testPatientUuid = 'test-patient-uuid-12345';

        // Create compliant test data for basic CareTeam
        $this->compliantCareTeamData = [
            'uuid' => 'care-team-uuid-001',
            'puuid' => $this->testPatientUuid,
            'care_team_status' => 'active',
            'team_name' => 'Patient Care Team',
            'date' => '2024-01-15',
            'date_updated' => '2024-01-15 10:30:00',
            'providers' => [],
            'facilities' => [],
            'contacts' => [],
            'primary_facility_uuid' => null,
        ];

        // Create compliant test data for CareTeam with Providers (Practitioners)
        $this->compliantCareTeamWithProvidersData = [
            'uuid' => 'care-team-uuid-002',
            'puuid' => $this->testPatientUuid,
            'care_team_status' => 'active',
            'team_name' => 'Primary Care Team',
            'date' => '2024-02-20',
            'date_updated' => '2024-02-20 14:15:00',
            'providers' => [
                [
                    'provider_uuid' => 'provider-uuid-001',
                    'provider_since' => '2024-01-01',
                    'role' => 'physician',
                    'role_title' => 'Attending Physician',
                    'physician_type_codes' => 'SNOMED-CT:158965000',
                    'physician_type_title' => 'Medical practitioner',
                    'facility_uuid' => 'facility-uuid-001',
                ],
                [
                    'provider_uuid' => 'provider-uuid-002',
                    'provider_since' => '2024-01-15',
                    'role' => 'nurse',
                    'role_title' => 'Registered Nurse',
                    'physician_type_codes' => null,
                    'physician_type_title' => null,
                    'facility_uuid' => null,
                ],
            ],
            'facilities' => [],
            'contacts' => [],
            'primary_facility_uuid' => 'facility-uuid-001',
        ];

        // Create compliant test data for CareTeam with Facilities (Organizations)
        $this->compliantCareTeamWithFacilitiesData = [
            'uuid' => 'care-team-uuid-003',
            'puuid' => $this->testPatientUuid,
            'care_team_status' => 'active',
            'team_name' => 'Hospital Care Team',
            'date' => '2024-03-10',
            'date_updated' => '2024-03-10 09:45:00',
            'providers' => [],
            'facilities' => [
                [
                    'uuid' => 'facility-uuid-001',
                    'facility_taxonomy' => '43741000',
                ],
                [
                    'uuid' => 'facility-uuid-002',
                    'facility_taxonomy' => 'SNOMED-CT:22232009',
                ],
            ],
            'contacts' => [],
            'primary_facility_uuid' => 'facility-uuid-001',
        ];

        // Create compliant test data for CareTeam with Contacts (RelatedPerson)
        $this->compliantCareTeamWithContactsData = [
            'uuid' => 'care-team-uuid-004',
            'puuid' => $this->testPatientUuid,
            'care_team_status' => 'active',
            'team_name' => 'Extended Care Team',
            'date' => '2024-04-05',
            'date_updated' => '2024-04-05 11:20:00',
            'providers' => [],
            'facilities' => [],
            'contacts' => [
                [
                    'uuid' => 'related-person-uuid-001',
                    'role' => 'caregiver',
                    'role_title' => 'Primary Caregiver',
                ],
            ],
            'primary_facility_uuid' => null,
        ];
    }

    #[Test]
    public function testImplementsIPatientCompartmentResourceService(): void
    {
        $this->assertInstanceOf(
            IPatientCompartmentResourceService::class,
            $this->fhirCareTeamService,
            'FhirCareTeamService must implement IPatientCompartmentResourceService'
        );
    }

    #[Test]
    public function testGetPatientContextSearchFieldReturnsValidDefinition(): void
    {
        $searchField = $this->fhirCareTeamService->getPatientContextSearchField();

        $this->assertInstanceOf(
            FhirSearchParameterDefinition::class,
            $searchField,
            'getPatientContextSearchField must return FhirSearchParameterDefinition'
        );

        $this->assertEquals(
            'patient',
            $searchField->getName(),
            'Patient context search field name must be "patient"'
        );

        $this->assertEquals(
            SearchFieldType::REFERENCE,
            $searchField->getType(),
            'Patient context search field type must be REFERENCE'
        );
    }

    #[Test]
    public function testUSCore8ProfileMetadata(): void
    {
        $careTeam = $this->fhirCareTeamService->parseOpenEMRRecord($this->compliantCareTeamData);

        // Test careTeam is created
        $this->assertInstanceOf(FHIRCareTeam::class, $careTeam);

        // Test meta profile is set correctly for US Core 8.0
        $meta = $careTeam->getMeta();
        $this->assertNotNull($meta, 'CareTeam must have meta element');

        $profiles = $meta->getProfile();
        $this->assertNotEmpty($profiles, 'CareTeam must have at least one profile');

        // Verify US Core 8.0 CareTeam profile is present
        $profileUris = array_map(static fn($profile): string => (string)$profile, $profiles);

        $this->assertContains(
            self::USCGI_PROFILE_URI,
            $profileUris,
            'CareTeam must declare US Core 8.0 CareTeam profile'
        );
    }

    #[Test]
    public function testRequiredStatus(): void
    {
        $careTeam = $this->fhirCareTeamService->parseOpenEMRRecord($this->compliantCareTeamData);

        // US Core requires status (1..1 cardinality, must-support)
        $status = $careTeam->getStatus();
        $this->assertNotNull($status, 'CareTeam must have status');
        $this->assertNotEmpty($status, 'Status must have value');

        // Test valid status values per FHIR R4 CareTeamStatus value set
        $validStatuses = ['proposed', 'active', 'suspended', 'inactive', 'entered-in-error'];
        $this->assertContains(
            $status,
            $validStatuses,
            'Status must be valid FHIR CareTeamStatus value'
        );
    }

    #[Test]
    #[DataProvider('statusValuesProvider')]
    public function testAllValidStatusValues(string $statusValue): void
    {
        $testData = $this->compliantCareTeamData;
        $testData['care_team_status'] = $statusValue;

        $careTeam = $this->fhirCareTeamService->parseOpenEMRRecord($testData);
        $status = $careTeam->getStatus();

        $this->assertNotNull($status, 'CareTeam must have status');
        $this->assertEquals($statusValue, $status, "Status should be '$statusValue'");
    }

    public static function statusValuesProvider(): array
    {
        return [
            'active' => ['active'],
            'inactive' => ['inactive'],
            'proposed' => ['proposed'],
            'suspended' => ['suspended'],
            'entered-in-error' => ['entered-in-error'],
        ];
    }

    #[Test]
    public function testInvalidStatusDefaultsToActive(): void
    {
        $testData = $this->compliantCareTeamData;
        $testData['care_team_status'] = 'invalid-status';

        $careTeam = $this->fhirCareTeamService->parseOpenEMRRecord($testData);
        $status = $careTeam->getStatus();

        $this->assertNotNull($status, 'CareTeam must have status');
        $this->assertEquals('active', $status, 'Invalid status should default to active');
    }

    #[Test]
    public function testRequiredSubject(): void
    {
        $careTeam = $this->fhirCareTeamService->parseOpenEMRRecord($this->compliantCareTeamData);

        // US Core requires subject (1..1 cardinality, must-support)
        $subject = $careTeam->getSubject();
        $this->assertNotNull($subject, 'CareTeam must have subject');

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

        // Verify patient UUID is included
        $this->assertStringContainsString(
            $this->testPatientUuid,
            $referenceString,
            'Subject reference must contain the patient UUID'
        );
    }

    #[Test]
    public function testResourceIdIsSet(): void
    {
        $careTeam = $this->fhirCareTeamService->parseOpenEMRRecord($this->compliantCareTeamData);

        $id = $careTeam->getId();
        $this->assertNotNull($id, 'CareTeam must have id');
        $this->assertEquals(
            $this->compliantCareTeamData['uuid'],
            (string)$id,
            'CareTeam id must match the uuid from the data record'
        );
    }

    #[Test]
    public function testOptionalNameElement(): void
    {
        $careTeam = $this->fhirCareTeamService->parseOpenEMRRecord($this->compliantCareTeamData);

        // name is optional but useful
        $name = $careTeam->getName();
        if ($name !== null) {
            $this->assertEquals(
                $this->compliantCareTeamData['team_name'],
                (string)$name,
                'CareTeam name should match the team_name from data record'
            );
        }
    }

    #[Test]
    public function testParticipantWithPractitionerMember(): void
    {
        $careTeam = $this->fhirCareTeamService->parseOpenEMRRecord($this->compliantCareTeamWithProvidersData);

        $participants = $careTeam->getParticipant();
        $this->assertNotEmpty($participants, 'CareTeam with providers must have participants');

        // Find a Practitioner participant
        $practitionerParticipant = null;
        foreach ($participants as $participant) {
            $member = $participant->getMember();
            if ($member !== null) {
                $reference = (string)$member->getReference();
                if (str_starts_with($reference, 'Practitioner/')) {
                    $practitionerParticipant = $participant;
                    break;
                }
            }
        }

        $this->assertNotNull($practitionerParticipant, 'CareTeam must have at least one Practitioner participant');

        // Test participant structure
        $this->assertInstanceOf(FHIRCareTeamParticipant::class, $practitionerParticipant);

        // Test member (required by US Core)
        $member = $practitionerParticipant->getMember();
        $this->assertNotNull($member, 'Participant must have member');
        $this->assertInstanceOf(FHIRReference::class, $member);
    }

    #[Test]
    public function testParticipantRoleWithSnomedCT(): void
    {
        $careTeam = $this->fhirCareTeamService->parseOpenEMRRecord($this->compliantCareTeamWithProvidersData);

        $participants = $careTeam->getParticipant();
        $this->assertNotEmpty($participants, 'CareTeam must have participants');

        // Check that at least one participant has a role with SNOMED CT coding
        $hasRoleWithSnomedCT = false;
        foreach ($participants as $participant) {
            $roles = $participant->getRole();
            if (!empty($roles)) {
                foreach ($roles as $role) {
                    $codings = $role->getCoding();
                    foreach ($codings as $coding) {
                        if ((string)$coding->getSystem() === self::SNOMED_CT_SYSTEM) {
                            $hasRoleWithSnomedCT = true;
                            // Verify code is not empty
                            $this->assertNotEmpty(
                                (string)$coding->getCode(),
                                'SNOMED CT role coding must have a code'
                            );
                            break 3;
                        }
                    }
                }
            }
        }

        $this->assertTrue(
            $hasRoleWithSnomedCT,
            'At least one participant role must have SNOMED CT coding (http://snomed.info/sct)'
        );
    }

    #[Test]
    public function testParticipantPeriod(): void
    {
        $careTeam = $this->fhirCareTeamService->parseOpenEMRRecord($this->compliantCareTeamWithProvidersData);

        $participants = $careTeam->getParticipant();
        $this->assertNotEmpty($participants, 'CareTeam must have participants');

        // Check that at least one participant has a period
        $hasPeriod = false;
        foreach ($participants as $participant) {
            $period = $participant->getPeriod();
            if ($period !== null) {
                $hasPeriod = true;
                // If period is present, verify it has at least a start date
                $start = $period->getStart();
                $this->assertNotNull($start, 'Period should have a start date');
                break;
            }
        }

        $this->assertTrue($hasPeriod, 'At least one participant should have a period');
    }

    #[Test]
    public function testParticipantOnBehalfOf(): void
    {
        $careTeam = $this->fhirCareTeamService->parseOpenEMRRecord($this->compliantCareTeamWithProvidersData);

        $participants = $careTeam->getParticipant();
        $this->assertNotEmpty($participants, 'CareTeam must have participants');

        // Check that at least one participant has onBehalfOf
        $hasOnBehalfOf = false;
        foreach ($participants as $participant) {
            $onBehalfOf = $participant->getOnBehalfOf();
            if ($onBehalfOf !== null) {
                $hasOnBehalfOf = true;
                $reference = (string)$onBehalfOf->getReference();
                $this->assertStringStartsWith(
                    'Organization/',
                    $reference,
                    'onBehalfOf must reference an Organization'
                );
                break;
            }
        }

        $this->assertTrue($hasOnBehalfOf, 'At least one participant should have onBehalfOf reference');
    }

    #[Test]
    public function testParticipantWithOrganizationMember(): void
    {
        $careTeam = $this->fhirCareTeamService->parseOpenEMRRecord($this->compliantCareTeamWithFacilitiesData);

        $participants = $careTeam->getParticipant();
        $this->assertNotEmpty($participants, 'CareTeam with facilities must have participants');

        // Find an Organization participant
        $organizationParticipant = null;
        foreach ($participants as $participant) {
            $member = $participant->getMember();
            if ($member !== null) {
                $reference = (string)$member->getReference();
                if (str_starts_with($reference, 'Organization/')) {
                    $organizationParticipant = $participant;
                    break;
                }
            }
        }

        $this->assertNotNull($organizationParticipant, 'CareTeam must have at least one Organization participant');
    }

    #[Test]
    public function testParticipantWithRelatedPersonMember(): void
    {
        // Set up the service to use a version that supports RelatedPerson
        $careTeam = $this->fhirCareTeamService->parseOpenEMRRecord($this->compliantCareTeamWithContactsData);

        $participants = $careTeam->getParticipant();

        // RelatedPerson support depends on US Core version
        // For US Core 8.0, RelatedPerson should be supported
        $supportedVersions = $this->fhirCareTeamService->getSupportedVersions();

        if (in_array('8.0.0', $supportedVersions)) {
            // Find a RelatedPerson participant
            $relatedPersonParticipant = null;
            foreach ($participants as $participant) {
                $member = $participant->getMember();
                if ($member !== null) {
                    $reference = (string)$member->getReference();
                    if (str_starts_with($reference, 'RelatedPerson/')) {
                        $relatedPersonParticipant = $participant;
                        break;
                    }
                }
            }

            $this->assertNotNull(
                $relatedPersonParticipant,
                'CareTeam with contacts should have RelatedPerson participant in US Core 8.0'
            );
        }
    }

    #[Test]
    public function testManagingOrganization(): void
    {
        $careTeam = $this->fhirCareTeamService->parseOpenEMRRecord($this->compliantCareTeamWithProvidersData);

        $managingOrganizations = $careTeam->getManagingOrganization();

        $this->assertNotEmpty(
            $managingOrganizations,
            'CareTeam with primary_facility_uuid should have managingOrganization'
        );

        $firstOrg = $managingOrganizations[0];
        $reference = (string)$firstOrg->getReference();

        $this->assertStringStartsWith(
            'Organization/',
            $reference,
            'managingOrganization must reference an Organization'
        );
    }

    #[Test]
    public function testGetProfileURIsReturnsUSCore8Profile(): void
    {
        $profiles = $this->fhirCareTeamService->getProfileURIs();

        $this->assertNotEmpty($profiles, 'Service must return profile URIs');

        // Check for US Core 8.0 profile (could be versioned or unversioned)
        $hasUSCoreProfile = false;
        foreach ($profiles as $profile) {
            if (str_contains($profile, self::USCGI_PROFILE_URI)) {
                $hasUSCoreProfile = true;
                break;
            }
        }

        $this->assertTrue($hasUSCoreProfile, 'Service must declare US Core CareTeam profile');
    }

    #[Test]
    public function testGetSupportedVersions(): void
    {
        $versions = $this->fhirCareTeamService->getSupportedVersions();

        $this->assertNotEmpty($versions, 'Service must return supported versions');
        $this->assertIsArray($versions, 'Supported versions must be an array');
    }

    #[Test]
    public function testSearchParametersIncludePatient(): void
    {
        $searchParams = $this->fhirCareTeamService->getSearchParams();

        $this->assertArrayHasKey('patient', $searchParams, 'Search parameters must include "patient"');
        $this->assertInstanceOf(
            FhirSearchParameterDefinition::class,
            $searchParams['patient'],
            'Patient search parameter must be FhirSearchParameterDefinition'
        );
    }

    #[Test]
    public function testSearchParametersIncludeStatus(): void
    {
        $searchParams = $this->fhirCareTeamService->getSearchParams();

        $this->assertArrayHasKey('status', $searchParams, 'Search parameters must include "status"');
        $this->assertInstanceOf(
            FhirSearchParameterDefinition::class,
            $searchParams['status'],
            'Status search parameter must be FhirSearchParameterDefinition'
        );
    }

    #[Test]
    public function testSearchParametersIncludeId(): void
    {
        $searchParams = $this->fhirCareTeamService->getSearchParams();

        $this->assertArrayHasKey('_id', $searchParams, 'Search parameters must include "_id"');
        $this->assertInstanceOf(
            FhirSearchParameterDefinition::class,
            $searchParams['_id'],
            '_id search parameter must be FhirSearchParameterDefinition'
        );
    }

    #[Test]
    public function testSearchParametersIncludeLastUpdated(): void
    {
        $searchParams = $this->fhirCareTeamService->getSearchParams();

        $this->assertArrayHasKey('_lastUpdated', $searchParams, 'Search parameters must include "_lastUpdated"');
        $this->assertInstanceOf(
            FhirSearchParameterDefinition::class,
            $searchParams['_lastUpdated'],
            '_lastUpdated search parameter must be FhirSearchParameterDefinition'
        );
    }

    #[Test]
    public function testGetLastModifiedSearchField(): void
    {
        $lastModifiedField = $this->fhirCareTeamService->getLastModifiedSearchField();

        $this->assertInstanceOf(
            FhirSearchParameterDefinition::class,
            $lastModifiedField,
            'getLastModifiedSearchField must return FhirSearchParameterDefinition'
        );

        $this->assertEquals(
            '_lastUpdated',
            $lastModifiedField->getName(),
            'Last modified search field name must be "_lastUpdated"'
        );

        $this->assertEquals(
            SearchFieldType::DATETIME,
            $lastModifiedField->getType(),
            'Last modified search field type must be DATETIME'
        );
    }

    #[Test]
    public function testMetaLastUpdated(): void
    {
        $careTeam = $this->fhirCareTeamService->parseOpenEMRRecord($this->compliantCareTeamData);

        $meta = $careTeam->getMeta();
        $this->assertNotNull($meta, 'CareTeam must have meta element');

        $lastUpdated = $meta->getLastUpdated();
        $this->assertNotNull($lastUpdated, 'Meta must have lastUpdated');
    }

    #[Test]
    public function testMetaVersionId(): void
    {
        $careTeam = $this->fhirCareTeamService->parseOpenEMRRecord($this->compliantCareTeamData);

        $meta = $careTeam->getMeta();
        $this->assertNotNull($meta, 'CareTeam must have meta element');

        $versionId = $meta->getVersionId();
        $this->assertNotNull($versionId, 'Meta must have versionId');
        $this->assertEquals('1', (string)$versionId, 'VersionId should be "1"');
    }

    #[Test]
    public function testCareTeamWithMinimalRequiredElements(): void
    {
        // Test with minimal required data (no optional elements)
        $minimalData = [
            'uuid' => 'minimal-care-team-uuid',
            'puuid' => $this->testPatientUuid,
            'care_team_status' => 'active',
            'date' => '2024-01-15',
            'providers' => [],
            'facilities' => [],
            'contacts' => [],
        ];

        $careTeam = $this->fhirCareTeamService->parseOpenEMRRecord($minimalData);

        // Should still create valid CareTeam with mandatory elements
        $this->assertInstanceOf(FHIRCareTeam::class, $careTeam);
        $this->assertNotNull($careTeam->getStatus(), 'CareTeam must have status');
        $this->assertNotNull($careTeam->getSubject(), 'CareTeam must have subject');
        $this->assertNotNull($careTeam->getId(), 'CareTeam must have id');
    }

    #[Test]
    public function testMultipleCareTeamTypes(): void
    {
        // Test that different CareTeam configurations can be created for same patient
        $careTeams = [
            $this->fhirCareTeamService->parseOpenEMRRecord($this->compliantCareTeamData),
            $this->fhirCareTeamService->parseOpenEMRRecord($this->compliantCareTeamWithProvidersData),
            $this->fhirCareTeamService->parseOpenEMRRecord($this->compliantCareTeamWithFacilitiesData),
            $this->fhirCareTeamService->parseOpenEMRRecord($this->compliantCareTeamWithContactsData)
        ];

        $this->assertCount(4, $careTeams, 'Should create 4 distinct CareTeam resources');

        // Verify each has unique UUID
        $ids = [];
        foreach ($careTeams as $careTeam) {
            $id = (string)$careTeam->getId();
            $this->assertNotContains($id, $ids, 'Each CareTeam should have unique ID');
            $ids[] = $id;
        }

        // Verify all reference the same patient
        foreach ($careTeams as $careTeam) {
            $subject = $careTeam->getSubject();
            $reference = (string)$subject->getReference();
            $this->assertStringContainsString(
                $this->testPatientUuid,
                $reference,
                'All CareTeams should reference the same patient'
            );
        }
    }

    #[Test]
    #[DataProvider('roleCodeMappingsProvider')]
    public function testRoleCodeMappings(string $role, string $expectedSnomedCode): void
    {
        $testData = $this->compliantCareTeamData;
        $testData['providers'] = [
            [
                'provider_uuid' => 'provider-uuid-test',
                'provider_since' => '2024-01-01',
                'role' => $role,
                'role_title' => ucfirst($role),
                'physician_type_codes' => null,
                'physician_type_title' => null,
                'facility_uuid' => null,
            ],
        ];

        $careTeam = $this->fhirCareTeamService->parseOpenEMRRecord($testData);
        $participants = $careTeam->getParticipant();

        $this->assertNotEmpty($participants, 'CareTeam should have participants');

        // Find the participant and verify role code
        $foundExpectedCode = false;
        foreach ($participants as $participant) {
            $roles = $participant->getRole();
            foreach ($roles as $role) {
                $codings = $role->getCoding();
                foreach ($codings as $coding) {
                    if ((string)$coding->getSystem() === self::SNOMED_CT_SYSTEM) {
                        if ((string)$coding->getCode() === $expectedSnomedCode) {
                            $foundExpectedCode = true;
                            break 3;
                        }
                    }
                }
            }
        }

        $this->assertTrue($foundExpectedCode, "Role '$role' should map to SNOMED CT code '$expectedSnomedCode'");
    }

    public static function roleCodeMappingsProvider(): array
    {
        return [
            'physician' => ['physician', '158965000'],
            'nurse' => ['nurse', '224535009'],
            'nurse_practitioner' => ['nurse_practitioner', '224571005'],
            'physician_assistant' => ['physician_assistant', '449161006'],
            'social_worker' => ['social_worker', '106328005'],
            'pharmacist' => ['pharmacist', '46255001'],
            'dietitian' => ['dietitian', '159033005'],
            'caregiver' => ['caregiver', '133932002'],
        ];
    }

    #[Test]
    public function testUnmappedRoleDefaultsToHealthcareProfessional(): void
    {
        $testData = $this->compliantCareTeamData;
        $testData['providers'] = [
            [
                'provider_uuid' => 'provider-uuid-test',
                'provider_since' => '2024-01-01',
                'role' => 'unknown_role',
                'role_title' => 'Unknown Role',
                'physician_type_codes' => null,
                'physician_type_title' => null,
                'facility_uuid' => null,
            ],
        ];

        $careTeam = $this->fhirCareTeamService->parseOpenEMRRecord($testData);
        $participants = $careTeam->getParticipant();

        $this->assertNotEmpty($participants, 'CareTeam should have participants');

        // Find the participant and verify role defaults to healthcare professional
        $foundDefaultCode = false;
        $defaultCode = '223366009'; // Healthcare professional (general)

        foreach ($participants as $participant) {
            $roles = $participant->getRole();
            foreach ($roles as $role) {
                $codings = $role->getCoding();
                foreach ($codings as $coding) {
                    if ((string)$coding->getSystem() === self::SNOMED_CT_SYSTEM) {
                        if ((string)$coding->getCode() === $defaultCode) {
                            $foundDefaultCode = true;
                            break 3;
                        }
                    }
                }
            }
        }

        $this->assertTrue($foundDefaultCode, 'Unknown role should default to healthcare professional code');
    }

    // Helper method to find a participant by member type
    private function findParticipantByMemberType(FHIRCareTeam $careTeam, string $resourceType): ?FHIRCareTeamParticipant
    {
        $participants = $careTeam->getParticipant();
        foreach ($participants as $participant) {
            $member = $participant->getMember();
            if ($member !== null) {
                $reference = (string)$member->getReference();
                if (str_starts_with($reference, $resourceType . '/')) {
                    return $participant;
                }
            }
        }
        return null;
    }

    // END AI GENERATED CODE
}
