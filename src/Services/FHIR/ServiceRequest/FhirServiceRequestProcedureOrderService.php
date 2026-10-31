<?php

/**
 * FhirServiceRequestProcedureOrderService.php: ServiceRequests from procedure_order
 * (laboratory, imaging, clinical test and procedure orders), one of the services
 * FhirServiceRequestService maps to by category.
 *
 * @package   openemr
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025-2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\FHIR\ServiceRequest;

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRServiceRequest;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRInstant;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRElement\FHIRRequestIntent;
use OpenEMR\FHIR\R4\FHIRElement\FHIRRequestPriority;
use OpenEMR\FHIR\R4\FHIRElement\FHIRRequestStatus;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\EncounterService;
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
use OpenEMR\Services\FHIR\FhirDateTimeParser;
use OpenEMR\Services\FHIR\FhirPayloadReader;
use OpenEMR\Services\FHIR\FhirProvenanceService;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\FHIR\IPatientCompartmentResourceService;
use OpenEMR\Services\FHIR\IResourceUSCIGProfileService;
use OpenEMR\Services\FHIR\PractitionerAttributionPolicy;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\PatientSearchTrait;
use OpenEMR\Services\FHIR\UtilsService;
use OpenEMR\Services\ProcedureOrderRelationshipService;
use OpenEMR\Services\ProcedureService;
use OpenEMR\Services\Search\CompositeSearchField;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Services\Search\StringSearchField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use OpenEMR\Validators\ProcessingResult;

/**
 * FHIR ServiceRequest Service for ONC 2025 USCDI v5 / US Core 8.0
 * Handles Laboratory Orders, Diagnostic Imaging Orders, Clinical Test Orders, and Procedure Orders
 *
 * US Core 8.0 Profile: http://hl7.org/fhir/us/core/StructureDefinition/us-core-servicerequest
 *
 * Must Support Elements:
 * - status, intent, category, code, subject, occurrence[x]/authoredOn, requester
 * - priority, patientInstruction, performer
 *
 * SHALL Support Search Parameters:
 * - patient (required)
 * - patient + category (required combination)
 * - patient + code (required combination)
 * - patient + status (required combination)
 * - patient + authored (required combination)
 *
 * Database Schema:
 * - procedure_order: main order table
 * - procedure_order_code: individual tests/procedures within order (one-to-many)
 */
