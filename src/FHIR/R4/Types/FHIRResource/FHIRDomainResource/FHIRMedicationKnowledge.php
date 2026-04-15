<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource;

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
use OpenEMR\FHIR\Encoding\SerializeConfig;
use OpenEMR\FHIR\Encoding\UnserializeConfig;
use OpenEMR\FHIR\Encoding\ValueXMLLocationEnum;
use OpenEMR\FHIR\Encoding\XMLSerializationOptionsTrait;
use OpenEMR\FHIR\Encoding\XMLWriter;
use OpenEMR\FHIR\Types\ResourceTypeInterface;
use OpenEMR\FHIR\Validation\TypeValidationsTrait;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeAdministrationGuidelines;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeCost;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeDrugCharacteristic;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeIngredient;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeKinetics;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMedicineClassification;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMonitoringProgram;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMonograph;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgePackaging;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeRegulatory;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeRelatedMedicationKnowledge;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRMarkdownPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive;
use OpenEMR\FHIR\Versions\R4\Version;
use OpenEMR\FHIR\Versions\R4\VersionConstants;
use OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface;
use OpenEMR\FHIR\Versions\R4\VersionTypeMap;

/**
 * Information about a medication that is used to support knowledge.
 * If the element is present, it must have either a \@value, an \@id, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRMedicationKnowledge extends FHIRDomainResource implements VersionContainedTypeInterface
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_MEDICATION_KNOWLEDGE;

    /* class_default.php:56 */
    public const FIELD_CODE = 'code';
    public const FIELD_STATUS = 'status';
    public const FIELD_STATUS_EXT = '_status';
    public const FIELD_MANUFACTURER = 'manufacturer';
    public const FIELD_DOSE_FORM = 'doseForm';
    public const FIELD_AMOUNT = 'amount';
    public const FIELD_SYNONYM = 'synonym';
    public const FIELD_SYNONYM_EXT = '_synonym';
    public const FIELD_RELATED_MEDICATION_KNOWLEDGE = 'relatedMedicationKnowledge';
    public const FIELD_ASSOCIATED_MEDICATION = 'associatedMedication';
    public const FIELD_PRODUCT_TYPE = 'productType';
    public const FIELD_MONOGRAPH = 'monograph';
    public const FIELD_INGREDIENT = 'ingredient';
    public const FIELD_PREPARATION_INSTRUCTION = 'preparationInstruction';
    public const FIELD_PREPARATION_INSTRUCTION_EXT = '_preparationInstruction';
    public const FIELD_INTENDED_ROUTE = 'intendedRoute';
    public const FIELD_COST = 'cost';
    public const FIELD_MONITORING_PROGRAM = 'monitoringProgram';
    public const FIELD_ADMINISTRATION_GUIDELINES = 'administrationGuidelines';
    public const FIELD_MEDICINE_CLASSIFICATION = 'medicineClassification';
    public const FIELD_PACKAGING = 'packaging';
    public const FIELD_DRUG_CHARACTERISTIC = 'drugCharacteristic';
    public const FIELD_CONTRAINDICATION = 'contraindication';
    public const FIELD_REGULATORY = 'regulatory';
    public const FIELD_KINETICS = 'kinetics';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_STATUS => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_PREPARATION_INSTRUCTION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A code that specifies this medication, or a textual description if no code is
     * available. Usage note: This could be a standard medication code such as a code
     * from RxNorm, SNOMED CT, IDMP etc. It could also be a national or local formulary
     * code, optionally with translations to other code systems.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $code;
    /**
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A code to indicate if the medication is in active use. The status refers to the
     * validity about the information of the medication and not to its medicinal
     * properties.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode
     */
    #[FHIRCode]
    protected FHIRCode $status;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Describes the details of the manufacturer of the medication product. This is not
     * intended to represent the distributor of a medication product.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $manufacturer;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Describes the form of the item. Powder; tablets; capsule.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $doseForm;
    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Specific amount of the drug in the packaged product. For example, when
     * specifying a product that has the same strength (For example, Insulin glargine
     * 100 unit per mL solution for injection), this attribute provides additional
     * clarification of the package amount (For example, 3 mL, 10mL, etc.).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    #[FHIRQuantity]
    protected FHIRQuantity $amount;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Additional names for a medication, for example, the name(s) given to a
     * medication in different countries. For example, acetaminophen and paracetamol or
     * salbutamol and albuterol.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    #[FHIRString]
    protected array $synonym;
    /**
     * Information about a medication that is used to support knowledge.
     *
     * Associated or related knowledge about a medication.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeRelatedMedicationKnowledge>
     */
    #[FHIRMedicationKnowledgeRelatedMedicationKnowledge]
    protected array $relatedMedicationKnowledge;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Associated or related medications. For example, if the medication is a branded
     * product (e.g. Crestor), this is the Therapeutic Moeity (e.g. Rosuvastatin) or if
     * this is a generic medication (e.g. Rosuvastatin), this would link to a branded
     * product (e.g. Crestor).
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $associatedMedication;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Category of the medication or product (e.g. branded product, therapeutic moeity,
     * generic product, innovator product, etc.).
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $productType;
    /**
     * Information about a medication that is used to support knowledge.
     *
     * Associated documentation about the medication.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMonograph>
     */
    #[FHIRMedicationKnowledgeMonograph]
    protected array $monograph;
    /**
     * Information about a medication that is used to support knowledge.
     *
     * Identifies a particular constituent of interest in the product.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeIngredient>
     */
    #[FHIRMedicationKnowledgeIngredient]
    protected array $ingredient;
    /**
     * A string that may contain Github Flavored Markdown syntax for optional
     * processing by a mark down presentation engine
     * Systems are not required to have markdown support, so the text should be
     * readable without markdown processing. The markdown syntax is GFM - see
     * https://github.github.com/gfm/
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The instructions for preparing the medication.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown
     */
    #[FHIRMarkdown]
    protected FHIRMarkdown $preparationInstruction;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The intended or approved route of administration.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $intendedRoute;
    /**
     * Information about a medication that is used to support knowledge.
     *
     * The price of the medication.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeCost>
     */
    #[FHIRMedicationKnowledgeCost]
    protected array $cost;
    /**
     * Information about a medication that is used to support knowledge.
     *
     * The program under which the medication is reviewed.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMonitoringProgram>
     */
    #[FHIRMedicationKnowledgeMonitoringProgram]
    protected array $monitoringProgram;
    /**
     * Information about a medication that is used to support knowledge.
     *
     * Guidelines for the administration of the medication.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeAdministrationGuidelines>
     */
    #[FHIRMedicationKnowledgeAdministrationGuidelines]
    protected array $administrationGuidelines;
    /**
     * Information about a medication that is used to support knowledge.
     *
     * Categorization of the medication within a formulary or classification system.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMedicineClassification>
     */
    #[FHIRMedicationKnowledgeMedicineClassification]
    protected array $medicineClassification;
    /**
     * Information about a medication that is used to support knowledge.
     *
     * Information that only applies to packages (not products).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgePackaging
     */
    #[FHIRMedicationKnowledgePackaging]
    protected FHIRMedicationKnowledgePackaging $packaging;
    /**
     * Information about a medication that is used to support knowledge.
     *
     * Specifies descriptive properties of the medicine, such as color, shape,
     * imprints, etc.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeDrugCharacteristic>
     */
    #[FHIRMedicationKnowledgeDrugCharacteristic]
    protected array $drugCharacteristic;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Potential clinical issue with or between medication(s) (for example, drug-drug
     * interaction, drug-disease contraindication, drug-allergy interaction, etc.).
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $contraindication;
    /**
     * Information about a medication that is used to support knowledge.
     *
     * Regulatory information about a medication.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeRegulatory>
     */
    #[FHIRMedicationKnowledgeRegulatory]
    protected array $regulatory;
    /**
     * Information about a medication that is used to support knowledge.
     *
     * The time course of drug absorption, distribution, metabolism and excretion of a
     * medication from the body.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeKinetics>
     */
    #[FHIRMedicationKnowledgeKinetics]
    protected array $kinetics;

    /* constructor.php:61 */
    /**
     * FHIRMedicationKnowledge Constructor
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $id
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta $meta
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $implicitRules
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $language
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative $text
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRResourceContainer>|iterable<\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface> $contained
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $code
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $status
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $manufacturer
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $doseForm
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $amount
     * @param null|iterable<string>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString> $synonym
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeRelatedMedicationKnowledge> $relatedMedicationKnowledge
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $associatedMedication
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $productType
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMonograph> $monograph
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeIngredient> $ingredient
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRMarkdownPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown $preparationInstruction
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $intendedRoute
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeCost> $cost
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMonitoringProgram> $monitoringProgram
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeAdministrationGuidelines> $administrationGuidelines
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMedicineClassification> $medicineClassification
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgePackaging $packaging
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeDrugCharacteristic> $drugCharacteristic
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $contraindication
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeRegulatory> $regulatory
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeKinetics> $kinetics
     * @param null|string[] $fhirComments
     */
    public function __construct(null|string|FHIRIdPrimitive|FHIRId $id = null,
                                null|FHIRMeta $meta = null,
                                null|string|FHIRUriPrimitive|FHIRUri $implicitRules = null,
                                null|string|FHIRCodePrimitive|FHIRCode $language = null,
                                null|FHIRNarrative $text = null,
                                null|iterable $contained = null,
                                null|iterable $extension = null,
                                null|iterable $modifierExtension = null,
                                null|FHIRCodeableConcept $code = null,
                                null|string|FHIRCodePrimitive|FHIRCode $status = null,
                                null|FHIRReference $manufacturer = null,
                                null|FHIRCodeableConcept $doseForm = null,
                                null|FHIRQuantity $amount = null,
                                null|iterable $synonym = null,
                                null|iterable $relatedMedicationKnowledge = null,
                                null|iterable $associatedMedication = null,
                                null|iterable $productType = null,
                                null|iterable $monograph = null,
                                null|iterable $ingredient = null,
                                null|string|FHIRMarkdownPrimitive|FHIRMarkdown $preparationInstruction = null,
                                null|iterable $intendedRoute = null,
                                null|iterable $cost = null,
                                null|iterable $monitoringProgram = null,
                                null|iterable $administrationGuidelines = null,
                                null|iterable $medicineClassification = null,
                                null|FHIRMedicationKnowledgePackaging $packaging = null,
                                null|iterable $drugCharacteristic = null,
                                null|iterable $contraindication = null,
                                null|iterable $regulatory = null,
                                null|iterable $kinetics = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(id: $id,
                            meta: $meta,
                            implicitRules: $implicitRules,
                            language: $language,
                            text: $text,
                            contained: $contained,
                            extension: $extension,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $code) {
            $this->setCode($code);
        }
        if (null !== $status) {
            $this->setStatus($status);
        }
        if (null !== $manufacturer) {
            $this->setManufacturer($manufacturer);
        }
        if (null !== $doseForm) {
            $this->setDoseForm($doseForm);
        }
        if (null !== $amount) {
            $this->setAmount($amount);
        }
        if (null !== $synonym) {
            $this->setSynonym(...$synonym);
        }
        if (null !== $relatedMedicationKnowledge) {
            $this->setRelatedMedicationKnowledge(...$relatedMedicationKnowledge);
        }
        if (null !== $associatedMedication) {
            $this->setAssociatedMedication(...$associatedMedication);
        }
        if (null !== $productType) {
            $this->setProductType(...$productType);
        }
        if (null !== $monograph) {
            $this->setMonograph(...$monograph);
        }
        if (null !== $ingredient) {
            $this->setIngredient(...$ingredient);
        }
        if (null !== $preparationInstruction) {
            $this->setPreparationInstruction($preparationInstruction);
        }
        if (null !== $intendedRoute) {
            $this->setIntendedRoute(...$intendedRoute);
        }
        if (null !== $cost) {
            $this->setCost(...$cost);
        }
        if (null !== $monitoringProgram) {
            $this->setMonitoringProgram(...$monitoringProgram);
        }
        if (null !== $administrationGuidelines) {
            $this->setAdministrationGuidelines(...$administrationGuidelines);
        }
        if (null !== $medicineClassification) {
            $this->setMedicineClassification(...$medicineClassification);
        }
        if (null !== $packaging) {
            $this->setPackaging($packaging);
        }
        if (null !== $drugCharacteristic) {
            $this->setDrugCharacteristic(...$drugCharacteristic);
        }
        if (null !== $contraindication) {
            $this->setContraindication(...$contraindication);
        }
        if (null !== $regulatory) {
            $this->setRegulatory(...$regulatory);
        }
        if (null !== $kinetics) {
            $this->setKinetics(...$kinetics);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:163 */
    public function _getResourceType(): string
    {
        return static::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A code that specifies this medication, or a textual description if no code is
     * available. Usage note: This could be a standard medication code such as a code
     * from RxNorm, SNOMED CT, IDMP etc. It could also be a national or local formulary
     * code, optionally with translations to other code systems.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getCode(): null|FHIRCodeableConcept
    {
        return $this->code ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A code that specifies this medication, or a textual description if no code is
     * available. Usage note: This could be a standard medication code such as a code
     * from RxNorm, SNOMED CT, IDMP etc. It could also be a national or local formulary
     * code, optionally with translations to other code systems.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $code
     * @return static
     */
    public function setCode(null|FHIRCodeableConcept $code): self
    {
        if (null === $code) {
            unset($this->code);
            return $this;
        }
        $this->code = $code;
        return $this;
    }

    /**
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A code to indicate if the medication is in active use. The status refers to the
     * validity about the information of the medication and not to its medicinal
     * properties.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode
     */
    public function getStatus(): null|FHIRCode
    {
        return $this->status ?? null;
    }

    /**
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A code to indicate if the medication is in active use. The status refers to the
     * validity about the information of the medication and not to its medicinal
     * properties.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $status
     * @return static
     */
    public function setStatus(null|string|FHIRCodePrimitive|FHIRCode $status): self
    {
        if (null === $status) {
            unset($this->status);
            return $this;
        }
        if (!($status instanceof FHIRCode)) {
            $status = new FHIRCode(value: $status);
        }
        $this->status = $status;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Describes the details of the manufacturer of the medication product. This is not
     * intended to represent the distributor of a medication product.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getManufacturer(): null|FHIRReference
    {
        return $this->manufacturer ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Describes the details of the manufacturer of the medication product. This is not
     * intended to represent the distributor of a medication product.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $manufacturer
     * @return static
     */
    public function setManufacturer(null|FHIRReference $manufacturer): self
    {
        if (null === $manufacturer) {
            unset($this->manufacturer);
            return $this;
        }
        $this->manufacturer = $manufacturer;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Describes the form of the item. Powder; tablets; capsule.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getDoseForm(): null|FHIRCodeableConcept
    {
        return $this->doseForm ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Describes the form of the item. Powder; tablets; capsule.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $doseForm
     * @return static
     */
    public function setDoseForm(null|FHIRCodeableConcept $doseForm): self
    {
        if (null === $doseForm) {
            unset($this->doseForm);
            return $this;
        }
        $this->doseForm = $doseForm;
        return $this;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Specific amount of the drug in the packaged product. For example, when
     * specifying a product that has the same strength (For example, Insulin glargine
     * 100 unit per mL solution for injection), this attribute provides additional
     * clarification of the package amount (For example, 3 mL, 10mL, etc.).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    public function getAmount(): null|FHIRQuantity
    {
        return $this->amount ?? null;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Specific amount of the drug in the packaged product. For example, when
     * specifying a product that has the same strength (For example, Insulin glargine
     * 100 unit per mL solution for injection), this attribute provides additional
     * clarification of the package amount (For example, 3 mL, 10mL, etc.).
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $amount
     * @return static
     */
    public function setAmount(null|FHIRQuantity $amount): self
    {
        if (null === $amount) {
            unset($this->amount);
            return $this;
        }
        $this->amount = $amount;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Additional names for a medication, for example, the name(s) given to a
     * medication in different countries. For example, acetaminophen and paracetamol or
     * salbutamol and albuterol.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    public function getSynonym(): array
    {
        return $this->synonym ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    public function getSynonymIterator(): iterable
    {
        if (!isset($this->synonym)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->synonym);
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Additional names for a medication, for example, the name(s) given to a
     * medication in different countries. For example, acetaminophen and paracetamol or
     * salbutamol and albuterol.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $synonym
     * @return static
     */
    public function addSynonym(string|FHIRStringPrimitive|FHIRString $synonym): self
    {
        if (!($synonym instanceof FHIRString)) {
            $synonym = new FHIRString(value: $synonym);
        }
        if (!isset($this->synonym)) {
            $this->synonym = [];
        }
        $this->synonym[] = $synonym;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Additional names for a medication, for example, the name(s) given to a
     * medication in different countries. For example, acetaminophen and paracetamol or
     * salbutamol and albuterol.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString ...$synonym
     * @return static
     */
    public function setSynonym(string|FHIRStringPrimitive|FHIRString ...$synonym): self
    {
        if ([] === $synonym) {
            unset($this->synonym);
            return $this;
        }
        $this->synonym = [];
        foreach($synonym as $v) {
            if ($v instanceof FHIRString) {
                $this->synonym[] = $v;
            } else {
                $this->synonym[] = new FHIRString(value: $v);
            }
        }
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Associated or related knowledge about a medication.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeRelatedMedicationKnowledge>
     */
    public function getRelatedMedicationKnowledge(): array
    {
        return $this->relatedMedicationKnowledge ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeRelatedMedicationKnowledge>
     */
    public function getRelatedMedicationKnowledgeIterator(): iterable
    {
        if (!isset($this->relatedMedicationKnowledge)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->relatedMedicationKnowledge);
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Associated or related knowledge about a medication.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeRelatedMedicationKnowledge $relatedMedicationKnowledge
     * @return static
     */
    public function addRelatedMedicationKnowledge(FHIRMedicationKnowledgeRelatedMedicationKnowledge $relatedMedicationKnowledge): self
    {
        if (!isset($this->relatedMedicationKnowledge)) {
            $this->relatedMedicationKnowledge = [];
        }
        $this->relatedMedicationKnowledge[] = $relatedMedicationKnowledge;
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Associated or related knowledge about a medication.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeRelatedMedicationKnowledge ...$relatedMedicationKnowledge
     * @return static
     */
    public function setRelatedMedicationKnowledge(FHIRMedicationKnowledgeRelatedMedicationKnowledge ...$relatedMedicationKnowledge): self
    {
        if ([] === $relatedMedicationKnowledge) {
            unset($this->relatedMedicationKnowledge);
            return $this;
        }
        $this->relatedMedicationKnowledge = $relatedMedicationKnowledge;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Associated or related medications. For example, if the medication is a branded
     * product (e.g. Crestor), this is the Therapeutic Moeity (e.g. Rosuvastatin) or if
     * this is a generic medication (e.g. Rosuvastatin), this would link to a branded
     * product (e.g. Crestor).
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getAssociatedMedication(): array
    {
        return $this->associatedMedication ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getAssociatedMedicationIterator(): iterable
    {
        if (!isset($this->associatedMedication)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->associatedMedication);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Associated or related medications. For example, if the medication is a branded
     * product (e.g. Crestor), this is the Therapeutic Moeity (e.g. Rosuvastatin) or if
     * this is a generic medication (e.g. Rosuvastatin), this would link to a branded
     * product (e.g. Crestor).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $associatedMedication
     * @return static
     */
    public function addAssociatedMedication(FHIRReference $associatedMedication): self
    {
        if (!isset($this->associatedMedication)) {
            $this->associatedMedication = [];
        }
        $this->associatedMedication[] = $associatedMedication;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Associated or related medications. For example, if the medication is a branded
     * product (e.g. Crestor), this is the Therapeutic Moeity (e.g. Rosuvastatin) or if
     * this is a generic medication (e.g. Rosuvastatin), this would link to a branded
     * product (e.g. Crestor).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$associatedMedication
     * @return static
     */
    public function setAssociatedMedication(FHIRReference ...$associatedMedication): self
    {
        if ([] === $associatedMedication) {
            unset($this->associatedMedication);
            return $this;
        }
        $this->associatedMedication = $associatedMedication;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Category of the medication or product (e.g. branded product, therapeutic moeity,
     * generic product, innovator product, etc.).
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getProductType(): array
    {
        return $this->productType ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getProductTypeIterator(): iterable
    {
        if (!isset($this->productType)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->productType);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Category of the medication or product (e.g. branded product, therapeutic moeity,
     * generic product, innovator product, etc.).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $productType
     * @return static
     */
    public function addProductType(FHIRCodeableConcept $productType): self
    {
        if (!isset($this->productType)) {
            $this->productType = [];
        }
        $this->productType[] = $productType;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Category of the medication or product (e.g. branded product, therapeutic moeity,
     * generic product, innovator product, etc.).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$productType
     * @return static
     */
    public function setProductType(FHIRCodeableConcept ...$productType): self
    {
        if ([] === $productType) {
            unset($this->productType);
            return $this;
        }
        $this->productType = $productType;
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Associated documentation about the medication.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMonograph>
     */
    public function getMonograph(): array
    {
        return $this->monograph ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMonograph>
     */
    public function getMonographIterator(): iterable
    {
        if (!isset($this->monograph)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->monograph);
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Associated documentation about the medication.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMonograph $monograph
     * @return static
     */
    public function addMonograph(FHIRMedicationKnowledgeMonograph $monograph): self
    {
        if (!isset($this->monograph)) {
            $this->monograph = [];
        }
        $this->monograph[] = $monograph;
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Associated documentation about the medication.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMonograph ...$monograph
     * @return static
     */
    public function setMonograph(FHIRMedicationKnowledgeMonograph ...$monograph): self
    {
        if ([] === $monograph) {
            unset($this->monograph);
            return $this;
        }
        $this->monograph = $monograph;
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Identifies a particular constituent of interest in the product.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeIngredient>
     */
    public function getIngredient(): array
    {
        return $this->ingredient ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeIngredient>
     */
    public function getIngredientIterator(): iterable
    {
        if (!isset($this->ingredient)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->ingredient);
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Identifies a particular constituent of interest in the product.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeIngredient $ingredient
     * @return static
     */
    public function addIngredient(FHIRMedicationKnowledgeIngredient $ingredient): self
    {
        if (!isset($this->ingredient)) {
            $this->ingredient = [];
        }
        $this->ingredient[] = $ingredient;
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Identifies a particular constituent of interest in the product.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeIngredient ...$ingredient
     * @return static
     */
    public function setIngredient(FHIRMedicationKnowledgeIngredient ...$ingredient): self
    {
        if ([] === $ingredient) {
            unset($this->ingredient);
            return $this;
        }
        $this->ingredient = $ingredient;
        return $this;
    }

    /**
     * A string that may contain Github Flavored Markdown syntax for optional
     * processing by a mark down presentation engine
     * Systems are not required to have markdown support, so the text should be
     * readable without markdown processing. The markdown syntax is GFM - see
     * https://github.github.com/gfm/
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The instructions for preparing the medication.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown
     */
    public function getPreparationInstruction(): null|FHIRMarkdown
    {
        return $this->preparationInstruction ?? null;
    }

    /**
     * A string that may contain Github Flavored Markdown syntax for optional
     * processing by a mark down presentation engine
     * Systems are not required to have markdown support, so the text should be
     * readable without markdown processing. The markdown syntax is GFM - see
     * https://github.github.com/gfm/
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The instructions for preparing the medication.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRMarkdownPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown $preparationInstruction
     * @return static
     */
    public function setPreparationInstruction(null|string|FHIRMarkdownPrimitive|FHIRMarkdown $preparationInstruction): self
    {
        if (null === $preparationInstruction) {
            unset($this->preparationInstruction);
            return $this;
        }
        if (!($preparationInstruction instanceof FHIRMarkdown)) {
            $preparationInstruction = new FHIRMarkdown(value: $preparationInstruction);
        }
        $this->preparationInstruction = $preparationInstruction;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The intended or approved route of administration.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getIntendedRoute(): array
    {
        return $this->intendedRoute ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getIntendedRouteIterator(): iterable
    {
        if (!isset($this->intendedRoute)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->intendedRoute);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The intended or approved route of administration.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $intendedRoute
     * @return static
     */
    public function addIntendedRoute(FHIRCodeableConcept $intendedRoute): self
    {
        if (!isset($this->intendedRoute)) {
            $this->intendedRoute = [];
        }
        $this->intendedRoute[] = $intendedRoute;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The intended or approved route of administration.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$intendedRoute
     * @return static
     */
    public function setIntendedRoute(FHIRCodeableConcept ...$intendedRoute): self
    {
        if ([] === $intendedRoute) {
            unset($this->intendedRoute);
            return $this;
        }
        $this->intendedRoute = $intendedRoute;
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * The price of the medication.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeCost>
     */
    public function getCost(): array
    {
        return $this->cost ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeCost>
     */
    public function getCostIterator(): iterable
    {
        if (!isset($this->cost)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->cost);
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * The price of the medication.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeCost $cost
     * @return static
     */
    public function addCost(FHIRMedicationKnowledgeCost $cost): self
    {
        if (!isset($this->cost)) {
            $this->cost = [];
        }
        $this->cost[] = $cost;
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * The price of the medication.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeCost ...$cost
     * @return static
     */
    public function setCost(FHIRMedicationKnowledgeCost ...$cost): self
    {
        if ([] === $cost) {
            unset($this->cost);
            return $this;
        }
        $this->cost = $cost;
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * The program under which the medication is reviewed.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMonitoringProgram>
     */
    public function getMonitoringProgram(): array
    {
        return $this->monitoringProgram ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMonitoringProgram>
     */
    public function getMonitoringProgramIterator(): iterable
    {
        if (!isset($this->monitoringProgram)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->monitoringProgram);
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * The program under which the medication is reviewed.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMonitoringProgram $monitoringProgram
     * @return static
     */
    public function addMonitoringProgram(FHIRMedicationKnowledgeMonitoringProgram $monitoringProgram): self
    {
        if (!isset($this->monitoringProgram)) {
            $this->monitoringProgram = [];
        }
        $this->monitoringProgram[] = $monitoringProgram;
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * The program under which the medication is reviewed.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMonitoringProgram ...$monitoringProgram
     * @return static
     */
    public function setMonitoringProgram(FHIRMedicationKnowledgeMonitoringProgram ...$monitoringProgram): self
    {
        if ([] === $monitoringProgram) {
            unset($this->monitoringProgram);
            return $this;
        }
        $this->monitoringProgram = $monitoringProgram;
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Guidelines for the administration of the medication.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeAdministrationGuidelines>
     */
    public function getAdministrationGuidelines(): array
    {
        return $this->administrationGuidelines ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeAdministrationGuidelines>
     */
    public function getAdministrationGuidelinesIterator(): iterable
    {
        if (!isset($this->administrationGuidelines)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->administrationGuidelines);
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Guidelines for the administration of the medication.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeAdministrationGuidelines $administrationGuidelines
     * @return static
     */
    public function addAdministrationGuidelines(FHIRMedicationKnowledgeAdministrationGuidelines $administrationGuidelines): self
    {
        if (!isset($this->administrationGuidelines)) {
            $this->administrationGuidelines = [];
        }
        $this->administrationGuidelines[] = $administrationGuidelines;
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Guidelines for the administration of the medication.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeAdministrationGuidelines ...$administrationGuidelines
     * @return static
     */
    public function setAdministrationGuidelines(FHIRMedicationKnowledgeAdministrationGuidelines ...$administrationGuidelines): self
    {
        if ([] === $administrationGuidelines) {
            unset($this->administrationGuidelines);
            return $this;
        }
        $this->administrationGuidelines = $administrationGuidelines;
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Categorization of the medication within a formulary or classification system.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMedicineClassification>
     */
    public function getMedicineClassification(): array
    {
        return $this->medicineClassification ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMedicineClassification>
     */
    public function getMedicineClassificationIterator(): iterable
    {
        if (!isset($this->medicineClassification)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->medicineClassification);
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Categorization of the medication within a formulary or classification system.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMedicineClassification $medicineClassification
     * @return static
     */
    public function addMedicineClassification(FHIRMedicationKnowledgeMedicineClassification $medicineClassification): self
    {
        if (!isset($this->medicineClassification)) {
            $this->medicineClassification = [];
        }
        $this->medicineClassification[] = $medicineClassification;
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Categorization of the medication within a formulary or classification system.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMedicineClassification ...$medicineClassification
     * @return static
     */
    public function setMedicineClassification(FHIRMedicationKnowledgeMedicineClassification ...$medicineClassification): self
    {
        if ([] === $medicineClassification) {
            unset($this->medicineClassification);
            return $this;
        }
        $this->medicineClassification = $medicineClassification;
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Information that only applies to packages (not products).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgePackaging
     */
    public function getPackaging(): null|FHIRMedicationKnowledgePackaging
    {
        return $this->packaging ?? null;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Information that only applies to packages (not products).
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgePackaging $packaging
     * @return static
     */
    public function setPackaging(null|FHIRMedicationKnowledgePackaging $packaging): self
    {
        if (null === $packaging) {
            unset($this->packaging);
            return $this;
        }
        $this->packaging = $packaging;
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Specifies descriptive properties of the medicine, such as color, shape,
     * imprints, etc.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeDrugCharacteristic>
     */
    public function getDrugCharacteristic(): array
    {
        return $this->drugCharacteristic ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeDrugCharacteristic>
     */
    public function getDrugCharacteristicIterator(): iterable
    {
        if (!isset($this->drugCharacteristic)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->drugCharacteristic);
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Specifies descriptive properties of the medicine, such as color, shape,
     * imprints, etc.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeDrugCharacteristic $drugCharacteristic
     * @return static
     */
    public function addDrugCharacteristic(FHIRMedicationKnowledgeDrugCharacteristic $drugCharacteristic): self
    {
        if (!isset($this->drugCharacteristic)) {
            $this->drugCharacteristic = [];
        }
        $this->drugCharacteristic[] = $drugCharacteristic;
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Specifies descriptive properties of the medicine, such as color, shape,
     * imprints, etc.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeDrugCharacteristic ...$drugCharacteristic
     * @return static
     */
    public function setDrugCharacteristic(FHIRMedicationKnowledgeDrugCharacteristic ...$drugCharacteristic): self
    {
        if ([] === $drugCharacteristic) {
            unset($this->drugCharacteristic);
            return $this;
        }
        $this->drugCharacteristic = $drugCharacteristic;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Potential clinical issue with or between medication(s) (for example, drug-drug
     * interaction, drug-disease contraindication, drug-allergy interaction, etc.).
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getContraindication(): array
    {
        return $this->contraindication ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getContraindicationIterator(): iterable
    {
        if (!isset($this->contraindication)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->contraindication);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Potential clinical issue with or between medication(s) (for example, drug-drug
     * interaction, drug-disease contraindication, drug-allergy interaction, etc.).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $contraindication
     * @return static
     */
    public function addContraindication(FHIRReference $contraindication): self
    {
        if (!isset($this->contraindication)) {
            $this->contraindication = [];
        }
        $this->contraindication[] = $contraindication;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Potential clinical issue with or between medication(s) (for example, drug-drug
     * interaction, drug-disease contraindication, drug-allergy interaction, etc.).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$contraindication
     * @return static
     */
    public function setContraindication(FHIRReference ...$contraindication): self
    {
        if ([] === $contraindication) {
            unset($this->contraindication);
            return $this;
        }
        $this->contraindication = $contraindication;
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Regulatory information about a medication.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeRegulatory>
     */
    public function getRegulatory(): array
    {
        return $this->regulatory ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeRegulatory>
     */
    public function getRegulatoryIterator(): iterable
    {
        if (!isset($this->regulatory)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->regulatory);
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Regulatory information about a medication.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeRegulatory $regulatory
     * @return static
     */
    public function addRegulatory(FHIRMedicationKnowledgeRegulatory $regulatory): self
    {
        if (!isset($this->regulatory)) {
            $this->regulatory = [];
        }
        $this->regulatory[] = $regulatory;
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Regulatory information about a medication.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeRegulatory ...$regulatory
     * @return static
     */
    public function setRegulatory(FHIRMedicationKnowledgeRegulatory ...$regulatory): self
    {
        if ([] === $regulatory) {
            unset($this->regulatory);
            return $this;
        }
        $this->regulatory = $regulatory;
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * The time course of drug absorption, distribution, metabolism and excretion of a
     * medication from the body.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeKinetics>
     */
    public function getKinetics(): array
    {
        return $this->kinetics ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeKinetics>
     */
    public function getKineticsIterator(): iterable
    {
        if (!isset($this->kinetics)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->kinetics);
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * The time course of drug absorption, distribution, metabolism and excretion of a
     * medication from the body.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeKinetics $kinetics
     * @return static
     */
    public function addKinetics(FHIRMedicationKnowledgeKinetics $kinetics): self
    {
        if (!isset($this->kinetics)) {
            $this->kinetics = [];
        }
        $this->kinetics[] = $kinetics;
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * The time course of drug absorption, distribution, metabolism and excretion of a
     * medication from the body.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeKinetics ...$kinetics
     * @return static
     */
    public function setKinetics(FHIRMedicationKnowledgeKinetics ...$kinetics): self
    {
        if ([] === $kinetics) {
            unset($this->kinetics);
            return $this;
        }
        $this->kinetics = $kinetics;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param string|\SimpleXMLElement $element
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicationKnowledge $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicationKnowledge
     * @throws \Exception
     */
    public static function xmlUnserialize(string|\SimpleXMLElement $element,
                                          null|UnserializeConfig $config = null,
                                          null|ResourceTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRMedicationKnowledge)) {
            throw new \RuntimeException(sprintf(
                '%s::xmlUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        if (null === $config) {
            $config = (new Version())->getConfig()->getUnserializeConfig();
        }
        if (is_string($element)) {
            $element = new \SimpleXMLElement($element, $config->getLibxmlOpts());
        }
        if (null !== ($ns = $element->getNamespaces()[''] ?? null)) {
            $type->_setSourceXMLNS((string)$ns);
        }
        foreach ($element->children() as $ce) {
            $cen = $ce->getName();
            if (self::FIELD_ID === $cen) {
                $type->setId(FHIRId::xmlUnserialize($ce, $config));
            } else if (self::FIELD_META === $cen) {
                $type->setMeta(FHIRMeta::xmlUnserialize($ce, $config));
            } else if (self::FIELD_IMPLICIT_RULES === $cen) {
                $type->setImplicitRules(FHIRUri::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LANGUAGE === $cen) {
                $type->setLanguage(FHIRCode::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TEXT === $cen) {
                $type->setText(FHIRNarrative::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CONTAINED === $cen) {
                foreach ($ce->children() as $cen) {
                    /** @var \OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface $cn */
                    $cn = VersionTypeMap::mustGetContainedTypeClassnameFromXML($cen);
                    $type->addContained($cn::xmlUnserialize($cen, $config));
                }
            } else if (self::FIELD_EXTENSION === $cen) {
                $type->addExtension(FHIRExtension::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MODIFIER_EXTENSION === $cen) {
                $type->addModifierExtension(FHIRExtension::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CODE === $cen) {
                $type->setCode(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_STATUS === $cen) {
                $type->setStatus(FHIRCode::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MANUFACTURER === $cen) {
                $type->setManufacturer(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DOSE_FORM === $cen) {
                $type->setDoseForm(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_AMOUNT === $cen) {
                $type->setAmount(FHIRQuantity::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SYNONYM === $cen) {
                $type->addSynonym(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RELATED_MEDICATION_KNOWLEDGE === $cen) {
                $type->addRelatedMedicationKnowledge(FHIRMedicationKnowledgeRelatedMedicationKnowledge::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ASSOCIATED_MEDICATION === $cen) {
                $type->addAssociatedMedication(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PRODUCT_TYPE === $cen) {
                $type->addProductType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MONOGRAPH === $cen) {
                $type->addMonograph(FHIRMedicationKnowledgeMonograph::xmlUnserialize($ce, $config));
            } else if (self::FIELD_INGREDIENT === $cen) {
                $type->addIngredient(FHIRMedicationKnowledgeIngredient::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PREPARATION_INSTRUCTION === $cen) {
                $type->setPreparationInstruction(FHIRMarkdown::xmlUnserialize($ce, $config));
            } else if (self::FIELD_INTENDED_ROUTE === $cen) {
                $type->addIntendedRoute(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_COST === $cen) {
                $type->addCost(FHIRMedicationKnowledgeCost::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MONITORING_PROGRAM === $cen) {
                $type->addMonitoringProgram(FHIRMedicationKnowledgeMonitoringProgram::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ADMINISTRATION_GUIDELINES === $cen) {
                $type->addAdministrationGuidelines(FHIRMedicationKnowledgeAdministrationGuidelines::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MEDICINE_CLASSIFICATION === $cen) {
                $type->addMedicineClassification(FHIRMedicationKnowledgeMedicineClassification::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PACKAGING === $cen) {
                $type->setPackaging(FHIRMedicationKnowledgePackaging::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DRUG_CHARACTERISTIC === $cen) {
                $type->addDrugCharacteristic(FHIRMedicationKnowledgeDrugCharacteristic::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CONTRAINDICATION === $cen) {
                $type->addContraindication(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REGULATORY === $cen) {
                $type->addRegulatory(FHIRMedicationKnowledgeRegulatory::xmlUnserialize($ce, $config));
            } else if (self::FIELD_KINETICS === $cen) {
                $type->addKinetics(FHIRMedicationKnowledgeKinetics::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            if (isset($type->id)) {
                $type->id->setValue((string)$attributes[self::FIELD_ID]);
            } else {
                $type->setId((string)$attributes[self::FIELD_ID]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_IMPLICIT_RULES])) {
            if (isset($type->implicitRules)) {
                $type->implicitRules->setValue((string)$attributes[self::FIELD_IMPLICIT_RULES]);
            } else {
                $type->setImplicitRules((string)$attributes[self::FIELD_IMPLICIT_RULES]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_IMPLICIT_RULES, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_LANGUAGE])) {
            if (isset($type->language)) {
                $type->language->setValue((string)$attributes[self::FIELD_LANGUAGE]);
            } else {
                $type->setLanguage((string)$attributes[self::FIELD_LANGUAGE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_LANGUAGE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_STATUS])) {
            if (isset($type->status)) {
                $type->status->setValue((string)$attributes[self::FIELD_STATUS]);
            } else {
                $type->setStatus((string)$attributes[self::FIELD_STATUS]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_STATUS, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_PREPARATION_INSTRUCTION])) {
            if (isset($type->preparationInstruction)) {
                $type->preparationInstruction->setValue((string)$attributes[self::FIELD_PREPARATION_INSTRUCTION]);
            } else {
                $type->setPreparationInstruction((string)$attributes[self::FIELD_PREPARATION_INSTRUCTION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_PREPARATION_INSTRUCTION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        return $type;
    }

    /**
     * @param null|\OpenEMR\FHIR\Encoding\XMLWriter $xw
     * @param null|\OpenEMR\FHIR\Encoding\SerializeConfig $config
     * @return \OpenEMR\FHIR\Encoding\XMLWriter
     */
    public function xmlSerialize(null|XMLWriter $xw = null,
                                 null|SerializeConfig $config = null): XMLWriter
    {
        if (null === $config) {
            $config = (new Version())->getConfig()->getSerializeConfig();
        }
        if (null === $xw) {
            $xw = new XMLWriter($config);
        }
        if (!$xw->isOpen()) {
            $xw->openMemory();
        }
        if (!$xw->isDocStarted()) {
            $docStarted = true;
            $xw->startDocument();
        }
        if (!$xw->isRootOpen()) {
            $rootOpened = true;
            $xw->openRootNode('MedicationKnowledge', $this->_getSourceXMLNS());
        }
        if (isset($this->status) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_STATUS]) {
            $xw->writeAttribute(self::FIELD_STATUS, $this->status->_getValueAsString());
        }
        if (isset($this->preparationInstruction) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_PREPARATION_INSTRUCTION]) {
            $xw->writeAttribute(self::FIELD_PREPARATION_INSTRUCTION, $this->preparationInstruction->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->code)) {
            $xw->startElement(self::FIELD_CODE);
            $this->code->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->status)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_STATUS]
                || $this->status->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_STATUS);
            $this->status->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_STATUS]);
            $xw->endElement();
        }
        if (isset($this->manufacturer)) {
            $xw->startElement(self::FIELD_MANUFACTURER);
            $this->manufacturer->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->doseForm)) {
            $xw->startElement(self::FIELD_DOSE_FORM);
            $this->doseForm->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->amount)) {
            $xw->startElement(self::FIELD_AMOUNT);
            $this->amount->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->synonym) && [] !== $this->synonym) {
            foreach($this->synonym as $v) {
                $xw->startElement(self::FIELD_SYNONYM);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->relatedMedicationKnowledge)) {
            foreach ($this->relatedMedicationKnowledge as $v) {
                $xw->startElement(self::FIELD_RELATED_MEDICATION_KNOWLEDGE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->associatedMedication)) {
            foreach ($this->associatedMedication as $v) {
                $xw->startElement(self::FIELD_ASSOCIATED_MEDICATION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->productType)) {
            foreach ($this->productType as $v) {
                $xw->startElement(self::FIELD_PRODUCT_TYPE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->monograph)) {
            foreach ($this->monograph as $v) {
                $xw->startElement(self::FIELD_MONOGRAPH);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->ingredient)) {
            foreach ($this->ingredient as $v) {
                $xw->startElement(self::FIELD_INGREDIENT);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->preparationInstruction)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_PREPARATION_INSTRUCTION]
                || $this->preparationInstruction->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_PREPARATION_INSTRUCTION);
            $this->preparationInstruction->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_PREPARATION_INSTRUCTION]);
            $xw->endElement();
        }
        if (isset($this->intendedRoute)) {
            foreach ($this->intendedRoute as $v) {
                $xw->startElement(self::FIELD_INTENDED_ROUTE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->cost)) {
            foreach ($this->cost as $v) {
                $xw->startElement(self::FIELD_COST);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->monitoringProgram)) {
            foreach ($this->monitoringProgram as $v) {
                $xw->startElement(self::FIELD_MONITORING_PROGRAM);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->administrationGuidelines)) {
            foreach ($this->administrationGuidelines as $v) {
                $xw->startElement(self::FIELD_ADMINISTRATION_GUIDELINES);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->medicineClassification)) {
            foreach ($this->medicineClassification as $v) {
                $xw->startElement(self::FIELD_MEDICINE_CLASSIFICATION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->packaging)) {
            $xw->startElement(self::FIELD_PACKAGING);
            $this->packaging->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->drugCharacteristic)) {
            foreach ($this->drugCharacteristic as $v) {
                $xw->startElement(self::FIELD_DRUG_CHARACTERISTIC);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->contraindication)) {
            foreach ($this->contraindication as $v) {
                $xw->startElement(self::FIELD_CONTRAINDICATION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->regulatory)) {
            foreach ($this->regulatory as $v) {
                $xw->startElement(self::FIELD_REGULATORY);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->kinetics)) {
            foreach ($this->kinetics as $v) {
                $xw->startElement(self::FIELD_KINETICS);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if ($rootOpened ?? false) {
            $xw->endElement();
        }
        if ($docStarted ?? false) {
            $xw->endDocument();
        }
        return $xw;
    }

    /**
     * @param string|\stdClass $decoded
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicationKnowledge $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicationKnowledge
     * @throws \Exception
     */
    public static function jsonUnserialize(string|\stdClass $decoded,
                                           null|UnserializeConfig $config = null,
                                           null|ResourceTypeInterface $type = null): self
    {
        if (null === $type) {
            if (isset($decoded->resourceType) && $decoded->resourceType !== static::FHIR_TYPE_NAME) {
                throw new \DomainException(sprintf(
                    '%s::jsonUnserialize - Cannot unmarshal data for resource type "%s" into this type.',
                    ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                    $decoded->resourceType,
                ));
            }
            $type = new static();
        } else if (!($type instanceof FHIRMedicationKnowledge)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        if (null === $config) {
            $config = (new Version())->getConfig()->getUnserializeConfig();
        }
        if (is_string($decoded)) {
            $decoded = json_decode(json: $decoded,
                                associative: false,
                                depth: $config->getJSONDecodeMaxDepth(),
                                flags: $config->getJSONDecodeOpts());
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->code) || property_exists($decoded, self::FIELD_CODE)) {
            if (is_array($decoded->code)) {
                $type->setCode(FHIRCodeableConcept::jsonUnserialize(reset($decoded->code), $config));
            } else {
                $type->setCode(FHIRCodeableConcept::jsonUnserialize($decoded->code, $config));
            }
        }
        if (isset($decoded->status)
            || isset($decoded->_status)
            || property_exists($decoded, self::FIELD_STATUS)
            || property_exists($decoded, self::FIELD_STATUS_EXT)) {
            $v = $decoded->_status ?? new \stdClass();
            $v->value = $decoded->status ?? null;
            $type->setStatus(FHIRCode::jsonUnserialize($v, $config));
        }
        if (isset($decoded->manufacturer) || property_exists($decoded, self::FIELD_MANUFACTURER)) {
            if (is_array($decoded->manufacturer)) {
                $type->setManufacturer(FHIRReference::jsonUnserialize(reset($decoded->manufacturer), $config));
            } else {
                $type->setManufacturer(FHIRReference::jsonUnserialize($decoded->manufacturer, $config));
            }
        }
        if (isset($decoded->doseForm) || property_exists($decoded, self::FIELD_DOSE_FORM)) {
            if (is_array($decoded->doseForm)) {
                $type->setDoseForm(FHIRCodeableConcept::jsonUnserialize(reset($decoded->doseForm), $config));
            } else {
                $type->setDoseForm(FHIRCodeableConcept::jsonUnserialize($decoded->doseForm, $config));
            }
        }
        if (isset($decoded->amount) || property_exists($decoded, self::FIELD_AMOUNT)) {
            if (is_array($decoded->amount)) {
                $type->setAmount(FHIRQuantity::jsonUnserialize(reset($decoded->amount), $config));
            } else {
                $type->setAmount(FHIRQuantity::jsonUnserialize($decoded->amount, $config));
            }
        }
        if (isset($decoded->synonym)
            || isset($decoded->_synonym)
            || property_exists($decoded, self::FIELD_SYNONYM)
            || property_exists($decoded, self::FIELD_SYNONYM_EXT)) {
            $vals = (array)($decoded->synonym ?? []);
            $exts = (array)($decoded->_synonym ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addSynonym(FHIRString::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->relatedMedicationKnowledge) || property_exists($decoded, self::FIELD_RELATED_MEDICATION_KNOWLEDGE)) {
            if (is_object($decoded->relatedMedicationKnowledge)) {
                $vals = [$decoded->relatedMedicationKnowledge];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_RELATED_MEDICATION_KNOWLEDGE, true);
            } else {
                $vals = $decoded->relatedMedicationKnowledge;
            }
            foreach($vals as $v) {
                $type->addRelatedMedicationKnowledge(FHIRMedicationKnowledgeRelatedMedicationKnowledge::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->associatedMedication) || property_exists($decoded, self::FIELD_ASSOCIATED_MEDICATION)) {
            if (is_object($decoded->associatedMedication)) {
                $vals = [$decoded->associatedMedication];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_ASSOCIATED_MEDICATION, true);
            } else {
                $vals = $decoded->associatedMedication;
            }
            foreach($vals as $v) {
                $type->addAssociatedMedication(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->productType) || property_exists($decoded, self::FIELD_PRODUCT_TYPE)) {
            if (is_object($decoded->productType)) {
                $vals = [$decoded->productType];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PRODUCT_TYPE, true);
            } else {
                $vals = $decoded->productType;
            }
            foreach($vals as $v) {
                $type->addProductType(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->monograph) || property_exists($decoded, self::FIELD_MONOGRAPH)) {
            if (is_object($decoded->monograph)) {
                $vals = [$decoded->monograph];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_MONOGRAPH, true);
            } else {
                $vals = $decoded->monograph;
            }
            foreach($vals as $v) {
                $type->addMonograph(FHIRMedicationKnowledgeMonograph::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->ingredient) || property_exists($decoded, self::FIELD_INGREDIENT)) {
            if (is_object($decoded->ingredient)) {
                $vals = [$decoded->ingredient];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_INGREDIENT, true);
            } else {
                $vals = $decoded->ingredient;
            }
            foreach($vals as $v) {
                $type->addIngredient(FHIRMedicationKnowledgeIngredient::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->preparationInstruction)
            || isset($decoded->_preparationInstruction)
            || property_exists($decoded, self::FIELD_PREPARATION_INSTRUCTION)
            || property_exists($decoded, self::FIELD_PREPARATION_INSTRUCTION_EXT)) {
            $v = $decoded->_preparationInstruction ?? new \stdClass();
            $v->value = $decoded->preparationInstruction ?? null;
            $type->setPreparationInstruction(FHIRMarkdown::jsonUnserialize($v, $config));
        }
        if (isset($decoded->intendedRoute) || property_exists($decoded, self::FIELD_INTENDED_ROUTE)) {
            if (is_object($decoded->intendedRoute)) {
                $vals = [$decoded->intendedRoute];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_INTENDED_ROUTE, true);
            } else {
                $vals = $decoded->intendedRoute;
            }
            foreach($vals as $v) {
                $type->addIntendedRoute(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->cost) || property_exists($decoded, self::FIELD_COST)) {
            if (is_object($decoded->cost)) {
                $vals = [$decoded->cost];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_COST, true);
            } else {
                $vals = $decoded->cost;
            }
            foreach($vals as $v) {
                $type->addCost(FHIRMedicationKnowledgeCost::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->monitoringProgram) || property_exists($decoded, self::FIELD_MONITORING_PROGRAM)) {
            if (is_object($decoded->monitoringProgram)) {
                $vals = [$decoded->monitoringProgram];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_MONITORING_PROGRAM, true);
            } else {
                $vals = $decoded->monitoringProgram;
            }
            foreach($vals as $v) {
                $type->addMonitoringProgram(FHIRMedicationKnowledgeMonitoringProgram::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->administrationGuidelines) || property_exists($decoded, self::FIELD_ADMINISTRATION_GUIDELINES)) {
            if (is_object($decoded->administrationGuidelines)) {
                $vals = [$decoded->administrationGuidelines];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_ADMINISTRATION_GUIDELINES, true);
            } else {
                $vals = $decoded->administrationGuidelines;
            }
            foreach($vals as $v) {
                $type->addAdministrationGuidelines(FHIRMedicationKnowledgeAdministrationGuidelines::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->medicineClassification) || property_exists($decoded, self::FIELD_MEDICINE_CLASSIFICATION)) {
            if (is_object($decoded->medicineClassification)) {
                $vals = [$decoded->medicineClassification];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_MEDICINE_CLASSIFICATION, true);
            } else {
                $vals = $decoded->medicineClassification;
            }
            foreach($vals as $v) {
                $type->addMedicineClassification(FHIRMedicationKnowledgeMedicineClassification::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->packaging) || property_exists($decoded, self::FIELD_PACKAGING)) {
            if (is_array($decoded->packaging)) {
                $type->setPackaging(FHIRMedicationKnowledgePackaging::jsonUnserialize(reset($decoded->packaging), $config));
            } else {
                $type->setPackaging(FHIRMedicationKnowledgePackaging::jsonUnserialize($decoded->packaging, $config));
            }
        }
        if (isset($decoded->drugCharacteristic) || property_exists($decoded, self::FIELD_DRUG_CHARACTERISTIC)) {
            if (is_object($decoded->drugCharacteristic)) {
                $vals = [$decoded->drugCharacteristic];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_DRUG_CHARACTERISTIC, true);
            } else {
                $vals = $decoded->drugCharacteristic;
            }
            foreach($vals as $v) {
                $type->addDrugCharacteristic(FHIRMedicationKnowledgeDrugCharacteristic::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->contraindication) || property_exists($decoded, self::FIELD_CONTRAINDICATION)) {
            if (is_object($decoded->contraindication)) {
                $vals = [$decoded->contraindication];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_CONTRAINDICATION, true);
            } else {
                $vals = $decoded->contraindication;
            }
            foreach($vals as $v) {
                $type->addContraindication(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->regulatory) || property_exists($decoded, self::FIELD_REGULATORY)) {
            if (is_object($decoded->regulatory)) {
                $vals = [$decoded->regulatory];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_REGULATORY, true);
            } else {
                $vals = $decoded->regulatory;
            }
            foreach($vals as $v) {
                $type->addRegulatory(FHIRMedicationKnowledgeRegulatory::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->kinetics) || property_exists($decoded, self::FIELD_KINETICS)) {
            if (is_object($decoded->kinetics)) {
                $vals = [$decoded->kinetics];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_KINETICS, true);
            } else {
                $vals = $decoded->kinetics;
            }
            foreach($vals as $v) {
                $type->addKinetics(FHIRMedicationKnowledgeKinetics::jsonUnserialize($v, $config));
            }
        }
        return $type;
    }

    /**
     * @return \stdClass
     */
    public function jsonSerialize(): mixed
    {
        $out = parent::jsonSerialize();
        if (isset($this->code)) {
            $out->code = $this->code;
        }
        if (isset($this->status)) {
            if (null !== ($val = $this->status->getValue())) {
                $out->status = $val;
            }
            if ($this->status->_nonValueFieldDefined()) {
                $ext = $this->status->jsonSerialize();
                unset($ext->value);
                $out->_status = $ext;
            }
        }
        if (isset($this->manufacturer)) {
            $out->manufacturer = $this->manufacturer;
        }
        if (isset($this->doseForm)) {
            $out->doseForm = $this->doseForm;
        }
        if (isset($this->amount)) {
            $out->amount = $this->amount;
        }
        if (isset($this->synonym) && [] !== $this->synonym) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->synonym as $v) {
                $val = $v->getValue();
                if (null !== $val) {
                    $hasVals = true;
                    $vals[] = $val;
                } else {
                    $vals[] = null;
                }
                if ($v->_nonValueFieldDefined()) {
                    $hasExts = true;
                    $ext = $v->jsonSerialize();
                    unset($ext->value);
                    $exts[] = $ext;
                } else {
                    $exts[] = null;
                }
            }
            if ($hasVals) {
                $out->synonym = $vals;
            }
            if ($hasExts) {
                $out->_synonym = $exts;
            }
        }
        if (isset($this->relatedMedicationKnowledge) && [] !== $this->relatedMedicationKnowledge) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_RELATED_MEDICATION_KNOWLEDGE) && 1 === count($this->relatedMedicationKnowledge)) {
                $out->relatedMedicationKnowledge = $this->relatedMedicationKnowledge[0];
            } else {
                $out->relatedMedicationKnowledge = $this->relatedMedicationKnowledge;
            }
        }
        if (isset($this->associatedMedication) && [] !== $this->associatedMedication) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_ASSOCIATED_MEDICATION) && 1 === count($this->associatedMedication)) {
                $out->associatedMedication = $this->associatedMedication[0];
            } else {
                $out->associatedMedication = $this->associatedMedication;
            }
        }
        if (isset($this->productType) && [] !== $this->productType) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PRODUCT_TYPE) && 1 === count($this->productType)) {
                $out->productType = $this->productType[0];
            } else {
                $out->productType = $this->productType;
            }
        }
        if (isset($this->monograph) && [] !== $this->monograph) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_MONOGRAPH) && 1 === count($this->monograph)) {
                $out->monograph = $this->monograph[0];
            } else {
                $out->monograph = $this->monograph;
            }
        }
        if (isset($this->ingredient) && [] !== $this->ingredient) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_INGREDIENT) && 1 === count($this->ingredient)) {
                $out->ingredient = $this->ingredient[0];
            } else {
                $out->ingredient = $this->ingredient;
            }
        }
        if (isset($this->preparationInstruction)) {
            if (null !== ($val = $this->preparationInstruction->getValue())) {
                $out->preparationInstruction = $val;
            }
            if ($this->preparationInstruction->_nonValueFieldDefined()) {
                $ext = $this->preparationInstruction->jsonSerialize();
                unset($ext->value);
                $out->_preparationInstruction = $ext;
            }
        }
        if (isset($this->intendedRoute) && [] !== $this->intendedRoute) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_INTENDED_ROUTE) && 1 === count($this->intendedRoute)) {
                $out->intendedRoute = $this->intendedRoute[0];
            } else {
                $out->intendedRoute = $this->intendedRoute;
            }
        }
        if (isset($this->cost) && [] !== $this->cost) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_COST) && 1 === count($this->cost)) {
                $out->cost = $this->cost[0];
            } else {
                $out->cost = $this->cost;
            }
        }
        if (isset($this->monitoringProgram) && [] !== $this->monitoringProgram) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_MONITORING_PROGRAM) && 1 === count($this->monitoringProgram)) {
                $out->monitoringProgram = $this->monitoringProgram[0];
            } else {
                $out->monitoringProgram = $this->monitoringProgram;
            }
        }
        if (isset($this->administrationGuidelines) && [] !== $this->administrationGuidelines) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_ADMINISTRATION_GUIDELINES) && 1 === count($this->administrationGuidelines)) {
                $out->administrationGuidelines = $this->administrationGuidelines[0];
            } else {
                $out->administrationGuidelines = $this->administrationGuidelines;
            }
        }
        if (isset($this->medicineClassification) && [] !== $this->medicineClassification) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_MEDICINE_CLASSIFICATION) && 1 === count($this->medicineClassification)) {
                $out->medicineClassification = $this->medicineClassification[0];
            } else {
                $out->medicineClassification = $this->medicineClassification;
            }
        }
        if (isset($this->packaging)) {
            $out->packaging = $this->packaging;
        }
        if (isset($this->drugCharacteristic) && [] !== $this->drugCharacteristic) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_DRUG_CHARACTERISTIC) && 1 === count($this->drugCharacteristic)) {
                $out->drugCharacteristic = $this->drugCharacteristic[0];
            } else {
                $out->drugCharacteristic = $this->drugCharacteristic;
            }
        }
        if (isset($this->contraindication) && [] !== $this->contraindication) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_CONTRAINDICATION) && 1 === count($this->contraindication)) {
                $out->contraindication = $this->contraindication[0];
            } else {
                $out->contraindication = $this->contraindication;
            }
        }
        if (isset($this->regulatory) && [] !== $this->regulatory) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_REGULATORY) && 1 === count($this->regulatory)) {
                $out->regulatory = $this->regulatory[0];
            } else {
                $out->regulatory = $this->regulatory;
            }
        }
        if (isset($this->kinetics) && [] !== $this->kinetics) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_KINETICS) && 1 === count($this->kinetics)) {
                $out->kinetics = $this->kinetics[0];
            } else {
                $out->kinetics = $this->kinetics;
            }
        }
        $out->resourceType = $this->_getResourceType();
        return $out;
    }
}
