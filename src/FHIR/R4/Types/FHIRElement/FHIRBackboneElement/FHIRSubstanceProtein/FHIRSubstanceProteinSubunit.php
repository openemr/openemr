<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceProtein;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * A SubstanceProtein is defined as a single unit of a linear amino acid sequence,
 * or a combination of subunits that are either covalently linked or have a defined
 * invariant stoichiometric relationship. This includes all synthetic, recombinant
 * and purified SubstanceProteins of defined sequence, whether the use is
 * therapeutic or prophylactic. This set of elements will be used to describe
 * albumins, coagulation factors, cytokines, growth factors,
 * peptide/SubstanceProtein hormones, enzymes, toxins, toxoids, recombinant
 * vaccines, and immunomodulators.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRSubstanceProteinSubunit extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_SUBSTANCE_PROTEIN_DOT_SUBUNIT;

    /* class_default.php:56 */
    public const FIELD_SUBUNIT = 'subunit';
    public const FIELD_SUBUNIT_EXT = '_subunit';
    public const FIELD_SEQUENCE = 'sequence';
    public const FIELD_SEQUENCE_EXT = '_sequence';
    public const FIELD_LENGTH = 'length';
    public const FIELD_LENGTH_EXT = '_length';
    public const FIELD_SEQUENCE_ATTACHMENT = 'sequenceAttachment';
    public const FIELD_N_TERMINAL_MODIFICATION_ID = 'nTerminalModificationId';
    public const FIELD_N_TERMINAL_MODIFICATION = 'nTerminalModification';
    public const FIELD_N_TERMINAL_MODIFICATION_EXT = '_nTerminalModification';
    public const FIELD_C_TERMINAL_MODIFICATION_ID = 'cTerminalModificationId';
    public const FIELD_C_TERMINAL_MODIFICATION = 'cTerminalModification';
    public const FIELD_C_TERMINAL_MODIFICATION_EXT = '_cTerminalModification';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_SUBUNIT => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_SEQUENCE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_LENGTH => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_N_TERMINAL_MODIFICATION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_C_TERMINAL_MODIFICATION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Index of primary sequences of amino acids linked through peptide bonds in order
     * of decreasing length. Sequences of the same length will be ordered by molecular
     * weight. Subunits that have identical sequences will be repeated and have
     * sequential subscripts.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger
     */
    #[FHIRInteger]
    protected FHIRInteger $subunit;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The sequence information shall be provided enumerating the amino acids from N-
     * to C-terminal end using standard single-letter amino acid codes. Uppercase shall
     * be used for L-amino acids and lowercase for D-amino acids. Transcribed
     * SubstanceProteins will always be described using the translated sequence; for
     * synthetic peptide containing amino acids that are not represented with a single
     * letter code an X should be used within the sequence. The modified amino acids
     * will be distinguished by their position in the sequence.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $sequence;
    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Length of linear sequences of amino acids contained in the subunit.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger
     */
    #[FHIRInteger]
    protected FHIRInteger $length;
    /**
     * For referring to data content defined in other formats.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The sequence information shall be provided enumerating the amino acids from N-
     * to C-terminal end using standard single-letter amino acid codes. Uppercase shall
     * be used for L-amino acids and lowercase for D-amino acids. Transcribed
     * SubstanceProteins will always be described using the translated sequence; for
     * synthetic peptide containing amino acids that are not represented with a single
     * letter code an X should be used within the sequence. The modified amino acids
     * will be distinguished by their position in the sequence.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment
     */
    #[FHIRAttachment]
    protected FHIRAttachment $sequenceAttachment;
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Unique identifier for molecular fragment modification based on the ISO 11238
     * Substance ID.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier
     */
    #[FHIRIdentifier]
    protected FHIRIdentifier $nTerminalModificationId;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The name of the fragment modified at the N-terminal of the SubstanceProtein
     * shall be specified.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $nTerminalModification;
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Unique identifier for molecular fragment modification based on the ISO 11238
     * Substance ID.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier
     */
    #[FHIRIdentifier]
    protected FHIRIdentifier $cTerminalModificationId;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The modification at the C-terminal shall be specified.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $cTerminalModification;

    /* constructor.php:61 */
    /**
     * FHIRSubstanceProteinSubunit Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger $subunit
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $sequence
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger $length
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment $sequenceAttachment
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier $nTerminalModificationId
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $nTerminalModification
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier $cTerminalModificationId
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $cTerminalModification
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|string|float|FHIRIntegerPrimitive|FHIRInteger $subunit = null,
                                null|string|FHIRStringPrimitive|FHIRString $sequence = null,
                                null|string|float|FHIRIntegerPrimitive|FHIRInteger $length = null,
                                null|FHIRAttachment $sequenceAttachment = null,
                                null|FHIRIdentifier $nTerminalModificationId = null,
                                null|string|FHIRStringPrimitive|FHIRString $nTerminalModification = null,
                                null|FHIRIdentifier $cTerminalModificationId = null,
                                null|string|FHIRStringPrimitive|FHIRString $cTerminalModification = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $subunit) {
            $this->setSubunit($subunit);
        }
        if (null !== $sequence) {
            $this->setSequence($sequence);
        }
        if (null !== $length) {
            $this->setLength($length);
        }
        if (null !== $sequenceAttachment) {
            $this->setSequenceAttachment($sequenceAttachment);
        }
        if (null !== $nTerminalModificationId) {
            $this->setNTerminalModificationId($nTerminalModificationId);
        }
        if (null !== $nTerminalModification) {
            $this->setNTerminalModification($nTerminalModification);
        }
        if (null !== $cTerminalModificationId) {
            $this->setCTerminalModificationId($cTerminalModificationId);
        }
        if (null !== $cTerminalModification) {
            $this->setCTerminalModification($cTerminalModification);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Index of primary sequences of amino acids linked through peptide bonds in order
     * of decreasing length. Sequences of the same length will be ordered by molecular
     * weight. Subunits that have identical sequences will be repeated and have
     * sequential subscripts.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger
     */
    public function getSubunit(): null|FHIRInteger
    {
        return $this->subunit ?? null;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Index of primary sequences of amino acids linked through peptide bonds in order
     * of decreasing length. Sequences of the same length will be ordered by molecular
     * weight. Subunits that have identical sequences will be repeated and have
     * sequential subscripts.
     *
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger $subunit
     * @return static
     */
    public function setSubunit(null|string|float|FHIRIntegerPrimitive|FHIRInteger $subunit): self
    {
        if (null === $subunit) {
            unset($this->subunit);
            return $this;
        }
        if (!($subunit instanceof FHIRInteger)) {
            $subunit = new FHIRInteger(value: $subunit);
        }
        $this->subunit = $subunit;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The sequence information shall be provided enumerating the amino acids from N-
     * to C-terminal end using standard single-letter amino acid codes. Uppercase shall
     * be used for L-amino acids and lowercase for D-amino acids. Transcribed
     * SubstanceProteins will always be described using the translated sequence; for
     * synthetic peptide containing amino acids that are not represented with a single
     * letter code an X should be used within the sequence. The modified amino acids
     * will be distinguished by their position in the sequence.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getSequence(): null|FHIRString
    {
        return $this->sequence ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The sequence information shall be provided enumerating the amino acids from N-
     * to C-terminal end using standard single-letter amino acid codes. Uppercase shall
     * be used for L-amino acids and lowercase for D-amino acids. Transcribed
     * SubstanceProteins will always be described using the translated sequence; for
     * synthetic peptide containing amino acids that are not represented with a single
     * letter code an X should be used within the sequence. The modified amino acids
     * will be distinguished by their position in the sequence.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $sequence
     * @return static
     */
    public function setSequence(null|string|FHIRStringPrimitive|FHIRString $sequence): self
    {
        if (null === $sequence) {
            unset($this->sequence);
            return $this;
        }
        if (!($sequence instanceof FHIRString)) {
            $sequence = new FHIRString(value: $sequence);
        }
        $this->sequence = $sequence;
        return $this;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Length of linear sequences of amino acids contained in the subunit.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger
     */
    public function getLength(): null|FHIRInteger
    {
        return $this->length ?? null;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Length of linear sequences of amino acids contained in the subunit.
     *
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger $length
     * @return static
     */
    public function setLength(null|string|float|FHIRIntegerPrimitive|FHIRInteger $length): self
    {
        if (null === $length) {
            unset($this->length);
            return $this;
        }
        if (!($length instanceof FHIRInteger)) {
            $length = new FHIRInteger(value: $length);
        }
        $this->length = $length;
        return $this;
    }

    /**
     * For referring to data content defined in other formats.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The sequence information shall be provided enumerating the amino acids from N-
     * to C-terminal end using standard single-letter amino acid codes. Uppercase shall
     * be used for L-amino acids and lowercase for D-amino acids. Transcribed
     * SubstanceProteins will always be described using the translated sequence; for
     * synthetic peptide containing amino acids that are not represented with a single
     * letter code an X should be used within the sequence. The modified amino acids
     * will be distinguished by their position in the sequence.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment
     */
    public function getSequenceAttachment(): null|FHIRAttachment
    {
        return $this->sequenceAttachment ?? null;
    }

    /**
     * For referring to data content defined in other formats.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The sequence information shall be provided enumerating the amino acids from N-
     * to C-terminal end using standard single-letter amino acid codes. Uppercase shall
     * be used for L-amino acids and lowercase for D-amino acids. Transcribed
     * SubstanceProteins will always be described using the translated sequence; for
     * synthetic peptide containing amino acids that are not represented with a single
     * letter code an X should be used within the sequence. The modified amino acids
     * will be distinguished by their position in the sequence.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment $sequenceAttachment
     * @return static
     */
    public function setSequenceAttachment(null|FHIRAttachment $sequenceAttachment): self
    {
        if (null === $sequenceAttachment) {
            unset($this->sequenceAttachment);
            return $this;
        }
        $this->sequenceAttachment = $sequenceAttachment;
        return $this;
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Unique identifier for molecular fragment modification based on the ISO 11238
     * Substance ID.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier
     */
    public function getNTerminalModificationId(): null|FHIRIdentifier
    {
        return $this->nTerminalModificationId ?? null;
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Unique identifier for molecular fragment modification based on the ISO 11238
     * Substance ID.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier $nTerminalModificationId
     * @return static
     */
    public function setNTerminalModificationId(null|FHIRIdentifier $nTerminalModificationId): self
    {
        if (null === $nTerminalModificationId) {
            unset($this->nTerminalModificationId);
            return $this;
        }
        $this->nTerminalModificationId = $nTerminalModificationId;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The name of the fragment modified at the N-terminal of the SubstanceProtein
     * shall be specified.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getNTerminalModification(): null|FHIRString
    {
        return $this->nTerminalModification ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The name of the fragment modified at the N-terminal of the SubstanceProtein
     * shall be specified.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $nTerminalModification
     * @return static
     */
    public function setNTerminalModification(null|string|FHIRStringPrimitive|FHIRString $nTerminalModification): self
    {
        if (null === $nTerminalModification) {
            unset($this->nTerminalModification);
            return $this;
        }
        if (!($nTerminalModification instanceof FHIRString)) {
            $nTerminalModification = new FHIRString(value: $nTerminalModification);
        }
        $this->nTerminalModification = $nTerminalModification;
        return $this;
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Unique identifier for molecular fragment modification based on the ISO 11238
     * Substance ID.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier
     */
    public function getCTerminalModificationId(): null|FHIRIdentifier
    {
        return $this->cTerminalModificationId ?? null;
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Unique identifier for molecular fragment modification based on the ISO 11238
     * Substance ID.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier $cTerminalModificationId
     * @return static
     */
    public function setCTerminalModificationId(null|FHIRIdentifier $cTerminalModificationId): self
    {
        if (null === $cTerminalModificationId) {
            unset($this->cTerminalModificationId);
            return $this;
        }
        $this->cTerminalModificationId = $cTerminalModificationId;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The modification at the C-terminal shall be specified.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getCTerminalModification(): null|FHIRString
    {
        return $this->cTerminalModification ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The modification at the C-terminal shall be specified.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $cTerminalModification
     * @return static
     */
    public function setCTerminalModification(null|string|FHIRStringPrimitive|FHIRString $cTerminalModification): self
    {
        if (null === $cTerminalModification) {
            unset($this->cTerminalModification);
            return $this;
        }
        if (!($cTerminalModification instanceof FHIRString)) {
            $cTerminalModification = new FHIRString(value: $cTerminalModification);
        }
        $this->cTerminalModification = $cTerminalModification;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceProtein\FHIRSubstanceProteinSubunit $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceProtein\FHIRSubstanceProteinSubunit
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRSubstanceProteinSubunit)) {
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
            } else if (self::FIELD_SUBUNIT === $cen) {
                $type->setSubunit(FHIRInteger::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SEQUENCE === $cen) {
                $type->setSequence(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LENGTH === $cen) {
                $type->setLength(FHIRInteger::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SEQUENCE_ATTACHMENT === $cen) {
                $type->setSequenceAttachment(FHIRAttachment::xmlUnserialize($ce, $config));
            } else if (self::FIELD_N_TERMINAL_MODIFICATION_ID === $cen) {
                $type->setNTerminalModificationId(FHIRIdentifier::xmlUnserialize($ce, $config));
            } else if (self::FIELD_N_TERMINAL_MODIFICATION === $cen) {
                $type->setNTerminalModification(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_C_TERMINAL_MODIFICATION_ID === $cen) {
                $type->setCTerminalModificationId(FHIRIdentifier::xmlUnserialize($ce, $config));
            } else if (self::FIELD_C_TERMINAL_MODIFICATION === $cen) {
                $type->setCTerminalModification(FHIRString::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_SUBUNIT])) {
            if (isset($type->subunit)) {
                $type->subunit->setValue((string)$attributes[self::FIELD_SUBUNIT]);
            } else {
                $type->setSubunit((string)$attributes[self::FIELD_SUBUNIT]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_SUBUNIT, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_SEQUENCE])) {
            if (isset($type->sequence)) {
                $type->sequence->setValue((string)$attributes[self::FIELD_SEQUENCE]);
            } else {
                $type->setSequence((string)$attributes[self::FIELD_SEQUENCE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_SEQUENCE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_LENGTH])) {
            if (isset($type->length)) {
                $type->length->setValue((string)$attributes[self::FIELD_LENGTH]);
            } else {
                $type->setLength((string)$attributes[self::FIELD_LENGTH]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_LENGTH, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_N_TERMINAL_MODIFICATION])) {
            if (isset($type->nTerminalModification)) {
                $type->nTerminalModification->setValue((string)$attributes[self::FIELD_N_TERMINAL_MODIFICATION]);
            } else {
                $type->setNTerminalModification((string)$attributes[self::FIELD_N_TERMINAL_MODIFICATION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_N_TERMINAL_MODIFICATION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_C_TERMINAL_MODIFICATION])) {
            if (isset($type->cTerminalModification)) {
                $type->cTerminalModification->setValue((string)$attributes[self::FIELD_C_TERMINAL_MODIFICATION]);
            } else {
                $type->setCTerminalModification((string)$attributes[self::FIELD_C_TERMINAL_MODIFICATION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_C_TERMINAL_MODIFICATION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->subunit) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_SUBUNIT]) {
            $xw->writeAttribute(self::FIELD_SUBUNIT, $this->subunit->_getValueAsString());
        }
        if (isset($this->sequence) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_SEQUENCE]) {
            $xw->writeAttribute(self::FIELD_SEQUENCE, $this->sequence->_getValueAsString());
        }
        if (isset($this->length) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_LENGTH]) {
            $xw->writeAttribute(self::FIELD_LENGTH, $this->length->_getValueAsString());
        }
        if (isset($this->nTerminalModification) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_N_TERMINAL_MODIFICATION]) {
            $xw->writeAttribute(self::FIELD_N_TERMINAL_MODIFICATION, $this->nTerminalModification->_getValueAsString());
        }
        if (isset($this->cTerminalModification) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_C_TERMINAL_MODIFICATION]) {
            $xw->writeAttribute(self::FIELD_C_TERMINAL_MODIFICATION, $this->cTerminalModification->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->subunit)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_SUBUNIT]
                || $this->subunit->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_SUBUNIT);
            $this->subunit->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_SUBUNIT]);
            $xw->endElement();
        }
        if (isset($this->sequence)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_SEQUENCE]
                || $this->sequence->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_SEQUENCE);
            $this->sequence->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_SEQUENCE]);
            $xw->endElement();
        }
        if (isset($this->length)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_LENGTH]
                || $this->length->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_LENGTH);
            $this->length->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_LENGTH]);
            $xw->endElement();
        }
        if (isset($this->sequenceAttachment)) {
            $xw->startElement(self::FIELD_SEQUENCE_ATTACHMENT);
            $this->sequenceAttachment->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->nTerminalModificationId)) {
            $xw->startElement(self::FIELD_N_TERMINAL_MODIFICATION_ID);
            $this->nTerminalModificationId->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->nTerminalModification)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_N_TERMINAL_MODIFICATION]
                || $this->nTerminalModification->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_N_TERMINAL_MODIFICATION);
            $this->nTerminalModification->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_N_TERMINAL_MODIFICATION]);
            $xw->endElement();
        }
        if (isset($this->cTerminalModificationId)) {
            $xw->startElement(self::FIELD_C_TERMINAL_MODIFICATION_ID);
            $this->cTerminalModificationId->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->cTerminalModification)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_C_TERMINAL_MODIFICATION]
                || $this->cTerminalModification->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_C_TERMINAL_MODIFICATION);
            $this->cTerminalModification->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_C_TERMINAL_MODIFICATION]);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceProtein\FHIRSubstanceProteinSubunit $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceProtein\FHIRSubstanceProteinSubunit
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
        } else if (!($type instanceof FHIRSubstanceProteinSubunit)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->subunit)
            || isset($decoded->_subunit)
            || property_exists($decoded, self::FIELD_SUBUNIT)
            || property_exists($decoded, self::FIELD_SUBUNIT_EXT)) {
            $v = $decoded->_subunit ?? new \stdClass();
            $v->value = $decoded->subunit ?? null;
            $type->setSubunit(FHIRInteger::jsonUnserialize($v, $config));
        }
        if (isset($decoded->sequence)
            || isset($decoded->_sequence)
            || property_exists($decoded, self::FIELD_SEQUENCE)
            || property_exists($decoded, self::FIELD_SEQUENCE_EXT)) {
            $v = $decoded->_sequence ?? new \stdClass();
            $v->value = $decoded->sequence ?? null;
            $type->setSequence(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->length)
            || isset($decoded->_length)
            || property_exists($decoded, self::FIELD_LENGTH)
            || property_exists($decoded, self::FIELD_LENGTH_EXT)) {
            $v = $decoded->_length ?? new \stdClass();
            $v->value = $decoded->length ?? null;
            $type->setLength(FHIRInteger::jsonUnserialize($v, $config));
        }
        if (isset($decoded->sequenceAttachment) || property_exists($decoded, self::FIELD_SEQUENCE_ATTACHMENT)) {
            if (is_array($decoded->sequenceAttachment)) {
                $type->setSequenceAttachment(FHIRAttachment::jsonUnserialize(reset($decoded->sequenceAttachment), $config));
            } else {
                $type->setSequenceAttachment(FHIRAttachment::jsonUnserialize($decoded->sequenceAttachment, $config));
            }
        }
        if (isset($decoded->nTerminalModificationId) || property_exists($decoded, self::FIELD_N_TERMINAL_MODIFICATION_ID)) {
            if (is_array($decoded->nTerminalModificationId)) {
                $type->setNTerminalModificationId(FHIRIdentifier::jsonUnserialize(reset($decoded->nTerminalModificationId), $config));
            } else {
                $type->setNTerminalModificationId(FHIRIdentifier::jsonUnserialize($decoded->nTerminalModificationId, $config));
            }
        }
        if (isset($decoded->nTerminalModification)
            || isset($decoded->_nTerminalModification)
            || property_exists($decoded, self::FIELD_N_TERMINAL_MODIFICATION)
            || property_exists($decoded, self::FIELD_N_TERMINAL_MODIFICATION_EXT)) {
            $v = $decoded->_nTerminalModification ?? new \stdClass();
            $v->value = $decoded->nTerminalModification ?? null;
            $type->setNTerminalModification(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->cTerminalModificationId) || property_exists($decoded, self::FIELD_C_TERMINAL_MODIFICATION_ID)) {
            if (is_array($decoded->cTerminalModificationId)) {
                $type->setCTerminalModificationId(FHIRIdentifier::jsonUnserialize(reset($decoded->cTerminalModificationId), $config));
            } else {
                $type->setCTerminalModificationId(FHIRIdentifier::jsonUnserialize($decoded->cTerminalModificationId, $config));
            }
        }
        if (isset($decoded->cTerminalModification)
            || isset($decoded->_cTerminalModification)
            || property_exists($decoded, self::FIELD_C_TERMINAL_MODIFICATION)
            || property_exists($decoded, self::FIELD_C_TERMINAL_MODIFICATION_EXT)) {
            $v = $decoded->_cTerminalModification ?? new \stdClass();
            $v->value = $decoded->cTerminalModification ?? null;
            $type->setCTerminalModification(FHIRString::jsonUnserialize($v, $config));
        }
        return $type;
    }

    /**
     * @return \stdClass
     */
    public function jsonSerialize(): mixed
    {
        $out = parent::jsonSerialize();
        if (isset($this->subunit)) {
            if (null !== ($val = $this->subunit->getValue())) {
                $out->subunit = $val;
            }
            if ($this->subunit->_nonValueFieldDefined()) {
                $ext = $this->subunit->jsonSerialize();
                unset($ext->value);
                $out->_subunit = $ext;
            }
        }
        if (isset($this->sequence)) {
            if (null !== ($val = $this->sequence->getValue())) {
                $out->sequence = $val;
            }
            if ($this->sequence->_nonValueFieldDefined()) {
                $ext = $this->sequence->jsonSerialize();
                unset($ext->value);
                $out->_sequence = $ext;
            }
        }
        if (isset($this->length)) {
            if (null !== ($val = $this->length->getValue())) {
                $out->length = $val;
            }
            if ($this->length->_nonValueFieldDefined()) {
                $ext = $this->length->jsonSerialize();
                unset($ext->value);
                $out->_length = $ext;
            }
        }
        if (isset($this->sequenceAttachment)) {
            $out->sequenceAttachment = $this->sequenceAttachment;
        }
        if (isset($this->nTerminalModificationId)) {
            $out->nTerminalModificationId = $this->nTerminalModificationId;
        }
        if (isset($this->nTerminalModification)) {
            if (null !== ($val = $this->nTerminalModification->getValue())) {
                $out->nTerminalModification = $val;
            }
            if ($this->nTerminalModification->_nonValueFieldDefined()) {
                $ext = $this->nTerminalModification->jsonSerialize();
                unset($ext->value);
                $out->_nTerminalModification = $ext;
            }
        }
        if (isset($this->cTerminalModificationId)) {
            $out->cTerminalModificationId = $this->cTerminalModificationId;
        }
        if (isset($this->cTerminalModification)) {
            if (null !== ($val = $this->cTerminalModification->getValue())) {
                $out->cTerminalModification = $val;
            }
            if ($this->cTerminalModification->_nonValueFieldDefined()) {
                $ext = $this->cTerminalModification->jsonSerialize();
                unset($ext->value);
                $out->_cTerminalModification = $ext;
            }
        }
        return $out;
    }
}
