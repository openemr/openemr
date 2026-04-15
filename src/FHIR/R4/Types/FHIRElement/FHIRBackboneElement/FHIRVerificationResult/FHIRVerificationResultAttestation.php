<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRVerificationResult;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRDatePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSignature;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * Describes validation requirements, source(s), status and dates for one or more
 * elements.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRVerificationResultAttestation extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_VERIFICATION_RESULT_DOT_ATTESTATION;

    /* class_default.php:56 */
    public const FIELD_WHO = 'who';
    public const FIELD_ON_BEHALF_OF = 'onBehalfOf';
    public const FIELD_COMMUNICATION_METHOD = 'communicationMethod';
    public const FIELD_DATE = 'date';
    public const FIELD_DATE_EXT = '_date';
    public const FIELD_SOURCE_IDENTITY_CERTIFICATE = 'sourceIdentityCertificate';
    public const FIELD_SOURCE_IDENTITY_CERTIFICATE_EXT = '_sourceIdentityCertificate';
    public const FIELD_PROXY_IDENTITY_CERTIFICATE = 'proxyIdentityCertificate';
    public const FIELD_PROXY_IDENTITY_CERTIFICATE_EXT = '_proxyIdentityCertificate';
    public const FIELD_PROXY_SIGNATURE = 'proxySignature';
    public const FIELD_SOURCE_SIGNATURE = 'sourceSignature';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_DATE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_SOURCE_IDENTITY_CERTIFICATE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_PROXY_IDENTITY_CERTIFICATE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The individual or organization attesting to information.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $who;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * When the who is asserting on behalf of another (organization or individual).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $onBehalfOf;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The method by which attested information was submitted/retrieved (manual; API;
     * Push).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $communicationMethod;
    /**
     * A date or partial date (e.g. just year or year + month). There is no time zone.
     * The format is a union of the schema types gYear, gYearMonth and date. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date the information was attested to.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate
     */
    #[FHIRDate]
    protected FHIRDate $date;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A digital identity certificate associated with the attestation source.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $sourceIdentityCertificate;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A digital identity certificate associated with the proxy entity submitting
     * attested information on behalf of the attestation source.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $proxyIdentityCertificate;
    /**
     * A signature along with supporting context. The signature may be a digital
     * signature that is cryptographic in nature, or some other signature acceptable to
     * the domain. This other signature may be as simple as a graphical image
     * representing a hand-written signature, or a signature ceremony Different
     * signature approaches have different utilities.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Signed assertion by the proxy entity indicating that they have the right to
     * submit attested information on behalf of the attestation source.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSignature
     */
    #[FHIRSignature]
    protected FHIRSignature $proxySignature;
    /**
     * A signature along with supporting context. The signature may be a digital
     * signature that is cryptographic in nature, or some other signature acceptable to
     * the domain. This other signature may be as simple as a graphical image
     * representing a hand-written signature, or a signature ceremony Different
     * signature approaches have different utilities.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Signed assertion by the attestation source that they have attested to the
     * information.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSignature
     */
    #[FHIRSignature]
    protected FHIRSignature $sourceSignature;

    /* constructor.php:61 */
    /**
     * FHIRVerificationResultAttestation Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $who
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $onBehalfOf
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $communicationMethod
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDatePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate $date
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $sourceIdentityCertificate
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $proxyIdentityCertificate
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSignature $proxySignature
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSignature $sourceSignature
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|FHIRReference $who = null,
                                null|FHIRReference $onBehalfOf = null,
                                null|FHIRCodeableConcept $communicationMethod = null,
                                null|string|\DateTimeInterface|FHIRDatePrimitive|FHIRDate $date = null,
                                null|string|FHIRStringPrimitive|FHIRString $sourceIdentityCertificate = null,
                                null|string|FHIRStringPrimitive|FHIRString $proxyIdentityCertificate = null,
                                null|FHIRSignature $proxySignature = null,
                                null|FHIRSignature $sourceSignature = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $who) {
            $this->setWho($who);
        }
        if (null !== $onBehalfOf) {
            $this->setOnBehalfOf($onBehalfOf);
        }
        if (null !== $communicationMethod) {
            $this->setCommunicationMethod($communicationMethod);
        }
        if (null !== $date) {
            $this->setDate($date);
        }
        if (null !== $sourceIdentityCertificate) {
            $this->setSourceIdentityCertificate($sourceIdentityCertificate);
        }
        if (null !== $proxyIdentityCertificate) {
            $this->setProxyIdentityCertificate($proxyIdentityCertificate);
        }
        if (null !== $proxySignature) {
            $this->setProxySignature($proxySignature);
        }
        if (null !== $sourceSignature) {
            $this->setSourceSignature($sourceSignature);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The individual or organization attesting to information.
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
     * The individual or organization attesting to information.
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
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * When the who is asserting on behalf of another (organization or individual).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getOnBehalfOf(): null|FHIRReference
    {
        return $this->onBehalfOf ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * When the who is asserting on behalf of another (organization or individual).
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $onBehalfOf
     * @return static
     */
    public function setOnBehalfOf(null|FHIRReference $onBehalfOf): self
    {
        if (null === $onBehalfOf) {
            unset($this->onBehalfOf);
            return $this;
        }
        $this->onBehalfOf = $onBehalfOf;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The method by which attested information was submitted/retrieved (manual; API;
     * Push).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getCommunicationMethod(): null|FHIRCodeableConcept
    {
        return $this->communicationMethod ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The method by which attested information was submitted/retrieved (manual; API;
     * Push).
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $communicationMethod
     * @return static
     */
    public function setCommunicationMethod(null|FHIRCodeableConcept $communicationMethod): self
    {
        if (null === $communicationMethod) {
            unset($this->communicationMethod);
            return $this;
        }
        $this->communicationMethod = $communicationMethod;
        return $this;
    }

    /**
     * A date or partial date (e.g. just year or year + month). There is no time zone.
     * The format is a union of the schema types gYear, gYearMonth and date. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date the information was attested to.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate
     */
    public function getDate(): null|FHIRDate
    {
        return $this->date ?? null;
    }

    /**
     * A date or partial date (e.g. just year or year + month). There is no time zone.
     * The format is a union of the schema types gYear, gYearMonth and date. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date the information was attested to.
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDatePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate $date
     * @return static
     */
    public function setDate(null|string|\DateTimeInterface|FHIRDatePrimitive|FHIRDate $date): self
    {
        if (null === $date) {
            unset($this->date);
            return $this;
        }
        if (!($date instanceof FHIRDate)) {
            $date = new FHIRDate(value: $date);
        }
        $this->date = $date;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A digital identity certificate associated with the attestation source.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getSourceIdentityCertificate(): null|FHIRString
    {
        return $this->sourceIdentityCertificate ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A digital identity certificate associated with the attestation source.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $sourceIdentityCertificate
     * @return static
     */
    public function setSourceIdentityCertificate(null|string|FHIRStringPrimitive|FHIRString $sourceIdentityCertificate): self
    {
        if (null === $sourceIdentityCertificate) {
            unset($this->sourceIdentityCertificate);
            return $this;
        }
        if (!($sourceIdentityCertificate instanceof FHIRString)) {
            $sourceIdentityCertificate = new FHIRString(value: $sourceIdentityCertificate);
        }
        $this->sourceIdentityCertificate = $sourceIdentityCertificate;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A digital identity certificate associated with the proxy entity submitting
     * attested information on behalf of the attestation source.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getProxyIdentityCertificate(): null|FHIRString
    {
        return $this->proxyIdentityCertificate ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A digital identity certificate associated with the proxy entity submitting
     * attested information on behalf of the attestation source.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $proxyIdentityCertificate
     * @return static
     */
    public function setProxyIdentityCertificate(null|string|FHIRStringPrimitive|FHIRString $proxyIdentityCertificate): self
    {
        if (null === $proxyIdentityCertificate) {
            unset($this->proxyIdentityCertificate);
            return $this;
        }
        if (!($proxyIdentityCertificate instanceof FHIRString)) {
            $proxyIdentityCertificate = new FHIRString(value: $proxyIdentityCertificate);
        }
        $this->proxyIdentityCertificate = $proxyIdentityCertificate;
        return $this;
    }

    /**
     * A signature along with supporting context. The signature may be a digital
     * signature that is cryptographic in nature, or some other signature acceptable to
     * the domain. This other signature may be as simple as a graphical image
     * representing a hand-written signature, or a signature ceremony Different
     * signature approaches have different utilities.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Signed assertion by the proxy entity indicating that they have the right to
     * submit attested information on behalf of the attestation source.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSignature
     */
    public function getProxySignature(): null|FHIRSignature
    {
        return $this->proxySignature ?? null;
    }

    /**
     * A signature along with supporting context. The signature may be a digital
     * signature that is cryptographic in nature, or some other signature acceptable to
     * the domain. This other signature may be as simple as a graphical image
     * representing a hand-written signature, or a signature ceremony Different
     * signature approaches have different utilities.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Signed assertion by the proxy entity indicating that they have the right to
     * submit attested information on behalf of the attestation source.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSignature $proxySignature
     * @return static
     */
    public function setProxySignature(null|FHIRSignature $proxySignature): self
    {
        if (null === $proxySignature) {
            unset($this->proxySignature);
            return $this;
        }
        $this->proxySignature = $proxySignature;
        return $this;
    }

    /**
     * A signature along with supporting context. The signature may be a digital
     * signature that is cryptographic in nature, or some other signature acceptable to
     * the domain. This other signature may be as simple as a graphical image
     * representing a hand-written signature, or a signature ceremony Different
     * signature approaches have different utilities.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Signed assertion by the attestation source that they have attested to the
     * information.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSignature
     */
    public function getSourceSignature(): null|FHIRSignature
    {
        return $this->sourceSignature ?? null;
    }

    /**
     * A signature along with supporting context. The signature may be a digital
     * signature that is cryptographic in nature, or some other signature acceptable to
     * the domain. This other signature may be as simple as a graphical image
     * representing a hand-written signature, or a signature ceremony Different
     * signature approaches have different utilities.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Signed assertion by the attestation source that they have attested to the
     * information.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSignature $sourceSignature
     * @return static
     */
    public function setSourceSignature(null|FHIRSignature $sourceSignature): self
    {
        if (null === $sourceSignature) {
            unset($this->sourceSignature);
            return $this;
        }
        $this->sourceSignature = $sourceSignature;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRVerificationResult\FHIRVerificationResultAttestation $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRVerificationResult\FHIRVerificationResultAttestation
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRVerificationResultAttestation)) {
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
            } else if (self::FIELD_WHO === $cen) {
                $type->setWho(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ON_BEHALF_OF === $cen) {
                $type->setOnBehalfOf(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_COMMUNICATION_METHOD === $cen) {
                $type->setCommunicationMethod(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DATE === $cen) {
                $type->setDate(FHIRDate::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SOURCE_IDENTITY_CERTIFICATE === $cen) {
                $type->setSourceIdentityCertificate(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PROXY_IDENTITY_CERTIFICATE === $cen) {
                $type->setProxyIdentityCertificate(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PROXY_SIGNATURE === $cen) {
                $type->setProxySignature(FHIRSignature::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SOURCE_SIGNATURE === $cen) {
                $type->setSourceSignature(FHIRSignature::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_DATE])) {
            if (isset($type->date)) {
                $type->date->setValue((string)$attributes[self::FIELD_DATE]);
            } else {
                $type->setDate((string)$attributes[self::FIELD_DATE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DATE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_SOURCE_IDENTITY_CERTIFICATE])) {
            if (isset($type->sourceIdentityCertificate)) {
                $type->sourceIdentityCertificate->setValue((string)$attributes[self::FIELD_SOURCE_IDENTITY_CERTIFICATE]);
            } else {
                $type->setSourceIdentityCertificate((string)$attributes[self::FIELD_SOURCE_IDENTITY_CERTIFICATE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_SOURCE_IDENTITY_CERTIFICATE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_PROXY_IDENTITY_CERTIFICATE])) {
            if (isset($type->proxyIdentityCertificate)) {
                $type->proxyIdentityCertificate->setValue((string)$attributes[self::FIELD_PROXY_IDENTITY_CERTIFICATE]);
            } else {
                $type->setProxyIdentityCertificate((string)$attributes[self::FIELD_PROXY_IDENTITY_CERTIFICATE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_PROXY_IDENTITY_CERTIFICATE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->date) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DATE]) {
            $xw->writeAttribute(self::FIELD_DATE, $this->date->_getValueAsString());
        }
        if (isset($this->sourceIdentityCertificate) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_SOURCE_IDENTITY_CERTIFICATE]) {
            $xw->writeAttribute(self::FIELD_SOURCE_IDENTITY_CERTIFICATE, $this->sourceIdentityCertificate->_getValueAsString());
        }
        if (isset($this->proxyIdentityCertificate) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_PROXY_IDENTITY_CERTIFICATE]) {
            $xw->writeAttribute(self::FIELD_PROXY_IDENTITY_CERTIFICATE, $this->proxyIdentityCertificate->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->who)) {
            $xw->startElement(self::FIELD_WHO);
            $this->who->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->onBehalfOf)) {
            $xw->startElement(self::FIELD_ON_BEHALF_OF);
            $this->onBehalfOf->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->communicationMethod)) {
            $xw->startElement(self::FIELD_COMMUNICATION_METHOD);
            $this->communicationMethod->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->date)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DATE]
                || $this->date->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DATE);
            $this->date->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DATE]);
            $xw->endElement();
        }
        if (isset($this->sourceIdentityCertificate)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_SOURCE_IDENTITY_CERTIFICATE]
                || $this->sourceIdentityCertificate->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_SOURCE_IDENTITY_CERTIFICATE);
            $this->sourceIdentityCertificate->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_SOURCE_IDENTITY_CERTIFICATE]);
            $xw->endElement();
        }
        if (isset($this->proxyIdentityCertificate)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_PROXY_IDENTITY_CERTIFICATE]
                || $this->proxyIdentityCertificate->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_PROXY_IDENTITY_CERTIFICATE);
            $this->proxyIdentityCertificate->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_PROXY_IDENTITY_CERTIFICATE]);
            $xw->endElement();
        }
        if (isset($this->proxySignature)) {
            $xw->startElement(self::FIELD_PROXY_SIGNATURE);
            $this->proxySignature->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->sourceSignature)) {
            $xw->startElement(self::FIELD_SOURCE_SIGNATURE);
            $this->sourceSignature->xmlSerialize($xw, $config);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRVerificationResult\FHIRVerificationResultAttestation $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRVerificationResult\FHIRVerificationResultAttestation
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
        } else if (!($type instanceof FHIRVerificationResultAttestation)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->who) || property_exists($decoded, self::FIELD_WHO)) {
            if (is_array($decoded->who)) {
                $type->setWho(FHIRReference::jsonUnserialize(reset($decoded->who), $config));
            } else {
                $type->setWho(FHIRReference::jsonUnserialize($decoded->who, $config));
            }
        }
        if (isset($decoded->onBehalfOf) || property_exists($decoded, self::FIELD_ON_BEHALF_OF)) {
            if (is_array($decoded->onBehalfOf)) {
                $type->setOnBehalfOf(FHIRReference::jsonUnserialize(reset($decoded->onBehalfOf), $config));
            } else {
                $type->setOnBehalfOf(FHIRReference::jsonUnserialize($decoded->onBehalfOf, $config));
            }
        }
        if (isset($decoded->communicationMethod) || property_exists($decoded, self::FIELD_COMMUNICATION_METHOD)) {
            if (is_array($decoded->communicationMethod)) {
                $type->setCommunicationMethod(FHIRCodeableConcept::jsonUnserialize(reset($decoded->communicationMethod), $config));
            } else {
                $type->setCommunicationMethod(FHIRCodeableConcept::jsonUnserialize($decoded->communicationMethod, $config));
            }
        }
        if (isset($decoded->date)
            || isset($decoded->_date)
            || property_exists($decoded, self::FIELD_DATE)
            || property_exists($decoded, self::FIELD_DATE_EXT)) {
            $v = $decoded->_date ?? new \stdClass();
            $v->value = $decoded->date ?? null;
            $type->setDate(FHIRDate::jsonUnserialize($v, $config));
        }
        if (isset($decoded->sourceIdentityCertificate)
            || isset($decoded->_sourceIdentityCertificate)
            || property_exists($decoded, self::FIELD_SOURCE_IDENTITY_CERTIFICATE)
            || property_exists($decoded, self::FIELD_SOURCE_IDENTITY_CERTIFICATE_EXT)) {
            $v = $decoded->_sourceIdentityCertificate ?? new \stdClass();
            $v->value = $decoded->sourceIdentityCertificate ?? null;
            $type->setSourceIdentityCertificate(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->proxyIdentityCertificate)
            || isset($decoded->_proxyIdentityCertificate)
            || property_exists($decoded, self::FIELD_PROXY_IDENTITY_CERTIFICATE)
            || property_exists($decoded, self::FIELD_PROXY_IDENTITY_CERTIFICATE_EXT)) {
            $v = $decoded->_proxyIdentityCertificate ?? new \stdClass();
            $v->value = $decoded->proxyIdentityCertificate ?? null;
            $type->setProxyIdentityCertificate(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->proxySignature) || property_exists($decoded, self::FIELD_PROXY_SIGNATURE)) {
            if (is_array($decoded->proxySignature)) {
                $type->setProxySignature(FHIRSignature::jsonUnserialize(reset($decoded->proxySignature), $config));
            } else {
                $type->setProxySignature(FHIRSignature::jsonUnserialize($decoded->proxySignature, $config));
            }
        }
        if (isset($decoded->sourceSignature) || property_exists($decoded, self::FIELD_SOURCE_SIGNATURE)) {
            if (is_array($decoded->sourceSignature)) {
                $type->setSourceSignature(FHIRSignature::jsonUnserialize(reset($decoded->sourceSignature), $config));
            } else {
                $type->setSourceSignature(FHIRSignature::jsonUnserialize($decoded->sourceSignature, $config));
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
        if (isset($this->who)) {
            $out->who = $this->who;
        }
        if (isset($this->onBehalfOf)) {
            $out->onBehalfOf = $this->onBehalfOf;
        }
        if (isset($this->communicationMethod)) {
            $out->communicationMethod = $this->communicationMethod;
        }
        if (isset($this->date)) {
            if (null !== ($val = $this->date->getValue())) {
                $out->date = $val;
            }
            if ($this->date->_nonValueFieldDefined()) {
                $ext = $this->date->jsonSerialize();
                unset($ext->value);
                $out->_date = $ext;
            }
        }
        if (isset($this->sourceIdentityCertificate)) {
            if (null !== ($val = $this->sourceIdentityCertificate->getValue())) {
                $out->sourceIdentityCertificate = $val;
            }
            if ($this->sourceIdentityCertificate->_nonValueFieldDefined()) {
                $ext = $this->sourceIdentityCertificate->jsonSerialize();
                unset($ext->value);
                $out->_sourceIdentityCertificate = $ext;
            }
        }
        if (isset($this->proxyIdentityCertificate)) {
            if (null !== ($val = $this->proxyIdentityCertificate->getValue())) {
                $out->proxyIdentityCertificate = $val;
            }
            if ($this->proxyIdentityCertificate->_nonValueFieldDefined()) {
                $ext = $this->proxyIdentityCertificate->jsonSerialize();
                unset($ext->value);
                $out->_proxyIdentityCertificate = $ext;
            }
        }
        if (isset($this->proxySignature)) {
            $out->proxySignature = $this->proxySignature;
        }
        if (isset($this->sourceSignature)) {
            $out->sourceSignature = $this->sourceSignature;
        }
        return $out;
    }
}
