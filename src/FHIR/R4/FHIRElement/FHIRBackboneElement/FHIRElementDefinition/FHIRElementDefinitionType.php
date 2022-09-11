<?php

namespace OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRElementDefinition;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: September 10th, 2022 20:42+0000
 * 
 * PHPFHIR Copyright:
 * 
 * Copyright 2016-2022 Daniel Carbone (daniel.p.carbone@gmail.com)
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

use OpenEMR\FHIR\R4\FHIRElement\FHIRAggregationMode;
use OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical;
use OpenEMR\FHIR\R4\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReferenceVersionRules;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUri;
use OpenEMR\FHIR\R4\FHIRStringPrimitive;
use OpenEMR\FHIR\R4\PHPFHIRConstants;
use OpenEMR\FHIR\R4\PHPFHIRTypeInterface;

/**
 * Captures constraints on each element within the resource, profile, or extension.
 * If the element is present, it must have a value for at least one of the defined
 * elements, an \@id referenced from the Narrative, or extensions
 *
 * Class FHIRElementDefinitionType
 * @package \OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRElementDefinition
 */
class FHIRElementDefinitionType extends FHIRBackboneElement
{
    // name of FHIR type this class describes
    const FHIR_TYPE_NAME = PHPFHIRConstants::TYPE_NAME_ELEMENT_DEFINITION_DOT_TYPE;
    const FIELD_CODE = 'code';
    const FIELD_CODE_EXT = '_code';
    const FIELD_PROFILE = 'profile';
    const FIELD_PROFILE_EXT = '_profile';
    const FIELD_TARGET_PROFILE = 'targetProfile';
    const FIELD_TARGET_PROFILE_EXT = '_targetProfile';
    const FIELD_AGGREGATION = 'aggregation';
    const FIELD_AGGREGATION_EXT = '_aggregation';
    const FIELD_VERSIONING = 'versioning';
    const FIELD_VERSIONING_EXT = '_versioning';

    /** @var string */
    private $_xmlns = '';

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * URL of Data type or Resource that is a(or the) type used for this element.
     * References are URLs that are relative to http://hl7.org/fhir/StructureDefinition
     * e.g. "string" is a reference to http://hl7.org/fhir/StructureDefinition/string.
     * Absolute URLs are only allowed in logical models.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    protected $code = null;

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Identifies a profile structure or implementation Guide that applies to the
     * datatype this element refers to. If any profiles are specified, then the content
     * must conform to at least one of them. The URL can be a local reference - to a
     * contained StructureDefinition, or a reference to another StructureDefinition or
     * Implementation Guide by a canonical URL. When an implementation guide is
     * specified, the type SHALL conform to at least one profile defined in the
     * implementation guide.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical[]
     */
    protected $profile = [];

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Used when the type is "Reference" or "canonical", and identifies a profile
     * structure or implementation Guide that applies to the target of the reference
     * this element refers to. If any profiles are specified, then the content must
     * conform to at least one of them. The URL can be a local reference - to a
     * contained StructureDefinition, or a reference to another StructureDefinition or
     * Implementation Guide by a canonical URL. When an implementation guide is
     * specified, the target resource SHALL conform to at least one profile defined in
     * the implementation guide.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical[]
     */
    protected $targetProfile = [];

    /**
     * How resource references can be aggregated.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the type is a reference to another resource, how the resource is or can be
     * aggregated - is it a contained resource, or a reference, and if the context is a
     * bundle, is it included in the bundle.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRAggregationMode[]
     */
    protected $aggregation = [];

    /**
     * Whether a reference needs to be version specific or version independent, or
     * whether either can be used.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether this reference needs to be version specific or version independent, or
     * whether either can be used.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReferenceVersionRules
     */
    protected $versioning = null;

    /**
     * Validation map for fields in type ElementDefinition.Type
     * @var array
     */
    private static $_validationRules = [    ];

