<?php

/**
 * FhirObservationVitalsService.php
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Observation;

use BadMethodCallException;
use InvalidArgumentException;
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Utils\MeasurementUtils;
use OpenEMR\Common\Uuid\UuidMapping;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRObservation;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRProvenance;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUri;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\FHIR\R4\FHIRResource\FHIRObservation\FHIRObservationComponent;
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
use OpenEMR\Services\FHIR\FhirDateTimeParser;
use OpenEMR\Services\FHIR\FhirPayloadReader;
use OpenEMR\Services\FHIR\FhirProvenanceService;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\FHIR\IPatientCompartmentResourceService;
use OpenEMR\Services\FHIR\IResourceUSCIGProfileService;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\VersionedProfileTrait;
use OpenEMR\Services\FHIR\UtilsService;
use OpenEMR\Services\Search\DateSearchField;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\VitalsCalculatedService;
use OpenEMR\Services\VitalsService;
use OpenEMR\Validators\ProcessingResult;

class FhirObservationVitalsService extends FhirServiceBase implements IPatientCompartmentResourceService, IResourceUSCIGProfileService
{
    use FhirServiceBaseEmptyTrait;
    use VersionedProfileTrait;

    // we set this to be 'Final' which has the follow interpretation
    // 'The observation is complete and there are no further actions needed.'
    // @see http://hl7.org/fhir/R4/valueset-observation-status.html
    const VITALS_DEFAULT_OBSERVATION_STATUS = "final";

    const VITALS_PANEL_LOINC_CODE = '85353-1';

    const USCDI_URI_BASE_PATH = 'http://hl7.org/fhir/us/core/StructureDefinition/';
    const USCDI_PROFILE_HEART_RATE_V3_1_1 = 'http://hl7.org/fhir/StructureDefinition/heartrate';
    const USCDI_PROFILE_HEART_RATE = self::USCDI_URI_BASE_PATH . 'us-core-heart-rate';
    const USCDI_PROFILE_BLOOD_PRESSURE_V3_1_1 = 'http://hl7.org/fhir/StructureDefinition/bp';
    const USCDI_PROFILE_BLOOD_PRESSURE = self::USCDI_URI_BASE_PATH . 'us-core-blood-pressure';
    const USCDI_PROFILE_AVERAGE_BLOOD_PRESSURE = self::USCDI_URI_BASE_PATH . 'us-core-average-blood-pressure';
    const USCDI_PROFILE_RESPIRATORY_RATE = self::USCDI_URI_BASE_PATH . 'us-core-respiratory-rate';
    const USCDI_PROFILE_RESPIRATORY_RATE_V3_1_1 = 'http://hl7.org/fhir/StructureDefinition/resprate';
    const USCDI_PROFILE_BODY_TEMPERATURE = self::USCDI_URI_BASE_PATH . 'us-core-body-temperature';
    const USCDI_PROFILE_BODY_TEMPERATURE_V3_1_1 = 'http://hl7.org/fhir/StructureDefinition/bodytemp';
    const USCDI_PROFILE_BODY_HEIGHT = self::USCDI_URI_BASE_PATH . 'us-core-body-height';
    const USCDI_PROFILE_BODY_HEIGHT_V3_1_1 = 'http://hl7.org/fhir/StructureDefinition/bodyheight';
    const USCDI_PROFILE_BODY_WEIGHT = self::USCDI_URI_BASE_PATH . 'us-core-body-weight';
    const USCDI_PROFILE_BODY_WEIGHT_V3_1_1 = 'http://hl7.org/fhir/StructureDefinition/bodyweight';
    const USCDI_PROFILE_BMI = self::USCDI_URI_BASE_PATH . 'us-core-bmi';
    const USCDI_PROFILE_PEDIATRIC_BMI = self::USCDI_URI_BASE_PATH . 'pediatric-bmi-for-age';
    const USCDI_PROFILE_HEAD_CIRCUMFERENCE = self::USCDI_URI_BASE_PATH . 'us-core-head-circumference';
    const USCDI_PROFILE_PULSE_OXIMETRY = self::USCDI_URI_BASE_PATH . 'us-core-pulse-oximetry';

    // any vital signs that do not conform to a specific US-Core profile (which includes the vital-signs panel for example)
    const USCDI_PROFILE_VITAL_SIGNS_V3_1_1 = 'http://hl7.org/fhir/R4/observation-vitalsigns';
    const USCDI_PROFILE_VITAL_SIGNS = self::USCDI_URI_BASE_PATH . 'us-core-vital-signs';

    const USCDI_PROFILE_HEAD_OCCIPITAL_FRONTAL_CIRCUMFERENCE = self::USCDI_URI_BASE_PATH . 'head-occipital-frontal-circumference-percentile';
    const USCDI_PROFILE_PEDIATRIC_WEIGHT_FOR_HEIGHT = self::USCDI_URI_BASE_PATH . 'pediatric-weight-for-height';
    const VITALS_CODE_PULSE_OXIMETRY_OXYGEN_FLOW_RATE = '3151-8';
    const VITALS_CODE_PULSE_OXIMETRY = '59408-5';
    const VITALS_CODE_PULSE_OXIMETRY_OXYGEN_SATURATION = '2708-6';
    const VITALS_CODE_PULSE_OXIMETRY_OXYGEN_CONCENTRATION = '3150-0';
    const VITALS_CODE_SYSTOLIC_BLOOD_PRESSURE = '8480-6';
    const VITALS_CODE_DIASTOLIC_BLOOD_PRESSURE = '8462-4';
    const VITALS_CODE_BLOOD_PRESSURE = '85354-9';

    /**
     * @var VitalsService
     */
    private VitalsService $service;

    /**
     * @var VitalsCalculatedService the calculated service that manages calculated vital statistics
     */
    private VitalsCalculatedService $calculatedService;

    const CATEGORY = "vital-signs";

    const COLUMN_MAPPINGS = [
        // @see http://hl7.org/fhir/R4/observation-vitalsigns.html
        self::VITALS_PANEL_LOINC_CODE => [
            // this code contains a lot of the other vital sign codes and is treated specially in this service.
            'fullcode' => 'LOINC:' . self::VITALS_PANEL_LOINC_CODE
            ,'code' => self::VITALS_PANEL_LOINC_CODE
            ,'description' => 'Vital signs, weight, height, head circumference, oxygen saturation and BMI panel'
            ,'column' => ''
            ,'in_vitals_panel' => false
            ,'profiles' => [
                self::USCDI_PROFILE_VITAL_SIGNS_V3_1_1 => self::PROFILE_VERSIONS_V1
                , self::USCDI_PROFILE_VITAL_SIGNS => self::PROFILE_VERSIONS_V2
            ]
        ],
        '9279-1' => [
            'fullcode' => 'LOINC:9279-1'
            ,'code' => '9279-1'
            ,'description' => 'Respiratory Rate'
            ,'column' => ['respiration', 'respiration_unit']
            ,'in_vitals_panel' => true
            ,'profiles' => [
                self::USCDI_PROFILE_RESPIRATORY_RATE_V3_1_1 => self::PROFILE_VERSIONS_V1
                , self::USCDI_PROFILE_RESPIRATORY_RATE => self::PROFILE_VERSIONS_V2
            ]
        ]
        ,'8867-4' => [
            'fullcode' => 'LOINC:8867-4'
            ,'code' => '8867-4'
            ,'description' => 'Heart rate'
            ,'column' => ['pulse', 'pulse_unit']
            ,'in_vitals_panel' => true
            ,'profiles' => [
                self::USCDI_PROFILE_HEART_RATE_V3_1_1 => self::PROFILE_VERSIONS_V1
                , self::USCDI_PROFILE_HEART_RATE => self::PROFILE_VERSIONS_V2
            ]
        ]
        , self::VITALS_CODE_PULSE_OXIMETRY_OXYGEN_SATURATION => [
            'fullcode' => 'LOINC:' . self::VITALS_CODE_PULSE_OXIMETRY_OXYGEN_SATURATION
            ,'code' => self::VITALS_CODE_PULSE_OXIMETRY_OXYGEN_SATURATION
            ,'description' => 'Oxygen saturation in Arterial blood'
            ,'column' => ['oxygen_saturation', 'oxygen_saturation_unit', 'oxygen_flow_rate', 'oxygen_flow_rate_unit', 'inhaled_oxygen_concentration', 'inhaled_oxygen_concentration_unit']
            ,'in_vitals_panel' => true
            ,'profiles' => [
                self::USCDI_PROFILE_PULSE_OXIMETRY => self::PROFILE_VERSIONS_ALL
            ]
        ]
        , self::VITALS_CODE_PULSE_OXIMETRY => [
            'fullcode' => 'LOINC:' . self::VITALS_CODE_PULSE_OXIMETRY,
            'code' => self::VITALS_CODE_PULSE_OXIMETRY,
            'description' => 'Oxygen saturation in Arterial blood by Pulse oximetry',
            'column' => ['oxygen_saturation', 'oxygen_saturation_unit', 'oxygen_flow_rate', 'oxygen_flow_rate_unit', 'inhaled_oxygen_concentration', 'inhaled_oxygen_concentration_unit'],
            'in_vitals_panel' => true,
            'profiles' => [
                self::USCDI_PROFILE_PULSE_OXIMETRY => self::PROFILE_VERSIONS_ALL
            ]
        ]
        ,'8310-5' => [
            'fullcode' => 'LOINC:8310-5',
            'code' => '8310-5',
            'description' => 'Body Temperature',
            'column' => ['temperature', 'temperature_unit'],
            'in_vitals_panel' => true
            ,'profiles' => [
                self::USCDI_PROFILE_BODY_TEMPERATURE_V3_1_1 => self::PROFILE_VERSIONS_V1
                , self::USCDI_PROFILE_BODY_TEMPERATURE => self::PROFILE_VERSIONS_V2
            ]
        ]
        ,'8327-9' => [
            'fullcode' => 'LOINC:8327-9',
            'code' => '8327-9',
            'description' => 'Temperature Location',
            'column' => ['temp_method'],
            'in_vitals_panel' => true
            ,'profiles' => [
                // we supported in 3.1.1 this location, but it doesn't conform to the full body temperature profile
                // so we are only including it in 7.0.0+ as a valid profile for this observation as it won't pass validation
                // on anything else.
                FhirObservationObservationFormService::USCGI_PROFILE_URI => self::PROFILE_VERSIONS_V2
            ]
        ]
        ,'8302-2' => [
            'fullcode' => 'LOINC:8302-2',
            'code' => '8302-2',
            'description' => 'Body height',
            'column' => ['height', 'height_unit'],
            'in_vitals_panel' => true
            ,'profiles' => [
                self::USCDI_PROFILE_BODY_HEIGHT_V3_1_1 => self::PROFILE_VERSIONS_V1
                , self::USCDI_PROFILE_BODY_HEIGHT => self::PROFILE_VERSIONS_V2
            ]
        ]
        ,'9843-4' => [
            'fullcode' => 'LOINC:9843-4'
            ,'code' => '9843-4'
            ,'description' => 'Head Occipital-frontal circumference'
            ,'column' => ['head_circ', 'head_circ_unit']
            ,'in_vitals_panel' => true
            ,'profiles' => [
                self::USCDI_PROFILE_HEAD_CIRCUMFERENCE => self::PROFILE_VERSIONS_ALL
            ]
        ]
        ,'29463-7' => [
            'fullcode' => 'LOINC:29463-7'
            ,'code' => '29463-7'
            ,'description' => 'Body weight'
            ,'column' => ['weight', 'weight_unit']
            ,'in_vitals_panel' => true
            ,'profiles' => [
                self::USCDI_PROFILE_BODY_WEIGHT_V3_1_1 => self::PROFILE_VERSIONS_V1
                , self::USCDI_PROFILE_BODY_WEIGHT => self::PROFILE_VERSIONS_V2
            ]
        ]
        ,'39156-5' => [
            'fullcode' => 'LOINC:39156-5'
            ,'code' => '39156-5'
            ,'description' => 'Body mass index (BMI) [Ratio]'
            ,'column' =>  ['BMI', 'BMI_status', 'BMI_unit']
            ,'in_vitals_panel' => true
            ,'profiles' => [
                self::USCDI_PROFILE_BMI => self::PROFILE_VERSIONS_ALL
            ]
        ]
        // note we removed the observations for the raw bps and bpd values as apparently they are not conformant to USCDI
        // and it was the full blood pressure panel that was required.
        , self::VITALS_CODE_BLOOD_PRESSURE => [
            'fullcode' => 'LOINC:85354-9'
            ,'code' => self::VITALS_CODE_BLOOD_PRESSURE
            ,'description' => 'Blood pressure systolic and diastolic'
            // we hack this a bit to make it work by having our systolic and diastolic together
            ,'column' => ['bps', 'bps_unit', 'bpd', 'bpd_unit']
            ,'in_vitals_panel' => true
            ,'profiles' => [
                self::USCDI_PROFILE_BLOOD_PRESSURE_V3_1_1 => self::PROFILE_VERSIONS_V1
                , self::USCDI_PROFILE_BLOOD_PRESSURE => self::PROFILE_VERSIONS_V2
            ]
        ]



        // pediatric profiles are different...
        // need pediatric BMI
        // need pediatric head-occipetal

        // Birth - 36 months @see https://www.cdc.gov/growthcharts/html_charts/hcageinf.htm
        // @see
        ,'8289-1' => [
            'fullcode' => 'LOINC:8289-1'
            ,'code' => '8289-1'
            ,'description' => 'Head Occipital-frontal circumference Percentile'
            ,'column' => ['ped_head_circ', 'ped_head_circ_unit']
            ,'in_vitals_panel' => false
            ,'profiles' => [
                self::USCDI_PROFILE_HEAD_OCCIPITAL_FRONTAL_CIRCUMFERENCE => self::PROFILE_VERSIONS_ALL
            ]
        ]
        // 2-20yr @see https://www.cdc.gov/growthcharts/html_charts/bmiagerev.htm
        ,'59576-9' => [
            'fullcode' => 'LOINC:59576-9'
            ,'code' => '59576-9'
            ,'description' => 'Body mass index (BMI) [Percentile] Per age and sex'
            ,'column' => ['ped_bmi', 'ped_bmi_unit']
            ,'in_vitals_panel' => false
            ,'profiles' => [
                self::USCDI_PROFILE_PEDIATRIC_BMI => self::PROFILE_VERSIONS_ALL
            ]
        ]
        // @see https://www.cdc.gov/growthcharts/html_charts/wtstat.htm
        // grab min(height) and find where weight <= 50
        // could do this with 3 columns representing height, weight & %
        // height, weight, %
        // select % WHERE height <= usrheight & weight <= usrweight ORDER BY height DESC, weight DESC LIMIT 1
        ,'77606-2' => [
            'fullcode' => 'LOINC:77606-2'
            ,'code' => '77606-2'
            ,'description' => 'Weight-for-length Per age and sex'
            ,'column' => ['ped_weight_height', 'ped_weight_height_unit']
            ,'in_vitals_panel' => false
            ,'profiles' => [
                self::USCDI_PROFILE_PEDIATRIC_WEIGHT_FOR_HEIGHT => self::PROFILE_VERSIONS_ALL
            ]
        ],
        // need pediatric weight for height observations...
    ];
    const AVERAGE_BLOOD_PRESSURE_LOINC_CODE = '96607-7';

    const CALCULATED_MAPPING_CODES = [
        self::AVERAGE_BLOOD_PRESSURE_LOINC_CODE
    ];

    const COLUMN_CALCULATED_MAPPINGS = [
        'bp-MeanLast5' => [
            'fullcode' => 'LOINC:' . self::AVERAGE_BLOOD_PRESSURE_LOINC_CODE,
            'code' => self::AVERAGE_BLOOD_PRESSURE_LOINC_CODE,
            'description' => 'Blood pressure panel mean systolic and mean diastolic',
            'calculation_type' => 'bp-MeanLast5',
            'in_vitals_panel' => false,
            'profiles' => [
                self::USCDI_PROFILE_AVERAGE_BLOOD_PRESSURE => self::PROFILE_VERSIONS_V2
            ]
        ],
        'bp-Mean3Day' => [
            'fullcode' => 'LOINC:' . self::AVERAGE_BLOOD_PRESSURE_LOINC_CODE,
            'code' => self::AVERAGE_BLOOD_PRESSURE_LOINC_CODE,
            'description' => 'Blood pressure panel mean systolic and mean diastolic',
            'calculation_type' => 'bp-Mean3Day',
            'in_vitals_panel' => false,
            'profiles' => [
                self::USCDI_PROFILE_AVERAGE_BLOOD_PRESSURE => self::PROFILE_VERSIONS_V2
            ]
        ],
        'bp-MeanEncounter' => [
            'fullcode' => 'LOINC:' . self::AVERAGE_BLOOD_PRESSURE_LOINC_CODE,
            'code' => self::AVERAGE_BLOOD_PRESSURE_LOINC_CODE,
            'description' => 'Blood pressure panel mean systolic and mean diastolic',
            'calculation_type' => 'bp-MeanEncounter',
            'in_vitals_panel' => false,
            'profiles' => [
                self::USCDI_PROFILE_AVERAGE_BLOOD_PRESSURE => self::PROFILE_VERSIONS_V2
            ]
        ]
    ];

    const COMPONENT_CODES = [
        self::VITALS_CODE_SYSTOLIC_BLOOD_PRESSURE,
        self::VITALS_CODE_DIASTOLIC_BLOOD_PRESSURE,
        self::VITALS_CODE_PULSE_OXIMETRY_OXYGEN_FLOW_RATE,
        self::VITALS_CODE_PULSE_OXIMETRY_OXYGEN_CONCENTRATION
    ];

    public function __construct($fhirApiURL = null)
    {
        parent::__construct($fhirApiURL);
        $this->service = new VitalsService();
    }

    public function getCodeFromResourcePath($resourcePath)
    {
        $query_vars = [];
        parse_str((string) $resourcePath, $query_vars);
        return $query_vars['code'] ?? null;
    }

    public function supportsCategory($category): bool
    {
        return ($category === self::CATEGORY);
    }

    public function supportsCode($code): bool
    {
        return in_array($code, array_keys(self::COLUMN_MAPPINGS))
            // our only calculated vital sign at the moment
            || in_array($code, self::CALCULATED_MAPPING_CODES)
            || in_array($code, self::COMPONENT_CODES);
    }

    public function setVitalsCalculatedService(VitalsCalculatedService $service): void
    {
        $this->calculatedService = $service;
    }

    public function getVitalsCalculatedService(): VitalsCalculatedService
    {
        $this->calculatedService ??= new VitalsCalculatedService();
        return $this->calculatedService;
    }
    /**
     * Returns an array mapping FHIR Resource search parameters to OpenEMR search parameters
     */
    protected function loadSearchParameters(): array
    {
        return [
            'patient' => $this->getPatientContextSearchField(),
            'code' => new FhirSearchParameterDefinition('code', SearchFieldType::TOKEN, ['code']),
            'category' => new FhirSearchParameterDefinition('category', SearchFieldType::TOKEN, ['category']),
            'date' => new FhirSearchParameterDefinition('date', SearchFieldType::DATETIME, ['date']),
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)]),
            '_lastUpdated' => $this->getLastModifiedSearchField()
        ];
    }

    public function getLastModifiedSearchField(): ?FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['last_updated']);
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     * @param array<string, \OpenEMR\Services\Search\ISearchField> $openEMRSearchParameters OpenEMR search fields
     * @return ProcessingResult OpenEMR records
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        try {
            $observationCodesToReturn = [];

            if (isset($openEMRSearchParameters['category']) && $openEMRSearchParameters['category'] instanceof TokenSearchField) {
                if (!$openEMRSearchParameters['category']->hasCodeValue(self::CATEGORY)) {
                    throw new SearchFieldException("category", "invalid value");
                }
                // we only support one category and then we remove it.
                unset($openEMRSearchParameters['category']);
            }

            if (isset($openEMRSearchParameters['code'])) {
                $code = $openEMRSearchParameters['code'];
                if (!($code instanceof TokenSearchField)) {
                    throw new SearchFieldException('code', "Invalid code");
                }
                foreach ($code->getValues() as $value) {
                    $code = $value->getCode();
                    // if we have a component value we are searching on, we need to convert it to the base code so we can return those records
                    $code = $this->convertComponentCodeToBaseCode($code);
                    if ($this->supportsCode($code)) {
                        $observationCodesToReturn[$code] = $code;
                    }
                }
                unset($openEMRSearchParameters['code']);
                if (empty($observationCodesToReturn)) {
                    return $processingResult; // nothing to return if we don't support any of the codes
                }
            }


            if (empty($observationCodesToReturn)) {
                // grab everything
                $observationCodesToReturn = array_merge(array_keys(self::COLUMN_MAPPINGS), self::CALCULATED_MAPPING_CODES);
                $observationCodesToReturn = array_combine($observationCodesToReturn, $observationCodesToReturn);
            }

            // convert vital sign records from 1:many

            $result = $this->service->search($openEMRSearchParameters);
            if (!$result->isValid()) {
                return $result;
            }
            $data = $result->getData() ?? [];

            // need to transform these into something we can consume
            foreach ($data as $record) {
                // each vital record becomes a 1 -> many record for our observations
                $this->parseVitalsIntoObservationRecords($processingResult, $record, $observationCodesToReturn);
            }


            // TODO: @adunsulag should the calculated vitals be pulled into a separate service class, its starting to behave
            // different enough that it might make sense.
            if (!empty($observationCodesToReturn[self::AVERAGE_BLOOD_PRESSURE_LOINC_CODE])) {
                if (isset($openEMRSearchParameters['date'])) {
                    $openEMRSearchParameters['date_start'] = new DateSearchField('date_start', $openEMRSearchParameters['date']->getValues());
                    unset($openEMRSearchParameters['date']);
                }
                $calculatedVitalsResult = $this->searchCalculatedVitals($openEMRSearchParameters);
                if (!$calculatedVitalsResult->isValid()) {
                    return $calculatedVitalsResult;
                }
                $processingResult->addProcessingResult($calculatedVitalsResult);
            }
        } catch (SearchFieldException $exception) {
            $processingResult->setValidationMessages([$exception->getField() => $exception->getMessage()]);
        }

        return $processingResult;
    }

    private function searchCalculatedVitals($openEMRSearchParameters): ProcessingResult
    {
        // Search calculated vitals using the VitalsCalculatedService
        $calculatedService = $this->getVitalsCalculatedService();
        $result = $calculatedService->search($openEMRSearchParameters);
        // Transform calculated records for FHIR compatibility
        $transformedResult = new ProcessingResult();
        foreach ($result->getData() as $record) {
            $transformedRecord = $this->transformCalculatedRecord($record);
            if ($transformedRecord !== null) {
                $transformedResult->addData($transformedRecord);
            }
        }

        return $transformedResult;
    }

    private function transformCalculatedRecord(array $calculatedRecord): ?array
    {
        // Transform calculated vitals record to FHIR-compatible format
        $calculationId = $calculatedRecord['calculation_id'];

        if (!isset(self::COLUMN_CALCULATED_MAPPINGS[$calculationId])) {
            return null;
        }

        $mapping = self::COLUMN_CALCULATED_MAPPINGS[$calculationId];

        return [
            'uuid' => $calculatedRecord['uuid'],
            'pid' => $calculatedRecord['pid'],
            'puuid' => $calculatedRecord['puuid'],
            'encounter' => $calculatedRecord['encounter'] ?? null,
            'encounter_uuid' => $calculatedRecord['euuid'] ?? null,
            'date' => $calculatedRecord['date_start'],
            'date_end' => $calculatedRecord['date_end'],
            'code' => $mapping['code'],
            'category' => self::CATEGORY,
            'description' => $mapping['description'],
            'user_uuid' => $calculatedRecord['created_by_uuid'] ?? null,
            'user_npi' => $calculatedRecord['created_by_npi'] ?? null,
            'last_updated_time' => $calculatedRecord['updated_at'],
            'parent_observation_uuid' => $calculatedRecord['parent_observation_uuid'] ?? [],
            'components' => $calculatedRecord['components'] ?? [],
            'profiles' => $mapping['profiles'],
            'is_calculated' => true,
            'calculation_type' => $calculationId
        ];
    }

    private function parseVitalsIntoObservationRecords(ProcessingResult $processingResult, $record, $observationCodesToReturn): void
    {
        $uuidMappings = $this->getVitalSignsUuidMappings(UuidRegistry::uuidToBytes($record['uuid']));
        // convert each record into it's own openEMR record array

        if (!empty($observationCodesToReturn[self::VITALS_PANEL_LOINC_CODE])) {
            if (!empty($uuidMappings[self::VITALS_PANEL_LOINC_CODE])) {
                $vitalsRecord = [
                    "code" => self::VITALS_PANEL_LOINC_CODE
                    , "description" => $this->getDescriptionForCode(self::VITALS_PANEL_LOINC_CODE)
                    , "category" => self::CATEGORY
                    , "puuid" => $record['puuid']
                    , "euuid" => $record['euuid']
                    , "status" => self::VITALS_DEFAULT_OBSERVATION_STATUS
                    , "sub_observations" => []
                    , "uuid" => UuidRegistry::uuidToString($uuidMappings[self::VITALS_PANEL_LOINC_CODE])
                    , "user_uuid" => $record['user_uuid']
                    , "user_npi" => $record['user_npi'] ?? null
                    , "date" => $record['date']
                    , "last_updated" => $record['last_updated']
                    , "notes" => $record['note'] ?? null

                ];
                foreach ($uuidMappings as $code => $uuid) {
                    if (!$this->isVitalSignPanelCodes($code)) {  // we will skip over our vital signs code, and any pediatric stuff
                        continue;
                    }
                    $vitalsRecord["sub_observations"][$code] = ['uuid' => UuidRegistry::uuidToString($uuid) ];
                }
                $processingResult->addData($vitalsRecord);
                unset($observationCodesToReturn[self::VITALS_PANEL_LOINC_CODE]);
            } else {
                ServiceContainer::getLogger()->error("FhirVitalsService->parseVitalsIntoObservationRecords() Cannot return vitals panel as mapping uuid is missing for code " . self::VITALS_PANEL_LOINC_CODE);
            }
        }

        foreach ($observationCodesToReturn as $code) {
            if (!isset(self::COLUMN_MAPPINGS[$code])) {
                // we only support the codes we know about
                continue;
            }
            $codeMapping = self::COLUMN_MAPPINGS[$code];
            if (!isset($uuidMappings[$code])) {
                $this->getSystemLogger()->error("Cannot return vital sign record as mapping uuid is missing for code {code}", ['code' => $code]);
                continue;
            }
            // uuid mappings are binary values, we need to convert them to string
            $uuid = UuidRegistry::uuidToString($uuidMappings[$code]);
            $vitalsRecord = [
                "code" => $code
                ,"description" => $this->getDescriptionForCode($code)
                ,"category" => self::CATEGORY
                , "puuid" => $record['puuid']
                , "euuid" => $record['euuid']
                , "user_uuid" => $record['user_uuid']
                , "user_npi" => $record['user_npi'] ?? null
                ,"uuid" => $uuid
                ,"date" => $record['date']
                , "last_updated" => $record['last_updated']
                , "profiles" => $codeMapping['profiles'] ?? []
                ,"sub_observations" => []
            ];

            $columns = $this->getColumnsForCode($code);
            $columns[] = 'details'; // make sure to grab our detail columns
            // if any value of the column is populated we will return that the record has a value.
            foreach ($columns as $column) {
                if (isset($record[$column]) && $record[$column] != "") {
                    $vitalsRecord[$column] = $record[$column];
                }
            }
            $processingResult->addData($vitalsRecord);
        }
    }

    private function getVitalSignsUuidMappings($uuid): array
    {
        $mappedRecords = UuidMapping::getMappedRecordsForTableUUID($uuid);
        $codeMappings = [];
        if (!empty($mappedRecords)) {
            foreach ($mappedRecords as $record) {
                $resourcePath = $record['resource_path'] ?? '';
                $code = $this->getCodeFromResourcePath($resourcePath);
                if (empty($code)) {
                    // TODO: @adunsulag handle this exception
                    continue;
                }
                $codeMappings[$code] = $record['uuid'];
            }
        }
        return $codeMappings;
    }

    /**
     * Parses an OpenEMR data record, returning the equivalent FHIR Resource
     *
     * @param array $dataRecord The source OpenEMR data record
     * @param bool $encode Indicates if the returned resource is encoded into a string. Defaults to True.
     * @return FHIRDomainResource|string the FHIR Resource. Returned format is defined using $encode parameter.
     */
    public function parseOpenEMRRecord($dataRecord = [], $encode = false): FHIRDomainResource|string
    {
        if (empty($dataRecord)) {
            throw new InvalidArgumentException("Data record cannot be empty");
        }

        $observation = new FHIRObservation();
        $meta = new FHIRMeta();
        $meta->setVersionId('1');

        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $observation->setId($id);

        if (!empty($dataRecord['date'])) {
            $startDate = new FHIRDateTime(UtilsService::getLocalDateAsUTC($dataRecord['date']));
            if (!empty($dataRecord['date_end'])) {
                $endDate = new FhirDateTime(UtilsService::getLocalDateAsUTC($dataRecord['date_end']));
                $observation->setEffectivePeriod(new FHIRPeriod(['start' => $startDate, 'end' => $endDate]));
            } else {
                $observation->setEffectiveDateTime($startDate);
            }
        } else {
            $observation->setEffectiveDateTime(UtilsService::createDataMissingExtension());
        }

        $code = $dataRecord['code'];
        $description = $dataRecord['description'] ?? $this->getDescriptionForCode($code);

        if (!empty($dataRecord['code'])) {
            $obsCodeConcept = UtilsService::createCodeableConcept([
                $dataRecord['code'] => [
                    'description' => $description,
                    'system' => FhirCodeSystemConstants::LOINC,
                    'code' => $dataRecord['code']
                ]
            ]);
            $observation->setCode($obsCodeConcept);
        }

        $observation->setStatus(self::VITALS_DEFAULT_OBSERVATION_STATUS);

        if (!empty($dataRecord['user_uuid']) && !empty($dataRecord['user_npi'])) {
            $observation->addPerformer(UtilsService::createRelativeReference("Practitioner", $dataRecord['user_uuid']));
        }

        $observation->addCategory(UtilsService::createCodeableConcept([
            $dataRecord['category'] => [
                'code' => $dataRecord['category'],
                'system' => FhirCodeSystemConstants::HL7_OBSERVATION_CATEGORY
            ]
        ]));

        $observation->setSubject(UtilsService::createRelativeReference("Patient", $dataRecord['puuid']));

        if (!empty($dataRecord['notes'])) {
            $observation->addNote(['text' => $dataRecord['notes']]);
        }

        $basic_codes = [
            "9279-1" => 'respiration', "8867-4" => 'pulse'
            , self::VITALS_CODE_PULSE_OXIMETRY_OXYGEN_SATURATION => 'oxygen_saturation'
            , '8310-5' => 'temperature'
            ,'8302-2' => 'height', '9843-4' => 'head_circ', '29463-7' => 'weight', '39156-5' => 'BMI'
            ,'59576-9' => 'ped_bmi', '8289-1' => 'ped_head_circ', '77606-2' => 'ped_weight_height'
        ];

        if (isset($basic_codes[$code])) {
            $this->populateBasicQuantityObservation($basic_codes[$code], $observation, $dataRecord);
        }
        $lookUp = self::COLUMN_MAPPINGS[$code] ?? [];
        $profiles = $lookUp['profiles'] ?? $dataRecord['profiles'] ?? [];
        // more complicated codes
        match ($code) {
            // vital-signs panel
            self::VITALS_PANEL_LOINC_CODE => $this->populateVitalSignsPanelObservation($observation, $dataRecord),
            '8327-9' => $this->populateBodyTemperatureLocation($observation, $dataRecord),
            // blood pressure panel that includes systolic & diastolic pressure
            self::VITALS_CODE_BLOOD_PRESSURE => $this->populateBloodPressurePanel($observation, $dataRecord),
            // PulseOx(59408-5) or Oxygen saturation (2708-6) are combined into one observation in 7.0.0+ which is backwards compatible for older versions
            self::VITALS_CODE_PULSE_OXIMETRY, self::VITALS_CODE_PULSE_OXIMETRY_OXYGEN_SATURATION => $this->populatePulseOximetryObservation($observation, $dataRecord),
            self::AVERAGE_BLOOD_PRESSURE_LOINC_CODE => $this->addAverageBPComponents($observation, $dataRecord['components'] ?? []),
            default => $observation,
        };

        if (!empty($dataRecord['euuid'])) {
            $observation->setEncounter(UtilsService::createRelativeReference("Encounter", $dataRecord['euuid']));
        }
        if (!empty($dataRecord['last_updated'])) {
            $meta->setLastUpdated(UtilsService::getLocalDateAsUTC($dataRecord['last_updated']));
        } else {
            $meta->setLastUpdated(UtilsService::getDateFormattedAsUTC());
        }
        // go through and populate our profiles now.
        foreach ($profiles as $baseProfile => $versions) {
            foreach ($this->getProfileForVersions($baseProfile, $versions) as $profile) {
                $meta->addProfile($profile);
            }
        }
        $observation->setMeta($meta);
        if ($encode) {
            return json_encode($observation);
        } else {
            return $observation;
        }
    }

    private function getColumnsForCode($code): array
    {
        $codeMapping = self::COLUMN_MAPPINGS[$code] ?? null;
        if (isset($codeMapping)) {
            return is_array($codeMapping['column']) ? $codeMapping['column'] : [$codeMapping['column']];
        }
        return [];
    }

    private function getDescriptionForCode($code)
    {
        $codeMapping = self::COLUMN_MAPPINGS[$code] ?? null;
        if (isset($codeMapping)) {
            return $codeMapping['description'];
        }
        return "";
    }

    private function populateCoding(FHIRObservation $observation, $code): void
    {
        // add additional oxygen-saturation coding
        $oxSaturation = new FHIRCoding();
        $oxSaturation->setCode($code);
        $oxSaturation->setDisplay($this->getDescriptionForCode($code));
        $oxSaturation->setSystem(FhirCodeSystemConstants::LOINC);

        $observation->getCode()->addCoding($oxSaturation);
    }

    private function populatePulseOximetryObservation(FHIRObservation $observation, $dataRecord): void
    {
        // going to reset the codeable concept as inferno validation appears to care about the order of the codings
        $concept = new FHIRCodeableConcept();
        $concept->setText("oxygen_saturation"); // this is set in the profile example, so we'll follow that, but its not listed anywhere in SPEC
        $observation->setCode($concept);
        $this->populateCoding($observation, self::VITALS_CODE_PULSE_OXIMETRY_OXYGEN_SATURATION);
        $this->populateCoding($observation, self::VITALS_CODE_PULSE_OXIMETRY);

        if (!($this->columnHasPositiveFloatValue('oxygen_saturation', $dataRecord))) {
            $observation->setDataAbsentReason(UtilsService::createDataAbsentUnknownCodeableConcept());
        }
        $addComponentsWithMissingData = $this->getHighestCompatibleUSCoreProfileVersion() != self::PROFILE_VERSION_3_1_1;

        if ($this->columnHasPositiveFloatValue('oxygen_flow_rate', $dataRecord)
            || $addComponentsWithMissingData
        ) {
            $this->populateComponentColumn(
                $observation,
                $dataRecord,
                'oxygen_flow_rate',
                self::VITALS_CODE_PULSE_OXIMETRY_OXYGEN_FLOW_RATE,
                'Inhaled oxygen flow rate'
            );
        }
        if ($this->columnHasPositiveFloatValue('inhaled_oxygen_concentration', $dataRecord)
            || $addComponentsWithMissingData
        ) {
            $this->populateComponentColumn(
                $observation,
                $dataRecord,
                'inhaled_oxygen_concentration',
                self::VITALS_CODE_PULSE_OXIMETRY_OXYGEN_CONCENTRATION,
                'Inhaled oxygen concentration'
            );
        }
    }

    private function populateVitalSignsPanelObservation(FHIRObservation $observation, $record): void
    {
        if (!empty($record['sub_observations'])) {
            foreach ($record['sub_observations'] as $code => $member) {
                $reference = UtilsService::createRelativeReference("Observation", $member['uuid']);
                $reference->setDisplay($this->getDescriptionForCode($code));
                $observation->addHasMember($reference);
            }
        }
    }

    private function isVitalSignPanelCodes($code)
    {
        $codeMapping = self::COLUMN_MAPPINGS[$code] ?? null;
        if (isset($codeMapping)) {
            return $codeMapping['in_vitals_panel'];
        }
        return false;
    }

    private function populateBasicQuantityObservation($column, FHIRObservation $observation, $record): void
    {
        $quantity = $this->getFHIRQuantityForColumn($column, $record);
        if ($quantity != null) {
            $observation->setValueQuantity($quantity);
        } else {
            $observation->setDataAbsentReason(UtilsService::createDataAbsentUnknownCodeableConcept());
        }

        if (isset($record['details'][$column])) {
            $observation->addInterpretation($this->getInterpretationForColumn($record, $column));
        }
    }

    private function getInterpretationForColumn($record, $column): ?FHIRCodeableConcept
    {
        if (isset($record['details'][$column])) {
            $code = $record['details'][$column]['interpretation_codes'];
            $text = $record['details'][$column]['interpretation_title'];
            return UtilsService::createCodeableConcept([$code =>
                ['code' => $code, 'description' => $text, 'system' => FhirCodeSystemConstants::HL7_V3_OBSERVATION_INTERPRETATION]
            ]);
        }
        return null;
    }

    private function getFHIRQuantityForColumn($column, $record): ?FHIRQuantity
    {
        if ($this->columnHasPositiveFloatValue($column, $record)) {
            $quantity = new FHIRQuantity();
            $quantity->setValue(floatval($record[$column]));
            $quantity->setSystem(FhirCodeSystemConstants::UNITS_OF_MEASURE);
            $unit = $record[$column . '_unit'] ?? null;
            $code = $unit;
            // @see http://hl7.org/fhir/R4/observation-vitalsigns.html for the codes on this
            if ($unit === 'in') {
                $unit = 'in_i';
                $code = "[" . $unit . "]";
            } elseif ($unit === 'lb') {
                $unit = 'lb_av';
                $code = "[" . $unit . "]";
            } elseif ($unit === 'degF') {
                $code = "[" . $unit . "]";
            }
            $quantity->setCode($code);
            $quantity->setUnit($unit);
            return $quantity;
        }
        return null;
    }

    private function columnHasPositiveFloatValue($column, $record): bool
    {
        return (isset($record[$column]) && floatval($record[$column]) > 0.00);
    }

    private function populateBloodPressurePanel(FHIRObservation $observation, $dataRecord): void
    {
        // Based on conversations with Jerry Padget and Brady Miller on August 14th 2021 we decided that if the values
        // were both 0 for bpd and bps we would treat this as a data absent reason.  In this case an attempt was made
        // to record the data but no value was recorded (such as the blood pressure cuff becoming loose).
        if ($dataRecord['bpd'] == 0 && $dataRecord['bps'] == 0) {
            $observation->setDataAbsentReason(UtilsService::createDataAbsentUnknownCodeableConcept());
        }
            $this->populateComponentColumn(
                $observation,
                $dataRecord,
                'bps',
                self::VITALS_CODE_SYSTOLIC_BLOOD_PRESSURE,
                'Systolic blood pressure'
            );
            $this->populateComponentColumn(
                $observation,
                $dataRecord,
                'bpd',
                self::VITALS_CODE_DIASTOLIC_BLOOD_PRESSURE,
                'Diastolic blood pressure'
            );
    }

    /**
     * @param literal-string $description
     */
    private function populateComponentColumn(FHIRObservation $observation, $dataRecord, $column, $code, $description): void
    {
        $component = new FHIRObservationComponent();
        $coding = UtilsService::createCodeableConcept(
            [
                $code => ['code' => $code, 'description' => xlt($description), 'system' => FhirCodeSystemConstants::LOINC]
            ]
        );
        $component->setCode($coding);
        $quantity = $this->getFHIRQuantityForColumn($column, $dataRecord);
        if ($quantity != null) {
            $component->setValueQuantity($quantity);
        } else {
            $component->setDataAbsentReason(UtilsService::createDataAbsentUnknownCodeableConcept());
        }
        if (isset($dataRecord['details'][$column])) {
            $component->addInterpretation($this->getInterpretationForColumn($dataRecord, $column));
        }
        $observation->addComponent($component);
    }

    private function populateBodyTemperatureLocation(FHIRObservation $observation, $record): void
    {
        if (empty($record['temp_method'])) {
            $observation->setDataAbsentReason(UtilsService::createDataAbsentUnknownCodeableConcept());
        } else {
            // no guidance on how to pass this on, so we are using the value string to pass this on.
            $observation->setValueString($record['temp_method']);
        }
    }

    /**
     * Creates the Provenance resource  for the equivalent FHIR Resource
     *
     * @param array|FHIRObservation $dataRecord The source OpenEMR data record
     * @param bool $encode Indicates if the returned resource is encoded into a string. Defaults to True.
     * @return FHIRProvenance|string the FHIR Resource. Returned format is defined using $encode parameter.
     */
    public function createProvenanceResource($dataRecord, $encode = false): FHIRProvenance|string|false
    {
        if (!($dataRecord instanceof FHIRObservation)) {
            throw new BadMethodCallException("Data record should be correct instance class");
        }
        $performer = null;
        if (!empty($dataRecord->getPerformer())) {
            // grab the first one
            $performer = current($dataRecord->getPerformer());
        }
        $fhirProvenance = $this->getFhirProvenanceService()->createProvenanceForDomainResource($dataRecord, $performer);
        if ($fhirProvenance === null) {
            // Provenance can legitimately be unavailable (e.g. no resolvable organization/author
            // reference); FhirServiceBase::getAll() treats a falsy return as "no provenance
            // available" and continues (see issue #13054).
            return false;
        }
        return $encode ? json_encode($fhirProvenance) : $fhirProvenance;
    }

    /**
     * Seam so unit tests can substitute the provenance factory.
     */
    protected function getFhirProvenanceService(): FhirProvenanceService
    {
        return new FhirProvenanceService();
    }

    public function getPatientContextSearchField(): FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('patient', SearchFieldType::REFERENCE, [new ServiceField('puuid', ServiceField::TYPE_UUID)]);
    }

    public function getProfileURIs(): array
    {
        $profiles = [];
        foreach (self::COLUMN_MAPPINGS as $mapping) {
            $mappingProfiles = $mapping['profiles'] ?? [];
            foreach ($mappingProfiles as $baseProfile => $versions) {
                foreach ($this->getProfileForVersions($baseProfile, $versions) as $profile) {
                    $profiles[$profile] = $profile;
                }
            }
        }
        foreach ($this->getProfileForVersions(self::USCDI_PROFILE_AVERAGE_BLOOD_PRESSURE, self::PROFILE_VERSIONS_V2) as $profile) {
            $profiles[$profile] = $profile;
        }
        return array_values($profiles);
    }

    private function addAverageBPComponents(FHIRObservation $observation, array $components): void
    {
        foreach ($components as $component) {
            $comp = new FHIRObservationComponent();

            // Set component code based on vitals_column
            $code = null;
            $description = null;

            switch ($component['vitals_column']) {
                case 'bps':
                    $code = '96608-5'; // Systolic blood pressure
                    $description = 'Systolic blood pressure mean';
                    break;
                case 'bpd':
                    $code = '96609-3'; // Diastolic blood pressure
                    $description = 'Diastolic blood pressure mean';
                    break;
                default:
                    continue 2; // Skip unknown components
            }

            $codeableConcept = UtilsService::createCodeableConcept([
                $code => [
                    'code' => $code,
                    'description' => $description,
                    'system' => FhirCodeSystemConstants::LOINC
                ]
            ]);
            $comp->setCode($codeableConcept);

            // Set value, if the value is 0 we will treat that as a data absent reason
            $value = floatval($component['value'] ?? 0);
            if ($value > 0) {
                $quantity = new FHIRQuantity();
                $quantity->setValue(floatval($component['value']));
                $quantity->setUnit($component['value_unit']);
                $quantity->setSystem(new FHIRUri(FhirCodeSystemConstants::UNITS_OF_MEASURE));
                $quantity->setCode($component['value_unit']); // mm[Hg]
                $comp->setValueQuantity($quantity);
            } else {
                $comp->setDataAbsentReason(UtilsService::createDataAbsentUnknownCodeableConcept());
            }

            $observation->addComponent($comp);
        }
    }

    protected function convertComponentCodeToBaseCode(string $code): string
    {
        return match ($code) {
            self::VITALS_CODE_SYSTOLIC_BLOOD_PRESSURE, self::VITALS_CODE_DIASTOLIC_BLOOD_PRESSURE => self::VITALS_CODE_BLOOD_PRESSURE,
            self::VITALS_CODE_PULSE_OXIMETRY_OXYGEN_FLOW_RATE, self::VITALS_CODE_PULSE_OXIMETRY_OXYGEN_CONCENTRATION => self::VITALS_CODE_PULSE_OXIMETRY,
            default => $code,
        };
    }

    /**
     * Storage columns each writable vitals code maps to.
     *
     * COLUMN_MAPPINGS pairs every value column with a `<column>_unit` name, but those
     * unit names are synthesized on read by VitalsService::search() and have no column
     * behind them in `form_vitals`, so the write path keys off the value columns alone.
     *
     * `unit` is the unit the column is stored in. VitalsService::search() converts
     * weight/height/head_circ/temperature out of storage when the units_of_measurement
     * global asks for metric, so a client can legitimately send either spelling; the
     * write converts back rather than trusting the number it was handed.
     *
     * @var array<string, array<string, array{unit: string}>>
     */
    private const VITALS_WRITE_COLUMNS = [
        '9279-1' => ['respiration' => ['unit' => '/min']],
        '8867-4' => ['pulse' => ['unit' => '/min']],
        '8310-5' => ['temperature' => ['unit' => 'degF']],
        '8302-2' => ['height' => ['unit' => 'in']],
        '9843-4' => ['head_circ' => ['unit' => 'in']],
        '29463-7' => ['weight' => ['unit' => 'lb']],
        self::VITALS_CODE_BLOOD_PRESSURE => [
            'bps' => ['unit' => 'mm[Hg]'],
            'bpd' => ['unit' => 'mm[Hg]'],
        ],
        self::VITALS_CODE_PULSE_OXIMETRY => [
            'oxygen_saturation' => ['unit' => '%'],
            'oxygen_flow_rate' => ['unit' => 'L/min'],
            'inhaled_oxygen_concentration' => ['unit' => '%'],
        ],
        self::VITALS_CODE_PULSE_OXIMETRY_OXYGEN_SATURATION => [
            'oxygen_saturation' => ['unit' => '%'],
            'oxygen_flow_rate' => ['unit' => 'L/min'],
            'inhaled_oxygen_concentration' => ['unit' => '%'],
        ],
        '8289-1' => ['ped_head_circ' => ['unit' => '%']],
        '59576-9' => ['ped_bmi' => ['unit' => '%']],
        '77606-2' => ['ped_weight_height' => ['unit' => '%']],
    ];

    /**
     * Codes this service reads but deliberately will not write, with the reason the
     * client gets back.
     *
     * Rejecting beats accepting-and-dropping: a client that posts a BMI and reads back
     * a different one has no way to tell the value was recomputed.
     *
     * @var array<string, string>
     */
    private const VITALS_UNWRITABLE_CODES = [
        self::VITALS_PANEL_LOINC_CODE =>
            'the vital signs panel is assembled from its members on read; post the individual vital signs instead',
        '39156-5' =>
            'BMI is derived from height and weight; post those instead',
        self::AVERAGE_BLOOD_PRESSURE_LOINC_CODE =>
            'average blood pressure is calculated across encounters and is not stored',
        '8327-9' =>
            'temperature location is recorded with the temperature reading, not on its own',
    ];

    /**
     * Component code -> storage column, for the two panel codes that carry their values
     * in Observation.component rather than Observation.valueQuantity.
     *
     * @var array<string, string>
     */
    private const VITALS_COMPONENT_COLUMNS = [
        self::VITALS_CODE_SYSTOLIC_BLOOD_PRESSURE => 'bps',
        self::VITALS_CODE_DIASTOLIC_BLOOD_PRESSURE => 'bpd',
        self::VITALS_CODE_PULSE_OXIMETRY_OXYGEN_SATURATION => 'oxygen_saturation',
        self::VITALS_CODE_PULSE_OXIMETRY_OXYGEN_FLOW_RATE => 'oxygen_flow_rate',
        self::VITALS_CODE_PULSE_OXIMETRY_OXYGEN_CONCENTRATION => 'inhaled_oxygen_concentration',
    ];

    /**
     * Builds a parsed record carrying a validation message for the caller to surface.
     *
     * @return array<string, mixed>
     */
    private static function vitalsValidationError(string $field, string $message): array
    {
        return [
            '__validation_error__' => $message,
            '__validation_field__' => $field,
        ];
    }

    /**
     * Converts an incoming quantity into the unit the column is stored in.
     *
     * Returns null when the unit is one this service cannot place. Storing the bare
     * number in that case would silently record 70 lb for a 70 kg patient, so callers
     * reject instead.
     */
    private static function convertQuantityToStorageUnit(float $value, string $fromUnit, string $storageUnit): ?float
    {
        $from = strtolower(trim($fromUnit));
        // UCUM spells several of these more than one way, and the read side emits the
        // short forms, so both are accepted.
        $from = match ($from) {
            '[lb_av]', 'lbs' => 'lb',
            '[in_i]', 'inch', 'inches' => 'in',
            '[degf]', 'f' => 'degf',
            'cel', 'c' => 'cel',
            '1/min', 'bpm', 'beats/min', 'breaths/min' => '/min',
            '%%' => '%',
            default => $from,
        };
        $to = strtolower($storageUnit);

        if ($from === $to) {
            return $value;
        }

        // Computed here rather than through MeasurementUtils' converters: those return
        // number_format() strings with a thousands separator, and casting "1,102.311311" to
        // float stops at the comma and yields 1.0 -- a 500 kg patient stored as 1 lb. The
        // factors and precision are the ones MeasurementUtils uses, so a value converted
        // here reads back through the read-side converters unchanged.
        $precision = MeasurementUtils::MEASUREMENT_PRECISION;
        return match ([$from, $to]) {
            ['kg', 'lb'] => round($value * 2.20462262185, $precision),
            ['g', 'lb'] => round(($value / 1000) * 2.20462262185, $precision),
            ['cm', 'in'] => round($value / 2.54, $precision),
            ['m', 'in'] => round(($value * 100) / 2.54, $precision),
            ['cel', 'degf'] => round(((9 / 5) * $value) + 32, $precision),
            default => null,
        };
    }

    /**
     * Pulls a numeric quantity value + unit out of a FHIR element.
     *
     * @param mixed $quantity The FHIRQuantity fragment as decoded JSON.
     * @return array{value: float, unit: string}|null Null when there is no usable value.
     */
    private static function readQuantity(mixed $quantity): ?array
    {
        $fragment = FhirPayloadReader::stringKeyed($quantity);
        $value = $fragment['value'] ?? null;
        if (!is_int($value) && !is_float($value) && !(is_string($value) && is_numeric($value))) {
            return null;
        }
        // Quantity.code is the UCUM symbol and Quantity.unit the human spelling; prefer
        // the coded form, which is what the read side round-trips.
        $unit = FhirPayloadReader::getString($fragment, 'code')
            ?? FhirPayloadReader::getString($fragment, 'unit')
            ?? '';

        return ['value' => (float) $value, 'unit' => $unit];
    }

    /**
     * Parses a FHIR Observation carrying a vital sign into the columns it writes.
     *
     * @param FHIRDomainResource $fhirResource
     * @return array<string, mixed>
     */
    public function parseFhirResource(FHIRDomainResource $fhirResource)
    {
        if (!($fhirResource instanceof FHIRObservation)) {
            throw new InvalidArgumentException(
                'Expected FHIRObservation resource, got ' . $fhirResource::class
            );
        }

        $json = $fhirResource->jsonSerialize();
        $data = [];

        $resourceId = $json['id'] ?? null;
        if (is_string($resourceId) && $resourceId !== '') {
            $data['uuid'] = $resourceId;
        }

        $code = FhirPayloadReader::firstCodingCode($json['code'] ?? null);
        if ($code === '') {
            return self::vitalsValidationError('code', 'Observation.code is required (FHIR R4 1..1)');
        }
        $data['code'] = $code;

        if (isset(self::VITALS_UNWRITABLE_CODES[$code])) {
            return self::vitalsValidationError(
                'code',
                'Observation.code "' . $code . '" cannot be written: ' . self::VITALS_UNWRITABLE_CODES[$code]
            );
        }
        if (!isset(self::VITALS_WRITE_COLUMNS[$code])) {
            return self::vitalsValidationError(
                'code',
                'Observation.code "' . $code . '" is not a vital sign OpenEMR can store'
            );
        }

        $subjectRef = FhirPayloadReader::reference($json['subject'] ?? null);
        $subjectUuid = $subjectRef === null
            ? null
            : (UtilsService::parseReferenceString($subjectRef, 'Patient')['uuid'] ?? null);
        if (!is_string($subjectUuid) || $subjectUuid === '' || !UuidRegistry::isValidStringUUID($subjectUuid)) {
            return self::vitalsValidationError(
                'subject',
                'Observation.subject must reference a Patient (FHIR R4 vital signs profile 1..1)'
            );
        }
        $data['puuid'] = $subjectUuid;

        // form_vitals rows are encounter forms -- VitalsService::saveVitalsArray() registers
        // each new row against an encounter through addForm(). Without an encounter the row
        // would be orphaned from the chart, so the reference is required rather than optional.
        $encounterRef = FhirPayloadReader::reference($json['encounter'] ?? null);
        $encounterUuid = $encounterRef === null
            ? null
            : (UtilsService::parseReferenceString($encounterRef, 'Encounter')['uuid'] ?? null);
        if (!is_string($encounterUuid) || $encounterUuid === '' || !UuidRegistry::isValidStringUUID($encounterUuid)) {
            return self::vitalsValidationError(
                'encounter',
                'Observation.encounter must reference an Encounter; vitals are stored as an encounter form'
            );
        }
        $data['euuid'] = $encounterUuid;

        // effectiveDateTime is half the identity of the row this Observation lands on, so a
        // vitals write cannot fall back to "now" the way a less structured resource could.
        $effective = $json['effectiveDateTime'] ?? null;
        $date = FhirDateTimeParser::toDbDateTime($effective, 'Observation.effectiveDateTime');
        if ($date === null) {
            return self::vitalsValidationError(
                'effectiveDateTime',
                'Observation.effectiveDateTime is required; it identifies the vitals reading being written'
            );
        }
        $data['date'] = $date;

        $columns = [];
        $spec = self::VITALS_WRITE_COLUMNS[$code];

        // Panel codes (blood pressure, pulse oximetry) carry their numbers in component[];
        // the single-value codes carry one valueQuantity.
        foreach (FhirPayloadReader::rows($json['component'] ?? null) as $component) {
            $componentCode = FhirPayloadReader::firstCodingCode($component['code'] ?? null);
            $column = self::VITALS_COMPONENT_COLUMNS[$componentCode] ?? null;
            if ($column === null || !isset($spec[$column])) {
                continue;
            }
            $quantity = self::readQuantity($component['valueQuantity'] ?? null);
            if ($quantity === null) {
                continue;
            }
            $converted = self::convertQuantityToStorageUnit($quantity['value'], $quantity['unit'], $spec[$column]['unit']);
            if ($converted === null) {
                return self::vitalsValidationError(
                    'component',
                    'Observation.component "' . $componentCode . '" has unit "' . $quantity['unit']
                    . '" which cannot be converted to "' . $spec[$column]['unit'] . '"'
                );
            }
            $columns[$column] = $converted;
        }

        $quantity = self::readQuantity($json['valueQuantity'] ?? null);
        if ($quantity !== null) {
            // The single-value codes list exactly one column; the panel codes list several
            // but put nothing in valueQuantity, so the first entry is the right target.
            $column = array_key_first($spec);
            $converted = self::convertQuantityToStorageUnit($quantity['value'], $quantity['unit'], $spec[$column]['unit']);
            if ($converted === null) {
                return self::vitalsValidationError(
                    'valueQuantity',
                    'Observation.valueQuantity has unit "' . $quantity['unit']
                    . '" which cannot be converted to "' . $spec[$column]['unit'] . '"'
                );
            }
            $columns[$column] = $converted;
        }

        if ($columns === []) {
            return self::vitalsValidationError(
                'value',
                'Observation for code "' . $code . '" carries no value OpenEMR can store; '
                . 'expected valueQuantity or a recognized component'
            );
        }
        $data['columns'] = $columns;

        return $data;
    }

    /**
     * @param mixed $openEmrRecord
     */
    protected function insertOpenEMRRecord($openEmrRecord): ProcessingResult
    {
        if (!is_array($openEmrRecord)) {
            throw new InvalidArgumentException('Expected a parsed OpenEMR vitals record array');
        }
        $validationResult = $this->vitalsValidationResult($openEmrRecord);
        if ($validationResult !== null) {
            return $validationResult;
        }

        $context = $this->resolveVitalsWriteContext($openEmrRecord);
        if ($context instanceof ProcessingResult) {
            return $context;
        }

        $code = $openEmrRecord['code'] ?? null;
        $date = $openEmrRecord['date'] ?? null;
        if (!is_string($code) || !is_string($date)) {
            throw new InvalidArgumentException('Parsed vitals record is missing its code or date');
        }

        $columns = FhirPayloadReader::stringKeyed($openEmrRecord['columns'] ?? null);

        // The lookup and the save have to be one step. Two clients posting different vital
        // signs for the same reading would otherwise both find no row and both insert one,
        // leaving the encounter with two Vitals forms for a single reading -- which is the
        // thing the coalescing exists to prevent, and a bulk importer posting a visit's
        // vitals in parallel is the case that hits it. The form_encounter row is what every
        // writer for this encounter has in common, so it is what they queue on.
        $saved = QueryUtils::inTransaction(function () use ($columns, $context, $date): array {
            QueryUtils::querySingleRow(
                'SELECT `encounter` FROM `form_encounter` WHERE `encounter` = ? AND `pid` = ? FOR UPDATE',
                [$context['eid'], $context['pid']]
            );

            $existing = $this->service->getVitalsFormForEncounterDate($context['eid'], $context['pid'], $date);

            $vitalsData = $columns;
            $vitalsData['pid'] = $context['pid'];
            $vitalsData['eid'] = $context['eid'];
            $vitalsData['authorized'] = 1;
            if ($existing !== null) {
                // A second vital sign for the same reading updates the row the first one
                // created rather than starting another Vitals form on the encounter.
                $vitalsData['id'] = $existing['id'];
                // The row uuid has to travel with an update as well as an insert.
                // UuidMappingEventsSubscriber keys the Observation mappings off the saved
                // record's uuid, and without one it inserts uuid_mapping rows with a null
                // target_uuid, which the column rejects.
                $vitalsData['uuid'] = $existing['uuid'];
            } else {
                $vitalsData['date'] = $date;
                $vitalsData['activity'] = 1;
            }

            return $this->service->saveVitalsArray($vitalsData);
        });
        $savedUuid = $saved['uuid'] ?? null;
        if (!is_string($savedUuid) || $savedUuid === '') {
            $result = new ProcessingResult();
            $result->setInternalErrors(['The vitals record was saved without a uuid']);
            return $result;
        }
        $rowUuid = UuidRegistry::uuidToString($savedUuid);

        $mappedUuid = $this->mappedObservationUuidForCode($rowUuid, $code);
        if ($mappedUuid === null) {
            $result = new ProcessingResult();
            $result->setInternalErrors(['Failed to register the Observation id for the saved vitals record']);
            return $result;
        }

        $result = new ProcessingResult();
        $result->addData(['uuid' => $mappedUuid]);
        return $result;
    }

    /**
     * @param string $fhirResourceId
     * @param mixed $updatedOpenEMRRecord
     */
    protected function updateOpenEMRRecord($fhirResourceId, $updatedOpenEMRRecord): ProcessingResult
    {
        if (!is_array($updatedOpenEMRRecord)) {
            throw new InvalidArgumentException('Expected a parsed OpenEMR vitals record array');
        }
        $validationResult = $this->vitalsValidationResult($updatedOpenEMRRecord);
        if ($validationResult !== null) {
            return $validationResult;
        }

        if (!UuidRegistry::isValidStringUUID($fhirResourceId)) {
            $result = new ProcessingResult();
            $result->setValidationMessages(['uuid' => 'Invalid Observation id']);
            return $result;
        }

        $mapping = UuidMapping::getMappingForUUID($fhirResourceId);
        if (
            !is_array($mapping)
            || ($mapping['resource'] ?? null) !== 'Observation'
            || ($mapping['table'] ?? null) !== VitalsService::TABLE_VITALS
        ) {
            $result = new ProcessingResult();
            $result->setValidationMessages(['uuid' => 'Observation not found for that id']);
            return $result;
        }

        // The id names one code on one row. Letting a PUT change the code would silently
        // move the value into a different column while keeping the same id, so the codes
        // have to agree.
        $mappedCode = $this->getCodeFromResourcePath($mapping['resource_path'] ?? null);
        $requestedCode = $updatedOpenEMRRecord['code'] ?? null;
        if (!is_string($requestedCode) || $mappedCode !== $requestedCode) {
            $result = new ProcessingResult();
            $result->setValidationMessages([
                'code' => 'Observation.code "' . (is_string($requestedCode) ? $requestedCode : '')
                    . '" does not match the code this id identifies ("'
                    . (is_string($mappedCode) ? $mappedCode : 'unknown') . '")',
            ]);
            return $result;
        }

        $context = $this->resolveVitalsWriteContext($updatedOpenEMRRecord);
        if ($context instanceof ProcessingResult) {
            return $context;
        }

        $targetUuid = $mapping['target_uuid'] ?? '';
        if ($targetUuid === '') {
            $result = new ProcessingResult();
            $result->setValidationMessages(['uuid' => 'Observation not found for that id']);
            return $result;
        }
        $rowUuid = UuidRegistry::uuidToString($targetUuid);
        // The row's own encounter and date come back with it: both are shared by every
        // vital sign on the record, so a PUT naming different ones is changing more than
        // the Observation it addresses.
        $row = QueryUtils::querySingleRow(
            'SELECT vitals.`id`, vitals.`pid`, vitals.`date`, `forms`.`encounter`'
            . ' FROM `' . VitalsService::TABLE_VITALS . '` vitals'
            . " JOIN `forms` ON `forms`.`form_id` = vitals.`id` AND `forms`.`formdir` = 'vitals'"
            . ' WHERE vitals.`uuid` = ?'
            . ' AND (`forms`.`deleted` IS NULL OR `forms`.`deleted` = 0)',
            [UuidRegistry::uuidToBytes($rowUuid)]
        );
        $rowId = is_array($row) ? ($row['id'] ?? null) : null;
        $rowPid = is_array($row) ? ($row['pid'] ?? null) : null;
        if (!is_numeric($rowId)) {
            $result = new ProcessingResult();
            $result->setValidationMessages(['uuid' => 'Observation not found for that id']);
            return $result;
        }

        // Same stored-patient check the other write services make: a leaked Observation id
        // must not let a caller write into a different patient's chart. This is checked
        // before the encounter and date below, so "not yours" wins over "wrong encounter".
        if (!is_numeric($rowPid) || (int) $rowPid !== $context['pid']) {
            $result = new ProcessingResult();
            $result->setValidationMessages(['uuid' => 'Observation not found for that id']);
            return $result;
        }

        $rowEncounter = $row['encounter'] ?? null;
        if (!is_numeric($rowEncounter) || (int) $rowEncounter !== $context['eid']) {
            $result = new ProcessingResult();
            $result->setValidationMessages([
                'encounter' => 'Observation.encounter does not match the vitals record this id identifies',
            ]);
            return $result;
        }

        $date = $updatedOpenEMRRecord['date'] ?? null;
        if (!is_string($date)) {
            throw new InvalidArgumentException('Parsed vitals record is missing its date');
        }

        // The date is half the identity insertOpenEMRRecord() coalesces on. Writing the
        // payload's date onto the row would move every sibling vital to the new time and
        // leave a later POST for the original time creating a second Vitals form, so a
        // changed effectiveDateTime is refused rather than applied.
        $rowDate = $row['date'] ?? null;
        if ($rowDate !== $date) {
            $result = new ProcessingResult();
            $result->setValidationMessages([
                'effectiveDateTime' => 'Observation.effectiveDateTime is shared by every vital sign on this '
                    . 'record and cannot be changed through one Observation',
            ]);
            return $result;
        }

        $vitalsData = FhirPayloadReader::stringKeyed($updatedOpenEMRRecord['columns'] ?? null);
        $vitalsData['id'] = (int) $rowId;
        $vitalsData['uuid'] = $rowUuid;
        $vitalsData['pid'] = $context['pid'];
        $vitalsData['eid'] = $context['eid'];
        $vitalsData['authorized'] = 1;
        // saveVitalsArray() builds its UPDATE from the keys it is handed, so the other
        // vital signs sharing this row are left alone.
        $this->service->saveVitalsArray($vitalsData);

        // FhirServiceBase::update() runs this result's first row back through
        // parseOpenEMRRecord() to build the response body, so it has to be the full
        // observation record the read path produces -- a bare ['uuid' => ...] would
        // serialize into an Observation with no code, subject or value.
        $readResult = $this->searchForOpenEMRRecords([
            'uuid' => new TokenSearchField('uuid', [$rowUuid], true),
            'code' => new TokenSearchField('code', [$requestedCode]),
        ]);
        if (!$readResult->isValid() || $readResult->getData() === []) {
            $result = new ProcessingResult();
            $result->setInternalErrors(['The vitals record could not be read back after the update']);
            return $result;
        }

        return $readResult;
    }

    /**
     * Turns a parse-time validation marker into a ProcessingResult, or null when the
     * record parsed cleanly.
     *
     * @param array<array-key, mixed> $record
     */
    private function vitalsValidationResult(array $record): ?ProcessingResult
    {
        $message = $record['__validation_error__'] ?? null;
        if ($message === null) {
            return null;
        }
        $field = $record['__validation_field__'] ?? 'code';
        $result = new ProcessingResult();
        $result->setValidationMessages([
            is_string($field) ? $field : 'code' => $message,
        ]);
        return $result;
    }

    /**
     * Resolves the patient and encounter a parsed vitals record writes to.
     *
     * @param array<array-key, mixed> $record
     * @return array{pid: int, eid: int}|ProcessingResult The resolved ids, or the rejection to return.
     */
    private function resolveVitalsWriteContext(array $record): array|ProcessingResult
    {
        $puuid = $record['puuid'] ?? null;
        $pid = is_string($puuid) && $puuid !== ''
            ? QueryUtils::fetchSingleValue(
                'SELECT pid FROM patient_data WHERE uuid = ?',
                'pid',
                [UuidRegistry::uuidToBytes($puuid)]
            )
            : null;
        if (!is_numeric($pid)) {
            $result = new ProcessingResult();
            $result->setValidationMessages([
                'subject' => 'Patient reference could not be resolved: ' . (is_string($puuid) ? $puuid : ''),
            ]);
            return $result;
        }

        $euuid = $record['euuid'] ?? null;
        $encounter = is_string($euuid) && $euuid !== ''
            ? QueryUtils::querySingleRow(
                'SELECT `encounter`, `pid` FROM `form_encounter` WHERE `uuid` = ?',
                [UuidRegistry::uuidToBytes($euuid)]
            )
            : null;
        $encounterId = is_array($encounter) ? ($encounter['encounter'] ?? null) : null;
        $encounterPid = is_array($encounter) ? ($encounter['pid'] ?? null) : null;
        if (!is_numeric($encounterId)) {
            $result = new ProcessingResult();
            $result->setValidationMessages([
                'encounter' => 'Encounter reference could not be resolved: ' . (is_string($euuid) ? $euuid : ''),
            ]);
            return $result;
        }

        // An encounter belonging to another patient would hang the vitals form off the
        // wrong chart, so the two references have to agree.
        if (!is_numeric($encounterPid) || (int) $encounterPid !== (int) $pid) {
            $result = new ProcessingResult();
            $result->setValidationMessages([
                'encounter' => 'Encounter does not belong to the patient named in Observation.subject',
            ]);
            return $result;
        }

        return ['pid' => (int) $pid, 'eid' => (int) $encounterId];
    }

    /**
     * Returns the mapped Observation uuid for one code on a form_vitals row.
     *
     * The mappings themselves are created by UuidMappingEventsSubscriber, which listens for
     * the vitals save event and mints the full code set for the row. That runs through the
     * kernel dispatcher on every save, so this only has to read the result back -- creating
     * them here as well would be a second writer for the same rows.
     */
    private function mappedObservationUuidForCode(string $rowUuid, string $code): ?string
    {
        $mappings = $this->getVitalSignsUuidMappings(UuidRegistry::uuidToBytes($rowUuid));
        $mappedUuid = $mappings[$code] ?? null;
        if (!is_string($mappedUuid) || $mappedUuid === '') {
            return null;
        }

        return UuidRegistry::uuidToString($mappedUuid);
    }
}
