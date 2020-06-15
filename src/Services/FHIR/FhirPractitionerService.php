<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\Services\ProviderService;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPractitioner;
use OpenEMR\FHIR\R4\FHIRElement\FhirId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRHumanName;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAddress;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAdministrativeGender;

class FhirPractitionerService
{
    private $providerService;

    public function __construct()
    {
        $this->providerService = new ProviderService();
    }

    public function createPractitionerResource($provider_id = '', $encode = true)
    {
        if (!$provider_id) {
            return false;
        }
        $this->providerService = new ProviderService();
        $data = $this->providerService->getById($provider_id);
        $resource = new FHIRPractitioner();
        $id = new FhirId();
        $name = new FHIRHumanName();
        $address = new FHIRAddress();
        $id->setValue('' . $provider_id);
        $name->setUse('official');
        $name->setFamily($data['lname']);
        $name->given = [$data['fname'], $data['mname']];
        $address->addLine($data['street']);
        $address->setCity($data['city']);
        $address->setState($data['state']);
        $address->setPostalCode($data['zip']);
        $resource->setId($id);
        $resource->setActive(true);
        $gender = new FHIRAdministrativeGender();
        $gender->setValue('unknown');
        $resource->setGender($gender);
        $resource->addName($name);
        $resource->addAddress($address);

        if ($encode) {
            return json_encode($resource);
        } else {
            return $resource;
        }
    }
}
