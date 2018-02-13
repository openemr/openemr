<?php namespace HL7\FHIR\STU3\FHIRDomainResource;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRResource\FHIRDomainResource;

/**
 * This resource provides: the claim details; adjudication details from the processing of a Claim; and optionally account balance information, for informing the subscriber of the benefits provided.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRExplanationOfBenefit extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * The EOB Business Identifier.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = [];

    /**
     * The status of the resource instance.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRExplanationOfBenefitStatus
     */
    public $status = null;

    /**
     * The category of claim, eg, oral, pharmacy, vision, insitutional, professional.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $type = null;

    /**
     * A finer grained suite of claim subtype codes which may convey Inpatient vs Outpatient and/or a specialty service. In the US the BillType.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public $subType = [];

    /**
     * Patient Resource.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $patient = null;

    /**
     * The billable period for which charges are being submitted.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRPeriod
     */
    public $billablePeriod = null;

    /**
     * The date when the EOB was created.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDateTime
     */
    public $created = null;

    /**
     * The person who created the explanation of benefit.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $enterer = null;

    /**
     * The insurer which is responsible for the explanation of benefit.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $insurer = null;

    /**
     * The provider which is responsible for the claim.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $provider = null;

    /**
     * The provider which is responsible for the claim.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $organization = null;

    /**
     * The referral resource which lists the date, practitioner, reason and other supporting information.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $referral = null;

    /**
     * Facility where the services were provided.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $facility = null;

    /**
     * The business identifier for the instance: invoice number, claim number, pre-determination or pre-authorization number.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $claim = null;

    /**
     * The business identifier for the instance: invoice number, claim number, pre-determination or pre-authorization number.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $claimResponse = null;

    /**
     * Processing outcome errror, partial or complete processing.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $outcome = null;

    /**
     * A description of the status of the adjudication.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $disposition = null;

    /**
     * Other claims which are related to this claim such as prior claim versions or for related services.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitRelated[]
     */
    public $related = [];

    /**
     * Prescription to support the dispensing of Pharmacy or Vision products.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $prescription = null;

    /**
     * Original prescription which has been superceded by this prescription to support the dispensing of pharmacy services, medications or products. For example, a physician may prescribe a medication which the pharmacy determines is contraindicated, or for which the patient has an intolerance, and therefor issues a new precription for an alternate medication which has the same theraputic intent. The prescription from the pharmacy becomes the 'prescription' and that from the physician becomes the 'original prescription'.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $originalPrescription = null;

    /**
     * The party to be reimbursed for the services.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitPayee
     */
    public $payee = null;

    /**
     * Additional information codes regarding exceptions, special considerations, the condition, situation, prior or concurrent issues. Often there are mutiple jurisdiction specific valuesets which are required.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitInformation[]
     */
    public $information = [];

    /**
     * The members of the team who provided the overall service as well as their role and whether responsible and qualifications.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitCareTeam[]
     */
    public $careTeam = [];

    /**
     * Ordered list of patient diagnosis for which care is sought.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitDiagnosis[]
     */
    public $diagnosis = [];

    /**
     * Ordered list of patient procedures performed to support the adjudication.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitProcedure[]
     */
    public $procedure = [];

    /**
     * Precedence (primary, secondary, etc.).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt
     */
    public $precedence = null;

    /**
     * Financial instrument by which payment information for health care.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitInsurance
     */
    public $insurance = null;

    /**
     * An accident which resulted in the need for healthcare services.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitAccident
     */
    public $accident = null;

    /**
     * The start and optional end dates of when the patient was precluded from working due to the treatable condition(s).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRPeriod
     */
    public $employmentImpacted = null;

    /**
     * The start and optional end dates of when the patient was confined to a treatment center.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRPeriod
     */
    public $hospitalization = null;

    /**
     * First tier of goods and services.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitItem[]
     */
    public $item = [];

    /**
     * The first tier service adjudications for payor added services.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitAddItem[]
     */
    public $addItem = [];

    /**
     * The total cost of the services reported.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRMoney
     */
    public $totalCost = null;

    /**
     * The amount of deductable applied which was not allocated to any particular service line.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRMoney
     */
    public $unallocDeductable = null;

    /**
     * Total amount of benefit payable (Equal to sum of the Benefit amounts from all detail lines and additions less the Unallocated Deductable).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRMoney
     */
    public $totalBenefit = null;

    /**
     * Payment details for the claim if the claim has been paid.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitPayment
     */
    public $payment = null;

    /**
     * The form to be used for printing the content.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $form = null;

    /**
     * Note text.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitProcessNote[]
     */
    public $processNote = [];

    /**
     * Balance by Benefit Category.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitBenefitBalance[]
     */
    public $benefitBalance = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'ExplanationOfBenefit';

    /**
     * The EOB Business Identifier.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * The EOB Business Identifier.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * The status of the resource instance.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRExplanationOfBenefitStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * The status of the resource instance.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRExplanationOfBenefitStatus $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * The category of claim, eg, oral, pharmacy, vision, insitutional, professional.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * The category of claim, eg, oral, pharmacy, vision, insitutional, professional.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * A finer grained suite of claim subtype codes which may convey Inpatient vs Outpatient and/or a specialty service. In the US the BillType.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public function getSubType()
    {
        return $this->subType;
    }

    /**
     * A finer grained suite of claim subtype codes which may convey Inpatient vs Outpatient and/or a specialty service. In the US the BillType.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $subType
     * @return $this
     */
    public function addSubType($subType)
    {
        $this->subType[] = $subType;
        return $this;
    }

    /**
     * Patient Resource.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getPatient()
    {
        return $this->patient;
    }

    /**
     * Patient Resource.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $patient
     * @return $this
     */
    public function setPatient($patient)
    {
        $this->patient = $patient;
        return $this;
    }

    /**
     * The billable period for which charges are being submitted.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRPeriod
     */
    public function getBillablePeriod()
    {
        return $this->billablePeriod;
    }

    /**
     * The billable period for which charges are being submitted.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRPeriod $billablePeriod
     * @return $this
     */
    public function setBillablePeriod($billablePeriod)
    {
        $this->billablePeriod = $billablePeriod;
        return $this;
    }

    /**
     * The date when the EOB was created.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * The date when the EOB was created.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDateTime $created
     * @return $this
     */
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }

    /**
     * The person who created the explanation of benefit.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getEnterer()
    {
        return $this->enterer;
    }

    /**
     * The person who created the explanation of benefit.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $enterer
     * @return $this
     */
    public function setEnterer($enterer)
    {
        $this->enterer = $enterer;
        return $this;
    }

    /**
     * The insurer which is responsible for the explanation of benefit.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getInsurer()
    {
        return $this->insurer;
    }

    /**
     * The insurer which is responsible for the explanation of benefit.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $insurer
     * @return $this
     */
    public function setInsurer($insurer)
    {
        $this->insurer = $insurer;
        return $this;
    }

    /**
     * The provider which is responsible for the claim.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * The provider which is responsible for the claim.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $provider
     * @return $this
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
        return $this;
    }

    /**
     * The provider which is responsible for the claim.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * The provider which is responsible for the claim.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $organization
     * @return $this
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;
        return $this;
    }

    /**
     * The referral resource which lists the date, practitioner, reason and other supporting information.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getReferral()
    {
        return $this->referral;
    }

    /**
     * The referral resource which lists the date, practitioner, reason and other supporting information.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $referral
     * @return $this
     */
    public function setReferral($referral)
    {
        $this->referral = $referral;
        return $this;
    }

    /**
     * Facility where the services were provided.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getFacility()
    {
        return $this->facility;
    }

    /**
     * Facility where the services were provided.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $facility
     * @return $this
     */
    public function setFacility($facility)
    {
        $this->facility = $facility;
        return $this;
    }

    /**
     * The business identifier for the instance: invoice number, claim number, pre-determination or pre-authorization number.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getClaim()
    {
        return $this->claim;
    }

    /**
     * The business identifier for the instance: invoice number, claim number, pre-determination or pre-authorization number.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $claim
     * @return $this
     */
    public function setClaim($claim)
    {
        $this->claim = $claim;
        return $this;
    }

    /**
     * The business identifier for the instance: invoice number, claim number, pre-determination or pre-authorization number.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getClaimResponse()
    {
        return $this->claimResponse;
    }

    /**
     * The business identifier for the instance: invoice number, claim number, pre-determination or pre-authorization number.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $claimResponse
     * @return $this
     */
    public function setClaimResponse($claimResponse)
    {
        $this->claimResponse = $claimResponse;
        return $this;
    }

    /**
     * Processing outcome errror, partial or complete processing.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getOutcome()
    {
        return $this->outcome;
    }

    /**
     * Processing outcome errror, partial or complete processing.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $outcome
     * @return $this
     */
    public function setOutcome($outcome)
    {
        $this->outcome = $outcome;
        return $this;
    }

    /**
     * A description of the status of the adjudication.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getDisposition()
    {
        return $this->disposition;
    }

    /**
     * A description of the status of the adjudication.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $disposition
     * @return $this
     */
    public function setDisposition($disposition)
    {
        $this->disposition = $disposition;
        return $this;
    }

    /**
     * Other claims which are related to this claim such as prior claim versions or for related services.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitRelated[]
     */
    public function getRelated()
    {
        return $this->related;
    }

    /**
     * Other claims which are related to this claim such as prior claim versions or for related services.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitRelated $related
     * @return $this
     */
    public function addRelated($related)
    {
        $this->related[] = $related;
        return $this;
    }

    /**
     * Prescription to support the dispensing of Pharmacy or Vision products.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getPrescription()
    {
        return $this->prescription;
    }

    /**
     * Prescription to support the dispensing of Pharmacy or Vision products.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $prescription
     * @return $this
     */
    public function setPrescription($prescription)
    {
        $this->prescription = $prescription;
        return $this;
    }

    /**
     * Original prescription which has been superceded by this prescription to support the dispensing of pharmacy services, medications or products. For example, a physician may prescribe a medication which the pharmacy determines is contraindicated, or for which the patient has an intolerance, and therefor issues a new precription for an alternate medication which has the same theraputic intent. The prescription from the pharmacy becomes the 'prescription' and that from the physician becomes the 'original prescription'.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getOriginalPrescription()
    {
        return $this->originalPrescription;
    }

    /**
     * Original prescription which has been superceded by this prescription to support the dispensing of pharmacy services, medications or products. For example, a physician may prescribe a medication which the pharmacy determines is contraindicated, or for which the patient has an intolerance, and therefor issues a new precription for an alternate medication which has the same theraputic intent. The prescription from the pharmacy becomes the 'prescription' and that from the physician becomes the 'original prescription'.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $originalPrescription
     * @return $this
     */
    public function setOriginalPrescription($originalPrescription)
    {
        $this->originalPrescription = $originalPrescription;
        return $this;
    }

    /**
     * The party to be reimbursed for the services.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitPayee
     */
    public function getPayee()
    {
        return $this->payee;
    }

    /**
     * The party to be reimbursed for the services.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitPayee $payee
     * @return $this
     */
    public function setPayee($payee)
    {
        $this->payee = $payee;
        return $this;
    }

    /**
     * Additional information codes regarding exceptions, special considerations, the condition, situation, prior or concurrent issues. Often there are mutiple jurisdiction specific valuesets which are required.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitInformation[]
     */
    public function getInformation()
    {
        return $this->information;
    }

    /**
     * Additional information codes regarding exceptions, special considerations, the condition, situation, prior or concurrent issues. Often there are mutiple jurisdiction specific valuesets which are required.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitInformation $information
     * @return $this
     */
    public function addInformation($information)
    {
        $this->information[] = $information;
        return $this;
    }

    /**
     * The members of the team who provided the overall service as well as their role and whether responsible and qualifications.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitCareTeam[]
     */
    public function getCareTeam()
    {
        return $this->careTeam;
    }

    /**
     * The members of the team who provided the overall service as well as their role and whether responsible and qualifications.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitCareTeam $careTeam
     * @return $this
     */
    public function addCareTeam($careTeam)
    {
        $this->careTeam[] = $careTeam;
        return $this;
    }

    /**
     * Ordered list of patient diagnosis for which care is sought.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitDiagnosis[]
     */
    public function getDiagnosis()
    {
        return $this->diagnosis;
    }

    /**
     * Ordered list of patient diagnosis for which care is sought.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitDiagnosis $diagnosis
     * @return $this
     */
    public function addDiagnosis($diagnosis)
    {
        $this->diagnosis[] = $diagnosis;
        return $this;
    }

    /**
     * Ordered list of patient procedures performed to support the adjudication.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitProcedure[]
     */
    public function getProcedure()
    {
        return $this->procedure;
    }

    /**
     * Ordered list of patient procedures performed to support the adjudication.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitProcedure $procedure
     * @return $this
     */
    public function addProcedure($procedure)
    {
        $this->procedure[] = $procedure;
        return $this;
    }

    /**
     * Precedence (primary, secondary, etc.).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt
     */
    public function getPrecedence()
    {
        return $this->precedence;
    }

    /**
     * Precedence (primary, secondary, etc.).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt $precedence
     * @return $this
     */
    public function setPrecedence($precedence)
    {
        $this->precedence = $precedence;
        return $this;
    }

    /**
     * Financial instrument by which payment information for health care.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitInsurance
     */
    public function getInsurance()
    {
        return $this->insurance;
    }

    /**
     * Financial instrument by which payment information for health care.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitInsurance $insurance
     * @return $this
     */
    public function setInsurance($insurance)
    {
        $this->insurance = $insurance;
        return $this;
    }

    /**
     * An accident which resulted in the need for healthcare services.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitAccident
     */
    public function getAccident()
    {
        return $this->accident;
    }

    /**
     * An accident which resulted in the need for healthcare services.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitAccident $accident
     * @return $this
     */
    public function setAccident($accident)
    {
        $this->accident = $accident;
        return $this;
    }

    /**
     * The start and optional end dates of when the patient was precluded from working due to the treatable condition(s).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRPeriod
     */
    public function getEmploymentImpacted()
    {
        return $this->employmentImpacted;
    }

    /**
     * The start and optional end dates of when the patient was precluded from working due to the treatable condition(s).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRPeriod $employmentImpacted
     * @return $this
     */
    public function setEmploymentImpacted($employmentImpacted)
    {
        $this->employmentImpacted = $employmentImpacted;
        return $this;
    }

    /**
     * The start and optional end dates of when the patient was confined to a treatment center.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRPeriod
     */
    public function getHospitalization()
    {
        return $this->hospitalization;
    }

    /**
     * The start and optional end dates of when the patient was confined to a treatment center.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRPeriod $hospitalization
     * @return $this
     */
    public function setHospitalization($hospitalization)
    {
        $this->hospitalization = $hospitalization;
        return $this;
    }

    /**
     * First tier of goods and services.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitItem[]
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * First tier of goods and services.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitItem $item
     * @return $this
     */
    public function addItem($item)
    {
        $this->item[] = $item;
        return $this;
    }

    /**
     * The first tier service adjudications for payor added services.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitAddItem[]
     */
    public function getAddItem()
    {
        return $this->addItem;
    }

    /**
     * The first tier service adjudications for payor added services.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitAddItem $addItem
     * @return $this
     */
    public function addAddItem($addItem)
    {
        $this->addItem[] = $addItem;
        return $this;
    }

    /**
     * The total cost of the services reported.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRMoney
     */
    public function getTotalCost()
    {
        return $this->totalCost;
    }

    /**
     * The total cost of the services reported.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRMoney $totalCost
     * @return $this
     */
    public function setTotalCost($totalCost)
    {
        $this->totalCost = $totalCost;
        return $this;
    }

    /**
     * The amount of deductable applied which was not allocated to any particular service line.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRMoney
     */
    public function getUnallocDeductable()
    {
        return $this->unallocDeductable;
    }

    /**
     * The amount of deductable applied which was not allocated to any particular service line.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRMoney $unallocDeductable
     * @return $this
     */
    public function setUnallocDeductable($unallocDeductable)
    {
        $this->unallocDeductable = $unallocDeductable;
        return $this;
    }

    /**
     * Total amount of benefit payable (Equal to sum of the Benefit amounts from all detail lines and additions less the Unallocated Deductable).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRMoney
     */
    public function getTotalBenefit()
    {
        return $this->totalBenefit;
    }

    /**
     * Total amount of benefit payable (Equal to sum of the Benefit amounts from all detail lines and additions less the Unallocated Deductable).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRMoney $totalBenefit
     * @return $this
     */
    public function setTotalBenefit($totalBenefit)
    {
        $this->totalBenefit = $totalBenefit;
        return $this;
    }

    /**
     * Payment details for the claim if the claim has been paid.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitPayment
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * Payment details for the claim if the claim has been paid.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitPayment $payment
     * @return $this
     */
    public function setPayment($payment)
    {
        $this->payment = $payment;
        return $this;
    }

    /**
     * The form to be used for printing the content.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * The form to be used for printing the content.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $form
     * @return $this
     */
    public function setForm($form)
    {
        $this->form = $form;
        return $this;
    }

    /**
     * Note text.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitProcessNote[]
     */
    public function getProcessNote()
    {
        return $this->processNote;
    }

    /**
     * Note text.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitProcessNote $processNote
     * @return $this
     */
    public function addProcessNote($processNote)
    {
        $this->processNote[] = $processNote;
        return $this;
    }

    /**
     * Balance by Benefit Category.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitBenefitBalance[]
     */
    public function getBenefitBalance()
    {
        return $this->benefitBalance;
    }

    /**
     * Balance by Benefit Category.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitBenefitBalance $benefitBalance
     * @return $this
     */
    public function addBenefitBalance($benefitBalance)
    {
        $this->benefitBalance[] = $benefitBalance;
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
        if (is_array($data)) {
            if (isset($data['identifier'])) {
                if (is_array($data['identifier'])) {
                    foreach ($data['identifier'] as $d) {
                        $this->addIdentifier($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"identifier" must be array of objects or null, '.gettype($data['identifier']).' seen.');
                }
            }
            if (isset($data['status'])) {
                $this->setStatus($data['status']);
            }
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['subType'])) {
                if (is_array($data['subType'])) {
                    foreach ($data['subType'] as $d) {
                        $this->addSubType($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"subType" must be array of objects or null, '.gettype($data['subType']).' seen.');
                }
            }
            if (isset($data['patient'])) {
                $this->setPatient($data['patient']);
            }
            if (isset($data['billablePeriod'])) {
                $this->setBillablePeriod($data['billablePeriod']);
            }
            if (isset($data['created'])) {
                $this->setCreated($data['created']);
            }
            if (isset($data['enterer'])) {
                $this->setEnterer($data['enterer']);
            }
            if (isset($data['insurer'])) {
                $this->setInsurer($data['insurer']);
            }
            if (isset($data['provider'])) {
                $this->setProvider($data['provider']);
            }
            if (isset($data['organization'])) {
                $this->setOrganization($data['organization']);
            }
            if (isset($data['referral'])) {
                $this->setReferral($data['referral']);
            }
            if (isset($data['facility'])) {
                $this->setFacility($data['facility']);
            }
            if (isset($data['claim'])) {
                $this->setClaim($data['claim']);
            }
            if (isset($data['claimResponse'])) {
                $this->setClaimResponse($data['claimResponse']);
            }
            if (isset($data['outcome'])) {
                $this->setOutcome($data['outcome']);
            }
            if (isset($data['disposition'])) {
                $this->setDisposition($data['disposition']);
            }
            if (isset($data['related'])) {
                if (is_array($data['related'])) {
                    foreach ($data['related'] as $d) {
                        $this->addRelated($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"related" must be array of objects or null, '.gettype($data['related']).' seen.');
                }
            }
            if (isset($data['prescription'])) {
                $this->setPrescription($data['prescription']);
            }
            if (isset($data['originalPrescription'])) {
                $this->setOriginalPrescription($data['originalPrescription']);
            }
            if (isset($data['payee'])) {
                $this->setPayee($data['payee']);
            }
            if (isset($data['information'])) {
                if (is_array($data['information'])) {
                    foreach ($data['information'] as $d) {
                        $this->addInformation($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"information" must be array of objects or null, '.gettype($data['information']).' seen.');
                }
            }
            if (isset($data['careTeam'])) {
                if (is_array($data['careTeam'])) {
                    foreach ($data['careTeam'] as $d) {
                        $this->addCareTeam($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"careTeam" must be array of objects or null, '.gettype($data['careTeam']).' seen.');
                }
            }
            if (isset($data['diagnosis'])) {
                if (is_array($data['diagnosis'])) {
                    foreach ($data['diagnosis'] as $d) {
                        $this->addDiagnosis($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"diagnosis" must be array of objects or null, '.gettype($data['diagnosis']).' seen.');
                }
            }
            if (isset($data['procedure'])) {
                if (is_array($data['procedure'])) {
                    foreach ($data['procedure'] as $d) {
                        $this->addProcedure($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"procedure" must be array of objects or null, '.gettype($data['procedure']).' seen.');
                }
            }
            if (isset($data['precedence'])) {
                $this->setPrecedence($data['precedence']);
            }
            if (isset($data['insurance'])) {
                $this->setInsurance($data['insurance']);
            }
            if (isset($data['accident'])) {
                $this->setAccident($data['accident']);
            }
            if (isset($data['employmentImpacted'])) {
                $this->setEmploymentImpacted($data['employmentImpacted']);
            }
            if (isset($data['hospitalization'])) {
                $this->setHospitalization($data['hospitalization']);
            }
            if (isset($data['item'])) {
                if (is_array($data['item'])) {
                    foreach ($data['item'] as $d) {
                        $this->addItem($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"item" must be array of objects or null, '.gettype($data['item']).' seen.');
                }
            }
            if (isset($data['addItem'])) {
                if (is_array($data['addItem'])) {
                    foreach ($data['addItem'] as $d) {
                        $this->addAddItem($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"addItem" must be array of objects or null, '.gettype($data['addItem']).' seen.');
                }
            }
            if (isset($data['totalCost'])) {
                $this->setTotalCost($data['totalCost']);
            }
            if (isset($data['unallocDeductable'])) {
                $this->setUnallocDeductable($data['unallocDeductable']);
            }
            if (isset($data['totalBenefit'])) {
                $this->setTotalBenefit($data['totalBenefit']);
            }
            if (isset($data['payment'])) {
                $this->setPayment($data['payment']);
            }
            if (isset($data['form'])) {
                $this->setForm($data['form']);
            }
            if (isset($data['processNote'])) {
                if (is_array($data['processNote'])) {
                    foreach ($data['processNote'] as $d) {
                        $this->addProcessNote($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"processNote" must be array of objects or null, '.gettype($data['processNote']).' seen.');
                }
            }
            if (isset($data['benefitBalance'])) {
                if (is_array($data['benefitBalance'])) {
                    foreach ($data['benefitBalance'] as $d) {
                        $this->addBenefitBalance($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"benefitBalance" must be array of objects or null, '.gettype($data['benefitBalance']).' seen.');
                }
            }
        } else if (null !== $data) {
            throw new \InvalidArgumentException('$data expected to be array of values, saw "'.gettype($data).'"');
        }
        parent::__construct($data);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->get_fhirElementName();
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $json = parent::jsonSerialize();
        $json['resourceType'] = $this->_fhirElementName;
        if (0 < count($this->identifier)) {
            $json['identifier'] = [];
            foreach ($this->identifier as $identifier) {
                $json['identifier'][] = $identifier;
            }
        }
        if (isset($this->status)) {
            $json['status'] = $this->status;
        }
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (0 < count($this->subType)) {
            $json['subType'] = [];
            foreach ($this->subType as $subType) {
                $json['subType'][] = $subType;
            }
        }
        if (isset($this->patient)) {
            $json['patient'] = $this->patient;
        }
        if (isset($this->billablePeriod)) {
            $json['billablePeriod'] = $this->billablePeriod;
        }
        if (isset($this->created)) {
            $json['created'] = $this->created;
        }
        if (isset($this->enterer)) {
            $json['enterer'] = $this->enterer;
        }
        if (isset($this->insurer)) {
            $json['insurer'] = $this->insurer;
        }
        if (isset($this->provider)) {
            $json['provider'] = $this->provider;
        }
        if (isset($this->organization)) {
            $json['organization'] = $this->organization;
        }
        if (isset($this->referral)) {
            $json['referral'] = $this->referral;
        }
        if (isset($this->facility)) {
            $json['facility'] = $this->facility;
        }
        if (isset($this->claim)) {
            $json['claim'] = $this->claim;
        }
        if (isset($this->claimResponse)) {
            $json['claimResponse'] = $this->claimResponse;
        }
        if (isset($this->outcome)) {
            $json['outcome'] = $this->outcome;
        }
        if (isset($this->disposition)) {
            $json['disposition'] = $this->disposition;
        }
        if (0 < count($this->related)) {
            $json['related'] = [];
            foreach ($this->related as $related) {
                $json['related'][] = $related;
            }
        }
        if (isset($this->prescription)) {
            $json['prescription'] = $this->prescription;
        }
        if (isset($this->originalPrescription)) {
            $json['originalPrescription'] = $this->originalPrescription;
        }
        if (isset($this->payee)) {
            $json['payee'] = $this->payee;
        }
        if (0 < count($this->information)) {
            $json['information'] = [];
            foreach ($this->information as $information) {
                $json['information'][] = $information;
            }
        }
        if (0 < count($this->careTeam)) {
            $json['careTeam'] = [];
            foreach ($this->careTeam as $careTeam) {
                $json['careTeam'][] = $careTeam;
            }
        }
        if (0 < count($this->diagnosis)) {
            $json['diagnosis'] = [];
            foreach ($this->diagnosis as $diagnosis) {
                $json['diagnosis'][] = $diagnosis;
            }
        }
        if (0 < count($this->procedure)) {
            $json['procedure'] = [];
            foreach ($this->procedure as $procedure) {
                $json['procedure'][] = $procedure;
            }
        }
        if (isset($this->precedence)) {
            $json['precedence'] = $this->precedence;
        }
        if (isset($this->insurance)) {
            $json['insurance'] = $this->insurance;
        }
        if (isset($this->accident)) {
            $json['accident'] = $this->accident;
        }
        if (isset($this->employmentImpacted)) {
            $json['employmentImpacted'] = $this->employmentImpacted;
        }
        if (isset($this->hospitalization)) {
            $json['hospitalization'] = $this->hospitalization;
        }
        if (0 < count($this->item)) {
            $json['item'] = [];
            foreach ($this->item as $item) {
                $json['item'][] = $item;
            }
        }
        if (0 < count($this->addItem)) {
            $json['addItem'] = [];
            foreach ($this->addItem as $addItem) {
                $json['addItem'][] = $addItem;
            }
        }
        if (isset($this->totalCost)) {
            $json['totalCost'] = $this->totalCost;
        }
        if (isset($this->unallocDeductable)) {
            $json['unallocDeductable'] = $this->unallocDeductable;
        }
        if (isset($this->totalBenefit)) {
            $json['totalBenefit'] = $this->totalBenefit;
        }
        if (isset($this->payment)) {
            $json['payment'] = $this->payment;
        }
        if (isset($this->form)) {
            $json['form'] = $this->form;
        }
        if (0 < count($this->processNote)) {
            $json['processNote'] = [];
            foreach ($this->processNote as $processNote) {
                $json['processNote'][] = $processNote;
            }
        }
        if (0 < count($this->benefitBalance)) {
            $json['benefitBalance'] = [];
            foreach ($this->benefitBalance as $benefitBalance) {
                $json['benefitBalance'][] = $benefitBalance;
            }
        }
        return $json;
    }

    /**
     * @param boolean $returnSXE
     * @param \SimpleXMLElement $sxe
     * @return string|\SimpleXMLElement
     */
    public function xmlSerialize($returnSXE = false, $sxe = null)
    {
        if (null === $sxe) {
            $sxe = new \SimpleXMLElement('<ExplanationOfBenefit xmlns="http://hl7.org/fhir"></ExplanationOfBenefit>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->identifier)) {
            foreach ($this->identifier as $identifier) {
                $identifier->xmlSerialize(true, $sxe->addChild('identifier'));
            }
        }
        if (isset($this->status)) {
            $this->status->xmlSerialize(true, $sxe->addChild('status'));
        }
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (0 < count($this->subType)) {
            foreach ($this->subType as $subType) {
                $subType->xmlSerialize(true, $sxe->addChild('subType'));
            }
        }
        if (isset($this->patient)) {
            $this->patient->xmlSerialize(true, $sxe->addChild('patient'));
        }
        if (isset($this->billablePeriod)) {
            $this->billablePeriod->xmlSerialize(true, $sxe->addChild('billablePeriod'));
        }
        if (isset($this->created)) {
            $this->created->xmlSerialize(true, $sxe->addChild('created'));
        }
        if (isset($this->enterer)) {
            $this->enterer->xmlSerialize(true, $sxe->addChild('enterer'));
        }
        if (isset($this->insurer)) {
            $this->insurer->xmlSerialize(true, $sxe->addChild('insurer'));
        }
        if (isset($this->provider)) {
            $this->provider->xmlSerialize(true, $sxe->addChild('provider'));
        }
        if (isset($this->organization)) {
            $this->organization->xmlSerialize(true, $sxe->addChild('organization'));
        }
        if (isset($this->referral)) {
            $this->referral->xmlSerialize(true, $sxe->addChild('referral'));
        }
        if (isset($this->facility)) {
            $this->facility->xmlSerialize(true, $sxe->addChild('facility'));
        }
        if (isset($this->claim)) {
            $this->claim->xmlSerialize(true, $sxe->addChild('claim'));
        }
        if (isset($this->claimResponse)) {
            $this->claimResponse->xmlSerialize(true, $sxe->addChild('claimResponse'));
        }
        if (isset($this->outcome)) {
            $this->outcome->xmlSerialize(true, $sxe->addChild('outcome'));
        }
        if (isset($this->disposition)) {
            $this->disposition->xmlSerialize(true, $sxe->addChild('disposition'));
        }
        if (0 < count($this->related)) {
            foreach ($this->related as $related) {
                $related->xmlSerialize(true, $sxe->addChild('related'));
            }
        }
        if (isset($this->prescription)) {
            $this->prescription->xmlSerialize(true, $sxe->addChild('prescription'));
        }
        if (isset($this->originalPrescription)) {
            $this->originalPrescription->xmlSerialize(true, $sxe->addChild('originalPrescription'));
        }
        if (isset($this->payee)) {
            $this->payee->xmlSerialize(true, $sxe->addChild('payee'));
        }
        if (0 < count($this->information)) {
            foreach ($this->information as $information) {
                $information->xmlSerialize(true, $sxe->addChild('information'));
            }
        }
        if (0 < count($this->careTeam)) {
            foreach ($this->careTeam as $careTeam) {
                $careTeam->xmlSerialize(true, $sxe->addChild('careTeam'));
            }
        }
        if (0 < count($this->diagnosis)) {
            foreach ($this->diagnosis as $diagnosis) {
                $diagnosis->xmlSerialize(true, $sxe->addChild('diagnosis'));
            }
        }
        if (0 < count($this->procedure)) {
            foreach ($this->procedure as $procedure) {
                $procedure->xmlSerialize(true, $sxe->addChild('procedure'));
            }
        }
        if (isset($this->precedence)) {
            $this->precedence->xmlSerialize(true, $sxe->addChild('precedence'));
        }
        if (isset($this->insurance)) {
            $this->insurance->xmlSerialize(true, $sxe->addChild('insurance'));
        }
        if (isset($this->accident)) {
            $this->accident->xmlSerialize(true, $sxe->addChild('accident'));
        }
        if (isset($this->employmentImpacted)) {
            $this->employmentImpacted->xmlSerialize(true, $sxe->addChild('employmentImpacted'));
        }
        if (isset($this->hospitalization)) {
            $this->hospitalization->xmlSerialize(true, $sxe->addChild('hospitalization'));
        }
        if (0 < count($this->item)) {
            foreach ($this->item as $item) {
                $item->xmlSerialize(true, $sxe->addChild('item'));
            }
        }
        if (0 < count($this->addItem)) {
            foreach ($this->addItem as $addItem) {
                $addItem->xmlSerialize(true, $sxe->addChild('addItem'));
            }
        }
        if (isset($this->totalCost)) {
            $this->totalCost->xmlSerialize(true, $sxe->addChild('totalCost'));
        }
        if (isset($this->unallocDeductable)) {
            $this->unallocDeductable->xmlSerialize(true, $sxe->addChild('unallocDeductable'));
        }
        if (isset($this->totalBenefit)) {
            $this->totalBenefit->xmlSerialize(true, $sxe->addChild('totalBenefit'));
        }
        if (isset($this->payment)) {
            $this->payment->xmlSerialize(true, $sxe->addChild('payment'));
        }
        if (isset($this->form)) {
            $this->form->xmlSerialize(true, $sxe->addChild('form'));
        }
        if (0 < count($this->processNote)) {
            foreach ($this->processNote as $processNote) {
                $processNote->xmlSerialize(true, $sxe->addChild('processNote'));
            }
        }
        if (0 < count($this->benefitBalance)) {
            foreach ($this->benefitBalance as $benefitBalance) {
                $benefitBalance->xmlSerialize(true, $sxe->addChild('benefitBalance'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
