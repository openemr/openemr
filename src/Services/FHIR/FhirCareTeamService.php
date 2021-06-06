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
use OpenEMR\FHIR\R4\FHIRElement\FHIRContactPoint;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRResource\FHIRCareTeam\FHIRCareTeamParticipant;
use OpenEMR\Services\CareTeamService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;

class FhirCareTeamService extends FhirServiceBase implements IResourceUSCIGProfileService
{
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
            'status' => new FhirSearchParameterDefinition('status', SearchFieldType::TOKEN, ['careteam_status']),
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

        if (array_search($dataRecord['status'], self::CARE_TEAM_STATII) !== false) {
            $careTeamResource->setStatus($dataRecord['status']);
        } else {
            // default is active
            $careTeamResource->setStatus(self::CARE_TEAM_STATUS_ACTIVE);
        }


        $careTeamResource->setSubject(UtilsService::createRelativeReference("Patient", $dataRecord['puuid']));


        foreach ($dataRecord['providers'] as $dataRecordProviderList) {
            $provider = new FHIRCareTeamParticipant();

            // provider can have more than facility matching... we are only going to grab the first facility for now
            $dataRecordProvider = end($dataRecordProviderList);

            if (!empty($dataRecordProvider['role_code'])) {
                $role = new FHIRCodeableConcept();
                $roleCoding = new FHIRCoding();
                $description = lookup_code_descriptions($dataRecordProvider['role_code']);
                if (empty($description)) { // if the NUCC code database is not installed and we get back an empty string
                    $description = xlt($dataRecordProvider['role_title']);
                }
                $codeParts = explode(':', $dataRecordProvider['role_code']);
                $code = end($codeParts);
                $roleCoding->setCode($code);
                $roleCoding->setDisplay($description);
                // our codes are NUCC codes in our system
                $roleCoding->setSystem(FhirCodeSystemUris::NUCC_PROVIDER);
            } else {
                // need to provide the data absent reason
                $role = new FHIRCodeableConcept();
                $roleCoding = new FHIRCoding();
                $roleCoding->setCode("unknown");
                $roleCoding->setDisplay(xlt("Unknown"));
                $roleCoding->setSystem(FhirCodeSystemUris::DATA_ABSENT_REASON);
            }

            // US Core only allows onBehalfOf to be populated if participant is a practitioner
            if (!empty($dataRecordProvider['facility_uuid'])) {
                $provider->setOnBehalfOf(UtilsService::createRelativeReference("Organization", $dataRecordProvider['facility_uuid']));
            }

            $role->addCoding($roleCoding);
            $provider->addRole($role);

            $provider->setMember(UtilsService::createRelativeReference("Practitioner", $dataRecordProvider['provider_uuid']));
            $careTeamResource->addParticipant($provider);
        }

        foreach ($dataRecord['facilities'] as $dataRecordFacility) {
            $organization = new FHIRCareTeamParticipant();
            $organization->setMember(UtilsService::createRelativeReference("Organization", $dataRecordFacility['uuid']));
            $role = new FHIRCodeableConcept();
            $roleCoding = new FHIRCoding();
            if (empty($dataRecordFacility['facility_taxonomy'])) {
                $roleCoding->setCode("unknown");
                $roleCoding->setDisplay(xlt("Unknown"));
                $roleCoding->setSystem(FhirCodeSystemUris::DATA_ABSENT_REASON);
            } else {
                $description = lookup_code_descriptions($dataRecordFacility['facility_taxonomy']);
                if (empty($description)) { // if the NUCC code database is not installed and we get back an empty string
                    $description = null;
                }
                // TODO: @adunsulag check with @brady.miller @sjpadgett on how facility_taxonomy has a limit on the column size.
                $codeParts = explode(':', $dataRecordFacility['facility_taxonomy']);
                $code = end($codeParts);
                $roleCoding->setCode($code);
                $roleCoding->setDisplay($description);
                if ($codeParts[0] === "SNOMED-CT") {
                    $roleCoding->setSystem(FhirCodeSystemUris::SNOMED_CT);
                } else {
                    // NUCC codes in OpenEMR don't appear to be prefixed with anything and that's the only option for
                    // a facility taxonomy here so we leave it at this.
                    $roleCoding->setSystem(FhirCodeSystemUris::NUCC_PROVIDER);
                }
            }

            $role->addCoding($roleCoding);

            $organization->addRole($role);
            $careTeamResource->addParticipant($organization);
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
    public function searchForOpenEMRRecords($openEMRSearchParameters, $puuidBind = null)
    {
        return $this->careTeamService->getAll($openEMRSearchParameters, true, $puuidBind);
    }

    public function parseFhirResource($fhirResource = array())
    {
        // TODO: If Required in Future
    }

    public function insertOpenEMRRecord($openEmrRecord)
    {
        // TODO: If Required in Future
    }

    public function updateOpenEMRRecord($fhirResourceId, $updatedOpenEMRRecord)
    {
        // TODO: If Required in Future
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
