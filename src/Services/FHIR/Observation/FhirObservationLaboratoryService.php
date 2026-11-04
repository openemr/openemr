<?php

/**
 * FhirObservationLaboratoryService.php
 *
 * @package   openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Observation;

use InvalidArgumentException;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Utils\ValidationUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRObservation;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRProvenance;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRInstant;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\FHIR\R4\FHIRResource\FHIRObservation\FHIRObservationReferenceRange;
use OpenEMR\Services\EncounterService;
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
use OpenEMR\Services\FHIR\FhirDateTimeParser;
use OpenEMR\Services\FHIR\FhirPayloadReader;
use OpenEMR\Services\FHIR\FhirProvenanceService;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\FHIR\IPatientCompartmentResourceService;
use OpenEMR\Services\FHIR\IResourceUSCIGProfileService;
use OpenEMR\Services\FHIR\Observation\Trait\FhirObservationTrait;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\VersionedProfileTrait;
use OpenEMR\Services\FHIR\UtilsService;
use OpenEMR\Services\ProcedureResultService;
use OpenEMR\Services\ProcedureResultStatus;
use OpenEMR\Services\ProcedureService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use OpenEMR\Validators\ProcessingResult;

class FhirObservationLaboratoryService extends FhirServiceBase implements IPatientCompartmentResourceService, IResourceUSCIGProfileService
{
    use FhirServiceBaseEmptyTrait;
    use VersionedProfileTrait;
    use FhirObservationTrait;

    // we set this to be 'Final' which has the follow interpretation
    // 'The observation is complete and there are no further actions needed.'
    // @see http://hl7.org/fhir/R4/valueset-observation-status.html
    const DEFAULT_OBSERVATION_STATUS = "final";

    const CATEGORY = "laboratory";
    const USCDI_PROFILE = 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-observation-lab';

    /**
     * @var ProcedureService
     */
    private ProcedureService $service;

    private const COLUMN_MAPPINGS = [
    ];

    private readonly ProcedureResultService $resultService;

    public function __construct($fhirApiURL = null, ?ProcedureResultService $resultService = null)
    {
        parent::__construct($fhirApiURL);
        $this->resultService = $resultService ?? new ProcedureResultService();
    }

    public function getProcedureService(): ProcedureService
    {
        $this->service ??= new ProcedureService();
        return $this->service;
    }

    public function setProcedureService(ProcedureService $service): void
    {
        $this->service = $service;
    }

    public function getResourcePathForCode($code): string
    {
        return "category=" . self::CATEGORY . "&code=" . $code;
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
        // we support pretty much any LOINC code, we could hit procedure_order_code and procedure_results to be
        // specific but we'll just let the query execute.
        return true;
    }


    /**
     * Returns an array mapping FHIR Resource search parameters to OpenEMR search parameters
     */
    protected function loadSearchParameters(): array
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
     *
     * @param array<string, ISearchField> $openEMRSearchParameters OpenEMR search fields
     * @return ProcessingResult records
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        try {
            // if we have a category let's remove it as its being passed from our upper layer and we don't want to map
            // it to our procedure codes.
            unset($openEMRSearchParameters['category']);

            if (!empty($openEMRSearchParameters['result_code'])) {
                if ($openEMRSearchParameters['result_code'] instanceof TokenSearchField) {
                    /**
                     * @var TokenSearchField
                     */
                    $codeField = $openEMRSearchParameters['result_code'];
                    // transform the code values into something our procedure service can understand
                    $codeField->transformValues(function ($tokenValue) {
                        // we only support LOINC codes here so if we have a system of LOINC we can strip it out
                        // every other system will just not return a result
                        if ($tokenValue instanceof TokenSearchValue && $tokenValue->getSystem() == FhirCodeSystemConstants::LOINC) {
                            return new TokenSearchValue($tokenValue->getCode());
                        }
                        return $tokenValue;
                    });
                }
            }
//            $result = $this->service->search($newSearchParams, true);
            $result = $this->getProcedureService()->search($openEMRSearchParameters, true);
            $data = $result->getData() ?? [];

            // need to transform these into something we can consume
            foreach ($data as $record) {
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

                        // IMPORTANT: Specimen data comes from report as an ARRAY
                        // Each result gets a reference to ALL specimens for that test line
                        if (!empty($report['specimens'])) {
                            $result['specimens'] = $report['specimens'];
                        }
                        $ranges = array_filter(array_map(ValidationUtils::validateFloat(...), explode('-', $record['range'] ?? '')));
                        $result['range_low'] = $ranges[0] ?? null;
                        $result['range_high'] = $ranges[0] ?? null;
                        $result['result_abnormal'] = $result['abnormal'] ?? null;
                        $result['result_abnormal_title'] ??= null;
                        $result['result_abnormal_codes'] ??= null;
                        $result['provider'] = $record['provider'] ?? null;
                        $result['encounter'] = $record['encounter'] ?? null;
                        if (is_array($result)) {
                            $result['order_uuid'] = is_array($record) ? ($record['uuid'] ?? null) : null;
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
     * @param $encode     Indicates if the returned resource is encoded into a string. Defaults to True.
     */
    public function parseOpenEMRRecord($dataRecord = [], $encode = false): FHIRObservation
    {
        $observation = new FHIRObservation();
        $meta = new FHIRMeta();
        $meta->setVersionId('1');
        if (!empty($dataRecord['report_date'])) {
            $meta->setLastUpdated(new FHIRInstant(UtilsService::getLocalDateAsUTC($dataRecord['report_date'])));
        } else {
            $meta->setLastUpdated(new FHIRInstant(UtilsService::getDateFormattedAsUTC()));
        }
        // setup our profiles
        $meta = $this->addProfilesToMeta([
            self::USCDI_PROFILE
        ], $meta);
        $observation->setMeta($meta);

        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $observation->setId($id);

        if (!empty($dataRecord['report_date'])) {
            $observation->setEffectiveDateTime(new FHIRDateTime(UtilsService::getLocalDateAsUTC($dataRecord['report_date'])));
        } else {
            $observation->setEffectiveDateTime(UtilsService::createDataMissingExtension());
        }

        $obsCategoryCoding = UtilsService::createCodeableConcept([
            self::CATEGORY => [
                'code' => self::CATEGORY,
                'system' => FhirCodeSystemConstants::HL7_OBSERVATION_CATEGORY,
                'description' => "Laboratory"
            ]
        ]);
        $observation->addCategory($obsCategoryCoding);

        // ONC FHIR requirements require there is a text value for the code, otherwise the code is not reported.
        if (!empty($dataRecord['code']) && !empty($dataRecord['text'])) {
            $observation->setCode(UtilsService::createCodeableConcept([
                $dataRecord['code'] => [
                    'code' => $dataRecord['code'],
                    'system' => FhirCodeSystemConstants::LOINC,
                    'description' => $dataRecord['text']
                ]
            ]));
        } else {
            $observation->setCode(UtilsService::createNullFlavorUnknownCodeableConcept());
        }

        // Results are stored with the proc_res_status ids (prelim, correct, cancel, ...); a value
        // already spelled the FHIR way is passed through as before.
        $storedStatus = $dataRecord['status'] ?? null;
        $mappedStatus = is_string($storedStatus) ? ProcedureResultStatus::tryFrom($storedStatus) : null;
        $status = $mappedStatus?->fhirStatus() ?? $this->getValidStatus($storedStatus ?? 'unknown');
        $observation->setStatus($status);

        $storedRange = is_string($dataRecord['range'] ?? null) ? trim($dataRecord['range']) : '';
        $bounds = self::referenceRangeBounds($storedRange);
        if ($bounds !== null) {
            $referenceRange = new FHIRObservationReferenceRange();
            $referenceRange->setLow(UtilsService::createQuantity($bounds['low'], $dataRecord['units'], $dataRecord['units']));
            $referenceRange->setHigh(UtilsService::createQuantity($bounds['high'], $dataRecord['units'], $dataRecord['units']));
            $observation->addReferenceRange($referenceRange);
        } elseif ($storedRange !== '') {
            // A range that isn't two numbers ("negative", "<200") is served as its text rather than dropped.
            $referenceRange = new FHIRObservationReferenceRange();
            $referenceRange->setText(new FHIRString($storedRange));
            $observation->addReferenceRange($referenceRange);
        }

        $this->setObservationValue($observation, $dataRecord);

        if (!empty($dataRecord['provider']['uuid']) && !empty($dataRecord['provider']['npi'])) {
            $observation->addPerformer(UtilsService::createRelativeReference('Practitioner', $dataRecord['provider']['uuid']));
        }

        if (!empty($dataRecord['comments'])) {
            $observation->addNote(['text' => $dataRecord['comments']]);
        }

        if (!empty($dataRecord['patient'])) {
            $observation->setSubject(UtilsService::createRelativeReference("Patient", $dataRecord['patient']['uuid']));
        }

        if (!empty($dataRecord['encounter']['uuid'])) {
            $observation->setEncounter(UtilsService::createRelativeReference("Encounter", $dataRecord['encounter']['uuid']));
        }

        // The order the result was reported against, which a write names in basedOn.
        $orderUuid = $dataRecord['order_uuid'] ?? null;
        if (is_string($orderUuid) && $orderUuid !== '') {
            $observation->addBasedOn(UtilsService::createRelativeReference('ServiceRequest', $orderUuid));
        }

        // USCDI v5 Specimen - Handle MULTIPLE specimen references
        // Note: FHIR R4 Observation.specimen is 0..1 cardinality (single reference)
        // If multiple specimens exist, we have several options:
        // 1. Reference only the first specimen
        // 2. Create separate Observation resources for each specimen
        // 3. Use a contained resource or extension

        // Option 1: Reference the first specimen (most common approach)
        if (!empty($dataRecord['specimens']) && is_array($dataRecord['specimens'])) {
            // Reference the first specimen
            $firstSpecimen = $dataRecord['specimens'][0];
            if (!empty($firstSpecimen['uuid'])) {
                $observation->setSpecimen(
                    UtilsService::createRelativeReference('Specimen', $firstSpecimen['uuid'])
                );
            }

            // Optional: Add a note if there are multiple specimens
            if (count($dataRecord['specimens']) > 1) {
                $specimenNote = self::SPECIMEN_NOTE_PREFIX;
                $specimenIdentifiers = array_map(fn($spec): mixed => $spec['identifier'] ?? $spec['type'] ?? 'Unknown', $dataRecord['specimens']);
                $specimenNote .= implode(', ', $specimenIdentifiers);
                $observation->addNote(['text' => $specimenNote]);
            }
        } elseif (!empty($dataRecord['specimen']['uuid'])) {
            // Legacy support: single specimen object (backward compatibility)
            $observation->setSpecimen(
                UtilsService::createRelativeReference('Specimen', $dataRecord['specimen']['uuid'])
            );
        }

        // Interpretation (USCDI v5)
        if (!empty($dataRecord['result_abnormal'])) {
            // we will populate the text if we don't have a mapped code
            if (empty($dataRecord['result_abnormal_codes'])) {
                $interpretation = new FHIRCodeableConcept();
                $interpretation->setText($dataRecord['result_abnormal_title'] ?? $dataRecord['result_abnormal'] ?? 'Indeterminate');
            } else {
                $interpretation = UtilsService::createCodeableConcept([
                    $dataRecord['result_abnormal_codes'] => [
                        'code' => $dataRecord['result_abnormal_codes'],
                        'system' => FhirCodeSystemConstants::HL7_V3_OBSERVATION_INTERPRETATION,
                        'description' => $dataRecord['result_abnormal_title'] ?? null
                    ]
                ]);
            }
            $observation->addInterpretation($interpretation);
        }

        return $observation;
    }

    protected function setObservationValue(FHIRObservation $observation, array $dataRecord): void
    {
        $value = $dataRecord['result'] ?? null;
        // The shared value mapping tests the value with empty(), which is true for '0', so a
        // stored zero would be served as missing. '0.0' is the same quantity and is not empty.
        if ($value === '0') {
            $value = '0.0';
        }
        $valueUnit = $dataRecord['units'] ?? null;
        $codeDescription = str_contains((string) $value, ':') ? $this->getCodeTypesService()->lookup_code_description($value) : null;
        // if no sub_observations, or components, we treat as a single value observation
        $children = $dataRecord['sub_observations'] ?? $dataRecord['components'] ?? [];
        $this->setObservationValueWithDetails($observation, $value, $valueUnit, $codeDescription, $children);
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
     * @param      $dataRecord - The source OpenEMR data record
     * @param bool $encode     - Indicates if the returned resource is encoded into a string. Defaults to True.
     * @return false|FHIRProvenance|string|null - the FHIR Resource. Returned format is defined using $encode parameter.
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

    /**
     * How the read opens the note it adds when an order line has several specimens. A body read
     * back and PUT carries it, and it isn't the result's comment, so the write skips it.
     */
    private const SPECIMEN_NOTE_PREFIX = 'Multiple specimens collected: ';

    /**
     * Value markers the lab feed uses for "did not report" and "test not performed". The
     * lab read leaves results carrying them out (ProcedureService::search()), so one written
     * here would be accepted and never served.
     */
    private const UNSERVED_RESULT_MARKERS = ['DNR', 'TNP'];

    /**
     * The FHIR element each procedure_result column comes from, for naming it in a 400.
     *
     * @var array<string, string>
     */
    private const COLUMN_ELEMENTS = [
        'result_code' => 'code',
        'result_text' => 'code',
        'result' => 'value',
        'units' => 'valueQuantity',
        'range' => 'referenceRange',
        'abnormal' => 'interpretation',
        'comments' => 'note',
        'result_status' => 'status',
        'result_data_type' => 'value',
    ];

    /**
     * Parses a laboratory Observation into what a result write needs (#14343). Only the
     * payload is read here; references, list entries and code systems are resolved against
     * the database when the record is written.
     *
     * @return array<string, mixed>
     */
    public function parseFhirResource(FHIRDomainResource $fhirResource): array
    {
        if (!($fhirResource instanceof FHIRObservation)) {
            throw new InvalidArgumentException('Expected FHIRObservation resource, got ' . $fhirResource::class);
        }
        $json = $fhirResource->jsonSerialize();
        $data = [];

        // The lab read serves result_code as a LOINC code, so that is what a result is matched
        // to its order line by, from any position in the coding list.
        $codes = array_values(array_unique(array_map(
            trim(...),
            FhirPayloadReader::codingCodes($json['code'] ?? null, FhirCodeSystemConstants::LOINC)
        )));
        if ($codes === []) {
            return self::labValidationError(
                'code',
                'Observation.code has no coding from ' . FhirCodeSystemConstants::LOINC
                . '; laboratory results are identified by their LOINC code'
            );
        }
        $data['codes'] = $codes;
        $display = '';
        foreach (FhirPayloadReader::codings($json['code'] ?? null) as $coding) {
            if (FhirPayloadReader::getString($coding, 'system') === FhirCodeSystemConstants::LOINC) {
                $display = FhirPayloadReader::getString($coding, 'display') ?? '';
                break;
            }
        }
        $data['text'] = $display !== '' ? $display : (FhirPayloadReader::getString($json['code'] ?? null, 'text') ?? '');

        // As for vital signs: the router chose this service by category, and the payload has to
        // say so itself, since everything written here reads back as category laboratory.
        $categoryCodes = [];
        foreach (FhirPayloadReader::rows($json['category'] ?? null) as $concept) {
            array_push($categoryCodes, ...FhirPayloadReader::codingCodes($concept, FhirCodeSystemConstants::HL7_OBSERVATION_CATEGORY));
        }
        if (!in_array(self::CATEGORY, $categoryCodes, true)) {
            return self::labValidationError(
                'category',
                'Observation.category must include "' . self::CATEGORY . '" from '
                . FhirCodeSystemConstants::HL7_OBSERVATION_CATEGORY . ' to be written as a laboratory result'
            );
        }

        $subjectUuid = self::referencedUuid($json['subject'] ?? null, 'Patient');
        if ($subjectUuid === null) {
            return self::labValidationError('subject', 'Observation.subject must reference a Patient');
        }
        $data['puuid'] = $subjectUuid;

        // One order, never a guess between several: a result belongs to exactly one order line.
        $orderUuids = [];
        foreach (FhirPayloadReader::rows($json['basedOn'] ?? null) as $basedOn) {
            $orderUuids[] = self::referencedUuid($basedOn, 'ServiceRequest');
        }
        if (count($orderUuids) !== 1 || $orderUuids[0] === null) {
            return self::labValidationError(
                'basedOn',
                'Observation.basedOn must reference exactly one ServiceRequest: a laboratory result is written against its order'
            );
        }
        $data['order_uuid'] = $orderUuids[0];

        $data['euuid'] = null;
        if (isset($json['encounter'])) {
            $encounterUuid = self::referencedUuid($json['encounter'], 'Encounter');
            if ($encounterUuid === null) {
                return self::labValidationError('encounter', 'Observation.encounter must reference an Encounter');
            }
            $data['euuid'] = $encounterUuid;
        }

        $status = is_string($json['status'] ?? null) ? ProcedureResultStatus::fromFhir($json['status']) : null;
        if ($status === null) {
            return self::labValidationError(
                'status',
                'Observation.status must be final, preliminary, corrected, cancelled or entered-in-error'
            );
        }
        $data['status'] = $status->value;

        foreach (['effectiveDateTime' => 'effective', 'issued' => 'issued'] as $element => $key) {
            try {
                $data[$key] = FhirDateTimeParser::toDbDateTime($json[$element] ?? null, 'Observation.' . $element);
            } catch (InvalidArgumentException) {
                return self::labValidationError(
                    $element,
                    'Observation.' . $element . ' must be a real date and time with a timezone offset (FHIR dateTime, at least a full date)'
                );
            }
        }

        foreach (['hasMember', 'component'] as $panelElement) {
            if (FhirPayloadReader::rows($json[$panelElement] ?? null) !== []) {
                return self::labValidationError(
                    $panelElement,
                    'Observation.' . $panelElement . ' is not written: one Observation is one result; post each result of a panel on its own'
                );
            }
        }

        $value = self::parseValue($json);
        if (isset($value['__validation_error__'])) {
            return $value;
        }
        $data['value'] = $value;

        $range = self::parseReferenceRange($json['referenceRange'] ?? null);
        if (is_array($range)) {
            return $range;
        }
        $data['range'] = $range;

        $interpretations = FhirPayloadReader::rows($json['interpretation'] ?? null);
        if (count($interpretations) > 1) {
            return self::labValidationError('interpretation', 'Observation.interpretation can hold one flag; OpenEMR stores one per result');
        }
        $data['interpretation'] = $interpretations[0] ?? null;

        $notes = [];
        foreach (FhirPayloadReader::rows($json['note'] ?? null) as $note) {
            $text = FhirPayloadReader::getString($note, 'text');
            if ($text !== null && !str_starts_with($text, self::SPECIMEN_NOTE_PREFIX)) {
                $notes[] = $text;
            }
        }
        if (count($notes) > 1) {
            return self::labValidationError('note', 'Observation.note can hold one comment; OpenEMR stores one per result');
        }
        $data['comments'] = $notes[0] ?? '';

        // performer and specimen are accepted and not stored: the read serves both from the
        // order (its provider, its line's specimens), so a body read back and PUT still writes.
        return $data;
    }

    /**
     * @param mixed $openEmrRecord
     */
    protected function insertOpenEMRRecord($openEmrRecord): ProcessingResult
    {
        if (!is_array($openEmrRecord)) {
            throw new InvalidArgumentException('Expected a parsed laboratory result record array');
        }
        $failure = self::labValidationResult($openEmrRecord);
        if ($failure !== null) {
            return $failure;
        }
        $effective = $openEmrRecord['effective'] ?? null;
        if (!is_string($effective)) {
            return self::validationFailure(
                'effectiveDateTime',
                'Observation.effectiveDateTime is required; it is the time the result applies to'
            );
        }

        $context = $this->resolveWriteContext($openEmrRecord);
        if ($context instanceof ProcessingResult) {
            return $context;
        }
        $columns = $this->resolveResultColumns($openEmrRecord);
        if ($columns instanceof ProcessingResult) {
            return $columns;
        }

        $issued = $openEmrRecord['issued'] ?? null;
        $text = $openEmrRecord['text'] ?? '';
        $saved = $this->resultService->insertResult(
            $context['order_id'],
            $context['pid'],
            $context['encounter'],
            $context['codes'],
            $columns + ['result_code' => '', 'result_text' => is_string($text) ? $text : '', 'date' => $effective],
            // The report date is what the lab read serves as effectiveDateTime, so without an
            // issued time the report is dated by the result.
            is_string($issued) ? $issued : $effective,
            $this->sessionUserId(),
        );
        if (!$saved->isValid()) {
            return self::withElementNames($saved);
        }
        $rows = $saved->getData();
        $row = is_array($rows) ? ($rows[0] ?? null) : null;
        $uuid = is_array($row) ? ($row['uuid'] ?? null) : null;
        if (!is_string($uuid)) {
            $result = new ProcessingResult();
            $result->setInternalErrors(['The laboratory result was saved without a uuid']);
            return $result;
        }

        $result = new ProcessingResult();
        $result->addData(['uuid' => $uuid]);
        return $result;
    }

    /**
     * @param string $fhirResourceId
     * @param mixed $updatedOpenEMRRecord
     */
    protected function updateOpenEMRRecord($fhirResourceId, $updatedOpenEMRRecord): ProcessingResult
    {
        if (!is_array($updatedOpenEMRRecord)) {
            throw new InvalidArgumentException('Expected a parsed laboratory result record array');
        }
        $failure = self::labValidationResult($updatedOpenEMRRecord);
        if ($failure !== null) {
            return $failure;
        }

        $context = $this->resolveWriteContext($updatedOpenEMRRecord);
        if ($context instanceof ProcessingResult) {
            return $context;
        }
        $columns = $this->resolveResultColumns($updatedOpenEMRRecord);
        if ($columns instanceof ProcessingResult) {
            return $columns;
        }

        $effective = $updatedOpenEMRRecord['effective'] ?? null;
        $issued = $updatedOpenEMRRecord['issued'] ?? null;
        $saved = $this->resultService->updateResult(
            $fhirResourceId,
            $context['order_id'],
            $context['pid'],
            $context['encounter'],
            $context['codes'],
            is_string($effective) ? $effective : null,
            is_string($issued) ? $issued : null,
            $columns,
        );
        if (!$saved->isValid()) {
            return self::withElementNames($saved);
        }

        // FhirServiceBase::update() turns the first row into the response body, so it has to be
        // the record the read path builds.
        $readBack = $this->searchForOpenEMRRecords([
            'result_uuid' => new TokenSearchField('result_uuid', [$fhirResourceId], true),
        ]);
        if (!$readBack->isValid() || $readBack->getData() === []) {
            $result = new ProcessingResult();
            $result->setInternalErrors(['The laboratory result could not be read back after the update']);
            return $result;
        }
        return $readBack;
    }

    /**
     * Resolves the patient, the order and the encounter a parsed record names.
     *
     * @param array<array-key, mixed> $record
     * @return array{pid: int, order_id: int, encounter: ?int, codes: list<string>}|ProcessingResult
     */
    private function resolveWriteContext(array $record): array|ProcessingResult
    {
        $puuid = is_string($record['puuid'] ?? null) ? $record['puuid'] : '';
        $pid = QueryUtils::fetchSingleValue(
            'SELECT `pid` FROM `patient_data` WHERE `uuid` = ?',
            'pid',
            [UuidRegistry::uuidToBytes($puuid)]
        );
        $pid = is_int($pid) || (is_string($pid) && ctype_digit($pid)) ? (int) $pid : null;
        if ($pid === null) {
            return self::validationFailure('subject', 'Patient reference could not be resolved: ' . $puuid);
        }

        $orderUuid = is_string($record['order_uuid'] ?? null) ? $record['order_uuid'] : '';
        $orderId = QueryUtils::fetchSingleValue(
            'SELECT `procedure_order_id` FROM `procedure_order` WHERE `uuid` = ?',
            'procedure_order_id',
            [UuidRegistry::uuidToBytes($orderUuid)]
        );
        $orderId = is_int($orderId) || (is_string($orderId) && ctype_digit($orderId)) ? (int) $orderId : null;
        if ($orderId === null) {
            return self::validationFailure(
                'basedOn',
                'Observation.basedOn must reference one active ServiceRequest (procedure order) for the patient in Observation.subject'
            );
        }

        $encounter = null;
        $euuid = $record['euuid'] ?? null;
        if (is_string($euuid)) {
            $resolved = EncounterService::getActiveEncounterByUuid($euuid);
            if ($resolved === null) {
                return self::validationFailure('encounter', 'Encounter reference could not be resolved: ' . $euuid);
            }
            $encounter = $resolved['encounter'];
        }

        $codes = [];
        foreach (is_array($record['codes'] ?? null) ? $record['codes'] : [] as $code) {
            if (is_string($code)) {
                $codes[] = $code;
            }
        }

        return ['pid' => $pid, 'order_id' => $orderId, 'encounter' => $encounter, 'codes' => $codes];
    }

    /**
     * Turns the parsed value, range, interpretation, status and note into procedure_result
     * columns, resolving what needs the database: the abnormal flag from the proc_res_abnormal
     * list, and a coded value's code system.
     *
     * @param array<array-key, mixed> $record
     * @return array{result_data_type: string, units: string, result: string, range: string, abnormal: string, comments: string, result_status: string}|ProcessingResult
     */
    private function resolveResultColumns(array $record): array|ProcessingResult
    {
        $value = is_array($record['value'] ?? null) ? $record['value'] : [];
        $result = is_string($value['result'] ?? null) ? $value['result'] : '';
        if (($value['type'] ?? null) === 'CodeableConcept') {
            $result = $this->storedCodedValue(
                is_string($value['system'] ?? null) ? $value['system'] : '',
                $result
            );
            if ($result === null) {
                return self::validationFailure(
                    'valueCodeableConcept',
                    'Observation.valueCodeableConcept must carry a coding from a code system installed in OpenEMR, '
                    . 'with a description there; otherwise the result would read back as text. Send valueString instead'
                );
            }
        }

        $abnormal = '';
        $interpretation = $record['interpretation'] ?? null;
        if (is_array($interpretation)) {
            $abnormal = $this->abnormalOptionFor($interpretation);
            if ($abnormal === null) {
                return self::validationFailure(
                    'interpretation',
                    'Observation.interpretation must be a flag in the proc_res_abnormal list (by its code from '
                    . FhirCodeSystemConstants::HL7_V3_OBSERVATION_INTERPRETATION . ', or its title as text)'
                );
            }
        }

        return [
            'result_data_type' => ($value['type'] ?? null) === 'Quantity' ? 'N' : 'S',
            'units' => is_string($value['units'] ?? null) ? $value['units'] : '',
            'result' => $result,
            'range' => is_string($record['range'] ?? null) ? $record['range'] : '',
            'abnormal' => $abnormal,
            'comments' => is_string($record['comments'] ?? null) ? $record['comments'] : '',
            'result_status' => is_string($record['status'] ?? null) ? $record['status'] : '',
        ];
    }

    /**
     * The proc_res_abnormal option for an interpretation, matched in PHP by its code (the
     * column the read serves as the coding) or else by its title (served as text when the
     * option has no code).
     *
     * @param array<array-key, mixed> $interpretation
     */
    private function abnormalOptionFor(array $interpretation): ?string
    {
        $options = QueryUtils::fetchRecords(
            "SELECT `option_id`, `title`, `codes` FROM `list_options` WHERE `list_id` = 'proc_res_abnormal' AND `activity` = 1"
        );
        $codes = FhirPayloadReader::codingCodes($interpretation, FhirCodeSystemConstants::HL7_V3_OBSERVATION_INTERPRETATION);
        $text = FhirPayloadReader::getString($interpretation, 'text');
        foreach ([true, false] as $byCode) {
            foreach ($options as $option) {
                $optionId = $option['option_id'] ?? null;
                if (!is_string($optionId) || $optionId === '') {
                    continue;
                }
                $optionCode = is_string($option['codes'] ?? null) ? trim($option['codes']) : '';
                if ($byCode && $optionCode !== '' && in_array($optionCode, $codes, true)) {
                    return $optionId;
                }
                if (!$byCode && $text !== null && ($option['title'] ?? null) === $text) {
                    return $optionId;
                }
            }
        }
        return null;
    }

    /**
     * The TYPE:code a coded value is stored as, or null when the read would not serve it back
     * as that coding: the lab read turns a stored value into a coding only when the code type
     * maps back to the same system and the code has a description in OpenEMR's code tables.
     */
    private function storedCodedValue(string $system, string $code): ?string
    {
        $codeTypes = $this->getCodeTypesService();
        $codeType = $codeTypes->getCodeTypeForSystemUrl($system);
        if ($codeType === null || $code === '' || str_contains($code, ':')) {
            return null;
        }
        if ($codeTypes->getSystemForCodeType($codeType) !== $system) {
            return null;
        }
        $stored = $codeType . ':' . $code;
        return $codeTypes->lookup_code_description($stored) !== '' ? $stored : null;
    }

    /**
     * Reads value[x] into the type, text and units it is stored as.
     *
     * @param array<array-key, mixed> $json
     * @return array<string, mixed>
     */
    private static function parseValue(array $json): array
    {
        if (isset($json['valueQuantity'])) {
            $quantity = FhirPayloadReader::stringKeyed($json['valueQuantity']);
            $number = $quantity['value'] ?? null;
            $result = is_int($number) || is_float($number) ? self::decimalString($number) : null;
            if ($result === null) {
                return self::labValidationError('valueQuantity', 'Observation.valueQuantity.value must be a number');
            }
            if (isset($quantity['comparator'])) {
                // The read serves a stored number as a plain quantity, so "<0.5" would come back as 0.5.
                return self::labValidationError(
                    'valueQuantity',
                    'Observation.valueQuantity.comparator is not stored; send a result such as "<0.5" as valueString'
                );
            }
            return [
                'type' => 'Quantity',
                'result' => $result,
                // Quantity.code is the UCUM symbol the read serves back as both code and unit.
                'units' => FhirPayloadReader::getString($quantity, 'code') ?? FhirPayloadReader::getString($quantity, 'unit') ?? '',
            ];
        }
        if (isset($json['valueString'])) {
            $text = is_string($json['valueString']) ? $json['valueString'] : '';
            if (trim($text) === '') {
                return self::labValidationError('valueString', 'Observation.valueString must not be empty');
            }
            // The read serves a stored number as a quantity, so a number sent as text would change type.
            if (is_numeric($text)) {
                return self::labValidationError('valueString', 'Observation.valueString "' . $text . '" is a number; send it as valueQuantity');
            }
            // Compared the way the read's filter compares them: case and trailing spaces folded.
            if (in_array(strtoupper(rtrim($text)), self::UNSERVED_RESULT_MARKERS, true)) {
                return self::labValidationError(
                    'valueString',
                    'Observation.valueString "' . $text . '" is a lab marker for a result that was not reported, '
                    . 'which the laboratory read does not serve; send status cancelled with a value saying why'
                );
            }
            return ['type' => 'String', 'result' => $text, 'units' => ''];
        }
        if (isset($json['valueCodeableConcept'])) {
            foreach (FhirPayloadReader::codings($json['valueCodeableConcept']) as $coding) {
                $system = FhirPayloadReader::getString($coding, 'system');
                $code = FhirPayloadReader::getString($coding, 'code');
                if ($system !== null && $code !== null) {
                    return ['type' => 'CodeableConcept', 'result' => trim($code), 'system' => $system, 'units' => ''];
                }
            }
            return self::labValidationError('valueCodeableConcept', 'Observation.valueCodeableConcept must carry a coding with a system and a code');
        }
        foreach (array_keys($json) as $key) {
            if (is_string($key) && str_starts_with($key, 'value')) {
                return self::labValidationError(
                    $key,
                    'Observation.' . $key . ' is not stored for laboratory results; send valueQuantity, valueString or valueCodeableConcept'
                );
            }
        }
        return self::labValidationError('value', 'Observation.value[x] is required: a laboratory result is written with its value');
    }

    /**
     * Reads referenceRange into the procedure_result.range text: "low-high" when both bounds
     * are numbers, which the read serves back as low and high, or the range's text.
     *
     * @return string|array<string, mixed> The range text, or a validation marker.
     */
    private static function parseReferenceRange(mixed $referenceRange): string|array
    {
        $ranges = FhirPayloadReader::rows($referenceRange);
        if ($ranges === []) {
            return '';
        }
        if (count($ranges) > 1) {
            return self::labValidationError('referenceRange', 'Observation.referenceRange can hold one range; OpenEMR stores one per result');
        }
        $low = FhirPayloadReader::get(FhirPayloadReader::get($ranges[0], 'low'), 'value');
        $high = FhirPayloadReader::get(FhirPayloadReader::get($ranges[0], 'high'), 'value');
        $lowText = is_int($low) || is_float($low) ? self::decimalString($low) : null;
        $highText = is_int($high) || is_float($high) ? self::decimalString($high) : null;
        if ($lowText !== null && $highText !== null) {
            return $lowText . '-' . $highText;
        }
        if ($low !== null || $high !== null) {
            return self::labValidationError(
                'referenceRange',
                'Observation.referenceRange needs both low and high as numbers, or text; OpenEMR stores a range as "low-high" or as text'
            );
        }
        return FhirPayloadReader::getString($ranges[0], 'text') ?? '';
    }

    /**
     * The low and high of a stored "low-high" range. Either bound may be zero or negative.
     *
     * @return ?array{low: float, high: float}
     */
    public static function referenceRangeBounds(string $range): ?array
    {
        $number = '-?(?:\d+(?:\.\d*)?|\.\d+)';
        if (preg_match('/^\s*(' . $number . ')\s*-\s*(' . $number . ')\s*$/', $range, $matches) !== 1) {
            return null;
        }
        return ['low' => (float) $matches[1], 'high' => (float) $matches[2]];
    }

    /**
     * A number as plain decimal text, to 15 significant digits: what a double holds exactly,
     * without the exponent form PHP gives very small or very large floats ("1.0E-7").
     */
    public static function decimalString(int|float $number): ?string
    {
        if (is_int($number)) {
            return (string) $number;
        }
        if (!is_finite($number)) {
            return null;
        }
        if ($number == 0.0) {
            return '0';
        }
        $decimals = max(0, min(30, 14 - (int) floor(log10(abs($number)))));
        $text = number_format($number, $decimals, '.', '');
        if (str_contains($text, '.')) {
            $text = rtrim(rtrim($text, '0'), '.');
        }
        return $text === '-0' ? '0' : $text;
    }

    private static function referencedUuid(mixed $reference, string $type): ?string
    {
        $literal = FhirPayloadReader::reference($reference);
        $uuid = $literal === null ? null : (UtilsService::parseReferenceString($literal, $type)['uuid'] ?? null);
        return is_string($uuid) && UuidRegistry::isValidStringUUID($uuid) ? $uuid : null;
    }

    private function sessionUserId(): int
    {
        $id = $this->getSession()?->get('authUserID');
        return is_int($id) ? $id : (is_string($id) && ctype_digit($id) ? (int) $id : 0);
    }

    /**
     * @return array<string, string>
     */
    private static function labValidationError(string $field, string $message): array
    {
        return ['__validation_error__' => $message, '__validation_field__' => $field];
    }

    /**
     * @param array<array-key, mixed> $record
     */
    private static function labValidationResult(array $record): ?ProcessingResult
    {
        $message = $record['__validation_error__'] ?? null;
        if (!is_string($message)) {
            return null;
        }
        $field = $record['__validation_field__'] ?? null;
        return self::validationFailure(is_string($field) ? $field : 'code', $message);
    }

    private static function validationFailure(string $field, string $message): ProcessingResult
    {
        $result = new ProcessingResult();
        $result->setValidationMessages([$field => $message]);
        return $result;
    }

    /**
     * Renames the result service's column-keyed messages after the FHIR elements they came from.
     */
    private static function withElementNames(ProcessingResult $saved): ProcessingResult
    {
        if ($saved->hasInternalErrors()) {
            return $saved;
        }
        $messages = [];
        $savedMessages = $saved->getValidationMessages();
        foreach (is_array($savedMessages) ? $savedMessages : [] as $field => $message) {
            $field = (string) $field;
            $element = self::COLUMN_ELEMENTS[$field] ?? $field;
            $messages[$element] = is_string($message) ? $message : 'is not valid';
        }
        $result = new ProcessingResult();
        $result->setValidationMessages($messages);
        return $result;
    }

    public function getPatientContextSearchField(): FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('patient', SearchFieldType::REFERENCE, [new ServiceField('puuid', ServiceField::TYPE_UUID)]);
    }

    public function getProfileURIs(): array
    {
        return $this->getProfileForVersions(self::USCDI_PROFILE, $this->getSupportedVersions());
    }
}
