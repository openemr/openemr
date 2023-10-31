<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRGraphDefinition;

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
 * A formal computable definition of a graph of resources - that is, a coherent set of resources that form a graph by following references. The Graph Definition resource defines a set and makes rules about the set.
 */
class FHIRGraphDefinitionLink extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * A FHIR expression that identifies one of FHIR References to other resources.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $path = null;

    /**
     * Which slice (if profiled).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $sliceName = null;

    /**
     * Minimum occurrences for this link.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public $min = null;

    /**
     * Maximum occurrences for this link.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $max = null;

    /**
     * Information about why this link is of interest in this graph definition.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $description = null;

    /**
     * Potential target for the link.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRGraphDefinition\FHIRGraphDefinitionTarget[]
     */
    public $target = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'GraphDefinition.Link';

    /**
     * A FHIR expression that identifies one of FHIR References to other resources.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * A FHIR expression that identifies one of FHIR References to other resources.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * Which slice (if profiled).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getSliceName()
    {
        return $this->sliceName;
    }

    /**
     * Which slice (if profiled).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $sliceName
     * @return $this
     */
    public function setSliceName($sliceName)
    {
        $this->sliceName = $sliceName;
        return $this;
    }

    /**
     * Minimum occurrences for this link.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * Minimum occurrences for this link.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger $min
     * @return $this
     */
    public function setMin($min)
    {
        $this->min = $min;
        return $this;
    }

    /**
     * Maximum occurrences for this link.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * Maximum occurrences for this link.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $max
     * @return $this
     */
    public function setMax($max)
    {
        $this->max = $max;
        return $this;
    }

    /**
     * Information about why this link is of interest in this graph definition.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Information about why this link is of interest in this graph definition.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Potential target for the link.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRGraphDefinition\FHIRGraphDefinitionTarget[]
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Potential target for the link.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRGraphDefinition\FHIRGraphDefinitionTarget $target
     * @return $this
     */
    public function addTarget($target)
    {
        $this->target[] = $target;
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
            if (isset($data['path'])) {
                $this->setPath($data['path']);
            }
            if (isset($data['sliceName'])) {
                $this->setSliceName($data['sliceName']);
            }
            if (isset($data['min'])) {
                $this->setMin($data['min']);
            }
            if (isset($data['max'])) {
                $this->setMax($data['max']);
            }
            if (isset($data['description'])) {
                $this->setDescription($data['description']);
            }
            if (isset($data['target'])) {
                if (is_array($data['target'])) {
                    foreach ($data['target'] as $d) {
                        $this->addTarget($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"target" must be array of objects or null, ' . gettype($data['target']) . ' seen.');
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
        if (isset($this->path)) {
            $json['path'] = $this->path;
        }
        if (isset($this->sliceName)) {
            $json['sliceName'] = $this->sliceName;
        }
        if (isset($this->min)) {
            $json['min'] = $this->min;
        }
        if (isset($this->max)) {
            $json['max'] = $this->max;
        }
        if (isset($this->description)) {
            $json['description'] = $this->description;
        }
        if (0 < count($this->target)) {
            $json['target'] = [];
            foreach ($this->target as $target) {
                $json['target'][] = $target;
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
            $sxe = new \SimpleXMLElement('<GraphDefinitionLink xmlns="http://hl7.org/fhir"></GraphDefinitionLink>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->path)) {
            $this->path->xmlSerialize(true, $sxe->addChild('path'));
        }
        if (isset($this->sliceName)) {
            $this->sliceName->xmlSerialize(true, $sxe->addChild('sliceName'));
        }
        if (isset($this->min)) {
            $this->min->xmlSerialize(true, $sxe->addChild('min'));
        }
        if (isset($this->max)) {
            $this->max->xmlSerialize(true, $sxe->addChild('max'));
        }
        if (isset($this->description)) {
            $this->description->xmlSerialize(true, $sxe->addChild('description'));
        }
        if (0 < count($this->target)) {
            foreach ($this->target as $target) {
                $target->xmlSerialize(true, $sxe->addChild('target'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
