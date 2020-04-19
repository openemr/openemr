<?php

namespace OpenEMR\RestControllers\FHIR;

use OpenEMR\Services\FHIR\FhirProcedureService;
use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;

class FhirProcedureRestController
{
    private $fhirProcedureService;
    private $fhirService;
    
    public function __construct($id)
    {
        $this->fhirProcedureService = new FhirProcedureService();
        $this->fhirProcedureService->setId($id);
        $this->fhirService = new FhirResourcesService();
    }
    
    public function getAll($search)
    {
        $result = $this->fhirProcedureService->getAll(array('patient' => $search['patient']));
        if ($result === false) {
            http_response_code(404);
            exit;
        }
        $entries = array();
        $resourceURL = \RestConfig::$REST_FULL_URL;
        foreach ($result as $procedure) {
            $entryResource = $this->fhirProcedureService->createProcedureResource(
                $procedure['id'],
                $procedure,
                false
            );
            $entry = array(
                'fullUrl' => $resourceURL . "/" . $procedure['id'],
                'resource' => $entryResource
            );
            $entries[] = new FHIRBundleEntry($entry);
        }
        $result = $this->fhirService->createBundle('Procedure', $entries, false);
        return RestControllerHelper::responseHandler($result, null, 200);
    }
    
    public function getOne($id)
    {
        $result = $this->fhirProcedureService->getOne($id);
        if ($result) {
            $resource = $this->fhirProcedureService->createProcedureResource(
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
