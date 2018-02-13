<?php namespace HL7\FHIR\STU3;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

class FHIRResourceContainer implements \JsonSerializable
{
    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRAccount
     */
    public $Account = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRActivityDefinition
     */
    public $ActivityDefinition = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRAdverseEvent
     */
    public $AdverseEvent = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRAllergyIntolerance
     */
    public $AllergyIntolerance = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRAppointment
     */
    public $Appointment = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRAppointmentResponse
     */
    public $AppointmentResponse = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRAuditEvent
     */
    public $AuditEvent = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRBasic
     */
    public $Basic = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRBinary
     */
    public $Binary = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRBodySite
     */
    public $BodySite = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRBundle
     */
    public $Bundle = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRCapabilityStatement
     */
    public $CapabilityStatement = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRCarePlan
     */
    public $CarePlan = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRCareTeam
     */
    public $CareTeam = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRChargeItem
     */
    public $ChargeItem = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRClaim
     */
    public $Claim = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRClaimResponse
     */
    public $ClaimResponse = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRClinicalImpression
     */
    public $ClinicalImpression = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRCodeSystem
     */
    public $CodeSystem = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRCommunication
     */
    public $Communication = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRCommunicationRequest
     */
    public $CommunicationRequest = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRCompartmentDefinition
     */
    public $CompartmentDefinition = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRComposition
     */
    public $Composition = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRConceptMap
     */
    public $ConceptMap = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRCondition
     */
    public $Condition = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRConsent
     */
    public $Consent = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRContract
     */
    public $Contract = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRCoverage
     */
    public $Coverage = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRDataElement
     */
    public $DataElement = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRDetectedIssue
     */
    public $DetectedIssue = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRDevice
     */
    public $Device = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRDeviceComponent
     */
    public $DeviceComponent = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRDeviceMetric
     */
    public $DeviceMetric = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRDeviceRequest
     */
    public $DeviceRequest = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRDeviceUseStatement
     */
    public $DeviceUseStatement = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRDiagnosticReport
     */
    public $DiagnosticReport = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRDocumentManifest
     */
    public $DocumentManifest = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRDocumentReference
     */
    public $DocumentReference = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIREligibilityRequest
     */
    public $EligibilityRequest = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIREligibilityResponse
     */
    public $EligibilityResponse = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIREncounter
     */
    public $Encounter = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIREndpoint
     */
    public $Endpoint = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIREnrollmentRequest
     */
    public $EnrollmentRequest = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIREnrollmentResponse
     */
    public $EnrollmentResponse = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIREpisodeOfCare
     */
    public $EpisodeOfCare = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRExpansionProfile
     */
    public $ExpansionProfile = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRExplanationOfBenefit
     */
    public $ExplanationOfBenefit = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRFamilyMemberHistory
     */
    public $FamilyMemberHistory = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRFlag
     */
    public $Flag = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRGoal
     */
    public $Goal = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRGraphDefinition
     */
    public $GraphDefinition = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRGroup
     */
    public $Group = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRGuidanceResponse
     */
    public $GuidanceResponse = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRHealthcareService
     */
    public $HealthcareService = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRImagingManifest
     */
    public $ImagingManifest = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRImagingStudy
     */
    public $ImagingStudy = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRImmunization
     */
    public $Immunization = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRImmunizationRecommendation
     */
    public $ImmunizationRecommendation = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRImplementationGuide
     */
    public $ImplementationGuide = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRLibrary
     */
    public $Library = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRLinkage
     */
    public $Linkage = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRList
     */
    public $List = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRLocation
     */
    public $Location = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRMeasure
     */
    public $Measure = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRMeasureReport
     */
    public $MeasureReport = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRMedia
     */
    public $Media = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRMedication
     */
    public $Medication = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRMedicationAdministration
     */
    public $MedicationAdministration = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRMedicationDispense
     */
    public $MedicationDispense = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRMedicationRequest
     */
    public $MedicationRequest = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRMedicationStatement
     */
    public $MedicationStatement = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRMessageDefinition
     */
    public $MessageDefinition = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRMessageHeader
     */
    public $MessageHeader = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRNamingSystem
     */
    public $NamingSystem = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRNutritionOrder
     */
    public $NutritionOrder = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRObservation
     */
    public $Observation = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIROperationDefinition
     */
    public $OperationDefinition = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIROperationOutcome
     */
    public $OperationOutcome = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIROrganization
     */
    public $Organization = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRPatient
     */
    public $Patient = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRPaymentNotice
     */
    public $PaymentNotice = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRPaymentReconciliation
     */
    public $PaymentReconciliation = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRPerson
     */
    public $Person = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRPlanDefinition
     */
    public $PlanDefinition = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRPractitioner
     */
    public $Practitioner = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRPractitionerRole
     */
    public $PractitionerRole = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRProcedure
     */
    public $Procedure = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRProcedureRequest
     */
    public $ProcedureRequest = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRProcessRequest
     */
    public $ProcessRequest = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRProcessResponse
     */
    public $ProcessResponse = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRProvenance
     */
    public $Provenance = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRQuestionnaire
     */
    public $Questionnaire = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRQuestionnaireResponse
     */
    public $QuestionnaireResponse = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRReferralRequest
     */
    public $ReferralRequest = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRRelatedPerson
     */
    public $RelatedPerson = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRRequestGroup
     */
    public $RequestGroup = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRResearchStudy
     */
    public $ResearchStudy = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRResearchSubject
     */
    public $ResearchSubject = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRRiskAssessment
     */
    public $RiskAssessment = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRSchedule
     */
    public $Schedule = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRSearchParameter
     */
    public $SearchParameter = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRSequence
     */
    public $Sequence = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRServiceDefinition
     */
    public $ServiceDefinition = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRSlot
     */
    public $Slot = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRSpecimen
     */
    public $Specimen = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRStructureDefinition
     */
    public $StructureDefinition = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRStructureMap
     */
    public $StructureMap = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRSubscription
     */
    public $Subscription = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRSubstance
     */
    public $Substance = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRSupplyDelivery
     */
    public $SupplyDelivery = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRSupplyRequest
     */
    public $SupplyRequest = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRTask
     */
    public $Task = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRTestReport
     */
    public $TestReport = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRTestScript
     */
    public $TestScript = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRValueSet
     */
    public $ValueSet = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRDomainResource\FHIRVisionPrescription
     */
    public $VisionPrescription = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRParameters
     */
    public $Parameters = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'ResourceContainer';

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRAccount
     */
    public function getAccount()
    {
        return $this->Account;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRAccount $Account
     * @return $this
     */
    public function setAccount($Account)
    {
        $this->Account = $Account;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRActivityDefinition
     */
    public function getActivityDefinition()
    {
        return $this->ActivityDefinition;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRActivityDefinition $ActivityDefinition
     * @return $this
     */
    public function setActivityDefinition($ActivityDefinition)
    {
        $this->ActivityDefinition = $ActivityDefinition;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRAdverseEvent
     */
    public function getAdverseEvent()
    {
        return $this->AdverseEvent;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRAdverseEvent $AdverseEvent
     * @return $this
     */
    public function setAdverseEvent($AdverseEvent)
    {
        $this->AdverseEvent = $AdverseEvent;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRAllergyIntolerance
     */
    public function getAllergyIntolerance()
    {
        return $this->AllergyIntolerance;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRAllergyIntolerance $AllergyIntolerance
     * @return $this
     */
    public function setAllergyIntolerance($AllergyIntolerance)
    {
        $this->AllergyIntolerance = $AllergyIntolerance;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRAppointment
     */
    public function getAppointment()
    {
        return $this->Appointment;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRAppointment $Appointment
     * @return $this
     */
    public function setAppointment($Appointment)
    {
        $this->Appointment = $Appointment;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRAppointmentResponse
     */
    public function getAppointmentResponse()
    {
        return $this->AppointmentResponse;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRAppointmentResponse $AppointmentResponse
     * @return $this
     */
    public function setAppointmentResponse($AppointmentResponse)
    {
        $this->AppointmentResponse = $AppointmentResponse;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRAuditEvent
     */
    public function getAuditEvent()
    {
        return $this->AuditEvent;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRAuditEvent $AuditEvent
     * @return $this
     */
    public function setAuditEvent($AuditEvent)
    {
        $this->AuditEvent = $AuditEvent;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRBasic
     */
    public function getBasic()
    {
        return $this->Basic;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRBasic $Basic
     * @return $this
     */
    public function setBasic($Basic)
    {
        $this->Basic = $Basic;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRBinary
     */
    public function getBinary()
    {
        return $this->Binary;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRBinary $Binary
     * @return $this
     */
    public function setBinary($Binary)
    {
        $this->Binary = $Binary;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRBodySite
     */
    public function getBodySite()
    {
        return $this->BodySite;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRBodySite $BodySite
     * @return $this
     */
    public function setBodySite($BodySite)
    {
        $this->BodySite = $BodySite;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRBundle
     */
    public function getBundle()
    {
        return $this->Bundle;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRBundle $Bundle
     * @return $this
     */
    public function setBundle($Bundle)
    {
        $this->Bundle = $Bundle;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRCapabilityStatement
     */
    public function getCapabilityStatement()
    {
        return $this->CapabilityStatement;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRCapabilityStatement $CapabilityStatement
     * @return $this
     */
    public function setCapabilityStatement($CapabilityStatement)
    {
        $this->CapabilityStatement = $CapabilityStatement;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRCarePlan
     */
    public function getCarePlan()
    {
        return $this->CarePlan;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRCarePlan $CarePlan
     * @return $this
     */
    public function setCarePlan($CarePlan)
    {
        $this->CarePlan = $CarePlan;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRCareTeam
     */
    public function getCareTeam()
    {
        return $this->CareTeam;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRCareTeam $CareTeam
     * @return $this
     */
    public function setCareTeam($CareTeam)
    {
        $this->CareTeam = $CareTeam;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRChargeItem
     */
    public function getChargeItem()
    {
        return $this->ChargeItem;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRChargeItem $ChargeItem
     * @return $this
     */
    public function setChargeItem($ChargeItem)
    {
        $this->ChargeItem = $ChargeItem;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRClaim
     */
    public function getClaim()
    {
        return $this->Claim;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRClaim $Claim
     * @return $this
     */
    public function setClaim($Claim)
    {
        $this->Claim = $Claim;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRClaimResponse
     */
    public function getClaimResponse()
    {
        return $this->ClaimResponse;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRClaimResponse $ClaimResponse
     * @return $this
     */
    public function setClaimResponse($ClaimResponse)
    {
        $this->ClaimResponse = $ClaimResponse;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRClinicalImpression
     */
    public function getClinicalImpression()
    {
        return $this->ClinicalImpression;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRClinicalImpression $ClinicalImpression
     * @return $this
     */
    public function setClinicalImpression($ClinicalImpression)
    {
        $this->ClinicalImpression = $ClinicalImpression;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRCodeSystem
     */
    public function getCodeSystem()
    {
        return $this->CodeSystem;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRCodeSystem $CodeSystem
     * @return $this
     */
    public function setCodeSystem($CodeSystem)
    {
        $this->CodeSystem = $CodeSystem;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRCommunication
     */
    public function getCommunication()
    {
        return $this->Communication;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRCommunication $Communication
     * @return $this
     */
    public function setCommunication($Communication)
    {
        $this->Communication = $Communication;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRCommunicationRequest
     */
    public function getCommunicationRequest()
    {
        return $this->CommunicationRequest;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRCommunicationRequest $CommunicationRequest
     * @return $this
     */
    public function setCommunicationRequest($CommunicationRequest)
    {
        $this->CommunicationRequest = $CommunicationRequest;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRCompartmentDefinition
     */
    public function getCompartmentDefinition()
    {
        return $this->CompartmentDefinition;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRCompartmentDefinition $CompartmentDefinition
     * @return $this
     */
    public function setCompartmentDefinition($CompartmentDefinition)
    {
        $this->CompartmentDefinition = $CompartmentDefinition;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRComposition
     */
    public function getComposition()
    {
        return $this->Composition;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRComposition $Composition
     * @return $this
     */
    public function setComposition($Composition)
    {
        $this->Composition = $Composition;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRConceptMap
     */
    public function getConceptMap()
    {
        return $this->ConceptMap;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRConceptMap $ConceptMap
     * @return $this
     */
    public function setConceptMap($ConceptMap)
    {
        $this->ConceptMap = $ConceptMap;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRCondition
     */
    public function getCondition()
    {
        return $this->Condition;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRCondition $Condition
     * @return $this
     */
    public function setCondition($Condition)
    {
        $this->Condition = $Condition;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRConsent
     */
    public function getConsent()
    {
        return $this->Consent;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRConsent $Consent
     * @return $this
     */
    public function setConsent($Consent)
    {
        $this->Consent = $Consent;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRContract
     */
    public function getContract()
    {
        return $this->Contract;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRContract $Contract
     * @return $this
     */
    public function setContract($Contract)
    {
        $this->Contract = $Contract;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRCoverage
     */
    public function getCoverage()
    {
        return $this->Coverage;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRCoverage $Coverage
     * @return $this
     */
    public function setCoverage($Coverage)
    {
        $this->Coverage = $Coverage;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRDataElement
     */
    public function getDataElement()
    {
        return $this->DataElement;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRDataElement $DataElement
     * @return $this
     */
    public function setDataElement($DataElement)
    {
        $this->DataElement = $DataElement;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRDetectedIssue
     */
    public function getDetectedIssue()
    {
        return $this->DetectedIssue;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRDetectedIssue $DetectedIssue
     * @return $this
     */
    public function setDetectedIssue($DetectedIssue)
    {
        $this->DetectedIssue = $DetectedIssue;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRDevice
     */
    public function getDevice()
    {
        return $this->Device;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRDevice $Device
     * @return $this
     */
    public function setDevice($Device)
    {
        $this->Device = $Device;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRDeviceComponent
     */
    public function getDeviceComponent()
    {
        return $this->DeviceComponent;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRDeviceComponent $DeviceComponent
     * @return $this
     */
    public function setDeviceComponent($DeviceComponent)
    {
        $this->DeviceComponent = $DeviceComponent;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRDeviceMetric
     */
    public function getDeviceMetric()
    {
        return $this->DeviceMetric;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRDeviceMetric $DeviceMetric
     * @return $this
     */
    public function setDeviceMetric($DeviceMetric)
    {
        $this->DeviceMetric = $DeviceMetric;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRDeviceRequest
     */
    public function getDeviceRequest()
    {
        return $this->DeviceRequest;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRDeviceRequest $DeviceRequest
     * @return $this
     */
    public function setDeviceRequest($DeviceRequest)
    {
        $this->DeviceRequest = $DeviceRequest;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRDeviceUseStatement
     */
    public function getDeviceUseStatement()
    {
        return $this->DeviceUseStatement;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRDeviceUseStatement $DeviceUseStatement
     * @return $this
     */
    public function setDeviceUseStatement($DeviceUseStatement)
    {
        $this->DeviceUseStatement = $DeviceUseStatement;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRDiagnosticReport
     */
    public function getDiagnosticReport()
    {
        return $this->DiagnosticReport;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRDiagnosticReport $DiagnosticReport
     * @return $this
     */
    public function setDiagnosticReport($DiagnosticReport)
    {
        $this->DiagnosticReport = $DiagnosticReport;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRDocumentManifest
     */
    public function getDocumentManifest()
    {
        return $this->DocumentManifest;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRDocumentManifest $DocumentManifest
     * @return $this
     */
    public function setDocumentManifest($DocumentManifest)
    {
        $this->DocumentManifest = $DocumentManifest;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRDocumentReference
     */
    public function getDocumentReference()
    {
        return $this->DocumentReference;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRDocumentReference $DocumentReference
     * @return $this
     */
    public function setDocumentReference($DocumentReference)
    {
        $this->DocumentReference = $DocumentReference;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIREligibilityRequest
     */
    public function getEligibilityRequest()
    {
        return $this->EligibilityRequest;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIREligibilityRequest $EligibilityRequest
     * @return $this
     */
    public function setEligibilityRequest($EligibilityRequest)
    {
        $this->EligibilityRequest = $EligibilityRequest;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIREligibilityResponse
     */
    public function getEligibilityResponse()
    {
        return $this->EligibilityResponse;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIREligibilityResponse $EligibilityResponse
     * @return $this
     */
    public function setEligibilityResponse($EligibilityResponse)
    {
        $this->EligibilityResponse = $EligibilityResponse;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIREncounter
     */
    public function getEncounter()
    {
        return $this->Encounter;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIREncounter $Encounter
     * @return $this
     */
    public function setEncounter($Encounter)
    {
        $this->Encounter = $Encounter;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIREndpoint
     */
    public function getEndpoint()
    {
        return $this->Endpoint;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIREndpoint $Endpoint
     * @return $this
     */
    public function setEndpoint($Endpoint)
    {
        $this->Endpoint = $Endpoint;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIREnrollmentRequest
     */
    public function getEnrollmentRequest()
    {
        return $this->EnrollmentRequest;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIREnrollmentRequest $EnrollmentRequest
     * @return $this
     */
    public function setEnrollmentRequest($EnrollmentRequest)
    {
        $this->EnrollmentRequest = $EnrollmentRequest;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIREnrollmentResponse
     */
    public function getEnrollmentResponse()
    {
        return $this->EnrollmentResponse;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIREnrollmentResponse $EnrollmentResponse
     * @return $this
     */
    public function setEnrollmentResponse($EnrollmentResponse)
    {
        $this->EnrollmentResponse = $EnrollmentResponse;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIREpisodeOfCare
     */
    public function getEpisodeOfCare()
    {
        return $this->EpisodeOfCare;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIREpisodeOfCare $EpisodeOfCare
     * @return $this
     */
    public function setEpisodeOfCare($EpisodeOfCare)
    {
        $this->EpisodeOfCare = $EpisodeOfCare;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRExpansionProfile
     */
    public function getExpansionProfile()
    {
        return $this->ExpansionProfile;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRExpansionProfile $ExpansionProfile
     * @return $this
     */
    public function setExpansionProfile($ExpansionProfile)
    {
        $this->ExpansionProfile = $ExpansionProfile;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRExplanationOfBenefit
     */
    public function getExplanationOfBenefit()
    {
        return $this->ExplanationOfBenefit;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRExplanationOfBenefit $ExplanationOfBenefit
     * @return $this
     */
    public function setExplanationOfBenefit($ExplanationOfBenefit)
    {
        $this->ExplanationOfBenefit = $ExplanationOfBenefit;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRFamilyMemberHistory
     */
    public function getFamilyMemberHistory()
    {
        return $this->FamilyMemberHistory;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRFamilyMemberHistory $FamilyMemberHistory
     * @return $this
     */
    public function setFamilyMemberHistory($FamilyMemberHistory)
    {
        $this->FamilyMemberHistory = $FamilyMemberHistory;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRFlag
     */
    public function getFlag()
    {
        return $this->Flag;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRFlag $Flag
     * @return $this
     */
    public function setFlag($Flag)
    {
        $this->Flag = $Flag;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRGoal
     */
    public function getGoal()
    {
        return $this->Goal;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRGoal $Goal
     * @return $this
     */
    public function setGoal($Goal)
    {
        $this->Goal = $Goal;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRGraphDefinition
     */
    public function getGraphDefinition()
    {
        return $this->GraphDefinition;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRGraphDefinition $GraphDefinition
     * @return $this
     */
    public function setGraphDefinition($GraphDefinition)
    {
        $this->GraphDefinition = $GraphDefinition;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRGroup
     */
    public function getGroup()
    {
        return $this->Group;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRGroup $Group
     * @return $this
     */
    public function setGroup($Group)
    {
        $this->Group = $Group;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRGuidanceResponse
     */
    public function getGuidanceResponse()
    {
        return $this->GuidanceResponse;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRGuidanceResponse $GuidanceResponse
     * @return $this
     */
    public function setGuidanceResponse($GuidanceResponse)
    {
        $this->GuidanceResponse = $GuidanceResponse;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRHealthcareService
     */
    public function getHealthcareService()
    {
        return $this->HealthcareService;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRHealthcareService $HealthcareService
     * @return $this
     */
    public function setHealthcareService($HealthcareService)
    {
        $this->HealthcareService = $HealthcareService;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRImagingManifest
     */
    public function getImagingManifest()
    {
        return $this->ImagingManifest;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRImagingManifest $ImagingManifest
     * @return $this
     */
    public function setImagingManifest($ImagingManifest)
    {
        $this->ImagingManifest = $ImagingManifest;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRImagingStudy
     */
    public function getImagingStudy()
    {
        return $this->ImagingStudy;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRImagingStudy $ImagingStudy
     * @return $this
     */
    public function setImagingStudy($ImagingStudy)
    {
        $this->ImagingStudy = $ImagingStudy;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRImmunization
     */
    public function getImmunization()
    {
        return $this->Immunization;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRImmunization $Immunization
     * @return $this
     */
    public function setImmunization($Immunization)
    {
        $this->Immunization = $Immunization;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRImmunizationRecommendation
     */
    public function getImmunizationRecommendation()
    {
        return $this->ImmunizationRecommendation;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRImmunizationRecommendation $ImmunizationRecommendation
     * @return $this
     */
    public function setImmunizationRecommendation($ImmunizationRecommendation)
    {
        $this->ImmunizationRecommendation = $ImmunizationRecommendation;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRImplementationGuide
     */
    public function getImplementationGuide()
    {
        return $this->ImplementationGuide;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRImplementationGuide $ImplementationGuide
     * @return $this
     */
    public function setImplementationGuide($ImplementationGuide)
    {
        $this->ImplementationGuide = $ImplementationGuide;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRLibrary
     */
    public function getLibrary()
    {
        return $this->Library;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRLibrary $Library
     * @return $this
     */
    public function setLibrary($Library)
    {
        $this->Library = $Library;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRLinkage
     */
    public function getLinkage()
    {
        return $this->Linkage;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRLinkage $Linkage
     * @return $this
     */
    public function setLinkage($Linkage)
    {
        $this->Linkage = $Linkage;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRList
     */
    public function getList()
    {
        return $this->List;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRList $List
     * @return $this
     */
    public function setList($List)
    {
        $this->List = $List;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRLocation
     */
    public function getLocation()
    {
        return $this->Location;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRLocation $Location
     * @return $this
     */
    public function setLocation($Location)
    {
        $this->Location = $Location;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRMeasure
     */
    public function getMeasure()
    {
        return $this->Measure;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRMeasure $Measure
     * @return $this
     */
    public function setMeasure($Measure)
    {
        $this->Measure = $Measure;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRMeasureReport
     */
    public function getMeasureReport()
    {
        return $this->MeasureReport;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRMeasureReport $MeasureReport
     * @return $this
     */
    public function setMeasureReport($MeasureReport)
    {
        $this->MeasureReport = $MeasureReport;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRMedia
     */
    public function getMedia()
    {
        return $this->Media;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRMedia $Media
     * @return $this
     */
    public function setMedia($Media)
    {
        $this->Media = $Media;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRMedication
     */
    public function getMedication()
    {
        return $this->Medication;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRMedication $Medication
     * @return $this
     */
    public function setMedication($Medication)
    {
        $this->Medication = $Medication;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRMedicationAdministration
     */
    public function getMedicationAdministration()
    {
        return $this->MedicationAdministration;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRMedicationAdministration $MedicationAdministration
     * @return $this
     */
    public function setMedicationAdministration($MedicationAdministration)
    {
        $this->MedicationAdministration = $MedicationAdministration;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRMedicationDispense
     */
    public function getMedicationDispense()
    {
        return $this->MedicationDispense;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRMedicationDispense $MedicationDispense
     * @return $this
     */
    public function setMedicationDispense($MedicationDispense)
    {
        $this->MedicationDispense = $MedicationDispense;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRMedicationRequest
     */
    public function getMedicationRequest()
    {
        return $this->MedicationRequest;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRMedicationRequest $MedicationRequest
     * @return $this
     */
    public function setMedicationRequest($MedicationRequest)
    {
        $this->MedicationRequest = $MedicationRequest;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRMedicationStatement
     */
    public function getMedicationStatement()
    {
        return $this->MedicationStatement;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRMedicationStatement $MedicationStatement
     * @return $this
     */
    public function setMedicationStatement($MedicationStatement)
    {
        $this->MedicationStatement = $MedicationStatement;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRMessageDefinition
     */
    public function getMessageDefinition()
    {
        return $this->MessageDefinition;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRMessageDefinition $MessageDefinition
     * @return $this
     */
    public function setMessageDefinition($MessageDefinition)
    {
        $this->MessageDefinition = $MessageDefinition;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRMessageHeader
     */
    public function getMessageHeader()
    {
        return $this->MessageHeader;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRMessageHeader $MessageHeader
     * @return $this
     */
    public function setMessageHeader($MessageHeader)
    {
        $this->MessageHeader = $MessageHeader;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRNamingSystem
     */
    public function getNamingSystem()
    {
        return $this->NamingSystem;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRNamingSystem $NamingSystem
     * @return $this
     */
    public function setNamingSystem($NamingSystem)
    {
        $this->NamingSystem = $NamingSystem;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRNutritionOrder
     */
    public function getNutritionOrder()
    {
        return $this->NutritionOrder;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRNutritionOrder $NutritionOrder
     * @return $this
     */
    public function setNutritionOrder($NutritionOrder)
    {
        $this->NutritionOrder = $NutritionOrder;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRObservation
     */
    public function getObservation()
    {
        return $this->Observation;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRObservation $Observation
     * @return $this
     */
    public function setObservation($Observation)
    {
        $this->Observation = $Observation;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIROperationDefinition
     */
    public function getOperationDefinition()
    {
        return $this->OperationDefinition;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIROperationDefinition $OperationDefinition
     * @return $this
     */
    public function setOperationDefinition($OperationDefinition)
    {
        $this->OperationDefinition = $OperationDefinition;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIROperationOutcome
     */
    public function getOperationOutcome()
    {
        return $this->OperationOutcome;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIROperationOutcome $OperationOutcome
     * @return $this
     */
    public function setOperationOutcome($OperationOutcome)
    {
        $this->OperationOutcome = $OperationOutcome;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIROrganization
     */
    public function getOrganization()
    {
        return $this->Organization;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIROrganization $Organization
     * @return $this
     */
    public function setOrganization($Organization)
    {
        $this->Organization = $Organization;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRPatient
     */
    public function getPatient()
    {
        return $this->Patient;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRPatient $Patient
     * @return $this
     */
    public function setPatient($Patient)
    {
        $this->Patient = $Patient;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRPaymentNotice
     */
    public function getPaymentNotice()
    {
        return $this->PaymentNotice;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRPaymentNotice $PaymentNotice
     * @return $this
     */
    public function setPaymentNotice($PaymentNotice)
    {
        $this->PaymentNotice = $PaymentNotice;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRPaymentReconciliation
     */
    public function getPaymentReconciliation()
    {
        return $this->PaymentReconciliation;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRPaymentReconciliation $PaymentReconciliation
     * @return $this
     */
    public function setPaymentReconciliation($PaymentReconciliation)
    {
        $this->PaymentReconciliation = $PaymentReconciliation;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRPerson
     */
    public function getPerson()
    {
        return $this->Person;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRPerson $Person
     * @return $this
     */
    public function setPerson($Person)
    {
        $this->Person = $Person;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRPlanDefinition
     */
    public function getPlanDefinition()
    {
        return $this->PlanDefinition;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRPlanDefinition $PlanDefinition
     * @return $this
     */
    public function setPlanDefinition($PlanDefinition)
    {
        $this->PlanDefinition = $PlanDefinition;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRPractitioner
     */
    public function getPractitioner()
    {
        return $this->Practitioner;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRPractitioner $Practitioner
     * @return $this
     */
    public function setPractitioner($Practitioner)
    {
        $this->Practitioner = $Practitioner;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRPractitionerRole
     */
    public function getPractitionerRole()
    {
        return $this->PractitionerRole;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRPractitionerRole $PractitionerRole
     * @return $this
     */
    public function setPractitionerRole($PractitionerRole)
    {
        $this->PractitionerRole = $PractitionerRole;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRProcedure
     */
    public function getProcedure()
    {
        return $this->Procedure;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRProcedure $Procedure
     * @return $this
     */
    public function setProcedure($Procedure)
    {
        $this->Procedure = $Procedure;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRProcedureRequest
     */
    public function getProcedureRequest()
    {
        return $this->ProcedureRequest;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRProcedureRequest $ProcedureRequest
     * @return $this
     */
    public function setProcedureRequest($ProcedureRequest)
    {
        $this->ProcedureRequest = $ProcedureRequest;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRProcessRequest
     */
    public function getProcessRequest()
    {
        return $this->ProcessRequest;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRProcessRequest $ProcessRequest
     * @return $this
     */
    public function setProcessRequest($ProcessRequest)
    {
        $this->ProcessRequest = $ProcessRequest;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRProcessResponse
     */
    public function getProcessResponse()
    {
        return $this->ProcessResponse;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRProcessResponse $ProcessResponse
     * @return $this
     */
    public function setProcessResponse($ProcessResponse)
    {
        $this->ProcessResponse = $ProcessResponse;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRProvenance
     */
    public function getProvenance()
    {
        return $this->Provenance;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRProvenance $Provenance
     * @return $this
     */
    public function setProvenance($Provenance)
    {
        $this->Provenance = $Provenance;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRQuestionnaire
     */
    public function getQuestionnaire()
    {
        return $this->Questionnaire;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRQuestionnaire $Questionnaire
     * @return $this
     */
    public function setQuestionnaire($Questionnaire)
    {
        $this->Questionnaire = $Questionnaire;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRQuestionnaireResponse
     */
    public function getQuestionnaireResponse()
    {
        return $this->QuestionnaireResponse;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRQuestionnaireResponse $QuestionnaireResponse
     * @return $this
     */
    public function setQuestionnaireResponse($QuestionnaireResponse)
    {
        $this->QuestionnaireResponse = $QuestionnaireResponse;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRReferralRequest
     */
    public function getReferralRequest()
    {
        return $this->ReferralRequest;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRReferralRequest $ReferralRequest
     * @return $this
     */
    public function setReferralRequest($ReferralRequest)
    {
        $this->ReferralRequest = $ReferralRequest;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRRelatedPerson
     */
    public function getRelatedPerson()
    {
        return $this->RelatedPerson;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRRelatedPerson $RelatedPerson
     * @return $this
     */
    public function setRelatedPerson($RelatedPerson)
    {
        $this->RelatedPerson = $RelatedPerson;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRRequestGroup
     */
    public function getRequestGroup()
    {
        return $this->RequestGroup;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRRequestGroup $RequestGroup
     * @return $this
     */
    public function setRequestGroup($RequestGroup)
    {
        $this->RequestGroup = $RequestGroup;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRResearchStudy
     */
    public function getResearchStudy()
    {
        return $this->ResearchStudy;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRResearchStudy $ResearchStudy
     * @return $this
     */
    public function setResearchStudy($ResearchStudy)
    {
        $this->ResearchStudy = $ResearchStudy;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRResearchSubject
     */
    public function getResearchSubject()
    {
        return $this->ResearchSubject;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRResearchSubject $ResearchSubject
     * @return $this
     */
    public function setResearchSubject($ResearchSubject)
    {
        $this->ResearchSubject = $ResearchSubject;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRRiskAssessment
     */
    public function getRiskAssessment()
    {
        return $this->RiskAssessment;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRRiskAssessment $RiskAssessment
     * @return $this
     */
    public function setRiskAssessment($RiskAssessment)
    {
        $this->RiskAssessment = $RiskAssessment;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRSchedule
     */
    public function getSchedule()
    {
        return $this->Schedule;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRSchedule $Schedule
     * @return $this
     */
    public function setSchedule($Schedule)
    {
        $this->Schedule = $Schedule;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRSearchParameter
     */
    public function getSearchParameter()
    {
        return $this->SearchParameter;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRSearchParameter $SearchParameter
     * @return $this
     */
    public function setSearchParameter($SearchParameter)
    {
        $this->SearchParameter = $SearchParameter;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRSequence
     */
    public function getSequence()
    {
        return $this->Sequence;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRSequence $Sequence
     * @return $this
     */
    public function setSequence($Sequence)
    {
        $this->Sequence = $Sequence;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRServiceDefinition
     */
    public function getServiceDefinition()
    {
        return $this->ServiceDefinition;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRServiceDefinition $ServiceDefinition
     * @return $this
     */
    public function setServiceDefinition($ServiceDefinition)
    {
        $this->ServiceDefinition = $ServiceDefinition;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRSlot
     */
    public function getSlot()
    {
        return $this->Slot;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRSlot $Slot
     * @return $this
     */
    public function setSlot($Slot)
    {
        $this->Slot = $Slot;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRSpecimen
     */
    public function getSpecimen()
    {
        return $this->Specimen;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRSpecimen $Specimen
     * @return $this
     */
    public function setSpecimen($Specimen)
    {
        $this->Specimen = $Specimen;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRStructureDefinition
     */
    public function getStructureDefinition()
    {
        return $this->StructureDefinition;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRStructureDefinition $StructureDefinition
     * @return $this
     */
    public function setStructureDefinition($StructureDefinition)
    {
        $this->StructureDefinition = $StructureDefinition;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRStructureMap
     */
    public function getStructureMap()
    {
        return $this->StructureMap;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRStructureMap $StructureMap
     * @return $this
     */
    public function setStructureMap($StructureMap)
    {
        $this->StructureMap = $StructureMap;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRSubscription
     */
    public function getSubscription()
    {
        return $this->Subscription;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRSubscription $Subscription
     * @return $this
     */
    public function setSubscription($Subscription)
    {
        $this->Subscription = $Subscription;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRSubstance
     */
    public function getSubstance()
    {
        return $this->Substance;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRSubstance $Substance
     * @return $this
     */
    public function setSubstance($Substance)
    {
        $this->Substance = $Substance;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRSupplyDelivery
     */
    public function getSupplyDelivery()
    {
        return $this->SupplyDelivery;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRSupplyDelivery $SupplyDelivery
     * @return $this
     */
    public function setSupplyDelivery($SupplyDelivery)
    {
        $this->SupplyDelivery = $SupplyDelivery;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRSupplyRequest
     */
    public function getSupplyRequest()
    {
        return $this->SupplyRequest;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRSupplyRequest $SupplyRequest
     * @return $this
     */
    public function setSupplyRequest($SupplyRequest)
    {
        $this->SupplyRequest = $SupplyRequest;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRTask
     */
    public function getTask()
    {
        return $this->Task;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRTask $Task
     * @return $this
     */
    public function setTask($Task)
    {
        $this->Task = $Task;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRTestReport
     */
    public function getTestReport()
    {
        return $this->TestReport;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRTestReport $TestReport
     * @return $this
     */
    public function setTestReport($TestReport)
    {
        $this->TestReport = $TestReport;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRTestScript
     */
    public function getTestScript()
    {
        return $this->TestScript;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRTestScript $TestScript
     * @return $this
     */
    public function setTestScript($TestScript)
    {
        $this->TestScript = $TestScript;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRValueSet
     */
    public function getValueSet()
    {
        return $this->ValueSet;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRValueSet $ValueSet
     * @return $this
     */
    public function setValueSet($ValueSet)
    {
        $this->ValueSet = $ValueSet;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRDomainResource\FHIRVisionPrescription
     */
    public function getVisionPrescription()
    {
        return $this->VisionPrescription;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRDomainResource\FHIRVisionPrescription $VisionPrescription
     * @return $this
     */
    public function setVisionPrescription($VisionPrescription)
    {
        $this->VisionPrescription = $VisionPrescription;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRParameters
     */
    public function getParameters()
    {
        return $this->Parameters;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRParameters $Parameters
     * @return $this
     */
    public function setParameters($Parameters)
    {
        $this->Parameters = $Parameters;
        return $this;
    }

    /**
     * @return string
     */
    public function get_fhirElementName()
    {
        return $this->_fhirElementName;
    }

    /**
     * @param mixed $data
     */
    public function __construct($data = [])
    {
        if (is_object($data)) {
            $n = substr(strrchr(get_class($data), 'FHIR'), 4);
            $this->{"set{$n}"}($data);
        } else if (is_array($data)) {
            if (($cnt = count($data)) > 1) {
                throw new \InvalidArgumentException("ResourceContainers may only contain 1 object, \"{$cnt}\" values provided");
            } else {
                $k = key($data);
                $this->{"set{$k}"}($data);
            }
        } else if (null !== $data) {
            throw new \InvalidArgumentException('$data expected to be object or array, saw '.gettype($data));
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->get_fhirElementName();
    }

    /**
     * @return mixed
     */
    public function jsonSerialize()
    {
        if (isset($this->Account)) {
            return $this->Account;
        }
        if (isset($this->ActivityDefinition)) {
            return $this->ActivityDefinition;
        }
        if (isset($this->AdverseEvent)) {
            return $this->AdverseEvent;
        }
        if (isset($this->AllergyIntolerance)) {
            return $this->AllergyIntolerance;
        }
        if (isset($this->Appointment)) {
            return $this->Appointment;
        }
        if (isset($this->AppointmentResponse)) {
            return $this->AppointmentResponse;
        }
        if (isset($this->AuditEvent)) {
            return $this->AuditEvent;
        }
        if (isset($this->Basic)) {
            return $this->Basic;
        }
        if (isset($this->Binary)) {
            return $this->Binary;
        }
        if (isset($this->BodySite)) {
            return $this->BodySite;
        }
        if (isset($this->Bundle)) {
            return $this->Bundle;
        }
        if (isset($this->CapabilityStatement)) {
            return $this->CapabilityStatement;
        }
        if (isset($this->CarePlan)) {
            return $this->CarePlan;
        }
        if (isset($this->CareTeam)) {
            return $this->CareTeam;
        }
        if (isset($this->ChargeItem)) {
            return $this->ChargeItem;
        }
        if (isset($this->Claim)) {
            return $this->Claim;
        }
        if (isset($this->ClaimResponse)) {
            return $this->ClaimResponse;
        }
        if (isset($this->ClinicalImpression)) {
            return $this->ClinicalImpression;
        }
        if (isset($this->CodeSystem)) {
            return $this->CodeSystem;
        }
        if (isset($this->Communication)) {
            return $this->Communication;
        }
        if (isset($this->CommunicationRequest)) {
            return $this->CommunicationRequest;
        }
        if (isset($this->CompartmentDefinition)) {
            return $this->CompartmentDefinition;
        }
        if (isset($this->Composition)) {
            return $this->Composition;
        }
        if (isset($this->ConceptMap)) {
            return $this->ConceptMap;
        }
        if (isset($this->Condition)) {
            return $this->Condition;
        }
        if (isset($this->Consent)) {
            return $this->Consent;
        }
        if (isset($this->Contract)) {
            return $this->Contract;
        }
        if (isset($this->Coverage)) {
            return $this->Coverage;
        }
        if (isset($this->DataElement)) {
            return $this->DataElement;
        }
        if (isset($this->DetectedIssue)) {
            return $this->DetectedIssue;
        }
        if (isset($this->Device)) {
            return $this->Device;
        }
        if (isset($this->DeviceComponent)) {
            return $this->DeviceComponent;
        }
        if (isset($this->DeviceMetric)) {
            return $this->DeviceMetric;
        }
        if (isset($this->DeviceRequest)) {
            return $this->DeviceRequest;
        }
        if (isset($this->DeviceUseStatement)) {
            return $this->DeviceUseStatement;
        }
        if (isset($this->DiagnosticReport)) {
            return $this->DiagnosticReport;
        }
        if (isset($this->DocumentManifest)) {
            return $this->DocumentManifest;
        }
        if (isset($this->DocumentReference)) {
            return $this->DocumentReference;
        }
        if (isset($this->EligibilityRequest)) {
            return $this->EligibilityRequest;
        }
        if (isset($this->EligibilityResponse)) {
            return $this->EligibilityResponse;
        }
        if (isset($this->Encounter)) {
            return $this->Encounter;
        }
        if (isset($this->Endpoint)) {
            return $this->Endpoint;
        }
        if (isset($this->EnrollmentRequest)) {
            return $this->EnrollmentRequest;
        }
        if (isset($this->EnrollmentResponse)) {
            return $this->EnrollmentResponse;
        }
        if (isset($this->EpisodeOfCare)) {
            return $this->EpisodeOfCare;
        }
        if (isset($this->ExpansionProfile)) {
            return $this->ExpansionProfile;
        }
        if (isset($this->ExplanationOfBenefit)) {
            return $this->ExplanationOfBenefit;
        }
        if (isset($this->FamilyMemberHistory)) {
            return $this->FamilyMemberHistory;
        }
        if (isset($this->Flag)) {
            return $this->Flag;
        }
        if (isset($this->Goal)) {
            return $this->Goal;
        }
        if (isset($this->GraphDefinition)) {
            return $this->GraphDefinition;
        }
        if (isset($this->Group)) {
            return $this->Group;
        }
        if (isset($this->GuidanceResponse)) {
            return $this->GuidanceResponse;
        }
        if (isset($this->HealthcareService)) {
            return $this->HealthcareService;
        }
        if (isset($this->ImagingManifest)) {
            return $this->ImagingManifest;
        }
        if (isset($this->ImagingStudy)) {
            return $this->ImagingStudy;
        }
        if (isset($this->Immunization)) {
            return $this->Immunization;
        }
        if (isset($this->ImmunizationRecommendation)) {
            return $this->ImmunizationRecommendation;
        }
        if (isset($this->ImplementationGuide)) {
            return $this->ImplementationGuide;
        }
        if (isset($this->Library)) {
            return $this->Library;
        }
        if (isset($this->Linkage)) {
            return $this->Linkage;
        }
        if (isset($this->List)) {
            return $this->List;
        }
        if (isset($this->Location)) {
            return $this->Location;
        }
        if (isset($this->Measure)) {
            return $this->Measure;
        }
        if (isset($this->MeasureReport)) {
            return $this->MeasureReport;
        }
        if (isset($this->Media)) {
            return $this->Media;
        }
        if (isset($this->Medication)) {
            return $this->Medication;
        }
        if (isset($this->MedicationAdministration)) {
            return $this->MedicationAdministration;
        }
        if (isset($this->MedicationDispense)) {
            return $this->MedicationDispense;
        }
        if (isset($this->MedicationRequest)) {
            return $this->MedicationRequest;
        }
        if (isset($this->MedicationStatement)) {
            return $this->MedicationStatement;
        }
        if (isset($this->MessageDefinition)) {
            return $this->MessageDefinition;
        }
        if (isset($this->MessageHeader)) {
            return $this->MessageHeader;
        }
        if (isset($this->NamingSystem)) {
            return $this->NamingSystem;
        }
        if (isset($this->NutritionOrder)) {
            return $this->NutritionOrder;
        }
        if (isset($this->Observation)) {
            return $this->Observation;
        }
        if (isset($this->OperationDefinition)) {
            return $this->OperationDefinition;
        }
        if (isset($this->OperationOutcome)) {
            return $this->OperationOutcome;
        }
        if (isset($this->Organization)) {
            return $this->Organization;
        }
        if (isset($this->Patient)) {
            return $this->Patient;
        }
        if (isset($this->PaymentNotice)) {
            return $this->PaymentNotice;
        }
        if (isset($this->PaymentReconciliation)) {
            return $this->PaymentReconciliation;
        }
        if (isset($this->Person)) {
            return $this->Person;
        }
        if (isset($this->PlanDefinition)) {
            return $this->PlanDefinition;
        }
        if (isset($this->Practitioner)) {
            return $this->Practitioner;
        }
        if (isset($this->PractitionerRole)) {
            return $this->PractitionerRole;
        }
        if (isset($this->Procedure)) {
            return $this->Procedure;
        }
        if (isset($this->ProcedureRequest)) {
            return $this->ProcedureRequest;
        }
        if (isset($this->ProcessRequest)) {
            return $this->ProcessRequest;
        }
        if (isset($this->ProcessResponse)) {
            return $this->ProcessResponse;
        }
        if (isset($this->Provenance)) {
            return $this->Provenance;
        }
        if (isset($this->Questionnaire)) {
            return $this->Questionnaire;
        }
        if (isset($this->QuestionnaireResponse)) {
            return $this->QuestionnaireResponse;
        }
        if (isset($this->ReferralRequest)) {
            return $this->ReferralRequest;
        }
        if (isset($this->RelatedPerson)) {
            return $this->RelatedPerson;
        }
        if (isset($this->RequestGroup)) {
            return $this->RequestGroup;
        }
        if (isset($this->ResearchStudy)) {
            return $this->ResearchStudy;
        }
        if (isset($this->ResearchSubject)) {
            return $this->ResearchSubject;
        }
        if (isset($this->RiskAssessment)) {
            return $this->RiskAssessment;
        }
        if (isset($this->Schedule)) {
            return $this->Schedule;
        }
        if (isset($this->SearchParameter)) {
            return $this->SearchParameter;
        }
        if (isset($this->Sequence)) {
            return $this->Sequence;
        }
        if (isset($this->ServiceDefinition)) {
            return $this->ServiceDefinition;
        }
        if (isset($this->Slot)) {
            return $this->Slot;
        }
        if (isset($this->Specimen)) {
            return $this->Specimen;
        }
        if (isset($this->StructureDefinition)) {
            return $this->StructureDefinition;
        }
        if (isset($this->StructureMap)) {
            return $this->StructureMap;
        }
        if (isset($this->Subscription)) {
            return $this->Subscription;
        }
        if (isset($this->Substance)) {
            return $this->Substance;
        }
        if (isset($this->SupplyDelivery)) {
            return $this->SupplyDelivery;
        }
        if (isset($this->SupplyRequest)) {
            return $this->SupplyRequest;
        }
        if (isset($this->Task)) {
            return $this->Task;
        }
        if (isset($this->TestReport)) {
            return $this->TestReport;
        }
        if (isset($this->TestScript)) {
            return $this->TestScript;
        }
        if (isset($this->ValueSet)) {
            return $this->ValueSet;
        }
        if (isset($this->VisionPrescription)) {
            return $this->VisionPrescription;
        }
        if (isset($this->Parameters)) {
            return $this->Parameters;
        }
        return null;
    }

    /**
     * @param boolean $returnSXE
     * @param \SimpleXMLElement $sxe
     * @return string|\SimpleXMLElement
     */
    public function xmlSerialize($returnSXE = false, $sxe = null)
    {
        if (null === $sxe) {
            $sxe = new \SimpleXMLElement('<ResourceContainer xmlns="http://hl7.org/fhir"></ResourceContainer>');
        }
        if (isset($this->Account)) {
            $this->Account->xmlSerialize(true, $sxe->addChild('Account'));
        } else if (isset($this->ActivityDefinition)) {
            $this->ActivityDefinition->xmlSerialize(true, $sxe->addChild('ActivityDefinition'));
        } else if (isset($this->AdverseEvent)) {
            $this->AdverseEvent->xmlSerialize(true, $sxe->addChild('AdverseEvent'));
        } else if (isset($this->AllergyIntolerance)) {
            $this->AllergyIntolerance->xmlSerialize(true, $sxe->addChild('AllergyIntolerance'));
        } else if (isset($this->Appointment)) {
            $this->Appointment->xmlSerialize(true, $sxe->addChild('Appointment'));
        } else if (isset($this->AppointmentResponse)) {
            $this->AppointmentResponse->xmlSerialize(true, $sxe->addChild('AppointmentResponse'));
        } else if (isset($this->AuditEvent)) {
            $this->AuditEvent->xmlSerialize(true, $sxe->addChild('AuditEvent'));
        } else if (isset($this->Basic)) {
            $this->Basic->xmlSerialize(true, $sxe->addChild('Basic'));
        } else if (isset($this->Binary)) {
            $this->Binary->xmlSerialize(true, $sxe->addChild('Binary'));
        } else if (isset($this->BodySite)) {
            $this->BodySite->xmlSerialize(true, $sxe->addChild('BodySite'));
        } else if (isset($this->Bundle)) {
            $this->Bundle->xmlSerialize(true, $sxe->addChild('Bundle'));
        } else if (isset($this->CapabilityStatement)) {
            $this->CapabilityStatement->xmlSerialize(true, $sxe->addChild('CapabilityStatement'));
        } else if (isset($this->CarePlan)) {
            $this->CarePlan->xmlSerialize(true, $sxe->addChild('CarePlan'));
        } else if (isset($this->CareTeam)) {
            $this->CareTeam->xmlSerialize(true, $sxe->addChild('CareTeam'));
        } else if (isset($this->ChargeItem)) {
            $this->ChargeItem->xmlSerialize(true, $sxe->addChild('ChargeItem'));
        } else if (isset($this->Claim)) {
            $this->Claim->xmlSerialize(true, $sxe->addChild('Claim'));
        } else if (isset($this->ClaimResponse)) {
            $this->ClaimResponse->xmlSerialize(true, $sxe->addChild('ClaimResponse'));
        } else if (isset($this->ClinicalImpression)) {
            $this->ClinicalImpression->xmlSerialize(true, $sxe->addChild('ClinicalImpression'));
        } else if (isset($this->CodeSystem)) {
            $this->CodeSystem->xmlSerialize(true, $sxe->addChild('CodeSystem'));
        } else if (isset($this->Communication)) {
            $this->Communication->xmlSerialize(true, $sxe->addChild('Communication'));
        } else if (isset($this->CommunicationRequest)) {
            $this->CommunicationRequest->xmlSerialize(true, $sxe->addChild('CommunicationRequest'));
        } else if (isset($this->CompartmentDefinition)) {
            $this->CompartmentDefinition->xmlSerialize(true, $sxe->addChild('CompartmentDefinition'));
        } else if (isset($this->Composition)) {
            $this->Composition->xmlSerialize(true, $sxe->addChild('Composition'));
        } else if (isset($this->ConceptMap)) {
            $this->ConceptMap->xmlSerialize(true, $sxe->addChild('ConceptMap'));
        } else if (isset($this->Condition)) {
            $this->Condition->xmlSerialize(true, $sxe->addChild('Condition'));
        } else if (isset($this->Consent)) {
            $this->Consent->xmlSerialize(true, $sxe->addChild('Consent'));
        } else if (isset($this->Contract)) {
            $this->Contract->xmlSerialize(true, $sxe->addChild('Contract'));
        } else if (isset($this->Coverage)) {
            $this->Coverage->xmlSerialize(true, $sxe->addChild('Coverage'));
        } else if (isset($this->DataElement)) {
            $this->DataElement->xmlSerialize(true, $sxe->addChild('DataElement'));
        } else if (isset($this->DetectedIssue)) {
            $this->DetectedIssue->xmlSerialize(true, $sxe->addChild('DetectedIssue'));
        } else if (isset($this->Device)) {
            $this->Device->xmlSerialize(true, $sxe->addChild('Device'));
        } else if (isset($this->DeviceComponent)) {
            $this->DeviceComponent->xmlSerialize(true, $sxe->addChild('DeviceComponent'));
        } else if (isset($this->DeviceMetric)) {
            $this->DeviceMetric->xmlSerialize(true, $sxe->addChild('DeviceMetric'));
        } else if (isset($this->DeviceRequest)) {
            $this->DeviceRequest->xmlSerialize(true, $sxe->addChild('DeviceRequest'));
        } else if (isset($this->DeviceUseStatement)) {
            $this->DeviceUseStatement->xmlSerialize(true, $sxe->addChild('DeviceUseStatement'));
        } else if (isset($this->DiagnosticReport)) {
            $this->DiagnosticReport->xmlSerialize(true, $sxe->addChild('DiagnosticReport'));
        } else if (isset($this->DocumentManifest)) {
            $this->DocumentManifest->xmlSerialize(true, $sxe->addChild('DocumentManifest'));
        } else if (isset($this->DocumentReference)) {
            $this->DocumentReference->xmlSerialize(true, $sxe->addChild('DocumentReference'));
        } else if (isset($this->EligibilityRequest)) {
            $this->EligibilityRequest->xmlSerialize(true, $sxe->addChild('EligibilityRequest'));
        } else if (isset($this->EligibilityResponse)) {
            $this->EligibilityResponse->xmlSerialize(true, $sxe->addChild('EligibilityResponse'));
        } else if (isset($this->Encounter)) {
            $this->Encounter->xmlSerialize(true, $sxe->addChild('Encounter'));
        } else if (isset($this->Endpoint)) {
            $this->Endpoint->xmlSerialize(true, $sxe->addChild('Endpoint'));
        } else if (isset($this->EnrollmentRequest)) {
            $this->EnrollmentRequest->xmlSerialize(true, $sxe->addChild('EnrollmentRequest'));
        } else if (isset($this->EnrollmentResponse)) {
            $this->EnrollmentResponse->xmlSerialize(true, $sxe->addChild('EnrollmentResponse'));
        } else if (isset($this->EpisodeOfCare)) {
            $this->EpisodeOfCare->xmlSerialize(true, $sxe->addChild('EpisodeOfCare'));
        } else if (isset($this->ExpansionProfile)) {
            $this->ExpansionProfile->xmlSerialize(true, $sxe->addChild('ExpansionProfile'));
        } else if (isset($this->ExplanationOfBenefit)) {
            $this->ExplanationOfBenefit->xmlSerialize(true, $sxe->addChild('ExplanationOfBenefit'));
        } else if (isset($this->FamilyMemberHistory)) {
            $this->FamilyMemberHistory->xmlSerialize(true, $sxe->addChild('FamilyMemberHistory'));
        } else if (isset($this->Flag)) {
            $this->Flag->xmlSerialize(true, $sxe->addChild('Flag'));
        } else if (isset($this->Goal)) {
            $this->Goal->xmlSerialize(true, $sxe->addChild('Goal'));
        } else if (isset($this->GraphDefinition)) {
            $this->GraphDefinition->xmlSerialize(true, $sxe->addChild('GraphDefinition'));
        } else if (isset($this->Group)) {
            $this->Group->xmlSerialize(true, $sxe->addChild('Group'));
        } else if (isset($this->GuidanceResponse)) {
            $this->GuidanceResponse->xmlSerialize(true, $sxe->addChild('GuidanceResponse'));
        } else if (isset($this->HealthcareService)) {
            $this->HealthcareService->xmlSerialize(true, $sxe->addChild('HealthcareService'));
        } else if (isset($this->ImagingManifest)) {
            $this->ImagingManifest->xmlSerialize(true, $sxe->addChild('ImagingManifest'));
        } else if (isset($this->ImagingStudy)) {
            $this->ImagingStudy->xmlSerialize(true, $sxe->addChild('ImagingStudy'));
        } else if (isset($this->Immunization)) {
            $this->Immunization->xmlSerialize(true, $sxe->addChild('Immunization'));
        } else if (isset($this->ImmunizationRecommendation)) {
            $this->ImmunizationRecommendation->xmlSerialize(true, $sxe->addChild('ImmunizationRecommendation'));
        } else if (isset($this->ImplementationGuide)) {
            $this->ImplementationGuide->xmlSerialize(true, $sxe->addChild('ImplementationGuide'));
        } else if (isset($this->Library)) {
            $this->Library->xmlSerialize(true, $sxe->addChild('Library'));
        } else if (isset($this->Linkage)) {
            $this->Linkage->xmlSerialize(true, $sxe->addChild('Linkage'));
        } else if (isset($this->List)) {
            $this->List->xmlSerialize(true, $sxe->addChild('List'));
        } else if (isset($this->Location)) {
            $this->Location->xmlSerialize(true, $sxe->addChild('Location'));
        } else if (isset($this->Measure)) {
            $this->Measure->xmlSerialize(true, $sxe->addChild('Measure'));
        } else if (isset($this->MeasureReport)) {
            $this->MeasureReport->xmlSerialize(true, $sxe->addChild('MeasureReport'));
        } else if (isset($this->Media)) {
            $this->Media->xmlSerialize(true, $sxe->addChild('Media'));
        } else if (isset($this->Medication)) {
            $this->Medication->xmlSerialize(true, $sxe->addChild('Medication'));
        } else if (isset($this->MedicationAdministration)) {
            $this->MedicationAdministration->xmlSerialize(true, $sxe->addChild('MedicationAdministration'));
        } else if (isset($this->MedicationDispense)) {
            $this->MedicationDispense->xmlSerialize(true, $sxe->addChild('MedicationDispense'));
        } else if (isset($this->MedicationRequest)) {
            $this->MedicationRequest->xmlSerialize(true, $sxe->addChild('MedicationRequest'));
        } else if (isset($this->MedicationStatement)) {
            $this->MedicationStatement->xmlSerialize(true, $sxe->addChild('MedicationStatement'));
        } else if (isset($this->MessageDefinition)) {
            $this->MessageDefinition->xmlSerialize(true, $sxe->addChild('MessageDefinition'));
        } else if (isset($this->MessageHeader)) {
            $this->MessageHeader->xmlSerialize(true, $sxe->addChild('MessageHeader'));
        } else if (isset($this->NamingSystem)) {
            $this->NamingSystem->xmlSerialize(true, $sxe->addChild('NamingSystem'));
        } else if (isset($this->NutritionOrder)) {
            $this->NutritionOrder->xmlSerialize(true, $sxe->addChild('NutritionOrder'));
        } else if (isset($this->Observation)) {
            $this->Observation->xmlSerialize(true, $sxe->addChild('Observation'));
        } else if (isset($this->OperationDefinition)) {
            $this->OperationDefinition->xmlSerialize(true, $sxe->addChild('OperationDefinition'));
        } else if (isset($this->OperationOutcome)) {
            $this->OperationOutcome->xmlSerialize(true, $sxe->addChild('OperationOutcome'));
        } else if (isset($this->Organization)) {
            $this->Organization->xmlSerialize(true, $sxe->addChild('Organization'));
        } else if (isset($this->Patient)) {
            $this->Patient->xmlSerialize(true, $sxe->addChild('Patient'));
        } else if (isset($this->PaymentNotice)) {
            $this->PaymentNotice->xmlSerialize(true, $sxe->addChild('PaymentNotice'));
        } else if (isset($this->PaymentReconciliation)) {
            $this->PaymentReconciliation->xmlSerialize(true, $sxe->addChild('PaymentReconciliation'));
        } else if (isset($this->Person)) {
            $this->Person->xmlSerialize(true, $sxe->addChild('Person'));
        } else if (isset($this->PlanDefinition)) {
            $this->PlanDefinition->xmlSerialize(true, $sxe->addChild('PlanDefinition'));
        } else if (isset($this->Practitioner)) {
            $this->Practitioner->xmlSerialize(true, $sxe->addChild('Practitioner'));
        } else if (isset($this->PractitionerRole)) {
            $this->PractitionerRole->xmlSerialize(true, $sxe->addChild('PractitionerRole'));
        } else if (isset($this->Procedure)) {
            $this->Procedure->xmlSerialize(true, $sxe->addChild('Procedure'));
        } else if (isset($this->ProcedureRequest)) {
            $this->ProcedureRequest->xmlSerialize(true, $sxe->addChild('ProcedureRequest'));
        } else if (isset($this->ProcessRequest)) {
            $this->ProcessRequest->xmlSerialize(true, $sxe->addChild('ProcessRequest'));
        } else if (isset($this->ProcessResponse)) {
            $this->ProcessResponse->xmlSerialize(true, $sxe->addChild('ProcessResponse'));
        } else if (isset($this->Provenance)) {
            $this->Provenance->xmlSerialize(true, $sxe->addChild('Provenance'));
        } else if (isset($this->Questionnaire)) {
            $this->Questionnaire->xmlSerialize(true, $sxe->addChild('Questionnaire'));
        } else if (isset($this->QuestionnaireResponse)) {
            $this->QuestionnaireResponse->xmlSerialize(true, $sxe->addChild('QuestionnaireResponse'));
        } else if (isset($this->ReferralRequest)) {
            $this->ReferralRequest->xmlSerialize(true, $sxe->addChild('ReferralRequest'));
        } else if (isset($this->RelatedPerson)) {
            $this->RelatedPerson->xmlSerialize(true, $sxe->addChild('RelatedPerson'));
        } else if (isset($this->RequestGroup)) {
            $this->RequestGroup->xmlSerialize(true, $sxe->addChild('RequestGroup'));
        } else if (isset($this->ResearchStudy)) {
            $this->ResearchStudy->xmlSerialize(true, $sxe->addChild('ResearchStudy'));
        } else if (isset($this->ResearchSubject)) {
            $this->ResearchSubject->xmlSerialize(true, $sxe->addChild('ResearchSubject'));
        } else if (isset($this->RiskAssessment)) {
            $this->RiskAssessment->xmlSerialize(true, $sxe->addChild('RiskAssessment'));
        } else if (isset($this->Schedule)) {
            $this->Schedule->xmlSerialize(true, $sxe->addChild('Schedule'));
        } else if (isset($this->SearchParameter)) {
            $this->SearchParameter->xmlSerialize(true, $sxe->addChild('SearchParameter'));
        } else if (isset($this->Sequence)) {
            $this->Sequence->xmlSerialize(true, $sxe->addChild('Sequence'));
        } else if (isset($this->ServiceDefinition)) {
            $this->ServiceDefinition->xmlSerialize(true, $sxe->addChild('ServiceDefinition'));
        } else if (isset($this->Slot)) {
            $this->Slot->xmlSerialize(true, $sxe->addChild('Slot'));
        } else if (isset($this->Specimen)) {
            $this->Specimen->xmlSerialize(true, $sxe->addChild('Specimen'));
        } else if (isset($this->StructureDefinition)) {
            $this->StructureDefinition->xmlSerialize(true, $sxe->addChild('StructureDefinition'));
        } else if (isset($this->StructureMap)) {
            $this->StructureMap->xmlSerialize(true, $sxe->addChild('StructureMap'));
        } else if (isset($this->Subscription)) {
            $this->Subscription->xmlSerialize(true, $sxe->addChild('Subscription'));
        } else if (isset($this->Substance)) {
            $this->Substance->xmlSerialize(true, $sxe->addChild('Substance'));
        } else if (isset($this->SupplyDelivery)) {
            $this->SupplyDelivery->xmlSerialize(true, $sxe->addChild('SupplyDelivery'));
        } else if (isset($this->SupplyRequest)) {
            $this->SupplyRequest->xmlSerialize(true, $sxe->addChild('SupplyRequest'));
        } else if (isset($this->Task)) {
            $this->Task->xmlSerialize(true, $sxe->addChild('Task'));
        } else if (isset($this->TestReport)) {
            $this->TestReport->xmlSerialize(true, $sxe->addChild('TestReport'));
        } else if (isset($this->TestScript)) {
            $this->TestScript->xmlSerialize(true, $sxe->addChild('TestScript'));
        } else if (isset($this->ValueSet)) {
            $this->ValueSet->xmlSerialize(true, $sxe->addChild('ValueSet'));
        } else if (isset($this->VisionPrescription)) {
            $this->VisionPrescription->xmlSerialize(true, $sxe->addChild('VisionPrescription'));
        } else if (isset($this->Parameters)) {
            $this->Parameters->xmlSerialize(true, $sxe->addChild('Parameters'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }

    /**
     * @return mixed
     */
    public function getResource()
    {
        return $this->jsonSerialize();
    }
}
