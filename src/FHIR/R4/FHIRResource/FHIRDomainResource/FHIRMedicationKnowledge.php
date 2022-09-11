<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 *
 * Class creation date: September 10th, 2022 20:42+0000
 *
 * PHPFHIR Copyright:
 *
 * Copyright 2016-2022 Daniel Carbone (daniel.p.carbone@gmail.com)
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

use OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeAdministrationGuidelines;
use OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeCost;
use OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeDrugCharacteristic;
use OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeIngredient;
use OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeKinetics;
use OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMedicineClassification;
use OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMonitoringProgram;
use OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMonograph;
use OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgePackaging;
use OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeRegulatory;
use OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeRelatedMedicationKnowledge;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCode;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRNarrative;
use OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUri;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\FHIR\R4\PHPFHIRConstants;
use OpenEMR\FHIR\R4\PHPFHIRContainedTypeInterface;
use OpenEMR\FHIR\R4\PHPFHIRTypeInterface;
use OpenEMR\FHIR\R4\PHPFHIRTypeMap;

/**
 * Information about a medication that is used to support knowledge.
 * If the element is present, it must have either a \@value, an \@id, or extensions
 *
 * Class FHIRMedicationKnowledge
 * @package \OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource
 */
class FHIRMedicationKnowledge extends FHIRDomainResource implements PHPFHIRContainedTypeInterface
{
    // name of FHIR type this class describes
    const FHIR_TYPE_NAME = PHPFHIRConstants::TYPE_NAME_MEDICATION_KNOWLEDGE;
    const FIELD_CODE = 'code';
    const FIELD_STATUS = 'status';
    const FIELD_STATUS_EXT = '_status';
    const FIELD_MANUFACTURER = 'manufacturer';
    const FIELD_DOSE_FORM = 'doseForm';
    const FIELD_AMOUNT = 'amount';
    const FIELD_SYNONYM = 'synonym';
    const FIELD_SYNONYM_EXT = '_synonym';
    const FIELD_RELATED_MEDICATION_KNOWLEDGE = 'relatedMedicationKnowledge';
    const FIELD_ASSOCIATED_MEDICATION = 'associatedMedication';
    const FIELD_PRODUCT_TYPE = 'productType';
    const FIELD_MONOGRAPH = 'monograph';
    const FIELD_INGREDIENT = 'ingredient';
    const FIELD_PREPARATION_INSTRUCTION = 'preparationInstruction';
    const FIELD_PREPARATION_INSTRUCTION_EXT = '_preparationInstruction';
    const FIELD_INTENDED_ROUTE = 'intendedRoute';
    const FIELD_COST = 'cost';
    const FIELD_MONITORING_PROGRAM = 'monitoringProgram';
    const FIELD_ADMINISTRATION_GUIDELINES = 'administrationGuidelines';
    const FIELD_MEDICINE_CLASSIFICATION = 'medicineClassification';
    const FIELD_PACKAGING = 'packaging';
    const FIELD_DRUG_CHARACTERISTIC = 'drugCharacteristic';
    const FIELD_CONTRAINDICATION = 'contraindication';
    const FIELD_REGULATORY = 'regulatory';
    const FIELD_KINETICS = 'kinetics';

    /** @var string */
    private $_xmlns = '';

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
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    protected $code = null;

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
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCode
     */
    protected $status = null;

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Describes the details of the manufacturer of the medication product. This is not
     * intended to represent the distributor of a medication product.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    protected $manufacturer = null;

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Describes the form of the item. Powder; tablets; capsule.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    protected $doseForm = null;

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
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    protected $amount = null;

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Additional names for a medication, for example, the name(s) given to a
     * medication in different countries. For example, acetaminophen and paracetamol or
     * salbutamol and albuterol.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    protected $synonym = [];

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Associated or related knowledge about a medication.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeRelatedMedicationKnowledge[]
     */
    protected $relatedMedicationKnowledge = [];

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
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    protected $associatedMedication = [];

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Category of the medication or product (e.g. branded product, therapeutic moeity,
     * generic product, innovator product, etc.).
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    protected $productType = [];

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Associated documentation about the medication.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMonograph[]
     */
    protected $monograph = [];

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Identifies a particular constituent of interest in the product.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeIngredient[]
     */
    protected $ingredient = [];

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
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    protected $preparationInstruction = null;

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The intended or approved route of administration.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    protected $intendedRoute = [];

    /**
     * Information about a medication that is used to support knowledge.
     *
     * The price of the medication.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeCost[]
     */
    protected $cost = [];

    /**
     * Information about a medication that is used to support knowledge.
     *
     * The program under which the medication is reviewed.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMonitoringProgram[]
     */
    protected $monitoringProgram = [];

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Guidelines for the administration of the medication.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeAdministrationGuidelines[]
     */
    protected $administrationGuidelines = [];

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Categorization of the medication within a formulary or classification system.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMedicineClassification[]
     */
    protected $medicineClassification = [];

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Information that only applies to packages (not products).
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgePackaging
     */
    protected $packaging = null;

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Specifies descriptive properties of the medicine, such as color, shape,
     * imprints, etc.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeDrugCharacteristic[]
     */
    protected $drugCharacteristic = [];

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Potential clinical issue with or between medication(s) (for example, drug-drug
     * interaction, drug-disease contraindication, drug-allergy interaction, etc.).
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    protected $contraindication = [];

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Regulatory information about a medication.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeRegulatory[]
     */
    protected $regulatory = [];

    /**
     * Information about a medication that is used to support knowledge.
     *
     * The time course of drug absorption, distribution, metabolism and excretion of a
     * medication from the body.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeKinetics[]
     */
    protected $kinetics = [];

    /**
     * Validation map for fields in type MedicationKnowledge
     * @var array
     */
    private static $_validationRules = [    ];

