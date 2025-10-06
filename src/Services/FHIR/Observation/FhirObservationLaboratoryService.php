<?php

/**
 * FhirObservationLaboratoryService.php
 * USCDI v5 / US Core 8.0 Compliant Laboratory Observation Service
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
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSpecimen;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUri;
use OpenEMR\FHIR\R4\FHIRResource\FHIRObservation\FHIRObservationReferenceRange;
use OpenEMR\FHIR\R4\FHIRResource\FHIRSpecimen\FHIRSpecimenCollection;
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
use OpenEMR\Services\FHIR\FhirProvenanceService;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\FHIR\IPatientCompartmentResourceService;
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

    const DEFAULT_OBSERVATION_STATUS = "final";
    const CATEGORY = "laboratory";

    /**
     * @var ProcedureService
     */
    private $service;

    public function __construct($fhirApiURL = null)
    {
        parent::__construct($fhirApiURL);
        $this->service = new ProcedureService($fhirApiURL);
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

    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        try {
            unset($openEMRSearchParameters['category']);
            $result = $this->service->search($openEMRSearchParameters, true);

            foreach ($result->getData() as $record) {
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

                        // Specimen data comes from report (linked to procedure_specimen table)
                        if (!empty($report['specimen'])) {
                            $result['specimen'] = $report['specimen'];
                        }

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
    public function parseOpenEMRRecord($dataRecord = [], $encode = false)
    {
        $observation = new FHIRObservation();

        // Meta with US Core Lab profile
        $meta = new FHIRMeta();
        $meta->setVersionId('1');
        $meta->addProfile(new FHIRUri(['value' => 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-observation-lab']));

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

        // Category - laboratory (required)
        $obsConcept = new FHIRCodeableConcept();
        $obsCategoryCoding = new FhirCoding();
        $obsCategoryCoding->setSystem(FhirCodeSystemConstants::HL7_OBSERVATION_CATEGORY);
        $obsCategoryCoding->setCode(self::CATEGORY);
        $obsConcept->addCoding($obsCategoryCoding);
        $observation->addCategory($obsConcept);

        // Code - LOINC (required)
        $categoryCoding = new FHIRCoding();
        $categoryCode = new FHIRCodeableConcept();
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

        // Reference range
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

        // Value
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

        // USCDI v5 Specimen - reference independent Specimen resource
        if (!empty($dataRecord['specimen']['uuid'])) {
            $observation->setSpecimen(
                UtilsService::createRelativeReference('Specimen', $dataRecord['specimen']['uuid'])
            );
        }
        // Interpretation (USCDI v5)
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

    private function getValidStatus($status)
    {
        $statii = ['registered', 'preliminary', 'final', 'amended', 'corrected', 'cancelled', 'entered-in-error', 'unknown'];
        if (array_search($status, $statii) !== false) {
            return $status;
        }
        return "unknown";
    }

    public function createProvenanceResource($dataRecord, $encode = false)
    {
        if (!($dataRecord instanceof FHIRObservation)) {
            throw new \BadMethodCallException("Data record should be correct instance class");
        }
        $fhirProvenanceService = new FhirProvenanceService();
        $performer = null;
        if (!empty($dataRecord->getPerformer())) {
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
