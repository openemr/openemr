<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 *
 * Class creation date: April 15th, 2026 16:02+0000
 *
 * PHPFHIR Copyright:
 *
 * Copyright 2016-2026 Daniel Carbone (daniel.p.carbone@gmail.com)
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
 *   Generated on Fri, Nov 1, 2019 09:29+1100 for FHIR v4.0.1
 *
 *   Note: the schemas & schematrons do not contain all of the rules about what makes resources
 *   valid. Implementers will still need to be familiar with the content of the specification and with
 *   any profiles that apply to the resources in order to make a conformant implementation.
 *
 */
use OpenEMR\FHIR\Client\ClientErrorException;
use OpenEMR\FHIR\Client\ClientInterface;
use OpenEMR\FHIR\Client\HTTPMethodEnum;
use OpenEMR\FHIR\Client\Request;
use OpenEMR\FHIR\Client\Response;
use OpenEMR\FHIR\Client\SortDirectionEnum;
use OpenEMR\FHIR\Client\UnexpectedResponseCodeException;
use OpenEMR\FHIR\Encoding\ResourceParser;
use OpenEMR\FHIR\Encoding\SerializeFormatEnum;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRBinary;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRAccount;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRActivityDefinition;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRAdverseEvent;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRAllergyIntolerance;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRAppointment;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRAppointmentResponse;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRAuditEvent;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRBasic;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRBiologicallyDerivedProduct;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRBodyStructure;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRCapabilityStatement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRCarePlan;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRCareTeam;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRCatalogEntry;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRChargeItem;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRChargeItemDefinition;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRClaim;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRClaimResponse;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRClinicalImpression;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRCodeSystem;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRCommunication;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRCommunicationRequest;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRCompartmentDefinition;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRComposition;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRConceptMap;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRCondition;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRConsent;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRContract;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRCoverage;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRCoverageEligibilityRequest;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRCoverageEligibilityResponse;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRDetectedIssue;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRDevice;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRDeviceDefinition;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRDeviceMetric;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRDeviceRequest;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRDeviceUseStatement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRDiagnosticReport;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRDocumentManifest;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRDocumentReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIREffectEvidenceSynthesis;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIREncounter;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIREndpoint;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIREnrollmentRequest;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIREnrollmentResponse;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIREpisodeOfCare;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIREventDefinition;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIREvidence;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIREvidenceVariable;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRExampleScenario;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRExplanationOfBenefit;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRFamilyMemberHistory;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRFlag;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRGoal;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRGraphDefinition;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRGroup;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRGuidanceResponse;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRHealthcareService;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRImagingStudy;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRImmunization;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRImmunizationEvaluation;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRImmunizationRecommendation;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRImplementationGuide;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRInsurancePlan;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRInvoice;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRLibrary;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRLinkage;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRLocation;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMeasure;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMeasureReport;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedia;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedication;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicationAdministration;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicationDispense;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicationKnowledge;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicationRequest;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicationStatement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicinalProduct;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicinalProductAuthorization;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicinalProductContraindication;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicinalProductIndication;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicinalProductIngredient;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicinalProductInteraction;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicinalProductManufactured;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicinalProductPackaged;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicinalProductPharmaceutical;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicinalProductUndesirableEffect;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMessageDefinition;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMessageHeader;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMolecularSequence;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRNamingSystem;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRNutritionOrder;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRObservation;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRObservationDefinition;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIROperationDefinition;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIROperationOutcome;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIROrganization;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIROrganizationAffiliation;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRPatient;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRPaymentNotice;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRPaymentReconciliation;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRPerson;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRPlanDefinition;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRPractitioner;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRPractitionerRole;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRProcedure;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRProvenance;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRQuestionnaire;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRQuestionnaireResponse;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRRelatedPerson;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRRequestGroup;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRResearchDefinition;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRResearchElementDefinition;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRResearchStudy;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRResearchSubject;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRRiskAssessment;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRRiskEvidenceSynthesis;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSchedule;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSearchParameter;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRServiceRequest;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSlot;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSpecimen;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSpecimenDefinition;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRStructureDefinition;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRStructureMap;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSubscription;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSubstance;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSubstanceNucleicAcid;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSubstancePolymer;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSubstanceProtein;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSubstanceReferenceInformation;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSubstanceSourceMaterial;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSubstanceSpecification;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSupplyDelivery;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSupplyRequest;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRTask;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRTerminologyCapabilities;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRTestReport;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRTestScript;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRValueSet;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRVerificationResult;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRVisionPrescription;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRParameters;

class VersionClient
{
    private const _STATUS_OK = 200;
    protected Version $_version;

    /**
     * VersionClient Constructor
     *
     * @param \OpenEMR\FHIR\Client\ClientInterface $_client
     * @param \OpenEMR\FHIR\Versions\R4\Version $version
     */
    public function __construct(protected ClientInterface $_client, Version $version)
    {
        $this->_version = $version;
    }

    /**
     * Queries for one or more resources of a given type, returning the raw response fromm the server.
     *
     * @see https://www.hl7.org/fhir/http.html#read
     *
     * @param \OpenEMR\FHIR\Versions\R4\VersionResourceTypeEnum $resourceType
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|int $count
     * @param null|string|\OpenEMR\FHIR\Client\SortDirectionEnum $sort May be a string value if your server supports non-standard sorting methods
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @param null|array $queryParams
     * @param null|bool $parseResponseHeaders
     * @return \OpenEMR\FHIR\Client\Response
     */
    public function read(VersionResourceTypeEnum $resourceType,
                         null|string|FHIRId|FHIRIdPrimitive $resourceID = null,
                         null|int $count = null,
                         null|string|SortDirectionEnum $sort = null,
                         null|SerializeFormatEnum $format = null,
                         null|array $queryParams = null,
                         null|bool $parseResponseHeaders = null): Response
    {

        $path = "/{$resourceType->value}";
        if (null !== $resourceID) {
            $resourceID = trim((string)$resourceID);
            if ('' === $resourceID) {
                throw new \InvalidArgumentException('Resource ID must be null or valued.');
            }
            $path .= "/{$resourceID}";
        }
        $req = new Request(
            method: HTTPMethodEnum::GET,
            path: $path,
            count: $count,
            format: $format,
            sort: $sort,
            acceptVersion: $this->_version->getFHIRVersion(),
            queryParams: $queryParams,
            parseResponseHeaders: $parseResponseHeaders,
        );
        return $this->_client->exec($req);
    }

    /**
     * Create a resource.
     *
     * @see https://www.hl7.org/fhir/http.html#create
     *
     * @param \OpenEMR\FHIR\Versions\R4\VersionResourceTypeInterface $resource The resource to update, must have a defined ID.
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @param null|array $queryParams Any additional query params to send as part of this request
     * @param null|bool $parseResponseHeaders
     * @return \OpenEMR\FHIR\Client\Response
     */
    public function create(VersionResourceTypeInterface $resource,
                           null|SerializeFormatEnum $format = null,
                           null|array $queryParams = null,
                           null|bool $parseResponseHeaders = null): Response
    {
        $req = new Request(
            method: HTTPMethodEnum::POST,
            path: "/{$resource->_getFHIRTypeName()}",
            format: $format,
            acceptVersion: $resource->_getFHIRVersion(),
            resource: $resource,
            resourceSerializeConfig: $this->_version->getConfig()->getSerializeConfig(),
            queryParams: $queryParams,
            parseResponseHeaders: $parseResponseHeaders,
        );
        return $this->_client->exec($req);
    }

