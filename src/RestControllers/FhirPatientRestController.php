<?php
/**
 * FhirPatientRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


namespace OpenEMR\RestControllers;

use OpenEMR\Services\PatientService;
use OpenEMR\Services\FhirResourcesService;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;

class FhirPatientRestController
{
    private $patientService;
    private $fhirService;

    public function __construct($pid)
    {
        $this->patientService = new PatientService();
        $this->patientService->setPid($pid);
        $this->fhirService = new FhirResourcesService();
    }

    public function post($fhirJson)
    {
        $data = $this->fhirService->parsePatientResource($fhirJson);

        $validationResult = $this->patientService->validate($data);
        $validationHandlerResult = RestControllerHelper::validationHandler($validationResult);
        if (is_array($validationHandlerResult)) {
            return $validationHandlerResult;
        }

        $fhirserviceResult = $this->patientService->insert($data);
        return RestControllerHelper::responseHandler($fhirserviceResult, array("pid" => $fhirserviceResult), 201);
    }

    public function put($pid, $fhirJson)
    {
        $data = $this->fhirService->parsePatientResource($fhirJson);

        $validationResult = $this->patientService->validate($data);
        $validationHandlerResult = RestControllerHelper::validationHandler($validationResult);
        if (is_array($validationHandlerResult)) {
            return $validationHandlerResult;
        }

        $fhirserviceResult = $this->patientService->update($pid, $data);
        return RestControllerHelper::responseHandler($fhirserviceResult, array("pid" => $pid), 200);
    }

    public function getOne()
    {
        $oept = $this->patientService->getOne();
        $pid = $this->patientService->getPid();
        if ($oept) {
            $resource = $this->fhirService->createPatientResource($pid, $oept, false);
            $statusCode = 200;
        } else {
            $statusCode = 404;
            $resource = $this->fhirService->createUnknownResource($pid, false);
        }

        return RestControllerHelper::responseHandler($resource, null, $statusCode);
    }

    public function getAll($search)
    {
        $resourceURL = \RestConfig::$REST_FULL_URL;
        if (strpos($resourceURL, '?') > 0) {
            $resourceURL = strstr($resourceURL, '?', true);
        }

        $searchParam = array(
            'name' => $search['name'],
            'dob' => $search['birthdate'],
            'city' => $search['address-city'],
            'state' => $search['address-state'],
            'postal_code' => $search['address-postalcode'],
            'phone_contact' => $search['phone'],
            'address' => $search['address'],
            'sex' => $search['gender'],
            'country_code' => $search['address-country']
        );

        $searchResult = $this->patientService->getAll($searchParam);
        if ($searchResult === false) {
            http_response_code(404);
            exit;
        }
        $entries = array();
        foreach ($searchResult as $oept) {
            $entryResource = $this->fhirService->createPatientResource($oept['pid'], $oept, false);
            $entry = array(
                'fullUrl' => $resourceURL . "/" . $oept['pid'],
                'resource' => $entryResource
            );
            $entries[] = new FHIRBundleEntry($entry);
        }
        $searchResult = $this->fhirService->createBundle('Patient', $entries, false);
        return RestControllerHelper::responseHandler($searchResult, null, 200);
    }
}
