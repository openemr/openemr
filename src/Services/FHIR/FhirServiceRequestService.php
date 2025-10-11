<?php

/**
 * FhirServiceRequestService.php
 *
 * @package   openemr
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRServiceRequest;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\R4\FHIRElement\FHIRRequestPriority;
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\ProcedureOrderRelationshipService;
use OpenEMR\Services\FHIR\Traits\BulkExportSupportAllOperationsTrait;
use OpenEMR\Services\FHIR\Traits\FhirBulkExportDomainResourceTrait;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\PatientSearchTrait;
use OpenEMR\Services\ProcedureService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
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
class FhirServiceRequestService extends FhirServiceBase implements
    IResourceUSCIGProfileService,
    IFhirExportableResourceService,
    IPatientCompartmentResourceService
{
    use FhirServiceBaseEmptyTrait;
    use PatientSearchTrait;
    use BulkExportSupportAllOperationsTrait;
    use FhirBulkExportDomainResourceTrait;

    /**
     * USCDI v5 Order Category Codes (SNOMED CT)
     */
    const CATEGORY_LABORATORY = "108252007"; // Laboratory procedure
    const CATEGORY_IMAGING = "363679005"; // Imaging procedure
    const CATEGORY_CLINICAL_TEST = "103693007"; // Diagnostic procedure
    const CATEGORY_PROCEDURE = "387713003"; // Surgical procedure

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

    /**
     * @var ProcedureService
     */
    private $procedureService;

    /**
     * @var ProcedureOrderRelationshipService
     */
    private $relationshipService;

    public function __construct()
    {
        parent::__construct();
        $this->procedureService = new ProcedureService();
        $this->relationshipService = new ProcedureOrderRelationshipService();
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
    protected function loadSearchParameters()
    {
        $return = [
            'patient' => $this->getPatientContextSearchField(),
            'category' => new FhirSearchParameterDefinition('category', SearchFieldType::TOKEN, ['procedure_order_type', 'procedure_type']),
            'code' => new FhirSearchParameterDefinition('code', SearchFieldType::TOKEN, ['procedure_code', 'procedure_name']),
            'authored' => new FhirSearchParameterDefinition('authored', SearchFieldType::DATETIME, ['date_ordered']),
            'status' => new FhirSearchParameterDefinition('status', SearchFieldType::TOKEN, ['order_status', 'order_activity']),
            'intent' => new FhirSearchParameterDefinition('intent', SearchFieldType::TOKEN, ['order_intent']),
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('order_uuid', ServiceField::TYPE_UUID)]),
            '_lastUpdated' => $this->getLastModifiedSearchField(),
        ];
        return $return;
    }

    public function getLastModifiedSearchField(): ?FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['date_ordered']);
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        // Query procedure_order with joined procedure_order_code data
        return $this->procedureService->search($openEMRSearchParameters);
    }

    /**
     * Parses an OpenEMR procedure_order record into a FHIR ServiceRequest resource
     *
     * @param array $dataRecord The source OpenEMR data record from procedure_order + procedure_order_code
     * @param bool  $encode     Indicates if the returned resource is encoded into a string
     * @return FHIRServiceRequest
     */
    public function parseOpenEMRRecord($dataRecord = [], $encode = false)
    {
        $serviceRequest = new FHIRServiceRequest();

        // Meta - US Core 8.0 profile
        $meta = new FHIRMeta();
        $meta->setVersionId('1');
        $meta->addProfile('http://hl7.org/fhir/us/core/StructureDefinition/us-core-servicerequest');

        if (!empty($dataRecord['date_ordered'])) {
            $meta->setLastUpdated(UtilsService::getLocalDateAsUTC($dataRecord['date_ordered']));
        } else {
            $meta->setLastUpdated(UtilsService::getDateFormattedAsUTC());
        }
        $serviceRequest->setMeta($meta);

        // ID - use procedure_order.uuid (aliased as order_uuid in query)
        $id = new FHIRId();
        $id->setValue($dataRecord['order_uuid']);
        $serviceRequest->setId($id);

        // Status - REQUIRED - map from order_status and activity fields
        $status = $this->mapOrderStatus(
            $dataRecord['order_status'] ?? '',
            $dataRecord['activity'] ?? 1
        );
        $serviceRequest->setStatus($status);

        // Intent - REQUIRED - typically 'order' for placed orders
        $intent = $dataRecord['order_intent'] ?? 'order';
        $serviceRequest->setIntent($intent);

        // Category - REQUIRED - USCDI v5 requirement from procedure_order_type
        $category = $this->mapOrderTypeToCategory($dataRecord['procedure_order_type'] ?? '');
        if ($category) {
            $serviceRequest->addCategory($category);
        }

        // Code - REQUIRED - from procedure_order_code table
        // Note: If multiple procedure_order_code records exist, this gets the primary one
        $code = $this->buildOrderCode($dataRecord);
        $serviceRequest->setCode($code);

        // Subject (patient) - REQUIRED
        if (!empty($dataRecord['patient_id'])) {
            if (!empty($dataRecord['patient']['uuid'])) {
                $serviceRequest->setSubject(
                    UtilsService::createRelativeReference('Patient', $dataRecord['patient']['uuid'])
                );
            } else {
                $serviceRequest->setSubject(UtilsService::createDataMissingExtension());
            }
        }

        // Encounter context
        if (!empty($dataRecord['encounter_id']) && !empty($dataRecord['encounter']['uuid'])) {
            $serviceRequest->setEncounter(
                UtilsService::createRelativeReference('Encounter', $dataRecord['encounter']['uuid'])
            );
        }

        // OccurrenceDateTime - when specimen should be collected or service performed
        // Use date_collected if available, or scheduled_date
        if (!empty($dataRecord['date_collected'])) {
            $serviceRequest->setOccurrenceDateTime(
                new FHIRDateTime(UtilsService::getLocalDateAsUTC($dataRecord['date_collected']))
            );
        } elseif (!empty($dataRecord['scheduled_date'])) {
            $serviceRequest->setOccurrenceDateTime(
                new FHIRDateTime(UtilsService::getLocalDateAsUTC($dataRecord['scheduled_date']))
            );
        }

        // OccurrencePeriod - for procedures with specific start and end times
        if (!empty($dataRecord['scheduled_start']) && !empty($dataRecord['scheduled_end'])) {
            $period = new FHIRPeriod();
            $period->setStart(new FHIRDateTime(UtilsService::getLocalDateAsUTC($dataRecord['scheduled_start'])));
            $period->setEnd(new FHIRDateTime(UtilsService::getLocalDateAsUTC($dataRecord['scheduled_end'])));
            $serviceRequest->setOccurrencePeriod($period);
        }

        // Authored date - REQUIRED if occurrence[x] not present - when order was created
        if (!empty($dataRecord['date_ordered'])) {
            $serviceRequest->setAuthoredOn(
                new FHIRDateTime(UtilsService::getLocalDateAsUTC($dataRecord['date_ordered']))
            );
        }

        // Requester - REQUIRED - the provider who ordered (provider_id)
        if (!empty($dataRecord['provider_id'])) {
            if (!empty($dataRecord['provider']['uuid'])) {
                $serviceRequest->setRequester(
                    UtilsService::createRelativeReference('Practitioner', $dataRecord['provider']['uuid'])
                );
            }
        }

        // Performer - who will perform the service (lab_id references procedure_providers)
        if (!empty($dataRecord['lab_id'])) {
            if (!empty($dataRecord['lab']['uuid'])) {
                $serviceRequest->addPerformer(
                    UtilsService::createRelativeReference('Organization', $dataRecord['lab']['uuid'])
                );
            }
        }

        // PerformerType - type of performer (from new field or inferred from order type)
        if (!empty($dataRecord['performer_type'])) {
            $performerType = $this->buildPerformerType($dataRecord['performer_type']);
            if ($performerType) {
                $serviceRequest->setPerformerType($performerType);
            }
        } else {
            // Infer from order type if not specified
            $performerType = $this->inferPerformerTypeFromCategory($dataRecord['procedure_order_type'] ?? '');
            if ($performerType) {
                $serviceRequest->setPerformerType($performerType);
            }
        }

        // LocationReference - where service should be performed
        if (!empty($dataRecord['location_id'])) {
            if (!empty($dataRecord['location']['uuid'])) {
                $serviceRequest->addLocationReference(
                    UtilsService::createRelativeReference('Location', $dataRecord['location']['uuid'])
                );
            }
        }

        // Priority - MUST SUPPORT - from order_priority field
        if (!empty($dataRecord['order_priority'])) {
            $priority = $this->mapOrderPriority($dataRecord['order_priority']);
            $serviceRequest->setPriority($priority);
        }

        // ReasonCode - from order_diagnosis (procedure_order) or diagnoses (procedure_order_code)
        $reasonCodes = [];
        if (!empty($dataRecord['order_diagnosis'])) {
            $reasonCodes = array_merge($reasonCodes, $this->buildReasonCodes($dataRecord['order_diagnosis']));
        }
        if (!empty($dataRecord['diagnoses'])) {
            $reasonCodes = array_merge($reasonCodes, $this->buildReasonCodes($dataRecord['diagnoses']));
        }
        foreach ($reasonCodes as $reasonCode) {
            $serviceRequest->addReasonCode($reasonCode);
        }

        // ReasonReference - from reason_code in procedure_order_code
        if (!empty($dataRecord['reason_condition_uuid'])) {
            $serviceRequest->addReasonReference(
                UtilsService::createRelativeReference('Condition', $dataRecord['reason_condition_uuid'])
            );
        }

        // PatientInstruction - MUST SUPPORT - from patient_instructions field
        if (!empty($dataRecord['patient_instructions'])) {
            $serviceRequest->setPatientInstruction($dataRecord['patient_instructions']);
        }

        // Note - clinical notes from clinical_hx field or reason_description
        $notes = [];
        if (!empty($dataRecord['clinical_hx'])) {
            $note = new FHIRAnnotation();
            $note->setText($dataRecord['clinical_hx']);
            $notes[] = $note;
        }
        if (!empty($dataRecord['reason_description'])) {
            $note = new FHIRAnnotation();
            $note->setText($dataRecord['reason_description']);
            $notes[] = $note;
        }
        foreach ($notes as $note) {
            $serviceRequest->addNote($note);
        }

        // SupportingInfo - additional clinical information
        // Fetch from procedure_order_relationships junction table
        if (!empty($dataRecord['procedure_order_id'])) {
            $relationshipRecords = $this->relationshipService->getRelationshipsForFhir(
                $dataRecord['procedure_order_id']
            );

            foreach ($relationshipRecords as $rel) {
                if (!empty($rel['resource_type']) && !empty($rel['uuid'])) {
                    $serviceRequest->addSupportingInfo(
                        UtilsService::createRelativeReference($rel['resource_type'], $rel['uuid'])
                    );
                }
            }
        }
        // Fallback: if supporting_info passed directly in dataRecord (for backwards compatibility)
        if (!empty($dataRecord['supporting_info']) && is_array($dataRecord['supporting_info'])) {
            foreach ($dataRecord['supporting_info'] as $info) {
                if (!empty($info['resource_type']) && !empty($info['uuid'])) {
                    $serviceRequest->addSupportingInfo(
                        UtilsService::createRelativeReference($info['resource_type'], $info['uuid'])
                    );
                }
            }
        }

        // Specimen - from specimen_type, specimen_location, specimen_volume
        if (!empty($dataRecord['specimen_uuid'])) {
            $serviceRequest->addSpecimen(
                UtilsService::createRelativeReference('Specimen', $dataRecord['specimen_uuid'])
            );
        } elseif (!empty($dataRecord['specimen_type'])) {
            // Add specimen details as note if no specimen resource
            $specimenNote = new FHIRAnnotation();
            $specimenText = "Specimen Type: " . $dataRecord['specimen_type'];
            if (!empty($dataRecord['specimen_location'])) {
                $specimenText .= ", Location: " . $dataRecord['specimen_location'];
            }
            if (!empty($dataRecord['specimen_volume'])) {
                $specimenText .= ", Volume: " . $dataRecord['specimen_volume'];
            }
            if (!empty($dataRecord['specimen_fasting'])) {
                $specimenText .= ", Fasting: " . $dataRecord['specimen_fasting'];
            }
            $specimenNote->setText($specimenText);
            $serviceRequest->addNote($specimenNote);
        }

        // Insurance/Coverage - from billing_type or linked coverage
        if (!empty($dataRecord['insurance_uuid'])) {
            $serviceRequest->addInsurance(
                UtilsService::createRelativeReference('Coverage', $dataRecord['insurance_uuid'])
            );
        }

        if ($encode) {
            return json_encode($serviceRequest);
        }

        return $serviceRequest;
    }

    /**
     * Map OpenEMR order_status and activity to FHIR ServiceRequest status
     *
     * @param string $orderStatus OpenEMR order_status value (pending,routed,complete,canceled)
     * @param int    $activity    OpenEMR activity flag (0=deleted, 1=active)
     * @return string FHIR status code
     */
    private function mapOrderStatus($orderStatus, $activity = 1)
    {
        // If activity = 0, it's deleted
        if ($activity == 0) {
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
    private function mapOrderPriority($orderPriority)
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
     * @return FHIRCodeableConcept|null
     */
    private function mapOrderTypeToCategory($procedureOrderType)
    {
        $categoryMap = [
            self::ORDER_TYPE_LABORATORY => [
                'code' => self::CATEGORY_LABORATORY,
                'display' => 'Laboratory procedure',
                'system' => self::CATEGORY_SYSTEM_SNOMED
            ],
            self::ORDER_TYPE_IMAGING => [
                'code' => self::CATEGORY_IMAGING,
                'display' => 'Imaging',
                'system' => self::CATEGORY_SYSTEM_SNOMED
            ],
            self::ORDER_TYPE_CLINICAL_TEST => [
                'code' => self::CATEGORY_CLINICAL_TEST,
                'display' => 'Diagnostic procedure',
                'system' => self::CATEGORY_SYSTEM_SNOMED
            ],
            self::ORDER_TYPE_PROCEDURE => [
                'code' => self::CATEGORY_PROCEDURE,
                'display' => 'Surgical procedure',
                'system' => self::CATEGORY_SYSTEM_SNOMED
            ],
        ];

        if (!isset($categoryMap[$procedureOrderType])) {
            // Default to diagnostic procedure
            return UtilsService::createCodeableConcept([
                self::CATEGORY_CLINICAL_TEST => [
                    'code' => self::CATEGORY_CLINICAL_TEST,
                    'display' => 'Diagnostic procedure',
                    'system' => self::CATEGORY_SYSTEM_SNOMED
                ]
            ]);
        }

        $category = $categoryMap[$procedureOrderType];
        return UtilsService::createCodeableConcept([
            $category['code'] => $category
        ]);
    }

    /**
     * Infer performer type from order category
     */
    private function inferPerformerTypeFromCategory($procedureOrderType)
    {
        $performerMap = [
            self::ORDER_TYPE_LABORATORY => 'laboratory',
            self::ORDER_TYPE_IMAGING => 'radiology',
            self::ORDER_TYPE_CLINICAL_TEST => 'laboratory',
        ];

        $performerType = $performerMap[$procedureOrderType] ?? null;
        if ($performerType) {
            return $this->buildPerformerType($performerType);
        }
        return null;
    }

    /**
     * Build performer type CodeableConcept
     * Uses SNOMED CT codes for healthcare provider taxonomy
     */
    private function buildPerformerType($performerTypeCode)
    {
        $performerTypeMap = [
            'laboratory' => [
                'code' => '159001',
                'display' => 'Laboratory technician',
                'system' => self::CATEGORY_SYSTEM_SNOMED
            ],
            'radiology' => [
                'code' => '66862007',
                'display' => 'Radiologist',
                'system' => self::CATEGORY_SYSTEM_SNOMED
            ],
            'pathology' => [
                'code' => '61207006',
                'display' => 'Pathologist',
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
     */
    private function buildOrderCode($dataRecord)
    {
        $codesService = new CodeTypesService();
        $codeableConcept = new FHIRCodeableConcept();

        // Primary code from procedure_order_code.procedure_code
        if (!empty($dataRecord['procedure_code'])) {
            $codeParts = $codesService->parseCode($dataRecord['procedure_code']);
            $system = $codesService->getSystemForCodeType($codeParts['code_type']);

            if (!empty($system)) {
                $description = $codesService->lookup_code_description($dataRecord['procedure_code']);
                if (empty($description) && !empty($dataRecord['procedure_name'])) {
                    $description = $dataRecord['procedure_name'];
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
        if (!empty($dataRecord['procedure_name'])) {
            $codeableConcept->setText($dataRecord['procedure_name']);
        }

        // If no codes, use text only or data absent
        if (empty($codeableConcept->getCoding())) {
            if (!empty($dataRecord['procedure_name'])) {
                $codeableConcept->setText($dataRecord['procedure_name']);
            } else {
                return UtilsService::createDataAbsentUnknownCodeableConcept();
            }
        }

        return $codeableConcept;
    }

    /**
     * Build reason codes from diagnosis string
     * Format in OpenEMR: "ICD10:E11.9;ICD10:I10" or similar
     * Can be from order_diagnosis (procedure_order) or diagnoses (procedure_order_code)
     */
    private function buildReasonCodes($diagnosisString)
    {
        $reasonCodes = [];
        $codesService = new CodeTypesService();

        // Split by semicolon for multiple diagnoses
        $diagnoses = explode(";", $diagnosisString);
        foreach ($diagnoses as $diagnosis) {
            if (empty(trim($diagnosis))) {
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

                if (!empty($system)) {
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
            'http://hl7.org/fhir/us/core/StructureDefinition/us-core-servicerequest'
        ];
    }
}
