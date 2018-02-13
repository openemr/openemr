<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRCapabilityStatement;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A Capability Statement documents a set of capabilities (behaviors) of a FHIR Server that may be used as a statement of actual server functionality or a statement of required or desired server implementation.
 */
class FHIRCapabilityStatementSoftware extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Name software is known by.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $name = null;

    /**
     * The version identifier for the software covered by this statement.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $version = null;

    /**
     * Date this version of the software was released.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDateTime
     */
    public $releaseDate = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'CapabilityStatement.Software';

    /**
     * Name software is known by.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Name software is known by.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * The version identifier for the software covered by this statement.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * The version identifier for the software covered by this statement.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $version
     * @return $this
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * Date this version of the software was released.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDateTime
     */
    public function getReleaseDate()
    {
        return $this->releaseDate;
    }

    /**
     * Date this version of the software was released.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDateTime $releaseDate
     * @return $this
     */
    public function setReleaseDate($releaseDate)
    {
        $this->releaseDate = $releaseDate;
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
            if (isset($data['name'])) {
                $this->setName($data['name']);
            }
            if (isset($data['version'])) {
                $this->setVersion($data['version']);
            }
            if (isset($data['releaseDate'])) {
                $this->setReleaseDate($data['releaseDate']);
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
        if (isset($this->name)) {
            $json['name'] = $this->name;
        }
        if (isset($this->version)) {
            $json['version'] = $this->version;
        }
        if (isset($this->releaseDate)) {
            $json['releaseDate'] = $this->releaseDate;
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
            $sxe = new \SimpleXMLElement('<CapabilityStatementSoftware xmlns="http://hl7.org/fhir"></CapabilityStatementSoftware>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->name)) {
            $this->name->xmlSerialize(true, $sxe->addChild('name'));
        }
        if (isset($this->version)) {
            $this->version->xmlSerialize(true, $sxe->addChild('version'));
        }
        if (isset($this->releaseDate)) {
            $this->releaseDate->xmlSerialize(true, $sxe->addChild('releaseDate'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
