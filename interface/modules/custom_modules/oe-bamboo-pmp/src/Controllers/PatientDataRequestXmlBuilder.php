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
use Juggernaut\Module\Bamboo\DataRequestXmlBuilders;

class PatientDataRequestXmlBuilder implements DataRequestXmlBuilders
{
    /**
     * @var array|mixed|null
     */
    private mixed $getFacilityInfo;
    private array $providerInfo;

    public function __construct()
    {
        $facilityInfo = new RequestData();
        $this->getFacilityInfo = $facilityInfo->practiceData();
        $this->providerInfo = $facilityInfo->userData();
    }

    private const DEVELOPER_DATA = "Juggernaut Systems Express";
    private const PRODUCT_DATA = "OpenEMR Module";

    private const VERSION_DATA = "1.0";

    public function buildReportDataRequestXml(): string
    {
        $doc = new DOMDocument('1.0', 'UTF-8');

// Create the root element with the namespace
        $root = $doc->createElementNS('http://xml.appriss.com/gateway/v5_1', 'PatientRequest');
        $doc->appendChild($root);

// Create the Requester element
        $requester = $doc->createElement('Requester');
        $root->appendChild($requester);

// LicenseeRequestId element
        $licenseeRequestId = $doc->createElement('LicenseeRequestId', 'identifier');
        $requester->appendChild($licenseeRequestId);

// SenderSoftware element
        $senderSoftware = $doc->createElement('SenderSoftware');
        $requester->appendChild($senderSoftware);

        $developer = $doc->createElement('Developer', self::DEVELOPER_DATA);
        $senderSoftware->appendChild($developer);

        $product = $doc->createElement('Product', self::PRODUCT_DATA);
        $senderSoftware->appendChild($product);

        $version = $doc->createElement('Version', self::VERSION_DATA);
        $senderSoftware->appendChild($version);

// RequestDestinations element
        $requestDestinations = $doc->createElement('RequestDestinations');
        $requester->appendChild($requestDestinations);

        $pmp = $doc->createElement('Pmp', $this->getFacilityInfo['state']);
        $requestDestinations->appendChild($pmp);

// Provider element
        $provider = $doc->createElement('Provider');
        $requester->appendChild($provider);

        $role = $doc->createElement('Role', 'Physician');
        $provider->appendChild($role);

        $firstName = $doc->createElement('FirstName', $this->providerInfo['fname']);
        $provider->appendChild($firstName);

        $lastName = $doc->createElement('LastName', $this->providerInfo['lname']);
        $provider->appendChild($lastName);

        $deaNumber = $doc->createElement('DEANumber', $this->providerInfo['drugid']);
        $provider->appendChild($deaNumber);

        $npiNumber = $doc->createElement('NPINumber', $this->providerInfo['npi']);
        $provider->appendChild($npiNumber);

// ProfessionalLicenseNumber element
        $professionalLicenseNumber = $doc->createElement('ProfessionalLicenseNumber');
        $provider->appendChild($professionalLicenseNumber);

        $type = $doc->createElement('Type', 'String');
        $professionalLicenseNumber->appendChild($type);

        $value = $doc->createElement('Value', $this->providerInfo['license']);
        $professionalLicenseNumber->appendChild($value);

        $stateCode = $doc->createElement('StateCode', $this->getFacilityInfo['state']);
        $professionalLicenseNumber->appendChild($stateCode);

// Location element
        $location = $doc->createElement('Location');
        $requester->appendChild($location);

        $name = $doc->createElement('Name', $this->getFacilityInfo['name']);
        $location->appendChild($name);

        $deaNumberLocation = $doc->createElement('DEANumber', $this->getFacilityInfo['dea']);
        $location->appendChild($deaNumberLocation);

        $npiNumberLocation = $doc->createElement('NPINumber', $this->getFacilityInfo['npi']);
        $location->appendChild($npiNumberLocation);

        $ncpdpNumber = $doc->createElement('NCPDPNumber', $this->getFacilityInfo['ncpdp']);
        $location->appendChild($ncpdpNumber);

// Address element under Location
        $address = $doc->createElement('Address');
        $location->appendChild($address);

        $street1 = $doc->createElement('Street', $this->getFacilityInfo['street']);
        $address->appendChild($street1);

        $street2 = $doc->createElement('Street', $this->getFacilityInfo['street2']);
        $address->appendChild($street2);

        $city = $doc->createElement('City', $this->getFacilityInfo['city']);
        $address->appendChild($city);

        $stateCodeAddress = $doc->createElement('StateCode', $this->getFacilityInfo['state']);
        $address->appendChild($stateCodeAddress);

        $zipCode = $doc->createElement('ZipCode', $this->getFacilityInfo['postal_code']);
        $address->appendChild($zipCode);

        $zipPlusFour = $doc->createElement('ZipPlusFour', $this->getFacilityInfo['plus_four']);
        $address->appendChild($zipPlusFour);

// PrescriptionRequest element
        $prescriptionRequest = $doc->createElement('PrescriptionRequest');
        $root->appendChild($prescriptionRequest);

// DateRange element
        $dateRange = $doc->createElement('DateRange');
        $prescriptionRequest->appendChild($dateRange);

        $begin = $doc->createElement('Begin', '2008-11-01');
        $dateRange->appendChild($begin);

        $end = $doc->createElement('End', '2010-11-30');
        $dateRange->appendChild($end);

// RxCodes element
        $rxCodes = $doc->createElement('RxCodes');
        $prescriptionRequest->appendChild($rxCodes);

// RxCurrent Code
        $codeCurrent = $doc->createElement('Code');
        $rxCodes->appendChild($codeCurrent);

        $rxCurrent = $doc->createElement('RxCurrent');
        $codeCurrent->appendChild($rxCurrent);

        $codeTypeCurrent = $doc->createElement('CodeType', 'NDC');
        $rxCurrent->appendChild($codeTypeCurrent);

        $codeValueCurrent = $doc->createElement('CodeValue', '0777310507');
        $rxCurrent->appendChild($codeValueCurrent);

        $quantityCurrent = $doc->createElement('Quantity', '1');
        $rxCurrent->appendChild($quantityCurrent);

        $daysSupplyCurrent = $doc->createElement('DaysSupply', '60');
        $rxCurrent->appendChild($daysSupplyCurrent);

        $fillDateCurrent = $doc->createElement('FillDate', '2017-08-05');
        $rxCurrent->appendChild($fillDateCurrent);

        $sigCurrent = $doc->createElement('Sig', 'Take one tablet by mouth two times a day with meals');
        $rxCurrent->appendChild($sigCurrent);

// RxPending Code
        $codePending = $doc->createElement('Code');
        $rxCodes->appendChild($codePending);

        $rxPending = $doc->createElement('RxPending');
        $codePending->appendChild($rxPending);

        $codeTypePending = $doc->createElement('CodeType', 'NDC');
        $rxPending->appendChild($codeTypePending);

        $codeValuePending = $doc->createElement('CodeValue', '0798319515');
        $rxPending->appendChild($codeValuePending);

        $quantityPending = $doc->createElement('Quantity', '2');
        $rxPending->appendChild($quantityPending);

        $daysSupplyPending = $doc->createElement('DaysSupply', '30');
        $rxPending->appendChild($daysSupplyPending);

        $fillDatePending = $doc->createElement('FillDate', '2017-09-12');
        $rxPending->appendChild($fillDatePending);

        $sigPending = $doc->createElement('Sig', 'Take two tablets by mouth once a day with meals');
        $rxPending->appendChild($sigPending);

// DiagnosisCodes element
        $diagnosisCodes = $doc->createElement('DiagnosisCodes');
        $prescriptionRequest->appendChild($diagnosisCodes);

        $diagnosisCode = $doc->createElement('Code');
        $diagnosisCodes->appendChild($diagnosisCode);

        $diagnosisCodeType = $doc->createElement('CodeType', 'ICD');
        $diagnosisCode->appendChild($diagnosisCodeType);

        $diagnosisCodeVersion = $doc->createElement('CodeVersion', '10');
        $diagnosisCode->appendChild($diagnosisCodeVersion);

        $diagnosisCodeValue = $doc->createElement('CodeValue', 'F31.2');
        $diagnosisCode->appendChild($diagnosisCodeValue);

// Patient element
        $patient = $doc->createElement('Patient');
        $prescriptionRequest->appendChild($patient);

// Patient Name element
        $patientName = $doc->createElement('Name');
        $patient->appendChild($patientName);

        $patientFirstName = $doc->createElement('First', 'Bob');
        $patientName->appendChild($patientFirstName);

        $patientMiddleName = $doc->createElement('Middle', 'Dylan');
        $patientName->appendChild($patientMiddleName);

        $patientLastName = $doc->createElement('Last', 'Testpatient');
        $patientName->appendChild($patientLastName);

        $birthdate = $doc->createElement('Birthdate', '1900-01-01');
        $patient->appendChild($birthdate);

        $sexCode = $doc->createElement('SexCode', 'M');
        $patient->appendChild($sexCode);

// Patient Address element
        $patientAddress = $doc->createElement('Address');
        $patient->appendChild($patientAddress);

        $patientStreet1 = $doc->createElement('Street', '123 Main St');
        $patientAddress->appendChild($patientStreet1);

        $patientStreet2 = $doc->createElement('Street', 'Apt B');
        $patientAddress->appendChild($patientStreet2);

        $patientCity = $doc->createElement('City', 'Wichita');
        $patientAddress->appendChild($patientCity);

        $patientStateCode = $doc->createElement('StateCode', 'KS');
        $patientAddress->appendChild($patientStateCode);

        $patientZipCode = $doc->createElement('ZipCode', '67203');
        $patientAddress->appendChild($patientZipCode);

        $patientZipPlusFour = $doc->createElement('ZipPlusFour', '4321');
        $patientAddress->appendChild($patientZipPlusFour);

        $phone = $doc->createElement('Phone', '1234567890');
        $patient->appendChild($phone);

        $ssn = $doc->createElement('SSN', '123-45-6789');
        $patient->appendChild($ssn);

// DriversLicenseIdentifier element
        $driversLicenseIdentifier = $doc->createElement('DriversLicenseIdentifier');
        $patient->appendChild($driversLicenseIdentifier);

        $driversLicenseValue = $doc->createElement('Value', 'S01-45-6789');
        $driversLicenseIdentifier->appendChild($driversLicenseValue);

        $driversLicenseStateCode = $doc->createElement('StateCode', 'KS');
        $driversLicenseIdentifier->appendChild($driversLicenseStateCode);

// VeterinaryPrescription element
        $veterinaryPrescription = $doc->createElement('VeterinaryPrescription');
        $patient->appendChild($veterinaryPrescription);

        $animalName = $doc->createElement('AnimalName', 'String');
        $veterinaryPrescription->appendChild($animalName);

// Additional elements in Patient
        $pharmacyBenefitsMemberID = $doc->createElement('PharmacyBenefitsMemberID', 'String');
        $patient->appendChild($pharmacyBenefitsMemberID);

        $medicalBenefitsMemberID = $doc->createElement('MedicalBenefitsMemberID', 'String');
        $patient->appendChild($medicalBenefitsMemberID);

        $medicalRecordID = $doc->createElement('MedicalRecordID', 'XX-1234-AnyString');
        $patient->appendChild($medicalRecordID);

// Save the XML as a string
        $xmlString = $doc->saveXML();

// Output the XML for debugging
        return $xmlString;
    }

    public function buildPatientReportDataRequestXml()
    {
        // TODO: Implement buildPatientReportDataRequestXml() method.
    }
}
