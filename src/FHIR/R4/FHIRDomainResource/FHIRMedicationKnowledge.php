<?php

namespace OpenEMR\FHIR\R4\FHIRDomainResource;

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

use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;

/**
 * Information about a medication that is used to support knowledge.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRMedicationKnowledge extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * A code that specifies this medication, or a textual description if no code is available. Usage note: This could be a standard medication code such as a code from RxNorm, SNOMED CT, IDMP etc. It could also be a national or local formulary code, optionally with translations to other code systems.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $code = null;

    /**
     * A code to indicate if the medication is in active use.  The status refers to the validity about the information of the medication and not to its medicinal properties.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCode
     */
    public $status = null;

    /**
     * Describes the details of the manufacturer of the medication product.  This is not intended to represent the distributor of a medication product.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $manufacturer = null;

    /**
     * Describes the form of the item.  Powder; tablets; capsule.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $doseForm = null;

    /**
     * Specific amount of the drug in the packaged product.  For example, when specifying a product that has the same strength (For example, Insulin glargine 100 unit per mL solution for injection), this attribute provides additional clarification of the package amount (For example, 3 mL, 10mL, etc.).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public $amount = null;

    /**
     * Additional names for a medication, for example, the name(s) given to a medication in different countries.  For example, acetaminophen and paracetamol or salbutamol and albuterol.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public $synonym = [];

    /**
     * Associated or related knowledge about a medication.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationKnowledge\FHIRMedicationKnowledgeRelatedMedicationKnowledge[]
     */
    public $relatedMedicationKnowledge = [];

    /**
     * Associated or related medications.  For example, if the medication is a branded product (e.g. Crestor), this is the Therapeutic Moeity (e.g. Rosuvastatin) or if this is a generic medication (e.g. Rosuvastatin), this would link to a branded product (e.g. Crestor).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $associatedMedication = [];

    /**
     * Category of the medication or product (e.g. branded product, therapeutic moeity, generic product, innovator product, etc.).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $productType = [];

    /**
     * Associated documentation about the medication.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMonograph[]
     */
    public $monograph = [];

    /**
     * Identifies a particular constituent of interest in the product.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationKnowledge\FHIRMedicationKnowledgeIngredient[]
     */
    public $ingredient = [];

    /**
     * The instructions for preparing the medication.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public $preparationInstruction = null;

    /**
     * The intended or approved route of administration.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $intendedRoute = [];

    /**
     * The price of the medication.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationKnowledge\FHIRMedicationKnowledgeCost[]
     */
    public $cost = [];

    /**
     * The program under which the medication is reviewed.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMonitoringProgram[]
     */
    public $monitoringProgram = [];

    /**
     * Guidelines for the administration of the medication.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationKnowledge\FHIRMedicationKnowledgeAdministrationGuidelines[]
     */
    public $administrationGuidelines = [];

    /**
     * Categorization of the medication within a formulary or classification system.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMedicineClassification[]
     */
    public $medicineClassification = [];

    /**
     * Information that only applies to packages (not products).
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationKnowledge\FHIRMedicationKnowledgePackaging
     */
    public $packaging = null;

    /**
     * Specifies descriptive properties of the medicine, such as color, shape, imprints, etc.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationKnowledge\FHIRMedicationKnowledgeDrugCharacteristic[]
     */
    public $drugCharacteristic = [];

    /**
     * Potential clinical issue with or between medication(s) (for example, drug-drug interaction, drug-disease contraindication, drug-allergy interaction, etc.).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $contraindication = [];

    /**
     * Regulatory information about a medication.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationKnowledge\FHIRMedicationKnowledgeRegulatory[]
     */
    public $regulatory = [];

    /**
     * The time course of drug absorption, distribution, metabolism and excretion of a medication from the body.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationKnowledge\FHIRMedicationKnowledgeKinetics[]
     */
    public $kinetics = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'MedicationKnowledge';

    /**
     * A code that specifies this medication, or a textual description if no code is available. Usage note: This could be a standard medication code such as a code from RxNorm, SNOMED CT, IDMP etc. It could also be a national or local formulary code, optionally with translations to other code systems.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * A code that specifies this medication, or a textual description if no code is available. Usage note: This could be a standard medication code such as a code from RxNorm, SNOMED CT, IDMP etc. It could also be a national or local formulary code, optionally with translations to other code systems.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * A code to indicate if the medication is in active use.  The status refers to the validity about the information of the medication and not to its medicinal properties.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCode
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * A code to indicate if the medication is in active use.  The status refers to the validity about the information of the medication and not to its medicinal properties.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCode $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Describes the details of the manufacturer of the medication product.  This is not intended to represent the distributor of a medication product.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getManufacturer()
    {
        return $this->manufacturer;
    }

    /**
     * Describes the details of the manufacturer of the medication product.  This is not intended to represent the distributor of a medication product.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $manufacturer
     * @return $this
     */
    public function setManufacturer($manufacturer)
    {
        $this->manufacturer = $manufacturer;
        return $this;
    }

    /**
     * Describes the form of the item.  Powder; tablets; capsule.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getDoseForm()
    {
        return $this->doseForm;
    }

    /**
     * Describes the form of the item.  Powder; tablets; capsule.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $doseForm
     * @return $this
     */
    public function setDoseForm($doseForm)
    {
        $this->doseForm = $doseForm;
        return $this;
    }

    /**
     * Specific amount of the drug in the packaged product.  For example, when specifying a product that has the same strength (For example, Insulin glargine 100 unit per mL solution for injection), this attribute provides additional clarification of the package amount (For example, 3 mL, 10mL, etc.).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Specific amount of the drug in the packaged product.  For example, when specifying a product that has the same strength (For example, Insulin glargine 100 unit per mL solution for injection), this attribute provides additional clarification of the package amount (For example, 3 mL, 10mL, etc.).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity $amount
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * Additional names for a medication, for example, the name(s) given to a medication in different countries.  For example, acetaminophen and paracetamol or salbutamol and albuterol.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public function getSynonym()
    {
        return $this->synonym;
    }

    /**
     * Additional names for a medication, for example, the name(s) given to a medication in different countries.  For example, acetaminophen and paracetamol or salbutamol and albuterol.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $synonym
     * @return $this
     */
    public function addSynonym($synonym)
    {
        $this->synonym[] = $synonym;
        return $this;
    }

    /**
     * Associated or related knowledge about a medication.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationKnowledge\FHIRMedicationKnowledgeRelatedMedicationKnowledge[]
     */
    public function getRelatedMedicationKnowledge()
    {
        return $this->relatedMedicationKnowledge;
    }

    /**
     * Associated or related knowledge about a medication.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationKnowledge\FHIRMedicationKnowledgeRelatedMedicationKnowledge $relatedMedicationKnowledge
     * @return $this
     */
    public function addRelatedMedicationKnowledge($relatedMedicationKnowledge)
    {
        $this->relatedMedicationKnowledge[] = $relatedMedicationKnowledge;
        return $this;
    }

    /**
     * Associated or related medications.  For example, if the medication is a branded product (e.g. Crestor), this is the Therapeutic Moeity (e.g. Rosuvastatin) or if this is a generic medication (e.g. Rosuvastatin), this would link to a branded product (e.g. Crestor).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getAssociatedMedication()
    {
        return $this->associatedMedication;
    }

    /**
     * Associated or related medications.  For example, if the medication is a branded product (e.g. Crestor), this is the Therapeutic Moeity (e.g. Rosuvastatin) or if this is a generic medication (e.g. Rosuvastatin), this would link to a branded product (e.g. Crestor).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $associatedMedication
     * @return $this
     */
    public function addAssociatedMedication($associatedMedication)
    {
        $this->associatedMedication[] = $associatedMedication;
        return $this;
    }

    /**
     * Category of the medication or product (e.g. branded product, therapeutic moeity, generic product, innovator product, etc.).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getProductType()
    {
        return $this->productType;
    }

    /**
     * Category of the medication or product (e.g. branded product, therapeutic moeity, generic product, innovator product, etc.).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $productType
     * @return $this
     */
    public function addProductType($productType)
    {
        $this->productType[] = $productType;
        return $this;
    }

    /**
     * Associated documentation about the medication.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMonograph[]
     */
    public function getMonograph()
    {
        return $this->monograph;
    }

    /**
     * Associated documentation about the medication.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMonograph $monograph
     * @return $this
     */
    public function addMonograph($monograph)
    {
        $this->monograph[] = $monograph;
        return $this;
    }

    /**
     * Identifies a particular constituent of interest in the product.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationKnowledge\FHIRMedicationKnowledgeIngredient[]
     */
    public function getIngredient()
    {
        return $this->ingredient;
    }

    /**
     * Identifies a particular constituent of interest in the product.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationKnowledge\FHIRMedicationKnowledgeIngredient $ingredient
     * @return $this
     */
    public function addIngredient($ingredient)
    {
        $this->ingredient[] = $ingredient;
        return $this;
    }

    /**
     * The instructions for preparing the medication.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public function getPreparationInstruction()
    {
        return $this->preparationInstruction;
    }

    /**
     * The instructions for preparing the medication.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown $preparationInstruction
     * @return $this
     */
    public function setPreparationInstruction($preparationInstruction)
    {
        $this->preparationInstruction = $preparationInstruction;
        return $this;
    }

    /**
     * The intended or approved route of administration.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getIntendedRoute()
    {
        return $this->intendedRoute;
    }

    /**
     * The intended or approved route of administration.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $intendedRoute
     * @return $this
     */
    public function addIntendedRoute($intendedRoute)
    {
        $this->intendedRoute[] = $intendedRoute;
        return $this;
    }

    /**
     * The price of the medication.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationKnowledge\FHIRMedicationKnowledgeCost[]
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * The price of the medication.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationKnowledge\FHIRMedicationKnowledgeCost $cost
     * @return $this
     */
    public function addCost($cost)
    {
        $this->cost[] = $cost;
        return $this;
    }

    /**
     * The program under which the medication is reviewed.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMonitoringProgram[]
     */
    public function getMonitoringProgram()
    {
        return $this->monitoringProgram;
    }

    /**
     * The program under which the medication is reviewed.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMonitoringProgram $monitoringProgram
     * @return $this
     */
    public function addMonitoringProgram($monitoringProgram)
    {
        $this->monitoringProgram[] = $monitoringProgram;
        return $this;
    }

    /**
     * Guidelines for the administration of the medication.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationKnowledge\FHIRMedicationKnowledgeAdministrationGuidelines[]
     */
    public function getAdministrationGuidelines()
    {
        return $this->administrationGuidelines;
    }

    /**
     * Guidelines for the administration of the medication.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationKnowledge\FHIRMedicationKnowledgeAdministrationGuidelines $administrationGuidelines
     * @return $this
     */
    public function addAdministrationGuidelines($administrationGuidelines)
    {
        $this->administrationGuidelines[] = $administrationGuidelines;
        return $this;
    }

    /**
     * Categorization of the medication within a formulary or classification system.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMedicineClassification[]
     */
    public function getMedicineClassification()
    {
        return $this->medicineClassification;
    }

    /**
     * Categorization of the medication within a formulary or classification system.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMedicineClassification $medicineClassification
     * @return $this
     */
    public function addMedicineClassification($medicineClassification)
    {
        $this->medicineClassification[] = $medicineClassification;
        return $this;
    }

    /**
     * Information that only applies to packages (not products).
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationKnowledge\FHIRMedicationKnowledgePackaging
     */
    public function getPackaging()
    {
        return $this->packaging;
    }

    /**
     * Information that only applies to packages (not products).
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationKnowledge\FHIRMedicationKnowledgePackaging $packaging
     * @return $this
     */
    public function setPackaging($packaging)
    {
        $this->packaging = $packaging;
        return $this;
    }

    /**
     * Specifies descriptive properties of the medicine, such as color, shape, imprints, etc.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationKnowledge\FHIRMedicationKnowledgeDrugCharacteristic[]
     */
    public function getDrugCharacteristic()
    {
        return $this->drugCharacteristic;
    }

    /**
     * Specifies descriptive properties of the medicine, such as color, shape, imprints, etc.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationKnowledge\FHIRMedicationKnowledgeDrugCharacteristic $drugCharacteristic
     * @return $this
     */
    public function addDrugCharacteristic($drugCharacteristic)
    {
        $this->drugCharacteristic[] = $drugCharacteristic;
        return $this;
    }

    /**
     * Potential clinical issue with or between medication(s) (for example, drug-drug interaction, drug-disease contraindication, drug-allergy interaction, etc.).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getContraindication()
    {
        return $this->contraindication;
    }

    /**
     * Potential clinical issue with or between medication(s) (for example, drug-drug interaction, drug-disease contraindication, drug-allergy interaction, etc.).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $contraindication
     * @return $this
     */
    public function addContraindication($contraindication)
    {
        $this->contraindication[] = $contraindication;
        return $this;
    }

    /**
     * Regulatory information about a medication.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationKnowledge\FHIRMedicationKnowledgeRegulatory[]
     */
    public function getRegulatory()
    {
        return $this->regulatory;
    }

    /**
     * Regulatory information about a medication.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationKnowledge\FHIRMedicationKnowledgeRegulatory $regulatory
     * @return $this
     */
    public function addRegulatory($regulatory)
    {
        $this->regulatory[] = $regulatory;
        return $this;
    }

    /**
     * The time course of drug absorption, distribution, metabolism and excretion of a medication from the body.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationKnowledge\FHIRMedicationKnowledgeKinetics[]
     */
    public function getKinetics()
    {
        return $this->kinetics;
    }

    /**
     * The time course of drug absorption, distribution, metabolism and excretion of a medication from the body.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationKnowledge\FHIRMedicationKnowledgeKinetics $kinetics
     * @return $this
     */
    public function addKinetics($kinetics)
    {
        $this->kinetics[] = $kinetics;
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
            if (isset($data['code'])) {
                $this->setCode($data['code']);
            }
            if (isset($data['status'])) {
                $this->setStatus($data['status']);
            }
            if (isset($data['manufacturer'])) {
                $this->setManufacturer($data['manufacturer']);
            }
            if (isset($data['doseForm'])) {
                $this->setDoseForm($data['doseForm']);
            }
            if (isset($data['amount'])) {
                $this->setAmount($data['amount']);
            }
            if (isset($data['synonym'])) {
                if (is_array($data['synonym'])) {
                    foreach ($data['synonym'] as $d) {
                        $this->addSynonym($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"synonym" must be array of objects or null, ' . gettype($data['synonym']) . ' seen.');
                }
            }
            if (isset($data['relatedMedicationKnowledge'])) {
                if (is_array($data['relatedMedicationKnowledge'])) {
                    foreach ($data['relatedMedicationKnowledge'] as $d) {
                        $this->addRelatedMedicationKnowledge($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"relatedMedicationKnowledge" must be array of objects or null, ' . gettype($data['relatedMedicationKnowledge']) . ' seen.');
                }
            }
            if (isset($data['associatedMedication'])) {
                if (is_array($data['associatedMedication'])) {
                    foreach ($data['associatedMedication'] as $d) {
                        $this->addAssociatedMedication($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"associatedMedication" must be array of objects or null, ' . gettype($data['associatedMedication']) . ' seen.');
                }
            }
            if (isset($data['productType'])) {
                if (is_array($data['productType'])) {
                    foreach ($data['productType'] as $d) {
                        $this->addProductType($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"productType" must be array of objects or null, ' . gettype($data['productType']) . ' seen.');
                }
            }
            if (isset($data['monograph'])) {
                if (is_array($data['monograph'])) {
                    foreach ($data['monograph'] as $d) {
                        $this->addMonograph($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"monograph" must be array of objects or null, ' . gettype($data['monograph']) . ' seen.');
                }
            }
            if (isset($data['ingredient'])) {
                if (is_array($data['ingredient'])) {
                    foreach ($data['ingredient'] as $d) {
                        $this->addIngredient($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"ingredient" must be array of objects or null, ' . gettype($data['ingredient']) . ' seen.');
                }
            }
            if (isset($data['preparationInstruction'])) {
                $this->setPreparationInstruction($data['preparationInstruction']);
            }
            if (isset($data['intendedRoute'])) {
                if (is_array($data['intendedRoute'])) {
                    foreach ($data['intendedRoute'] as $d) {
                        $this->addIntendedRoute($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"intendedRoute" must be array of objects or null, ' . gettype($data['intendedRoute']) . ' seen.');
                }
            }
            if (isset($data['cost'])) {
                if (is_array($data['cost'])) {
                    foreach ($data['cost'] as $d) {
                        $this->addCost($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"cost" must be array of objects or null, ' . gettype($data['cost']) . ' seen.');
                }
            }
            if (isset($data['monitoringProgram'])) {
                if (is_array($data['monitoringProgram'])) {
                    foreach ($data['monitoringProgram'] as $d) {
                        $this->addMonitoringProgram($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"monitoringProgram" must be array of objects or null, ' . gettype($data['monitoringProgram']) . ' seen.');
                }
            }
            if (isset($data['administrationGuidelines'])) {
                if (is_array($data['administrationGuidelines'])) {
                    foreach ($data['administrationGuidelines'] as $d) {
                        $this->addAdministrationGuidelines($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"administrationGuidelines" must be array of objects or null, ' . gettype($data['administrationGuidelines']) . ' seen.');
                }
            }
            if (isset($data['medicineClassification'])) {
                if (is_array($data['medicineClassification'])) {
                    foreach ($data['medicineClassification'] as $d) {
                        $this->addMedicineClassification($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"medicineClassification" must be array of objects or null, ' . gettype($data['medicineClassification']) . ' seen.');
                }
            }
            if (isset($data['packaging'])) {
                $this->setPackaging($data['packaging']);
            }
            if (isset($data['drugCharacteristic'])) {
                if (is_array($data['drugCharacteristic'])) {
                    foreach ($data['drugCharacteristic'] as $d) {
                        $this->addDrugCharacteristic($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"drugCharacteristic" must be array of objects or null, ' . gettype($data['drugCharacteristic']) . ' seen.');
                }
            }
            if (isset($data['contraindication'])) {
                if (is_array($data['contraindication'])) {
                    foreach ($data['contraindication'] as $d) {
                        $this->addContraindication($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"contraindication" must be array of objects or null, ' . gettype($data['contraindication']) . ' seen.');
                }
            }
            if (isset($data['regulatory'])) {
                if (is_array($data['regulatory'])) {
                    foreach ($data['regulatory'] as $d) {
                        $this->addRegulatory($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"regulatory" must be array of objects or null, ' . gettype($data['regulatory']) . ' seen.');
                }
            }
            if (isset($data['kinetics'])) {
                if (is_array($data['kinetics'])) {
                    foreach ($data['kinetics'] as $d) {
                        $this->addKinetics($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"kinetics" must be array of objects or null, ' . gettype($data['kinetics']) . ' seen.');
                }
            }
        } elseif (null !== $data) {
            throw new \InvalidArgumentException('$data expected to be array of values, saw "' . gettype($data) . '"');
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
        if (isset($this->code)) {
            $json['code'] = $this->code;
        }
        if (isset($this->status)) {
            $json['status'] = $this->status;
        }
        if (isset($this->manufacturer)) {
            $json['manufacturer'] = $this->manufacturer;
        }
        if (isset($this->doseForm)) {
            $json['doseForm'] = $this->doseForm;
        }
        if (isset($this->amount)) {
            $json['amount'] = $this->amount;
        }
        if (0 < count($this->synonym)) {
            $json['synonym'] = [];
            foreach ($this->synonym as $synonym) {
                $json['synonym'][] = $synonym;
            }
        }
        if (0 < count($this->relatedMedicationKnowledge)) {
            $json['relatedMedicationKnowledge'] = [];
            foreach ($this->relatedMedicationKnowledge as $relatedMedicationKnowledge) {
                $json['relatedMedicationKnowledge'][] = $relatedMedicationKnowledge;
            }
        }
        if (0 < count($this->associatedMedication)) {
            $json['associatedMedication'] = [];
            foreach ($this->associatedMedication as $associatedMedication) {
                $json['associatedMedication'][] = $associatedMedication;
            }
        }
        if (0 < count($this->productType)) {
            $json['productType'] = [];
            foreach ($this->productType as $productType) {
                $json['productType'][] = $productType;
            }
        }
        if (0 < count($this->monograph)) {
            $json['monograph'] = [];
            foreach ($this->monograph as $monograph) {
                $json['monograph'][] = $monograph;
            }
        }
        if (0 < count($this->ingredient)) {
            $json['ingredient'] = [];
            foreach ($this->ingredient as $ingredient) {
                $json['ingredient'][] = $ingredient;
            }
        }
        if (isset($this->preparationInstruction)) {
            $json['preparationInstruction'] = $this->preparationInstruction;
        }
        if (0 < count($this->intendedRoute)) {
            $json['intendedRoute'] = [];
            foreach ($this->intendedRoute as $intendedRoute) {
                $json['intendedRoute'][] = $intendedRoute;
            }
        }
        if (0 < count($this->cost)) {
            $json['cost'] = [];
            foreach ($this->cost as $cost) {
                $json['cost'][] = $cost;
            }
        }
        if (0 < count($this->monitoringProgram)) {
            $json['monitoringProgram'] = [];
            foreach ($this->monitoringProgram as $monitoringProgram) {
                $json['monitoringProgram'][] = $monitoringProgram;
            }
        }
        if (0 < count($this->administrationGuidelines)) {
            $json['administrationGuidelines'] = [];
            foreach ($this->administrationGuidelines as $administrationGuidelines) {
                $json['administrationGuidelines'][] = $administrationGuidelines;
            }
        }
        if (0 < count($this->medicineClassification)) {
            $json['medicineClassification'] = [];
            foreach ($this->medicineClassification as $medicineClassification) {
                $json['medicineClassification'][] = $medicineClassification;
            }
        }
        if (isset($this->packaging)) {
            $json['packaging'] = $this->packaging;
        }
        if (0 < count($this->drugCharacteristic)) {
            $json['drugCharacteristic'] = [];
            foreach ($this->drugCharacteristic as $drugCharacteristic) {
                $json['drugCharacteristic'][] = $drugCharacteristic;
            }
        }
        if (0 < count($this->contraindication)) {
            $json['contraindication'] = [];
            foreach ($this->contraindication as $contraindication) {
                $json['contraindication'][] = $contraindication;
            }
        }
        if (0 < count($this->regulatory)) {
            $json['regulatory'] = [];
            foreach ($this->regulatory as $regulatory) {
                $json['regulatory'][] = $regulatory;
            }
        }
        if (0 < count($this->kinetics)) {
            $json['kinetics'] = [];
            foreach ($this->kinetics as $kinetics) {
                $json['kinetics'][] = $kinetics;
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
            $sxe = new \SimpleXMLElement('<MedicationKnowledge xmlns="http://hl7.org/fhir"></MedicationKnowledge>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->code)) {
            $this->code->xmlSerialize(true, $sxe->addChild('code'));
        }
        if (isset($this->status)) {
            $this->status->xmlSerialize(true, $sxe->addChild('status'));
        }
        if (isset($this->manufacturer)) {
            $this->manufacturer->xmlSerialize(true, $sxe->addChild('manufacturer'));
        }
        if (isset($this->doseForm)) {
            $this->doseForm->xmlSerialize(true, $sxe->addChild('doseForm'));
        }
        if (isset($this->amount)) {
            $this->amount->xmlSerialize(true, $sxe->addChild('amount'));
        }
        if (0 < count($this->synonym)) {
            foreach ($this->synonym as $synonym) {
                $synonym->xmlSerialize(true, $sxe->addChild('synonym'));
            }
        }
        if (0 < count($this->relatedMedicationKnowledge)) {
            foreach ($this->relatedMedicationKnowledge as $relatedMedicationKnowledge) {
                $relatedMedicationKnowledge->xmlSerialize(true, $sxe->addChild('relatedMedicationKnowledge'));
            }
        }
        if (0 < count($this->associatedMedication)) {
            foreach ($this->associatedMedication as $associatedMedication) {
                $associatedMedication->xmlSerialize(true, $sxe->addChild('associatedMedication'));
            }
        }
        if (0 < count($this->productType)) {
            foreach ($this->productType as $productType) {
                $productType->xmlSerialize(true, $sxe->addChild('productType'));
            }
        }
        if (0 < count($this->monograph)) {
            foreach ($this->monograph as $monograph) {
                $monograph->xmlSerialize(true, $sxe->addChild('monograph'));
            }
        }
        if (0 < count($this->ingredient)) {
            foreach ($this->ingredient as $ingredient) {
                $ingredient->xmlSerialize(true, $sxe->addChild('ingredient'));
            }
        }
        if (isset($this->preparationInstruction)) {
            $this->preparationInstruction->xmlSerialize(true, $sxe->addChild('preparationInstruction'));
        }
        if (0 < count($this->intendedRoute)) {
            foreach ($this->intendedRoute as $intendedRoute) {
                $intendedRoute->xmlSerialize(true, $sxe->addChild('intendedRoute'));
            }
        }
        if (0 < count($this->cost)) {
            foreach ($this->cost as $cost) {
                $cost->xmlSerialize(true, $sxe->addChild('cost'));
            }
        }
        if (0 < count($this->monitoringProgram)) {
            foreach ($this->monitoringProgram as $monitoringProgram) {
                $monitoringProgram->xmlSerialize(true, $sxe->addChild('monitoringProgram'));
            }
        }
        if (0 < count($this->administrationGuidelines)) {
            foreach ($this->administrationGuidelines as $administrationGuidelines) {
                $administrationGuidelines->xmlSerialize(true, $sxe->addChild('administrationGuidelines'));
            }
        }
        if (0 < count($this->medicineClassification)) {
            foreach ($this->medicineClassification as $medicineClassification) {
                $medicineClassification->xmlSerialize(true, $sxe->addChild('medicineClassification'));
            }
        }
        if (isset($this->packaging)) {
            $this->packaging->xmlSerialize(true, $sxe->addChild('packaging'));
        }
        if (0 < count($this->drugCharacteristic)) {
            foreach ($this->drugCharacteristic as $drugCharacteristic) {
                $drugCharacteristic->xmlSerialize(true, $sxe->addChild('drugCharacteristic'));
            }
        }
        if (0 < count($this->contraindication)) {
            foreach ($this->contraindication as $contraindication) {
                $contraindication->xmlSerialize(true, $sxe->addChild('contraindication'));
            }
        }
        if (0 < count($this->regulatory)) {
            foreach ($this->regulatory as $regulatory) {
                $regulatory->xmlSerialize(true, $sxe->addChild('regulatory'));
            }
        }
        if (0 < count($this->kinetics)) {
            foreach ($this->kinetics as $kinetics) {
                $kinetics->xmlSerialize(true, $sxe->addChild('kinetics'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
