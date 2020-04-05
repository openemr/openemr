<?php

namespace OpenEMR\RestControllers\FHIR;

use OpenEMR\Services\FHIR\FhirAllergyIntoleranceService;
use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;

class FhirAllergyIntoleranceRestController
{
    private $fhirAllergyIntoleranceService;
    private $fhirService;
    
    public function __construct($pid)
    {
        $this->fhirAllergyIntoleranceService = new FhirAllergyIntoleranceService();
        $this->fhirAllergyIntoleranceService->setId($pid);
        $this->fhirService = new FhirResourcesService();
    }
    
    public function getAll()
    {
        $result = $this->fhirAllergyIntoleranceService->getAll();
        if ($result === false) {
            http_response_code(404);
            exit;
        }
        $entries = array();
		$resourceURL = \RestConfig::$REST_FULL_URL;
        foreach ($result as $allergy) {
            $entryResource = $this->fhirAllergyIntoleranceService->createAllergyIntoleranceResource(
                $allergy['id'],
                $allergy,
                false
            );
            $entry = array(
                'fullUrl' => $resourceURL . "/" . $allergy['id'],
                'resource' => $entryResource
            );
            $entries[] = new FHIRBundleEntry($entry);
        }
        $result = $this->fhirService->createBundle('AllergyIntolerance', $entries, false);
        return RestControllerHelper::responseHandler($result, null, 200);
    }
	
	public function getOne($id)
	{
        $result = $this->fhirAllergyIntoleranceService->getOne($id);
        if ($result) {
            $resource = $this->fhirAllergyIntoleranceService->createAllergyIntoleranceResource(
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
