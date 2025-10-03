<?php

/**
 * FhirServiceRequestService.php
 *
 * @package openemr
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
use OpenEMR\Services\CodeTypesService;
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
 * FHIR ServiceRequest Service for USCDI v5 Orders Data Class
 * Handles Laboratory Orders, Diagnostic Imaging Orders, Clinical Test Orders, and Procedure Orders
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
    const CATEGORY_MEDICATION = "order"; // From http://hl7.org/fhir/us/core/CodeSystem/us-core-category

    /**
     * US Core ServiceRequest Category System
     */
    const CATEGORY_SYSTEM_SNOMED = FhirCodeSystemConstants::SNOMED_CT;
    const CATEGORY_SYSTEM_US_CORE = "http://hl7.org/fhir/us/core/CodeSystem/us-core-category";

    /**
     * OpenEMR procedure_order_title / order types mapping
     * @see list_options order_types
     */
    const ORDER_TYPE_LABORATORY = "laboratory_test";
    const ORDER_TYPE_IMAGING = "imaging";
    const ORDER_TYPE_CLINICAL_TEST = "clinical_test";
    const ORDER_TYPE_PROCEDURE = "procedure";

    /**
     * @var ProcedureService
     */
    private $procedureService;

    public function __construct()
    {
        parent::__construct();
        $this->procedureService = new ProcedureService();
    }

    /**
     * Returns an array mapping FHIR ServiceRequest search parameters to OpenEMR search parameters
     */
    protected function loadSearchParameters()
    {
        return [
            'patient' => $this->getPatientContextSearchField(),
            'category' => new FhirSearchParameterDefinition('category', SearchFieldType::TOKEN, ['procedure_type']),
            'code' => new FhirSearchParameterDefinition('code', SearchFieldType::TOKEN, ['procedure_code', 'standard_code']),
            'authored' => new FhirSearchParameterDefinition('authored', SearchFieldType::DATETIME, ['date_ordered']),
            'status' => new FhirSearchParameterDefinition('status', SearchFieldType::TOKEN, ['order_status']),
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('order_uuid', ServiceField::TYPE_UUID)]),
            '_lastUpdated' => $this->getLastModifiedSearchField(),
        ];
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
        // We want procedure_order records (not procedure_report)
        // Exclude entries that are laboratory tests being returned as procedures
        return $this->procedureService->search($openEMRSearchParameters);
    }

    /**
     * Parses an OpenEMR procedure_order record into a FHIR ServiceRequest resource
     *
     * @param array $dataRecord The source OpenEMR data record
     * @param bool $encode Indicates if the returned resource is encoded into a string
     * @return FHIRServiceRequest
     */
    public function parseOpenEMRRecord($dataRecord = array(), $encode = false)
    {
        $serviceRequest = new FHIRServiceRequest();

        // Meta
        $meta = new FHIRMeta();
        $meta->setVersionId('1');
        $meta->addProfile('http://hl7.org/fhir/us/core/StructureDefinition/us-core-servicerequest');

        if (!empty($dataRecord['date_ordered'])) {
            $meta->setLastUpdated(UtilsService::getLocalDateAsUTC($dataRecord['date_ordered']));
        } else {
            $meta->setLastUpdated(UtilsService::getDateFormattedAsUTC());
        }
        $serviceRequest->setMeta($meta);

        // ID
        $id = new FHIRId();
        $id->setValue($dataRecord['order_uuid']);
        $serviceRequest->setId($id);

        // Status - map OpenEMR order status to FHIR
        $status = $this->mapOrderStatus($dataRecord['order_activity'] ?? 'active');
        $serviceRequest->setStatus($status);

        // Intent - typically 'order' for orders placed
        $serviceRequest->setIntent('order');

        // Category - USCDI v5 requirement for order type
        $category = $this->mapOrderTypeToCategory($dataRecord['procedure_type'] ?? '');
        if ($category) {
            $serviceRequest->addCategory($category);
        }

        // Code - the procedure/test being ordered
        $code = $this->buildOrderCode($dataRecord);
        $serviceRequest->setCode($code);

        // Subject (patient) - required
        if (!empty($dataRecord['patient']['uuid'])) {
            $serviceRequest->setSubject(
                UtilsService::createRelativeReference('Patient', $dataRecord['patient']['uuid'])
            );
        } else {
            $serviceRequest->setSubject(UtilsService::createDataMissingExtension());
        }

        // Encounter context
        if (!empty($dataRecord['encounter']['uuid'])) {
            $serviceRequest->setEncounter(
                UtilsService::createRelativeReference('Encounter', $dataRecord['encounter']['uuid'])
            );
        }

        // Authored date - when the order was created
        if (!empty($dataRecord['date_ordered'])) {
            $serviceRequest->setAuthoredOn(
                new FHIRDateTime(UtilsService::getLocalDateAsUTC($dataRecord['date_ordered']))
            );
        }

        // Requester - the provider who ordered
        if (!empty($dataRecord['provider']['uuid'])) {
            $serviceRequest->setRequester(
                UtilsService::createRelativeReference('Practitioner', $dataRecord['provider']['uuid'])
            );
        }

        // Performer - the lab/facility performing the order
        if (!empty($dataRecord['lab']['uuid'])) {
            $serviceRequest->addPerformer(
                UtilsService::createRelativeReference('Organization', $dataRecord['lab']['uuid'])
            );
        }

        // ReasonCode - diagnosis/indication for the order
        if (!empty($dataRecord['order_diagnosis'])) {
            $reasonCodes = $this->buildReasonCodes($dataRecord['order_diagnosis']);
            foreach ($reasonCodes as $reasonCode) {
                $serviceRequest->addReasonCode($reasonCode);
            }
        }

        // Note - any order notes/instructions
        if (!empty($dataRecord['order_notes'])) {
            $note = new FHIRAnnotation();
            $note->setText($dataRecord['order_notes']);
            $serviceRequest->addNote($note);
        }

        if ($encode) {
            return json_encode($serviceRequest);
        }

        return $serviceRequest;
    }

    /**
     * Map OpenEMR order activity/status to FHIR ServiceRequest status
     *
     * @param string $orderActivity OpenEMR order activity value
     * @return string FHIR status code
     */
    private function mapOrderStatus($orderActivity)
    {
        // OpenEMR procedure_order.activity: 0=pending, 1=routed, 2=complete, 3=canceled
        // FHIR status: draft | active | on-hold | revoked | completed | entered-in-error | unknown

        $statusMap = [
            '0' => 'active',      // pending
            '1' => 'active',      // routed
            '2' => 'completed',   // complete
            '3' => 'revoked',     // canceled
            'pending' => 'active',
            'routed' => 'active',
            'complete' => 'completed',
            'completed' => 'completed',
            'canceled' => 'revoked',
            'cancelled' => 'revoked',
        ];

        return $statusMap[strtolower($orderActivity)] ?? 'active';
    }

    /**
     * Map OpenEMR procedure_type to USCDI v5 order category
     *
     * @param string $procedureType OpenEMR procedure_order_title value
     * @return FHIRCodeableConcept|null
     */
    private function mapOrderTypeToCategory($procedureType)
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

        if (!isset($categoryMap[$procedureType])) {
            return null;
        }

        $category = $categoryMap[$procedureType];
        return UtilsService::createCodeableConcept([
            $category['code'] => $category
        ]);
    }

    /**
     * Build the code element for the ServiceRequest
     * Uses procedure_code with fallback to standard_code (LOINC)
     */
    private function buildOrderCode($dataRecord)
    {
        $codesService = new CodeTypesService();
        $codeableConcept = new FHIRCodeableConcept();

        // Primary code from procedure_code
        if (!empty($dataRecord['procedure_code'])) {
            $codeParts = $codesService->parseCode($dataRecord['procedure_code']);
            $system = $codesService->getSystemForCodeType($codeParts['code_type']);

            if (!empty($system)) {
                $description = $codesService->lookup_code_description($dataRecord['procedure_code']);
                $description = !empty($description) ? $description : $dataRecord['procedure_name'];

                $codeableConcept->addCoding(
                    UtilsService::createCoding(
                        $codeParts['code'],
                        $description,
                        $system
                    )
                );
            }
        }

        // Add standard_code (LOINC) if available
        if (!empty($dataRecord['standard_code'])) {
            $description = $dataRecord['procedure_name'] ?? null;
            $codeableConcept->addCoding(
                UtilsService::createCoding(
                    $dataRecord['standard_code'],
                    $description,
                    FhirCodeSystemConstants::LOINC
                )
            );
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
     * Build reason codes from order_diagnosis field
     * Format: "code1:type1;code2:type2"
     */
    private function buildReasonCodes($diagnosisString)
    {
        $reasonCodes = [];
        $codesService = new CodeTypesService();

        $diagnoses = explode(";", $diagnosisString);
        foreach ($diagnoses as $diagnosis) {
            if (empty(trim($diagnosis))) {
                continue;
            }

            $parts = explode(":", $diagnosis);
            if (count($parts) >= 2) {
                $code = trim($parts[0]);
                $codeType = trim($parts[1]);

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
     * Returns the Canonical URIs for US Core Implementation Guide Profiles
     */
    public function getProfileURIs(): array
    {
        return [
            'http://hl7.org/fhir/us/core/StructureDefinition/us-core-servicerequest'
        ];
    }
}
