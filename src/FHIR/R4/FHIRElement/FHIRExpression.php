<?php

namespace OpenEMR\FHIR\R4\FHIRElement;

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

use OpenEMR\FHIR\R4\FHIRElement;

/**
 * A expression that is evaluated in a specified context and returns a value. The context of use of the expression must specify the context in which the expression is evaluated, and how the result of the expression is used.
 * If the element is present, it must have a value for at least one of the defined elements, an @id referenced from the Narrative, or extensions
 */
class FHIRExpression extends FHIRElement implements \JsonSerializable
{
    /**
     * A brief, natural language description of the condition that effectively communicates the intended semantics.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $description = null;

    /**
     * A short name assigned to the expression to allow for multiple reuse of the expression in the context where it is defined.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    public $name = null;

    /**
     * The media type of the language for the expression.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRExpressionLanguage
     */
    public $language = null;

    /**
     * An expression in the specified language that returns a value.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $expression = null;

    /**
     * A URI that defines where the expression is found.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public $reference = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Expression';

    /**
     * A brief, natural language description of the condition that effectively communicates the intended semantics.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * A brief, natural language description of the condition that effectively communicates the intended semantics.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * A short name assigned to the expression to allow for multiple reuse of the expression in the context where it is defined.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * A short name assigned to the expression to allow for multiple reuse of the expression in the context where it is defined.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRId $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * The media type of the language for the expression.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRExpressionLanguage
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * The media type of the language for the expression.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRExpressionLanguage $language
     * @return $this
     */
    public function setLanguage($language)
    {
        $this->language = $language;
        return $this;
    }

    /**
     * An expression in the specified language that returns a value.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * An expression in the specified language that returns a value.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $expression
     * @return $this
     */
    public function setExpression($expression)
    {
        $this->expression = $expression;
        return $this;
    }

    /**
     * A URI that defines where the expression is found.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * A URI that defines where the expression is found.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUri $reference
     * @return $this
     */
    public function setReference($reference)
    {
        $this->reference = $reference;
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
            if (isset($data['description'])) {
                $this->setDescription($data['description']);
            }
            if (isset($data['name'])) {
                $this->setName($data['name']);
            }
            if (isset($data['language'])) {
                $this->setLanguage($data['language']);
            }
            if (isset($data['expression'])) {
                $this->setExpression($data['expression']);
            }
            if (isset($data['reference'])) {
                $this->setReference($data['reference']);
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
        if (isset($this->description)) {
            $json['description'] = $this->description;
        }
        if (isset($this->name)) {
            $json['name'] = $this->name;
        }
        if (isset($this->language)) {
            $json['language'] = $this->language;
        }
        if (isset($this->expression)) {
            $json['expression'] = $this->expression;
        }
        if (isset($this->reference)) {
            $json['reference'] = $this->reference;
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
            $sxe = new \SimpleXMLElement('<Expression xmlns="http://hl7.org/fhir"></Expression>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->description)) {
            $this->description->xmlSerialize(true, $sxe->addChild('description'));
        }
        if (isset($this->name)) {
            $this->name->xmlSerialize(true, $sxe->addChild('name'));
        }
        if (isset($this->language)) {
            $this->language->xmlSerialize(true, $sxe->addChild('language'));
        }
        if (isset($this->expression)) {
            $this->expression->xmlSerialize(true, $sxe->addChild('expression'));
        }
        if (isset($this->reference)) {
            $this->reference->xmlSerialize(true, $sxe->addChild('reference'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
