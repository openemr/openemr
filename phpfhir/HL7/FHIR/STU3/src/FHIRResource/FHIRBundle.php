<?php namespace HL7\FHIR\STU3\FHIRResource;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRResource;

/**
 * A container for a collection of resources.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRBundle extends FHIRResource implements \JsonSerializable
{
    /**
     * A persistent identifier for the batch that won't change as a batch is copied from server to server.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier
     */
    public $identifier = null;

    /**
     * Indicates the purpose of this bundle - how it was intended to be used.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRBundleType
     */
    public $type = null;

    /**
     * If a set of search matches, this is the total number of matches for the search (as opposed to the number of results in this bundle).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUnsignedInt
     */
    public $total = null;

    /**
     * A series of links that provide context to this bundle.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRBundle\FHIRBundleLink[]
     */
    public $link = [];

    /**
     * An entry in a bundle resource - will either contain a resource, or information about a resource (transactions and history only).
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRBundle\FHIRBundleEntry[]
     */
    public $entry = [];

    /**
     * Digital Signature - base64 encoded. XML-DSIg or a JWT.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRSignature
     */
    public $signature = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Bundle';

    /**
     * A persistent identifier for the batch that won't change as a batch is copied from server to server.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * A persistent identifier for the batch that won't change as a batch is copied from server to server.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * Indicates the purpose of this bundle - how it was intended to be used.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRBundleType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Indicates the purpose of this bundle - how it was intended to be used.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRBundleType $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * If a set of search matches, this is the total number of matches for the search (as opposed to the number of results in this bundle).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUnsignedInt
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * If a set of search matches, this is the total number of matches for the search (as opposed to the number of results in this bundle).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUnsignedInt $total
     * @return $this
     */
    public function setTotal($total)
    {
        $this->total = $total;
        return $this;
    }

    /**
     * A series of links that provide context to this bundle.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRBundle\FHIRBundleLink[]
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * A series of links that provide context to this bundle.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRBundle\FHIRBundleLink $link
     * @return $this
     */
    public function addLink($link)
    {
        $this->link[] = $link;
        return $this;
    }

    /**
     * An entry in a bundle resource - will either contain a resource, or information about a resource (transactions and history only).
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRBundle\FHIRBundleEntry[]
     */
    public function getEntry()
    {
        return $this->entry;
    }

    /**
     * An entry in a bundle resource - will either contain a resource, or information about a resource (transactions and history only).
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRBundle\FHIRBundleEntry $entry
     * @return $this
     */
    public function addEntry($entry)
    {
        $this->entry[] = $entry;
        return $this;
    }

    /**
     * Digital Signature - base64 encoded. XML-DSIg or a JWT.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRSignature
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * Digital Signature - base64 encoded. XML-DSIg or a JWT.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRSignature $signature
     * @return $this
     */
    public function setSignature($signature)
    {
        $this->signature = $signature;
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
            if (isset($data['identifier'])) {
                $this->setIdentifier($data['identifier']);
            }
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['total'])) {
                $this->setTotal($data['total']);
            }
            if (isset($data['link'])) {
                if (is_array($data['link'])) {
                    foreach ($data['link'] as $d) {
                        $this->addLink($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"link" must be array of objects or null, '.gettype($data['link']).' seen.');
                }
            }
            if (isset($data['entry'])) {
                if (is_array($data['entry'])) {
                    foreach ($data['entry'] as $d) {
                        $this->addEntry($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"entry" must be array of objects or null, '.gettype($data['entry']).' seen.');
                }
            }
            if (isset($data['signature'])) {
                $this->setSignature($data['signature']);
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
        $json['resourceType'] = $this->_fhirElementName;
        if (isset($this->identifier)) {
            $json['identifier'] = $this->identifier;
        }
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->total)) {
            $json['total'] = $this->total;
        }
        if (0 < count($this->link)) {
            $json['link'] = [];
            foreach ($this->link as $link) {
                $json['link'][] = $link;
            }
        }
        if (0 < count($this->entry)) {
            $json['entry'] = [];
            foreach ($this->entry as $entry) {
                $json['entry'][] = $entry;
            }
        }
        if (isset($this->signature)) {
            $json['signature'] = $this->signature;
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
            $sxe = new \SimpleXMLElement('<Bundle xmlns="http://hl7.org/fhir"></Bundle>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->identifier)) {
            $this->identifier->xmlSerialize(true, $sxe->addChild('identifier'));
        }
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->total)) {
            $this->total->xmlSerialize(true, $sxe->addChild('total'));
        }
        if (0 < count($this->link)) {
            foreach ($this->link as $link) {
                $link->xmlSerialize(true, $sxe->addChild('link'));
            }
        }
        if (0 < count($this->entry)) {
            foreach ($this->entry as $entry) {
                $entry->xmlSerialize(true, $sxe->addChild('entry'));
            }
        }
        if (isset($this->signature)) {
            $this->signature->xmlSerialize(true, $sxe->addChild('signature'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
