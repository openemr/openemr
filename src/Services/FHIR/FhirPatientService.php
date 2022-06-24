<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRElement\FHIRCode;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRContactPoint;
use OpenEMR\FHIR\R4\FHIRElement\FHIRContactPointSystem;
use OpenEMR\FHIR\R4\FHIRElement\FHIRContactPointUse;
use OpenEMR\FHIR\R4\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifierUse;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUri;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\FHIR\R4\FHIRResource\FHIRPatient\FHIRPatientCommunication;
use OpenEMR\Services\FHIR\Traits\BulkExportSupportAllOperationsTrait;
use OpenEMR\Services\FHIR\Traits\FhirBulkExportDomainResourceTrait;
use OpenEMR\Services\ListService;
use OpenEMR\Services\PatientService;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPatient;
use OpenEMR\FHIR\R4\FHIRElement\FHIRHumanName;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAdministrativeGender;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Services\Search\TokenSearchValue;
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
class FhirPatientService extends FhirServiceBase implements IFhirExportableResourceService, IResourceUSCIGProfileService, IPatientCompartmentResourceService
{
    use BulkExportSupportAllOperationsTrait;
    use FhirBulkExportDomainResourceTrait;

    /**
     * @var PatientService
     */
    private $patientService;

    /**
     * @var ListService
     */
    private $listService;

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

    const FIELD_NAME_GENDER = 'sex';

    public function __construct()
    {
        parent::__construct();
        $this->patientService = new PatientService();
        $this->listService = new ListService();
    }

