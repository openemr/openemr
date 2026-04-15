<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRPaymentReconciliation;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRDatePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMoney;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * This resource provides the details including amount of a payment and allocates
 * the payment items being paid.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRPaymentReconciliationDetail extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_PAYMENT_RECONCILIATION_DOT_DETAIL;

    /* class_default.php:56 */
    public const FIELD_IDENTIFIER = 'identifier';
    public const FIELD_PREDECESSOR = 'predecessor';
    public const FIELD_TYPE = 'type';
    public const FIELD_REQUEST = 'request';
    public const FIELD_SUBMITTER = 'submitter';
    public const FIELD_RESPONSE = 'response';
    public const FIELD_DATE = 'date';
    public const FIELD_DATE_EXT = '_date';
    public const FIELD_RESPONSIBLE = 'responsible';
    public const FIELD_PAYEE = 'payee';
    public const FIELD_AMOUNT = 'amount';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_TYPE => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_DATE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Unique identifier for the current payment item for the referenced payable.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier
     */
    #[FHIRIdentifier]
    protected FHIRIdentifier $identifier;
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Unique identifier for the prior payment item for the referenced payable.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier
     */
    #[FHIRIdentifier]
    protected FHIRIdentifier $predecessor;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Code to indicate the nature of the payment.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $type;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A resource, such as a Claim, the evaluation of which could lead to payment.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $request;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The party which submitted the claim or financial transaction.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $submitter;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A resource, such as a ClaimResponse, which contains a commitment to payment.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $response;
    /**
     * A date or partial date (e.g. just year or year + month). There is no time zone.
     * The format is a union of the schema types gYear, gYearMonth and date. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date from the response resource containing a commitment to pay.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate
     */
    #[FHIRDate]
    protected FHIRDate $date;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A reference to the individual who is responsible for inquiries regarding the
     * response and its payment.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $responsible;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The party which is receiving the payment.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $payee;
    /**
     * An amount of economic utility in some recognized currency.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The monetary amount allocated from the total payment to the payable.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMoney
     */
    #[FHIRMoney]
    protected FHIRMoney $amount;

    /* constructor.php:61 */
    /**
     * FHIRPaymentReconciliationDetail Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier $identifier
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier $predecessor
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $type
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $request
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $submitter
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $response
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDatePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate $date
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $responsible
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $payee
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMoney $amount
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|FHIRIdentifier $identifier = null,
                                null|FHIRIdentifier $predecessor = null,
                                null|FHIRCodeableConcept $type = null,
                                null|FHIRReference $request = null,
                                null|FHIRReference $submitter = null,
                                null|FHIRReference $response = null,
                                null|string|\DateTimeInterface|FHIRDatePrimitive|FHIRDate $date = null,
                                null|FHIRReference $responsible = null,
                                null|FHIRReference $payee = null,
                                null|FHIRMoney $amount = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $identifier) {
            $this->setIdentifier($identifier);
        }
        if (null !== $predecessor) {
            $this->setPredecessor($predecessor);
        }
        if (null !== $type) {
            $this->setType($type);
        }
        if (null !== $request) {
            $this->setRequest($request);
        }
        if (null !== $submitter) {
            $this->setSubmitter($submitter);
        }
        if (null !== $response) {
            $this->setResponse($response);
        }
        if (null !== $date) {
            $this->setDate($date);
        }
        if (null !== $responsible) {
            $this->setResponsible($responsible);
        }
        if (null !== $payee) {
            $this->setPayee($payee);
        }
        if (null !== $amount) {
            $this->setAmount($amount);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Unique identifier for the current payment item for the referenced payable.
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
     * Unique identifier for the current payment item for the referenced payable.
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
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Unique identifier for the prior payment item for the referenced payable.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier
     */
    public function getPredecessor(): null|FHIRIdentifier
    {
        return $this->predecessor ?? null;
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Unique identifier for the prior payment item for the referenced payable.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier $predecessor
     * @return static
     */
    public function setPredecessor(null|FHIRIdentifier $predecessor): self
    {
        if (null === $predecessor) {
            unset($this->predecessor);
            return $this;
        }
        $this->predecessor = $predecessor;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Code to indicate the nature of the payment.
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
     * Code to indicate the nature of the payment.
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
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A resource, such as a Claim, the evaluation of which could lead to payment.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getRequest(): null|FHIRReference
    {
        return $this->request ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A resource, such as a Claim, the evaluation of which could lead to payment.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $request
     * @return static
     */
    public function setRequest(null|FHIRReference $request): self
    {
        if (null === $request) {
            unset($this->request);
            return $this;
        }
        $this->request = $request;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The party which submitted the claim or financial transaction.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getSubmitter(): null|FHIRReference
    {
        return $this->submitter ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The party which submitted the claim or financial transaction.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $submitter
     * @return static
     */
    public function setSubmitter(null|FHIRReference $submitter): self
    {
        if (null === $submitter) {
            unset($this->submitter);
            return $this;
        }
        $this->submitter = $submitter;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A resource, such as a ClaimResponse, which contains a commitment to payment.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getResponse(): null|FHIRReference
    {
        return $this->response ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A resource, such as a ClaimResponse, which contains a commitment to payment.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $response
     * @return static
     */
    public function setResponse(null|FHIRReference $response): self
    {
        if (null === $response) {
            unset($this->response);
            return $this;
        }
        $this->response = $response;
        return $this;
    }

    /**
     * A date or partial date (e.g. just year or year + month). There is no time zone.
     * The format is a union of the schema types gYear, gYearMonth and date. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date from the response resource containing a commitment to pay.
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
     * The date from the response resource containing a commitment to pay.
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
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A reference to the individual who is responsible for inquiries regarding the
     * response and its payment.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getResponsible(): null|FHIRReference
    {
        return $this->responsible ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A reference to the individual who is responsible for inquiries regarding the
     * response and its payment.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $responsible
     * @return static
     */
    public function setResponsible(null|FHIRReference $responsible): self
    {
        if (null === $responsible) {
            unset($this->responsible);
            return $this;
        }
        $this->responsible = $responsible;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The party which is receiving the payment.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getPayee(): null|FHIRReference
    {
        return $this->payee ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The party which is receiving the payment.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $payee
     * @return static
     */
    public function setPayee(null|FHIRReference $payee): self
    {
        if (null === $payee) {
            unset($this->payee);
            return $this;
        }
        $this->payee = $payee;
        return $this;
    }

    /**
     * An amount of economic utility in some recognized currency.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The monetary amount allocated from the total payment to the payable.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMoney
     */
    public function getAmount(): null|FHIRMoney
    {
        return $this->amount ?? null;
    }

    /**
     * An amount of economic utility in some recognized currency.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The monetary amount allocated from the total payment to the payable.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMoney $amount
     * @return static
     */
    public function setAmount(null|FHIRMoney $amount): self
    {
        if (null === $amount) {
            unset($this->amount);
            return $this;
        }
        $this->amount = $amount;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRPaymentReconciliation\FHIRPaymentReconciliationDetail $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRPaymentReconciliation\FHIRPaymentReconciliationDetail
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRPaymentReconciliationDetail)) {
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
            } else if (self::FIELD_IDENTIFIER === $cen) {
                $type->setIdentifier(FHIRIdentifier::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PREDECESSOR === $cen) {
                $type->setPredecessor(FHIRIdentifier::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TYPE === $cen) {
                $type->setType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REQUEST === $cen) {
                $type->setRequest(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SUBMITTER === $cen) {
                $type->setSubmitter(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RESPONSE === $cen) {
                $type->setResponse(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DATE === $cen) {
                $type->setDate(FHIRDate::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RESPONSIBLE === $cen) {
                $type->setResponsible(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PAYEE === $cen) {
                $type->setPayee(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_AMOUNT === $cen) {
                $type->setAmount(FHIRMoney::xmlUnserialize($ce, $config));
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
        parent::xmlSerialize($xw, $config);
        if (isset($this->identifier)) {
            $xw->startElement(self::FIELD_IDENTIFIER);
            $this->identifier->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->predecessor)) {
            $xw->startElement(self::FIELD_PREDECESSOR);
            $this->predecessor->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->type)) {
            $xw->startElement(self::FIELD_TYPE);
            $this->type->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->request)) {
            $xw->startElement(self::FIELD_REQUEST);
            $this->request->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->submitter)) {
            $xw->startElement(self::FIELD_SUBMITTER);
            $this->submitter->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->response)) {
            $xw->startElement(self::FIELD_RESPONSE);
            $this->response->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->date)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DATE]
                || $this->date->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DATE);
            $this->date->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DATE]);
            $xw->endElement();
        }
        if (isset($this->responsible)) {
            $xw->startElement(self::FIELD_RESPONSIBLE);
            $this->responsible->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->payee)) {
            $xw->startElement(self::FIELD_PAYEE);
            $this->payee->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->amount)) {
            $xw->startElement(self::FIELD_AMOUNT);
            $this->amount->xmlSerialize($xw, $config);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRPaymentReconciliation\FHIRPaymentReconciliationDetail $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRPaymentReconciliation\FHIRPaymentReconciliationDetail
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
        } else if (!($type instanceof FHIRPaymentReconciliationDetail)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->identifier) || property_exists($decoded, self::FIELD_IDENTIFIER)) {
            if (is_array($decoded->identifier)) {
                $type->setIdentifier(FHIRIdentifier::jsonUnserialize(reset($decoded->identifier), $config));
            } else {
                $type->setIdentifier(FHIRIdentifier::jsonUnserialize($decoded->identifier, $config));
            }
        }
        if (isset($decoded->predecessor) || property_exists($decoded, self::FIELD_PREDECESSOR)) {
            if (is_array($decoded->predecessor)) {
                $type->setPredecessor(FHIRIdentifier::jsonUnserialize(reset($decoded->predecessor), $config));
            } else {
                $type->setPredecessor(FHIRIdentifier::jsonUnserialize($decoded->predecessor, $config));
            }
        }
        if (isset($decoded->type) || property_exists($decoded, self::FIELD_TYPE)) {
            if (is_array($decoded->type)) {
                $type->setType(FHIRCodeableConcept::jsonUnserialize(reset($decoded->type), $config));
            } else {
                $type->setType(FHIRCodeableConcept::jsonUnserialize($decoded->type, $config));
            }
        }
        if (isset($decoded->request) || property_exists($decoded, self::FIELD_REQUEST)) {
            if (is_array($decoded->request)) {
                $type->setRequest(FHIRReference::jsonUnserialize(reset($decoded->request), $config));
            } else {
                $type->setRequest(FHIRReference::jsonUnserialize($decoded->request, $config));
            }
        }
        if (isset($decoded->submitter) || property_exists($decoded, self::FIELD_SUBMITTER)) {
            if (is_array($decoded->submitter)) {
                $type->setSubmitter(FHIRReference::jsonUnserialize(reset($decoded->submitter), $config));
            } else {
                $type->setSubmitter(FHIRReference::jsonUnserialize($decoded->submitter, $config));
            }
        }
        if (isset($decoded->response) || property_exists($decoded, self::FIELD_RESPONSE)) {
            if (is_array($decoded->response)) {
                $type->setResponse(FHIRReference::jsonUnserialize(reset($decoded->response), $config));
            } else {
                $type->setResponse(FHIRReference::jsonUnserialize($decoded->response, $config));
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
        if (isset($decoded->responsible) || property_exists($decoded, self::FIELD_RESPONSIBLE)) {
            if (is_array($decoded->responsible)) {
                $type->setResponsible(FHIRReference::jsonUnserialize(reset($decoded->responsible), $config));
            } else {
                $type->setResponsible(FHIRReference::jsonUnserialize($decoded->responsible, $config));
            }
        }
        if (isset($decoded->payee) || property_exists($decoded, self::FIELD_PAYEE)) {
            if (is_array($decoded->payee)) {
                $type->setPayee(FHIRReference::jsonUnserialize(reset($decoded->payee), $config));
            } else {
                $type->setPayee(FHIRReference::jsonUnserialize($decoded->payee, $config));
            }
        }
        if (isset($decoded->amount) || property_exists($decoded, self::FIELD_AMOUNT)) {
            if (is_array($decoded->amount)) {
                $type->setAmount(FHIRMoney::jsonUnserialize(reset($decoded->amount), $config));
            } else {
                $type->setAmount(FHIRMoney::jsonUnserialize($decoded->amount, $config));
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
        if (isset($this->predecessor)) {
            $out->predecessor = $this->predecessor;
        }
        if (isset($this->type)) {
            $out->type = $this->type;
        }
        if (isset($this->request)) {
            $out->request = $this->request;
        }
        if (isset($this->submitter)) {
            $out->submitter = $this->submitter;
        }
        if (isset($this->response)) {
            $out->response = $this->response;
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
        if (isset($this->responsible)) {
            $out->responsible = $this->responsible;
        }
        if (isset($this->payee)) {
            $out->payee = $this->payee;
        }
        if (isset($this->amount)) {
            $out->amount = $this->amount;
        }
        return $out;
    }
}
