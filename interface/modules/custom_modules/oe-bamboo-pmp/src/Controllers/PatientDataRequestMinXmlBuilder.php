<?php

/*
 *   package   OpenEMR
 *   link      http://www.open-emr.org
 *   author    Sherwin Gaddis <sherwingaddis@gmail.com>
 *   Copyright (c)
 *   All rights reserved
 */

namespace Juggernaut\Module\Bamboo\Controllers;


use DOMDocument;
use Juggernaut\Module\Bamboo\Interfaces\DataRequestXmlBuilders;

require_once dirname(__DIR__, 6) . "/library/patient.inc.php";
class PatientDataRequestMinXmlBuilder implements DataRequestXmlBuilders
{
    private array $patientData;

    public function __construct()
    {
        $this->patientData = getPatientData($_SESSION['pid']);
    }

    public function buildReportDataRequestXml(): string
    {
        $data = new RequestData();
        $user = $data->userData();
        $practice = $data->practiceData();

        // Create a new DOMDocument
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;

        // Create the root element with the namespace
        $root = $doc->createElementNS('http://xml.appriss.com/gateway/v5_1', 'PatientRequest');
        $doc->appendChild($root);

        // Add the xsi namespace
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');

        // Create the Requester element
        $requester = $doc->createElement('Requester');
        $root->appendChild($requester);

        // Provider element inside Requester
        $provider = $doc->createElement('Provider');
        $requester->appendChild($provider);

        $role = $doc->createElement('Role', 'Physician');
        $provider->appendChild($role);
        $firstname = $doc->createElement('FirstName', $user['fname']);
        $provider->appendChild($firstname);
        $lastname = $doc->createElement('LastName', $user['lname']);
        $provider->appendChild($lastname);
        $deaNumber = $doc->createElement('DEANumber', $user['federaldrugid']);
        $provider->appendChild($deaNumber);
        $npi = $doc->createElement('NPINumber', $user['npi']);
        $provider->appendChild($npi);
        $proLicense = $doc->createElement('ProfessionalLicenseNumber');
        $provider->appendChild($proLicense);
        $type = $doc->createElement('Type', 'RPH');
        $proLicense->appendChild($type);
        $value = $doc->createElement('Value', $user['state_license_number']);
        $proLicense->appendChild($value);
        $stateCode = $doc->createElement('StateCode', $practice['state']);
        $proLicense->appendChild($stateCode);
        // Location element inside Requester
        $location = $doc->createElement('Location');
        $requester->appendChild($location);

        $name = $doc->createElement('Name', $practice['name']);
        $location->appendChild($name);

        $deaNumber = $doc->createElement('DEANumber', $user['federaldrugid']);
        $location->appendChild($deaNumber);

        $npi = $doc->createElement('NPINumber', $user['npi']);
        $location->appendChild($npi);

        // Address element inside Location
        $address = $doc->createElement('Address');
        $location->appendChild($address);

        $stateCode = $doc->createElement('StateCode', $practice['state']);
        $address->appendChild($stateCode);

        // PrescriptionRequest element
        $prescriptionRequest = $doc->createElement('PrescriptionRequest');
        $root->appendChild($prescriptionRequest);

        // Patient element inside PrescriptionRequest
        $patient = $doc->createElement('Patient');
        $prescriptionRequest->appendChild($patient);

        // Name element inside Patient
        $nameElement = $doc->createElement('Name');
        $patient->appendChild($nameElement);

        $firstName = $doc->createElement('First', $this->patientData['fname']);
        $nameElement->appendChild($firstName);

        $lastName = $doc->createElement('Last', $this->patientData['lname']);
        $nameElement->appendChild($lastName);

        $birthdate = $doc->createElement('Birthdate', $this->patientData['DOB']);
        $patient->appendChild($birthdate);

        // Address element inside Patient
        $patientAddress = $doc->createElement('Address');
        $patient->appendChild($patientAddress);
        $street = $doc->createElement('Street', $this->patientData['street']);
        $patientAddress->appendChild($street);
        $city = $doc->createElement('City', $this->patientData['city']);
        $patientAddress->appendChild($city);
        $state = $doc->createElement('StateCode', $this->patientData['state']);
        $patientAddress->appendChild($state);
        $zipCode = $doc->createElement('ZipCode', $this->patientData['postal_code']);
        $patientAddress->appendChild($zipCode);

        // Save the XML as a string
        $xmlString = $doc->saveXML();

        // Output the XML for debugging
        return $xmlString;
    }
}
