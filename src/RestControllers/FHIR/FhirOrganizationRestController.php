<?php

namespace OpenEMR\RestControllers\FHIR;

use OpenEMR\Services\FHIR\FhirOrganizationService;
use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;

class FhirOrganizationRestController
{
    private $fhirOrganizationService;
    private $fhirService;
    
    public function __construct($pid)
    {
        $this->fhirOrganizationService = new FhirOrganizationService();
        $this->fhirOrganizationService->setId($pid);
        $this->fhirService = new FhirResourcesService();
    }

    public function getAll($search)
    {
        $result = $this->fhirOrganizationService->getAll();
        if ($result === false) {
            http_response_code(404);
            exit;
        }
        $entries = array();
        foreach ($result as $org) {
            $entryResource = $this->fhirOrganizationService->createOrganizationResource($org['id'], $org, false, $org['code'], $org['display']);
            $entry = array(
                'fullUrl' => $resourceURL . "/" . $org['id'],
                'resource' => $entryResource
            );
            $entries[] = new FHIRBundleEntry($entry);
        }
        $result = $this->fhirService->createBundle('Organization', $entries, false);
        return RestControllerHelper::responseHandler($result, null, 200);
    }
	
    public function getOne($oid)
    {
        $result = $this->fhirOrganizationService->getOne($oid);
        if ($result) {
            $resource = $this->fhirOrganizationService->createOrganizationResource($result['id'], $result, false, $result['code'], $result['display']);
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
}
