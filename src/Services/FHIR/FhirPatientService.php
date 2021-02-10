<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\Export\ExportCannotEncodeException;
use OpenEMR\FHIR\Export\ExportException;
use OpenEMR\FHIR\Export\ExportStreamWriter;
use OpenEMR\FHIR\Export\ExportWillShutdownException;
use OpenEMR\FHIR\FhirSearchParameterType;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCommunication;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCode;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRContactPoint;
use OpenEMR\FHIR\R4\FHIRElement\FHIRContactPointSystem;
use OpenEMR\FHIR\R4\FHIRElement\FHIRContactPointUse;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifierUse;
use OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUri;
use OpenEMR\FHIR\Export\ExportJob;
use OpenEMR\FHIR\R4\FHIRResource\FHIRPatient\FHIRPatientCommunication;
use OpenEMR\Services\PatientService;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPatient;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAddress;
use OpenEMR\FHIR\R4\FHIRElement\FHIRHumanName;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAdministrativeGender;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\Validators\ProcessingResult;

/**
 * FHIR Patient Service
 *
 * @coversDefaultClass OpenEMR\Services\FHIR\FhirPatientService
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Dixon Whitmire <dixonwh@gmail.com>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */
class FhirPatientService extends FhirServiceBase implements IFhirExportableResourceService, IResourceUSCIGProfileService
{
    /**
     * @var PatientService
     */
    private $patientService;

    /**
     * Note requirements for US Core are:
     * Each Patient must HAVE (if missing data in EMR, must have a data missing definition extension)
     * 1. a patient identifier
     * 2. a patient name
     * 3. a gender
     * Each patient must SUPPORT
     * 1. a contact detail (telephone or email)
     * 2. a birth date
     * 3. an address
     * 4. a communication language
     * 5. a race
     * 6. an ethnicity
     * 7. a birth sex
     *
     * Search Parameters Required
     * 1. Must support exact token match _id
     * 2. Must support exact token match identifier
     * 3. Must support fuzzy string matching name
     * 4. Must support name+birthdate search
     * 5. Must support gender+name search
     *
     * Search Parameters optional
     * 1. birthdate+family search
     * 2. family+gender search
     */
    const USCGI_PROFILE_URI = 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-patient';

    public function __construct()
    {
        parent::__construct();
        $this->patientService = new PatientService();
    }

    /**
     * Returns an array mapping FHIR Patient Resource search parameters to OpenEMR Patient search parameters
     * @return array The search parameters
     */
    protected function loadSearchParameters()
    {
        // @see https://www.hl7.org/fhir/patient.html#search
        return  [
            '_id' => ['type' => FhirSearchParameterType::TOKEN, 'fields' => ['uuid'] ],
            // TODO: this must be an exact match OR condition, since we don't have that supported yet we are just going off ssn
//            'identifier' => ['type' => FhirSearchParameterType::TOKEN, 'fields' => ['ssn', 'pubid'] ],
            'identifier' => ['type' => FhirSearchParameterType::TOKEN, 'fields' => ['ss'] ],
            'address' => ['type' => FhirSearchParameterType::STRING, 'fields' => ['street', 'postal_code', 'city', 'state'] ],
            'address-city' => ['type' => FhirSearchParameterType::STRING, 'fields' => ['city'] ],
            'address-postalcode' => ['type' => FhirSearchParameterType::STRING, 'fields' => ['postal_code'] ],
            'address-state' => ['type' => FhirSearchParameterType::STRING, 'fields' => ['state'] ],
            'birthdate' => ['type' => FhirSearchParameterType::DATE, 'fields' => ['DOB'] ],
            'email' => ['type' => FhirSearchParameterType::TOKEN, 'fields' => ['email'] ],
            'family' => ['type' => FhirSearchParameterType::STRING, 'fields' => ['lname'] ],
            'gender' => ['type' => FhirSearchParameterType::TOKEN, 'fields' => ['sex'] ],
            'given' => ['type' => FhirSearchParameterType::STRING, 'fields' => ['fname', 'mname'] ],
            'name' => ['type' => FhirSearchParameterType::STRING, 'fields' => ['title', 'fname', 'mname', 'lname'] ],
            'phone' => ['type' => FhirSearchParameterType::TOKEN, 'fields' => ['phone_home', 'phone_biz', 'phone_cell'] ],
            'telecom' => ['type' => FhirSearchParameterType::TOKEN, 'fields' => ['email', 'phone_home', 'phone_biz', 'phone_cell'] ]
        ];
    }