    /**
     * Update or create a specific resource.
     *
     * @see https://www.hl7.org/fhir/http.html#update
     *
     * @param \OpenEMR\FHIR\Versions\R4\VersionResourceTypeInterface $resource The resource to update, must have a defined ID.
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @param null|array $queryParams Any additional query params to send as part of this request
     * @param null|bool $parseResponseHeaders
     * @return \OpenEMR\FHIR\Client\Response
     */
    public function update(VersionResourceTypeInterface $resource,
                           null|SerializeFormatEnum $format = null,
                           null|array $queryParams = null,
                           null|bool $parseResponseHeaders = null): Response
    {
        $req = new Request(
            method: HTTPMethodEnum::PUT,
            path: "/{$resource->_getFHIRTypeName()}/{$this->_mustGetResourceID($resource)}",
            format: $format,
            acceptVersion: $resource->_getFHIRVersion(),
            resource: $resource,
            resourceSerializeConfig: $this->_version->getConfig()->getSerializeConfig(),
            queryParams: $queryParams,
            parseResponseHeaders: $parseResponseHeaders,
        );
        return $this->_client->exec($req);
    }

    /**
     * Perform a partial update on a resource.
     *
     * @see https://www.hl7.org/fhir/http.html#patch
     *
     * @param \OpenEMR\FHIR\Versions\R4\VersionResourceTypeInterface $resource The resource to update, must have a defined ID.
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @param null|array $queryParams Any additional query params to send as part of this request
     * @param null|bool $parseResponseHeaders
     * @return \OpenEMR\FHIR\Client\Response
     */
    public function patch(VersionResourceTypeInterface $resource,
                           null|SerializeFormatEnum $format = null,
                           null|array $queryParams = null,
                           null|bool $parseResponseHeaders = null): Response
    {
        $req = new Request(
            method: HTTPMethodEnum::PATCH,
            path: "/{$resource->_getFHIRTypeName()}/{$this->_mustGetResourceID($resource)}",
            format: $format,
            acceptVersion: $resource->_getFHIRVersion(),
            resource: $resource,
            resourceSerializeConfig: $this->_version->getConfig()->getSerializeConfig(),
            queryParams: $queryParams,
            parseResponseHeaders: $parseResponseHeaders,
        );
        return $this->_client->exec($req);
    }

    /**
     * Delete a resource by ID.
     *
     * @see https://www.hl7.org/fhir/http.html#delete
     *
     * @param \OpenEMR\FHIR\Versions\R4\VersionResourceTypeEnum $resourceType
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID ID of resource to delete.
     * @return \OpenEMR\FHIR\Client\Response
     */
    public function delete(VersionResourceTypeEnum $resourceType,
                           string|FHIRId|FHIRIdPrimitive $resourceID): Response
    {
        $req = new Request(
            method: HTTPMethodEnum::DELETE,
            path: "{$resourceType->value}/{$resourceID}",
        );
        return $this->_client->exec($req);
    }

    /**
     * Delete a specific resource.
     *
     * @see https://www.hl7.org/fhir/http.html#delete
     *
     * @param \OpenEMR\FHIR\Versions\R4\VersionResourceTypeInterface $resource Specific resource to delete.
     * @return \OpenEMR\FHIR\Client\Response
     */
    public function deleteResource(VersionResourceTypeInterface $resource): Response
    {
        $req = new Request(
            method: HTTPMethodEnum::DELETE,
            path: "/{$resource->_getFHIRTypeName()}/{$this->_mustGetResourceID($resource)}",
        );
        return $this->_client->exec($req);
    }

