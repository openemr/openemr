<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPractitioner;
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
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUri;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\FHIR\R4\FHIRResource\FHIRPatient\FHIRPatientCommunication;
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\FHIR\Traits\BulkExportSupportAllOperationsTrait;
use OpenEMR\Services\FHIR\Traits\FhirBulkExportDomainResourceTrait;
use OpenEMR\Services\FHIR\Traits\VersionedProfileTrait;
use OpenEMR\Services\ListService;
use OpenEMR\Services\PatientService;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPatient;
use OpenEMR\FHIR\R4\FHIRElement\FHIRHumanName;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAdministrativeGender;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\SearchQueryConfig;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Services\Search\TokenSearchValue;
use OpenEMR\Validators\ProcessingResult;

/**
 * FHIR Patient Service
 *
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
    use VersionedProfileTrait;

    /**
     * @var PatientService
     */
    private PatientService $patientService;

    /**
     * @var ?ListService
     */
    private ?ListService $listService;

    /**
     * @var CodeTypesService
     */
    private CodeTypesService $codeTypesService;

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

    private ?array $searchParameters = null;

    /**
     * @var array <string,array>  Cache of list options keyed by list_id then option_id for faster lookup
     */
    private array $cachedListOptions = [];

    /**
     * @var array <string,array>  Cache of list options keyed by list_id then code for faster lookup.  Codes are treated as unique within a given list_id
     */
    private array $cachedListOptionsByCode = [];

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
            'name' => new FhirSearchParameterDefinition('name', SearchFieldType::STRING, ['fname', 'mname', 'lname', 'title']),
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

            'email' => new FhirSearchParameterDefinition('email', SearchFieldType::TOKEN, ['email','email_direct']),
            'family' => new FhirSearchParameterDefinition('family', SearchFieldType::STRING, ['lname']),
            'given' => new FhirSearchParameterDefinition('given', SearchFieldType::STRING, ['fname', 'mname']),
            'phone' => new FhirSearchParameterDefinition('phone', SearchFieldType::TOKEN, ['phone_home', 'phone_biz', 'phone_cell']),
            'telecom' => new FhirSearchParameterDefinition('telecom', SearchFieldType::TOKEN, ['email','email_direct', 'phone_home', 'phone_biz', 'phone_cell']),
            '_lastUpdated' => $this->getLastModifiedSearchField(),
            'generalPractitioner' => new FhirSearchParameterDefinition('generalPractitioner', SearchFieldType::REFERENCE, ['provider_uuid'])
        ];
    }

    public function getListService(): ListService
    {
        if (!isset($this->listService)) {
            $this->listService = new ListService();
        }
        return $this->listService;
    }

    public function setListService(ListService $listService): void
    {
        $this->listService = $listService;
    }


    public function getLastModifiedSearchField(): ?FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['last_updated']);
    }

    /**
     * Parses an OpenEMR patient record, returning the equivalent FHIR Patient Resource
     *
     * @param array $dataRecord The source OpenEMR data record
     * @param boolean $encode Indicates if the returned resource is encoded into a string. Defaults to false.
     * @return FHIRPatient
     */
    public function parseOpenEMRRecord($dataRecord = [], $encode = false)
    {
        $patientResource = new FHIRPatient();

        $meta = new FHIRMeta();
        $meta->setVersionId('1');
        if (!empty($dataRecord['last_updated'])) {
            $meta->setLastUpdated(UtilsService::getLocalDateAsUTC($dataRecord['last_updated']));
        } else {
            $meta->setLastUpdated(UtilsService::getDateFormattedAsUTC());
        }
        foreach ($this->getProfileForVersions(self::USCGI_PROFILE_URI, $this->getSupportedVersions()) as $profile) {
            $meta->addProfile($profile);
        }
        $patientResource->setMeta($meta);

        $patientResource->setActive(true);
        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $patientResource->setId($id);

        $this->parseOpenEMRPatientSummaryText($patientResource, $dataRecord);
        $this->parseOpenEMRPatientName($patientResource, $dataRecord);
        $this->parseOpenEMRPatientAddress($patientResource, $dataRecord);
        $this->parseOpenEMRPatientTelecom($patientResource, $dataRecord);

        $this->parseOpenEMRDateOfBirth($patientResource, $dataRecord['DOB'] ?? null);
        $this->parseOpenEMRGenderAndBirthSex($patientResource, $dataRecord['sex'] ?? 'Unknown');
        $this->parseOpenEMRRaceRecord($patientResource, $dataRecord['race'] ?? '');
        $this->parseOpenEMREthnicityRecord($patientResource, $dataRecord['ethnicity'] ?? '');
        $this->parseOpenEMRSocialSecurityRecord($patientResource, $dataRecord['ss'] ?? null);
        $this->parseOpenEMRPublicPatientIdentifier($patientResource, $dataRecord['pubpid'] ?? null);
        $this->parseOpenEMRCommunicationRecord($patientResource, $dataRecord['language'] ?? null);
        $this->parseOpenEMRGeneralPractitioner($patientResource, $dataRecord);

        // US Core 6.1.1 Extensions
        // us-core-race -> race, us-core-ethnicity -> ethnicity, tribalAffiliation, us-core-birthsex -> birthsex, sex, genderIdentity
        // birthsex and genderIdentity are handled already in parseOpenEMRGenderAndBirthSex
        $this->parseOpenEMRGenderIdentity($patientResource, $dataRecord);
        $this->parseOpenEMRPatientSexExtension($patientResource, $dataRecord);
        $this->parseOpenEMRPatientTribalAffiliationExtension($patientResource, $dataRecord);

        // US Core 7.0.0 Extensions
        // nothing added here

        // US Core 8.0.0 Extensions
        // drops genderIdentity,birthSex, adds interpreterRequired
        $this->parseOpenEMRPatientInterpreterNeededExtension($patientResource, $dataRecord);

        // Deceased date
        $this->parseOpenEMRPatientDeceasedDateTime($patientResource, $dataRecord);

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
            $text = [
                'status' => 'generated',
                'div' => '<div xmlns="http://www.w3.org/1999/xhtml"> <p>' . $narrativeText . '</p></div>'
            ];
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
                    $fhirPeriod->setEnd(UtilsService::getLocalDateAsUTC($prevName['previous_name_enddate']));
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
            $patientResource->addTelecom(UtilsService::createContactPoint($dataRecord['phone_home'], 'phone','home'));
        }

        if (!empty($dataRecord['phone_biz'])) {
            $patientResource->addTelecom(UtilsService::createContactPoint($dataRecord['phone_biz'], 'phone', 'work'));
        }

        if (!empty($dataRecord['phone_cell'])) {
            $patientResource->addTelecom(UtilsService::createContactPoint($dataRecord['phone_cell'],'phone',  'mobile'));
        }

        if (!empty($dataRecord['email'])) {
            $patientResource->addTelecom(UtilsService::createContactPoint($dataRecord['email'],'email',  'home'));
        }
        if (!empty($dataRecord['email_direct'])) {
            $patientResource->addTelecom(UtilsService::createContactPoint($dataRecord['email_direct'],'email','mobile'));
            // "mobile" per spec:
            //    "A telecommunication device that moves and stays with its owner.
            //    May have characteristics of all other use codes, suitable for urgent matters,
            //    not the first choice for routine business."
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
            } elseif ($genderValue === 'Female') {
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
            $record = $this->getCachedListOption('race', $race);
            if ($race === 'declne_to_specfy') { // TODO: we should rename this mispelled value in the database
                // @see https://www.hl7.org/fhir/us/core/ValueSet-omb-race-category.html
                $code = "ASKU";
                $display = xlt("Asked but no answer");
            } elseif (!empty($record)) {
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

            $record = $this->getCachedListOption('ethnicity', $ethnicity);
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

    protected function getCachedListOptionByCode($list_id, $code): ?array
    {
        // TODO: str_contains works for now but if a code is a subset of another code this will fail...
        // we may need to do parseCode and do another cached list by codes to make this accurate.
        if (!isset($this->cachedListOptionsByCode[$list_id])) {
            if (!isset($this->cachedListOptions[$list_id])) {
                $options = $this->getListService()->getOptionsByListName($list_id);
                foreach ($options as $option) {
                    $this->cachedListOptions[$list_id][$option['option_id']] = $option;
                    $parsedCode = $this->getCodeTypesService()->parseCode($option['codes']);
                    $this->cachedListOptionsByCode[$list_id][$parsedCode['code']] = $option;
                }
            } else {
                foreach ($this->cachedListOptions[$list_id] as $option) {
                    $parsedCode = $this->getCodeTypesService()->parseCode($option['codes']);
                    $this->cachedListOptionsByCode[$list_id][$parsedCode['code']] = $option;
                }
            }
        }
        return $this->cachedListOptionsByCode[$list_id][$code] ?? null;
    }
    protected function getCachedListOption($list_id, $option_id): ?array
    {
        if (!isset($this->cachedListOptions[$list_id])) {
            $this->cachedListOptions[$list_id] = [];
        }
        if (!isset($this->cachedListOptions[$list_id][$option_id])) {
            $options = $this->getListService()->getOptionsByListName($list_id);
            foreach ($options as $option) {
                $this->cachedListOptions[$list_id][$option['option_id']] = $option;
            }
        }
        return $this->cachedListOptions[$list_id][$option_id] ?? null;
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
        $record = $this->getCachedListOption('language', $language);
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

    private function parseOpenEMRGeneralPractitioner(FHIRPatient $patientResource, array $dataRecord)
    {
        if (!empty($dataRecord['provider_uuid'])) {
            $patientResource->addGeneralPractitioner(UtilsService::createRelativeReference('Practitioner', $dataRecord['provider_uuid']));
        }
    }

    protected function parseOpenEMRGenderIdentity(FhirPatient $patientResource, array $dataRecord): void
    {
        if (!empty($dataRecord['gender_identity'])) {
            $genderIdentityExtension = new FHIRExtension();
            $genderIdentityExtension->setUrl('http://hl7.org/fhir/us/core/StructureDefinition/us-core-genderidentity');
            $code = 'UNK';
            $system = FhirCodeSystemConstants::HL7_NULL_FLAVOR;
            $display = 'Unknown';

            if ($dataRecord['gender_identity'] == 'asked-declined') {
                $genderIdentityExtension->setValueCodeableConcept(UtilsService::createDataAbsentUnknownCodeableConcept());
            } else if ($dataRecord['gender_identity'] == 'OTH') {
                $code = 'OTH';
                $system = FhirCodeSystemConstants::HL7_NULL_FLAVOR;
                $display = 'Other';
            } else if ($dataRecord['gender_identity'] === 'UNK') {
                $code = 'UNK';
                $system = FhirCodeSystemConstants::HL7_NULL_FLAVOR;
                $display = 'Unknown';
            } else {
                $record = $this->getCachedListOption('gender', $dataRecord['gender_identity']);
                if (!empty($record)) {
                    $parsedCode = $this->getCodeTypesService()->parseCode($record['codes']);
                    $this->getCodeTypesService()->getSystemForCodeType($parsedCode['code_type']);
                    $code = $parsedCode['code'];
                    $display = $record['title'];
                }
            }
            if (isset($code)) {
                $genderIdentityExtension->setValueCodeableConcept(UtilsService::createCodeableConcept([
                    $code => [
                        'code' => $code,
                        'system' => $system,
                        'description' => $display
                    ]
                    ,FhirCodeSystemConstants::SNOMED_CT
                    ,$display ?? 'Unknown'
                ]));
                $patientResource->addExtension($genderIdentityExtension);
            }
        }
    }

    /**
     * Parses OpenEMR administrative sex data into US Core sex extension
     */
    protected function parseOpenEMRPatientSexExtension(FHIRPatient $patientResource, array $dataRecord)
    {
        // we have two different implementations based upon the highest US Core version we are supporting.
        // US Core 8.0.0 implemented a breaking change by switching from using a Code datatype to a Coding datatype.

        $sexExtension = new FHIRExtension();
        $sexExtension->setUrl('http://hl7.org/fhir/us/core/StructureDefinition/us-core-sex');
        $code = UtilsService::UNKNOWNABLE_CODE_DATA_ABSENT;
        $system = CodeTypesService::CODE_TYPE_DATE_ABSENT_REASON;
        $display = "Unknown";
        if (!empty($dataRecord['sex_identified'])) {
            $record = $this->getCachedListOption('administrative_sex', $dataRecord['sex_identified']);
            if (!empty($record)) { // valid entry so we can set the value
                $parsedCode = $this->getCodeTypesService()->parseCode($record['codes']);
                $code = $parsedCode['code'];
                $system = $this->getCodeTypesService()->getSystemForCodeType($parsedCode['code_type']);
                $display = $record['title'];
            }
        }

        if ($this->getHighestCompatibleUSCoreProfileVersion() === self::PROFILE_VERSION_8_0_0) {
            $coding = UtilsService::createCoding($code, $display, $system);
            $sexExtension->setValueCoding($coding);
        } else {
            $fhirCode = new FHIRCode();
            $fhirCode->setValue($code);
            $sexExtension->setValueCode($fhirCode);
        }
        $patientResource->addExtension($sexExtension);
    }

    /**
     * Parses OpenEMR tribal affiliation data into US Core tribal-affiliation extension
     */
    protected function parseOpenEMRPatientTribalAffiliationExtension(FHIRPatient $patientResource, array $dataRecord): void
    {
        if (!empty($dataRecord['tribal_affiliations'])) {
            // for now we just handle a single tribal affiliation
            $record = $this->getCachedListOption('tribal_affiliations', $dataRecord['tribal_affiliations']);
            if (empty($record)) {
                $this->getSystemLogger()->error("Tribal affiliations not found for option_id", ['option_id' => $dataRecord['tribal_affiliations']]);
                return;
            }
            $tribalAffiliations[$record['option_id']] = $record['title'] ?? $dataRecord['tribal_affiliations'];
            foreach ($tribalAffiliations as $code => $tribalAffiliation) {
                if (!empty($tribalAffiliation)) {
                    $tribalExtension = new FHIRExtension();
                    $tribalExtension->setUrl('http://hl7.org/fhir/us/core/StructureDefinition/us-core-tribal-affiliation');

                    $tribalValueExtension = new FHIRExtension();
                    $tribalValueExtension->setUrl('tribalAffiliation');
                    $tribalValueExtension->setValueCodeableConcept(UtilsService::createCodeableConcept([
                        $code => [
                            'code' => $code,
                            'system' => 'http://terminology.hl7.org/CodeSystem/v3-TribalEntityUS',
                            'description' => $tribalAffiliation
                        ]
                    ]));
                    $tribalExtension->addExtension($tribalValueExtension);
                    $patientResource->addExtension($tribalExtension);
                }
            }
        }
    }

    /**
     * Parses OpenEMR interpreter needed data into US Core interpreter-needed extension
     */
    protected function parseOpenEMRPatientInterpreterNeededExtension(FHIRPatient $patientResource, array $dataRecord)
    {
        $interpreterExtension = new FHIRExtension();
        $interpreterExtension->setUrl('http://hl7.org/fhir/us/core/StructureDefinition/us-core-interpreter-needed');
        // default to unknown
        $code = UtilsService::UNKNOWNABLE_CODE_DATA_ABSENT;
        $system = FhirCodeSystemConstants::DATA_ABSENT_REASON_CODE_SYSTEM;
        $display = "Unknown";
        // per spec we can implement in patient profile OR in the encounter.. we are implementing in profile here.
        if (isset($dataRecord['interpreter_needed'])) {
            $record = $this->getCachedListOption('yes_no_unknown', $dataRecord['interpreter_needed']);
            if (!empty($record)) { // valid entry so we can set the value
                $parsedCode = $this->getCodeTypesService()->parseCode($record['codes']);
                $code = $parsedCode['code'];
                $system = $this->getCodeTypesService()->getSystemForCodeType($parsedCode['code_type']);
                $display = $record['title'];
            }
        }
        $coding = UtilsService::createCoding($code, $display, $system);
        $interpreterExtension->setValueCoding($coding);
        $patientResource->addExtension($interpreterExtension);
    }

    /**
     * Parses OpenEMR deceased date into FHIR deceasedDateTime
     * @param FHIRPatient $patientResource
     * @param array $dataRecord
     */
    protected function parseOpenEMRPatientDeceasedDateTime(FHIRPatient $patientResource, array $dataRecord): void
    {
        // note this is a 0..1 field so we either have a date or we explicitly state false
        if (!empty($dataRecord['deceased_date'])) {
            $deceasedDateTime = new FHIRDateTime();
            $deceasedDateTime->setValue(UtilsService::getLocalDateAsUTC($dataRecord['deceased_date']));
            $patientResource->setDeceasedDateTime($deceasedDateTime);
        } else {
            // explicitly state not deceased
            $patientResource->setDeceasedBoolean(false);
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

        $data = [];
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
            $mostRecentPeriods = $activeAddress->getPeriod() !== null ? UtilsService::getPeriodTimestamps($activeAddress->getPeriod()) : [];
            foreach ($fhirResource->getAddress() as $address) {
                if ($address->getPeriod() === null) {
                    // if we have no period, we can't determine if it is more recent than our current one
                    $this->getSystemLogger()->warning("FHIR Address has no period, skipping for active address determination", ['puuid' => $data['uuid']]);
                    continue;
                }
                $addressPeriod = UtilsService::getPeriodTimestamps($address->getPeriod());
                if (empty($addressPeriod['end'])) {
                    $activeAddress = $address;
                } elseif (!empty($mostRecentPeriods['end']) && $addressPeriod['end'] > $mostRecentPeriods['end']) {
                    // if our current period is more recent than our most recent address we want to grab that one
                    $mostRecentPeriods = $addressPeriod;
                    $activeAddress = $address;
                }
            }

            $lineValues = array_map(fn($val): string => (string)$val, $activeAddress->getLine() ?? []);
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
                    $use = (string)$contactPoint->getUse() ?? "home";
                    $useMapping = ['mobile' => 'email_direct'];
                    if (isset($useMapping[$use])) {
                        $data[$useMapping[$use]] = $contactValue;
                    } else {
                        $data[$systemValue] = $contactValue;
                    }
                } elseif ($systemValue == "phone") {
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

        foreach ($fhirResource->getExtension() as $extension) {
            $url = $extension->getUrl();
            match ($url) {
                'http://hl7.org/fhir/us/core/StructureDefinition/us-core-birthsex' => $data['sex'] = $this->extractBirthSex($extension),
                'http://hl7.org/fhir/us/core/StructureDefinition/us-core-sex' => $data['sex_identified'] = $this->extractSex($extension),
                'http://hl7.org/fhir/us/core/StructureDefinition/us-core-tribal-affiliation' => $data['tribal_affiliations'] = $this->extractTribalAffiliation($extension),
                'http://hl7.org/fhir/us/core/StructureDefinition/us-core-interpreter-needed' => $data['interpreter_needed'] = $this->extractInterpreterNeeded($extension),
//                'http://hl7.org/fhir/us/core/StructureDefinition/us-core-race' => $data['race'] = $this->extractRace($extension),
//                'http://hl7.org/fhir/us/core/StructureDefinition/us-core-ethnicity' => $data['ethnicity'] = $this->extractEthnicity($extension),
                default => null
            };
        }

        foreach ($fhirResource->getIdentifier() as $identifier) {
            $type = $identifier->getType();
            $validCodes = ['SS' => 'ss', 'PT' => 'pubpid'];
            $coding = $type->getCoding() ?? [];
            foreach ($coding as $codingItem) {
                $codingCode = (string)$codingItem->getCode();
                $value = is_string($identifier->getValue()) ? $identifier->getValue() : $identifier->getValue()->getValue();
                if (isset($validCodes[$codingCode])) {
                    $data[$validCodes[$codingCode]] = $value;
                }
            }
        }

        if (!empty($fhirResource->getGeneralPractitioner())) {
            $providerReference = UtilsService::parseReference($fhirResource->getGeneralPractitioner()[0]);
            if (!empty($providerReference) && $providerReference['resourceType'] === 'Practitioner' && $providerReference['localResource']) {
                $data['provider_uuid'] = $providerReference['uuid'];
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

    protected function searchForOpenEMRRecordsWithConfig($openEMRSearchParameters, SearchQueryConfig $config): ProcessingResult
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

        return $this->patientService->search($openEMRSearchParameters, true, $config);
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     *
     * @param array<string, ISearchField> $openEMRSearchParameters OpenEMR search fields
     * @return ProcessingResult
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        return $this->searchForOpenEMRRecordsWithConfig($openEMRSearchParameters, new SearchQueryConfig());
    }

    public function createProvenanceResource($dataRecord, $encode = false)
    {
        if (!($dataRecord instanceof FHIRPatient)) {
            throw new \BadMethodCallException("Data record should be correct instance class");
        }
        $provenanceService = new FhirProvenanceService();
        return $provenanceService->createProvenanceForDomainResource($dataRecord);
    }

    public function getSupportedVersions()
    {
        $highestVersion = $this->getHighestCompatibleUSCoreProfileVersion();
        // Version 8.0.0 and version 7.0.0 are backwards compatible with 3.1.1 and none but ARE not backwards compatible with each other
        return match ($highestVersion) {
            self::PROFILE_VERSION_3_1_1 => self::PROFILE_VERSIONS_V1,
            self::PROFILE_VERSION_7_0_0 => [self::PROFILE_VERSION_NONE, self::PROFILE_VERSION_3_1_1, self::PROFILE_VERSION_7_0_0],
            self::PROFILE_VERSION_8_0_0 => [self::PROFILE_VERSION_NONE, self::PROFILE_VERSION_3_1_1, self::PROFILE_VERSION_8_0_0],
            default => [self::PROFILE_VERSION_NONE, self::PROFILE_VERSION_3_1_1, self::PROFILE_VERSION_8_0_0]
        };
    }

    /**
     * We only have one profile URI we need to return here
     * @return array
     */
    public function getProfileURIs(): array
    {
        return $this->getProfileForVersions(self::USCGI_PROFILE_URI, $this->getSupportedVersions());
    }

    public function getPatientContextSearchField(): FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)]);
    }

    public function getCodeTypesService(): CodeTypesService
    {
        if (!isset($this->codeTypesService)) {
            $this->codeTypesService = new CodeTypesService();
        }
        return $this->codeTypesService;
    }

    private function extractTribalAffiliation(FHIRExtension $extension): ?string
    {
        // for now we just handle a single tribal affiliation

        $value = null;
        foreach ($extension->getExtension() as $subExtension) {
            if ($subExtension->getUrl() === 'tribalAffiliation') {
                $value = $subExtension->getValueCodeableConcept();
                if ($value !== null) {
                    $coding = $value->getCoding();
                    if (!empty($coding)) {
                        $value = (string)$coding[0]->getCode();
                        break;
                    }
                }
            }
        }
        if (!empty($value)) {
            $record = $this->getCachedListOption('tribal_affiliations', $value);
            if (empty($record)) {
                $this->getSystemLogger()->error("Tribal affiliations not found for codes", ['codes' => $value]);
                $value = null;
            }
        }
        return $value;
    }

    private function extractInterpreterNeeded(FHIRExtension $extension): ?string
    {
        $value = null;
        if ($extension->getValueCoding() !== null) {
            $coding = $extension->getValueCoding();
            $value = (string)$coding->getCode();
        } elseif ($extension->getValueCode() !== null) {
            $value = (string)$extension->getValueCode();
        }
        if (!empty($value)) {
            $record = $this->getCachedListOptionByCode('yes_no_unknown', $value);
            if (empty($record)) {
                $this->getSystemLogger()->error("Interpreter needed value not found for codes", ['codes' => $value]);
                $value = null;
            } else {
                $value = $record['option_id'];
            }
        }
        return $value;
    }

    private function extractBirthSex(FHIRExtension $extension): string
    {
        $value = null;
        if ($extension->getValueCode() !== null) {
            $value = (string)$extension->getValueCode();
        } elseif ($extension->getValueCoding() !== null) {
            $value = (string)$extension->getValueCoding()->getCode();
        }
        $mapping = [
            'M' => 'Male',
            'F' => 'Female',
            'U' => 'Unknown',
        ];
        return $mapping[$value] ?? 'Unknown';
    }
    private function extractSex(FHIRExtension $extension): ?string
    {
        $value = null;
        if ($extension->getValueCode() !== null) {
            $value = (string)$extension->getValueCode();
        } elseif ($extension->getValueCoding() !== null) {
            $value = (string)$extension->getValueCoding()->getCode();
        }
        if (!empty($value)) {
            $record = $this->getCachedListOptionByCode('administrative_sex', $value);
            if (empty($record)) {
                $this->getSystemLogger()->error("Administrative Sex not found for codes", ['code' => $value]);
                $value = null;
            } else {
                $value = $record['option_id'];
            }
        }
        return $value;
    }
}