    /**
     * Parses an OpenEMR patient record, returning the equivalent FHIR Patient Resource
     *
     * @param array $dataRecord The source OpenEMR data record
     * @param boolean $encode Indicates if the returned resource is encoded into a string. Defaults to false.
     * @return FHIRPatient
     */
    public function parseOpenEMRRecord($dataRecord = array(), $encode = false)
    {
        $patientResource = new FHIRPatient();

        $meta = array('versionId' => '1', 'lastUpdated' => gmdate('c'));
        $patientResource->setMeta($meta);

        $patientResource->setActive(true);

        $narrativeText = '';
        if (!empty($dataRecord['fname'])) {
            $narrativeText = $dataRecord['fname'];
        }
        if (!empty($dataRecord['lname'])) {
            $narrativeText .= ' ' . $dataRecord['lname'];
        }
        if (!empty($narrativeText)) {
            $text = array(
                'status' => 'generated',
                'div' => '<div xmlns="http://www.w3.org/1999/xhtml"> <p>' . $narrativeText . '</p></div>'
            );
            $patientResource->setText($text);
        }

        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $patientResource->setId($id);

        $name = new FHIRHumanName();
        $name->setUse('official');

        if (!empty($dataRecord['title'])) {
            $name->addPrefix($dataRecord['title']);
        }
        if (!empty($dataRecord['lname'])) {
            $name->setFamily($dataRecord['lname']);
        }

        if (!empty($dataRecord['fname'])) {
            $name->addGiven($dataRecord['fname']);
        }

        if (!empty($dataRecord['mname'])) {
            $name->addGiven($dataRecord['mname']);
        }

        $patientResource->addName($name);

        if (isset($dataRecord['DOB'])) {
            $patientResource->setBirthDate($dataRecord['DOB']);
        }

        $address = new FHIRAddress();
        // TODO: we don't track start and end periods for dates so what value should go here...?
        $addressPeriod = new FHIRPeriod();
        $start = new \DateTime();
        $start->sub(new \DateInterval('P1Y')); // subtract one year
        $end = new \DateTime();
        $addressPeriod->setStart(new FHIRDateTime($start->format(\DateTime::RFC3339_EXTENDED)));
        // if there's an end date we provide one here, but for now we just go back one year
//        $addressPeriod->setEnd(new FHIRDateTime($end->format(\DateTime::RFC3339_EXTENDED)));
        $address->setPeriod($addressPeriod);
        $hasAddress = false;
        if (!empty($dataRecord['street'])) {
            $address->addLine($dataRecord['street']);
            $hasAddress = true;
        }
        if (!empty($dataRecord['city'])) {
            $address->setCity($dataRecord['city']);
            $hasAddress = true;
        }
        if (!empty($dataRecord['state'])) {
            $address->setState($dataRecord['state']);
            $hasAddress = true;
        }
        if (!empty($dataRecord['postal_code'])) {
            $address->setPostalCode($dataRecord['postal_code']);
            $hasAddress = true;
        }
        if (!empty($dataRecord['country_code'])) {
            $address->setCountry($dataRecord['country_code']);
            $hasAddress = true;
        }
        if ($hasAddress) {
            $patientResource->addAddress($address);
        }

        if (!empty($dataRecord['phone_home'])) {
            $patientResource->addTelecom($this->createContactPoint('phone', $dataRecord['phone_home'], 'home'));
        }

        if (!empty($dataRecord['phone_biz'])) {
            $patientResource->addTelecom($this->createContactPoint('phone', $dataRecord['phone_biz'], 'work'));
        }

        if (!empty($dataRecord['phone_cell'])) {
            $patientResource->addTelecom($this->createContactPoint('phone', $dataRecord['phone_cell'], 'mobile'));
        }

        if (!empty($dataRecord['email'])) {
            $patientResource->addTelecom($this->createContactPoint('email', $dataRecord['email'], 'home'));
        }

        $gender = new FHIRAdministrativeGender();
        if (!empty($dataRecord['sex'])) {
            $gender->setValue(strtolower($dataRecord['sex']));

            // if this is not here we have to add a data missing element
            // birth sex
            // TODO: I don't see anywhere we are tracking birth sex and we will need to handle that... for now we
            // just key off recorded sex
            $birthSex = $dataRecord['sex'] == 'Male' ? 'M' : 'F';
            $birthSexExtension = new FHIRExtension();
            $birthSexExtension->setUrl("http://hl7.org/fhir/us/core/StructureDefinition/us-core-birthsex");
            $birthSexExtension->setValueCode($birthSex);
            $patientResource->addExtension($birthSexExtension);
        }
        $patientResource->setGender($gender);

        // note to figure out what the crap to put in this FHIR thing you have to look at the DETAILED Descriptions
        // of us-core-patient Profile and see the race has a USCoreRaceExtension in the race property.  Clicking on
        // that absurd rabit whole brings you to the ACTUAL way that this field should be populated:
        // @see http://hl7.org/fhir/us/core/STU3.1.1/StructureDefinition-us-core-race.html
        // what a frickin mess.
        if (isset($dataRecord['race'])) {
            // race is defined as containing 2 required extensions, text & ombCategory
            $raceExtension = new FHIRExtension();
            $raceExtension->setUrl("http://hl7.org/fhir/StructureDefinition/us-core-race");

            $ombCategory = new FHIRExtension();
            $ombCategory->setUrl("ombCategory");
            $ombCategoryCoding = new FHIRCoding();
            $ombCategoryCoding->setSystem(new FHIRUri("urn:oid:2.16.840.1.113883.6.238"));
            $coding = new FHIRCoding();
            $coding->setSystem(new FHIRUri("http://hl7.org/fhir/v3/Race"));
            // 2106-3 is White
            // 2076-8 is Native Hawaiian or Other Pacific Islander
            // 2131-1 is Other Race
            // 2054-5 is Black or African American
            // 2028-9 is Asian
            // 1002-5 is American Indian or Alaska Native
            if ($dataRecord['race'] == 'amer_ind_or_alaska_native') {
                $ombCategoryCoding->setCode("1002-5");
                $ombCategoryCoding->setDisplay("American Indian or Alaska Native");
            } else if ($dataRecord['race'] == 'white') {
                $ombCategoryCoding->setCode("2106-3");
                $ombCategoryCoding->setDisplay("White");
            } else {
                // TODO: this is just to pass for FHIR, we need to map whatever we track for race onto this.
                // TODO: we need to populate these values
            }
            $ombCategory->setValueCoding($coding);
            $raceExtension->addExtension($ombCategory);

            $textExtension = new FHIRExtension();
            $textExtension->setUrl("text");
            $textExtension->setValueString(new FHIRString($ombCategoryCoding->getDisplay()));
            $raceExtension->addExtension($textExtension);
        }

        // TODO: this is a required field, so not sure what we want to do if this is missing?
        if (!empty($dataRecord['ethnicity'])) {
            $ethnicityExtension = new FHIRExtension();
            $ethnicityExtension->setUrl("http://hl7.org/fhir/StructureDefinition/us-core-ethnicity");
            $coding = new FHIRCoding();
            $coding->setSystem(new FHIRUri("http://terminology.hl7.org/CodeSystem/v3-Ethnicity"));
            $codeableConcept = new FHIRCodeableConcept();
            // 2135-2 is Hispanic or Latino
            // 2186-5 is Not Hispanic or Latino
            if ($dataRecord['ethnicity'] != 'not_hisp_or_latin') {
                $coding->setCode("2135-2");
                $coding->setDisplay("Hispanic or Latino");
                $codeableConcept->setText("Hispanic or Latino");
            } else {
                $coding->setCode("2186-5");
                $coding->setDisplay("Not Hispanic or Latino");
                $codeableConcept->setText("Not Hispanic or Latino");
            }
            $codeableConcept->addCoding($coding);
            $ethnicityExtension->setValueCodeableConcept($codeableConcept);
            $patientResource->addExtension($ethnicityExtension);
        }

        // Not sure what to do here but this is on the 2021 HL7 US Core page about SSN
        // * The Patient’s Social Security Numbers SHOULD NOT be used as a patient identifier in Patient.identifier.value.
        // There is increasing concern over the use of Social Security Numbers in healthcare due to the risk of identity
        // theft and related issues. Many payers and providers have actively purged them from their systems and
        // filter them out of incoming data.
        // @see http://hl7.org/fhir/us/core/2021Jan/StructureDefinition-us-core-patient.html#FHIR-27731
        if (!empty($dataRecord['ss'])) {
            $patientResource->addIdentifier(
                $this->createIdentifier(
                    'official',
                    'http://terminology.hl7.org/CodeSystem/v2-0203',
                    'SS',
                    'http://hl7.org/fhir/sid/us-ssn',
                    $dataRecord['ss']
                )
            );
        }

        if (!empty($dataRecord['pubpid'])) {
            $patientResource->addIdentifier(
                // not sure if the SystemURI for PT should be the same or not.
                $this->createIdentifier(
                    'official',
                    'http://terminology.hl7.org/CodeSystem/v2-0203',
                    'PT',
                    'http://terminology.hl7.org/CodeSystem/v2-0203',
                    $dataRecord['pubpid']
                )
            );
        }

        $communication = new FHIRPatientCommunication();
        $languageConcept = new FHIRCodeableConcept();
        $language = new FHIRCoding();
        $language->setSystem(new FHIRUri("urn:ietf:bcp:47"));
        // TODO: @bradymiller @sjpadget what should go here?  What should we pull from here?
        $language->setCode(new FHIRCode('en-US'));
        $language->setDisplay("English");
        $languageConcept->addCoding($language);
        $languageConcept->setText("English");
        $communication->setLanguage($languageConcept);
        $patientResource->addCommunication($communication);

        if ($encode) {
            return json_encode($patientResource);
        } else {
            return $patientResource;
        }
    }

