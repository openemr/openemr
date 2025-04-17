<?php

namespace OpenEMR\FHIR\R4;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 *
 * Class creation date: June 14th, 2019
 *
 * PHPFHIR Copyright:
 *
 * Copyright 2016-2017 Daniel Carbone (daniel.p.carbone@gmail.com)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *        http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 *
 * FHIR Copyright Notice:
 *
 *   Copyright (c) 2011+, HL7, Inc.
 *   All rights reserved.
 *
 *   Redistribution and use in source and binary forms, with or without modification,
 *   are permitted provided that the following conditions are met:
 *
 *    * Redistributions of source code must retain the above copyright notice, this
 *      list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above copyright notice,
 *      this list of conditions and the following disclaimer in the documentation
 *      and/or other materials provided with the distribution.
 *    * Neither the name of HL7 nor the names of its contributors may be used to
 *      endorse or promote products derived from this software without specific
 *      prior written permission.
 *
 *   THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 *   ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 *   WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 *   IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 *   INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 *   NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 *   PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 *   WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 *   ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 *   POSSIBILITY OF SUCH DAMAGE.
 *
 *
 *   Generated on Thu, Dec 27, 2018 22:37+1100 for FHIR v4.0.0
 *
 *   Note: the schemas & schematrons do not contain all of the rules about what makes resources
 *   valid. Implementers will still need to be familiar with the content of the specification and with
 *   any profiles that apply to the resources in order to make a conformant implementation.
 *
 */

