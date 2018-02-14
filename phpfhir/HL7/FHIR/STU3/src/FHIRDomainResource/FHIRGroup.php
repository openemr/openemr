<?php namespace HL7\FHIR\STU3\FHIRDomainResource;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRResource\FHIRDomainResource;

/**
 * Represents a defined collection of entities that may be discussed or acted upon collectively but which are not expected to act collectively and are not formally or legally recognized; i.e. a collection of entities that isn't an Organization.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRGroup extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * A unique business identifier for this group.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = [];

    /**
     * Indicates whether the record for the group is available for use or is merely being retained for historical purposes.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public $active = null;

    /**
     * Identifies the broad classification of the kind of resources the group includes.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRGroupType
     */
    public $type = null;

    /**
     * If true, indicates that the resource refers to a specific group of real individuals.  If false, the group defines a set of intended individuals.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public $actual = null;

    /**
     * Provides a specific type of resource the group includes; e.g. "cow", "syringe", etc.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $code = null;

    /**
     * A label assigned to the group for human identification and communication.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $name = null;

    /**
     * A count of the number of resource instances that are part of the group.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUnsignedInt
     */
    public $quantity = null;

    /**
     * Identifies the traits shared by members of the group.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRGroup\FHIRGroupCharacteristic[]
     */
    public $characteristic = [];

    /**
     * Identifies the resource instances that are members of the group.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRGroup\FHIRGroupMember[]
     */
    public $member = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'Group';

    /**
     * A unique business identifier for this group.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * A unique business identifier for this group.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * Indicates whether the record for the group is available for use or is merely being retained for historical purposes.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Indicates whether the record for the group is available for use or is merely being retained for historical purposes.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRBoolean $active
     * @return $this
     */
    public function setActive($active)
    {
        $this->active = $active;
        return $this;
    }

    /**
     * Identifies the broad classification of the kind of resources the group includes.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRGroupType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Identifies the broad classification of the kind of resources the group includes.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRGroupType $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * If true, indicates that the resource refers to a specific group of real individuals.  If false, the group defines a set of intended individuals.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public function getActual()
    {
        return $this->actual;
    }

    /**
     * If true, indicates that the resource refers to a specific group of real individuals.  If false, the group defines a set of intended individuals.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRBoolean $actual
     * @return $this
     */
    public function setActual($actual)
    {
        $this->actual = $actual;
        return $this;
    }

    /**
     * Provides a specific type of resource the group includes; e.g. "cow", "syringe", etc.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Provides a specific type of resource the group includes; e.g. "cow", "syringe", etc.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * A label assigned to the group for human identification and communication.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * A label assigned to the group for human identification and communication.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * A count of the number of resource instances that are part of the group.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUnsignedInt
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * A count of the number of resource instances that are part of the group.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUnsignedInt $quantity
     * @return $this
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * Identifies the traits shared by members of the group.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRGroup\FHIRGroupCharacteristic[]
     */
    public function getCharacteristic()
    {
        return $this->characteristic;
    }

    /**
     * Identifies the traits shared by members of the group.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRGroup\FHIRGroupCharacteristic $characteristic
     * @return $this
     */
    public function addCharacteristic($characteristic)
    {
        $this->characteristic[] = $characteristic;
        return $this;
    }

    /**
     * Identifies the resource instances that are members of the group.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRGroup\FHIRGroupMember[]
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * Identifies the resource instances that are members of the group.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRGroup\FHIRGroupMember $member
     * @return $this
     */
    public function addMember($member)
    {
        $this->member[] = $member;
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
                if (is_array($data['identifier'])) {
                    foreach ($data['identifier'] as $d) {
                        $this->addIdentifier($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"identifier" must be array of objects or null, '.gettype($data['identifier']).' seen.');
                }
            }
            if (isset($data['active'])) {
                $this->setActive($data['active']);
            }
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['actual'])) {
                $this->setActual($data['actual']);
            }
            if (isset($data['code'])) {
                $this->setCode($data['code']);
            }
            if (isset($data['name'])) {
                $this->setName($data['name']);
            }
            if (isset($data['quantity'])) {
                $this->setQuantity($data['quantity']);
            }
            if (isset($data['characteristic'])) {
                if (is_array($data['characteristic'])) {
                    foreach ($data['characteristic'] as $d) {
                        $this->addCharacteristic($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"characteristic" must be array of objects or null, '.gettype($data['characteristic']).' seen.');
                }
            }
            if (isset($data['member'])) {
                if (is_array($data['member'])) {
                    foreach ($data['member'] as $d) {
                        $this->addMember($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"member" must be array of objects or null, '.gettype($data['member']).' seen.');
                }
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
        if (0 < count($this->identifier)) {
            $json['identifier'] = [];
            foreach ($this->identifier as $identifier) {
                $json['identifier'][] = $identifier;
            }
        }
        if (isset($this->active)) {
            $json['active'] = $this->active;
        }
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->actual)) {
            $json['actual'] = $this->actual;
        }
        if (isset($this->code)) {
            $json['code'] = $this->code;
        }
        if (isset($this->name)) {
            $json['name'] = $this->name;
        }
        if (isset($this->quantity)) {
            $json['quantity'] = $this->quantity;
        }
        if (0 < count($this->characteristic)) {
            $json['characteristic'] = [];
            foreach ($this->characteristic as $characteristic) {
                $json['characteristic'][] = $characteristic;
            }
        }
        if (0 < count($this->member)) {
            $json['member'] = [];
            foreach ($this->member as $member) {
                $json['member'][] = $member;
            }
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
            $sxe = new \SimpleXMLElement('<Group xmlns="http://hl7.org/fhir"></Group>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->identifier)) {
            foreach ($this->identifier as $identifier) {
                $identifier->xmlSerialize(true, $sxe->addChild('identifier'));
            }
        }
        if (isset($this->active)) {
            $this->active->xmlSerialize(true, $sxe->addChild('active'));
        }
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->actual)) {
            $this->actual->xmlSerialize(true, $sxe->addChild('actual'));
        }
        if (isset($this->code)) {
            $this->code->xmlSerialize(true, $sxe->addChild('code'));
        }
        if (isset($this->name)) {
            $this->name->xmlSerialize(true, $sxe->addChild('name'));
        }
        if (isset($this->quantity)) {
            $this->quantity->xmlSerialize(true, $sxe->addChild('quantity'));
        }
        if (0 < count($this->characteristic)) {
            foreach ($this->characteristic as $characteristic) {
                $characteristic->xmlSerialize(true, $sxe->addChild('characteristic'));
            }
        }
        if (0 < count($this->member)) {
            foreach ($this->member as $member) {
                $member->xmlSerialize(true, $sxe->addChild('member'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
