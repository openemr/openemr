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
use OpenEMR\FHIR\R4\FHIRElement\FHIRContactPoint;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRResource\FHIRCareTeam\FHIRCareTeamParticipant;
use OpenEMR\Services\CareTeamService;

class FhirCareTeamService extends FhirServiceBase
{
    /**
     * @var CareTeamService
     */
    private $careTeamService;

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
        return  [];
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

        $meta = array('versionId' => '1', 'lastUpdated' => gmdate('c'));
        $careTeamResource->setMeta($meta);

        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $careTeamResource->setId($id);

        $careTeamResource->setStatus("active");

        $careTeamResource->setSubject("Patient/" . $dataRecord['puuid']);

        foreach ($dataRecord['providers'] as $pruuid) {
            $provider = new FHIRCareTeamParticipant();
            $provider->setMember("Practitioner/" . $pruuid);
            $careTeamResource->addParticipant($provider);
        }

        foreach ($dataRecord['facilities'] as $ouuid) {
            $organization = new FHIRCareTeamParticipant();
            $organization->setMember("Organization/" . $ouuid);
            $careTeamResource->addParticipant($organization);
        }

        if ($encode) {
            return json_encode($careTeamResource);
        } else {
            return $careTeamResource;
        }
    }

    /**
     * Performs a FHIR CareTeam Resource lookup by FHIR Resource ID
     *
     * @param $fhirResourceId //The OpenEMR record's FHIR CareTeam Resource ID.
     */
    public function getOne($fhirResourceId)
    {
        $processingResult = $this->careTeamService->getOne($fhirResourceId);
        if (!$processingResult->hasErrors()) {
            if (count($processingResult->getData()) > 0) {
                $openEmrRecord = $processingResult->getData()[0];
                $fhirRecord = $this->parseOpenEMRRecord($openEmrRecord);
                $processingResult->setData([]);
                $processingResult->addData($fhirRecord);
            }
        }
        return $processingResult;
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     *
     * @param  array openEMRSearchParameters OpenEMR search fields
     * @return ProcessingResult
     */
    public function searchForOpenEMRRecords($openEMRSearchParameters)
    {
        return $this->careTeamService->getAll($openEMRSearchParameters, false);
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
        // TODO: If Required in Future
    }
}
