<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMolecularSequence;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 *
 * Class creation date: April 15th, 2026 16:02+0000
 *
 * PHPFHIR Copyright:
 *
 * Copyright 2016-2026 Daniel Carbone (daniel.p.carbone@gmail.com)
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
use OpenEMR\FHIR\Encoding\JSONSerializationOptionsTrait;
use OpenEMR\FHIR\Encoding\SerializeConfig;
use OpenEMR\FHIR\Encoding\UnserializeConfig;
use OpenEMR\FHIR\Encoding\ValueXMLLocationEnum;
use OpenEMR\FHIR\Encoding\XMLSerializationOptionsTrait;
use OpenEMR\FHIR\Encoding\XMLWriter;
use OpenEMR\FHIR\Types\ElementTypeInterface;
use OpenEMR\FHIR\Validation\TypeValidationsTrait;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIROrientationTypeList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRStrandTypeList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIROrientationType;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRStrandType;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * Raw data describing a biological sequence.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRMolecularSequenceReferenceSeq extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_MOLECULAR_SEQUENCE_DOT_REFERENCE_SEQ;

    /* class_default.php:56 */
    public const FIELD_CHROMOSOME = 'chromosome';
    public const FIELD_GENOME_BUILD = 'genomeBuild';
    public const FIELD_GENOME_BUILD_EXT = '_genomeBuild';
    public const FIELD_ORIENTATION = 'orientation';
    public const FIELD_ORIENTATION_EXT = '_orientation';
    public const FIELD_REFERENCE_SEQ_ID = 'referenceSeqId';
    public const FIELD_REFERENCE_SEQ_POINTER = 'referenceSeqPointer';
    public const FIELD_REFERENCE_SEQ_STRING = 'referenceSeqString';
    public const FIELD_REFERENCE_SEQ_STRING_EXT = '_referenceSeqString';
    public const FIELD_STRAND = 'strand';
    public const FIELD_STRAND_EXT = '_strand';
    public const FIELD_WINDOW_START = 'windowStart';
    public const FIELD_WINDOW_START_EXT = '_windowStart';
    public const FIELD_WINDOW_END = 'windowEnd';
    public const FIELD_WINDOW_END_EXT = '_windowEnd';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_GENOME_BUILD => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_ORIENTATION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_REFERENCE_SEQ_STRING => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_STRAND => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_WINDOW_START => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_WINDOW_END => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
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
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $chromosome;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The Genome Build used for reference, following GRCh build versions e.g. 'GRCh
     * 37'. Version number must be included if a versioned release of a primary build
     * was used.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $genomeBuild;
    /**
     * Type for orientation.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A relative reference to a DNA strand based on gene orientation. The strand that
     * contains the open reading frame of the gene is the "sense" strand, and the
     * opposite complementary strand is the "antisense" strand.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIROrientationType
     */
    #[FHIROrientationType]
    protected FHIROrientationType $orientation;
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
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $referenceSeqId;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A pointer to another MolecularSequence entity as reference sequence.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $referenceSeqPointer;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A string like "ACGT".
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $referenceSeqString;
    /**
     * Type for strand.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An absolute reference to a strand. The Watson strand is the strand whose 5'-end
     * is on the short arm of the chromosome, and the Crick strand as the one whose
     * 5'-end is on the long arm.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRStrandType
     */
    #[FHIRStrandType]
    protected FHIRStrandType $strand;
    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Start position of the window on the reference sequence. If the coordinate system
     * is either 0-based or 1-based, then start position is inclusive.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger
     */
    #[FHIRInteger]
    protected FHIRInteger $windowStart;
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
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger
     */
    #[FHIRInteger]
    protected FHIRInteger $windowEnd;

    /* constructor.php:61 */
    /**
     * FHIRMolecularSequenceReferenceSeq Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $chromosome
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $genomeBuild
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIROrientationTypeList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIROrientationType $orientation
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $referenceSeqId
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $referenceSeqPointer
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $referenceSeqString
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRStrandTypeList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRStrandType $strand
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger $windowStart
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger $windowEnd
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|FHIRCodeableConcept $chromosome = null,
                                null|string|FHIRStringPrimitive|FHIRString $genomeBuild = null,
                                null|string|FHIROrientationTypeList|FHIROrientationType $orientation = null,
                                null|FHIRCodeableConcept $referenceSeqId = null,
                                null|FHIRReference $referenceSeqPointer = null,
                                null|string|FHIRStringPrimitive|FHIRString $referenceSeqString = null,
                                null|string|FHIRStrandTypeList|FHIRStrandType $strand = null,
                                null|string|float|FHIRIntegerPrimitive|FHIRInteger $windowStart = null,
                                null|string|float|FHIRIntegerPrimitive|FHIRInteger $windowEnd = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $chromosome) {
            $this->setChromosome($chromosome);
        }
        if (null !== $genomeBuild) {
            $this->setGenomeBuild($genomeBuild);
        }
        if (null !== $orientation) {
            $this->setOrientation($orientation);
        }
        if (null !== $referenceSeqId) {
            $this->setReferenceSeqId($referenceSeqId);
        }
        if (null !== $referenceSeqPointer) {
            $this->setReferenceSeqPointer($referenceSeqPointer);
        }
        if (null !== $referenceSeqString) {
            $this->setReferenceSeqString($referenceSeqString);
        }
        if (null !== $strand) {
            $this->setStrand($strand);
        }
        if (null !== $windowStart) {
            $this->setWindowStart($windowStart);
        }
        if (null !== $windowEnd) {
            $this->setWindowEnd($windowEnd);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
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
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getChromosome(): null|FHIRCodeableConcept
    {
        return $this->chromosome ?? null;
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
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $chromosome
     * @return static
     */
    public function setChromosome(null|FHIRCodeableConcept $chromosome): self
    {
        if (null === $chromosome) {
            unset($this->chromosome);
            return $this;
        }
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
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getGenomeBuild(): null|FHIRString
    {
        return $this->genomeBuild ?? null;
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
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $genomeBuild
     * @return static
     */
    public function setGenomeBuild(null|string|FHIRStringPrimitive|FHIRString $genomeBuild): self
    {
        if (null === $genomeBuild) {
            unset($this->genomeBuild);
            return $this;
        }
        if (!($genomeBuild instanceof FHIRString)) {
            $genomeBuild = new FHIRString(value: $genomeBuild);
        }
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
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIROrientationType
     */
    public function getOrientation(): null|FHIROrientationType
    {
        return $this->orientation ?? null;
    }

    /**
     * Type for orientation.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A relative reference to a DNA strand based on gene orientation. The strand that
     * contains the open reading frame of the gene is the "sense" strand, and the
     * opposite complementary strand is the "antisense" strand.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIROrientationTypeList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIROrientationType $orientation
     * @return static
     */
    public function setOrientation(null|string|FHIROrientationTypeList|FHIROrientationType $orientation): self
    {
        if (null === $orientation) {
            unset($this->orientation);
            return $this;
        }
        if (!($orientation instanceof FHIROrientationType)) {
            $orientation = new FHIROrientationType(value: $orientation);
        }
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
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getReferenceSeqId(): null|FHIRCodeableConcept
    {
        return $this->referenceSeqId ?? null;
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
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $referenceSeqId
     * @return static
     */
    public function setReferenceSeqId(null|FHIRCodeableConcept $referenceSeqId): self
    {
        if (null === $referenceSeqId) {
            unset($this->referenceSeqId);
            return $this;
        }
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
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getReferenceSeqPointer(): null|FHIRReference
    {
        return $this->referenceSeqPointer ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A pointer to another MolecularSequence entity as reference sequence.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $referenceSeqPointer
     * @return static
     */
    public function setReferenceSeqPointer(null|FHIRReference $referenceSeqPointer): self
    {
        if (null === $referenceSeqPointer) {
            unset($this->referenceSeqPointer);
            return $this;
        }
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
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getReferenceSeqString(): null|FHIRString
    {
        return $this->referenceSeqString ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A string like "ACGT".
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $referenceSeqString
     * @return static
     */
    public function setReferenceSeqString(null|string|FHIRStringPrimitive|FHIRString $referenceSeqString): self
    {
        if (null === $referenceSeqString) {
            unset($this->referenceSeqString);
            return $this;
        }
        if (!($referenceSeqString instanceof FHIRString)) {
            $referenceSeqString = new FHIRString(value: $referenceSeqString);
        }
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
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRStrandType
     */
    public function getStrand(): null|FHIRStrandType
    {
        return $this->strand ?? null;
    }

    /**
     * Type for strand.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An absolute reference to a strand. The Watson strand is the strand whose 5'-end
     * is on the short arm of the chromosome, and the Crick strand as the one whose
     * 5'-end is on the long arm.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRStrandTypeList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRStrandType $strand
     * @return static
     */
    public function setStrand(null|string|FHIRStrandTypeList|FHIRStrandType $strand): self
    {
        if (null === $strand) {
            unset($this->strand);
            return $this;
        }
        if (!($strand instanceof FHIRStrandType)) {
            $strand = new FHIRStrandType(value: $strand);
        }
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
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger
     */
    public function getWindowStart(): null|FHIRInteger
    {
        return $this->windowStart ?? null;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Start position of the window on the reference sequence. If the coordinate system
     * is either 0-based or 1-based, then start position is inclusive.
     *
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger $windowStart
     * @return static
     */
    public function setWindowStart(null|string|float|FHIRIntegerPrimitive|FHIRInteger $windowStart): self
    {
        if (null === $windowStart) {
            unset($this->windowStart);
            return $this;
        }
        if (!($windowStart instanceof FHIRInteger)) {
            $windowStart = new FHIRInteger(value: $windowStart);
        }
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
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger
     */
    public function getWindowEnd(): null|FHIRInteger
    {
        return $this->windowEnd ?? null;
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
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger $windowEnd
     * @return static
     */
    public function setWindowEnd(null|string|float|FHIRIntegerPrimitive|FHIRInteger $windowEnd): self
    {
        if (null === $windowEnd) {
            unset($this->windowEnd);
            return $this;
        }
        if (!($windowEnd instanceof FHIRInteger)) {
            $windowEnd = new FHIRInteger(value: $windowEnd);
        }
        $this->windowEnd = $windowEnd;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMolecularSequence\FHIRMolecularSequenceReferenceSeq $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMolecularSequence\FHIRMolecularSequenceReferenceSeq
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRMolecularSequenceReferenceSeq)) {
            throw new \RuntimeException(sprintf(
                '%s::xmlUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        foreach ($element->children() as $ce) {
            $cen = $ce->getName();
            if (self::FIELD_EXTENSION === $cen) {
                $type->addExtension(FHIRExtension::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ID === $cen) {
                $va = $ce->attributes()[FHIRStringPrimitive::FIELD_VALUE] ?? null;
                if (null !== $va) {
                    $type->setId((string)$va);
                    $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::ELEMENT_ATTRIBUTE);
                } else {
                    $type->setId((string)$ce);
                    $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::ELEMENT_VALUE);
                }
            } else if (self::FIELD_MODIFIER_EXTENSION === $cen) {
                $type->addModifierExtension(FHIRExtension::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CHROMOSOME === $cen) {
                $type->setChromosome(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_GENOME_BUILD === $cen) {
                $type->setGenomeBuild(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ORIENTATION === $cen) {
                $type->setOrientation(FHIROrientationType::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REFERENCE_SEQ_ID === $cen) {
                $type->setReferenceSeqId(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REFERENCE_SEQ_POINTER === $cen) {
                $type->setReferenceSeqPointer(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REFERENCE_SEQ_STRING === $cen) {
                $type->setReferenceSeqString(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_STRAND === $cen) {
                $type->setStrand(FHIRStrandType::xmlUnserialize($ce, $config));
            } else if (self::FIELD_WINDOW_START === $cen) {
                $type->setWindowStart(FHIRInteger::xmlUnserialize($ce, $config));
            } else if (self::FIELD_WINDOW_END === $cen) {
                $type->setWindowEnd(FHIRInteger::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_GENOME_BUILD])) {
            if (isset($type->genomeBuild)) {
                $type->genomeBuild->setValue((string)$attributes[self::FIELD_GENOME_BUILD]);
            } else {
                $type->setGenomeBuild((string)$attributes[self::FIELD_GENOME_BUILD]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_GENOME_BUILD, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_ORIENTATION])) {
            if (isset($type->orientation)) {
                $type->orientation->setValue((string)$attributes[self::FIELD_ORIENTATION]);
            } else {
                $type->setOrientation((string)$attributes[self::FIELD_ORIENTATION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_ORIENTATION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_REFERENCE_SEQ_STRING])) {
            if (isset($type->referenceSeqString)) {
                $type->referenceSeqString->setValue((string)$attributes[self::FIELD_REFERENCE_SEQ_STRING]);
            } else {
                $type->setReferenceSeqString((string)$attributes[self::FIELD_REFERENCE_SEQ_STRING]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_REFERENCE_SEQ_STRING, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_STRAND])) {
            if (isset($type->strand)) {
                $type->strand->setValue((string)$attributes[self::FIELD_STRAND]);
            } else {
                $type->setStrand((string)$attributes[self::FIELD_STRAND]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_STRAND, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_WINDOW_START])) {
            if (isset($type->windowStart)) {
                $type->windowStart->setValue((string)$attributes[self::FIELD_WINDOW_START]);
            } else {
                $type->setWindowStart((string)$attributes[self::FIELD_WINDOW_START]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_WINDOW_START, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_WINDOW_END])) {
            if (isset($type->windowEnd)) {
                $type->windowEnd->setValue((string)$attributes[self::FIELD_WINDOW_END]);
            } else {
                $type->setWindowEnd((string)$attributes[self::FIELD_WINDOW_END]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_WINDOW_END, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        return $type;
    }

    /**
     * @param \OpenEMR\FHIR\Encoding\XMLWriter $xw
     * @param \OpenEMR\FHIR\Encoding\SerializeConfig $config
     */
    public function xmlSerialize(XMLWriter $xw,
                                 SerializeConfig $config): void
    {
        if (isset($this->genomeBuild) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_GENOME_BUILD]) {
            $xw->writeAttribute(self::FIELD_GENOME_BUILD, $this->genomeBuild->_getValueAsString());
        }
        if (isset($this->orientation) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_ORIENTATION]) {
            $xw->writeAttribute(self::FIELD_ORIENTATION, $this->orientation->_getValueAsString());
        }
        if (isset($this->referenceSeqString) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_REFERENCE_SEQ_STRING]) {
            $xw->writeAttribute(self::FIELD_REFERENCE_SEQ_STRING, $this->referenceSeqString->_getValueAsString());
        }
        if (isset($this->strand) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_STRAND]) {
            $xw->writeAttribute(self::FIELD_STRAND, $this->strand->_getValueAsString());
        }
        if (isset($this->windowStart) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_WINDOW_START]) {
            $xw->writeAttribute(self::FIELD_WINDOW_START, $this->windowStart->_getValueAsString());
        }
        if (isset($this->windowEnd) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_WINDOW_END]) {
            $xw->writeAttribute(self::FIELD_WINDOW_END, $this->windowEnd->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->chromosome)) {
            $xw->startElement(self::FIELD_CHROMOSOME);
            $this->chromosome->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->genomeBuild)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_GENOME_BUILD]
                || $this->genomeBuild->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_GENOME_BUILD);
            $this->genomeBuild->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_GENOME_BUILD]);
            $xw->endElement();
        }
        if (isset($this->orientation)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_ORIENTATION]
                || $this->orientation->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_ORIENTATION);
            $this->orientation->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_ORIENTATION]);
            $xw->endElement();
        }
        if (isset($this->referenceSeqId)) {
            $xw->startElement(self::FIELD_REFERENCE_SEQ_ID);
            $this->referenceSeqId->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->referenceSeqPointer)) {
            $xw->startElement(self::FIELD_REFERENCE_SEQ_POINTER);
            $this->referenceSeqPointer->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->referenceSeqString)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_REFERENCE_SEQ_STRING]
                || $this->referenceSeqString->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_REFERENCE_SEQ_STRING);
            $this->referenceSeqString->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_REFERENCE_SEQ_STRING]);
            $xw->endElement();
        }
        if (isset($this->strand)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_STRAND]
                || $this->strand->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_STRAND);
            $this->strand->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_STRAND]);
            $xw->endElement();
        }
        if (isset($this->windowStart)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_WINDOW_START]
                || $this->windowStart->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_WINDOW_START);
            $this->windowStart->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_WINDOW_START]);
            $xw->endElement();
        }
        if (isset($this->windowEnd)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_WINDOW_END]
                || $this->windowEnd->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_WINDOW_END);
            $this->windowEnd->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_WINDOW_END]);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMolecularSequence\FHIRMolecularSequenceReferenceSeq $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMolecularSequence\FHIRMolecularSequenceReferenceSeq
     * @throws \Exception
     */
    public static function jsonUnserialize(\stdClass $decoded,
                                           UnserializeConfig $config,
                                           null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            if (isset($decoded->resourceType) && $decoded->resourceType !== static::FHIR_TYPE_NAME) {
                throw new \DomainException(sprintf(
                    '%s::jsonUnserialize - Cannot unmarshal data for resource type "%s" into this type.',
                    ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                    $decoded->resourceType,
                ));
            }
            $type = new static();
        } else if (!($type instanceof FHIRMolecularSequenceReferenceSeq)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->chromosome) || property_exists($decoded, self::FIELD_CHROMOSOME)) {
            if (is_array($decoded->chromosome)) {
                $type->setChromosome(FHIRCodeableConcept::jsonUnserialize(reset($decoded->chromosome), $config));
            } else {
                $type->setChromosome(FHIRCodeableConcept::jsonUnserialize($decoded->chromosome, $config));
            }
        }
        if (isset($decoded->genomeBuild)
            || isset($decoded->_genomeBuild)
            || property_exists($decoded, self::FIELD_GENOME_BUILD)
            || property_exists($decoded, self::FIELD_GENOME_BUILD_EXT)) {
            $v = $decoded->_genomeBuild ?? new \stdClass();
            $v->value = $decoded->genomeBuild ?? null;
            $type->setGenomeBuild(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->orientation)
            || isset($decoded->_orientation)
            || property_exists($decoded, self::FIELD_ORIENTATION)
            || property_exists($decoded, self::FIELD_ORIENTATION_EXT)) {
            $v = $decoded->_orientation ?? new \stdClass();
            $v->value = $decoded->orientation ?? null;
            $type->setOrientation(FHIROrientationType::jsonUnserialize($v, $config));
        }
        if (isset($decoded->referenceSeqId) || property_exists($decoded, self::FIELD_REFERENCE_SEQ_ID)) {
            if (is_array($decoded->referenceSeqId)) {
                $type->setReferenceSeqId(FHIRCodeableConcept::jsonUnserialize(reset($decoded->referenceSeqId), $config));
            } else {
                $type->setReferenceSeqId(FHIRCodeableConcept::jsonUnserialize($decoded->referenceSeqId, $config));
            }
        }
        if (isset($decoded->referenceSeqPointer) || property_exists($decoded, self::FIELD_REFERENCE_SEQ_POINTER)) {
            if (is_array($decoded->referenceSeqPointer)) {
                $type->setReferenceSeqPointer(FHIRReference::jsonUnserialize(reset($decoded->referenceSeqPointer), $config));
            } else {
                $type->setReferenceSeqPointer(FHIRReference::jsonUnserialize($decoded->referenceSeqPointer, $config));
            }
        }
        if (isset($decoded->referenceSeqString)
            || isset($decoded->_referenceSeqString)
            || property_exists($decoded, self::FIELD_REFERENCE_SEQ_STRING)
            || property_exists($decoded, self::FIELD_REFERENCE_SEQ_STRING_EXT)) {
            $v = $decoded->_referenceSeqString ?? new \stdClass();
            $v->value = $decoded->referenceSeqString ?? null;
            $type->setReferenceSeqString(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->strand)
            || isset($decoded->_strand)
            || property_exists($decoded, self::FIELD_STRAND)
            || property_exists($decoded, self::FIELD_STRAND_EXT)) {
            $v = $decoded->_strand ?? new \stdClass();
            $v->value = $decoded->strand ?? null;
            $type->setStrand(FHIRStrandType::jsonUnserialize($v, $config));
        }
        if (isset($decoded->windowStart)
            || isset($decoded->_windowStart)
            || property_exists($decoded, self::FIELD_WINDOW_START)
            || property_exists($decoded, self::FIELD_WINDOW_START_EXT)) {
            $v = $decoded->_windowStart ?? new \stdClass();
            $v->value = $decoded->windowStart ?? null;
            $type->setWindowStart(FHIRInteger::jsonUnserialize($v, $config));
        }
        if (isset($decoded->windowEnd)
            || isset($decoded->_windowEnd)
            || property_exists($decoded, self::FIELD_WINDOW_END)
            || property_exists($decoded, self::FIELD_WINDOW_END_EXT)) {
            $v = $decoded->_windowEnd ?? new \stdClass();
            $v->value = $decoded->windowEnd ?? null;
            $type->setWindowEnd(FHIRInteger::jsonUnserialize($v, $config));
        }
        return $type;
    }

    /**
     * @return \stdClass
     */
    public function jsonSerialize(): mixed
    {
        $out = parent::jsonSerialize();
        if (isset($this->chromosome)) {
            $out->chromosome = $this->chromosome;
        }
        if (isset($this->genomeBuild)) {
            if (null !== ($val = $this->genomeBuild->getValue())) {
                $out->genomeBuild = $val;
            }
            if ($this->genomeBuild->_nonValueFieldDefined()) {
                $ext = $this->genomeBuild->jsonSerialize();
                unset($ext->value);
                $out->_genomeBuild = $ext;
            }
        }
        if (isset($this->orientation)) {
            if (null !== ($val = $this->orientation->getValue())) {
                $out->orientation = $val;
            }
            if ($this->orientation->_nonValueFieldDefined()) {
                $ext = $this->orientation->jsonSerialize();
                unset($ext->value);
                $out->_orientation = $ext;
            }
        }
        if (isset($this->referenceSeqId)) {
            $out->referenceSeqId = $this->referenceSeqId;
        }
        if (isset($this->referenceSeqPointer)) {
            $out->referenceSeqPointer = $this->referenceSeqPointer;
        }
        if (isset($this->referenceSeqString)) {
            if (null !== ($val = $this->referenceSeqString->getValue())) {
                $out->referenceSeqString = $val;
            }
            if ($this->referenceSeqString->_nonValueFieldDefined()) {
                $ext = $this->referenceSeqString->jsonSerialize();
                unset($ext->value);
                $out->_referenceSeqString = $ext;
            }
        }
        if (isset($this->strand)) {
            if (null !== ($val = $this->strand->getValue())) {
                $out->strand = $val;
            }
            if ($this->strand->_nonValueFieldDefined()) {
                $ext = $this->strand->jsonSerialize();
                unset($ext->value);
                $out->_strand = $ext;
            }
        }
        if (isset($this->windowStart)) {
            if (null !== ($val = $this->windowStart->getValue())) {
                $out->windowStart = $val;
            }
            if ($this->windowStart->_nonValueFieldDefined()) {
                $ext = $this->windowStart->jsonSerialize();
                unset($ext->value);
                $out->_windowStart = $ext;
            }
        }
        if (isset($this->windowEnd)) {
            if (null !== ($val = $this->windowEnd->getValue())) {
                $out->windowEnd = $val;
            }
            if ($this->windowEnd->_nonValueFieldDefined()) {
                $ext = $this->windowEnd->jsonSerialize();
                unset($ext->value);
                $out->_windowEnd = $ext;
            }
        }
        return $out;
    }
}