    private function createIdentifier($use, $system, $code, $systemUri, $value): FHIRIdentifier
    {
        $identifier = new FHIRIdentifier();
        $idUse = new FHIRIdentifierUse();
        $idUse->setValue($use);
        $identifier->setUse($idUse);
        $idType = new FHIRCodeableConcept();
        $idTypeCoding = new FHIRCoding();
        $idTypeCoding->setSystem(new FHIRUri($system));
        $idTypeCoding->setCode(new FHIRCode($code));
        $idType->addCoding($idTypeCoding);
        $identifier->setType($idType);
        $identifier->setSystem(new FHIRUri($systemUri));
        $identifier->setValue(new FHIRString($value));
        return $identifier;
    }

    private function createContactPoint($system, $value, $use): FHIRContactPoint
    {
        $contactPoint = new FHIRContactPoint();
        $contactPoint->setSystem(new FHIRContactPointSystem(['value' => $system]));
        $contactPoint->setValue(new FHIRString($value));
        $contactPoint->setUse(new FHIRContactPointUse(['value' => $use]));
        return $contactPoint;
    }

    /**
     * Parses a FHIR Patient Resource, returning the equivalent OpenEMR patient record.
     *
     * @param array $fhirResource The source FHIR resource
     * @return array a mapped OpenEMR data record (array)
     */
    public function parseFhirResource($fhirResource = array())
    {
        $data = array();

        if (isset($fhirResource['id'])) {
            $data['uuid'] = $fhirResource['id'];
        }

        if (isset($fhirResource['name'])) {
            $name = [];
            foreach ($fhirResource['name'] as $sub_name) {
                if ($sub_name['use'] === 'official') {
                    $name = $sub_name;
                    break;
                }
            }
            if (isset($name['family'])) {
                $data['lname'] = $name['family'];
            }
            if ($name['given'][0]) {
                $data['fname'] = $name['given'][0];
            }
            if (isset($name['given'][1])) {
                $data['mname'] = $name['given'][1];
            }
            if (isset($name['prefix'][0])) {
                $data['title'] = $name['prefix'][0];
            }
        }
        if (isset($fhirResource['address'])) {
            if (isset($fhirResource['address'][0]['line'][0])) {
                $data['street'] = $fhirResource['address'][0]['line'][0];
            }
            if (isset($fhirResource['address'][0]['postalCode'][0])) {
                $data['postal_code'] = $fhirResource['address'][0]['postalCode'];
            }
            if (isset($fhirResource['address'][0]['city'][0])) {
                $data['city'] = $fhirResource['address'][0]['city'];
            }
            if (isset($fhirResource['address'][0]['state'][0])) {
                $data['state'] = $fhirResource['address'][0]['state'];
            }
            if (isset($fhirResource['address'][0]['country'][0])) {
                $data['country_code'] = $fhirResource['address'][0]['country'];
            }
        }
        if (isset($fhirResource['telecom'])) {
            foreach ($fhirResource['telecom'] as $telecom) {
                switch ($telecom['system']) {
                    case 'phone':
                        switch ($telecom['use']) {
                            case 'mobile':
                                $data['phone_cell'] = $telecom['value'];
                                break;
                            case 'home':
                                $data['phone_home'] = $telecom['value'];
                                break;
                            case 'work':
                                $data['phone_biz'] = $telecom['value'];
                                break;
                        }
                        break;
                    case 'email':
                        $data['email'] = $telecom['value'];
                        break;
                    default:
                    //Should give Error for incapability
                        break;
                }
            }
        }
        if (isset($fhirResource['birthDate'])) {
            $data['DOB'] = $fhirResource['birthDate'];
        }
        if (isset($fhirResource['gender'])) {
            $data['sex'] = $fhirResource['gender'];
        }

        foreach ($fhirResource['identifier'] as $index => $identifier) {
            if (!isset($identifier['type']['coding'][0])) {
                continue;
            }

            $code = $identifier['type']['coding'][0]['code'];
            switch ($code) {
                case 'SS':
                    $data['ss'] = $identifier['value'];
                    break;
                case 'PT':
                    $data['pubpid'] = $identifier['value'];
                    break;
            }
        }
        return $data;
    }

