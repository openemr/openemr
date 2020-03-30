<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIROrganization;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAddress;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleLink;
use OpenEMR\FHIR\R4\PHPFHIRResponseParser;

class OrganizationService
{
    
    private $id;
    
    public function __construct()
    {
    }
    
    public function setId($id)
    {
        $this->id = $id;
    }
    
    public function getAll()
    {      
        $facilitySQL = "SELECT id,
                            name,
                            phone,
                            street,
                            city,
                            state,
                            postal_code,
                            country_code as country,
                            email
                        FROM facility;";
        $facilityResults = sqlStatement($facilitySQL);
        while ($row = sqlFetchArray($facilityResults)) {
            $row['id'] = 'facility-' . $row['id'];
            $row['code'] = 'prov';
            $row['display'] = "Healthcare Provider";
            array_push($results, $row);
        }
        return $results;
    }
    
    public function createOrganizationResource($oid = '', $data = '', $encode = true, $code = '', $display = '')
    {
        $id = new FhirId();
        $id->setValue($oid);
        $nowDate = date("Y-m-d\TH:i:s");
        $meta = new FHIRMeta();
        $meta->setVersionId('1');
        $meta->setLastUpdated($nowDate);
        $initResource = array(
            'id' => $id,
            'meta' => $meta
        );
        
        $initResource['active'] = true;
        $address = new FHIRAddress();
        $address->addLine($data['line1'] . ' ' . $data['line2']);
        $address->setCity($data['city']);
        $address->setState($data['state']);
        $address->setCountry($data['country']);
        $address->setPostalCode($data['postal_code']);
        $initResource['address'] = array();
        array_push($initResource['address'], $address->jsonSerialize());
        $initResource['name'] = $data['name'];
        $coding = new FHIRCoding();
        $coding->setSystem("http://terminology.hl7.org/CodeSystem/organization-type");
        $coding->setCode($code);
        $coding->setDisplay($display);
        $initResource['type'] = array();
        array_push($initResource['type'], $coding->jsonSerialize());
        $initResource['telecom'] = array();
        $email = array(
            'system' => 'email',
            'value' => $data["email"]
        );
        $phone = array(
            'system' => 'phone',
            'value' => $data['phone']
        );
        array_push($initResource['telecom'], $email);
        array_push($initResource['telecom'], $phone);
        $resource = new FHIROrganization($initResource);
        if ($encode) {
            return json_encode($resource);
        } else {
            return $resource;
        }
    }
}
