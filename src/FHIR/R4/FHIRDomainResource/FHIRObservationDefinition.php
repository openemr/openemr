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
 * Set of definitional characteristics for a kind of observation or measurement produced or consumed by an orderable health care service.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRObservationDefinition extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * A code that classifies the general type of observation.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $category = [];

    /**
     * Describes what will be observed. Sometimes this is called the observation "name".
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $code = null;

    /**
     * A unique identifier assigned to this ObservationDefinition artifact.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = [];

    /**
     * The data types allowed for the value element of the instance observations conforming to this ObservationDefinition.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRObservationDataType[]
     */
    public $permittedDataType = [];

    /**
     * Multiple results allowed for observations conforming to this ObservationDefinition.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $multipleResultsAllowed = null;

    /**
     * The method or technique used to perform the observation.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $method = null;

    /**
     * The preferred name to be used when reporting the results of observations conforming to this ObservationDefinition.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $preferredReportName = null;

    /**
     * Characteristics for quantitative results of this observation.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRObservationDefinition\FHIRObservationDefinitionQuantitativeDetails
     */
    public $quantitativeDetails = null;

    /**
     * Multiple  ranges of results qualified by different contexts for ordinal or continuous observations conforming to this ObservationDefinition.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRObservationDefinition\FHIRObservationDefinitionQualifiedInterval[]
     */
    public $qualifiedInterval = [];

    /**
     * The set of valid coded results for the observations  conforming to this ObservationDefinition.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $validCodedValueSet = null;

    /**
     * The set of normal coded results for the observations conforming to this ObservationDefinition.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $normalCodedValueSet = null;

    /**
     * The set of abnormal coded results for the observation conforming to this ObservationDefinition.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $abnormalCodedValueSet = null;

    /**
     * The set of critical coded results for the observation conforming to this ObservationDefinition.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $criticalCodedValueSet = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'ObservationDefinition';

    /**
     * A code that classifies the general type of observation.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * A code that classifies the general type of observation.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $category
     * @return $this
     */
    public function addCategory($category)
    {
        $this->category[] = $category;
        return $this;
    }

    /**
     * Describes what will be observed. Sometimes this is called the observation "name".
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Describes what will be observed. Sometimes this is called the observation "name".
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * A unique identifier assigned to this ObservationDefinition artifact.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * A unique identifier assigned to this ObservationDefinition artifact.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * The data types allowed for the value element of the instance observations conforming to this ObservationDefinition.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRObservationDataType[]
     */
    public function getPermittedDataType()
    {
        return $this->permittedDataType;
    }

    /**
     * The data types allowed for the value element of the instance observations conforming to this ObservationDefinition.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRObservationDataType $permittedDataType
     * @return $this
     */
    public function addPermittedDataType($permittedDataType)
    {
        $this->permittedDataType[] = $permittedDataType;
        return $this;
    }

    /**
     * Multiple results allowed for observations conforming to this ObservationDefinition.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getMultipleResultsAllowed()
    {
        return $this->multipleResultsAllowed;
    }

    /**
     * Multiple results allowed for observations conforming to this ObservationDefinition.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $multipleResultsAllowed
     * @return $this
     */
    public function setMultipleResultsAllowed($multipleResultsAllowed)
    {
        $this->multipleResultsAllowed = $multipleResultsAllowed;
        return $this;
    }

    /**
     * The method or technique used to perform the observation.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * The method or technique used to perform the observation.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * The preferred name to be used when reporting the results of observations conforming to this ObservationDefinition.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getPreferredReportName()
    {
        return $this->preferredReportName;
    }

    /**
     * The preferred name to be used when reporting the results of observations conforming to this ObservationDefinition.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $preferredReportName
     * @return $this
     */
    public function setPreferredReportName($preferredReportName)
    {
        $this->preferredReportName = $preferredReportName;
        return $this;
    }

    /**
     * Characteristics for quantitative results of this observation.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRObservationDefinition\FHIRObservationDefinitionQuantitativeDetails
     */
    public function getQuantitativeDetails()
    {
        return $this->quantitativeDetails;
    }

    /**
     * Characteristics for quantitative results of this observation.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRObservationDefinition\FHIRObservationDefinitionQuantitativeDetails $quantitativeDetails
     * @return $this
     */
    public function setQuantitativeDetails($quantitativeDetails)
    {
        $this->quantitativeDetails = $quantitativeDetails;
        return $this;
    }

    /**
     * Multiple  ranges of results qualified by different contexts for ordinal or continuous observations conforming to this ObservationDefinition.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRObservationDefinition\FHIRObservationDefinitionQualifiedInterval[]
     */
    public function getQualifiedInterval()
    {
        return $this->qualifiedInterval;
    }

    /**
     * Multiple  ranges of results qualified by different contexts for ordinal or continuous observations conforming to this ObservationDefinition.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRObservationDefinition\FHIRObservationDefinitionQualifiedInterval $qualifiedInterval
     * @return $this
     */
    public function addQualifiedInterval($qualifiedInterval)
    {
        $this->qualifiedInterval[] = $qualifiedInterval;
        return $this;
    }

    /**
     * The set of valid coded results for the observations  conforming to this ObservationDefinition.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getValidCodedValueSet()
    {
        return $this->validCodedValueSet;
    }

    /**
     * The set of valid coded results for the observations  conforming to this ObservationDefinition.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $validCodedValueSet
     * @return $this
     */
    public function setValidCodedValueSet($validCodedValueSet)
    {
        $this->validCodedValueSet = $validCodedValueSet;
        return $this;
    }

    /**
     * The set of normal coded results for the observations conforming to this ObservationDefinition.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getNormalCodedValueSet()
    {
        return $this->normalCodedValueSet;
    }

    /**
     * The set of normal coded results for the observations conforming to this ObservationDefinition.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $normalCodedValueSet
     * @return $this
     */
    public function setNormalCodedValueSet($normalCodedValueSet)
    {
        $this->normalCodedValueSet = $normalCodedValueSet;
        return $this;
    }

    /**
     * The set of abnormal coded results for the observation conforming to this ObservationDefinition.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getAbnormalCodedValueSet()
    {
        return $this->abnormalCodedValueSet;
    }

    /**
     * The set of abnormal coded results for the observation conforming to this ObservationDefinition.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $abnormalCodedValueSet
     * @return $this
     */
    public function setAbnormalCodedValueSet($abnormalCodedValueSet)
    {
        $this->abnormalCodedValueSet = $abnormalCodedValueSet;
        return $this;
    }

    /**
     * The set of critical coded results for the observation conforming to this ObservationDefinition.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getCriticalCodedValueSet()
    {
        return $this->criticalCodedValueSet;
    }

    /**
     * The set of critical coded results for the observation conforming to this ObservationDefinition.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $criticalCodedValueSet
     * @return $this
     */
    public function setCriticalCodedValueSet($criticalCodedValueSet)
    {
        $this->criticalCodedValueSet = $criticalCodedValueSet;
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
            if (isset($data['category'])) {
                if (is_array($data['category'])) {
                    foreach ($data['category'] as $d) {
                        $this->addCategory($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"category" must be array of objects or null, ' . gettype($data['category']) . ' seen.');
                }
            }
            if (isset($data['code'])) {
                $this->setCode($data['code']);
            }
            if (isset($data['identifier'])) {
                if (is_array($data['identifier'])) {
                    foreach ($data['identifier'] as $d) {
                        $this->addIdentifier($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"identifier" must be array of objects or null, ' . gettype($data['identifier']) . ' seen.');
                }
            }
            if (isset($data['permittedDataType'])) {
                if (is_array($data['permittedDataType'])) {
                    foreach ($data['permittedDataType'] as $d) {
                        $this->addPermittedDataType($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"permittedDataType" must be array of objects or null, ' . gettype($data['permittedDataType']) . ' seen.');
                }
            }
            if (isset($data['multipleResultsAllowed'])) {
                $this->setMultipleResultsAllowed($data['multipleResultsAllowed']);
            }
            if (isset($data['method'])) {
                $this->setMethod($data['method']);
            }
            if (isset($data['preferredReportName'])) {
                $this->setPreferredReportName($data['preferredReportName']);
            }
            if (isset($data['quantitativeDetails'])) {
                $this->setQuantitativeDetails($data['quantitativeDetails']);
            }
            if (isset($data['qualifiedInterval'])) {
                if (is_array($data['qualifiedInterval'])) {
                    foreach ($data['qualifiedInterval'] as $d) {
                        $this->addQualifiedInterval($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"qualifiedInterval" must be array of objects or null, ' . gettype($data['qualifiedInterval']) . ' seen.');
                }
            }
            if (isset($data['validCodedValueSet'])) {
                $this->setValidCodedValueSet($data['validCodedValueSet']);
            }
            if (isset($data['normalCodedValueSet'])) {
                $this->setNormalCodedValueSet($data['normalCodedValueSet']);
            }
            if (isset($data['abnormalCodedValueSet'])) {
                $this->setAbnormalCodedValueSet($data['abnormalCodedValueSet']);
            }
            if (isset($data['criticalCodedValueSet'])) {
                $this->setCriticalCodedValueSet($data['criticalCodedValueSet']);
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
        if (0 < count($this->category)) {
            $json['category'] = [];
            foreach ($this->category as $category) {
                $json['category'][] = $category;
            }
        }
        if (isset($this->code)) {
            $json['code'] = $this->code;
        }
        if (0 < count($this->identifier)) {
            $json['identifier'] = [];
            foreach ($this->identifier as $identifier) {
                $json['identifier'][] = $identifier;
            }
        }
        if (0 < count($this->permittedDataType)) {
            $json['permittedDataType'] = [];
            foreach ($this->permittedDataType as $permittedDataType) {
                $json['permittedDataType'][] = $permittedDataType;
            }
        }
        if (isset($this->multipleResultsAllowed)) {
            $json['multipleResultsAllowed'] = $this->multipleResultsAllowed;
        }
        if (isset($this->method)) {
            $json['method'] = $this->method;
        }
        if (isset($this->preferredReportName)) {
            $json['preferredReportName'] = $this->preferredReportName;
        }
        if (isset($this->quantitativeDetails)) {
            $json['quantitativeDetails'] = $this->quantitativeDetails;
        }
        if (0 < count($this->qualifiedInterval)) {
            $json['qualifiedInterval'] = [];
            foreach ($this->qualifiedInterval as $qualifiedInterval) {
                $json['qualifiedInterval'][] = $qualifiedInterval;
            }
        }
        if (isset($this->validCodedValueSet)) {
            $json['validCodedValueSet'] = $this->validCodedValueSet;
        }
        if (isset($this->normalCodedValueSet)) {
            $json['normalCodedValueSet'] = $this->normalCodedValueSet;
        }
        if (isset($this->abnormalCodedValueSet)) {
            $json['abnormalCodedValueSet'] = $this->abnormalCodedValueSet;
        }
        if (isset($this->criticalCodedValueSet)) {
            $json['criticalCodedValueSet'] = $this->criticalCodedValueSet;
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
            $sxe = new \SimpleXMLElement('<ObservationDefinition xmlns="http://hl7.org/fhir"></ObservationDefinition>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->category)) {
            foreach ($this->category as $category) {
                $category->xmlSerialize(true, $sxe->addChild('category'));
            }
        }
        if (isset($this->code)) {
            $this->code->xmlSerialize(true, $sxe->addChild('code'));
        }
        if (0 < count($this->identifier)) {
            foreach ($this->identifier as $identifier) {
                $identifier->xmlSerialize(true, $sxe->addChild('identifier'));
            }
        }
        if (0 < count($this->permittedDataType)) {
            foreach ($this->permittedDataType as $permittedDataType) {
                $permittedDataType->xmlSerialize(true, $sxe->addChild('permittedDataType'));
            }
        }
        if (isset($this->multipleResultsAllowed)) {
            $this->multipleResultsAllowed->xmlSerialize(true, $sxe->addChild('multipleResultsAllowed'));
        }
        if (isset($this->method)) {
            $this->method->xmlSerialize(true, $sxe->addChild('method'));
        }
        if (isset($this->preferredReportName)) {
            $this->preferredReportName->xmlSerialize(true, $sxe->addChild('preferredReportName'));
        }
        if (isset($this->quantitativeDetails)) {
            $this->quantitativeDetails->xmlSerialize(true, $sxe->addChild('quantitativeDetails'));
        }
        if (0 < count($this->qualifiedInterval)) {
            foreach ($this->qualifiedInterval as $qualifiedInterval) {
                $qualifiedInterval->xmlSerialize(true, $sxe->addChild('qualifiedInterval'));
            }
        }
        if (isset($this->validCodedValueSet)) {
            $this->validCodedValueSet->xmlSerialize(true, $sxe->addChild('validCodedValueSet'));
        }
        if (isset($this->normalCodedValueSet)) {
            $this->normalCodedValueSet->xmlSerialize(true, $sxe->addChild('normalCodedValueSet'));
        }
        if (isset($this->abnormalCodedValueSet)) {
            $this->abnormalCodedValueSet->xmlSerialize(true, $sxe->addChild('abnormalCodedValueSet'));
        }
        if (isset($this->criticalCodedValueSet)) {
            $this->criticalCodedValueSet->xmlSerialize(true, $sxe->addChild('criticalCodedValueSet'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
