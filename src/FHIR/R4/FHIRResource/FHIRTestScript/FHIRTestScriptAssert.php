<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRTestScript;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 *
 * Class creation date: June 14th, 2019
 *
 * PHPFHIR Copyright:
 *
 * Copyright 2016-2017 Daniel Carbone (daniel.p.carbone@gmail.com)
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
 *   Generated on Thu, Dec 27, 2018 22:37+1100 for FHIR v4.0.0
 *
 *   Note: the schemas & schematrons do not contain all of the rules about what makes resources
 *   valid. Implementers will still need to be familiar with the content of the specification and with
 *   any profiles that apply to the resources in order to make a conformant implementation.
 *
 */

use OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement;

/**
 * A structured set of tests against a FHIR server or client implementation to determine compliance against the FHIR specification.
 */
class FHIRTestScriptAssert extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The label would be used for tracking/logging purposes by test engines.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $label = null;

    /**
     * The description would be used by test engines for tracking and reporting purposes.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $description = null;

    /**
     * The direction to use for the assertion.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRAssertionDirectionType
     */
    public $direction = null;

    /**
     * Id of the source fixture used as the contents to be evaluated by either the "source/expression" or "sourceId/path" definition.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $compareToSourceId = null;

    /**
     * The FHIRPath expression to evaluate against the source fixture. When compareToSourceId is defined, either compareToSourceExpression or compareToSourcePath must be defined, but not both.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $compareToSourceExpression = null;

    /**
     * XPath or JSONPath expression to evaluate against the source fixture. When compareToSourceId is defined, either compareToSourceExpression or compareToSourcePath must be defined, but not both.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $compareToSourcePath = null;

    /**
     * The mime-type contents to compare against the request or response message 'Content-Type' header.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCode
     */
    public $contentType = null;

    /**
     * The FHIRPath expression to be evaluated against the request or response message contents - HTTP headers and payload.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $expression = null;

    /**
     * The HTTP header field name e.g. 'Location'.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $headerField = null;

    /**
     * The ID of a fixture.  Asserts that the response contains at a minimum the fixture specified by minimumId.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $minimumId = null;

    /**
     * Whether or not the test execution performs validation on the bundle navigation links.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $navigationLinks = null;

    /**
     * The operator type defines the conditional behavior of the assert. If not defined, the default is equals.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRAssertionOperatorType
     */
    public $operator = null;

    /**
     * The XPath or JSONPath expression to be evaluated against the fixture representing the response received from server.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $path = null;

    /**
     * The request method or HTTP operation code to compare against that used by the client system under test.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRTestScriptRequestMethodCode
     */
    public $requestMethod = null;

    /**
     * The value to use in a comparison against the request URL path string.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $requestURL = null;

    /**
     * The type of the resource.  See http://build.fhir.org/resourcelist.html.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCode
     */
    public $resource = null;

    /**
     * okay | created | noContent | notModified | bad | forbidden | notFound | methodNotAllowed | conflict | gone | preconditionFailed | unprocessable.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRAssertionResponseTypes
     */
    public $response = null;

    /**
     * The value of the HTTP response code to be tested.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $responseCode = null;

    /**
     * Fixture to evaluate the XPath/JSONPath expression or the headerField  against.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    public $sourceId = null;

    /**
     * The ID of the Profile to validate against.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    public $validateProfileId = null;

    /**
     * The value to compare to.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $value = null;

    /**
     * Whether or not the test execution will produce a warning only on error for this assert.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $warningOnly = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'TestScript.Assert';

    /**
     * The label would be used for tracking/logging purposes by test engines.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * The label would be used for tracking/logging purposes by test engines.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $label
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * The description would be used by test engines for tracking and reporting purposes.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * The description would be used by test engines for tracking and reporting purposes.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * The direction to use for the assertion.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRAssertionDirectionType
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * The direction to use for the assertion.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRAssertionDirectionType $direction
     * @return $this
     */
    public function setDirection($direction)
    {
        $this->direction = $direction;
        return $this;
    }

    /**
     * Id of the source fixture used as the contents to be evaluated by either the "source/expression" or "sourceId/path" definition.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getCompareToSourceId()
    {
        return $this->compareToSourceId;
    }

    /**
     * Id of the source fixture used as the contents to be evaluated by either the "source/expression" or "sourceId/path" definition.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $compareToSourceId
     * @return $this
     */
    public function setCompareToSourceId($compareToSourceId)
    {
        $this->compareToSourceId = $compareToSourceId;
        return $this;
    }

    /**
     * The FHIRPath expression to evaluate against the source fixture. When compareToSourceId is defined, either compareToSourceExpression or compareToSourcePath must be defined, but not both.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getCompareToSourceExpression()
    {
        return $this->compareToSourceExpression;
    }

    /**
     * The FHIRPath expression to evaluate against the source fixture. When compareToSourceId is defined, either compareToSourceExpression or compareToSourcePath must be defined, but not both.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $compareToSourceExpression
     * @return $this
     */
    public function setCompareToSourceExpression($compareToSourceExpression)
    {
        $this->compareToSourceExpression = $compareToSourceExpression;
        return $this;
    }

    /**
     * XPath or JSONPath expression to evaluate against the source fixture. When compareToSourceId is defined, either compareToSourceExpression or compareToSourcePath must be defined, but not both.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getCompareToSourcePath()
    {
        return $this->compareToSourcePath;
    }

    /**
     * XPath or JSONPath expression to evaluate against the source fixture. When compareToSourceId is defined, either compareToSourceExpression or compareToSourcePath must be defined, but not both.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $compareToSourcePath
     * @return $this
     */
    public function setCompareToSourcePath($compareToSourcePath)
    {
        $this->compareToSourcePath = $compareToSourcePath;
        return $this;
    }

    /**
     * The mime-type contents to compare against the request or response message 'Content-Type' header.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCode
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * The mime-type contents to compare against the request or response message 'Content-Type' header.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCode $contentType
     * @return $this
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
        return $this;
    }

    /**
     * The FHIRPath expression to be evaluated against the request or response message contents - HTTP headers and payload.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * The FHIRPath expression to be evaluated against the request or response message contents - HTTP headers and payload.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $expression
     * @return $this
     */
    public function setExpression($expression)
    {
        $this->expression = $expression;
        return $this;
    }

    /**
     * The HTTP header field name e.g. 'Location'.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getHeaderField()
    {
        return $this->headerField;
    }

    /**
     * The HTTP header field name e.g. 'Location'.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $headerField
     * @return $this
     */
    public function setHeaderField($headerField)
    {
        $this->headerField = $headerField;
        return $this;
    }

    /**
     * The ID of a fixture.  Asserts that the response contains at a minimum the fixture specified by minimumId.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getMinimumId()
    {
        return $this->minimumId;
    }

    /**
     * The ID of a fixture.  Asserts that the response contains at a minimum the fixture specified by minimumId.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $minimumId
     * @return $this
     */
    public function setMinimumId($minimumId)
    {
        $this->minimumId = $minimumId;
        return $this;
    }

    /**
     * Whether or not the test execution performs validation on the bundle navigation links.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getNavigationLinks()
    {
        return $this->navigationLinks;
    }

    /**
     * Whether or not the test execution performs validation on the bundle navigation links.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $navigationLinks
     * @return $this
     */
    public function setNavigationLinks($navigationLinks)
    {
        $this->navigationLinks = $navigationLinks;
        return $this;
    }

    /**
     * The operator type defines the conditional behavior of the assert. If not defined, the default is equals.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRAssertionOperatorType
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * The operator type defines the conditional behavior of the assert. If not defined, the default is equals.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRAssertionOperatorType $operator
     * @return $this
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;
        return $this;
    }

    /**
     * The XPath or JSONPath expression to be evaluated against the fixture representing the response received from server.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * The XPath or JSONPath expression to be evaluated against the fixture representing the response received from server.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * The request method or HTTP operation code to compare against that used by the client system under test.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRTestScriptRequestMethodCode
     */
    public function getRequestMethod()
    {
        return $this->requestMethod;
    }

    /**
     * The request method or HTTP operation code to compare against that used by the client system under test.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRTestScriptRequestMethodCode $requestMethod
     * @return $this
     */
    public function setRequestMethod($requestMethod)
    {
        $this->requestMethod = $requestMethod;
        return $this;
    }

    /**
     * The value to use in a comparison against the request URL path string.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getRequestURL()
    {
        return $this->requestURL;
    }

    /**
     * The value to use in a comparison against the request URL path string.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $requestURL
     * @return $this
     */
    public function setRequestURL($requestURL)
    {
        $this->requestURL = $requestURL;
        return $this;
    }

    /**
     * The type of the resource.  See http://build.fhir.org/resourcelist.html.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCode
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * The type of the resource.  See http://build.fhir.org/resourcelist.html.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCode $resource
     * @return $this
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * okay | created | noContent | notModified | bad | forbidden | notFound | methodNotAllowed | conflict | gone | preconditionFailed | unprocessable.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRAssertionResponseTypes
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * okay | created | noContent | notModified | bad | forbidden | notFound | methodNotAllowed | conflict | gone | preconditionFailed | unprocessable.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRAssertionResponseTypes $response
     * @return $this
     */
    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * The value of the HTTP response code to be tested.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * The value of the HTTP response code to be tested.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $responseCode
     * @return $this
     */
    public function setResponseCode($responseCode)
    {
        $this->responseCode = $responseCode;
        return $this;
    }

    /**
     * Fixture to evaluate the XPath/JSONPath expression or the headerField  against.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    public function getSourceId()
    {
        return $this->sourceId;
    }

    /**
     * Fixture to evaluate the XPath/JSONPath expression or the headerField  against.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRId $sourceId
     * @return $this
     */
    public function setSourceId($sourceId)
    {
        $this->sourceId = $sourceId;
        return $this;
    }

    /**
     * The ID of the Profile to validate against.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    public function getValidateProfileId()
    {
        return $this->validateProfileId;
    }

    /**
     * The ID of the Profile to validate against.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRId $validateProfileId
     * @return $this
     */
    public function setValidateProfileId($validateProfileId)
    {
        $this->validateProfileId = $validateProfileId;
        return $this;
    }

    /**
     * The value to compare to.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * The value to compare to.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Whether or not the test execution will produce a warning only on error for this assert.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getWarningOnly()
    {
        return $this->warningOnly;
    }

    /**
     * Whether or not the test execution will produce a warning only on error for this assert.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $warningOnly
     * @return $this
     */
    public function setWarningOnly($warningOnly)
    {
        $this->warningOnly = $warningOnly;
        return $this;
    }

    /**
     * @return string
     */
    public function get_fhirElementName()
    {
        return $this->_fhirElementName;
    }

    /**
     * @param mixed $data
     */
    public function __construct($data = [])
    {
        if (is_array($data)) {
            if (isset($data['label'])) {
                $this->setLabel($data['label']);
            }
            if (isset($data['description'])) {
                $this->setDescription($data['description']);
            }
            if (isset($data['direction'])) {
                $this->setDirection($data['direction']);
            }
            if (isset($data['compareToSourceId'])) {
                $this->setCompareToSourceId($data['compareToSourceId']);
            }
            if (isset($data['compareToSourceExpression'])) {
                $this->setCompareToSourceExpression($data['compareToSourceExpression']);
            }
            if (isset($data['compareToSourcePath'])) {
                $this->setCompareToSourcePath($data['compareToSourcePath']);
            }
            if (isset($data['contentType'])) {
                $this->setContentType($data['contentType']);
            }
            if (isset($data['expression'])) {
                $this->setExpression($data['expression']);
            }
            if (isset($data['headerField'])) {
                $this->setHeaderField($data['headerField']);
            }
            if (isset($data['minimumId'])) {
                $this->setMinimumId($data['minimumId']);
            }
            if (isset($data['navigationLinks'])) {
                $this->setNavigationLinks($data['navigationLinks']);
            }
            if (isset($data['operator'])) {
                $this->setOperator($data['operator']);
            }
            if (isset($data['path'])) {
                $this->setPath($data['path']);
            }
            if (isset($data['requestMethod'])) {
                $this->setRequestMethod($data['requestMethod']);
            }
            if (isset($data['requestURL'])) {
                $this->setRequestURL($data['requestURL']);
            }
            if (isset($data['resource'])) {
                $this->setResource($data['resource']);
            }
            if (isset($data['response'])) {
                $this->setResponse($data['response']);
            }
            if (isset($data['responseCode'])) {
                $this->setResponseCode($data['responseCode']);
            }
            if (isset($data['sourceId'])) {
                $this->setSourceId($data['sourceId']);
            }
            if (isset($data['validateProfileId'])) {
                $this->setValidateProfileId($data['validateProfileId']);
            }
            if (isset($data['value'])) {
                $this->setValue($data['value']);
            }
            if (isset($data['warningOnly'])) {
                $this->setWarningOnly($data['warningOnly']);
            }
        } elseif (null !== $data) {
            throw new \InvalidArgumentException('$data expected to be array of values, saw "' . gettype($data) . '"');
        }
        parent::__construct($data);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getValue();
    }

    /**
     * @return array
     */
    public function jsonSerialize(): mixed
    {
        $json = parent::jsonSerialize();
        if (isset($this->label)) {
            $json['label'] = $this->label;
        }
        if (isset($this->description)) {
            $json['description'] = $this->description;
        }
        if (isset($this->direction)) {
            $json['direction'] = $this->direction;
        }
        if (isset($this->compareToSourceId)) {
            $json['compareToSourceId'] = $this->compareToSourceId;
        }
        if (isset($this->compareToSourceExpression)) {
            $json['compareToSourceExpression'] = $this->compareToSourceExpression;
        }
        if (isset($this->compareToSourcePath)) {
            $json['compareToSourcePath'] = $this->compareToSourcePath;
        }
        if (isset($this->contentType)) {
            $json['contentType'] = $this->contentType;
        }
        if (isset($this->expression)) {
            $json['expression'] = $this->expression;
        }
        if (isset($this->headerField)) {
            $json['headerField'] = $this->headerField;
        }
        if (isset($this->minimumId)) {
            $json['minimumId'] = $this->minimumId;
        }
        if (isset($this->navigationLinks)) {
            $json['navigationLinks'] = $this->navigationLinks;
        }
        if (isset($this->operator)) {
            $json['operator'] = $this->operator;
        }
        if (isset($this->path)) {
            $json['path'] = $this->path;
        }
        if (isset($this->requestMethod)) {
            $json['requestMethod'] = $this->requestMethod;
        }
        if (isset($this->requestURL)) {
            $json['requestURL'] = $this->requestURL;
        }
        if (isset($this->resource)) {
            $json['resource'] = $this->resource;
        }
        if (isset($this->response)) {
            $json['response'] = $this->response;
        }
        if (isset($this->responseCode)) {
            $json['responseCode'] = $this->responseCode;
        }
        if (isset($this->sourceId)) {
            $json['sourceId'] = $this->sourceId;
        }
        if (isset($this->validateProfileId)) {
            $json['validateProfileId'] = $this->validateProfileId;
        }
        if (isset($this->value)) {
            $json['value'] = $this->value;
        }
        if (isset($this->warningOnly)) {
            $json['warningOnly'] = $this->warningOnly;
        }
        return $json;
    }

    /**
     * @param boolean $returnSXE
     * @param \SimpleXMLElement $sxe
     * @return string|\SimpleXMLElement
     */
    public function xmlSerialize($returnSXE = false, $sxe = null)
    {
        if (null === $sxe) {
            $sxe = new \SimpleXMLElement('<TestScriptAssert xmlns="http://hl7.org/fhir"></TestScriptAssert>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->label)) {
            $this->label->xmlSerialize(true, $sxe->addChild('label'));
        }
        if (isset($this->description)) {
            $this->description->xmlSerialize(true, $sxe->addChild('description'));
        }
        if (isset($this->direction)) {
            $this->direction->xmlSerialize(true, $sxe->addChild('direction'));
        }
        if (isset($this->compareToSourceId)) {
            $this->compareToSourceId->xmlSerialize(true, $sxe->addChild('compareToSourceId'));
        }
        if (isset($this->compareToSourceExpression)) {
            $this->compareToSourceExpression->xmlSerialize(true, $sxe->addChild('compareToSourceExpression'));
        }
        if (isset($this->compareToSourcePath)) {
            $this->compareToSourcePath->xmlSerialize(true, $sxe->addChild('compareToSourcePath'));
        }
        if (isset($this->contentType)) {
            $this->contentType->xmlSerialize(true, $sxe->addChild('contentType'));
        }
        if (isset($this->expression)) {
            $this->expression->xmlSerialize(true, $sxe->addChild('expression'));
        }
        if (isset($this->headerField)) {
            $this->headerField->xmlSerialize(true, $sxe->addChild('headerField'));
        }
        if (isset($this->minimumId)) {
            $this->minimumId->xmlSerialize(true, $sxe->addChild('minimumId'));
        }
        if (isset($this->navigationLinks)) {
            $this->navigationLinks->xmlSerialize(true, $sxe->addChild('navigationLinks'));
        }
        if (isset($this->operator)) {
            $this->operator->xmlSerialize(true, $sxe->addChild('operator'));
        }
        if (isset($this->path)) {
            $this->path->xmlSerialize(true, $sxe->addChild('path'));
        }
        if (isset($this->requestMethod)) {
            $this->requestMethod->xmlSerialize(true, $sxe->addChild('requestMethod'));
        }
        if (isset($this->requestURL)) {
            $this->requestURL->xmlSerialize(true, $sxe->addChild('requestURL'));
        }
        if (isset($this->resource)) {
            $this->resource->xmlSerialize(true, $sxe->addChild('resource'));
        }
        if (isset($this->response)) {
            $this->response->xmlSerialize(true, $sxe->addChild('response'));
        }
        if (isset($this->responseCode)) {
            $this->responseCode->xmlSerialize(true, $sxe->addChild('responseCode'));
        }
        if (isset($this->sourceId)) {
            $this->sourceId->xmlSerialize(true, $sxe->addChild('sourceId'));
        }
        if (isset($this->validateProfileId)) {
            $this->validateProfileId->xmlSerialize(true, $sxe->addChild('validateProfileId'));
        }
        if (isset($this->value)) {
            $this->value->xmlSerialize(true, $sxe->addChild('value'));
        }
        if (isset($this->warningOnly)) {
            $this->warningOnly->xmlSerialize(true, $sxe->addChild('warningOnly'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
