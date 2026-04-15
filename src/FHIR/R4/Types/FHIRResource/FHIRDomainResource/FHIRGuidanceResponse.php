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
use OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRGuidanceResponseStatusList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDataRequirement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRGuidanceResponseStatus;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative;
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
 * A guidance response is the formal response to a guidance request, including any
 * output parameters returned by the evaluation, as well as the description of any
 * proposed actions to be taken.
 * If the element is present, it must have either a \@value, an \@id, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRGuidanceResponse extends FHIRDomainResource implements VersionContainedTypeInterface
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_GUIDANCE_RESPONSE;

    /* class_default.php:56 */
    public const FIELD_REQUEST_IDENTIFIER = 'requestIdentifier';
    public const FIELD_IDENTIFIER = 'identifier';
    public const FIELD_MODULE_URI = 'moduleUri';
    public const FIELD_MODULE_URI_EXT = '_moduleUri';
    public const FIELD_MODULE_CANONICAL = 'moduleCanonical';
    public const FIELD_MODULE_CANONICAL_EXT = '_moduleCanonical';
    public const FIELD_MODULE_CODEABLE_CONCEPT = 'moduleCodeableConcept';
    public const FIELD_STATUS = 'status';
    public const FIELD_STATUS_EXT = '_status';
    public const FIELD_SUBJECT = 'subject';
    public const FIELD_ENCOUNTER = 'encounter';
    public const FIELD_OCCURRENCE_DATE_TIME = 'occurrenceDateTime';
    public const FIELD_OCCURRENCE_DATE_TIME_EXT = '_occurrenceDateTime';
    public const FIELD_PERFORMER = 'performer';
    public const FIELD_REASON_CODE = 'reasonCode';
    public const FIELD_REASON_REFERENCE = 'reasonReference';
    public const FIELD_NOTE = 'note';
    public const FIELD_EVALUATION_MESSAGE = 'evaluationMessage';
    public const FIELD_OUTPUT_PARAMETERS = 'outputParameters';
    public const FIELD_RESULT = 'result';
    public const FIELD_DATA_REQUIREMENT = 'dataRequirement';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_STATUS => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_MODULE_URI => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_MODULE_CANONICAL => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_STATUS => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_OCCURRENCE_DATE_TIME => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The identifier of the request associated with this response. If an identifier
     * was given as part of the request, it will be reproduced here to enable the
     * requester to more easily identify the response in a multi-request scenario.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier
     */
    #[FHIRIdentifier]
    protected FHIRIdentifier $requestIdentifier;
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Allows a service to provide unique, business identifiers for the response.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier>
     */
    #[FHIRIdentifier]
    protected array $identifier;
    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An identifier, CodeableConcept or canonical reference to the guidance that was
     * requested. (choose any one of module*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri
     */
    #[FHIRUri]
    protected FHIRUri $moduleUri;
    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * An identifier, CodeableConcept or canonical reference to the guidance that was
     * requested. (choose any one of module*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical
     */
    #[FHIRCanonical]
    protected FHIRCanonical $moduleCanonical;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * An identifier, CodeableConcept or canonical reference to the guidance that was
     * requested. (choose any one of module*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $moduleCodeableConcept;
    /**
     * The status of a guidance response.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The status of the response. If the evaluation is completed successfully, the
     * status will indicate success. However, in order to complete the evaluation, the
     * engine may require more information. In this case, the status will be
     * data-required, and the response will contain a description of the additional
     * required information. If the evaluation completed successfully, but the engine
     * determines that a potentially more accurate response could be provided if more
     * data was available, the status will be data-requested, and the response will
     * contain a description of the additional requested information.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRGuidanceResponseStatus
     */
    #[FHIRGuidanceResponseStatus]
    protected FHIRGuidanceResponseStatus $status;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The patient for which the request was processed.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $subject;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The encounter during which this response was created or to which the creation of
     * this record is tightly associated.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $encounter;
    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates when the guidance response was processed.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    #[FHIRDateTime]
    protected FHIRDateTime $occurrenceDateTime;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Provides a reference to the device that performed the guidance.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $performer;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Describes the reason for the guidance response in coded or textual form.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $reasonCode;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates the reason the request was initiated. This is typically provided as a
     * parameter to the evaluation and echoed by the service, although for some use
     * cases, such as subscription- or event-based scenarios, it may provide an
     * indication of the cause for the response.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $reasonReference;
    /**
     * A text note which also contains information about who made the statement and
     * when.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Provides a mechanism to communicate additional information about the response.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation>
     */
    #[FHIRAnnotation]
    protected array $note;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Messages resulting from the evaluation of the artifact or artifacts. As part of
     * evaluating the request, the engine may produce informational or warning
     * messages. These messages will be provided by this element.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $evaluationMessage;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The output parameters of the evaluation, if any. Many modules will result in the
     * return of specific resources such as procedure or communication requests that
     * are returned as part of the operation result. However, modules may define
     * specific outputs that would be returned as the result of the evaluation, and
     * these would be returned in this element.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $outputParameters;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The actions, if any, produced by the evaluation of the artifact.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $result;
    /**
     * Describes a required data item for evaluation in terms of the type of data, and
     * optional code or date-based filters of the data.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the evaluation could not be completed due to lack of information, or
     * additional information would potentially result in a more accurate response,
     * this element will a description of the data required in order to proceed with
     * the evaluation. A subsequent request to the service should include this data.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDataRequirement>
     */
    #[FHIRDataRequirement]
    protected array $dataRequirement;

    /* constructor.php:61 */
    /**
     * FHIRGuidanceResponse Constructor
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $id
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta $meta
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $implicitRules
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $language
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative $text
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRResourceContainer>|iterable<\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface> $contained
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier $requestIdentifier
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier> $identifier
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $moduleUri
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical $moduleCanonical
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $moduleCodeableConcept
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRGuidanceResponseStatusList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRGuidanceResponseStatus $status
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $subject
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $encounter
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $occurrenceDateTime
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $performer
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $reasonCode
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $reasonReference
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation> $note
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $evaluationMessage
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $outputParameters
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $result
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDataRequirement> $dataRequirement
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
                                null|FHIRIdentifier $requestIdentifier = null,
                                null|iterable $identifier = null,
                                null|string|FHIRUriPrimitive|FHIRUri $moduleUri = null,
                                null|string|FHIRCanonicalPrimitive|FHIRCanonical $moduleCanonical = null,
                                null|FHIRCodeableConcept $moduleCodeableConcept = null,
                                null|string|FHIRGuidanceResponseStatusList|FHIRGuidanceResponseStatus $status = null,
                                null|FHIRReference $subject = null,
                                null|FHIRReference $encounter = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $occurrenceDateTime = null,
                                null|FHIRReference $performer = null,
                                null|iterable $reasonCode = null,
                                null|iterable $reasonReference = null,
                                null|iterable $note = null,
                                null|iterable $evaluationMessage = null,
                                null|FHIRReference $outputParameters = null,
                                null|FHIRReference $result = null,
                                null|iterable $dataRequirement = null,
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
        if (null !== $requestIdentifier) {
            $this->setRequestIdentifier($requestIdentifier);
        }
        if (null !== $identifier) {
            $this->setIdentifier(...$identifier);
        }
        if (null !== $moduleUri) {
            $this->setModuleUri($moduleUri);
        }
        if (null !== $moduleCanonical) {
            $this->setModuleCanonical($moduleCanonical);
        }
        if (null !== $moduleCodeableConcept) {
            $this->setModuleCodeableConcept($moduleCodeableConcept);
        }
        if (null !== $status) {
            $this->setStatus($status);
        }
        if (null !== $subject) {
            $this->setSubject($subject);
        }
        if (null !== $encounter) {
            $this->setEncounter($encounter);
        }
        if (null !== $occurrenceDateTime) {
            $this->setOccurrenceDateTime($occurrenceDateTime);
        }
        if (null !== $performer) {
            $this->setPerformer($performer);
        }
        if (null !== $reasonCode) {
            $this->setReasonCode(...$reasonCode);
        }
        if (null !== $reasonReference) {
            $this->setReasonReference(...$reasonReference);
        }
        if (null !== $note) {
            $this->setNote(...$note);
        }
        if (null !== $evaluationMessage) {
            $this->setEvaluationMessage(...$evaluationMessage);
        }
        if (null !== $outputParameters) {
            $this->setOutputParameters($outputParameters);
        }
        if (null !== $result) {
            $this->setResult($result);
        }
        if (null !== $dataRequirement) {
            $this->setDataRequirement(...$dataRequirement);
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
     * The identifier of the request associated with this response. If an identifier
     * was given as part of the request, it will be reproduced here to enable the
     * requester to more easily identify the response in a multi-request scenario.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier
     */
    public function getRequestIdentifier(): null|FHIRIdentifier
    {
        return $this->requestIdentifier ?? null;
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The identifier of the request associated with this response. If an identifier
     * was given as part of the request, it will be reproduced here to enable the
     * requester to more easily identify the response in a multi-request scenario.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier $requestIdentifier
     * @return static
     */
    public function setRequestIdentifier(null|FHIRIdentifier $requestIdentifier): self
    {
        if (null === $requestIdentifier) {
            unset($this->requestIdentifier);
            return $this;
        }
        $this->requestIdentifier = $requestIdentifier;
        return $this;
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Allows a service to provide unique, business identifiers for the response.
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
     * Allows a service to provide unique, business identifiers for the response.
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
     * Allows a service to provide unique, business identifiers for the response.
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
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An identifier, CodeableConcept or canonical reference to the guidance that was
     * requested. (choose any one of module*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri
     */
    public function getModuleUri(): null|FHIRUri
    {
        return $this->moduleUri ?? null;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An identifier, CodeableConcept or canonical reference to the guidance that was
     * requested. (choose any one of module*, but only one)
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $moduleUri
     * @return static
     */
    public function setModuleUri(null|string|FHIRUriPrimitive|FHIRUri $moduleUri): self
    {
        if (null === $moduleUri) {
            unset($this->moduleUri);
            return $this;
        }
        if (!($moduleUri instanceof FHIRUri)) {
            $moduleUri = new FHIRUri(value: $moduleUri);
        }
        $this->moduleUri = $moduleUri;
        return $this;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * An identifier, CodeableConcept or canonical reference to the guidance that was
     * requested. (choose any one of module*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical
     */
    public function getModuleCanonical(): null|FHIRCanonical
    {
        return $this->moduleCanonical ?? null;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * An identifier, CodeableConcept or canonical reference to the guidance that was
     * requested. (choose any one of module*, but only one)
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical $moduleCanonical
     * @return static
     */
    public function setModuleCanonical(null|string|FHIRCanonicalPrimitive|FHIRCanonical $moduleCanonical): self
    {
        if (null === $moduleCanonical) {
            unset($this->moduleCanonical);
            return $this;
        }
        if (!($moduleCanonical instanceof FHIRCanonical)) {
            $moduleCanonical = new FHIRCanonical(value: $moduleCanonical);
        }
        $this->moduleCanonical = $moduleCanonical;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * An identifier, CodeableConcept or canonical reference to the guidance that was
     * requested. (choose any one of module*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getModuleCodeableConcept(): null|FHIRCodeableConcept
    {
        return $this->moduleCodeableConcept ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * An identifier, CodeableConcept or canonical reference to the guidance that was
     * requested. (choose any one of module*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $moduleCodeableConcept
     * @return static
     */
    public function setModuleCodeableConcept(null|FHIRCodeableConcept $moduleCodeableConcept): self
    {
        if (null === $moduleCodeableConcept) {
            unset($this->moduleCodeableConcept);
            return $this;
        }
        $this->moduleCodeableConcept = $moduleCodeableConcept;
        return $this;
    }

    /**
     * The status of a guidance response.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The status of the response. If the evaluation is completed successfully, the
     * status will indicate success. However, in order to complete the evaluation, the
     * engine may require more information. In this case, the status will be
     * data-required, and the response will contain a description of the additional
     * required information. If the evaluation completed successfully, but the engine
     * determines that a potentially more accurate response could be provided if more
     * data was available, the status will be data-requested, and the response will
     * contain a description of the additional requested information.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRGuidanceResponseStatus
     */
    public function getStatus(): null|FHIRGuidanceResponseStatus
    {
        return $this->status ?? null;
    }

    /**
     * The status of a guidance response.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The status of the response. If the evaluation is completed successfully, the
     * status will indicate success. However, in order to complete the evaluation, the
     * engine may require more information. In this case, the status will be
     * data-required, and the response will contain a description of the additional
     * required information. If the evaluation completed successfully, but the engine
     * determines that a potentially more accurate response could be provided if more
     * data was available, the status will be data-requested, and the response will
     * contain a description of the additional requested information.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRGuidanceResponseStatusList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRGuidanceResponseStatus $status
     * @return static
     */
    public function setStatus(null|string|FHIRGuidanceResponseStatusList|FHIRGuidanceResponseStatus $status): self
    {
        if (null === $status) {
            unset($this->status);
            return $this;
        }
        if (!($status instanceof FHIRGuidanceResponseStatus)) {
            $status = new FHIRGuidanceResponseStatus(value: $status);
        }
        $this->status = $status;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The patient for which the request was processed.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getSubject(): null|FHIRReference
    {
        return $this->subject ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The patient for which the request was processed.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $subject
     * @return static
     */
    public function setSubject(null|FHIRReference $subject): self
    {
        if (null === $subject) {
            unset($this->subject);
            return $this;
        }
        $this->subject = $subject;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The encounter during which this response was created or to which the creation of
     * this record is tightly associated.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getEncounter(): null|FHIRReference
    {
        return $this->encounter ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The encounter during which this response was created or to which the creation of
     * this record is tightly associated.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $encounter
     * @return static
     */
    public function setEncounter(null|FHIRReference $encounter): self
    {
        if (null === $encounter) {
            unset($this->encounter);
            return $this;
        }
        $this->encounter = $encounter;
        return $this;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates when the guidance response was processed.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    public function getOccurrenceDateTime(): null|FHIRDateTime
    {
        return $this->occurrenceDateTime ?? null;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates when the guidance response was processed.
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $occurrenceDateTime
     * @return static
     */
    public function setOccurrenceDateTime(null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $occurrenceDateTime): self
    {
        if (null === $occurrenceDateTime) {
            unset($this->occurrenceDateTime);
            return $this;
        }
        if (!($occurrenceDateTime instanceof FHIRDateTime)) {
            $occurrenceDateTime = new FHIRDateTime(value: $occurrenceDateTime);
        }
        $this->occurrenceDateTime = $occurrenceDateTime;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Provides a reference to the device that performed the guidance.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getPerformer(): null|FHIRReference
    {
        return $this->performer ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Provides a reference to the device that performed the guidance.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $performer
     * @return static
     */
    public function setPerformer(null|FHIRReference $performer): self
    {
        if (null === $performer) {
            unset($this->performer);
            return $this;
        }
        $this->performer = $performer;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Describes the reason for the guidance response in coded or textual form.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getReasonCode(): array
    {
        return $this->reasonCode ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getReasonCodeIterator(): iterable
    {
        if (!isset($this->reasonCode)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->reasonCode);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Describes the reason for the guidance response in coded or textual form.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $reasonCode
     * @return static
     */
    public function addReasonCode(FHIRCodeableConcept $reasonCode): self
    {
        if (!isset($this->reasonCode)) {
            $this->reasonCode = [];
        }
        $this->reasonCode[] = $reasonCode;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Describes the reason for the guidance response in coded or textual form.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$reasonCode
     * @return static
     */
    public function setReasonCode(FHIRCodeableConcept ...$reasonCode): self
    {
        if ([] === $reasonCode) {
            unset($this->reasonCode);
            return $this;
        }
        $this->reasonCode = $reasonCode;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates the reason the request was initiated. This is typically provided as a
     * parameter to the evaluation and echoed by the service, although for some use
     * cases, such as subscription- or event-based scenarios, it may provide an
     * indication of the cause for the response.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getReasonReference(): array
    {
        return $this->reasonReference ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getReasonReferenceIterator(): iterable
    {
        if (!isset($this->reasonReference)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->reasonReference);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates the reason the request was initiated. This is typically provided as a
     * parameter to the evaluation and echoed by the service, although for some use
     * cases, such as subscription- or event-based scenarios, it may provide an
     * indication of the cause for the response.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $reasonReference
     * @return static
     */
    public function addReasonReference(FHIRReference $reasonReference): self
    {
        if (!isset($this->reasonReference)) {
            $this->reasonReference = [];
        }
        $this->reasonReference[] = $reasonReference;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates the reason the request was initiated. This is typically provided as a
     * parameter to the evaluation and echoed by the service, although for some use
     * cases, such as subscription- or event-based scenarios, it may provide an
     * indication of the cause for the response.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$reasonReference
     * @return static
     */
    public function setReasonReference(FHIRReference ...$reasonReference): self
    {
        if ([] === $reasonReference) {
            unset($this->reasonReference);
            return $this;
        }
        $this->reasonReference = $reasonReference;
        return $this;
    }

    /**
     * A text note which also contains information about who made the statement and
     * when.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Provides a mechanism to communicate additional information about the response.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation>
     */
    public function getNote(): array
    {
        return $this->note ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation>
     */
    public function getNoteIterator(): iterable
    {
        if (!isset($this->note)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->note);
    }

    /**
     * A text note which also contains information about who made the statement and
     * when.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Provides a mechanism to communicate additional information about the response.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation $note
     * @return static
     */
    public function addNote(FHIRAnnotation $note): self
    {
        if (!isset($this->note)) {
            $this->note = [];
        }
        $this->note[] = $note;
        return $this;
    }

    /**
     * A text note which also contains information about who made the statement and
     * when.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Provides a mechanism to communicate additional information about the response.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation ...$note
     * @return static
     */
    public function setNote(FHIRAnnotation ...$note): self
    {
        if ([] === $note) {
            unset($this->note);
            return $this;
        }
        $this->note = $note;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Messages resulting from the evaluation of the artifact or artifacts. As part of
     * evaluating the request, the engine may produce informational or warning
     * messages. These messages will be provided by this element.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getEvaluationMessage(): array
    {
        return $this->evaluationMessage ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getEvaluationMessageIterator(): iterable
    {
        if (!isset($this->evaluationMessage)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->evaluationMessage);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Messages resulting from the evaluation of the artifact or artifacts. As part of
     * evaluating the request, the engine may produce informational or warning
     * messages. These messages will be provided by this element.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $evaluationMessage
     * @return static
     */
    public function addEvaluationMessage(FHIRReference $evaluationMessage): self
    {
        if (!isset($this->evaluationMessage)) {
            $this->evaluationMessage = [];
        }
        $this->evaluationMessage[] = $evaluationMessage;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Messages resulting from the evaluation of the artifact or artifacts. As part of
     * evaluating the request, the engine may produce informational or warning
     * messages. These messages will be provided by this element.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$evaluationMessage
     * @return static
     */
    public function setEvaluationMessage(FHIRReference ...$evaluationMessage): self
    {
        if ([] === $evaluationMessage) {
            unset($this->evaluationMessage);
            return $this;
        }
        $this->evaluationMessage = $evaluationMessage;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The output parameters of the evaluation, if any. Many modules will result in the
     * return of specific resources such as procedure or communication requests that
     * are returned as part of the operation result. However, modules may define
     * specific outputs that would be returned as the result of the evaluation, and
     * these would be returned in this element.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getOutputParameters(): null|FHIRReference
    {
        return $this->outputParameters ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The output parameters of the evaluation, if any. Many modules will result in the
     * return of specific resources such as procedure or communication requests that
     * are returned as part of the operation result. However, modules may define
     * specific outputs that would be returned as the result of the evaluation, and
     * these would be returned in this element.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $outputParameters
     * @return static
     */
    public function setOutputParameters(null|FHIRReference $outputParameters): self
    {
        if (null === $outputParameters) {
            unset($this->outputParameters);
            return $this;
        }
        $this->outputParameters = $outputParameters;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The actions, if any, produced by the evaluation of the artifact.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getResult(): null|FHIRReference
    {
        return $this->result ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The actions, if any, produced by the evaluation of the artifact.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $result
     * @return static
     */
    public function setResult(null|FHIRReference $result): self
    {
        if (null === $result) {
            unset($this->result);
            return $this;
        }
        $this->result = $result;
        return $this;
    }

    /**
     * Describes a required data item for evaluation in terms of the type of data, and
     * optional code or date-based filters of the data.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the evaluation could not be completed due to lack of information, or
     * additional information would potentially result in a more accurate response,
     * this element will a description of the data required in order to proceed with
     * the evaluation. A subsequent request to the service should include this data.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDataRequirement>
     */
    public function getDataRequirement(): array
    {
        return $this->dataRequirement ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDataRequirement>
     */
    public function getDataRequirementIterator(): iterable
    {
        if (!isset($this->dataRequirement)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->dataRequirement);
    }

    /**
     * Describes a required data item for evaluation in terms of the type of data, and
     * optional code or date-based filters of the data.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the evaluation could not be completed due to lack of information, or
     * additional information would potentially result in a more accurate response,
     * this element will a description of the data required in order to proceed with
     * the evaluation. A subsequent request to the service should include this data.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDataRequirement $dataRequirement
     * @return static
     */
    public function addDataRequirement(FHIRDataRequirement $dataRequirement): self
    {
        if (!isset($this->dataRequirement)) {
            $this->dataRequirement = [];
        }
        $this->dataRequirement[] = $dataRequirement;
        return $this;
    }

    /**
     * Describes a required data item for evaluation in terms of the type of data, and
     * optional code or date-based filters of the data.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the evaluation could not be completed due to lack of information, or
     * additional information would potentially result in a more accurate response,
     * this element will a description of the data required in order to proceed with
     * the evaluation. A subsequent request to the service should include this data.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDataRequirement ...$dataRequirement
     * @return static
     */
    public function setDataRequirement(FHIRDataRequirement ...$dataRequirement): self
    {
        if ([] === $dataRequirement) {
            unset($this->dataRequirement);
            return $this;
        }
        $this->dataRequirement = $dataRequirement;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param string|\SimpleXMLElement $element
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRGuidanceResponse $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRGuidanceResponse
     * @throws \Exception
     */
    public static function xmlUnserialize(string|\SimpleXMLElement $element,
                                          null|UnserializeConfig $config = null,
                                          null|ResourceTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRGuidanceResponse)) {
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
            } else if (self::FIELD_REQUEST_IDENTIFIER === $cen) {
                $type->setRequestIdentifier(FHIRIdentifier::xmlUnserialize($ce, $config));
            } else if (self::FIELD_IDENTIFIER === $cen) {
                $type->addIdentifier(FHIRIdentifier::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MODULE_URI === $cen) {
                $type->setModuleUri(FHIRUri::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MODULE_CANONICAL === $cen) {
                $type->setModuleCanonical(FHIRCanonical::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MODULE_CODEABLE_CONCEPT === $cen) {
                $type->setModuleCodeableConcept(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_STATUS === $cen) {
                $type->setStatus(FHIRGuidanceResponseStatus::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SUBJECT === $cen) {
                $type->setSubject(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ENCOUNTER === $cen) {
                $type->setEncounter(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_OCCURRENCE_DATE_TIME === $cen) {
                $type->setOccurrenceDateTime(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PERFORMER === $cen) {
                $type->setPerformer(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REASON_CODE === $cen) {
                $type->addReasonCode(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REASON_REFERENCE === $cen) {
                $type->addReasonReference(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_NOTE === $cen) {
                $type->addNote(FHIRAnnotation::xmlUnserialize($ce, $config));
            } else if (self::FIELD_EVALUATION_MESSAGE === $cen) {
                $type->addEvaluationMessage(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_OUTPUT_PARAMETERS === $cen) {
                $type->setOutputParameters(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RESULT === $cen) {
                $type->setResult(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DATA_REQUIREMENT === $cen) {
                $type->addDataRequirement(FHIRDataRequirement::xmlUnserialize($ce, $config));
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
        if (isset($attributes[self::FIELD_MODULE_URI])) {
            if (isset($type->moduleUri)) {
                $type->moduleUri->setValue((string)$attributes[self::FIELD_MODULE_URI]);
            } else {
                $type->setModuleUri((string)$attributes[self::FIELD_MODULE_URI]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_MODULE_URI, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_MODULE_CANONICAL])) {
            if (isset($type->moduleCanonical)) {
                $type->moduleCanonical->setValue((string)$attributes[self::FIELD_MODULE_CANONICAL]);
            } else {
                $type->setModuleCanonical((string)$attributes[self::FIELD_MODULE_CANONICAL]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_MODULE_CANONICAL, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_STATUS])) {
            if (isset($type->status)) {
                $type->status->setValue((string)$attributes[self::FIELD_STATUS]);
            } else {
                $type->setStatus((string)$attributes[self::FIELD_STATUS]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_STATUS, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_OCCURRENCE_DATE_TIME])) {
            if (isset($type->occurrenceDateTime)) {
                $type->occurrenceDateTime->setValue((string)$attributes[self::FIELD_OCCURRENCE_DATE_TIME]);
            } else {
                $type->setOccurrenceDateTime((string)$attributes[self::FIELD_OCCURRENCE_DATE_TIME]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_OCCURRENCE_DATE_TIME, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
            $xw->openRootNode('GuidanceResponse', $this->_getSourceXMLNS());
        }
        if (isset($this->moduleUri) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_MODULE_URI]) {
            $xw->writeAttribute(self::FIELD_MODULE_URI, $this->moduleUri->_getValueAsString());
        }
        if (isset($this->moduleCanonical) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_MODULE_CANONICAL]) {
            $xw->writeAttribute(self::FIELD_MODULE_CANONICAL, $this->moduleCanonical->_getValueAsString());
        }
        if (isset($this->status) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_STATUS]) {
            $xw->writeAttribute(self::FIELD_STATUS, $this->status->_getValueAsString());
        }
        if (isset($this->occurrenceDateTime) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_OCCURRENCE_DATE_TIME]) {
            $xw->writeAttribute(self::FIELD_OCCURRENCE_DATE_TIME, $this->occurrenceDateTime->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->requestIdentifier)) {
            $xw->startElement(self::FIELD_REQUEST_IDENTIFIER);
            $this->requestIdentifier->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->identifier)) {
            foreach ($this->identifier as $v) {
                $xw->startElement(self::FIELD_IDENTIFIER);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->moduleUri)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_MODULE_URI]
                || $this->moduleUri->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_MODULE_URI);
            $this->moduleUri->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_MODULE_URI]);
            $xw->endElement();
        }
        if (isset($this->moduleCanonical)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_MODULE_CANONICAL]
                || $this->moduleCanonical->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_MODULE_CANONICAL);
            $this->moduleCanonical->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_MODULE_CANONICAL]);
            $xw->endElement();
        }
        if (isset($this->moduleCodeableConcept)) {
            $xw->startElement(self::FIELD_MODULE_CODEABLE_CONCEPT);
            $this->moduleCodeableConcept->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->status)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_STATUS]
                || $this->status->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_STATUS);
            $this->status->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_STATUS]);
            $xw->endElement();
        }
        if (isset($this->subject)) {
            $xw->startElement(self::FIELD_SUBJECT);
            $this->subject->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->encounter)) {
            $xw->startElement(self::FIELD_ENCOUNTER);
            $this->encounter->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->occurrenceDateTime)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_OCCURRENCE_DATE_TIME]
                || $this->occurrenceDateTime->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_OCCURRENCE_DATE_TIME);
            $this->occurrenceDateTime->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_OCCURRENCE_DATE_TIME]);
            $xw->endElement();
        }
        if (isset($this->performer)) {
            $xw->startElement(self::FIELD_PERFORMER);
            $this->performer->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->reasonCode)) {
            foreach ($this->reasonCode as $v) {
                $xw->startElement(self::FIELD_REASON_CODE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->reasonReference)) {
            foreach ($this->reasonReference as $v) {
                $xw->startElement(self::FIELD_REASON_REFERENCE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->note)) {
            foreach ($this->note as $v) {
                $xw->startElement(self::FIELD_NOTE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->evaluationMessage)) {
            foreach ($this->evaluationMessage as $v) {
                $xw->startElement(self::FIELD_EVALUATION_MESSAGE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->outputParameters)) {
            $xw->startElement(self::FIELD_OUTPUT_PARAMETERS);
            $this->outputParameters->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->result)) {
            $xw->startElement(self::FIELD_RESULT);
            $this->result->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->dataRequirement)) {
            foreach ($this->dataRequirement as $v) {
                $xw->startElement(self::FIELD_DATA_REQUIREMENT);
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
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRGuidanceResponse $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRGuidanceResponse
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
        } else if (!($type instanceof FHIRGuidanceResponse)) {
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
        if (isset($decoded->requestIdentifier) || property_exists($decoded, self::FIELD_REQUEST_IDENTIFIER)) {
            if (is_array($decoded->requestIdentifier)) {
                $type->setRequestIdentifier(FHIRIdentifier::jsonUnserialize(reset($decoded->requestIdentifier), $config));
            } else {
                $type->setRequestIdentifier(FHIRIdentifier::jsonUnserialize($decoded->requestIdentifier, $config));
            }
        }
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
        if (isset($decoded->moduleUri)
            || isset($decoded->_moduleUri)
            || property_exists($decoded, self::FIELD_MODULE_URI)
            || property_exists($decoded, self::FIELD_MODULE_URI_EXT)) {
            $v = $decoded->_moduleUri ?? new \stdClass();
            $v->value = $decoded->moduleUri ?? null;
            $type->setModuleUri(FHIRUri::jsonUnserialize($v, $config));
        }
        if (isset($decoded->moduleCanonical)
            || isset($decoded->_moduleCanonical)
            || property_exists($decoded, self::FIELD_MODULE_CANONICAL)
            || property_exists($decoded, self::FIELD_MODULE_CANONICAL_EXT)) {
            $v = $decoded->_moduleCanonical ?? new \stdClass();
            $v->value = $decoded->moduleCanonical ?? null;
            $type->setModuleCanonical(FHIRCanonical::jsonUnserialize($v, $config));
        }
        if (isset($decoded->moduleCodeableConcept) || property_exists($decoded, self::FIELD_MODULE_CODEABLE_CONCEPT)) {
            if (is_array($decoded->moduleCodeableConcept)) {
                $type->setModuleCodeableConcept(FHIRCodeableConcept::jsonUnserialize(reset($decoded->moduleCodeableConcept), $config));
            } else {
                $type->setModuleCodeableConcept(FHIRCodeableConcept::jsonUnserialize($decoded->moduleCodeableConcept, $config));
            }
        }
        if (isset($decoded->status)
            || isset($decoded->_status)
            || property_exists($decoded, self::FIELD_STATUS)
            || property_exists($decoded, self::FIELD_STATUS_EXT)) {
            $v = $decoded->_status ?? new \stdClass();
            $v->value = $decoded->status ?? null;
            $type->setStatus(FHIRGuidanceResponseStatus::jsonUnserialize($v, $config));
        }
        if (isset($decoded->subject) || property_exists($decoded, self::FIELD_SUBJECT)) {
            if (is_array($decoded->subject)) {
                $type->setSubject(FHIRReference::jsonUnserialize(reset($decoded->subject), $config));
            } else {
                $type->setSubject(FHIRReference::jsonUnserialize($decoded->subject, $config));
            }
        }
        if (isset($decoded->encounter) || property_exists($decoded, self::FIELD_ENCOUNTER)) {
            if (is_array($decoded->encounter)) {
                $type->setEncounter(FHIRReference::jsonUnserialize(reset($decoded->encounter), $config));
            } else {
                $type->setEncounter(FHIRReference::jsonUnserialize($decoded->encounter, $config));
            }
        }
        if (isset($decoded->occurrenceDateTime)
            || isset($decoded->_occurrenceDateTime)
            || property_exists($decoded, self::FIELD_OCCURRENCE_DATE_TIME)
            || property_exists($decoded, self::FIELD_OCCURRENCE_DATE_TIME_EXT)) {
            $v = $decoded->_occurrenceDateTime ?? new \stdClass();
            $v->value = $decoded->occurrenceDateTime ?? null;
            $type->setOccurrenceDateTime(FHIRDateTime::jsonUnserialize($v, $config));
        }
        if (isset($decoded->performer) || property_exists($decoded, self::FIELD_PERFORMER)) {
            if (is_array($decoded->performer)) {
                $type->setPerformer(FHIRReference::jsonUnserialize(reset($decoded->performer), $config));
            } else {
                $type->setPerformer(FHIRReference::jsonUnserialize($decoded->performer, $config));
            }
        }
        if (isset($decoded->reasonCode) || property_exists($decoded, self::FIELD_REASON_CODE)) {
            if (is_object($decoded->reasonCode)) {
                $vals = [$decoded->reasonCode];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_REASON_CODE, true);
            } else {
                $vals = $decoded->reasonCode;
            }
            foreach($vals as $v) {
                $type->addReasonCode(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->reasonReference) || property_exists($decoded, self::FIELD_REASON_REFERENCE)) {
            if (is_object($decoded->reasonReference)) {
                $vals = [$decoded->reasonReference];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_REASON_REFERENCE, true);
            } else {
                $vals = $decoded->reasonReference;
            }
            foreach($vals as $v) {
                $type->addReasonReference(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->note) || property_exists($decoded, self::FIELD_NOTE)) {
            if (is_object($decoded->note)) {
                $vals = [$decoded->note];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_NOTE, true);
            } else {
                $vals = $decoded->note;
            }
            foreach($vals as $v) {
                $type->addNote(FHIRAnnotation::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->evaluationMessage) || property_exists($decoded, self::FIELD_EVALUATION_MESSAGE)) {
            if (is_object($decoded->evaluationMessage)) {
                $vals = [$decoded->evaluationMessage];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_EVALUATION_MESSAGE, true);
            } else {
                $vals = $decoded->evaluationMessage;
            }
            foreach($vals as $v) {
                $type->addEvaluationMessage(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->outputParameters) || property_exists($decoded, self::FIELD_OUTPUT_PARAMETERS)) {
            if (is_array($decoded->outputParameters)) {
                $type->setOutputParameters(FHIRReference::jsonUnserialize(reset($decoded->outputParameters), $config));
            } else {
                $type->setOutputParameters(FHIRReference::jsonUnserialize($decoded->outputParameters, $config));
            }
        }
        if (isset($decoded->result) || property_exists($decoded, self::FIELD_RESULT)) {
            if (is_array($decoded->result)) {
                $type->setResult(FHIRReference::jsonUnserialize(reset($decoded->result), $config));
            } else {
                $type->setResult(FHIRReference::jsonUnserialize($decoded->result, $config));
            }
        }
        if (isset($decoded->dataRequirement) || property_exists($decoded, self::FIELD_DATA_REQUIREMENT)) {
            if (is_object($decoded->dataRequirement)) {
                $vals = [$decoded->dataRequirement];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_DATA_REQUIREMENT, true);
            } else {
                $vals = $decoded->dataRequirement;
            }
            foreach($vals as $v) {
                $type->addDataRequirement(FHIRDataRequirement::jsonUnserialize($v, $config));
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
        if (isset($this->requestIdentifier)) {
            $out->requestIdentifier = $this->requestIdentifier;
        }
        if (isset($this->identifier) && [] !== $this->identifier) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_IDENTIFIER) && 1 === count($this->identifier)) {
                $out->identifier = $this->identifier[0];
            } else {
                $out->identifier = $this->identifier;
            }
        }
        if (isset($this->moduleUri)) {
            if (null !== ($val = $this->moduleUri->getValue())) {
                $out->moduleUri = $val;
            }
            if ($this->moduleUri->_nonValueFieldDefined()) {
                $ext = $this->moduleUri->jsonSerialize();
                unset($ext->value);
                $out->_moduleUri = $ext;
            }
        }
        if (isset($this->moduleCanonical)) {
            if (null !== ($val = $this->moduleCanonical->getValue())) {
                $out->moduleCanonical = $val;
            }
            if ($this->moduleCanonical->_nonValueFieldDefined()) {
                $ext = $this->moduleCanonical->jsonSerialize();
                unset($ext->value);
                $out->_moduleCanonical = $ext;
            }
        }
        if (isset($this->moduleCodeableConcept)) {
            $out->moduleCodeableConcept = $this->moduleCodeableConcept;
        }
        if (isset($this->status)) {
            if (null !== ($val = $this->status->getValue())) {
                $out->status = $val;
            }
            if ($this->status->_nonValueFieldDefined()) {
                $ext = $this->status->jsonSerialize();
                unset($ext->value);
                $out->_status = $ext;
            }
        }
        if (isset($this->subject)) {
            $out->subject = $this->subject;
        }
        if (isset($this->encounter)) {
            $out->encounter = $this->encounter;
        }
        if (isset($this->occurrenceDateTime)) {
            if (null !== ($val = $this->occurrenceDateTime->getValue())) {
                $out->occurrenceDateTime = $val;
            }
            if ($this->occurrenceDateTime->_nonValueFieldDefined()) {
                $ext = $this->occurrenceDateTime->jsonSerialize();
                unset($ext->value);
                $out->_occurrenceDateTime = $ext;
            }
        }
        if (isset($this->performer)) {
            $out->performer = $this->performer;
        }
        if (isset($this->reasonCode) && [] !== $this->reasonCode) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_REASON_CODE) && 1 === count($this->reasonCode)) {
                $out->reasonCode = $this->reasonCode[0];
            } else {
                $out->reasonCode = $this->reasonCode;
            }
        }
        if (isset($this->reasonReference) && [] !== $this->reasonReference) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_REASON_REFERENCE) && 1 === count($this->reasonReference)) {
                $out->reasonReference = $this->reasonReference[0];
            } else {
                $out->reasonReference = $this->reasonReference;
            }
        }
        if (isset($this->note) && [] !== $this->note) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_NOTE) && 1 === count($this->note)) {
                $out->note = $this->note[0];
            } else {
                $out->note = $this->note;
            }
        }
        if (isset($this->evaluationMessage) && [] !== $this->evaluationMessage) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_EVALUATION_MESSAGE) && 1 === count($this->evaluationMessage)) {
                $out->evaluationMessage = $this->evaluationMessage[0];
            } else {
                $out->evaluationMessage = $this->evaluationMessage;
            }
        }
        if (isset($this->outputParameters)) {
            $out->outputParameters = $this->outputParameters;
        }
        if (isset($this->result)) {
            $out->result = $this->result;
        }
        if (isset($this->dataRequirement) && [] !== $this->dataRequirement) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_DATA_REQUIREMENT) && 1 === count($this->dataRequirement)) {
                $out->dataRequirement = $this->dataRequirement[0];
            } else {
                $out->dataRequirement = $this->dataRequirement;
            }
        }
        $out->resourceType = $this->_getResourceType();
        return $out;
    }
}
