<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\DomainModels\OpenEMRFHIRDateTime;
use OpenEMR\FHIR\DomainModels\OpenEMRFHIRDosage;
use OpenEMR\FHIR\DomainModels\OpenEMRFHIRTiming;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCondition;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicationRequest;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRProvenance;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDosage\FHIRDosageDoseAndRate;
use OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationRequest\FHIRMedicationRequestDispenseRequest;
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\FHIR\Enum\FHIRMedicationIntentEnum;
use OpenEMR\Services\FHIR\Enum\FHIRMedicationStatusEnum;
use OpenEMR\Services\FHIR\Traits\BulkExportSupportAllOperationsTrait;
use OpenEMR\Services\FHIR\Traits\FhirBulkExportDomainResourceTrait;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\PatientSearchTrait;
use OpenEMR\Services\FHIR\Traits\VersionedProfileTrait;
use OpenEMR\Services\PrescriptionService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Validators\ProcessingResult;

/**
 * NOTE: when making modifications to this class follow all the guidance in the US Core Medication List guidance
 * here: https://www.hl7.org/fhir/us/core/medication-list-guidance.html
 *
 * Class FhirMedicationRequestService
 * @package OpenEMR\Services\FHIR
 */
class FhirMedicationRequestService extends FhirServiceBase implements IResourceUSCIGProfileService, IFhirExportableResourceService, IPatientCompartmentResourceService
{
    use PatientSearchTrait;
    use FhirServiceBaseEmptyTrait;
    use BulkExportSupportAllOperationsTrait;
    use FhirBulkExportDomainResourceTrait;
    use VersionedProfileTrait;

    /**
     * @deprecated use FHIRMedicationStatusEnum::STATUS_COMPLETED
     */
    const MEDICATION_REQUEST_STATUS_COMPLETED = "completed";
    /**
     * @deprecated use FHIRMedicationStatusEnum::STATUS_STOPPED
     */
    const MEDICATION_REQUEST_STATUS_STOPPED = "stopped";
    /**
     * @deprecated use FHIRMedicationStatusEnum::STATUS_ACTIVE
     */
    const MEDICATION_REQUEST_STATUS_ACTIVE = "active";
    /**
     * @deprecated use FHIRMedicationStatusEnum::STATUS_UNKNOWN
     */
    const MEDICATION_REQUEST_STATUS_UNKNOWN = "unknown";

    /**
     * @deprecated use FHIRMedicationStatusEnum::STATUS_PLAN
     */
    const MEDICATION_REQUEST_INTENT_PLAN = "plan";
    /**
     * @deprecated use FHIRMedicationStatusEnum::STATUS_ORDER
     */
    const MEDICATION_REQUEST_INTENT_ORDER = "order";

    const MEDICATION_REQUEST_REPORTED_PRIMARY_SOURCE = false;

    /**
     * Includes requests for medications to be administered or consumed by the patient in their home (this would include long term care or nursing homes, hospices, etc.)
     */
    const MEDICATION_REQUEST_CATEGORY_COMMUNITY = "community";

    const MEDICATION_REQUEST_CATEGORY_COMMUNITY_TITLE = "Home/Community";


    const USCGI_PROFILE_URI = "http://hl7.org/fhir/us/core/StructureDefinition/us-core-medicationrequest";
    /**
     * @deprecated use USCGI_PROFILE_URI
     */
    const PROFILE_URI = self::USCGI_PROFILE_URI;

    /**
     * @var PrescriptionService
     */
    private PrescriptionService $prescriptionService;

    private CodeTypesService $codeTypesService;

