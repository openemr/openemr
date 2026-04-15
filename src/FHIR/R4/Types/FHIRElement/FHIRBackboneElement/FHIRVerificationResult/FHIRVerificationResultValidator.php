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
use OpenEMR\FHIR\Validation\Rules\MinOccursRule;
use OpenEMR\FHIR\Validation\TypeValidationsTrait;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
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
class FHIRVerificationResultValidator extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_VERIFICATION_RESULT_DOT_VALIDATOR;

    /* class_default.php:56 */
    public const FIELD_ORGANIZATION = 'organization';
    public const FIELD_IDENTITY_CERTIFICATE = 'identityCertificate';
    public const FIELD_IDENTITY_CERTIFICATE_EXT = '_identityCertificate';
    public const FIELD_ATTESTATION_SIGNATURE = 'attestationSignature';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_ORGANIZATION => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_IDENTITY_CERTIFICATE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reference to the organization validating information.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $organization;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A digital identity certificate associated with the validator.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $identityCertificate;
    /**
     * A signature along with supporting context. The signature may be a digital
     * signature that is cryptographic in nature, or some other signature acceptable to
     * the domain. This other signature may be as simple as a graphical image
     * representing a hand-written signature, or a signature ceremony Different
     * signature approaches have different utilities.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Signed assertion by the validator that they have validated the information.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSignature
     */
    #[FHIRSignature]
    protected FHIRSignature $attestationSignature;

    /* constructor.php:61 */
    /**
     * FHIRVerificationResultValidator Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $organization
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $identityCertificate
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSignature $attestationSignature
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|FHIRReference $organization = null,
                                null|string|FHIRStringPrimitive|FHIRString $identityCertificate = null,
                                null|FHIRSignature $attestationSignature = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $organization) {
            $this->setOrganization($organization);
        }
        if (null !== $identityCertificate) {
            $this->setIdentityCertificate($identityCertificate);
        }
        if (null !== $attestationSignature) {
            $this->setAttestationSignature($attestationSignature);
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
     * Reference to the organization validating information.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getOrganization(): null|FHIRReference
    {
        return $this->organization ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reference to the organization validating information.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $organization
     * @return static
     */
    public function setOrganization(null|FHIRReference $organization): self
    {
        if (null === $organization) {
            unset($this->organization);
            return $this;
        }
        $this->organization = $organization;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A digital identity certificate associated with the validator.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getIdentityCertificate(): null|FHIRString
    {
        return $this->identityCertificate ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A digital identity certificate associated with the validator.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $identityCertificate
     * @return static
     */
    public function setIdentityCertificate(null|string|FHIRStringPrimitive|FHIRString $identityCertificate): self
    {
        if (null === $identityCertificate) {
            unset($this->identityCertificate);
            return $this;
        }
        if (!($identityCertificate instanceof FHIRString)) {
            $identityCertificate = new FHIRString(value: $identityCertificate);
        }
        $this->identityCertificate = $identityCertificate;
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
     * Signed assertion by the validator that they have validated the information.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSignature
     */
    public function getAttestationSignature(): null|FHIRSignature
    {
        return $this->attestationSignature ?? null;
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
     * Signed assertion by the validator that they have validated the information.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSignature $attestationSignature
     * @return static
     */
    public function setAttestationSignature(null|FHIRSignature $attestationSignature): self
    {
        if (null === $attestationSignature) {
            unset($this->attestationSignature);
            return $this;
        }
        $this->attestationSignature = $attestationSignature;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRVerificationResult\FHIRVerificationResultValidator $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRVerificationResult\FHIRVerificationResultValidator
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRVerificationResultValidator)) {
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
            } else if (self::FIELD_ORGANIZATION === $cen) {
                $type->setOrganization(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_IDENTITY_CERTIFICATE === $cen) {
                $type->setIdentityCertificate(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ATTESTATION_SIGNATURE === $cen) {
                $type->setAttestationSignature(FHIRSignature::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_IDENTITY_CERTIFICATE])) {
            if (isset($type->identityCertificate)) {
                $type->identityCertificate->setValue((string)$attributes[self::FIELD_IDENTITY_CERTIFICATE]);
            } else {
                $type->setIdentityCertificate((string)$attributes[self::FIELD_IDENTITY_CERTIFICATE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_IDENTITY_CERTIFICATE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->identityCertificate) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_IDENTITY_CERTIFICATE]) {
            $xw->writeAttribute(self::FIELD_IDENTITY_CERTIFICATE, $this->identityCertificate->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->organization)) {
            $xw->startElement(self::FIELD_ORGANIZATION);
            $this->organization->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->identityCertificate)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_IDENTITY_CERTIFICATE]
                || $this->identityCertificate->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_IDENTITY_CERTIFICATE);
            $this->identityCertificate->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_IDENTITY_CERTIFICATE]);
            $xw->endElement();
        }
        if (isset($this->attestationSignature)) {
            $xw->startElement(self::FIELD_ATTESTATION_SIGNATURE);
            $this->attestationSignature->xmlSerialize($xw, $config);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRVerificationResult\FHIRVerificationResultValidator $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRVerificationResult\FHIRVerificationResultValidator
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
        } else if (!($type instanceof FHIRVerificationResultValidator)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->organization) || property_exists($decoded, self::FIELD_ORGANIZATION)) {
            if (is_array($decoded->organization)) {
                $type->setOrganization(FHIRReference::jsonUnserialize(reset($decoded->organization), $config));
            } else {
                $type->setOrganization(FHIRReference::jsonUnserialize($decoded->organization, $config));
            }
        }
        if (isset($decoded->identityCertificate)
            || isset($decoded->_identityCertificate)
            || property_exists($decoded, self::FIELD_IDENTITY_CERTIFICATE)
            || property_exists($decoded, self::FIELD_IDENTITY_CERTIFICATE_EXT)) {
            $v = $decoded->_identityCertificate ?? new \stdClass();
            $v->value = $decoded->identityCertificate ?? null;
            $type->setIdentityCertificate(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->attestationSignature) || property_exists($decoded, self::FIELD_ATTESTATION_SIGNATURE)) {
            if (is_array($decoded->attestationSignature)) {
                $type->setAttestationSignature(FHIRSignature::jsonUnserialize(reset($decoded->attestationSignature), $config));
            } else {
                $type->setAttestationSignature(FHIRSignature::jsonUnserialize($decoded->attestationSignature, $config));
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
        if (isset($this->organization)) {
            $out->organization = $this->organization;
        }
        if (isset($this->identityCertificate)) {
            if (null !== ($val = $this->identityCertificate->getValue())) {
                $out->identityCertificate = $val;
            }
            if ($this->identityCertificate->_nonValueFieldDefined()) {
                $ext = $this->identityCertificate->jsonSerialize();
                unset($ext->value);
                $out->_identityCertificate = $ext;
            }
        }
        if (isset($this->attestationSignature)) {
            $out->attestationSignature = $this->attestationSignature;
        }
        return $out;
    }
}
