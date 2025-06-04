<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRLocation;

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
 * Details and position information for a physical place where services are provided and resources and participants may be stored, found, contained, or accommodated.
 */
class FHIRLocationHoursOfOperation extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Indicates which days of the week are available between the start and end Times.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDaysOfWeek[]
     */
    public $daysOfWeek = [];

    /**
     * The Location is open all day.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $allDay = null;

    /**
     * Time that the Location opens.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRTime
     */
    public $openingTime = null;

    /**
     * Time that the Location closes.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRTime
     */
    public $closingTime = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Location.HoursOfOperation';

    /**
     * Indicates which days of the week are available between the start and end Times.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDaysOfWeek[]
     */
    public function getDaysOfWeek()
    {
        return $this->daysOfWeek;
    }

    /**
     * Indicates which days of the week are available between the start and end Times.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDaysOfWeek $daysOfWeek
     * @return $this
     */
    public function addDaysOfWeek($daysOfWeek)
    {
        $this->daysOfWeek[] = $daysOfWeek;
        return $this;
    }

    /**
     * The Location is open all day.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getAllDay()
    {
        return $this->allDay;
    }

    /**
     * The Location is open all day.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $allDay
     * @return $this
     */
    public function setAllDay($allDay)
    {
        $this->allDay = $allDay;
        return $this;
    }

    /**
     * Time that the Location opens.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRTime
     */
    public function getOpeningTime()
    {
        return $this->openingTime;
    }

    /**
     * Time that the Location opens.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRTime $openingTime
     * @return $this
     */
    public function setOpeningTime($openingTime)
    {
        $this->openingTime = $openingTime;
        return $this;
    }

    /**
     * Time that the Location closes.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRTime
     */
    public function getClosingTime()
    {
        return $this->closingTime;
    }

    /**
     * Time that the Location closes.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRTime $closingTime
     * @return $this
     */
    public function setClosingTime($closingTime)
    {
        $this->closingTime = $closingTime;
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
            if (isset($data['daysOfWeek'])) {
                if (is_array($data['daysOfWeek'])) {
                    foreach ($data['daysOfWeek'] as $d) {
                        $this->addDaysOfWeek($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"daysOfWeek" must be array of objects or null, ' . gettype($data['daysOfWeek']) . ' seen.');
                }
            }
            if (isset($data['allDay'])) {
                $this->setAllDay($data['allDay']);
            }
            if (isset($data['openingTime'])) {
                $this->setOpeningTime($data['openingTime']);
            }
            if (isset($data['closingTime'])) {
                $this->setClosingTime($data['closingTime']);
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
        if (0 < count($this->daysOfWeek)) {
            $json['daysOfWeek'] = [];
            foreach ($this->daysOfWeek as $daysOfWeek) {
                $json['daysOfWeek'][] = $daysOfWeek;
            }
        }
        if (isset($this->allDay)) {
            $json['allDay'] = $this->allDay;
        }
        if (isset($this->openingTime)) {
            $json['openingTime'] = $this->openingTime;
        }
        if (isset($this->closingTime)) {
            $json['closingTime'] = $this->closingTime;
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
            $sxe = new \SimpleXMLElement('<LocationHoursOfOperation xmlns="http://hl7.org/fhir"></LocationHoursOfOperation>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->daysOfWeek)) {
            foreach ($this->daysOfWeek as $daysOfWeek) {
                $daysOfWeek->xmlSerialize(true, $sxe->addChild('daysOfWeek'));
            }
        }
        if (isset($this->allDay)) {
            $this->allDay->xmlSerialize(true, $sxe->addChild('allDay'));
        }
        if (isset($this->openingTime)) {
            $this->openingTime->xmlSerialize(true, $sxe->addChild('openingTime'));
        }
        if (isset($this->closingTime)) {
            $this->closingTime->xmlSerialize(true, $sxe->addChild('closingTime'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
