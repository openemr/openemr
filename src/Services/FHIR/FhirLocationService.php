<?php

/**
 * FhirLocationService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRLocation;
use OpenEMR\FHIR\R4\FHIRElement\FHIRContactPoint;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\Services\LocationService;

class FhirLocationService extends FhirServiceBase
{
    /**
     * @var LocationService
     */
    private $locationService;

    public function __construct()
    {
        parent::__construct();
        $this->locationService = new LocationService();
    }

    /**
     * Returns an array mapping FHIR Location Resource search parameters to OpenEMR Location search parameters
     * @return array The search parameters
     */
    protected function loadSearchParameters()
    {
        return  [];
    }

    /**
     * Parses an OpenEMR location record, returning the equivalent FHIR Location Resource
     *
     * @param array $dataRecord The source OpenEMR data record
     * @param boolean $encode Indicates if the returned resource is encoded into a string. Defaults to false.
     * @return FHIRLocation
     */
    public function parseOpenEMRRecord($dataRecord = array(), $encode = false)
    {
        $locationResource = new FHIRLocation();

        $meta = array('versionId' => '1', 'lastUpdated' => gmdate('c'));
        $locationResource->setMeta($meta);

        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $locationResource->setId($id);

        $locationResource->setStatus("active");

        $locationResource->setAddress(array(
            'line' => [$dataRecord['street']],
            'city' => $dataRecord['city'],
            'state' => $dataRecord['state'],
            'postalCode' => $dataRecord['postal_code'],
        ));

        if (!empty($dataRecord['name'] && $dataRecord['name'] != "`s Home")) {
            $locationResource->setName($dataRecord['name']);
        }

        if (!empty($dataRecord['phone'])) {
            $phone = new FHIRContactPoint();
            $phone->setSystem('phone');
            $phone->setValue($dataRecord['phone']);
            $locationResource->addTelecom($phone);
        }

        if (!empty($dataRecord['fax'])) {
            $fax = new FHIRContactPoint();
            $fax->setSystem('fax');
            $fax->setValue($dataRecord['fax']);
            $locationResource->addTelecom($fax);
        }

        if (!empty($dataRecord['website'])) {
            $url = new FHIRContactPoint();
            $url->setSystem('website');
            $url->setValue($dataRecord['website']);
            $locationResource->addTelecom($url);
        }

        if (!empty($dataRecord['email'])) {
            $email = new FHIRContactPoint();
            $email->setSystem('email');
            $email->setValue($dataRecord['email']);
            $locationResource->addTelecom($email);
        }

        if ($encode) {
            return json_encode($locationResource);
        } else {
            return $locationResource;
        }
    }

    /**
     * Performs a FHIR Location Resource lookup by FHIR Resource ID
     *
     * @param $fhirResourceId //The OpenEMR record's FHIR Location Resource ID.
     */
    public function getOne($fhirResourceId)
    {
        $processingResult = $this->locationService->getOne($fhirResourceId);
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
        return $this->locationService->getAll($openEMRSearchParameters, false);
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
