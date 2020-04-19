<?php

namespace OpenEMR\RestControllers\FHIR;

use OpenEMR\Services\FHIR\FhirObservationService;
use OpenEMR\Services\FHIR\FhirValidationService;
use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;

class FhirObservationRestController
{
    private $fhirObservationService;
    private $fhirService;
    private $fhirValidate;

    public function __construct()
    {
        $this->fhirObservationService = new FhirObservationService();
        $this->fhirValidate = new FhirValidationService();
        $this->fhirService = new FhirResourcesService();
    }

    public function getAll($search)
    {
        $resourceURL = \RestConfig::$REST_FULL_URL;
        if (strpos($resourceURL, '?') > 0) {
            $resourceURL = strstr($resourceURL, '?', true);
        }

        $searchParam = array(
            'pid' => $search['patient'],
            'category' => $search['category'],
            'date' => $search['date'],
            'code' => $search['code'] ? explode(',', $search['code']) : null
        );
        $code = array();

        if ($searchParam['code']) {
            $code = array(
                "85353 - 1" => 'vitals',
                "29463 - 7" => 'weight',
                "8302 - 2" => 'height',
                "85354-9" => 'bp',
                "8310 - 5"  => 'temperature',
                "8867 - 4"  => 'pulse',
                "9279 - 1"  => 'respiration',
                "39156 - 5"  => 'BMI',
                "9843 - 4"  => 'head_circ',
                "2708 - 6"  => 'oxygen_saturation',
            );
        }

        $searchResult = $this->fhirObservationService->getAll($searchParam);
        if ($searchResult !== false) {
            $entries = array();
            foreach ($searchResult as $profile) {
                $id = 'vitals-' . $profile['form_id'];
                $profile_data = $this->fhirObservationService->getOne($id);
                $entryResource = $this->fhirObservationService->createObservationResource($id, $profile_data, false);
                if ($this->checkCode($code, $searchParam['code'], $id)) {
                    $entry = array(
                        'fullUrl' => $resourceURL . "/" . $id,
                        'resource' => $entryResource
                    );
                    $entries[] = new FHIRBundleEntry($entry);
                }
                foreach ($entryResource->hasMember as $profiles) {
                    $ref = explode("/", $profiles->reference);
                    if (empty($code) || $this->checkCode($code, $searchParam['code'], $ref[1])) {
                        $profile_data = $this->fhirObservationService->getOne($ref[1]);
                        $resource = $this->fhirObservationService->createObservationResource(
                            $ref[1],
                            $profile_data,
                            false
                        );
                        $entry = array(
                            'fullUrl' => $resourceURL . "/" . $ref[1],
                            'resource' => $resource
                        );
                        $entries[] = new FHIRBundleEntry($entry);
                    }
                };
            }
            $searchResult = $this->fhirService->createBundle('Observation', $entries, false);
            $statusCode = 200;
        } else {
            $statusCode = 400;
            $searchResult = $this->fhirValidate->operationOutcomeResourceService(
                'error',
                'invalid',
                false,
                "Invalid Parameter"
            );
        }
        return RestControllerHelper::responseHandler($searchResult, null, $statusCode);
    }

    public function getOne($id)
    {
        $profile_data = $this->fhirObservationService->getOne($id);
        if ($profile_data) {
            $resource = $this->fhirObservationService->createObservationResource($id, $profile_data, false);
            $statusCode = 200;
        } else {
            $statusCode = 404;
            $resource = $this->fhirValidate->operationOutcomeResourceService(
                'error',
                'invalid',
                false,
                "Resource Id $id does not exist"
            );
        }

        return RestControllerHelper::responseHandler($resource, null, $statusCode);
    }

    private function checkCode($code, $searchParam, $param)
    {
        $param = explode("-", $param);
        if (is_array($searchParam)) {
            foreach ($searchParam as $search) {
                if ($code[$search] == $param[0]) {
                    return true;
                }
            }
        }
        return false;
    }
}
