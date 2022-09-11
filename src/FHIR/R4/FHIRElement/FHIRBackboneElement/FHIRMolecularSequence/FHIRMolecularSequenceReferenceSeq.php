<?php

namespace OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMolecularSequence;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: September 10th, 2022 20:42+0000
 * 
 * PHPFHIR Copyright:
 * 
 * Copyright 2016-2022 Daniel Carbone (daniel.p.carbone@gmail.com)
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
 *   Generated on Fri, Nov 1, 2019 09:29+1100 for FHIR v4.0.1
 * 
 *   Note: the schemas & schematrons do not contain all of the rules about what makes resources
 *   valid. Implementers will still need to be familiar with the content of the specification and with
 *   any profiles that apply to the resources in order to make a conformant implementation.
 * 
 */

use OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\R4\FHIRElement\FHIRInteger;
use OpenEMR\FHIR\R4\FHIRElement\FHIROrientationType;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRElement\FHIRStrandType;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\FHIR\R4\FHIRStringPrimitive;
use OpenEMR\FHIR\R4\PHPFHIRConstants;
use OpenEMR\FHIR\R4\PHPFHIRTypeInterface;

/**
 * Raw data describing a biological sequence.
 *
 * Class FHIRMolecularSequenceReferenceSeq
 * @package \OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMolecularSequence
 */
class FHIRMolecularSequenceReferenceSeq extends FHIRBackboneElement
{
    // name of FHIR type this class describes
    const FHIR_TYPE_NAME = PHPFHIRConstants::TYPE_NAME_MOLECULAR_SEQUENCE_DOT_REFERENCE_SEQ;
    const FIELD_CHROMOSOME = 'chromosome';
    const FIELD_GENOME_BUILD = 'genomeBuild';
    const FIELD_GENOME_BUILD_EXT = '_genomeBuild';
    const FIELD_ORIENTATION = 'orientation';
    const FIELD_ORIENTATION_EXT = '_orientation';
    const FIELD_REFERENCE_SEQ_ID = 'referenceSeqId';
    const FIELD_REFERENCE_SEQ_POINTER = 'referenceSeqPointer';
    const FIELD_REFERENCE_SEQ_STRING = 'referenceSeqString';
    const FIELD_REFERENCE_SEQ_STRING_EXT = '_referenceSeqString';
    const FIELD_STRAND = 'strand';
    const FIELD_STRAND_EXT = '_strand';
    const FIELD_WINDOW_START = 'windowStart';
    const FIELD_WINDOW_START_EXT = '_windowStart';
    const FIELD_WINDOW_END = 'windowEnd';
    const FIELD_WINDOW_END_EXT = '_windowEnd';

