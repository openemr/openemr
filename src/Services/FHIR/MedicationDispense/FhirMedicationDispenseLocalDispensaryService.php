<?php

/*
 * FhirMedicationDispenseLocalDispensaryService.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\MedicationDispense;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicationDispense;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMedicationDispenseStatus;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDosage;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
use OpenEMR\Services\FHIR\FhirProvenanceService;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\FHIR\IResourceUSCIGProfileService;
use OpenEMR\Services\FHIR\IPatientCompartmentResourceService;
use OpenEMR\Services\FHIR\IFhirExportableResourceService;
use OpenEMR\Services\FHIR\Traits\BulkExportSupportAllOperationsTrait;
use OpenEMR\Services\FHIR\Traits\FhirBulkExportDomainResourceTrait;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\PatientSearchTrait;
use OpenEMR\Services\FHIR\Traits\VersionedProfileTrait;
use OpenEMR\Services\FHIR\UtilsService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Validators\ProcessingResult;

/**
 * FHIR MedicationDispense Local Dispensary Service
 */
class FhirMedicationDispenseLocalDispensaryService extends FhirServiceBase implements
    IResourceUSCIGProfileService,
    IPatientCompartmentResourceService,
    IFhirExportableResourceService
{
    use PatientSearchTrait;
    use FhirServiceBaseEmptyTrait;
    use BulkExportSupportAllOperationsTrait;
    use FhirBulkExportDomainResourceTrait;
    use VersionedProfileTrait;

    const USCGI_PROFILE_URI = "http://hl7.org/fhir/us/core/StructureDefinition/us-core-medicationdispense";

    // Status mapping from drug_sales trans_type
    const STATUS_MAPPING = [
        1 => 'completed',      // sale
        2 => 'preparation',    // purchase (not applicable for dispense)
        3 => 'entered-in-error', // return
        4 => 'preparation',    // transfer
        5 => 'cancelled'       // adjustment
    ];

    // Type mapping for dispensing events
    const TYPE_MAPPING = [
        'FF' => 'FF', // Final Fill
        'PF' => 'PF', // Partial Fill
        'EM' => 'EM', // Emergency Fill
        'TF' => 'TF', // Trial Fill
        'RF' => 'RF', // Refill
        'DF' => 'DF'  // Default Fill
    ];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns an array mapping FHIR Resource search parameters to OpenEMR search parameters
     */
    protected function loadSearchParameters(): array
    {
        return [
            'patient' => $this->getPatientContextSearchField(),
            'status' => new FhirSearchParameterDefinition('status', SearchFieldType::TOKEN, ['status']),
            'type' => new FhirSearchParameterDefinition('type', SearchFieldType::TOKEN, ['type']),
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)]),
            '_lastUpdated' => $this->getLastModifiedSearchField()
        ];
    }

    public function getLastModifiedSearchField(): ?FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['bill_date']);
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        $sql = "SELECT
                    ds.uuid,
                    ds.sale_id,
                    ds.drug_id,
                    ds.inventory_id,
                    ds.prescription_id,
                    ds.pid as patient_id,
                    ds.encounter,
                    ds.user,
                    ds.sale_date,
                    ds.quantity,
                    ds.fee,
                    ds.billed,
                    ds.trans_type,
                    ds.notes,
                    ds.bill_date,
                    ds.selector,
                    d.name as drug_name,
                    d.ndc_number,
                    d.drug_code as rxnorm_code,
                    d.form as drug_form,
                    d.size as drug_size,
                    d.unit as drug_unit,
                    d.route as drug_route,
                    di.lot_number,
                    di.expiration,
                    p.uuid as prescription_uuid,
                    pd.uuid as patient_uuid,
                    fe.uuid as encounter_uuid,
                    pr.dosage,
                    pr.route as prescription_route,
                    pr.interval as prescription_interval,
                    pr.refills,
                    pr.note as prescription_note
                FROM drug_sales ds
                LEFT JOIN drugs d ON ds.drug_id = d.drug_id
                LEFT JOIN drug_inventory di ON ds.inventory_id = di.inventory_id
                LEFT JOIN prescriptions p ON ds.prescription_id = p.id
                LEFT JOIN patient_data pd ON ds.pid = pd.pid
                LEFT JOIN form_encounter fe ON ds.encounter = fe.encounter
                LEFT JOIN prescriptions pr ON ds.prescription_id = pr.id
                WHERE ds.trans_type IN (1, 3, 4, 5)"; // Only include sales, returns, transfers, adjustments

        $whereClause = FhirSearchWhereClauseBuilder::build($openEMRSearchParameters);
        $sql .= $whereClause->getFragment();
        $sqlBindArray = $whereClause->getBoundValues();

        $sql .= " ORDER BY ds.sale_date DESC";

        $statementResults = QueryUtils::sqlStatementThrowException($sql, $sqlBindArray);
        $processingResult = new ProcessingResult();
        while ($row = QueryUtils::fetchArrayFromResultSet($statementResults)) {
            $row['uuid'] = UuidRegistry::uuidToString($row['uuid']);
            $row['patient_uuid'] = UuidRegistry::uuidToString($row['patient_uuid']);
            if ($row['prescription_uuid']) {
                $row['prescription_uuid'] = UuidRegistry::uuidToString($row['prescription_uuid']);
            }
            if ($row['encounter_uuid']) {
                $row['encounter_uuid'] = UuidRegistry::uuidToString($row['encounter_uuid']);
            }

            $processingResult->addData($row);
        }

        return $processingResult;
    }

    /**
     * Parses an OpenEMR data record, returning the equivalent FHIR MedicationDispense Resource
     */
    public function parseOpenEMRRecord($dataRecord = [], $encode = false)
    {
        $medicationDispenseResource = new FHIRMedicationDispense();

        $meta = new FHIRMeta();
        $meta->setVersionId('1');
        $meta->setLastUpdated(UtilsService::getLocalDateAsUTC($dataRecord['bill_date'] ?? $dataRecord['sale_date']));
        $meta->addProfile(self::USCGI_PROFILE_URI);
        $medicationDispenseResource->setMeta($meta);

        // Resource ID
        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $medicationDispenseResource->setId($id);

        // Status (required) - map from trans_type
        $status = $this->mapStatus($dataRecord['trans_type']);
        $medicationDispenseResource->setStatus($status);

        // TODO: @adunsulag I think we want to point this to the Medication resource
        // Medication (required) - prefer RxNorm, fallback to NDC, then text
        $medication = $this->mapMedication($dataRecord);
        $medicationDispenseResource->setMedicationCodeableConcept($medication);

        // Subject (required) - reference to patient
        if (!empty($dataRecord['patient_uuid'])) {
            $medicationDispenseResource->setSubject(UtilsService::createRelativeReference('Patient', $dataRecord['patient_uuid']));
        } else {
            $fhirReference = new FHIRReference();
            $fhirReference->addExtension(UtilsService::createDataMissingExtension());
            $medicationDispenseResource->setSubject($fhirReference);
        }

        // Context (mustSupport) - reference to encounter
        if (!empty($dataRecord['encounter_uuid'])) {
            $medicationDispenseResource->setContext(UtilsService::createRelativeReference('Encounter', $dataRecord['encounter_uuid']));
        }

        // AuthorizingPrescription (mustSupport) - reference to prescription
        if (!empty($dataRecord['prescription_uuid'])) {
            $authorizingPrescription = UtilsService::createRelativeReference('MedicationRequest', $dataRecord['prescription_uuid']);
            $medicationDispenseResource->addAuthorizingPrescription($authorizingPrescription);
        }

        // Type (mustSupport) - type of dispensing event
        $type = $this->mapDispenseType($dataRecord);
        $medicationDispenseResource->setType($type);

        // Quantity (mustSupport)
        if (!empty($dataRecord['quantity'])) {
            $quantity = UtilsService::createQuantity($dataRecord['quantity'], $dataRecord['drug_unit'] ?? 'unit', $dataRecord['drug_unit'] ?? 'unit');
            $medicationDispenseResource->setQuantity($quantity);
        }

        // WhenHandedOver (mustSupport) - when medication was dispensed
        if (!empty($dataRecord['sale_date'])) {
            $whenHandedOver = new FHIRDateTime();
            $whenHandedOver->setValue(UtilsService::getLocalDateAsUTC($dataRecord['sale_date']));
            $medicationDispenseResource->setWhenHandedOver($whenHandedOver);
        }

        // Dosage instructions if available
        if (!empty($dataRecord['dosage']) || !empty($dataRecord['prescription_route'])) {
            $dosage = $this->mapDosageInstruction($dataRecord);
            if ($dosage) {
                $medicationDispenseResource->addDosageInstruction($dosage);
            }
        }

        // Note if available
        if (!empty($dataRecord['notes'])) {
            $note = UtilsService::createAnnotation($dataRecord['notes']);
            $medicationDispenseResource->addNote($note);
        }

        if ($encode) {
            return json_encode($medicationDispenseResource);
        } else {
            return $medicationDispenseResource;
        }
    }

    /**
     * Map trans_type to FHIR status
     */
    private function mapStatus(int $transType): string
    {
        return self::STATUS_MAPPING[$transType] ?? 'unknown';
    }

    /**
     * Map medication information to FHIR CodeableConcept
     */
    private function mapMedication(array $dataRecord): FHIRCodeableConcept
    {
        $medicationConcept = new FHIRCodeableConcept();

        // Prefer RxNorm code
        if (!empty($dataRecord['rxnorm_code'])) {
            $rxnormCoding = new FHIRCoding();
            $rxnormCoding->setSystem(FhirCodeSystemConstants::RXNORM);
            $rxnormCoding->setCode($dataRecord['rxnorm_code']);
            $rxnormCoding->setDisplay($dataRecord['drug_name']);
            $medicationConcept->addCoding($rxnormCoding);
        }

        // Add NDC code if available
        if (!empty($dataRecord['ndc_number'])) {
            $ndcCoding = new FHIRCoding();
            $ndcCoding->setSystem('http://hl7.org/fhir/sid/ndc');
            $ndcCoding->setCode($dataRecord['ndc_number']);
            $ndcCoding->setDisplay($dataRecord['drug_name']);
            $medicationConcept->addCoding($ndcCoding);
        }

        // Always include text description
        if (!empty($dataRecord['drug_name'])) {
            $medicationConcept->setText($dataRecord['drug_name']);
        }

        return $medicationConcept;
    }

    /**
     * Map dispense type to FHIR CodeableConcept
     */
    private function mapDispenseType(array $dataRecord): FHIRCodeableConcept
    {
        $typeConcept = new FHIRCodeableConcept();

        // Default to Final Fill if no specific type
        $typeCode = 'FF';

        // Could be extracted from selector field or other business logic
        if (!empty($dataRecord['selector'])) {
            // Parse selector for type information if needed
            $typeCode = 'FF'; // Default for now
        }

        $typeCoding = new FHIRCoding();
        $typeCoding->setSystem('http://terminology.hl7.org/ValueSet/v3-ActPharmacySupplyType');
        $typeCoding->setCode($typeCode);
        $typeCoding->setDisplay($this->getTypeDisplay($typeCode));
        $typeConcept->addCoding($typeCoding);

        return $typeConcept;
    }

    /**
     * Get display text for dispense type code
     */
    private function getTypeDisplay(string $code): string
    {
        $displays = [
            'FF' => 'Final Fill',
            'PF' => 'Partial Fill',
            'EM' => 'Emergency Fill',
            'TF' => 'Trial Fill',
            'RF' => 'Refill',
            'DF' => 'Default Fill'
        ];

        return $displays[$code] ?? 'Final Fill';
    }

    /**
     * Map dosage instruction information
     */
    private function mapDosageInstruction(array $dataRecord): ?FHIRDosage
    {
        if (empty($dataRecord['dosage']) && empty($dataRecord['prescription_route'])) {
            return null;
        }

        $dosage = new FHIRDosage();

        // Text instruction
        if (!empty($dataRecord['dosage'])) {
            $dosage->setText($dataRecord['dosage']);
        }

        // Route if available
        if (!empty($dataRecord['prescription_route']) || !empty($dataRecord['drug_route'])) {
            $route = $dataRecord['prescription_route'] ?? $dataRecord['drug_route'];
            $routeConcept = new FHIRCodeableConcept();
            $routeConcept->setText($route);
            $dosage->setRoute($routeConcept);
        }

        return $dosage;
    }

    public function getProfileURIs(): array
    {
        return $this->getProfileForVersions(self::USCGI_PROFILE_URI, ['', '7.0.0', '8.0.0']);
    }

    /**
     * Returns the supported categories for this service
     */
    public function getSupportsCategory(): array
    {
        return ['local-dispensary'];
    }

    /**
     * Creates the Provenance resource for the equivalent FHIR Resource
     */
    public function createProvenanceResource($dataRecord, $encode = false)
    {
        if (!($dataRecord instanceof FHIRMedicationDispense)) {
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

    /**
     * Inserts an OpenEMR record into the system.
     * Not supported for MedicationDispense as they represent historical events.
     */
    protected function insertOpenEMRRecord($openEmrRecord): ProcessingResult
    {
        $processingResult = new ProcessingResult();
        $processingResult->setValidationMessages(["MedicationDispense" => "Insert operation not supported for dispense records"]);
        return $processingResult;
    }

    /**
     * Updates an OpenEMR record in the system.
     * Not supported for MedicationDispense as they represent historical events.
     */
    protected function updateOpenEMRRecord($fhirResourceId, $updatedOpenEMRRecord): ProcessingResult
    {
        $processingResult = new ProcessingResult();
        $processingResult->setValidationMessages(["MedicationDispense" => "Update operation not supported for dispense records"]);
        return $processingResult;
    }
}
