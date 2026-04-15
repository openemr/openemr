<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceNucleicAcid;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * Nucleic acids are defined by three distinct elements: the base, sugar and
 * linkage. Individual substance/moiety IDs will be created for each of these
 * elements. The nucleotide sequence will be always entered in the 5’-3’
 * direction.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRSubstanceNucleicAcidSubunit extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_SUBSTANCE_NUCLEIC_ACID_DOT_SUBUNIT;

    /* class_default.php:56 */
    public const FIELD_SUBUNIT = 'subunit';
    public const FIELD_SUBUNIT_EXT = '_subunit';
    public const FIELD_SEQUENCE = 'sequence';
    public const FIELD_SEQUENCE_EXT = '_sequence';
    public const FIELD_LENGTH = 'length';
    public const FIELD_LENGTH_EXT = '_length';
    public const FIELD_SEQUENCE_ATTACHMENT = 'sequenceAttachment';
    public const FIELD_FIVE_PRIME = 'fivePrime';
    public const FIELD_THREE_PRIME = 'threePrime';
    public const FIELD_LINKAGE = 'linkage';
    public const FIELD_SUGAR = 'sugar';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_SUBUNIT => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_SEQUENCE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_LENGTH => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Index of linear sequences of nucleic acids in order of decreasing length.
     * Sequences of the same length will be ordered by molecular weight. Subunits that
     * have identical sequences will be repeated and have sequential subscripts.
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
     * Actual nucleotide sequence notation from 5' to 3' end using standard single
     * letter codes. In addition to the base sequence, sugar and type of phosphate or
     * non-phosphate linkage should also be captured.
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
     * The length of the sequence shall be captured.
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
     * (TBC).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment
     */
    #[FHIRAttachment]
    protected FHIRAttachment $sequenceAttachment;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The nucleotide present at the 5’ terminal shall be specified based on a
     * controlled vocabulary. Since the sequence is represented from the 5' to the 3'
     * end, the 5’ prime nucleotide is the letter at the first position in the
     * sequence. A separate representation would be redundant.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $fivePrime;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The nucleotide present at the 3’ terminal shall be specified based on a
     * controlled vocabulary. Since the sequence is represented from the 5' to the 3'
     * end, the 5’ prime nucleotide is the letter at the last position in the
     * sequence. A separate representation would be redundant.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $threePrime;
    /**
     * Nucleic acids are defined by three distinct elements: the base, sugar and
     * linkage. Individual substance/moiety IDs will be created for each of these
     * elements. The nucleotide sequence will be always entered in the 5’-3’
     * direction.
     *
     * The linkages between sugar residues will also be captured.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceNucleicAcid\FHIRSubstanceNucleicAcidLinkage>
     */
    #[FHIRSubstanceNucleicAcidLinkage]
    protected array $linkage;
    /**
     * Nucleic acids are defined by three distinct elements: the base, sugar and
     * linkage. Individual substance/moiety IDs will be created for each of these
     * elements. The nucleotide sequence will be always entered in the 5’-3’
     * direction.
     *
     * 5.3.6.8.1 Sugar ID (Mandatory).
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceNucleicAcid\FHIRSubstanceNucleicAcidSugar>
     */
    #[FHIRSubstanceNucleicAcidSugar]
    protected array $sugar;

    /* constructor.php:61 */
    /**
     * FHIRSubstanceNucleicAcidSubunit Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger $subunit
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $sequence
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger $length
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment $sequenceAttachment
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $fivePrime
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $threePrime
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceNucleicAcid\FHIRSubstanceNucleicAcidLinkage> $linkage
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceNucleicAcid\FHIRSubstanceNucleicAcidSugar> $sugar
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|string|float|FHIRIntegerPrimitive|FHIRInteger $subunit = null,
                                null|string|FHIRStringPrimitive|FHIRString $sequence = null,
                                null|string|float|FHIRIntegerPrimitive|FHIRInteger $length = null,
                                null|FHIRAttachment $sequenceAttachment = null,
                                null|FHIRCodeableConcept $fivePrime = null,
                                null|FHIRCodeableConcept $threePrime = null,
                                null|iterable $linkage = null,
                                null|iterable $sugar = null,
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
        if (null !== $fivePrime) {
            $this->setFivePrime($fivePrime);
        }
        if (null !== $threePrime) {
            $this->setThreePrime($threePrime);
        }
        if (null !== $linkage) {
            $this->setLinkage(...$linkage);
        }
        if (null !== $sugar) {
            $this->setSugar(...$sugar);
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
     * Index of linear sequences of nucleic acids in order of decreasing length.
     * Sequences of the same length will be ordered by molecular weight. Subunits that
     * have identical sequences will be repeated and have sequential subscripts.
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
     * Index of linear sequences of nucleic acids in order of decreasing length.
     * Sequences of the same length will be ordered by molecular weight. Subunits that
     * have identical sequences will be repeated and have sequential subscripts.
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
     * Actual nucleotide sequence notation from 5' to 3' end using standard single
     * letter codes. In addition to the base sequence, sugar and type of phosphate or
     * non-phosphate linkage should also be captured.
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
     * Actual nucleotide sequence notation from 5' to 3' end using standard single
     * letter codes. In addition to the base sequence, sugar and type of phosphate or
     * non-phosphate linkage should also be captured.
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
     * The length of the sequence shall be captured.
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
     * The length of the sequence shall be captured.
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
     * (TBC).
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
     * (TBC).
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
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The nucleotide present at the 5’ terminal shall be specified based on a
     * controlled vocabulary. Since the sequence is represented from the 5' to the 3'
     * end, the 5’ prime nucleotide is the letter at the first position in the
     * sequence. A separate representation would be redundant.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getFivePrime(): null|FHIRCodeableConcept
    {
        return $this->fivePrime ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The nucleotide present at the 5’ terminal shall be specified based on a
     * controlled vocabulary. Since the sequence is represented from the 5' to the 3'
     * end, the 5’ prime nucleotide is the letter at the first position in the
     * sequence. A separate representation would be redundant.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $fivePrime
     * @return static
     */
    public function setFivePrime(null|FHIRCodeableConcept $fivePrime): self
    {
        if (null === $fivePrime) {
            unset($this->fivePrime);
            return $this;
        }
        $this->fivePrime = $fivePrime;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The nucleotide present at the 3’ terminal shall be specified based on a
     * controlled vocabulary. Since the sequence is represented from the 5' to the 3'
     * end, the 5’ prime nucleotide is the letter at the last position in the
     * sequence. A separate representation would be redundant.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getThreePrime(): null|FHIRCodeableConcept
    {
        return $this->threePrime ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The nucleotide present at the 3’ terminal shall be specified based on a
     * controlled vocabulary. Since the sequence is represented from the 5' to the 3'
     * end, the 5’ prime nucleotide is the letter at the last position in the
     * sequence. A separate representation would be redundant.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $threePrime
     * @return static
     */
    public function setThreePrime(null|FHIRCodeableConcept $threePrime): self
    {
        if (null === $threePrime) {
            unset($this->threePrime);
            return $this;
        }
        $this->threePrime = $threePrime;
        return $this;
    }

    /**
     * Nucleic acids are defined by three distinct elements: the base, sugar and
     * linkage. Individual substance/moiety IDs will be created for each of these
     * elements. The nucleotide sequence will be always entered in the 5’-3’
     * direction.
     *
     * The linkages between sugar residues will also be captured.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceNucleicAcid\FHIRSubstanceNucleicAcidLinkage>
     */
    public function getLinkage(): array
    {
        return $this->linkage ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceNucleicAcid\FHIRSubstanceNucleicAcidLinkage>
     */
    public function getLinkageIterator(): iterable
    {
        if (!isset($this->linkage)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->linkage);
    }

    /**
     * Nucleic acids are defined by three distinct elements: the base, sugar and
     * linkage. Individual substance/moiety IDs will be created for each of these
     * elements. The nucleotide sequence will be always entered in the 5’-3’
     * direction.
     *
     * The linkages between sugar residues will also be captured.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceNucleicAcid\FHIRSubstanceNucleicAcidLinkage $linkage
     * @return static
     */
    public function addLinkage(FHIRSubstanceNucleicAcidLinkage $linkage): self
    {
        if (!isset($this->linkage)) {
            $this->linkage = [];
        }
        $this->linkage[] = $linkage;
        return $this;
    }

    /**
     * Nucleic acids are defined by three distinct elements: the base, sugar and
     * linkage. Individual substance/moiety IDs will be created for each of these
     * elements. The nucleotide sequence will be always entered in the 5’-3’
     * direction.
     *
     * The linkages between sugar residues will also be captured.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceNucleicAcid\FHIRSubstanceNucleicAcidLinkage ...$linkage
     * @return static
     */
    public function setLinkage(FHIRSubstanceNucleicAcidLinkage ...$linkage): self
    {
        if ([] === $linkage) {
            unset($this->linkage);
            return $this;
        }
        $this->linkage = $linkage;
        return $this;
    }

    /**
     * Nucleic acids are defined by three distinct elements: the base, sugar and
     * linkage. Individual substance/moiety IDs will be created for each of these
     * elements. The nucleotide sequence will be always entered in the 5’-3’
     * direction.
     *
     * 5.3.6.8.1 Sugar ID (Mandatory).
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceNucleicAcid\FHIRSubstanceNucleicAcidSugar>
     */
    public function getSugar(): array
    {
        return $this->sugar ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceNucleicAcid\FHIRSubstanceNucleicAcidSugar>
     */
    public function getSugarIterator(): iterable
    {
        if (!isset($this->sugar)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->sugar);
    }

    /**
     * Nucleic acids are defined by three distinct elements: the base, sugar and
     * linkage. Individual substance/moiety IDs will be created for each of these
     * elements. The nucleotide sequence will be always entered in the 5’-3’
     * direction.
     *
     * 5.3.6.8.1 Sugar ID (Mandatory).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceNucleicAcid\FHIRSubstanceNucleicAcidSugar $sugar
     * @return static
     */
    public function addSugar(FHIRSubstanceNucleicAcidSugar $sugar): self
    {
        if (!isset($this->sugar)) {
            $this->sugar = [];
        }
        $this->sugar[] = $sugar;
        return $this;
    }

    /**
     * Nucleic acids are defined by three distinct elements: the base, sugar and
     * linkage. Individual substance/moiety IDs will be created for each of these
     * elements. The nucleotide sequence will be always entered in the 5’-3’
     * direction.
     *
     * 5.3.6.8.1 Sugar ID (Mandatory).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceNucleicAcid\FHIRSubstanceNucleicAcidSugar ...$sugar
     * @return static
     */
    public function setSugar(FHIRSubstanceNucleicAcidSugar ...$sugar): self
    {
        if ([] === $sugar) {
            unset($this->sugar);
            return $this;
        }
        $this->sugar = $sugar;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceNucleicAcid\FHIRSubstanceNucleicAcidSubunit $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceNucleicAcid\FHIRSubstanceNucleicAcidSubunit
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRSubstanceNucleicAcidSubunit)) {
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
            } else if (self::FIELD_FIVE_PRIME === $cen) {
                $type->setFivePrime(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_THREE_PRIME === $cen) {
                $type->setThreePrime(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LINKAGE === $cen) {
                $type->addLinkage(FHIRSubstanceNucleicAcidLinkage::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SUGAR === $cen) {
                $type->addSugar(FHIRSubstanceNucleicAcidSugar::xmlUnserialize($ce, $config));
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
        if (isset($this->fivePrime)) {
            $xw->startElement(self::FIELD_FIVE_PRIME);
            $this->fivePrime->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->threePrime)) {
            $xw->startElement(self::FIELD_THREE_PRIME);
            $this->threePrime->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->linkage)) {
            foreach ($this->linkage as $v) {
                $xw->startElement(self::FIELD_LINKAGE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->sugar)) {
            foreach ($this->sugar as $v) {
                $xw->startElement(self::FIELD_SUGAR);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceNucleicAcid\FHIRSubstanceNucleicAcidSubunit $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceNucleicAcid\FHIRSubstanceNucleicAcidSubunit
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
        } else if (!($type instanceof FHIRSubstanceNucleicAcidSubunit)) {
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
        if (isset($decoded->fivePrime) || property_exists($decoded, self::FIELD_FIVE_PRIME)) {
            if (is_array($decoded->fivePrime)) {
                $type->setFivePrime(FHIRCodeableConcept::jsonUnserialize(reset($decoded->fivePrime), $config));
            } else {
                $type->setFivePrime(FHIRCodeableConcept::jsonUnserialize($decoded->fivePrime, $config));
            }
        }
        if (isset($decoded->threePrime) || property_exists($decoded, self::FIELD_THREE_PRIME)) {
            if (is_array($decoded->threePrime)) {
                $type->setThreePrime(FHIRCodeableConcept::jsonUnserialize(reset($decoded->threePrime), $config));
            } else {
                $type->setThreePrime(FHIRCodeableConcept::jsonUnserialize($decoded->threePrime, $config));
            }
        }
        if (isset($decoded->linkage) || property_exists($decoded, self::FIELD_LINKAGE)) {
            if (is_object($decoded->linkage)) {
                $vals = [$decoded->linkage];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_LINKAGE, true);
            } else {
                $vals = $decoded->linkage;
            }
            foreach($vals as $v) {
                $type->addLinkage(FHIRSubstanceNucleicAcidLinkage::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->sugar) || property_exists($decoded, self::FIELD_SUGAR)) {
            if (is_object($decoded->sugar)) {
                $vals = [$decoded->sugar];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_SUGAR, true);
            } else {
                $vals = $decoded->sugar;
            }
            foreach($vals as $v) {
                $type->addSugar(FHIRSubstanceNucleicAcidSugar::jsonUnserialize($v, $config));
            }
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
        if (isset($this->fivePrime)) {
            $out->fivePrime = $this->fivePrime;
        }
        if (isset($this->threePrime)) {
            $out->threePrime = $this->threePrime;
        }
        if (isset($this->linkage) && [] !== $this->linkage) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_LINKAGE) && 1 === count($this->linkage)) {
                $out->linkage = $this->linkage[0];
            } else {
                $out->linkage = $this->linkage;
            }
        }
        if (isset($this->sugar) && [] !== $this->sugar) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_SUGAR) && 1 === count($this->sugar)) {
                $out->sugar = $this->sugar[0];
            } else {
                $out->sugar = $this->sugar;
            }
        }
        return $out;
    }
}
