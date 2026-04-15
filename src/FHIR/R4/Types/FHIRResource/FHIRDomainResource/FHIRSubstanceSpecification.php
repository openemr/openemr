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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationMoiety;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationMolecularWeight;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationName;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationProperty;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationRelationship;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationStructure;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
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
 * The detailed description of a substance, typically at a level beyond what is
 * used for prescribing.
 * If the element is present, it must have either a \@value, an \@id, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRSubstanceSpecification extends FHIRDomainResource implements VersionContainedTypeInterface
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_SUBSTANCE_SPECIFICATION;

    /* class_default.php:56 */
    public const FIELD_IDENTIFIER = 'identifier';
    public const FIELD_TYPE = 'type';
    public const FIELD_STATUS = 'status';
    public const FIELD_DOMAIN = 'domain';
    public const FIELD_DESCRIPTION = 'description';
    public const FIELD_DESCRIPTION_EXT = '_description';
    public const FIELD_SOURCE = 'source';
    public const FIELD_COMMENT = 'comment';
    public const FIELD_COMMENT_EXT = '_comment';
    public const FIELD_MOIETY = 'moiety';
    public const FIELD_PROPERTY = 'property';
    public const FIELD_REFERENCE_INFORMATION = 'referenceInformation';
    public const FIELD_STRUCTURE = 'structure';
    public const FIELD_CODE = 'code';
    public const FIELD_NAME = 'name';
    public const FIELD_MOLECULAR_WEIGHT = 'molecularWeight';
    public const FIELD_RELATIONSHIP = 'relationship';
    public const FIELD_NUCLEIC_ACID = 'nucleicAcid';
    public const FIELD_POLYMER = 'polymer';
    public const FIELD_PROTEIN = 'protein';
    public const FIELD_SOURCE_MATERIAL = 'sourceMaterial';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_DESCRIPTION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_COMMENT => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifier by which this substance is known.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier
     */
    #[FHIRIdentifier]
    protected FHIRIdentifier $identifier;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * High level categorization, e.g. polymer or nucleic acid.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $type;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Status of substance within the catalogue e.g. approved.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $status;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the substance applies to only human or veterinary use.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $domain;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Textual description of the substance.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $description;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Supporting literature.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $source;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Textual comment about this record of a substance.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $comment;
    /**
     * The detailed description of a substance, typically at a level beyond what is
     * used for prescribing.
     *
     * Moiety, for structural modifications.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationMoiety>
     */
    #[FHIRSubstanceSpecificationMoiety]
    protected array $moiety;
    /**
     * The detailed description of a substance, typically at a level beyond what is
     * used for prescribing.
     *
     * General specifications for this substance, including how it is related to other
     * substances.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationProperty>
     */
    #[FHIRSubstanceSpecificationProperty]
    protected array $property;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * General information detailing this substance.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $referenceInformation;
    /**
     * The detailed description of a substance, typically at a level beyond what is
     * used for prescribing.
     *
     * Structural information.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationStructure
     */
    #[FHIRSubstanceSpecificationStructure]
    protected FHIRSubstanceSpecificationStructure $structure;
    /**
     * The detailed description of a substance, typically at a level beyond what is
     * used for prescribing.
     *
     * Codes associated with the substance.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationCode>
     */
    #[FHIRSubstanceSpecificationCode]
    protected array $code;
    /**
     * The detailed description of a substance, typically at a level beyond what is
     * used for prescribing.
     *
     * Names applicable to this substance.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationName>
     */
    #[FHIRSubstanceSpecificationName]
    protected array $name;
    /**
     * The detailed description of a substance, typically at a level beyond what is
     * used for prescribing.
     *
     * The molecular weight or weight range (for proteins, polymers or nucleic acids).
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationMolecularWeight>
     */
    #[FHIRSubstanceSpecificationMolecularWeight]
    protected array $molecularWeight;
    /**
     * The detailed description of a substance, typically at a level beyond what is
     * used for prescribing.
     *
     * A link between this substance and another, with details of the relationship.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationRelationship>
     */
    #[FHIRSubstanceSpecificationRelationship]
    protected array $relationship;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Data items specific to nucleic acids.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $nucleicAcid;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Data items specific to polymers.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $polymer;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Data items specific to proteins.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $protein;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Material or taxonomic/anatomical source for the substance.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $sourceMaterial;

    /* constructor.php:61 */
    /**
     * FHIRSubstanceSpecification Constructor
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $id
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta $meta
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $implicitRules
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $language
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative $text
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRResourceContainer>|iterable<\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface> $contained
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier $identifier
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $type
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $status
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $domain
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $description
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $source
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $comment
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationMoiety> $moiety
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationProperty> $property
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $referenceInformation
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationStructure $structure
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationCode> $code
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationName> $name
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationMolecularWeight> $molecularWeight
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationRelationship> $relationship
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $nucleicAcid
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $polymer
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $protein
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $sourceMaterial
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
                                null|FHIRIdentifier $identifier = null,
                                null|FHIRCodeableConcept $type = null,
                                null|FHIRCodeableConcept $status = null,
                                null|FHIRCodeableConcept $domain = null,
                                null|string|FHIRStringPrimitive|FHIRString $description = null,
                                null|iterable $source = null,
                                null|string|FHIRStringPrimitive|FHIRString $comment = null,
                                null|iterable $moiety = null,
                                null|iterable $property = null,
                                null|FHIRReference $referenceInformation = null,
                                null|FHIRSubstanceSpecificationStructure $structure = null,
                                null|iterable $code = null,
                                null|iterable $name = null,
                                null|iterable $molecularWeight = null,
                                null|iterable $relationship = null,
                                null|FHIRReference $nucleicAcid = null,
                                null|FHIRReference $polymer = null,
                                null|FHIRReference $protein = null,
                                null|FHIRReference $sourceMaterial = null,
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
        if (null !== $identifier) {
            $this->setIdentifier($identifier);
        }
        if (null !== $type) {
            $this->setType($type);
        }
        if (null !== $status) {
            $this->setStatus($status);
        }
        if (null !== $domain) {
            $this->setDomain($domain);
        }
        if (null !== $description) {
            $this->setDescription($description);
        }
        if (null !== $source) {
            $this->setSource(...$source);
        }
        if (null !== $comment) {
            $this->setComment($comment);
        }
        if (null !== $moiety) {
            $this->setMoiety(...$moiety);
        }
        if (null !== $property) {
            $this->setProperty(...$property);
        }
        if (null !== $referenceInformation) {
            $this->setReferenceInformation($referenceInformation);
        }
        if (null !== $structure) {
            $this->setStructure($structure);
        }
        if (null !== $code) {
            $this->setCode(...$code);
        }
        if (null !== $name) {
            $this->setName(...$name);
        }
        if (null !== $molecularWeight) {
            $this->setMolecularWeight(...$molecularWeight);
        }
        if (null !== $relationship) {
            $this->setRelationship(...$relationship);
        }
        if (null !== $nucleicAcid) {
            $this->setNucleicAcid($nucleicAcid);
        }
        if (null !== $polymer) {
            $this->setPolymer($polymer);
        }
        if (null !== $protein) {
            $this->setProtein($protein);
        }
        if (null !== $sourceMaterial) {
            $this->setSourceMaterial($sourceMaterial);
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
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifier by which this substance is known.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier
     */
    public function getIdentifier(): null|FHIRIdentifier
    {
        return $this->identifier ?? null;
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifier by which this substance is known.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier $identifier
     * @return static
     */
    public function setIdentifier(null|FHIRIdentifier $identifier): self
    {
        if (null === $identifier) {
            unset($this->identifier);
            return $this;
        }
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * High level categorization, e.g. polymer or nucleic acid.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getType(): null|FHIRCodeableConcept
    {
        return $this->type ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * High level categorization, e.g. polymer or nucleic acid.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $type
     * @return static
     */
    public function setType(null|FHIRCodeableConcept $type): self
    {
        if (null === $type) {
            unset($this->type);
            return $this;
        }
        $this->type = $type;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Status of substance within the catalogue e.g. approved.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getStatus(): null|FHIRCodeableConcept
    {
        return $this->status ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Status of substance within the catalogue e.g. approved.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $status
     * @return static
     */
    public function setStatus(null|FHIRCodeableConcept $status): self
    {
        if (null === $status) {
            unset($this->status);
            return $this;
        }
        $this->status = $status;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the substance applies to only human or veterinary use.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getDomain(): null|FHIRCodeableConcept
    {
        return $this->domain ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the substance applies to only human or veterinary use.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $domain
     * @return static
     */
    public function setDomain(null|FHIRCodeableConcept $domain): self
    {
        if (null === $domain) {
            unset($this->domain);
            return $this;
        }
        $this->domain = $domain;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Textual description of the substance.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getDescription(): null|FHIRString
    {
        return $this->description ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Textual description of the substance.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $description
     * @return static
     */
    public function setDescription(null|string|FHIRStringPrimitive|FHIRString $description): self
    {
        if (null === $description) {
            unset($this->description);
            return $this;
        }
        if (!($description instanceof FHIRString)) {
            $description = new FHIRString(value: $description);
        }
        $this->description = $description;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Supporting literature.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getSource(): array
    {
        return $this->source ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getSourceIterator(): iterable
    {
        if (!isset($this->source)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->source);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Supporting literature.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $source
     * @return static
     */
    public function addSource(FHIRReference $source): self
    {
        if (!isset($this->source)) {
            $this->source = [];
        }
        $this->source[] = $source;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Supporting literature.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$source
     * @return static
     */
    public function setSource(FHIRReference ...$source): self
    {
        if ([] === $source) {
            unset($this->source);
            return $this;
        }
        $this->source = $source;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Textual comment about this record of a substance.
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
     * Textual comment about this record of a substance.
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
     * The detailed description of a substance, typically at a level beyond what is
     * used for prescribing.
     *
     * Moiety, for structural modifications.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationMoiety>
     */
    public function getMoiety(): array
    {
        return $this->moiety ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationMoiety>
     */
    public function getMoietyIterator(): iterable
    {
        if (!isset($this->moiety)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->moiety);
    }

    /**
     * The detailed description of a substance, typically at a level beyond what is
     * used for prescribing.
     *
     * Moiety, for structural modifications.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationMoiety $moiety
     * @return static
     */
    public function addMoiety(FHIRSubstanceSpecificationMoiety $moiety): self
    {
        if (!isset($this->moiety)) {
            $this->moiety = [];
        }
        $this->moiety[] = $moiety;
        return $this;
    }

    /**
     * The detailed description of a substance, typically at a level beyond what is
     * used for prescribing.
     *
     * Moiety, for structural modifications.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationMoiety ...$moiety
     * @return static
     */
    public function setMoiety(FHIRSubstanceSpecificationMoiety ...$moiety): self
    {
        if ([] === $moiety) {
            unset($this->moiety);
            return $this;
        }
        $this->moiety = $moiety;
        return $this;
    }

    /**
     * The detailed description of a substance, typically at a level beyond what is
     * used for prescribing.
     *
     * General specifications for this substance, including how it is related to other
     * substances.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationProperty>
     */
    public function getProperty(): array
    {
        return $this->property ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationProperty>
     */
    public function getPropertyIterator(): iterable
    {
        if (!isset($this->property)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->property);
    }

    /**
     * The detailed description of a substance, typically at a level beyond what is
     * used for prescribing.
     *
     * General specifications for this substance, including how it is related to other
     * substances.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationProperty $property
     * @return static
     */
    public function addProperty(FHIRSubstanceSpecificationProperty $property): self
    {
        if (!isset($this->property)) {
            $this->property = [];
        }
        $this->property[] = $property;
        return $this;
    }

    /**
     * The detailed description of a substance, typically at a level beyond what is
     * used for prescribing.
     *
     * General specifications for this substance, including how it is related to other
     * substances.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationProperty ...$property
     * @return static
     */
    public function setProperty(FHIRSubstanceSpecificationProperty ...$property): self
    {
        if ([] === $property) {
            unset($this->property);
            return $this;
        }
        $this->property = $property;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * General information detailing this substance.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getReferenceInformation(): null|FHIRReference
    {
        return $this->referenceInformation ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * General information detailing this substance.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $referenceInformation
     * @return static
     */
    public function setReferenceInformation(null|FHIRReference $referenceInformation): self
    {
        if (null === $referenceInformation) {
            unset($this->referenceInformation);
            return $this;
        }
        $this->referenceInformation = $referenceInformation;
        return $this;
    }

    /**
     * The detailed description of a substance, typically at a level beyond what is
     * used for prescribing.
     *
     * Structural information.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationStructure
     */
    public function getStructure(): null|FHIRSubstanceSpecificationStructure
    {
        return $this->structure ?? null;
    }

    /**
     * The detailed description of a substance, typically at a level beyond what is
     * used for prescribing.
     *
     * Structural information.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationStructure $structure
     * @return static
     */
    public function setStructure(null|FHIRSubstanceSpecificationStructure $structure): self
    {
        if (null === $structure) {
            unset($this->structure);
            return $this;
        }
        $this->structure = $structure;
        return $this;
    }

    /**
     * The detailed description of a substance, typically at a level beyond what is
     * used for prescribing.
     *
     * Codes associated with the substance.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationCode>
     */
    public function getCode(): array
    {
        return $this->code ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationCode>
     */
    public function getCodeIterator(): iterable
    {
        if (!isset($this->code)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->code);
    }

    /**
     * The detailed description of a substance, typically at a level beyond what is
     * used for prescribing.
     *
     * Codes associated with the substance.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationCode $code
     * @return static
     */
    public function addCode(FHIRSubstanceSpecificationCode $code): self
    {
        if (!isset($this->code)) {
            $this->code = [];
        }
        $this->code[] = $code;
        return $this;
    }

    /**
     * The detailed description of a substance, typically at a level beyond what is
     * used for prescribing.
     *
     * Codes associated with the substance.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationCode ...$code
     * @return static
     */
    public function setCode(FHIRSubstanceSpecificationCode ...$code): self
    {
        if ([] === $code) {
            unset($this->code);
            return $this;
        }
        $this->code = $code;
        return $this;
    }

    /**
     * The detailed description of a substance, typically at a level beyond what is
     * used for prescribing.
     *
     * Names applicable to this substance.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationName>
     */
    public function getName(): array
    {
        return $this->name ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationName>
     */
    public function getNameIterator(): iterable
    {
        if (!isset($this->name)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->name);
    }

    /**
     * The detailed description of a substance, typically at a level beyond what is
     * used for prescribing.
     *
     * Names applicable to this substance.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationName $name
     * @return static
     */
    public function addName(FHIRSubstanceSpecificationName $name): self
    {
        if (!isset($this->name)) {
            $this->name = [];
        }
        $this->name[] = $name;
        return $this;
    }

    /**
     * The detailed description of a substance, typically at a level beyond what is
     * used for prescribing.
     *
     * Names applicable to this substance.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationName ...$name
     * @return static
     */
    public function setName(FHIRSubstanceSpecificationName ...$name): self
    {
        if ([] === $name) {
            unset($this->name);
            return $this;
        }
        $this->name = $name;
        return $this;
    }

    /**
     * The detailed description of a substance, typically at a level beyond what is
     * used for prescribing.
     *
     * The molecular weight or weight range (for proteins, polymers or nucleic acids).
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationMolecularWeight>
     */
    public function getMolecularWeight(): array
    {
        return $this->molecularWeight ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationMolecularWeight>
     */
    public function getMolecularWeightIterator(): iterable
    {
        if (!isset($this->molecularWeight)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->molecularWeight);
    }

    /**
     * The detailed description of a substance, typically at a level beyond what is
     * used for prescribing.
     *
     * The molecular weight or weight range (for proteins, polymers or nucleic acids).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationMolecularWeight $molecularWeight
     * @return static
     */
    public function addMolecularWeight(FHIRSubstanceSpecificationMolecularWeight $molecularWeight): self
    {
        if (!isset($this->molecularWeight)) {
            $this->molecularWeight = [];
        }
        $this->molecularWeight[] = $molecularWeight;
        return $this;
    }

    /**
     * The detailed description of a substance, typically at a level beyond what is
     * used for prescribing.
     *
     * The molecular weight or weight range (for proteins, polymers or nucleic acids).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationMolecularWeight ...$molecularWeight
     * @return static
     */
    public function setMolecularWeight(FHIRSubstanceSpecificationMolecularWeight ...$molecularWeight): self
    {
        if ([] === $molecularWeight) {
            unset($this->molecularWeight);
            return $this;
        }
        $this->molecularWeight = $molecularWeight;
        return $this;
    }

    /**
     * The detailed description of a substance, typically at a level beyond what is
     * used for prescribing.
     *
     * A link between this substance and another, with details of the relationship.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationRelationship>
     */
    public function getRelationship(): array
    {
        return $this->relationship ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationRelationship>
     */
    public function getRelationshipIterator(): iterable
    {
        if (!isset($this->relationship)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->relationship);
    }

    /**
     * The detailed description of a substance, typically at a level beyond what is
     * used for prescribing.
     *
     * A link between this substance and another, with details of the relationship.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationRelationship $relationship
     * @return static
     */
    public function addRelationship(FHIRSubstanceSpecificationRelationship $relationship): self
    {
        if (!isset($this->relationship)) {
            $this->relationship = [];
        }
        $this->relationship[] = $relationship;
        return $this;
    }

    /**
     * The detailed description of a substance, typically at a level beyond what is
     * used for prescribing.
     *
     * A link between this substance and another, with details of the relationship.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationRelationship ...$relationship
     * @return static
     */
    public function setRelationship(FHIRSubstanceSpecificationRelationship ...$relationship): self
    {
        if ([] === $relationship) {
            unset($this->relationship);
            return $this;
        }
        $this->relationship = $relationship;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Data items specific to nucleic acids.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getNucleicAcid(): null|FHIRReference
    {
        return $this->nucleicAcid ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Data items specific to nucleic acids.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $nucleicAcid
     * @return static
     */
    public function setNucleicAcid(null|FHIRReference $nucleicAcid): self
    {
        if (null === $nucleicAcid) {
            unset($this->nucleicAcid);
            return $this;
        }
        $this->nucleicAcid = $nucleicAcid;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Data items specific to polymers.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getPolymer(): null|FHIRReference
    {
        return $this->polymer ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Data items specific to polymers.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $polymer
     * @return static
     */
    public function setPolymer(null|FHIRReference $polymer): self
    {
        if (null === $polymer) {
            unset($this->polymer);
            return $this;
        }
        $this->polymer = $polymer;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Data items specific to proteins.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getProtein(): null|FHIRReference
    {
        return $this->protein ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Data items specific to proteins.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $protein
     * @return static
     */
    public function setProtein(null|FHIRReference $protein): self
    {
        if (null === $protein) {
            unset($this->protein);
            return $this;
        }
        $this->protein = $protein;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Material or taxonomic/anatomical source for the substance.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getSourceMaterial(): null|FHIRReference
    {
        return $this->sourceMaterial ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Material or taxonomic/anatomical source for the substance.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $sourceMaterial
     * @return static
     */
    public function setSourceMaterial(null|FHIRReference $sourceMaterial): self
    {
        if (null === $sourceMaterial) {
            unset($this->sourceMaterial);
            return $this;
        }
        $this->sourceMaterial = $sourceMaterial;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param string|\SimpleXMLElement $element
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSubstanceSpecification $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSubstanceSpecification
     * @throws \Exception
     */
    public static function xmlUnserialize(string|\SimpleXMLElement $element,
                                          null|UnserializeConfig $config = null,
                                          null|ResourceTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRSubstanceSpecification)) {
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
            } else if (self::FIELD_IDENTIFIER === $cen) {
                $type->setIdentifier(FHIRIdentifier::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TYPE === $cen) {
                $type->setType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_STATUS === $cen) {
                $type->setStatus(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DOMAIN === $cen) {
                $type->setDomain(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DESCRIPTION === $cen) {
                $type->setDescription(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SOURCE === $cen) {
                $type->addSource(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_COMMENT === $cen) {
                $type->setComment(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MOIETY === $cen) {
                $type->addMoiety(FHIRSubstanceSpecificationMoiety::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PROPERTY === $cen) {
                $type->addProperty(FHIRSubstanceSpecificationProperty::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REFERENCE_INFORMATION === $cen) {
                $type->setReferenceInformation(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_STRUCTURE === $cen) {
                $type->setStructure(FHIRSubstanceSpecificationStructure::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CODE === $cen) {
                $type->addCode(FHIRSubstanceSpecificationCode::xmlUnserialize($ce, $config));
            } else if (self::FIELD_NAME === $cen) {
                $type->addName(FHIRSubstanceSpecificationName::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MOLECULAR_WEIGHT === $cen) {
                $type->addMolecularWeight(FHIRSubstanceSpecificationMolecularWeight::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RELATIONSHIP === $cen) {
                $type->addRelationship(FHIRSubstanceSpecificationRelationship::xmlUnserialize($ce, $config));
            } else if (self::FIELD_NUCLEIC_ACID === $cen) {
                $type->setNucleicAcid(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_POLYMER === $cen) {
                $type->setPolymer(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PROTEIN === $cen) {
                $type->setProtein(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SOURCE_MATERIAL === $cen) {
                $type->setSourceMaterial(FHIRReference::xmlUnserialize($ce, $config));
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
        if (isset($attributes[self::FIELD_DESCRIPTION])) {
            if (isset($type->description)) {
                $type->description->setValue((string)$attributes[self::FIELD_DESCRIPTION]);
            } else {
                $type->setDescription((string)$attributes[self::FIELD_DESCRIPTION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DESCRIPTION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
            $xw->openRootNode('SubstanceSpecification', $this->_getSourceXMLNS());
        }
        if (isset($this->description) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DESCRIPTION]) {
            $xw->writeAttribute(self::FIELD_DESCRIPTION, $this->description->_getValueAsString());
        }
        if (isset($this->comment) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_COMMENT]) {
            $xw->writeAttribute(self::FIELD_COMMENT, $this->comment->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->identifier)) {
            $xw->startElement(self::FIELD_IDENTIFIER);
            $this->identifier->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->type)) {
            $xw->startElement(self::FIELD_TYPE);
            $this->type->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->status)) {
            $xw->startElement(self::FIELD_STATUS);
            $this->status->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->domain)) {
            $xw->startElement(self::FIELD_DOMAIN);
            $this->domain->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->description)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DESCRIPTION]
                || $this->description->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DESCRIPTION);
            $this->description->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DESCRIPTION]);
            $xw->endElement();
        }
        if (isset($this->source)) {
            foreach ($this->source as $v) {
                $xw->startElement(self::FIELD_SOURCE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->comment)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_COMMENT]
                || $this->comment->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_COMMENT);
            $this->comment->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_COMMENT]);
            $xw->endElement();
        }
        if (isset($this->moiety)) {
            foreach ($this->moiety as $v) {
                $xw->startElement(self::FIELD_MOIETY);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->property)) {
            foreach ($this->property as $v) {
                $xw->startElement(self::FIELD_PROPERTY);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->referenceInformation)) {
            $xw->startElement(self::FIELD_REFERENCE_INFORMATION);
            $this->referenceInformation->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->structure)) {
            $xw->startElement(self::FIELD_STRUCTURE);
            $this->structure->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->code)) {
            foreach ($this->code as $v) {
                $xw->startElement(self::FIELD_CODE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->name)) {
            foreach ($this->name as $v) {
                $xw->startElement(self::FIELD_NAME);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->molecularWeight)) {
            foreach ($this->molecularWeight as $v) {
                $xw->startElement(self::FIELD_MOLECULAR_WEIGHT);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->relationship)) {
            foreach ($this->relationship as $v) {
                $xw->startElement(self::FIELD_RELATIONSHIP);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->nucleicAcid)) {
            $xw->startElement(self::FIELD_NUCLEIC_ACID);
            $this->nucleicAcid->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->polymer)) {
            $xw->startElement(self::FIELD_POLYMER);
            $this->polymer->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->protein)) {
            $xw->startElement(self::FIELD_PROTEIN);
            $this->protein->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->sourceMaterial)) {
            $xw->startElement(self::FIELD_SOURCE_MATERIAL);
            $this->sourceMaterial->xmlSerialize($xw, $config);
            $xw->endElement();
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
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSubstanceSpecification $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSubstanceSpecification
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
        } else if (!($type instanceof FHIRSubstanceSpecification)) {
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
        if (isset($decoded->identifier) || property_exists($decoded, self::FIELD_IDENTIFIER)) {
            if (is_array($decoded->identifier)) {
                $type->setIdentifier(FHIRIdentifier::jsonUnserialize(reset($decoded->identifier), $config));
            } else {
                $type->setIdentifier(FHIRIdentifier::jsonUnserialize($decoded->identifier, $config));
            }
        }
        if (isset($decoded->type) || property_exists($decoded, self::FIELD_TYPE)) {
            if (is_array($decoded->type)) {
                $type->setType(FHIRCodeableConcept::jsonUnserialize(reset($decoded->type), $config));
            } else {
                $type->setType(FHIRCodeableConcept::jsonUnserialize($decoded->type, $config));
            }
        }
        if (isset($decoded->status) || property_exists($decoded, self::FIELD_STATUS)) {
            if (is_array($decoded->status)) {
                $type->setStatus(FHIRCodeableConcept::jsonUnserialize(reset($decoded->status), $config));
            } else {
                $type->setStatus(FHIRCodeableConcept::jsonUnserialize($decoded->status, $config));
            }
        }
        if (isset($decoded->domain) || property_exists($decoded, self::FIELD_DOMAIN)) {
            if (is_array($decoded->domain)) {
                $type->setDomain(FHIRCodeableConcept::jsonUnserialize(reset($decoded->domain), $config));
            } else {
                $type->setDomain(FHIRCodeableConcept::jsonUnserialize($decoded->domain, $config));
            }
        }
        if (isset($decoded->description)
            || isset($decoded->_description)
            || property_exists($decoded, self::FIELD_DESCRIPTION)
            || property_exists($decoded, self::FIELD_DESCRIPTION_EXT)) {
            $v = $decoded->_description ?? new \stdClass();
            $v->value = $decoded->description ?? null;
            $type->setDescription(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->source) || property_exists($decoded, self::FIELD_SOURCE)) {
            if (is_object($decoded->source)) {
                $vals = [$decoded->source];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_SOURCE, true);
            } else {
                $vals = $decoded->source;
            }
            foreach($vals as $v) {
                $type->addSource(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->comment)
            || isset($decoded->_comment)
            || property_exists($decoded, self::FIELD_COMMENT)
            || property_exists($decoded, self::FIELD_COMMENT_EXT)) {
            $v = $decoded->_comment ?? new \stdClass();
            $v->value = $decoded->comment ?? null;
            $type->setComment(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->moiety) || property_exists($decoded, self::FIELD_MOIETY)) {
            if (is_object($decoded->moiety)) {
                $vals = [$decoded->moiety];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_MOIETY, true);
            } else {
                $vals = $decoded->moiety;
            }
            foreach($vals as $v) {
                $type->addMoiety(FHIRSubstanceSpecificationMoiety::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->property) || property_exists($decoded, self::FIELD_PROPERTY)) {
            if (is_object($decoded->property)) {
                $vals = [$decoded->property];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PROPERTY, true);
            } else {
                $vals = $decoded->property;
            }
            foreach($vals as $v) {
                $type->addProperty(FHIRSubstanceSpecificationProperty::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->referenceInformation) || property_exists($decoded, self::FIELD_REFERENCE_INFORMATION)) {
            if (is_array($decoded->referenceInformation)) {
                $type->setReferenceInformation(FHIRReference::jsonUnserialize(reset($decoded->referenceInformation), $config));
            } else {
                $type->setReferenceInformation(FHIRReference::jsonUnserialize($decoded->referenceInformation, $config));
            }
        }
        if (isset($decoded->structure) || property_exists($decoded, self::FIELD_STRUCTURE)) {
            if (is_array($decoded->structure)) {
                $type->setStructure(FHIRSubstanceSpecificationStructure::jsonUnserialize(reset($decoded->structure), $config));
            } else {
                $type->setStructure(FHIRSubstanceSpecificationStructure::jsonUnserialize($decoded->structure, $config));
            }
        }
        if (isset($decoded->code) || property_exists($decoded, self::FIELD_CODE)) {
            if (is_object($decoded->code)) {
                $vals = [$decoded->code];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_CODE, true);
            } else {
                $vals = $decoded->code;
            }
            foreach($vals as $v) {
                $type->addCode(FHIRSubstanceSpecificationCode::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->name) || property_exists($decoded, self::FIELD_NAME)) {
            if (is_object($decoded->name)) {
                $vals = [$decoded->name];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_NAME, true);
            } else {
                $vals = $decoded->name;
            }
            foreach($vals as $v) {
                $type->addName(FHIRSubstanceSpecificationName::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->molecularWeight) || property_exists($decoded, self::FIELD_MOLECULAR_WEIGHT)) {
            if (is_object($decoded->molecularWeight)) {
                $vals = [$decoded->molecularWeight];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_MOLECULAR_WEIGHT, true);
            } else {
                $vals = $decoded->molecularWeight;
            }
            foreach($vals as $v) {
                $type->addMolecularWeight(FHIRSubstanceSpecificationMolecularWeight::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->relationship) || property_exists($decoded, self::FIELD_RELATIONSHIP)) {
            if (is_object($decoded->relationship)) {
                $vals = [$decoded->relationship];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_RELATIONSHIP, true);
            } else {
                $vals = $decoded->relationship;
            }
            foreach($vals as $v) {
                $type->addRelationship(FHIRSubstanceSpecificationRelationship::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->nucleicAcid) || property_exists($decoded, self::FIELD_NUCLEIC_ACID)) {
            if (is_array($decoded->nucleicAcid)) {
                $type->setNucleicAcid(FHIRReference::jsonUnserialize(reset($decoded->nucleicAcid), $config));
            } else {
                $type->setNucleicAcid(FHIRReference::jsonUnserialize($decoded->nucleicAcid, $config));
            }
        }
        if (isset($decoded->polymer) || property_exists($decoded, self::FIELD_POLYMER)) {
            if (is_array($decoded->polymer)) {
                $type->setPolymer(FHIRReference::jsonUnserialize(reset($decoded->polymer), $config));
            } else {
                $type->setPolymer(FHIRReference::jsonUnserialize($decoded->polymer, $config));
            }
        }
        if (isset($decoded->protein) || property_exists($decoded, self::FIELD_PROTEIN)) {
            if (is_array($decoded->protein)) {
                $type->setProtein(FHIRReference::jsonUnserialize(reset($decoded->protein), $config));
            } else {
                $type->setProtein(FHIRReference::jsonUnserialize($decoded->protein, $config));
            }
        }
        if (isset($decoded->sourceMaterial) || property_exists($decoded, self::FIELD_SOURCE_MATERIAL)) {
            if (is_array($decoded->sourceMaterial)) {
                $type->setSourceMaterial(FHIRReference::jsonUnserialize(reset($decoded->sourceMaterial), $config));
            } else {
                $type->setSourceMaterial(FHIRReference::jsonUnserialize($decoded->sourceMaterial, $config));
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
        if (isset($this->identifier)) {
            $out->identifier = $this->identifier;
        }
        if (isset($this->type)) {
            $out->type = $this->type;
        }
        if (isset($this->status)) {
            $out->status = $this->status;
        }
        if (isset($this->domain)) {
            $out->domain = $this->domain;
        }
        if (isset($this->description)) {
            if (null !== ($val = $this->description->getValue())) {
                $out->description = $val;
            }
            if ($this->description->_nonValueFieldDefined()) {
                $ext = $this->description->jsonSerialize();
                unset($ext->value);
                $out->_description = $ext;
            }
        }
        if (isset($this->source) && [] !== $this->source) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_SOURCE) && 1 === count($this->source)) {
                $out->source = $this->source[0];
            } else {
                $out->source = $this->source;
            }
        }
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
        if (isset($this->moiety) && [] !== $this->moiety) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_MOIETY) && 1 === count($this->moiety)) {
                $out->moiety = $this->moiety[0];
            } else {
                $out->moiety = $this->moiety;
            }
        }
        if (isset($this->property) && [] !== $this->property) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PROPERTY) && 1 === count($this->property)) {
                $out->property = $this->property[0];
            } else {
                $out->property = $this->property;
            }
        }
        if (isset($this->referenceInformation)) {
            $out->referenceInformation = $this->referenceInformation;
        }
        if (isset($this->structure)) {
            $out->structure = $this->structure;
        }
        if (isset($this->code) && [] !== $this->code) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_CODE) && 1 === count($this->code)) {
                $out->code = $this->code[0];
            } else {
                $out->code = $this->code;
            }
        }
        if (isset($this->name) && [] !== $this->name) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_NAME) && 1 === count($this->name)) {
                $out->name = $this->name[0];
            } else {
                $out->name = $this->name;
            }
        }
        if (isset($this->molecularWeight) && [] !== $this->molecularWeight) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_MOLECULAR_WEIGHT) && 1 === count($this->molecularWeight)) {
                $out->molecularWeight = $this->molecularWeight[0];
            } else {
                $out->molecularWeight = $this->molecularWeight;
            }
        }
        if (isset($this->relationship) && [] !== $this->relationship) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_RELATIONSHIP) && 1 === count($this->relationship)) {
                $out->relationship = $this->relationship[0];
            } else {
                $out->relationship = $this->relationship;
            }
        }
        if (isset($this->nucleicAcid)) {
            $out->nucleicAcid = $this->nucleicAcid;
        }
        if (isset($this->polymer)) {
            $out->polymer = $this->polymer;
        }
        if (isset($this->protein)) {
            $out->protein = $this->protein;
        }
        if (isset($this->sourceMaterial)) {
            $out->sourceMaterial = $this->sourceMaterial;
        }
        $out->resourceType = $this->_getResourceType();
        return $out;
    }
}
