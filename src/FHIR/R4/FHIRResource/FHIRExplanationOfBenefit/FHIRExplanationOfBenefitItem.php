<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRExplanationOfBenefit;

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

use OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement;

/**
 * This resource provides: the claim details; adjudication details from the processing of a Claim; and optionally account balance information, for informing the subscriber of the benefits provided.
 */
class FHIRExplanationOfBenefitItem extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * A number to uniquely identify item entries.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt
     */
    public $sequence = null;

    /**
     * Care team members related to this service or product.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt[]
     */
    public $careTeamSequence = [];

    /**
     * Diagnoses applicable for this service or product.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt[]
     */
    public $diagnosisSequence = [];

    /**
     * Procedures applicable for this service or product.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt[]
     */
    public $procedureSequence = [];

    /**
     * Exceptions, special conditions and supporting information applicable for this service or product.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt[]
     */
    public $informationSequence = [];

    /**
     * The type of revenue or cost center providing the product and/or service.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $revenue = null;

    /**
     * Code to identify the general type of benefits under which products and services are provided.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $category = null;

    /**
     * When the value is a group code then this item collects a set of related claim details, otherwise this contains the product, service, drug or other billing code for the item.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $productOrService = null;

    /**
     * Item typification or modifiers codes to convey additional context for the product or service.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $modifier = [];

    /**
     * Identifies the program under which this may be recovered.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $programCode = [];

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDate
     */
    public $servicedDate = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public $servicedPeriod = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $locationCodeableConcept = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRAddress
     */
    public $locationAddress = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $locationReference = null;

    /**
     * The number of repetitions of a service or product.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public $quantity = null;

    /**
     * If the item is not a group then this is the fee for the product or service, otherwise this is the total of the fees for the details of the group.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMoney
     */
    public $unitPrice = null;

    /**
     * A real number that represents a multiplier used in determining the overall value of services delivered and/or goods received. The concept of a Factor allows for a discount or surcharge multiplier to be applied to a monetary amount.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public $factor = null;

    /**
     * The quantity times the unit price for an additional service or product or charge.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMoney
     */
    public $net = null;

    /**
     * Unique Device Identifiers associated with this line item.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $udi = [];

    /**
     * Physical service site on the patient (limb, tooth, etc.).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $bodySite = null;

    /**
     * A region or surface of the bodySite, e.g. limb region or tooth surface(s).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $subSite = [];

    /**
     * A billed item may include goods or services provided in multiple encounters.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $encounter = [];

    /**
     * The numbers associated with notes below which apply to the adjudication of this item.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt[]
     */
    public $noteNumber = [];

    /**
     * If this item is a group then the values here are a summary of the adjudication of the detail items. If this item is a simple product or service then this is the result of the adjudication of this item.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitAdjudication[]
     */
    public $adjudication = [];

    /**
     * Second-tier of goods and services.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitDetail[]
     */
    public $detail = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'ExplanationOfBenefit.Item';

    /**
     * A number to uniquely identify item entries.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt
     */
    public function getSequence()
    {
        return $this->sequence;
    }

    /**
     * A number to uniquely identify item entries.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt $sequence
     * @return $this
     */
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;
        return $this;
    }

    /**
     * Care team members related to this service or product.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt[]
     */
    public function getCareTeamSequence()
    {
        return $this->careTeamSequence;
    }

    /**
     * Care team members related to this service or product.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt $careTeamSequence
     * @return $this
     */
    public function addCareTeamSequence($careTeamSequence)
    {
        $this->careTeamSequence[] = $careTeamSequence;
        return $this;
    }

    /**
     * Diagnoses applicable for this service or product.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt[]
     */
    public function getDiagnosisSequence()
    {
        return $this->diagnosisSequence;
    }

    /**
     * Diagnoses applicable for this service or product.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt $diagnosisSequence
     * @return $this
     */
    public function addDiagnosisSequence($diagnosisSequence)
    {
        $this->diagnosisSequence[] = $diagnosisSequence;
        return $this;
    }

    /**
     * Procedures applicable for this service or product.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt[]
     */
    public function getProcedureSequence()
    {
        return $this->procedureSequence;
    }

    /**
     * Procedures applicable for this service or product.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt $procedureSequence
     * @return $this
     */
    public function addProcedureSequence($procedureSequence)
    {
        $this->procedureSequence[] = $procedureSequence;
        return $this;
    }

    /**
     * Exceptions, special conditions and supporting information applicable for this service or product.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt[]
     */
    public function getInformationSequence()
    {
        return $this->informationSequence;
    }

    /**
     * Exceptions, special conditions and supporting information applicable for this service or product.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt $informationSequence
     * @return $this
     */
    public function addInformationSequence($informationSequence)
    {
        $this->informationSequence[] = $informationSequence;
        return $this;
    }

    /**
     * The type of revenue or cost center providing the product and/or service.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getRevenue()
    {
        return $this->revenue;
    }

    /**
     * The type of revenue or cost center providing the product and/or service.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $revenue
     * @return $this
     */
    public function setRevenue($revenue)
    {
        $this->revenue = $revenue;
        return $this;
    }

    /**
     * Code to identify the general type of benefits under which products and services are provided.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Code to identify the general type of benefits under which products and services are provided.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $category
     * @return $this
     */
    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * When the value is a group code then this item collects a set of related claim details, otherwise this contains the product, service, drug or other billing code for the item.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getProductOrService()
    {
        return $this->productOrService;
    }

    /**
     * When the value is a group code then this item collects a set of related claim details, otherwise this contains the product, service, drug or other billing code for the item.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $productOrService
     * @return $this
     */
    public function setProductOrService($productOrService)
    {
        $this->productOrService = $productOrService;
        return $this;
    }

    /**
     * Item typification or modifiers codes to convey additional context for the product or service.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getModifier()
    {
        return $this->modifier;
    }

    /**
     * Item typification or modifiers codes to convey additional context for the product or service.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $modifier
     * @return $this
     */
    public function addModifier($modifier)
    {
        $this->modifier[] = $modifier;
        return $this;
    }

    /**
     * Identifies the program under which this may be recovered.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getProgramCode()
    {
        return $this->programCode;
    }

    /**
     * Identifies the program under which this may be recovered.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $programCode
     * @return $this
     */
    public function addProgramCode($programCode)
    {
        $this->programCode[] = $programCode;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDate
     */
    public function getServicedDate()
    {
        return $this->servicedDate;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDate $servicedDate
     * @return $this
     */
    public function setServicedDate($servicedDate)
    {
        $this->servicedDate = $servicedDate;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public function getServicedPeriod()
    {
        return $this->servicedPeriod;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod $servicedPeriod
     * @return $this
     */
    public function setServicedPeriod($servicedPeriod)
    {
        $this->servicedPeriod = $servicedPeriod;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getLocationCodeableConcept()
    {
        return $this->locationCodeableConcept;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $locationCodeableConcept
     * @return $this
     */
    public function setLocationCodeableConcept($locationCodeableConcept)
    {
        $this->locationCodeableConcept = $locationCodeableConcept;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRAddress
     */
    public function getLocationAddress()
    {
        return $this->locationAddress;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRAddress $locationAddress
     * @return $this
     */
    public function setLocationAddress($locationAddress)
    {
        $this->locationAddress = $locationAddress;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getLocationReference()
    {
        return $this->locationReference;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $locationReference
     * @return $this
     */
    public function setLocationReference($locationReference)
    {
        $this->locationReference = $locationReference;
        return $this;
    }

    /**
     * The number of repetitions of a service or product.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * The number of repetitions of a service or product.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity $quantity
     * @return $this
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * If the item is not a group then this is the fee for the product or service, otherwise this is the total of the fees for the details of the group.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMoney
     */
    public function getUnitPrice()
    {
        return $this->unitPrice;
    }

    /**
     * If the item is not a group then this is the fee for the product or service, otherwise this is the total of the fees for the details of the group.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMoney $unitPrice
     * @return $this
     */
    public function setUnitPrice($unitPrice)
    {
        $this->unitPrice = $unitPrice;
        return $this;
    }

    /**
     * A real number that represents a multiplier used in determining the overall value of services delivered and/or goods received. The concept of a Factor allows for a discount or surcharge multiplier to be applied to a monetary amount.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public function getFactor()
    {
        return $this->factor;
    }

    /**
     * A real number that represents a multiplier used in determining the overall value of services delivered and/or goods received. The concept of a Factor allows for a discount or surcharge multiplier to be applied to a monetary amount.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal $factor
     * @return $this
     */
    public function setFactor($factor)
    {
        $this->factor = $factor;
        return $this;
    }

    /**
     * The quantity times the unit price for an additional service or product or charge.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMoney
     */
    public function getNet()
    {
        return $this->net;
    }

    /**
     * The quantity times the unit price for an additional service or product or charge.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMoney $net
     * @return $this
     */
    public function setNet($net)
    {
        $this->net = $net;
        return $this;
    }

    /**
     * Unique Device Identifiers associated with this line item.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getUdi()
    {
        return $this->udi;
    }

    /**
     * Unique Device Identifiers associated with this line item.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $udi
     * @return $this
     */
    public function addUdi($udi)
    {
        $this->udi[] = $udi;
        return $this;
    }

    /**
     * Physical service site on the patient (limb, tooth, etc.).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getBodySite()
    {
        return $this->bodySite;
    }

    /**
     * Physical service site on the patient (limb, tooth, etc.).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $bodySite
     * @return $this
     */
    public function setBodySite($bodySite)
    {
        $this->bodySite = $bodySite;
        return $this;
    }

    /**
     * A region or surface of the bodySite, e.g. limb region or tooth surface(s).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getSubSite()
    {
        return $this->subSite;
    }

    /**
     * A region or surface of the bodySite, e.g. limb region or tooth surface(s).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $subSite
     * @return $this
     */
    public function addSubSite($subSite)
    {
        $this->subSite[] = $subSite;
        return $this;
    }

    /**
     * A billed item may include goods or services provided in multiple encounters.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getEncounter()
    {
        return $this->encounter;
    }

    /**
     * A billed item may include goods or services provided in multiple encounters.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $encounter
     * @return $this
     */
    public function addEncounter($encounter)
    {
        $this->encounter[] = $encounter;
        return $this;
    }

    /**
     * The numbers associated with notes below which apply to the adjudication of this item.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt[]
     */
    public function getNoteNumber()
    {
        return $this->noteNumber;
    }

    /**
     * The numbers associated with notes below which apply to the adjudication of this item.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt $noteNumber
     * @return $this
     */
    public function addNoteNumber($noteNumber)
    {
        $this->noteNumber[] = $noteNumber;
        return $this;
    }

    /**
     * If this item is a group then the values here are a summary of the adjudication of the detail items. If this item is a simple product or service then this is the result of the adjudication of this item.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitAdjudication[]
     */
    public function getAdjudication()
    {
        return $this->adjudication;
    }

    /**
     * If this item is a group then the values here are a summary of the adjudication of the detail items. If this item is a simple product or service then this is the result of the adjudication of this item.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitAdjudication $adjudication
     * @return $this
     */
    public function addAdjudication($adjudication)
    {
        $this->adjudication[] = $adjudication;
        return $this;
    }

    /**
     * Second-tier of goods and services.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitDetail[]
     */
    public function getDetail()
    {
        return $this->detail;
    }

    /**
     * Second-tier of goods and services.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitDetail $detail
     * @return $this
     */
    public function addDetail($detail)
    {
        $this->detail[] = $detail;
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
            if (isset($data['sequence'])) {
                $this->setSequence($data['sequence']);
            }
            if (isset($data['careTeamSequence'])) {
                if (is_array($data['careTeamSequence'])) {
                    foreach ($data['careTeamSequence'] as $d) {
                        $this->addCareTeamSequence($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"careTeamSequence" must be array of objects or null, ' . gettype($data['careTeamSequence']) . ' seen.');
                }
            }
            if (isset($data['diagnosisSequence'])) {
                if (is_array($data['diagnosisSequence'])) {
                    foreach ($data['diagnosisSequence'] as $d) {
                        $this->addDiagnosisSequence($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"diagnosisSequence" must be array of objects or null, ' . gettype($data['diagnosisSequence']) . ' seen.');
                }
            }
            if (isset($data['procedureSequence'])) {
                if (is_array($data['procedureSequence'])) {
                    foreach ($data['procedureSequence'] as $d) {
                        $this->addProcedureSequence($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"procedureSequence" must be array of objects or null, ' . gettype($data['procedureSequence']) . ' seen.');
                }
            }
            if (isset($data['informationSequence'])) {
                if (is_array($data['informationSequence'])) {
                    foreach ($data['informationSequence'] as $d) {
                        $this->addInformationSequence($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"informationSequence" must be array of objects or null, ' . gettype($data['informationSequence']) . ' seen.');
                }
            }
            if (isset($data['revenue'])) {
                $this->setRevenue($data['revenue']);
            }
            if (isset($data['category'])) {
                $this->setCategory($data['category']);
            }
            if (isset($data['productOrService'])) {
                $this->setProductOrService($data['productOrService']);
            }
            if (isset($data['modifier'])) {
                if (is_array($data['modifier'])) {
                    foreach ($data['modifier'] as $d) {
                        $this->addModifier($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"modifier" must be array of objects or null, ' . gettype($data['modifier']) . ' seen.');
                }
            }
            if (isset($data['programCode'])) {
                if (is_array($data['programCode'])) {
                    foreach ($data['programCode'] as $d) {
                        $this->addProgramCode($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"programCode" must be array of objects or null, ' . gettype($data['programCode']) . ' seen.');
                }
            }
            if (isset($data['servicedDate'])) {
                $this->setServicedDate($data['servicedDate']);
            }
            if (isset($data['servicedPeriod'])) {
                $this->setServicedPeriod($data['servicedPeriod']);
            }
            if (isset($data['locationCodeableConcept'])) {
                $this->setLocationCodeableConcept($data['locationCodeableConcept']);
            }
            if (isset($data['locationAddress'])) {
                $this->setLocationAddress($data['locationAddress']);
            }
            if (isset($data['locationReference'])) {
                $this->setLocationReference($data['locationReference']);
            }
            if (isset($data['quantity'])) {
                $this->setQuantity($data['quantity']);
            }
            if (isset($data['unitPrice'])) {
                $this->setUnitPrice($data['unitPrice']);
            }
            if (isset($data['factor'])) {
                $this->setFactor($data['factor']);
            }
            if (isset($data['net'])) {
                $this->setNet($data['net']);
            }
            if (isset($data['udi'])) {
                if (is_array($data['udi'])) {
                    foreach ($data['udi'] as $d) {
                        $this->addUdi($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"udi" must be array of objects or null, ' . gettype($data['udi']) . ' seen.');
                }
            }
            if (isset($data['bodySite'])) {
                $this->setBodySite($data['bodySite']);
            }
            if (isset($data['subSite'])) {
                if (is_array($data['subSite'])) {
                    foreach ($data['subSite'] as $d) {
                        $this->addSubSite($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"subSite" must be array of objects or null, ' . gettype($data['subSite']) . ' seen.');
                }
            }
            if (isset($data['encounter'])) {
                if (is_array($data['encounter'])) {
                    foreach ($data['encounter'] as $d) {
                        $this->addEncounter($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"encounter" must be array of objects or null, ' . gettype($data['encounter']) . ' seen.');
                }
            }
            if (isset($data['noteNumber'])) {
                if (is_array($data['noteNumber'])) {
                    foreach ($data['noteNumber'] as $d) {
                        $this->addNoteNumber($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"noteNumber" must be array of objects or null, ' . gettype($data['noteNumber']) . ' seen.');
                }
            }
            if (isset($data['adjudication'])) {
                if (is_array($data['adjudication'])) {
                    foreach ($data['adjudication'] as $d) {
                        $this->addAdjudication($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"adjudication" must be array of objects or null, ' . gettype($data['adjudication']) . ' seen.');
                }
            }
            if (isset($data['detail'])) {
                if (is_array($data['detail'])) {
                    foreach ($data['detail'] as $d) {
                        $this->addDetail($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"detail" must be array of objects or null, ' . gettype($data['detail']) . ' seen.');
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
    public function jsonSerialize(): mixed
    {
        $json = parent::jsonSerialize();
        if (isset($this->sequence)) {
            $json['sequence'] = $this->sequence;
        }
        if (0 < count($this->careTeamSequence)) {
            $json['careTeamSequence'] = [];
            foreach ($this->careTeamSequence as $careTeamSequence) {
                $json['careTeamSequence'][] = $careTeamSequence;
            }
        }
        if (0 < count($this->diagnosisSequence)) {
            $json['diagnosisSequence'] = [];
            foreach ($this->diagnosisSequence as $diagnosisSequence) {
                $json['diagnosisSequence'][] = $diagnosisSequence;
            }
        }
        if (0 < count($this->procedureSequence)) {
            $json['procedureSequence'] = [];
            foreach ($this->procedureSequence as $procedureSequence) {
                $json['procedureSequence'][] = $procedureSequence;
            }
        }
        if (0 < count($this->informationSequence)) {
            $json['informationSequence'] = [];
            foreach ($this->informationSequence as $informationSequence) {
                $json['informationSequence'][] = $informationSequence;
            }
        }
        if (isset($this->revenue)) {
            $json['revenue'] = $this->revenue;
        }
        if (isset($this->category)) {
            $json['category'] = $this->category;
        }
        if (isset($this->productOrService)) {
            $json['productOrService'] = $this->productOrService;
        }
        if (0 < count($this->modifier)) {
            $json['modifier'] = [];
            foreach ($this->modifier as $modifier) {
                $json['modifier'][] = $modifier;
            }
        }
        if (0 < count($this->programCode)) {
            $json['programCode'] = [];
            foreach ($this->programCode as $programCode) {
                $json['programCode'][] = $programCode;
            }
        }
        if (isset($this->servicedDate)) {
            $json['servicedDate'] = $this->servicedDate;
        }
        if (isset($this->servicedPeriod)) {
            $json['servicedPeriod'] = $this->servicedPeriod;
        }
        if (isset($this->locationCodeableConcept)) {
            $json['locationCodeableConcept'] = $this->locationCodeableConcept;
        }
        if (isset($this->locationAddress)) {
            $json['locationAddress'] = $this->locationAddress;
        }
        if (isset($this->locationReference)) {
            $json['locationReference'] = $this->locationReference;
        }
        if (isset($this->quantity)) {
            $json['quantity'] = $this->quantity;
        }
        if (isset($this->unitPrice)) {
            $json['unitPrice'] = $this->unitPrice;
        }
        if (isset($this->factor)) {
            $json['factor'] = $this->factor;
        }
        if (isset($this->net)) {
            $json['net'] = $this->net;
        }
        if (0 < count($this->udi)) {
            $json['udi'] = [];
            foreach ($this->udi as $udi) {
                $json['udi'][] = $udi;
            }
        }
        if (isset($this->bodySite)) {
            $json['bodySite'] = $this->bodySite;
        }
        if (0 < count($this->subSite)) {
            $json['subSite'] = [];
            foreach ($this->subSite as $subSite) {
                $json['subSite'][] = $subSite;
            }
        }
        if (0 < count($this->encounter)) {
            $json['encounter'] = [];
            foreach ($this->encounter as $encounter) {
                $json['encounter'][] = $encounter;
            }
        }
        if (0 < count($this->noteNumber)) {
            $json['noteNumber'] = [];
            foreach ($this->noteNumber as $noteNumber) {
                $json['noteNumber'][] = $noteNumber;
            }
        }
        if (0 < count($this->adjudication)) {
            $json['adjudication'] = [];
            foreach ($this->adjudication as $adjudication) {
                $json['adjudication'][] = $adjudication;
            }
        }
        if (0 < count($this->detail)) {
            $json['detail'] = [];
            foreach ($this->detail as $detail) {
                $json['detail'][] = $detail;
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
            $sxe = new \SimpleXMLElement('<ExplanationOfBenefitItem xmlns="http://hl7.org/fhir"></ExplanationOfBenefitItem>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->sequence)) {
            $this->sequence->xmlSerialize(true, $sxe->addChild('sequence'));
        }
        if (0 < count($this->careTeamSequence)) {
            foreach ($this->careTeamSequence as $careTeamSequence) {
                $careTeamSequence->xmlSerialize(true, $sxe->addChild('careTeamSequence'));
            }
        }
        if (0 < count($this->diagnosisSequence)) {
            foreach ($this->diagnosisSequence as $diagnosisSequence) {
                $diagnosisSequence->xmlSerialize(true, $sxe->addChild('diagnosisSequence'));
            }
        }
        if (0 < count($this->procedureSequence)) {
            foreach ($this->procedureSequence as $procedureSequence) {
                $procedureSequence->xmlSerialize(true, $sxe->addChild('procedureSequence'));
            }
        }
        if (0 < count($this->informationSequence)) {
            foreach ($this->informationSequence as $informationSequence) {
                $informationSequence->xmlSerialize(true, $sxe->addChild('informationSequence'));
            }
        }
        if (isset($this->revenue)) {
            $this->revenue->xmlSerialize(true, $sxe->addChild('revenue'));
        }
        if (isset($this->category)) {
            $this->category->xmlSerialize(true, $sxe->addChild('category'));
        }
        if (isset($this->productOrService)) {
            $this->productOrService->xmlSerialize(true, $sxe->addChild('productOrService'));
        }
        if (0 < count($this->modifier)) {
            foreach ($this->modifier as $modifier) {
                $modifier->xmlSerialize(true, $sxe->addChild('modifier'));
            }
        }
        if (0 < count($this->programCode)) {
            foreach ($this->programCode as $programCode) {
                $programCode->xmlSerialize(true, $sxe->addChild('programCode'));
            }
        }
        if (isset($this->servicedDate)) {
            $this->servicedDate->xmlSerialize(true, $sxe->addChild('servicedDate'));
        }
        if (isset($this->servicedPeriod)) {
            $this->servicedPeriod->xmlSerialize(true, $sxe->addChild('servicedPeriod'));
        }
        if (isset($this->locationCodeableConcept)) {
            $this->locationCodeableConcept->xmlSerialize(true, $sxe->addChild('locationCodeableConcept'));
        }
        if (isset($this->locationAddress)) {
            $this->locationAddress->xmlSerialize(true, $sxe->addChild('locationAddress'));
        }
        if (isset($this->locationReference)) {
            $this->locationReference->xmlSerialize(true, $sxe->addChild('locationReference'));
        }
        if (isset($this->quantity)) {
            $this->quantity->xmlSerialize(true, $sxe->addChild('quantity'));
        }
        if (isset($this->unitPrice)) {
            $this->unitPrice->xmlSerialize(true, $sxe->addChild('unitPrice'));
        }
        if (isset($this->factor)) {
            $this->factor->xmlSerialize(true, $sxe->addChild('factor'));
        }
        if (isset($this->net)) {
            $this->net->xmlSerialize(true, $sxe->addChild('net'));
        }
        if (0 < count($this->udi)) {
            foreach ($this->udi as $udi) {
                $udi->xmlSerialize(true, $sxe->addChild('udi'));
            }
        }
        if (isset($this->bodySite)) {
            $this->bodySite->xmlSerialize(true, $sxe->addChild('bodySite'));
        }
        if (0 < count($this->subSite)) {
            foreach ($this->subSite as $subSite) {
                $subSite->xmlSerialize(true, $sxe->addChild('subSite'));
            }
        }
        if (0 < count($this->encounter)) {
            foreach ($this->encounter as $encounter) {
                $encounter->xmlSerialize(true, $sxe->addChild('encounter'));
            }
        }
        if (0 < count($this->noteNumber)) {
            foreach ($this->noteNumber as $noteNumber) {
                $noteNumber->xmlSerialize(true, $sxe->addChild('noteNumber'));
            }
        }
        if (0 < count($this->adjudication)) {
            foreach ($this->adjudication as $adjudication) {
                $adjudication->xmlSerialize(true, $sxe->addChild('adjudication'));
            }
        }
        if (0 < count($this->detail)) {
            foreach ($this->detail as $detail) {
                $detail->xmlSerialize(true, $sxe->addChild('detail'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
