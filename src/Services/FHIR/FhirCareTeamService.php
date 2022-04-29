<?php

/**
 * FhirCareTeamService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCareTeam;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRResource\FHIRCareTeam\FHIRCareTeamParticipant;
use OpenEMR\Services\CareTeamService;
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\FHIR\Traits\BulkExportSupportAllOperationsTrait;
use OpenEMR\Services\FHIR\Traits\FhirBulkExportDomainResourceTrait;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Validators\ProcessingResult;

class FhirCareTeamService extends FhirServiceBase implements IResourceUSCIGProfileService, IFhirExportableResourceService
{
    use FhirServiceBaseEmptyTrait;
    use BulkExportSupportAllOperationsTrait;
    use FhirBulkExportDomainResourceTrait;

    // @see http://hl7.org/fhir/R4/valueset-care-team-status.html
    private const CARE_TEAM_STATUS_ACTIVE = "active";
    private const CARE_TEAM_STATUS_PROPOSED = "proposed";
    private const CARE_TEAM_STATUS_SUSPENDED = "suspended";
    private const CARE_TEAM_STATUS_INACTIVE = "inactive";
    private const CARE_TEAM_STATUS_ENTERED_IN_ERROR = "entered-in-error";
    private const CARE_TEAM_STATII = [self::CARE_TEAM_STATUS_ACTIVE, self::CARE_TEAM_STATUS_INACTIVE
        , self::CARE_TEAM_STATUS_PROPOSED, self::CARE_TEAM_STATUS_SUSPENDED, self::CARE_TEAM_STATUS_ENTERED_IN_ERROR];

    /**
     * @var CareTeamService
     */
    private $careTeamService;


    const USCGI_PROFILE_URI = 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-careteam';

    public function __construct()
    {
        parent::__construct();
        $this->careTeamService = new CareTeamService();
    }

    /**
     * Returns an array mapping FHIR CareTeam Resource search parameters to OpenEMR CareTeam search parameters
     * @return array The search parameters
     */
    protected function loadSearchParameters()
    {
        return  [
            'patient' => $this->getPatientContextSearchField(),
            'status' => new FhirSearchParameterDefinition('status', SearchFieldType::TOKEN, ['care_team_status']),
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)]),
        ];
    }

    /**
     * Parses an OpenEMR careTeam record, returning the equivalent FHIR CareTeam Resource
     *
     * @param array $dataRecord The source OpenEMR data record
     * @param boolean $encode Indicates if the returned resource is encoded into a string. Defaults to false.
     * @return FHIRCareTeam
     */
    public function parseOpenEMRRecord($dataRecord = array(), $encode = false)
    {
        $careTeamResource = new FHIRCareTeam();

        $fhirMeta = new FHIRMeta();
        $fhirMeta->setVersionId('1');
        $fhirMeta->setLastUpdated(gmdate('c'));
        $careTeamResource->setMeta($fhirMeta);

        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $careTeamResource->setId($id);

        if (array_search($dataRecord['care_team_status'], self::CARE_TEAM_STATII) !== false) {
            $careTeamResource->setStatus($dataRecord['care_team_status']);
        } else {
            // default is active
            $careTeamResource->setStatus(self::CARE_TEAM_STATUS_ACTIVE);
        }


        $careTeamResource->setSubject(UtilsService::createRelativeReference("Patient", $dataRecord['puuid']));
        $codeTypesService = new CodeTypesService();

        if (!empty($dataRecord['providers'])) {
            foreach ($dataRecord['providers'] as $dataRecordProviderList) {
                $provider = new FHIRCareTeamParticipant();

                // provider can have more than facility matching... we are only going to grab the first facility for now
                $dataRecordProvider = end($dataRecordProviderList);

                if (!empty($dataRecordProvider['physician_type_codes'])) {
                    $codes = $codeTypesService->parseCode($dataRecordProvider['physician_type_codes']);
                    $codes['description'] = $codeTypesService->lookup_code_description($dataRecordProvider['physician_type_title']) ?? xlt($dataRecordProvider['physician_type']);
                    if (empty($codes['description'])) {
                        $codes['description'] = xlt($dataRecordProvider['role_title']);
                    }
                    // The OID is the value set system but the larger system is SNOMED_CT so we will put that as the system.
                    $codes['system'] = FhirCodeSystemConstants::SNOMED_CT;
//                    $codes['system'] = FhirCodeSystemConstants::CARE_TEAM_MEMBER_FUNCTION_SNOMEDCT;
                    $role = UtilsService::createCodeableConcept([$codes['code'] => $codes]);
                } else {
                    // need to provide the data absent reason
                    $role = UtilsService::createDataAbsentUnknownCodeableConcept();
                }

                // US Core only allows onBehalfOf to be populated if participant is a practitioner
                if (!empty($dataRecordProvider['facility_uuid'])) {
                    $provider->setOnBehalfOf(UtilsService::createRelativeReference("Organization", $dataRecordProvider['facility_uuid']));
                }

                $provider->addRole($role);
                $provider->setMember(UtilsService::createRelativeReference("Practitioner", $dataRecordProvider['provider_uuid']));
                $careTeamResource->addParticipant($provider);
            }
        }

        // facilities have to use SNOMED_CT for our code system since NUCC is no longer a valid code system for FHIR.
        if (!empty($dataRecord['facilities'])) {
            foreach ($dataRecord['facilities'] as $dataRecordFacility) {
                $organization = new FHIRCareTeamParticipant();
                $organization->setMember(UtilsService::createRelativeReference("Organization", $dataRecordFacility['uuid']));

                $roleCoding = new FHIRCoding();
                if (empty($dataRecordFacility['facility_taxonomy'])) {
                    $role = UtilsService::createDataAbsentUnknownCodeableConcept();
                } else {
                    $codes = [
                        'code' => $dataRecordFacility['facility_taxonomy']
                        ,'system' => FhirCodeSystemConstants::SNOMED_CT
                        ,'description' => null
                    ];
                    $fullCode = $codeTypesService->getCodeWithType($codes['code'], CodeTypesService::CODE_TYPE_SNOMED_CT);
                    $codes['description'] = $codeTypesService->lookup_code_description($fullCode);
                    if (empty($codes['description'])) {
                        $codes['description'] = xlt('Healthcare facility');
                    }
                    $role = UtilsService::createCodeableConcept([$codes['code'] => $codes]);
                }
                $organization->addRole($role);
                $careTeamResource->addParticipant($organization);
            }
        }

        if ($encode) {
            return json_encode($careTeamResource);
        } else {
            return $careTeamResource;
        }
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     *
     * @param  array openEMRSearchParameters OpenEMR search fields
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return ProcessingResult
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters, $puuidBind = null): ProcessingResult
    {
        return $this->careTeamService->getAll($openEMRSearchParameters, true, $puuidBind);
    }

    public function createProvenanceResource($dataRecord = array(), $encode = false)
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

    public function getProfileURIs(): array
    {
        return [self::USCGI_PROFILE_URI];
    }

    public function getPatientContextSearchField(): FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('patient', SearchFieldType::REFERENCE, [new ServiceField('puuid', ServiceField::TYPE_UUID)]);
    }
}
