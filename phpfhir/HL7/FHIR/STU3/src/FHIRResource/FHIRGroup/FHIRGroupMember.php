<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRGroup;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * Represents a defined collection of entities that may be discussed or acted upon collectively but which are not expected to act collectively and are not formally or legally recognized; i.e. a collection of entities that isn't an Organization.
 */
class FHIRGroupMember extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * A reference to the entity that is a member of the group. Must be consistent with Group.type.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $entity = null;

    /**
     * The period that the member was in the group, if known.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRPeriod
     */
    public $period = null;

    /**
     * A flag to indicate that the member is no longer in the group, but previously may have been a member.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public $inactive = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Group.Member';

    /**
     * A reference to the entity that is a member of the group. Must be consistent with Group.type.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * A reference to the entity that is a member of the group. Must be consistent with Group.type.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $entity
     * @return $this
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
        return $this;
    }

    /**
     * The period that the member was in the group, if known.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRPeriod
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * The period that the member was in the group, if known.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRPeriod $period
     * @return $this
     */
    public function setPeriod($period)
    {
        $this->period = $period;
        return $this;
    }

    /**
     * A flag to indicate that the member is no longer in the group, but previously may have been a member.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public function getInactive()
    {
        return $this->inactive;
    }

    /**
     * A flag to indicate that the member is no longer in the group, but previously may have been a member.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRBoolean $inactive
     * @return $this
     */
    public function setInactive($inactive)
    {
        $this->inactive = $inactive;
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
            if (isset($data['entity'])) {
                $this->setEntity($data['entity']);
            }
            if (isset($data['period'])) {
                $this->setPeriod($data['period']);
            }
            if (isset($data['inactive'])) {
                $this->setInactive($data['inactive']);
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
        if (isset($this->entity)) {
            $json['entity'] = $this->entity;
        }
        if (isset($this->period)) {
            $json['period'] = $this->period;
        }
        if (isset($this->inactive)) {
            $json['inactive'] = $this->inactive;
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
            $sxe = new \SimpleXMLElement('<GroupMember xmlns="http://hl7.org/fhir"></GroupMember>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->entity)) {
            $this->entity->xmlSerialize(true, $sxe->addChild('entity'));
        }
        if (isset($this->period)) {
            $this->period->xmlSerialize(true, $sxe->addChild('period'));
        }
        if (isset($this->inactive)) {
            $this->inactive->xmlSerialize(true, $sxe->addChild('inactive'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
