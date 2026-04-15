<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive;

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
use OpenEMR\FHIR\Encoding\JSONSerializationOptionsTrait;
use OpenEMR\FHIR\Encoding\XMLSerializationOptionsTrait;
use OpenEMR\FHIR\Validation\Rules\ValueOneOfRule;
use OpenEMR\FHIR\Validation\TypeValidationsTrait;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRResourceTypeList extends FHIRCodePrimitive
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_RESOURCE_TYPE_HYPHEN_LIST;

    /* class_default.php:56 */

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_VALUE => [
            ValueOneOfRule::NAME => [
                0 => 'Account',
                1 => 'ActivityDefinition',
                2 => 'AdverseEvent',
                3 => 'AllergyIntolerance',
                4 => 'Appointment',
                5 => 'AppointmentResponse',
                6 => 'AuditEvent',
                7 => 'Basic',
                8 => 'Binary',
                9 => 'BiologicallyDerivedProduct',
                10 => 'BodyStructure',
                11 => 'Bundle',
                12 => 'CapabilityStatement',
                13 => 'CarePlan',
                14 => 'CareTeam',
                15 => 'CatalogEntry',
                16 => 'ChargeItem',
                17 => 'ChargeItemDefinition',
                18 => 'Claim',
                19 => 'ClaimResponse',
                20 => 'ClinicalImpression',
                21 => 'CodeSystem',
                22 => 'Communication',
                23 => 'CommunicationRequest',
                24 => 'CompartmentDefinition',
                25 => 'Composition',
                26 => 'ConceptMap',
                27 => 'Condition',
                28 => 'Consent',
                29 => 'Contract',
                30 => 'Coverage',
                31 => 'CoverageEligibilityRequest',
                32 => 'CoverageEligibilityResponse',
                33 => 'DetectedIssue',
                34 => 'Device',
                35 => 'DeviceDefinition',
                36 => 'DeviceMetric',
                37 => 'DeviceRequest',
                38 => 'DeviceUseStatement',
                39 => 'DiagnosticReport',
                40 => 'DocumentManifest',
                41 => 'DocumentReference',
                42 => 'DomainResource',
                43 => 'EffectEvidenceSynthesis',
                44 => 'Encounter',
                45 => 'Endpoint',
                46 => 'EnrollmentRequest',
                47 => 'EnrollmentResponse',
                48 => 'EpisodeOfCare',
                49 => 'EventDefinition',
                50 => 'Evidence',
                51 => 'EvidenceVariable',
                52 => 'ExampleScenario',
                53 => 'ExplanationOfBenefit',
                54 => 'FamilyMemberHistory',
                55 => 'Flag',
                56 => 'Goal',
                57 => 'GraphDefinition',
                58 => 'Group',
                59 => 'GuidanceResponse',
                60 => 'HealthcareService',
                61 => 'ImagingStudy',
                62 => 'Immunization',
                63 => 'ImmunizationEvaluation',
                64 => 'ImmunizationRecommendation',
                65 => 'ImplementationGuide',
                66 => 'InsurancePlan',
                67 => 'Invoice',
                68 => 'Library',
                69 => 'Linkage',
                70 => 'List',
                71 => 'Location',
                72 => 'Measure',
                73 => 'MeasureReport',
                74 => 'Media',
                75 => 'Medication',
                76 => 'MedicationAdministration',
                77 => 'MedicationDispense',
                78 => 'MedicationKnowledge',
                79 => 'MedicationRequest',
                80 => 'MedicationStatement',
                81 => 'MedicinalProduct',
                82 => 'MedicinalProductAuthorization',
                83 => 'MedicinalProductContraindication',
                84 => 'MedicinalProductIndication',
                85 => 'MedicinalProductIngredient',
                86 => 'MedicinalProductInteraction',
                87 => 'MedicinalProductManufactured',
                88 => 'MedicinalProductPackaged',
                89 => 'MedicinalProductPharmaceutical',
                90 => 'MedicinalProductUndesirableEffect',
                91 => 'MessageDefinition',
                92 => 'MessageHeader',
                93 => 'MolecularSequence',
                94 => 'NamingSystem',
                95 => 'NutritionOrder',
                96 => 'Observation',
                97 => 'ObservationDefinition',
                98 => 'OperationDefinition',
                99 => 'OperationOutcome',
                100 => 'Organization',
                101 => 'OrganizationAffiliation',
                102 => 'Parameters',
                103 => 'Patient',
                104 => 'PaymentNotice',
                105 => 'PaymentReconciliation',
                106 => 'Person',
                107 => 'PlanDefinition',
                108 => 'Practitioner',
                109 => 'PractitionerRole',
                110 => 'Procedure',
                111 => 'Provenance',
                112 => 'Questionnaire',
                113 => 'QuestionnaireResponse',
                114 => 'RelatedPerson',
                115 => 'RequestGroup',
                116 => 'ResearchDefinition',
                117 => 'ResearchElementDefinition',
                118 => 'ResearchStudy',
                119 => 'ResearchSubject',
                120 => 'Resource',
                121 => 'RiskAssessment',
                122 => 'RiskEvidenceSynthesis',
                123 => 'Schedule',
                124 => 'SearchParameter',
                125 => 'ServiceRequest',
                126 => 'Slot',
                127 => 'Specimen',
                128 => 'SpecimenDefinition',
                129 => 'StructureDefinition',
                130 => 'StructureMap',
                131 => 'Subscription',
                132 => 'Substance',
                133 => 'SubstanceNucleicAcid',
                134 => 'SubstancePolymer',
                135 => 'SubstanceProtein',
                136 => 'SubstanceReferenceInformation',
                137 => 'SubstanceSourceMaterial',
                138 => 'SubstanceSpecification',
                139 => 'SupplyDelivery',
                140 => 'SupplyRequest',
                141 => 'Task',
                142 => 'TerminologyCapabilities',
                143 => 'TestReport',
                144 => 'TestScript',
                145 => 'ValueSet',
                146 => 'VerificationResult',
                147 => 'VisionPrescription',
            ],
        ],
    ];

    /* class_default.php:112 */

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:201 */
}
