<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource;

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
use OpenEMR\FHIR\Types\ResourceTypeInterface;
use OpenEMR\FHIR\Validation\TypeValidationsTrait;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceNucleicAcid\FHIRSubstanceNucleicAcidSubunit;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive;
use OpenEMR\FHIR\Versions\R4\Version;
use OpenEMR\FHIR\Versions\R4\VersionConstants;
use OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface;
use OpenEMR\FHIR\Versions\R4\VersionTypeMap;

/**
 * Nucleic acids are defined by three distinct elements: the base, sugar and
 * linkage. Individual substance/moiety IDs will be created for each of these
 * elements. The nucleotide sequence will be always entered in the 5’-3’
 * direction.
 * If the element is present, it must have either a \@value, an \@id, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRSubstanceNucleicAcid extends FHIRDomainResource implements VersionContainedTypeInterface
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_SUBSTANCE_NUCLEIC_ACID;

    /* class_default.php:56 */
    public const FIELD_SEQUENCE_TYPE = 'sequenceType';
    public const FIELD_NUMBER_OF_SUBUNITS = 'numberOfSubunits';
    public const FIELD_NUMBER_OF_SUBUNITS_EXT = '_numberOfSubunits';
    public const FIELD_AREA_OF_HYBRIDISATION = 'areaOfHybridisation';
    public const FIELD_AREA_OF_HYBRIDISATION_EXT = '_areaOfHybridisation';
    public const FIELD_OLIGO_NUCLEOTIDE_TYPE = 'oligoNucleotideType';
    public const FIELD_SUBUNIT = 'subunit';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_NUMBER_OF_SUBUNITS => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_AREA_OF_HYBRIDISATION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The type of the sequence shall be specified based on a controlled vocabulary.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $sequenceType;
    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The number of linear sequences of nucleotides linked through phosphodiester
     * bonds shall be described. Subunits would be strands of nucleic acids that are
     * tightly associated typically through Watson-Crick base pairing. NOTE: If not
     * specified in the reference source, the assumption is that there is 1 subunit.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger
     */
    #[FHIRInteger]
    protected FHIRInteger $numberOfSubunits;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The area of hybridisation shall be described if applicable for double stranded
     * RNA or DNA. The number associated with the subunit followed by the number
     * associated to the residue shall be specified in increasing order. The underscore
     * “” shall be used as separator as follows: “Subunitnumber Residue”.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $areaOfHybridisation;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * (TBC).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $oligoNucleotideType;
    /**
     * Nucleic acids are defined by three distinct elements: the base, sugar and
     * linkage. Individual substance/moiety IDs will be created for each of these
     * elements. The nucleotide sequence will be always entered in the 5’-3’
     * direction.
     *
     * Subunits are listed in order of decreasing length; sequences of the same length
     * will be ordered by molecular weight; subunits that have identical sequences will
     * be repeated multiple times.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceNucleicAcid\FHIRSubstanceNucleicAcidSubunit>
     */
    #[FHIRSubstanceNucleicAcidSubunit]
    protected array $subunit;

    /* constructor.php:61 */
    /**
     * FHIRSubstanceNucleicAcid Constructor
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $id
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta $meta
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $implicitRules
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $language
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative $text
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRResourceContainer>|iterable<\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface> $contained
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $sequenceType
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger $numberOfSubunits
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $areaOfHybridisation
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $oligoNucleotideType
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceNucleicAcid\FHIRSubstanceNucleicAcidSubunit> $subunit
     * @param null|string[] $fhirComments
     */
    public function __construct(null|string|FHIRIdPrimitive|FHIRId $id = null,
                                null|FHIRMeta $meta = null,
                                null|string|FHIRUriPrimitive|FHIRUri $implicitRules = null,
                                null|string|FHIRCodePrimitive|FHIRCode $language = null,
                                null|FHIRNarrative $text = null,
                                null|iterable $contained = null,
                                null|iterable $extension = null,
                                null|iterable $modifierExtension = null,
                                null|FHIRCodeableConcept $sequenceType = null,
                                null|string|float|FHIRIntegerPrimitive|FHIRInteger $numberOfSubunits = null,
                                null|string|FHIRStringPrimitive|FHIRString $areaOfHybridisation = null,
                                null|FHIRCodeableConcept $oligoNucleotideType = null,
                                null|iterable $subunit = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(id: $id,
                            meta: $meta,
                            implicitRules: $implicitRules,
                            language: $language,
                            text: $text,
                            contained: $contained,
                            extension: $extension,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $sequenceType) {
            $this->setSequenceType($sequenceType);
        }
        if (null !== $numberOfSubunits) {
            $this->setNumberOfSubunits($numberOfSubunits);
        }
        if (null !== $areaOfHybridisation) {
            $this->setAreaOfHybridisation($areaOfHybridisation);
        }
        if (null !== $oligoNucleotideType) {
            $this->setOligoNucleotideType($oligoNucleotideType);
        }
        if (null !== $subunit) {
            $this->setSubunit(...$subunit);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:163 */
    public function _getResourceType(): string
    {
        return static::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The type of the sequence shall be specified based on a controlled vocabulary.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getSequenceType(): null|FHIRCodeableConcept
    {
        return $this->sequenceType ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The type of the sequence shall be specified based on a controlled vocabulary.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $sequenceType
     * @return static
     */
    public function setSequenceType(null|FHIRCodeableConcept $sequenceType): self
    {
        if (null === $sequenceType) {
            unset($this->sequenceType);
            return $this;
        }
        $this->sequenceType = $sequenceType;
        return $this;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The number of linear sequences of nucleotides linked through phosphodiester
     * bonds shall be described. Subunits would be strands of nucleic acids that are
     * tightly associated typically through Watson-Crick base pairing. NOTE: If not
     * specified in the reference source, the assumption is that there is 1 subunit.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger
     */
    public function getNumberOfSubunits(): null|FHIRInteger
    {
        return $this->numberOfSubunits ?? null;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The number of linear sequences of nucleotides linked through phosphodiester
     * bonds shall be described. Subunits would be strands of nucleic acids that are
     * tightly associated typically through Watson-Crick base pairing. NOTE: If not
     * specified in the reference source, the assumption is that there is 1 subunit.
     *
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger $numberOfSubunits
     * @return static
     */
    public function setNumberOfSubunits(null|string|float|FHIRIntegerPrimitive|FHIRInteger $numberOfSubunits): self
    {
        if (null === $numberOfSubunits) {
            unset($this->numberOfSubunits);
            return $this;
        }
        if (!($numberOfSubunits instanceof FHIRInteger)) {
            $numberOfSubunits = new FHIRInteger(value: $numberOfSubunits);
        }
        $this->numberOfSubunits = $numberOfSubunits;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The area of hybridisation shall be described if applicable for double stranded
     * RNA or DNA. The number associated with the subunit followed by the number
     * associated to the residue shall be specified in increasing order. The underscore
     * “” shall be used as separator as follows: “Subunitnumber Residue”.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getAreaOfHybridisation(): null|FHIRString
    {
        return $this->areaOfHybridisation ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The area of hybridisation shall be described if applicable for double stranded
     * RNA or DNA. The number associated with the subunit followed by the number
     * associated to the residue shall be specified in increasing order. The underscore
     * “” shall be used as separator as follows: “Subunitnumber Residue”.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $areaOfHybridisation
     * @return static
     */
    public function setAreaOfHybridisation(null|string|FHIRStringPrimitive|FHIRString $areaOfHybridisation): self
    {
        if (null === $areaOfHybridisation) {
            unset($this->areaOfHybridisation);
            return $this;
        }
        if (!($areaOfHybridisation instanceof FHIRString)) {
            $areaOfHybridisation = new FHIRString(value: $areaOfHybridisation);
        }
        $this->areaOfHybridisation = $areaOfHybridisation;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * (TBC).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getOligoNucleotideType(): null|FHIRCodeableConcept
    {
        return $this->oligoNucleotideType ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * (TBC).
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $oligoNucleotideType
     * @return static
     */
    public function setOligoNucleotideType(null|FHIRCodeableConcept $oligoNucleotideType): self
    {
        if (null === $oligoNucleotideType) {
            unset($this->oligoNucleotideType);
            return $this;
        }
        $this->oligoNucleotideType = $oligoNucleotideType;
        return $this;
    }

    /**
     * Nucleic acids are defined by three distinct elements: the base, sugar and
     * linkage. Individual substance/moiety IDs will be created for each of these
     * elements. The nucleotide sequence will be always entered in the 5’-3’
     * direction.
     *
     * Subunits are listed in order of decreasing length; sequences of the same length
     * will be ordered by molecular weight; subunits that have identical sequences will
     * be repeated multiple times.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceNucleicAcid\FHIRSubstanceNucleicAcidSubunit>
     */
    public function getSubunit(): array
    {
        return $this->subunit ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceNucleicAcid\FHIRSubstanceNucleicAcidSubunit>
     */
    public function getSubunitIterator(): iterable
    {
        if (!isset($this->subunit)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->subunit);
    }

    /**
     * Nucleic acids are defined by three distinct elements: the base, sugar and
     * linkage. Individual substance/moiety IDs will be created for each of these
     * elements. The nucleotide sequence will be always entered in the 5’-3’
     * direction.
     *
     * Subunits are listed in order of decreasing length; sequences of the same length
     * will be ordered by molecular weight; subunits that have identical sequences will
     * be repeated multiple times.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceNucleicAcid\FHIRSubstanceNucleicAcidSubunit $subunit
     * @return static
     */
    public function addSubunit(FHIRSubstanceNucleicAcidSubunit $subunit): self
    {
        if (!isset($this->subunit)) {
            $this->subunit = [];
        }
        $this->subunit[] = $subunit;
        return $this;
    }

    /**
     * Nucleic acids are defined by three distinct elements: the base, sugar and
     * linkage. Individual substance/moiety IDs will be created for each of these
     * elements. The nucleotide sequence will be always entered in the 5’-3’
     * direction.
     *
     * Subunits are listed in order of decreasing length; sequences of the same length
     * will be ordered by molecular weight; subunits that have identical sequences will
     * be repeated multiple times.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceNucleicAcid\FHIRSubstanceNucleicAcidSubunit ...$subunit
     * @return static
     */
    public function setSubunit(FHIRSubstanceNucleicAcidSubunit ...$subunit): self
    {
        if ([] === $subunit) {
            unset($this->subunit);
            return $this;
        }
        $this->subunit = $subunit;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param string|\SimpleXMLElement $element
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSubstanceNucleicAcid $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSubstanceNucleicAcid
     * @throws \Exception
     */
    public static function xmlUnserialize(string|\SimpleXMLElement $element,
                                          null|UnserializeConfig $config = null,
                                          null|ResourceTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRSubstanceNucleicAcid)) {
            throw new \RuntimeException(sprintf(
                '%s::xmlUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        if (null === $config) {
            $config = (new Version())->getConfig()->getUnserializeConfig();
        }
        if (is_string($element)) {
            $element = new \SimpleXMLElement($element, $config->getLibxmlOpts());
        }
        if (null !== ($ns = $element->getNamespaces()[''] ?? null)) {
            $type->_setSourceXMLNS((string)$ns);
        }
        foreach ($element->children() as $ce) {
            $cen = $ce->getName();
            if (self::FIELD_ID === $cen) {
                $type->setId(FHIRId::xmlUnserialize($ce, $config));
            } else if (self::FIELD_META === $cen) {
                $type->setMeta(FHIRMeta::xmlUnserialize($ce, $config));
            } else if (self::FIELD_IMPLICIT_RULES === $cen) {
                $type->setImplicitRules(FHIRUri::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LANGUAGE === $cen) {
                $type->setLanguage(FHIRCode::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TEXT === $cen) {
                $type->setText(FHIRNarrative::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CONTAINED === $cen) {
                foreach ($ce->children() as $cen) {
                    /** @var \OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface $cn */
                    $cn = VersionTypeMap::mustGetContainedTypeClassnameFromXML($cen);
                    $type->addContained($cn::xmlUnserialize($cen, $config));
                }
            } else if (self::FIELD_EXTENSION === $cen) {
                $type->addExtension(FHIRExtension::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MODIFIER_EXTENSION === $cen) {
                $type->addModifierExtension(FHIRExtension::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SEQUENCE_TYPE === $cen) {
                $type->setSequenceType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_NUMBER_OF_SUBUNITS === $cen) {
                $type->setNumberOfSubunits(FHIRInteger::xmlUnserialize($ce, $config));
            } else if (self::FIELD_AREA_OF_HYBRIDISATION === $cen) {
                $type->setAreaOfHybridisation(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_OLIGO_NUCLEOTIDE_TYPE === $cen) {
                $type->setOligoNucleotideType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SUBUNIT === $cen) {
                $type->addSubunit(FHIRSubstanceNucleicAcidSubunit::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            if (isset($type->id)) {
                $type->id->setValue((string)$attributes[self::FIELD_ID]);
            } else {
                $type->setId((string)$attributes[self::FIELD_ID]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_IMPLICIT_RULES])) {
            if (isset($type->implicitRules)) {
                $type->implicitRules->setValue((string)$attributes[self::FIELD_IMPLICIT_RULES]);
            } else {
                $type->setImplicitRules((string)$attributes[self::FIELD_IMPLICIT_RULES]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_IMPLICIT_RULES, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_LANGUAGE])) {
            if (isset($type->language)) {
                $type->language->setValue((string)$attributes[self::FIELD_LANGUAGE]);
            } else {
                $type->setLanguage((string)$attributes[self::FIELD_LANGUAGE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_LANGUAGE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_NUMBER_OF_SUBUNITS])) {
            if (isset($type->numberOfSubunits)) {
                $type->numberOfSubunits->setValue((string)$attributes[self::FIELD_NUMBER_OF_SUBUNITS]);
            } else {
                $type->setNumberOfSubunits((string)$attributes[self::FIELD_NUMBER_OF_SUBUNITS]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_NUMBER_OF_SUBUNITS, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_AREA_OF_HYBRIDISATION])) {
            if (isset($type->areaOfHybridisation)) {
                $type->areaOfHybridisation->setValue((string)$attributes[self::FIELD_AREA_OF_HYBRIDISATION]);
            } else {
                $type->setAreaOfHybridisation((string)$attributes[self::FIELD_AREA_OF_HYBRIDISATION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_AREA_OF_HYBRIDISATION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        return $type;
    }

    /**
     * @param null|\OpenEMR\FHIR\Encoding\XMLWriter $xw
     * @param null|\OpenEMR\FHIR\Encoding\SerializeConfig $config
     * @return \OpenEMR\FHIR\Encoding\XMLWriter
     */
    public function xmlSerialize(null|XMLWriter $xw = null,
                                 null|SerializeConfig $config = null): XMLWriter
    {
        if (null === $config) {
            $config = (new Version())->getConfig()->getSerializeConfig();
        }
        if (null === $xw) {
            $xw = new XMLWriter($config);
        }
        if (!$xw->isOpen()) {
            $xw->openMemory();
        }
        if (!$xw->isDocStarted()) {
            $docStarted = true;
            $xw->startDocument();
        }
        if (!$xw->isRootOpen()) {
            $rootOpened = true;
            $xw->openRootNode('SubstanceNucleicAcid', $this->_getSourceXMLNS());
        }
        if (isset($this->numberOfSubunits) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_NUMBER_OF_SUBUNITS]) {
            $xw->writeAttribute(self::FIELD_NUMBER_OF_SUBUNITS, $this->numberOfSubunits->_getValueAsString());
        }
        if (isset($this->areaOfHybridisation) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_AREA_OF_HYBRIDISATION]) {
            $xw->writeAttribute(self::FIELD_AREA_OF_HYBRIDISATION, $this->areaOfHybridisation->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->sequenceType)) {
            $xw->startElement(self::FIELD_SEQUENCE_TYPE);
            $this->sequenceType->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->numberOfSubunits)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_NUMBER_OF_SUBUNITS]
                || $this->numberOfSubunits->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_NUMBER_OF_SUBUNITS);
            $this->numberOfSubunits->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_NUMBER_OF_SUBUNITS]);
            $xw->endElement();
        }
        if (isset($this->areaOfHybridisation)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_AREA_OF_HYBRIDISATION]
                || $this->areaOfHybridisation->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_AREA_OF_HYBRIDISATION);
            $this->areaOfHybridisation->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_AREA_OF_HYBRIDISATION]);
            $xw->endElement();
        }
        if (isset($this->oligoNucleotideType)) {
            $xw->startElement(self::FIELD_OLIGO_NUCLEOTIDE_TYPE);
            $this->oligoNucleotideType->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->subunit)) {
            foreach ($this->subunit as $v) {
                $xw->startElement(self::FIELD_SUBUNIT);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if ($rootOpened ?? false) {
            $xw->endElement();
        }
        if ($docStarted ?? false) {
            $xw->endDocument();
        }
        return $xw;
    }

    /**
     * @param string|\stdClass $decoded
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSubstanceNucleicAcid $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSubstanceNucleicAcid
     * @throws \Exception
     */
    public static function jsonUnserialize(string|\stdClass $decoded,
                                           null|UnserializeConfig $config = null,
                                           null|ResourceTypeInterface $type = null): self
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
        } else if (!($type instanceof FHIRSubstanceNucleicAcid)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        if (null === $config) {
            $config = (new Version())->getConfig()->getUnserializeConfig();
        }
        if (is_string($decoded)) {
            $decoded = json_decode(json: $decoded,
                                associative: false,
                                depth: $config->getJSONDecodeMaxDepth(),
                                flags: $config->getJSONDecodeOpts());
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->sequenceType) || property_exists($decoded, self::FIELD_SEQUENCE_TYPE)) {
            if (is_array($decoded->sequenceType)) {
                $type->setSequenceType(FHIRCodeableConcept::jsonUnserialize(reset($decoded->sequenceType), $config));
            } else {
                $type->setSequenceType(FHIRCodeableConcept::jsonUnserialize($decoded->sequenceType, $config));
            }
        }
        if (isset($decoded->numberOfSubunits)
            || isset($decoded->_numberOfSubunits)
            || property_exists($decoded, self::FIELD_NUMBER_OF_SUBUNITS)
            || property_exists($decoded, self::FIELD_NUMBER_OF_SUBUNITS_EXT)) {
            $v = $decoded->_numberOfSubunits ?? new \stdClass();
            $v->value = $decoded->numberOfSubunits ?? null;
            $type->setNumberOfSubunits(FHIRInteger::jsonUnserialize($v, $config));
        }
        if (isset($decoded->areaOfHybridisation)
            || isset($decoded->_areaOfHybridisation)
            || property_exists($decoded, self::FIELD_AREA_OF_HYBRIDISATION)
            || property_exists($decoded, self::FIELD_AREA_OF_HYBRIDISATION_EXT)) {
            $v = $decoded->_areaOfHybridisation ?? new \stdClass();
            $v->value = $decoded->areaOfHybridisation ?? null;
            $type->setAreaOfHybridisation(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->oligoNucleotideType) || property_exists($decoded, self::FIELD_OLIGO_NUCLEOTIDE_TYPE)) {
            if (is_array($decoded->oligoNucleotideType)) {
                $type->setOligoNucleotideType(FHIRCodeableConcept::jsonUnserialize(reset($decoded->oligoNucleotideType), $config));
            } else {
                $type->setOligoNucleotideType(FHIRCodeableConcept::jsonUnserialize($decoded->oligoNucleotideType, $config));
            }
        }
        if (isset($decoded->subunit) || property_exists($decoded, self::FIELD_SUBUNIT)) {
            if (is_object($decoded->subunit)) {
                $vals = [$decoded->subunit];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_SUBUNIT, true);
            } else {
                $vals = $decoded->subunit;
            }
            foreach($vals as $v) {
                $type->addSubunit(FHIRSubstanceNucleicAcidSubunit::jsonUnserialize($v, $config));
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
        if (isset($this->sequenceType)) {
            $out->sequenceType = $this->sequenceType;
        }
        if (isset($this->numberOfSubunits)) {
            if (null !== ($val = $this->numberOfSubunits->getValue())) {
                $out->numberOfSubunits = $val;
            }
            if ($this->numberOfSubunits->_nonValueFieldDefined()) {
                $ext = $this->numberOfSubunits->jsonSerialize();
                unset($ext->value);
                $out->_numberOfSubunits = $ext;
            }
        }
        if (isset($this->areaOfHybridisation)) {
            if (null !== ($val = $this->areaOfHybridisation->getValue())) {
                $out->areaOfHybridisation = $val;
            }
            if ($this->areaOfHybridisation->_nonValueFieldDefined()) {
                $ext = $this->areaOfHybridisation->jsonSerialize();
                unset($ext->value);
                $out->_areaOfHybridisation = $ext;
            }
        }
        if (isset($this->oligoNucleotideType)) {
            $out->oligoNucleotideType = $this->oligoNucleotideType;
        }
        if (isset($this->subunit) && [] !== $this->subunit) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_SUBUNIT) && 1 === count($this->subunit)) {
                $out->subunit = $this->subunit[0];
            } else {
                $out->subunit = $this->subunit;
            }
        }
        $out->resourceType = $this->_getResourceType();
        return $out;
    }
}
