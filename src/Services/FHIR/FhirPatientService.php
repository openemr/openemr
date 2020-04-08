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
        if (isset($fhirJson['name'])) {
            $name = [];
            foreach ($fhirJson["name"] as $sub_name) {
                if ($sub_name["use"] == "official") {
                    $name = $sub_name;
                    break;
                }
            }
            if (isset($name["family"])) {
                $data["lname"] = $name["family"];
            }
            if ($name["given"][0]) {
                $data["fname"] = $name["given"][0];
            }
            if (isset($name["given"][1])) {
                $data["mname"] = $name["given"][1];
            }
        }
        if (isset($fhirJson["address"])) {
            if (isset($fhirJson["address"][0]["line"][0])) {
                $data["street"] = $fhirJson["address"][0]["line"][0];
            }
            if (isset($fhirJson["address"][0]["postalCode"][0])) {
                $data["postal_code"] = $fhirJson["address"][0]["postalCode"];
            }
            if (isset($fhirJson["address"][0]["city"][0])) {
                $data["city"] = $fhirJson["address"][0]["city"];
            }
            if (isset($fhirJson["address"][0]["state"][0])) {
                $data["state"] = $fhirJson["address"][0]["state"];
            }
            if (isset($fhirJson["address"][0]["country"][0])) {
                $data["country"] = $fhirJson["address"][0]["country"];
            }
        }
        if (isset($fhirJson["telecom"]['system'])) {
            foreach ($fhirJson["telecom"] as $telecom) {
                switch ($telecom['system']) {
                    case 'phone':
                        switch ($telecom['use']) {
                            case 'mobile':
                                $data["phone_contact"] = $telecom["value"];
                                break;
                            case 'home':
                                $data["phone_home"] = $telecom["value"];
                                break;
                            case 'work':
                                $data["phone_biz"] = $telecom["value"];
                                break;
                            default:
                                $data["phone_contact"] = $telecom["value"];
                                break;
                        }
                        break;
                    case 'email':
                        $data["email"] = $telecom["value"];
                        break;
                    default:
                    //Should give Error for incapability
                        break;
                }
            }
        }
        if (isset($fhirJson["birthDate"])) {
            $data["DOB"] = $fhirJson["birthDate"];
        }
        if (isset($fhirJson["gender"])) {
            $data["sex"] = $fhirJson["gender"];
        }
        return $data;
    }

    public function setId($id)
    {
        $this->patientService->setPid($id);
    }

    public function validate($data)
    {
        return $this->patientService->validate($data, 'insert');
    }

    public function validateUpdate($pid, $data)
    {
        return $this->patientService->validate($data, 'update', $pid);
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