    /**
     * Inserts an OpenEMR record into the sytem.
     *
     * @param array $openEmrRecord OpenEMR patient record
     * @return ProcessingResult
     */
    public function insertOpenEMRRecord($openEmrRecord)
    {
        return $this->patientService->insert($openEmrRecord);
    }


    /**
     * Updates an existing OpenEMR record.
     *
     * @param $fhirResourceId //The OpenEMR record's FHIR Resource ID.
     * @param $updatedOpenEMRRecord //The "updated" OpenEMR record.
     * @return ProcessingResult
     */
    public function updateOpenEMRRecord($fhirResourceId, $updatedOpenEMRRecord)
    {
        $processingResult = $this->patientService->update($fhirResourceId, $updatedOpenEMRRecord);
        return $processingResult;
    }

    /**
     * Performs a FHIR Patient Resource lookup by FHIR Resource ID
     * @param $fhirResourceId //The OpenEMR record's FHIR Patient Resource ID.
     */
    public function getOne($fhirResourceId)
    {
        $processingResult = $this->patientService->getOne($fhirResourceId);
        if (!$processingResult->hasErrors()) {
            if (count($processingResult->getData()) > 0) {
                $openEmrRecord = $processingResult->getData()[0];
                $fhirRecord = $this->parseOpenEMRRecord($openEmrRecord);
                $processingResult->setData([]);
                $processingResult->addData($fhirRecord);
            }
        }
        return $processingResult;
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     *
     * @param array openEMRSearchParameters OpenEMR search fields
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return ProcessingResult
     */
    public function searchForOpenEMRRecords($openEMRSearchParameters, $puuidBind = null)
    {
        // TODO: @bradymiller all the patient unit tests require this to be set to false for fuzzy matching.  However,
        // we need to redo all of the search stuff to have each search param
        // have it's own search conditions (AND, OR, prefix string, suffix string, fuzzy match, etc).
	return $this->patientService->getAll($openEMRSearchParameters, false, $puuidBind);
    }

    public function createProvenanceResource($dataRecord = array(), $encode = false)
    {
        // TODO: If Required in Future
    }

    /**
     * Grabs all the objects in my service that match the criteria specified in the ExportJob.  If a
     * $lastResourceIdExported is provided, The service executes the same data collection query it used previously and
     * startes processing at the resource that is immediately after (ordered by date) the resource that matches the id of
     * $lastResourceIdExported.  This allows processing of the service to be resumed or paused.
     * @param ExportStreamWriter $writer Object that writes out to a stream any object that extend the FhirResource object
     * @param ExportJob $job The export job we are processing the request for.  Holds all of the context information needed for the export service.
     * @return void
     * @throws ExportWillShutdownException  Thrown if the export is about to be shutdown and all processing must be halted.
     * @throws ExportException  If there is an error in processing the export
     * @throws ExportCannotEncodeException Thrown if the resource cannot be properly converted into the right format (ie JSON).
     */
    public function export(ExportStreamWriter $writer, ExportJob $job, $lastResourceIdExported = null): void
    {
        // we have no concept of date created & date modified in the system for patients, so we just return everything

        $type = $job->getExportType();

        $searchParams = [];
        if ($type == ExportJob::EXPORT_OPERATION_GROUP) {
            $group = $job->getGroupId();
            // we would need to grab all of the patient ids that belong to this group
            // TODO: if we fully implement groups of patient populations we would set our patient ids into $searchParams
            // or filter the results here
        }

        $processingResult = $this->getAll($searchParams);
        $patientData = $processingResult->getData();
        foreach ($patientData as $patient) {
            if (!($patient instanceof FHIRPatient)) {
                throw new ExportException("Patient Service return records that are not a valid patient resource", 0, $lastResourceIdExported);
            }
            $writer->append($patient);
            $lastResourceIdExported = $patient->getId();
        }
    }

    /**
     * Returns whether the service supports the system export operation
     * @see https://hl7.org/fhir/uv/bulkdata/export/index.html#endpoint---system-level-export
     * @return bool true if this resource service should be called for a system export operation, false otherwise
     */
    public function supportsSystemExport()
    {
        return true;
    }

    /**
     * Returns whether the service supports the group export operation.
     * Note only resources in the Patient compartment SHOULD be returned unless the resource assists in interpreting
     * patient data (such as Organization or Practitioner)
     * @see https://hl7.org/fhir/uv/bulkdata/export/index.html#endpoint---group-of-patients
     * @return bool true if this resource service should be called for a group export operation, false otherwise
     */
    public function supportsGroupExport()
    {
        return true;
    }

    /**
     * Returns whether the service supports the all patient export operation
     * Note only resources in the Patient compartment SHOULD be returned unless the resource assists in interpreting
     * patient data (such as Organization or Practitioner)
     * @see https://hl7.org/fhir/uv/bulkdata/export/index.html#endpoint---all-patients
     * @return bool true if this resource service should be called for a patient export operation, false otherwise
     */
    public function supportsPatientExport()
    {
        return true;
    }

    /**
     * We only have one profile URI we need to return here
     * @return array
     */
    public function getProfileURIs(): array
    {
        return [self::USCGI_PROFILE_URI];
    }
}