    private FhirOrganizationService $fhirOrganizationService;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns an array mapping FHIR Patient Resource search parameters to OpenEMR Patient search parameters
     * @return array The search parameters
     */
    protected function loadSearchParameters(): array
    {
        return  [
            'patient' => $this->getPatientContextSearchField(),
            'intent' => new FhirSearchParameterDefinition('intent', SearchFieldType::TOKEN, ['intent']),
            'status' => new FhirSearchParameterDefinition('status', SearchFieldType::TOKEN, ['status']),
            '_id' => new FhirSearchParameterDefinition('uuid', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)]),
            '_lastUpdated' => $this->getLastModifiedSearchField()
        ];
    }

    public function getLastModifiedSearchField(): ?FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['date_modified']);
    }

    /**
     * Parses an OpenEMR prescription record, returning the equivalent FHIR Patient Resource
     *
     * @param array $dataRecord The source OpenEMR data record
     * @param boolean $encode Indicates if the returned resource is encoded into a string. Defaults to false.
     * @return FHIRMedicationRequest|string
     */
    public function parseOpenEMRRecord($dataRecord = [], $encode = false): FHIRMedicationRequest|string
    {
        $medRequestResource = new FHIRMedicationRequest();

        // meta required 1..1
        $this->populateMeta($medRequestResource, $dataRecord);

        // id required 1..1
        $this->populateId($medRequestResource, $dataRecord);

        // status required 1..1
        $this->populateStatus($medRequestResource, $dataRecord);

        // intent required 1..1
        $this->populateIntent($medRequestResource, $dataRecord);

        // category:us-core must support
        $this->populateCategory($medRequestResource, $dataRecord);

        // reported must support
        // we will treat everything as a primary source as OpenEMR has no way of differentiating right now primary versus secondary.
        $this->populateReported($medRequestResource, $dataRecord);

        // medication[x] required 1..1
        /**
         * US Core Requirements
         * The MedicationRequest resources can represent a medication using either a code, or reference a Medication resource.
         * When referencing a Medication resource, the resource may be contained or an external resource.
         * The server systems are not required to support both a code and a reference, but SHALL support at least one of these methods.
         * If an external reference to Medication is used, the server SHALL support the _include parameter for searching this element.
         * The client application SHALL support all methods.
         *
         * NOTE: for our requirements we support ONLY the medicationCodeableConcept requirement ie code option and NOT the
         * embedded medication.
         */
        $this->populateMedication($medRequestResource, $dataRecord);

        // subject required 1..1 DAR if not there
        $this->populateSubject($medRequestResource, $dataRecord);

        // encounter must support 0..1
        $this->populateEncounter($medRequestResource, $dataRecord);

        // authoredOn must support 0..1
        $this->populateAuthoredOn($medRequestResource, $dataRecord);

        // requester required 0..1
        $this->populateRequestor($medRequestResource, $dataRecord);

        // must support
        // reasonCode, reasonReference, dosageInstruction.timing,
        // dosageInstruction.doseAndRate,
        // dosageInstruction.doseAndRate.doseQuantity,
        // dispenseRequest,
        // dispenseRequest.numberOfRepeatsAllowed,
        // dispenseRequest.quantity,
        // MedicationRequest.extension:medicationAdherence
        $this->populateReasonCodeAndReference($medRequestResource, $dataRecord);
        $this->populateDosageInstruction($medRequestResource, $dataRecord);
        $this->populateDispenseRequest($medRequestResource, $dataRecord);
        $this->populateMedicationAdherenceExtension($medRequestResource, $dataRecord);

        // optional
        $this->populateNote($medRequestResource, $dataRecord);

        if ($encode) {
            return json_encode($medRequestResource);
        } else {
            return $medRequestResource;
        }
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     *
     * @param array<string, ISearchField> $openEMRSearchParameters OpenEMR search fields
     * @return ProcessingResult
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        return $this->getPrescriptionService()->getAll($openEMRSearchParameters);
    }

    public function createProvenanceResource($dataRecord = [], $encode = false): FHIRProvenance|string
    {
        if (!($dataRecord instanceof FHIRMedicationRequest)) {
            throw new \BadMethodCallException("Data record should be correct instance class");
        }
        $fhirProvenanceService = new FhirProvenanceService();
        $fhirProvenance = $fhirProvenanceService->createProvenanceForDomainResource($dataRecord, $dataRecord->getRequester());
        if ($encode) {
            return json_encode($fhirProvenance);
        } else {
            return $fhirProvenance;
        }
    }

    /**
     * Returns the Canonical URIs for the FHIR resource for each of the US Core Implementation Guide Profiles that the
     * resource implements.  Most resources have only one profile, but several like DiagnosticReport and Observation
     * has multiple profiles that must be conformed to.
     * @see https://www.hl7.org/fhir/us/core/CapabilityStatement-us-core-server.html for the list of profiles
     * @return string[]
     */
    public function getProfileURIs(): array
    {
        return $this->getProfileForVersions(self::USCGI_PROFILE_URI, $this->getSupportedVersions());
    }

    public function populateReasonCodeAndReference(FHIRMedicationRequest $medRequestResource, array $dataRecord): void
    {
        if (empty($dataRecord['puuid'])) {
            return;
        }
        if (!empty($dataRecord['diagnosis'])) {
            $codes = $this->getCodeTypesService()->parseCodesIntoCodeableConcepts($dataRecord['diagnosis']);
            $medRequestResource->addReasonCode(UtilsService::createCodeableConcept($codes, FhirCodeSystemConstants::SNOMED_CT, ''));
        }
        // TODO: if we want a linkage to the Condition resource we need to implement that in OpenEMR first
    }

    public function populateDosageInstruction(FHIRMedicationRequest $medRequestResource, array $dataRecord): void
    {
        if (empty($dataRecord['drug_dosage_instructions']) && empty($dataRecord['dosage']) && empty($dataRecord['prescription_route'])) {
            return;
        }

        $dosage = new OpenEMRFHIRDosage();
        // need to support text 0..1, timing 0..1, route 0..1, doseAndRate 0..*

        // Text instruction (prescription will set dosage to be a single numeric value even though its a textfield)
        // simple prescriptions will put the entire SIG in the dosage field
        $dosageInstructions = $dataRecord['drug_dosage_instructions'] ?? $dataRecord['dosage'];
        if (!empty($dosageInstructions) && !is_numeric($dosageInstructions)) {
            $dosage->setText($dosageInstructions);
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
        if (!empty($dataRecord['route_codes']) || !empty($dataRecord['route_title'])) {
            // we only add the route if we have something to put in there
            if (!empty($dataRecord['route_codes'])) {
                $codeTypesService = $this->getCodeTypesService();
                $parsedCodes = $codeTypesService->parseCodesIntoCodeableConcepts($dataRecord['route_codes']);
                $route = UtilsService::createCodeableConcept($parsedCodes);
            } else {
                $route = new FHIRCodeableConcept();
            }
            $route->setText($dataRecord['route_title']);
            $dosage->setRoute($route);
        }

        $medRequestResource->addDosageInstruction($dosage);
    }

    public function populateDispenseRequest(FHIRMedicationRequest $medRequestResource, array $dataRecord): void
    {
        $dispenseRequest = new FHIRMedicationRequestDispenseRequest();
        $dispenseRequest->setNumberOfRepeatsAllowed($dataRecord['refills'] ?? 0);
        if (!empty($dataRecord['quantity']) && is_numeric($dataRecord['quantity'])) {
            $quantity = intval($dataRecord['quantity']);
            $dispenseRequest->setQuantity(UtilsService::createQuantity(
                $quantity,
                $dataRecord['unit_title'] ?? '',
                $dataRecord['unit_title'] ?? ''
            ));
        }
        $medRequestResource->setDispenseRequest($dispenseRequest);
    }

    public function populateMedicationAdherenceExtension(FHIRMedicationRequest $medRequestResource, array $dataRecord): void
    {
        // INFERNO doesn't like data absence reasons on the base types even though FHIR allows it.
        // so we are going to skip adding the extension if ANY of the required elements are missing
        if (
            empty($dataRecord['medication_adherence'])
            || empty($dataRecord['medication_adherence_date_asserted'])
            || empty($dataRecord['medication_adherence_information_source'])
        ) {
            return;
        }
        $codeTypeService = $this->getCodeTypesService();
        $extension = new FHIRExtension();
        $extension->setUrl("http://hl7.org/fhir/us/core/StructureDefinition/us-core-medication-adherence");
        $adherenceExtension = new FHIRExtension();
        $adherenceExtension->setUrl("medicationAdherence");

        if (!empty($dataRecord['medication_adherence']) && !empty($dataRecord['medication_adherence_title'])) {
            $parsedCode = $codeTypeService->parseCode($dataRecord['medication_adherence_codes']);
            $concept = UtilsService::createCodeableConcept([
                $parsedCode['code'] => [
                    'code' => $parsedCode['code'],
                    'description' => $dataRecord['medication_adherence_title'],
                    'system' => $codeTypeService->getSystemForCodeType($parsedCode['code_type'])
                ]
            ]);
            $adherenceExtension->setValueCodeableConcept($concept);
        } else {
            $adherenceExtension->setValueCodeableConcept(UtilsService::createDataAbsentUnknownCodeableConcept());
        }
        $extension->addExtension($adherenceExtension);

        $dateExtension = new FHIRExtension();
        $dateExtension->setUrl("dateAsserted");
        $dateAsserted = $dataRecord['medication_adherence_date_asserted'];
        // empty date
        if ('0000-00-00 00:00:00' !== $dateAsserted) {
            $formattedDate = UtilsService::getLocalDateAsUTC($dataRecord['medication_adherence_date_asserted']);
            $dateExtension->setValueDateTime($formattedDate);
            $extension->addExtension($dateExtension);
        }

        $sourceExtension = new FHIRExtension();
        $sourceExtension->setUrl("informationSource");
        if (!empty($dataRecord['medication_adherence_information_source']) && !empty($dataRecord['medication_adherence_information_source_title'])) {
            $parsedCode = $codeTypeService->parseCode($dataRecord['medication_adherence_information_source']);
            $concept = UtilsService::createCodeableConcept([
                $parsedCode['code'] => [
                    'code' => $parsedCode['code'],
                    'description' => $dataRecord['medication_adherence_information_source_title'],
                    'system' => $codeTypeService->getSystemForCodeType($parsedCode['code_type'])
                ]
            ]);
            $sourceExtension->setValueCodeableConcept($concept);
        } else {
            $sourceExtension->setValueCodeableConcept(UtilsService::createDataAbsentUnknownCodeableConcept());
        }
        $extension->addExtension($sourceExtension);
        $medRequestResource->addExtension($extension);
    }

    public function populateReported(FHIRMedicationRequest $medRequestResource, array $dataRecord)
    {
        $medRequestResource->setReportedBoolean(self::MEDICATION_REQUEST_REPORTED_PRIMARY_SOURCE);
        if ('1' === ($dataRecord['isReportedRecord'] ?? 0)) {
            // non-primary source
            $medRequestResource->setReportedBoolean(true);
        }
//        // TODO: @adunsulag we fall back to the organization if no reporter is set, but we may want to revisit this logic later
//        if (!empty($dataRecord['reporter_type'])) {
//            $resourceType = match ($dataRecord['reporter_type_table_name']) {
//                'user' => 'Practitioner',
//                'patient_data' => 'Patient',
//                'facility' => 'Organization',
//                default => null,
//            };
//            if (!empty($resourceType) && !empty($dataRecord['reporter_uuid'])) {
//                $medRequestResource->setReportedReference(UtilsService::createRelativeReference($resourceType, $dataRecord['reporter_uuid']));
//            }
//        } else {
//            // reporter needs to be the primary organization
//            $orgService = $this->getFhirOrganizationService();
//            $medRequestResource->setReportedReference($orgService->getPrimaryBusinessEntityReference());
//        }
    }

    public function populateSubject(FHIRMedicationRequest $medRequestResource, array $dataRecord)
    {
        if (!empty($dataRecord['puuid'])) {
            $medRequestResource->setSubject(UtilsService::createRelativeReference('Patient', $dataRecord['puuid']));
        } else {
            $fhirReference = new FHIRReference();
            $fhirReference->addExtension(UtilsService::createDataMissingExtension());
            $medRequestResource->setSubject($fhirReference);
        }
    }

    public function populateEncounter(FHIRMedicationRequest $medRequestResource, array $dataRecord)
    {
        if (!empty($dataRecord['euuid'])) {
            $medRequestResource->setEncounter(UtilsService::createRelativeReference("Encounter", $dataRecord['euuid']));
        }
    }

    public function populateAuthoredOn(FHIRMedicationRequest $medRequestResource, array $dataRecord)
    {
        if (!empty($dataRecord['date_added'])) {
            $authored_on = new FHIRDateTime();
            $authored_on->setValue(UtilsService::getLocalDateAsUTC($dataRecord['date_added']));
            $medRequestResource->setAuthoredOn($authored_on);
        }
    }

    public function populateRequestor(FHIRMedicationRequest $medRequestResource, array $dataRecord)
    {
        if (!empty($dataRecord['pruuid'])) {
            $medRequestResource->setRequester(UtilsService::createRelativeReference('Practitioner', $dataRecord['pruuid']));
        } else {
            // if we have no practitioner we need to default it to the organization
            $fhirOrgService = new FhirOrganizationService();
            $medRequestResource->setRequester($fhirOrgService->getPrimaryBusinessEntityReference());
        }
    }

    public function populateMedication(FHIRMedicationRequest $medRequestResource, array $dataRecord)
    {
        if (!empty($dataRecord['drugcode'])) {
            $rxnormCode = UtilsService::createCodeableConcept($dataRecord['drugcode'], FhirCodeSystemConstants::RXNORM);
            $medRequestResource->setMedicationCodeableConcept($rxnormCode);
        } else if (!empty($dataRecord['drug'])) {
            $textOnlyCode = new FHIRCodeableConcept();
            $textOnlyCode->setText($dataRecord['drug']);
            $medRequestResource->setMedicationCodeableConcept($textOnlyCode);
        }
    }

    public function populateCategory(FHIRMedicationRequest $medRequestResource, array $dataRecord)
    {
        if (isset($dataRecord['category'])) {
            $medRequestResource->addCategory(UtilsService::createCodeableConcept(
                [
                    $dataRecord['category'] =>
                        ['code' => $dataRecord['category'], 'description' => xlt($dataRecord['category_title'])
                            ,'system' => FhirCodeSystemConstants::HL7_MEDICATION_REQUEST_CATEGORY]
                ]
            ));
        } else {
            // if no category has been sent then the default is home usage
            $medRequestResource->addCategory(UtilsService::createCodeableConcept(
                [
                    self::MEDICATION_REQUEST_CATEGORY_COMMUNITY => [
                        'code' => self::MEDICATION_REQUEST_CATEGORY_COMMUNITY,
                        'description' => xlt(self::MEDICATION_REQUEST_CATEGORY_COMMUNITY_TITLE),
                        'system' => FhirCodeSystemConstants::HL7_MEDICATION_REQUEST_CATEGORY
                    ]
                ],
            ));
        }
    }

    public function populateIntent(FHIRMedicationRequest $medRequestResource, array $dataRecord)
    {
        $intent = FHIRMedicationIntentEnum::tryFrom($dataRecord['intent'] ?? 'plan');
        if ($intent != null) {
            $medRequestResource->setIntent($intent->value);
        } else {
            // if we are missing the intent for whatever reason we should convey the code that does the least harm
            // which is that this is a plan but does not have authorization
            $medRequestResource->setIntent(FHIRMedicationIntentEnum::PLAN);
        }
    }

    public function populateStatus(FHIRMedicationRequest $medRequestResource, array $dataRecord)
    {
        $enum = FHIRMedicationStatusEnum::tryFrom($dataRecord['status'] ?? 'unknown');
        if ($enum != null) {
            $medRequestResource->setStatus($enum->value);
        } else {
            $medRequestResource->setStatus(FHIRMedicationStatusEnum::UNKNOWN);
        }
    }

    public function populateMeta(FHIRMedicationRequest $medRequestResource, array $dataRecord)
    {
        $meta = new FHIRMeta();
        $meta->setVersionId('1');
        if (!empty($dataRecord['date_modified'])) {
            $meta->setLastUpdated(UtilsService::getLocalDateAsUTC($dataRecord['date_modified']));
        } else {
            $meta->setLastUpdated(UtilsService::getDateFormattedAsUTC());
        }
        $medRequestResource->setMeta($meta);
    }

    public function populateId(FHIRMedicationRequest $medRequestResource, array $dataRecord)
    {
        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $medRequestResource->setId($id);
    }

    public function populateNote(FHIRMedicationRequest $medRequestResource, array $dataRecord)
    {
        if (!empty($dataRecord['note'])) {
            $note = new FHIRAnnotation();
            $note->setText($dataRecord['note']);
            $medRequestResource->addNote($note);
        }
    }

    public function getCodeTypesService(): CodeTypesService
    {
        if (!isset($this->codeTypesService)) {
            $this->codeTypesService = new CodeTypesService();
        }
        return $this->codeTypesService;
    }

    /**
     * @param CodeTypesService $codeTypesService
     */
    public function setCodeTypesService(CodeTypesService $codeTypesService): void
    {
        $this->codeTypesService = $codeTypesService;
    }

    /**
     * @return PrescriptionService
     */
    public function getPrescriptionService(): PrescriptionService
    {
        if (!isset($this->prescriptionService)) {
            $this->prescriptionService = new PrescriptionService();
        }
        return $this->prescriptionService;
    }

    /**
     * @param PrescriptionService $prescriptionService
     */
    public function setPrescriptionService(PrescriptionService $prescriptionService): void
    {
        $this->prescriptionService = $prescriptionService;
    }

    public function getFhirOrganizationService(): FhirOrganizationService
    {
        if (!isset($this->fhirOrganizationService)) {
            $this->fhirOrganizationService = new FhirOrganizationService();
        }
        return $this->fhirOrganizationService;
    }

    /**
     * @param FhirOrganizationService $fhirOrganizationService
     */
    public function setFhirOrganizationService(FhirOrganizationService $fhirOrganizationService): void
    {
        $this->fhirOrganizationService = $fhirOrganizationService;
    }
}
