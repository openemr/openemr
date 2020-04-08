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


namespace OpenEMR\RestControllers\FHIR;

use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\Services\FHIR\FhirPatientService;
use OpenEMR\Services\FHIR\FhirValidationService;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;

class FhirPatientRestController
{
    private $fhirPatientService;
    private $fhirService;
    private $fhirValidate;

    public function __construct($pid)
    {
        $this->fhirService = new FhirResourcesService();
        $this->fhirPatientService = new FhirPatientService();
        $this->fhirPatientService->setId($pid);
        $this->fhirValidate = new FhirValidationService();
    }

    public function post($fhirJson)
    {
        $fhirValidate = $this->fhirValidate->validate($fhirJson);
        if (!empty($fhirValidate)) {
            return RestControllerHelper::responseHandler($fhirValidate, null, 404);
        }
        $data = $this->fhirPatientService->parsePatientResource($fhirJson);

        $validationResult = $this->fhirPatientService->validate($data);
        $validationHandlerResult = RestControllerHelper::validationHandler($validationResult);
        if (is_array($validationHandlerResult)) {
            return $validationHandlerResult;
        }

        $fhirserviceResult = $this->fhirPatientService->insert($data);
        return RestControllerHelper::responseHandler($fhirserviceResult, array("pid" => $fhirserviceResult), 201);
    }

    public function put($pid, $fhirJson)
    {
        $fhirValidate = $this->fhirValidate->validate($fhirJson);
        if (!empty($fhirValidate)) {
            return RestControllerHelper::responseHandler($fhirValidate, null, 404);
        }
        $data = $this->fhirPatientService->parsePatientResource($fhirJson);

        $validationResult = $this->fhirPatientService->validateUpdate($pid, $data);
        $validationHandlerResult = RestControllerHelper::validationHandler($validationResult);
        if (is_array($validationHandlerResult)) {
            return $validationHandlerResult;
        }

        $fhirserviceResult = $this->fhirPatientService->update($pid, $data);
        return RestControllerHelper::responseHandler($fhirserviceResult, array("pid" => $pid), 200);
    }

    public function getOne()
    {
        $oept = $this->fhirPatientService->getOne();
        $pid = $this->fhirPatientService->getId();
        if ($oept) {
            $resource = $this->fhirPatientService->createPatientResource($pid, $oept, false);
            $statusCode = 200;
        } else {
            $statusCode = 404;
            $resource = $this->fhirValidate->operationOutcomeResourceService(
                'error',
                'invalid',
                false,
                "Resource Id $pid does not exist"
            );
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
            'DOB' => $search['birthdate'],
            'city' => $search['address-city'],
            'state' => $search['address-state'],
            'postal_code' => $search['address-postalcode'],
            'phone_contact' => $search['phone'],
            'address' => $search['address'],
            'sex' => $search['gender'],
            'country_code' => $search['address-country']
        );

        $searchResult = $this->fhirPatientService->getAll($searchParam);
        if ($searchResult === false) {
            http_response_code(404);
            exit;
        }
        $entries = array();
        foreach ($searchResult as $oept) {
            $entryResource = $this->fhirPatientService->createPatientResource($oept['pid'], $oept, false);
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