    /**
     * Read one Account resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRAccount
     * @throws \Exception
     */
    public function readOneAccount(string|FHIRId|FHIRIdPrimitive $resourceID,
                                   null|SerializeFormatEnum $format = null): FHIRAccount
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::ACCOUNT,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRAccount::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRAccount::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one ActivityDefinition resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRActivityDefinition
     * @throws \Exception
     */
    public function readOneActivityDefinition(string|FHIRId|FHIRIdPrimitive $resourceID,
                                              null|SerializeFormatEnum $format = null): FHIRActivityDefinition
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::ACTIVITY_DEFINITION,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRActivityDefinition::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRActivityDefinition::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one AdverseEvent resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRAdverseEvent
     * @throws \Exception
     */
    public function readOneAdverseEvent(string|FHIRId|FHIRIdPrimitive $resourceID,
                                        null|SerializeFormatEnum $format = null): FHIRAdverseEvent
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::ADVERSE_EVENT,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRAdverseEvent::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRAdverseEvent::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one AllergyIntolerance resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRAllergyIntolerance
     * @throws \Exception
     */
    public function readOneAllergyIntolerance(string|FHIRId|FHIRIdPrimitive $resourceID,
                                              null|SerializeFormatEnum $format = null): FHIRAllergyIntolerance
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::ALLERGY_INTOLERANCE,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRAllergyIntolerance::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRAllergyIntolerance::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one Appointment resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRAppointment
     * @throws \Exception
     */
    public function readOneAppointment(string|FHIRId|FHIRIdPrimitive $resourceID,
                                       null|SerializeFormatEnum $format = null): FHIRAppointment
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::APPOINTMENT,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRAppointment::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRAppointment::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one AppointmentResponse resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRAppointmentResponse
     * @throws \Exception
     */
    public function readOneAppointmentResponse(string|FHIRId|FHIRIdPrimitive $resourceID,
                                               null|SerializeFormatEnum $format = null): FHIRAppointmentResponse
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::APPOINTMENT_RESPONSE,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRAppointmentResponse::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRAppointmentResponse::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one AuditEvent resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRAuditEvent
     * @throws \Exception
     */
    public function readOneAuditEvent(string|FHIRId|FHIRIdPrimitive $resourceID,
                                      null|SerializeFormatEnum $format = null): FHIRAuditEvent
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::AUDIT_EVENT,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRAuditEvent::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRAuditEvent::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one Basic resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRBasic
     * @throws \Exception
     */
    public function readOneBasic(string|FHIRId|FHIRIdPrimitive $resourceID,
                                 null|SerializeFormatEnum $format = null): FHIRBasic
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::BASIC,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRBasic::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRBasic::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one Binary resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRBinary
     * @throws \Exception
     */
    public function readOneBinary(string|FHIRId|FHIRIdPrimitive $resourceID,
                                  null|SerializeFormatEnum $format = null): FHIRBinary
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::BINARY,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRBinary::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRBinary::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one BiologicallyDerivedProduct resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRBiologicallyDerivedProduct
     * @throws \Exception
     */
    public function readOneBiologicallyDerivedProduct(string|FHIRId|FHIRIdPrimitive $resourceID,
                                                      null|SerializeFormatEnum $format = null): FHIRBiologicallyDerivedProduct
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::BIOLOGICALLY_DERIVED_PRODUCT,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRBiologicallyDerivedProduct::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRBiologicallyDerivedProduct::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one BodyStructure resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRBodyStructure
     * @throws \Exception
     */
    public function readOneBodyStructure(string|FHIRId|FHIRIdPrimitive $resourceID,
                                         null|SerializeFormatEnum $format = null): FHIRBodyStructure
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::BODY_STRUCTURE,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRBodyStructure::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRBodyStructure::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one CapabilityStatement resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRCapabilityStatement
     * @throws \Exception
     */
    public function readOneCapabilityStatement(string|FHIRId|FHIRIdPrimitive $resourceID,
                                               null|SerializeFormatEnum $format = null): FHIRCapabilityStatement
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::CAPABILITY_STATEMENT,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRCapabilityStatement::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRCapabilityStatement::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one CarePlan resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRCarePlan
     * @throws \Exception
     */
    public function readOneCarePlan(string|FHIRId|FHIRIdPrimitive $resourceID,
                                    null|SerializeFormatEnum $format = null): FHIRCarePlan
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::CARE_PLAN,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRCarePlan::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRCarePlan::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one CareTeam resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRCareTeam
     * @throws \Exception
     */
    public function readOneCareTeam(string|FHIRId|FHIRIdPrimitive $resourceID,
                                    null|SerializeFormatEnum $format = null): FHIRCareTeam
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::CARE_TEAM,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRCareTeam::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRCareTeam::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one CatalogEntry resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRCatalogEntry
     * @throws \Exception
     */
    public function readOneCatalogEntry(string|FHIRId|FHIRIdPrimitive $resourceID,
                                        null|SerializeFormatEnum $format = null): FHIRCatalogEntry
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::CATALOG_ENTRY,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRCatalogEntry::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRCatalogEntry::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one ChargeItem resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRChargeItem
     * @throws \Exception
     */
    public function readOneChargeItem(string|FHIRId|FHIRIdPrimitive $resourceID,
                                      null|SerializeFormatEnum $format = null): FHIRChargeItem
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::CHARGE_ITEM,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRChargeItem::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRChargeItem::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one ChargeItemDefinition resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRChargeItemDefinition
     * @throws \Exception
     */
    public function readOneChargeItemDefinition(string|FHIRId|FHIRIdPrimitive $resourceID,
                                                null|SerializeFormatEnum $format = null): FHIRChargeItemDefinition
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::CHARGE_ITEM_DEFINITION,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRChargeItemDefinition::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRChargeItemDefinition::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one Claim resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRClaim
     * @throws \Exception
     */
    public function readOneClaim(string|FHIRId|FHIRIdPrimitive $resourceID,
                                 null|SerializeFormatEnum $format = null): FHIRClaim
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::CLAIM,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRClaim::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRClaim::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one ClaimResponse resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRClaimResponse
     * @throws \Exception
     */
    public function readOneClaimResponse(string|FHIRId|FHIRIdPrimitive $resourceID,
                                         null|SerializeFormatEnum $format = null): FHIRClaimResponse
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::CLAIM_RESPONSE,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRClaimResponse::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRClaimResponse::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one ClinicalImpression resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRClinicalImpression
     * @throws \Exception
     */
    public function readOneClinicalImpression(string|FHIRId|FHIRIdPrimitive $resourceID,
                                              null|SerializeFormatEnum $format = null): FHIRClinicalImpression
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::CLINICAL_IMPRESSION,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRClinicalImpression::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRClinicalImpression::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one CodeSystem resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRCodeSystem
     * @throws \Exception
     */
    public function readOneCodeSystem(string|FHIRId|FHIRIdPrimitive $resourceID,
                                      null|SerializeFormatEnum $format = null): FHIRCodeSystem
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::CODE_SYSTEM,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRCodeSystem::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRCodeSystem::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one Communication resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRCommunication
     * @throws \Exception
     */
    public function readOneCommunication(string|FHIRId|FHIRIdPrimitive $resourceID,
                                         null|SerializeFormatEnum $format = null): FHIRCommunication
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::COMMUNICATION,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRCommunication::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRCommunication::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one CommunicationRequest resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRCommunicationRequest
     * @throws \Exception
     */
    public function readOneCommunicationRequest(string|FHIRId|FHIRIdPrimitive $resourceID,
                                                null|SerializeFormatEnum $format = null): FHIRCommunicationRequest
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::COMMUNICATION_REQUEST,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRCommunicationRequest::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRCommunicationRequest::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one CompartmentDefinition resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRCompartmentDefinition
     * @throws \Exception
     */
    public function readOneCompartmentDefinition(string|FHIRId|FHIRIdPrimitive $resourceID,
                                                 null|SerializeFormatEnum $format = null): FHIRCompartmentDefinition
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::COMPARTMENT_DEFINITION,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRCompartmentDefinition::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRCompartmentDefinition::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one Composition resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRComposition
     * @throws \Exception
     */
    public function readOneComposition(string|FHIRId|FHIRIdPrimitive $resourceID,
                                       null|SerializeFormatEnum $format = null): FHIRComposition
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::COMPOSITION,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRComposition::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRComposition::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one ConceptMap resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRConceptMap
     * @throws \Exception
     */
    public function readOneConceptMap(string|FHIRId|FHIRIdPrimitive $resourceID,
                                      null|SerializeFormatEnum $format = null): FHIRConceptMap
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::CONCEPT_MAP,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRConceptMap::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRConceptMap::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one Condition resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRCondition
     * @throws \Exception
     */
    public function readOneCondition(string|FHIRId|FHIRIdPrimitive $resourceID,
                                     null|SerializeFormatEnum $format = null): FHIRCondition
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::CONDITION,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRCondition::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRCondition::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one Consent resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRConsent
     * @throws \Exception
     */
    public function readOneConsent(string|FHIRId|FHIRIdPrimitive $resourceID,
                                   null|SerializeFormatEnum $format = null): FHIRConsent
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::CONSENT,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRConsent::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRConsent::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one Contract resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRContract
     * @throws \Exception
     */
    public function readOneContract(string|FHIRId|FHIRIdPrimitive $resourceID,
                                    null|SerializeFormatEnum $format = null): FHIRContract
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::CONTRACT,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRContract::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRContract::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one Coverage resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRCoverage
     * @throws \Exception
     */
    public function readOneCoverage(string|FHIRId|FHIRIdPrimitive $resourceID,
                                    null|SerializeFormatEnum $format = null): FHIRCoverage
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::COVERAGE,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRCoverage::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRCoverage::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one CoverageEligibilityRequest resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRCoverageEligibilityRequest
     * @throws \Exception
     */
    public function readOneCoverageEligibilityRequest(string|FHIRId|FHIRIdPrimitive $resourceID,
                                                      null|SerializeFormatEnum $format = null): FHIRCoverageEligibilityRequest
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::COVERAGE_ELIGIBILITY_REQUEST,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRCoverageEligibilityRequest::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRCoverageEligibilityRequest::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one CoverageEligibilityResponse resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRCoverageEligibilityResponse
     * @throws \Exception
     */
    public function readOneCoverageEligibilityResponse(string|FHIRId|FHIRIdPrimitive $resourceID,
                                                       null|SerializeFormatEnum $format = null): FHIRCoverageEligibilityResponse
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::COVERAGE_ELIGIBILITY_RESPONSE,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRCoverageEligibilityResponse::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRCoverageEligibilityResponse::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one DetectedIssue resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRDetectedIssue
     * @throws \Exception
     */
    public function readOneDetectedIssue(string|FHIRId|FHIRIdPrimitive $resourceID,
                                         null|SerializeFormatEnum $format = null): FHIRDetectedIssue
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::DETECTED_ISSUE,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRDetectedIssue::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRDetectedIssue::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one Device resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRDevice
     * @throws \Exception
     */
    public function readOneDevice(string|FHIRId|FHIRIdPrimitive $resourceID,
                                  null|SerializeFormatEnum $format = null): FHIRDevice
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::DEVICE,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRDevice::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRDevice::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one DeviceDefinition resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRDeviceDefinition
     * @throws \Exception
     */
    public function readOneDeviceDefinition(string|FHIRId|FHIRIdPrimitive $resourceID,
                                            null|SerializeFormatEnum $format = null): FHIRDeviceDefinition
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::DEVICE_DEFINITION,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRDeviceDefinition::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRDeviceDefinition::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one DeviceMetric resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRDeviceMetric
     * @throws \Exception
     */
    public function readOneDeviceMetric(string|FHIRId|FHIRIdPrimitive $resourceID,
                                        null|SerializeFormatEnum $format = null): FHIRDeviceMetric
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::DEVICE_METRIC,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRDeviceMetric::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRDeviceMetric::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one DeviceRequest resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRDeviceRequest
     * @throws \Exception
     */
    public function readOneDeviceRequest(string|FHIRId|FHIRIdPrimitive $resourceID,
                                         null|SerializeFormatEnum $format = null): FHIRDeviceRequest
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::DEVICE_REQUEST,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRDeviceRequest::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRDeviceRequest::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one DeviceUseStatement resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRDeviceUseStatement
     * @throws \Exception
     */
    public function readOneDeviceUseStatement(string|FHIRId|FHIRIdPrimitive $resourceID,
                                              null|SerializeFormatEnum $format = null): FHIRDeviceUseStatement
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::DEVICE_USE_STATEMENT,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRDeviceUseStatement::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRDeviceUseStatement::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one DiagnosticReport resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRDiagnosticReport
     * @throws \Exception
     */
    public function readOneDiagnosticReport(string|FHIRId|FHIRIdPrimitive $resourceID,
                                            null|SerializeFormatEnum $format = null): FHIRDiagnosticReport
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::DIAGNOSTIC_REPORT,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRDiagnosticReport::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRDiagnosticReport::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one DocumentManifest resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRDocumentManifest
     * @throws \Exception
     */
    public function readOneDocumentManifest(string|FHIRId|FHIRIdPrimitive $resourceID,
                                            null|SerializeFormatEnum $format = null): FHIRDocumentManifest
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::DOCUMENT_MANIFEST,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRDocumentManifest::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRDocumentManifest::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one DocumentReference resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRDocumentReference
     * @throws \Exception
     */
    public function readOneDocumentReference(string|FHIRId|FHIRIdPrimitive $resourceID,
                                             null|SerializeFormatEnum $format = null): FHIRDocumentReference
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::DOCUMENT_REFERENCE,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRDocumentReference::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRDocumentReference::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one EffectEvidenceSynthesis resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIREffectEvidenceSynthesis
     * @throws \Exception
     */
    public function readOneEffectEvidenceSynthesis(string|FHIRId|FHIRIdPrimitive $resourceID,
                                                   null|SerializeFormatEnum $format = null): FHIREffectEvidenceSynthesis
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::EFFECT_EVIDENCE_SYNTHESIS,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIREffectEvidenceSynthesis::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIREffectEvidenceSynthesis::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one Encounter resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIREncounter
     * @throws \Exception
     */
    public function readOneEncounter(string|FHIRId|FHIRIdPrimitive $resourceID,
                                     null|SerializeFormatEnum $format = null): FHIREncounter
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::ENCOUNTER,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIREncounter::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIREncounter::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one Endpoint resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIREndpoint
     * @throws \Exception
     */
    public function readOneEndpoint(string|FHIRId|FHIRIdPrimitive $resourceID,
                                    null|SerializeFormatEnum $format = null): FHIREndpoint
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::ENDPOINT,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIREndpoint::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIREndpoint::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one EnrollmentRequest resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIREnrollmentRequest
     * @throws \Exception
     */
    public function readOneEnrollmentRequest(string|FHIRId|FHIRIdPrimitive $resourceID,
                                             null|SerializeFormatEnum $format = null): FHIREnrollmentRequest
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::ENROLLMENT_REQUEST,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIREnrollmentRequest::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIREnrollmentRequest::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one EnrollmentResponse resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIREnrollmentResponse
     * @throws \Exception
     */
    public function readOneEnrollmentResponse(string|FHIRId|FHIRIdPrimitive $resourceID,
                                              null|SerializeFormatEnum $format = null): FHIREnrollmentResponse
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::ENROLLMENT_RESPONSE,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIREnrollmentResponse::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIREnrollmentResponse::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one EpisodeOfCare resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIREpisodeOfCare
     * @throws \Exception
     */
    public function readOneEpisodeOfCare(string|FHIRId|FHIRIdPrimitive $resourceID,
                                         null|SerializeFormatEnum $format = null): FHIREpisodeOfCare
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::EPISODE_OF_CARE,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIREpisodeOfCare::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIREpisodeOfCare::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one EventDefinition resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIREventDefinition
     * @throws \Exception
     */
    public function readOneEventDefinition(string|FHIRId|FHIRIdPrimitive $resourceID,
                                           null|SerializeFormatEnum $format = null): FHIREventDefinition
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::EVENT_DEFINITION,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIREventDefinition::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIREventDefinition::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one Evidence resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIREvidence
     * @throws \Exception
     */
    public function readOneEvidence(string|FHIRId|FHIRIdPrimitive $resourceID,
                                    null|SerializeFormatEnum $format = null): FHIREvidence
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::EVIDENCE,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIREvidence::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIREvidence::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one EvidenceVariable resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIREvidenceVariable
     * @throws \Exception
     */
    public function readOneEvidenceVariable(string|FHIRId|FHIRIdPrimitive $resourceID,
                                            null|SerializeFormatEnum $format = null): FHIREvidenceVariable
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::EVIDENCE_VARIABLE,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIREvidenceVariable::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIREvidenceVariable::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one ExampleScenario resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRExampleScenario
     * @throws \Exception
     */
    public function readOneExampleScenario(string|FHIRId|FHIRIdPrimitive $resourceID,
                                           null|SerializeFormatEnum $format = null): FHIRExampleScenario
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::EXAMPLE_SCENARIO,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRExampleScenario::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRExampleScenario::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one ExplanationOfBenefit resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRExplanationOfBenefit
     * @throws \Exception
     */
    public function readOneExplanationOfBenefit(string|FHIRId|FHIRIdPrimitive $resourceID,
                                                null|SerializeFormatEnum $format = null): FHIRExplanationOfBenefit
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::EXPLANATION_OF_BENEFIT,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRExplanationOfBenefit::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRExplanationOfBenefit::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one FamilyMemberHistory resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRFamilyMemberHistory
     * @throws \Exception
     */
    public function readOneFamilyMemberHistory(string|FHIRId|FHIRIdPrimitive $resourceID,
                                               null|SerializeFormatEnum $format = null): FHIRFamilyMemberHistory
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::FAMILY_MEMBER_HISTORY,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRFamilyMemberHistory::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRFamilyMemberHistory::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one Flag resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRFlag
     * @throws \Exception
     */
    public function readOneFlag(string|FHIRId|FHIRIdPrimitive $resourceID,
                                null|SerializeFormatEnum $format = null): FHIRFlag
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::FLAG,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRFlag::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRFlag::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one Goal resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRGoal
     * @throws \Exception
     */
    public function readOneGoal(string|FHIRId|FHIRIdPrimitive $resourceID,
                                null|SerializeFormatEnum $format = null): FHIRGoal
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::GOAL,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRGoal::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRGoal::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one GraphDefinition resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRGraphDefinition
     * @throws \Exception
     */
    public function readOneGraphDefinition(string|FHIRId|FHIRIdPrimitive $resourceID,
                                           null|SerializeFormatEnum $format = null): FHIRGraphDefinition
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::GRAPH_DEFINITION,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRGraphDefinition::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRGraphDefinition::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one Group resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRGroup
     * @throws \Exception
     */
    public function readOneGroup(string|FHIRId|FHIRIdPrimitive $resourceID,
                                 null|SerializeFormatEnum $format = null): FHIRGroup
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::GROUP,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRGroup::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRGroup::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one GuidanceResponse resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRGuidanceResponse
     * @throws \Exception
     */
    public function readOneGuidanceResponse(string|FHIRId|FHIRIdPrimitive $resourceID,
                                            null|SerializeFormatEnum $format = null): FHIRGuidanceResponse
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::GUIDANCE_RESPONSE,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRGuidanceResponse::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRGuidanceResponse::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one HealthcareService resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRHealthcareService
     * @throws \Exception
     */
    public function readOneHealthcareService(string|FHIRId|FHIRIdPrimitive $resourceID,
                                             null|SerializeFormatEnum $format = null): FHIRHealthcareService
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::HEALTHCARE_SERVICE,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRHealthcareService::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRHealthcareService::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one ImagingStudy resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRImagingStudy
     * @throws \Exception
     */
    public function readOneImagingStudy(string|FHIRId|FHIRIdPrimitive $resourceID,
                                        null|SerializeFormatEnum $format = null): FHIRImagingStudy
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::IMAGING_STUDY,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRImagingStudy::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRImagingStudy::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one Immunization resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRImmunization
     * @throws \Exception
     */
    public function readOneImmunization(string|FHIRId|FHIRIdPrimitive $resourceID,
                                        null|SerializeFormatEnum $format = null): FHIRImmunization
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::IMMUNIZATION,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRImmunization::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRImmunization::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one ImmunizationEvaluation resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRImmunizationEvaluation
     * @throws \Exception
     */
    public function readOneImmunizationEvaluation(string|FHIRId|FHIRIdPrimitive $resourceID,
                                                  null|SerializeFormatEnum $format = null): FHIRImmunizationEvaluation
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::IMMUNIZATION_EVALUATION,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRImmunizationEvaluation::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRImmunizationEvaluation::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one ImmunizationRecommendation resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRImmunizationRecommendation
     * @throws \Exception
     */
    public function readOneImmunizationRecommendation(string|FHIRId|FHIRIdPrimitive $resourceID,
                                                      null|SerializeFormatEnum $format = null): FHIRImmunizationRecommendation
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::IMMUNIZATION_RECOMMENDATION,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRImmunizationRecommendation::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRImmunizationRecommendation::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one ImplementationGuide resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRImplementationGuide
     * @throws \Exception
     */
    public function readOneImplementationGuide(string|FHIRId|FHIRIdPrimitive $resourceID,
                                               null|SerializeFormatEnum $format = null): FHIRImplementationGuide
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::IMPLEMENTATION_GUIDE,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRImplementationGuide::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRImplementationGuide::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one InsurancePlan resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRInsurancePlan
     * @throws \Exception
     */
    public function readOneInsurancePlan(string|FHIRId|FHIRIdPrimitive $resourceID,
                                         null|SerializeFormatEnum $format = null): FHIRInsurancePlan
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::INSURANCE_PLAN,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRInsurancePlan::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRInsurancePlan::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one Invoice resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRInvoice
     * @throws \Exception
     */
    public function readOneInvoice(string|FHIRId|FHIRIdPrimitive $resourceID,
                                   null|SerializeFormatEnum $format = null): FHIRInvoice
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::INVOICE,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRInvoice::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRInvoice::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one Library resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRLibrary
     * @throws \Exception
     */
    public function readOneLibrary(string|FHIRId|FHIRIdPrimitive $resourceID,
                                   null|SerializeFormatEnum $format = null): FHIRLibrary
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::LIBRARY,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRLibrary::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRLibrary::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one Linkage resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRLinkage
     * @throws \Exception
     */
    public function readOneLinkage(string|FHIRId|FHIRIdPrimitive $resourceID,
                                   null|SerializeFormatEnum $format = null): FHIRLinkage
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::LINKAGE,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRLinkage::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRLinkage::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one List resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRList
     * @throws \Exception
     */
    public function readOneList(string|FHIRId|FHIRIdPrimitive $resourceID,
                                null|SerializeFormatEnum $format = null): FHIRList
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::LIST,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRList::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRList::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one Location resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRLocation
     * @throws \Exception
     */
    public function readOneLocation(string|FHIRId|FHIRIdPrimitive $resourceID,
                                    null|SerializeFormatEnum $format = null): FHIRLocation
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::LOCATION,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRLocation::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRLocation::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one Measure resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMeasure
     * @throws \Exception
     */
    public function readOneMeasure(string|FHIRId|FHIRIdPrimitive $resourceID,
                                   null|SerializeFormatEnum $format = null): FHIRMeasure
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::MEASURE,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRMeasure::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRMeasure::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one MeasureReport resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMeasureReport
     * @throws \Exception
     */
    public function readOneMeasureReport(string|FHIRId|FHIRIdPrimitive $resourceID,
                                         null|SerializeFormatEnum $format = null): FHIRMeasureReport
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::MEASURE_REPORT,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRMeasureReport::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRMeasureReport::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one Media resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedia
     * @throws \Exception
     */
    public function readOneMedia(string|FHIRId|FHIRIdPrimitive $resourceID,
                                 null|SerializeFormatEnum $format = null): FHIRMedia
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::MEDIA,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRMedia::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRMedia::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one Medication resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedication
     * @throws \Exception
     */
    public function readOneMedication(string|FHIRId|FHIRIdPrimitive $resourceID,
                                      null|SerializeFormatEnum $format = null): FHIRMedication
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::MEDICATION,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRMedication::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRMedication::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one MedicationAdministration resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicationAdministration
     * @throws \Exception
     */
    public function readOneMedicationAdministration(string|FHIRId|FHIRIdPrimitive $resourceID,
                                                    null|SerializeFormatEnum $format = null): FHIRMedicationAdministration
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::MEDICATION_ADMINISTRATION,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRMedicationAdministration::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRMedicationAdministration::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one MedicationDispense resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicationDispense
     * @throws \Exception
     */
    public function readOneMedicationDispense(string|FHIRId|FHIRIdPrimitive $resourceID,
                                              null|SerializeFormatEnum $format = null): FHIRMedicationDispense
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::MEDICATION_DISPENSE,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRMedicationDispense::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRMedicationDispense::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one MedicationKnowledge resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicationKnowledge
     * @throws \Exception
     */
    public function readOneMedicationKnowledge(string|FHIRId|FHIRIdPrimitive $resourceID,
                                               null|SerializeFormatEnum $format = null): FHIRMedicationKnowledge
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::MEDICATION_KNOWLEDGE,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRMedicationKnowledge::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRMedicationKnowledge::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one MedicationRequest resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicationRequest
     * @throws \Exception
     */
    public function readOneMedicationRequest(string|FHIRId|FHIRIdPrimitive $resourceID,
                                             null|SerializeFormatEnum $format = null): FHIRMedicationRequest
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::MEDICATION_REQUEST,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRMedicationRequest::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRMedicationRequest::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one MedicationStatement resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicationStatement
     * @throws \Exception
     */
    public function readOneMedicationStatement(string|FHIRId|FHIRIdPrimitive $resourceID,
                                               null|SerializeFormatEnum $format = null): FHIRMedicationStatement
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::MEDICATION_STATEMENT,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRMedicationStatement::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRMedicationStatement::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one MedicinalProduct resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicinalProduct
     * @throws \Exception
     */
    public function readOneMedicinalProduct(string|FHIRId|FHIRIdPrimitive $resourceID,
                                            null|SerializeFormatEnum $format = null): FHIRMedicinalProduct
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::MEDICINAL_PRODUCT,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRMedicinalProduct::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRMedicinalProduct::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one MedicinalProductAuthorization resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicinalProductAuthorization
     * @throws \Exception
     */
    public function readOneMedicinalProductAuthorization(string|FHIRId|FHIRIdPrimitive $resourceID,
                                                         null|SerializeFormatEnum $format = null): FHIRMedicinalProductAuthorization
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::MEDICINAL_PRODUCT_AUTHORIZATION,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRMedicinalProductAuthorization::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRMedicinalProductAuthorization::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one MedicinalProductContraindication resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicinalProductContraindication
     * @throws \Exception
     */
    public function readOneMedicinalProductContraindication(string|FHIRId|FHIRIdPrimitive $resourceID,
                                                            null|SerializeFormatEnum $format = null): FHIRMedicinalProductContraindication
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::MEDICINAL_PRODUCT_CONTRAINDICATION,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRMedicinalProductContraindication::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRMedicinalProductContraindication::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one MedicinalProductIndication resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicinalProductIndication
     * @throws \Exception
     */
    public function readOneMedicinalProductIndication(string|FHIRId|FHIRIdPrimitive $resourceID,
                                                      null|SerializeFormatEnum $format = null): FHIRMedicinalProductIndication
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::MEDICINAL_PRODUCT_INDICATION,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRMedicinalProductIndication::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRMedicinalProductIndication::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one MedicinalProductIngredient resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicinalProductIngredient
     * @throws \Exception
     */
    public function readOneMedicinalProductIngredient(string|FHIRId|FHIRIdPrimitive $resourceID,
                                                      null|SerializeFormatEnum $format = null): FHIRMedicinalProductIngredient
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::MEDICINAL_PRODUCT_INGREDIENT,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRMedicinalProductIngredient::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRMedicinalProductIngredient::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one MedicinalProductInteraction resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicinalProductInteraction
     * @throws \Exception
     */
    public function readOneMedicinalProductInteraction(string|FHIRId|FHIRIdPrimitive $resourceID,
                                                       null|SerializeFormatEnum $format = null): FHIRMedicinalProductInteraction
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::MEDICINAL_PRODUCT_INTERACTION,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRMedicinalProductInteraction::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRMedicinalProductInteraction::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one MedicinalProductManufactured resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicinalProductManufactured
     * @throws \Exception
     */
    public function readOneMedicinalProductManufactured(string|FHIRId|FHIRIdPrimitive $resourceID,
                                                        null|SerializeFormatEnum $format = null): FHIRMedicinalProductManufactured
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::MEDICINAL_PRODUCT_MANUFACTURED,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRMedicinalProductManufactured::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRMedicinalProductManufactured::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one MedicinalProductPackaged resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicinalProductPackaged
     * @throws \Exception
     */
    public function readOneMedicinalProductPackaged(string|FHIRId|FHIRIdPrimitive $resourceID,
                                                    null|SerializeFormatEnum $format = null): FHIRMedicinalProductPackaged
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::MEDICINAL_PRODUCT_PACKAGED,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRMedicinalProductPackaged::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRMedicinalProductPackaged::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one MedicinalProductPharmaceutical resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicinalProductPharmaceutical
     * @throws \Exception
     */
    public function readOneMedicinalProductPharmaceutical(string|FHIRId|FHIRIdPrimitive $resourceID,
                                                          null|SerializeFormatEnum $format = null): FHIRMedicinalProductPharmaceutical
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::MEDICINAL_PRODUCT_PHARMACEUTICAL,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRMedicinalProductPharmaceutical::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRMedicinalProductPharmaceutical::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one MedicinalProductUndesirableEffect resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicinalProductUndesirableEffect
     * @throws \Exception
     */
    public function readOneMedicinalProductUndesirableEffect(string|FHIRId|FHIRIdPrimitive $resourceID,
                                                             null|SerializeFormatEnum $format = null): FHIRMedicinalProductUndesirableEffect
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::MEDICINAL_PRODUCT_UNDESIRABLE_EFFECT,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRMedicinalProductUndesirableEffect::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRMedicinalProductUndesirableEffect::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one MessageDefinition resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMessageDefinition
     * @throws \Exception
     */
    public function readOneMessageDefinition(string|FHIRId|FHIRIdPrimitive $resourceID,
                                             null|SerializeFormatEnum $format = null): FHIRMessageDefinition
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::MESSAGE_DEFINITION,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRMessageDefinition::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRMessageDefinition::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one MessageHeader resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMessageHeader
     * @throws \Exception
     */
    public function readOneMessageHeader(string|FHIRId|FHIRIdPrimitive $resourceID,
                                         null|SerializeFormatEnum $format = null): FHIRMessageHeader
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::MESSAGE_HEADER,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRMessageHeader::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRMessageHeader::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one MolecularSequence resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMolecularSequence
     * @throws \Exception
     */
    public function readOneMolecularSequence(string|FHIRId|FHIRIdPrimitive $resourceID,
                                             null|SerializeFormatEnum $format = null): FHIRMolecularSequence
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::MOLECULAR_SEQUENCE,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRMolecularSequence::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRMolecularSequence::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one NamingSystem resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRNamingSystem
     * @throws \Exception
     */
    public function readOneNamingSystem(string|FHIRId|FHIRIdPrimitive $resourceID,
                                        null|SerializeFormatEnum $format = null): FHIRNamingSystem
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::NAMING_SYSTEM,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRNamingSystem::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRNamingSystem::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one NutritionOrder resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRNutritionOrder
     * @throws \Exception
     */
    public function readOneNutritionOrder(string|FHIRId|FHIRIdPrimitive $resourceID,
                                          null|SerializeFormatEnum $format = null): FHIRNutritionOrder
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::NUTRITION_ORDER,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRNutritionOrder::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRNutritionOrder::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one Observation resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRObservation
     * @throws \Exception
     */
    public function readOneObservation(string|FHIRId|FHIRIdPrimitive $resourceID,
                                       null|SerializeFormatEnum $format = null): FHIRObservation
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::OBSERVATION,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRObservation::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRObservation::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one ObservationDefinition resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRObservationDefinition
     * @throws \Exception
     */
    public function readOneObservationDefinition(string|FHIRId|FHIRIdPrimitive $resourceID,
                                                 null|SerializeFormatEnum $format = null): FHIRObservationDefinition
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::OBSERVATION_DEFINITION,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRObservationDefinition::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRObservationDefinition::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one OperationDefinition resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIROperationDefinition
     * @throws \Exception
     */
    public function readOneOperationDefinition(string|FHIRId|FHIRIdPrimitive $resourceID,
                                               null|SerializeFormatEnum $format = null): FHIROperationDefinition
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::OPERATION_DEFINITION,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIROperationDefinition::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIROperationDefinition::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one OperationOutcome resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIROperationOutcome
     * @throws \Exception
     */
    public function readOneOperationOutcome(string|FHIRId|FHIRIdPrimitive $resourceID,
                                            null|SerializeFormatEnum $format = null): FHIROperationOutcome
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::OPERATION_OUTCOME,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIROperationOutcome::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIROperationOutcome::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one Organization resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIROrganization
     * @throws \Exception
     */
    public function readOneOrganization(string|FHIRId|FHIRIdPrimitive $resourceID,
                                        null|SerializeFormatEnum $format = null): FHIROrganization
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::ORGANIZATION,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIROrganization::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIROrganization::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one OrganizationAffiliation resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIROrganizationAffiliation
     * @throws \Exception
     */
    public function readOneOrganizationAffiliation(string|FHIRId|FHIRIdPrimitive $resourceID,
                                                   null|SerializeFormatEnum $format = null): FHIROrganizationAffiliation
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::ORGANIZATION_AFFILIATION,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIROrganizationAffiliation::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIROrganizationAffiliation::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one Parameters resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRParameters
     * @throws \Exception
     */
    public function readOneParameters(string|FHIRId|FHIRIdPrimitive $resourceID,
                                      null|SerializeFormatEnum $format = null): FHIRParameters
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::PARAMETERS,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRParameters::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRParameters::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one Patient resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRPatient
     * @throws \Exception
     */
    public function readOnePatient(string|FHIRId|FHIRIdPrimitive $resourceID,
                                   null|SerializeFormatEnum $format = null): FHIRPatient
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::PATIENT,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRPatient::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRPatient::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one PaymentNotice resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRPaymentNotice
     * @throws \Exception
     */
    public function readOnePaymentNotice(string|FHIRId|FHIRIdPrimitive $resourceID,
                                         null|SerializeFormatEnum $format = null): FHIRPaymentNotice
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::PAYMENT_NOTICE,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRPaymentNotice::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRPaymentNotice::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one PaymentReconciliation resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRPaymentReconciliation
     * @throws \Exception
     */
    public function readOnePaymentReconciliation(string|FHIRId|FHIRIdPrimitive $resourceID,
                                                 null|SerializeFormatEnum $format = null): FHIRPaymentReconciliation
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::PAYMENT_RECONCILIATION,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRPaymentReconciliation::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRPaymentReconciliation::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one Person resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRPerson
     * @throws \Exception
     */
    public function readOnePerson(string|FHIRId|FHIRIdPrimitive $resourceID,
                                  null|SerializeFormatEnum $format = null): FHIRPerson
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::PERSON,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRPerson::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRPerson::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one PlanDefinition resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRPlanDefinition
     * @throws \Exception
     */
    public function readOnePlanDefinition(string|FHIRId|FHIRIdPrimitive $resourceID,
                                          null|SerializeFormatEnum $format = null): FHIRPlanDefinition
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::PLAN_DEFINITION,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRPlanDefinition::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRPlanDefinition::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one Practitioner resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRPractitioner
     * @throws \Exception
     */
    public function readOnePractitioner(string|FHIRId|FHIRIdPrimitive $resourceID,
                                        null|SerializeFormatEnum $format = null): FHIRPractitioner
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::PRACTITIONER,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRPractitioner::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRPractitioner::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one PractitionerRole resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRPractitionerRole
     * @throws \Exception
     */
    public function readOnePractitionerRole(string|FHIRId|FHIRIdPrimitive $resourceID,
                                            null|SerializeFormatEnum $format = null): FHIRPractitionerRole
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::PRACTITIONER_ROLE,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRPractitionerRole::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRPractitionerRole::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one Procedure resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRProcedure
     * @throws \Exception
     */
    public function readOneProcedure(string|FHIRId|FHIRIdPrimitive $resourceID,
                                     null|SerializeFormatEnum $format = null): FHIRProcedure
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::PROCEDURE,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRProcedure::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRProcedure::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one Provenance resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRProvenance
     * @throws \Exception
     */
    public function readOneProvenance(string|FHIRId|FHIRIdPrimitive $resourceID,
                                      null|SerializeFormatEnum $format = null): FHIRProvenance
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::PROVENANCE,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRProvenance::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRProvenance::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one Questionnaire resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRQuestionnaire
     * @throws \Exception
     */
    public function readOneQuestionnaire(string|FHIRId|FHIRIdPrimitive $resourceID,
                                         null|SerializeFormatEnum $format = null): FHIRQuestionnaire
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::QUESTIONNAIRE,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRQuestionnaire::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRQuestionnaire::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one QuestionnaireResponse resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRQuestionnaireResponse
     * @throws \Exception
     */
    public function readOneQuestionnaireResponse(string|FHIRId|FHIRIdPrimitive $resourceID,
                                                 null|SerializeFormatEnum $format = null): FHIRQuestionnaireResponse
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::QUESTIONNAIRE_RESPONSE,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRQuestionnaireResponse::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRQuestionnaireResponse::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one RelatedPerson resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRRelatedPerson
     * @throws \Exception
     */
    public function readOneRelatedPerson(string|FHIRId|FHIRIdPrimitive $resourceID,
                                         null|SerializeFormatEnum $format = null): FHIRRelatedPerson
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::RELATED_PERSON,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRRelatedPerson::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRRelatedPerson::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one RequestGroup resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRRequestGroup
     * @throws \Exception
     */
    public function readOneRequestGroup(string|FHIRId|FHIRIdPrimitive $resourceID,
                                        null|SerializeFormatEnum $format = null): FHIRRequestGroup
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::REQUEST_GROUP,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRRequestGroup::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRRequestGroup::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one ResearchDefinition resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRResearchDefinition
     * @throws \Exception
     */
    public function readOneResearchDefinition(string|FHIRId|FHIRIdPrimitive $resourceID,
                                              null|SerializeFormatEnum $format = null): FHIRResearchDefinition
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::RESEARCH_DEFINITION,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRResearchDefinition::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRResearchDefinition::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one ResearchElementDefinition resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRResearchElementDefinition
     * @throws \Exception
     */
    public function readOneResearchElementDefinition(string|FHIRId|FHIRIdPrimitive $resourceID,
                                                     null|SerializeFormatEnum $format = null): FHIRResearchElementDefinition
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::RESEARCH_ELEMENT_DEFINITION,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRResearchElementDefinition::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRResearchElementDefinition::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one ResearchStudy resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRResearchStudy
     * @throws \Exception
     */
    public function readOneResearchStudy(string|FHIRId|FHIRIdPrimitive $resourceID,
                                         null|SerializeFormatEnum $format = null): FHIRResearchStudy
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::RESEARCH_STUDY,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRResearchStudy::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRResearchStudy::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one ResearchSubject resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRResearchSubject
     * @throws \Exception
     */
    public function readOneResearchSubject(string|FHIRId|FHIRIdPrimitive $resourceID,
                                           null|SerializeFormatEnum $format = null): FHIRResearchSubject
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::RESEARCH_SUBJECT,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRResearchSubject::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRResearchSubject::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one RiskAssessment resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRRiskAssessment
     * @throws \Exception
     */
    public function readOneRiskAssessment(string|FHIRId|FHIRIdPrimitive $resourceID,
                                          null|SerializeFormatEnum $format = null): FHIRRiskAssessment
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::RISK_ASSESSMENT,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRRiskAssessment::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRRiskAssessment::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one RiskEvidenceSynthesis resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRRiskEvidenceSynthesis
     * @throws \Exception
     */
    public function readOneRiskEvidenceSynthesis(string|FHIRId|FHIRIdPrimitive $resourceID,
                                                 null|SerializeFormatEnum $format = null): FHIRRiskEvidenceSynthesis
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::RISK_EVIDENCE_SYNTHESIS,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRRiskEvidenceSynthesis::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRRiskEvidenceSynthesis::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one Schedule resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSchedule
     * @throws \Exception
     */
    public function readOneSchedule(string|FHIRId|FHIRIdPrimitive $resourceID,
                                    null|SerializeFormatEnum $format = null): FHIRSchedule
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::SCHEDULE,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRSchedule::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRSchedule::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one SearchParameter resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSearchParameter
     * @throws \Exception
     */
    public function readOneSearchParameter(string|FHIRId|FHIRIdPrimitive $resourceID,
                                           null|SerializeFormatEnum $format = null): FHIRSearchParameter
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::SEARCH_PARAMETER,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRSearchParameter::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRSearchParameter::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one ServiceRequest resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRServiceRequest
     * @throws \Exception
     */
    public function readOneServiceRequest(string|FHIRId|FHIRIdPrimitive $resourceID,
                                          null|SerializeFormatEnum $format = null): FHIRServiceRequest
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::SERVICE_REQUEST,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRServiceRequest::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRServiceRequest::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one Slot resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSlot
     * @throws \Exception
     */
    public function readOneSlot(string|FHIRId|FHIRIdPrimitive $resourceID,
                                null|SerializeFormatEnum $format = null): FHIRSlot
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::SLOT,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRSlot::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRSlot::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one Specimen resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSpecimen
     * @throws \Exception
     */
    public function readOneSpecimen(string|FHIRId|FHIRIdPrimitive $resourceID,
                                    null|SerializeFormatEnum $format = null): FHIRSpecimen
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::SPECIMEN,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRSpecimen::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRSpecimen::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one SpecimenDefinition resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSpecimenDefinition
     * @throws \Exception
     */
    public function readOneSpecimenDefinition(string|FHIRId|FHIRIdPrimitive $resourceID,
                                              null|SerializeFormatEnum $format = null): FHIRSpecimenDefinition
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::SPECIMEN_DEFINITION,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRSpecimenDefinition::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRSpecimenDefinition::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one StructureDefinition resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRStructureDefinition
     * @throws \Exception
     */
    public function readOneStructureDefinition(string|FHIRId|FHIRIdPrimitive $resourceID,
                                               null|SerializeFormatEnum $format = null): FHIRStructureDefinition
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::STRUCTURE_DEFINITION,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRStructureDefinition::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRStructureDefinition::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one StructureMap resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRStructureMap
     * @throws \Exception
     */
    public function readOneStructureMap(string|FHIRId|FHIRIdPrimitive $resourceID,
                                        null|SerializeFormatEnum $format = null): FHIRStructureMap
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::STRUCTURE_MAP,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRStructureMap::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRStructureMap::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one Subscription resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSubscription
     * @throws \Exception
     */
    public function readOneSubscription(string|FHIRId|FHIRIdPrimitive $resourceID,
                                        null|SerializeFormatEnum $format = null): FHIRSubscription
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::SUBSCRIPTION,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRSubscription::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRSubscription::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one Substance resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSubstance
     * @throws \Exception
     */
    public function readOneSubstance(string|FHIRId|FHIRIdPrimitive $resourceID,
                                     null|SerializeFormatEnum $format = null): FHIRSubstance
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::SUBSTANCE,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRSubstance::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRSubstance::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one SubstanceNucleicAcid resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSubstanceNucleicAcid
     * @throws \Exception
     */
    public function readOneSubstanceNucleicAcid(string|FHIRId|FHIRIdPrimitive $resourceID,
                                                null|SerializeFormatEnum $format = null): FHIRSubstanceNucleicAcid
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::SUBSTANCE_NUCLEIC_ACID,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRSubstanceNucleicAcid::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRSubstanceNucleicAcid::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one SubstancePolymer resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSubstancePolymer
     * @throws \Exception
     */
    public function readOneSubstancePolymer(string|FHIRId|FHIRIdPrimitive $resourceID,
                                            null|SerializeFormatEnum $format = null): FHIRSubstancePolymer
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::SUBSTANCE_POLYMER,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRSubstancePolymer::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRSubstancePolymer::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one SubstanceProtein resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSubstanceProtein
     * @throws \Exception
     */
    public function readOneSubstanceProtein(string|FHIRId|FHIRIdPrimitive $resourceID,
                                            null|SerializeFormatEnum $format = null): FHIRSubstanceProtein
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::SUBSTANCE_PROTEIN,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRSubstanceProtein::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRSubstanceProtein::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one SubstanceReferenceInformation resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSubstanceReferenceInformation
     * @throws \Exception
     */
    public function readOneSubstanceReferenceInformation(string|FHIRId|FHIRIdPrimitive $resourceID,
                                                         null|SerializeFormatEnum $format = null): FHIRSubstanceReferenceInformation
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::SUBSTANCE_REFERENCE_INFORMATION,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRSubstanceReferenceInformation::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRSubstanceReferenceInformation::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one SubstanceSourceMaterial resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSubstanceSourceMaterial
     * @throws \Exception
     */
    public function readOneSubstanceSourceMaterial(string|FHIRId|FHIRIdPrimitive $resourceID,
                                                   null|SerializeFormatEnum $format = null): FHIRSubstanceSourceMaterial
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::SUBSTANCE_SOURCE_MATERIAL,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRSubstanceSourceMaterial::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRSubstanceSourceMaterial::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one SubstanceSpecification resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSubstanceSpecification
     * @throws \Exception
     */
    public function readOneSubstanceSpecification(string|FHIRId|FHIRIdPrimitive $resourceID,
                                                  null|SerializeFormatEnum $format = null): FHIRSubstanceSpecification
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::SUBSTANCE_SPECIFICATION,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRSubstanceSpecification::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRSubstanceSpecification::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one SupplyDelivery resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSupplyDelivery
     * @throws \Exception
     */
    public function readOneSupplyDelivery(string|FHIRId|FHIRIdPrimitive $resourceID,
                                          null|SerializeFormatEnum $format = null): FHIRSupplyDelivery
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::SUPPLY_DELIVERY,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRSupplyDelivery::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRSupplyDelivery::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one SupplyRequest resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSupplyRequest
     * @throws \Exception
     */
    public function readOneSupplyRequest(string|FHIRId|FHIRIdPrimitive $resourceID,
                                         null|SerializeFormatEnum $format = null): FHIRSupplyRequest
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::SUPPLY_REQUEST,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRSupplyRequest::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRSupplyRequest::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one Task resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRTask
     * @throws \Exception
     */
    public function readOneTask(string|FHIRId|FHIRIdPrimitive $resourceID,
                                null|SerializeFormatEnum $format = null): FHIRTask
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::TASK,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRTask::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRTask::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one TerminologyCapabilities resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRTerminologyCapabilities
     * @throws \Exception
     */
    public function readOneTerminologyCapabilities(string|FHIRId|FHIRIdPrimitive $resourceID,
                                                   null|SerializeFormatEnum $format = null): FHIRTerminologyCapabilities
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::TERMINOLOGY_CAPABILITIES,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRTerminologyCapabilities::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRTerminologyCapabilities::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one TestReport resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRTestReport
     * @throws \Exception
     */
    public function readOneTestReport(string|FHIRId|FHIRIdPrimitive $resourceID,
                                      null|SerializeFormatEnum $format = null): FHIRTestReport
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::TEST_REPORT,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRTestReport::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRTestReport::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one TestScript resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRTestScript
     * @throws \Exception
     */
    public function readOneTestScript(string|FHIRId|FHIRIdPrimitive $resourceID,
                                      null|SerializeFormatEnum $format = null): FHIRTestScript
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::TEST_SCRIPT,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRTestScript::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRTestScript::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one ValueSet resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRValueSet
     * @throws \Exception
     */
    public function readOneValueSet(string|FHIRId|FHIRIdPrimitive $resourceID,
                                    null|SerializeFormatEnum $format = null): FHIRValueSet
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::VALUE_SET,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRValueSet::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRValueSet::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one VerificationResult resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRVerificationResult
     * @throws \Exception
     */
    public function readOneVerificationResult(string|FHIRId|FHIRIdPrimitive $resourceID,
                                              null|SerializeFormatEnum $format = null): FHIRVerificationResult
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::VERIFICATION_RESULT,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRVerificationResult::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRVerificationResult::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    /**
     * Read one VisionPrescription resource.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive $resourceID
     * @param null|\OpenEMR\FHIR\Encoding\SerializeFormatEnum $format
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRVisionPrescription
     * @throws \Exception
     */
    public function readOneVisionPrescription(string|FHIRId|FHIRIdPrimitive $resourceID,
                                              null|SerializeFormatEnum $format = null): FHIRVisionPrescription
    {
        $rc = $this->read(resourceType: VersionResourceTypeEnum::VISION_PRESCRIPTION,
                          resourceID: $resourceID,
                          format: $format);
        $this->_requireOK($rc);
        return match($format) {
            SerializeFormatEnum::JSON => FHIRVisionPrescription::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => FHIRVisionPrescription::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
        };
    }

