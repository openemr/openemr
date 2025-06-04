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
 * This resource is primarily used for the identification and definition of a medication for the purposes of prescribing, dispensing, and administering a medication as well as for making statements about medication use.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRMedication extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * Business identifier for this medication.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = [];

    /**
     * A code (or set of codes) that specify this medication, or a textual description if no code is available. Usage note: This could be a standard medication code such as a code from RxNorm, SNOMED CT, IDMP etc. It could also be a national or local formulary code, optionally with translations to other code systems.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $code = null;

    /**
     * A code to indicate if the medication is in active use.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMedicationStatusCodes
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
    public $form = null;

    /**
     * Specific amount of the drug in the packaged product.  For example, when specifying a product that has the same strength (For example, Insulin glargine 100 unit per mL solution for injection), this attribute provides additional clarification of the package amount (For example, 3 mL, 10mL, etc.).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRRatio
     */
    public $amount = null;

    /**
     * Identifies a particular constituent of interest in the product.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMedication\FHIRMedicationIngredient[]
     */
    public $ingredient = [];

    /**
     * Information that only applies to packages (not products).
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMedication\FHIRMedicationBatch
     */
    public $batch = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Medication';

    /**
     * Business identifier for this medication.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Business identifier for this medication.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * A code (or set of codes) that specify this medication, or a textual description if no code is available. Usage note: This could be a standard medication code such as a code from RxNorm, SNOMED CT, IDMP etc. It could also be a national or local formulary code, optionally with translations to other code systems.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * A code (or set of codes) that specify this medication, or a textual description if no code is available. Usage note: This could be a standard medication code such as a code from RxNorm, SNOMED CT, IDMP etc. It could also be a national or local formulary code, optionally with translations to other code systems.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * A code to indicate if the medication is in active use.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMedicationStatusCodes
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * A code to indicate if the medication is in active use.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMedicationStatusCodes $status
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
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Describes the form of the item.  Powder; tablets; capsule.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $form
     * @return $this
     */
    public function setForm($form)
    {
        $this->form = $form;
        return $this;
    }

    /**
     * Specific amount of the drug in the packaged product.  For example, when specifying a product that has the same strength (For example, Insulin glargine 100 unit per mL solution for injection), this attribute provides additional clarification of the package amount (For example, 3 mL, 10mL, etc.).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRRatio
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Specific amount of the drug in the packaged product.  For example, when specifying a product that has the same strength (For example, Insulin glargine 100 unit per mL solution for injection), this attribute provides additional clarification of the package amount (For example, 3 mL, 10mL, etc.).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRRatio $amount
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * Identifies a particular constituent of interest in the product.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMedication\FHIRMedicationIngredient[]
     */
    public function getIngredient()
    {
        return $this->ingredient;
    }

    /**
     * Identifies a particular constituent of interest in the product.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMedication\FHIRMedicationIngredient $ingredient
     * @return $this
     */
    public function addIngredient($ingredient)
    {
        $this->ingredient[] = $ingredient;
        return $this;
    }

    /**
     * Information that only applies to packages (not products).
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMedication\FHIRMedicationBatch
     */
    public function getBatch()
    {
        return $this->batch;
    }

    /**
     * Information that only applies to packages (not products).
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMedication\FHIRMedicationBatch $batch
     * @return $this
     */
    public function setBatch($batch)
    {
        $this->batch = $batch;
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
                    throw new \InvalidArgumentException('"identifier" must be array of objects or null, ' . gettype($data['identifier']) . ' seen.');
                }
            }
            if (isset($data['code'])) {
                $this->setCode($data['code']);
            }
            if (isset($data['status'])) {
                $this->setStatus($data['status']);
            }
            if (isset($data['manufacturer'])) {
                $this->setManufacturer($data['manufacturer']);
            }
            if (isset($data['form'])) {
                $this->setForm($data['form']);
            }
            if (isset($data['amount'])) {
                $this->setAmount($data['amount']);
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
            if (isset($data['batch'])) {
                $this->setBatch($data['batch']);
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
    public function jsonSerialize(): mixed
    {
        $json = parent::jsonSerialize();
        $json['resourceType'] = $this->_fhirElementName;
        if (0 < count($this->identifier)) {
            $json['identifier'] = [];
            foreach ($this->identifier as $identifier) {
                $json['identifier'][] = $identifier;
            }
        }
        if (isset($this->code)) {
            $json['code'] = $this->code;
        }
        if (isset($this->status)) {
            $json['status'] = $this->status;
        }
        if (isset($this->manufacturer)) {
            $json['manufacturer'] = $this->manufacturer;
        }
        if (isset($this->form)) {
            $json['form'] = $this->form;
        }
        if (isset($this->amount)) {
            $json['amount'] = $this->amount;
        }
        if (0 < count($this->ingredient)) {
            $json['ingredient'] = [];
            foreach ($this->ingredient as $ingredient) {
                $json['ingredient'][] = $ingredient;
            }
        }
        if (isset($this->batch)) {
            $json['batch'] = $this->batch;
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
            $sxe = new \SimpleXMLElement('<Medication xmlns="http://hl7.org/fhir"></Medication>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->identifier)) {
            foreach ($this->identifier as $identifier) {
                $identifier->xmlSerialize(true, $sxe->addChild('identifier'));
            }
        }
        if (isset($this->code)) {
            $this->code->xmlSerialize(true, $sxe->addChild('code'));
        }
        if (isset($this->status)) {
            $this->status->xmlSerialize(true, $sxe->addChild('status'));
        }
        if (isset($this->manufacturer)) {
            $this->manufacturer->xmlSerialize(true, $sxe->addChild('manufacturer'));
        }
        if (isset($this->form)) {
            $this->form->xmlSerialize(true, $sxe->addChild('form'));
        }
        if (isset($this->amount)) {
            $this->amount->xmlSerialize(true, $sxe->addChild('amount'));
        }
        if (0 < count($this->ingredient)) {
            foreach ($this->ingredient as $ingredient) {
                $ingredient->xmlSerialize(true, $sxe->addChild('ingredient'));
            }
        }
        if (isset($this->batch)) {
            $this->batch->xmlSerialize(true, $sxe->addChild('batch'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
