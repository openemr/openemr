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
 * Describes a required data item for evaluation in terms of the type of data, and optional code or date-based filters of the data.
 * If the element is present, it must have a value for at least one of the defined elements, an @id referenced from the Narrative, or extensions
 */
class FHIRDataRequirement extends FHIRElement implements \JsonSerializable
{
    /**
     * The type of the required data, specified as the type name of a resource. For profiles, this value is set to the type of the base resource of the profile.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCode
     */
    public $type = null;

    /**
     * The profile of the required data, specified as the uri of the profile definition.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical[]
     */
    public $profile = [];

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $subjectCodeableConcept = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $subjectReference = null;

    /**
     * Indicates that specific elements of the type are referenced by the knowledge module and must be supported by the consumer in order to obtain an effective evaluation. This does not mean that a value is required for this element, only that the consuming system must understand the element and be able to provide values for it if they are available.

The value of mustSupport SHALL be a FHIRPath resolveable on the type of the DataRequirement. The path SHALL consist only of identifiers, constant indexers, and .resolve() (see the [Simple FHIRPath Profile](fhirpath.html#simple) for full details).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public $mustSupport = [];

    /**
     * Code filters specify additional constraints on the data, specifying the value set of interest for a particular element of the data. Each code filter defines an additional constraint on the data, i.e. code filters are AND'ed, not OR'ed.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRDataRequirement\FHIRDataRequirementCodeFilter[]
     */
    public $codeFilter = [];

    /**
     * Date filters specify additional constraints on the data in terms of the applicable date range for specific elements. Each date filter specifies an additional constraint on the data, i.e. date filters are AND'ed, not OR'ed.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRDataRequirement\FHIRDataRequirementDateFilter[]
     */
    public $dateFilter = [];

    /**
     * Specifies a maximum number of results that are required (uses the _count search parameter).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt
     */
    public $limit = null;

    /**
     * Specifies the order of the results to be returned.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRDataRequirement\FHIRDataRequirementSort[]
     */
    public $sort = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'DataRequirement';