    protected function _requireOK(Response $rc): void
    {
        if (isset($rc->err)) {
            throw new ClientErrorException($rc);
        }
        if (!isset($rc->code) || self::_STATUS_OK !== $rc->code) {
            throw new UnexpectedResponseCodeException($rc, self::_STATUS_OK);
        }
    }

    protected function _parseResponse(VersionResourceTypeEnum $resourceType,
                                     Response $rc): VersionResourceTypeInterface
    {
        /** @var \OpenEMR\FHIR\Versions\R4\VersionResourceTypeInterface $class */
        $class = $this->_version->getTypeMap()::getTypeClassname($resourceType->name);
        return match ($rc->getResponseFormat()) {
            SerializeFormatEnum::JSON => $class::jsonUnserialize(
                decoded: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            SerializeFormatEnum::XML => $class::xmlUnserialize(
                element: $rc->resp,
                config: $this->_version->getConfig()->getUnserializeConfig(),
            ),
            null => ResourceParser::parse($this->_version, $rc->resp),
        };
    }

    protected function _mustGetResourceID(VersionResourceTypeInterface $resource): string
    {
        $id = $resource->getId()?->_getValueAsString();
        if (null === $id || '' === $id) {
            throw new \DomainException(sprintf(
                'Cannot update resource of type "%s" as its ID is undefined or empty',
                $resource->_getFHIRTypeName(),
            ));
        }
        return $id;
    }
}
