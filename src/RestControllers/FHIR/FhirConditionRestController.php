<?php

namespace OpenEMR\RestControllers\FHIR;

use OpenEMR\Services\FHIR\FhirConditionService;
use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;

class FhirConditionRestController
{
    private $fhirConditionService;
    private $fhirService;
    
    public function __construct($id)
    {
        $this->fhirConditionService = new FhirConditionService();
        $this->fhirConditionService->setId($id);
        $this->fhirService = new FhirResourcesService();
    }
    
    public function getAll($search)
    {
        $result = $this->fhirConditionService->getAll(array('patient' => $search['patient']));
        if ($result === false) {
            http_response_code(404);
            exit;
        }
        $entries = array();
        $resourceURL = \RestConfig::$REST_FULL_URL;
        foreach ($result as $condition) {
            $entryResource = $this->fhirConditionService->createConditionResource(
                $condition['id'],
                $condition,
                false
            );
            $entry = array(
                'fullUrl' => $resourceURL . "/" . $condition['id'],
                'resource' => $entryResource
            );
            $entries[] = new FHIRBundleEntry($entry);
        }
        $result = $this->fhirService->createBundle('Condition', $entries, false);
        return RestControllerHelper::responseHandler($result, null, 200);
    }
    
    public function getOne($id)
    {
        $result = $this->fhirConditionService->getOne($id);
        if ($result) {
            $resource = $this->fhirConditionService->createConditionResource(
                $result['id'],
                $result,
                false
            );
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
}
