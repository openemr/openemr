<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\Services\PatientService;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPatient;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAddress;
use OpenEMR\FHIR\R4\FHIRElement\FHIRHumanName;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAdministrativeGender;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;

class FhirPatientService
{
    private $patientService;

    public function __construct()
    {
        $this->patientService = new PatientService();
    }

    public function createPatientResource($resourceId = '', $data = '', $encode = true)
    {
        // @todo add display text after meta
        $nowDate = date("Y-m-d\TH:i:s");
        $id = new FhirId();
        $id->setValue($resourceId);
        $name = new FHIRHumanName();
        $address = new FHIRAddress();
        $gender = new FHIRAdministrativeGender();
        $meta = array('versionId' => '1', 'lastUpdated' => $nowDate);
        $initResource = array('id' => $id, 'meta' => $meta);
        $name->setUse('official');
        $name->setFamily($data['lname']);
        $name->given = [$data['fname'], $data['mname']];
        $address->addLine($data['street']);
        $address->setCity($data['city']);
        $address->setState($data['state']);
        $address->setPostalCode($data['postal_code']);
        $gender->setValue(strtolower($data['sex']));

        $patientResource = new FHIRPatient($initResource);
        //$patientResource->setId($id);
        $patientResource->setActive(true);
        $patientResource->setGender($gender);
        $patientResource->addName($name);
        $patientResource->addAddress($address);

        if ($encode) {
            return json_encode($patientResource);
        } else {
            return $patientResource;
        }
    }

    public function parsePatientResource($fhirJson)
    {
        $data["title"] = "";
        $name = [];
        foreach ($fhirJson["name"] as $sub_name) {
            if ($sub_name["use"] == "official") {
                $name = $sub_name;
                break;
            }
        }
        $data["lname"] = $name["family"];
        $data["fname"] = $name["given"][0];
        $data["mname"] = $name["given"][1];
        $data["street"] = $fhirJson["address"][0]["line"][0];
        $data["postal_code"] = $fhirJson["address"][0]["postalCode"];
        $data["city"] = $fhirJson["address"][0]["city"];
        $data["state"] = $fhirJson["address"][0]["state"];
        $data["country_code"] = "" ;
        $phone = [];
        foreach ($fhirJson["telecom"] as $phone) {
            if ($phone["use"] == "mobile") {
                $name = $phone;
                break;
            }
        }
        $data["phone_contact"] = $phone["value"];
        $data["DOB"] = $fhirJson["birthDate"];
        $data["sex"] = $fhirJson["gender"];
        $data["race"] = "";
        $data["ethnicity"] = "";
        return $data;
    }

    public function setId($id)
    {
        $this->patientService->setPid($id);
    }

    public function validate($data)
    {
        return $this->patientService->validate($data);
    }

    public function insert($data)
    {
        return $this->patientService->insert($data);
    }

    public function update($pid, $data)
    {
        return $this->patientService->update($pid, $data);
    }

    public function getOne()
    {
        return $this->patientService->getOne();
    }

    public function getId()
    {
        return $this->patientService->getPid();
    }

    public function getAll($searchParam)
    {
        return $this->patientService->getAll($searchParam);
    }
}
