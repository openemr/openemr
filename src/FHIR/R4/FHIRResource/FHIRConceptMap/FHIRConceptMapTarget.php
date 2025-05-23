<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRConceptMap;

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
 * A statement of relationships from one set of concepts to one or more other concepts - either concepts in code systems, or data element/data element concepts, or classes in class models.
 */
class FHIRConceptMapTarget extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Identity (code or path) or the element/item that the map refers to.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCode
     */
    public $code = null;

    /**
     * The display for the code. The display is only provided to help editors when editing the concept map.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $display = null;

    /**
     * The equivalence between the source and target concepts (counting for the dependencies and products). The equivalence is read from target to source (e.g. the target is 'wider' than the source).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRConceptMapEquivalence
     */
    public $equivalence = null;

    /**
     * A description of status/issues in mapping that conveys additional information not represented in  the structured data.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $comment = null;

    /**
     * A set of additional dependencies for this mapping to hold. This mapping is only applicable if the specified element can be resolved, and it has the specified value.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRConceptMap\FHIRConceptMapDependsOn[]
     */
    public $dependsOn = [];

    /**
     * A set of additional outcomes from this mapping to other elements. To properly execute this mapping, the specified element must be mapped to some data element or source that is in context. The mapping may still be useful without a place for the additional data elements, but the equivalence cannot be relied on.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRConceptMap\FHIRConceptMapDependsOn[]
     */
    public $product = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'ConceptMap.Target';

    /**
     * Identity (code or path) or the element/item that the map refers to.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCode
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Identity (code or path) or the element/item that the map refers to.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCode $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * The display for the code. The display is only provided to help editors when editing the concept map.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getDisplay()
    {
        return $this->display;
    }

    /**
     * The display for the code. The display is only provided to help editors when editing the concept map.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $display
     * @return $this
     */
    public function setDisplay($display)
    {
        $this->display = $display;
        return $this;
    }

    /**
     * The equivalence between the source and target concepts (counting for the dependencies and products). The equivalence is read from target to source (e.g. the target is 'wider' than the source).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRConceptMapEquivalence
     */
    public function getEquivalence()
    {
        return $this->equivalence;
    }

    /**
     * The equivalence between the source and target concepts (counting for the dependencies and products). The equivalence is read from target to source (e.g. the target is 'wider' than the source).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRConceptMapEquivalence $equivalence
     * @return $this
     */
    public function setEquivalence($equivalence)
    {
        $this->equivalence = $equivalence;
        return $this;
    }

    /**
     * A description of status/issues in mapping that conveys additional information not represented in  the structured data.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * A description of status/issues in mapping that conveys additional information not represented in  the structured data.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $comment
     * @return $this
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * A set of additional dependencies for this mapping to hold. This mapping is only applicable if the specified element can be resolved, and it has the specified value.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRConceptMap\FHIRConceptMapDependsOn[]
     */
    public function getDependsOn()
    {
        return $this->dependsOn;
    }

    /**
     * A set of additional dependencies for this mapping to hold. This mapping is only applicable if the specified element can be resolved, and it has the specified value.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRConceptMap\FHIRConceptMapDependsOn $dependsOn
     * @return $this
     */
    public function addDependsOn($dependsOn)
    {
        $this->dependsOn[] = $dependsOn;
        return $this;
    }

    /**
     * A set of additional outcomes from this mapping to other elements. To properly execute this mapping, the specified element must be mapped to some data element or source that is in context. The mapping may still be useful without a place for the additional data elements, but the equivalence cannot be relied on.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRConceptMap\FHIRConceptMapDependsOn[]
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * A set of additional outcomes from this mapping to other elements. To properly execute this mapping, the specified element must be mapped to some data element or source that is in context. The mapping may still be useful without a place for the additional data elements, but the equivalence cannot be relied on.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRConceptMap\FHIRConceptMapDependsOn $product
     * @return $this
     */
    public function addProduct($product)
    {
        $this->product[] = $product;
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
            if (isset($data['display'])) {
                $this->setDisplay($data['display']);
            }
            if (isset($data['equivalence'])) {
                $this->setEquivalence($data['equivalence']);
            }
            if (isset($data['comment'])) {
                $this->setComment($data['comment']);
            }
            if (isset($data['dependsOn'])) {
                if (is_array($data['dependsOn'])) {
                    foreach ($data['dependsOn'] as $d) {
                        $this->addDependsOn($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"dependsOn" must be array of objects or null, ' . gettype($data['dependsOn']) . ' seen.');
                }
            }
            if (isset($data['product'])) {
                if (is_array($data['product'])) {
                    foreach ($data['product'] as $d) {
                        $this->addProduct($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"product" must be array of objects or null, ' . gettype($data['product']) . ' seen.');
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
        if (isset($this->code)) {
            $json['code'] = $this->code;
        }
        if (isset($this->display)) {
            $json['display'] = $this->display;
        }
        if (isset($this->equivalence)) {
            $json['equivalence'] = $this->equivalence;
        }
        if (isset($this->comment)) {
            $json['comment'] = $this->comment;
        }
        if (0 < count($this->dependsOn)) {
            $json['dependsOn'] = [];
            foreach ($this->dependsOn as $dependsOn) {
                $json['dependsOn'][] = $dependsOn;
            }
        }
        if (0 < count($this->product)) {
            $json['product'] = [];
            foreach ($this->product as $product) {
                $json['product'][] = $product;
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
            $sxe = new \SimpleXMLElement('<ConceptMapTarget xmlns="http://hl7.org/fhir"></ConceptMapTarget>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->code)) {
            $this->code->xmlSerialize(true, $sxe->addChild('code'));
        }
        if (isset($this->display)) {
            $this->display->xmlSerialize(true, $sxe->addChild('display'));
        }
        if (isset($this->equivalence)) {
            $this->equivalence->xmlSerialize(true, $sxe->addChild('equivalence'));
        }
        if (isset($this->comment)) {
            $this->comment->xmlSerialize(true, $sxe->addChild('comment'));
        }
        if (0 < count($this->dependsOn)) {
            foreach ($this->dependsOn as $dependsOn) {
                $dependsOn->xmlSerialize(true, $sxe->addChild('dependsOn'));
            }
        }
        if (0 < count($this->product)) {
            foreach ($this->product as $product) {
                $product->xmlSerialize(true, $sxe->addChild('product'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
