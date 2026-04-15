<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * Source material shall capture information on the taxonomic and anatomical
 * origins as well as the fraction of a material that can result in or can be
 * modified to form a substance. This set of data elements shall be used to define
 * polymer substances isolated from biological matrices. Taxonomic and anatomical
 * origins shall be described using a controlled vocabulary as required. This
 * information is captured for naturally derived polymers ( . starch) and
 * structurally diverse substances. For Organisms belonging to the Kingdom Plantae
 * the Substance level defines the fresh material of a single species or
 * infraspecies, the Herbal Drug and the Herbal preparation. For Herbal
 * preparations, the fraction information will be captured at the Substance
 * information level and additional information for herbal extracts will be
 * captured at the Specified Substance Group 1 information level. See for further
 * explanation the Substance Class: Structurally Diverse and the herbal annex.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRSubstanceSourceMaterialHybrid extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_SUBSTANCE_SOURCE_MATERIAL_DOT_HYBRID;

    /* class_default.php:56 */
    public const FIELD_MATERNAL_ORGANISM_ID = 'maternalOrganismId';
    public const FIELD_MATERNAL_ORGANISM_ID_EXT = '_maternalOrganismId';
    public const FIELD_MATERNAL_ORGANISM_NAME = 'maternalOrganismName';
    public const FIELD_MATERNAL_ORGANISM_NAME_EXT = '_maternalOrganismName';
    public const FIELD_PATERNAL_ORGANISM_ID = 'paternalOrganismId';
    public const FIELD_PATERNAL_ORGANISM_ID_EXT = '_paternalOrganismId';
    public const FIELD_PATERNAL_ORGANISM_NAME = 'paternalOrganismName';
    public const FIELD_PATERNAL_ORGANISM_NAME_EXT = '_paternalOrganismName';
    public const FIELD_HYBRID_TYPE = 'hybridType';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_MATERNAL_ORGANISM_ID => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_MATERNAL_ORGANISM_NAME => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_PATERNAL_ORGANISM_ID => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_PATERNAL_ORGANISM_NAME => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The identifier of the maternal species constituting the hybrid organism shall be
     * specified based on a controlled vocabulary. For plants, the parents aren’t
     * always known, and it is unlikely that it will be known which is maternal and
     * which is paternal.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $maternalOrganismId;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The name of the maternal species constituting the hybrid organism shall be
     * specified. For plants, the parents aren’t always known, and it is unlikely
     * that it will be known which is maternal and which is paternal.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $maternalOrganismName;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The identifier of the paternal species constituting the hybrid organism shall be
     * specified based on a controlled vocabulary.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $paternalOrganismId;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The name of the paternal species constituting the hybrid organism shall be
     * specified.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $paternalOrganismName;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The hybrid type of an organism shall be specified.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $hybridType;

    /* constructor.php:61 */
    /**
     * FHIRSubstanceSourceMaterialHybrid Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $maternalOrganismId
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $maternalOrganismName
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $paternalOrganismId
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $paternalOrganismName
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $hybridType
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|string|FHIRStringPrimitive|FHIRString $maternalOrganismId = null,
                                null|string|FHIRStringPrimitive|FHIRString $maternalOrganismName = null,
                                null|string|FHIRStringPrimitive|FHIRString $paternalOrganismId = null,
                                null|string|FHIRStringPrimitive|FHIRString $paternalOrganismName = null,
                                null|FHIRCodeableConcept $hybridType = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $maternalOrganismId) {
            $this->setMaternalOrganismId($maternalOrganismId);
        }
        if (null !== $maternalOrganismName) {
            $this->setMaternalOrganismName($maternalOrganismName);
        }
        if (null !== $paternalOrganismId) {
            $this->setPaternalOrganismId($paternalOrganismId);
        }
        if (null !== $paternalOrganismName) {
            $this->setPaternalOrganismName($paternalOrganismName);
        }
        if (null !== $hybridType) {
            $this->setHybridType($hybridType);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The identifier of the maternal species constituting the hybrid organism shall be
     * specified based on a controlled vocabulary. For plants, the parents aren’t
     * always known, and it is unlikely that it will be known which is maternal and
     * which is paternal.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getMaternalOrganismId(): null|FHIRString
    {
        return $this->maternalOrganismId ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The identifier of the maternal species constituting the hybrid organism shall be
     * specified based on a controlled vocabulary. For plants, the parents aren’t
     * always known, and it is unlikely that it will be known which is maternal and
     * which is paternal.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $maternalOrganismId
     * @return static
     */
    public function setMaternalOrganismId(null|string|FHIRStringPrimitive|FHIRString $maternalOrganismId): self
    {
        if (null === $maternalOrganismId) {
            unset($this->maternalOrganismId);
            return $this;
        }
        if (!($maternalOrganismId instanceof FHIRString)) {
            $maternalOrganismId = new FHIRString(value: $maternalOrganismId);
        }
        $this->maternalOrganismId = $maternalOrganismId;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The name of the maternal species constituting the hybrid organism shall be
     * specified. For plants, the parents aren’t always known, and it is unlikely
     * that it will be known which is maternal and which is paternal.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getMaternalOrganismName(): null|FHIRString
    {
        return $this->maternalOrganismName ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The name of the maternal species constituting the hybrid organism shall be
     * specified. For plants, the parents aren’t always known, and it is unlikely
     * that it will be known which is maternal and which is paternal.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $maternalOrganismName
     * @return static
     */
    public function setMaternalOrganismName(null|string|FHIRStringPrimitive|FHIRString $maternalOrganismName): self
    {
        if (null === $maternalOrganismName) {
            unset($this->maternalOrganismName);
            return $this;
        }
        if (!($maternalOrganismName instanceof FHIRString)) {
            $maternalOrganismName = new FHIRString(value: $maternalOrganismName);
        }
        $this->maternalOrganismName = $maternalOrganismName;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The identifier of the paternal species constituting the hybrid organism shall be
     * specified based on a controlled vocabulary.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getPaternalOrganismId(): null|FHIRString
    {
        return $this->paternalOrganismId ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The identifier of the paternal species constituting the hybrid organism shall be
     * specified based on a controlled vocabulary.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $paternalOrganismId
     * @return static
     */
    public function setPaternalOrganismId(null|string|FHIRStringPrimitive|FHIRString $paternalOrganismId): self
    {
        if (null === $paternalOrganismId) {
            unset($this->paternalOrganismId);
            return $this;
        }
        if (!($paternalOrganismId instanceof FHIRString)) {
            $paternalOrganismId = new FHIRString(value: $paternalOrganismId);
        }
        $this->paternalOrganismId = $paternalOrganismId;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The name of the paternal species constituting the hybrid organism shall be
     * specified.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getPaternalOrganismName(): null|FHIRString
    {
        return $this->paternalOrganismName ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The name of the paternal species constituting the hybrid organism shall be
     * specified.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $paternalOrganismName
     * @return static
     */
    public function setPaternalOrganismName(null|string|FHIRStringPrimitive|FHIRString $paternalOrganismName): self
    {
        if (null === $paternalOrganismName) {
            unset($this->paternalOrganismName);
            return $this;
        }
        if (!($paternalOrganismName instanceof FHIRString)) {
            $paternalOrganismName = new FHIRString(value: $paternalOrganismName);
        }
        $this->paternalOrganismName = $paternalOrganismName;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The hybrid type of an organism shall be specified.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getHybridType(): null|FHIRCodeableConcept
    {
        return $this->hybridType ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The hybrid type of an organism shall be specified.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $hybridType
     * @return static
     */
    public function setHybridType(null|FHIRCodeableConcept $hybridType): self
    {
        if (null === $hybridType) {
            unset($this->hybridType);
            return $this;
        }
        $this->hybridType = $hybridType;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialHybrid $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialHybrid
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRSubstanceSourceMaterialHybrid)) {
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
            } else if (self::FIELD_MATERNAL_ORGANISM_ID === $cen) {
                $type->setMaternalOrganismId(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MATERNAL_ORGANISM_NAME === $cen) {
                $type->setMaternalOrganismName(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PATERNAL_ORGANISM_ID === $cen) {
                $type->setPaternalOrganismId(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PATERNAL_ORGANISM_NAME === $cen) {
                $type->setPaternalOrganismName(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_HYBRID_TYPE === $cen) {
                $type->setHybridType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_MATERNAL_ORGANISM_ID])) {
            if (isset($type->maternalOrganismId)) {
                $type->maternalOrganismId->setValue((string)$attributes[self::FIELD_MATERNAL_ORGANISM_ID]);
            } else {
                $type->setMaternalOrganismId((string)$attributes[self::FIELD_MATERNAL_ORGANISM_ID]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_MATERNAL_ORGANISM_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_MATERNAL_ORGANISM_NAME])) {
            if (isset($type->maternalOrganismName)) {
                $type->maternalOrganismName->setValue((string)$attributes[self::FIELD_MATERNAL_ORGANISM_NAME]);
            } else {
                $type->setMaternalOrganismName((string)$attributes[self::FIELD_MATERNAL_ORGANISM_NAME]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_MATERNAL_ORGANISM_NAME, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_PATERNAL_ORGANISM_ID])) {
            if (isset($type->paternalOrganismId)) {
                $type->paternalOrganismId->setValue((string)$attributes[self::FIELD_PATERNAL_ORGANISM_ID]);
            } else {
                $type->setPaternalOrganismId((string)$attributes[self::FIELD_PATERNAL_ORGANISM_ID]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_PATERNAL_ORGANISM_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_PATERNAL_ORGANISM_NAME])) {
            if (isset($type->paternalOrganismName)) {
                $type->paternalOrganismName->setValue((string)$attributes[self::FIELD_PATERNAL_ORGANISM_NAME]);
            } else {
                $type->setPaternalOrganismName((string)$attributes[self::FIELD_PATERNAL_ORGANISM_NAME]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_PATERNAL_ORGANISM_NAME, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->maternalOrganismId) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_MATERNAL_ORGANISM_ID]) {
            $xw->writeAttribute(self::FIELD_MATERNAL_ORGANISM_ID, $this->maternalOrganismId->_getValueAsString());
        }
        if (isset($this->maternalOrganismName) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_MATERNAL_ORGANISM_NAME]) {
            $xw->writeAttribute(self::FIELD_MATERNAL_ORGANISM_NAME, $this->maternalOrganismName->_getValueAsString());
        }
        if (isset($this->paternalOrganismId) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_PATERNAL_ORGANISM_ID]) {
            $xw->writeAttribute(self::FIELD_PATERNAL_ORGANISM_ID, $this->paternalOrganismId->_getValueAsString());
        }
        if (isset($this->paternalOrganismName) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_PATERNAL_ORGANISM_NAME]) {
            $xw->writeAttribute(self::FIELD_PATERNAL_ORGANISM_NAME, $this->paternalOrganismName->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->maternalOrganismId)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_MATERNAL_ORGANISM_ID]
                || $this->maternalOrganismId->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_MATERNAL_ORGANISM_ID);
            $this->maternalOrganismId->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_MATERNAL_ORGANISM_ID]);
            $xw->endElement();
        }
        if (isset($this->maternalOrganismName)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_MATERNAL_ORGANISM_NAME]
                || $this->maternalOrganismName->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_MATERNAL_ORGANISM_NAME);
            $this->maternalOrganismName->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_MATERNAL_ORGANISM_NAME]);
            $xw->endElement();
        }
        if (isset($this->paternalOrganismId)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_PATERNAL_ORGANISM_ID]
                || $this->paternalOrganismId->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_PATERNAL_ORGANISM_ID);
            $this->paternalOrganismId->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_PATERNAL_ORGANISM_ID]);
            $xw->endElement();
        }
        if (isset($this->paternalOrganismName)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_PATERNAL_ORGANISM_NAME]
                || $this->paternalOrganismName->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_PATERNAL_ORGANISM_NAME);
            $this->paternalOrganismName->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_PATERNAL_ORGANISM_NAME]);
            $xw->endElement();
        }
        if (isset($this->hybridType)) {
            $xw->startElement(self::FIELD_HYBRID_TYPE);
            $this->hybridType->xmlSerialize($xw, $config);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialHybrid $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialHybrid
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
        } else if (!($type instanceof FHIRSubstanceSourceMaterialHybrid)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->maternalOrganismId)
            || isset($decoded->_maternalOrganismId)
            || property_exists($decoded, self::FIELD_MATERNAL_ORGANISM_ID)
            || property_exists($decoded, self::FIELD_MATERNAL_ORGANISM_ID_EXT)) {
            $v = $decoded->_maternalOrganismId ?? new \stdClass();
            $v->value = $decoded->maternalOrganismId ?? null;
            $type->setMaternalOrganismId(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->maternalOrganismName)
            || isset($decoded->_maternalOrganismName)
            || property_exists($decoded, self::FIELD_MATERNAL_ORGANISM_NAME)
            || property_exists($decoded, self::FIELD_MATERNAL_ORGANISM_NAME_EXT)) {
            $v = $decoded->_maternalOrganismName ?? new \stdClass();
            $v->value = $decoded->maternalOrganismName ?? null;
            $type->setMaternalOrganismName(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->paternalOrganismId)
            || isset($decoded->_paternalOrganismId)
            || property_exists($decoded, self::FIELD_PATERNAL_ORGANISM_ID)
            || property_exists($decoded, self::FIELD_PATERNAL_ORGANISM_ID_EXT)) {
            $v = $decoded->_paternalOrganismId ?? new \stdClass();
            $v->value = $decoded->paternalOrganismId ?? null;
            $type->setPaternalOrganismId(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->paternalOrganismName)
            || isset($decoded->_paternalOrganismName)
            || property_exists($decoded, self::FIELD_PATERNAL_ORGANISM_NAME)
            || property_exists($decoded, self::FIELD_PATERNAL_ORGANISM_NAME_EXT)) {
            $v = $decoded->_paternalOrganismName ?? new \stdClass();
            $v->value = $decoded->paternalOrganismName ?? null;
            $type->setPaternalOrganismName(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->hybridType) || property_exists($decoded, self::FIELD_HYBRID_TYPE)) {
            if (is_array($decoded->hybridType)) {
                $type->setHybridType(FHIRCodeableConcept::jsonUnserialize(reset($decoded->hybridType), $config));
            } else {
                $type->setHybridType(FHIRCodeableConcept::jsonUnserialize($decoded->hybridType, $config));
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
        if (isset($this->maternalOrganismId)) {
            if (null !== ($val = $this->maternalOrganismId->getValue())) {
                $out->maternalOrganismId = $val;
            }
            if ($this->maternalOrganismId->_nonValueFieldDefined()) {
                $ext = $this->maternalOrganismId->jsonSerialize();
                unset($ext->value);
                $out->_maternalOrganismId = $ext;
            }
        }
        if (isset($this->maternalOrganismName)) {
            if (null !== ($val = $this->maternalOrganismName->getValue())) {
                $out->maternalOrganismName = $val;
            }
            if ($this->maternalOrganismName->_nonValueFieldDefined()) {
                $ext = $this->maternalOrganismName->jsonSerialize();
                unset($ext->value);
                $out->_maternalOrganismName = $ext;
            }
        }
        if (isset($this->paternalOrganismId)) {
            if (null !== ($val = $this->paternalOrganismId->getValue())) {
                $out->paternalOrganismId = $val;
            }
            if ($this->paternalOrganismId->_nonValueFieldDefined()) {
                $ext = $this->paternalOrganismId->jsonSerialize();
                unset($ext->value);
                $out->_paternalOrganismId = $ext;
            }
        }
        if (isset($this->paternalOrganismName)) {
            if (null !== ($val = $this->paternalOrganismName->getValue())) {
                $out->paternalOrganismName = $val;
            }
            if ($this->paternalOrganismName->_nonValueFieldDefined()) {
                $ext = $this->paternalOrganismName->jsonSerialize();
                unset($ext->value);
                $out->_paternalOrganismName = $ext;
            }
        }
        if (isset($this->hybridType)) {
            $out->hybridType = $this->hybridType;
        }
        return $out;
    }
}
