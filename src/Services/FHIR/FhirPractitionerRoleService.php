<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPractitionerRole;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\Services\PractitionerRoleService;

/**
 * FHIR PractitionerRole Service
 *
 * @coversDefaultClass OpenEMR\Services\FHIR\FhirPractitionerRoleService
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */
class FhirPractitionerRoleService extends FhirServiceBase
{
    /**
     * @var PractitionerRoleService
     */
    private $practitionerRoleService;

    public function __construct()
    {
        parent::__construct();
        $this->practitionerRoleService = new PractitionerRoleService();
    }

    /**
     * Returns an array mapping FHIR PractitionerRole Resource search parameters to OpenEMR PractitionerRole search parameters
     * @return array The search parameters
     */
    protected function loadSearchParameters()
    {
        return  [
            "specialty" => ["specialty_code"],
            "practitioner" => ["user_name"],
        ];
    }

    /**
     * Parses an OpenEMR practitionerRole record, returning the equivalent FHIR PractitionerRole Resource
     *
     * @param array $dataRecord The source OpenEMR data record
     * @param boolean $encode Indicates if the returned resource is encoded into a string. Defaults to false.
     * @return FHIRPractitionerRole
     */
    public function parseOpenEMRRecord($dataRecord = array(), $encode = false)
    {
        $practitionerRoleResource = new FHIRPractitionerRole();

        $meta = array('versionId' => '1', 'lastUpdated' => gmdate('c'));
        $practitionerRoleResource->setMeta($meta);

        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $practitionerRoleResource->setId($id);

        if (!empty($dataRecord['user_uuid'])) {
            $practitioner = new FHIRReference(
                [
                    'reference' => 'Practitioner/' . $dataRecord['user_uuid'],
                    'display' => $dataRecord['user_name']
                ]
            );
            $practitionerRoleResource->setPractitioner($practitioner);
        }

        if (!empty($dataRecord['facility_uuid'])) {
            $organization = new FHIRReference([
                'reference' => 'Organization/' . $dataRecord['facility_uuid'],
                'display' => $dataRecord['facility_name']
            ]);
            $practitionerRoleResource->setOrganization($organization);
        }


        if (!empty($dataRecord['role_code'])) {
            $reason = new FHIRCodeableConcept();
            $reason->addCoding($dataRecord['role_code']);
            $reason->setText($dataRecord['role']);
            $practitionerRoleResource->addCode($reason);
        }

        if (!empty($dataRecord['specialty_code'])) {
            $reason = new FHIRCodeableConcept();
            $reason->addCoding($dataRecord['specialty_code']);
            $reason->setText($dataRecord['specialty']);
            $practitionerRoleResource->addCode($reason);
        }

        if ($encode) {
            return json_encode($practitionerRoleResource);
        } else {
            return $practitionerRoleResource;
        }
    }

    /**
     * Parses a FHIR PractitionerRole Resource, returning the equivalent OpenEMR practitionerRole record.
     *
     * @param array $fhirResource The source FHIR resource
     * @return array a mapped OpenEMR data record (array)
     */
    public function parseFhirResource($fhirResource = array())
    {
    }


    /**
     * Inserts an OpenEMR record into the sytem.
     *
     * @param array $openEmrRecord OpenEMR practitionerRole record
     * @return ProcessingResult
     */
    public function insertOpenEMRRecord($openEmrRecord)
    {
    }


    /**
     * Updates an existing OpenEMR record.
     *
     * @param $fhirResourceId //The OpenEMR record's FHIR Resource ID.
     * @param $updatedOpenEMRRecord //The "updated" OpenEMR record.
     * @return ProcessingResult
     */
    public function updateOpenEMRRecord($fhirResourceId, $updatedOpenEMRRecord)
    {
    }

    /**
     * Performs a FHIR PractitionerRole Resource lookup by FHIR Resource ID
     * @param $fhirResourceId //The OpenEMR record's FHIR PractitionerRole Resource ID.
     */
    public function getOne($fhirResourceId)
    {
        $processingResult = $this->practitionerRoleService->getOne($fhirResourceId);
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
     * @param array openEMRSearchParameters OpenEMR search fields
     * @return ProcessingResult
     */
    public function searchForOpenEMRRecords($openEMRSearchParameters)
    {
        return $this->practitionerRoleService->getAll($openEMRSearchParameters, false);
    }
    public function createProvenanceResource($dataRecord = array(), $encode = false)
    {
        // TODO: If Required in Future
    }
}
