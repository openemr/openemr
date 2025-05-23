<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProduct;

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
 * Detailed definition of a medicinal product, typically for uses other than direct patient care (e.g. regulatory use).
 */
class FHIRMedicinalProductName extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The full product name.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $productName = null;

    /**
     * Coding words or phrases of the name.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProduct\FHIRMedicinalProductNamePart[]
     */
    public $namePart = [];

    /**
     * Country where the name applies.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProduct\FHIRMedicinalProductCountryLanguage[]
     */
    public $countryLanguage = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'MedicinalProduct.Name';

    /**
     * The full product name.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getProductName()
    {
        return $this->productName;
    }

    /**
     * The full product name.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $productName
     * @return $this
     */
    public function setProductName($productName)
    {
        $this->productName = $productName;
        return $this;
    }

    /**
     * Coding words or phrases of the name.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProduct\FHIRMedicinalProductNamePart[]
     */
    public function getNamePart()
    {
        return $this->namePart;
    }

    /**
     * Coding words or phrases of the name.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProduct\FHIRMedicinalProductNamePart $namePart
     * @return $this
     */
    public function addNamePart($namePart)
    {
        $this->namePart[] = $namePart;
        return $this;
    }

    /**
     * Country where the name applies.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProduct\FHIRMedicinalProductCountryLanguage[]
     */
    public function getCountryLanguage()
    {
        return $this->countryLanguage;
    }

    /**
     * Country where the name applies.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProduct\FHIRMedicinalProductCountryLanguage $countryLanguage
     * @return $this
     */
    public function addCountryLanguage($countryLanguage)
    {
        $this->countryLanguage[] = $countryLanguage;
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
            if (isset($data['productName'])) {
                $this->setProductName($data['productName']);
            }
            if (isset($data['namePart'])) {
                if (is_array($data['namePart'])) {
                    foreach ($data['namePart'] as $d) {
                        $this->addNamePart($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"namePart" must be array of objects or null, ' . gettype($data['namePart']) . ' seen.');
                }
            }
            if (isset($data['countryLanguage'])) {
                if (is_array($data['countryLanguage'])) {
                    foreach ($data['countryLanguage'] as $d) {
                        $this->addCountryLanguage($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"countryLanguage" must be array of objects or null, ' . gettype($data['countryLanguage']) . ' seen.');
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
        if (isset($this->productName)) {
            $json['productName'] = $this->productName;
        }
        if (0 < count($this->namePart)) {
            $json['namePart'] = [];
            foreach ($this->namePart as $namePart) {
                $json['namePart'][] = $namePart;
            }
        }
        if (0 < count($this->countryLanguage)) {
            $json['countryLanguage'] = [];
            foreach ($this->countryLanguage as $countryLanguage) {
                $json['countryLanguage'][] = $countryLanguage;
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
            $sxe = new \SimpleXMLElement('<MedicinalProductName xmlns="http://hl7.org/fhir"></MedicinalProductName>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->productName)) {
            $this->productName->xmlSerialize(true, $sxe->addChild('productName'));
        }
        if (0 < count($this->namePart)) {
            foreach ($this->namePart as $namePart) {
                $namePart->xmlSerialize(true, $sxe->addChild('namePart'));
            }
        }
        if (0 < count($this->countryLanguage)) {
            foreach ($this->countryLanguage as $countryLanguage) {
                $countryLanguage->xmlSerialize(true, $sxe->addChild('countryLanguage'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
