<?php

namespace OpenEMR\RestControllers\FHIR;

use OpenEMR\Services\FHIR\FhirObservationService;
use OpenEMR\RestControllers\RestControllerHelper;

class FhirObservationRestController
{
    private $fhirObservationService;

    public function __construct()
    {
        $this->fhirObservationService = new FhirObservationService();
    }

    public function getOne($id)
    {
        $split_id = explode("-", $id);
        $profile = $split_id[0];
        $observation_id = $split_id[1];
        $profile_data = $this->fhirObservationService->getVital($observation_id);
        if ($profile_data) {
            $profile_data['profile'] = $profile;
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
