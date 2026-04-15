<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRAuditEvent;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * A record of an event made for purposes of maintaining a security log. Typical
 * uses include detection of intrusion attempts and monitoring for inappropriate
 * usage.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRAuditEventAgent extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_AUDIT_EVENT_DOT_AGENT;

    /* class_default.php:56 */
    public const FIELD_TYPE = 'type';
    public const FIELD_ROLE = 'role';
    public const FIELD_WHO = 'who';
    public const FIELD_ALT_ID = 'altId';
    public const FIELD_ALT_ID_EXT = '_altId';
    public const FIELD_NAME = 'name';
    public const FIELD_NAME_EXT = '_name';
    public const FIELD_REQUESTOR = 'requestor';
    public const FIELD_REQUESTOR_EXT = '_requestor';
    public const FIELD_LOCATION = 'location';
    public const FIELD_POLICY = 'policy';
    public const FIELD_POLICY_EXT = '_policy';
    public const FIELD_MEDIA = 'media';
    public const FIELD_NETWORK = 'network';
    public const FIELD_PURPOSE_OF_USE = 'purposeOfUse';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_REQUESTOR => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_ALT_ID => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_NAME => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_REQUESTOR => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Specification of the participation type the user plays when performing the
     * event.
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
     * The security role that the user was acting under, that come from local codes
     * defined by the access control security system (e.g. RBAC, ABAC) used in the
     * local context.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $role;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reference to who this agent is that was involved in the event.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $who;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Alternative agent Identifier. For a human, this should be a user identifier text
     * string from authentication system. This identifier would be one known to a
     * common authentication system (e.g. single sign-on), if available.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $altId;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Human-meaningful name for the agent.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $name;
    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicator that the user is or is not the requestor, or initiator, for the event
     * being audited.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    #[FHIRBoolean]
    protected FHIRBoolean $requestor;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Where the event occurred.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $location;
    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The policy or plan that authorized the activity being recorded. Typically, a
     * single activity may have multiple applicable policies, such as patient consent,
     * guarantor funding, etc. The policy would also indicate the security token used.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri>
     */
    #[FHIRUri]
    protected array $policy;
    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Type of media involved. Used when the event is about exporting/importing onto
     * media.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding
     */
    #[FHIRCoding]
    protected FHIRCoding $media;
    /**
     * A record of an event made for purposes of maintaining a security log. Typical
     * uses include detection of intrusion attempts and monitoring for inappropriate
     * usage.
     *
     * Logical network location for application activity, if the activity has a network
     * location.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRAuditEvent\FHIRAuditEventNetwork
     */
    #[FHIRAuditEventNetwork]
    protected FHIRAuditEventNetwork $network;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The reason (purpose of use), specific to this agent, that was used during the
     * event being recorded.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $purposeOfUse;

    /* constructor.php:61 */
    /**
     * FHIRAuditEventAgent Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $type
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $role
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $who
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $altId
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $name
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $requestor
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $location
     * @param null|iterable<string>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri> $policy
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding $media
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRAuditEvent\FHIRAuditEventNetwork $network
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $purposeOfUse
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|FHIRCodeableConcept $type = null,
                                null|iterable $role = null,
                                null|FHIRReference $who = null,
                                null|string|FHIRStringPrimitive|FHIRString $altId = null,
                                null|string|FHIRStringPrimitive|FHIRString $name = null,
                                null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $requestor = null,
                                null|FHIRReference $location = null,
                                null|iterable $policy = null,
                                null|FHIRCoding $media = null,
                                null|FHIRAuditEventNetwork $network = null,
                                null|iterable $purposeOfUse = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $type) {
            $this->setType($type);
        }
        if (null !== $role) {
            $this->setRole(...$role);
        }
        if (null !== $who) {
            $this->setWho($who);
        }
        if (null !== $altId) {
            $this->setAltId($altId);
        }
        if (null !== $name) {
            $this->setName($name);
        }
        if (null !== $requestor) {
            $this->setRequestor($requestor);
        }
        if (null !== $location) {
            $this->setLocation($location);
        }
        if (null !== $policy) {
            $this->setPolicy(...$policy);
        }
        if (null !== $media) {
            $this->setMedia($media);
        }
        if (null !== $network) {
            $this->setNetwork($network);
        }
        if (null !== $purposeOfUse) {
            $this->setPurposeOfUse(...$purposeOfUse);
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
     * Specification of the participation type the user plays when performing the
     * event.
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
     * Specification of the participation type the user plays when performing the
     * event.
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
     * The security role that the user was acting under, that come from local codes
     * defined by the access control security system (e.g. RBAC, ABAC) used in the
     * local context.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getRole(): array
    {
        return $this->role ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getRoleIterator(): iterable
    {
        if (!isset($this->role)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->role);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The security role that the user was acting under, that come from local codes
     * defined by the access control security system (e.g. RBAC, ABAC) used in the
     * local context.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $role
     * @return static
     */
    public function addRole(FHIRCodeableConcept $role): self
    {
        if (!isset($this->role)) {
            $this->role = [];
        }
        $this->role[] = $role;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The security role that the user was acting under, that come from local codes
     * defined by the access control security system (e.g. RBAC, ABAC) used in the
     * local context.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$role
     * @return static
     */
    public function setRole(FHIRCodeableConcept ...$role): self
    {
        if ([] === $role) {
            unset($this->role);
            return $this;
        }
        $this->role = $role;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reference to who this agent is that was involved in the event.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getWho(): null|FHIRReference
    {
        return $this->who ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reference to who this agent is that was involved in the event.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $who
     * @return static
     */
    public function setWho(null|FHIRReference $who): self
    {
        if (null === $who) {
            unset($this->who);
            return $this;
        }
        $this->who = $who;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Alternative agent Identifier. For a human, this should be a user identifier text
     * string from authentication system. This identifier would be one known to a
     * common authentication system (e.g. single sign-on), if available.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getAltId(): null|FHIRString
    {
        return $this->altId ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Alternative agent Identifier. For a human, this should be a user identifier text
     * string from authentication system. This identifier would be one known to a
     * common authentication system (e.g. single sign-on), if available.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $altId
     * @return static
     */
    public function setAltId(null|string|FHIRStringPrimitive|FHIRString $altId): self
    {
        if (null === $altId) {
            unset($this->altId);
            return $this;
        }
        if (!($altId instanceof FHIRString)) {
            $altId = new FHIRString(value: $altId);
        }
        $this->altId = $altId;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Human-meaningful name for the agent.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getName(): null|FHIRString
    {
        return $this->name ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Human-meaningful name for the agent.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $name
     * @return static
     */
    public function setName(null|string|FHIRStringPrimitive|FHIRString $name): self
    {
        if (null === $name) {
            unset($this->name);
            return $this;
        }
        if (!($name instanceof FHIRString)) {
            $name = new FHIRString(value: $name);
        }
        $this->name = $name;
        return $this;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicator that the user is or is not the requestor, or initiator, for the event
     * being audited.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    public function getRequestor(): null|FHIRBoolean
    {
        return $this->requestor ?? null;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicator that the user is or is not the requestor, or initiator, for the event
     * being audited.
     *
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $requestor
     * @return static
     */
    public function setRequestor(null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $requestor): self
    {
        if (null === $requestor) {
            unset($this->requestor);
            return $this;
        }
        if (!($requestor instanceof FHIRBoolean)) {
            $requestor = new FHIRBoolean(value: $requestor);
        }
        $this->requestor = $requestor;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Where the event occurred.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getLocation(): null|FHIRReference
    {
        return $this->location ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Where the event occurred.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $location
     * @return static
     */
    public function setLocation(null|FHIRReference $location): self
    {
        if (null === $location) {
            unset($this->location);
            return $this;
        }
        $this->location = $location;
        return $this;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The policy or plan that authorized the activity being recorded. Typically, a
     * single activity may have multiple applicable policies, such as patient consent,
     * guarantor funding, etc. The policy would also indicate the security token used.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri>
     */
    public function getPolicy(): array
    {
        return $this->policy ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri>
     */
    public function getPolicyIterator(): iterable
    {
        if (!isset($this->policy)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->policy);
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The policy or plan that authorized the activity being recorded. Typically, a
     * single activity may have multiple applicable policies, such as patient consent,
     * guarantor funding, etc. The policy would also indicate the security token used.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $policy
     * @return static
     */
    public function addPolicy(string|FHIRUriPrimitive|FHIRUri $policy): self
    {
        if (!($policy instanceof FHIRUri)) {
            $policy = new FHIRUri(value: $policy);
        }
        if (!isset($this->policy)) {
            $this->policy = [];
        }
        $this->policy[] = $policy;
        return $this;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The policy or plan that authorized the activity being recorded. Typically, a
     * single activity may have multiple applicable policies, such as patient consent,
     * guarantor funding, etc. The policy would also indicate the security token used.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri ...$policy
     * @return static
     */
    public function setPolicy(string|FHIRUriPrimitive|FHIRUri ...$policy): self
    {
        if ([] === $policy) {
            unset($this->policy);
            return $this;
        }
        $this->policy = [];
        foreach($policy as $v) {
            if ($v instanceof FHIRUri) {
                $this->policy[] = $v;
            } else {
                $this->policy[] = new FHIRUri(value: $v);
            }
        }
        return $this;
    }

    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Type of media involved. Used when the event is about exporting/importing onto
     * media.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding
     */
    public function getMedia(): null|FHIRCoding
    {
        return $this->media ?? null;
    }

    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Type of media involved. Used when the event is about exporting/importing onto
     * media.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding $media
     * @return static
     */
    public function setMedia(null|FHIRCoding $media): self
    {
        if (null === $media) {
            unset($this->media);
            return $this;
        }
        $this->media = $media;
        return $this;
    }

    /**
     * A record of an event made for purposes of maintaining a security log. Typical
     * uses include detection of intrusion attempts and monitoring for inappropriate
     * usage.
     *
     * Logical network location for application activity, if the activity has a network
     * location.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRAuditEvent\FHIRAuditEventNetwork
     */
    public function getNetwork(): null|FHIRAuditEventNetwork
    {
        return $this->network ?? null;
    }

    /**
     * A record of an event made for purposes of maintaining a security log. Typical
     * uses include detection of intrusion attempts and monitoring for inappropriate
     * usage.
     *
     * Logical network location for application activity, if the activity has a network
     * location.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRAuditEvent\FHIRAuditEventNetwork $network
     * @return static
     */
    public function setNetwork(null|FHIRAuditEventNetwork $network): self
    {
        if (null === $network) {
            unset($this->network);
            return $this;
        }
        $this->network = $network;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The reason (purpose of use), specific to this agent, that was used during the
     * event being recorded.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getPurposeOfUse(): array
    {
        return $this->purposeOfUse ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getPurposeOfUseIterator(): iterable
    {
        if (!isset($this->purposeOfUse)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->purposeOfUse);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The reason (purpose of use), specific to this agent, that was used during the
     * event being recorded.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $purposeOfUse
     * @return static
     */
    public function addPurposeOfUse(FHIRCodeableConcept $purposeOfUse): self
    {
        if (!isset($this->purposeOfUse)) {
            $this->purposeOfUse = [];
        }
        $this->purposeOfUse[] = $purposeOfUse;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The reason (purpose of use), specific to this agent, that was used during the
     * event being recorded.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$purposeOfUse
     * @return static
     */
    public function setPurposeOfUse(FHIRCodeableConcept ...$purposeOfUse): self
    {
        if ([] === $purposeOfUse) {
            unset($this->purposeOfUse);
            return $this;
        }
        $this->purposeOfUse = $purposeOfUse;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRAuditEvent\FHIRAuditEventAgent $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRAuditEvent\FHIRAuditEventAgent
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRAuditEventAgent)) {
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
            } else if (self::FIELD_TYPE === $cen) {
                $type->setType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ROLE === $cen) {
                $type->addRole(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_WHO === $cen) {
                $type->setWho(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ALT_ID === $cen) {
                $type->setAltId(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_NAME === $cen) {
                $type->setName(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REQUESTOR === $cen) {
                $type->setRequestor(FHIRBoolean::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LOCATION === $cen) {
                $type->setLocation(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_POLICY === $cen) {
                $type->addPolicy(FHIRUri::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MEDIA === $cen) {
                $type->setMedia(FHIRCoding::xmlUnserialize($ce, $config));
            } else if (self::FIELD_NETWORK === $cen) {
                $type->setNetwork(FHIRAuditEventNetwork::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PURPOSE_OF_USE === $cen) {
                $type->addPurposeOfUse(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_ALT_ID])) {
            if (isset($type->altId)) {
                $type->altId->setValue((string)$attributes[self::FIELD_ALT_ID]);
            } else {
                $type->setAltId((string)$attributes[self::FIELD_ALT_ID]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_ALT_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_NAME])) {
            if (isset($type->name)) {
                $type->name->setValue((string)$attributes[self::FIELD_NAME]);
            } else {
                $type->setName((string)$attributes[self::FIELD_NAME]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_NAME, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_REQUESTOR])) {
            if (isset($type->requestor)) {
                $type->requestor->setValue((string)$attributes[self::FIELD_REQUESTOR]);
            } else {
                $type->setRequestor((string)$attributes[self::FIELD_REQUESTOR]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_REQUESTOR, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->altId) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_ALT_ID]) {
            $xw->writeAttribute(self::FIELD_ALT_ID, $this->altId->_getValueAsString());
        }
        if (isset($this->name) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_NAME]) {
            $xw->writeAttribute(self::FIELD_NAME, $this->name->_getValueAsString());
        }
        if (isset($this->requestor) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_REQUESTOR]) {
            $xw->writeAttribute(self::FIELD_REQUESTOR, $this->requestor->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->type)) {
            $xw->startElement(self::FIELD_TYPE);
            $this->type->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->role)) {
            foreach ($this->role as $v) {
                $xw->startElement(self::FIELD_ROLE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->who)) {
            $xw->startElement(self::FIELD_WHO);
            $this->who->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->altId)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_ALT_ID]
                || $this->altId->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_ALT_ID);
            $this->altId->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_ALT_ID]);
            $xw->endElement();
        }
        if (isset($this->name)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_NAME]
                || $this->name->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_NAME);
            $this->name->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_NAME]);
            $xw->endElement();
        }
        if (isset($this->requestor)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_REQUESTOR]
                || $this->requestor->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_REQUESTOR);
            $this->requestor->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_REQUESTOR]);
            $xw->endElement();
        }
        if (isset($this->location)) {
            $xw->startElement(self::FIELD_LOCATION);
            $this->location->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->policy) && [] !== $this->policy) {
            foreach($this->policy as $v) {
                $xw->startElement(self::FIELD_POLICY);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->media)) {
            $xw->startElement(self::FIELD_MEDIA);
            $this->media->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->network)) {
            $xw->startElement(self::FIELD_NETWORK);
            $this->network->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->purposeOfUse)) {
            foreach ($this->purposeOfUse as $v) {
                $xw->startElement(self::FIELD_PURPOSE_OF_USE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRAuditEvent\FHIRAuditEventAgent $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRAuditEvent\FHIRAuditEventAgent
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
        } else if (!($type instanceof FHIRAuditEventAgent)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->type) || property_exists($decoded, self::FIELD_TYPE)) {
            if (is_array($decoded->type)) {
                $type->setType(FHIRCodeableConcept::jsonUnserialize(reset($decoded->type), $config));
            } else {
                $type->setType(FHIRCodeableConcept::jsonUnserialize($decoded->type, $config));
            }
        }
        if (isset($decoded->role) || property_exists($decoded, self::FIELD_ROLE)) {
            if (is_object($decoded->role)) {
                $vals = [$decoded->role];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_ROLE, true);
            } else {
                $vals = $decoded->role;
            }
            foreach($vals as $v) {
                $type->addRole(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->who) || property_exists($decoded, self::FIELD_WHO)) {
            if (is_array($decoded->who)) {
                $type->setWho(FHIRReference::jsonUnserialize(reset($decoded->who), $config));
            } else {
                $type->setWho(FHIRReference::jsonUnserialize($decoded->who, $config));
            }
        }
        if (isset($decoded->altId)
            || isset($decoded->_altId)
            || property_exists($decoded, self::FIELD_ALT_ID)
            || property_exists($decoded, self::FIELD_ALT_ID_EXT)) {
            $v = $decoded->_altId ?? new \stdClass();
            $v->value = $decoded->altId ?? null;
            $type->setAltId(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->name)
            || isset($decoded->_name)
            || property_exists($decoded, self::FIELD_NAME)
            || property_exists($decoded, self::FIELD_NAME_EXT)) {
            $v = $decoded->_name ?? new \stdClass();
            $v->value = $decoded->name ?? null;
            $type->setName(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->requestor)
            || isset($decoded->_requestor)
            || property_exists($decoded, self::FIELD_REQUESTOR)
            || property_exists($decoded, self::FIELD_REQUESTOR_EXT)) {
            $v = $decoded->_requestor ?? new \stdClass();
            $v->value = $decoded->requestor ?? null;
            $type->setRequestor(FHIRBoolean::jsonUnserialize($v, $config));
        }
        if (isset($decoded->location) || property_exists($decoded, self::FIELD_LOCATION)) {
            if (is_array($decoded->location)) {
                $type->setLocation(FHIRReference::jsonUnserialize(reset($decoded->location), $config));
            } else {
                $type->setLocation(FHIRReference::jsonUnserialize($decoded->location, $config));
            }
        }
        if (isset($decoded->policy)
            || isset($decoded->_policy)
            || property_exists($decoded, self::FIELD_POLICY)
            || property_exists($decoded, self::FIELD_POLICY_EXT)) {
            $vals = (array)($decoded->policy ?? []);
            $exts = (array)($decoded->_policy ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addPolicy(FHIRUri::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->media) || property_exists($decoded, self::FIELD_MEDIA)) {
            if (is_array($decoded->media)) {
                $type->setMedia(FHIRCoding::jsonUnserialize(reset($decoded->media), $config));
            } else {
                $type->setMedia(FHIRCoding::jsonUnserialize($decoded->media, $config));
            }
        }
        if (isset($decoded->network) || property_exists($decoded, self::FIELD_NETWORK)) {
            if (is_array($decoded->network)) {
                $type->setNetwork(FHIRAuditEventNetwork::jsonUnserialize(reset($decoded->network), $config));
            } else {
                $type->setNetwork(FHIRAuditEventNetwork::jsonUnserialize($decoded->network, $config));
            }
        }
        if (isset($decoded->purposeOfUse) || property_exists($decoded, self::FIELD_PURPOSE_OF_USE)) {
            if (is_object($decoded->purposeOfUse)) {
                $vals = [$decoded->purposeOfUse];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PURPOSE_OF_USE, true);
            } else {
                $vals = $decoded->purposeOfUse;
            }
            foreach($vals as $v) {
                $type->addPurposeOfUse(FHIRCodeableConcept::jsonUnserialize($v, $config));
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
        if (isset($this->type)) {
            $out->type = $this->type;
        }
        if (isset($this->role) && [] !== $this->role) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_ROLE) && 1 === count($this->role)) {
                $out->role = $this->role[0];
            } else {
                $out->role = $this->role;
            }
        }
        if (isset($this->who)) {
            $out->who = $this->who;
        }
        if (isset($this->altId)) {
            if (null !== ($val = $this->altId->getValue())) {
                $out->altId = $val;
            }
            if ($this->altId->_nonValueFieldDefined()) {
                $ext = $this->altId->jsonSerialize();
                unset($ext->value);
                $out->_altId = $ext;
            }
        }
        if (isset($this->name)) {
            if (null !== ($val = $this->name->getValue())) {
                $out->name = $val;
            }
            if ($this->name->_nonValueFieldDefined()) {
                $ext = $this->name->jsonSerialize();
                unset($ext->value);
                $out->_name = $ext;
            }
        }
        if (isset($this->requestor)) {
            if (null !== ($val = $this->requestor->getValue())) {
                $out->requestor = $val;
            }
            if ($this->requestor->_nonValueFieldDefined()) {
                $ext = $this->requestor->jsonSerialize();
                unset($ext->value);
                $out->_requestor = $ext;
            }
        }
        if (isset($this->location)) {
            $out->location = $this->location;
        }
        if (isset($this->policy) && [] !== $this->policy) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->policy as $v) {
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
                $out->policy = $vals;
            }
            if ($hasExts) {
                $out->_policy = $exts;
            }
        }
        if (isset($this->media)) {
            $out->media = $this->media;
        }
        if (isset($this->network)) {
            $out->network = $this->network;
        }
        if (isset($this->purposeOfUse) && [] !== $this->purposeOfUse) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PURPOSE_OF_USE) && 1 === count($this->purposeOfUse)) {
                $out->purposeOfUse = $this->purposeOfUse[0];
            } else {
                $out->purposeOfUse = $this->purposeOfUse;
            }
        }
        return $out;
    }
}