    /**
     * FHIRMedicationKnowledge Constructor
     * @param null|array $data
     */
    public function __construct($data = null)
    {
        if (null === $data || [] === $data) {
            return;
        }
        if (!is_array($data)) {
            throw new \InvalidArgumentException(sprintf(
                'FHIRMedicationKnowledge::_construct - $data expected to be null or array, %s seen',
                gettype($data)
            ));
        }
        parent::__construct($data);
        if (isset($data[self::FIELD_CODE])) {
            if ($data[self::FIELD_CODE] instanceof FHIRCodeableConcept) {
                $this->setCode($data[self::FIELD_CODE]);
            } else {
                $this->setCode(new FHIRCodeableConcept($data[self::FIELD_CODE]));
            }
        }
        if (isset($data[self::FIELD_STATUS]) || isset($data[self::FIELD_STATUS_EXT])) {
            $value = isset($data[self::FIELD_STATUS]) ? $data[self::FIELD_STATUS] : null;
            $ext = (isset($data[self::FIELD_STATUS_EXT]) && is_array($data[self::FIELD_STATUS_EXT])) ? $ext = $data[self::FIELD_STATUS_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRCode) {
                    $this->setStatus($value);
                } else if (is_array($value)) {
                    $this->setStatus(new FHIRCode(array_merge($ext, $value)));
                } else {
                    $this->setStatus(new FHIRCode([FHIRCode::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setStatus(new FHIRCode($ext));
            }
        }
        if (isset($data[self::FIELD_MANUFACTURER])) {
            if ($data[self::FIELD_MANUFACTURER] instanceof FHIRReference) {
                $this->setManufacturer($data[self::FIELD_MANUFACTURER]);
            } else {
                $this->setManufacturer(new FHIRReference($data[self::FIELD_MANUFACTURER]));
            }
        }
        if (isset($data[self::FIELD_DOSE_FORM])) {
            if ($data[self::FIELD_DOSE_FORM] instanceof FHIRCodeableConcept) {
                $this->setDoseForm($data[self::FIELD_DOSE_FORM]);
            } else {
                $this->setDoseForm(new FHIRCodeableConcept($data[self::FIELD_DOSE_FORM]));
            }
        }
        if (isset($data[self::FIELD_AMOUNT])) {
            if ($data[self::FIELD_AMOUNT] instanceof FHIRQuantity) {
                $this->setAmount($data[self::FIELD_AMOUNT]);
            } else {
                $this->setAmount(new FHIRQuantity($data[self::FIELD_AMOUNT]));
            }
        }
        if (isset($data[self::FIELD_SYNONYM]) || isset($data[self::FIELD_SYNONYM_EXT])) {
            $value = isset($data[self::FIELD_SYNONYM]) ? $data[self::FIELD_SYNONYM] : null;
            $ext = (isset($data[self::FIELD_SYNONYM_EXT]) && is_array($data[self::FIELD_SYNONYM_EXT])) ? $ext = $data[self::FIELD_SYNONYM_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRString) {
                    $this->addSynonym($value);
                } else if (is_array($value)) {
                    foreach ($value as $i => $v) {
                        if ($v instanceof FHIRString) {
                            $this->addSynonym($v);
                        } else {
                            $iext = (isset($ext[$i]) && is_array($ext[$i])) ? $ext[$i] : [];
                            if (is_array($v)) {
                                $this->addSynonym(new FHIRString(array_merge($v, $iext)));
                            } else {
                                $this->addSynonym(new FHIRString([FHIRString::FIELD_VALUE => $v] + $iext));
                            }
                        }
                    }
                } elseif (is_array($value)) {
                    $this->addSynonym(new FHIRString(array_merge($ext, $value)));
                } else {
                    $this->addSynonym(new FHIRString([FHIRString::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                foreach ($ext as $iext) {
                    $this->addSynonym(new FHIRString($iext));
                }
            }
        }
        if (isset($data[self::FIELD_RELATED_MEDICATION_KNOWLEDGE])) {
            if (is_array($data[self::FIELD_RELATED_MEDICATION_KNOWLEDGE])) {
                foreach ($data[self::FIELD_RELATED_MEDICATION_KNOWLEDGE] as $v) {
                    if (null === $v) {
                        continue;
                    }
                    if ($v instanceof FHIRMedicationKnowledgeRelatedMedicationKnowledge) {
                        $this->addRelatedMedicationKnowledge($v);
                    } else {
                        $this->addRelatedMedicationKnowledge(new FHIRMedicationKnowledgeRelatedMedicationKnowledge($v));
                    }
                }
            } elseif ($data[self::FIELD_RELATED_MEDICATION_KNOWLEDGE] instanceof FHIRMedicationKnowledgeRelatedMedicationKnowledge) {
                $this->addRelatedMedicationKnowledge($data[self::FIELD_RELATED_MEDICATION_KNOWLEDGE]);
            } else {
                $this->addRelatedMedicationKnowledge(new FHIRMedicationKnowledgeRelatedMedicationKnowledge($data[self::FIELD_RELATED_MEDICATION_KNOWLEDGE]));
            }
        }
        if (isset($data[self::FIELD_ASSOCIATED_MEDICATION])) {
            if (is_array($data[self::FIELD_ASSOCIATED_MEDICATION])) {
                foreach ($data[self::FIELD_ASSOCIATED_MEDICATION] as $v) {
                    if (null === $v) {
                        continue;
                    }
                    if ($v instanceof FHIRReference) {
                        $this->addAssociatedMedication($v);
                    } else {
                        $this->addAssociatedMedication(new FHIRReference($v));
                    }
                }
            } elseif ($data[self::FIELD_ASSOCIATED_MEDICATION] instanceof FHIRReference) {
                $this->addAssociatedMedication($data[self::FIELD_ASSOCIATED_MEDICATION]);
            } else {
                $this->addAssociatedMedication(new FHIRReference($data[self::FIELD_ASSOCIATED_MEDICATION]));
            }
        }
        if (isset($data[self::FIELD_PRODUCT_TYPE])) {
            if (is_array($data[self::FIELD_PRODUCT_TYPE])) {
                foreach ($data[self::FIELD_PRODUCT_TYPE] as $v) {
                    if (null === $v) {
                        continue;
                    }
                    if ($v instanceof FHIRCodeableConcept) {
                        $this->addProductType($v);
                    } else {
                        $this->addProductType(new FHIRCodeableConcept($v));
                    }
                }
            } elseif ($data[self::FIELD_PRODUCT_TYPE] instanceof FHIRCodeableConcept) {
                $this->addProductType($data[self::FIELD_PRODUCT_TYPE]);
            } else {
                $this->addProductType(new FHIRCodeableConcept($data[self::FIELD_PRODUCT_TYPE]));
            }
        }
        if (isset($data[self::FIELD_MONOGRAPH])) {
            if (is_array($data[self::FIELD_MONOGRAPH])) {
                foreach ($data[self::FIELD_MONOGRAPH] as $v) {
                    if (null === $v) {
                        continue;
                    }
                    if ($v instanceof FHIRMedicationKnowledgeMonograph) {
                        $this->addMonograph($v);
                    } else {
                        $this->addMonograph(new FHIRMedicationKnowledgeMonograph($v));
                    }
                }
            } elseif ($data[self::FIELD_MONOGRAPH] instanceof FHIRMedicationKnowledgeMonograph) {
                $this->addMonograph($data[self::FIELD_MONOGRAPH]);
            } else {
                $this->addMonograph(new FHIRMedicationKnowledgeMonograph($data[self::FIELD_MONOGRAPH]));
            }
        }
        if (isset($data[self::FIELD_INGREDIENT])) {
            if (is_array($data[self::FIELD_INGREDIENT])) {
                foreach ($data[self::FIELD_INGREDIENT] as $v) {
                    if (null === $v) {
                        continue;
                    }
                    if ($v instanceof FHIRMedicationKnowledgeIngredient) {
                        $this->addIngredient($v);
                    } else {
                        $this->addIngredient(new FHIRMedicationKnowledgeIngredient($v));
                    }
                }
            } elseif ($data[self::FIELD_INGREDIENT] instanceof FHIRMedicationKnowledgeIngredient) {
                $this->addIngredient($data[self::FIELD_INGREDIENT]);
            } else {
                $this->addIngredient(new FHIRMedicationKnowledgeIngredient($data[self::FIELD_INGREDIENT]));
            }
        }
        if (isset($data[self::FIELD_PREPARATION_INSTRUCTION]) || isset($data[self::FIELD_PREPARATION_INSTRUCTION_EXT])) {
            $value = isset($data[self::FIELD_PREPARATION_INSTRUCTION]) ? $data[self::FIELD_PREPARATION_INSTRUCTION] : null;
            $ext = (isset($data[self::FIELD_PREPARATION_INSTRUCTION_EXT]) && is_array($data[self::FIELD_PREPARATION_INSTRUCTION_EXT])) ? $ext = $data[self::FIELD_PREPARATION_INSTRUCTION_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRMarkdown) {
                    $this->setPreparationInstruction($value);
                } else if (is_array($value)) {
                    $this->setPreparationInstruction(new FHIRMarkdown(array_merge($ext, $value)));
                } else {
                    $this->setPreparationInstruction(new FHIRMarkdown([FHIRMarkdown::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setPreparationInstruction(new FHIRMarkdown($ext));
            }
        }
        if (isset($data[self::FIELD_INTENDED_ROUTE])) {
            if (is_array($data[self::FIELD_INTENDED_ROUTE])) {
                foreach ($data[self::FIELD_INTENDED_ROUTE] as $v) {
                    if (null === $v) {
                        continue;
                    }
                    if ($v instanceof FHIRCodeableConcept) {
                        $this->addIntendedRoute($v);
                    } else {
                        $this->addIntendedRoute(new FHIRCodeableConcept($v));
                    }
                }
            } elseif ($data[self::FIELD_INTENDED_ROUTE] instanceof FHIRCodeableConcept) {
                $this->addIntendedRoute($data[self::FIELD_INTENDED_ROUTE]);
            } else {
                $this->addIntendedRoute(new FHIRCodeableConcept($data[self::FIELD_INTENDED_ROUTE]));
            }
        }
        if (isset($data[self::FIELD_COST])) {
            if (is_array($data[self::FIELD_COST])) {
                foreach ($data[self::FIELD_COST] as $v) {
                    if (null === $v) {
                        continue;
                    }
                    if ($v instanceof FHIRMedicationKnowledgeCost) {
                        $this->addCost($v);
                    } else {
                        $this->addCost(new FHIRMedicationKnowledgeCost($v));
                    }
                }
            } elseif ($data[self::FIELD_COST] instanceof FHIRMedicationKnowledgeCost) {
                $this->addCost($data[self::FIELD_COST]);
            } else {
                $this->addCost(new FHIRMedicationKnowledgeCost($data[self::FIELD_COST]));
            }
        }
        if (isset($data[self::FIELD_MONITORING_PROGRAM])) {
            if (is_array($data[self::FIELD_MONITORING_PROGRAM])) {
                foreach ($data[self::FIELD_MONITORING_PROGRAM] as $v) {
                    if (null === $v) {
                        continue;
                    }
                    if ($v instanceof FHIRMedicationKnowledgeMonitoringProgram) {
                        $this->addMonitoringProgram($v);
                    } else {
                        $this->addMonitoringProgram(new FHIRMedicationKnowledgeMonitoringProgram($v));
                    }
                }
            } elseif ($data[self::FIELD_MONITORING_PROGRAM] instanceof FHIRMedicationKnowledgeMonitoringProgram) {
                $this->addMonitoringProgram($data[self::FIELD_MONITORING_PROGRAM]);
            } else {
                $this->addMonitoringProgram(new FHIRMedicationKnowledgeMonitoringProgram($data[self::FIELD_MONITORING_PROGRAM]));
            }
        }
        if (isset($data[self::FIELD_ADMINISTRATION_GUIDELINES])) {
            if (is_array($data[self::FIELD_ADMINISTRATION_GUIDELINES])) {
                foreach ($data[self::FIELD_ADMINISTRATION_GUIDELINES] as $v) {
                    if (null === $v) {
                        continue;
                    }
                    if ($v instanceof FHIRMedicationKnowledgeAdministrationGuidelines) {
                        $this->addAdministrationGuidelines($v);
                    } else {
                        $this->addAdministrationGuidelines(new FHIRMedicationKnowledgeAdministrationGuidelines($v));
                    }
                }
            } elseif ($data[self::FIELD_ADMINISTRATION_GUIDELINES] instanceof FHIRMedicationKnowledgeAdministrationGuidelines) {
                $this->addAdministrationGuidelines($data[self::FIELD_ADMINISTRATION_GUIDELINES]);
            } else {
                $this->addAdministrationGuidelines(new FHIRMedicationKnowledgeAdministrationGuidelines($data[self::FIELD_ADMINISTRATION_GUIDELINES]));
            }
        }
        if (isset($data[self::FIELD_MEDICINE_CLASSIFICATION])) {
            if (is_array($data[self::FIELD_MEDICINE_CLASSIFICATION])) {
                foreach ($data[self::FIELD_MEDICINE_CLASSIFICATION] as $v) {
                    if (null === $v) {
                        continue;
                    }
                    if ($v instanceof FHIRMedicationKnowledgeMedicineClassification) {
                        $this->addMedicineClassification($v);
                    } else {
                        $this->addMedicineClassification(new FHIRMedicationKnowledgeMedicineClassification($v));
                    }
                }
            } elseif ($data[self::FIELD_MEDICINE_CLASSIFICATION] instanceof FHIRMedicationKnowledgeMedicineClassification) {
                $this->addMedicineClassification($data[self::FIELD_MEDICINE_CLASSIFICATION]);
            } else {
                $this->addMedicineClassification(new FHIRMedicationKnowledgeMedicineClassification($data[self::FIELD_MEDICINE_CLASSIFICATION]));
            }
        }
        if (isset($data[self::FIELD_PACKAGING])) {
            if ($data[self::FIELD_PACKAGING] instanceof FHIRMedicationKnowledgePackaging) {
                $this->setPackaging($data[self::FIELD_PACKAGING]);
            } else {
                $this->setPackaging(new FHIRMedicationKnowledgePackaging($data[self::FIELD_PACKAGING]));
            }
        }
        if (isset($data[self::FIELD_DRUG_CHARACTERISTIC])) {
            if (is_array($data[self::FIELD_DRUG_CHARACTERISTIC])) {
                foreach ($data[self::FIELD_DRUG_CHARACTERISTIC] as $v) {
                    if (null === $v) {
                        continue;
                    }
                    if ($v instanceof FHIRMedicationKnowledgeDrugCharacteristic) {
                        $this->addDrugCharacteristic($v);
                    } else {
                        $this->addDrugCharacteristic(new FHIRMedicationKnowledgeDrugCharacteristic($v));
                    }
                }
            } elseif ($data[self::FIELD_DRUG_CHARACTERISTIC] instanceof FHIRMedicationKnowledgeDrugCharacteristic) {
                $this->addDrugCharacteristic($data[self::FIELD_DRUG_CHARACTERISTIC]);
            } else {
                $this->addDrugCharacteristic(new FHIRMedicationKnowledgeDrugCharacteristic($data[self::FIELD_DRUG_CHARACTERISTIC]));
            }
        }
        if (isset($data[self::FIELD_CONTRAINDICATION])) {
            if (is_array($data[self::FIELD_CONTRAINDICATION])) {
                foreach ($data[self::FIELD_CONTRAINDICATION] as $v) {
                    if (null === $v) {
                        continue;
                    }
                    if ($v instanceof FHIRReference) {
                        $this->addContraindication($v);
                    } else {
                        $this->addContraindication(new FHIRReference($v));
                    }
                }
            } elseif ($data[self::FIELD_CONTRAINDICATION] instanceof FHIRReference) {
                $this->addContraindication($data[self::FIELD_CONTRAINDICATION]);
            } else {
                $this->addContraindication(new FHIRReference($data[self::FIELD_CONTRAINDICATION]));
            }
        }
        if (isset($data[self::FIELD_REGULATORY])) {
            if (is_array($data[self::FIELD_REGULATORY])) {
                foreach ($data[self::FIELD_REGULATORY] as $v) {
                    if (null === $v) {
                        continue;
                    }
                    if ($v instanceof FHIRMedicationKnowledgeRegulatory) {
                        $this->addRegulatory($v);
                    } else {
                        $this->addRegulatory(new FHIRMedicationKnowledgeRegulatory($v));
                    }
                }
            } elseif ($data[self::FIELD_REGULATORY] instanceof FHIRMedicationKnowledgeRegulatory) {
                $this->addRegulatory($data[self::FIELD_REGULATORY]);
            } else {
                $this->addRegulatory(new FHIRMedicationKnowledgeRegulatory($data[self::FIELD_REGULATORY]));
            }
        }
        if (isset($data[self::FIELD_KINETICS])) {
            if (is_array($data[self::FIELD_KINETICS])) {
                foreach ($data[self::FIELD_KINETICS] as $v) {
                    if (null === $v) {
                        continue;
                    }
                    if ($v instanceof FHIRMedicationKnowledgeKinetics) {
                        $this->addKinetics($v);
                    } else {
                        $this->addKinetics(new FHIRMedicationKnowledgeKinetics($v));
                    }
                }
            } elseif ($data[self::FIELD_KINETICS] instanceof FHIRMedicationKnowledgeKinetics) {
                $this->addKinetics($data[self::FIELD_KINETICS]);
            } else {
                $this->addKinetics(new FHIRMedicationKnowledgeKinetics($data[self::FIELD_KINETICS]));
            }
        }
    }

    /**
     * @return string
     */
    public function _getFHIRTypeName()
    {
        return self::FHIR_TYPE_NAME;
    }

    /**
     * @return string
     */
    public function _getFHIRXMLElementDefinition()
    {
        $xmlns = $this->_getFHIRXMLNamespace();
        if ('' !==  $xmlns) {
            $xmlns = " xmlns=\"{$xmlns}\"";
        }
        return "<MedicationKnowledge{$xmlns}></MedicationKnowledge>";
    }
    /**
     * @return string
     */
    public function _getResourceType()
    {
        return static::FHIR_TYPE_NAME;
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
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getCode()
    {
        return $this->code;
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
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $code
     * @return static
     */
    public function setCode(FHIRCodeableConcept $code = null)
    {
        $this->_trackValueSet($this->code, $code);
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
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCode
     */
    public function getStatus()
    {
        return $this->status;
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
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCode $status
     * @return static
     */
    public function setStatus($status = null)
    {
        if (null !== $status && !($status instanceof FHIRCode)) {
            $status = new FHIRCode($status);
        }
        $this->_trackValueSet($this->status, $status);
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
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getManufacturer()
    {
        return $this->manufacturer;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Describes the details of the manufacturer of the medication product. This is not
     * intended to represent the distributor of a medication product.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference $manufacturer
     * @return static
     */
    public function setManufacturer(FHIRReference $manufacturer = null)
    {
        $this->_trackValueSet($this->manufacturer, $manufacturer);
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
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getDoseForm()
    {
        return $this->doseForm;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Describes the form of the item. Powder; tablets; capsule.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $doseForm
     * @return static
     */
    public function setDoseForm(FHIRCodeableConcept $doseForm = null)
    {
        $this->_trackValueSet($this->doseForm, $doseForm);
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
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public function getAmount()
    {
        return $this->amount;
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
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity $amount
     * @return static
     */
    public function setAmount(FHIRQuantity $amount = null)
    {
        $this->_trackValueSet($this->amount, $amount);
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
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public function getSynonym()
    {
        return $this->synonym;
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
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString $synonym
     * @return static
     */
    public function addSynonym($synonym = null)
    {
        if (null !== $synonym && !($synonym instanceof FHIRString)) {
            $synonym = new FHIRString($synonym);
        }
        $this->_trackValueAdded();
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
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString[] $synonym
     * @return static
     */
    public function setSynonym(array $synonym = [])
    {
        if ([] !== $this->synonym) {
            $this->_trackValuesRemoved(count($this->synonym));
            $this->synonym = [];
        }
        if ([] === $synonym) {
            return $this;
        }
        foreach ($synonym as $v) {
            if ($v instanceof FHIRString) {
                $this->addSynonym($v);
            } else {
                $this->addSynonym(new FHIRString($v));
            }
        }
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Associated or related knowledge about a medication.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeRelatedMedicationKnowledge[]
     */
    public function getRelatedMedicationKnowledge()
    {
        return $this->relatedMedicationKnowledge;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Associated or related knowledge about a medication.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeRelatedMedicationKnowledge $relatedMedicationKnowledge
     * @return static
     */
    public function addRelatedMedicationKnowledge(FHIRMedicationKnowledgeRelatedMedicationKnowledge $relatedMedicationKnowledge = null)
    {
        $this->_trackValueAdded();
        $this->relatedMedicationKnowledge[] = $relatedMedicationKnowledge;
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Associated or related knowledge about a medication.
     *
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeRelatedMedicationKnowledge[] $relatedMedicationKnowledge
     * @return static
     */
    public function setRelatedMedicationKnowledge(array $relatedMedicationKnowledge = [])
    {
        if ([] !== $this->relatedMedicationKnowledge) {
            $this->_trackValuesRemoved(count($this->relatedMedicationKnowledge));
            $this->relatedMedicationKnowledge = [];
        }
        if ([] === $relatedMedicationKnowledge) {
            return $this;
        }
        foreach ($relatedMedicationKnowledge as $v) {
            if ($v instanceof FHIRMedicationKnowledgeRelatedMedicationKnowledge) {
                $this->addRelatedMedicationKnowledge($v);
            } else {
                $this->addRelatedMedicationKnowledge(new FHIRMedicationKnowledgeRelatedMedicationKnowledge($v));
            }
        }
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
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getAssociatedMedication()
    {
        return $this->associatedMedication;
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
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference $associatedMedication
     * @return static
     */
    public function addAssociatedMedication(FHIRReference $associatedMedication = null)
    {
        $this->_trackValueAdded();
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
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[] $associatedMedication
     * @return static
     */
    public function setAssociatedMedication(array $associatedMedication = [])
    {
        if ([] !== $this->associatedMedication) {
            $this->_trackValuesRemoved(count($this->associatedMedication));
            $this->associatedMedication = [];
        }
        if ([] === $associatedMedication) {
            return $this;
        }
        foreach ($associatedMedication as $v) {
            if ($v instanceof FHIRReference) {
                $this->addAssociatedMedication($v);
            } else {
                $this->addAssociatedMedication(new FHIRReference($v));
            }
        }
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
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getProductType()
    {
        return $this->productType;
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
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $productType
     * @return static
     */
    public function addProductType(FHIRCodeableConcept $productType = null)
    {
        $this->_trackValueAdded();
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
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[] $productType
     * @return static
     */
    public function setProductType(array $productType = [])
    {
        if ([] !== $this->productType) {
            $this->_trackValuesRemoved(count($this->productType));
            $this->productType = [];
        }
        if ([] === $productType) {
            return $this;
        }
        foreach ($productType as $v) {
            if ($v instanceof FHIRCodeableConcept) {
                $this->addProductType($v);
            } else {
                $this->addProductType(new FHIRCodeableConcept($v));
            }
        }
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Associated documentation about the medication.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMonograph[]
     */
    public function getMonograph()
    {
        return $this->monograph;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Associated documentation about the medication.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMonograph $monograph
     * @return static
     */
    public function addMonograph(FHIRMedicationKnowledgeMonograph $monograph = null)
    {
        $this->_trackValueAdded();
        $this->monograph[] = $monograph;
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Associated documentation about the medication.
     *
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMonograph[] $monograph
     * @return static
     */
    public function setMonograph(array $monograph = [])
    {
        if ([] !== $this->monograph) {
            $this->_trackValuesRemoved(count($this->monograph));
            $this->monograph = [];
        }
        if ([] === $monograph) {
            return $this;
        }
        foreach ($monograph as $v) {
            if ($v instanceof FHIRMedicationKnowledgeMonograph) {
                $this->addMonograph($v);
            } else {
                $this->addMonograph(new FHIRMedicationKnowledgeMonograph($v));
            }
        }
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Identifies a particular constituent of interest in the product.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeIngredient[]
     */
    public function getIngredient()
    {
        return $this->ingredient;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Identifies a particular constituent of interest in the product.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeIngredient $ingredient
     * @return static
     */
    public function addIngredient(FHIRMedicationKnowledgeIngredient $ingredient = null)
    {
        $this->_trackValueAdded();
        $this->ingredient[] = $ingredient;
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Identifies a particular constituent of interest in the product.
     *
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeIngredient[] $ingredient
     * @return static
     */
    public function setIngredient(array $ingredient = [])
    {
        if ([] !== $this->ingredient) {
            $this->_trackValuesRemoved(count($this->ingredient));
            $this->ingredient = [];
        }
        if ([] === $ingredient) {
            return $this;
        }
        foreach ($ingredient as $v) {
            if ($v instanceof FHIRMedicationKnowledgeIngredient) {
                $this->addIngredient($v);
            } else {
                $this->addIngredient(new FHIRMedicationKnowledgeIngredient($v));
            }
        }
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
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public function getPreparationInstruction()
    {
        return $this->preparationInstruction;
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
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown $preparationInstruction
     * @return static
     */
    public function setPreparationInstruction($preparationInstruction = null)
    {
        if (null !== $preparationInstruction && !($preparationInstruction instanceof FHIRMarkdown)) {
            $preparationInstruction = new FHIRMarkdown($preparationInstruction);
        }
        $this->_trackValueSet($this->preparationInstruction, $preparationInstruction);
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
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getIntendedRoute()
    {
        return $this->intendedRoute;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The intended or approved route of administration.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $intendedRoute
     * @return static
     */
    public function addIntendedRoute(FHIRCodeableConcept $intendedRoute = null)
    {
        $this->_trackValueAdded();
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
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[] $intendedRoute
     * @return static
     */
    public function setIntendedRoute(array $intendedRoute = [])
    {
        if ([] !== $this->intendedRoute) {
            $this->_trackValuesRemoved(count($this->intendedRoute));
            $this->intendedRoute = [];
        }
        if ([] === $intendedRoute) {
            return $this;
        }
        foreach ($intendedRoute as $v) {
            if ($v instanceof FHIRCodeableConcept) {
                $this->addIntendedRoute($v);
            } else {
                $this->addIntendedRoute(new FHIRCodeableConcept($v));
            }
        }
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * The price of the medication.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeCost[]
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * The price of the medication.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeCost $cost
     * @return static
     */
    public function addCost(FHIRMedicationKnowledgeCost $cost = null)
    {
        $this->_trackValueAdded();
        $this->cost[] = $cost;
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * The price of the medication.
     *
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeCost[] $cost
     * @return static
     */
    public function setCost(array $cost = [])
    {
        if ([] !== $this->cost) {
            $this->_trackValuesRemoved(count($this->cost));
            $this->cost = [];
        }
        if ([] === $cost) {
            return $this;
        }
        foreach ($cost as $v) {
            if ($v instanceof FHIRMedicationKnowledgeCost) {
                $this->addCost($v);
            } else {
                $this->addCost(new FHIRMedicationKnowledgeCost($v));
            }
        }
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * The program under which the medication is reviewed.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMonitoringProgram[]
     */
    public function getMonitoringProgram()
    {
        return $this->monitoringProgram;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * The program under which the medication is reviewed.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMonitoringProgram $monitoringProgram
     * @return static
     */
    public function addMonitoringProgram(FHIRMedicationKnowledgeMonitoringProgram $monitoringProgram = null)
    {
        $this->_trackValueAdded();
        $this->monitoringProgram[] = $monitoringProgram;
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * The program under which the medication is reviewed.
     *
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMonitoringProgram[] $monitoringProgram
     * @return static
     */
    public function setMonitoringProgram(array $monitoringProgram = [])
    {
        if ([] !== $this->monitoringProgram) {
            $this->_trackValuesRemoved(count($this->monitoringProgram));
            $this->monitoringProgram = [];
        }
        if ([] === $monitoringProgram) {
            return $this;
        }
        foreach ($monitoringProgram as $v) {
            if ($v instanceof FHIRMedicationKnowledgeMonitoringProgram) {
                $this->addMonitoringProgram($v);
            } else {
                $this->addMonitoringProgram(new FHIRMedicationKnowledgeMonitoringProgram($v));
            }
        }
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Guidelines for the administration of the medication.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeAdministrationGuidelines[]
     */
    public function getAdministrationGuidelines()
    {
        return $this->administrationGuidelines;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Guidelines for the administration of the medication.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeAdministrationGuidelines $administrationGuidelines
     * @return static
     */
    public function addAdministrationGuidelines(FHIRMedicationKnowledgeAdministrationGuidelines $administrationGuidelines = null)
    {
        $this->_trackValueAdded();
        $this->administrationGuidelines[] = $administrationGuidelines;
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Guidelines for the administration of the medication.
     *
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeAdministrationGuidelines[] $administrationGuidelines
     * @return static
     */
    public function setAdministrationGuidelines(array $administrationGuidelines = [])
    {
        if ([] !== $this->administrationGuidelines) {
            $this->_trackValuesRemoved(count($this->administrationGuidelines));
            $this->administrationGuidelines = [];
        }
        if ([] === $administrationGuidelines) {
            return $this;
        }
        foreach ($administrationGuidelines as $v) {
            if ($v instanceof FHIRMedicationKnowledgeAdministrationGuidelines) {
                $this->addAdministrationGuidelines($v);
            } else {
                $this->addAdministrationGuidelines(new FHIRMedicationKnowledgeAdministrationGuidelines($v));
            }
        }
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Categorization of the medication within a formulary or classification system.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMedicineClassification[]
     */
    public function getMedicineClassification()
    {
        return $this->medicineClassification;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Categorization of the medication within a formulary or classification system.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMedicineClassification $medicineClassification
     * @return static
     */
    public function addMedicineClassification(FHIRMedicationKnowledgeMedicineClassification $medicineClassification = null)
    {
        $this->_trackValueAdded();
        $this->medicineClassification[] = $medicineClassification;
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Categorization of the medication within a formulary or classification system.
     *
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMedicineClassification[] $medicineClassification
     * @return static
     */
    public function setMedicineClassification(array $medicineClassification = [])
    {
        if ([] !== $this->medicineClassification) {
            $this->_trackValuesRemoved(count($this->medicineClassification));
            $this->medicineClassification = [];
        }
        if ([] === $medicineClassification) {
            return $this;
        }
        foreach ($medicineClassification as $v) {
            if ($v instanceof FHIRMedicationKnowledgeMedicineClassification) {
                $this->addMedicineClassification($v);
            } else {
                $this->addMedicineClassification(new FHIRMedicationKnowledgeMedicineClassification($v));
            }
        }
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Information that only applies to packages (not products).
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgePackaging
     */
    public function getPackaging()
    {
        return $this->packaging;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Information that only applies to packages (not products).
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgePackaging $packaging
     * @return static
     */
    public function setPackaging(FHIRMedicationKnowledgePackaging $packaging = null)
    {
        $this->_trackValueSet($this->packaging, $packaging);
        $this->packaging = $packaging;
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Specifies descriptive properties of the medicine, such as color, shape,
     * imprints, etc.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeDrugCharacteristic[]
     */
    public function getDrugCharacteristic()
    {
        return $this->drugCharacteristic;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Specifies descriptive properties of the medicine, such as color, shape,
     * imprints, etc.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeDrugCharacteristic $drugCharacteristic
     * @return static
     */
    public function addDrugCharacteristic(FHIRMedicationKnowledgeDrugCharacteristic $drugCharacteristic = null)
    {
        $this->_trackValueAdded();
        $this->drugCharacteristic[] = $drugCharacteristic;
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Specifies descriptive properties of the medicine, such as color, shape,
     * imprints, etc.
     *
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeDrugCharacteristic[] $drugCharacteristic
     * @return static
     */
    public function setDrugCharacteristic(array $drugCharacteristic = [])
    {
        if ([] !== $this->drugCharacteristic) {
            $this->_trackValuesRemoved(count($this->drugCharacteristic));
            $this->drugCharacteristic = [];
        }
        if ([] === $drugCharacteristic) {
            return $this;
        }
        foreach ($drugCharacteristic as $v) {
            if ($v instanceof FHIRMedicationKnowledgeDrugCharacteristic) {
                $this->addDrugCharacteristic($v);
            } else {
                $this->addDrugCharacteristic(new FHIRMedicationKnowledgeDrugCharacteristic($v));
            }
        }
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
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getContraindication()
    {
        return $this->contraindication;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Potential clinical issue with or between medication(s) (for example, drug-drug
     * interaction, drug-disease contraindication, drug-allergy interaction, etc.).
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference $contraindication
     * @return static
     */
    public function addContraindication(FHIRReference $contraindication = null)
    {
        $this->_trackValueAdded();
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
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[] $contraindication
     * @return static
     */
    public function setContraindication(array $contraindication = [])
    {
        if ([] !== $this->contraindication) {
            $this->_trackValuesRemoved(count($this->contraindication));
            $this->contraindication = [];
        }
        if ([] === $contraindication) {
            return $this;
        }
        foreach ($contraindication as $v) {
            if ($v instanceof FHIRReference) {
                $this->addContraindication($v);
            } else {
                $this->addContraindication(new FHIRReference($v));
            }
        }
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Regulatory information about a medication.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeRegulatory[]
     */
    public function getRegulatory()
    {
        return $this->regulatory;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Regulatory information about a medication.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeRegulatory $regulatory
     * @return static
     */
    public function addRegulatory(FHIRMedicationKnowledgeRegulatory $regulatory = null)
    {
        $this->_trackValueAdded();
        $this->regulatory[] = $regulatory;
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Regulatory information about a medication.
     *
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeRegulatory[] $regulatory
     * @return static
     */
    public function setRegulatory(array $regulatory = [])
    {
        if ([] !== $this->regulatory) {
            $this->_trackValuesRemoved(count($this->regulatory));
            $this->regulatory = [];
        }
        if ([] === $regulatory) {
            return $this;
        }
        foreach ($regulatory as $v) {
            if ($v instanceof FHIRMedicationKnowledgeRegulatory) {
                $this->addRegulatory($v);
            } else {
                $this->addRegulatory(new FHIRMedicationKnowledgeRegulatory($v));
            }
        }
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * The time course of drug absorption, distribution, metabolism and excretion of a
     * medication from the body.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeKinetics[]
     */
    public function getKinetics()
    {
        return $this->kinetics;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * The time course of drug absorption, distribution, metabolism and excretion of a
     * medication from the body.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeKinetics $kinetics
     * @return static
     */
    public function addKinetics(FHIRMedicationKnowledgeKinetics $kinetics = null)
    {
        $this->_trackValueAdded();
        $this->kinetics[] = $kinetics;
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * The time course of drug absorption, distribution, metabolism and excretion of a
     * medication from the body.
     *
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeKinetics[] $kinetics
     * @return static
     */
    public function setKinetics(array $kinetics = [])
    {
        if ([] !== $this->kinetics) {
            $this->_trackValuesRemoved(count($this->kinetics));
            $this->kinetics = [];
        }
        if ([] === $kinetics) {
            return $this;
        }
        foreach ($kinetics as $v) {
            if ($v instanceof FHIRMedicationKnowledgeKinetics) {
                $this->addKinetics($v);
            } else {
                $this->addKinetics(new FHIRMedicationKnowledgeKinetics($v));
            }
        }
        return $this;
    }

    /**
     * Returns the validation rules that this type's fields must comply with to be considered "valid"
     * The returned array is in ["fieldname[.offset]" => ["rule" => {constraint}]]
     *
     * @return array
     */
    public function _getValidationRules()
    {
        return self::$_validationRules;
    }

    /**
     * Validates that this type conforms to the specifications set forth for it by FHIR.  An empty array must be seen as
     * passing.
     *
     * @return array
     */
    public function _getValidationErrors()
    {
        $errs = parent::_getValidationErrors();
        $validationRules = $this->_getValidationRules();
        if (null !== ($v = $this->getCode())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_CODE] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getStatus())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_STATUS] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getManufacturer())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_MANUFACTURER] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getDoseForm())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_DOSE_FORM] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getAmount())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_AMOUNT] = $fieldErrs;
            }
        }
        if ([] !== ($vs = $this->getSynonym())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_SYNONYM, $i)] = $fieldErrs;
                }
            }
        }
        if ([] !== ($vs = $this->getRelatedMedicationKnowledge())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_RELATED_MEDICATION_KNOWLEDGE, $i)] = $fieldErrs;
                }
            }
        }
        if ([] !== ($vs = $this->getAssociatedMedication())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_ASSOCIATED_MEDICATION, $i)] = $fieldErrs;
                }
            }
        }
        if ([] !== ($vs = $this->getProductType())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_PRODUCT_TYPE, $i)] = $fieldErrs;
                }
            }
        }
        if ([] !== ($vs = $this->getMonograph())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_MONOGRAPH, $i)] = $fieldErrs;
                }
            }
        }
        if ([] !== ($vs = $this->getIngredient())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_INGREDIENT, $i)] = $fieldErrs;
                }
            }
        }
        if (null !== ($v = $this->getPreparationInstruction())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_PREPARATION_INSTRUCTION] = $fieldErrs;
            }
        }
        if ([] !== ($vs = $this->getIntendedRoute())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_INTENDED_ROUTE, $i)] = $fieldErrs;
                }
            }
        }
        if ([] !== ($vs = $this->getCost())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_COST, $i)] = $fieldErrs;
                }
            }
        }
        if ([] !== ($vs = $this->getMonitoringProgram())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_MONITORING_PROGRAM, $i)] = $fieldErrs;
                }
            }
        }
        if ([] !== ($vs = $this->getAdministrationGuidelines())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_ADMINISTRATION_GUIDELINES, $i)] = $fieldErrs;
                }
            }
        }
        if ([] !== ($vs = $this->getMedicineClassification())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_MEDICINE_CLASSIFICATION, $i)] = $fieldErrs;
                }
            }
        }
        if (null !== ($v = $this->getPackaging())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_PACKAGING] = $fieldErrs;
            }
        }
        if ([] !== ($vs = $this->getDrugCharacteristic())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_DRUG_CHARACTERISTIC, $i)] = $fieldErrs;
                }
            }
        }
        if ([] !== ($vs = $this->getContraindication())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_CONTRAINDICATION, $i)] = $fieldErrs;
                }
            }
        }
        if ([] !== ($vs = $this->getRegulatory())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_REGULATORY, $i)] = $fieldErrs;
                }
            }
        }
        if ([] !== ($vs = $this->getKinetics())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_KINETICS, $i)] = $fieldErrs;
                }
            }
        }
        if (isset($validationRules[self::FIELD_CODE])) {
            $v = $this->getCode();
            foreach ($validationRules[self::FIELD_CODE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MEDICATION_KNOWLEDGE, self::FIELD_CODE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_CODE])) {
                        $errs[self::FIELD_CODE] = [];
                    }
                    $errs[self::FIELD_CODE][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_STATUS])) {
            $v = $this->getStatus();
            foreach ($validationRules[self::FIELD_STATUS] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MEDICATION_KNOWLEDGE, self::FIELD_STATUS, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_STATUS])) {
                        $errs[self::FIELD_STATUS] = [];
                    }
                    $errs[self::FIELD_STATUS][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_MANUFACTURER])) {
            $v = $this->getManufacturer();
            foreach ($validationRules[self::FIELD_MANUFACTURER] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MEDICATION_KNOWLEDGE, self::FIELD_MANUFACTURER, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_MANUFACTURER])) {
                        $errs[self::FIELD_MANUFACTURER] = [];
                    }
                    $errs[self::FIELD_MANUFACTURER][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_DOSE_FORM])) {
            $v = $this->getDoseForm();
            foreach ($validationRules[self::FIELD_DOSE_FORM] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MEDICATION_KNOWLEDGE, self::FIELD_DOSE_FORM, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_DOSE_FORM])) {
                        $errs[self::FIELD_DOSE_FORM] = [];
                    }
                    $errs[self::FIELD_DOSE_FORM][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_AMOUNT])) {
            $v = $this->getAmount();
            foreach ($validationRules[self::FIELD_AMOUNT] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MEDICATION_KNOWLEDGE, self::FIELD_AMOUNT, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_AMOUNT])) {
                        $errs[self::FIELD_AMOUNT] = [];
                    }
                    $errs[self::FIELD_AMOUNT][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_SYNONYM])) {
            $v = $this->getSynonym();
            foreach ($validationRules[self::FIELD_SYNONYM] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MEDICATION_KNOWLEDGE, self::FIELD_SYNONYM, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_SYNONYM])) {
                        $errs[self::FIELD_SYNONYM] = [];
                    }
                    $errs[self::FIELD_SYNONYM][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_RELATED_MEDICATION_KNOWLEDGE])) {
            $v = $this->getRelatedMedicationKnowledge();
            foreach ($validationRules[self::FIELD_RELATED_MEDICATION_KNOWLEDGE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MEDICATION_KNOWLEDGE, self::FIELD_RELATED_MEDICATION_KNOWLEDGE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_RELATED_MEDICATION_KNOWLEDGE])) {
                        $errs[self::FIELD_RELATED_MEDICATION_KNOWLEDGE] = [];
                    }
                    $errs[self::FIELD_RELATED_MEDICATION_KNOWLEDGE][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_ASSOCIATED_MEDICATION])) {
            $v = $this->getAssociatedMedication();
            foreach ($validationRules[self::FIELD_ASSOCIATED_MEDICATION] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MEDICATION_KNOWLEDGE, self::FIELD_ASSOCIATED_MEDICATION, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_ASSOCIATED_MEDICATION])) {
                        $errs[self::FIELD_ASSOCIATED_MEDICATION] = [];
                    }
                    $errs[self::FIELD_ASSOCIATED_MEDICATION][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_PRODUCT_TYPE])) {
            $v = $this->getProductType();
            foreach ($validationRules[self::FIELD_PRODUCT_TYPE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MEDICATION_KNOWLEDGE, self::FIELD_PRODUCT_TYPE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_PRODUCT_TYPE])) {
                        $errs[self::FIELD_PRODUCT_TYPE] = [];
                    }
                    $errs[self::FIELD_PRODUCT_TYPE][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_MONOGRAPH])) {
            $v = $this->getMonograph();
            foreach ($validationRules[self::FIELD_MONOGRAPH] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MEDICATION_KNOWLEDGE, self::FIELD_MONOGRAPH, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_MONOGRAPH])) {
                        $errs[self::FIELD_MONOGRAPH] = [];
                    }
                    $errs[self::FIELD_MONOGRAPH][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_INGREDIENT])) {
            $v = $this->getIngredient();
            foreach ($validationRules[self::FIELD_INGREDIENT] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MEDICATION_KNOWLEDGE, self::FIELD_INGREDIENT, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_INGREDIENT])) {
                        $errs[self::FIELD_INGREDIENT] = [];
                    }
                    $errs[self::FIELD_INGREDIENT][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_PREPARATION_INSTRUCTION])) {
            $v = $this->getPreparationInstruction();
            foreach ($validationRules[self::FIELD_PREPARATION_INSTRUCTION] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MEDICATION_KNOWLEDGE, self::FIELD_PREPARATION_INSTRUCTION, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_PREPARATION_INSTRUCTION])) {
                        $errs[self::FIELD_PREPARATION_INSTRUCTION] = [];
                    }
                    $errs[self::FIELD_PREPARATION_INSTRUCTION][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_INTENDED_ROUTE])) {
            $v = $this->getIntendedRoute();
            foreach ($validationRules[self::FIELD_INTENDED_ROUTE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MEDICATION_KNOWLEDGE, self::FIELD_INTENDED_ROUTE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_INTENDED_ROUTE])) {
                        $errs[self::FIELD_INTENDED_ROUTE] = [];
                    }
                    $errs[self::FIELD_INTENDED_ROUTE][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_COST])) {
            $v = $this->getCost();
            foreach ($validationRules[self::FIELD_COST] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MEDICATION_KNOWLEDGE, self::FIELD_COST, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_COST])) {
                        $errs[self::FIELD_COST] = [];
                    }
                    $errs[self::FIELD_COST][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_MONITORING_PROGRAM])) {
            $v = $this->getMonitoringProgram();
            foreach ($validationRules[self::FIELD_MONITORING_PROGRAM] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MEDICATION_KNOWLEDGE, self::FIELD_MONITORING_PROGRAM, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_MONITORING_PROGRAM])) {
                        $errs[self::FIELD_MONITORING_PROGRAM] = [];
                    }
                    $errs[self::FIELD_MONITORING_PROGRAM][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_ADMINISTRATION_GUIDELINES])) {
            $v = $this->getAdministrationGuidelines();
            foreach ($validationRules[self::FIELD_ADMINISTRATION_GUIDELINES] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MEDICATION_KNOWLEDGE, self::FIELD_ADMINISTRATION_GUIDELINES, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_ADMINISTRATION_GUIDELINES])) {
                        $errs[self::FIELD_ADMINISTRATION_GUIDELINES] = [];
                    }
                    $errs[self::FIELD_ADMINISTRATION_GUIDELINES][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_MEDICINE_CLASSIFICATION])) {
            $v = $this->getMedicineClassification();
            foreach ($validationRules[self::FIELD_MEDICINE_CLASSIFICATION] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MEDICATION_KNOWLEDGE, self::FIELD_MEDICINE_CLASSIFICATION, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_MEDICINE_CLASSIFICATION])) {
                        $errs[self::FIELD_MEDICINE_CLASSIFICATION] = [];
                    }
                    $errs[self::FIELD_MEDICINE_CLASSIFICATION][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_PACKAGING])) {
            $v = $this->getPackaging();
            foreach ($validationRules[self::FIELD_PACKAGING] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MEDICATION_KNOWLEDGE, self::FIELD_PACKAGING, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_PACKAGING])) {
                        $errs[self::FIELD_PACKAGING] = [];
                    }
                    $errs[self::FIELD_PACKAGING][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_DRUG_CHARACTERISTIC])) {
            $v = $this->getDrugCharacteristic();
            foreach ($validationRules[self::FIELD_DRUG_CHARACTERISTIC] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MEDICATION_KNOWLEDGE, self::FIELD_DRUG_CHARACTERISTIC, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_DRUG_CHARACTERISTIC])) {
                        $errs[self::FIELD_DRUG_CHARACTERISTIC] = [];
                    }
                    $errs[self::FIELD_DRUG_CHARACTERISTIC][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_CONTRAINDICATION])) {
            $v = $this->getContraindication();
            foreach ($validationRules[self::FIELD_CONTRAINDICATION] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MEDICATION_KNOWLEDGE, self::FIELD_CONTRAINDICATION, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_CONTRAINDICATION])) {
                        $errs[self::FIELD_CONTRAINDICATION] = [];
                    }
                    $errs[self::FIELD_CONTRAINDICATION][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_REGULATORY])) {
            $v = $this->getRegulatory();
            foreach ($validationRules[self::FIELD_REGULATORY] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MEDICATION_KNOWLEDGE, self::FIELD_REGULATORY, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_REGULATORY])) {
                        $errs[self::FIELD_REGULATORY] = [];
                    }
                    $errs[self::FIELD_REGULATORY][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_KINETICS])) {
            $v = $this->getKinetics();
            foreach ($validationRules[self::FIELD_KINETICS] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MEDICATION_KNOWLEDGE, self::FIELD_KINETICS, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_KINETICS])) {
                        $errs[self::FIELD_KINETICS] = [];
                    }
                    $errs[self::FIELD_KINETICS][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_TEXT])) {
            $v = $this->getText();
            foreach ($validationRules[self::FIELD_TEXT] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_DOMAIN_RESOURCE, self::FIELD_TEXT, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_TEXT])) {
                        $errs[self::FIELD_TEXT] = [];
                    }
                    $errs[self::FIELD_TEXT][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_CONTAINED])) {
            $v = $this->getContained();
            foreach ($validationRules[self::FIELD_CONTAINED] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_DOMAIN_RESOURCE, self::FIELD_CONTAINED, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_CONTAINED])) {
                        $errs[self::FIELD_CONTAINED] = [];
                    }
                    $errs[self::FIELD_CONTAINED][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_EXTENSION])) {
            $v = $this->getExtension();
            foreach ($validationRules[self::FIELD_EXTENSION] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_DOMAIN_RESOURCE, self::FIELD_EXTENSION, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_EXTENSION])) {
                        $errs[self::FIELD_EXTENSION] = [];
                    }
                    $errs[self::FIELD_EXTENSION][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_MODIFIER_EXTENSION])) {
            $v = $this->getModifierExtension();
            foreach ($validationRules[self::FIELD_MODIFIER_EXTENSION] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_DOMAIN_RESOURCE, self::FIELD_MODIFIER_EXTENSION, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_MODIFIER_EXTENSION])) {
                        $errs[self::FIELD_MODIFIER_EXTENSION] = [];
                    }
                    $errs[self::FIELD_MODIFIER_EXTENSION][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_ID])) {
            $v = $this->getId();
            foreach ($validationRules[self::FIELD_ID] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RESOURCE, self::FIELD_ID, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_ID])) {
                        $errs[self::FIELD_ID] = [];
                    }
                    $errs[self::FIELD_ID][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_META])) {
            $v = $this->getMeta();
            foreach ($validationRules[self::FIELD_META] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RESOURCE, self::FIELD_META, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_META])) {
                        $errs[self::FIELD_META] = [];
                    }
                    $errs[self::FIELD_META][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_IMPLICIT_RULES])) {
            $v = $this->getImplicitRules();
            foreach ($validationRules[self::FIELD_IMPLICIT_RULES] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RESOURCE, self::FIELD_IMPLICIT_RULES, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_IMPLICIT_RULES])) {
                        $errs[self::FIELD_IMPLICIT_RULES] = [];
                    }
                    $errs[self::FIELD_IMPLICIT_RULES][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_LANGUAGE])) {
            $v = $this->getLanguage();
            foreach ($validationRules[self::FIELD_LANGUAGE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RESOURCE, self::FIELD_LANGUAGE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_LANGUAGE])) {
                        $errs[self::FIELD_LANGUAGE] = [];
                    }
                    $errs[self::FIELD_LANGUAGE][$rule] = $err;
                }
            }
        }
        return $errs;
    }

    /**
     * @param null|string|\DOMElement $element
     * @param null|\OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource\FHIRMedicationKnowledge $type
     * @param null|int $libxmlOpts
     * @return null|\OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource\FHIRMedicationKnowledge
     */
    public static function xmlUnserialize($element = null, PHPFHIRTypeInterface $type = null, $libxmlOpts = 591872)
    {
        if (null === $element) {
            return null;
        }
        if (is_string($element)) {
            libxml_use_internal_errors(true);
            $dom = new \DOMDocument();
            $dom->loadXML($element, $libxmlOpts);
            if (false === $dom) {
                throw new \DomainException(sprintf('FHIRMedicationKnowledge::xmlUnserialize - String provided is not parseable as XML: %s', implode(', ', array_map(function (\libXMLError $err) {
                    return $err->message;
                }, libxml_get_errors()))));
            }
            libxml_use_internal_errors(false);
            $element = $dom->documentElement;
        }
        if (!($element instanceof \DOMElement)) {
            throw new \InvalidArgumentException(sprintf('FHIRMedicationKnowledge::xmlUnserialize - $node value must be null, \\DOMElement, or valid XML string, %s seen', is_object($element) ? get_class($element) : gettype($element)));
        }
        if (null === $type) {
            $type = new FHIRMedicationKnowledge(null);
        } elseif (!is_object($type) || !($type instanceof FHIRMedicationKnowledge)) {
            throw new \RuntimeException(sprintf(
                'FHIRMedicationKnowledge::xmlUnserialize - $type must be instance of \OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource\FHIRMedicationKnowledge or null, %s seen.',
                is_object($type) ? get_class($type) : gettype($type)
            ));
        }
        if ('' === $type->_getFHIRXMLNamespace() && (null === $element->parentNode || $element->namespaceURI !== $element->parentNode->namespaceURI)) {
            $type->_setFHIRXMLNamespace($element->namespaceURI);
        }
        for ($i = 0; $i < $element->childNodes->length; $i++) {
            $n = $element->childNodes->item($i);
            if (!($n instanceof \DOMElement)) {
                continue;
            }
            if (self::FIELD_CODE === $n->nodeName) {
                $type->setCode(FHIRCodeableConcept::xmlUnserialize($n));
            } elseif (self::FIELD_STATUS === $n->nodeName) {
                $type->setStatus(FHIRCode::xmlUnserialize($n));
            } elseif (self::FIELD_MANUFACTURER === $n->nodeName) {
                $type->setManufacturer(FHIRReference::xmlUnserialize($n));
            } elseif (self::FIELD_DOSE_FORM === $n->nodeName) {
                $type->setDoseForm(FHIRCodeableConcept::xmlUnserialize($n));
            } elseif (self::FIELD_AMOUNT === $n->nodeName) {
                $type->setAmount(FHIRQuantity::xmlUnserialize($n));
            } elseif (self::FIELD_SYNONYM === $n->nodeName) {
                $type->addSynonym(FHIRString::xmlUnserialize($n));
            } elseif (self::FIELD_RELATED_MEDICATION_KNOWLEDGE === $n->nodeName) {
                $type->addRelatedMedicationKnowledge(FHIRMedicationKnowledgeRelatedMedicationKnowledge::xmlUnserialize($n));
            } elseif (self::FIELD_ASSOCIATED_MEDICATION === $n->nodeName) {
                $type->addAssociatedMedication(FHIRReference::xmlUnserialize($n));
            } elseif (self::FIELD_PRODUCT_TYPE === $n->nodeName) {
                $type->addProductType(FHIRCodeableConcept::xmlUnserialize($n));
            } elseif (self::FIELD_MONOGRAPH === $n->nodeName) {
                $type->addMonograph(FHIRMedicationKnowledgeMonograph::xmlUnserialize($n));
            } elseif (self::FIELD_INGREDIENT === $n->nodeName) {
                $type->addIngredient(FHIRMedicationKnowledgeIngredient::xmlUnserialize($n));
            } elseif (self::FIELD_PREPARATION_INSTRUCTION === $n->nodeName) {
                $type->setPreparationInstruction(FHIRMarkdown::xmlUnserialize($n));
            } elseif (self::FIELD_INTENDED_ROUTE === $n->nodeName) {
                $type->addIntendedRoute(FHIRCodeableConcept::xmlUnserialize($n));
            } elseif (self::FIELD_COST === $n->nodeName) {
                $type->addCost(FHIRMedicationKnowledgeCost::xmlUnserialize($n));
            } elseif (self::FIELD_MONITORING_PROGRAM === $n->nodeName) {
                $type->addMonitoringProgram(FHIRMedicationKnowledgeMonitoringProgram::xmlUnserialize($n));
            } elseif (self::FIELD_ADMINISTRATION_GUIDELINES === $n->nodeName) {
                $type->addAdministrationGuidelines(FHIRMedicationKnowledgeAdministrationGuidelines::xmlUnserialize($n));
            } elseif (self::FIELD_MEDICINE_CLASSIFICATION === $n->nodeName) {
                $type->addMedicineClassification(FHIRMedicationKnowledgeMedicineClassification::xmlUnserialize($n));
            } elseif (self::FIELD_PACKAGING === $n->nodeName) {
                $type->setPackaging(FHIRMedicationKnowledgePackaging::xmlUnserialize($n));
            } elseif (self::FIELD_DRUG_CHARACTERISTIC === $n->nodeName) {
                $type->addDrugCharacteristic(FHIRMedicationKnowledgeDrugCharacteristic::xmlUnserialize($n));
            } elseif (self::FIELD_CONTRAINDICATION === $n->nodeName) {
                $type->addContraindication(FHIRReference::xmlUnserialize($n));
            } elseif (self::FIELD_REGULATORY === $n->nodeName) {
                $type->addRegulatory(FHIRMedicationKnowledgeRegulatory::xmlUnserialize($n));
            } elseif (self::FIELD_KINETICS === $n->nodeName) {
                $type->addKinetics(FHIRMedicationKnowledgeKinetics::xmlUnserialize($n));
            } elseif (self::FIELD_TEXT === $n->nodeName) {
                $type->setText(FHIRNarrative::xmlUnserialize($n));
            } elseif (self::FIELD_CONTAINED === $n->nodeName) {
                for ($ni = 0; $ni < $n->childNodes->length; $ni++) {
                    $nn = $n->childNodes->item($ni);
                    if ($nn instanceof \DOMElement) {
                        $type->addContained(PHPFHIRTypeMap::getContainedTypeFromXML($nn));
                    }
                }
            } elseif (self::FIELD_EXTENSION === $n->nodeName) {
                $type->addExtension(FHIRExtension::xmlUnserialize($n));
            } elseif (self::FIELD_MODIFIER_EXTENSION === $n->nodeName) {
                $type->addModifierExtension(FHIRExtension::xmlUnserialize($n));
            } elseif (self::FIELD_ID === $n->nodeName) {
                $type->setId(FHIRId::xmlUnserialize($n));
            } elseif (self::FIELD_META === $n->nodeName) {
                $type->setMeta(FHIRMeta::xmlUnserialize($n));
            } elseif (self::FIELD_IMPLICIT_RULES === $n->nodeName) {
                $type->setImplicitRules(FHIRUri::xmlUnserialize($n));
            } elseif (self::FIELD_LANGUAGE === $n->nodeName) {
                $type->setLanguage(FHIRCode::xmlUnserialize($n));
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_STATUS);
        if (null !== $n) {
            $pt = $type->getStatus();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setStatus($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_SYNONYM);
        if (null !== $n) {
            $pt = $type->getSynonym();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->addSynonym($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_PREPARATION_INSTRUCTION);
        if (null !== $n) {
            $pt = $type->getPreparationInstruction();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setPreparationInstruction($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_ID);
        if (null !== $n) {
            $pt = $type->getId();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setId($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_IMPLICIT_RULES);
        if (null !== $n) {
            $pt = $type->getImplicitRules();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setImplicitRules($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_LANGUAGE);
        if (null !== $n) {
            $pt = $type->getLanguage();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setLanguage($n->nodeValue);
            }
        }
        return $type;
    }

    /**
     * @param null|\DOMElement $element
     * @param null|int $libxmlOpts
     * @return \DOMElement
     */
    public function xmlSerialize(\DOMElement $element = null, $libxmlOpts = 591872)
    {
        if (null === $element) {
            $dom = new \DOMDocument();
            $dom->loadXML($this->_getFHIRXMLElementDefinition(), $libxmlOpts);
            $element = $dom->documentElement;
        } elseif (null === $element->namespaceURI && '' !== ($xmlns = $this->_getFHIRXMLNamespace())) {
            $element->setAttribute('xmlns', $xmlns);
        }
        parent::xmlSerialize($element);
        if (null !== ($v = $this->getCode())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_CODE);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getStatus())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_STATUS);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getManufacturer())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_MANUFACTURER);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getDoseForm())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_DOSE_FORM);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getAmount())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_AMOUNT);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if ([] !== ($vs = $this->getSynonym())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_SYNONYM);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if ([] !== ($vs = $this->getRelatedMedicationKnowledge())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_RELATED_MEDICATION_KNOWLEDGE);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if ([] !== ($vs = $this->getAssociatedMedication())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_ASSOCIATED_MEDICATION);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if ([] !== ($vs = $this->getProductType())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_PRODUCT_TYPE);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if ([] !== ($vs = $this->getMonograph())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_MONOGRAPH);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if ([] !== ($vs = $this->getIngredient())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_INGREDIENT);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if (null !== ($v = $this->getPreparationInstruction())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_PREPARATION_INSTRUCTION);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if ([] !== ($vs = $this->getIntendedRoute())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_INTENDED_ROUTE);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if ([] !== ($vs = $this->getCost())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_COST);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if ([] !== ($vs = $this->getMonitoringProgram())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_MONITORING_PROGRAM);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if ([] !== ($vs = $this->getAdministrationGuidelines())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_ADMINISTRATION_GUIDELINES);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if ([] !== ($vs = $this->getMedicineClassification())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_MEDICINE_CLASSIFICATION);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if (null !== ($v = $this->getPackaging())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_PACKAGING);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if ([] !== ($vs = $this->getDrugCharacteristic())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_DRUG_CHARACTERISTIC);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if ([] !== ($vs = $this->getContraindication())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_CONTRAINDICATION);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if ([] !== ($vs = $this->getRegulatory())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_REGULATORY);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if ([] !== ($vs = $this->getKinetics())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_KINETICS);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        return $element;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $a = parent::jsonSerialize();
        if (null !== ($v = $this->getCode())) {
            $a[self::FIELD_CODE] = $v;
        }
        if (null !== ($v = $this->getStatus())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_STATUS] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRCode::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_STATUS_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getManufacturer())) {
            $a[self::FIELD_MANUFACTURER] = $v;
        }
        if (null !== ($v = $this->getDoseForm())) {
            $a[self::FIELD_DOSE_FORM] = $v;
        }
        if (null !== ($v = $this->getAmount())) {
            $a[self::FIELD_AMOUNT] = $v;
        }
        if ([] !== ($vs = $this->getSynonym())) {
            $vals = [];
            $exts = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $val = $v->getValue();
                $ext = $v->jsonSerialize();
                unset($ext[FHIRString::FIELD_VALUE]);
                if (null !== $val) {
                    $vals[] = $val;
                }
                if ([] !== $ext) {
                    $exts[] = $ext;
                }
            }
            if ([] !== $vals) {
                $a[self::FIELD_SYNONYM] = $vals;
            }
            if ([] !== $exts) {
                $a[self::FIELD_SYNONYM_EXT] = $exts;
            }
        }
        if ([] !== ($vs = $this->getRelatedMedicationKnowledge())) {
            $a[self::FIELD_RELATED_MEDICATION_KNOWLEDGE] = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $a[self::FIELD_RELATED_MEDICATION_KNOWLEDGE][] = $v;
            }
        }
        if ([] !== ($vs = $this->getAssociatedMedication())) {
            $a[self::FIELD_ASSOCIATED_MEDICATION] = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $a[self::FIELD_ASSOCIATED_MEDICATION][] = $v;
            }
        }
        if ([] !== ($vs = $this->getProductType())) {
            $a[self::FIELD_PRODUCT_TYPE] = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $a[self::FIELD_PRODUCT_TYPE][] = $v;
            }
        }
        if ([] !== ($vs = $this->getMonograph())) {
            $a[self::FIELD_MONOGRAPH] = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $a[self::FIELD_MONOGRAPH][] = $v;
            }
        }
        if ([] !== ($vs = $this->getIngredient())) {
            $a[self::FIELD_INGREDIENT] = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $a[self::FIELD_INGREDIENT][] = $v;
            }
        }
        if (null !== ($v = $this->getPreparationInstruction())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_PREPARATION_INSTRUCTION] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRMarkdown::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_PREPARATION_INSTRUCTION_EXT] = $ext;
            }
        }
        if ([] !== ($vs = $this->getIntendedRoute())) {
            $a[self::FIELD_INTENDED_ROUTE] = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $a[self::FIELD_INTENDED_ROUTE][] = $v;
            }
        }
        if ([] !== ($vs = $this->getCost())) {
            $a[self::FIELD_COST] = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $a[self::FIELD_COST][] = $v;
            }
        }
        if ([] !== ($vs = $this->getMonitoringProgram())) {
            $a[self::FIELD_MONITORING_PROGRAM] = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $a[self::FIELD_MONITORING_PROGRAM][] = $v;
            }
        }
        if ([] !== ($vs = $this->getAdministrationGuidelines())) {
            $a[self::FIELD_ADMINISTRATION_GUIDELINES] = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $a[self::FIELD_ADMINISTRATION_GUIDELINES][] = $v;
            }
        }
        if ([] !== ($vs = $this->getMedicineClassification())) {
            $a[self::FIELD_MEDICINE_CLASSIFICATION] = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $a[self::FIELD_MEDICINE_CLASSIFICATION][] = $v;
            }
        }
        if (null !== ($v = $this->getPackaging())) {
            $a[self::FIELD_PACKAGING] = $v;
        }
        if ([] !== ($vs = $this->getDrugCharacteristic())) {
            $a[self::FIELD_DRUG_CHARACTERISTIC] = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $a[self::FIELD_DRUG_CHARACTERISTIC][] = $v;
            }
        }
        if ([] !== ($vs = $this->getContraindication())) {
            $a[self::FIELD_CONTRAINDICATION] = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $a[self::FIELD_CONTRAINDICATION][] = $v;
            }
        }
        if ([] !== ($vs = $this->getRegulatory())) {
            $a[self::FIELD_REGULATORY] = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $a[self::FIELD_REGULATORY][] = $v;
            }
        }
        if ([] !== ($vs = $this->getKinetics())) {
            $a[self::FIELD_KINETICS] = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $a[self::FIELD_KINETICS][] = $v;
            }
        }
        return [PHPFHIRConstants::JSON_FIELD_RESOURCE_TYPE => $this->_getResourceType()] + $a;
    }


    /**
     * @return string
     */
    public function __toString()
    {
        return self::FHIR_TYPE_NAME;
    }
}
