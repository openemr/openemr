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
 * A material substance originating from a biological entity intended to be transplanted or infused
into another (possibly the same) biological entity.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRBiologicallyDerivedProduct extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * This records identifiers associated with this biologically derived product instance that are defined by business processes and/or used to refer to it when a direct URL reference to the resource itself is not appropriate (e.g. in CDA documents, or in written / printed documentation).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = [];

    /**
     * Broad category of this product.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBiologicallyDerivedProductCategory
     */
    public $productCategory = null;

    /**
     * A code that identifies the kind of this biologically derived product (SNOMED Ctcode).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $productCode = null;

    /**
     * Whether the product is currently available.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBiologicallyDerivedProductStatus
     */
    public $status = null;

    /**
     * Procedure request to obtain this biologically derived product.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $request = [];

    /**
     * Number of discrete units within this product.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public $quantity = null;

    /**
     * Parent product (if any).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $parent = [];

    /**
     * How this product was collected.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRBiologicallyDerivedProduct\FHIRBiologicallyDerivedProductCollection
     */
    public $collection = null;

    /**
     * Any processing of the product during collection that does not change the fundamental nature of the product. For example adding anti-coagulants during the collection of Peripheral Blood Stem Cells.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRBiologicallyDerivedProduct\FHIRBiologicallyDerivedProductProcessing[]
     */
    public $processing = [];

    /**
     * Any manipulation of product post-collection that is intended to alter the product.  For example a buffy-coat enrichment or CD8 reduction of Peripheral Blood Stem Cells to make it more suitable for infusion.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRBiologicallyDerivedProduct\FHIRBiologicallyDerivedProductManipulation
     */
    public $manipulation = null;

    /**
     * Product storage.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRBiologicallyDerivedProduct\FHIRBiologicallyDerivedProductStorage[]
     */
    public $storage = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'BiologicallyDerivedProduct';

    /**
     * This records identifiers associated with this biologically derived product instance that are defined by business processes and/or used to refer to it when a direct URL reference to the resource itself is not appropriate (e.g. in CDA documents, or in written / printed documentation).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * This records identifiers associated with this biologically derived product instance that are defined by business processes and/or used to refer to it when a direct URL reference to the resource itself is not appropriate (e.g. in CDA documents, or in written / printed documentation).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * Broad category of this product.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBiologicallyDerivedProductCategory
     */
    public function getProductCategory()
    {
        return $this->productCategory;
    }

    /**
     * Broad category of this product.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBiologicallyDerivedProductCategory $productCategory
     * @return $this
     */
    public function setProductCategory($productCategory)
    {
        $this->productCategory = $productCategory;
        return $this;
    }

    /**
     * A code that identifies the kind of this biologically derived product (SNOMED Ctcode).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getProductCode()
    {
        return $this->productCode;
    }

    /**
     * A code that identifies the kind of this biologically derived product (SNOMED Ctcode).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $productCode
     * @return $this
     */
    public function setProductCode($productCode)
    {
        $this->productCode = $productCode;
        return $this;
    }

    /**
     * Whether the product is currently available.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBiologicallyDerivedProductStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Whether the product is currently available.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBiologicallyDerivedProductStatus $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Procedure request to obtain this biologically derived product.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Procedure request to obtain this biologically derived product.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $request
     * @return $this
     */
    public function addRequest($request)
    {
        $this->request[] = $request;
        return $this;
    }

    /**
     * Number of discrete units within this product.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Number of discrete units within this product.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger $quantity
     * @return $this
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * Parent product (if any).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Parent product (if any).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $parent
     * @return $this
     */
    public function addParent($parent)
    {
        $this->parent[] = $parent;
        return $this;
    }

    /**
     * How this product was collected.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRBiologicallyDerivedProduct\FHIRBiologicallyDerivedProductCollection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * How this product was collected.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRBiologicallyDerivedProduct\FHIRBiologicallyDerivedProductCollection $collection
     * @return $this
     */
    public function setCollection($collection)
    {
        $this->collection = $collection;
        return $this;
    }

    /**
     * Any processing of the product during collection that does not change the fundamental nature of the product. For example adding anti-coagulants during the collection of Peripheral Blood Stem Cells.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRBiologicallyDerivedProduct\FHIRBiologicallyDerivedProductProcessing[]
     */
    public function getProcessing()
    {
        return $this->processing;
    }

    /**
     * Any processing of the product during collection that does not change the fundamental nature of the product. For example adding anti-coagulants during the collection of Peripheral Blood Stem Cells.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRBiologicallyDerivedProduct\FHIRBiologicallyDerivedProductProcessing $processing
     * @return $this
     */
    public function addProcessing($processing)
    {
        $this->processing[] = $processing;
        return $this;
    }

    /**
     * Any manipulation of product post-collection that is intended to alter the product.  For example a buffy-coat enrichment or CD8 reduction of Peripheral Blood Stem Cells to make it more suitable for infusion.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRBiologicallyDerivedProduct\FHIRBiologicallyDerivedProductManipulation
     */
    public function getManipulation()
    {
        return $this->manipulation;
    }

    /**
     * Any manipulation of product post-collection that is intended to alter the product.  For example a buffy-coat enrichment or CD8 reduction of Peripheral Blood Stem Cells to make it more suitable for infusion.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRBiologicallyDerivedProduct\FHIRBiologicallyDerivedProductManipulation $manipulation
     * @return $this
     */
    public function setManipulation($manipulation)
    {
        $this->manipulation = $manipulation;
        return $this;
    }

    /**
     * Product storage.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRBiologicallyDerivedProduct\FHIRBiologicallyDerivedProductStorage[]
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * Product storage.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRBiologicallyDerivedProduct\FHIRBiologicallyDerivedProductStorage $storage
     * @return $this
     */
    public function addStorage($storage)
    {
        $this->storage[] = $storage;
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
            if (isset($data['productCategory'])) {
                $this->setProductCategory($data['productCategory']);
            }
            if (isset($data['productCode'])) {
                $this->setProductCode($data['productCode']);
            }
            if (isset($data['status'])) {
                $this->setStatus($data['status']);
            }
            if (isset($data['request'])) {
                if (is_array($data['request'])) {
                    foreach ($data['request'] as $d) {
                        $this->addRequest($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"request" must be array of objects or null, ' . gettype($data['request']) . ' seen.');
                }
            }
            if (isset($data['quantity'])) {
                $this->setQuantity($data['quantity']);
            }
            if (isset($data['parent'])) {
                if (is_array($data['parent'])) {
                    foreach ($data['parent'] as $d) {
                        $this->addParent($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"parent" must be array of objects or null, ' . gettype($data['parent']) . ' seen.');
                }
            }
            if (isset($data['collection'])) {
                $this->setCollection($data['collection']);
            }
            if (isset($data['processing'])) {
                if (is_array($data['processing'])) {
                    foreach ($data['processing'] as $d) {
                        $this->addProcessing($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"processing" must be array of objects or null, ' . gettype($data['processing']) . ' seen.');
                }
            }
            if (isset($data['manipulation'])) {
                $this->setManipulation($data['manipulation']);
            }
            if (isset($data['storage'])) {
                if (is_array($data['storage'])) {
                    foreach ($data['storage'] as $d) {
                        $this->addStorage($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"storage" must be array of objects or null, ' . gettype($data['storage']) . ' seen.');
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
        if (0 < count($this->identifier)) {
            $json['identifier'] = [];
            foreach ($this->identifier as $identifier) {
                $json['identifier'][] = $identifier;
            }
        }
        if (isset($this->productCategory)) {
            $json['productCategory'] = $this->productCategory;
        }
        if (isset($this->productCode)) {
            $json['productCode'] = $this->productCode;
        }
        if (isset($this->status)) {
            $json['status'] = $this->status;
        }
        if (0 < count($this->request)) {
            $json['request'] = [];
            foreach ($this->request as $request) {
                $json['request'][] = $request;
            }
        }
        if (isset($this->quantity)) {
            $json['quantity'] = $this->quantity;
        }
        if (0 < count($this->parent)) {
            $json['parent'] = [];
            foreach ($this->parent as $parent) {
                $json['parent'][] = $parent;
            }
        }
        if (isset($this->collection)) {
            $json['collection'] = $this->collection;
        }
        if (0 < count($this->processing)) {
            $json['processing'] = [];
            foreach ($this->processing as $processing) {
                $json['processing'][] = $processing;
            }
        }
        if (isset($this->manipulation)) {
            $json['manipulation'] = $this->manipulation;
        }
        if (0 < count($this->storage)) {
            $json['storage'] = [];
            foreach ($this->storage as $storage) {
                $json['storage'][] = $storage;
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
            $sxe = new \SimpleXMLElement('<BiologicallyDerivedProduct xmlns="http://hl7.org/fhir"></BiologicallyDerivedProduct>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->identifier)) {
            foreach ($this->identifier as $identifier) {
                $identifier->xmlSerialize(true, $sxe->addChild('identifier'));
            }
        }
        if (isset($this->productCategory)) {
            $this->productCategory->xmlSerialize(true, $sxe->addChild('productCategory'));
        }
        if (isset($this->productCode)) {
            $this->productCode->xmlSerialize(true, $sxe->addChild('productCode'));
        }
        if (isset($this->status)) {
            $this->status->xmlSerialize(true, $sxe->addChild('status'));
        }
        if (0 < count($this->request)) {
            foreach ($this->request as $request) {
                $request->xmlSerialize(true, $sxe->addChild('request'));
            }
        }
        if (isset($this->quantity)) {
            $this->quantity->xmlSerialize(true, $sxe->addChild('quantity'));
        }
        if (0 < count($this->parent)) {
            foreach ($this->parent as $parent) {
                $parent->xmlSerialize(true, $sxe->addChild('parent'));
            }
        }
        if (isset($this->collection)) {
            $this->collection->xmlSerialize(true, $sxe->addChild('collection'));
        }
        if (0 < count($this->processing)) {
            foreach ($this->processing as $processing) {
                $processing->xmlSerialize(true, $sxe->addChild('processing'));
            }
        }
        if (isset($this->manipulation)) {
            $this->manipulation->xmlSerialize(true, $sxe->addChild('manipulation'));
        }
        if (0 < count($this->storage)) {
            foreach ($this->storage as $storage) {
                $storage->xmlSerialize(true, $sxe->addChild('storage'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
