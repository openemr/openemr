<?php

namespace OpenEMR\RestControllers\FHIR;

use OpenEMR\Services\FHIR\FhirValidationService;
use OpenEMR\Services\FHIR\FhirOrganizationService;
use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;

class FhirOrganizationRestController
{
    private $fhirOrganizationService;
    private $fhirService;
    private $fhirValidationService;
    
    public function __construct($pid)
    {
        $this->fhirOrganizationService = new FhirOrganizationService();
        $this->fhirOrganizationService->setId($pid);
        $this->fhirService = new FhirResourcesService();
        $this->fhirValidationService = new FhirValidationService();
    }

    public function getAll($search)
    {
        $result = $this->fhirOrganizationService->getAll();
        if (!$result) {
            $statusCode = 400;
            $result = $this->fhirValidationService->operationOutcomeResourceService(
                'error',
                'invalid',
                false,
                "Invalid Parameter"
            );
        } else {
            $statusCode = 200;
            $entries = array();
            foreach ($result as $org) {
                $entryResource = $this->fhirOrganizationService->createOrganizationResource(
                    $org['id'],
                    $org,
                    false,
                    $org['code'],
                    $org['display']
                );
                $entry = array(
                    'fullUrl' => $resourceURL . "/" . $org['id'],
                    'resource' => $entryResource
                );
                $entries[] = new FHIRBundleEntry($entry);
            }
            $result = $this->fhirService->createBundle('Organization', $entries, false);
        }
        return RestControllerHelper::responseHandler($result, null, $statusCode);
    }

    public function getOne($oid)
    {
        $result = $this->fhirOrganizationService->getOne($oid);
        if ($result) {
            $resource = $this->fhirOrganizationService->createOrganizationResource($result['id'], $result, false, $result['code'], $result['display']);
            $statusCode = 200;
        } else {
            $statusCode = 404;
            $resource = $this->fhirValidationService->operationOutcomeResourceService(
                'error',
                'invalid',
                false,
                "Resource Id $pid does not exist"
            );
        }

        return RestControllerHelper::responseHandler($resource, null, $statusCode);
    }
}
