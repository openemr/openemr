<?php

namespace OpenEMR\RestControllers\FHIR;

use OpenEMR\Services\FHIR\FhirValidationService;
use OpenEMR\Services\FHIR\FhirAllergyIntoleranceService;
use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;

class FhirAllergyIntoleranceRestController
{
    private $fhirAllergyIntoleranceService;
    private $fhirService;
	private $fhirValidationService;
    
    public function __construct($id)
    {
        $this->fhirAllergyIntoleranceService = new FhirAllergyIntoleranceService();
        $this->fhirAllergyIntoleranceService->setId($id);
        $this->fhirService = new FhirResourcesService();
        $this->fhirValidationService = new FhirValidationService();
    }
    
    public function getAll($search)
    {
        $result = $this->fhirAllergyIntoleranceService->getAll(array('patient' => $search['patient']));
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
        }
        return RestControllerHelper::responseHandler($result, null, $statusCode);
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
            $resource = $this->fhirValidationService->operationOutcomeResourceService(
                'error',
                'invalid',
                false,
                "Resource Id $id does not exist"
            );
        }

        return RestControllerHelper::responseHandler($resource, null, $statusCode);
    }
}
