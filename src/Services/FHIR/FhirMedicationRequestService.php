<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedication;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicationRequest;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRNarrative;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUnitsOfTime;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDosage;
use OpenEMR\FHIR\R4\FHIRResource\FHIRTiming;
use OpenEMR\FHIR\R4\FHIRResource\FHIRTiming\FHIRTimingRepeat;
use OpenEMR\Services\FHIR\Traits\BulkExportSupportAllOperationsTrait;
use OpenEMR\Services\FHIR\Traits\FhirBulkExportDomainResourceTrait;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\PatientSearchTrait;
use OpenEMR\Services\FHIR\Traits\VersionedProfileTrait;
use OpenEMR\Services\ListService;
use OpenEMR\Services\PrescriptionService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
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

    private $medicationRequestIdCounter = 1;

    const MEDICATION_REQUEST_STATUS_COMPLETED = "completed";
    const MEDICATION_REQUEST_STATUS_STOPPED = "stopped";
    const MEDICATION_REQUEST_STATUS_ACTIVE = "active";
    const MEDICATION_REQUEST_STATUS_UNKNOWN = "unknown";

    const MEDICATION_REQUEST_INTENT_PLAN = "plan";
    const MEDICATION_REQUEST_INTENT_ORDER = "order";

    /**
     * Constants for the reported flag or reference signaling that information is from a secondary source such as a patient
     */
    const MEDICATION_REQUEST_REPORTED_SECONDARY_SOURCE = true;
    const MEDICATION_REQUEST_REPORTED_PRIMARY_SOURCE = false;

    /**
     * Includes requests for medications to be administered or consumed in an inpatient or acute care setting
     */
    const MEDICATION_REQUEST_CATEGORY_INPATIENT = "inpatient";

    /**
     * Includes requests for medications to be administered or consumed in an outpatient setting (for example, Emergency Department, Outpatient Clinic, Outpatient Surgery, Doctor's office)
     */
    const MEDICATION_REQUEST_CATEGORY_OUTPATIENT = "outpatient";

    /**
     * Includes requests for medications to be administered or consumed by the patient in their home (this would include long term care or nursing homes, hospices, etc.)
     */
    const MEDICATION_REQUEST_CATEGORY_COMMUNITY = "community";

    const MEDICATION_REQUEST_CATEGORY_COMMUNITY_TITLE = "Home/Community";

    /**
     * Includes requests for medications created when the patient is being released from a facility
     */
    const MEDICATION_REQUEST_CATEGORY_DISCHARGE = "discharge";

    /**
     * Unique reference that is contained inside a MedicationRequest if we have no connected RXNorm drug data.
     */
    const MEDICATION_REQUEST_REFERENCE_ID_PREFIX = "m";

    const USCGI_PROFILE_URI = "http://hl7.org/fhir/us/core/StructureDefinition/us-core-medicationrequest";
    /**
     * @deprecated use USCGI_PROFILE_URI
     */
    const PROFILE_URI = self::USCGI_PROFILE_URI;

    /**
     * @var PrescriptionService
     */
    private $prescriptionService;

    public function __construct()
    {
        parent::__construct();
        $this->prescriptionService = new PrescriptionService();
    }

    /**
     * Returns an array mapping FHIR Patient Resource search parameters to OpenEMR Patient search parameters
     * @return array The search parameters
     */
    protected function loadSearchParameters()
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
     * @return FHIRPatient
     */
    public function parseOpenEMRRecord($dataRecord = [], $encode = false)
    {
        $medRequestResource = new FHIRMedicationRequest();

        $meta = new FHIRMeta();
        $meta->setVersionId('1');
        if (!empty($dataRecord['date_modified'])) {
            $meta->setLastUpdated(UtilsService::getLocalDateAsUTC($dataRecord['date_modified']));
        } else {
            $meta->setLastUpdated(UtilsService::getDateFormattedAsUTC());
        }
        $medRequestResource->setMeta($meta);

        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $medRequestResource->setId($id);

        // status required
        $validStatii = [self::MEDICATION_REQUEST_STATUS_STOPPED,
            self::MEDICATION_REQUEST_STATUS_ACTIVE, self::MEDICATION_REQUEST_STATUS_COMPLETED];
        if (
            !empty($dataRecord['status'])
            && array_search($dataRecord['status'], $validStatii) !== false
        ) {
            $medRequestResource->setStatus($dataRecord['status']);
        } else {
            $medRequestResource->setStatus(self::MEDICATION_REQUEST_STATUS_UNKNOWN);
        }

        // intent required
        if (isset($dataRecord['intent'])) {
            $medRequestResource->setIntent($dataRecord['intent']);
        } else {
            // if we are missing the intent for whatever reason we should convey the code that does the least harm
            // which is that this is a plan but does not have authorization
            $medRequestResource->setIntent(self::MEDICATION_REQUEST_INTENT_PLAN);
        }

        // category must support
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

        // reported must support
        // we will treat everything as a primary source as OpenEMR has no way of differentiating right now primary versus secondary.
        $medRequestResource->setReportedBoolean(self::MEDICATION_REQUEST_REPORTED_PRIMARY_SOURCE);

        // medication[x] required
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
        if (!empty($dataRecord['drugcode'])) {
//            $rxnormCoding = new FHIRCoding();
            $rxnormCode = UtilsService::createCodeableConcept($dataRecord['drugcode'], FhirCodeSystemConstants::RXNORM);
//            $rxnormCoding->addCoding($rxNormConcept);
//            $rxnormCode = new FHIRCodeableConcept();
//            $rxnormCoding->setSystem(FhirCodeSystemConstants::RXNORM);
//            foreach ($dataRecord['drugcode'] as $code => $codeValues) {
//
//            }
            $medRequestResource->setMedicationCodeableConcept($rxnormCode);
        } else {
            $textOnlyCode = new FHIRCodeableConcept();
            $textOnlyCode->setText($dataRecord['drug']);
            $medRequestResource->setMedicationCodeableConcept($textOnlyCode);
        }

        // subject required
        if (!empty($dataRecord['puuid'])) {
            $medRequestResource->setSubject(UtilsService::createRelativeReference('Patient', $dataRecord['puuid']));
        } else {
            $medRequestResource->setSubject(UtilsService::createDataMissingExtension());
        }

        // encounter must support
        if (!empty($dataRecord['euuid'])) {
            $medRequestResource->setEncounter(UtilsService::createRelativeReference("Encounter", $dataRecord['euuid']));
        }

        // authoredOn must support
        if (!empty($dataRecord['date_added'])) {
            $authored_on = new FHIRDateTime();
            $authored_on->setValue(UtilsService::getLocalDateAsUTC($dataRecord['date_added']));
            $medRequestResource->setAuthoredOn($authored_on);
        }

        // requester required
        if (!empty($dataRecord['pruuid'])) {
            $medRequestResource->setRequester(UtilsService::createRelativeReference('Practitioner', $dataRecord['pruuid']));
        } else {
            // if we have no practitioner we need to default it to the organization
            $fhirOrgService = new FhirOrganizationService();
            $medRequestResource->setRequester($fhirOrgService->getPrimaryBusinessEntityReference());
        }

        // dosageInstructions must support
        // we ignore unit,interval,and route for now as WENO does not populate it and NewCrop does not either inside OpenEMR
        // instead we will populate the dosageInstructions if we have it in order to meet ONC certification
        // TODO: @adunsulag if we actually support specific dosage instruction fields like period, interval, etc that
        // can be used to report in FHIR, populate the dosage instructions here.

        if (!empty($dataRecord['drug_dosage_instructions'])) {
            $dosage = new FHIRDosage();
            // TODO: @adunsulag if inferno fixes their testsuite change to use the object.  The inferno test suite fails
            // to recognize resourceType even though Dosage is a BackboneElement subtype, so this is one of the few times
            // we will use an array dataset instead of the actual class
            $dosageArray = [];
            if (!empty($dataRecord['route'])) {
                $this->populateRouteOptions($dataRecord, $dosage);
                if (!empty($dosage->getRoute())) {
                    $dosageArray['route'] = $dosage->getRoute()->jsonSerialize();
                }
            }

            if (!empty($dataRecord['drug_dosage_instructions'])) {
//                $dosage->setText($dataRecord['drug_dosage_instructions']);
                $dosageArray['text'] = $dataRecord['drug_dosage_instructions'];
            }


            $medRequestResource->addDosageInstruction($dosageArray);
        }

        if (!empty($dataRecord['note'])) {
            $note = new FHIRAnnotation();
            $note->setText($dataRecord['note']);
        }

        if ($encode) {
            return json_encode($medRequestResource);
        } else {
            return $medRequestResource;
        }
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     *
     * @param  array openEMRSearchParameters OpenEMR search fields
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return ProcessingResult
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters, $puuidBind = null): ProcessingResult
    {
        return $this->prescriptionService->getAll($openEMRSearchParameters, true, $puuidBind);
    }

    public function createProvenanceResource($dataRecord = [], $encode = false)
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

    private function populateRouteOptions($dataRecord, FHIRDosage $dosage)
    {
        // TODO: populate this in next PR
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
}
