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
 * Raw data describing a biological sequence.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRMolecularSequence extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * A unique identifier for this particular sequence instance. This is a FHIR-defined id.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = [];

    /**
     * Amino Acid Sequence/ DNA Sequence / RNA Sequence.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRSequenceType
     */
    public $type = null;

    /**
     * Whether the sequence is numbered starting at 0 (0-based numbering or coordinates, inclusive start, exclusive end) or starting at 1 (1-based numbering, inclusive start and inclusive end).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public $coordinateSystem = null;

    /**
     * The patient whose sequencing results are described by this resource.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $patient = null;

    /**
     * Specimen used for sequencing.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $specimen = null;

    /**
     * The method for sequencing, for example, chip information.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $device = null;

    /**
     * The organization or lab that should be responsible for this result.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $performer = null;

    /**
     * The number of copies of the sequence of interest. (RNASeq).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public $quantity = null;

    /**
     * A sequence that is used as a reference to describe variants that are present in a sequence analyzed.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMolecularSequence\FHIRMolecularSequenceReferenceSeq
     */
    public $referenceSeq = null;

    /**
     * The definition of variant here originates from Sequence ontology ([variant_of](http://www.sequenceontology.org/browser/current_svn/term/variant_of)). This element can represent amino acid or nucleic sequence change(including insertion,deletion,SNP,etc.)  It can represent some complex mutation or segment variation with the assist of CIGAR string.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMolecularSequence\FHIRMolecularSequenceVariant[]
     */
    public $variant = [];

    /**
     * Sequence that was observed. It is the result marked by referenceSeq along with variant records on referenceSeq. This shall start from referenceSeq.windowStart and end by referenceSeq.windowEnd.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $observedSeq = null;

    /**
     * An experimental feature attribute that defines the quality of the feature in a quantitative way, such as a phred quality score ([SO:0001686](http://www.sequenceontology.org/browser/current_svn/term/SO:0001686)).
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMolecularSequence\FHIRMolecularSequenceQuality[]
     */
    public $quality = [];

    /**
     * Coverage (read depth or depth) is the average number of reads representing a given nucleotide in the reconstructed sequence.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public $readCoverage = null;

    /**
     * Configurations of the external repository. The repository shall store target's observedSeq or records related with target's observedSeq.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMolecularSequence\FHIRMolecularSequenceRepository[]
     */
    public $repository = [];

    /**
     * Pointer to next atomic sequence which at most contains one variant.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $pointer = [];

    /**
     * Information about chromosome structure variation.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMolecularSequence\FHIRMolecularSequenceStructureVariant[]
     */
    public $structureVariant = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'MolecularSequence';

    /**
     * A unique identifier for this particular sequence instance. This is a FHIR-defined id.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * A unique identifier for this particular sequence instance. This is a FHIR-defined id.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * Amino Acid Sequence/ DNA Sequence / RNA Sequence.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRSequenceType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Amino Acid Sequence/ DNA Sequence / RNA Sequence.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRSequenceType $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Whether the sequence is numbered starting at 0 (0-based numbering or coordinates, inclusive start, exclusive end) or starting at 1 (1-based numbering, inclusive start and inclusive end).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public function getCoordinateSystem()
    {
        return $this->coordinateSystem;
    }

    /**
     * Whether the sequence is numbered starting at 0 (0-based numbering or coordinates, inclusive start, exclusive end) or starting at 1 (1-based numbering, inclusive start and inclusive end).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger $coordinateSystem
     * @return $this
     */
    public function setCoordinateSystem($coordinateSystem)
    {
        $this->coordinateSystem = $coordinateSystem;
        return $this;
    }

    /**
     * The patient whose sequencing results are described by this resource.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getPatient()
    {
        return $this->patient;
    }

    /**
     * The patient whose sequencing results are described by this resource.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $patient
     * @return $this
     */
    public function setPatient($patient)
    {
        $this->patient = $patient;
        return $this;
    }

    /**
     * Specimen used for sequencing.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getSpecimen()
    {
        return $this->specimen;
    }

    /**
     * Specimen used for sequencing.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $specimen
     * @return $this
     */
    public function setSpecimen($specimen)
    {
        $this->specimen = $specimen;
        return $this;
    }

    /**
     * The method for sequencing, for example, chip information.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getDevice()
    {
        return $this->device;
    }

    /**
     * The method for sequencing, for example, chip information.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $device
     * @return $this
     */
    public function setDevice($device)
    {
        $this->device = $device;
        return $this;
    }

    /**
     * The organization or lab that should be responsible for this result.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getPerformer()
    {
        return $this->performer;
    }

    /**
     * The organization or lab that should be responsible for this result.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $performer
     * @return $this
     */
    public function setPerformer($performer)
    {
        $this->performer = $performer;
        return $this;
    }

    /**
     * The number of copies of the sequence of interest. (RNASeq).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * The number of copies of the sequence of interest. (RNASeq).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity $quantity
     * @return $this
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * A sequence that is used as a reference to describe variants that are present in a sequence analyzed.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMolecularSequence\FHIRMolecularSequenceReferenceSeq
     */
    public function getReferenceSeq()
    {
        return $this->referenceSeq;
    }

    /**
     * A sequence that is used as a reference to describe variants that are present in a sequence analyzed.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMolecularSequence\FHIRMolecularSequenceReferenceSeq $referenceSeq
     * @return $this
     */
    public function setReferenceSeq($referenceSeq)
    {
        $this->referenceSeq = $referenceSeq;
        return $this;
    }

    /**
     * The definition of variant here originates from Sequence ontology ([variant_of](http://www.sequenceontology.org/browser/current_svn/term/variant_of)). This element can represent amino acid or nucleic sequence change(including insertion,deletion,SNP,etc.)  It can represent some complex mutation or segment variation with the assist of CIGAR string.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMolecularSequence\FHIRMolecularSequenceVariant[]
     */
    public function getVariant()
    {
        return $this->variant;
    }

    /**
     * The definition of variant here originates from Sequence ontology ([variant_of](http://www.sequenceontology.org/browser/current_svn/term/variant_of)). This element can represent amino acid or nucleic sequence change(including insertion,deletion,SNP,etc.)  It can represent some complex mutation or segment variation with the assist of CIGAR string.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMolecularSequence\FHIRMolecularSequenceVariant $variant
     * @return $this
     */
    public function addVariant($variant)
    {
        $this->variant[] = $variant;
        return $this;
    }

    /**
     * Sequence that was observed. It is the result marked by referenceSeq along with variant records on referenceSeq. This shall start from referenceSeq.windowStart and end by referenceSeq.windowEnd.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getObservedSeq()
    {
        return $this->observedSeq;
    }

    /**
     * Sequence that was observed. It is the result marked by referenceSeq along with variant records on referenceSeq. This shall start from referenceSeq.windowStart and end by referenceSeq.windowEnd.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $observedSeq
     * @return $this
     */
    public function setObservedSeq($observedSeq)
    {
        $this->observedSeq = $observedSeq;
        return $this;
    }

    /**
     * An experimental feature attribute that defines the quality of the feature in a quantitative way, such as a phred quality score ([SO:0001686](http://www.sequenceontology.org/browser/current_svn/term/SO:0001686)).
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMolecularSequence\FHIRMolecularSequenceQuality[]
     */
    public function getQuality()
    {
        return $this->quality;
    }

    /**
     * An experimental feature attribute that defines the quality of the feature in a quantitative way, such as a phred quality score ([SO:0001686](http://www.sequenceontology.org/browser/current_svn/term/SO:0001686)).
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMolecularSequence\FHIRMolecularSequenceQuality $quality
     * @return $this
     */
    public function addQuality($quality)
    {
        $this->quality[] = $quality;
        return $this;
    }

    /**
     * Coverage (read depth or depth) is the average number of reads representing a given nucleotide in the reconstructed sequence.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public function getReadCoverage()
    {
        return $this->readCoverage;
    }

    /**
     * Coverage (read depth or depth) is the average number of reads representing a given nucleotide in the reconstructed sequence.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger $readCoverage
     * @return $this
     */
    public function setReadCoverage($readCoverage)
    {
        $this->readCoverage = $readCoverage;
        return $this;
    }

    /**
     * Configurations of the external repository. The repository shall store target's observedSeq or records related with target's observedSeq.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMolecularSequence\FHIRMolecularSequenceRepository[]
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Configurations of the external repository. The repository shall store target's observedSeq or records related with target's observedSeq.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMolecularSequence\FHIRMolecularSequenceRepository $repository
     * @return $this
     */
    public function addRepository($repository)
    {
        $this->repository[] = $repository;
        return $this;
    }

    /**
     * Pointer to next atomic sequence which at most contains one variant.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getPointer()
    {
        return $this->pointer;
    }

    /**
     * Pointer to next atomic sequence which at most contains one variant.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $pointer
     * @return $this
     */
    public function addPointer($pointer)
    {
        $this->pointer[] = $pointer;
        return $this;
    }

    /**
     * Information about chromosome structure variation.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMolecularSequence\FHIRMolecularSequenceStructureVariant[]
     */
    public function getStructureVariant()
    {
        return $this->structureVariant;
    }

    /**
     * Information about chromosome structure variation.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMolecularSequence\FHIRMolecularSequenceStructureVariant $structureVariant
     * @return $this
     */
    public function addStructureVariant($structureVariant)
    {
        $this->structureVariant[] = $structureVariant;
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
                if (is_array($data['identifier'])) {
                    foreach ($data['identifier'] as $d) {
                        $this->addIdentifier($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"identifier" must be array of objects or null, ' . gettype($data['identifier']) . ' seen.');
                }
            }
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['coordinateSystem'])) {
                $this->setCoordinateSystem($data['coordinateSystem']);
            }
            if (isset($data['patient'])) {
                $this->setPatient($data['patient']);
            }
            if (isset($data['specimen'])) {
                $this->setSpecimen($data['specimen']);
            }
            if (isset($data['device'])) {
                $this->setDevice($data['device']);
            }
            if (isset($data['performer'])) {
                $this->setPerformer($data['performer']);
            }
            if (isset($data['quantity'])) {
                $this->setQuantity($data['quantity']);
            }
            if (isset($data['referenceSeq'])) {
                $this->setReferenceSeq($data['referenceSeq']);
            }
            if (isset($data['variant'])) {
                if (is_array($data['variant'])) {
                    foreach ($data['variant'] as $d) {
                        $this->addVariant($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"variant" must be array of objects or null, ' . gettype($data['variant']) . ' seen.');
                }
            }
            if (isset($data['observedSeq'])) {
                $this->setObservedSeq($data['observedSeq']);
            }
            if (isset($data['quality'])) {
                if (is_array($data['quality'])) {
                    foreach ($data['quality'] as $d) {
                        $this->addQuality($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"quality" must be array of objects or null, ' . gettype($data['quality']) . ' seen.');
                }
            }
            if (isset($data['readCoverage'])) {
                $this->setReadCoverage($data['readCoverage']);
            }
            if (isset($data['repository'])) {
                if (is_array($data['repository'])) {
                    foreach ($data['repository'] as $d) {
                        $this->addRepository($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"repository" must be array of objects or null, ' . gettype($data['repository']) . ' seen.');
                }
            }
            if (isset($data['pointer'])) {
                if (is_array($data['pointer'])) {
                    foreach ($data['pointer'] as $d) {
                        $this->addPointer($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"pointer" must be array of objects or null, ' . gettype($data['pointer']) . ' seen.');
                }
            }
            if (isset($data['structureVariant'])) {
                if (is_array($data['structureVariant'])) {
                    foreach ($data['structureVariant'] as $d) {
                        $this->addStructureVariant($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"structureVariant" must be array of objects or null, ' . gettype($data['structureVariant']) . ' seen.');
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
        $json['resourceType'] = $this->_fhirElementName;
        if (0 < count($this->identifier)) {
            $json['identifier'] = [];
            foreach ($this->identifier as $identifier) {
                $json['identifier'][] = $identifier;
            }
        }
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->coordinateSystem)) {
            $json['coordinateSystem'] = $this->coordinateSystem;
        }
        if (isset($this->patient)) {
            $json['patient'] = $this->patient;
        }
        if (isset($this->specimen)) {
            $json['specimen'] = $this->specimen;
        }
        if (isset($this->device)) {
            $json['device'] = $this->device;
        }
        if (isset($this->performer)) {
            $json['performer'] = $this->performer;
        }
        if (isset($this->quantity)) {
            $json['quantity'] = $this->quantity;
        }
        if (isset($this->referenceSeq)) {
            $json['referenceSeq'] = $this->referenceSeq;
        }
        if (0 < count($this->variant)) {
            $json['variant'] = [];
            foreach ($this->variant as $variant) {
                $json['variant'][] = $variant;
            }
        }
        if (isset($this->observedSeq)) {
            $json['observedSeq'] = $this->observedSeq;
        }
        if (0 < count($this->quality)) {
            $json['quality'] = [];
            foreach ($this->quality as $quality) {
                $json['quality'][] = $quality;
            }
        }
        if (isset($this->readCoverage)) {
            $json['readCoverage'] = $this->readCoverage;
        }
        if (0 < count($this->repository)) {
            $json['repository'] = [];
            foreach ($this->repository as $repository) {
                $json['repository'][] = $repository;
            }
        }
        if (0 < count($this->pointer)) {
            $json['pointer'] = [];
            foreach ($this->pointer as $pointer) {
                $json['pointer'][] = $pointer;
            }
        }
        if (0 < count($this->structureVariant)) {
            $json['structureVariant'] = [];
            foreach ($this->structureVariant as $structureVariant) {
                $json['structureVariant'][] = $structureVariant;
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
            $sxe = new \SimpleXMLElement('<MolecularSequence xmlns="http://hl7.org/fhir"></MolecularSequence>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->identifier)) {
            foreach ($this->identifier as $identifier) {
                $identifier->xmlSerialize(true, $sxe->addChild('identifier'));
            }
        }
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->coordinateSystem)) {
            $this->coordinateSystem->xmlSerialize(true, $sxe->addChild('coordinateSystem'));
        }
        if (isset($this->patient)) {
            $this->patient->xmlSerialize(true, $sxe->addChild('patient'));
        }
        if (isset($this->specimen)) {
            $this->specimen->xmlSerialize(true, $sxe->addChild('specimen'));
        }
        if (isset($this->device)) {
            $this->device->xmlSerialize(true, $sxe->addChild('device'));
        }
        if (isset($this->performer)) {
            $this->performer->xmlSerialize(true, $sxe->addChild('performer'));
        }
        if (isset($this->quantity)) {
            $this->quantity->xmlSerialize(true, $sxe->addChild('quantity'));
        }
        if (isset($this->referenceSeq)) {
            $this->referenceSeq->xmlSerialize(true, $sxe->addChild('referenceSeq'));
        }
        if (0 < count($this->variant)) {
            foreach ($this->variant as $variant) {
                $variant->xmlSerialize(true, $sxe->addChild('variant'));
            }
        }
        if (isset($this->observedSeq)) {
            $this->observedSeq->xmlSerialize(true, $sxe->addChild('observedSeq'));
        }
        if (0 < count($this->quality)) {
            foreach ($this->quality as $quality) {
                $quality->xmlSerialize(true, $sxe->addChild('quality'));
            }
        }
        if (isset($this->readCoverage)) {
            $this->readCoverage->xmlSerialize(true, $sxe->addChild('readCoverage'));
        }
        if (0 < count($this->repository)) {
            foreach ($this->repository as $repository) {
                $repository->xmlSerialize(true, $sxe->addChild('repository'));
            }
        }
        if (0 < count($this->pointer)) {
            foreach ($this->pointer as $pointer) {
                $pointer->xmlSerialize(true, $sxe->addChild('pointer'));
            }
        }
        if (0 < count($this->structureVariant)) {
            foreach ($this->structureVariant as $structureVariant) {
                $structureVariant->xmlSerialize(true, $sxe->addChild('structureVariant'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
