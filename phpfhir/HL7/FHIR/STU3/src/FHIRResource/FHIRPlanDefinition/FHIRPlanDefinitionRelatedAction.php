<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRPlanDefinition;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * This resource allows for the definition of various types of plans as a sharable, consumable, and executable artifact. The resource is general enough to support the description of a broad range of clinical artifacts such as clinical decision support rules, order sets and protocols.
 */
class FHIRPlanDefinitionRelatedAction extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The element id of the related action.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRId
     */
    public $actionId = null;

    /**
     * The relationship of this action to the related action.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRActionRelationshipType
     */
    public $relationship = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public $offsetDuration = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRRange
     */
    public $offsetRange = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'PlanDefinition.RelatedAction';

    /**
     * The element id of the related action.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRId
     */
    public function getActionId()
    {
        return $this->actionId;
    }

    /**
     * The element id of the related action.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRId $actionId
     * @return $this
     */
    public function setActionId($actionId)
    {
        $this->actionId = $actionId;
        return $this;
    }

    /**
     * The relationship of this action to the related action.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRActionRelationshipType
     */
    public function getRelationship()
    {
        return $this->relationship;
    }

    /**
     * The relationship of this action to the related action.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRActionRelationshipType $relationship
     * @return $this
     */
    public function setRelationship($relationship)
    {
        $this->relationship = $relationship;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public function getOffsetDuration()
    {
        return $this->offsetDuration;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRDuration $offsetDuration
     * @return $this
     */
    public function setOffsetDuration($offsetDuration)
    {
        $this->offsetDuration = $offsetDuration;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRRange
     */
    public function getOffsetRange()
    {
        return $this->offsetRange;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRRange $offsetRange
     * @return $this
     */
    public function setOffsetRange($offsetRange)
    {
        $this->offsetRange = $offsetRange;
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
            if (isset($data['actionId'])) {
                $this->setActionId($data['actionId']);
            }
            if (isset($data['relationship'])) {
                $this->setRelationship($data['relationship']);
            }
            if (isset($data['offsetDuration'])) {
                $this->setOffsetDuration($data['offsetDuration']);
            }
            if (isset($data['offsetRange'])) {
                $this->setOffsetRange($data['offsetRange']);
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
        if (isset($this->actionId)) {
            $json['actionId'] = $this->actionId;
        }
        if (isset($this->relationship)) {
            $json['relationship'] = $this->relationship;
        }
        if (isset($this->offsetDuration)) {
            $json['offsetDuration'] = $this->offsetDuration;
        }
        if (isset($this->offsetRange)) {
            $json['offsetRange'] = $this->offsetRange;
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
            $sxe = new \SimpleXMLElement('<PlanDefinitionRelatedAction xmlns="http://hl7.org/fhir"></PlanDefinitionRelatedAction>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->actionId)) {
            $this->actionId->xmlSerialize(true, $sxe->addChild('actionId'));
        }
        if (isset($this->relationship)) {
            $this->relationship->xmlSerialize(true, $sxe->addChild('relationship'));
        }
        if (isset($this->offsetDuration)) {
            $this->offsetDuration->xmlSerialize(true, $sxe->addChild('offsetDuration'));
        }
        if (isset($this->offsetRange)) {
            $this->offsetRange->xmlSerialize(true, $sxe->addChild('offsetRange'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
