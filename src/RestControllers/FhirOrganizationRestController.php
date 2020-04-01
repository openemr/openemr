<?php

namespace OpenEMR\RestControllers;

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
}
