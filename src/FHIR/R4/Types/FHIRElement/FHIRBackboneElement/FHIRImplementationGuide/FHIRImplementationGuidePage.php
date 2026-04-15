<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImplementationGuide;

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
use OpenEMR\FHIR\Validation\Rules\MinOccursRule;
use OpenEMR\FHIR\Validation\TypeValidationsTrait;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRGuidePageGenerationList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRGuidePageGeneration;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUrl;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUrlPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * A set of rules of how a particular interoperability or standards problem is
 * solved - typically through the use of FHIR resources. This resource is used to
 * gather all the parts of an implementation guide into a logical whole and to
 * publish a computable definition of all the parts.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRImplementationGuidePage extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_IMPLEMENTATION_GUIDE_DOT_PAGE;

    /* class_default.php:56 */
    public const FIELD_NAME_URL = 'nameUrl';
    public const FIELD_NAME_URL_EXT = '_nameUrl';
    public const FIELD_NAME_REFERENCE = 'nameReference';
    public const FIELD_TITLE = 'title';
    public const FIELD_TITLE_EXT = '_title';
    public const FIELD_GENERATION = 'generation';
    public const FIELD_GENERATION_EXT = '_generation';
    public const FIELD_PAGE = 'page';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_TITLE => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_GENERATION => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_NAME_URL => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_TITLE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_GENERATION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A URI that is a literal reference
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The source address for the page. (choose any one of name*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUrl
     */
    #[FHIRUrl]
    protected FHIRUrl $nameUrl;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The source address for the page. (choose any one of name*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $nameReference;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A short title used to represent this page in navigational structures such as
     * table of contents, bread crumbs, etc.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $title;
    /**
     * A code that indicates how the page is generated.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A code that indicates how the page is generated.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRGuidePageGeneration
     */
    #[FHIRGuidePageGeneration]
    protected FHIRGuidePageGeneration $generation;
    /**
     * A set of rules of how a particular interoperability or standards problem is
     * solved - typically through the use of FHIR resources. This resource is used to
     * gather all the parts of an implementation guide into a logical whole and to
     * publish a computable definition of all the parts.
     *
     * Nested Pages/Sections under this page.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImplementationGuide\FHIRImplementationGuidePage>
     */
    #[FHIRImplementationGuidePage]
    protected array $page;

    /* constructor.php:61 */
    /**
     * FHIRImplementationGuidePage Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUrlPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUrl $nameUrl
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $nameReference
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $title
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRGuidePageGenerationList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRGuidePageGeneration $generation
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImplementationGuide\FHIRImplementationGuidePage> $page
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|string|FHIRUrlPrimitive|FHIRUrl $nameUrl = null,
                                null|FHIRReference $nameReference = null,
                                null|string|FHIRStringPrimitive|FHIRString $title = null,
                                null|string|FHIRGuidePageGenerationList|FHIRGuidePageGeneration $generation = null,
                                null|iterable $page = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $nameUrl) {
            $this->setNameUrl($nameUrl);
        }
        if (null !== $nameReference) {
            $this->setNameReference($nameReference);
        }
        if (null !== $title) {
            $this->setTitle($title);
        }
        if (null !== $generation) {
            $this->setGeneration($generation);
        }
        if (null !== $page) {
            $this->setPage(...$page);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * A URI that is a literal reference
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The source address for the page. (choose any one of name*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUrl
     */
    public function getNameUrl(): null|FHIRUrl
    {
        return $this->nameUrl ?? null;
    }

    /**
     * A URI that is a literal reference
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The source address for the page. (choose any one of name*, but only one)
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUrlPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUrl $nameUrl
     * @return static
     */
    public function setNameUrl(null|string|FHIRUrlPrimitive|FHIRUrl $nameUrl): self
    {
        if (null === $nameUrl) {
            unset($this->nameUrl);
            return $this;
        }
        if (!($nameUrl instanceof FHIRUrl)) {
            $nameUrl = new FHIRUrl(value: $nameUrl);
        }
        $this->nameUrl = $nameUrl;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The source address for the page. (choose any one of name*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getNameReference(): null|FHIRReference
    {
        return $this->nameReference ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The source address for the page. (choose any one of name*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $nameReference
     * @return static
     */
    public function setNameReference(null|FHIRReference $nameReference): self
    {
        if (null === $nameReference) {
            unset($this->nameReference);
            return $this;
        }
        $this->nameReference = $nameReference;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A short title used to represent this page in navigational structures such as
     * table of contents, bread crumbs, etc.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getTitle(): null|FHIRString
    {
        return $this->title ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A short title used to represent this page in navigational structures such as
     * table of contents, bread crumbs, etc.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $title
     * @return static
     */
    public function setTitle(null|string|FHIRStringPrimitive|FHIRString $title): self
    {
        if (null === $title) {
            unset($this->title);
            return $this;
        }
        if (!($title instanceof FHIRString)) {
            $title = new FHIRString(value: $title);
        }
        $this->title = $title;
        return $this;
    }

    /**
     * A code that indicates how the page is generated.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A code that indicates how the page is generated.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRGuidePageGeneration
     */
    public function getGeneration(): null|FHIRGuidePageGeneration
    {
        return $this->generation ?? null;
    }

    /**
     * A code that indicates how the page is generated.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A code that indicates how the page is generated.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRGuidePageGenerationList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRGuidePageGeneration $generation
     * @return static
     */
    public function setGeneration(null|string|FHIRGuidePageGenerationList|FHIRGuidePageGeneration $generation): self
    {
        if (null === $generation) {
            unset($this->generation);
            return $this;
        }
        if (!($generation instanceof FHIRGuidePageGeneration)) {
            $generation = new FHIRGuidePageGeneration(value: $generation);
        }
        $this->generation = $generation;
        return $this;
    }

    /**
     * A set of rules of how a particular interoperability or standards problem is
     * solved - typically through the use of FHIR resources. This resource is used to
     * gather all the parts of an implementation guide into a logical whole and to
     * publish a computable definition of all the parts.
     *
     * Nested Pages/Sections under this page.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImplementationGuide\FHIRImplementationGuidePage>
     */
    public function getPage(): array
    {
        return $this->page ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImplementationGuide\FHIRImplementationGuidePage>
     */
    public function getPageIterator(): iterable
    {
        if (!isset($this->page)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->page);
    }

    /**
     * A set of rules of how a particular interoperability or standards problem is
     * solved - typically through the use of FHIR resources. This resource is used to
     * gather all the parts of an implementation guide into a logical whole and to
     * publish a computable definition of all the parts.
     *
     * Nested Pages/Sections under this page.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImplementationGuide\FHIRImplementationGuidePage $page
     * @return static
     */
    public function addPage(FHIRImplementationGuidePage $page): self
    {
        if (!isset($this->page)) {
            $this->page = [];
        }
        $this->page[] = $page;
        return $this;
    }

    /**
     * A set of rules of how a particular interoperability or standards problem is
     * solved - typically through the use of FHIR resources. This resource is used to
     * gather all the parts of an implementation guide into a logical whole and to
     * publish a computable definition of all the parts.
     *
     * Nested Pages/Sections under this page.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImplementationGuide\FHIRImplementationGuidePage ...$page
     * @return static
     */
    public function setPage(FHIRImplementationGuidePage ...$page): self
    {
        if ([] === $page) {
            unset($this->page);
            return $this;
        }
        $this->page = $page;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImplementationGuide\FHIRImplementationGuidePage $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImplementationGuide\FHIRImplementationGuidePage
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRImplementationGuidePage)) {
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
            } else if (self::FIELD_NAME_URL === $cen) {
                $type->setNameUrl(FHIRUrl::xmlUnserialize($ce, $config));
            } else if (self::FIELD_NAME_REFERENCE === $cen) {
                $type->setNameReference(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TITLE === $cen) {
                $type->setTitle(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_GENERATION === $cen) {
                $type->setGeneration(FHIRGuidePageGeneration::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PAGE === $cen) {
                $type->addPage(FHIRImplementationGuidePage::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_NAME_URL])) {
            if (isset($type->nameUrl)) {
                $type->nameUrl->setValue((string)$attributes[self::FIELD_NAME_URL]);
            } else {
                $type->setNameUrl((string)$attributes[self::FIELD_NAME_URL]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_NAME_URL, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_TITLE])) {
            if (isset($type->title)) {
                $type->title->setValue((string)$attributes[self::FIELD_TITLE]);
            } else {
                $type->setTitle((string)$attributes[self::FIELD_TITLE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_TITLE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_GENERATION])) {
            if (isset($type->generation)) {
                $type->generation->setValue((string)$attributes[self::FIELD_GENERATION]);
            } else {
                $type->setGeneration((string)$attributes[self::FIELD_GENERATION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_GENERATION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->nameUrl) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_NAME_URL]) {
            $xw->writeAttribute(self::FIELD_NAME_URL, $this->nameUrl->_getValueAsString());
        }
        if (isset($this->title) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_TITLE]) {
            $xw->writeAttribute(self::FIELD_TITLE, $this->title->_getValueAsString());
        }
        if (isset($this->generation) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_GENERATION]) {
            $xw->writeAttribute(self::FIELD_GENERATION, $this->generation->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->nameUrl)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_NAME_URL]
                || $this->nameUrl->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_NAME_URL);
            $this->nameUrl->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_NAME_URL]);
            $xw->endElement();
        }
        if (isset($this->nameReference)) {
            $xw->startElement(self::FIELD_NAME_REFERENCE);
            $this->nameReference->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->title)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_TITLE]
                || $this->title->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_TITLE);
            $this->title->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_TITLE]);
            $xw->endElement();
        }
        if (isset($this->generation)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_GENERATION]
                || $this->generation->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_GENERATION);
            $this->generation->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_GENERATION]);
            $xw->endElement();
        }
        if (isset($this->page)) {
            foreach ($this->page as $v) {
                $xw->startElement(self::FIELD_PAGE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImplementationGuide\FHIRImplementationGuidePage $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImplementationGuide\FHIRImplementationGuidePage
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
        } else if (!($type instanceof FHIRImplementationGuidePage)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->nameUrl)
            || isset($decoded->_nameUrl)
            || property_exists($decoded, self::FIELD_NAME_URL)
            || property_exists($decoded, self::FIELD_NAME_URL_EXT)) {
            $v = $decoded->_nameUrl ?? new \stdClass();
            $v->value = $decoded->nameUrl ?? null;
            $type->setNameUrl(FHIRUrl::jsonUnserialize($v, $config));
        }
        if (isset($decoded->nameReference) || property_exists($decoded, self::FIELD_NAME_REFERENCE)) {
            if (is_array($decoded->nameReference)) {
                $type->setNameReference(FHIRReference::jsonUnserialize(reset($decoded->nameReference), $config));
            } else {
                $type->setNameReference(FHIRReference::jsonUnserialize($decoded->nameReference, $config));
            }
        }
        if (isset($decoded->title)
            || isset($decoded->_title)
            || property_exists($decoded, self::FIELD_TITLE)
            || property_exists($decoded, self::FIELD_TITLE_EXT)) {
            $v = $decoded->_title ?? new \stdClass();
            $v->value = $decoded->title ?? null;
            $type->setTitle(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->generation)
            || isset($decoded->_generation)
            || property_exists($decoded, self::FIELD_GENERATION)
            || property_exists($decoded, self::FIELD_GENERATION_EXT)) {
            $v = $decoded->_generation ?? new \stdClass();
            $v->value = $decoded->generation ?? null;
            $type->setGeneration(FHIRGuidePageGeneration::jsonUnserialize($v, $config));
        }
        if (isset($decoded->page) || property_exists($decoded, self::FIELD_PAGE)) {
            if (is_object($decoded->page)) {
                $vals = [$decoded->page];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PAGE, true);
            } else {
                $vals = $decoded->page;
            }
            foreach($vals as $v) {
                $type->addPage(FHIRImplementationGuidePage::jsonUnserialize($v, $config));
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
        if (isset($this->nameUrl)) {
            if (null !== ($val = $this->nameUrl->getValue())) {
                $out->nameUrl = $val;
            }
            if ($this->nameUrl->_nonValueFieldDefined()) {
                $ext = $this->nameUrl->jsonSerialize();
                unset($ext->value);
                $out->_nameUrl = $ext;
            }
        }
        if (isset($this->nameReference)) {
            $out->nameReference = $this->nameReference;
        }
        if (isset($this->title)) {
            if (null !== ($val = $this->title->getValue())) {
                $out->title = $val;
            }
            if ($this->title->_nonValueFieldDefined()) {
                $ext = $this->title->jsonSerialize();
                unset($ext->value);
                $out->_title = $ext;
            }
        }
        if (isset($this->generation)) {
            if (null !== ($val = $this->generation->getValue())) {
                $out->generation = $val;
            }
            if ($this->generation->_nonValueFieldDefined()) {
                $ext = $this->generation->jsonSerialize();
                unset($ext->value);
                $out->_generation = $ext;
            }
        }
        if (isset($this->page) && [] !== $this->page) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PAGE) && 1 === count($this->page)) {
                $out->page = $this->page[0];
            } else {
                $out->page = $this->page;
            }
        }
        return $out;
    }
}
