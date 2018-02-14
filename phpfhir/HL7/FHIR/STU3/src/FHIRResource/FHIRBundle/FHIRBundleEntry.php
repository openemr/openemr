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
class FHIRBundleEntry extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * A series of links that provide context to this entry.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRBundle\FHIRBundleLink[]
     */
    public $link = [];

    /**
     * The Absolute URL for the resource.  The fullUrl SHALL not disagree with the id in the resource. The fullUrl is a version independent reference to the resource. The fullUrl element SHALL have a value except that:
* fullUrl can be empty on a POST (although it does not need to when specifying a temporary id for reference in the bundle)
* Results from operations might involve resources that are not identified.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public $fullUrl = null;

    /**
     * The Resources for the entry.
     * @var \HL7\FHIR\STU3\FHIRResourceContainer
     */
    public $resource = null;

    /**
     * Information about the search process that lead to the creation of this entry.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRBundle\FHIRBundleSearch
     */
    public $search = null;

    /**
     * Additional information about how this entry should be processed as part of a transaction.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRBundle\FHIRBundleRequest
     */
    public $request = null;

    /**
     * Additional information about how this entry should be processed as part of a transaction.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRBundle\FHIRBundleResponse
     */
    public $response = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Bundle.Entry';

    /**
     * A series of links that provide context to this entry.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRBundle\FHIRBundleLink[]
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * A series of links that provide context to this entry.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRBundle\FHIRBundleLink $link
     * @return $this
     */
    public function addLink($link)
    {
        $this->link[] = $link;
        return $this;
    }

    /**
     * The Absolute URL for the resource.  The fullUrl SHALL not disagree with the id in the resource. The fullUrl is a version independent reference to the resource. The fullUrl element SHALL have a value except that:
* fullUrl can be empty on a POST (although it does not need to when specifying a temporary id for reference in the bundle)
* Results from operations might involve resources that are not identified.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public function getFullUrl()
    {
        return $this->fullUrl;
    }

    /**
     * The Absolute URL for the resource.  The fullUrl SHALL not disagree with the id in the resource. The fullUrl is a version independent reference to the resource. The fullUrl element SHALL have a value except that:
* fullUrl can be empty on a POST (although it does not need to when specifying a temporary id for reference in the bundle)
* Results from operations might involve resources that are not identified.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUri $fullUrl
     * @return $this
     */
    public function setFullUrl($fullUrl)
    {
        $this->fullUrl = $fullUrl;
        return $this;
    }

    /**
     * The Resources for the entry.
     * @return mixed
     */
    public function getResource()
    {
        return isset($this->resource) ? $this->resource->jsonSerialize() : null;
    }

    /**
     * The Resources for the entry.
     * @param \HL7\FHIR\STU3\FHIRResourceContainer $resource
     * @return $this
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * Information about the search process that lead to the creation of this entry.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRBundle\FHIRBundleSearch
     */
    public function getSearch()
    {
        return $this->search;
    }

    /**
     * Information about the search process that lead to the creation of this entry.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRBundle\FHIRBundleSearch $search
     * @return $this
     */
    public function setSearch($search)
    {
        $this->search = $search;
        return $this;
    }

    /**
     * Additional information about how this entry should be processed as part of a transaction.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRBundle\FHIRBundleRequest
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Additional information about how this entry should be processed as part of a transaction.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRBundle\FHIRBundleRequest $request
     * @return $this
     */
    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Additional information about how this entry should be processed as part of a transaction.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRBundle\FHIRBundleResponse
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Additional information about how this entry should be processed as part of a transaction.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRBundle\FHIRBundleResponse $response
     * @return $this
     */
    public function setResponse($response)
    {
        $this->response = $response;
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
            if (isset($data['link'])) {
                if (is_array($data['link'])) {
                    foreach ($data['link'] as $d) {
                        $this->addLink($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"link" must be array of objects or null, '.gettype($data['link']).' seen.');
                }
            }
            if (isset($data['fullUrl'])) {
                $this->setFullUrl($data['fullUrl']);
            }
            if (isset($data['resource'])) {
                $this->setResource($data['resource']);
            }
            if (isset($data['search'])) {
                $this->setSearch($data['search']);
            }
            if (isset($data['request'])) {
                $this->setRequest($data['request']);
            }
            if (isset($data['response'])) {
                $this->setResponse($data['response']);
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
        if (0 < count($this->link)) {
            $json['link'] = [];
            foreach ($this->link as $link) {
                $json['link'][] = $link;
            }
        }
        if (isset($this->fullUrl)) {
            $json['fullUrl'] = $this->fullUrl;
        }
        if (isset($this->resource)) {
            $json['resource'] = $this->resource;
        }
        if (isset($this->search)) {
            $json['search'] = $this->search;
        }
        if (isset($this->request)) {
            $json['request'] = $this->request;
        }
        if (isset($this->response)) {
            $json['response'] = $this->response;
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
            $sxe = new \SimpleXMLElement('<BundleEntry xmlns="http://hl7.org/fhir"></BundleEntry>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->link)) {
            foreach ($this->link as $link) {
                $link->xmlSerialize(true, $sxe->addChild('link'));
            }
        }
        if (isset($this->fullUrl)) {
            $this->fullUrl->xmlSerialize(true, $sxe->addChild('fullUrl'));
        }
        if (isset($this->resource)) {
            $this->resource->xmlSerialize(true, $sxe->addChild('resource'));
        }
        if (isset($this->search)) {
            $this->search->xmlSerialize(true, $sxe->addChild('search'));
        }
        if (isset($this->request)) {
            $this->request->xmlSerialize(true, $sxe->addChild('request'));
        }
        if (isset($this->response)) {
            $this->response->xmlSerialize(true, $sxe->addChild('response'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
