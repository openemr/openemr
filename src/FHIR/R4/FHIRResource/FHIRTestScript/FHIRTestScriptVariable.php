<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRTestScript;

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
 * A structured set of tests against a FHIR server or client implementation to determine compliance against the FHIR specification.
 */
class FHIRTestScriptVariable extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Descriptive name for this variable.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $name = null;

    /**
     * A default, hard-coded, or user-defined value for this variable.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $defaultValue = null;

    /**
     * A free text natural language description of the variable and its purpose.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $description = null;

    /**
     * The FHIRPath expression to evaluate against the fixture body. When variables are defined, only one of either expression, headerField or path must be specified.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $expression = null;

    /**
     * Will be used to grab the HTTP header field value from the headers that sourceId is pointing to.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $headerField = null;

    /**
     * Displayable text string with hint help information to the user when entering a default value.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $hint = null;

    /**
     * XPath or JSONPath to evaluate against the fixture body.  When variables are defined, only one of either expression, headerField or path must be specified.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $path = null;

    /**
     * Fixture to evaluate the XPath/JSONPath expression or the headerField  against within this variable.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    public $sourceId = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'TestScript.Variable';

    /**
     * Descriptive name for this variable.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Descriptive name for this variable.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * A default, hard-coded, or user-defined value for this variable.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * A default, hard-coded, or user-defined value for this variable.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $defaultValue
     * @return $this
     */
    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;
        return $this;
    }

    /**
     * A free text natural language description of the variable and its purpose.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * A free text natural language description of the variable and its purpose.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * The FHIRPath expression to evaluate against the fixture body. When variables are defined, only one of either expression, headerField or path must be specified.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * The FHIRPath expression to evaluate against the fixture body. When variables are defined, only one of either expression, headerField or path must be specified.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $expression
     * @return $this
     */
    public function setExpression($expression)
    {
        $this->expression = $expression;
        return $this;
    }

    /**
     * Will be used to grab the HTTP header field value from the headers that sourceId is pointing to.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getHeaderField()
    {
        return $this->headerField;
    }

    /**
     * Will be used to grab the HTTP header field value from the headers that sourceId is pointing to.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $headerField
     * @return $this
     */
    public function setHeaderField($headerField)
    {
        $this->headerField = $headerField;
        return $this;
    }

    /**
     * Displayable text string with hint help information to the user when entering a default value.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getHint()
    {
        return $this->hint;
    }

    /**
     * Displayable text string with hint help information to the user when entering a default value.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $hint
     * @return $this
     */
    public function setHint($hint)
    {
        $this->hint = $hint;
        return $this;
    }

    /**
     * XPath or JSONPath to evaluate against the fixture body.  When variables are defined, only one of either expression, headerField or path must be specified.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * XPath or JSONPath to evaluate against the fixture body.  When variables are defined, only one of either expression, headerField or path must be specified.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * Fixture to evaluate the XPath/JSONPath expression or the headerField  against within this variable.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    public function getSourceId()
    {
        return $this->sourceId;
    }

    /**
     * Fixture to evaluate the XPath/JSONPath expression or the headerField  against within this variable.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRId $sourceId
     * @return $this
     */
    public function setSourceId($sourceId)
    {
        $this->sourceId = $sourceId;
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
            if (isset($data['name'])) {
                $this->setName($data['name']);
            }
            if (isset($data['defaultValue'])) {
                $this->setDefaultValue($data['defaultValue']);
            }
            if (isset($data['description'])) {
                $this->setDescription($data['description']);
            }
            if (isset($data['expression'])) {
                $this->setExpression($data['expression']);
            }
            if (isset($data['headerField'])) {
                $this->setHeaderField($data['headerField']);
            }
            if (isset($data['hint'])) {
                $this->setHint($data['hint']);
            }
            if (isset($data['path'])) {
                $this->setPath($data['path']);
            }
            if (isset($data['sourceId'])) {
                $this->setSourceId($data['sourceId']);
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
        if (isset($this->name)) {
            $json['name'] = $this->name;
        }
        if (isset($this->defaultValue)) {
            $json['defaultValue'] = $this->defaultValue;
        }
        if (isset($this->description)) {
            $json['description'] = $this->description;
        }
        if (isset($this->expression)) {
            $json['expression'] = $this->expression;
        }
        if (isset($this->headerField)) {
            $json['headerField'] = $this->headerField;
        }
        if (isset($this->hint)) {
            $json['hint'] = $this->hint;
        }
        if (isset($this->path)) {
            $json['path'] = $this->path;
        }
        if (isset($this->sourceId)) {
            $json['sourceId'] = $this->sourceId;
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
            $sxe = new \SimpleXMLElement('<TestScriptVariable xmlns="http://hl7.org/fhir"></TestScriptVariable>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->name)) {
            $this->name->xmlSerialize(true, $sxe->addChild('name'));
        }
        if (isset($this->defaultValue)) {
            $this->defaultValue->xmlSerialize(true, $sxe->addChild('defaultValue'));
        }
        if (isset($this->description)) {
            $this->description->xmlSerialize(true, $sxe->addChild('description'));
        }
        if (isset($this->expression)) {
            $this->expression->xmlSerialize(true, $sxe->addChild('expression'));
        }
        if (isset($this->headerField)) {
            $this->headerField->xmlSerialize(true, $sxe->addChild('headerField'));
        }
        if (isset($this->hint)) {
            $this->hint->xmlSerialize(true, $sxe->addChild('hint'));
        }
        if (isset($this->path)) {
            $this->path->xmlSerialize(true, $sxe->addChild('path'));
        }
        if (isset($this->sourceId)) {
            $this->sourceId->xmlSerialize(true, $sxe->addChild('sourceId'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
