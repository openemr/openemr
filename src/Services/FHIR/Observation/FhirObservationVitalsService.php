<?php

/**
 * FhirObservationVitalsService.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Observation;

use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UuidMapping;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRObservation;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity;
use OpenEMR\FHIR\R4\FHIRResource\FHIRObservation\FHIRObservationComponent;
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
use OpenEMR\Services\FHIR\FhirProvenanceService;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\FHIR\IPatientCompartmentResourceService;
use OpenEMR\Services\FHIR\UtilsService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\VitalsService;
use OpenEMR\Validators\ProcessingResult;

class FhirObservationVitalsService extends FhirServiceBase implements IPatientCompartmentResourceService
{
    // we set this to be 'Final' which has the follow interpretation
    // 'The observation is complete and there are no further actions needed.'
    // @see http://hl7.org/fhir/R4/valueset-observation-status.html
    const VITALS_DEFAULT_OBSERVATION_STATUS = "final";

    const VITALS_PANEL_LOINC_CODE = '85353-1';
    /**
     * @var VitalsService
     */
    private $service;

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
        ],
        '9279-1' => [
            'fullcode' => 'LOINC:9279-1'
            ,'code' => '9279-1'
            ,'description' => 'Respiratory Rate'
            ,'column' => ['respiration', 'respiration_unit']
            ,'in_vitals_panel' => true
        ]
        ,'8867-4' => [
            'fullcode' => 'LOINC:8867-4'
            ,'code' => '8867-4'
            ,'description' => 'Heart rate'
            ,'column' => ['pulse', 'pulse_unit']
            ,'in_vitals_panel' => true
        ]
        ,'2708-6' => [
            'fullcode' => 'LOINC:2708-6'
            ,'code' => '2708-6'
            ,'description' => 'Oxygen saturation in Arterial blood'
            ,'column' => ['oxygen_saturation', 'oxygen_saturation_unit']
            ,'in_vitals_panel' => true
        ]
        ,'59408-5' => [
            'fullcode' => 'LOINC:59408-5',
            'code' => '59408-5',
            'description' => 'Oxygen saturation in Arterial blood by Pulse oximetry',
            'column' => ['oxygen_saturation', 'oxygen_saturation_unit', 'oxygen_flow_rate', 'oxygen_flow_rate_unit'],
            'in_vitals_panel' => true
        ]
        ,'3151-8' => [
            'fullcode' => 'LOINC:3151-8'
            ,'code' => '3151-8',
            'description' => 'Inhaled oxygen flow rate',
            'column' => ['oxygen_flow_rate', 'oxygen_flow_rate_unit'],
            'in_vitals_panel' => true
        ]
        ,'8310-5' => [
            'fullcode' => 'LOINC:8310-5',
            'code' => '8310-5',
            'description' => 'Body Temperature',
            'column' => ['temperature', 'temperature_unit'],
            'in_vitals_panel' => true
        ]
        ,'8327-9' => [
            'fullcode' => 'LOINC:8327-9',
            'code' => '8327-9',
            'description' => 'Temperature Location',
            'column' => 'temp_method',
            'in_vitals_panel' => true
        ]
        ,'8302-2' => [
            'fullcode' => 'LOINC:8302-2',
            'code' => '8302-2',
            'description' => 'Body height',
            'column' => ['height', 'height_unit'],
            'in_vitals_panel' => true
        ]
        ,'9843-4' => [
            'fullcode' => 'LOINC:9843-4'
            ,'code' => '9843-4'
            ,'description' => 'Head Occipital-frontal circumference'
            ,'column' => ['head_circ', 'head_circ_unit']
            ,'in_vitals_panel' => true
        ]
        ,'29463-7' => [
            'fullcode' => 'LOINC:29463-7'
            ,'code' => '29463-7'
            ,'description' => 'Body weight'
            ,'column' => ['weight', 'weight_unit']
            ,'in_vitals_panel' => true
        ]
        ,'39156-5' => [
            'fullcode' => 'LOINC:39156-5'
            ,'code' => '39156-5'
            ,'description' => 'Body mass index (BMI) [Ratio]'
            ,'column' =>  ['BMI', 'BMI_status', 'BMI_unit']
            ,'in_vitals_panel' => true
        ]
        ,'85354-9' => [
            'fullcode' => 'LOINC:85354-9'
            ,'code' => '85354-9'
            ,'description' => 'Blood pressure systolic and diastolic'
            // we hack this a bit to make it work by having our systolic and diastolic together
            ,'column' => ['bps', 'bps_unit', 'bpd', 'bpd_unit']
            ,'in_vitals_panel' => true
        ]
        ,'8480-6' => [
            'fullcode' => 'LOINC:8480-6'
            ,'code' => '8480-6'
            ,'description' => 'Systolic blood pressure'
            // we hack this a bit to make it work by having our systolic and diastolic together
            ,'column' => ['bps', 'bps_unit']
            ,'in_vitals_panel' => true
        ]
        ,'8462-4' => [
            'fullcode' => 'LOINC:8462-4'
            ,'code' => '8462-4'
            ,'description' => 'Diastolic blood pressure'
            // we hack this a bit to make it work by having our systolic and diastolic together
            ,'column' => ['bpd', 'bpd_unit']
            ,'in_vitals_panel' => true
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
        ]
        // 2-20yr @see https://www.cdc.gov/growthcharts/html_charts/bmiagerev.htm
        ,'59576-9' => [
            'fullcode' => 'LOINC:59576-9'
            ,'code' => '59576-9'
            ,'description' => 'Body mass index (BMI) [Percentile] Per age and sex'
            ,'column' => ['ped_bmi', 'ped_bmi_unit']
            ,'in_vitals_panel' => false
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
        ],
        '3150-0' => [
            'fullcode' => 'LOINC:3150-0'
            ,'code' => '3150-0'
            ,'description' => 'Inhaled Oxygen Saturation'
            ,'column' => ['inhaled_oxygen_concentration', 'inhaled_oxygen_concentration_unit']
            ,'in_vitals_panel' => false
        ]
        // need pediatric weight for height observations...
    ];

    public function __construct($fhirApiURL = null)
    {
        parent::__construct($fhirApiURL);
        $this->service = new VitalsService();
    }

    public function getCodeFromResourcePath($resourcePath)
    {
        $query_vars = [];
        parse_str($resourcePath, $query_vars);
        return $query_vars['code'] ?? null;
    }

    public function supportsCategory($category)
    {
        return ($category === self::CATEGORY);
    }

    public function supportsCode($code)
    {
        return array_search($code, array_keys(self::COLUMN_MAPPINGS)) !== false;
    }


    /**
     * Returns an array mapping FHIR Resource search parameters to OpenEMR search parameters
     */
    protected function loadSearchParameters()
    {
        return [
            'patient' => $this->getPatientContextSearchField(),
            'code' => new FhirSearchParameterDefinition('code', SearchFieldType::TOKEN, ['code']),
            'category' => new FhirSearchParameterDefinition('category', SearchFieldType::TOKEN, ['category']),
            'date' => new FhirSearchParameterDefinition('date', SearchFieldType::DATETIME, ['date']),
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)]),
        ];
    }


    /**
     * Inserts an OpenEMR record into the sytem.
     * @return The OpenEMR processing result.
     */
    protected function insertOpenEMRRecord($openEmrRecord)
    {
        // TODO: Implement insertOpenEMRRecord() method.
    }

    /**
     * Updates an existing OpenEMR record.
     * @param $fhirResourceId The OpenEMR record's FHIR Resource ID.
     * @param $updatedOpenEMRRecord The "updated" OpenEMR record.
     * @return The OpenEMR Service Result
     */
    protected function updateOpenEMRRecord($fhirResourceId, $updatedOpenEMRRecord)
    {
        // TODO: Implement updateOpenEMRRecord() method.
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     * @param openEMRSearchParameters OpenEMR search fields
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return OpenEMR records
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
                /**
                 * @var TokenSearchField
                 */
                $code = $openEMRSearchParameters['code'];
                if (!($code instanceof TokenSearchField)) {
                    throw new SearchFieldException('code', "Invalid code");
                }
                foreach ($code->getValues() as $value) {
                    $code = $value->getCode();
                    $observationCodesToReturn[$code] = $code;
                }
                unset($openEMRSearchParameters['code']);
            }


            if (empty($observationCodesToReturn)) {
                // grab everything
                $observationCodesToReturn = array_keys(self::COLUMN_MAPPINGS);
                $observationCodesToReturn = array_combine($observationCodesToReturn, $observationCodesToReturn);
            }

            // convert vital sign records from 1:many

            $result = $this->service->search($openEMRSearchParameters, true);
            $data = $result->getData() ?? [];

            // need to transform these into something we can consume
            foreach ($data as $record) {
                // each vital record becomes a 1 -> many record for our observations
                $this->parseVitalsIntoObservationRecords($processingResult, $record, $observationCodesToReturn);
            }
        } catch (SearchFieldException $exception) {
            $processingResult->setValidationMessages([$exception->getField() => $exception->getMessage()]);
        }

        return $processingResult;
    }

    private function parseVitalsIntoObservationRecords(ProcessingResult $processingResult, $record, $observationCodesToReturn)
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
                    , "members" => []
                    , "uuid" => UuidRegistry::uuidToString($uuidMappings[self::VITALS_PANEL_LOINC_CODE])
                    , "user_uuid" => $record['user_uuid']
                    , "date" => $record['date']
                ];
                foreach ($uuidMappings as $code => $uuid) {
                    if (!$this->isVitalSignPanelCodes($code)) {  // we will skip over our vital signs code, and any pediatric stuff
                        continue;
                    }
                    $vitalsRecord["members"][$code] = UuidRegistry::uuidToString($uuid);
                }
                $processingResult->addData($vitalsRecord);
                unset($observationCodesToReturn[self::VITALS_PANEL_LOINC_CODE]);
            } else {
                (new SystemLogger())->error("FhirVitalsService->parseVitalsIntoObservationRecords() Cannot return vitals panel as mapping uuid is missing for code " . self::VITALS_PANEL_LOINC_CODE);
            }
        }
        foreach ($observationCodesToReturn as $code) {
            $vitalsRecord = [
                "code" => $code
                ,"description" => $this->getDescriptionForCode($code)
                ,"category" => self::CATEGORY
                , "puuid" => $record['puuid']
                , "euuid" => $record['euuid']
                , "user_uuid" => $record['user_uuid']
                ,"uuid" => UuidRegistry::uuidToString($uuidMappings[$code])
                ,"date" => $record['date']
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

    private function getVitalSignsUuidMappings($uuid)
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
     * Parses a FHIR Resource, returning the equivalent OpenEMR record.
     *
     * @param $fhirResource The source FHIR resource
     * @return a mapped OpenEMR data record (array)
     */
    public function parseFhirResource($fhirResource = array())
    {
    }


    /**
     * Parses an OpenEMR data record, returning the equivalent FHIR Resource
     *
     * @param $dataRecord The source OpenEMR data record
     * @param $encode Indicates if the returned resource is encoded into a string. Defaults to True.
     * @return the FHIR Resource. Returned format is defined using $encode parameter.
     */
    public function parseOpenEMRRecord($dataRecord = array(), $encode = false)
    {
        $observation = new FHIRObservation();
        $meta = new FHIRMeta();
        $meta->setVersionId('1');
        $meta->setLastUpdated(gmdate('c'));
        $observation->setMeta($meta);

        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $observation->setId($id);

        if (!empty($dataRecord['date'])) {
            $observation->setEffectiveDateTime(gmdate('c', strtotime($dataRecord['date'])));
        } else {
            $observation->setEffectiveDateTime(UtilsService::createDataMissingExtension());
        }

        $code = $dataRecord['code'];
        $description = $this->getDescriptionForCode($code);

        $categoryCoding = new FHIRCoding();
        $categoryCode = new FHIRCodeableConcept();
        if (!empty($dataRecord['code'])) {
            $categoryCoding->setCode($dataRecord['code']);
            $categoryCoding->setDisplay($description);
            $categoryCoding->setSystem(FhirCodeSystemConstants::LOINC);
            $categoryCode->addCoding($categoryCoding);
            $observation->setCode($categoryCode);
        }


        $observation->setStatus(self::VITALS_DEFAULT_OBSERVATION_STATUS);

        if (!empty($dataRecord['user_uuid']) && !empty($dataRecord['user_npi'])) {
            $observation->addPerformer(UtilsService::createRelativeReference("Practitioner", $dataRecord['user_uuid']));
        }

        $obsConcept = new FHIRCodeableConcept();
        $obsCategoryCoding = new FhirCoding();
        $obsCategoryCoding->setSystem(FhirCodeSystemConstants::HL7_OBSERVATION_CATEGORY);
        $obsCategoryCoding->setCode($dataRecord['category']);
        $obsConcept->addCoding($obsCategoryCoding);
        $observation->addCategory($obsConcept);

        $observation->setSubject(UtilsService::createRelativeReference("Patient", $dataRecord['puuid']));

        if (!empty($dataRecord['notes'])) {
            $observation->addNote(['text' => $dataRecord['notes']]);
        }

        $basic_codes = [
            "9279-1" => 'respiration', "8867-4" => 'pulse', '2708-6' => 'oxygen_saturation'
            , '3150-0' => 'inhaled_oxygen_concentration'
            , '8310-5' => 'temperature'
            ,'8302-2' => 'height', '9843-4' => 'head_circ', '29463-7' => 'weight', '39156-5' => 'BMI'
            ,'59576-9' => 'ped_bmi', '8289-1' => 'ped_head_circ', '77606-2' => 'ped_weight_height'
        ];

        if (isset($basic_codes[$code])) {
            $this->populateBasicQuantityObservation($basic_codes[$code], $observation, $dataRecord);
        }
        // more complicated codes
        switch ($code) {
            case self::VITALS_PANEL_LOINC_CODE: // vital-signs panel
                $this->populateVitalSignsPanelObservation($observation, $dataRecord);
                break;
            case '8327-9':
                $this->populateBodyTemperatureLocation($observation, $dataRecord);
                break;
            case '85354-9': // blood pressure panel that includes systolic & diastolic pressure
                $this->populateBloodPressurePanel($observation, $dataRecord);
                break;
            case '8480-6':
                $this->populateComponentColumn(
                    $observation,
                    $dataRecord,
                    'bps',
                    '8480-6',
                    $this->getDescriptionForCode('8480-6')
                );
                break;
            case '8462-4':
                $this->populateComponentColumn(
                    $observation,
                    $dataRecord,
                    'bpd',
                    '8462-4',
                    $this->getDescriptionForCode('8462-4')
                );
                break;
            case '2708-6':
                $this->populateCoding($observation, '59408-5');
                break;
            case '59408-5':
                $this->populatePulseOximetryObservation($observation, $dataRecord);
                break;
        }
        return $observation;
    }

    private function getColumnsForCode($code)
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

    private function populateCoding(FHIRObservation $observation, $code)
    {
        // add additional oxygen-saturation coding
        $oxSaturation = new FHIRCoding();
        $oxSaturation->setCode($code);
        $oxSaturation->setDisplay($this->getDescriptionForCode($code));
        $oxSaturation->setSystem(FhirCodeSystemConstants::LOINC);

        $observation->getCode()->addCoding($oxSaturation);
    }

    private function populatePulseOximetryObservation(FHIRObservation $observation, $dataRecord)
    {
        $this->populateCoding($observation, '2708-6');
        if (
            $this->columnHasPositiveFloatValue('oxygen_flow_rate', $dataRecord)
            || $this->columnHasPositiveFloatValue('oxygen_saturation', $dataRecord)
        ) {
            $this->populateComponentColumn(
                $observation,
                $dataRecord,
                'oxygen_flow_rate',
                '3151-8',
                $this->getDescriptionForCode('3151-8')
            );
            $this->populateComponentColumn(
                $observation,
                $dataRecord,
                'oxygen_saturation',
                '3150-0',
                // only place this is used.
                'Oxygen saturation in Arterial blood'
            );
        } else {
            $observation->setDataAbsentReason(UtilsService::createDataAbsentUnknownCodeableConcept());
        }
    }

    private function populateVitalSignsPanelObservation(FHIRObservation $observation, $record)
    {
        if (!empty($record['members'])) {
            foreach ($record['members'] as $code => $uuid) {
                $reference = UtilsService::createRelativeReference("Observation", $uuid);
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

    private function populateBasicQuantityObservation($column, FHIRObservation $observation, $record)
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

    private function getFHIRQuantityForColumn($column, $record)
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
            } else if ($unit === 'lb') {
                $unit = 'lb_av';
                $code = "[" . $unit . "]";
            } else if ($unit === 'degF') {
                $code = "[" . $unit . "]";
            }
            $quantity->setCode($code);
            $quantity->setUnit($unit);
            return $quantity;
        }
        return null;
    }

    private function columnHasPositiveFloatValue($column, $record)
    {
        return (isset($record[$column]) && floatval($record[$column]) > 0.00);
    }

    private function populateBloodPressurePanel(FHIRObservation $observation, $dataRecord)
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
                '8480-6',
                $this->getDescriptionForCode('8480-6')
            );
            $this->populateComponentColumn(
                $observation,
                $dataRecord,
                'bpd',
                '8462-4',
                $this->getDescriptionForCode('8462-4')
            );
    }

    private function populateComponentColumn(FHIRObservation $observation, $dataRecord, $column, $code, $description)
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

    private function populateBodyTemperatureLocation(FHIRObservation $observation, $record)
    {
        // no guidance on how to pass this on, so we are using the value string to pass this on.
        $observation->setValueString($record['temp_method']);
    }

    /**
     * Creates the Provenance resource  for the equivalent FHIR Resource
     *
     * @param $dataRecord The source OpenEMR data record
     * @param $encode Indicates if the returned resource is encoded into a string. Defaults to True.
     * @return the FHIR Resource. Returned format is defined using $encode parameter.
     */
    public function createProvenanceResource($dataRecord, $encode = false)
    {
        if (!($dataRecord instanceof FHIRObservation)) {
            throw new \BadMethodCallException("Data record should be correct instance class");
        }
        $fhirProvenanceService = new FhirProvenanceService();
        $performer = null;
        if (!empty($dataRecord->getPerformer())) {
            // grab the first one
            $performer = current($dataRecord->getPerformer());
        }
        $fhirProvenance = $fhirProvenanceService->createProvenanceForDomainResource($dataRecord, $performer);
        if ($encode) {
            return json_encode($fhirProvenance);
        } else {
            return $fhirProvenance;
        }
    }

    public function getPatientContextSearchField(): FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('patient', SearchFieldType::REFERENCE, [new ServiceField('puuid', ServiceField::TYPE_UUID)]);
    }
}
