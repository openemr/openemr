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
 * The detailed description of a substance, typically at a level beyond what is used for prescribing.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRSubstanceSpecification extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * Identifier by which this substance is known.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier
     */
    public $identifier = null;

    /**
     * High level categorization, e.g. polymer or nucleic acid.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $type = null;

    /**
     * Status of substance within the catalogue e.g. approved.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $status = null;

    /**
     * If the substance applies to only human or veterinary use.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $domain = null;

    /**
     * Textual description of the substance.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $description = null;

    /**
     * Supporting literature.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $source = [];

    /**
     * Textual comment about this record of a substance.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $comment = null;

    /**
     * Moiety, for structural modifications.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSpecification\FHIRSubstanceSpecificationMoiety[]
     */
    public $moiety = [];

    /**
     * General specifications for this substance, including how it is related to other substances.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSpecification\FHIRSubstanceSpecificationProperty[]
     */
    public $property = [];

    /**
     * General information detailing this substance.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $referenceInformation = null;

    /**
     * Structural information.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSpecification\FHIRSubstanceSpecificationStructure
     */
    public $structure = null;

    /**
     * Codes associated with the substance.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSpecification\FHIRSubstanceSpecificationCode[]
     */
    public $code = [];

    /**
     * Names applicable to this substance.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSpecification\FHIRSubstanceSpecificationName[]
     */
    public $name = [];

    /**
     * The molecular weight or weight range (for proteins, polymers or nucleic acids).
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSpecification\FHIRSubstanceSpecificationMolecularWeight[]
     */
    public $molecularWeight = [];

    /**
     * A link between this substance and another, with details of the relationship.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSpecification\FHIRSubstanceSpecificationRelationship[]
     */
    public $relationship = [];

    /**
     * Data items specific to nucleic acids.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $nucleicAcid = null;

    /**
     * Data items specific to polymers.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $polymer = null;

    /**
     * Data items specific to proteins.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $protein = null;

    /**
     * Material or taxonomic/anatomical source for the substance.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $sourceMaterial = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'SubstanceSpecification';

    /**
     * Identifier by which this substance is known.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Identifier by which this substance is known.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * High level categorization, e.g. polymer or nucleic acid.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * High level categorization, e.g. polymer or nucleic acid.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Status of substance within the catalogue e.g. approved.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Status of substance within the catalogue e.g. approved.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * If the substance applies to only human or veterinary use.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * If the substance applies to only human or veterinary use.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $domain
     * @return $this
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * Textual description of the substance.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Textual description of the substance.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Supporting literature.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Supporting literature.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $source
     * @return $this
     */
    public function addSource($source)
    {
        $this->source[] = $source;
        return $this;
    }

    /**
     * Textual comment about this record of a substance.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Textual comment about this record of a substance.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $comment
     * @return $this
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * Moiety, for structural modifications.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSpecification\FHIRSubstanceSpecificationMoiety[]
     */
    public function getMoiety()
    {
        return $this->moiety;
    }

    /**
     * Moiety, for structural modifications.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSpecification\FHIRSubstanceSpecificationMoiety $moiety
     * @return $this
     */
    public function addMoiety($moiety)
    {
        $this->moiety[] = $moiety;
        return $this;
    }

    /**
     * General specifications for this substance, including how it is related to other substances.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSpecification\FHIRSubstanceSpecificationProperty[]
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * General specifications for this substance, including how it is related to other substances.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSpecification\FHIRSubstanceSpecificationProperty $property
     * @return $this
     */
    public function addProperty($property)
    {
        $this->property[] = $property;
        return $this;
    }

    /**
     * General information detailing this substance.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getReferenceInformation()
    {
        return $this->referenceInformation;
    }

    /**
     * General information detailing this substance.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $referenceInformation
     * @return $this
     */
    public function setReferenceInformation($referenceInformation)
    {
        $this->referenceInformation = $referenceInformation;
        return $this;
    }

    /**
     * Structural information.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSpecification\FHIRSubstanceSpecificationStructure
     */
    public function getStructure()
    {
        return $this->structure;
    }

    /**
     * Structural information.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSpecification\FHIRSubstanceSpecificationStructure $structure
     * @return $this
     */
    public function setStructure($structure)
    {
        $this->structure = $structure;
        return $this;
    }

    /**
     * Codes associated with the substance.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSpecification\FHIRSubstanceSpecificationCode[]
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Codes associated with the substance.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSpecification\FHIRSubstanceSpecificationCode $code
     * @return $this
     */
    public function addCode($code)
    {
        $this->code[] = $code;
        return $this;
    }

    /**
     * Names applicable to this substance.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSpecification\FHIRSubstanceSpecificationName[]
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Names applicable to this substance.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSpecification\FHIRSubstanceSpecificationName $name
     * @return $this
     */
    public function addName($name)
    {
        $this->name[] = $name;
        return $this;
    }

    /**
     * The molecular weight or weight range (for proteins, polymers or nucleic acids).
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSpecification\FHIRSubstanceSpecificationMolecularWeight[]
     */
    public function getMolecularWeight()
    {
        return $this->molecularWeight;
    }

    /**
     * The molecular weight or weight range (for proteins, polymers or nucleic acids).
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSpecification\FHIRSubstanceSpecificationMolecularWeight $molecularWeight
     * @return $this
     */
    public function addMolecularWeight($molecularWeight)
    {
        $this->molecularWeight[] = $molecularWeight;
        return $this;
    }

    /**
     * A link between this substance and another, with details of the relationship.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSpecification\FHIRSubstanceSpecificationRelationship[]
     */
    public function getRelationship()
    {
        return $this->relationship;
    }

    /**
     * A link between this substance and another, with details of the relationship.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSpecification\FHIRSubstanceSpecificationRelationship $relationship
     * @return $this
     */
    public function addRelationship($relationship)
    {
        $this->relationship[] = $relationship;
        return $this;
    }

    /**
     * Data items specific to nucleic acids.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getNucleicAcid()
    {
        return $this->nucleicAcid;
    }

    /**
     * Data items specific to nucleic acids.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $nucleicAcid
     * @return $this
     */
    public function setNucleicAcid($nucleicAcid)
    {
        $this->nucleicAcid = $nucleicAcid;
        return $this;
    }

    /**
     * Data items specific to polymers.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getPolymer()
    {
        return $this->polymer;
    }

    /**
     * Data items specific to polymers.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $polymer
     * @return $this
     */
    public function setPolymer($polymer)
    {
        $this->polymer = $polymer;
        return $this;
    }

    /**
     * Data items specific to proteins.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getProtein()
    {
        return $this->protein;
    }

    /**
     * Data items specific to proteins.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $protein
     * @return $this
     */
    public function setProtein($protein)
    {
        $this->protein = $protein;
        return $this;
    }

    /**
     * Material or taxonomic/anatomical source for the substance.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getSourceMaterial()
    {
        return $this->sourceMaterial;
    }

    /**
     * Material or taxonomic/anatomical source for the substance.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $sourceMaterial
     * @return $this
     */
    public function setSourceMaterial($sourceMaterial)
    {
        $this->sourceMaterial = $sourceMaterial;
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
            if (isset($data['identifier'])) {
                $this->setIdentifier($data['identifier']);
            }
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['status'])) {
                $this->setStatus($data['status']);
            }
            if (isset($data['domain'])) {
                $this->setDomain($data['domain']);
            }
            if (isset($data['description'])) {
                $this->setDescription($data['description']);
            }
            if (isset($data['source'])) {
                if (is_array($data['source'])) {
                    foreach ($data['source'] as $d) {
                        $this->addSource($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"source" must be array of objects or null, ' . gettype($data['source']) . ' seen.');
                }
            }
            if (isset($data['comment'])) {
                $this->setComment($data['comment']);
            }
            if (isset($data['moiety'])) {
                if (is_array($data['moiety'])) {
                    foreach ($data['moiety'] as $d) {
                        $this->addMoiety($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"moiety" must be array of objects or null, ' . gettype($data['moiety']) . ' seen.');
                }
            }
            if (isset($data['property'])) {
                if (is_array($data['property'])) {
                    foreach ($data['property'] as $d) {
                        $this->addProperty($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"property" must be array of objects or null, ' . gettype($data['property']) . ' seen.');
                }
            }
            if (isset($data['referenceInformation'])) {
                $this->setReferenceInformation($data['referenceInformation']);
            }
            if (isset($data['structure'])) {
                $this->setStructure($data['structure']);
            }
            if (isset($data['code'])) {
                if (is_array($data['code'])) {
                    foreach ($data['code'] as $d) {
                        $this->addCode($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"code" must be array of objects or null, ' . gettype($data['code']) . ' seen.');
                }
            }
            if (isset($data['name'])) {
                if (is_array($data['name'])) {
                    foreach ($data['name'] as $d) {
                        $this->addName($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"name" must be array of objects or null, ' . gettype($data['name']) . ' seen.');
                }
            }
            if (isset($data['molecularWeight'])) {
                if (is_array($data['molecularWeight'])) {
                    foreach ($data['molecularWeight'] as $d) {
                        $this->addMolecularWeight($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"molecularWeight" must be array of objects or null, ' . gettype($data['molecularWeight']) . ' seen.');
                }
            }
            if (isset($data['relationship'])) {
                if (is_array($data['relationship'])) {
                    foreach ($data['relationship'] as $d) {
                        $this->addRelationship($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"relationship" must be array of objects or null, ' . gettype($data['relationship']) . ' seen.');
                }
            }
            if (isset($data['nucleicAcid'])) {
                $this->setNucleicAcid($data['nucleicAcid']);
            }
            if (isset($data['polymer'])) {
                $this->setPolymer($data['polymer']);
            }
            if (isset($data['protein'])) {
                $this->setProtein($data['protein']);
            }
            if (isset($data['sourceMaterial'])) {
                $this->setSourceMaterial($data['sourceMaterial']);
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
        if (isset($this->identifier)) {
            $json['identifier'] = $this->identifier;
        }
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->status)) {
            $json['status'] = $this->status;
        }
        if (isset($this->domain)) {
            $json['domain'] = $this->domain;
        }
        if (isset($this->description)) {
            $json['description'] = $this->description;
        }
        if (0 < count($this->source)) {
            $json['source'] = [];
            foreach ($this->source as $source) {
                $json['source'][] = $source;
            }
        }
        if (isset($this->comment)) {
            $json['comment'] = $this->comment;
        }
        if (0 < count($this->moiety)) {
            $json['moiety'] = [];
            foreach ($this->moiety as $moiety) {
                $json['moiety'][] = $moiety;
            }
        }
        if (0 < count($this->property)) {
            $json['property'] = [];
            foreach ($this->property as $property) {
                $json['property'][] = $property;
            }
        }
        if (isset($this->referenceInformation)) {
            $json['referenceInformation'] = $this->referenceInformation;
        }
        if (isset($this->structure)) {
            $json['structure'] = $this->structure;
        }
        if (0 < count($this->code)) {
            $json['code'] = [];
            foreach ($this->code as $code) {
                $json['code'][] = $code;
            }
        }
        if (0 < count($this->name)) {
            $json['name'] = [];
            foreach ($this->name as $name) {
                $json['name'][] = $name;
            }
        }
        if (0 < count($this->molecularWeight)) {
            $json['molecularWeight'] = [];
            foreach ($this->molecularWeight as $molecularWeight) {
                $json['molecularWeight'][] = $molecularWeight;
            }
        }
        if (0 < count($this->relationship)) {
            $json['relationship'] = [];
            foreach ($this->relationship as $relationship) {
                $json['relationship'][] = $relationship;
            }
        }
        if (isset($this->nucleicAcid)) {
            $json['nucleicAcid'] = $this->nucleicAcid;
        }
        if (isset($this->polymer)) {
            $json['polymer'] = $this->polymer;
        }
        if (isset($this->protein)) {
            $json['protein'] = $this->protein;
        }
        if (isset($this->sourceMaterial)) {
            $json['sourceMaterial'] = $this->sourceMaterial;
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
            $sxe = new \SimpleXMLElement('<SubstanceSpecification xmlns="http://hl7.org/fhir"></SubstanceSpecification>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->identifier)) {
            $this->identifier->xmlSerialize(true, $sxe->addChild('identifier'));
        }
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->status)) {
            $this->status->xmlSerialize(true, $sxe->addChild('status'));
        }
        if (isset($this->domain)) {
            $this->domain->xmlSerialize(true, $sxe->addChild('domain'));
        }
        if (isset($this->description)) {
            $this->description->xmlSerialize(true, $sxe->addChild('description'));
        }
        if (0 < count($this->source)) {
            foreach ($this->source as $source) {
                $source->xmlSerialize(true, $sxe->addChild('source'));
            }
        }
        if (isset($this->comment)) {
            $this->comment->xmlSerialize(true, $sxe->addChild('comment'));
        }
        if (0 < count($this->moiety)) {
            foreach ($this->moiety as $moiety) {
                $moiety->xmlSerialize(true, $sxe->addChild('moiety'));
            }
        }
        if (0 < count($this->property)) {
            foreach ($this->property as $property) {
                $property->xmlSerialize(true, $sxe->addChild('property'));
            }
        }
        if (isset($this->referenceInformation)) {
            $this->referenceInformation->xmlSerialize(true, $sxe->addChild('referenceInformation'));
        }
        if (isset($this->structure)) {
            $this->structure->xmlSerialize(true, $sxe->addChild('structure'));
        }
        if (0 < count($this->code)) {
            foreach ($this->code as $code) {
                $code->xmlSerialize(true, $sxe->addChild('code'));
            }
        }
        if (0 < count($this->name)) {
            foreach ($this->name as $name) {
                $name->xmlSerialize(true, $sxe->addChild('name'));
            }
        }
        if (0 < count($this->molecularWeight)) {
            foreach ($this->molecularWeight as $molecularWeight) {
                $molecularWeight->xmlSerialize(true, $sxe->addChild('molecularWeight'));
            }
        }
        if (0 < count($this->relationship)) {
            foreach ($this->relationship as $relationship) {
                $relationship->xmlSerialize(true, $sxe->addChild('relationship'));
            }
        }
        if (isset($this->nucleicAcid)) {
            $this->nucleicAcid->xmlSerialize(true, $sxe->addChild('nucleicAcid'));
        }
        if (isset($this->polymer)) {
            $this->polymer->xmlSerialize(true, $sxe->addChild('polymer'));
        }
        if (isset($this->protein)) {
            $this->protein->xmlSerialize(true, $sxe->addChild('protein'));
        }
        if (isset($this->sourceMaterial)) {
            $this->sourceMaterial->xmlSerialize(true, $sxe->addChild('sourceMaterial'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
