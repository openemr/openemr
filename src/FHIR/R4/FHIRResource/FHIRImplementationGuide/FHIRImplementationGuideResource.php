<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRImplementationGuide;

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
 * A set of rules of how a particular interoperability or standards problem is solved - typically through the use of FHIR resources. This resource is used to gather all the parts of an implementation guide into a logical whole and to publish a computable definition of all the parts.
 */
class FHIRImplementationGuideResource extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Where this resource is found.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $reference = null;

    /**
     * Indicates the FHIR Version(s) this artifact is intended to apply to. If no versions are specified, the resource is assumed to apply to all the versions stated in ImplementationGuide.fhirVersion.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRFHIRVersion[]
     */
    public $fhirVersion = [];

    /**
     * A human assigned name for the resource. All resources SHOULD have a name, but the name may be extracted from the resource (e.g. ValueSet.name).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $name = null;

    /**
     * A description of the reason that a resource has been included in the implementation guide.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $description = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $exampleBoolean = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical
     */
    public $exampleCanonical = null;

    /**
     * Reference to the id of the grouping this resource appears in.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    public $groupingId = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'ImplementationGuide.Resource';

    /**
     * Where this resource is found.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Where this resource is found.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $reference
     * @return $this
     */
    public function setReference($reference)
    {
        $this->reference = $reference;
        return $this;
    }

    /**
     * Indicates the FHIR Version(s) this artifact is intended to apply to. If no versions are specified, the resource is assumed to apply to all the versions stated in ImplementationGuide.fhirVersion.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRFHIRVersion[]
     */
    public function getFhirVersion()
    {
        return $this->fhirVersion;
    }

    /**
     * Indicates the FHIR Version(s) this artifact is intended to apply to. If no versions are specified, the resource is assumed to apply to all the versions stated in ImplementationGuide.fhirVersion.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRFHIRVersion $fhirVersion
     * @return $this
     */
    public function addFhirVersion($fhirVersion)
    {
        $this->fhirVersion[] = $fhirVersion;
        return $this;
    }

    /**
     * A human assigned name for the resource. All resources SHOULD have a name, but the name may be extracted from the resource (e.g. ValueSet.name).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * A human assigned name for the resource. All resources SHOULD have a name, but the name may be extracted from the resource (e.g. ValueSet.name).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * A description of the reason that a resource has been included in the implementation guide.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * A description of the reason that a resource has been included in the implementation guide.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getExampleBoolean()
    {
        return $this->exampleBoolean;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $exampleBoolean
     * @return $this
     */
    public function setExampleBoolean($exampleBoolean)
    {
        $this->exampleBoolean = $exampleBoolean;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical
     */
    public function getExampleCanonical()
    {
        return $this->exampleCanonical;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical $exampleCanonical
     * @return $this
     */
    public function setExampleCanonical($exampleCanonical)
    {
        $this->exampleCanonical = $exampleCanonical;
        return $this;
    }

    /**
     * Reference to the id of the grouping this resource appears in.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    public function getGroupingId()
    {
        return $this->groupingId;
    }

    /**
     * Reference to the id of the grouping this resource appears in.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRId $groupingId
     * @return $this
     */
    public function setGroupingId($groupingId)
    {
        $this->groupingId = $groupingId;
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
            if (isset($data['reference'])) {
                $this->setReference($data['reference']);
            }
            if (isset($data['fhirVersion'])) {
                if (is_array($data['fhirVersion'])) {
                    foreach ($data['fhirVersion'] as $d) {
                        $this->addFhirVersion($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"fhirVersion" must be array of objects or null, ' . gettype($data['fhirVersion']) . ' seen.');
                }
            }
            if (isset($data['name'])) {
                $this->setName($data['name']);
            }
            if (isset($data['description'])) {
                $this->setDescription($data['description']);
            }
            if (isset($data['exampleBoolean'])) {
                $this->setExampleBoolean($data['exampleBoolean']);
            }
            if (isset($data['exampleCanonical'])) {
                $this->setExampleCanonical($data['exampleCanonical']);
            }
            if (isset($data['groupingId'])) {
                $this->setGroupingId($data['groupingId']);
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
        if (isset($this->reference)) {
            $json['reference'] = $this->reference;
        }
        if (0 < count($this->fhirVersion)) {
            $json['fhirVersion'] = [];
            foreach ($this->fhirVersion as $fhirVersion) {
                $json['fhirVersion'][] = $fhirVersion;
            }
        }
        if (isset($this->name)) {
            $json['name'] = $this->name;
        }
        if (isset($this->description)) {
            $json['description'] = $this->description;
        }
        if (isset($this->exampleBoolean)) {
            $json['exampleBoolean'] = $this->exampleBoolean;
        }
        if (isset($this->exampleCanonical)) {
            $json['exampleCanonical'] = $this->exampleCanonical;
        }
        if (isset($this->groupingId)) {
            $json['groupingId'] = $this->groupingId;
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
            $sxe = new \SimpleXMLElement('<ImplementationGuideResource xmlns="http://hl7.org/fhir"></ImplementationGuideResource>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->reference)) {
            $this->reference->xmlSerialize(true, $sxe->addChild('reference'));
        }
        if (0 < count($this->fhirVersion)) {
            foreach ($this->fhirVersion as $fhirVersion) {
                $fhirVersion->xmlSerialize(true, $sxe->addChild('fhirVersion'));
            }
        }
        if (isset($this->name)) {
            $this->name->xmlSerialize(true, $sxe->addChild('name'));
        }
        if (isset($this->description)) {
            $this->description->xmlSerialize(true, $sxe->addChild('description'));
        }
        if (isset($this->exampleBoolean)) {
            $this->exampleBoolean->xmlSerialize(true, $sxe->addChild('exampleBoolean'));
        }
        if (isset($this->exampleCanonical)) {
            $this->exampleCanonical->xmlSerialize(true, $sxe->addChild('exampleCanonical'));
        }
        if (isset($this->groupingId)) {
            $this->groupingId->xmlSerialize(true, $sxe->addChild('groupingId'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
