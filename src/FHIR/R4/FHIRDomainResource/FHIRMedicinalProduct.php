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
 * Detailed definition of a medicinal product, typically for uses other than direct patient care (e.g. regulatory use).
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRMedicinalProduct extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * Business identifier for this product. Could be an MPID.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = [];

    /**
     * Regulatory type, e.g. Investigational or Authorized.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $type = null;

    /**
     * If this medicine applies to human or veterinary uses.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding
     */
    public $domain = null;

    /**
     * The dose form for a single part product, or combined form of a multiple part product.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $combinedPharmaceuticalDoseForm = null;

    /**
     * The legal status of supply of the medicinal product as classified by the regulator.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $legalStatusOfSupply = null;

    /**
     * Whether the Medicinal Product is subject to additional monitoring for regulatory reasons.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $additionalMonitoringIndicator = null;

    /**
     * Whether the Medicinal Product is subject to special measures for regulatory reasons.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public $specialMeasures = [];

    /**
     * If authorised for use in children.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $paediatricUseIndicator = null;

    /**
     * Allows the product to be classified by various systems.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $productClassification = [];

    /**
     * Marketing status of the medicinal product, in contrast to marketing authorizaton.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMarketingStatus[]
     */
    public $marketingStatus = [];

    /**
     * Pharmaceutical aspects of product.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $pharmaceuticalProduct = [];

    /**
     * Package representation for the product.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $packagedMedicinalProduct = [];

    /**
     * Supporting documentation, typically for regulatory submission.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $attachedDocument = [];

    /**
     * A master file for to the medicinal product (e.g. Pharmacovigilance System Master File).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $masterFile = [];

    /**
     * A product specific contact, person (in a role), or an organization.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $contact = [];

    /**
     * Clinical trials or studies that this product is involved in.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $clinicalTrial = [];

    /**
     * The product's name, including full name and possibly coded parts.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProduct\FHIRMedicinalProductName[]
     */
    public $name = [];

    /**
     * Reference to another product, e.g. for linking authorised to investigational product.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public $crossReference = [];

    /**
     * An operation applied to the product, for manufacturing or adminsitrative purpose.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProduct\FHIRMedicinalProductManufacturingBusinessOperation[]
     */
    public $manufacturingBusinessOperation = [];

    /**
     * Indicates if the medicinal product has an orphan designation for the treatment of a rare disease.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProduct\FHIRMedicinalProductSpecialDesignation[]
     */
    public $specialDesignation = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'MedicinalProduct';

    /**
     * Business identifier for this product. Could be an MPID.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Business identifier for this product. Could be an MPID.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * Regulatory type, e.g. Investigational or Authorized.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Regulatory type, e.g. Investigational or Authorized.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * If this medicine applies to human or veterinary uses.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * If this medicine applies to human or veterinary uses.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding $domain
     * @return $this
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * The dose form for a single part product, or combined form of a multiple part product.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getCombinedPharmaceuticalDoseForm()
    {
        return $this->combinedPharmaceuticalDoseForm;
    }

    /**
     * The dose form for a single part product, or combined form of a multiple part product.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $combinedPharmaceuticalDoseForm
     * @return $this
     */
    public function setCombinedPharmaceuticalDoseForm($combinedPharmaceuticalDoseForm)
    {
        $this->combinedPharmaceuticalDoseForm = $combinedPharmaceuticalDoseForm;
        return $this;
    }

    /**
     * The legal status of supply of the medicinal product as classified by the regulator.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getLegalStatusOfSupply()
    {
        return $this->legalStatusOfSupply;
    }

    /**
     * The legal status of supply of the medicinal product as classified by the regulator.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $legalStatusOfSupply
     * @return $this
     */
    public function setLegalStatusOfSupply($legalStatusOfSupply)
    {
        $this->legalStatusOfSupply = $legalStatusOfSupply;
        return $this;
    }

    /**
     * Whether the Medicinal Product is subject to additional monitoring for regulatory reasons.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getAdditionalMonitoringIndicator()
    {
        return $this->additionalMonitoringIndicator;
    }

    /**
     * Whether the Medicinal Product is subject to additional monitoring for regulatory reasons.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $additionalMonitoringIndicator
     * @return $this
     */
    public function setAdditionalMonitoringIndicator($additionalMonitoringIndicator)
    {
        $this->additionalMonitoringIndicator = $additionalMonitoringIndicator;
        return $this;
    }

    /**
     * Whether the Medicinal Product is subject to special measures for regulatory reasons.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public function getSpecialMeasures()
    {
        return $this->specialMeasures;
    }

    /**
     * Whether the Medicinal Product is subject to special measures for regulatory reasons.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $specialMeasures
     * @return $this
     */
    public function addSpecialMeasures($specialMeasures)
    {
        $this->specialMeasures[] = $specialMeasures;
        return $this;
    }

    /**
     * If authorised for use in children.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getPaediatricUseIndicator()
    {
        return $this->paediatricUseIndicator;
    }

    /**
     * If authorised for use in children.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $paediatricUseIndicator
     * @return $this
     */
    public function setPaediatricUseIndicator($paediatricUseIndicator)
    {
        $this->paediatricUseIndicator = $paediatricUseIndicator;
        return $this;
    }

    /**
     * Allows the product to be classified by various systems.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getProductClassification()
    {
        return $this->productClassification;
    }

    /**
     * Allows the product to be classified by various systems.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $productClassification
     * @return $this
     */
    public function addProductClassification($productClassification)
    {
        $this->productClassification[] = $productClassification;
        return $this;
    }

    /**
     * Marketing status of the medicinal product, in contrast to marketing authorizaton.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMarketingStatus[]
     */
    public function getMarketingStatus()
    {
        return $this->marketingStatus;
    }

    /**
     * Marketing status of the medicinal product, in contrast to marketing authorizaton.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMarketingStatus $marketingStatus
     * @return $this
     */
    public function addMarketingStatus($marketingStatus)
    {
        $this->marketingStatus[] = $marketingStatus;
        return $this;
    }

    /**
     * Pharmaceutical aspects of product.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getPharmaceuticalProduct()
    {
        return $this->pharmaceuticalProduct;
    }

    /**
     * Pharmaceutical aspects of product.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $pharmaceuticalProduct
     * @return $this
     */
    public function addPharmaceuticalProduct($pharmaceuticalProduct)
    {
        $this->pharmaceuticalProduct[] = $pharmaceuticalProduct;
        return $this;
    }

    /**
     * Package representation for the product.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getPackagedMedicinalProduct()
    {
        return $this->packagedMedicinalProduct;
    }

    /**
     * Package representation for the product.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $packagedMedicinalProduct
     * @return $this
     */
    public function addPackagedMedicinalProduct($packagedMedicinalProduct)
    {
        $this->packagedMedicinalProduct[] = $packagedMedicinalProduct;
        return $this;
    }

    /**
     * Supporting documentation, typically for regulatory submission.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getAttachedDocument()
    {
        return $this->attachedDocument;
    }

    /**
     * Supporting documentation, typically for regulatory submission.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $attachedDocument
     * @return $this
     */
    public function addAttachedDocument($attachedDocument)
    {
        $this->attachedDocument[] = $attachedDocument;
        return $this;
    }

    /**
     * A master file for to the medicinal product (e.g. Pharmacovigilance System Master File).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getMasterFile()
    {
        return $this->masterFile;
    }

    /**
     * A master file for to the medicinal product (e.g. Pharmacovigilance System Master File).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $masterFile
     * @return $this
     */
    public function addMasterFile($masterFile)
    {
        $this->masterFile[] = $masterFile;
        return $this;
    }

    /**
     * A product specific contact, person (in a role), or an organization.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * A product specific contact, person (in a role), or an organization.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $contact
     * @return $this
     */
    public function addContact($contact)
    {
        $this->contact[] = $contact;
        return $this;
    }

    /**
     * Clinical trials or studies that this product is involved in.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getClinicalTrial()
    {
        return $this->clinicalTrial;
    }

    /**
     * Clinical trials or studies that this product is involved in.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $clinicalTrial
     * @return $this
     */
    public function addClinicalTrial($clinicalTrial)
    {
        $this->clinicalTrial[] = $clinicalTrial;
        return $this;
    }

    /**
     * The product's name, including full name and possibly coded parts.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProduct\FHIRMedicinalProductName[]
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * The product's name, including full name and possibly coded parts.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProduct\FHIRMedicinalProductName $name
     * @return $this
     */
    public function addName($name)
    {
        $this->name[] = $name;
        return $this;
    }

    /**
     * Reference to another product, e.g. for linking authorised to investigational product.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getCrossReference()
    {
        return $this->crossReference;
    }

    /**
     * Reference to another product, e.g. for linking authorised to investigational product.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $crossReference
     * @return $this
     */
    public function addCrossReference($crossReference)
    {
        $this->crossReference[] = $crossReference;
        return $this;
    }

    /**
     * An operation applied to the product, for manufacturing or adminsitrative purpose.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProduct\FHIRMedicinalProductManufacturingBusinessOperation[]
     */
    public function getManufacturingBusinessOperation()
    {
        return $this->manufacturingBusinessOperation;
    }

    /**
     * An operation applied to the product, for manufacturing or adminsitrative purpose.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProduct\FHIRMedicinalProductManufacturingBusinessOperation $manufacturingBusinessOperation
     * @return $this
     */
    public function addManufacturingBusinessOperation($manufacturingBusinessOperation)
    {
        $this->manufacturingBusinessOperation[] = $manufacturingBusinessOperation;
        return $this;
    }

    /**
     * Indicates if the medicinal product has an orphan designation for the treatment of a rare disease.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProduct\FHIRMedicinalProductSpecialDesignation[]
     */
    public function getSpecialDesignation()
    {
        return $this->specialDesignation;
    }

    /**
     * Indicates if the medicinal product has an orphan designation for the treatment of a rare disease.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProduct\FHIRMedicinalProductSpecialDesignation $specialDesignation
     * @return $this
     */
    public function addSpecialDesignation($specialDesignation)
    {
        $this->specialDesignation[] = $specialDesignation;
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
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['domain'])) {
                $this->setDomain($data['domain']);
            }
            if (isset($data['combinedPharmaceuticalDoseForm'])) {
                $this->setCombinedPharmaceuticalDoseForm($data['combinedPharmaceuticalDoseForm']);
            }
            if (isset($data['legalStatusOfSupply'])) {
                $this->setLegalStatusOfSupply($data['legalStatusOfSupply']);
            }
            if (isset($data['additionalMonitoringIndicator'])) {
                $this->setAdditionalMonitoringIndicator($data['additionalMonitoringIndicator']);
            }
            if (isset($data['specialMeasures'])) {
                if (is_array($data['specialMeasures'])) {
                    foreach ($data['specialMeasures'] as $d) {
                        $this->addSpecialMeasures($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"specialMeasures" must be array of objects or null, ' . gettype($data['specialMeasures']) . ' seen.');
                }
            }
            if (isset($data['paediatricUseIndicator'])) {
                $this->setPaediatricUseIndicator($data['paediatricUseIndicator']);
            }
            if (isset($data['productClassification'])) {
                if (is_array($data['productClassification'])) {
                    foreach ($data['productClassification'] as $d) {
                        $this->addProductClassification($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"productClassification" must be array of objects or null, ' . gettype($data['productClassification']) . ' seen.');
                }
            }
            if (isset($data['marketingStatus'])) {
                if (is_array($data['marketingStatus'])) {
                    foreach ($data['marketingStatus'] as $d) {
                        $this->addMarketingStatus($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"marketingStatus" must be array of objects or null, ' . gettype($data['marketingStatus']) . ' seen.');
                }
            }
            if (isset($data['pharmaceuticalProduct'])) {
                if (is_array($data['pharmaceuticalProduct'])) {
                    foreach ($data['pharmaceuticalProduct'] as $d) {
                        $this->addPharmaceuticalProduct($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"pharmaceuticalProduct" must be array of objects or null, ' . gettype($data['pharmaceuticalProduct']) . ' seen.');
                }
            }
            if (isset($data['packagedMedicinalProduct'])) {
                if (is_array($data['packagedMedicinalProduct'])) {
                    foreach ($data['packagedMedicinalProduct'] as $d) {
                        $this->addPackagedMedicinalProduct($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"packagedMedicinalProduct" must be array of objects or null, ' . gettype($data['packagedMedicinalProduct']) . ' seen.');
                }
            }
            if (isset($data['attachedDocument'])) {
                if (is_array($data['attachedDocument'])) {
                    foreach ($data['attachedDocument'] as $d) {
                        $this->addAttachedDocument($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"attachedDocument" must be array of objects or null, ' . gettype($data['attachedDocument']) . ' seen.');
                }
            }
            if (isset($data['masterFile'])) {
                if (is_array($data['masterFile'])) {
                    foreach ($data['masterFile'] as $d) {
                        $this->addMasterFile($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"masterFile" must be array of objects or null, ' . gettype($data['masterFile']) . ' seen.');
                }
            }
            if (isset($data['contact'])) {
                if (is_array($data['contact'])) {
                    foreach ($data['contact'] as $d) {
                        $this->addContact($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"contact" must be array of objects or null, ' . gettype($data['contact']) . ' seen.');
                }
            }
            if (isset($data['clinicalTrial'])) {
                if (is_array($data['clinicalTrial'])) {
                    foreach ($data['clinicalTrial'] as $d) {
                        $this->addClinicalTrial($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"clinicalTrial" must be array of objects or null, ' . gettype($data['clinicalTrial']) . ' seen.');
                }
            }
            if (isset($data['name'])) {
                if (is_array($data['name'])) {
                    foreach ($data['name'] as $d) {
                        $this->addName($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"name" must be array of objects or null, ' . gettype($data['name']) . ' seen.');
                }
            }
            if (isset($data['crossReference'])) {
                if (is_array($data['crossReference'])) {
                    foreach ($data['crossReference'] as $d) {
                        $this->addCrossReference($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"crossReference" must be array of objects or null, ' . gettype($data['crossReference']) . ' seen.');
                }
            }
            if (isset($data['manufacturingBusinessOperation'])) {
                if (is_array($data['manufacturingBusinessOperation'])) {
                    foreach ($data['manufacturingBusinessOperation'] as $d) {
                        $this->addManufacturingBusinessOperation($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"manufacturingBusinessOperation" must be array of objects or null, ' . gettype($data['manufacturingBusinessOperation']) . ' seen.');
                }
            }
            if (isset($data['specialDesignation'])) {
                if (is_array($data['specialDesignation'])) {
                    foreach ($data['specialDesignation'] as $d) {
                        $this->addSpecialDesignation($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"specialDesignation" must be array of objects or null, ' . gettype($data['specialDesignation']) . ' seen.');
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
        $json['resourceType'] = $this->_fhirElementName;
        if (0 < count($this->identifier)) {
            $json['identifier'] = [];
            foreach ($this->identifier as $identifier) {
                $json['identifier'][] = $identifier;
            }
        }
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->domain)) {
            $json['domain'] = $this->domain;
        }
        if (isset($this->combinedPharmaceuticalDoseForm)) {
            $json['combinedPharmaceuticalDoseForm'] = $this->combinedPharmaceuticalDoseForm;
        }
        if (isset($this->legalStatusOfSupply)) {
            $json['legalStatusOfSupply'] = $this->legalStatusOfSupply;
        }
        if (isset($this->additionalMonitoringIndicator)) {
            $json['additionalMonitoringIndicator'] = $this->additionalMonitoringIndicator;
        }
        if (0 < count($this->specialMeasures)) {
            $json['specialMeasures'] = [];
            foreach ($this->specialMeasures as $specialMeasures) {
                $json['specialMeasures'][] = $specialMeasures;
            }
        }
        if (isset($this->paediatricUseIndicator)) {
            $json['paediatricUseIndicator'] = $this->paediatricUseIndicator;
        }
        if (0 < count($this->productClassification)) {
            $json['productClassification'] = [];
            foreach ($this->productClassification as $productClassification) {
                $json['productClassification'][] = $productClassification;
            }
        }
        if (0 < count($this->marketingStatus)) {
            $json['marketingStatus'] = [];
            foreach ($this->marketingStatus as $marketingStatus) {
                $json['marketingStatus'][] = $marketingStatus;
            }
        }
        if (0 < count($this->pharmaceuticalProduct)) {
            $json['pharmaceuticalProduct'] = [];
            foreach ($this->pharmaceuticalProduct as $pharmaceuticalProduct) {
                $json['pharmaceuticalProduct'][] = $pharmaceuticalProduct;
            }
        }
        if (0 < count($this->packagedMedicinalProduct)) {
            $json['packagedMedicinalProduct'] = [];
            foreach ($this->packagedMedicinalProduct as $packagedMedicinalProduct) {
                $json['packagedMedicinalProduct'][] = $packagedMedicinalProduct;
            }
        }
        if (0 < count($this->attachedDocument)) {
            $json['attachedDocument'] = [];
            foreach ($this->attachedDocument as $attachedDocument) {
                $json['attachedDocument'][] = $attachedDocument;
            }
        }
        if (0 < count($this->masterFile)) {
            $json['masterFile'] = [];
            foreach ($this->masterFile as $masterFile) {
                $json['masterFile'][] = $masterFile;
            }
        }
        if (0 < count($this->contact)) {
            $json['contact'] = [];
            foreach ($this->contact as $contact) {
                $json['contact'][] = $contact;
            }
        }
        if (0 < count($this->clinicalTrial)) {
            $json['clinicalTrial'] = [];
            foreach ($this->clinicalTrial as $clinicalTrial) {
                $json['clinicalTrial'][] = $clinicalTrial;
            }
        }
        if (0 < count($this->name)) {
            $json['name'] = [];
            foreach ($this->name as $name) {
                $json['name'][] = $name;
            }
        }
        if (0 < count($this->crossReference)) {
            $json['crossReference'] = [];
            foreach ($this->crossReference as $crossReference) {
                $json['crossReference'][] = $crossReference;
            }
        }
        if (0 < count($this->manufacturingBusinessOperation)) {
            $json['manufacturingBusinessOperation'] = [];
            foreach ($this->manufacturingBusinessOperation as $manufacturingBusinessOperation) {
                $json['manufacturingBusinessOperation'][] = $manufacturingBusinessOperation;
            }
        }
        if (0 < count($this->specialDesignation)) {
            $json['specialDesignation'] = [];
            foreach ($this->specialDesignation as $specialDesignation) {
                $json['specialDesignation'][] = $specialDesignation;
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
            $sxe = new \SimpleXMLElement('<MedicinalProduct xmlns="http://hl7.org/fhir"></MedicinalProduct>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->identifier)) {
            foreach ($this->identifier as $identifier) {
                $identifier->xmlSerialize(true, $sxe->addChild('identifier'));
            }
        }
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->domain)) {
            $this->domain->xmlSerialize(true, $sxe->addChild('domain'));
        }
        if (isset($this->combinedPharmaceuticalDoseForm)) {
            $this->combinedPharmaceuticalDoseForm->xmlSerialize(true, $sxe->addChild('combinedPharmaceuticalDoseForm'));
        }
        if (isset($this->legalStatusOfSupply)) {
            $this->legalStatusOfSupply->xmlSerialize(true, $sxe->addChild('legalStatusOfSupply'));
        }
        if (isset($this->additionalMonitoringIndicator)) {
            $this->additionalMonitoringIndicator->xmlSerialize(true, $sxe->addChild('additionalMonitoringIndicator'));
        }
        if (0 < count($this->specialMeasures)) {
            foreach ($this->specialMeasures as $specialMeasures) {
                $specialMeasures->xmlSerialize(true, $sxe->addChild('specialMeasures'));
            }
        }
        if (isset($this->paediatricUseIndicator)) {
            $this->paediatricUseIndicator->xmlSerialize(true, $sxe->addChild('paediatricUseIndicator'));
        }
        if (0 < count($this->productClassification)) {
            foreach ($this->productClassification as $productClassification) {
                $productClassification->xmlSerialize(true, $sxe->addChild('productClassification'));
            }
        }
        if (0 < count($this->marketingStatus)) {
            foreach ($this->marketingStatus as $marketingStatus) {
                $marketingStatus->xmlSerialize(true, $sxe->addChild('marketingStatus'));
            }
        }
        if (0 < count($this->pharmaceuticalProduct)) {
            foreach ($this->pharmaceuticalProduct as $pharmaceuticalProduct) {
                $pharmaceuticalProduct->xmlSerialize(true, $sxe->addChild('pharmaceuticalProduct'));
            }
        }
        if (0 < count($this->packagedMedicinalProduct)) {
            foreach ($this->packagedMedicinalProduct as $packagedMedicinalProduct) {
                $packagedMedicinalProduct->xmlSerialize(true, $sxe->addChild('packagedMedicinalProduct'));
            }
        }
        if (0 < count($this->attachedDocument)) {
            foreach ($this->attachedDocument as $attachedDocument) {
                $attachedDocument->xmlSerialize(true, $sxe->addChild('attachedDocument'));
            }
        }
        if (0 < count($this->masterFile)) {
            foreach ($this->masterFile as $masterFile) {
                $masterFile->xmlSerialize(true, $sxe->addChild('masterFile'));
            }
        }
        if (0 < count($this->contact)) {
            foreach ($this->contact as $contact) {
                $contact->xmlSerialize(true, $sxe->addChild('contact'));
            }
        }
        if (0 < count($this->clinicalTrial)) {
            foreach ($this->clinicalTrial as $clinicalTrial) {
                $clinicalTrial->xmlSerialize(true, $sxe->addChild('clinicalTrial'));
            }
        }
        if (0 < count($this->name)) {
            foreach ($this->name as $name) {
                $name->xmlSerialize(true, $sxe->addChild('name'));
            }
        }
        if (0 < count($this->crossReference)) {
            foreach ($this->crossReference as $crossReference) {
                $crossReference->xmlSerialize(true, $sxe->addChild('crossReference'));
            }
        }
        if (0 < count($this->manufacturingBusinessOperation)) {
            foreach ($this->manufacturingBusinessOperation as $manufacturingBusinessOperation) {
                $manufacturingBusinessOperation->xmlSerialize(true, $sxe->addChild('manufacturingBusinessOperation'));
            }
        }
        if (0 < count($this->specialDesignation)) {
            foreach ($this->specialDesignation as $specialDesignation) {
                $specialDesignation->xmlSerialize(true, $sxe->addChild('specialDesignation'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
