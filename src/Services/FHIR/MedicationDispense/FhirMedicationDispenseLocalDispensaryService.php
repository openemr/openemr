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

use OpenEMR\FHIR\DomainModels\OpenEMRFHIRDosage;
use OpenEMR\FHIR\DomainModels\OpenEMRFHIRTiming;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicationDispense;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMedicationDispenseStatus;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDosage;
use OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationDispense\FHIRMedicationDispensePerformer;
use OpenEMR\FHIR\R4\FHIRResource\FHIRTiming;
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\DrugSalesService;
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
use OpenEMR\Services\FHIR\FhirOrganizationService;
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
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Validators\ProcessingResult;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDosage\FHIRDosageDoseAndRate;

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

    private DrugSalesService $drugSalesService;

    private CodeTypesService $codeTypesService;

    private FhirOrganizationService $fhirOrganizationService;

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

    public function getCodeTypesService(): CodeTypesService
    {
        if (!isset($this->codeTypesService)) {
            $this->codeTypesService = new CodeTypesService();
        }
        return $this->codeTypesService;
    }

    public function setCodeTypesService(CodeTypesService $service): void
    {
        $this->codeTypesService = $service;
    }

    public function getFhirOrganizationService(): FhirOrganizationService
    {
        if (!isset($this->fhirOrganizationService)) {
            $this->fhirOrganizationService = new FhirOrganizationService();
        }
        return $this->fhirOrganizationService;
    }

    public function setFhirOrganizationService(FhirOrganizationService $service): void
    {
        $this->fhirOrganizationService = $service;
    }

    public function getDrugSalesService(): DrugSalesService
    {
        if (!isset($this->drugSalesService)) {
            $this->drugSalesService = new DrugSalesService();
        }
        return $this->drugSalesService;
    }

    public function setDrugSalesService(DrugSalesService $service): void
    {
        $this->drugSalesService = $service;
    }

    public function getDispensedMedicationSummaryForEncounter(string $patientUuid, string $encounterUuid): array
    {
        $medications = [];
        $meds = $this->getAll(['patient' => $patientUuid, 'context' => $encounterUuid]);
        if ($meds->hasData()) {
            /**
             * @var FHIRMedicationDispense $fhirResource
             */
            foreach ($meds->getData() as $fhirResource) {
                /**
                 * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicationDispense $fhirResource
                 */
                // dosageInstructions may be text or coded, so we will try to get text first
                // and if not present, look for coded dosage instruction
                // for coded we will create a text narrative
                // Dose: {value} {unit} {timing} {route} {additionalInstructions}
                $instructions = [];
                foreach ($fhirResource->getDosageInstruction() as $fhirDosage) {
                    $dosageText = $fhirDosage->getText();
                    if ($dosageText !== null) {
                        $dosageText = 'Dose: ';
                        $doseQuantity = $fhirDosage->getDoseAndRate()[0]->getDoseQuantity();
                        if ($doseQuantity) {
                            $dosageText .= $doseQuantity->getValue() . ' ' . $doseQuantity->getUnit() . ' ';
                        }
                        $timing = $fhirDosage->getTiming();
                        if ($timing !== null) {
                            $dosageText .= ' ' . $timing->getCode()->getText() . ' ';
                        }
                        $route = $fhirDosage->getRoute();
                        if ($route) {
                            $dosageText .= ' via ' . $route->getCoding()[0]->getDisplay() . ' ';
                        }
                        $additionalInstructions = $fhirDosage->getAdditionalInstruction();
                        if ($additionalInstructions) {
                            $dosageText .= ' ' . $additionalInstructions[0]->getText() . ' ';
                        }
                    }
                    $instructions[] = trim($dosageText);
                }

                $medicationName = $fhirResource->getMedicationCodeableConcept()->getText() ?? xl("Unknown Medication");
                $quantity = $fhirResource->getQuantity()->getValue();
                $quantityUnits = $fhirResource->getQuantity()->getUnit();
                $dosageInstructions = implode("\n", $instructions);
                $medications[] = [
                    'medicationName' => $medicationName,
                    'quantity' => $quantity,
                    'quantityUnits' => $quantityUnits,
                    'dispensedDate' => $fhirResource->getWhenHandedOver()->getValue(),
                    'supplyType' => $fhirResource->getType() ? $fhirResource->getType()->getCoding()[0]->getDisplay() : '',
                    'dosageInstructions' => $dosageInstructions,
                    'status' => $fhirResource->getStatus() ?? 'unknown'
                ];
            }
        } else {
            if (!empty($meds->getValidationMessages())) {
                // log validation messages
                $this->getSystemLogger()->warning("FhirMedicationDispenseLocalDispensaryService->getDispensedMedicationSummaryForEncounter() validation messages", [
                    'messages' => $meds->getValidationMessages()
                ]);
            }
            if ($meds->getInternalErrors()) {
                // log internal errors
                $this->getSystemLogger()->error("FhirMedicationDispenseLocalDispensaryService->getDispensedMedicationSummaryForEncounter() internal errors", [
                    'errors' => $meds->getInternalErrors()
                ]);
            }
        }
        return $medications;
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
            // 'encounter' search parameter, not part of US-Core but used internally
            'context' => new FhirSearchParameterDefinition('context', SearchFieldType::TOKEN, [new ServiceField('encounter_uuid', ServiceField::TYPE_UUID)]),
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)]),
            '_lastUpdated' => $this->getLastModifiedSearchField()
        ];
    }

    public function getPatientContextSearchField(): FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('patient', SearchFieldType::REFERENCE, [new ServiceField('patient_uuid', ServiceField::TYPE_UUID)]);
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
        // we only want sales transactions (which in this case is a dispense event)
        $openEMRSearchParameters['trans_type'] = new TokenSearchField("trans_type", ["1"]);
        return $this->getDrugSalesService()->search($openEMRSearchParameters);
    }

    /**
     * Parses an OpenEMR data record, returning the equivalent FHIR MedicationDispense Resource
     */
    public function parseOpenEMRRecord($dataRecord = [], $encode = false): FHIRMedicationDispense|string
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

        // TODO: @adunsulag I think we may want to point this to the Medication resource
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

        // performer (mustSupport) - who dispensed the medication
        if (!empty($dataRecord['dispenser_uuid'])) {
            $performerReference = UtilsService::createRelativeReference('Practitioner', $dataRecord['dispenser_uuid']);
            $performer = new FHIRMedicationDispensePerformer();
            $performer->setActor($performerReference);
            $medicationDispenseResource->addPerformer($performer);
        }

        $fhirOrganizationService = $this->getFhirOrganizationService();
        $primaryBusinessEntity = $fhirOrganizationService->getPrimaryBusinessEntityReference();
        if (!empty($primaryBusinessEntity)) {
            $performer = new FHIRMedicationDispensePerformer();
            $performer->setActor($primaryBusinessEntity);
            $medicationDispenseResource->addPerformer($performer);
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
        $unit = $dataRecord['drug_form_title'] ?? '';
        if (!empty($dataRecord['quantity'])) {
            $quantity = UtilsService::createQuantity($dataRecord['quantity'], $unit, $unit);
            $medicationDispenseResource->setQuantity($quantity);
        }

        // WhenHandedOver (mustSupport) - when medication was dispensed
        if (!empty($dataRecord['sale_date'])) {
            $whenHandedOver = new FHIRDateTime();
            $whenHandedOver->setValue(UtilsService::getLocalDateAsUTC($dataRecord['sale_date']));
            $medicationDispenseResource->setWhenHandedOver($whenHandedOver);
        }

        // Dosage instructions if available
        // text -> prescription.drug_dosage_instructions
        // route -> prescription.prescription_route || drug.drug_route
        // timing -> prescription.interval (list_options)
        // doseAndRate -> prescription.dosage
        if (!empty($dataRecord['dosage']) || !empty($dataRecord['prescription_route'])) {
            $dosage = $this->mapDosageInstruction($dataRecord);
            if ($dosage) {
                $medicationDispenseResource->addDosageInstruction($dosage);
            }
        }

        // Note if available
        if (!empty($dataRecord['notes'])) {
            $note = new FHIRAnnotation();
            $note->setText($dataRecord['note']);
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
        $codeTypesService = $this->getCodeTypesService();

        // RxNorm should come first
        if (!empty($dataRecord['rxnorm_code'])) {
            // rxnorm_code will have the RXCUI prefix
            $parsedCode = $codeTypesService->parseCode($dataRecord['rxnorm_code']);
            $system = $codeTypesService->getSystemForCodeType($parsedCode['code_type']);
            $codedesc = $codeTypesService->lookup_code_description($dataRecord['rxnorm_code']);
            $codedesc = !empty($codedesc) ? $codedesc : $dataRecord['drug_name'];
            // per spec coding is not allowed to be empty
            if (!empty($codedesc)) {
                $coding = UtilsService::createCoding($parsedCode['code'], $codedesc, $system);
                $medicationConcept->addCoding($coding);
            }
        }
        // ndc_number does not have the NDC prefix
        if (!empty($dataRecord['ndc_number'])) {
            // if we have the NDC database installed we'll use the code description from there
            $codedesc = $codeTypesService->lookup_code_description(CodeTypesService::CODE_TYPE_NDC . ":" . $dataRecord['ndc_number']);
            $codedesc = !empty($codedesc) ? $codedesc : $dataRecord['drug_name'];
            // per spec coding is not allowed to be empty
            if (!empty($codedesc)) {
                $coding = UtilsService::createCoding($dataRecord['ndc_number'], $codedesc, FhirCodeSystemConstants::NDC);
                $medicationConcept->addCoding($coding);
            }
        }
        // if we have no concepts or a name... bad data and we'll create a DataAbsentUnknown
        if (empty($medicationConcept->getCoding())) {
            // No codes found, use the title with no coding
            if (!empty($dataRecord['drug_name'])) {
                $medicationConcept->setText($dataRecord['drug_name']);
            } else {
                $medicationConcept = UtilsService::createDataAbsentUnknownCodeableConcept();
            }
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

        // Default to First Fill if no specific type
        $typeCode = 'FF';
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
            'FF' => 'First Fill',
            'EM' => 'Emergency Fill',
            'TF' => 'Trial Fill',
            'RF' => 'Refill'
        ];

        return $displays[$code] ?? 'First Fille';
    }

    /**
     * Map dosage instruction information
     */
    private function mapDosageInstruction(array $dataRecord): ?FHIRDosage
    {
        if (empty($dataRecord['dosage']) && empty($dataRecord['prescription_route'])) {
            return null;
        }

        $dosage = new OpenEMRFHIRDosage();
        // need to support text 0..1, timing 0..1, route 0..1, doseAndRate 0..*

        // Text instruction (prescription will set dosage to be a single numeric value even though its a textfield)
        // simple prescriptions will put the entire SIG in the dosage field
        if (!empty($dataRecord['dosage']) && !is_numeric($dataRecord['dosage'])) {
            $dosage->setText($dataRecord['dosage']);
            // TODO: @adunsulag if we have a SIG text should we just return it even if we might have some structured data?
        }
        // Dose and Rate
        if (!empty($dataRecord['interval_codes'])) {
            $intervalConcept = UtilsService::createCodeableConcept([
                $dataRecord['interval_codes'] => [
                    'code' => $dataRecord['interval_codes'],
                    'description' => $dataRecord['interval_notes'],
                    'system' => FhirCodeSystemConstants::HL7_TIMING_ABBREVIATION
                ]
            ]);
            $intervalConcept->setText($dataRecord['interval_notes'] ?? $dataRecord['interval_title']);
            $fhirTiming = new OpenEMRFHIRTiming();
            $fhirTiming->setCode($intervalConcept);
            $dosage->setTiming($fhirTiming);
        } else if (!empty($dataRecord['interval_notes'])) {
            // if we have notes but no corresponding code, just set the text
            $intervalConcept = new FHIRCodeableConcept();
            $intervalConcept->setText($dataRecord['interval_notes']);
            $fhirTiming = new OpenEMRFHIRTiming();
            $fhirTiming->setCode($intervalConcept);
            $dosage->setTiming($fhirTiming);
        }

        if (!empty($dataRecord['prescription_drug_size']) && is_numeric($dataRecord['prescription_drug_size'])) {
            $quantity = intval($dataRecord['prescription_drug_size']); // should be an integer value for dosage
            $doseQuantity = UtilsService::createQuantity($quantity, $dataRecord['unit_title'] ?? '', $dataRecord['unit_title'] ?? '');
            $doseAndRate = new FHIRDosageDoseAndRate();
            $doseAndRate->setDoseQuantity($doseQuantity);
            $dosage->addDoseAndRate($doseAndRate);
        }

        // Route if available
        if (!empty($dataRecord['route_codes'])) {
            $codeTypesService = $this->getCodeTypesService();
            $parsedCodes = $codeTypesService->parseCodesIntoCodeableConcepts($dataRecord['route_codes']);
            $route = UtilsService::createCodeableConcept($parsedCodes);
        } else {
            $route = new FHIRCodeableConcept();
        }
        $route->setText($dataRecord['route_title']);
        $dosage->setRoute($route);

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
