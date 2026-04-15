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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceReferenceInformation\FHIRSubstanceReferenceInformationClassification;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceReferenceInformation\FHIRSubstanceReferenceInformationGene;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceReferenceInformation\FHIRSubstanceReferenceInformationGeneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceReferenceInformation\FHIRSubstanceReferenceInformationTarget;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive;
use OpenEMR\FHIR\Versions\R4\Version;
use OpenEMR\FHIR\Versions\R4\VersionConstants;
use OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface;
use OpenEMR\FHIR\Versions\R4\VersionTypeMap;

/**
 * Todo.
 * If the element is present, it must have either a \@value, an \@id, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRSubstanceReferenceInformation extends FHIRDomainResource implements VersionContainedTypeInterface
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_SUBSTANCE_REFERENCE_INFORMATION;

    /* class_default.php:56 */
    public const FIELD_COMMENT = 'comment';
    public const FIELD_COMMENT_EXT = '_comment';
    public const FIELD_GENE = 'gene';
    public const FIELD_GENE_ELEMENT = 'geneElement';
    public const FIELD_CLASSIFICATION = 'classification';
    public const FIELD_TARGET = 'target';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_COMMENT => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Todo.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $comment;
    /**
     * Todo.
     *
     * Todo.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceReferenceInformation\FHIRSubstanceReferenceInformationGene>
     */
    #[FHIRSubstanceReferenceInformationGene]
    protected array $gene;
    /**
     * Todo.
     *
     * Todo.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceReferenceInformation\FHIRSubstanceReferenceInformationGeneElement>
     */
    #[FHIRSubstanceReferenceInformationGeneElement]
    protected array $geneElement;
    /**
     * Todo.
     *
     * Todo.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceReferenceInformation\FHIRSubstanceReferenceInformationClassification>
     */
    #[FHIRSubstanceReferenceInformationClassification]
    protected array $classification;
    /**
     * Todo.
     *
     * Todo.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceReferenceInformation\FHIRSubstanceReferenceInformationTarget>
     */
    #[FHIRSubstanceReferenceInformationTarget]
    protected array $target;

    /* constructor.php:61 */
    /**
     * FHIRSubstanceReferenceInformation Constructor
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $id
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta $meta
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $implicitRules
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $language
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative $text
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRResourceContainer>|iterable<\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface> $contained
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $comment
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceReferenceInformation\FHIRSubstanceReferenceInformationGene> $gene
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceReferenceInformation\FHIRSubstanceReferenceInformationGeneElement> $geneElement
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceReferenceInformation\FHIRSubstanceReferenceInformationClassification> $classification
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceReferenceInformation\FHIRSubstanceReferenceInformationTarget> $target
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
                                null|string|FHIRStringPrimitive|FHIRString $comment = null,
                                null|iterable $gene = null,
                                null|iterable $geneElement = null,
                                null|iterable $classification = null,
                                null|iterable $target = null,
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
        if (null !== $comment) {
            $this->setComment($comment);
        }
        if (null !== $gene) {
            $this->setGene(...$gene);
        }
        if (null !== $geneElement) {
            $this->setGeneElement(...$geneElement);
        }
        if (null !== $classification) {
            $this->setClassification(...$classification);
        }
        if (null !== $target) {
            $this->setTarget(...$target);
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
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Todo.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getComment(): null|FHIRString
    {
        return $this->comment ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Todo.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $comment
     * @return static
     */
    public function setComment(null|string|FHIRStringPrimitive|FHIRString $comment): self
    {
        if (null === $comment) {
            unset($this->comment);
            return $this;
        }
        if (!($comment instanceof FHIRString)) {
            $comment = new FHIRString(value: $comment);
        }
        $this->comment = $comment;
        return $this;
    }

    /**
     * Todo.
     *
     * Todo.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceReferenceInformation\FHIRSubstanceReferenceInformationGene>
     */
    public function getGene(): array
    {
        return $this->gene ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceReferenceInformation\FHIRSubstanceReferenceInformationGene>
     */
    public function getGeneIterator(): iterable
    {
        if (!isset($this->gene)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->gene);
    }

    /**
     * Todo.
     *
     * Todo.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceReferenceInformation\FHIRSubstanceReferenceInformationGene $gene
     * @return static
     */
    public function addGene(FHIRSubstanceReferenceInformationGene $gene): self
    {
        if (!isset($this->gene)) {
            $this->gene = [];
        }
        $this->gene[] = $gene;
        return $this;
    }

    /**
     * Todo.
     *
     * Todo.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceReferenceInformation\FHIRSubstanceReferenceInformationGene ...$gene
     * @return static
     */
    public function setGene(FHIRSubstanceReferenceInformationGene ...$gene): self
    {
        if ([] === $gene) {
            unset($this->gene);
            return $this;
        }
        $this->gene = $gene;
        return $this;
    }

    /**
     * Todo.
     *
     * Todo.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceReferenceInformation\FHIRSubstanceReferenceInformationGeneElement>
     */
    public function getGeneElement(): array
    {
        return $this->geneElement ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceReferenceInformation\FHIRSubstanceReferenceInformationGeneElement>
     */
    public function getGeneElementIterator(): iterable
    {
        if (!isset($this->geneElement)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->geneElement);
    }

    /**
     * Todo.
     *
     * Todo.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceReferenceInformation\FHIRSubstanceReferenceInformationGeneElement $geneElement
     * @return static
     */
    public function addGeneElement(FHIRSubstanceReferenceInformationGeneElement $geneElement): self
    {
        if (!isset($this->geneElement)) {
            $this->geneElement = [];
        }
        $this->geneElement[] = $geneElement;
        return $this;
    }

    /**
     * Todo.
     *
     * Todo.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceReferenceInformation\FHIRSubstanceReferenceInformationGeneElement ...$geneElement
     * @return static
     */
    public function setGeneElement(FHIRSubstanceReferenceInformationGeneElement ...$geneElement): self
    {
        if ([] === $geneElement) {
            unset($this->geneElement);
            return $this;
        }
        $this->geneElement = $geneElement;
        return $this;
    }

    /**
     * Todo.
     *
     * Todo.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceReferenceInformation\FHIRSubstanceReferenceInformationClassification>
     */
    public function getClassification(): array
    {
        return $this->classification ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceReferenceInformation\FHIRSubstanceReferenceInformationClassification>
     */
    public function getClassificationIterator(): iterable
    {
        if (!isset($this->classification)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->classification);
    }

    /**
     * Todo.
     *
     * Todo.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceReferenceInformation\FHIRSubstanceReferenceInformationClassification $classification
     * @return static
     */
    public function addClassification(FHIRSubstanceReferenceInformationClassification $classification): self
    {
        if (!isset($this->classification)) {
            $this->classification = [];
        }
        $this->classification[] = $classification;
        return $this;
    }

    /**
     * Todo.
     *
     * Todo.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceReferenceInformation\FHIRSubstanceReferenceInformationClassification ...$classification
     * @return static
     */
    public function setClassification(FHIRSubstanceReferenceInformationClassification ...$classification): self
    {
        if ([] === $classification) {
            unset($this->classification);
            return $this;
        }
        $this->classification = $classification;
        return $this;
    }

    /**
     * Todo.
     *
     * Todo.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceReferenceInformation\FHIRSubstanceReferenceInformationTarget>
     */
    public function getTarget(): array
    {
        return $this->target ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceReferenceInformation\FHIRSubstanceReferenceInformationTarget>
     */
    public function getTargetIterator(): iterable
    {
        if (!isset($this->target)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->target);
    }

    /**
     * Todo.
     *
     * Todo.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceReferenceInformation\FHIRSubstanceReferenceInformationTarget $target
     * @return static
     */
    public function addTarget(FHIRSubstanceReferenceInformationTarget $target): self
    {
        if (!isset($this->target)) {
            $this->target = [];
        }
        $this->target[] = $target;
        return $this;
    }

    /**
     * Todo.
     *
     * Todo.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceReferenceInformation\FHIRSubstanceReferenceInformationTarget ...$target
     * @return static
     */
    public function setTarget(FHIRSubstanceReferenceInformationTarget ...$target): self
    {
        if ([] === $target) {
            unset($this->target);
            return $this;
        }
        $this->target = $target;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param string|\SimpleXMLElement $element
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSubstanceReferenceInformation $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSubstanceReferenceInformation
     * @throws \Exception
     */
    public static function xmlUnserialize(string|\SimpleXMLElement $element,
                                          null|UnserializeConfig $config = null,
                                          null|ResourceTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRSubstanceReferenceInformation)) {
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
            } else if (self::FIELD_COMMENT === $cen) {
                $type->setComment(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_GENE === $cen) {
                $type->addGene(FHIRSubstanceReferenceInformationGene::xmlUnserialize($ce, $config));
            } else if (self::FIELD_GENE_ELEMENT === $cen) {
                $type->addGeneElement(FHIRSubstanceReferenceInformationGeneElement::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CLASSIFICATION === $cen) {
                $type->addClassification(FHIRSubstanceReferenceInformationClassification::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TARGET === $cen) {
                $type->addTarget(FHIRSubstanceReferenceInformationTarget::xmlUnserialize($ce, $config));
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
        if (isset($attributes[self::FIELD_COMMENT])) {
            if (isset($type->comment)) {
                $type->comment->setValue((string)$attributes[self::FIELD_COMMENT]);
            } else {
                $type->setComment((string)$attributes[self::FIELD_COMMENT]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_COMMENT, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
            $xw->openRootNode('SubstanceReferenceInformation', $this->_getSourceXMLNS());
        }
        if (isset($this->comment) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_COMMENT]) {
            $xw->writeAttribute(self::FIELD_COMMENT, $this->comment->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->comment)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_COMMENT]
                || $this->comment->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_COMMENT);
            $this->comment->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_COMMENT]);
            $xw->endElement();
        }
        if (isset($this->gene)) {
            foreach ($this->gene as $v) {
                $xw->startElement(self::FIELD_GENE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->geneElement)) {
            foreach ($this->geneElement as $v) {
                $xw->startElement(self::FIELD_GENE_ELEMENT);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->classification)) {
            foreach ($this->classification as $v) {
                $xw->startElement(self::FIELD_CLASSIFICATION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->target)) {
            foreach ($this->target as $v) {
                $xw->startElement(self::FIELD_TARGET);
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
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSubstanceReferenceInformation $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSubstanceReferenceInformation
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
        } else if (!($type instanceof FHIRSubstanceReferenceInformation)) {
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
        if (isset($decoded->comment)
            || isset($decoded->_comment)
            || property_exists($decoded, self::FIELD_COMMENT)
            || property_exists($decoded, self::FIELD_COMMENT_EXT)) {
            $v = $decoded->_comment ?? new \stdClass();
            $v->value = $decoded->comment ?? null;
            $type->setComment(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->gene) || property_exists($decoded, self::FIELD_GENE)) {
            if (is_object($decoded->gene)) {
                $vals = [$decoded->gene];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_GENE, true);
            } else {
                $vals = $decoded->gene;
            }
            foreach($vals as $v) {
                $type->addGene(FHIRSubstanceReferenceInformationGene::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->geneElement) || property_exists($decoded, self::FIELD_GENE_ELEMENT)) {
            if (is_object($decoded->geneElement)) {
                $vals = [$decoded->geneElement];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_GENE_ELEMENT, true);
            } else {
                $vals = $decoded->geneElement;
            }
            foreach($vals as $v) {
                $type->addGeneElement(FHIRSubstanceReferenceInformationGeneElement::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->classification) || property_exists($decoded, self::FIELD_CLASSIFICATION)) {
            if (is_object($decoded->classification)) {
                $vals = [$decoded->classification];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_CLASSIFICATION, true);
            } else {
                $vals = $decoded->classification;
            }
            foreach($vals as $v) {
                $type->addClassification(FHIRSubstanceReferenceInformationClassification::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->target) || property_exists($decoded, self::FIELD_TARGET)) {
            if (is_object($decoded->target)) {
                $vals = [$decoded->target];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_TARGET, true);
            } else {
                $vals = $decoded->target;
            }
            foreach($vals as $v) {
                $type->addTarget(FHIRSubstanceReferenceInformationTarget::jsonUnserialize($v, $config));
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
        if (isset($this->comment)) {
            if (null !== ($val = $this->comment->getValue())) {
                $out->comment = $val;
            }
            if ($this->comment->_nonValueFieldDefined()) {
                $ext = $this->comment->jsonSerialize();
                unset($ext->value);
                $out->_comment = $ext;
            }
        }
        if (isset($this->gene) && [] !== $this->gene) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_GENE) && 1 === count($this->gene)) {
                $out->gene = $this->gene[0];
            } else {
                $out->gene = $this->gene;
            }
        }
        if (isset($this->geneElement) && [] !== $this->geneElement) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_GENE_ELEMENT) && 1 === count($this->geneElement)) {
                $out->geneElement = $this->geneElement[0];
            } else {
                $out->geneElement = $this->geneElement;
            }
        }
        if (isset($this->classification) && [] !== $this->classification) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_CLASSIFICATION) && 1 === count($this->classification)) {
                $out->classification = $this->classification[0];
            } else {
                $out->classification = $this->classification;
            }
        }
        if (isset($this->target) && [] !== $this->target) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_TARGET) && 1 === count($this->target)) {
                $out->target = $this->target[0];
            } else {
                $out->target = $this->target;
            }
        }
        $out->resourceType = $this->_getResourceType();
        return $out;
    }
}
