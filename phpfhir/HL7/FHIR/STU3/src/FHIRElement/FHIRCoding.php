<?php namespace HL7\FHIR\STU3\FHIRElement;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement;

/**
 * A reference to a code defined by a terminology system.
 * If the element is present, it must have a value for at least one of the defined elements, an @id referenced from the Narrative, or extensions
 */
class FHIRCoding extends FHIRElement implements \JsonSerializable
{
    /**
     * The identification of the code system that defines the meaning of the symbol in the code.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public $system = null;

    /**
     * The version of the code system which was used when choosing this code. Note that a well-maintained code system does not need the version reported, because the meaning of codes is consistent across versions. However this cannot consistently be assured. and when the meaning is not guaranteed to be consistent, the version SHOULD be exchanged.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $version = null;

    /**
     * A symbol in syntax defined by the system. The symbol may be a predefined code or an expression in a syntax defined by the coding system (e.g. post-coordination).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCode
     */
    public $code = null;

    /**
     * A representation of the meaning of the code in the system, following the rules of the system.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $display = null;

    /**
     * Indicates that this coding was chosen by a user directly - i.e. off a pick list of available items (codes or displays).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public $userSelected = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Coding';

    /**
     * The identification of the code system that defines the meaning of the symbol in the code.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public function getSystem()
    {
        return $this->system;
    }

    /**
     * The identification of the code system that defines the meaning of the symbol in the code.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUri $system
     * @return $this
     */
    public function setSystem($system)
    {
        $this->system = $system;
        return $this;
    }

    /**
     * The version of the code system which was used when choosing this code. Note that a well-maintained code system does not need the version reported, because the meaning of codes is consistent across versions. However this cannot consistently be assured. and when the meaning is not guaranteed to be consistent, the version SHOULD be exchanged.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * The version of the code system which was used when choosing this code. Note that a well-maintained code system does not need the version reported, because the meaning of codes is consistent across versions. However this cannot consistently be assured. and when the meaning is not guaranteed to be consistent, the version SHOULD be exchanged.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $version
     * @return $this
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * A symbol in syntax defined by the system. The symbol may be a predefined code or an expression in a syntax defined by the coding system (e.g. post-coordination).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCode
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * A symbol in syntax defined by the system. The symbol may be a predefined code or an expression in a syntax defined by the coding system (e.g. post-coordination).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCode $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * A representation of the meaning of the code in the system, following the rules of the system.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getDisplay()
    {
        return $this->display;
    }

    /**
     * A representation of the meaning of the code in the system, following the rules of the system.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $display
     * @return $this
     */
    public function setDisplay($display)
    {
        $this->display = $display;
        return $this;
    }

    /**
     * Indicates that this coding was chosen by a user directly - i.e. off a pick list of available items (codes or displays).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public function getUserSelected()
    {
        return $this->userSelected;
    }

    /**
     * Indicates that this coding was chosen by a user directly - i.e. off a pick list of available items (codes or displays).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRBoolean $userSelected
     * @return $this
     */
    public function setUserSelected($userSelected)
    {
        $this->userSelected = $userSelected;
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
            if (isset($data['system'])) {
                $this->setSystem($data['system']);
            }
            if (isset($data['version'])) {
                $this->setVersion($data['version']);
            }
            if (isset($data['code'])) {
                $this->setCode($data['code']);
            }
            if (isset($data['display'])) {
                $this->setDisplay($data['display']);
            }
            if (isset($data['userSelected'])) {
                $this->setUserSelected($data['userSelected']);
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
        if (isset($this->system)) {
            $json['system'] = $this->system;
        }
        if (isset($this->version)) {
            $json['version'] = $this->version;
        }
        if (isset($this->code)) {
            $json['code'] = $this->code;
        }
        if (isset($this->display)) {
            $json['display'] = $this->display;
        }
        if (isset($this->userSelected)) {
            $json['userSelected'] = $this->userSelected;
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
            $sxe = new \SimpleXMLElement('<Coding xmlns="http://hl7.org/fhir"></Coding>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->system)) {
            $this->system->xmlSerialize(true, $sxe->addChild('system'));
        }
        if (isset($this->version)) {
            $this->version->xmlSerialize(true, $sxe->addChild('version'));
        }
        if (isset($this->code)) {
            $this->code->xmlSerialize(true, $sxe->addChild('code'));
        }
        if (isset($this->display)) {
            $this->display->xmlSerialize(true, $sxe->addChild('display'));
        }
        if (isset($this->userSelected)) {
            $this->userSelected->xmlSerialize(true, $sxe->addChild('userSelected'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