class FHIRResourceContainer implements \JsonSerializable
{
    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRAccount
     */
    public $Account = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRActivityDefinition
     */
    public $ActivityDefinition = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRAdverseEvent
     */
    public $AdverseEvent = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRAllergyIntolerance
     */
    public $AllergyIntolerance = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRAppointment
     */
    public $Appointment = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRAppointmentResponse
     */
    public $AppointmentResponse = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRAuditEvent
     */
    public $AuditEvent = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRBasic
     */
    public $Basic = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRBinary
     */
    public $Binary = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRBiologicallyDerivedProduct
     */
    public $BiologicallyDerivedProduct = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRBodyStructure
     */
    public $BodyStructure = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRBundle
     */
    public $Bundle = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCapabilityStatement
     */
    public $CapabilityStatement = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCarePlan
     */
    public $CarePlan = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCareTeam
     */
    public $CareTeam = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCatalogEntry
     */
    public $CatalogEntry = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRChargeItem
     */
    public $ChargeItem = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRChargeItemDefinition
     */
    public $ChargeItemDefinition = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRClaim
     */
    public $Claim = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRClaimResponse
     */
    public $ClaimResponse = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRClinicalImpression
     */
    public $ClinicalImpression = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCodeSystem
     */
    public $CodeSystem = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCommunication
     */
    public $Communication = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCommunicationRequest
     */
    public $CommunicationRequest = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCompartmentDefinition
     */
    public $CompartmentDefinition = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRComposition
     */
    public $Composition = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRConceptMap
     */
    public $ConceptMap = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCondition
     */
    public $Condition = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRConsent
     */
    public $Consent = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRContract
     */
    public $Contract = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCoverage
     */
    public $Coverage = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCoverageEligibilityRequest
     */
    public $CoverageEligibilityRequest = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCoverageEligibilityResponse
     */
    public $CoverageEligibilityResponse = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRDetectedIssue
     */
    public $DetectedIssue = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRDevice
     */
    public $Device = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRDeviceDefinition
     */
    public $DeviceDefinition = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRDeviceMetric
     */
    public $DeviceMetric = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRDeviceRequest
     */
    public $DeviceRequest = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRDeviceUseStatement
     */
    public $DeviceUseStatement = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRDiagnosticReport
     */
    public $DiagnosticReport = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRDocumentManifest
     */
    public $DocumentManifest = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRDocumentReference
     */
    public $DocumentReference = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIREffectEvidenceSynthesis
     */
    public $EffectEvidenceSynthesis = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIREncounter
     */
    public $Encounter = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIREndpoint
     */
    public $Endpoint = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIREnrollmentRequest
     */
    public $EnrollmentRequest = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIREnrollmentResponse
     */
    public $EnrollmentResponse = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIREpisodeOfCare
     */
    public $EpisodeOfCare = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIREventDefinition
     */
    public $EventDefinition = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIREvidence
     */
    public $Evidence = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIREvidenceVariable
     */
    public $EvidenceVariable = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRExampleScenario
     */
    public $ExampleScenario = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRExplanationOfBenefit
     */
    public $ExplanationOfBenefit = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRFamilyMemberHistory
     */
    public $FamilyMemberHistory = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRFlag
     */
    public $Flag = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRGoal
     */
    public $Goal = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRGraphDefinition
     */
    public $GraphDefinition = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRGroup
     */
    public $Group = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRGuidanceResponse
     */
    public $GuidanceResponse = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRHealthcareService
     */
    public $HealthcareService = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRImagingStudy
     */
    public $ImagingStudy = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRImmunization
     */
    public $Immunization = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRImmunizationEvaluation
     */
    public $ImmunizationEvaluation = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRImmunizationRecommendation
     */
    public $ImmunizationRecommendation = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRImplementationGuide
     */
    public $ImplementationGuide = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRInsurancePlan
     */
    public $InsurancePlan = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRInvoice
     */
    public $Invoice = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRLibrary
     */
    public $Library = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRLinkage
     */
    public $Linkage = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRList
     */
    public $List = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRLocation
     */
    public $Location = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMeasure
     */
    public $Measure = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMeasureReport
     */
    public $MeasureReport = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedia
     */
    public $Media = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedication
     */
    public $Medication = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicationAdministration
     */
    public $MedicationAdministration = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicationDispense
     */
    public $MedicationDispense = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicationKnowledge
     */
    public $MedicationKnowledge = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicationRequest
     */
    public $MedicationRequest = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicationStatement
     */
    public $MedicationStatement = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicinalProduct
     */
    public $MedicinalProduct = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicinalProductAuthorization
     */
    public $MedicinalProductAuthorization = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicinalProductContraindication
     */
    public $MedicinalProductContraindication = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicinalProductIndication
     */
    public $MedicinalProductIndication = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicinalProductIngredient
     */
    public $MedicinalProductIngredient = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicinalProductInteraction
     */
    public $MedicinalProductInteraction = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicinalProductManufactured
     */
    public $MedicinalProductManufactured = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicinalProductPackaged
     */
    public $MedicinalProductPackaged = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicinalProductPharmaceutical
     */
    public $MedicinalProductPharmaceutical = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicinalProductUndesirableEffect
     */
    public $MedicinalProductUndesirableEffect = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMessageDefinition
     */
    public $MessageDefinition = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMessageHeader
     */
    public $MessageHeader = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMolecularSequence
     */
    public $MolecularSequence = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRNamingSystem
     */
    public $NamingSystem = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRNutritionOrder
     */
    public $NutritionOrder = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRObservation
     */
    public $Observation = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRObservationDefinition
     */
    public $ObservationDefinition = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIROperationDefinition
     */
    public $OperationDefinition = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIROperationOutcome
     */
    public $OperationOutcome = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIROrganization
     */
    public $Organization = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIROrganizationAffiliation
     */
    public $OrganizationAffiliation = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPatient
     */
    public $Patient = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPaymentNotice
     */
    public $PaymentNotice = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPaymentReconciliation
     */
    public $PaymentReconciliation = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPerson
     */
    public $Person = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPlanDefinition
     */
    public $PlanDefinition = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPractitioner
     */
    public $Practitioner = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPractitionerRole
     */
    public $PractitionerRole = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRProcedure
     */
    public $Procedure = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRProvenance
     */
    public $Provenance = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRQuestionnaire
     */
    public $Questionnaire = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRQuestionnaireResponse
     */
    public $QuestionnaireResponse = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRRelatedPerson
     */
    public $RelatedPerson = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRRequestGroup
     */
    public $RequestGroup = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRResearchDefinition
     */
    public $ResearchDefinition = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRResearchElementDefinition
     */
    public $ResearchElementDefinition = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRResearchStudy
     */
    public $ResearchStudy = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRResearchSubject
     */
    public $ResearchSubject = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRRiskAssessment
     */
    public $RiskAssessment = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRRiskEvidenceSynthesis
     */
    public $RiskEvidenceSynthesis = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSchedule
     */
    public $Schedule = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSearchParameter
     */
    public $SearchParameter = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRServiceRequest
     */
    public $ServiceRequest = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSlot
     */
    public $Slot = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSpecimen
     */
    public $Specimen = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSpecimenDefinition
     */
    public $SpecimenDefinition = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRStructureDefinition
     */
    public $StructureDefinition = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRStructureMap
     */
    public $StructureMap = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSubscription
     */
    public $Subscription = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSubstance
     */
    public $Substance = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSubstanceNucleicAcid
     */
    public $SubstanceNucleicAcid = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSubstancePolymer
     */
    public $SubstancePolymer = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSubstanceProtein
     */
    public $SubstanceProtein = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSubstanceReferenceInformation
     */
    public $SubstanceReferenceInformation = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSubstanceSourceMaterial
     */
    public $SubstanceSourceMaterial = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSubstanceSpecification
     */
    public $SubstanceSpecification = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSupplyDelivery
     */
    public $SupplyDelivery = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSupplyRequest
     */
    public $SupplyRequest = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRTask
     */
    public $Task = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRTerminologyCapabilities
     */
    public $TerminologyCapabilities = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRTestReport
     */
    public $TestReport = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRTestScript
     */
    public $TestScript = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRValueSet
     */
    public $ValueSet = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRVerificationResult
     */
    public $VerificationResult = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRVisionPrescription
     */
    public $VisionPrescription = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRParameters
     */
    public $Parameters = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'ResourceContainer';

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRAccount
     */
    public function getAccount()
    {
        return $this->Account;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRAccount $Account
     * @return $this
     */
    public function setAccount($Account)
    {
        $this->Account = $Account;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRActivityDefinition
     */
    public function getActivityDefinition()
    {
        return $this->ActivityDefinition;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRActivityDefinition $ActivityDefinition
     * @return $this
     */
    public function setActivityDefinition($ActivityDefinition)
    {
        $this->ActivityDefinition = $ActivityDefinition;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRAdverseEvent
     */
    public function getAdverseEvent()
    {
        return $this->AdverseEvent;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRAdverseEvent $AdverseEvent
     * @return $this
     */
    public function setAdverseEvent($AdverseEvent)
    {
        $this->AdverseEvent = $AdverseEvent;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRAllergyIntolerance
     */
    public function getAllergyIntolerance()
    {
        return $this->AllergyIntolerance;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRAllergyIntolerance $AllergyIntolerance
     * @return $this
     */
    public function setAllergyIntolerance($AllergyIntolerance)
    {
        $this->AllergyIntolerance = $AllergyIntolerance;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRAppointment
     */
    public function getAppointment()
    {
        return $this->Appointment;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRAppointment $Appointment
     * @return $this
     */
    public function setAppointment($Appointment)
    {
        $this->Appointment = $Appointment;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRAppointmentResponse
     */
    public function getAppointmentResponse()
    {
        return $this->AppointmentResponse;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRAppointmentResponse $AppointmentResponse
     * @return $this
     */
    public function setAppointmentResponse($AppointmentResponse)
    {
        $this->AppointmentResponse = $AppointmentResponse;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRAuditEvent
     */
    public function getAuditEvent()
    {
        return $this->AuditEvent;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRAuditEvent $AuditEvent
     * @return $this
     */
    public function setAuditEvent($AuditEvent)
    {
        $this->AuditEvent = $AuditEvent;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRBasic
     */
    public function getBasic()
    {
        return $this->Basic;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRBasic $Basic
     * @return $this
     */
    public function setBasic($Basic)
    {
        $this->Basic = $Basic;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRBinary
     */
    public function getBinary()
    {
        return $this->Binary;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRBinary $Binary
     * @return $this
     */
    public function setBinary($Binary)
    {
        $this->Binary = $Binary;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRBiologicallyDerivedProduct
     */
    public function getBiologicallyDerivedProduct()
    {
        return $this->BiologicallyDerivedProduct;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRBiologicallyDerivedProduct $BiologicallyDerivedProduct
     * @return $this
     */
    public function setBiologicallyDerivedProduct($BiologicallyDerivedProduct)
    {
        $this->BiologicallyDerivedProduct = $BiologicallyDerivedProduct;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRBodyStructure
     */
    public function getBodyStructure()
    {
        return $this->BodyStructure;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRBodyStructure $BodyStructure
     * @return $this
     */
    public function setBodyStructure($BodyStructure)
    {
        $this->BodyStructure = $BodyStructure;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRBundle
     */
    public function getBundle()
    {
        return $this->Bundle;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRBundle $Bundle
     * @return $this
     */
    public function setBundle($Bundle)
    {
        $this->Bundle = $Bundle;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCapabilityStatement
     */
    public function getCapabilityStatement()
    {
        return $this->CapabilityStatement;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCapabilityStatement $CapabilityStatement
     * @return $this
     */
    public function setCapabilityStatement($CapabilityStatement)
    {
        $this->CapabilityStatement = $CapabilityStatement;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCarePlan
     */
    public function getCarePlan()
    {
        return $this->CarePlan;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCarePlan $CarePlan
     * @return $this
     */
    public function setCarePlan($CarePlan)
    {
        $this->CarePlan = $CarePlan;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCareTeam
     */
    public function getCareTeam()
    {
        return $this->CareTeam;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCareTeam $CareTeam
     * @return $this
     */
    public function setCareTeam($CareTeam)
    {
        $this->CareTeam = $CareTeam;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCatalogEntry
     */
    public function getCatalogEntry()
    {
        return $this->CatalogEntry;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCatalogEntry $CatalogEntry
     * @return $this
     */
    public function setCatalogEntry($CatalogEntry)
    {
        $this->CatalogEntry = $CatalogEntry;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRChargeItem
     */
    public function getChargeItem()
    {
        return $this->ChargeItem;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRChargeItem $ChargeItem
     * @return $this
     */
    public function setChargeItem($ChargeItem)
    {
        $this->ChargeItem = $ChargeItem;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRChargeItemDefinition
     */
    public function getChargeItemDefinition()
    {
        return $this->ChargeItemDefinition;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRChargeItemDefinition $ChargeItemDefinition
     * @return $this
     */
    public function setChargeItemDefinition($ChargeItemDefinition)
    {
        $this->ChargeItemDefinition = $ChargeItemDefinition;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRClaim
     */
    public function getClaim()
    {
        return $this->Claim;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRClaim $Claim
     * @return $this
     */
    public function setClaim($Claim)
    {
        $this->Claim = $Claim;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRClaimResponse
     */
    public function getClaimResponse()
    {
        return $this->ClaimResponse;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRClaimResponse $ClaimResponse
     * @return $this
     */
    public function setClaimResponse($ClaimResponse)
    {
        $this->ClaimResponse = $ClaimResponse;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRClinicalImpression
     */
    public function getClinicalImpression()
    {
        return $this->ClinicalImpression;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRClinicalImpression $ClinicalImpression
     * @return $this
     */
    public function setClinicalImpression($ClinicalImpression)
    {
        $this->ClinicalImpression = $ClinicalImpression;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCodeSystem
     */
    public function getCodeSystem()
    {
        return $this->CodeSystem;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCodeSystem $CodeSystem
     * @return $this
     */
    public function setCodeSystem($CodeSystem)
    {
        $this->CodeSystem = $CodeSystem;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCommunication
     */
    public function getCommunication()
    {
        return $this->Communication;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCommunication $Communication
     * @return $this
     */
    public function setCommunication($Communication)
    {
        $this->Communication = $Communication;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCommunicationRequest
     */
    public function getCommunicationRequest()
    {
        return $this->CommunicationRequest;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCommunicationRequest $CommunicationRequest
     * @return $this
     */
    public function setCommunicationRequest($CommunicationRequest)
    {
        $this->CommunicationRequest = $CommunicationRequest;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCompartmentDefinition
     */
    public function getCompartmentDefinition()
    {
        return $this->CompartmentDefinition;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCompartmentDefinition $CompartmentDefinition
     * @return $this
     */
    public function setCompartmentDefinition($CompartmentDefinition)
    {
        $this->CompartmentDefinition = $CompartmentDefinition;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRComposition
     */
    public function getComposition()
    {
        return $this->Composition;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRComposition $Composition
     * @return $this
     */
    public function setComposition($Composition)
    {
        $this->Composition = $Composition;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRConceptMap
     */
    public function getConceptMap()
    {
        return $this->ConceptMap;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRConceptMap $ConceptMap
     * @return $this
     */
    public function setConceptMap($ConceptMap)
    {
        $this->ConceptMap = $ConceptMap;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCondition
     */
    public function getCondition()
    {
        return $this->Condition;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCondition $Condition
     * @return $this
     */
    public function setCondition($Condition)
    {
        $this->Condition = $Condition;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRConsent
     */
    public function getConsent()
    {
        return $this->Consent;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRConsent $Consent
     * @return $this
     */
    public function setConsent($Consent)
    {
        $this->Consent = $Consent;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRContract
     */
    public function getContract()
    {
        return $this->Contract;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRContract $Contract
     * @return $this
     */
    public function setContract($Contract)
    {
        $this->Contract = $Contract;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCoverage
     */
    public function getCoverage()
    {
        return $this->Coverage;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCoverage $Coverage
     * @return $this
     */
    public function setCoverage($Coverage)
    {
        $this->Coverage = $Coverage;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCoverageEligibilityRequest
     */
    public function getCoverageEligibilityRequest()
    {
        return $this->CoverageEligibilityRequest;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCoverageEligibilityRequest $CoverageEligibilityRequest
     * @return $this
     */
    public function setCoverageEligibilityRequest($CoverageEligibilityRequest)
    {
        $this->CoverageEligibilityRequest = $CoverageEligibilityRequest;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCoverageEligibilityResponse
     */
    public function getCoverageEligibilityResponse()
    {
        return $this->CoverageEligibilityResponse;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCoverageEligibilityResponse $CoverageEligibilityResponse
     * @return $this
     */
    public function setCoverageEligibilityResponse($CoverageEligibilityResponse)
    {
        $this->CoverageEligibilityResponse = $CoverageEligibilityResponse;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRDetectedIssue
     */
    public function getDetectedIssue()
    {
        return $this->DetectedIssue;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRDetectedIssue $DetectedIssue
     * @return $this
     */
    public function setDetectedIssue($DetectedIssue)
    {
        $this->DetectedIssue = $DetectedIssue;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRDevice
     */
    public function getDevice()
    {
        return $this->Device;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRDevice $Device
     * @return $this
     */
    public function setDevice($Device)
    {
        $this->Device = $Device;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRDeviceDefinition
     */
    public function getDeviceDefinition()
    {
        return $this->DeviceDefinition;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRDeviceDefinition $DeviceDefinition
     * @return $this
     */
    public function setDeviceDefinition($DeviceDefinition)
    {
        $this->DeviceDefinition = $DeviceDefinition;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRDeviceMetric
     */
    public function getDeviceMetric()
    {
        return $this->DeviceMetric;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRDeviceMetric $DeviceMetric
     * @return $this
     */
    public function setDeviceMetric($DeviceMetric)
    {
        $this->DeviceMetric = $DeviceMetric;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRDeviceRequest
     */
    public function getDeviceRequest()
    {
        return $this->DeviceRequest;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRDeviceRequest $DeviceRequest
     * @return $this
     */
    public function setDeviceRequest($DeviceRequest)
    {
        $this->DeviceRequest = $DeviceRequest;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRDeviceUseStatement
     */
    public function getDeviceUseStatement()
    {
        return $this->DeviceUseStatement;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRDeviceUseStatement $DeviceUseStatement
     * @return $this
     */
    public function setDeviceUseStatement($DeviceUseStatement)
    {
        $this->DeviceUseStatement = $DeviceUseStatement;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRDiagnosticReport
     */
    public function getDiagnosticReport()
    {
        return $this->DiagnosticReport;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRDiagnosticReport $DiagnosticReport
     * @return $this
     */
    public function setDiagnosticReport($DiagnosticReport)
    {
        $this->DiagnosticReport = $DiagnosticReport;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRDocumentManifest
     */
    public function getDocumentManifest()
    {
        return $this->DocumentManifest;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRDocumentManifest $DocumentManifest
     * @return $this
     */
    public function setDocumentManifest($DocumentManifest)
    {
        $this->DocumentManifest = $DocumentManifest;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRDocumentReference
     */
    public function getDocumentReference()
    {
        return $this->DocumentReference;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRDocumentReference $DocumentReference
     * @return $this
     */
    public function setDocumentReference($DocumentReference)
    {
        $this->DocumentReference = $DocumentReference;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIREffectEvidenceSynthesis
     */
    public function getEffectEvidenceSynthesis()
    {
        return $this->EffectEvidenceSynthesis;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIREffectEvidenceSynthesis $EffectEvidenceSynthesis
     * @return $this
     */
    public function setEffectEvidenceSynthesis($EffectEvidenceSynthesis)
    {
        $this->EffectEvidenceSynthesis = $EffectEvidenceSynthesis;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIREncounter
     */
    public function getEncounter()
    {
        return $this->Encounter;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIREncounter $Encounter
     * @return $this
     */
    public function setEncounter($Encounter)
    {
        $this->Encounter = $Encounter;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIREndpoint
     */
    public function getEndpoint()
    {
        return $this->Endpoint;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIREndpoint $Endpoint
     * @return $this
     */
    public function setEndpoint($Endpoint)
    {
        $this->Endpoint = $Endpoint;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIREnrollmentRequest
     */
    public function getEnrollmentRequest()
    {
        return $this->EnrollmentRequest;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIREnrollmentRequest $EnrollmentRequest
     * @return $this
     */
    public function setEnrollmentRequest($EnrollmentRequest)
    {
        $this->EnrollmentRequest = $EnrollmentRequest;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIREnrollmentResponse
     */
    public function getEnrollmentResponse()
    {
        return $this->EnrollmentResponse;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIREnrollmentResponse $EnrollmentResponse
     * @return $this
     */
    public function setEnrollmentResponse($EnrollmentResponse)
    {
        $this->EnrollmentResponse = $EnrollmentResponse;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIREpisodeOfCare
     */
    public function getEpisodeOfCare()
    {
        return $this->EpisodeOfCare;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIREpisodeOfCare $EpisodeOfCare
     * @return $this
     */
    public function setEpisodeOfCare($EpisodeOfCare)
    {
        $this->EpisodeOfCare = $EpisodeOfCare;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIREventDefinition
     */
    public function getEventDefinition()
    {
        return $this->EventDefinition;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIREventDefinition $EventDefinition
     * @return $this
     */
    public function setEventDefinition($EventDefinition)
    {
        $this->EventDefinition = $EventDefinition;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIREvidence
     */
    public function getEvidence()
    {
        return $this->Evidence;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIREvidence $Evidence
     * @return $this
     */
    public function setEvidence($Evidence)
    {
        $this->Evidence = $Evidence;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIREvidenceVariable
     */
    public function getEvidenceVariable()
    {
        return $this->EvidenceVariable;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIREvidenceVariable $EvidenceVariable
     * @return $this
     */
    public function setEvidenceVariable($EvidenceVariable)
    {
        $this->EvidenceVariable = $EvidenceVariable;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRExampleScenario
     */
    public function getExampleScenario()
    {
        return $this->ExampleScenario;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRExampleScenario $ExampleScenario
     * @return $this
     */
    public function setExampleScenario($ExampleScenario)
    {
        $this->ExampleScenario = $ExampleScenario;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRExplanationOfBenefit
     */
    public function getExplanationOfBenefit()
    {
        return $this->ExplanationOfBenefit;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRExplanationOfBenefit $ExplanationOfBenefit
     * @return $this
     */
    public function setExplanationOfBenefit($ExplanationOfBenefit)
    {
        $this->ExplanationOfBenefit = $ExplanationOfBenefit;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRFamilyMemberHistory
     */
    public function getFamilyMemberHistory()
    {
        return $this->FamilyMemberHistory;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRFamilyMemberHistory $FamilyMemberHistory
     * @return $this
     */
    public function setFamilyMemberHistory($FamilyMemberHistory)
    {
        $this->FamilyMemberHistory = $FamilyMemberHistory;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRFlag
     */
    public function getFlag()
    {
        return $this->Flag;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRFlag $Flag
     * @return $this
     */
    public function setFlag($Flag)
    {
        $this->Flag = $Flag;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRGoal
     */
    public function getGoal()
    {
        return $this->Goal;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRGoal $Goal
     * @return $this
     */
    public function setGoal($Goal)
    {
        $this->Goal = $Goal;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRGraphDefinition
     */
    public function getGraphDefinition()
    {
        return $this->GraphDefinition;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRGraphDefinition $GraphDefinition
     * @return $this
     */
    public function setGraphDefinition($GraphDefinition)
    {
        $this->GraphDefinition = $GraphDefinition;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRGroup
     */
    public function getGroup()
    {
        return $this->Group;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRGroup $Group
     * @return $this
     */
    public function setGroup($Group)
    {
        $this->Group = $Group;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRGuidanceResponse
     */
    public function getGuidanceResponse()
    {
        return $this->GuidanceResponse;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRGuidanceResponse $GuidanceResponse
     * @return $this
     */
    public function setGuidanceResponse($GuidanceResponse)
    {
        $this->GuidanceResponse = $GuidanceResponse;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRHealthcareService
     */
    public function getHealthcareService()
    {
        return $this->HealthcareService;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRHealthcareService $HealthcareService
     * @return $this
     */
    public function setHealthcareService($HealthcareService)
    {
        $this->HealthcareService = $HealthcareService;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRImagingStudy
     */
    public function getImagingStudy()
    {
        return $this->ImagingStudy;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRImagingStudy $ImagingStudy
     * @return $this
     */
    public function setImagingStudy($ImagingStudy)
    {
        $this->ImagingStudy = $ImagingStudy;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRImmunization
     */
    public function getImmunization()
    {
        return $this->Immunization;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRImmunization $Immunization
     * @return $this
     */
    public function setImmunization($Immunization)
    {
        $this->Immunization = $Immunization;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRImmunizationEvaluation
     */
    public function getImmunizationEvaluation()
    {
        return $this->ImmunizationEvaluation;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRImmunizationEvaluation $ImmunizationEvaluation
     * @return $this
     */
    public function setImmunizationEvaluation($ImmunizationEvaluation)
    {
        $this->ImmunizationEvaluation = $ImmunizationEvaluation;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRImmunizationRecommendation
     */
    public function getImmunizationRecommendation()
    {
        return $this->ImmunizationRecommendation;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRImmunizationRecommendation $ImmunizationRecommendation
     * @return $this
     */
    public function setImmunizationRecommendation($ImmunizationRecommendation)
    {
        $this->ImmunizationRecommendation = $ImmunizationRecommendation;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRImplementationGuide
     */
    public function getImplementationGuide()
    {
        return $this->ImplementationGuide;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRImplementationGuide $ImplementationGuide
     * @return $this
     */
    public function setImplementationGuide($ImplementationGuide)
    {
        $this->ImplementationGuide = $ImplementationGuide;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRInsurancePlan
     */
    public function getInsurancePlan()
    {
        return $this->InsurancePlan;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRInsurancePlan $InsurancePlan
     * @return $this
     */
    public function setInsurancePlan($InsurancePlan)
    {
        $this->InsurancePlan = $InsurancePlan;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRInvoice
     */
    public function getInvoice()
    {
        return $this->Invoice;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRInvoice $Invoice
     * @return $this
     */
    public function setInvoice($Invoice)
    {
        $this->Invoice = $Invoice;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRLibrary
     */
    public function getLibrary()
    {
        return $this->Library;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRLibrary $Library
     * @return $this
     */
    public function setLibrary($Library)
    {
        $this->Library = $Library;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRLinkage
     */
    public function getLinkage()
    {
        return $this->Linkage;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRLinkage $Linkage
     * @return $this
     */
    public function setLinkage($Linkage)
    {
        $this->Linkage = $Linkage;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRList
     */
    public function getList()
    {
        return $this->List;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRList $List
     * @return $this
     */
    public function setList($List)
    {
        $this->List = $List;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRLocation
     */
    public function getLocation()
    {
        return $this->Location;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRLocation $Location
     * @return $this
     */
    public function setLocation($Location)
    {
        $this->Location = $Location;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMeasure
     */
    public function getMeasure()
    {
        return $this->Measure;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMeasure $Measure
     * @return $this
     */
    public function setMeasure($Measure)
    {
        $this->Measure = $Measure;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMeasureReport
     */
    public function getMeasureReport()
    {
        return $this->MeasureReport;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMeasureReport $MeasureReport
     * @return $this
     */
    public function setMeasureReport($MeasureReport)
    {
        $this->MeasureReport = $MeasureReport;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedia
     */
    public function getMedia()
    {
        return $this->Media;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedia $Media
     * @return $this
     */
    public function setMedia($Media)
    {
        $this->Media = $Media;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedication
     */
    public function getMedication()
    {
        return $this->Medication;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedication $Medication
     * @return $this
     */
    public function setMedication($Medication)
    {
        $this->Medication = $Medication;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicationAdministration
     */
    public function getMedicationAdministration()
    {
        return $this->MedicationAdministration;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicationAdministration $MedicationAdministration
     * @return $this
     */
    public function setMedicationAdministration($MedicationAdministration)
    {
        $this->MedicationAdministration = $MedicationAdministration;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicationDispense
     */
    public function getMedicationDispense()
    {
        return $this->MedicationDispense;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicationDispense $MedicationDispense
     * @return $this
     */
    public function setMedicationDispense($MedicationDispense)
    {
        $this->MedicationDispense = $MedicationDispense;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicationKnowledge
     */
    public function getMedicationKnowledge()
    {
        return $this->MedicationKnowledge;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicationKnowledge $MedicationKnowledge
     * @return $this
     */
    public function setMedicationKnowledge($MedicationKnowledge)
    {
        $this->MedicationKnowledge = $MedicationKnowledge;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicationRequest
     */
    public function getMedicationRequest()
    {
        return $this->MedicationRequest;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicationRequest $MedicationRequest
     * @return $this
     */
    public function setMedicationRequest($MedicationRequest)
    {
        $this->MedicationRequest = $MedicationRequest;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicationStatement
     */
    public function getMedicationStatement()
    {
        return $this->MedicationStatement;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicationStatement $MedicationStatement
     * @return $this
     */
    public function setMedicationStatement($MedicationStatement)
    {
        $this->MedicationStatement = $MedicationStatement;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicinalProduct
     */
    public function getMedicinalProduct()
    {
        return $this->MedicinalProduct;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicinalProduct $MedicinalProduct
     * @return $this
     */
    public function setMedicinalProduct($MedicinalProduct)
    {
        $this->MedicinalProduct = $MedicinalProduct;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicinalProductAuthorization
     */
    public function getMedicinalProductAuthorization()
    {
        return $this->MedicinalProductAuthorization;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicinalProductAuthorization $MedicinalProductAuthorization
     * @return $this
     */
    public function setMedicinalProductAuthorization($MedicinalProductAuthorization)
    {
        $this->MedicinalProductAuthorization = $MedicinalProductAuthorization;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicinalProductContraindication
     */
    public function getMedicinalProductContraindication()
    {
        return $this->MedicinalProductContraindication;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicinalProductContraindication $MedicinalProductContraindication
     * @return $this
     */
    public function setMedicinalProductContraindication($MedicinalProductContraindication)
    {
        $this->MedicinalProductContraindication = $MedicinalProductContraindication;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicinalProductIndication
     */
    public function getMedicinalProductIndication()
    {
        return $this->MedicinalProductIndication;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicinalProductIndication $MedicinalProductIndication
     * @return $this
     */
    public function setMedicinalProductIndication($MedicinalProductIndication)
    {
        $this->MedicinalProductIndication = $MedicinalProductIndication;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicinalProductIngredient
     */
    public function getMedicinalProductIngredient()
    {
        return $this->MedicinalProductIngredient;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicinalProductIngredient $MedicinalProductIngredient
     * @return $this
     */
    public function setMedicinalProductIngredient($MedicinalProductIngredient)
    {
        $this->MedicinalProductIngredient = $MedicinalProductIngredient;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicinalProductInteraction
     */
    public function getMedicinalProductInteraction()
    {
        return $this->MedicinalProductInteraction;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicinalProductInteraction $MedicinalProductInteraction
     * @return $this
     */
    public function setMedicinalProductInteraction($MedicinalProductInteraction)
    {
        $this->MedicinalProductInteraction = $MedicinalProductInteraction;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicinalProductManufactured
     */
    public function getMedicinalProductManufactured()
    {
        return $this->MedicinalProductManufactured;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicinalProductManufactured $MedicinalProductManufactured
     * @return $this
     */
    public function setMedicinalProductManufactured($MedicinalProductManufactured)
    {
        $this->MedicinalProductManufactured = $MedicinalProductManufactured;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicinalProductPackaged
     */
    public function getMedicinalProductPackaged()
    {
        return $this->MedicinalProductPackaged;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicinalProductPackaged $MedicinalProductPackaged
     * @return $this
     */
    public function setMedicinalProductPackaged($MedicinalProductPackaged)
    {
        $this->MedicinalProductPackaged = $MedicinalProductPackaged;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicinalProductPharmaceutical
     */
    public function getMedicinalProductPharmaceutical()
    {
        return $this->MedicinalProductPharmaceutical;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicinalProductPharmaceutical $MedicinalProductPharmaceutical
     * @return $this
     */
    public function setMedicinalProductPharmaceutical($MedicinalProductPharmaceutical)
    {
        $this->MedicinalProductPharmaceutical = $MedicinalProductPharmaceutical;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicinalProductUndesirableEffect
     */
    public function getMedicinalProductUndesirableEffect()
    {
        return $this->MedicinalProductUndesirableEffect;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicinalProductUndesirableEffect $MedicinalProductUndesirableEffect
     * @return $this
     */
    public function setMedicinalProductUndesirableEffect($MedicinalProductUndesirableEffect)
    {
        $this->MedicinalProductUndesirableEffect = $MedicinalProductUndesirableEffect;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMessageDefinition
     */
    public function getMessageDefinition()
    {
        return $this->MessageDefinition;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMessageDefinition $MessageDefinition
     * @return $this
     */
    public function setMessageDefinition($MessageDefinition)
    {
        $this->MessageDefinition = $MessageDefinition;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMessageHeader
     */
    public function getMessageHeader()
    {
        return $this->MessageHeader;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMessageHeader $MessageHeader
     * @return $this
     */
    public function setMessageHeader($MessageHeader)
    {
        $this->MessageHeader = $MessageHeader;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMolecularSequence
     */
    public function getMolecularSequence()
    {
        return $this->MolecularSequence;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMolecularSequence $MolecularSequence
     * @return $this
     */
    public function setMolecularSequence($MolecularSequence)
    {
        $this->MolecularSequence = $MolecularSequence;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRNamingSystem
     */
    public function getNamingSystem()
    {
        return $this->NamingSystem;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRNamingSystem $NamingSystem
     * @return $this
     */
    public function setNamingSystem($NamingSystem)
    {
        $this->NamingSystem = $NamingSystem;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRNutritionOrder
     */
    public function getNutritionOrder()
    {
        return $this->NutritionOrder;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRNutritionOrder $NutritionOrder
     * @return $this
     */
    public function setNutritionOrder($NutritionOrder)
    {
        $this->NutritionOrder = $NutritionOrder;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRObservation
     */
    public function getObservation()
    {
        return $this->Observation;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRObservation $Observation
     * @return $this
     */
    public function setObservation($Observation)
    {
        $this->Observation = $Observation;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRObservationDefinition
     */
    public function getObservationDefinition()
    {
        return $this->ObservationDefinition;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRObservationDefinition $ObservationDefinition
     * @return $this
     */
    public function setObservationDefinition($ObservationDefinition)
    {
        $this->ObservationDefinition = $ObservationDefinition;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIROperationDefinition
     */
    public function getOperationDefinition()
    {
        return $this->OperationDefinition;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIROperationDefinition $OperationDefinition
     * @return $this
     */
    public function setOperationDefinition($OperationDefinition)
    {
        $this->OperationDefinition = $OperationDefinition;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIROperationOutcome
     */
    public function getOperationOutcome()
    {
        return $this->OperationOutcome;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIROperationOutcome $OperationOutcome
     * @return $this
     */
    public function setOperationOutcome($OperationOutcome)
    {
        $this->OperationOutcome = $OperationOutcome;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIROrganization
     */
    public function getOrganization()
    {
        return $this->Organization;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIROrganization $Organization
     * @return $this
     */
    public function setOrganization($Organization)
    {
        $this->Organization = $Organization;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIROrganizationAffiliation
     */
    public function getOrganizationAffiliation()
    {
        return $this->OrganizationAffiliation;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIROrganizationAffiliation $OrganizationAffiliation
     * @return $this
     */
    public function setOrganizationAffiliation($OrganizationAffiliation)
    {
        $this->OrganizationAffiliation = $OrganizationAffiliation;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPatient
     */
    public function getPatient()
    {
        return $this->Patient;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPatient $Patient
     * @return $this
     */
    public function setPatient($Patient)
    {
        $this->Patient = $Patient;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPaymentNotice
     */
    public function getPaymentNotice()
    {
        return $this->PaymentNotice;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPaymentNotice $PaymentNotice
     * @return $this
     */
    public function setPaymentNotice($PaymentNotice)
    {
        $this->PaymentNotice = $PaymentNotice;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPaymentReconciliation
     */
    public function getPaymentReconciliation()
    {
        return $this->PaymentReconciliation;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPaymentReconciliation $PaymentReconciliation
     * @return $this
     */
    public function setPaymentReconciliation($PaymentReconciliation)
    {
        $this->PaymentReconciliation = $PaymentReconciliation;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPerson
     */
    public function getPerson()
    {
        return $this->Person;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPerson $Person
     * @return $this
     */
    public function setPerson($Person)
    {
        $this->Person = $Person;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPlanDefinition
     */
    public function getPlanDefinition()
    {
        return $this->PlanDefinition;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPlanDefinition $PlanDefinition
     * @return $this
     */
    public function setPlanDefinition($PlanDefinition)
    {
        $this->PlanDefinition = $PlanDefinition;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPractitioner
     */
    public function getPractitioner()
    {
        return $this->Practitioner;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPractitioner $Practitioner
     * @return $this
     */
    public function setPractitioner($Practitioner)
    {
        $this->Practitioner = $Practitioner;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPractitionerRole
     */
    public function getPractitionerRole()
    {
        return $this->PractitionerRole;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPractitionerRole $PractitionerRole
     * @return $this
     */
    public function setPractitionerRole($PractitionerRole)
    {
        $this->PractitionerRole = $PractitionerRole;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRProcedure
     */
    public function getProcedure()
    {
        return $this->Procedure;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRProcedure $Procedure
     * @return $this
     */
    public function setProcedure($Procedure)
    {
        $this->Procedure = $Procedure;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRProvenance
     */
    public function getProvenance()
    {
        return $this->Provenance;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRProvenance $Provenance
     * @return $this
     */
    public function setProvenance($Provenance)
    {
        $this->Provenance = $Provenance;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRQuestionnaire
     */
    public function getQuestionnaire()
    {
        return $this->Questionnaire;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRQuestionnaire $Questionnaire
     * @return $this
     */
    public function setQuestionnaire($Questionnaire)
    {
        $this->Questionnaire = $Questionnaire;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRQuestionnaireResponse
     */
    public function getQuestionnaireResponse()
    {
        return $this->QuestionnaireResponse;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRQuestionnaireResponse $QuestionnaireResponse
     * @return $this
     */
    public function setQuestionnaireResponse($QuestionnaireResponse)
    {
        $this->QuestionnaireResponse = $QuestionnaireResponse;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRRelatedPerson
     */
    public function getRelatedPerson()
    {
        return $this->RelatedPerson;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRRelatedPerson $RelatedPerson
     * @return $this
     */
    public function setRelatedPerson($RelatedPerson)
    {
        $this->RelatedPerson = $RelatedPerson;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRRequestGroup
     */
    public function getRequestGroup()
    {
        return $this->RequestGroup;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRRequestGroup $RequestGroup
     * @return $this
     */
    public function setRequestGroup($RequestGroup)
    {
        $this->RequestGroup = $RequestGroup;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRResearchDefinition
     */
    public function getResearchDefinition()
    {
        return $this->ResearchDefinition;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRResearchDefinition $ResearchDefinition
     * @return $this
     */
    public function setResearchDefinition($ResearchDefinition)
    {
        $this->ResearchDefinition = $ResearchDefinition;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRResearchElementDefinition
     */
    public function getResearchElementDefinition()
    {
        return $this->ResearchElementDefinition;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRResearchElementDefinition $ResearchElementDefinition
     * @return $this
     */
    public function setResearchElementDefinition($ResearchElementDefinition)
    {
        $this->ResearchElementDefinition = $ResearchElementDefinition;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRResearchStudy
     */
    public function getResearchStudy()
    {
        return $this->ResearchStudy;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRResearchStudy $ResearchStudy
     * @return $this
     */
    public function setResearchStudy($ResearchStudy)
    {
        $this->ResearchStudy = $ResearchStudy;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRResearchSubject
     */
    public function getResearchSubject()
    {
        return $this->ResearchSubject;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRResearchSubject $ResearchSubject
     * @return $this
     */
    public function setResearchSubject($ResearchSubject)
    {
        $this->ResearchSubject = $ResearchSubject;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRRiskAssessment
     */
    public function getRiskAssessment()
    {
        return $this->RiskAssessment;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRRiskAssessment $RiskAssessment
     * @return $this
     */
    public function setRiskAssessment($RiskAssessment)
    {
        $this->RiskAssessment = $RiskAssessment;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRRiskEvidenceSynthesis
     */
    public function getRiskEvidenceSynthesis()
    {
        return $this->RiskEvidenceSynthesis;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRRiskEvidenceSynthesis $RiskEvidenceSynthesis
     * @return $this
     */
    public function setRiskEvidenceSynthesis($RiskEvidenceSynthesis)
    {
        $this->RiskEvidenceSynthesis = $RiskEvidenceSynthesis;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSchedule
     */
    public function getSchedule()
    {
        return $this->Schedule;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSchedule $Schedule
     * @return $this
     */
    public function setSchedule($Schedule)
    {
        $this->Schedule = $Schedule;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSearchParameter
     */
    public function getSearchParameter()
    {
        return $this->SearchParameter;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSearchParameter $SearchParameter
     * @return $this
     */
    public function setSearchParameter($SearchParameter)
    {
        $this->SearchParameter = $SearchParameter;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRServiceRequest
     */
    public function getServiceRequest()
    {
        return $this->ServiceRequest;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRServiceRequest $ServiceRequest
     * @return $this
     */
    public function setServiceRequest($ServiceRequest)
    {
        $this->ServiceRequest = $ServiceRequest;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSlot
     */
    public function getSlot()
    {
        return $this->Slot;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSlot $Slot
     * @return $this
     */
    public function setSlot($Slot)
    {
        $this->Slot = $Slot;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSpecimen
     */
    public function getSpecimen()
    {
        return $this->Specimen;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSpecimen $Specimen
     * @return $this
     */
    public function setSpecimen($Specimen)
    {
        $this->Specimen = $Specimen;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSpecimenDefinition
     */
    public function getSpecimenDefinition()
    {
        return $this->SpecimenDefinition;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSpecimenDefinition $SpecimenDefinition
     * @return $this
     */
    public function setSpecimenDefinition($SpecimenDefinition)
    {
        $this->SpecimenDefinition = $SpecimenDefinition;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRStructureDefinition
     */
    public function getStructureDefinition()
    {
        return $this->StructureDefinition;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRStructureDefinition $StructureDefinition
     * @return $this
     */
    public function setStructureDefinition($StructureDefinition)
    {
        $this->StructureDefinition = $StructureDefinition;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRStructureMap
     */
    public function getStructureMap()
    {
        return $this->StructureMap;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRStructureMap $StructureMap
     * @return $this
     */
    public function setStructureMap($StructureMap)
    {
        $this->StructureMap = $StructureMap;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSubscription
     */
    public function getSubscription()
    {
        return $this->Subscription;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSubscription $Subscription
     * @return $this
     */
    public function setSubscription($Subscription)
    {
        $this->Subscription = $Subscription;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSubstance
     */
    public function getSubstance()
    {
        return $this->Substance;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSubstance $Substance
     * @return $this
     */
    public function setSubstance($Substance)
    {
        $this->Substance = $Substance;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSubstanceNucleicAcid
     */
    public function getSubstanceNucleicAcid()
    {
        return $this->SubstanceNucleicAcid;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSubstanceNucleicAcid $SubstanceNucleicAcid
     * @return $this
     */
    public function setSubstanceNucleicAcid($SubstanceNucleicAcid)
    {
        $this->SubstanceNucleicAcid = $SubstanceNucleicAcid;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSubstancePolymer
     */
    public function getSubstancePolymer()
    {
        return $this->SubstancePolymer;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSubstancePolymer $SubstancePolymer
     * @return $this
     */
    public function setSubstancePolymer($SubstancePolymer)
    {
        $this->SubstancePolymer = $SubstancePolymer;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSubstanceProtein
     */
    public function getSubstanceProtein()
    {
        return $this->SubstanceProtein;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSubstanceProtein $SubstanceProtein
     * @return $this
     */
    public function setSubstanceProtein($SubstanceProtein)
    {
        $this->SubstanceProtein = $SubstanceProtein;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSubstanceReferenceInformation
     */
    public function getSubstanceReferenceInformation()
    {
        return $this->SubstanceReferenceInformation;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSubstanceReferenceInformation $SubstanceReferenceInformation
     * @return $this
     */
    public function setSubstanceReferenceInformation($SubstanceReferenceInformation)
    {
        $this->SubstanceReferenceInformation = $SubstanceReferenceInformation;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSubstanceSourceMaterial
     */
    public function getSubstanceSourceMaterial()
    {
        return $this->SubstanceSourceMaterial;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSubstanceSourceMaterial $SubstanceSourceMaterial
     * @return $this
     */
    public function setSubstanceSourceMaterial($SubstanceSourceMaterial)
    {
        $this->SubstanceSourceMaterial = $SubstanceSourceMaterial;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSubstanceSpecification
     */
    public function getSubstanceSpecification()
    {
        return $this->SubstanceSpecification;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSubstanceSpecification $SubstanceSpecification
     * @return $this
     */
    public function setSubstanceSpecification($SubstanceSpecification)
    {
        $this->SubstanceSpecification = $SubstanceSpecification;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSupplyDelivery
     */
    public function getSupplyDelivery()
    {
        return $this->SupplyDelivery;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSupplyDelivery $SupplyDelivery
     * @return $this
     */
    public function setSupplyDelivery($SupplyDelivery)
    {
        $this->SupplyDelivery = $SupplyDelivery;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSupplyRequest
     */
    public function getSupplyRequest()
    {
        return $this->SupplyRequest;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSupplyRequest $SupplyRequest
     * @return $this
     */
    public function setSupplyRequest($SupplyRequest)
    {
        $this->SupplyRequest = $SupplyRequest;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRTask
     */
    public function getTask()
    {
        return $this->Task;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRTask $Task
     * @return $this
     */
    public function setTask($Task)
    {
        $this->Task = $Task;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRTerminologyCapabilities
     */
    public function getTerminologyCapabilities()
    {
        return $this->TerminologyCapabilities;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRTerminologyCapabilities $TerminologyCapabilities
     * @return $this
     */
    public function setTerminologyCapabilities($TerminologyCapabilities)
    {
        $this->TerminologyCapabilities = $TerminologyCapabilities;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRTestReport
     */
    public function getTestReport()
    {
        return $this->TestReport;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRTestReport $TestReport
     * @return $this
     */
    public function setTestReport($TestReport)
    {
        $this->TestReport = $TestReport;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRTestScript
     */
    public function getTestScript()
    {
        return $this->TestScript;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRTestScript $TestScript
     * @return $this
     */
    public function setTestScript($TestScript)
    {
        $this->TestScript = $TestScript;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRValueSet
     */
    public function getValueSet()
    {
        return $this->ValueSet;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRValueSet $ValueSet
     * @return $this
     */
    public function setValueSet($ValueSet)
    {
        $this->ValueSet = $ValueSet;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRVerificationResult
     */
    public function getVerificationResult()
    {
        return $this->VerificationResult;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRVerificationResult $VerificationResult
     * @return $this
     */
    public function setVerificationResult($VerificationResult)
    {
        $this->VerificationResult = $VerificationResult;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRVisionPrescription
     */
    public function getVisionPrescription()
    {
        return $this->VisionPrescription;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRVisionPrescription $VisionPrescription
     * @return $this
     */
    public function setVisionPrescription($VisionPrescription)
    {
        $this->VisionPrescription = $VisionPrescription;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRParameters
     */
    public function getParameters()
    {
        return $this->Parameters;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRParameters $Parameters
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
        } elseif (is_array($data)) {
            if (($cnt = count($data)) > 1) {
                throw new \InvalidArgumentException("ResourceContainers may only contain 1 object, \"{$cnt}\" values provided");
            } else {
                $k = key($data);
                $this->{"set{$k}"}($data);
            }
        } elseif (null !== $data) {
            throw new \InvalidArgumentException('$data expected to be object or array, saw ' . gettype($data));
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
    public function jsonSerialize(): mixed
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
        if (isset($this->BiologicallyDerivedProduct)) {
            return $this->BiologicallyDerivedProduct;
        }
        if (isset($this->BodyStructure)) {
            return $this->BodyStructure;
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
        if (isset($this->CatalogEntry)) {
            return $this->CatalogEntry;
        }
        if (isset($this->ChargeItem)) {
            return $this->ChargeItem;
        }
        if (isset($this->ChargeItemDefinition)) {
            return $this->ChargeItemDefinition;
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
        if (isset($this->CoverageEligibilityRequest)) {
            return $this->CoverageEligibilityRequest;
        }
        if (isset($this->CoverageEligibilityResponse)) {
            return $this->CoverageEligibilityResponse;
        }
        if (isset($this->DetectedIssue)) {
            return $this->DetectedIssue;
        }
        if (isset($this->Device)) {
            return $this->Device;
        }
        if (isset($this->DeviceDefinition)) {
            return $this->DeviceDefinition;
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
        if (isset($this->EffectEvidenceSynthesis)) {
            return $this->EffectEvidenceSynthesis;
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
        if (isset($this->EventDefinition)) {
            return $this->EventDefinition;
        }
        if (isset($this->Evidence)) {
            return $this->Evidence;
        }
        if (isset($this->EvidenceVariable)) {
            return $this->EvidenceVariable;
        }
        if (isset($this->ExampleScenario)) {
            return $this->ExampleScenario;
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
        if (isset($this->ImagingStudy)) {
            return $this->ImagingStudy;
        }
        if (isset($this->Immunization)) {
            return $this->Immunization;
        }
        if (isset($this->ImmunizationEvaluation)) {
            return $this->ImmunizationEvaluation;
        }
        if (isset($this->ImmunizationRecommendation)) {
            return $this->ImmunizationRecommendation;
        }
        if (isset($this->ImplementationGuide)) {
            return $this->ImplementationGuide;
        }
        if (isset($this->InsurancePlan)) {
            return $this->InsurancePlan;
        }
        if (isset($this->Invoice)) {
            return $this->Invoice;
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
        if (isset($this->MedicationKnowledge)) {
            return $this->MedicationKnowledge;
        }
        if (isset($this->MedicationRequest)) {
            return $this->MedicationRequest;
        }
        if (isset($this->MedicationStatement)) {
            return $this->MedicationStatement;
        }
        if (isset($this->MedicinalProduct)) {
            return $this->MedicinalProduct;
        }
        if (isset($this->MedicinalProductAuthorization)) {
            return $this->MedicinalProductAuthorization;
        }
        if (isset($this->MedicinalProductContraindication)) {
            return $this->MedicinalProductContraindication;
        }
        if (isset($this->MedicinalProductIndication)) {
            return $this->MedicinalProductIndication;
        }
        if (isset($this->MedicinalProductIngredient)) {
            return $this->MedicinalProductIngredient;
        }
        if (isset($this->MedicinalProductInteraction)) {
            return $this->MedicinalProductInteraction;
        }
        if (isset($this->MedicinalProductManufactured)) {
            return $this->MedicinalProductManufactured;
        }
        if (isset($this->MedicinalProductPackaged)) {
            return $this->MedicinalProductPackaged;
        }
        if (isset($this->MedicinalProductPharmaceutical)) {
            return $this->MedicinalProductPharmaceutical;
        }
        if (isset($this->MedicinalProductUndesirableEffect)) {
            return $this->MedicinalProductUndesirableEffect;
        }
        if (isset($this->MessageDefinition)) {
            return $this->MessageDefinition;
        }
        if (isset($this->MessageHeader)) {
            return $this->MessageHeader;
        }
        if (isset($this->MolecularSequence)) {
            return $this->MolecularSequence;
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
        if (isset($this->ObservationDefinition)) {
            return $this->ObservationDefinition;
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
        if (isset($this->OrganizationAffiliation)) {
            return $this->OrganizationAffiliation;
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
        if (isset($this->Provenance)) {
            return $this->Provenance;
        }
        if (isset($this->Questionnaire)) {
            return $this->Questionnaire;
        }
        if (isset($this->QuestionnaireResponse)) {
            return $this->QuestionnaireResponse;
        }
        if (isset($this->RelatedPerson)) {
            return $this->RelatedPerson;
        }
        if (isset($this->RequestGroup)) {
            return $this->RequestGroup;
        }
        if (isset($this->ResearchDefinition)) {
            return $this->ResearchDefinition;
        }
        if (isset($this->ResearchElementDefinition)) {
            return $this->ResearchElementDefinition;
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
        if (isset($this->RiskEvidenceSynthesis)) {
            return $this->RiskEvidenceSynthesis;
        }
        if (isset($this->Schedule)) {
            return $this->Schedule;
        }
        if (isset($this->SearchParameter)) {
            return $this->SearchParameter;
        }
        if (isset($this->ServiceRequest)) {
            return $this->ServiceRequest;
        }
        if (isset($this->Slot)) {
            return $this->Slot;
        }
        if (isset($this->Specimen)) {
            return $this->Specimen;
        }
        if (isset($this->SpecimenDefinition)) {
            return $this->SpecimenDefinition;
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
        if (isset($this->SubstanceNucleicAcid)) {
            return $this->SubstanceNucleicAcid;
        }
        if (isset($this->SubstancePolymer)) {
            return $this->SubstancePolymer;
        }
        if (isset($this->SubstanceProtein)) {
            return $this->SubstanceProtein;
        }
        if (isset($this->SubstanceReferenceInformation)) {
            return $this->SubstanceReferenceInformation;
        }
        if (isset($this->SubstanceSourceMaterial)) {
            return $this->SubstanceSourceMaterial;
        }
        if (isset($this->SubstanceSpecification)) {
            return $this->SubstanceSpecification;
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
        if (isset($this->TerminologyCapabilities)) {
            return $this->TerminologyCapabilities;
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
        if (isset($this->VerificationResult)) {
            return $this->VerificationResult;
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
        } elseif (isset($this->ActivityDefinition)) {
            $this->ActivityDefinition->xmlSerialize(true, $sxe->addChild('ActivityDefinition'));
        } elseif (isset($this->AdverseEvent)) {
            $this->AdverseEvent->xmlSerialize(true, $sxe->addChild('AdverseEvent'));
        } elseif (isset($this->AllergyIntolerance)) {
            $this->AllergyIntolerance->xmlSerialize(true, $sxe->addChild('AllergyIntolerance'));
        } elseif (isset($this->Appointment)) {
            $this->Appointment->xmlSerialize(true, $sxe->addChild('Appointment'));
        } elseif (isset($this->AppointmentResponse)) {
            $this->AppointmentResponse->xmlSerialize(true, $sxe->addChild('AppointmentResponse'));
        } elseif (isset($this->AuditEvent)) {
            $this->AuditEvent->xmlSerialize(true, $sxe->addChild('AuditEvent'));
        } elseif (isset($this->Basic)) {
            $this->Basic->xmlSerialize(true, $sxe->addChild('Basic'));
        } elseif (isset($this->Binary)) {
            $this->Binary->xmlSerialize(true, $sxe->addChild('Binary'));
        } elseif (isset($this->BiologicallyDerivedProduct)) {
            $this->BiologicallyDerivedProduct->xmlSerialize(true, $sxe->addChild('BiologicallyDerivedProduct'));
        } elseif (isset($this->BodyStructure)) {
            $this->BodyStructure->xmlSerialize(true, $sxe->addChild('BodyStructure'));
        } elseif (isset($this->Bundle)) {
            $this->Bundle->xmlSerialize(true, $sxe->addChild('Bundle'));
        } elseif (isset($this->CapabilityStatement)) {
            $this->CapabilityStatement->xmlSerialize(true, $sxe->addChild('CapabilityStatement'));
        } elseif (isset($this->CarePlan)) {
            $this->CarePlan->xmlSerialize(true, $sxe->addChild('CarePlan'));
        } elseif (isset($this->CareTeam)) {
            $this->CareTeam->xmlSerialize(true, $sxe->addChild('CareTeam'));
        } elseif (isset($this->CatalogEntry)) {
            $this->CatalogEntry->xmlSerialize(true, $sxe->addChild('CatalogEntry'));
        } elseif (isset($this->ChargeItem)) {
            $this->ChargeItem->xmlSerialize(true, $sxe->addChild('ChargeItem'));
        } elseif (isset($this->ChargeItemDefinition)) {
            $this->ChargeItemDefinition->xmlSerialize(true, $sxe->addChild('ChargeItemDefinition'));
        } elseif (isset($this->Claim)) {
            $this->Claim->xmlSerialize(true, $sxe->addChild('Claim'));
        } elseif (isset($this->ClaimResponse)) {
            $this->ClaimResponse->xmlSerialize(true, $sxe->addChild('ClaimResponse'));
        } elseif (isset($this->ClinicalImpression)) {
            $this->ClinicalImpression->xmlSerialize(true, $sxe->addChild('ClinicalImpression'));
        } elseif (isset($this->CodeSystem)) {
            $this->CodeSystem->xmlSerialize(true, $sxe->addChild('CodeSystem'));
        } elseif (isset($this->Communication)) {
            $this->Communication->xmlSerialize(true, $sxe->addChild('Communication'));
        } elseif (isset($this->CommunicationRequest)) {
            $this->CommunicationRequest->xmlSerialize(true, $sxe->addChild('CommunicationRequest'));
        } elseif (isset($this->CompartmentDefinition)) {
            $this->CompartmentDefinition->xmlSerialize(true, $sxe->addChild('CompartmentDefinition'));
        } elseif (isset($this->Composition)) {
            $this->Composition->xmlSerialize(true, $sxe->addChild('Composition'));
        } elseif (isset($this->ConceptMap)) {
            $this->ConceptMap->xmlSerialize(true, $sxe->addChild('ConceptMap'));
        } elseif (isset($this->Condition)) {
            $this->Condition->xmlSerialize(true, $sxe->addChild('Condition'));
        } elseif (isset($this->Consent)) {
            $this->Consent->xmlSerialize(true, $sxe->addChild('Consent'));
        } elseif (isset($this->Contract)) {
            $this->Contract->xmlSerialize(true, $sxe->addChild('Contract'));
        } elseif (isset($this->Coverage)) {
            $this->Coverage->xmlSerialize(true, $sxe->addChild('Coverage'));
        } elseif (isset($this->CoverageEligibilityRequest)) {
            $this->CoverageEligibilityRequest->xmlSerialize(true, $sxe->addChild('CoverageEligibilityRequest'));
        } elseif (isset($this->CoverageEligibilityResponse)) {
            $this->CoverageEligibilityResponse->xmlSerialize(true, $sxe->addChild('CoverageEligibilityResponse'));
        } elseif (isset($this->DetectedIssue)) {
            $this->DetectedIssue->xmlSerialize(true, $sxe->addChild('DetectedIssue'));
        } elseif (isset($this->Device)) {
            $this->Device->xmlSerialize(true, $sxe->addChild('Device'));
        } elseif (isset($this->DeviceDefinition)) {
            $this->DeviceDefinition->xmlSerialize(true, $sxe->addChild('DeviceDefinition'));
        } elseif (isset($this->DeviceMetric)) {
            $this->DeviceMetric->xmlSerialize(true, $sxe->addChild('DeviceMetric'));
        } elseif (isset($this->DeviceRequest)) {
            $this->DeviceRequest->xmlSerialize(true, $sxe->addChild('DeviceRequest'));
        } elseif (isset($this->DeviceUseStatement)) {
            $this->DeviceUseStatement->xmlSerialize(true, $sxe->addChild('DeviceUseStatement'));
        } elseif (isset($this->DiagnosticReport)) {
            $this->DiagnosticReport->xmlSerialize(true, $sxe->addChild('DiagnosticReport'));
        } elseif (isset($this->DocumentManifest)) {
            $this->DocumentManifest->xmlSerialize(true, $sxe->addChild('DocumentManifest'));
        } elseif (isset($this->DocumentReference)) {
            $this->DocumentReference->xmlSerialize(true, $sxe->addChild('DocumentReference'));
        } elseif (isset($this->EffectEvidenceSynthesis)) {
            $this->EffectEvidenceSynthesis->xmlSerialize(true, $sxe->addChild('EffectEvidenceSynthesis'));
        } elseif (isset($this->Encounter)) {
            $this->Encounter->xmlSerialize(true, $sxe->addChild('Encounter'));
        } elseif (isset($this->Endpoint)) {
            $this->Endpoint->xmlSerialize(true, $sxe->addChild('Endpoint'));
        } elseif (isset($this->EnrollmentRequest)) {
            $this->EnrollmentRequest->xmlSerialize(true, $sxe->addChild('EnrollmentRequest'));
        } elseif (isset($this->EnrollmentResponse)) {
            $this->EnrollmentResponse->xmlSerialize(true, $sxe->addChild('EnrollmentResponse'));
        } elseif (isset($this->EpisodeOfCare)) {
            $this->EpisodeOfCare->xmlSerialize(true, $sxe->addChild('EpisodeOfCare'));
        } elseif (isset($this->EventDefinition)) {
            $this->EventDefinition->xmlSerialize(true, $sxe->addChild('EventDefinition'));
        } elseif (isset($this->Evidence)) {
            $this->Evidence->xmlSerialize(true, $sxe->addChild('Evidence'));
        } elseif (isset($this->EvidenceVariable)) {
            $this->EvidenceVariable->xmlSerialize(true, $sxe->addChild('EvidenceVariable'));
        } elseif (isset($this->ExampleScenario)) {
            $this->ExampleScenario->xmlSerialize(true, $sxe->addChild('ExampleScenario'));
        } elseif (isset($this->ExplanationOfBenefit)) {
            $this->ExplanationOfBenefit->xmlSerialize(true, $sxe->addChild('ExplanationOfBenefit'));
        } elseif (isset($this->FamilyMemberHistory)) {
            $this->FamilyMemberHistory->xmlSerialize(true, $sxe->addChild('FamilyMemberHistory'));
        } elseif (isset($this->Flag)) {
            $this->Flag->xmlSerialize(true, $sxe->addChild('Flag'));
        } elseif (isset($this->Goal)) {
            $this->Goal->xmlSerialize(true, $sxe->addChild('Goal'));
        } elseif (isset($this->GraphDefinition)) {
            $this->GraphDefinition->xmlSerialize(true, $sxe->addChild('GraphDefinition'));
        } elseif (isset($this->Group)) {
            $this->Group->xmlSerialize(true, $sxe->addChild('Group'));
        } elseif (isset($this->GuidanceResponse)) {
            $this->GuidanceResponse->xmlSerialize(true, $sxe->addChild('GuidanceResponse'));
        } elseif (isset($this->HealthcareService)) {
            $this->HealthcareService->xmlSerialize(true, $sxe->addChild('HealthcareService'));
        } elseif (isset($this->ImagingStudy)) {
            $this->ImagingStudy->xmlSerialize(true, $sxe->addChild('ImagingStudy'));
        } elseif (isset($this->Immunization)) {
            $this->Immunization->xmlSerialize(true, $sxe->addChild('Immunization'));
        } elseif (isset($this->ImmunizationEvaluation)) {
            $this->ImmunizationEvaluation->xmlSerialize(true, $sxe->addChild('ImmunizationEvaluation'));
        } elseif (isset($this->ImmunizationRecommendation)) {
            $this->ImmunizationRecommendation->xmlSerialize(true, $sxe->addChild('ImmunizationRecommendation'));
        } elseif (isset($this->ImplementationGuide)) {
            $this->ImplementationGuide->xmlSerialize(true, $sxe->addChild('ImplementationGuide'));
        } elseif (isset($this->InsurancePlan)) {
            $this->InsurancePlan->xmlSerialize(true, $sxe->addChild('InsurancePlan'));
        } elseif (isset($this->Invoice)) {
            $this->Invoice->xmlSerialize(true, $sxe->addChild('Invoice'));
        } elseif (isset($this->Library)) {
            $this->Library->xmlSerialize(true, $sxe->addChild('Library'));
        } elseif (isset($this->Linkage)) {
            $this->Linkage->xmlSerialize(true, $sxe->addChild('Linkage'));
        } elseif (isset($this->List)) {
            $this->List->xmlSerialize(true, $sxe->addChild('List'));
        } elseif (isset($this->Location)) {
            $this->Location->xmlSerialize(true, $sxe->addChild('Location'));
        } elseif (isset($this->Measure)) {
            $this->Measure->xmlSerialize(true, $sxe->addChild('Measure'));
        } elseif (isset($this->MeasureReport)) {
            $this->MeasureReport->xmlSerialize(true, $sxe->addChild('MeasureReport'));
        } elseif (isset($this->Media)) {
            $this->Media->xmlSerialize(true, $sxe->addChild('Media'));
        } elseif (isset($this->Medication)) {
            $this->Medication->xmlSerialize(true, $sxe->addChild('Medication'));
        } elseif (isset($this->MedicationAdministration)) {
            $this->MedicationAdministration->xmlSerialize(true, $sxe->addChild('MedicationAdministration'));
        } elseif (isset($this->MedicationDispense)) {
            $this->MedicationDispense->xmlSerialize(true, $sxe->addChild('MedicationDispense'));
        } elseif (isset($this->MedicationKnowledge)) {
            $this->MedicationKnowledge->xmlSerialize(true, $sxe->addChild('MedicationKnowledge'));
        } elseif (isset($this->MedicationRequest)) {
            $this->MedicationRequest->xmlSerialize(true, $sxe->addChild('MedicationRequest'));
        } elseif (isset($this->MedicationStatement)) {
            $this->MedicationStatement->xmlSerialize(true, $sxe->addChild('MedicationStatement'));
        } elseif (isset($this->MedicinalProduct)) {
            $this->MedicinalProduct->xmlSerialize(true, $sxe->addChild('MedicinalProduct'));
        } elseif (isset($this->MedicinalProductAuthorization)) {
            $this->MedicinalProductAuthorization->xmlSerialize(true, $sxe->addChild('MedicinalProductAuthorization'));
        } elseif (isset($this->MedicinalProductContraindication)) {
            $this->MedicinalProductContraindication->xmlSerialize(true, $sxe->addChild('MedicinalProductContraindication'));
        } elseif (isset($this->MedicinalProductIndication)) {
            $this->MedicinalProductIndication->xmlSerialize(true, $sxe->addChild('MedicinalProductIndication'));
        } elseif (isset($this->MedicinalProductIngredient)) {
            $this->MedicinalProductIngredient->xmlSerialize(true, $sxe->addChild('MedicinalProductIngredient'));
        } elseif (isset($this->MedicinalProductInteraction)) {
            $this->MedicinalProductInteraction->xmlSerialize(true, $sxe->addChild('MedicinalProductInteraction'));
        } elseif (isset($this->MedicinalProductManufactured)) {
            $this->MedicinalProductManufactured->xmlSerialize(true, $sxe->addChild('MedicinalProductManufactured'));
        } elseif (isset($this->MedicinalProductPackaged)) {
            $this->MedicinalProductPackaged->xmlSerialize(true, $sxe->addChild('MedicinalProductPackaged'));
        } elseif (isset($this->MedicinalProductPharmaceutical)) {
            $this->MedicinalProductPharmaceutical->xmlSerialize(true, $sxe->addChild('MedicinalProductPharmaceutical'));
        } elseif (isset($this->MedicinalProductUndesirableEffect)) {
            $this->MedicinalProductUndesirableEffect->xmlSerialize(true, $sxe->addChild('MedicinalProductUndesirableEffect'));
        } elseif (isset($this->MessageDefinition)) {
            $this->MessageDefinition->xmlSerialize(true, $sxe->addChild('MessageDefinition'));
        } elseif (isset($this->MessageHeader)) {
            $this->MessageHeader->xmlSerialize(true, $sxe->addChild('MessageHeader'));
        } elseif (isset($this->MolecularSequence)) {
            $this->MolecularSequence->xmlSerialize(true, $sxe->addChild('MolecularSequence'));
        } elseif (isset($this->NamingSystem)) {
            $this->NamingSystem->xmlSerialize(true, $sxe->addChild('NamingSystem'));
        } elseif (isset($this->NutritionOrder)) {
            $this->NutritionOrder->xmlSerialize(true, $sxe->addChild('NutritionOrder'));
        } elseif (isset($this->Observation)) {
            $this->Observation->xmlSerialize(true, $sxe->addChild('Observation'));
        } elseif (isset($this->ObservationDefinition)) {
            $this->ObservationDefinition->xmlSerialize(true, $sxe->addChild('ObservationDefinition'));
        } elseif (isset($this->OperationDefinition)) {
            $this->OperationDefinition->xmlSerialize(true, $sxe->addChild('OperationDefinition'));
        } elseif (isset($this->OperationOutcome)) {
            $this->OperationOutcome->xmlSerialize(true, $sxe->addChild('OperationOutcome'));
        } elseif (isset($this->Organization)) {
            $this->Organization->xmlSerialize(true, $sxe->addChild('Organization'));
        } elseif (isset($this->OrganizationAffiliation)) {
            $this->OrganizationAffiliation->xmlSerialize(true, $sxe->addChild('OrganizationAffiliation'));
        } elseif (isset($this->Patient)) {
            $this->Patient->xmlSerialize(true, $sxe->addChild('Patient'));
        } elseif (isset($this->PaymentNotice)) {
            $this->PaymentNotice->xmlSerialize(true, $sxe->addChild('PaymentNotice'));
        } elseif (isset($this->PaymentReconciliation)) {
            $this->PaymentReconciliation->xmlSerialize(true, $sxe->addChild('PaymentReconciliation'));
        } elseif (isset($this->Person)) {
            $this->Person->xmlSerialize(true, $sxe->addChild('Person'));
        } elseif (isset($this->PlanDefinition)) {
            $this->PlanDefinition->xmlSerialize(true, $sxe->addChild('PlanDefinition'));
        } elseif (isset($this->Practitioner)) {
            $this->Practitioner->xmlSerialize(true, $sxe->addChild('Practitioner'));
        } elseif (isset($this->PractitionerRole)) {
            $this->PractitionerRole->xmlSerialize(true, $sxe->addChild('PractitionerRole'));
        } elseif (isset($this->Procedure)) {
            $this->Procedure->xmlSerialize(true, $sxe->addChild('Procedure'));
        } elseif (isset($this->Provenance)) {
            $this->Provenance->xmlSerialize(true, $sxe->addChild('Provenance'));
        } elseif (isset($this->Questionnaire)) {
            $this->Questionnaire->xmlSerialize(true, $sxe->addChild('Questionnaire'));
        } elseif (isset($this->QuestionnaireResponse)) {
            $this->QuestionnaireResponse->xmlSerialize(true, $sxe->addChild('QuestionnaireResponse'));
        } elseif (isset($this->RelatedPerson)) {
            $this->RelatedPerson->xmlSerialize(true, $sxe->addChild('RelatedPerson'));
        } elseif (isset($this->RequestGroup)) {
            $this->RequestGroup->xmlSerialize(true, $sxe->addChild('RequestGroup'));
        } elseif (isset($this->ResearchDefinition)) {
            $this->ResearchDefinition->xmlSerialize(true, $sxe->addChild('ResearchDefinition'));
        } elseif (isset($this->ResearchElementDefinition)) {
            $this->ResearchElementDefinition->xmlSerialize(true, $sxe->addChild('ResearchElementDefinition'));
        } elseif (isset($this->ResearchStudy)) {
            $this->ResearchStudy->xmlSerialize(true, $sxe->addChild('ResearchStudy'));
        } elseif (isset($this->ResearchSubject)) {
            $this->ResearchSubject->xmlSerialize(true, $sxe->addChild('ResearchSubject'));
        } elseif (isset($this->RiskAssessment)) {
            $this->RiskAssessment->xmlSerialize(true, $sxe->addChild('RiskAssessment'));
        } elseif (isset($this->RiskEvidenceSynthesis)) {
            $this->RiskEvidenceSynthesis->xmlSerialize(true, $sxe->addChild('RiskEvidenceSynthesis'));
        } elseif (isset($this->Schedule)) {
            $this->Schedule->xmlSerialize(true, $sxe->addChild('Schedule'));
        } elseif (isset($this->SearchParameter)) {
            $this->SearchParameter->xmlSerialize(true, $sxe->addChild('SearchParameter'));
        } elseif (isset($this->ServiceRequest)) {
            $this->ServiceRequest->xmlSerialize(true, $sxe->addChild('ServiceRequest'));
        } elseif (isset($this->Slot)) {
            $this->Slot->xmlSerialize(true, $sxe->addChild('Slot'));
        } elseif (isset($this->Specimen)) {
            $this->Specimen->xmlSerialize(true, $sxe->addChild('Specimen'));
        } elseif (isset($this->SpecimenDefinition)) {
            $this->SpecimenDefinition->xmlSerialize(true, $sxe->addChild('SpecimenDefinition'));
        } elseif (isset($this->StructureDefinition)) {
            $this->StructureDefinition->xmlSerialize(true, $sxe->addChild('StructureDefinition'));
        } elseif (isset($this->StructureMap)) {
            $this->StructureMap->xmlSerialize(true, $sxe->addChild('StructureMap'));
        } elseif (isset($this->Subscription)) {
            $this->Subscription->xmlSerialize(true, $sxe->addChild('Subscription'));
        } elseif (isset($this->Substance)) {
            $this->Substance->xmlSerialize(true, $sxe->addChild('Substance'));
        } elseif (isset($this->SubstanceNucleicAcid)) {
            $this->SubstanceNucleicAcid->xmlSerialize(true, $sxe->addChild('SubstanceNucleicAcid'));
        } elseif (isset($this->SubstancePolymer)) {
            $this->SubstancePolymer->xmlSerialize(true, $sxe->addChild('SubstancePolymer'));
        } elseif (isset($this->SubstanceProtein)) {
            $this->SubstanceProtein->xmlSerialize(true, $sxe->addChild('SubstanceProtein'));
        } elseif (isset($this->SubstanceReferenceInformation)) {
            $this->SubstanceReferenceInformation->xmlSerialize(true, $sxe->addChild('SubstanceReferenceInformation'));
        } elseif (isset($this->SubstanceSourceMaterial)) {
            $this->SubstanceSourceMaterial->xmlSerialize(true, $sxe->addChild('SubstanceSourceMaterial'));
        } elseif (isset($this->SubstanceSpecification)) {
            $this->SubstanceSpecification->xmlSerialize(true, $sxe->addChild('SubstanceSpecification'));
        } elseif (isset($this->SupplyDelivery)) {
            $this->SupplyDelivery->xmlSerialize(true, $sxe->addChild('SupplyDelivery'));
        } elseif (isset($this->SupplyRequest)) {
            $this->SupplyRequest->xmlSerialize(true, $sxe->addChild('SupplyRequest'));
        } elseif (isset($this->Task)) {
            $this->Task->xmlSerialize(true, $sxe->addChild('Task'));
        } elseif (isset($this->TerminologyCapabilities)) {
            $this->TerminologyCapabilities->xmlSerialize(true, $sxe->addChild('TerminologyCapabilities'));
        } elseif (isset($this->TestReport)) {
            $this->TestReport->xmlSerialize(true, $sxe->addChild('TestReport'));
        } elseif (isset($this->TestScript)) {
            $this->TestScript->xmlSerialize(true, $sxe->addChild('TestScript'));
        } elseif (isset($this->ValueSet)) {
            $this->ValueSet->xmlSerialize(true, $sxe->addChild('ValueSet'));
        } elseif (isset($this->VerificationResult)) {
            $this->VerificationResult->xmlSerialize(true, $sxe->addChild('VerificationResult'));
        } elseif (isset($this->VisionPrescription)) {
            $this->VisionPrescription->xmlSerialize(true, $sxe->addChild('VisionPrescription'));
        } elseif (isset($this->Parameters)) {
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