    /**
     * Returns an array mapping FHIR Patient Resource search parameters to OpenEMR Patient search parameters
     * @return array The search parameters
     */
    protected function loadSearchParameters()
    {
        // @see https://www.hl7.org/fhir/patient.html#search
        return  [
            // core FHIR required fields for now
            '_id' => $this->getPatientContextSearchField(),
            'identifier' => new FhirSearchParameterDefinition('identifier', SearchFieldType::TOKEN, ['ss', 'pubpid']),
            'name' => new FhirSearchParameterDefinition('name', SearchFieldType::STRING, ['title', 'fname', 'mname', 'lname']),
            'birthdate' => new FhirSearchParameterDefinition('birthdate', SearchFieldType::DATE, ['DOB']),
            'gender' => new FhirSearchParameterDefinition('gender', SearchFieldType::TOKEN, [self::FIELD_NAME_GENDER]),
            'address' => new FhirSearchParameterDefinition(
                'address',
                SearchFieldType::STRING,
                ['street', 'street_line_2', 'postal_code', 'city', 'state', 'contact_address_line1'
                    , 'contact_address_line2', 'contact_address_postal_code', 'contact_address_city'
                ,
                'contact_address_state',
                'contact_address_district']
            ),

            // these are not standard in US Core
            'address-city' => new FhirSearchParameterDefinition(
                'address-city',
                SearchFieldType::STRING,
                ['city', 'contact_address_city']
            ),
            'address-postalcode' => new FhirSearchParameterDefinition(
                'address-postalcode',
                SearchFieldType::STRING,
                ['postal_code', 'contact_address_postal_code']
            ),
            'address-state' => new FhirSearchParameterDefinition(
                'address-state',
                SearchFieldType::STRING,
                ['state', 'contact_address_state']
            ),

            'email' => new FhirSearchParameterDefinition('email', SearchFieldType::TOKEN, ['email']),
            'family' => new FhirSearchParameterDefinition('family', SearchFieldType::STRING, ['lname']),
            'given' => new FhirSearchParameterDefinition('given', SearchFieldType::STRING, ['fname', 'mname']),
            'phone' => new FhirSearchParameterDefinition('phone', SearchFieldType::TOKEN, ['phone_home', 'phone_biz', 'phone_cell']),
            'telecom' => new FhirSearchParameterDefinition('telecom', SearchFieldType::TOKEN, ['email', 'phone_home', 'phone_biz', 'phone_cell'])
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
        $patientResource->setMeta(new FHIRMeta($meta));

        $patientResource->setActive(true);
        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $patientResource->setId($id);

        $this->parseOpenEMRPatientSummaryText($patientResource, $dataRecord);
        $this->parseOpenEMRPatientName($patientResource, $dataRecord);
        $this->parseOpenEMRPatientAddress($patientResource, $dataRecord);
        $this->parseOpenEMRPatientTelecom($patientResource, $dataRecord);

        $this->parseOpenEMRDateOfBirth($patientResource, $dataRecord['DOB']);
        $this->parseOpenEMRGenderAndBirthSex($patientResource, $dataRecord['sex']);
        $this->parseOpenEMRRaceRecord($patientResource, $dataRecord['race']);
        $this->parseOpenEMREthnicityRecord($patientResource, $dataRecord['ethnicity']);
        $this->parseOpenEMRSocialSecurityRecord($patientResource, $dataRecord['ss']);
        $this->parseOpenEMRPublicPatientIdentifier($patientResource, $dataRecord['pubpid']);
        $this->parseOpenEMRCommunicationRecord($patientResource, $dataRecord['language']);


        if ($encode) {
            return json_encode($patientResource);
        } else {
            return $patientResource;
        }
    }

    private function parseOpenEMRPatientSummaryText(FHIRPatient $patientResource, $dataRecord)
    {

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
    }

    private function parseOpenEMRDateOfBirth(FHIRPatient $patientResource, $dateOfBirth)
    {
        if (isset($dateOfBirth)) {
            $patientResource->setBirthDate($dateOfBirth);
        }
    }

    private function parseOpenEMRPatientName(FHIRPatient $patientResource, $dataRecord)
    {

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

        if (!empty($dataRecord['suffix'])) {
            $name->addSuffix($dataRecord['suffix']);
        }

        $patientResource->addName($name);

        if (!empty($dataRecord['previous_names'])) {
            foreach ($dataRecord['previous_names'] as $prevName) {
                $previousHumanName = new FHIRHumanName();
                $previousHumanName->setUse("old");
                if (!empty($prevName['previous_name_first'])) {
                    $previousHumanName->addGiven($prevName['previous_name_first']);
                }
                if (!empty($prevName['previous_name_last'])) {
                    $previousHumanName->setFamily($prevName['previous_name_last']);
                }
                if (!empty($prevName['previous_name_middle'])) {
                    $previousHumanName->addGiven($prevName['previous_name_middle']);
                }
                if (!empty($prevName['previous_name_title'])) {
                    $previousHumanName->addPrefix($prevName['previous_name_title']);
                }
                if (!empty($prevName['previous_name_suffix'])) {
                    $previousHumanName->addSuffix($prevName['previous_name_suffix']);
                }
                if (!empty($prevName['previous_name_enddate'])) {
                    $fhirPeriod = new FHIRPeriod();
                    $fhirPeriod->setEnd(gmdate('c', strtotime($prevName['previous_name_enddate'])));
                    $previousHumanName->setPeriod($fhirPeriod);
                }
                $patientResource->addName($previousHumanName);
            }
        }
    }

    private function parseOpenEMRPatientAddress(FHIRPatient $patientResource, $dataRecord)
    {
        if (!empty($dataRecord['addresses'])) {
            foreach ($dataRecord['addresses'] as $address) {
                $address = UtilsService::createAddressFromRecord($address);
                if ($address !== null) {
                    $patientResource->addAddress($address);
                }
            }
        }
    }

    private function parseOpenEMRPatientTelecom(FHIRPatient $patientResource, $dataRecord)
    {

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
    }

    private function parseOpenEMRGenderAndBirthSex(FHIRPatient $patientResource, $sex)
    {
        // @see https://www.hl7.org/fhir/us/core/ValueSet-birthsex.html
        $genderValue = $sex ?? 'Unknown';
        $birthSex = "UNK";
        $gender = new FHIRAdministrativeGender();
        $birthSexExtension = new FHIRExtension();
        if ($genderValue !== 'Unknown') {
            if ($genderValue === 'Male') {
                $birthSex = 'M';
            } else if ($genderValue === 'Female') {
                $birthSex = 'F';
            }
        }
        $gender->setValue(strtolower($genderValue));
        $birthSexExtension->setUrl("http://hl7.org/fhir/us/core/StructureDefinition/us-core-birthsex");
        $birthSexExtension->setValueCode($birthSex);
        $patientResource->addExtension($birthSexExtension);
        $patientResource->setGender($gender);
    }
    private function parseOpenEMRRaceRecord(FHIRPatient $patientResource, $race)
    {
        $code = 'UNK';
        $display = xlt("Unknown");
        $system = FhirCodeSystemConstants::HL7_NULL_FLAVOR;
        // race is defined as containing 2 required extensions, text & ombCategory
        $raceExtension = new FHIRExtension();
        $raceExtension->setUrl(FhirCodeSystemConstants::HL7_US_CORE_RACE);

        $ombCategory = new FHIRExtension();
        $ombCategory->setUrl("ombCategory");
        $ombCategoryCoding = new FHIRCoding();

        if (!empty($race)) {
            $record = $this->listService->getListOption('race', $race);
            if ($race === 'declne_to_specfy') { // TODO: we should rename this mispelled value in the database
                // @see https://www.hl7.org/fhir/us/core/ValueSet-omb-race-category.html
                $code = "ASKU";
                $display = xlt("Asked but no answer");
            } else if (!empty($record)) {
                $code = $record['notes'];
                $display = $record['title'];
                $system = FhirCodeSystemConstants::OID_RACE_AND_ETHNICITY;
            }
        }
        $ombCategoryCoding->setSystem(new FHIRUri($system));
        $ombCategoryCoding->setCode($code);
        $ombCategoryCoding->setDisplay(xlt($display));
        $ombCategory->setValueCoding($ombCategoryCoding);
        $raceExtension->addExtension($ombCategory);

        $textExtension = new FHIRExtension();
        $textExtension->setUrl("text");
        $textExtension->setValueString(new FHIRString($ombCategoryCoding->getDisplay()));
        $raceExtension->addExtension($textExtension);
        $patientResource->addExtension($raceExtension);
    }

    private function parseOpenEMREthnicityRecord(FHIRPatient $patientResource, $ethnicity)
    {
        // TODO: this is a required field, so not sure what we want to do if this is missing?
        if (!empty($ethnicity)) {
            $ethnicityExtension = new FHIRExtension();
            $ethnicityExtension->setUrl("http://hl7.org/fhir/us/core/StructureDefinition/us-core-ethnicity");

            $ombCategoryExtension = new FHIRExtension();
            $ombCategoryExtension->setUrl("ombCategory");

            $textExtension = new FHIRExtension();
            $textExtension->setUrl("text");



            $record = $this->listService->getListOption('ethnicity', $ethnicity);
            if (!empty($record)) {
                $textExtension->setValueString($record['title']);
                // the only possible options for ombCategory are hispanic or not hispanic
                if ($record['option_id'] != 'declne_to_specfy') {
                    $coding = new FHIRCoding();
                    $coding->setSystem(new FHIRUri("http://terminology.hl7.org/CodeSystem/v3-Ethnicity"));
                    $coding->setCode($record['notes']);
                    $coding->setDisplay($record['title']);
                    $coding->setSystem("urn:oid:2.16.840.1.113883.6.238");
                    $ombCategoryExtension->setValueCoding($coding);
                    $ethnicityExtension->addExtension($ombCategoryExtension);
                }
            }

            $ethnicityExtension->addExtension($textExtension);
            $patientResource->addExtension($ethnicityExtension);
        }
    }

    private function parseOpenEMRSocialSecurityRecord(FHIRPatient $patientResource, $ssn)
    {
        // Not sure what to do here but this is on the 2021 HL7 US Core page about SSN
        // * The Patient’s Social Security Numbers SHOULD NOT be used as a patient identifier in Patient.identifier.value.
        // There is increasing concern over the use of Social Security Numbers in healthcare due to the risk of identity
        // theft and related issues. Many payers and providers have actively purged them from their systems and
        // filter them out of incoming data.
        // @see http://hl7.org/fhir/us/core/2021Jan/StructureDefinition-us-core-patient.html#FHIR-27731
        if (!empty($ssn)) {
            $patientResource->addIdentifier(
                $this->createIdentifier(
                    'official',
                    'http://terminology.hl7.org/CodeSystem/v2-0203',
                    'SS',
                    'http://hl7.org/fhir/sid/us-ssn',
                    $ssn
                )
            );
        }
    }

    private function parseOpenEMRPublicPatientIdentifier(FHIRPatient $patientResource, $pubpid)
    {
        if (!empty($pubpid)) {
            $patientResource->addIdentifier(
            // not sure if the SystemURI for PT should be the same or not.
                $this->createIdentifier(
                    'official',
                    'http://terminology.hl7.org/CodeSystem/v2-0203',
                    'PT',
                    'http://terminology.hl7.org/CodeSystem/v2-0203',
                    $pubpid
                )
            );
        }
    }

    private function parseOpenEMRCommunicationRecord(FHIRPatient $patientResource, $language)
    {
        $record = $this->listService->getListOption('language', $language);
        if (empty($language) || empty($record)) {
            $communication = new FHIRPatientCommunication();
            $communication->setLanguage(UtilsService::createDataAbsentUnknownCodeableConcept());
            $patientResource->addCommunication($communication);
        } else {
            $communication = new FHIRPatientCommunication();
            $languageConcept = new FHIRCodeableConcept();
            $language = new FHIRCoding();
            $language->setSystem(new FHIRUri(FhirCodeSystemConstants::LANGUAGE_BCP_47));
            $language->setCode(new FHIRCode($record['notes']));
            $language->setDisplay(xlt($record['title']));
            $languageConcept->addCoding($language);
            $languageConcept->setText(xlt($record['title']));
            $communication->setLanguage($languageConcept);
            $patientResource->addCommunication($communication);
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
    public function parseFhirResource(FHIRDomainResource $fhirResource)
    {
        // TODO: ONC certification only deals with READ operations, the mapping of FHIR values such as language,ethnicity
        // etc are NOT being done here and so the creation/updating of resources is currently NOT correct, this will
        // need to be addressed by future development work.
        if (!$fhirResource instanceof FHIRPatient) {
            throw new \BadMethodCallException("fhir resource must be of type " . FHIRPractitioner::class);
        }

        $data = array();
        $data['uuid'] = (string)$fhirResource->getId() ?? null;

        if (!empty($fhirResource->getName())) {
            $name = new FHIRHumanName();
            foreach ($fhirResource->getName() as $sub_name) {
                if ((string)$sub_name->getUse() === 'official') {
                    $name = $sub_name;
                    break;
                }
            }
            $data['lname'] = (string)$name->getFamily() ?? null;

            $given = $name->getGiven() ?? [];
            // we cast due to the way FHIRString works
            $data['fname'] = (string)($given[0] ?? null);
            $data['mname'] = (string)($given[1] ?? null);

            $prefix = $name->getPrefix() ?? [];
            // we don't support updating the title right now, it requires updating another table which is breaking
            // the service class.  As far as I can tell, this was never tested and never worked.
            $data['title'] = $prefix[0] ?? null;
        }

        $addresses = $fhirResource->getAddress();
        if (!empty($addresses)) {
            $activeAddress = $addresses[0];
            $mostRecentPeriods = UtilsService::getPeriodTimestamps($activeAddress->getPeriod());
            foreach ($fhirResource->getAddress() as $address) {
                $addressPeriod = UtilsService::getPeriodTimestamps($address->getPeriod());
                if (empty($addressPeriod['end'])) {
                    $activeAddress = $address;
                } else if (!empty($mostRecentPeriods['end']) && $addressPeriod['end'] > $mostRecentPeriods['end']) {
                    // if our current period is more recent than our most recent address we want to grab that one
                    $mostRecentPeriods = $addressPeriod;
                    $activeAddress = $address;
                }
            }

            $lineValues = array_map(function ($val) {
                return (string)$val;
            }, $activeAddress->getLine() ?? []);
            $data['street'] = implode("\n", $lineValues) ?? null;
            $data['postal_code'] = (string)$activeAddress->getPostalCode() ?? null;
            $data['city'] = (string)$activeAddress->getCity() ?? null;
            $data['state'] = (string)$activeAddress->getState() ?? null;
        }

        $telecom = $fhirResource->getTelecom();
        if (!empty($telecom)) {
            foreach ($telecom as $contactPoint) {
                $systemValue = (string)$contactPoint->getSystem() ?? "contact_other";
                $contactValue = (string)$contactPoint->getValue();
                if ($systemValue === 'email') {
                    $data[$systemValue] = (string)$contactValue;
                } else if ($systemValue == "phone") {
                    $use = (string)$contactPoint->getUse() ?? "work";
                    $useMapping = ['mobile' => 'phone_cell', 'home' => 'phone_home', 'work' => 'phone_biz'];
                    if (isset($useMapping[$use])) {
                        $data[$useMapping[$use]] = $contactValue;
                    }
                }
            }
        }

        $data['DOB'] = (string)$fhirResource->getBirthDate();
        $data['sex'] = (string)$fhirResource->getGender();

        foreach ($fhirResource->getIdentifier() as $index => $identifier) {
            $type = $identifier->getType();
            $validCodes = ['SS' => 'ss', 'PT' => 'pubpid'];
            $coding = $type->getCoding() ?? [];
            foreach ($coding as $codingItem) {
                $codingCode = (string)$codingItem->getCode();

                if (isset($validCodes[$codingCode])) {
                    $data[$validCodes[$codingCode]] = $identifier->getValue() ?? null;
                }
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
     * Searches for OpenEMR records using OpenEMR search parameters
     *
     * @param ISearchField[] openEMRSearchParameters OpenEMR search fields
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return ProcessingResult
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        // do any conversions on the data that we need here

        // we need to process our gender values here.
        if (isset($openEMRSearchParameters[self::FIELD_NAME_GENDER])) {
            /**
             * @var $field ISearchField
             */
            $field = $openEMRSearchParameters[self::FIELD_NAME_GENDER];

            $upperCaseCode = function (TokenSearchValue $tokenSearchValue) {
                $tokenSearchValue->setCode(ucfirst($tokenSearchValue->getCode()));
                return $tokenSearchValue;
            };

            // need to convert our gender's to a format that are stored in the database.
            $field->setValues(array_map($upperCaseCode, $field->getValues()));
        }

        return $this->patientService->search($openEMRSearchParameters);
    }

    public function createProvenanceResource($dataRecord, $encode = false)
    {
        if (!($dataRecord instanceof FHIRPatient)) {
            throw new \BadMethodCallException("Data record should be correct instance class");
        }
        $provenanceService = new FhirProvenanceService();
        return $provenanceService->createProvenanceForDomainResource($dataRecord);
    }

    /**
     * We only have one profile URI we need to return here
     * @return array
     */
    public function getProfileURIs(): array
    {
        return [self::USCGI_PROFILE_URI];
    }

    public function getPatientContextSearchField(): FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)]);
    }
}
