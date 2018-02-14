<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRTestScript;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A structured set of tests against a FHIR server implementation to determine compliance against the FHIR specification.
 */
class FHIRTestScriptAssert extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The label would be used for tracking/logging purposes by test engines.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $label = null;

    /**
     * The description would be used by test engines for tracking and reporting purposes.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $description = null;

    /**
     * The direction to use for the assertion.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRAssertionDirectionType
     */
    public $direction = null;

    /**
     * Id of the source fixture used as the contents to be evaluated by either the "source/expression" or "sourceId/path" definition.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $compareToSourceId = null;

    /**
     * The fluentpath expression to evaluate against the source fixture. When compareToSourceId is defined, either compareToSourceExpression or compareToSourcePath must be defined, but not both.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $compareToSourceExpression = null;

    /**
     * XPath or JSONPath expression to evaluate against the source fixture. When compareToSourceId is defined, either compareToSourceExpression or compareToSourcePath must be defined, but not both.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $compareToSourcePath = null;

    /**
     * The content-type or mime-type to use for RESTful operation in the 'Content-Type' header.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRContentType
     */
    public $contentType = null;

    /**
     * The fluentpath expression to be evaluated against the request or response message contents - HTTP headers and payload.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $expression = null;

    /**
     * The HTTP header field name e.g. 'Location'.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $headerField = null;

    /**
     * The ID of a fixture.  Asserts that the response contains at a minimum the fixture specified by minimumId.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $minimumId = null;

    /**
     * Whether or not the test execution performs validation on the bundle navigation links.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public $navigationLinks = null;

    /**
     * The operator type defines the conditional behavior of the assert. If not defined, the default is equals.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRAssertionOperatorType
     */
    public $operator = null;

    /**
     * The XPath or JSONPath expression to be evaluated against the fixture representing the response received from server.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $path = null;

    /**
     * The request method or HTTP operation code to compare against that used by the client system under test.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRTestScriptRequestMethodCode
     */
    public $requestMethod = null;

    /**
     * The value to use in a comparison against the request URL path string.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $requestURL = null;

    /**
     * The type of the resource.  See http://build.fhir.org/resourcelist.html.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRFHIRDefinedType
     */
    public $resource = null;

    /**
     * okay | created | noContent | notModified | bad | forbidden | notFound | methodNotAllowed | conflict | gone | preconditionFailed | unprocessable.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRAssertionResponseTypes
     */
    public $response = null;

    /**
     * The value of the HTTP response code to be tested.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $responseCode = null;

    /**
     * The TestScript.rule this assert will evaluate.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRTestScript\FHIRTestScriptRule2
     */
    public $rule = null;

    /**
     * The TestScript.ruleset this assert will evaluate.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRTestScript\FHIRTestScriptRuleset1
     */
    public $ruleset = null;

    /**
     * Fixture to evaluate the XPath/JSONPath expression or the headerField  against.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRId
     */
    public $sourceId = null;

    /**
     * The ID of the Profile to validate against.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRId
     */
    public $validateProfileId = null;

    /**
     * The value to compare to.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $value = null;

    /**
     * Whether or not the test execution will produce a warning only on error for this assert.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public $warningOnly = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'TestScript.Assert';

    /**
     * The label would be used for tracking/logging purposes by test engines.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * The label would be used for tracking/logging purposes by test engines.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $label
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * The description would be used by test engines for tracking and reporting purposes.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * The description would be used by test engines for tracking and reporting purposes.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * The direction to use for the assertion.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRAssertionDirectionType
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * The direction to use for the assertion.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRAssertionDirectionType $direction
     * @return $this
     */
    public function setDirection($direction)
    {
        $this->direction = $direction;
        return $this;
    }

    /**
     * Id of the source fixture used as the contents to be evaluated by either the "source/expression" or "sourceId/path" definition.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getCompareToSourceId()
    {
        return $this->compareToSourceId;
    }

    /**
     * Id of the source fixture used as the contents to be evaluated by either the "source/expression" or "sourceId/path" definition.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $compareToSourceId
     * @return $this
     */
    public function setCompareToSourceId($compareToSourceId)
    {
        $this->compareToSourceId = $compareToSourceId;
        return $this;
    }

    /**
     * The fluentpath expression to evaluate against the source fixture. When compareToSourceId is defined, either compareToSourceExpression or compareToSourcePath must be defined, but not both.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getCompareToSourceExpression()
    {
        return $this->compareToSourceExpression;
    }

    /**
     * The fluentpath expression to evaluate against the source fixture. When compareToSourceId is defined, either compareToSourceExpression or compareToSourcePath must be defined, but not both.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $compareToSourceExpression
     * @return $this
     */
    public function setCompareToSourceExpression($compareToSourceExpression)
    {
        $this->compareToSourceExpression = $compareToSourceExpression;
        return $this;
    }

    /**
     * XPath or JSONPath expression to evaluate against the source fixture. When compareToSourceId is defined, either compareToSourceExpression or compareToSourcePath must be defined, but not both.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getCompareToSourcePath()
    {
        return $this->compareToSourcePath;
    }

    /**
     * XPath or JSONPath expression to evaluate against the source fixture. When compareToSourceId is defined, either compareToSourceExpression or compareToSourcePath must be defined, but not both.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $compareToSourcePath
     * @return $this
     */
    public function setCompareToSourcePath($compareToSourcePath)
    {
        $this->compareToSourcePath = $compareToSourcePath;
        return $this;
    }

    /**
     * The content-type or mime-type to use for RESTful operation in the 'Content-Type' header.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRContentType
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * The content-type or mime-type to use for RESTful operation in the 'Content-Type' header.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRContentType $contentType
     * @return $this
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
        return $this;
    }

    /**
     * The fluentpath expression to be evaluated against the request or response message contents - HTTP headers and payload.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * The fluentpath expression to be evaluated against the request or response message contents - HTTP headers and payload.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $expression
     * @return $this
     */
    public function setExpression($expression)
    {
        $this->expression = $expression;
        return $this;
    }

    /**
     * The HTTP header field name e.g. 'Location'.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getHeaderField()
    {
        return $this->headerField;
    }

    /**
     * The HTTP header field name e.g. 'Location'.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $headerField
     * @return $this
     */
    public function setHeaderField($headerField)
    {
        $this->headerField = $headerField;
        return $this;
    }

    /**
     * The ID of a fixture.  Asserts that the response contains at a minimum the fixture specified by minimumId.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getMinimumId()
    {
        return $this->minimumId;
    }

    /**
     * The ID of a fixture.  Asserts that the response contains at a minimum the fixture specified by minimumId.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $minimumId
     * @return $this
     */
    public function setMinimumId($minimumId)
    {
        $this->minimumId = $minimumId;
        return $this;
    }

    /**
     * Whether or not the test execution performs validation on the bundle navigation links.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public function getNavigationLinks()
    {
        return $this->navigationLinks;
    }

    /**
     * Whether or not the test execution performs validation on the bundle navigation links.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRBoolean $navigationLinks
     * @return $this
     */
    public function setNavigationLinks($navigationLinks)
    {
        $this->navigationLinks = $navigationLinks;
        return $this;
    }

    /**
     * The operator type defines the conditional behavior of the assert. If not defined, the default is equals.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRAssertionOperatorType
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * The operator type defines the conditional behavior of the assert. If not defined, the default is equals.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRAssertionOperatorType $operator
     * @return $this
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;
        return $this;
    }

    /**
     * The XPath or JSONPath expression to be evaluated against the fixture representing the response received from server.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * The XPath or JSONPath expression to be evaluated against the fixture representing the response received from server.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * The request method or HTTP operation code to compare against that used by the client system under test.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRTestScriptRequestMethodCode
     */
    public function getRequestMethod()
    {
        return $this->requestMethod;
    }

    /**
     * The request method or HTTP operation code to compare against that used by the client system under test.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRTestScriptRequestMethodCode $requestMethod
     * @return $this
     */
    public function setRequestMethod($requestMethod)
    {
        $this->requestMethod = $requestMethod;
        return $this;
    }

    /**
     * The value to use in a comparison against the request URL path string.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getRequestURL()
    {
        return $this->requestURL;
    }

    /**
     * The value to use in a comparison against the request URL path string.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $requestURL
     * @return $this
     */
    public function setRequestURL($requestURL)
    {
        $this->requestURL = $requestURL;
        return $this;
    }

    /**
     * The type of the resource.  See http://build.fhir.org/resourcelist.html.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRFHIRDefinedType
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * The type of the resource.  See http://build.fhir.org/resourcelist.html.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRFHIRDefinedType $resource
     * @return $this
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * okay | created | noContent | notModified | bad | forbidden | notFound | methodNotAllowed | conflict | gone | preconditionFailed | unprocessable.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRAssertionResponseTypes
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * okay | created | noContent | notModified | bad | forbidden | notFound | methodNotAllowed | conflict | gone | preconditionFailed | unprocessable.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRAssertionResponseTypes $response
     * @return $this
     */
    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * The value of the HTTP response code to be tested.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * The value of the HTTP response code to be tested.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $responseCode
     * @return $this
     */
    public function setResponseCode($responseCode)
    {
        $this->responseCode = $responseCode;
        return $this;
    }

    /**
     * The TestScript.rule this assert will evaluate.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRTestScript\FHIRTestScriptRule2
     */
    public function getRule()
    {
        return $this->rule;
    }

    /**
     * The TestScript.rule this assert will evaluate.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRTestScript\FHIRTestScriptRule2 $rule
     * @return $this
     */
    public function setRule($rule)
    {
        $this->rule = $rule;
        return $this;
    }

    /**
     * The TestScript.ruleset this assert will evaluate.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRTestScript\FHIRTestScriptRuleset1
     */
    public function getRuleset()
    {
        return $this->ruleset;
    }

    /**
     * The TestScript.ruleset this assert will evaluate.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRTestScript\FHIRTestScriptRuleset1 $ruleset
     * @return $this
     */
    public function setRuleset($ruleset)
    {
        $this->ruleset = $ruleset;
        return $this;
    }

    /**
     * Fixture to evaluate the XPath/JSONPath expression or the headerField  against.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRId
     */
    public function getSourceId()
    {
        return $this->sourceId;
    }

    /**
     * Fixture to evaluate the XPath/JSONPath expression or the headerField  against.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRId $sourceId
     * @return $this
     */
    public function setSourceId($sourceId)
    {
        $this->sourceId = $sourceId;
        return $this;
    }

    /**
     * The ID of the Profile to validate against.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRId
     */
    public function getValidateProfileId()
    {
        return $this->validateProfileId;
    }

    /**
     * The ID of the Profile to validate against.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRId $validateProfileId
     * @return $this
     */
    public function setValidateProfileId($validateProfileId)
    {
        $this->validateProfileId = $validateProfileId;
        return $this;
    }

    /**
     * The value to compare to.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * The value to compare to.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Whether or not the test execution will produce a warning only on error for this assert.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public function getWarningOnly()
    {
        return $this->warningOnly;
    }

    /**
     * Whether or not the test execution will produce a warning only on error for this assert.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRBoolean $warningOnly
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
            if (isset($data['rule'])) {
                $this->setRule($data['rule']);
            }
            if (isset($data['ruleset'])) {
                $this->setRuleset($data['ruleset']);
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
        } else if (null !== $data) {
            throw new \InvalidArgumentException('$data expected to be array of values, saw "'.gettype($data).'"');
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
    public function jsonSerialize()
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
        if (isset($this->rule)) {
            $json['rule'] = $this->rule;
        }
        if (isset($this->ruleset)) {
            $json['ruleset'] = $this->ruleset;
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
        if (isset($this->rule)) {
            $this->rule->xmlSerialize(true, $sxe->addChild('rule'));
        }
        if (isset($this->ruleset)) {
            $this->ruleset->xmlSerialize(true, $sxe->addChild('ruleset'));
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
