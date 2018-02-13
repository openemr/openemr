<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRDeviceComponent;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * The characteristics, operational status and capabilities of a medical-related component of a medical device.
 */
class FHIRDeviceComponentProductionSpecification extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The specification type, such as, serial number, part number, hardware revision, software revision, etc.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $specType = null;

    /**
     * The internal component unique identification. This is a provision for manufacture specific standard components using a private OID. 11073-10101 has a partition for private OID semantic that the manufacturer can make use of.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier
     */
    public $componentId = null;

    /**
     * The printable string defining the component.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $productionSpec = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'DeviceComponent.ProductionSpecification';

    /**
     * The specification type, such as, serial number, part number, hardware revision, software revision, etc.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getSpecType()
    {
        return $this->specType;
    }

    /**
     * The specification type, such as, serial number, part number, hardware revision, software revision, etc.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $specType
     * @return $this
     */
    public function setSpecType($specType)
    {
        $this->specType = $specType;
        return $this;
    }

    /**
     * The internal component unique identification. This is a provision for manufacture specific standard components using a private OID. 11073-10101 has a partition for private OID semantic that the manufacturer can make use of.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier
     */
    public function getComponentId()
    {
        return $this->componentId;
    }

    /**
     * The internal component unique identification. This is a provision for manufacture specific standard components using a private OID. 11073-10101 has a partition for private OID semantic that the manufacturer can make use of.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier $componentId
     * @return $this
     */
    public function setComponentId($componentId)
    {
        $this->componentId = $componentId;
        return $this;
    }

    /**
     * The printable string defining the component.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getProductionSpec()
    {
        return $this->productionSpec;
    }

    /**
     * The printable string defining the component.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $productionSpec
     * @return $this
     */
    public function setProductionSpec($productionSpec)
    {
        $this->productionSpec = $productionSpec;
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
            if (isset($data['specType'])) {
                $this->setSpecType($data['specType']);
            }
            if (isset($data['componentId'])) {
                $this->setComponentId($data['componentId']);
            }
            if (isset($data['productionSpec'])) {
                $this->setProductionSpec($data['productionSpec']);
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
        if (isset($this->specType)) {
            $json['specType'] = $this->specType;
        }
        if (isset($this->componentId)) {
            $json['componentId'] = $this->componentId;
        }
        if (isset($this->productionSpec)) {
            $json['productionSpec'] = $this->productionSpec;
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
            $sxe = new \SimpleXMLElement('<DeviceComponentProductionSpecification xmlns="http://hl7.org/fhir"></DeviceComponentProductionSpecification>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->specType)) {
            $this->specType->xmlSerialize(true, $sxe->addChild('specType'));
        }
        if (isset($this->componentId)) {
            $this->componentId->xmlSerialize(true, $sxe->addChild('componentId'));
        }
        if (isset($this->productionSpec)) {
            $this->productionSpec->xmlSerialize(true, $sxe->addChild('productionSpec'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
