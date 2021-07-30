<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicationRequest;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUnitsOfTime;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDosage;
use OpenEMR\FHIR\R4\FHIRResource\FHIRTiming;
use OpenEMR\FHIR\R4\FHIRResource\FHIRTiming\FHIRTimingRepeat;
use OpenEMR\Services\FHIR\Traits\PatientSearchTrait;
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
class FhirMedicationRequestService extends FhirServiceBase
{
    use PatientSearchTrait;

    const MEDICATION_REQUEST_STATUS_COMPLETED = "completed";
    const MEDICATION_REQUEST_STATUS_STOPPED = "stopped";
    const MEDICATION_REQUEST_STATUS_ACTIVE = "active";

    const MEDICATION_REQUEST_INTENT_PLAN = "plan";
    const MEDICATION_REQUEST_INTENT_ORDER = "order";

    /**
     * Constants for the reported flag or reference signaling that information is from a secondary source such as a patient
     */
    const MEDICATION_REQUEST_REPORTED_SECONDARY_SOURCE = 1;
    const MEDICATION_REQUEST_REPORTED_PRIMARY_SOURCE = 0;

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

    /**
     * Includes requests for medications created when the patient is being released from a facility
     */
    const MEDICATION_REQUEST_CATEGORY_DISCHARGE = "discharge";
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
            'intent' => new FhirSearchParameterDefinition('intent', SearchFieldType::TOKEN, [new ServiceField('intent', ServiceField::TYPE_UUID)]),
            '_id' => new FhirSearchParameterDefinition('uuid', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)]),
        ];
    }

    /**
     * Parses an OpenEMR prescription record, returning the equivalent FHIR Patient Resource
     *
     * @param array $dataRecord The source OpenEMR data record
     * @param boolean $encode Indicates if the returned resource is encoded into a string. Defaults to false.
     * @return FHIRPatient
     */
    public function parseOpenEMRRecord($dataRecord = array(), $encode = false)
    {
        $medRequestResource = new FHIRMedicationRequest();

        $meta = new FHIRMeta();
        $meta->setVersionId('1');
        $meta->setLastUpdated(gmdate('c'));
        $medRequestResource->setMeta($meta);

        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $medRequestResource->setId($id);

        // status required

        if (isset($dataRecord['end_date']) && $dataRecord['active'] == '1') {
            $medRequestResource->setStatus(self::MEDICATION_REQUEST_STATUS_COMPLETED);
        } elseif ($dataRecord['active'] == '1') {
            $medRequestResource->setStatus(self::MEDICATION_REQUEST_STATUS_ACTIVE);
        } else {
            $medRequestResource->setStatus(self::MEDICATION_REQUEST_STATUS_STOPPED);
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
        // TODO: @adunsulag need to add a category to the prescriptions table and to our medication list issues table so we can support where this drug is being taken...
        $medRequestResource->addCategory(UtilsService::createCodeableConcept([self::MEDICATION_REQUEST_CATEGORY_COMMUNITY => xlt("Community"), FhirCodeSystemConstants::HL7_MEDICATION_REQUEST_CATEGORY]));

        // reported must support
        // Talking with Sherwin Gaddis (implementor of WENO e-prescribe) anything in prescription will be clinician requested
        // and anything in the medications list table will be user requested...
        $medRequestResource->setReportedBoolean(self::MEDICATION_REQUEST_REPORTED_PRIMARY_SOURCE);

        // TODO: we need to embed this unless we want to deal with the medication resource and allow _include which we don't support right now
        // medication[x] required
        if (!empty($dataRecord['rxnorm_drugcode'])) {
            $rxnormCoding = new FHIRCoding();
            $rxnormCode = new FHIRCodeableConcept();
            $rxnormCoding->setSystem(FhirCodeSystemConstants::RXNORM);
            foreach ($dataRecord['rxnorm_drugcode'] as $code => $display) {
                $rxnormCoding->setCode($code);
                $rxnormCoding->setDisplay($display);
                $rxnormCode->addCoding($rxnormCoding);
            }
            $medRequestResource->setMedicationCodeableConcept($rxnormCode);
        }
        if (!empty($dataRecord['drug_uuid'])) {
            $medication = new FHIRReference();
            $medication->setReference('Medication/' . $dataRecord['drug_uuid']);
            $medRequestResource->setMedicationReference($medication);
        }

        // subject required
        if (!empty($dataRecord['puuid'])) {
            $subject = new FHIRReference();
            $subject->setReference('Patient/' . $dataRecord['puuid']);
            $medRequestResource->setSubject($subject);
        } else {
            $medRequestResource->setSubject(UtilsService::createDataMissingExtension());
        }

        // encounter must support
        if (!empty($dataRecord['euuid'])) {
            $encounter = new FHIRReference();
            $encounter->setReference('Encounter/' . $dataRecord['euuid']);
            $medRequestResource->setEncounter($encounter);
        }

        // authoredOn must support
        if (!empty($dataRecord['date_added'])) {
            $authored_on = new FHIRDateTime();
            $authored_on->setValue($dataRecord['date_added']);
            $medRequestResource->setAuthoredOn($authored_on);
        }

        // requester required
        if (!empty($dataRecord['pruuid'])) {
            $requester = new FHIRReference();
            $requester->setReference('Practitioner/' . $dataRecord['pruuid']);
            $medRequestResource->setRequester($requester);
        } else {
            $medRequestResource->setRequester(UtilsService::createDataMissingExtension());
        }
        
        // dosageInstructions must support
        if (!empty($dataRecord['unit'] || $dataRecord['interval'])) {
            list($unitValue) = [
                '0' => [''],
                '1' => ['mg'],
                '2' => ['mg/1cc'],
                '3' => ['mg/2cc'],
                '4' => ['mg/3cc'],
                '5' => ['mg/4cc'],
                '6' => ['mg/5cc'],
                '7' => ['mcg'],
                '8' => ['grams'],
                '9' => ['mL']
            ][$dataRecord['unit']] ?? [''];
            $unit = new FHIRUnitsOfTime();
            $unit->setValue($unitValue);
            $decimal = new FHIRDecimal();
            $decimal->setValue($dataRecord['interval']);
            $repeat = new FHIRTimingRepeat();
            $repeat->setPeriodUnit($unit);
            $repeat->setPeriod($decimal);
            $timing = new FHIRTiming();
            $timing->setRepeat($repeat);
        }

        if (!empty($dataRecord['route'])) {
            list($routeValue, $routeCode) = [
                '0' => ['', ''],
                '1' => ['ORAL', 'C38288'],
                '2' => ['RECTAL', 'C38295'],
                '3' => ['CUTANEOUS', 'C38675'],
                '4' => ['To Affected Area', ''],
                '5' => ['SUBLINGUAL', 'C38300'],
                '6' => ['ORAL', 'C38288'],
                '7' => ['OD', ''],
                '8' => ['OPHTHALMIC', 'C38287'],
                '9' => ['SUBCUTANEOUS', 'C38299'],
                '10' => ['INTRAMUSCULAR', 'C28161'],
                '11' => ['INTRAVENOUS', 'C38276'],
                '12' => ['NASAL', 'C38284'],
                '13' => ['AURICULAR (OTIC)', 'C38192'],
                '14' => ['AURICULAR (OTIC)', 'C38192'],
                '15' => ['AURICULAR (OTIC)', 'C38192'],
                '16' => ['INTRADERMAL', 'C38238'],
                '18' => ['OTHER', 'C38290'],
                '19' => ['TRANSDERMAL', 'C38305'],
                '20' => ['INTRAMUSCULAR', 'C28161'],
            ][$dataRecord['route']] ?? ['', ''];
            $route = new FHIRCodeableConcept();
            $routeCoding = new FHIRCoding();
            $routeCoding->setSystem("http://ncimeta.nci.nih.gov");
            $routeCoding->setCode($routeCode);
            $routeCoding->setDisplay($routeValue);
            $route->addCoding($routeCoding);
            $dosage = new FHIRDosage();
            $dosage->setRoute($route);
            $dosage->setTiming($timing);
            $medRequestResource->addDosageInstruction($dosage);
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
        // for now we hardcode all of our prescriptions to be 'order' so we don't actually deal with any filtering other than the single status
        if (isset($openEMRSearchParameters['intent']))
        {
            unset($openEMRSearchParameters['intent']);
        }
        return $this->prescriptionService->getAll($openEMRSearchParameters, false, $puuidBind);
    }

    public function parseFhirResource($fhirResource = array())
    {
        // TODO: If Required in Future
    }

    public function insertOpenEMRRecord($openEmrRecord)
    {
        // TODO: If Required in Future
    }

    public function updateOpenEMRRecord($fhirResourceId, $updatedOpenEMRRecord)
    {
        // TODO: If Required in Future
    }
    public function createProvenanceResource($dataRecord = array(), $encode = false)
    {
        // TODO: If Required in Future
    }
}
