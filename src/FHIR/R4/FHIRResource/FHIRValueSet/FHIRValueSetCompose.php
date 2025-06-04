<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRValueSet;

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
 * A ValueSet resource instance specifies a set of codes drawn from one or more code systems, intended for use in a particular context. Value sets link between [[[CodeSystem]]] definitions and their use in [coded elements](terminologies.html).
 */
class FHIRValueSetCompose extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The Locked Date is  the effective date that is used to determine the version of all referenced Code Systems and Value Set Definitions included in the compose that are not already tied to a specific version.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDate
     */
    public $lockedDate = null;

    /**
     * Whether inactive codes - codes that are not approved for current use - are in the value set. If inactive = true, inactive codes are to be included in the expansion, if inactive = false, the inactive codes will not be included in the expansion. If absent, the behavior is determined by the implementation, or by the applicable $expand parameters (but generally, inactive codes would be expected to be included).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $inactive = null;

    /**
     * Include one or more codes from a code system or other value set(s).
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRValueSet\FHIRValueSetInclude[]
     */
    public $include = [];

    /**
     * Exclude one or more codes from the value set based on code system filters and/or other value sets.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRValueSet\FHIRValueSetInclude[]
     */
    public $exclude = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'ValueSet.Compose';

    /**
     * The Locked Date is  the effective date that is used to determine the version of all referenced Code Systems and Value Set Definitions included in the compose that are not already tied to a specific version.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDate
     */
    public function getLockedDate()
    {
        return $this->lockedDate;
    }

    /**
     * The Locked Date is  the effective date that is used to determine the version of all referenced Code Systems and Value Set Definitions included in the compose that are not already tied to a specific version.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDate $lockedDate
     * @return $this
     */
    public function setLockedDate($lockedDate)
    {
        $this->lockedDate = $lockedDate;
        return $this;
    }

    /**
     * Whether inactive codes - codes that are not approved for current use - are in the value set. If inactive = true, inactive codes are to be included in the expansion, if inactive = false, the inactive codes will not be included in the expansion. If absent, the behavior is determined by the implementation, or by the applicable $expand parameters (but generally, inactive codes would be expected to be included).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getInactive()
    {
        return $this->inactive;
    }

    /**
     * Whether inactive codes - codes that are not approved for current use - are in the value set. If inactive = true, inactive codes are to be included in the expansion, if inactive = false, the inactive codes will not be included in the expansion. If absent, the behavior is determined by the implementation, or by the applicable $expand parameters (but generally, inactive codes would be expected to be included).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $inactive
     * @return $this
     */
    public function setInactive($inactive)
    {
        $this->inactive = $inactive;
        return $this;
    }

    /**
     * Include one or more codes from a code system or other value set(s).
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRValueSet\FHIRValueSetInclude[]
     */
    public function getInclude()
    {
        return $this->include;
    }

    /**
     * Include one or more codes from a code system or other value set(s).
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRValueSet\FHIRValueSetInclude $include
     * @return $this
     */
    public function addInclude($include)
    {
        $this->include[] = $include;
        return $this;
    }

    /**
     * Exclude one or more codes from the value set based on code system filters and/or other value sets.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRValueSet\FHIRValueSetInclude[]
     */
    public function getExclude()
    {
        return $this->exclude;
    }

    /**
     * Exclude one or more codes from the value set based on code system filters and/or other value sets.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRValueSet\FHIRValueSetInclude $exclude
     * @return $this
     */
    public function addExclude($exclude)
    {
        $this->exclude[] = $exclude;
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
            if (isset($data['lockedDate'])) {
                $this->setLockedDate($data['lockedDate']);
            }
            if (isset($data['inactive'])) {
                $this->setInactive($data['inactive']);
            }
            if (isset($data['include'])) {
                if (is_array($data['include'])) {
                    foreach ($data['include'] as $d) {
                        $this->addInclude($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"include" must be array of objects or null, ' . gettype($data['include']) . ' seen.');
                }
            }
            if (isset($data['exclude'])) {
                if (is_array($data['exclude'])) {
                    foreach ($data['exclude'] as $d) {
                        $this->addExclude($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"exclude" must be array of objects or null, ' . gettype($data['exclude']) . ' seen.');
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
        if (isset($this->lockedDate)) {
            $json['lockedDate'] = $this->lockedDate;
        }
        if (isset($this->inactive)) {
            $json['inactive'] = $this->inactive;
        }
        if (0 < count($this->include)) {
            $json['include'] = [];
            foreach ($this->include as $include) {
                $json['include'][] = $include;
            }
        }
        if (0 < count($this->exclude)) {
            $json['exclude'] = [];
            foreach ($this->exclude as $exclude) {
                $json['exclude'][] = $exclude;
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
            $sxe = new \SimpleXMLElement('<ValueSetCompose xmlns="http://hl7.org/fhir"></ValueSetCompose>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->lockedDate)) {
            $this->lockedDate->xmlSerialize(true, $sxe->addChild('lockedDate'));
        }
        if (isset($this->inactive)) {
            $this->inactive->xmlSerialize(true, $sxe->addChild('inactive'));
        }
        if (0 < count($this->include)) {
            foreach ($this->include as $include) {
                $include->xmlSerialize(true, $sxe->addChild('include'));
            }
        }
        if (0 < count($this->exclude)) {
            foreach ($this->exclude as $exclude) {
                $exclude->xmlSerialize(true, $sxe->addChild('exclude'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
