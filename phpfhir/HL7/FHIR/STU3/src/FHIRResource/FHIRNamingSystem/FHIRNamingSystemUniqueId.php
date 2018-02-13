<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRNamingSystem;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A curated namespace that issues unique symbols within that namespace for the identification of concepts, people, devices, etc.  Represents a "System" used within the Identifier and Coding data types.
 */
class FHIRNamingSystemUniqueId extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Identifies the unique identifier scheme used for this particular identifier.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRNamingSystemIdentifierType
     */
    public $type = null;

    /**
     * The string that should be sent over the wire to identify the code system or identifier system.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $value = null;

    /**
     * Indicates whether this identifier is the "preferred" identifier of this type.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public $preferred = null;

    /**
     * Notes about the past or intended usage of this identifier.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $comment = null;

    /**
     * Identifies the period of time over which this identifier is considered appropriate to refer to the naming system.  Outside of this window, the identifier might be non-deterministic.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRPeriod
     */
    public $period = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'NamingSystem.UniqueId';

    /**
     * Identifies the unique identifier scheme used for this particular identifier.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRNamingSystemIdentifierType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Identifies the unique identifier scheme used for this particular identifier.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRNamingSystemIdentifierType $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * The string that should be sent over the wire to identify the code system or identifier system.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * The string that should be sent over the wire to identify the code system or identifier system.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Indicates whether this identifier is the "preferred" identifier of this type.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public function getPreferred()
    {
        return $this->preferred;
    }

    /**
     * Indicates whether this identifier is the "preferred" identifier of this type.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRBoolean $preferred
     * @return $this
     */
    public function setPreferred($preferred)
    {
        $this->preferred = $preferred;
        return $this;
    }

    /**
     * Notes about the past or intended usage of this identifier.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Notes about the past or intended usage of this identifier.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $comment
     * @return $this
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * Identifies the period of time over which this identifier is considered appropriate to refer to the naming system.  Outside of this window, the identifier might be non-deterministic.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRPeriod
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * Identifies the period of time over which this identifier is considered appropriate to refer to the naming system.  Outside of this window, the identifier might be non-deterministic.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRPeriod $period
     * @return $this
     */
    public function setPeriod($period)
    {
        $this->period = $period;
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
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['value'])) {
                $this->setValue($data['value']);
            }
            if (isset($data['preferred'])) {
                $this->setPreferred($data['preferred']);
            }
            if (isset($data['comment'])) {
                $this->setComment($data['comment']);
            }
            if (isset($data['period'])) {
                $this->setPeriod($data['period']);
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
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->value)) {
            $json['value'] = $this->value;
        }
        if (isset($this->preferred)) {
            $json['preferred'] = $this->preferred;
        }
        if (isset($this->comment)) {
            $json['comment'] = $this->comment;
        }
        if (isset($this->period)) {
            $json['period'] = $this->period;
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
            $sxe = new \SimpleXMLElement('<NamingSystemUniqueId xmlns="http://hl7.org/fhir"></NamingSystemUniqueId>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->value)) {
            $this->value->xmlSerialize(true, $sxe->addChild('value'));
        }
        if (isset($this->preferred)) {
            $this->preferred->xmlSerialize(true, $sxe->addChild('preferred'));
        }
        if (isset($this->comment)) {
            $this->comment->xmlSerialize(true, $sxe->addChild('comment'));
        }
        if (isset($this->period)) {
            $this->period->xmlSerialize(true, $sxe->addChild('period'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
