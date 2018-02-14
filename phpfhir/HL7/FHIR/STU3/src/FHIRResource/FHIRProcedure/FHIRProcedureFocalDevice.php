<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRProcedure;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * An action that is or was performed on a patient. This can be a physical intervention like an operation, or less invasive like counseling or hypnotherapy.
 */
class FHIRProcedureFocalDevice extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The kind of change that happened to the device during the procedure.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $action = null;

    /**
     * The device that was manipulated (changed) during the procedure.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $manipulated = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Procedure.FocalDevice';

    /**
     * The kind of change that happened to the device during the procedure.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * The kind of change that happened to the device during the procedure.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $action
     * @return $this
     */
    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * The device that was manipulated (changed) during the procedure.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getManipulated()
    {
        return $this->manipulated;
    }

    /**
     * The device that was manipulated (changed) during the procedure.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $manipulated
     * @return $this
     */
    public function setManipulated($manipulated)
    {
        $this->manipulated = $manipulated;
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
            if (isset($data['action'])) {
                $this->setAction($data['action']);
            }
            if (isset($data['manipulated'])) {
                $this->setManipulated($data['manipulated']);
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
        if (isset($this->action)) {
            $json['action'] = $this->action;
        }
        if (isset($this->manipulated)) {
            $json['manipulated'] = $this->manipulated;
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
            $sxe = new \SimpleXMLElement('<ProcedureFocalDevice xmlns="http://hl7.org/fhir"></ProcedureFocalDevice>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->action)) {
            $this->action->xmlSerialize(true, $sxe->addChild('action'));
        }
        if (isset($this->manipulated)) {
            $this->manipulated->xmlSerialize(true, $sxe->addChild('manipulated'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
