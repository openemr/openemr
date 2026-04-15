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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstancePolymer\FHIRSubstancePolymerMonomerSet;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstancePolymer\FHIRSubstancePolymerRepeat;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
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
class FHIRSubstancePolymer extends FHIRDomainResource implements VersionContainedTypeInterface
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_SUBSTANCE_POLYMER;

    /* class_default.php:56 */
    public const FIELD_CLASS = 'class';
    public const FIELD_GEOMETRY = 'geometry';
    public const FIELD_COPOLYMER_CONNECTIVITY = 'copolymerConnectivity';
    public const FIELD_MODIFICATION = 'modification';
    public const FIELD_MODIFICATION_EXT = '_modification';
    public const FIELD_MONOMER_SET = 'monomerSet';
    public const FIELD_REPEAT = 'repeat';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
    ];

    /* class_default.php:112 */
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Todo.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $class;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Todo.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $geometry;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Todo.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $copolymerConnectivity;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Todo.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    #[FHIRString]
    protected array $modification;
    /**
     * Todo.
     *
     * Todo.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstancePolymer\FHIRSubstancePolymerMonomerSet>
     */
    #[FHIRSubstancePolymerMonomerSet]
    protected array $monomerSet;
    /**
     * Todo.
     *
     * Todo.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstancePolymer\FHIRSubstancePolymerRepeat>
     */
    #[FHIRSubstancePolymerRepeat]
    protected array $repeat;

    /* constructor.php:61 */
    /**
     * FHIRSubstancePolymer Constructor
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $id
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta $meta
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $implicitRules
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $language
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative $text
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRResourceContainer>|iterable<\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface> $contained
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $class
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $geometry
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $copolymerConnectivity
     * @param null|iterable<string>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString> $modification
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstancePolymer\FHIRSubstancePolymerMonomerSet> $monomerSet
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstancePolymer\FHIRSubstancePolymerRepeat> $repeat
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
                                null|FHIRCodeableConcept $class = null,
                                null|FHIRCodeableConcept $geometry = null,
                                null|iterable $copolymerConnectivity = null,
                                null|iterable $modification = null,
                                null|iterable $monomerSet = null,
                                null|iterable $repeat = null,
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
        if (null !== $class) {
            $this->setClass($class);
        }
        if (null !== $geometry) {
            $this->setGeometry($geometry);
        }
        if (null !== $copolymerConnectivity) {
            $this->setCopolymerConnectivity(...$copolymerConnectivity);
        }
        if (null !== $modification) {
            $this->setModification(...$modification);
        }
        if (null !== $monomerSet) {
            $this->setMonomerSet(...$monomerSet);
        }
        if (null !== $repeat) {
            $this->setRepeat(...$repeat);
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
     * Todo.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getClass(): null|FHIRCodeableConcept
    {
        return $this->class ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Todo.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $class
     * @return static
     */
    public function setClass(null|FHIRCodeableConcept $class): self
    {
        if (null === $class) {
            unset($this->class);
            return $this;
        }
        $this->class = $class;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Todo.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getGeometry(): null|FHIRCodeableConcept
    {
        return $this->geometry ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Todo.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $geometry
     * @return static
     */
    public function setGeometry(null|FHIRCodeableConcept $geometry): self
    {
        if (null === $geometry) {
            unset($this->geometry);
            return $this;
        }
        $this->geometry = $geometry;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Todo.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getCopolymerConnectivity(): array
    {
        return $this->copolymerConnectivity ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getCopolymerConnectivityIterator(): iterable
    {
        if (!isset($this->copolymerConnectivity)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->copolymerConnectivity);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Todo.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $copolymerConnectivity
     * @return static
     */
    public function addCopolymerConnectivity(FHIRCodeableConcept $copolymerConnectivity): self
    {
        if (!isset($this->copolymerConnectivity)) {
            $this->copolymerConnectivity = [];
        }
        $this->copolymerConnectivity[] = $copolymerConnectivity;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Todo.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$copolymerConnectivity
     * @return static
     */
    public function setCopolymerConnectivity(FHIRCodeableConcept ...$copolymerConnectivity): self
    {
        if ([] === $copolymerConnectivity) {
            unset($this->copolymerConnectivity);
            return $this;
        }
        $this->copolymerConnectivity = $copolymerConnectivity;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Todo.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    public function getModification(): array
    {
        return $this->modification ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    public function getModificationIterator(): iterable
    {
        if (!isset($this->modification)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->modification);
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Todo.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $modification
     * @return static
     */
    public function addModification(string|FHIRStringPrimitive|FHIRString $modification): self
    {
        if (!($modification instanceof FHIRString)) {
            $modification = new FHIRString(value: $modification);
        }
        if (!isset($this->modification)) {
            $this->modification = [];
        }
        $this->modification[] = $modification;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Todo.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString ...$modification
     * @return static
     */
    public function setModification(string|FHIRStringPrimitive|FHIRString ...$modification): self
    {
        if ([] === $modification) {
            unset($this->modification);
            return $this;
        }
        $this->modification = [];
        foreach($modification as $v) {
            if ($v instanceof FHIRString) {
                $this->modification[] = $v;
            } else {
                $this->modification[] = new FHIRString(value: $v);
            }
        }
        return $this;
    }

    /**
     * Todo.
     *
     * Todo.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstancePolymer\FHIRSubstancePolymerMonomerSet>
     */
    public function getMonomerSet(): array
    {
        return $this->monomerSet ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstancePolymer\FHIRSubstancePolymerMonomerSet>
     */
    public function getMonomerSetIterator(): iterable
    {
        if (!isset($this->monomerSet)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->monomerSet);
    }

    /**
     * Todo.
     *
     * Todo.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstancePolymer\FHIRSubstancePolymerMonomerSet $monomerSet
     * @return static
     */
    public function addMonomerSet(FHIRSubstancePolymerMonomerSet $monomerSet): self
    {
        if (!isset($this->monomerSet)) {
            $this->monomerSet = [];
        }
        $this->monomerSet[] = $monomerSet;
        return $this;
    }

    /**
     * Todo.
     *
     * Todo.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstancePolymer\FHIRSubstancePolymerMonomerSet ...$monomerSet
     * @return static
     */
    public function setMonomerSet(FHIRSubstancePolymerMonomerSet ...$monomerSet): self
    {
        if ([] === $monomerSet) {
            unset($this->monomerSet);
            return $this;
        }
        $this->monomerSet = $monomerSet;
        return $this;
    }

    /**
     * Todo.
     *
     * Todo.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstancePolymer\FHIRSubstancePolymerRepeat>
     */
    public function getRepeat(): array
    {
        return $this->repeat ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstancePolymer\FHIRSubstancePolymerRepeat>
     */
    public function getRepeatIterator(): iterable
    {
        if (!isset($this->repeat)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->repeat);
    }

    /**
     * Todo.
     *
     * Todo.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstancePolymer\FHIRSubstancePolymerRepeat $repeat
     * @return static
     */
    public function addRepeat(FHIRSubstancePolymerRepeat $repeat): self
    {
        if (!isset($this->repeat)) {
            $this->repeat = [];
        }
        $this->repeat[] = $repeat;
        return $this;
    }

    /**
     * Todo.
     *
     * Todo.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstancePolymer\FHIRSubstancePolymerRepeat ...$repeat
     * @return static
     */
    public function setRepeat(FHIRSubstancePolymerRepeat ...$repeat): self
    {
        if ([] === $repeat) {
            unset($this->repeat);
            return $this;
        }
        $this->repeat = $repeat;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param string|\SimpleXMLElement $element
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSubstancePolymer $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSubstancePolymer
     * @throws \Exception
     */
    public static function xmlUnserialize(string|\SimpleXMLElement $element,
                                          null|UnserializeConfig $config = null,
                                          null|ResourceTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRSubstancePolymer)) {
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
            } else if (self::FIELD_CLASS === $cen) {
                $type->setClass(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_GEOMETRY === $cen) {
                $type->setGeometry(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_COPOLYMER_CONNECTIVITY === $cen) {
                $type->addCopolymerConnectivity(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MODIFICATION === $cen) {
                $type->addModification(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MONOMER_SET === $cen) {
                $type->addMonomerSet(FHIRSubstancePolymerMonomerSet::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REPEAT === $cen) {
                $type->addRepeat(FHIRSubstancePolymerRepeat::xmlUnserialize($ce, $config));
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
            $xw->openRootNode('SubstancePolymer', $this->_getSourceXMLNS());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->class)) {
            $xw->startElement(self::FIELD_CLASS);
            $this->class->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->geometry)) {
            $xw->startElement(self::FIELD_GEOMETRY);
            $this->geometry->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->copolymerConnectivity)) {
            foreach ($this->copolymerConnectivity as $v) {
                $xw->startElement(self::FIELD_COPOLYMER_CONNECTIVITY);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->modification) && [] !== $this->modification) {
            foreach($this->modification as $v) {
                $xw->startElement(self::FIELD_MODIFICATION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->monomerSet)) {
            foreach ($this->monomerSet as $v) {
                $xw->startElement(self::FIELD_MONOMER_SET);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->repeat)) {
            foreach ($this->repeat as $v) {
                $xw->startElement(self::FIELD_REPEAT);
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
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSubstancePolymer $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSubstancePolymer
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
        } else if (!($type instanceof FHIRSubstancePolymer)) {
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
        if (isset($decoded->class) || property_exists($decoded, self::FIELD_CLASS)) {
            if (is_array($decoded->class)) {
                $type->setClass(FHIRCodeableConcept::jsonUnserialize(reset($decoded->class), $config));
            } else {
                $type->setClass(FHIRCodeableConcept::jsonUnserialize($decoded->class, $config));
            }
        }
        if (isset($decoded->geometry) || property_exists($decoded, self::FIELD_GEOMETRY)) {
            if (is_array($decoded->geometry)) {
                $type->setGeometry(FHIRCodeableConcept::jsonUnserialize(reset($decoded->geometry), $config));
            } else {
                $type->setGeometry(FHIRCodeableConcept::jsonUnserialize($decoded->geometry, $config));
            }
        }
        if (isset($decoded->copolymerConnectivity) || property_exists($decoded, self::FIELD_COPOLYMER_CONNECTIVITY)) {
            if (is_object($decoded->copolymerConnectivity)) {
                $vals = [$decoded->copolymerConnectivity];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_COPOLYMER_CONNECTIVITY, true);
            } else {
                $vals = $decoded->copolymerConnectivity;
            }
            foreach($vals as $v) {
                $type->addCopolymerConnectivity(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->modification)
            || isset($decoded->_modification)
            || property_exists($decoded, self::FIELD_MODIFICATION)
            || property_exists($decoded, self::FIELD_MODIFICATION_EXT)) {
            $vals = (array)($decoded->modification ?? []);
            $exts = (array)($decoded->_modification ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addModification(FHIRString::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->monomerSet) || property_exists($decoded, self::FIELD_MONOMER_SET)) {
            if (is_object($decoded->monomerSet)) {
                $vals = [$decoded->monomerSet];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_MONOMER_SET, true);
            } else {
                $vals = $decoded->monomerSet;
            }
            foreach($vals as $v) {
                $type->addMonomerSet(FHIRSubstancePolymerMonomerSet::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->repeat) || property_exists($decoded, self::FIELD_REPEAT)) {
            if (is_object($decoded->repeat)) {
                $vals = [$decoded->repeat];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_REPEAT, true);
            } else {
                $vals = $decoded->repeat;
            }
            foreach($vals as $v) {
                $type->addRepeat(FHIRSubstancePolymerRepeat::jsonUnserialize($v, $config));
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
        if (isset($this->class)) {
            $out->class = $this->class;
        }
        if (isset($this->geometry)) {
            $out->geometry = $this->geometry;
        }
        if (isset($this->copolymerConnectivity) && [] !== $this->copolymerConnectivity) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_COPOLYMER_CONNECTIVITY) && 1 === count($this->copolymerConnectivity)) {
                $out->copolymerConnectivity = $this->copolymerConnectivity[0];
            } else {
                $out->copolymerConnectivity = $this->copolymerConnectivity;
            }
        }
        if (isset($this->modification) && [] !== $this->modification) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->modification as $v) {
                $val = $v->getValue();
                if (null !== $val) {
                    $hasVals = true;
                    $vals[] = $val;
                } else {
                    $vals[] = null;
                }
                if ($v->_nonValueFieldDefined()) {
                    $hasExts = true;
                    $ext = $v->jsonSerialize();
                    unset($ext->value);
                    $exts[] = $ext;
                } else {
                    $exts[] = null;
                }
            }
            if ($hasVals) {
                $out->modification = $vals;
            }
            if ($hasExts) {
                $out->_modification = $exts;
            }
        }
        if (isset($this->monomerSet) && [] !== $this->monomerSet) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_MONOMER_SET) && 1 === count($this->monomerSet)) {
                $out->monomerSet = $this->monomerSet[0];
            } else {
                $out->monomerSet = $this->monomerSet;
            }
        }
        if (isset($this->repeat) && [] !== $this->repeat) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_REPEAT) && 1 === count($this->repeat)) {
                $out->repeat = $this->repeat[0];
            } else {
                $out->repeat = $this->repeat;
            }
        }
        $out->resourceType = $this->_getResourceType();
        return $out;
    }
}
