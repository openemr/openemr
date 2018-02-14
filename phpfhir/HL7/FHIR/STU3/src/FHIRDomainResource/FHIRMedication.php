<?php namespace HL7\FHIR\STU3\FHIRDomainResource;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRResource\FHIRDomainResource;

/**
 * This resource is primarily used for the identification and definition of a medication. It covers the ingredients and the packaging for a medication.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRMedication extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * A code (or set of codes) that specify this medication, or a textual description if no code is available. Usage note: This could be a standard medication code such as a code from RxNorm, SNOMED CT, IDMP etc. It could also be a national or local formulary code, optionally with translations to other code systems.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $code = null;

    /**
     * A code to indicate if the medication is in active use.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRMedicationStatus
     */
    public $status = null;

    /**
     * Set to true if the item is attributable to a specific manufacturer.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public $isBrand = null;

    /**
     * Set to true if the medication can be obtained without an order from a prescriber.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public $isOverTheCounter = null;

    /**
     * Describes the details of the manufacturer of the medication product.  This is not intended to represent the distributor of a medication product.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $manufacturer = null;

    /**
     * Describes the form of the item.  Powder; tablets; capsule.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $form = null;

    /**
     * Identifies a particular constituent of interest in the product.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRMedication\FHIRMedicationIngredient[]
     */
    public $ingredient = [];

    /**
     * Information that only applies to packages (not products).
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRMedication\FHIRMedicationPackage
     */
    public $package = null;

    /**
     * Photo(s) or graphic representation(s) of the medication.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRAttachment[]
     */
    public $image = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'Medication';

    /**
     * A code (or set of codes) that specify this medication, or a textual description if no code is available. Usage note: This could be a standard medication code such as a code from RxNorm, SNOMED CT, IDMP etc. It could also be a national or local formulary code, optionally with translations to other code systems.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * A code (or set of codes) that specify this medication, or a textual description if no code is available. Usage note: This could be a standard medication code such as a code from RxNorm, SNOMED CT, IDMP etc. It could also be a national or local formulary code, optionally with translations to other code systems.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * A code to indicate if the medication is in active use.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRMedicationStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * A code to indicate if the medication is in active use.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRMedicationStatus $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Set to true if the item is attributable to a specific manufacturer.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public function getIsBrand()
    {
        return $this->isBrand;
    }

    /**
     * Set to true if the item is attributable to a specific manufacturer.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRBoolean $isBrand
     * @return $this
     */
    public function setIsBrand($isBrand)
    {
        $this->isBrand = $isBrand;
        return $this;
    }

    /**
     * Set to true if the medication can be obtained without an order from a prescriber.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public function getIsOverTheCounter()
    {
        return $this->isOverTheCounter;
    }

    /**
     * Set to true if the medication can be obtained without an order from a prescriber.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRBoolean $isOverTheCounter
     * @return $this
     */
    public function setIsOverTheCounter($isOverTheCounter)
    {
        $this->isOverTheCounter = $isOverTheCounter;
        return $this;
    }

    /**
     * Describes the details of the manufacturer of the medication product.  This is not intended to represent the distributor of a medication product.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getManufacturer()
    {
        return $this->manufacturer;
    }

    /**
     * Describes the details of the manufacturer of the medication product.  This is not intended to represent the distributor of a medication product.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $manufacturer
     * @return $this
     */
    public function setManufacturer($manufacturer)
    {
        $this->manufacturer = $manufacturer;
        return $this;
    }

    /**
     * Describes the form of the item.  Powder; tablets; capsule.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Describes the form of the item.  Powder; tablets; capsule.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $form
     * @return $this
     */
    public function setForm($form)
    {
        $this->form = $form;
        return $this;
    }

    /**
     * Identifies a particular constituent of interest in the product.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRMedication\FHIRMedicationIngredient[]
     */
    public function getIngredient()
    {
        return $this->ingredient;
    }

    /**
     * Identifies a particular constituent of interest in the product.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRMedication\FHIRMedicationIngredient $ingredient
     * @return $this
     */
    public function addIngredient($ingredient)
    {
        $this->ingredient[] = $ingredient;
        return $this;
    }

    /**
     * Information that only applies to packages (not products).
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRMedication\FHIRMedicationPackage
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * Information that only applies to packages (not products).
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRMedication\FHIRMedicationPackage $package
     * @return $this
     */
    public function setPackage($package)
    {
        $this->package = $package;
        return $this;
    }

    /**
     * Photo(s) or graphic representation(s) of the medication.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRAttachment[]
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Photo(s) or graphic representation(s) of the medication.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRAttachment $image
     * @return $this
     */
    public function addImage($image)
    {
        $this->image[] = $image;
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
            if (isset($data['code'])) {
                $this->setCode($data['code']);
            }
            if (isset($data['status'])) {
                $this->setStatus($data['status']);
            }
            if (isset($data['isBrand'])) {
                $this->setIsBrand($data['isBrand']);
            }
            if (isset($data['isOverTheCounter'])) {
                $this->setIsOverTheCounter($data['isOverTheCounter']);
            }
            if (isset($data['manufacturer'])) {
                $this->setManufacturer($data['manufacturer']);
            }
            if (isset($data['form'])) {
                $this->setForm($data['form']);
            }
            if (isset($data['ingredient'])) {
                if (is_array($data['ingredient'])) {
                    foreach ($data['ingredient'] as $d) {
                        $this->addIngredient($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"ingredient" must be array of objects or null, '.gettype($data['ingredient']).' seen.');
                }
            }
            if (isset($data['package'])) {
                $this->setPackage($data['package']);
            }
            if (isset($data['image'])) {
                if (is_array($data['image'])) {
                    foreach ($data['image'] as $d) {
                        $this->addImage($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"image" must be array of objects or null, '.gettype($data['image']).' seen.');
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
        if (isset($this->code)) {
            $json['code'] = $this->code;
        }
        if (isset($this->status)) {
            $json['status'] = $this->status;
        }
        if (isset($this->isBrand)) {
            $json['isBrand'] = $this->isBrand;
        }
        if (isset($this->isOverTheCounter)) {
            $json['isOverTheCounter'] = $this->isOverTheCounter;
        }
        if (isset($this->manufacturer)) {
            $json['manufacturer'] = $this->manufacturer;
        }
        if (isset($this->form)) {
            $json['form'] = $this->form;
        }
        if (0 < count($this->ingredient)) {
            $json['ingredient'] = [];
            foreach ($this->ingredient as $ingredient) {
                $json['ingredient'][] = $ingredient;
            }
        }
        if (isset($this->package)) {
            $json['package'] = $this->package;
        }
        if (0 < count($this->image)) {
            $json['image'] = [];
            foreach ($this->image as $image) {
                $json['image'][] = $image;
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
            $sxe = new \SimpleXMLElement('<Medication xmlns="http://hl7.org/fhir"></Medication>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->code)) {
            $this->code->xmlSerialize(true, $sxe->addChild('code'));
        }
        if (isset($this->status)) {
            $this->status->xmlSerialize(true, $sxe->addChild('status'));
        }
        if (isset($this->isBrand)) {
            $this->isBrand->xmlSerialize(true, $sxe->addChild('isBrand'));
        }
        if (isset($this->isOverTheCounter)) {
            $this->isOverTheCounter->xmlSerialize(true, $sxe->addChild('isOverTheCounter'));
        }
        if (isset($this->manufacturer)) {
            $this->manufacturer->xmlSerialize(true, $sxe->addChild('manufacturer'));
        }
        if (isset($this->form)) {
            $this->form->xmlSerialize(true, $sxe->addChild('form'));
        }
        if (0 < count($this->ingredient)) {
            foreach ($this->ingredient as $ingredient) {
                $ingredient->xmlSerialize(true, $sxe->addChild('ingredient'));
            }
        }
        if (isset($this->package)) {
            $this->package->xmlSerialize(true, $sxe->addChild('package'));
        }
        if (0 < count($this->image)) {
            foreach ($this->image as $image) {
                $image->xmlSerialize(true, $sxe->addChild('image'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
