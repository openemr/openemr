<?php

/**
 * FhirObservationLaboratoryService.php
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license    https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Observation;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRObservation;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\R4\FHIRResource\FHIRObservation\FHIRObservationReferenceRange;
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
use OpenEMR\Services\FHIR\FhirProvenanceService;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\FHIR\Indicates;
use OpenEMR\Services\FHIR\IPatientCompartmentResourceService;
use OpenEMR\Services\FHIR\OpenEMR;
use OpenEMR\Services\FHIR\openEMRSearchParameters;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\UtilsService;
use OpenEMR\Services\ProcedureService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Validators\ProcessingResult;

class FhirObservationLaboratoryService extends FhirServiceBase implements IPatientCompartmentResourceService
{
    use FhirServiceBaseEmptyTrait;

    // we set this to be 'Final' which has the follow interpretation
    // 'The observation is complete and there are no further actions needed.'
    // @see http://hl7.org/fhir/R4/valueset-observation-status.html
    const DEFAULT_OBSERVATION_STATUS = "final";

    const CATEGORY = "laboratory";

    // USCDI v5 / US Core 8.0 Extension URLs
    const EXT_SPECIMEN_SOURCE_SITE = "http://hl7.org/fhir/StructureDefinition/specimen-source-site";
    const EXT_SPECIMEN_COLLECTION_PERIOD = "http://hl7.org/fhir/StructureDefinition/specimen-collection-period";
    const EXT_SPECIMEN_CONDITION = "http://hl7.org/fhir/StructureDefinition/specimen-condition";
    const EXT_SPECIMEN_VOLUME = "http://hl7.org/fhir/StructureDefinition/specimen-volume";

    /**
     * @var ProcedureService
     */
    private $service;

    private const COLUMN_MAPPINGS = [
    ];

    public function __construct($fhirApiURL = null)
    {
        parent::__construct($fhirApiURL);
        $this->service = new ProcedureService($fhirApiURL);
//        $this->service = new ObservationLabService();
    }

    public function getResourcePathForCode($code)
    {
        return "category=" . self::CATEGORY . "&code=" . $code;
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
        // we support pretty much any LOINC code, we could hit procedure_order_code and procedure_results to be
        // specific but we'll just let the query execute.
        return true;
    }


    /**
     * Returns an array mapping FHIR Resource search parameters to OpenEMR search parameters
     */
    protected function loadSearchParameters()
    {
        return [
            'patient' => $this->getPatientContextSearchField(),
            'code' => new FhirSearchParameterDefinition('code', SearchFieldType::TOKEN, ['result_code']),
            'category' => new FhirSearchParameterDefinition('category', SearchFieldType::TOKEN, ['category']),
            'date' => new FhirSearchParameterDefinition('date', SearchFieldType::DATETIME, ['report_date']),
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('result_uuid', ServiceField::TYPE_UUID)]),
            '_lastUpdated' => $this->getLastModifiedSearchField()
        ];
    }

    public function getLastModifiedSearchField(): ?FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['report_date']);
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
            // if we have a category let's remove it as its being passed from our upper layer and we don't want to map
            // it to our procedure codes.
            unset($openEMRSearchParameters['category']);

