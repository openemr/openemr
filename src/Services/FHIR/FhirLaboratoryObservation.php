<?php
/**
 * FhirLaboratoryObservation.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR;

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
use OpenEMR\FHIR\R4\FHIRResource\FHIRObservation\FHIRObservationReferenceRange;
use OpenEMR\Services\ListService;
use OpenEMR\Services\ObservationLabService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use OpenEMR\Services\SocialHistoryService;
use OpenEMR\Services\VitalsService;
use OpenEMR\Validators\ProcessingResult;

class FhirLaboratoryObservation extends FhirServiceBase implements IPatientCompartmentResourceService
{
    // we set this to be 'Final' which has the follow interpretation
    // 'The observation is complete and there are no further actions needed.'
    // @see http://hl7.org/fhir/R4/valueset-observation-status.html
    const DEFAULT_OBSERVATION_STATUS = "final";

    const CATEGORY = "laboratory";

    /**
     * @var ObservationLabService
     */
    private $service;

    private const COLUMN_MAPPINGS = [
    ];

    public function __construct($fhirApiURL = null)
    {
        parent::__construct($fhirApiURL);
        $this->service = new ObservationLabService();
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
        // if we have no codes here than we just won't respond to the request
        // obviously this could be cached if we wanted to optimize this.
        return $this->service->isValidProcedureCode($code);
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
            'date' => new FhirSearchParameterDefinition('date', SearchFieldType::DATETIME, ['date_report']),
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
            // if we have a category let's remove it as its being passed from our upper layer and we don't want to map
            // it to our procedure codes.
            unset($openEMRSearchParameters['category']);

//            $result = $this->service->search($newSearchParams, true);
            $result = $this->service->search($openEMRSearchParameters, true);
            $data = $result->getData() ?? [];

            // need to transform these into something we can consume
            foreach ($result->getData() as $record) {
                // each vital record becomes a 1 -> many record for our observations
                $this->parseDataRecordsIntoObservationRecords($processingResult, $record, $observationCodesToReturn);
            }
        } catch (SearchFieldException $exception) {
            $processingResult->setValidationMessages([$exception->getField() => $exception->getMessage()]);
        }

        return $processingResult;
    }

    private function parseDataRecordsIntoObservationRecords(ProcessingResult $processingResult, $record)
    {
        $processingResult->addData($record);
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

        if (!empty($dataRecord['date_report'])) {
            $observation->setEffectiveDateTime(gmdate('c', strtotime($dataRecord['date'])));
        } else {
            $observation->setEffectiveDateTime(UtilsService::createDataMissingExtension());
        }

        $obsConcept = new FHIRCodeableConcept();
        $obsCategoryCoding = new FhirCoding();
        $obsCategoryCoding->setSystem(FhirCodeSystemUris::HL7_OBSERVATION_CATEGORY);
        $obsCategoryCoding->setCode(self::CATEGORY);
        $obsConcept->addCoding($obsCategoryCoding);
        $observation->addCategory($obsConcept);

        $categoryCoding = new FHIRCoding();
        $categoryCode = new FHIRCodeableConcept();
        if (!empty($dataRecord['result_code'])) {
            $categoryCoding->setCode($dataRecord['result_code']);
            $categoryCoding->setDisplay($dataRecord['result_text']);
            $categoryCoding->setSystem(FhirCodeSystemUris::LOINC);
            $categoryCode->addCoding($categoryCoding);
            $observation->setCode($categoryCode);
        }
        else
        {
            // TODO: @adunsulag need to set the data absent.
        }

        $status = $this->getValidStatus($dataRecord['result_status'] ?? 'unknown');
        $observation->setStatus($status);

        if (!empty($dataRecord['range'])) {
            $referenceRange = new FHIRObservationReferenceRange();
            if (isset($dataRecord['range_low']))
            {
                $referenceRange->setLow(UtilsService::createQuantity($dataRecord['low'], $dataRecord['units'], $dataRecord['units']));
            }
            else
            {
                $referenceRange->setHigh(UtilsService::createQuantity($dataRecord['high'], $dataRecord['units'], $dataRecord['units']));
            }

            $observation->addReferenceRange($referenceRange);
        }

        if (!empty($dataRecord['result'])) {
            if (is_numeric($dataRecord['result'])) {
                $quantity = new FHIRQuantity();
                $quantity->setValue($dataRecord['result']);
                if (!empty($dataRecord['units'])) {
                    $quantity->setUnit($dataRecord['units']);
                }
                $observation->setValueQuantity($quantity);
            } else {
                $observation->setValueString($dataRecord['result']);
            }
        }


        if (!empty($dataRecord['comments'])) {
            $observation->addNote(['text' => $dataRecord['comments']]);
        }

        $observation->setSubject(UtilsService::createRelativeReference("Patient", $dataRecord['puuid']));

        return $observation;
    }

    private function getValidStatus($status)
    {
        $statii = ['registered', 'preliminary', 'final', 'amended', 'corrected', 'cancelled', 'entered-in-error', 'unknown'];
        if (array_search($status, $statii) !== false)
        {
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
        $fhirProvenance = $fhirProvenanceService->createProvenanceForDomainResource($dataRecord);
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
