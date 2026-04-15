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
use OpenEMR\FHIR\Validation\Rules\MinOccursRule;
use OpenEMR\FHIR\Validation\TypeValidationsTrait;
use OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRAdministrativeGenderList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRDatePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAddress;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAdministrativeGender;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRRelatedPerson\FHIRRelatedPersonCommunication;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactPoint;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRHumanName;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive;
use OpenEMR\FHIR\Versions\R4\Version;
use OpenEMR\FHIR\Versions\R4\VersionConstants;
use OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface;
use OpenEMR\FHIR\Versions\R4\VersionTypeMap;

/**
 * Information about a person that is involved in the care for a patient, but who
 * is not the target of healthcare, nor has a formal responsibility in the care
 * process.
 * If the element is present, it must have either a \@value, an \@id, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRRelatedPerson extends FHIRDomainResource implements VersionContainedTypeInterface
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_RELATED_PERSON;

    /* class_default.php:56 */
    public const FIELD_IDENTIFIER = 'identifier';
    public const FIELD_ACTIVE = 'active';
    public const FIELD_ACTIVE_EXT = '_active';
    public const FIELD_PATIENT = 'patient';
    public const FIELD_RELATIONSHIP = 'relationship';
    public const FIELD_NAME = 'name';
    public const FIELD_TELECOM = 'telecom';
    public const FIELD_GENDER = 'gender';
    public const FIELD_GENDER_EXT = '_gender';
    public const FIELD_BIRTH_DATE = 'birthDate';
    public const FIELD_BIRTH_DATE_EXT = '_birthDate';
    public const FIELD_ADDRESS = 'address';
    public const FIELD_PHOTO = 'photo';
    public const FIELD_PERIOD = 'period';
    public const FIELD_COMMUNICATION = 'communication';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_PATIENT => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_ACTIVE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_GENDER => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_BIRTH_DATE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifier for a person within a particular scope.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier>
     */
    #[FHIRIdentifier]
    protected array $identifier;
    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether this related person record is in active use.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    #[FHIRBoolean]
    protected FHIRBoolean $active;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The patient this person is related to.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $patient;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The nature of the relationship between a patient and the related person.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $relationship;
    /**
     * A human's name with the ability to identify parts and usage.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A name associated with the person.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRHumanName>
     */
    #[FHIRHumanName]
    protected array $name;
    /**
     * Details for all kinds of technology mediated contact points for a person or
     * organization, including telephone, email, etc.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A contact detail for the person, e.g. a telephone number or an email address.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactPoint>
     */
    #[FHIRContactPoint]
    protected array $telecom;
    /**
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Administrative Gender - the gender that the person is considered to have for
     * administration and record keeping purposes.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAdministrativeGender
     */
    #[FHIRAdministrativeGender]
    protected FHIRAdministrativeGender $gender;
    /**
     * A date or partial date (e.g. just year or year + month). There is no time zone.
     * The format is a union of the schema types gYear, gYearMonth and date. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date on which the related person was born.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate
     */
    #[FHIRDate]
    protected FHIRDate $birthDate;
    /**
     * An address expressed using postal conventions (as opposed to GPS or other
     * location definition formats). This data type may be used to convey addresses for
     * use in delivering mail as well as for visiting locations which might not be
     * valid for mail delivery. There are a variety of postal address formats defined
     * around the world.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Address where the related person can be contacted or visited.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAddress>
     */
    #[FHIRAddress]
    protected array $address;
    /**
     * For referring to data content defined in other formats.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Image of the person.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment>
     */
    #[FHIRAttachment]
    protected array $photo;
    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The period of time during which this relationship is or was active. If there are
     * no dates defined, then the interval is unknown.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    #[FHIRPeriod]
    protected FHIRPeriod $period;
    /**
     * Information about a person that is involved in the care for a patient, but who
     * is not the target of healthcare, nor has a formal responsibility in the care
     * process.
     *
     * A language which may be used to communicate with about the patient's health.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRRelatedPerson\FHIRRelatedPersonCommunication>
     */
    #[FHIRRelatedPersonCommunication]
    protected array $communication;

    /* constructor.php:61 */
    /**
     * FHIRRelatedPerson Constructor
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $id
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta $meta
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $implicitRules
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $language
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative $text
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRResourceContainer>|iterable<\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface> $contained
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier> $identifier
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $active
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $patient
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $relationship
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRHumanName> $name
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactPoint> $telecom
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRAdministrativeGenderList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAdministrativeGender $gender
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDatePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate $birthDate
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAddress> $address
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment> $photo
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $period
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRRelatedPerson\FHIRRelatedPersonCommunication> $communication
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
                                null|iterable $identifier = null,
                                null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $active = null,
                                null|FHIRReference $patient = null,
                                null|iterable $relationship = null,
                                null|iterable $name = null,
                                null|iterable $telecom = null,
                                null|string|FHIRAdministrativeGenderList|FHIRAdministrativeGender $gender = null,
                                null|string|\DateTimeInterface|FHIRDatePrimitive|FHIRDate $birthDate = null,
                                null|iterable $address = null,
                                null|iterable $photo = null,
                                null|FHIRPeriod $period = null,
                                null|iterable $communication = null,
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
            $this->setIdentifier(...$identifier);
        }
        if (null !== $active) {
            $this->setActive($active);
        }
        if (null !== $patient) {
            $this->setPatient($patient);
        }
        if (null !== $relationship) {
            $this->setRelationship(...$relationship);
        }
        if (null !== $name) {
            $this->setName(...$name);
        }
        if (null !== $telecom) {
            $this->setTelecom(...$telecom);
        }
        if (null !== $gender) {
            $this->setGender($gender);
        }
        if (null !== $birthDate) {
            $this->setBirthDate($birthDate);
        }
        if (null !== $address) {
            $this->setAddress(...$address);
        }
        if (null !== $photo) {
            $this->setPhoto(...$photo);
        }
        if (null !== $period) {
            $this->setPeriod($period);
        }
        if (null !== $communication) {
            $this->setCommunication(...$communication);
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
     * Identifier for a person within a particular scope.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier>
     */
    public function getIdentifier(): array
    {
        return $this->identifier ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier>
     */
    public function getIdentifierIterator(): iterable
    {
        if (!isset($this->identifier)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->identifier);
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifier for a person within a particular scope.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier $identifier
     * @return static
     */
    public function addIdentifier(FHIRIdentifier $identifier): self
    {
        if (!isset($this->identifier)) {
            $this->identifier = [];
        }
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifier for a person within a particular scope.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier ...$identifier
     * @return static
     */
    public function setIdentifier(FHIRIdentifier ...$identifier): self
    {
        if ([] === $identifier) {
            unset($this->identifier);
            return $this;
        }
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether this related person record is in active use.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    public function getActive(): null|FHIRBoolean
    {
        return $this->active ?? null;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether this related person record is in active use.
     *
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $active
     * @return static
     */
    public function setActive(null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $active): self
    {
        if (null === $active) {
            unset($this->active);
            return $this;
        }
        if (!($active instanceof FHIRBoolean)) {
            $active = new FHIRBoolean(value: $active);
        }
        $this->active = $active;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The patient this person is related to.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getPatient(): null|FHIRReference
    {
        return $this->patient ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The patient this person is related to.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $patient
     * @return static
     */
    public function setPatient(null|FHIRReference $patient): self
    {
        if (null === $patient) {
            unset($this->patient);
            return $this;
        }
        $this->patient = $patient;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The nature of the relationship between a patient and the related person.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getRelationship(): array
    {
        return $this->relationship ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getRelationshipIterator(): iterable
    {
        if (!isset($this->relationship)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->relationship);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The nature of the relationship between a patient and the related person.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $relationship
     * @return static
     */
    public function addRelationship(FHIRCodeableConcept $relationship): self
    {
        if (!isset($this->relationship)) {
            $this->relationship = [];
        }
        $this->relationship[] = $relationship;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The nature of the relationship between a patient and the related person.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$relationship
     * @return static
     */
    public function setRelationship(FHIRCodeableConcept ...$relationship): self
    {
        if ([] === $relationship) {
            unset($this->relationship);
            return $this;
        }
        $this->relationship = $relationship;
        return $this;
    }

    /**
     * A human's name with the ability to identify parts and usage.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A name associated with the person.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRHumanName>
     */
    public function getName(): array
    {
        return $this->name ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRHumanName>
     */
    public function getNameIterator(): iterable
    {
        if (!isset($this->name)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->name);
    }

    /**
     * A human's name with the ability to identify parts and usage.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A name associated with the person.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRHumanName $name
     * @return static
     */
    public function addName(FHIRHumanName $name): self
    {
        if (!isset($this->name)) {
            $this->name = [];
        }
        $this->name[] = $name;
        return $this;
    }

    /**
     * A human's name with the ability to identify parts and usage.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A name associated with the person.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRHumanName ...$name
     * @return static
     */
    public function setName(FHIRHumanName ...$name): self
    {
        if ([] === $name) {
            unset($this->name);
            return $this;
        }
        $this->name = $name;
        return $this;
    }

    /**
     * Details for all kinds of technology mediated contact points for a person or
     * organization, including telephone, email, etc.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A contact detail for the person, e.g. a telephone number or an email address.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactPoint>
     */
    public function getTelecom(): array
    {
        return $this->telecom ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactPoint>
     */
    public function getTelecomIterator(): iterable
    {
        if (!isset($this->telecom)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->telecom);
    }

    /**
     * Details for all kinds of technology mediated contact points for a person or
     * organization, including telephone, email, etc.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A contact detail for the person, e.g. a telephone number or an email address.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactPoint $telecom
     * @return static
     */
    public function addTelecom(FHIRContactPoint $telecom): self
    {
        if (!isset($this->telecom)) {
            $this->telecom = [];
        }
        $this->telecom[] = $telecom;
        return $this;
    }

    /**
     * Details for all kinds of technology mediated contact points for a person or
     * organization, including telephone, email, etc.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A contact detail for the person, e.g. a telephone number or an email address.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactPoint ...$telecom
     * @return static
     */
    public function setTelecom(FHIRContactPoint ...$telecom): self
    {
        if ([] === $telecom) {
            unset($this->telecom);
            return $this;
        }
        $this->telecom = $telecom;
        return $this;
    }

    /**
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Administrative Gender - the gender that the person is considered to have for
     * administration and record keeping purposes.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAdministrativeGender
     */
    public function getGender(): null|FHIRAdministrativeGender
    {
        return $this->gender ?? null;
    }

    /**
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Administrative Gender - the gender that the person is considered to have for
     * administration and record keeping purposes.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRAdministrativeGenderList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAdministrativeGender $gender
     * @return static
     */
    public function setGender(null|string|FHIRAdministrativeGenderList|FHIRAdministrativeGender $gender): self
    {
        if (null === $gender) {
            unset($this->gender);
            return $this;
        }
        if (!($gender instanceof FHIRAdministrativeGender)) {
            $gender = new FHIRAdministrativeGender(value: $gender);
        }
        $this->gender = $gender;
        return $this;
    }

    /**
     * A date or partial date (e.g. just year or year + month). There is no time zone.
     * The format is a union of the schema types gYear, gYearMonth and date. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date on which the related person was born.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate
     */
    public function getBirthDate(): null|FHIRDate
    {
        return $this->birthDate ?? null;
    }

    /**
     * A date or partial date (e.g. just year or year + month). There is no time zone.
     * The format is a union of the schema types gYear, gYearMonth and date. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date on which the related person was born.
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDatePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate $birthDate
     * @return static
     */
    public function setBirthDate(null|string|\DateTimeInterface|FHIRDatePrimitive|FHIRDate $birthDate): self
    {
        if (null === $birthDate) {
            unset($this->birthDate);
            return $this;
        }
        if (!($birthDate instanceof FHIRDate)) {
            $birthDate = new FHIRDate(value: $birthDate);
        }
        $this->birthDate = $birthDate;
        return $this;
    }

    /**
     * An address expressed using postal conventions (as opposed to GPS or other
     * location definition formats). This data type may be used to convey addresses for
     * use in delivering mail as well as for visiting locations which might not be
     * valid for mail delivery. There are a variety of postal address formats defined
     * around the world.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Address where the related person can be contacted or visited.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAddress>
     */
    public function getAddress(): array
    {
        return $this->address ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAddress>
     */
    public function getAddressIterator(): iterable
    {
        if (!isset($this->address)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->address);
    }

    /**
     * An address expressed using postal conventions (as opposed to GPS or other
     * location definition formats). This data type may be used to convey addresses for
     * use in delivering mail as well as for visiting locations which might not be
     * valid for mail delivery. There are a variety of postal address formats defined
     * around the world.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Address where the related person can be contacted or visited.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAddress $address
     * @return static
     */
    public function addAddress(FHIRAddress $address): self
    {
        if (!isset($this->address)) {
            $this->address = [];
        }
        $this->address[] = $address;
        return $this;
    }

    /**
     * An address expressed using postal conventions (as opposed to GPS or other
     * location definition formats). This data type may be used to convey addresses for
     * use in delivering mail as well as for visiting locations which might not be
     * valid for mail delivery. There are a variety of postal address formats defined
     * around the world.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Address where the related person can be contacted or visited.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAddress ...$address
     * @return static
     */
    public function setAddress(FHIRAddress ...$address): self
    {
        if ([] === $address) {
            unset($this->address);
            return $this;
        }
        $this->address = $address;
        return $this;
    }

    /**
     * For referring to data content defined in other formats.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Image of the person.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment>
     */
    public function getPhoto(): array
    {
        return $this->photo ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment>
     */
    public function getPhotoIterator(): iterable
    {
        if (!isset($this->photo)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->photo);
    }

    /**
     * For referring to data content defined in other formats.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Image of the person.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment $photo
     * @return static
     */
    public function addPhoto(FHIRAttachment $photo): self
    {
        if (!isset($this->photo)) {
            $this->photo = [];
        }
        $this->photo[] = $photo;
        return $this;
    }

    /**
     * For referring to data content defined in other formats.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Image of the person.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment ...$photo
     * @return static
     */
    public function setPhoto(FHIRAttachment ...$photo): self
    {
        if ([] === $photo) {
            unset($this->photo);
            return $this;
        }
        $this->photo = $photo;
        return $this;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The period of time during which this relationship is or was active. If there are
     * no dates defined, then the interval is unknown.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    public function getPeriod(): null|FHIRPeriod
    {
        return $this->period ?? null;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The period of time during which this relationship is or was active. If there are
     * no dates defined, then the interval is unknown.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $period
     * @return static
     */
    public function setPeriod(null|FHIRPeriod $period): self
    {
        if (null === $period) {
            unset($this->period);
            return $this;
        }
        $this->period = $period;
        return $this;
    }

    /**
     * Information about a person that is involved in the care for a patient, but who
     * is not the target of healthcare, nor has a formal responsibility in the care
     * process.
     *
     * A language which may be used to communicate with about the patient's health.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRRelatedPerson\FHIRRelatedPersonCommunication>
     */
    public function getCommunication(): array
    {
        return $this->communication ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRRelatedPerson\FHIRRelatedPersonCommunication>
     */
    public function getCommunicationIterator(): iterable
    {
        if (!isset($this->communication)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->communication);
    }

    /**
     * Information about a person that is involved in the care for a patient, but who
     * is not the target of healthcare, nor has a formal responsibility in the care
     * process.
     *
     * A language which may be used to communicate with about the patient's health.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRRelatedPerson\FHIRRelatedPersonCommunication $communication
     * @return static
     */
    public function addCommunication(FHIRRelatedPersonCommunication $communication): self
    {
        if (!isset($this->communication)) {
            $this->communication = [];
        }
        $this->communication[] = $communication;
        return $this;
    }

    /**
     * Information about a person that is involved in the care for a patient, but who
     * is not the target of healthcare, nor has a formal responsibility in the care
     * process.
     *
     * A language which may be used to communicate with about the patient's health.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRRelatedPerson\FHIRRelatedPersonCommunication ...$communication
     * @return static
     */
    public function setCommunication(FHIRRelatedPersonCommunication ...$communication): self
    {
        if ([] === $communication) {
            unset($this->communication);
            return $this;
        }
        $this->communication = $communication;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param string|\SimpleXMLElement $element
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRRelatedPerson $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRRelatedPerson
     * @throws \Exception
     */
    public static function xmlUnserialize(string|\SimpleXMLElement $element,
                                          null|UnserializeConfig $config = null,
                                          null|ResourceTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRRelatedPerson)) {
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
                $type->addIdentifier(FHIRIdentifier::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ACTIVE === $cen) {
                $type->setActive(FHIRBoolean::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PATIENT === $cen) {
                $type->setPatient(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RELATIONSHIP === $cen) {
                $type->addRelationship(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_NAME === $cen) {
                $type->addName(FHIRHumanName::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TELECOM === $cen) {
                $type->addTelecom(FHIRContactPoint::xmlUnserialize($ce, $config));
            } else if (self::FIELD_GENDER === $cen) {
                $type->setGender(FHIRAdministrativeGender::xmlUnserialize($ce, $config));
            } else if (self::FIELD_BIRTH_DATE === $cen) {
                $type->setBirthDate(FHIRDate::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ADDRESS === $cen) {
                $type->addAddress(FHIRAddress::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PHOTO === $cen) {
                $type->addPhoto(FHIRAttachment::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PERIOD === $cen) {
                $type->setPeriod(FHIRPeriod::xmlUnserialize($ce, $config));
            } else if (self::FIELD_COMMUNICATION === $cen) {
                $type->addCommunication(FHIRRelatedPersonCommunication::xmlUnserialize($ce, $config));
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
        if (isset($attributes[self::FIELD_ACTIVE])) {
            if (isset($type->active)) {
                $type->active->setValue((string)$attributes[self::FIELD_ACTIVE]);
            } else {
                $type->setActive((string)$attributes[self::FIELD_ACTIVE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_ACTIVE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_GENDER])) {
            if (isset($type->gender)) {
                $type->gender->setValue((string)$attributes[self::FIELD_GENDER]);
            } else {
                $type->setGender((string)$attributes[self::FIELD_GENDER]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_GENDER, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_BIRTH_DATE])) {
            if (isset($type->birthDate)) {
                $type->birthDate->setValue((string)$attributes[self::FIELD_BIRTH_DATE]);
            } else {
                $type->setBirthDate((string)$attributes[self::FIELD_BIRTH_DATE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_BIRTH_DATE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
            $xw->openRootNode('RelatedPerson', $this->_getSourceXMLNS());
        }
        if (isset($this->active) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_ACTIVE]) {
            $xw->writeAttribute(self::FIELD_ACTIVE, $this->active->_getValueAsString());
        }
        if (isset($this->gender) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_GENDER]) {
            $xw->writeAttribute(self::FIELD_GENDER, $this->gender->_getValueAsString());
        }
        if (isset($this->birthDate) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_BIRTH_DATE]) {
            $xw->writeAttribute(self::FIELD_BIRTH_DATE, $this->birthDate->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->identifier)) {
            foreach ($this->identifier as $v) {
                $xw->startElement(self::FIELD_IDENTIFIER);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->active)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_ACTIVE]
                || $this->active->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_ACTIVE);
            $this->active->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_ACTIVE]);
            $xw->endElement();
        }
        if (isset($this->patient)) {
            $xw->startElement(self::FIELD_PATIENT);
            $this->patient->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->relationship)) {
            foreach ($this->relationship as $v) {
                $xw->startElement(self::FIELD_RELATIONSHIP);
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
        if (isset($this->telecom)) {
            foreach ($this->telecom as $v) {
                $xw->startElement(self::FIELD_TELECOM);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->gender)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_GENDER]
                || $this->gender->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_GENDER);
            $this->gender->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_GENDER]);
            $xw->endElement();
        }
        if (isset($this->birthDate)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_BIRTH_DATE]
                || $this->birthDate->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_BIRTH_DATE);
            $this->birthDate->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_BIRTH_DATE]);
            $xw->endElement();
        }
        if (isset($this->address)) {
            foreach ($this->address as $v) {
                $xw->startElement(self::FIELD_ADDRESS);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->photo)) {
            foreach ($this->photo as $v) {
                $xw->startElement(self::FIELD_PHOTO);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->period)) {
            $xw->startElement(self::FIELD_PERIOD);
            $this->period->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->communication)) {
            foreach ($this->communication as $v) {
                $xw->startElement(self::FIELD_COMMUNICATION);
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
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRRelatedPerson $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRRelatedPerson
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
        } else if (!($type instanceof FHIRRelatedPerson)) {
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
            if (is_object($decoded->identifier)) {
                $vals = [$decoded->identifier];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_IDENTIFIER, true);
            } else {
                $vals = $decoded->identifier;
            }
            foreach($vals as $v) {
                $type->addIdentifier(FHIRIdentifier::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->active)
            || isset($decoded->_active)
            || property_exists($decoded, self::FIELD_ACTIVE)
            || property_exists($decoded, self::FIELD_ACTIVE_EXT)) {
            $v = $decoded->_active ?? new \stdClass();
            $v->value = $decoded->active ?? null;
            $type->setActive(FHIRBoolean::jsonUnserialize($v, $config));
        }
        if (isset($decoded->patient) || property_exists($decoded, self::FIELD_PATIENT)) {
            if (is_array($decoded->patient)) {
                $type->setPatient(FHIRReference::jsonUnserialize(reset($decoded->patient), $config));
            } else {
                $type->setPatient(FHIRReference::jsonUnserialize($decoded->patient, $config));
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
                $type->addRelationship(FHIRCodeableConcept::jsonUnserialize($v, $config));
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
                $type->addName(FHIRHumanName::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->telecom) || property_exists($decoded, self::FIELD_TELECOM)) {
            if (is_object($decoded->telecom)) {
                $vals = [$decoded->telecom];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_TELECOM, true);
            } else {
                $vals = $decoded->telecom;
            }
            foreach($vals as $v) {
                $type->addTelecom(FHIRContactPoint::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->gender)
            || isset($decoded->_gender)
            || property_exists($decoded, self::FIELD_GENDER)
            || property_exists($decoded, self::FIELD_GENDER_EXT)) {
            $v = $decoded->_gender ?? new \stdClass();
            $v->value = $decoded->gender ?? null;
            $type->setGender(FHIRAdministrativeGender::jsonUnserialize($v, $config));
        }
        if (isset($decoded->birthDate)
            || isset($decoded->_birthDate)
            || property_exists($decoded, self::FIELD_BIRTH_DATE)
            || property_exists($decoded, self::FIELD_BIRTH_DATE_EXT)) {
            $v = $decoded->_birthDate ?? new \stdClass();
            $v->value = $decoded->birthDate ?? null;
            $type->setBirthDate(FHIRDate::jsonUnserialize($v, $config));
        }
        if (isset($decoded->address) || property_exists($decoded, self::FIELD_ADDRESS)) {
            if (is_object($decoded->address)) {
                $vals = [$decoded->address];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_ADDRESS, true);
            } else {
                $vals = $decoded->address;
            }
            foreach($vals as $v) {
                $type->addAddress(FHIRAddress::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->photo) || property_exists($decoded, self::FIELD_PHOTO)) {
            if (is_object($decoded->photo)) {
                $vals = [$decoded->photo];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PHOTO, true);
            } else {
                $vals = $decoded->photo;
            }
            foreach($vals as $v) {
                $type->addPhoto(FHIRAttachment::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->period) || property_exists($decoded, self::FIELD_PERIOD)) {
            if (is_array($decoded->period)) {
                $type->setPeriod(FHIRPeriod::jsonUnserialize(reset($decoded->period), $config));
            } else {
                $type->setPeriod(FHIRPeriod::jsonUnserialize($decoded->period, $config));
            }
        }
        if (isset($decoded->communication) || property_exists($decoded, self::FIELD_COMMUNICATION)) {
            if (is_object($decoded->communication)) {
                $vals = [$decoded->communication];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_COMMUNICATION, true);
            } else {
                $vals = $decoded->communication;
            }
            foreach($vals as $v) {
                $type->addCommunication(FHIRRelatedPersonCommunication::jsonUnserialize($v, $config));
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
        if (isset($this->identifier) && [] !== $this->identifier) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_IDENTIFIER) && 1 === count($this->identifier)) {
                $out->identifier = $this->identifier[0];
            } else {
                $out->identifier = $this->identifier;
            }
        }
        if (isset($this->active)) {
            if (null !== ($val = $this->active->getValue())) {
                $out->active = $val;
            }
            if ($this->active->_nonValueFieldDefined()) {
                $ext = $this->active->jsonSerialize();
                unset($ext->value);
                $out->_active = $ext;
            }
        }
        if (isset($this->patient)) {
            $out->patient = $this->patient;
        }
        if (isset($this->relationship) && [] !== $this->relationship) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_RELATIONSHIP) && 1 === count($this->relationship)) {
                $out->relationship = $this->relationship[0];
            } else {
                $out->relationship = $this->relationship;
            }
        }
        if (isset($this->name) && [] !== $this->name) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_NAME) && 1 === count($this->name)) {
                $out->name = $this->name[0];
            } else {
                $out->name = $this->name;
            }
        }
        if (isset($this->telecom) && [] !== $this->telecom) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_TELECOM) && 1 === count($this->telecom)) {
                $out->telecom = $this->telecom[0];
            } else {
                $out->telecom = $this->telecom;
            }
        }
        if (isset($this->gender)) {
            if (null !== ($val = $this->gender->getValue())) {
                $out->gender = $val;
            }
            if ($this->gender->_nonValueFieldDefined()) {
                $ext = $this->gender->jsonSerialize();
                unset($ext->value);
                $out->_gender = $ext;
            }
        }
        if (isset($this->birthDate)) {
            if (null !== ($val = $this->birthDate->getValue())) {
                $out->birthDate = $val;
            }
            if ($this->birthDate->_nonValueFieldDefined()) {
                $ext = $this->birthDate->jsonSerialize();
                unset($ext->value);
                $out->_birthDate = $ext;
            }
        }
        if (isset($this->address) && [] !== $this->address) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_ADDRESS) && 1 === count($this->address)) {
                $out->address = $this->address[0];
            } else {
                $out->address = $this->address;
            }
        }
        if (isset($this->photo) && [] !== $this->photo) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PHOTO) && 1 === count($this->photo)) {
                $out->photo = $this->photo[0];
            } else {
                $out->photo = $this->photo;
            }
        }
        if (isset($this->period)) {
            $out->period = $this->period;
        }
        if (isset($this->communication) && [] !== $this->communication) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_COMMUNICATION) && 1 === count($this->communication)) {
                $out->communication = $this->communication[0];
            } else {
                $out->communication = $this->communication;
            }
        }
        $out->resourceType = $this->_getResourceType();
        return $out;
    }
}