    /**
     * FHIRElementDefinitionType Constructor
     * @param null|array $data
     */
    public function __construct($data = null)
    {
        if (null === $data || [] === $data) {
            return;
        }
        if (!is_array($data)) {
            throw new \InvalidArgumentException(sprintf(
                'FHIRElementDefinitionType::_construct - $data expected to be null or array, %s seen',
                gettype($data)
            ));
        }
        parent::__construct($data);
        if (isset($data[self::FIELD_CODE]) || isset($data[self::FIELD_CODE_EXT])) {
            $value = isset($data[self::FIELD_CODE]) ? $data[self::FIELD_CODE] : null;
            $ext = (isset($data[self::FIELD_CODE_EXT]) && is_array($data[self::FIELD_CODE_EXT])) ? $ext = $data[self::FIELD_CODE_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRUri) {
                    $this->setCode($value);
                } else if (is_array($value)) {
                    $this->setCode(new FHIRUri(array_merge($ext, $value)));
                } else {
                    $this->setCode(new FHIRUri([FHIRUri::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setCode(new FHIRUri($ext));
            }
        }
        if (isset($data[self::FIELD_PROFILE]) || isset($data[self::FIELD_PROFILE_EXT])) {
            $value = isset($data[self::FIELD_PROFILE]) ? $data[self::FIELD_PROFILE] : null;
            $ext = (isset($data[self::FIELD_PROFILE_EXT]) && is_array($data[self::FIELD_PROFILE_EXT])) ? $ext = $data[self::FIELD_PROFILE_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRCanonical) {
                    $this->addProfile($value);
                } else if (is_array($value)) {
                    foreach($value as $i => $v) {
                        if ($v instanceof FHIRCanonical) {
                            $this->addProfile($v);
                        } else {
                            $iext = (isset($ext[$i]) && is_array($ext[$i])) ? $ext[$i] : [];
                            if (is_array($v)) {
                                $this->addProfile(new FHIRCanonical(array_merge($v, $iext)));
                            } else {
                                $this->addProfile(new FHIRCanonical([FHIRCanonical::FIELD_VALUE => $v] + $iext));
                            }
                        }
                    }
                } elseif (is_array($value)) {
                    $this->addProfile(new FHIRCanonical(array_merge($ext, $value)));
                } else {
                    $this->addProfile(new FHIRCanonical([FHIRCanonical::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                foreach($ext as $iext) {
                    $this->addProfile(new FHIRCanonical($iext));
                }
            }
        }
        if (isset($data[self::FIELD_TARGET_PROFILE]) || isset($data[self::FIELD_TARGET_PROFILE_EXT])) {
            $value = isset($data[self::FIELD_TARGET_PROFILE]) ? $data[self::FIELD_TARGET_PROFILE] : null;
            $ext = (isset($data[self::FIELD_TARGET_PROFILE_EXT]) && is_array($data[self::FIELD_TARGET_PROFILE_EXT])) ? $ext = $data[self::FIELD_TARGET_PROFILE_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRCanonical) {
                    $this->addTargetProfile($value);
                } else if (is_array($value)) {
                    foreach($value as $i => $v) {
                        if ($v instanceof FHIRCanonical) {
                            $this->addTargetProfile($v);
                        } else {
                            $iext = (isset($ext[$i]) && is_array($ext[$i])) ? $ext[$i] : [];
                            if (is_array($v)) {
                                $this->addTargetProfile(new FHIRCanonical(array_merge($v, $iext)));
                            } else {
                                $this->addTargetProfile(new FHIRCanonical([FHIRCanonical::FIELD_VALUE => $v] + $iext));
                            }
                        }
                    }
                } elseif (is_array($value)) {
                    $this->addTargetProfile(new FHIRCanonical(array_merge($ext, $value)));
                } else {
                    $this->addTargetProfile(new FHIRCanonical([FHIRCanonical::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                foreach($ext as $iext) {
                    $this->addTargetProfile(new FHIRCanonical($iext));
                }
            }
        }
        if (isset($data[self::FIELD_AGGREGATION]) || isset($data[self::FIELD_AGGREGATION_EXT])) {
            $value = isset($data[self::FIELD_AGGREGATION]) ? $data[self::FIELD_AGGREGATION] : null;
            $ext = (isset($data[self::FIELD_AGGREGATION_EXT]) && is_array($data[self::FIELD_AGGREGATION_EXT])) ? $ext = $data[self::FIELD_AGGREGATION_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRAggregationMode) {
                    $this->addAggregation($value);
                } else if (is_array($value)) {
                    foreach($value as $i => $v) {
                        if ($v instanceof FHIRAggregationMode) {
                            $this->addAggregation($v);
                        } else {
                            $iext = (isset($ext[$i]) && is_array($ext[$i])) ? $ext[$i] : [];
                            if (is_array($v)) {
                                $this->addAggregation(new FHIRAggregationMode(array_merge($v, $iext)));
                            } else {
                                $this->addAggregation(new FHIRAggregationMode([FHIRAggregationMode::FIELD_VALUE => $v] + $iext));
                            }
                        }
                    }
                } elseif (is_array($value)) {
                    $this->addAggregation(new FHIRAggregationMode(array_merge($ext, $value)));
                } else {
                    $this->addAggregation(new FHIRAggregationMode([FHIRAggregationMode::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                foreach($ext as $iext) {
                    $this->addAggregation(new FHIRAggregationMode($iext));
                }
            }
        }
        if (isset($data[self::FIELD_VERSIONING]) || isset($data[self::FIELD_VERSIONING_EXT])) {
            $value = isset($data[self::FIELD_VERSIONING]) ? $data[self::FIELD_VERSIONING] : null;
            $ext = (isset($data[self::FIELD_VERSIONING_EXT]) && is_array($data[self::FIELD_VERSIONING_EXT])) ? $ext = $data[self::FIELD_VERSIONING_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRReferenceVersionRules) {
                    $this->setVersioning($value);
                } else if (is_array($value)) {
                    $this->setVersioning(new FHIRReferenceVersionRules(array_merge($ext, $value)));
                } else {
                    $this->setVersioning(new FHIRReferenceVersionRules([FHIRReferenceVersionRules::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setVersioning(new FHIRReferenceVersionRules($ext));
            }
        }
    }

    /**
     * @return string
     */
    public function _getFHIRTypeName()
    {
        return self::FHIR_TYPE_NAME;
    }

    /**
     * @return string
     */
    public function _getFHIRXMLElementDefinition()
    {
        $xmlns = $this->_getFHIRXMLNamespace();
        if ('' !==  $xmlns) {
            $xmlns = " xmlns=\"{$xmlns}\"";
        }
        return "<ElementDefinitionType{$xmlns}></ElementDefinitionType>";
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * URL of Data type or Resource that is a(or the) type used for this element.
     * References are URLs that are relative to http://hl7.org/fhir/StructureDefinition
     * e.g. "string" is a reference to http://hl7.org/fhir/StructureDefinition/string.
     * Absolute URLs are only allowed in logical models.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * URL of Data type or Resource that is a(or the) type used for this element.
     * References are URLs that are relative to http://hl7.org/fhir/StructureDefinition
     * e.g. "string" is a reference to http://hl7.org/fhir/StructureDefinition/string.
     * Absolute URLs are only allowed in logical models.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRUri $code
     * @return static
     */
    public function setCode($code = null)
    {
        if (null !== $code && !($code instanceof FHIRUri)) {
            $code = new FHIRUri($code);
        }
        $this->_trackValueSet($this->code, $code);
        $this->code = $code;
        return $this;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Identifies a profile structure or implementation Guide that applies to the
     * datatype this element refers to. If any profiles are specified, then the content
     * must conform to at least one of them. The URL can be a local reference - to a
     * contained StructureDefinition, or a reference to another StructureDefinition or
     * Implementation Guide by a canonical URL. When an implementation guide is
     * specified, the type SHALL conform to at least one profile defined in the
     * implementation guide.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical[]
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Identifies a profile structure or implementation Guide that applies to the
     * datatype this element refers to. If any profiles are specified, then the content
     * must conform to at least one of them. The URL can be a local reference - to a
     * contained StructureDefinition, or a reference to another StructureDefinition or
     * Implementation Guide by a canonical URL. When an implementation guide is
     * specified, the type SHALL conform to at least one profile defined in the
     * implementation guide.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical $profile
     * @return static
     */
    public function addProfile($profile = null)
    {
        if (null !== $profile && !($profile instanceof FHIRCanonical)) {
            $profile = new FHIRCanonical($profile);
        }
        $this->_trackValueAdded();
        $this->profile[] = $profile;
        return $this;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Identifies a profile structure or implementation Guide that applies to the
     * datatype this element refers to. If any profiles are specified, then the content
     * must conform to at least one of them. The URL can be a local reference - to a
     * contained StructureDefinition, or a reference to another StructureDefinition or
     * Implementation Guide by a canonical URL. When an implementation guide is
     * specified, the type SHALL conform to at least one profile defined in the
     * implementation guide.
     *
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical[] $profile
     * @return static
     */
    public function setProfile(array $profile = [])
    {
        if ([] !== $this->profile) {
            $this->_trackValuesRemoved(count($this->profile));
            $this->profile = [];
        }
        if ([] === $profile) {
            return $this;
        }
        foreach($profile as $v) {
            if ($v instanceof FHIRCanonical) {
                $this->addProfile($v);
            } else {
                $this->addProfile(new FHIRCanonical($v));
            }
        }
        return $this;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Used when the type is "Reference" or "canonical", and identifies a profile
     * structure or implementation Guide that applies to the target of the reference
     * this element refers to. If any profiles are specified, then the content must
     * conform to at least one of them. The URL can be a local reference - to a
     * contained StructureDefinition, or a reference to another StructureDefinition or
     * Implementation Guide by a canonical URL. When an implementation guide is
     * specified, the target resource SHALL conform to at least one profile defined in
     * the implementation guide.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical[]
     */
    public function getTargetProfile()
    {
        return $this->targetProfile;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Used when the type is "Reference" or "canonical", and identifies a profile
     * structure or implementation Guide that applies to the target of the reference
     * this element refers to. If any profiles are specified, then the content must
     * conform to at least one of them. The URL can be a local reference - to a
     * contained StructureDefinition, or a reference to another StructureDefinition or
     * Implementation Guide by a canonical URL. When an implementation guide is
     * specified, the target resource SHALL conform to at least one profile defined in
     * the implementation guide.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical $targetProfile
     * @return static
     */
    public function addTargetProfile($targetProfile = null)
    {
        if (null !== $targetProfile && !($targetProfile instanceof FHIRCanonical)) {
            $targetProfile = new FHIRCanonical($targetProfile);
        }
        $this->_trackValueAdded();
        $this->targetProfile[] = $targetProfile;
        return $this;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Used when the type is "Reference" or "canonical", and identifies a profile
     * structure or implementation Guide that applies to the target of the reference
     * this element refers to. If any profiles are specified, then the content must
     * conform to at least one of them. The URL can be a local reference - to a
     * contained StructureDefinition, or a reference to another StructureDefinition or
     * Implementation Guide by a canonical URL. When an implementation guide is
     * specified, the target resource SHALL conform to at least one profile defined in
     * the implementation guide.
     *
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical[] $targetProfile
     * @return static
     */
    public function setTargetProfile(array $targetProfile = [])
    {
        if ([] !== $this->targetProfile) {
            $this->_trackValuesRemoved(count($this->targetProfile));
            $this->targetProfile = [];
        }
        if ([] === $targetProfile) {
            return $this;
        }
        foreach($targetProfile as $v) {
            if ($v instanceof FHIRCanonical) {
                $this->addTargetProfile($v);
            } else {
                $this->addTargetProfile(new FHIRCanonical($v));
            }
        }
        return $this;
    }

    /**
     * How resource references can be aggregated.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the type is a reference to another resource, how the resource is or can be
     * aggregated - is it a contained resource, or a reference, and if the context is a
     * bundle, is it included in the bundle.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRAggregationMode[]
     */
    public function getAggregation()
    {
        return $this->aggregation;
    }

    /**
     * How resource references can be aggregated.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the type is a reference to another resource, how the resource is or can be
     * aggregated - is it a contained resource, or a reference, and if the context is a
     * bundle, is it included in the bundle.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRAggregationMode $aggregation
     * @return static
     */
    public function addAggregation(FHIRAggregationMode $aggregation = null)
    {
        $this->_trackValueAdded();
        $this->aggregation[] = $aggregation;
        return $this;
    }

    /**
     * How resource references can be aggregated.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the type is a reference to another resource, how the resource is or can be
     * aggregated - is it a contained resource, or a reference, and if the context is a
     * bundle, is it included in the bundle.
     *
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRAggregationMode[] $aggregation
     * @return static
     */
    public function setAggregation(array $aggregation = [])
    {
        if ([] !== $this->aggregation) {
            $this->_trackValuesRemoved(count($this->aggregation));
            $this->aggregation = [];
        }
        if ([] === $aggregation) {
            return $this;
        }
        foreach($aggregation as $v) {
            if ($v instanceof FHIRAggregationMode) {
                $this->addAggregation($v);
            } else {
                $this->addAggregation(new FHIRAggregationMode($v));
            }
        }
        return $this;
    }

    /**
     * Whether a reference needs to be version specific or version independent, or
     * whether either can be used.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether this reference needs to be version specific or version independent, or
     * whether either can be used.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReferenceVersionRules
     */
    public function getVersioning()
    {
        return $this->versioning;
    }

    /**
     * Whether a reference needs to be version specific or version independent, or
     * whether either can be used.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether this reference needs to be version specific or version independent, or
     * whether either can be used.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReferenceVersionRules $versioning
     * @return static
     */
    public function setVersioning(FHIRReferenceVersionRules $versioning = null)
    {
        $this->_trackValueSet($this->versioning, $versioning);
        $this->versioning = $versioning;
        return $this;
    }

    /**
     * Returns the validation rules that this type's fields must comply with to be considered "valid"
     * The returned array is in ["fieldname[.offset]" => ["rule" => {constraint}]]
     *
     * @return array
     */
    public function _getValidationRules()
    {
        return self::$_validationRules;
    }

    /**
     * Validates that this type conforms to the specifications set forth for it by FHIR.  An empty array must be seen as
     * passing.
     *
     * @return array
     */
    public function _getValidationErrors()
    {
        $errs = parent::_getValidationErrors();
        $validationRules = $this->_getValidationRules();
        if (null !== ($v = $this->getCode())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_CODE] = $fieldErrs;
            }
        }
        if ([] !== ($vs = $this->getProfile())) {
            foreach($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_PROFILE, $i)] = $fieldErrs;
                }
            }
        }
        if ([] !== ($vs = $this->getTargetProfile())) {
            foreach($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_TARGET_PROFILE, $i)] = $fieldErrs;
                }
            }
        }
        if ([] !== ($vs = $this->getAggregation())) {
            foreach($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_AGGREGATION, $i)] = $fieldErrs;
                }
            }
        }
        if (null !== ($v = $this->getVersioning())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_VERSIONING] = $fieldErrs;
            }
        }
        if (isset($validationRules[self::FIELD_CODE])) {
            $v = $this->getCode();
            foreach($validationRules[self::FIELD_CODE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_ELEMENT_DEFINITION_DOT_TYPE, self::FIELD_CODE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_CODE])) {
                        $errs[self::FIELD_CODE] = [];
                    }
                    $errs[self::FIELD_CODE][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_PROFILE])) {
            $v = $this->getProfile();
            foreach($validationRules[self::FIELD_PROFILE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_ELEMENT_DEFINITION_DOT_TYPE, self::FIELD_PROFILE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_PROFILE])) {
                        $errs[self::FIELD_PROFILE] = [];
                    }
                    $errs[self::FIELD_PROFILE][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_TARGET_PROFILE])) {
            $v = $this->getTargetProfile();
            foreach($validationRules[self::FIELD_TARGET_PROFILE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_ELEMENT_DEFINITION_DOT_TYPE, self::FIELD_TARGET_PROFILE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_TARGET_PROFILE])) {
                        $errs[self::FIELD_TARGET_PROFILE] = [];
                    }
                    $errs[self::FIELD_TARGET_PROFILE][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_AGGREGATION])) {
            $v = $this->getAggregation();
            foreach($validationRules[self::FIELD_AGGREGATION] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_ELEMENT_DEFINITION_DOT_TYPE, self::FIELD_AGGREGATION, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_AGGREGATION])) {
                        $errs[self::FIELD_AGGREGATION] = [];
                    }
                    $errs[self::FIELD_AGGREGATION][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_VERSIONING])) {
            $v = $this->getVersioning();
            foreach($validationRules[self::FIELD_VERSIONING] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_ELEMENT_DEFINITION_DOT_TYPE, self::FIELD_VERSIONING, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_VERSIONING])) {
                        $errs[self::FIELD_VERSIONING] = [];
                    }
                    $errs[self::FIELD_VERSIONING][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_MODIFIER_EXTENSION])) {
            $v = $this->getModifierExtension();
            foreach($validationRules[self::FIELD_MODIFIER_EXTENSION] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_BACKBONE_ELEMENT, self::FIELD_MODIFIER_EXTENSION, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_MODIFIER_EXTENSION])) {
                        $errs[self::FIELD_MODIFIER_EXTENSION] = [];
                    }
                    $errs[self::FIELD_MODIFIER_EXTENSION][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_EXTENSION])) {
            $v = $this->getExtension();
            foreach($validationRules[self::FIELD_EXTENSION] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_ELEMENT, self::FIELD_EXTENSION, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_EXTENSION])) {
                        $errs[self::FIELD_EXTENSION] = [];
                    }
                    $errs[self::FIELD_EXTENSION][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_ID])) {
            $v = $this->getId();
            foreach($validationRules[self::FIELD_ID] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_ELEMENT, self::FIELD_ID, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_ID])) {
                        $errs[self::FIELD_ID] = [];
                    }
                    $errs[self::FIELD_ID][$rule] = $err;
                }
            }
        }
        return $errs;
    }

    /**
     * @param null|string|\DOMElement $element
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRElementDefinition\FHIRElementDefinitionType $type
     * @param null|int $libxmlOpts
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRElementDefinition\FHIRElementDefinitionType
     */
    public static function xmlUnserialize($element = null, PHPFHIRTypeInterface $type = null, $libxmlOpts = 591872)
    {
        if (null === $element) {
            return null;
        }
        if (is_string($element)) {
            libxml_use_internal_errors(true);
            $dom = new \DOMDocument();
            $dom->loadXML($element, $libxmlOpts);
            if (false === $dom) {
                throw new \DomainException(sprintf('FHIRElementDefinitionType::xmlUnserialize - String provided is not parseable as XML: %s', implode(', ', array_map(function(\libXMLError $err) { return $err->message; }, libxml_get_errors()))));
            }
            libxml_use_internal_errors(false);
            $element = $dom->documentElement;
        }
        if (!($element instanceof \DOMElement)) {
            throw new \InvalidArgumentException(sprintf('FHIRElementDefinitionType::xmlUnserialize - $node value must be null, \\DOMElement, or valid XML string, %s seen', is_object($element) ? get_class($element) : gettype($element)));
        }
        if (null === $type) {
            $type = new FHIRElementDefinitionType(null);
        } elseif (!is_object($type) || !($type instanceof FHIRElementDefinitionType)) {
            throw new \RuntimeException(sprintf(
                'FHIRElementDefinitionType::xmlUnserialize - $type must be instance of \OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRElementDefinition\FHIRElementDefinitionType or null, %s seen.',
                is_object($type) ? get_class($type) : gettype($type)
            ));
        }
        if ('' === $type->_getFHIRXMLNamespace() && (null === $element->parentNode || $element->namespaceURI !== $element->parentNode->namespaceURI)) {
            $type->_setFHIRXMLNamespace($element->namespaceURI);
        }
        for($i = 0; $i < $element->childNodes->length; $i++) {
            $n = $element->childNodes->item($i);
            if (!($n instanceof \DOMElement)) {
                continue;
            }
            if (self::FIELD_CODE === $n->nodeName) {
                $type->setCode(FHIRUri::xmlUnserialize($n));
            } elseif (self::FIELD_PROFILE === $n->nodeName) {
                $type->addProfile(FHIRCanonical::xmlUnserialize($n));
            } elseif (self::FIELD_TARGET_PROFILE === $n->nodeName) {
                $type->addTargetProfile(FHIRCanonical::xmlUnserialize($n));
            } elseif (self::FIELD_AGGREGATION === $n->nodeName) {
                $type->addAggregation(FHIRAggregationMode::xmlUnserialize($n));
            } elseif (self::FIELD_VERSIONING === $n->nodeName) {
                $type->setVersioning(FHIRReferenceVersionRules::xmlUnserialize($n));
            } elseif (self::FIELD_MODIFIER_EXTENSION === $n->nodeName) {
                $type->addModifierExtension(FHIRExtension::xmlUnserialize($n));
            } elseif (self::FIELD_EXTENSION === $n->nodeName) {
                $type->addExtension(FHIRExtension::xmlUnserialize($n));
            } elseif (self::FIELD_ID === $n->nodeName) {
                $type->setId(FHIRStringPrimitive::xmlUnserialize($n));
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_CODE);
        if (null !== $n) {
            $pt = $type->getCode();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setCode($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_PROFILE);
        if (null !== $n) {
            $pt = $type->getProfile();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->addProfile($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_TARGET_PROFILE);
        if (null !== $n) {
            $pt = $type->getTargetProfile();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->addTargetProfile($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_ID);
        if (null !== $n) {
            $pt = $type->getId();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setId($n->nodeValue);
            }
        }
        return $type;
    }

    /**
     * @param null|\DOMElement $element
     * @param null|int $libxmlOpts
     * @return \DOMElement
     */
    public function xmlSerialize(\DOMElement $element = null, $libxmlOpts = 591872)
    {
        if (null === $element) {
            $dom = new \DOMDocument();
            $dom->loadXML($this->_getFHIRXMLElementDefinition(), $libxmlOpts);
            $element = $dom->documentElement;
        } elseif (null === $element->namespaceURI && '' !== ($xmlns = $this->_getFHIRXMLNamespace())) {
            $element->setAttribute('xmlns', $xmlns);
        }
        parent::xmlSerialize($element);
        if (null !== ($v = $this->getCode())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_CODE);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if ([] !== ($vs = $this->getProfile())) {
            foreach($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_PROFILE);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if ([] !== ($vs = $this->getTargetProfile())) {
            foreach($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_TARGET_PROFILE);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if ([] !== ($vs = $this->getAggregation())) {
            foreach($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_AGGREGATION);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if (null !== ($v = $this->getVersioning())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_VERSIONING);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        return $element;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $a = parent::jsonSerialize();
        if (null !== ($v = $this->getCode())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_CODE] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRUri::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_CODE_EXT] = $ext;
            }
        }
        if ([] !== ($vs = $this->getProfile())) {
            $vals = [];
            $exts = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $val = $v->getValue();
                $ext = $v->jsonSerialize();
                unset($ext[FHIRCanonical::FIELD_VALUE]);
                if (null !== $val) {
                    $vals[] = $val;
                }
                if ([] !== $ext) {
                    $exts[] = $ext;
                }
            }
            if ([] !== $vals) {
                $a[self::FIELD_PROFILE] = $vals;
            }
            if ([] !== $exts) {
                $a[self::FIELD_PROFILE_EXT] = $exts;
            }
        }
        if ([] !== ($vs = $this->getTargetProfile())) {
            $vals = [];
            $exts = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $val = $v->getValue();
                $ext = $v->jsonSerialize();
                unset($ext[FHIRCanonical::FIELD_VALUE]);
                if (null !== $val) {
                    $vals[] = $val;
                }
                if ([] !== $ext) {
                    $exts[] = $ext;
                }
            }
            if ([] !== $vals) {
                $a[self::FIELD_TARGET_PROFILE] = $vals;
            }
            if ([] !== $exts) {
                $a[self::FIELD_TARGET_PROFILE_EXT] = $exts;
            }
        }
        if ([] !== ($vs = $this->getAggregation())) {
            $vals = [];
            $exts = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $val = $v->getValue();
                $ext = $v->jsonSerialize();
                unset($ext[FHIRAggregationMode::FIELD_VALUE]);
                if (null !== $val) {
                    $vals[] = $val;
                }
                if ([] !== $ext) {
                    $exts[] = $ext;
                }
            }
            if ([] !== $vals) {
                $a[self::FIELD_AGGREGATION] = $vals;
            }
            if ([] !== $exts) {
                $a[self::FIELD_AGGREGATION_EXT] = $exts;
            }
        }
        if (null !== ($v = $this->getVersioning())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_VERSIONING] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRReferenceVersionRules::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_VERSIONING_EXT] = $ext;
            }
        }
        return $a;
    }


    /**
     * @return string
     */
    public function __toString()
    {
        return self::FHIR_TYPE_NAME;
    }
}