class FhirServiceRequestProcedureOrderService extends FhirServiceBase implements
    IResourceUSCIGProfileService,
    IPatientCompartmentResourceService
{
    use FhirServiceBaseEmptyTrait;
    use PatientSearchTrait;

    /**
     * USCDI v5 Order Category Codes (SNOMED CT)
     */
    const CATEGORY_LABORATORY = "108252007"; // Laboratory procedure
    const CATEGORY_IMAGING = "363679005"; // Imaging procedure
    const CATEGORY_CLINICAL_TEST = "103693007"; // Diagnostic procedure
    const CATEGORY_PROCEDURE = "387713003"; // Surgical procedure

    const CATEGORY_MAP = [
            self::ORDER_TYPE_LABORATORY => [
                'code' => self::CATEGORY_LABORATORY,
                'description' => 'Laboratory procedure',
                'system' => self::CATEGORY_SYSTEM_SNOMED
            ],
            self::ORDER_TYPE_IMAGING => [
                'code' => self::CATEGORY_IMAGING,
                'description' => 'Imaging',
                'system' => self::CATEGORY_SYSTEM_SNOMED
            ],
            self::ORDER_TYPE_CLINICAL_TEST => [
                'code' => self::CATEGORY_CLINICAL_TEST,
                'description' => 'Diagnostic procedure',
                'system' => self::CATEGORY_SYSTEM_SNOMED
            ],
            self::ORDER_TYPE_PROCEDURE => [
                'code' => self::CATEGORY_PROCEDURE,
                'description' => 'Surgical procedure',
                'system' => self::CATEGORY_SYSTEM_SNOMED
            ],
        ];

    /**
     * Code Systems
     */
    const CATEGORY_SYSTEM_SNOMED = FhirCodeSystemConstants::SNOMED_CT;
    const REQUEST_PRIORITY_SYSTEM = "http://hl7.org/fhir/request-priority";

    /**
     * OpenEMR procedure_order_type values from procedure_order table
     */
    const ORDER_TYPE_LABORATORY = "laboratory_test";
    const ORDER_TYPE_IMAGING = "imaging";
    const ORDER_TYPE_CLINICAL_TEST = "clinical_test";
    const ORDER_TYPE_PROCEDURE = "procedure";

    private const PROFILE_URI = 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-servicerequest';

    private readonly ProcedureService $procedureService;

    private readonly ProcedureOrderRelationshipService $relationshipService;

    /**
     * Enable strict US Core validation (throws errors on missing required fields)
     */
    private bool $strictValidation = false;

    public function __construct()
    {
        parent::__construct();
        $this->procedureService = new ProcedureService();
        $this->relationshipService = new ProcedureOrderRelationshipService();
    }

    /**
     * The order categories procedure_order_type maps to (CATEGORY_MAP). The referral category is
     * FhirServiceRequestReferralService's.
     */
    public function supportsCategory(string $category): bool
    {
        foreach (self::CATEGORY_MAP as $mapped) {
            if ($mapped['code'] === $category) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns an array mapping FHIR ServiceRequest search parameters to OpenEMR search parameters
     *
     * US Core 8.0 SHALL support:
     * - patient
     * - patient + category
     * - patient + code
     * - patient + status
     * - patient + authored
     */
    protected function loadSearchParameters(): array
    {
        $return = [
            'patient' => $this->getPatientContextSearchField(),
            'category' => new FhirSearchParameterDefinition('category', SearchFieldType::TOKEN, ['procedure_order_type']),
            'code' => new FhirSearchParameterDefinition('code', SearchFieldType::TOKEN, ['procedure_code']),
            'authored' => new FhirSearchParameterDefinition('authored', SearchFieldType::DATETIME, ['date_ordered']),
            'status' => new FhirSearchParameterDefinition('status', SearchFieldType::TOKEN, ['order_status', 'order_activity']),
            'intent' => new FhirSearchParameterDefinition('intent', SearchFieldType::TOKEN, ['order_intent']),
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('order_uuid', ServiceField::TYPE_UUID)]),
            '_lastUpdated' => $this->getLastModifiedSearchField(),
        ];
        return $return;
    }

    public function getLastModifiedSearchField(): FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['date_ordered']);
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     * @param array<mixed> $openEMRSearchParameters OpenEMR search fields
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        $openEMRSearchParameters = self::searchFields($openEMRSearchParameters);
        if (isset($openEMRSearchParameters['procedure_order_type']) && $openEMRSearchParameters['procedure_order_type'] instanceof TokenSearchField) {
            $codes = $openEMRSearchParameters['procedure_order_type']->getValues();
            $modifier = $openEMRSearchParameters['procedure_order_type']->getModifier();
            $newCodeValues = [];
            foreach ($codes as $code) {
                foreach (self::CATEGORY_MAP as $orderType => $category) {
                    if ($code instanceof TokenSearchValue) {
                        if (
                            $code->getCode() === $category['code'] &&
                            (in_array($code->getSystem(), [null, ''], true) || $code->getSystem() === $category['system'])
                        ) {
                            // we don't pass in system as its a local codetype mapping internally
                            $newCodeValues[] = new TokenSearchValue($orderType);
                        }
                    }
                }
            }
            if ($newCodeValues !== []) {
                $openEMRSearchParameters['procedure_order_type'] = new TokenSearchField(
                    'procedure_order_type',
                    $newCodeValues
                );
                $openEMRSearchParameters['procedure_order_type']->setModifier($modifier);
            } else {
                // No matching order types found for category codes, return empty result
                $processingResult = new ProcessingResult();
                $processingResult->setData([]);
                return $processingResult;
            }
        }

        // until we figure out a better way to do code searches we need to handle code searches across different codetypes
        // the procedure_code value can be a LOINC code, SNOMED code, or local code depending on the test/procedure ordered.
        if (isset($openEMRSearchParameters['procedure_code']) && $openEMRSearchParameters['procedure_code'] instanceof TokenSearchField) {
            $compositeSearch = new CompositeSearchField('procedure_code', [], false);
            $codeValues = $openEMRSearchParameters['procedure_code']->getValues();
            $newCodeValues = [];
            $stringCodeValues = [];
            $exactStringCodeValues = [];
            foreach ($codeValues as $codeValue) {
                if ($codeValue instanceof TokenSearchValue) {
                    if (self::nonEmpty($codeValue->getSystem()) !== null) {
                        $newCodeValues[] = $codeValue;
                    } else {
                        $stringCodeValues[] = ":" . $codeValue->getCode();
                        $exactStringCodeValues[] = $codeValue->getCode();
                    }
                }
            }
            if ($newCodeValues !== []) {
                $compositeSearch->addChild(new TokenSearchField('procedure_code', $newCodeValues));
            }
            // note this will only work if we have single code usage of codetype:value multiple code usage
            // of codetype:value1;codetype:value2 won't work here
            if ($stringCodeValues !== []) {
                $compositeSearch->addChild(new StringSearchField('procedure_code', $stringCodeValues, SearchModifier::SUFFIX, isLiteral: true));
                // also add exact match for codes without codetype prefix in case someone is
                $compositeSearch->addChild(new StringSearchField('procedure_code', $exactStringCodeValues, SearchModifier::EXACT));
            }
            if ($compositeSearch->getChildren() !== []) {
                $openEMRSearchParameters['procedure_code'] = $compositeSearch;
            }
        }
        // Query procedure_order with joined procedure_order_code data
        return $this->procedureService->search($openEMRSearchParameters);
    }

    /**
     * Validate that data record meets US Core 8.0 requirements
     *
     * @param array<mixed> $dataRecord The source data
     * @return array{errors: list<string>, warnings: list<string>}
     */
    private function validateUSCoreRequirements(array $dataRecord): array
    {
        $errors = [];
        $warnings = [];

        // Required: category
        if (self::field($dataRecord, 'procedure_order_type') === null) {
            $errors[] = "procedure_order_type is required for ServiceRequest.category (USCDI v5 requirement)";
        }

        // Required: code
        if (self::field($dataRecord, 'procedure_code') === null && self::field($dataRecord, 'procedure_name') === null) {
            $errors[] = "procedure_code or procedure_name required for ServiceRequest.code";
        }

        // Required: subject (patient)
        if (self::linkedUuid($dataRecord, 'patient_id', 'patient') === null) {
            $errors[] = "patient_id and patient.uuid are required for ServiceRequest.subject";
        }

        // Required: occurrence[x] OR authoredOn (at least one must be present)
        $hasOccurrence = self::field($dataRecord, 'date_collected') !== null ||
            self::field($dataRecord, 'scheduled_date') !== null ||
            (self::field($dataRecord, 'scheduled_start') !== null && self::field($dataRecord, 'scheduled_end') !== null);
        $hasAuthored = self::field($dataRecord, 'date_ordered') !== null;

        if (!$hasOccurrence && !$hasAuthored) {
            $warnings[] = "Either occurrence date (date_collected/scheduled_date/scheduled_start+end) or authoredOn (date_ordered) is recommended";
        }

        // Must Support: requester
        if (self::linkedUuid($dataRecord, 'provider_id', 'provider') === null) {
            $warnings[] = "provider_id and provider.uuid recommended for ServiceRequest.requester (Must Support element)";
        }

        return [
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }

    /**
     * Parses an OpenEMR procedure_order record into a FHIR ServiceRequest resource. Always the
     * resource itself, as FhirServiceBase declares: no caller asks for it encoded.
     *
     * @param array<mixed> $dataRecord The source OpenEMR data record from procedure_order + procedure_order_code
     * @param bool  $encode     Not used (see above)
     */
    public function parseOpenEMRRecord($dataRecord = [], $encode = false): FHIRServiceRequest
    {
        // Validate US Core 8.0 requirements
        $validation = $this->validateUSCoreRequirements($dataRecord);

        // Log validation issues
        if ($validation['errors'] !== []) {
            $errorMsg = "US Core 8.0 validation errors: " . implode("; ", $validation['errors']);
            ServiceContainer::getLogger()->error($errorMsg, ['dataRecord_keys' => array_keys($dataRecord)]);

            // In strict mode, throw exception
            if ($this->strictValidation) {
                throw new \RuntimeException($errorMsg);
            }
        }

        if ($validation['warnings'] !== []) {
            $warningMsg = "US Core 8.0 validation warnings: " . implode("; ", $validation['warnings']);
            ServiceContainer::getLogger()->debug($warningMsg);
        }

        $serviceRequest = new FHIRServiceRequest();

        // Meta - US Core 8.0 profile
        $meta = new FHIRMeta();
        $meta->setVersionId(new FHIRId('1'));
        $meta->addProfile(new FHIRCanonical(self::PROFILE_URI));

        $dateOrdered = self::field($dataRecord, 'date_ordered');
        $meta->setLastUpdated(new FHIRInstant($dateOrdered !== null ? self::utc($dateOrdered) : UtilsService::getDateFormattedAsUTC()));
        $serviceRequest->setMeta($meta);

        // ID - use procedure_order.uuid (aliased as order_uuid in query)
        $id = new FHIRId();
        $orderUuid = $dataRecord['order_uuid'] ?? null;
        if (is_string($orderUuid)) {
            $id->setValue($orderUuid);
        }
        $serviceRequest->setId($id);

        // Status - REQUIRED - map from order_status and activity fields (activity 0 marks a deleted order)
        $orderStatus = $dataRecord['order_status'] ?? '';
        $activity = $dataRecord['activity'] ?? 1;
        $status = $this->mapOrderStatus(is_string($orderStatus) ? $orderStatus : '', $activity === 0 || $activity === '0');
        $serviceRequest->setStatus(new FHIRRequestStatus(['value' => $status]));

        // Intent - REQUIRED - typically 'order' for placed orders
        $intent = $dataRecord['order_intent'] ?? 'order';
        $serviceRequest->setIntent(new FHIRRequestIntent(['value' => is_string($intent) ? $intent : 'order']));

        // Category - REQUIRED - USCDI v5 requirement from procedure_order_type; a missing or
        // unknown type maps to the default category
        $orderType = self::field($dataRecord, 'procedure_order_type');
        $serviceRequest->addCategory($this->mapOrderTypeToCategory($orderType ?? ''));

        // Code - REQUIRED - from procedure_order_code table
        $serviceRequest->setCode($this->buildOrderCode($dataRecord));

        // Subject (patient) - REQUIRED, falling back to puuid if the patient array isn't populated
        $patientUuid = self::linkedUuid($dataRecord, 'patient_id', 'patient') ?? self::field($dataRecord, 'puuid');
        if ($patientUuid !== null) {
            $serviceRequest->setSubject(UtilsService::createRelativeReference('Patient', $patientUuid));
        } else {
            // Last resort: a reference carrying only the data-absent-reason extension (not ideal
            // for US Core but prevents crash)
            $missingSubject = new FHIRReference();
            $missingSubject->addExtension(UtilsService::createDataAbsentReasonExtension());
            $serviceRequest->setSubject($missingSubject);
        }

        // Encounter context, falling back to euuid
        $encounterUuid = self::linkedUuid($dataRecord, 'encounter_id', 'encounter') ?? self::field($dataRecord, 'euuid');
        if ($encounterUuid !== null) {
            $serviceRequest->setEncounter(UtilsService::createRelativeReference('Encounter', $encounterUuid));
        }

        // Track whether we set occurrence[x]
        $hasOccurrence = false;

        // Occurrence[x] - MUST SUPPORT - when service is to be performed
        // we go off the scheduled start time, then the scheduled date, then date collected of specimen if we have no other option
        $serviceStartDate = self::nonEmpty($dataRecord['scheduled_start'] ?? $dataRecord['scheduled_date'] ?? $dataRecord['date_collected'] ?? null);
        if ($serviceStartDate !== null) {
            // period takes precedence over dateTime if both start and end are present
            $occurrenceStart = new FHIRDateTime(self::utc($serviceStartDate));
            $scheduledEnd = self::field($dataRecord, 'scheduled_end');
            if ($scheduledEnd !== null) {
                $period = new FHIRPeriod();
                $period->setStart($occurrenceStart);
                $period->setEnd(new FHIRDateTime(self::utc($scheduledEnd)));
                $serviceRequest->setOccurrencePeriod($period);
            } else {
                $serviceRequest->setOccurrenceDateTime($occurrenceStart);
            }
            $hasOccurrence = true;
        }

        // Authored date - REQUIRED if occurrence[x] not present - when order was created
        // US Core 8.0: Must have occurrence[x] OR authoredOn (at least one)
        if ($dateOrdered !== null) {
            $serviceRequest->setAuthoredOn(new FHIRDateTime(self::utc($dateOrdered)));
        } elseif (!$hasOccurrence) {
            // Fallback: Use current timestamp if no temporal data exists
            $serviceRequest->setAuthoredOn(new FHIRDateTime(UtilsService::getDateFormattedAsUTC()));
        }

        // Requester - MUST SUPPORT - the provider who ordered (provider_id), falling back to prov_uuid
        $requesterUuid = self::linkedUuid($dataRecord, 'provider_id', 'provider') ?? self::field($dataRecord, 'prov_uuid');
        if ($requesterUuid !== null) {
            $serviceRequest->setRequester(UtilsService::createRelativeReference('Practitioner', $requesterUuid));
        }

        // Performer - MUST SUPPORT - who will perform the service (lab_id references procedure_providers),
        // falling back to lab_uuid
        $labUuid = self::linkedUuid($dataRecord, 'lab_id', 'lab') ?? self::field($dataRecord, 'lab_uuid');
        if ($labUuid !== null) {
            $serviceRequest->addPerformer(UtilsService::createRelativeReference('Organization', $labUuid));
        }

        // PerformerType - type of performer (from new field or inferred from order type)
        $performerTypeCode = self::field($dataRecord, 'performer_type');
        $performerType = null;
        if ($performerTypeCode !== null) {
            $performerType = $this->buildPerformerType($performerTypeCode);
        } elseif ($orderType !== null) {
            // Infer from order type if not specified
            $performerType = $this->inferPerformerTypeFromCategory($orderType);
        }
        if ($performerType !== null) {
            $serviceRequest->setPerformerType($performerType);
        }

        // LocationReference - where service should be performed
        $locationUuid = self::linkedUuid($dataRecord, 'location_id', 'location');
        if ($locationUuid !== null) {
            $serviceRequest->addLocationReference(UtilsService::createRelativeReference('Location', $locationUuid));
        }

        // Priority - MUST SUPPORT - from order_priority field
        $orderPriority = self::field($dataRecord, 'order_priority');
        if ($orderPriority !== null) {
            $serviceRequest->setPriority(new FHIRRequestPriority(['value' => $this->mapOrderPriority($orderPriority)]));
        }

        // ReasonCode - from order_diagnosis (procedure_order) or diagnoses (procedure_order_code)
        $reasonCodes = [];
        $orderDiagnosis = self::field($dataRecord, 'order_diagnosis');
        if ($orderDiagnosis !== null) {
            $reasonCodes = array_merge($reasonCodes, $this->buildReasonCodes($orderDiagnosis));
        }
        $codeDiagnoses = self::field($dataRecord, 'diagnoses');
        if ($codeDiagnoses !== null) {
            $reasonCodes = array_merge($reasonCodes, $this->buildReasonCodes($codeDiagnoses));
        }
        foreach ($reasonCodes as $reasonCode) {
            $serviceRequest->addReasonCode($reasonCode);
        }

        // ReasonReference - from reason_code in procedure_order_code
        $reasonConditionUuid = self::field($dataRecord, 'reason_condition_uuid');
        if ($reasonConditionUuid !== null) {
            $serviceRequest->addReasonReference(UtilsService::createRelativeReference('Condition', $reasonConditionUuid));
        }

        // PatientInstruction - MUST SUPPORT - from patient_instructions field
        $patientInstructions = self::field($dataRecord, 'patient_instructions');
        if ($patientInstructions !== null) {
            $serviceRequest->setPatientInstruction(new FHIRString($patientInstructions));
        }

        // Note - clinical notes from clinical_hx field or reason_description
        foreach (['clinical_hx', 'reason_description'] as $noteColumn) {
            $noteText = self::field($dataRecord, $noteColumn);
            if ($noteText !== null) {
                $note = new FHIRAnnotation();
                $note->setText(new FHIRMarkdown($noteText));
                $serviceRequest->addNote($note);
            }
        }

        // SupportingInfo - additional clinical information
        // Fetch from procedure_order_relationships junction table
        $procedureOrderId = self::id($dataRecord, 'procedure_order_id');
        if ($procedureOrderId !== null) {
            foreach ($this->relationshipService->getRelationshipsForFhir($procedureOrderId) as $rel) {
                $supportingInfo = self::typedReference($rel);
                if ($supportingInfo !== null) {
                    $serviceRequest->addSupportingInfo($supportingInfo);
                }
            }
        }
        // Fallback: if supporting_info passed directly in dataRecord (for backwards compatibility)
        $passedSupportingInfo = $dataRecord['supporting_info'] ?? null;
        if (is_array($passedSupportingInfo)) {
            foreach ($passedSupportingInfo as $info) {
                $supportingInfo = self::typedReference($info);
                if ($supportingInfo !== null) {
                    $serviceRequest->addSupportingInfo($supportingInfo);
                }
            }
        }

        // Specimen - from specimen_type, specimen_location, specimen_volume
        $specimenUuid = self::field($dataRecord, 'specimen_uuid');
        $specimenType = self::field($dataRecord, 'specimen_type');
        if ($specimenUuid !== null) {
            $serviceRequest->addSpecimen(UtilsService::createRelativeReference('Specimen', $specimenUuid));
        } elseif ($specimenType !== null) {
            // Add specimen details as note if no specimen resource
            $specimenText = "Specimen Type: " . $specimenType;
            foreach (['specimen_location' => 'Location', 'specimen_volume' => 'Volume', 'specimen_fasting' => 'Fasting'] as $column => $label) {
                $detail = self::field($dataRecord, $column);
                if ($detail !== null) {
                    $specimenText .= ", " . $label . ": " . $detail;
                }
            }
            $specimenNote = new FHIRAnnotation();
            $specimenNote->setText(new FHIRMarkdown($specimenText));
            $serviceRequest->addNote($specimenNote);
        }

        // Insurance/Coverage - from billing_type or linked coverage
        $insuranceUuid = self::field($dataRecord, 'insurance_uuid');
        if ($insuranceUuid !== null) {
            $serviceRequest->addInsurance(UtilsService::createRelativeReference('Coverage', $insuranceUuid));
        }

        return $serviceRequest;
    }

    /**
     * Map OpenEMR order_status and activity to FHIR ServiceRequest status
     *
     * @param string $orderStatus OpenEMR order_status value (pending,routed,complete,canceled)
     * @param bool   $deleted     Whether the order's activity flag is 0 (deleted)
     * @return string FHIR status code
     */
    private function mapOrderStatus(string $orderStatus, bool $deleted): string
    {
        // If activity = 0, it's deleted
        if ($deleted) {
            return 'entered-in-error';
        }

        // OpenEMR order_status: pending, routed, complete, canceled
        // FHIR status: draft | active | on-hold | revoked | completed | entered-in-error | unknown

        $statusMap = [
            'pending' => 'active',
            'routed' => 'active',
            'complete' => 'completed',
            'completed' => 'completed',
            'canceled' => 'revoked',
            'cancelled' => 'revoked',
            'on-hold' => 'on-hold',
            'draft' => 'draft',
        ];

        return $statusMap[strtolower($orderStatus)] ?? 'active';
    }

    /**
     * Map OpenEMR order_priority to FHIR request priority
     *
     * @param string $orderPriority OpenEMR order_priority value
     * @return string FHIR priority code (routine | urgent | asap | stat)
     */
    private function mapOrderPriority(string $orderPriority): string
    {
        $priorityMap = [
            'routine' => 'routine',
            'normal' => 'routine',
            'urgent' => 'urgent',
            'high' => 'urgent',
            'asap' => 'asap',
            'stat' => 'stat',
            'emergency' => 'stat',
        ];

        return $priorityMap[strtolower($orderPriority)] ?? 'routine';
    }

    /**
     * Map OpenEMR procedure_order_type to USCDI v5 order category
     * US Core 8.0 requires category from SNOMED CT
     *
     * @param string $procedureOrderType From procedure_order.procedure_order_type field
     */
    private function mapOrderTypeToCategory(string $procedureOrderType): FHIRCodeableConcept
    {
        if (!isset(self::CATEGORY_MAP[$procedureOrderType])) {
            // Default to diagnostic procedure
            $procedureOrderType = self::ORDER_TYPE_CLINICAL_TEST;
        }

        $category = self::CATEGORY_MAP[$procedureOrderType];
        return UtilsService::createCodeableConcept([
            $category['code'] => $category
        ]);
    }

    /**
     * Infer performer type from order category
     */
    private function inferPerformerTypeFromCategory(string $procedureOrderType): ?FHIRCodeableConcept
    {
        $performerMap = [
            self::ORDER_TYPE_LABORATORY => 'laboratory',
            self::ORDER_TYPE_IMAGING => 'radiology',
            self::ORDER_TYPE_CLINICAL_TEST => 'laboratory',
        ];

        $performerType = $performerMap[$procedureOrderType] ?? null;
        if ($performerType !== null) {
            return $this->buildPerformerType($performerType);
        }
        return null;
    }

    /**
     * Build performer type CodeableConcept
     * Uses SNOMED CT codes for healthcare provider taxonomy
     */
    private function buildPerformerType(string $performerTypeCode): ?FHIRCodeableConcept
    {
        $performerTypeMap = [
            'laboratory' => [
                'code' => '159001',
                'description' => 'Laboratory technician',
                'system' => self::CATEGORY_SYSTEM_SNOMED
            ],
            'radiology' => [
                'code' => '66862007',
                'description' => 'Radiologist',
                'system' => self::CATEGORY_SYSTEM_SNOMED
            ],
            'pathology' => [
                'code' => '61207006',
                'description' => 'Pathologist',
                'system' => self::CATEGORY_SYSTEM_SNOMED
            ],
        ];

        if (isset($performerTypeMap[$performerTypeCode])) {
            $type = $performerTypeMap[$performerTypeCode];
            return UtilsService::createCodeableConcept([
                $type['code'] => $type
            ]);
        }

        return null;
    }

    /**
     * Build the code element for the ServiceRequest
     * Uses procedure_code from procedure_order_code table
     * US Core 8.0: Codes should be from LOINC, SNOMED CT, CPT, or HCPCS
     *
     * @param array<mixed> $dataRecord
     */
    private function buildOrderCode(array $dataRecord): FHIRCodeableConcept
    {
        $codesService = new CodeTypesService();
        $codeableConcept = new FHIRCodeableConcept();
        $procedureCode = self::field($dataRecord, 'procedure_code');
        $procedureName = self::field($dataRecord, 'procedure_name');

        // Primary code from procedure_order_code.procedure_code
        if ($procedureCode !== null) {
            $codeParts = $codesService->parseCode($procedureCode);
            // code_type is null when the code has no "type:" prefix; null and '' find no system alike
            $codeType = $codeParts['code_type'];
            $system = $codesService->getSystemForCodeType(is_string($codeType) ? $codeType : '');

            if (self::nonEmpty($system) !== null) {
                $description = $codesService->lookup_code_description($procedureCode);
                if (self::nonEmpty($description) === null && $procedureName !== null) {
                    $description = $procedureName;
                }

                $codeableConcept->addCoding(
                    UtilsService::createCoding(
                        $codeParts['code'],
                        $description,
                        $system
                    )
                );
            }
        }

        // Text description from procedure_order_code.procedure_name
        if ($procedureName !== null) {
            $codeableConcept->setText(new FHIRString($procedureName));
        }

        // US Core 8.0: Code is REQUIRED
        // If no coded values and no text, return data-absent
        if ($codeableConcept->getCoding() === [] && $procedureName === null) {
            // UtilsService declares no return type here; it builds a FHIRCodeableConcept
            $dataAbsent = UtilsService::createDataAbsentUnknownCodeableConcept();
            if ($dataAbsent instanceof FHIRCodeableConcept) {
                return $dataAbsent;
            }
        }

        return $codeableConcept;
    }

    /**
     * Build reason codes from diagnosis string
     * Format in OpenEMR: "ICD10:E11.9;ICD10:I10" or similar
     * Can be from order_diagnosis (procedure_order) or diagnoses (procedure_order_code)
     * @return list<FHIRCodeableConcept>
     */
    private function buildReasonCodes(string $diagnosisString): array
    {
        $reasonCodes = [];
        $codesService = new CodeTypesService();

        // Split by semicolon for multiple diagnoses
        $diagnoses = explode(";", $diagnosisString);
        foreach ($diagnoses as $diagnosis) {
            if (self::nonEmpty(trim($diagnosis)) === null) {
                continue;
            }

            // Format is typically "ICD10:E11.9" or "SNOMED:1234567"
            $parts = explode(":", $diagnosis, 2);
            if (count($parts) >= 2) {
                $codeType = trim($parts[0]);
                $code = trim($parts[1]);

                $fullCode = $codesService->getCodeWithType($code, $codeType);
                $description = $codesService->lookup_code_description($fullCode);
                $system = $codesService->getSystemForCodeType($codeType);

                if (self::nonEmpty($system) !== null) {
                    $reasonCodes[] = UtilsService::createCodeableConcept([
                        $code => [
                            'code' => $code,
                            'description' => $description ?: null,
                            'system' => $system
                        ]
                    ]);
                }
            }
        }

        return $reasonCodes;
    }

    /**
     * Parses a FHIR ServiceRequest into a {header, codes} shape consumed by
     * ProcedureService::createOrder / updateOrder. The header describes the procedure_order
     * row (patient, requester, encounter, category=procedure_order_type, status, priority,
     * authoredOn, notes); the codes array becomes procedure_order_code rows (one per
     * code.coding entry).
     *
     * Subject (Patient) reference is required for new orders. The mapping inverts the
     * mapOrderStatus and mapOrderPriority helpers used by parseOpenEMRRecord, with
     * 'on-hold'/'draft' falling through to their literal OpenEMR values.
     *
     * @param FHIRDomainResource $fhirResource
     * @return array<string, mixed>
     */
    public function parseFhirResource(FHIRDomainResource $fhirResource): array
    {
        if (!($fhirResource instanceof FHIRServiceRequest)) {
            throw new \InvalidArgumentException(
                'Expected FHIRServiceRequest resource, got ' . $fhirResource::class
            );
        }

        $json = $fhirResource->jsonSerialize();
        $header = [];
        $codes = [];
        $data = ['header' => &$header, 'codes' => &$codes];

        $resourceId = $json['id'] ?? null;
        if (is_string($resourceId) && $resourceId !== '') {
            $data['uuid'] = $resourceId;
        }

        // subject.reference -> patient puuid (resolved downstream)
        $subjectRef = FhirPayloadReader::reference($json['subject'] ?? null);
        if ($subjectRef !== null) {
            $subjectUuid = UtilsService::parseReferenceString($subjectRef, 'Patient')['uuid'] ?? null;
            if (is_string($subjectUuid) && $subjectUuid !== '' && UuidRegistry::isValidStringUUID($subjectUuid)) {
                $data['puuid'] = $subjectUuid;
            }
        }

        // requester.reference -> provider pruuid (resolved downstream; optional)
        $requesterRef = FhirPayloadReader::reference($json['requester'] ?? null);
        if ($requesterRef !== null) {
            $requesterUuid = UtilsService::parseReferenceString($requesterRef, 'Practitioner')['uuid'] ?? null;
            if (is_string($requesterUuid) && $requesterUuid !== '' && UuidRegistry::isValidStringUUID($requesterUuid)) {
                $data['pruuid'] = $requesterUuid;
            }
        }

        // encounter.reference -> encounter euuid (optional)
        $encounterRef = FhirPayloadReader::reference($json['encounter'] ?? null);
        if ($encounterRef !== null) {
            $encounterUuid = UtilsService::parseReferenceString($encounterRef, 'Encounter')['uuid'] ?? null;
            if (is_string($encounterUuid) && $encounterUuid !== '' && UuidRegistry::isValidStringUUID($encounterUuid)) {
                $data['euuid'] = $encounterUuid;
            }
        }

        // status -> order_status (inverse of mapOrderStatus)
        $status = $json['status'] ?? null;
        if (is_string($status) && $status !== '') {
            $statusReverseMap = [
                'active' => 'pending',
                'completed' => 'complete',
                'revoked' => 'canceled',
                'draft' => 'pending',
                'on-hold' => 'pending',
            ];
            $header['order_status'] = $statusReverseMap[$status] ?? 'pending';
            if ($status === 'entered-in-error') {
                $header['activity'] = 0;
            }
        }

        // intent -> order_intent (FHIR R4 1..1). The list_options 'order_intent' set holds
        // order/plan/directive/proposal/option. The other R4 intents -- original-order,
        // reflex-order, filler-order, instance-order -- used to fall back to 'order', which
        // answered 200 for an order whose clinical meaning the server had quietly changed:
        // a reflex-order is one the lab raised off another result, not one a clinician placed.
        // There is no column that preserves the distinction, so the write is refused instead.
        $intent = $json['intent'] ?? null;
        if (is_string($intent) && $intent !== '') {
            $supportedIntents = ['order', 'plan', 'directive', 'proposal', 'option'];
            if (!in_array($intent, $supportedIntents, true)) {
                $data['__validation_error__'] = 'ServiceRequest.intent "' . $intent
                    . '" cannot be stored; OpenEMR supports '
                    . implode(', ', $supportedIntents);
                $data['__validation_field__'] = 'intent';
            } else {
                $header['order_intent'] = $intent;
            }
        }

        // priority passthrough (matches OpenEMR vocab for routine/urgent/asap/stat)
        $priority = $json['priority'] ?? null;
        if (is_string($priority) && $priority !== '') {
            $header['order_priority'] = $priority;
        }

        // category[0].coding[0].code (SNOMED) -> procedure_order_type (inverse of CATEGORY_MAP)
        $categoryCode = FhirPayloadReader::firstConceptCode($json['category'] ?? null);
        if ($categoryCode !== '') {
            $categoryReverseMap = [
                self::CATEGORY_LABORATORY => self::ORDER_TYPE_LABORATORY,
                self::CATEGORY_IMAGING => self::ORDER_TYPE_IMAGING,
                self::CATEGORY_CLINICAL_TEST => self::ORDER_TYPE_CLINICAL_TEST,
                self::CATEGORY_PROCEDURE => self::ORDER_TYPE_PROCEDURE,
            ];
            if (isset($categoryReverseMap[$categoryCode])) {
                $header['procedure_order_type'] = $categoryReverseMap[$categoryCode];
            }
        }

        // authoredOn -> date_ordered (Y-m-d H:i:s for DATETIME column)
        $dateOrdered = FhirDateTimeParser::toDbDateTime(
            $json['authoredOn'] ?? null,
            'ServiceRequest.authoredOn'
        );
        if ($dateOrdered !== null) {
            $header['date_ordered'] = $dateOrdered;
        }

        // patientInstruction -> patient_instructions
        $patientInstruction = $json['patientInstruction'] ?? null;
        if (is_string($patientInstruction) && $patientInstruction !== '') {
            $header['patient_instructions'] = $patientInstruction;
        }

        // note[0].text -> clinical_hx (best-fit free-text field)
        $noteText = FhirPayloadReader::getString(FhirPayloadReader::get($json['note'] ?? null, 0), 'text');
        if ($noteText !== null) {
            $header['clinical_hx'] = $noteText;
        }

        // code.coding[] -> procedure_order_code rows (one per coding, or fallback to text)
        $codeConcept = $json['code'] ?? null;
        $codeText = FhirPayloadReader::getString($codeConcept, 'text');
        foreach (FhirPayloadReader::codings($codeConcept) as $coding) {
            $procCode = $coding['code'] ?? '';
            if (!is_string($procCode) || $procCode === '') {
                continue;
            }
            $display = $coding['display'] ?? null;
            $codes[] = [
                'procedure_code' => $procCode,
                'procedure_name' => is_string($display)
                    ? $display
                    : ($codeText ?? ''),
                'procedure_order_title' => is_string($display) ? $display : '',
            ];
        }
        // No usable coding: procedure_order_code.procedure_code is varchar(64) NOT NULL and
        // ProcedureService requires it non-empty, so code.text cannot stand in for a code --
        // a long display string would be truncated on the way to the column. Surface the
        // gap instead; insertOpenEMRRecord/updateOpenEMRRecord turn it into a 422.
        if ($codes === []) {
            $data['__validation_error__'] = $codeText !== null
                ? 'ServiceRequest.code requires a coding with a code; code.text alone cannot be stored'
                : 'ServiceRequest.code is required and must carry a coding with a code';
            $data['__validation_field__'] = 'code';
        }

        // reasonCode[0].coding -> diagnoses on the first procedure code row (string form)
        $reasonCoding = FhirPayloadReader::firstCoding(
            FhirPayloadReader::get($json['reasonCode'] ?? null, 0)
        );
        $reasonCode = $reasonCoding['code'] ?? null;
        if (is_string($reasonCode) && $reasonCode !== '' && $codes !== []) {
            $reasonSystem = $reasonCoding['system'] ?? null;
            $system = is_string($reasonSystem) ? $reasonSystem : '';
            $codes[0]['diagnoses'] = (new CodeTypesService())
                ->getOpenEMRCodeForSystemAndCode($system, $reasonCode);
        }

        return $data;
    }

    /**
     * @param mixed $openEmrRecord The parsed record from parseFhirResource()
     */
    protected function insertOpenEMRRecord($openEmrRecord): ProcessingResult
    {
        if (!is_array($openEmrRecord)) {
            throw new \InvalidArgumentException('Expected a parsed OpenEMR ServiceRequest record array');
        }

        $validationError = $openEmrRecord['__validation_error__'] ?? null;
        if (is_string($validationError)) {
            $result = new ProcessingResult();
            // The marker names its own element; 'code' stays the default because it was the
            // first and only producer before intent started using it.
            $field = $openEmrRecord['__validation_field__'] ?? 'code';
            $result->setValidationMessages([is_string($field) ? $field : 'code' => $validationError]);
            return $result;
        }

        $puuid = $openEmrRecord['puuid'] ?? null;
        if (!is_string($puuid) || $puuid === '') {
            $result = new ProcessingResult();
            $result->setValidationMessages(['subject' => 'FHIR ServiceRequest requires a Patient reference']);
            return $result;
        }
        $pid = QueryUtils::fetchSingleValue(
            'SELECT pid FROM patient_data WHERE uuid = ?',
            'pid',
            [UuidRegistry::uuidToBytes($puuid)]
        );
        if (!is_numeric($pid)) {
            $result = new ProcessingResult();
            $result->setValidationMessages(['subject' => 'Patient reference could not be resolved: ' . $puuid]);
            return $result;
        }

        $header = FhirPayloadReader::stringKeyed($openEmrRecord['header'] ?? null);
        $header['patient_id'] = (int) $pid;

        // Optional encounter resolution
        $euuid = $openEmrRecord['euuid'] ?? null;
        if (is_string($euuid) && $euuid !== '') {
            // The encounter is optional, but one the client names has to exist: dropping an
            // unresolvable reference would save the order unlinked and answer success.
            // A soft-deleted encounter resolves to null, like an unknown one.
            $encounter = EncounterService::getActiveEncounterByUuid($euuid);
            if ($encounter === null) {
                $result = new ProcessingResult();
                $result->setValidationMessages([
                    'encounter' => 'Encounter reference could not be resolved: ' . $euuid,
                ]);
                return $result;
            }
            $header['encounter_id'] = $encounter['encounter'];
        }

        // Optional requester resolution. procedure_order.provider_id is the ordering provider of
        // record, so a caller may only name themselves unless they hold admin/users -- see
        // PractitionerAttributionPolicy.
        $pruuid = $openEmrRecord['pruuid'] ?? null;
        if (is_string($pruuid) && $pruuid !== '') {
            $header['provider_id'] = (new PractitionerAttributionPolicy($this->getSession()))
                ->resolveAndAssert(
                    $pruuid,
                    'ServiceRequest.requester',
                    static fn(string $bytes) => QueryUtils::fetchSingleValue(
                        'SELECT id FROM users WHERE uuid = ?',
                        'id',
                        [$bytes]
                    )
                );
        }

        $codes = FhirPayloadReader::rows($openEmrRecord['codes'] ?? null);

        return $this->procedureService->createOrder($header, $codes);
    }

    /**
     * @param string $fhirResourceId
     * @param array<array-key, mixed> $updatedOpenEMRRecord
     */
    protected function updateOpenEMRRecord($fhirResourceId, $updatedOpenEMRRecord): ProcessingResult
    {
        $validationError = $updatedOpenEMRRecord['__validation_error__'] ?? null;
        if (is_string($validationError)) {
            $result = new ProcessingResult();
            // The marker names its own element; 'code' stays the default because it was the
            // first and only producer before intent started using it.
            $field = $updatedOpenEMRRecord['__validation_field__'] ?? 'code';
            $result->setValidationMessages([is_string($field) ? $field : 'code' => $validationError]);
            return $result;
        }

        $header = FhirPayloadReader::stringKeyed($updatedOpenEMRRecord['header'] ?? null);
        // PUT cannot rebind patient/encounter/requester; drop those resolved ids.
        unset($header['patient_id']);

        $codes = FhirPayloadReader::rows($updatedOpenEMRRecord['codes'] ?? null);

        // Resolve the body's subject to a pid and require it to match the stored
        // procedure_order.patient_id, so a leaked order uuid cannot be used to mutate
        // another patient's order. The subject is required rather than optional: leaving
        // it out would resolve to no expected pid and skip the check entirely, which is
        // the same as not having it. ServiceRequest.subject is 1..1 in R4 regardless.
        $puuid = $updatedOpenEMRRecord['puuid'] ?? null;
        if (!is_string($puuid) || $puuid === '') {
            $result = new ProcessingResult();
            $result->setValidationMessages(
                ['subject' => 'FHIR ServiceRequest requires a Patient reference']
            );
            return $result;
        }
        $pid = QueryUtils::fetchSingleValue(
            'SELECT pid FROM patient_data WHERE uuid = ?',
            'pid',
            [UuidRegistry::uuidToBytes($puuid)]
        );
        if (!is_numeric($pid)) {
            $result = new ProcessingResult();
            $result->setValidationMessages(
                ['subject' => 'Patient reference could not be resolved: ' . $puuid]
            );
            return $result;
        }

        return $this->procedureService->updateOrder($fhirResourceId, $header, $codes, (int) $pid);
    }

    /**
     * Creates the Provenance resource for the ServiceRequest
     */
    public function createProvenanceResource($dataRecord, $encode = false)
    {
        if (!($dataRecord instanceof FHIRServiceRequest)) {
            throw new \BadMethodCallException("Data record should be correct instance class");
        }

        $fhirProvenanceService = new FhirProvenanceService();
        $requester = $dataRecord->getRequester();

        $fhirProvenance = $fhirProvenanceService->createProvenanceForDomainResource(
            $dataRecord,
            $requester
        );

        if ($encode) {
            return json_encode($fhirProvenance);
        }

        return $fhirProvenance;
    }

    /**
     * Returns the Canonical URIs for US Core 8.0 Implementation Guide Profiles
     */
    public function getProfileURIs(): array
    {
        return [
            self::PROFILE_URI
        ];
    }

    /**
     * @param array<mixed> $openEMRSearchParameters
     * @return array<string, ISearchField>
     */
    private static function searchFields(array $openEMRSearchParameters): array
    {
        $search = [];
        foreach ($openEMRSearchParameters as $name => $field) {
            if (is_string($name) && $field instanceof ISearchField) {
                $search[$name] = $field;
            }
        }
        return $search;
    }

    /**
     * A column's value as nonEmpty() reads it.
     *
     * @param array<mixed> $record
     */
    private static function field(array $record, string $column): ?string
    {
        return self::nonEmpty($record[$column] ?? null);
    }

    /**
     * A string or number as a string, when empty() would have called it set: not '' or '0'. An int
     * or float reads as its digits; anything else is null. empty()'s answer is kept from the code
     * this class was moved from, because the id columns use 0 for none.
     */
    private static function nonEmpty(mixed $value): ?string
    {
        if (is_int($value) || is_float($value)) {
            $value = (string) $value;
        }
        return is_string($value) && $value !== '' && $value !== '0' ? $value : null;
    }

    /**
     * An id column as an int, or null when it's unset or not an integer.
     *
     * @param array<mixed> $record
     */
    private static function id(array $record, string $column): ?int
    {
        $id = filter_var(self::field($record, $column), FILTER_VALIDATE_INT);
        return is_int($id) ? $id : null;
    }

    /**
     * The uuid of a linked record (patient, encounter, provider, lab, location) when the record
     * holds both the link's id column and the linked array with a uuid.
     *
     * @param array<mixed> $record
     */
    private static function linkedUuid(array $record, string $idColumn, string $linked): ?string
    {
        $linkedRecord = $record[$linked] ?? null;
        if (self::field($record, $idColumn) === null || !is_array($linkedRecord)) {
            return null;
        }
        return self::field($linkedRecord, 'uuid');
    }

    /**
     * A reference from a row holding resource_type and uuid (procedure_order_relationships, or
     * supporting_info passed in the record).
     */
    private static function typedReference(mixed $row): ?FHIRReference
    {
        if (!is_array($row)) {
            return null;
        }
        $type = self::field($row, 'resource_type');
        $uuid = self::field($row, 'uuid');
        return $type === null || $uuid === null ? null : UtilsService::createRelativeReference($type, $uuid);
    }

    /** A local date and time from the database, as FHIR's UTC form. */
    private static function utc(string $localDateTime): string
    {
        $utc = UtilsService::getLocalDateAsUTC($localDateTime);
        return is_string($utc) ? $utc : $localDateTime;
    }
}
