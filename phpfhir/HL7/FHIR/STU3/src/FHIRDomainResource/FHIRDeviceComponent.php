<?php namespace HL7\FHIR\STU3\FHIRDomainResource;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRResource\FHIRDomainResource;

/**
 * The characteristics, operational status and capabilities of a medical-related component of a medical device.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRDeviceComponent extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * The locally assigned unique identification by the software. For example: handle ID.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier
     */
    public $identifier = null;

    /**
     * The component type as defined in the object-oriented or metric nomenclature partition.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $type = null;

    /**
     * The timestamp for the most recent system change which includes device configuration or setting change.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRInstant
     */
    public $lastSystemChange = null;

    /**
     * The link to the source Device that contains administrative device information such as manufacture, serial number, etc.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $source = null;

    /**
     * The link to the parent resource. For example: Channel is linked to its VMD parent.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $parent = null;

    /**
     * The current operational status of the device. For example: On, Off, Standby, etc.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public $operationalStatus = [];

    /**
     * The parameter group supported by the current device component that is based on some nomenclature, e.g. cardiovascular.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $parameterGroup = null;

    /**
     * The physical principle of the measurement. For example: thermal, chemical, acoustical, etc.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRMeasmntPrinciple
     */
    public $measurementPrinciple = null;

    /**
     * The production specification such as component revision, serial number, etc.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRDeviceComponent\FHIRDeviceComponentProductionSpecification[]
     */
    public $productionSpecification = [];

    /**
     * The language code for the human-readable text string produced by the device. This language code will follow the IETF language tag. Example: en-US.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $languageCode = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'DeviceComponent';

    /**
     * The locally assigned unique identification by the software. For example: handle ID.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * The locally assigned unique identification by the software. For example: handle ID.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * The component type as defined in the object-oriented or metric nomenclature partition.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * The component type as defined in the object-oriented or metric nomenclature partition.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * The timestamp for the most recent system change which includes device configuration or setting change.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRInstant
     */
    public function getLastSystemChange()
    {
        return $this->lastSystemChange;
    }

    /**
     * The timestamp for the most recent system change which includes device configuration or setting change.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRInstant $lastSystemChange
     * @return $this
     */
    public function setLastSystemChange($lastSystemChange)
    {
        $this->lastSystemChange = $lastSystemChange;
        return $this;
    }

    /**
     * The link to the source Device that contains administrative device information such as manufacture, serial number, etc.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * The link to the source Device that contains administrative device information such as manufacture, serial number, etc.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $source
     * @return $this
     */
    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }

    /**
     * The link to the parent resource. For example: Channel is linked to its VMD parent.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * The link to the parent resource. For example: Channel is linked to its VMD parent.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $parent
     * @return $this
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * The current operational status of the device. For example: On, Off, Standby, etc.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public function getOperationalStatus()
    {
        return $this->operationalStatus;
    }

    /**
     * The current operational status of the device. For example: On, Off, Standby, etc.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $operationalStatus
     * @return $this
     */
    public function addOperationalStatus($operationalStatus)
    {
        $this->operationalStatus[] = $operationalStatus;
        return $this;
    }

    /**
     * The parameter group supported by the current device component that is based on some nomenclature, e.g. cardiovascular.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getParameterGroup()
    {
        return $this->parameterGroup;
    }

    /**
     * The parameter group supported by the current device component that is based on some nomenclature, e.g. cardiovascular.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $parameterGroup
     * @return $this
     */
    public function setParameterGroup($parameterGroup)
    {
        $this->parameterGroup = $parameterGroup;
        return $this;
    }

    /**
     * The physical principle of the measurement. For example: thermal, chemical, acoustical, etc.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRMeasmntPrinciple
     */
    public function getMeasurementPrinciple()
    {
        return $this->measurementPrinciple;
    }

    /**
     * The physical principle of the measurement. For example: thermal, chemical, acoustical, etc.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRMeasmntPrinciple $measurementPrinciple
     * @return $this
     */
    public function setMeasurementPrinciple($measurementPrinciple)
    {
        $this->measurementPrinciple = $measurementPrinciple;
        return $this;
    }

    /**
     * The production specification such as component revision, serial number, etc.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRDeviceComponent\FHIRDeviceComponentProductionSpecification[]
     */
    public function getProductionSpecification()
    {
        return $this->productionSpecification;
    }

    /**
     * The production specification such as component revision, serial number, etc.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRDeviceComponent\FHIRDeviceComponentProductionSpecification $productionSpecification
     * @return $this
     */
    public function addProductionSpecification($productionSpecification)
    {
        $this->productionSpecification[] = $productionSpecification;
        return $this;
    }

    /**
     * The language code for the human-readable text string produced by the device. This language code will follow the IETF language tag. Example: en-US.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getLanguageCode()
    {
        return $this->languageCode;
    }

    /**
     * The language code for the human-readable text string produced by the device. This language code will follow the IETF language tag. Example: en-US.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $languageCode
     * @return $this
     */
    public function setLanguageCode($languageCode)
    {
        $this->languageCode = $languageCode;
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
            if (isset($data['lastSystemChange'])) {
                $this->setLastSystemChange($data['lastSystemChange']);
            }
            if (isset($data['source'])) {
                $this->setSource($data['source']);
            }
            if (isset($data['parent'])) {
                $this->setParent($data['parent']);
            }
            if (isset($data['operationalStatus'])) {
                if (is_array($data['operationalStatus'])) {
                    foreach ($data['operationalStatus'] as $d) {
                        $this->addOperationalStatus($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"operationalStatus" must be array of objects or null, '.gettype($data['operationalStatus']).' seen.');
                }
            }
            if (isset($data['parameterGroup'])) {
                $this->setParameterGroup($data['parameterGroup']);
            }
            if (isset($data['measurementPrinciple'])) {
                $this->setMeasurementPrinciple($data['measurementPrinciple']);
            }
            if (isset($data['productionSpecification'])) {
                if (is_array($data['productionSpecification'])) {
                    foreach ($data['productionSpecification'] as $d) {
                        $this->addProductionSpecification($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"productionSpecification" must be array of objects or null, '.gettype($data['productionSpecification']).' seen.');
                }
            }
            if (isset($data['languageCode'])) {
                $this->setLanguageCode($data['languageCode']);
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
        if (isset($this->lastSystemChange)) {
            $json['lastSystemChange'] = $this->lastSystemChange;
        }
        if (isset($this->source)) {
            $json['source'] = $this->source;
        }
        if (isset($this->parent)) {
            $json['parent'] = $this->parent;
        }
        if (0 < count($this->operationalStatus)) {
            $json['operationalStatus'] = [];
            foreach ($this->operationalStatus as $operationalStatus) {
                $json['operationalStatus'][] = $operationalStatus;
            }
        }
        if (isset($this->parameterGroup)) {
            $json['parameterGroup'] = $this->parameterGroup;
        }
        if (isset($this->measurementPrinciple)) {
            $json['measurementPrinciple'] = $this->measurementPrinciple;
        }
        if (0 < count($this->productionSpecification)) {
            $json['productionSpecification'] = [];
            foreach ($this->productionSpecification as $productionSpecification) {
                $json['productionSpecification'][] = $productionSpecification;
            }
        }
        if (isset($this->languageCode)) {
            $json['languageCode'] = $this->languageCode;
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
            $sxe = new \SimpleXMLElement('<DeviceComponent xmlns="http://hl7.org/fhir"></DeviceComponent>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->identifier)) {
            $this->identifier->xmlSerialize(true, $sxe->addChild('identifier'));
        }
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->lastSystemChange)) {
            $this->lastSystemChange->xmlSerialize(true, $sxe->addChild('lastSystemChange'));
        }
        if (isset($this->source)) {
            $this->source->xmlSerialize(true, $sxe->addChild('source'));
        }
        if (isset($this->parent)) {
            $this->parent->xmlSerialize(true, $sxe->addChild('parent'));
        }
        if (0 < count($this->operationalStatus)) {
            foreach ($this->operationalStatus as $operationalStatus) {
                $operationalStatus->xmlSerialize(true, $sxe->addChild('operationalStatus'));
            }
        }
        if (isset($this->parameterGroup)) {
            $this->parameterGroup->xmlSerialize(true, $sxe->addChild('parameterGroup'));
        }
        if (isset($this->measurementPrinciple)) {
            $this->measurementPrinciple->xmlSerialize(true, $sxe->addChild('measurementPrinciple'));
        }
        if (0 < count($this->productionSpecification)) {
            foreach ($this->productionSpecification as $productionSpecification) {
                $productionSpecification->xmlSerialize(true, $sxe->addChild('productionSpecification'));
            }
        }
        if (isset($this->languageCode)) {
            $this->languageCode->xmlSerialize(true, $sxe->addChild('languageCode'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