    /**
     * The type of the required data, specified as the type name of a resource. For profiles, this value is set to the type of the base resource of the profile.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCode
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * The type of the required data, specified as the type name of a resource. For profiles, this value is set to the type of the base resource of the profile.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCode $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * The profile of the required data, specified as the uri of the profile definition.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical[]
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * The profile of the required data, specified as the uri of the profile definition.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical $profile
     * @return $this
     */
    public function addProfile($profile)
    {
        $this->profile[] = $profile;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getSubjectCodeableConcept()
    {
        return $this->subjectCodeableConcept;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $subjectCodeableConcept
     * @return $this
     */
    public function setSubjectCodeableConcept($subjectCodeableConcept)
    {
        $this->subjectCodeableConcept = $subjectCodeableConcept;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getSubjectReference()
    {
        return $this->subjectReference;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $subjectReference
     * @return $this
     */
    public function setSubjectReference($subjectReference)
    {
        $this->subjectReference = $subjectReference;
        return $this;
    }

    /**
     * Indicates that specific elements of the type are referenced by the knowledge module and must be supported by the consumer in order to obtain an effective evaluation. This does not mean that a value is required for this element, only that the consuming system must understand the element and be able to provide values for it if they are available.

The value of mustSupport SHALL be a FHIRPath resolveable on the type of the DataRequirement. The path SHALL consist only of identifiers, constant indexers, and .resolve() (see the [Simple FHIRPath Profile](fhirpath.html#simple) for full details).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public function getMustSupport()
    {
        return $this->mustSupport;
    }

    /**
     * Indicates that specific elements of the type are referenced by the knowledge module and must be supported by the consumer in order to obtain an effective evaluation. This does not mean that a value is required for this element, only that the consuming system must understand the element and be able to provide values for it if they are available.

The value of mustSupport SHALL be a FHIRPath resolveable on the type of the DataRequirement. The path SHALL consist only of identifiers, constant indexers, and .resolve() (see the [Simple FHIRPath Profile](fhirpath.html#simple) for full details).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $mustSupport
     * @return $this
     */
    public function addMustSupport($mustSupport)
    {
        $this->mustSupport[] = $mustSupport;
        return $this;
    }

    /**
     * Code filters specify additional constraints on the data, specifying the value set of interest for a particular element of the data. Each code filter defines an additional constraint on the data, i.e. code filters are AND'ed, not OR'ed.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRDataRequirement\FHIRDataRequirementCodeFilter[]
     */
    public function getCodeFilter()
    {
        return $this->codeFilter;
    }

    /**
     * Code filters specify additional constraints on the data, specifying the value set of interest for a particular element of the data. Each code filter defines an additional constraint on the data, i.e. code filters are AND'ed, not OR'ed.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRDataRequirement\FHIRDataRequirementCodeFilter $codeFilter
     * @return $this
     */
    public function addCodeFilter($codeFilter)
    {
        $this->codeFilter[] = $codeFilter;
        return $this;
    }

    /**
     * Date filters specify additional constraints on the data in terms of the applicable date range for specific elements. Each date filter specifies an additional constraint on the data, i.e. date filters are AND'ed, not OR'ed.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRDataRequirement\FHIRDataRequirementDateFilter[]
     */
    public function getDateFilter()
    {
        return $this->dateFilter;
    }

    /**
     * Date filters specify additional constraints on the data in terms of the applicable date range for specific elements. Each date filter specifies an additional constraint on the data, i.e. date filters are AND'ed, not OR'ed.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRDataRequirement\FHIRDataRequirementDateFilter $dateFilter
     * @return $this
     */
    public function addDateFilter($dateFilter)
    {
        $this->dateFilter[] = $dateFilter;
        return $this;
    }

    /**
     * Specifies a maximum number of results that are required (uses the _count search parameter).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Specifies a maximum number of results that are required (uses the _count search parameter).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt $limit
     * @return $this
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Specifies the order of the results to be returned.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRDataRequirement\FHIRDataRequirementSort[]
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * Specifies the order of the results to be returned.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRDataRequirement\FHIRDataRequirementSort $sort
     * @return $this
     */
    public function addSort($sort)
    {
        $this->sort[] = $sort;
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
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['profile'])) {
                if (is_array($data['profile'])) {
                    foreach ($data['profile'] as $d) {
                        $this->addProfile($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"profile" must be array of objects or null, ' . gettype($data['profile']) . ' seen.');
                }
            }
            if (isset($data['subjectCodeableConcept'])) {
                $this->setSubjectCodeableConcept($data['subjectCodeableConcept']);
            }
            if (isset($data['subjectReference'])) {
                $this->setSubjectReference($data['subjectReference']);
            }
            if (isset($data['mustSupport'])) {
                if (is_array($data['mustSupport'])) {
                    foreach ($data['mustSupport'] as $d) {
                        $this->addMustSupport($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"mustSupport" must be array of objects or null, ' . gettype($data['mustSupport']) . ' seen.');
                }
            }
            if (isset($data['codeFilter'])) {
                if (is_array($data['codeFilter'])) {
                    foreach ($data['codeFilter'] as $d) {
                        $this->addCodeFilter($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"codeFilter" must be array of objects or null, ' . gettype($data['codeFilter']) . ' seen.');
                }
            }
            if (isset($data['dateFilter'])) {
                if (is_array($data['dateFilter'])) {
                    foreach ($data['dateFilter'] as $d) {
                        $this->addDateFilter($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"dateFilter" must be array of objects or null, ' . gettype($data['dateFilter']) . ' seen.');
                }
            }
            if (isset($data['limit'])) {
                $this->setLimit($data['limit']);
            }
            if (isset($data['sort'])) {
                if (is_array($data['sort'])) {
                    foreach ($data['sort'] as $d) {
                        $this->addSort($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"sort" must be array of objects or null, ' . gettype($data['sort']) . ' seen.');
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
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (0 < count($this->profile)) {
            $json['profile'] = [];
            foreach ($this->profile as $profile) {
                $json['profile'][] = $profile;
            }
        }
        if (isset($this->subjectCodeableConcept)) {
            $json['subjectCodeableConcept'] = $this->subjectCodeableConcept;
        }
        if (isset($this->subjectReference)) {
            $json['subjectReference'] = $this->subjectReference;
        }
        if (0 < count($this->mustSupport)) {
            $json['mustSupport'] = [];
            foreach ($this->mustSupport as $mustSupport) {
                $json['mustSupport'][] = $mustSupport;
            }
        }
        if (0 < count($this->codeFilter)) {
            $json['codeFilter'] = [];
            foreach ($this->codeFilter as $codeFilter) {
                $json['codeFilter'][] = $codeFilter;
            }
        }
        if (0 < count($this->dateFilter)) {
            $json['dateFilter'] = [];
            foreach ($this->dateFilter as $dateFilter) {
                $json['dateFilter'][] = $dateFilter;
            }
        }
        if (isset($this->limit)) {
            $json['limit'] = $this->limit;
        }
        if (0 < count($this->sort)) {
            $json['sort'] = [];
            foreach ($this->sort as $sort) {
                $json['sort'][] = $sort;
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
            $sxe = new \SimpleXMLElement('<DataRequirement xmlns="http://hl7.org/fhir"></DataRequirement>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (0 < count($this->profile)) {
            foreach ($this->profile as $profile) {
                $profile->xmlSerialize(true, $sxe->addChild('profile'));
            }
        }
        if (isset($this->subjectCodeableConcept)) {
            $this->subjectCodeableConcept->xmlSerialize(true, $sxe->addChild('subjectCodeableConcept'));
        }
        if (isset($this->subjectReference)) {
            $this->subjectReference->xmlSerialize(true, $sxe->addChild('subjectReference'));
        }
        if (0 < count($this->mustSupport)) {
            foreach ($this->mustSupport as $mustSupport) {
                $mustSupport->xmlSerialize(true, $sxe->addChild('mustSupport'));
            }
        }
        if (0 < count($this->codeFilter)) {
            foreach ($this->codeFilter as $codeFilter) {
                $codeFilter->xmlSerialize(true, $sxe->addChild('codeFilter'));
            }
        }
        if (0 < count($this->dateFilter)) {
            foreach ($this->dateFilter as $dateFilter) {
                $dateFilter->xmlSerialize(true, $sxe->addChild('dateFilter'));
            }
        }
        if (isset($this->limit)) {
            $this->limit->xmlSerialize(true, $sxe->addChild('limit'));
        }
        if (0 < count($this->sort)) {
            foreach ($this->sort as $sort) {
                $sort->xmlSerialize(true, $sxe->addChild('sort'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
