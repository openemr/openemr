<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRNutritionOrder;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A request to supply a diet, formula feeding (enteral) or oral nutritional supplement to a patient/resident.
 */
class FHIRNutritionOrderEnteralFormula extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The type of enteral or infant formula such as an adult standard formula with fiber or a soy-based infant formula.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $baseFormulaType = null;

    /**
     * The product or brand name of the enteral or infant formula product such as "ACME Adult Standard Formula".
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $baseFormulaProductName = null;

    /**
     * Indicates the type of modular component such as protein, carbohydrate, fat or fiber to be provided in addition to or mixed with the base formula.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $additiveType = null;

    /**
     * The product or brand name of the type of modular component to be added to the formula.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $additiveProductName = null;

    /**
     * The amount of energy (calories) that the formula should provide per specified volume, typically per mL or fluid oz.  For example, an infant may require a formula that provides 24 calories per fluid ounce or an adult may require an enteral formula that provides 1.5 calorie/mL.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRQuantity
     */
    public $caloricDensity = null;

    /**
     * The route or physiological path of administration into the patient's gastrointestinal  tract for purposes of providing the formula feeding, e.g. nasogastric tube.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $routeofAdministration = null;

    /**
     * Formula administration instructions as structured data.  This repeating structure allows for changing the administration rate or volume over time for both bolus and continuous feeding.  An example of this would be an instruction to increase the rate of continuous feeding every 2 hours.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRNutritionOrder\FHIRNutritionOrderAdministration[]
     */
    public $administration = [];

    /**
     * The maximum total quantity of formula that may be administered to a subject over the period of time, e.g. 1440 mL over 24 hours.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRQuantity
     */
    public $maxVolumeToDeliver = null;

    /**
     * Free text formula administration, feeding instructions or additional instructions or information.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $administrationInstruction = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'NutritionOrder.EnteralFormula';

    /**
     * The type of enteral or infant formula such as an adult standard formula with fiber or a soy-based infant formula.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getBaseFormulaType()
    {
        return $this->baseFormulaType;
    }

    /**
     * The type of enteral or infant formula such as an adult standard formula with fiber or a soy-based infant formula.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $baseFormulaType
     * @return $this
     */
    public function setBaseFormulaType($baseFormulaType)
    {
        $this->baseFormulaType = $baseFormulaType;
        return $this;
    }

    /**
     * The product or brand name of the enteral or infant formula product such as "ACME Adult Standard Formula".
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getBaseFormulaProductName()
    {
        return $this->baseFormulaProductName;
    }

    /**
     * The product or brand name of the enteral or infant formula product such as "ACME Adult Standard Formula".
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $baseFormulaProductName
     * @return $this
     */
    public function setBaseFormulaProductName($baseFormulaProductName)
    {
        $this->baseFormulaProductName = $baseFormulaProductName;
        return $this;
    }

    /**
     * Indicates the type of modular component such as protein, carbohydrate, fat or fiber to be provided in addition to or mixed with the base formula.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getAdditiveType()
    {
        return $this->additiveType;
    }

    /**
     * Indicates the type of modular component such as protein, carbohydrate, fat or fiber to be provided in addition to or mixed with the base formula.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $additiveType
     * @return $this
     */
    public function setAdditiveType($additiveType)
    {
        $this->additiveType = $additiveType;
        return $this;
    }

    /**
     * The product or brand name of the type of modular component to be added to the formula.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getAdditiveProductName()
    {
        return $this->additiveProductName;
    }

    /**
     * The product or brand name of the type of modular component to be added to the formula.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $additiveProductName
     * @return $this
     */
    public function setAdditiveProductName($additiveProductName)
    {
        $this->additiveProductName = $additiveProductName;
        return $this;
    }

    /**
     * The amount of energy (calories) that the formula should provide per specified volume, typically per mL or fluid oz.  For example, an infant may require a formula that provides 24 calories per fluid ounce or an adult may require an enteral formula that provides 1.5 calorie/mL.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRQuantity
     */
    public function getCaloricDensity()
    {
        return $this->caloricDensity;
    }

    /**
     * The amount of energy (calories) that the formula should provide per specified volume, typically per mL or fluid oz.  For example, an infant may require a formula that provides 24 calories per fluid ounce or an adult may require an enteral formula that provides 1.5 calorie/mL.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRQuantity $caloricDensity
     * @return $this
     */
    public function setCaloricDensity($caloricDensity)
    {
        $this->caloricDensity = $caloricDensity;
        return $this;
    }

    /**
     * The route or physiological path of administration into the patient's gastrointestinal  tract for purposes of providing the formula feeding, e.g. nasogastric tube.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getRouteofAdministration()
    {
        return $this->routeofAdministration;
    }

    /**
     * The route or physiological path of administration into the patient's gastrointestinal  tract for purposes of providing the formula feeding, e.g. nasogastric tube.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $routeofAdministration
     * @return $this
     */
    public function setRouteofAdministration($routeofAdministration)
    {
        $this->routeofAdministration = $routeofAdministration;
        return $this;
    }

    /**
     * Formula administration instructions as structured data.  This repeating structure allows for changing the administration rate or volume over time for both bolus and continuous feeding.  An example of this would be an instruction to increase the rate of continuous feeding every 2 hours.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRNutritionOrder\FHIRNutritionOrderAdministration[]
     */
    public function getAdministration()
    {
        return $this->administration;
    }

    /**
     * Formula administration instructions as structured data.  This repeating structure allows for changing the administration rate or volume over time for both bolus and continuous feeding.  An example of this would be an instruction to increase the rate of continuous feeding every 2 hours.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRNutritionOrder\FHIRNutritionOrderAdministration $administration
     * @return $this
     */
    public function addAdministration($administration)
    {
        $this->administration[] = $administration;
        return $this;
    }

    /**
     * The maximum total quantity of formula that may be administered to a subject over the period of time, e.g. 1440 mL over 24 hours.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRQuantity
     */
    public function getMaxVolumeToDeliver()
    {
        return $this->maxVolumeToDeliver;
    }

    /**
     * The maximum total quantity of formula that may be administered to a subject over the period of time, e.g. 1440 mL over 24 hours.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRQuantity $maxVolumeToDeliver
     * @return $this
     */
    public function setMaxVolumeToDeliver($maxVolumeToDeliver)
    {
        $this->maxVolumeToDeliver = $maxVolumeToDeliver;
        return $this;
    }

    /**
     * Free text formula administration, feeding instructions or additional instructions or information.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getAdministrationInstruction()
    {
        return $this->administrationInstruction;
    }

    /**
     * Free text formula administration, feeding instructions or additional instructions or information.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $administrationInstruction
     * @return $this
     */
    public function setAdministrationInstruction($administrationInstruction)
    {
        $this->administrationInstruction = $administrationInstruction;
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
            if (isset($data['baseFormulaType'])) {
                $this->setBaseFormulaType($data['baseFormulaType']);
            }
            if (isset($data['baseFormulaProductName'])) {
                $this->setBaseFormulaProductName($data['baseFormulaProductName']);
            }
            if (isset($data['additiveType'])) {
                $this->setAdditiveType($data['additiveType']);
            }
            if (isset($data['additiveProductName'])) {
                $this->setAdditiveProductName($data['additiveProductName']);
            }
            if (isset($data['caloricDensity'])) {
                $this->setCaloricDensity($data['caloricDensity']);
            }
            if (isset($data['routeofAdministration'])) {
                $this->setRouteofAdministration($data['routeofAdministration']);
            }
            if (isset($data['administration'])) {
                if (is_array($data['administration'])) {
                    foreach ($data['administration'] as $d) {
                        $this->addAdministration($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"administration" must be array of objects or null, '.gettype($data['administration']).' seen.');
                }
            }
            if (isset($data['maxVolumeToDeliver'])) {
                $this->setMaxVolumeToDeliver($data['maxVolumeToDeliver']);
            }
            if (isset($data['administrationInstruction'])) {
                $this->setAdministrationInstruction($data['administrationInstruction']);
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
        if (isset($this->baseFormulaType)) {
            $json['baseFormulaType'] = $this->baseFormulaType;
        }
        if (isset($this->baseFormulaProductName)) {
            $json['baseFormulaProductName'] = $this->baseFormulaProductName;
        }
        if (isset($this->additiveType)) {
            $json['additiveType'] = $this->additiveType;
        }
        if (isset($this->additiveProductName)) {
            $json['additiveProductName'] = $this->additiveProductName;
        }
        if (isset($this->caloricDensity)) {
            $json['caloricDensity'] = $this->caloricDensity;
        }
        if (isset($this->routeofAdministration)) {
            $json['routeofAdministration'] = $this->routeofAdministration;
        }
        if (0 < count($this->administration)) {
            $json['administration'] = [];
            foreach ($this->administration as $administration) {
                $json['administration'][] = $administration;
            }
        }
        if (isset($this->maxVolumeToDeliver)) {
            $json['maxVolumeToDeliver'] = $this->maxVolumeToDeliver;
        }
        if (isset($this->administrationInstruction)) {
            $json['administrationInstruction'] = $this->administrationInstruction;
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
            $sxe = new \SimpleXMLElement('<NutritionOrderEnteralFormula xmlns="http://hl7.org/fhir"></NutritionOrderEnteralFormula>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->baseFormulaType)) {
            $this->baseFormulaType->xmlSerialize(true, $sxe->addChild('baseFormulaType'));
        }
        if (isset($this->baseFormulaProductName)) {
            $this->baseFormulaProductName->xmlSerialize(true, $sxe->addChild('baseFormulaProductName'));
        }
        if (isset($this->additiveType)) {
            $this->additiveType->xmlSerialize(true, $sxe->addChild('additiveType'));
        }
        if (isset($this->additiveProductName)) {
            $this->additiveProductName->xmlSerialize(true, $sxe->addChild('additiveProductName'));
        }
        if (isset($this->caloricDensity)) {
            $this->caloricDensity->xmlSerialize(true, $sxe->addChild('caloricDensity'));
        }
        if (isset($this->routeofAdministration)) {
            $this->routeofAdministration->xmlSerialize(true, $sxe->addChild('routeofAdministration'));
        }
        if (0 < count($this->administration)) {
            foreach ($this->administration as $administration) {
                $administration->xmlSerialize(true, $sxe->addChild('administration'));
            }
        }
        if (isset($this->maxVolumeToDeliver)) {
            $this->maxVolumeToDeliver->xmlSerialize(true, $sxe->addChild('maxVolumeToDeliver'));
        }
        if (isset($this->administrationInstruction)) {
            $this->administrationInstruction->xmlSerialize(true, $sxe->addChild('administrationInstruction'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
