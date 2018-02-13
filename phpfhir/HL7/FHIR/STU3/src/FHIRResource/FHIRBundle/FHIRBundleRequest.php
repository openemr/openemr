<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRBundle;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A container for a collection of resources.
 */
class FHIRBundleRequest extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The HTTP verb for this entry in either a change history, or a transaction/ transaction response.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRHTTPVerb
     */
    public $method = null;

    /**
     * The URL for this entry, relative to the root (the address to which the request is posted).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public $url = null;

    /**
     * If the ETag values match, return a 304 Not Modified status. See the API documentation for ["Conditional Read"](http.html#cread).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $ifNoneMatch = null;

    /**
     * Only perform the operation if the last updated date matches. See the API documentation for ["Conditional Read"](http.html#cread).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRInstant
     */
    public $ifModifiedSince = null;

    /**
     * Only perform the operation if the Etag value matches. For more information, see the API section ["Managing Resource Contention"](http.html#concurrency).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $ifMatch = null;

    /**
     * Instruct the server not to perform the create if a specified resource already exists. For further information, see the API documentation for ["Conditional Create"](http.html#ccreate). This is just the query portion of the URL - what follows the "?" (not including the "?").
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $ifNoneExist = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Bundle.Request';

    /**
     * The HTTP verb for this entry in either a change history, or a transaction/ transaction response.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRHTTPVerb
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * The HTTP verb for this entry in either a change history, or a transaction/ transaction response.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRHTTPVerb $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * The URL for this entry, relative to the root (the address to which the request is posted).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * The URL for this entry, relative to the root (the address to which the request is posted).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUri $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * If the ETag values match, return a 304 Not Modified status. See the API documentation for ["Conditional Read"](http.html#cread).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getIfNoneMatch()
    {
        return $this->ifNoneMatch;
    }

    /**
     * If the ETag values match, return a 304 Not Modified status. See the API documentation for ["Conditional Read"](http.html#cread).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $ifNoneMatch
     * @return $this
     */
    public function setIfNoneMatch($ifNoneMatch)
    {
        $this->ifNoneMatch = $ifNoneMatch;
        return $this;
    }

    /**
     * Only perform the operation if the last updated date matches. See the API documentation for ["Conditional Read"](http.html#cread).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRInstant
     */
    public function getIfModifiedSince()
    {
        return $this->ifModifiedSince;
    }

    /**
     * Only perform the operation if the last updated date matches. See the API documentation for ["Conditional Read"](http.html#cread).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRInstant $ifModifiedSince
     * @return $this
     */
    public function setIfModifiedSince($ifModifiedSince)
    {
        $this->ifModifiedSince = $ifModifiedSince;
        return $this;
    }

    /**
     * Only perform the operation if the Etag value matches. For more information, see the API section ["Managing Resource Contention"](http.html#concurrency).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getIfMatch()
    {
        return $this->ifMatch;
    }

    /**
     * Only perform the operation if the Etag value matches. For more information, see the API section ["Managing Resource Contention"](http.html#concurrency).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $ifMatch
     * @return $this
     */
    public function setIfMatch($ifMatch)
    {
        $this->ifMatch = $ifMatch;
        return $this;
    }

    /**
     * Instruct the server not to perform the create if a specified resource already exists. For further information, see the API documentation for ["Conditional Create"](http.html#ccreate). This is just the query portion of the URL - what follows the "?" (not including the "?").
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getIfNoneExist()
    {
        return $this->ifNoneExist;
    }

    /**
     * Instruct the server not to perform the create if a specified resource already exists. For further information, see the API documentation for ["Conditional Create"](http.html#ccreate). This is just the query portion of the URL - what follows the "?" (not including the "?").
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $ifNoneExist
     * @return $this
     */
    public function setIfNoneExist($ifNoneExist)
    {
        $this->ifNoneExist = $ifNoneExist;
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
            if (isset($data['method'])) {
                $this->setMethod($data['method']);
            }
            if (isset($data['url'])) {
                $this->setUrl($data['url']);
            }
            if (isset($data['ifNoneMatch'])) {
                $this->setIfNoneMatch($data['ifNoneMatch']);
            }
            if (isset($data['ifModifiedSince'])) {
                $this->setIfModifiedSince($data['ifModifiedSince']);
            }
            if (isset($data['ifMatch'])) {
                $this->setIfMatch($data['ifMatch']);
            }
            if (isset($data['ifNoneExist'])) {
                $this->setIfNoneExist($data['ifNoneExist']);
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
        return $this->get_fhirElementName();
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $json = parent::jsonSerialize();
        if (isset($this->method)) {
            $json['method'] = $this->method;
        }
        if (isset($this->url)) {
            $json['url'] = $this->url;
        }
        if (isset($this->ifNoneMatch)) {
            $json['ifNoneMatch'] = $this->ifNoneMatch;
        }
        if (isset($this->ifModifiedSince)) {
            $json['ifModifiedSince'] = $this->ifModifiedSince;
        }
        if (isset($this->ifMatch)) {
            $json['ifMatch'] = $this->ifMatch;
        }
        if (isset($this->ifNoneExist)) {
            $json['ifNoneExist'] = $this->ifNoneExist;
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
            $sxe = new \SimpleXMLElement('<BundleRequest xmlns="http://hl7.org/fhir"></BundleRequest>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->method)) {
            $this->method->xmlSerialize(true, $sxe->addChild('method'));
        }
        if (isset($this->url)) {
            $this->url->xmlSerialize(true, $sxe->addChild('url'));
        }
        if (isset($this->ifNoneMatch)) {
            $this->ifNoneMatch->xmlSerialize(true, $sxe->addChild('ifNoneMatch'));
        }
        if (isset($this->ifModifiedSince)) {
            $this->ifModifiedSince->xmlSerialize(true, $sxe->addChild('ifModifiedSince'));
        }
        if (isset($this->ifMatch)) {
            $this->ifMatch->xmlSerialize(true, $sxe->addChild('ifMatch'));
        }
        if (isset($this->ifNoneExist)) {
            $this->ifNoneExist->xmlSerialize(true, $sxe->addChild('ifNoneExist'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
