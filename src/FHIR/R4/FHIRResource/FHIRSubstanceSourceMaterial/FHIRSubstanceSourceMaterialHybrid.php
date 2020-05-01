<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSourceMaterial;

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
 * Source material shall capture information on the taxonomic and anatomical origins as well as the fraction of a material that can result in or can be modified to form a substance. This set of data elements shall be used to define polymer substances isolated from biological matrices. Taxonomic and anatomical origins shall be described using a controlled vocabulary as required. This information is captured for naturally derived polymers ( . starch) and structurally diverse substances. For Organisms belonging to the Kingdom Plantae the Substance level defines the fresh material of a single species or infraspecies, the Herbal Drug and the Herbal preparation. For Herbal preparations, the fraction information will be captured at the Substance information level and additional information for herbal extracts will be captured at the Specified Substance Group 1 information level. See for further explanation the Substance Class: Structurally Diverse and the herbal annex.
 */
class FHIRSubstanceSourceMaterialHybrid extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The identifier of the maternal species constituting the hybrid organism shall be specified based on a controlled vocabulary. For plants, the parents aren’t always known, and it is unlikely that it will be known which is maternal and which is paternal.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $maternalOrganismId = null;

    /**
     * The name of the maternal species constituting the hybrid organism shall be specified. For plants, the parents aren’t always known, and it is unlikely that it will be known which is maternal and which is paternal.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $maternalOrganismName = null;

    /**
     * The identifier of the paternal species constituting the hybrid organism shall be specified based on a controlled vocabulary.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $paternalOrganismId = null;

    /**
     * The name of the paternal species constituting the hybrid organism shall be specified.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $paternalOrganismName = null;

    /**
     * The hybrid type of an organism shall be specified.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $hybridType = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'SubstanceSourceMaterial.Hybrid';

    /**
     * The identifier of the maternal species constituting the hybrid organism shall be specified based on a controlled vocabulary. For plants, the parents aren’t always known, and it is unlikely that it will be known which is maternal and which is paternal.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getMaternalOrganismId()
    {
        return $this->maternalOrganismId;
    }

    /**
     * The identifier of the maternal species constituting the hybrid organism shall be specified based on a controlled vocabulary. For plants, the parents aren’t always known, and it is unlikely that it will be known which is maternal and which is paternal.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $maternalOrganismId
     * @return $this
     */
    public function setMaternalOrganismId($maternalOrganismId)
    {
        $this->maternalOrganismId = $maternalOrganismId;
        return $this;
    }

    /**
     * The name of the maternal species constituting the hybrid organism shall be specified. For plants, the parents aren’t always known, and it is unlikely that it will be known which is maternal and which is paternal.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getMaternalOrganismName()
    {
        return $this->maternalOrganismName;
    }

    /**
     * The name of the maternal species constituting the hybrid organism shall be specified. For plants, the parents aren’t always known, and it is unlikely that it will be known which is maternal and which is paternal.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $maternalOrganismName
     * @return $this
     */
    public function setMaternalOrganismName($maternalOrganismName)
    {
        $this->maternalOrganismName = $maternalOrganismName;
        return $this;
    }

    /**
     * The identifier of the paternal species constituting the hybrid organism shall be specified based on a controlled vocabulary.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getPaternalOrganismId()
    {
        return $this->paternalOrganismId;
    }

    /**
     * The identifier of the paternal species constituting the hybrid organism shall be specified based on a controlled vocabulary.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $paternalOrganismId
     * @return $this
     */
    public function setPaternalOrganismId($paternalOrganismId)
    {
        $this->paternalOrganismId = $paternalOrganismId;
        return $this;
    }

    /**
     * The name of the paternal species constituting the hybrid organism shall be specified.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getPaternalOrganismName()
    {
        return $this->paternalOrganismName;
    }

    /**
     * The name of the paternal species constituting the hybrid organism shall be specified.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $paternalOrganismName
     * @return $this
     */
    public function setPaternalOrganismName($paternalOrganismName)
    {
        $this->paternalOrganismName = $paternalOrganismName;
        return $this;
    }

    /**
     * The hybrid type of an organism shall be specified.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getHybridType()
    {
        return $this->hybridType;
    }

    /**
     * The hybrid type of an organism shall be specified.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $hybridType
     * @return $this
     */
    public function setHybridType($hybridType)
    {
        $this->hybridType = $hybridType;
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
            if (isset($data['maternalOrganismId'])) {
                $this->setMaternalOrganismId($data['maternalOrganismId']);
            }
            if (isset($data['maternalOrganismName'])) {
                $this->setMaternalOrganismName($data['maternalOrganismName']);
            }
            if (isset($data['paternalOrganismId'])) {
                $this->setPaternalOrganismId($data['paternalOrganismId']);
            }
            if (isset($data['paternalOrganismName'])) {
                $this->setPaternalOrganismName($data['paternalOrganismName']);
            }
            if (isset($data['hybridType'])) {
                $this->setHybridType($data['hybridType']);
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
        if (isset($this->maternalOrganismId)) {
            $json['maternalOrganismId'] = $this->maternalOrganismId;
        }
        if (isset($this->maternalOrganismName)) {
            $json['maternalOrganismName'] = $this->maternalOrganismName;
        }
        if (isset($this->paternalOrganismId)) {
            $json['paternalOrganismId'] = $this->paternalOrganismId;
        }
        if (isset($this->paternalOrganismName)) {
            $json['paternalOrganismName'] = $this->paternalOrganismName;
        }
        if (isset($this->hybridType)) {
            $json['hybridType'] = $this->hybridType;
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
            $sxe = new \SimpleXMLElement('<SubstanceSourceMaterialHybrid xmlns="http://hl7.org/fhir"></SubstanceSourceMaterialHybrid>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->maternalOrganismId)) {
            $this->maternalOrganismId->xmlSerialize(true, $sxe->addChild('maternalOrganismId'));
        }
        if (isset($this->maternalOrganismName)) {
            $this->maternalOrganismName->xmlSerialize(true, $sxe->addChild('maternalOrganismName'));
        }
        if (isset($this->paternalOrganismId)) {
            $this->paternalOrganismId->xmlSerialize(true, $sxe->addChild('paternalOrganismId'));
        }
        if (isset($this->paternalOrganismName)) {
            $this->paternalOrganismName->xmlSerialize(true, $sxe->addChild('paternalOrganismName'));
        }
        if (isset($this->hybridType)) {
            $this->hybridType->xmlSerialize(true, $sxe->addChild('hybridType'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
