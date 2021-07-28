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

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRLocation;
use OpenEMR\FHIR\R4\FHIRElement\FHIRContactPoint;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\Services\LocationService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Validators\ProcessingResult;

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
        return  [
            '_id' => new FhirSearchParameterDefinition('uuid', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)])
        ];
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

        // TODO: what is this `s Home thing, seems really wierd... and its not documented
        if (!empty($dataRecord['name'] && $dataRecord['name'] != "`s Home")) {
            $locationResource->setName($dataRecord['name']);
        } else {
            $locationResource->setName(UtilsService::createDataMissingExtension());
        }

        // TODO: @brady.miller is this the right security ACL for a facilities organization?
        if ($this->shouldIncludeContactInformationForLocationType($dataRecord['type'])) {
            $locationResource->setAddress(UtilsService::createAddressFromRecord($dataRecord));

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
        }

        if ($encode) {
            return json_encode($locationResource);
        } else {
            return $locationResource;
        }
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     *
     * @param  array openEMRSearchParameters OpenEMR search fields
     * @param $puuidBind - NOT USED
     * @return ProcessingResult
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters, $puuidBind = null): ProcessingResult
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

    private function shouldIncludeContactInformationForLocationType($type)
    {
        if ($type == 'patient')
        {
            // only those with access to a patient's demographic information can get their data
            return AclMain::aclCheckCore("patients", "demo") !== false;
        }
        else if ($type == 'user')
        {
            // only those with access to the user information can get address information about a user.
            return AclMain::aclCheckCore('admin', 'users') !== false;
        }
        else {
            // facilities we just let all contact information be displayed for the location.
            return true;
        }
    }
}
