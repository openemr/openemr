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
        $searchResult = $this->fhirObservationService->getAll();
        if ($searchResult !== false) {
            $entries = array();
            foreach ($searchResult as $profile) {
                $id = 'vitals-' . $profile['form_id'];
                $profile_data = $this->fhirObservationService->getOne($id);
                $entryResource = $this->fhirObservationService->createObservationResource($id, $profile_data, false);
                $entry = array(
                    'fullUrl' => $resourceURL . "/" . $id,
                    'resource' => $entryResource
                );
                $entries[] = new FHIRBundleEntry($entry);
                foreach ($entryResource->hasMember as $profiles) {
                    $ref = explode("/", $profiles->reference);
                    $profile_data = $this->fhirObservationService->getOne($ref[1]);
                    $resource = $this->fhirObservationService->createObservationResource($ref[1], $profile_data, false);
                    $entry = array(
                        'fullUrl' => $resourceURL . "/" . $ref[1],
                        'resource' => $resource
                    );
                    $entries[] = new FHIRBundleEntry($entry);
                };
            }
            $searchResult = $this->fhirService->createBundle('Observation', $entries, false);
            $statusCode = 200;
        } else {
            $statusCode = 404;
            $searchResult = $this->fhirValidate->operationOutcomeResourceService(
                'error',
                'invalid',
                false,
                "Something went wrong"
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
}