//            $result = $this->service->search($newSearchParams, true);
            $result = $this->service->search($openEMRSearchParameters, true);
            $data = $result->getData() ?? [];

            // need to transform these into something we can consume
            foreach ($result->getData() as $record) {
                // each vital record becomes a 1 -> many record for our observations
                $this->parseDataRecordsIntoObservationRecords($processingResult, $record);
            }
        } catch (SearchFieldException $exception) {
            $processingResult->setValidationMessages([$exception->getField() => $exception->getMessage()]);
        }

        return $processingResult;
    }

    private function parseDataRecordsIntoObservationRecords(ProcessingResult $processingResult, $record)
    {
        $patient = $record['patient'] ?? null;

        if (!empty($record['reports'])) {
            foreach ($record['reports'] as $report) {
                if (!empty($report['results'])) {
                    foreach ($report['results'] as $result) {
                        $result['patient'] = $patient;
                        $result['report_date'] = $report['date'];
                        // Pass specimen data from procedure_order to results
                        $result['specimen_type'] = $report['specimen_type'] ?? null;
                        $result['specimen_location'] = $report['specimen_location'] ?? null;
                        $result['specimen_volume'] = $report['specimen_volume'] ?? null;
                        // Map date_collected to collection start and date_collected_end to collection end
                        $result['specimen_collection_start'] = $report['date_collected'] ?? null;
                        $result['specimen_collection_end'] = $report['date_collected_end'] ?? null;
                        $result['specimen_condition'] = $report['specimen_condition'] ?? null;
                        $result['specimen_identifier'] = $report['specimen_identifier'] ?? null;
                        $processingResult->addData($result);
                    }
                }
            }
        }
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
        if (!empty($dataRecord['report_date'])) {
            $meta->setLastUpdated(UtilsService::getLocalDateAsUTC($dataRecord['report_date']));
        } else {
            $meta->setLastUpdated(UtilsService::getDateFormattedAsUTC());
        }
        $observation->setMeta($meta);

        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $observation->setId($id);

        if (!empty($dataRecord['report_date'])) {
            $observation->setEffectiveDateTime(UtilsService::getLocalDateAsUTC($dataRecord['report_date']));
        } else {
            $observation->setEffectiveDateTime(UtilsService::createDataMissingExtension());
        }

        $obsConcept = new FHIRCodeableConcept();
        $obsCategoryCoding = new FhirCoding();
        $obsCategoryCoding->setSystem(FhirCodeSystemConstants::HL7_OBSERVATION_CATEGORY);
        $obsCategoryCoding->setCode(self::CATEGORY);
        $obsConcept->addCoding($obsCategoryCoding);
        $observation->addCategory($obsConcept);

        $categoryCoding = new FHIRCoding();
        $categoryCode = new FHIRCodeableConcept();
        // ONC FHIR requirements require there is a text value for the code, otherwise the code is not reported.
        if (!empty($dataRecord['code']) && !empty($dataRecord['text'])) {
            $categoryCoding->setCode($dataRecord['code']);
            $categoryCoding->setDisplay($dataRecord['text']);
            $categoryCoding->setSystem(FhirCodeSystemConstants::LOINC);
            $categoryCode->addCoding($categoryCoding);
            $observation->setCode($categoryCode);
        } else {
            $observation->setCode(UtilsService::createNullFlavorUnknownCodeableConcept());
        }

        $status = $this->getValidStatus($dataRecord['status'] ?? 'unknown');
        $observation->setStatus($status);

        if (!empty($dataRecord['range_low']) && !empty($dataRecord['range_high'])) {
            $referenceRange = new FHIRObservationReferenceRange();
            if (isset($dataRecord['range_low'])) {
                $referenceRange->setLow(UtilsService::createQuantity($dataRecord['range_low'], $dataRecord['units'], $dataRecord['units']));
            }
            if (isset($dataRecord['range_high'])) {
                $referenceRange->setHigh(UtilsService::createQuantity($dataRecord['range_high'], $dataRecord['units'], $dataRecord['units']));
            }
            $observation->addReferenceRange($referenceRange);
        }

        if (!empty($dataRecord['result'])) {
            if (is_numeric($dataRecord['result'])) {
                $quantity = new FHIRQuantity();
                $quantityValue = $dataRecord['result'];
                $unit = $dataRecord['units'] ?? null;
                if (!empty($unit)) {
                    if ($unit === 'in') {
                        $unit = 'in_i';
                    } else if ($unit === 'lb') {
                        $unit = 'lb_av';
                    }
                    $quantity->setUnit($unit);
                    $quantity->setSystem(FhirCodeSystemConstants::UNITS_OF_MEASURE);
                }

                if (is_float($quantityValue)) {
                    $quantity->setValue(floatval($quantityValue));
                } else {
                    $quantity->setValue(intval($quantityValue));
                }
                $observation->setValueQuantity($quantity);
            } else {
                $observation->setValueString($dataRecord['result']);
            }
        } else {
            $observation->setDataAbsentReason(UtilsService::createDataAbsentUnknownCodeableConcept());
        }

        if (!empty($dataRecord['provider']['uuid']) && !empty($dataRecord['provider']['npi'])) {
            $observation->addPerformer(UtilsService::createRelativeReference('Practitioner', $dataRecord['provider']['uuid']));
        }

        if (!empty($dataRecord['comments'])) {
            $observation->addNote(['text' => $dataRecord['comments']]);
        }

        if (!empty($dataRecord['patient'])) {
            $observation->setSubject(UtilsService::createRelativeReference("Patient", $dataRecord['patient']['uuid']));
        }

        // USCDI v5 / US Core 8.0 Specimen Information
        if (!empty($dataRecord['specimen_type'])) {
            $specimen = $this->createSpecimenReference($dataRecord);
            $observation->setSpecimen($specimen);
        }

        // Add interpretation (Result Interpretation - USCDI v5)
        if (!empty($dataRecord['result_abnormal'])) {
            $interpretation = UtilsService::createCodeableConcept([
                $dataRecord['result_abnormal'] => [
                    'code' => $dataRecord['result_abnormal'],
                    'system' => FhirCodeSystemConstants::HL7_V3_OBSERVATION_INTERPRETATION
                ]
            ]);
            $observation->addInterpretation($interpretation);
        }

        return $observation;
    }

    /**
     * Creates a Specimen Reference with USCDI v5 extensions
     *
     * @param array $dataRecord The data record containing specimen information
     * @return FHIRReference The specimen reference with extensions
     */
    private function createSpecimenReference(array $dataRecord): FHIRReference
    {
        $specimen = new FHIRReference();

        // Set display text (specimen type)
        if (!empty($dataRecord['specimen_type'])) {
            $specimen->setDisplay($dataRecord['specimen_type']);
        }

        // Specimen Identifier (USCDI v5)
        if (!empty($dataRecord['specimen_identifier'])) {
            $identifier = new FHIRIdentifier();
            $identifier->setValue($dataRecord['specimen_identifier']);
            $identifier->setSystem('urn:oid:2.16.840.1.113883.4.349'); // Use appropriate system
            $specimen->setIdentifier($identifier);
        }

        // Specimen Source Site / Location (USCDI v5)
        if (!empty($dataRecord['specimen_location'])) {
            $sourceSiteExt = new FHIRExtension();
            $sourceSiteExt->setUrl(self::EXT_SPECIMEN_SOURCE_SITE);

            $sourceSiteConcept = new FHIRCodeableConcept();
            $sourceSiteCoding = new FHIRCoding();
            $sourceSiteCoding->setDisplay($dataRecord['specimen_location']);
            // Add SNOMED CT code if available in your data
            if (!empty($dataRecord['specimen_location_code'])) {
                $sourceSiteCoding->setCode($dataRecord['specimen_location_code']);
                $sourceSiteCoding->setSystem(FhirCodeSystemConstants::SNOMED_CT);
            }
            $sourceSiteConcept->addCoding($sourceSiteCoding);
            $sourceSiteExt->setValueCodeableConcept($sourceSiteConcept);

            $specimen->addExtension($sourceSiteExt);
        }

        // Specimen Collection Period (USCDI v5)
        if (!empty($dataRecord['specimen_collection_start']) || !empty($dataRecord['specimen_collection_end'])) {
            $collectionPeriodExt = new FHIRExtension();
            $collectionPeriodExt->setUrl(self::EXT_SPECIMEN_COLLECTION_PERIOD);

            $period = new FHIRPeriod();
            if (!empty($dataRecord['specimen_collection_start'])) {
                $period->setStart(UtilsService::getLocalDateAsUTC($dataRecord['specimen_collection_start']));
            }
            if (!empty($dataRecord['specimen_collection_end'])) {
                $period->setEnd(UtilsService::getLocalDateAsUTC($dataRecord['specimen_collection_end']));
            }
            $collectionPeriodExt->setValuePeriod($period);

            $specimen->addExtension($collectionPeriodExt);
        }

        // Specimen Volume (USCDI v5)
        if (!empty($dataRecord['specimen_volume'])) {
            $volumeExt = new FHIRExtension();
            $volumeExt->setUrl(self::EXT_SPECIMEN_VOLUME);

            $volumeQuantity = new FHIRQuantity();
            $volumeQuantity->setValue(floatval($dataRecord['specimen_volume']));
            $volumeQuantity->setUnit('mL');
            $volumeQuantity->setSystem(FhirCodeSystemConstants::UNITS_OF_MEASURE);
            $volumeQuantity->setCode('mL');

            $volumeExt->setValueQuantity($volumeQuantity);
            $specimen->addExtension($volumeExt);
        }

        // Specimen Condition Acceptability (USCDI v5)
        if (!empty($dataRecord['specimen_condition'])) {
            $conditionExt = new FHIRExtension();
            $conditionExt->setUrl(self::EXT_SPECIMEN_CONDITION);

            $conditionConcept = new FHIRCodeableConcept();
            $conditionCoding = new FHIRCoding();

            // Map specimen condition to HL7 V2 Table 0493
            // Common values: "acceptable", "hemolyzed", "lipemic", "contaminated"
            $conditionCode = $this->mapSpecimenConditionToHL7($dataRecord['specimen_condition']);
            $conditionCoding->setCode($conditionCode);
            $conditionCoding->setDisplay($dataRecord['specimen_condition']);
            $conditionCoding->setSystem('http://terminology.hl7.org/CodeSystem/v2-0493');

            $conditionConcept->addCoding($conditionCoding);
            $conditionExt->setValueCodeableConcept($conditionConcept);

            $specimen->addExtension($conditionExt);
        }

        return $specimen;
    }

    /**
     * Maps specimen condition text to HL7 V2 Table 0493 codes
     *
     * @param string $condition The specimen condition text
     * @return string The HL7 code
     */
    private function mapSpecimenConditionToHL7(string $condition): string
    {
        $conditionLower = strtolower($condition);

        $mappings = [
            'acceptable' => 'ACT',
            'hemolyzed' => 'HEM',
            'lipemic' => 'LIP',
            'contaminated' => 'CON',
            'clotted' => 'CLO',
            'insufficient' => 'INS',
            'QNS' => 'QNS', // Quantity Not Sufficient
        ];

        foreach ($mappings as $key => $code) {
            if (strpos($conditionLower, $key) !== false) {
                return $code;
            }
        }

        return 'ACT'; // Default to acceptable
    }

    private function getValidStatus($status)
    {
        $statii = ['registered', 'preliminary', 'final', 'amended', 'corrected', 'cancelled', 'entered-in-error', 'unknown'];
        if (array_search($status, $statii) !== false) {
            return $status;
        }
        return "unknown";
    }
    private function getDescriptionForCode($code)
    {
        $codeMapping = self::COLUMN_MAPPINGS[$code] ?? null;
        if (isset($codeMapping)) {
            return $codeMapping['description'];
        }
        return "";
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