    /** @var string */
    private $_xmlns = '';

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Structural unit composed of a nucleic acid molecule which controls its own
     * replication through the interaction of specific proteins at one or more origins
     * of replication
     * ([SO:0000340](http://www.sequenceontology.org/browser/current_svn/term/SO:0000340)).
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    protected $chromosome = null;

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The Genome Build used for reference, following GRCh build versions e.g. 'GRCh
     * 37'. Version number must be included if a versioned release of a primary build
     * was used.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    protected $genomeBuild = null;

    /**
     * Type for orientation.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A relative reference to a DNA strand based on gene orientation. The strand that
     * contains the open reading frame of the gene is the "sense" strand, and the
     * opposite complementary strand is the "antisense" strand.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIROrientationType
     */
    protected $orientation = null;

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reference identifier of reference sequence submitted to NCBI. It must match the
     * type in the MolecularSequence.type field. For example, the prefix, “NG_”
     * identifies reference sequence for genes, “NM_” for messenger RNA
     * transcripts, and “NP_” for amino acid sequences.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    protected $referenceSeqId = null;

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A pointer to another MolecularSequence entity as reference sequence.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    protected $referenceSeqPointer = null;

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A string like "ACGT".
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    protected $referenceSeqString = null;

    /**
     * Type for strand.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An absolute reference to a strand. The Watson strand is the strand whose 5'-end
     * is on the short arm of the chromosome, and the Crick strand as the one whose
     * 5'-end is on the long arm.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRStrandType
     */
    protected $strand = null;

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Start position of the window on the reference sequence. If the coordinate system
     * is either 0-based or 1-based, then start position is inclusive.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    protected $windowStart = null;

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * End position of the window on the reference sequence. If the coordinate system
     * is 0-based then end is exclusive and does not include the last position. If the
     * coordinate system is 1-base, then end is inclusive and includes the last
     * position.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    protected $windowEnd = null;

    /**
     * Validation map for fields in type MolecularSequence.ReferenceSeq
     * @var array
     */
    private static $_validationRules = [    ];

    /**
     * FHIRMolecularSequenceReferenceSeq Constructor
     * @param null|array $data
     */
    public function __construct($data = null)
    {
        if (null === $data || [] === $data) {
            return;
        }
        if (!is_array($data)) {
            throw new \InvalidArgumentException(sprintf(
                'FHIRMolecularSequenceReferenceSeq::_construct - $data expected to be null or array, %s seen',
                gettype($data)
            ));
        }
        parent::__construct($data);
        if (isset($data[self::FIELD_CHROMOSOME])) {
            if ($data[self::FIELD_CHROMOSOME] instanceof FHIRCodeableConcept) {
                $this->setChromosome($data[self::FIELD_CHROMOSOME]);
            } else {
                $this->setChromosome(new FHIRCodeableConcept($data[self::FIELD_CHROMOSOME]));
            }
        }
        if (isset($data[self::FIELD_GENOME_BUILD]) || isset($data[self::FIELD_GENOME_BUILD_EXT])) {
            $value = isset($data[self::FIELD_GENOME_BUILD]) ? $data[self::FIELD_GENOME_BUILD] : null;
            $ext = (isset($data[self::FIELD_GENOME_BUILD_EXT]) && is_array($data[self::FIELD_GENOME_BUILD_EXT])) ? $ext = $data[self::FIELD_GENOME_BUILD_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRString) {
                    $this->setGenomeBuild($value);
                } else if (is_array($value)) {
                    $this->setGenomeBuild(new FHIRString(array_merge($ext, $value)));
                } else {
                    $this->setGenomeBuild(new FHIRString([FHIRString::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setGenomeBuild(new FHIRString($ext));
            }
        }
        if (isset($data[self::FIELD_ORIENTATION]) || isset($data[self::FIELD_ORIENTATION_EXT])) {
            $value = isset($data[self::FIELD_ORIENTATION]) ? $data[self::FIELD_ORIENTATION] : null;
            $ext = (isset($data[self::FIELD_ORIENTATION_EXT]) && is_array($data[self::FIELD_ORIENTATION_EXT])) ? $ext = $data[self::FIELD_ORIENTATION_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIROrientationType) {
                    $this->setOrientation($value);
                } else if (is_array($value)) {
                    $this->setOrientation(new FHIROrientationType(array_merge($ext, $value)));
                } else {
                    $this->setOrientation(new FHIROrientationType([FHIROrientationType::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setOrientation(new FHIROrientationType($ext));
            }
        }
        if (isset($data[self::FIELD_REFERENCE_SEQ_ID])) {
            if ($data[self::FIELD_REFERENCE_SEQ_ID] instanceof FHIRCodeableConcept) {
                $this->setReferenceSeqId($data[self::FIELD_REFERENCE_SEQ_ID]);
            } else {
                $this->setReferenceSeqId(new FHIRCodeableConcept($data[self::FIELD_REFERENCE_SEQ_ID]));
            }
        }
        if (isset($data[self::FIELD_REFERENCE_SEQ_POINTER])) {
            if ($data[self::FIELD_REFERENCE_SEQ_POINTER] instanceof FHIRReference) {
                $this->setReferenceSeqPointer($data[self::FIELD_REFERENCE_SEQ_POINTER]);
            } else {
                $this->setReferenceSeqPointer(new FHIRReference($data[self::FIELD_REFERENCE_SEQ_POINTER]));
            }
        }
        if (isset($data[self::FIELD_REFERENCE_SEQ_STRING]) || isset($data[self::FIELD_REFERENCE_SEQ_STRING_EXT])) {
            $value = isset($data[self::FIELD_REFERENCE_SEQ_STRING]) ? $data[self::FIELD_REFERENCE_SEQ_STRING] : null;
            $ext = (isset($data[self::FIELD_REFERENCE_SEQ_STRING_EXT]) && is_array($data[self::FIELD_REFERENCE_SEQ_STRING_EXT])) ? $ext = $data[self::FIELD_REFERENCE_SEQ_STRING_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRString) {
                    $this->setReferenceSeqString($value);
                } else if (is_array($value)) {
                    $this->setReferenceSeqString(new FHIRString(array_merge($ext, $value)));
                } else {
                    $this->setReferenceSeqString(new FHIRString([FHIRString::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setReferenceSeqString(new FHIRString($ext));
            }
        }
        if (isset($data[self::FIELD_STRAND]) || isset($data[self::FIELD_STRAND_EXT])) {
            $value = isset($data[self::FIELD_STRAND]) ? $data[self::FIELD_STRAND] : null;
            $ext = (isset($data[self::FIELD_STRAND_EXT]) && is_array($data[self::FIELD_STRAND_EXT])) ? $ext = $data[self::FIELD_STRAND_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRStrandType) {
                    $this->setStrand($value);
                } else if (is_array($value)) {
                    $this->setStrand(new FHIRStrandType(array_merge($ext, $value)));
                } else {
                    $this->setStrand(new FHIRStrandType([FHIRStrandType::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setStrand(new FHIRStrandType($ext));
            }
        }
        if (isset($data[self::FIELD_WINDOW_START]) || isset($data[self::FIELD_WINDOW_START_EXT])) {
            $value = isset($data[self::FIELD_WINDOW_START]) ? $data[self::FIELD_WINDOW_START] : null;
            $ext = (isset($data[self::FIELD_WINDOW_START_EXT]) && is_array($data[self::FIELD_WINDOW_START_EXT])) ? $ext = $data[self::FIELD_WINDOW_START_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRInteger) {
                    $this->setWindowStart($value);
                } else if (is_array($value)) {
                    $this->setWindowStart(new FHIRInteger(array_merge($ext, $value)));
                } else {
                    $this->setWindowStart(new FHIRInteger([FHIRInteger::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setWindowStart(new FHIRInteger($ext));
            }
        }
        if (isset($data[self::FIELD_WINDOW_END]) || isset($data[self::FIELD_WINDOW_END_EXT])) {
            $value = isset($data[self::FIELD_WINDOW_END]) ? $data[self::FIELD_WINDOW_END] : null;
            $ext = (isset($data[self::FIELD_WINDOW_END_EXT]) && is_array($data[self::FIELD_WINDOW_END_EXT])) ? $ext = $data[self::FIELD_WINDOW_END_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRInteger) {
                    $this->setWindowEnd($value);
                } else if (is_array($value)) {
                    $this->setWindowEnd(new FHIRInteger(array_merge($ext, $value)));
                } else {
                    $this->setWindowEnd(new FHIRInteger([FHIRInteger::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setWindowEnd(new FHIRInteger($ext));
            }
        }
    }

    /**
     * @return string
     */
    public function _getFHIRTypeName()
    {
        return self::FHIR_TYPE_NAME;
    }

    /**
     * @return string
     */
    public function _getFHIRXMLElementDefinition()
    {
        $xmlns = $this->_getFHIRXMLNamespace();
        if ('' !==  $xmlns) {
            $xmlns = " xmlns=\"{$xmlns}\"";
        }
        return "<MolecularSequenceReferenceSeq{$xmlns}></MolecularSequenceReferenceSeq>";
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Structural unit composed of a nucleic acid molecule which controls its own
     * replication through the interaction of specific proteins at one or more origins
     * of replication
     * ([SO:0000340](http://www.sequenceontology.org/browser/current_svn/term/SO:0000340)).
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getChromosome()
    {
        return $this->chromosome;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Structural unit composed of a nucleic acid molecule which controls its own
     * replication through the interaction of specific proteins at one or more origins
     * of replication
     * ([SO:0000340](http://www.sequenceontology.org/browser/current_svn/term/SO:0000340)).
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $chromosome
     * @return static
     */
    public function setChromosome(FHIRCodeableConcept $chromosome = null)
    {
        $this->_trackValueSet($this->chromosome, $chromosome);
        $this->chromosome = $chromosome;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The Genome Build used for reference, following GRCh build versions e.g. 'GRCh
     * 37'. Version number must be included if a versioned release of a primary build
     * was used.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getGenomeBuild()
    {
        return $this->genomeBuild;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The Genome Build used for reference, following GRCh build versions e.g. 'GRCh
     * 37'. Version number must be included if a versioned release of a primary build
     * was used.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString $genomeBuild
     * @return static
     */
    public function setGenomeBuild($genomeBuild = null)
    {
        if (null !== $genomeBuild && !($genomeBuild instanceof FHIRString)) {
            $genomeBuild = new FHIRString($genomeBuild);
        }
        $this->_trackValueSet($this->genomeBuild, $genomeBuild);
        $this->genomeBuild = $genomeBuild;
        return $this;
    }

    /**
     * Type for orientation.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A relative reference to a DNA strand based on gene orientation. The strand that
     * contains the open reading frame of the gene is the "sense" strand, and the
     * opposite complementary strand is the "antisense" strand.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIROrientationType
     */
    public function getOrientation()
    {
        return $this->orientation;
    }

    /**
     * Type for orientation.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A relative reference to a DNA strand based on gene orientation. The strand that
     * contains the open reading frame of the gene is the "sense" strand, and the
     * opposite complementary strand is the "antisense" strand.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIROrientationType $orientation
     * @return static
     */
    public function setOrientation(FHIROrientationType $orientation = null)
    {
        $this->_trackValueSet($this->orientation, $orientation);
        $this->orientation = $orientation;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reference identifier of reference sequence submitted to NCBI. It must match the
     * type in the MolecularSequence.type field. For example, the prefix, “NG_”
     * identifies reference sequence for genes, “NM_” for messenger RNA
     * transcripts, and “NP_” for amino acid sequences.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getReferenceSeqId()
    {
        return $this->referenceSeqId;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reference identifier of reference sequence submitted to NCBI. It must match the
     * type in the MolecularSequence.type field. For example, the prefix, “NG_”
     * identifies reference sequence for genes, “NM_” for messenger RNA
     * transcripts, and “NP_” for amino acid sequences.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $referenceSeqId
     * @return static
     */
    public function setReferenceSeqId(FHIRCodeableConcept $referenceSeqId = null)
    {
        $this->_trackValueSet($this->referenceSeqId, $referenceSeqId);
        $this->referenceSeqId = $referenceSeqId;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A pointer to another MolecularSequence entity as reference sequence.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getReferenceSeqPointer()
    {
        return $this->referenceSeqPointer;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A pointer to another MolecularSequence entity as reference sequence.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference $referenceSeqPointer
     * @return static
     */
    public function setReferenceSeqPointer(FHIRReference $referenceSeqPointer = null)
    {
        $this->_trackValueSet($this->referenceSeqPointer, $referenceSeqPointer);
        $this->referenceSeqPointer = $referenceSeqPointer;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A string like "ACGT".
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getReferenceSeqString()
    {
        return $this->referenceSeqString;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A string like "ACGT".
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString $referenceSeqString
     * @return static
     */
    public function setReferenceSeqString($referenceSeqString = null)
    {
        if (null !== $referenceSeqString && !($referenceSeqString instanceof FHIRString)) {
            $referenceSeqString = new FHIRString($referenceSeqString);
        }
        $this->_trackValueSet($this->referenceSeqString, $referenceSeqString);
        $this->referenceSeqString = $referenceSeqString;
        return $this;
    }

    /**
     * Type for strand.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An absolute reference to a strand. The Watson strand is the strand whose 5'-end
     * is on the short arm of the chromosome, and the Crick strand as the one whose
     * 5'-end is on the long arm.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRStrandType
     */
    public function getStrand()
    {
        return $this->strand;
    }

    /**
     * Type for strand.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An absolute reference to a strand. The Watson strand is the strand whose 5'-end
     * is on the short arm of the chromosome, and the Crick strand as the one whose
     * 5'-end is on the long arm.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRStrandType $strand
     * @return static
     */
    public function setStrand(FHIRStrandType $strand = null)
    {
        $this->_trackValueSet($this->strand, $strand);
        $this->strand = $strand;
        return $this;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Start position of the window on the reference sequence. If the coordinate system
     * is either 0-based or 1-based, then start position is inclusive.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public function getWindowStart()
    {
        return $this->windowStart;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Start position of the window on the reference sequence. If the coordinate system
     * is either 0-based or 1-based, then start position is inclusive.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRInteger $windowStart
     * @return static
     */
    public function setWindowStart($windowStart = null)
    {
        if (null !== $windowStart && !($windowStart instanceof FHIRInteger)) {
            $windowStart = new FHIRInteger($windowStart);
        }
        $this->_trackValueSet($this->windowStart, $windowStart);
        $this->windowStart = $windowStart;
        return $this;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * End position of the window on the reference sequence. If the coordinate system
     * is 0-based then end is exclusive and does not include the last position. If the
     * coordinate system is 1-base, then end is inclusive and includes the last
     * position.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public function getWindowEnd()
    {
        return $this->windowEnd;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * End position of the window on the reference sequence. If the coordinate system
     * is 0-based then end is exclusive and does not include the last position. If the
     * coordinate system is 1-base, then end is inclusive and includes the last
     * position.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRInteger $windowEnd
     * @return static
     */
    public function setWindowEnd($windowEnd = null)
    {
        if (null !== $windowEnd && !($windowEnd instanceof FHIRInteger)) {
            $windowEnd = new FHIRInteger($windowEnd);
        }
        $this->_trackValueSet($this->windowEnd, $windowEnd);
        $this->windowEnd = $windowEnd;
        return $this;
    }

    /**
     * Returns the validation rules that this type's fields must comply with to be considered "valid"
     * The returned array is in ["fieldname[.offset]" => ["rule" => {constraint}]]
     *
     * @return array
     */
    public function _getValidationRules()
    {
        return self::$_validationRules;
    }

    /**
     * Validates that this type conforms to the specifications set forth for it by FHIR.  An empty array must be seen as
     * passing.
     *
     * @return array
     */
    public function _getValidationErrors()
    {
        $errs = parent::_getValidationErrors();
        $validationRules = $this->_getValidationRules();
        if (null !== ($v = $this->getChromosome())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_CHROMOSOME] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getGenomeBuild())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_GENOME_BUILD] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getOrientation())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_ORIENTATION] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getReferenceSeqId())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_REFERENCE_SEQ_ID] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getReferenceSeqPointer())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_REFERENCE_SEQ_POINTER] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getReferenceSeqString())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_REFERENCE_SEQ_STRING] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getStrand())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_STRAND] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getWindowStart())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_WINDOW_START] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getWindowEnd())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_WINDOW_END] = $fieldErrs;
            }
        }
        if (isset($validationRules[self::FIELD_CHROMOSOME])) {
            $v = $this->getChromosome();
            foreach($validationRules[self::FIELD_CHROMOSOME] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MOLECULAR_SEQUENCE_DOT_REFERENCE_SEQ, self::FIELD_CHROMOSOME, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_CHROMOSOME])) {
                        $errs[self::FIELD_CHROMOSOME] = [];
                    }
                    $errs[self::FIELD_CHROMOSOME][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_GENOME_BUILD])) {
            $v = $this->getGenomeBuild();
            foreach($validationRules[self::FIELD_GENOME_BUILD] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MOLECULAR_SEQUENCE_DOT_REFERENCE_SEQ, self::FIELD_GENOME_BUILD, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_GENOME_BUILD])) {
                        $errs[self::FIELD_GENOME_BUILD] = [];
                    }
                    $errs[self::FIELD_GENOME_BUILD][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_ORIENTATION])) {
            $v = $this->getOrientation();
            foreach($validationRules[self::FIELD_ORIENTATION] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MOLECULAR_SEQUENCE_DOT_REFERENCE_SEQ, self::FIELD_ORIENTATION, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_ORIENTATION])) {
                        $errs[self::FIELD_ORIENTATION] = [];
                    }
                    $errs[self::FIELD_ORIENTATION][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_REFERENCE_SEQ_ID])) {
            $v = $this->getReferenceSeqId();
            foreach($validationRules[self::FIELD_REFERENCE_SEQ_ID] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MOLECULAR_SEQUENCE_DOT_REFERENCE_SEQ, self::FIELD_REFERENCE_SEQ_ID, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_REFERENCE_SEQ_ID])) {
                        $errs[self::FIELD_REFERENCE_SEQ_ID] = [];
                    }
                    $errs[self::FIELD_REFERENCE_SEQ_ID][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_REFERENCE_SEQ_POINTER])) {
            $v = $this->getReferenceSeqPointer();
            foreach($validationRules[self::FIELD_REFERENCE_SEQ_POINTER] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MOLECULAR_SEQUENCE_DOT_REFERENCE_SEQ, self::FIELD_REFERENCE_SEQ_POINTER, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_REFERENCE_SEQ_POINTER])) {
                        $errs[self::FIELD_REFERENCE_SEQ_POINTER] = [];
                    }
                    $errs[self::FIELD_REFERENCE_SEQ_POINTER][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_REFERENCE_SEQ_STRING])) {
            $v = $this->getReferenceSeqString();
            foreach($validationRules[self::FIELD_REFERENCE_SEQ_STRING] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MOLECULAR_SEQUENCE_DOT_REFERENCE_SEQ, self::FIELD_REFERENCE_SEQ_STRING, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_REFERENCE_SEQ_STRING])) {
                        $errs[self::FIELD_REFERENCE_SEQ_STRING] = [];
                    }
                    $errs[self::FIELD_REFERENCE_SEQ_STRING][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_STRAND])) {
            $v = $this->getStrand();
            foreach($validationRules[self::FIELD_STRAND] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MOLECULAR_SEQUENCE_DOT_REFERENCE_SEQ, self::FIELD_STRAND, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_STRAND])) {
                        $errs[self::FIELD_STRAND] = [];
                    }
                    $errs[self::FIELD_STRAND][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_WINDOW_START])) {
            $v = $this->getWindowStart();
            foreach($validationRules[self::FIELD_WINDOW_START] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MOLECULAR_SEQUENCE_DOT_REFERENCE_SEQ, self::FIELD_WINDOW_START, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_WINDOW_START])) {
                        $errs[self::FIELD_WINDOW_START] = [];
                    }
                    $errs[self::FIELD_WINDOW_START][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_WINDOW_END])) {
            $v = $this->getWindowEnd();
            foreach($validationRules[self::FIELD_WINDOW_END] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MOLECULAR_SEQUENCE_DOT_REFERENCE_SEQ, self::FIELD_WINDOW_END, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_WINDOW_END])) {
                        $errs[self::FIELD_WINDOW_END] = [];
                    }
                    $errs[self::FIELD_WINDOW_END][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_MODIFIER_EXTENSION])) {
            $v = $this->getModifierExtension();
            foreach($validationRules[self::FIELD_MODIFIER_EXTENSION] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_BACKBONE_ELEMENT, self::FIELD_MODIFIER_EXTENSION, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_MODIFIER_EXTENSION])) {
                        $errs[self::FIELD_MODIFIER_EXTENSION] = [];
                    }
                    $errs[self::FIELD_MODIFIER_EXTENSION][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_EXTENSION])) {
            $v = $this->getExtension();
            foreach($validationRules[self::FIELD_EXTENSION] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_ELEMENT, self::FIELD_EXTENSION, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_EXTENSION])) {
                        $errs[self::FIELD_EXTENSION] = [];
                    }
                    $errs[self::FIELD_EXTENSION][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_ID])) {
            $v = $this->getId();
            foreach($validationRules[self::FIELD_ID] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_ELEMENT, self::FIELD_ID, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_ID])) {
                        $errs[self::FIELD_ID] = [];
                    }
                    $errs[self::FIELD_ID][$rule] = $err;
                }
            }
        }
        return $errs;
    }

    /**
     * @param null|string|\DOMElement $element
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMolecularSequence\FHIRMolecularSequenceReferenceSeq $type
     * @param null|int $libxmlOpts
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMolecularSequence\FHIRMolecularSequenceReferenceSeq
     */
    public static function xmlUnserialize($element = null, PHPFHIRTypeInterface $type = null, $libxmlOpts = 591872)
    {
        if (null === $element) {
            return null;
        }
        if (is_string($element)) {
            libxml_use_internal_errors(true);
            $dom = new \DOMDocument();
            $dom->loadXML($element, $libxmlOpts);
            if (false === $dom) {
                throw new \DomainException(sprintf('FHIRMolecularSequenceReferenceSeq::xmlUnserialize - String provided is not parseable as XML: %s', implode(', ', array_map(function(\libXMLError $err) { return $err->message; }, libxml_get_errors()))));
            }
            libxml_use_internal_errors(false);
            $element = $dom->documentElement;
        }
        if (!($element instanceof \DOMElement)) {
            throw new \InvalidArgumentException(sprintf('FHIRMolecularSequenceReferenceSeq::xmlUnserialize - $node value must be null, \\DOMElement, or valid XML string, %s seen', is_object($element) ? get_class($element) : gettype($element)));
        }
        if (null === $type) {
            $type = new FHIRMolecularSequenceReferenceSeq(null);
        } elseif (!is_object($type) || !($type instanceof FHIRMolecularSequenceReferenceSeq)) {
            throw new \RuntimeException(sprintf(
                'FHIRMolecularSequenceReferenceSeq::xmlUnserialize - $type must be instance of \OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMolecularSequence\FHIRMolecularSequenceReferenceSeq or null, %s seen.',
                is_object($type) ? get_class($type) : gettype($type)
            ));
        }
        if ('' === $type->_getFHIRXMLNamespace() && (null === $element->parentNode || $element->namespaceURI !== $element->parentNode->namespaceURI)) {
            $type->_setFHIRXMLNamespace($element->namespaceURI);
        }
        for($i = 0; $i < $element->childNodes->length; $i++) {
            $n = $element->childNodes->item($i);
            if (!($n instanceof \DOMElement)) {
                continue;
            }
            if (self::FIELD_CHROMOSOME === $n->nodeName) {
                $type->setChromosome(FHIRCodeableConcept::xmlUnserialize($n));
            } elseif (self::FIELD_GENOME_BUILD === $n->nodeName) {
                $type->setGenomeBuild(FHIRString::xmlUnserialize($n));
            } elseif (self::FIELD_ORIENTATION === $n->nodeName) {
                $type->setOrientation(FHIROrientationType::xmlUnserialize($n));
            } elseif (self::FIELD_REFERENCE_SEQ_ID === $n->nodeName) {
                $type->setReferenceSeqId(FHIRCodeableConcept::xmlUnserialize($n));
            } elseif (self::FIELD_REFERENCE_SEQ_POINTER === $n->nodeName) {
                $type->setReferenceSeqPointer(FHIRReference::xmlUnserialize($n));
            } elseif (self::FIELD_REFERENCE_SEQ_STRING === $n->nodeName) {
                $type->setReferenceSeqString(FHIRString::xmlUnserialize($n));
            } elseif (self::FIELD_STRAND === $n->nodeName) {
                $type->setStrand(FHIRStrandType::xmlUnserialize($n));
            } elseif (self::FIELD_WINDOW_START === $n->nodeName) {
                $type->setWindowStart(FHIRInteger::xmlUnserialize($n));
            } elseif (self::FIELD_WINDOW_END === $n->nodeName) {
                $type->setWindowEnd(FHIRInteger::xmlUnserialize($n));
            } elseif (self::FIELD_MODIFIER_EXTENSION === $n->nodeName) {
                $type->addModifierExtension(FHIRExtension::xmlUnserialize($n));
            } elseif (self::FIELD_EXTENSION === $n->nodeName) {
                $type->addExtension(FHIRExtension::xmlUnserialize($n));
            } elseif (self::FIELD_ID === $n->nodeName) {
                $type->setId(FHIRStringPrimitive::xmlUnserialize($n));
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_GENOME_BUILD);
        if (null !== $n) {
            $pt = $type->getGenomeBuild();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setGenomeBuild($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_REFERENCE_SEQ_STRING);
        if (null !== $n) {
            $pt = $type->getReferenceSeqString();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setReferenceSeqString($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_WINDOW_START);
        if (null !== $n) {
            $pt = $type->getWindowStart();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setWindowStart($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_WINDOW_END);
        if (null !== $n) {
            $pt = $type->getWindowEnd();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setWindowEnd($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_ID);
        if (null !== $n) {
            $pt = $type->getId();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setId($n->nodeValue);
            }
        }
        return $type;
    }

    /**
     * @param null|\DOMElement $element
     * @param null|int $libxmlOpts
     * @return \DOMElement
     */
    public function xmlSerialize(\DOMElement $element = null, $libxmlOpts = 591872)
    {
        if (null === $element) {
            $dom = new \DOMDocument();
            $dom->loadXML($this->_getFHIRXMLElementDefinition(), $libxmlOpts);
            $element = $dom->documentElement;
        } elseif (null === $element->namespaceURI && '' !== ($xmlns = $this->_getFHIRXMLNamespace())) {
            $element->setAttribute('xmlns', $xmlns);
        }
        parent::xmlSerialize($element);
        if (null !== ($v = $this->getChromosome())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_CHROMOSOME);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getGenomeBuild())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_GENOME_BUILD);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getOrientation())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_ORIENTATION);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getReferenceSeqId())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_REFERENCE_SEQ_ID);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getReferenceSeqPointer())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_REFERENCE_SEQ_POINTER);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getReferenceSeqString())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_REFERENCE_SEQ_STRING);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getStrand())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_STRAND);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getWindowStart())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_WINDOW_START);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getWindowEnd())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_WINDOW_END);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        return $element;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $a = parent::jsonSerialize();
        if (null !== ($v = $this->getChromosome())) {
            $a[self::FIELD_CHROMOSOME] = $v;
        }
        if (null !== ($v = $this->getGenomeBuild())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_GENOME_BUILD] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRString::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_GENOME_BUILD_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getOrientation())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_ORIENTATION] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIROrientationType::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_ORIENTATION_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getReferenceSeqId())) {
            $a[self::FIELD_REFERENCE_SEQ_ID] = $v;
        }
        if (null !== ($v = $this->getReferenceSeqPointer())) {
            $a[self::FIELD_REFERENCE_SEQ_POINTER] = $v;
        }
        if (null !== ($v = $this->getReferenceSeqString())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_REFERENCE_SEQ_STRING] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRString::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_REFERENCE_SEQ_STRING_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getStrand())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_STRAND] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRStrandType::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_STRAND_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getWindowStart())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_WINDOW_START] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRInteger::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_WINDOW_START_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getWindowEnd())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_WINDOW_END] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRInteger::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_WINDOW_END_EXT] = $ext;
            }
        }
        return $a;
    }


    /**
     * @return string
     */
    public function __toString()
    {
        return self::FHIR_TYPE_NAME;
    }
}