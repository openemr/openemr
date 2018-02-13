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
class FHIRBundleResponse extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The status code returned by processing this entry. The status SHALL start with a 3 digit HTTP code (e.g. 404) and may contain the standard HTTP description associated with the status code.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $status = null;

    /**
     * The location header created by processing this operation.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public $location = null;

    /**
     * The etag for the resource, it the operation for the entry produced a versioned resource (see [Resource Metadata and Versioning](http.html#versioning) and [Managing Resource Contention](http.html#concurrency)).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $etag = null;

    /**
     * The date/time that the resource was modified on the server.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRInstant
     */
    public $lastModified = null;

    /**
     * An OperationOutcome containing hints and warnings produced as part of processing this entry in a batch or transaction.
     * @var \HL7\FHIR\STU3\FHIRResourceContainer
     */
    public $outcome = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Bundle.Response';

    /**
     * The status code returned by processing this entry. The status SHALL start with a 3 digit HTTP code (e.g. 404) and may contain the standard HTTP description associated with the status code.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * The status code returned by processing this entry. The status SHALL start with a 3 digit HTTP code (e.g. 404) and may contain the standard HTTP description associated with the status code.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * The location header created by processing this operation.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * The location header created by processing this operation.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUri $location
     * @return $this
     */
    public function setLocation($location)
    {
        $this->location = $location;
        return $this;
    }

    /**
     * The etag for the resource, it the operation for the entry produced a versioned resource (see [Resource Metadata and Versioning](http.html#versioning) and [Managing Resource Contention](http.html#concurrency)).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getEtag()
    {
        return $this->etag;
    }

    /**
     * The etag for the resource, it the operation for the entry produced a versioned resource (see [Resource Metadata and Versioning](http.html#versioning) and [Managing Resource Contention](http.html#concurrency)).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $etag
     * @return $this
     */
    public function setEtag($etag)
    {
        $this->etag = $etag;
        return $this;
    }

    /**
     * The date/time that the resource was modified on the server.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRInstant
     */
    public function getLastModified()
    {
        return $this->lastModified;
    }

    /**
     * The date/time that the resource was modified on the server.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRInstant $lastModified
     * @return $this
     */
    public function setLastModified($lastModified)
    {
        $this->lastModified = $lastModified;
        return $this;
    }

    /**
     * An OperationOutcome containing hints and warnings produced as part of processing this entry in a batch or transaction.
     * @return mixed
     */
    public function getOutcome()
    {
        return isset($this->outcome) ? $this->outcome->jsonSerialize() : null;
    }

    /**
     * An OperationOutcome containing hints and warnings produced as part of processing this entry in a batch or transaction.
     * @param \HL7\FHIR\STU3\FHIRResourceContainer $outcome
     * @return $this
     */
    public function setOutcome($outcome)
    {
        $this->outcome = $outcome;
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
            if (isset($data['status'])) {
                $this->setStatus($data['status']);
            }
            if (isset($data['location'])) {
                $this->setLocation($data['location']);
            }
            if (isset($data['etag'])) {
                $this->setEtag($data['etag']);
            }
            if (isset($data['lastModified'])) {
                $this->setLastModified($data['lastModified']);
            }
            if (isset($data['outcome'])) {
                $this->setOutcome($data['outcome']);
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
        if (isset($this->status)) {
            $json['status'] = $this->status;
        }
        if (isset($this->location)) {
            $json['location'] = $this->location;
        }
        if (isset($this->etag)) {
            $json['etag'] = $this->etag;
        }
        if (isset($this->lastModified)) {
            $json['lastModified'] = $this->lastModified;
        }
        if (isset($this->outcome)) {
            $json['outcome'] = $this->outcome;
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
            $sxe = new \SimpleXMLElement('<BundleResponse xmlns="http://hl7.org/fhir"></BundleResponse>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->status)) {
            $this->status->xmlSerialize(true, $sxe->addChild('status'));
        }
        if (isset($this->location)) {
            $this->location->xmlSerialize(true, $sxe->addChild('location'));
        }
        if (isset($this->etag)) {
            $this->etag->xmlSerialize(true, $sxe->addChild('etag'));
        }
        if (isset($this->lastModified)) {
            $this->lastModified->xmlSerialize(true, $sxe->addChild('lastModified'));
        }
        if (isset($this->outcome)) {
            $this->outcome->xmlSerialize(true, $sxe->addChild('outcome'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
