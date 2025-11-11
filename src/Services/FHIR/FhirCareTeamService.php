<?php

/**
 * FhirCareTeamService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCareTeam;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\R4\FHIRResource\FHIRCareTeam\FHIRCareTeamParticipant;
use OpenEMR\Services\CareTeamService;
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\FHIR\Traits\BulkExportSupportAllOperationsTrait;
use OpenEMR\Services\FHIR\Traits\FhirBulkExportDomainResourceTrait;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\VersionedProfileTrait;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Validators\ProcessingResult;

class FhirCareTeamService extends FhirServiceBase implements IResourceUSCIGProfileService, IFhirExportableResourceService
{
    use FhirServiceBaseEmptyTrait;
    use BulkExportSupportAllOperationsTrait;
    use FhirBulkExportDomainResourceTrait;
    use VersionedProfileTrait;

    // Care Team status values per FHIR R4 spec
    // @see http://hl7.org/fhir/R4/valueset-care-team-status.html
    private const CARE_TEAM_STATUS_ACTIVE = "active";
    private const CARE_TEAM_STATUS_PROPOSED = "proposed";
    private const CARE_TEAM_STATUS_SUSPENDED = "suspended";
    private const CARE_TEAM_STATUS_INACTIVE = "inactive";
    private const CARE_TEAM_STATUS_ENTERED_IN_ERROR = "entered-in-error";
    private const CARE_TEAM_STATII = [
        self::CARE_TEAM_STATUS_ACTIVE,
        self::CARE_TEAM_STATUS_INACTIVE,
        self::CARE_TEAM_STATUS_PROPOSED,
        self::CARE_TEAM_STATUS_SUSPENDED,
        self::CARE_TEAM_STATUS_ENTERED_IN_ERROR
    ];

    // US Core 8.0 Profile URI
    const USCGI_PROFILE_URI = 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-careteam';

    // USCDI v5 Care Team Member Function codes from SNOMED CT
    // @see http://hl7.org/fhir/us/core/ValueSet/us-core-careteam-provider-roles
    const CARE_TEAM_MEMBER_FUNCTION_SYSTEM = 'http://snomed.info/sct';

    /**
     * @var CareTeamService
     */
    private CareTeamService $careTeamService;

    public function __construct()
    {
        parent::__construct();
        $this->careTeamService = new CareTeamService();
    }

    /**
     * Returns an array mapping FHIR CareTeam Resource search parameters to OpenEMR CareTeam search parameters
     *
     * @return array The search parameters
     */
    protected function loadSearchParameters()
    {
        return [
            'patient' => $this->getPatientContextSearchField(),
            'status' => new FhirSearchParameterDefinition('status', SearchFieldType::TOKEN, ['status']),
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)]),
            '_lastUpdated' => $this->getLastModifiedSearchField(),
        ];
    }

    public function getLastModifiedSearchField(): ?FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['date']);
    }

    /**
     * Parses an OpenEMR careTeam record, returning the equivalent FHIR CareTeam Resource
     * Compliant with US Core 8.0 and USCDI v5 requirements
     *
     * @param  array   $dataRecord The source OpenEMR data record
     * @param  boolean $encode     Indicates if the returned resource is encoded into a string. Defaults to false.
     * @return FHIRCareTeam|string|false
     */
    public function parseOpenEMRRecord($dataRecord = [], $encode = false)
    {
        $careTeamResource = new FHIRCareTeam();

        // Set metadata
        $fhirMeta = new FHIRMeta();
        $fhirMeta->setVersionId('1');

        // Add US Core 8.0 profile
        $fhirMeta->addProfile(self::USCGI_PROFILE_URI);

        if (!empty($dataRecord['date'])) {
            $fhirMeta->setLastUpdated(UtilsService::getLocalDateAsUTC($dataRecord['date']));
        } else {
            $fhirMeta->setLastUpdated(UtilsService::getDateFormattedAsUTC());
        }
        $careTeamResource->setMeta($fhirMeta);

        // Set resource ID
        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $careTeamResource->setId($id);

        // Set status (required by US Core)
        if (array_search($dataRecord['care_team_status'], self::CARE_TEAM_STATII) !== false) {
            $careTeamResource->setStatus($dataRecord['care_team_status']);
        } else {
            $careTeamResource->setStatus(self::CARE_TEAM_STATUS_ACTIVE);
        }

        // Set subject (required by US Core) - must be a Patient reference
        $careTeamResource->setSubject(UtilsService::createRelativeReference("Patient", $dataRecord['puuid']));

        // Set name if available (optional but useful)
        if (!empty($dataRecord['team_name'])) {
            $careTeamResource->setName($dataRecord['team_name']);
        }

        $codeTypesService = new CodeTypesService();

        // Add participants (practitioners with roles)
        $this->populateProviderTeamMembers($careTeamResource, $dataRecord, $codeTypesService);
        $this->populateFacilityTeamMembers($careTeamResource, $dataRecord, $codeTypesService);
        $this->populateRelatedPersonTeamMembers($careTeamResource, $dataRecord, $codeTypesService);
        $this->populateManagingOrganization($careTeamResource, $dataRecord);
        // inferno is having issues with the subject not validating.  I'm wondering if it has a bug with
        // the subject and a patient team member being the same reference.
        //        $this->populatePatientMember($careTeamResource, $dataRecord, $codeTypesService);

        if ($encode) {
            return json_encode($careTeamResource);
        } else {
            return $careTeamResource;
        }
    }

    protected function getRoleMappings(): array
    {
        $roleMapping = [
            'physician' => '158965000', // Medical practitioner
            'attending' => '309343006', // Attending physician
            'consulting' => '309345004', // Consultant physician
            'nurse' => '224535009', // Registered nurse
            'nurse_practitioner' => '224571005', // Nurse practitioner
            'physician_assistant' => '449161006', // Physician assistant
            'therapist' => '224538006', // Clinical therapist
            'social_worker' => '106328005', // Social worker
            'case_manager' => '768832004', // Case manager
            'primary_care' => '446050000', // Primary care provider
            'specialist' => '69280009', // Specialist physician
            'pharmacist' => '46255001', // Clinical pharmacist
            'dietitian' => '159033005', // Dietitian
            'mental_health' => '224597008', // Mental health professional
            'care_coordinator' => '768820003', // Care coordinator
            'patient_navigator' => '768821004', // Patient navigator
            'caregiver' => '133932002',
        ];
        return $roleMapping;
    }
    /**
     * Create role CodeableConcept for a provider participant
     * Uses SNOMED CT codes per US Core 8.0 requirements
     */
    private function createRoleCodeableConcept($dataRecordProvider, $codeTypesService)
    {
        // Map common roles to SNOMED CT codes for USCDI v5 compliance
        $roleMapping = $this->getRoleMappings();

        // Try to use physician_type_codes if available
        if (!empty($dataRecordProvider['physician_type_codes'])) {
            $codes = $codeTypesService->parseCode($dataRecordProvider['physician_type_codes']);
            $codes['system'] = self::CARE_TEAM_MEMBER_FUNCTION_SYSTEM;

            // Get description from various sources
            if (!empty($dataRecordProvider['physician_type_title'])) {
                $codes['description'] = $dataRecordProvider['physician_type_title'];
            } elseif (!empty($dataRecordProvider['role_title'])) {
                $codes['description'] = $dataRecordProvider['role_title'];
            } else {
                $fullCode = $codeTypesService->getCodeWithType($codes['code'], CodeTypesService::CODE_TYPE_SNOMED_CT);
                $codes['description'] = $codeTypesService->lookup_code_description($fullCode);
            }

            if (empty($codes['description'])) {
                $codes['description'] = xlt('Healthcare professional');
            }

            return UtilsService::createCodeableConcept([$codes['code'] => $codes]);
        }

        // Try to map role to SNOMED CT code
        if (!empty($dataRecordProvider['role'])) {
            $role = strtolower((string)$dataRecordProvider['role']);
            if (isset($roleMapping[$role])) {
                $codes = [
                    'code' => $roleMapping[$role],
                    'system' => self::CARE_TEAM_MEMBER_FUNCTION_SYSTEM,
                    'description' => $dataRecordProvider['role_title'] ?? xlt($dataRecordProvider['role'])
                ];
                return UtilsService::createCodeableConcept([$codes['code'] => $codes]);
            }
        }

        // Default to generic healthcare professional code
        $codes = [
            'code' => '223366009', // Healthcare professional (general)
            'system' => self::CARE_TEAM_MEMBER_FUNCTION_SYSTEM,
            'description' => $dataRecordProvider['role_title'] ?? xlt('Healthcare professional')
        ];

        return UtilsService::createCodeableConcept([$codes['code'] => $codes]);
    }

    /**
     * Create role CodeableConcept for an organization participant
     */
    private function createOrganizationRoleCodeableConcept($dataRecordFacility, $codeTypesService)
    {
        // Default to healthcare facility code - ensure no prefix
        $defaultCode = '43741000'; // Healthcare facility (general)

        if (!empty($dataRecordFacility['facility_taxonomy'])) {
            // Ensure we're using clean SNOMED codes without prefixes
            $code = preg_replace('/^SNOMED-CT:/', '', (string)$dataRecordFacility['facility_taxonomy']);

            $codes = [
                'code' => $code,
                'system' => self::CARE_TEAM_MEMBER_FUNCTION_SYSTEM,
                'description' => null
            ];

            // Validate the code is numeric and looks like SNOMED
            if (!preg_match('/^\d+$/', (string) $code)) {
                $codes['code'] = $defaultCode;
            }
        } else {
            $codes = [
                'code' => $defaultCode,
                'system' => self::CARE_TEAM_MEMBER_FUNCTION_SYSTEM,
                'description' => xlt('Healthcare facility')
            ];
        }

        // Get description
        $fullCode = $codeTypesService->getCodeWithType($codes['code'], CodeTypesService::CODE_TYPE_SNOMED_CT);
        $codes['description'] = $codeTypesService->lookup_code_description($fullCode);

        if (empty($codes['description'])) {
            $codes['description'] = xlt('Healthcare facility');
        }

        return UtilsService::createCodeableConcept([$codes['code'] => $codes]);
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     *
     * @param  array<string, ISearchField> $openEMRSearchParameters OpenEMR search fields
     * @return ProcessingResult
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        $processingResult = $this->careTeamService->getAll($openEMRSearchParameters, true);
        return $processingResult;
    }

    /**
     * Create Provenance resource for the Care Team
     */
    public function createProvenanceResource($dataRecord = [], $encode = false)
    {
        if (!($dataRecord instanceof FHIRCareTeam)) {
            throw new \BadMethodCallException("Data record should be correct instance class");
        }
        $fhirProvenanceService = new FhirProvenanceService();
        $fhirProvenance = $fhirProvenanceService->createProvenanceForDomainResource($dataRecord);
        if ($encode) {
            return json_encode($fhirProvenance);
        } else {
            return $fhirProvenance;
        }
    }

    public function getSupportedVersions(): array
    {
        // version 3.1.1 DOES NOT support RelatedPerson as care team members so we can't compatible across all versions
        if ($this->getHighestCompatibleUSCoreProfileVersion() == self::PROFILE_VERSION_3_1_1) {
            return self::PROFILE_VERSIONS_V1;
        } else {
            return self::PROFILE_VERSIONS_V2;
        }
    }

    /**
     * Get profile URIs for US Core 8.0
     */
    public function getProfileURIs(): array
    {
        return $this->getProfileForVersions(self::USCGI_PROFILE_URI, $this->getSupportedVersions());
    }

    /**
     * Get patient context search field definition
     */
    public function getPatientContextSearchField(): FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition(
            'patient',
            SearchFieldType::REFERENCE,
            [new ServiceField('puuid', ServiceField::TYPE_UUID)]
        );
    }

    public function populateProviderTeamMembers(FHIRCareTeam $careTeamResource, array $dataRecord, CodeTypesService $codeTypesService)
    {
        if (!empty($dataRecord['providers'])) {
            foreach ($dataRecord['providers'] as $dataRecordProvider) {
                $participant = new FHIRCareTeamParticipant();

                // Set period if provider_since is available
                if (!empty($dataRecordProvider['provider_since'])) {
                    $period = new FHIRPeriod();
                    $period->setStart($dataRecordProvider['provider_since']);
                    $participant->setPeriod($period);
                }

                // Set role (required by US Core)
                $roleCodeableConcept = $this->createRoleCodeableConcept(
                    $dataRecordProvider,
                    $codeTypesService
                );
                $participant->addRole($roleCodeableConcept);

                // Set member reference (required by US Core)
                $participant->setMember(
                    UtilsService::createRelativeReference("Practitioner", $dataRecordProvider['provider_uuid'])
                );

                // Set onBehalfOf if facility is present (US Core allows this for Practitioners)
                if (!empty($dataRecordProvider['facility_uuid'])) {
                    $participant->setOnBehalfOf(
                        UtilsService::createRelativeReference("Organization", $dataRecordProvider['facility_uuid'])
                    );
                }

                $careTeamResource->addParticipant($participant);
            }
        }
    }

    public function populateFacilityTeamMembers(FHIRCareTeam $careTeamResource, array $dataRecord, CodeTypesService $codeTypesService)
    {
        // Add organizations as participants (facilities)
        if (!empty($dataRecord['facilities'])) {
            foreach ($dataRecord['facilities'] as $dataRecordFacility) {
                $organization = new FHIRCareTeamParticipant();

                // Set member reference for organization
                $organization->setMember(
                    UtilsService::createRelativeReference("Organization", $dataRecordFacility['uuid'])
                );

                // Set role for organization
                $roleCodeableConcept = $this->createOrganizationRoleCodeableConcept(
                    $dataRecordFacility,
                    $codeTypesService
                );
                $organization->addRole($roleCodeableConcept);

                $careTeamResource->addParticipant($organization);
            }
        }
    }
    public function populateRelatedPersonTeamMembers(FHIRCareTeam $careTeamResource, array $dataRecord, CodeTypesService $codeTypesService)
    {
        if ($this->getHighestCompatibleUSCoreProfileVersion() == self::PROFILE_VERSION_3_1_1) {
            return;
        }
        // Add RelatedPerson as participants (contacts)
        // for now we only support RelatedPerson type contacts but this could be expanded in future
        if (!empty($dataRecord['contacts'])) {
            foreach ($dataRecord['contacts'] as $person) {
                $participant = new FHIRCareTeamParticipant();

                // Set member reference for organization
                $participant->setMember(
                    UtilsService::createRelativeReference("RelatedPerson", $person['uuid'])
                );
                $roleMapping = $this->getRoleMappings();

                if (!empty($person['role'])) {
                    $role = strtolower((string)$person['role']);
                    if (isset($roleMapping[$role])) {
                        $codes = [
                            'code' => $roleMapping[$role],
                            'system' => self::CARE_TEAM_MEMBER_FUNCTION_SYSTEM,
                            'description' => $person['role_title'] ?? xlt($person['role'])
                        ];
                        $participant->addRole(UtilsService::createCodeableConcept([$codes['code'] => $codes]));
                    }
                } else {
                    $code = '407542009'; // if we don't have anything to match then we default to informal caregiver as the safest approach
                    $fullCode = CodeTypesService::CODE_TYPE_SNOMED_CT . ':' . $code;
                    $description = $codeTypesService->lookup_code_description($fullCode);
                    $participant->addRole(
                        UtilsService::createCodeableConcept(
                            [
                            $code => [
                            'code' => $code
                            ,'system' => self::CARE_TEAM_MEMBER_FUNCTION_SYSTEM
                            ,'description' => !empty($description) ? $description : $person['role_title']
                            ]
                            ]
                        )
                    );
                }

                $careTeamResource->addParticipant($participant);
            }
        }
    }

    public function populateManagingOrganization(FHIRCareTeam $careTeamResource, array $dataRecord)
    {
        // Set managing organization if primary facility is set
        if (!empty($dataRecord['primary_facility_uuid'])) {
            $careTeamResource->addManagingOrganization(
                [
                UtilsService::createRelativeReference("Organization", $dataRecord['primary_facility_uuid'])
                ]
            );
        }
    }

    /**
     * in FHIR terminology the patient is part of the care team even though the resource still populates the identified patient
     *
     * @param  FHIRCareTeam     $careTeamResource
     * @param  array            $dataRecord
     * @param  CodeTypesService $codeTypesService
     * @return void
     */
    private function populatePatientMember(FHIRCareTeam $careTeamResource, array $dataRecord, CodeTypesService $codeTypesService)
    {
        $participant = new FHIRCareTeamParticipant();

        // Set member reference for organization
        $participant->setMember(
            UtilsService::createRelativeReference("Patient", $dataRecord['puuid'])
        );
        $code = '116154003'; // if we don't have anything to match then we default to informal caregiver as the safest approach
        $fullCode = CodeTypesService::CODE_TYPE_SNOMED_CT . ':' . $code;
        $description = $codeTypesService->lookup_code_description($fullCode);
        $participant->addRole(
            UtilsService::createCodeableConcept(
                [
                $code => [
                'code' => $code
                ,'system' => self::CARE_TEAM_MEMBER_FUNCTION_SYSTEM
                ,'description' => !empty($description) ? $description : 'Patient (person)'
                ]
                ]
            )
        );
        $careTeamResource->addParticipant($participant);
    }
}
