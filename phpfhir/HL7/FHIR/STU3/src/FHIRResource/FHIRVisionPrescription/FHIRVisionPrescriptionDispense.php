<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRVisionPrescription;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * An authorization for the supply of glasses and/or contact lenses to a patient.
 */
class FHIRVisionPrescriptionDispense extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Identifies the type of vision correction product which is required for the patient.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $product = null;

    /**
     * The eye for which the lens applies.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRVisionEyes
     */
    public $eye = null;

    /**
     * Lens power measured in diopters (0.25 units).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public $sphere = null;

    /**
     * Power adjustment for astigmatism measured in diopters (0.25 units).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public $cylinder = null;

    /**
     * Adjustment for astigmatism measured in integer degrees.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRInteger
     */
    public $axis = null;

    /**
     * Amount of prism to compensate for eye alignment in fractional units.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public $prism = null;

    /**
     * The relative base, or reference lens edge, for the prism.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRVisionBase
     */
    public $base = null;

    /**
     * Power adjustment for multifocal lenses measured in diopters (0.25 units).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public $add = null;

    /**
     * Contact lens power measured in diopters (0.25 units).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public $power = null;

    /**
     * Back curvature measured in millimeters.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public $backCurve = null;

    /**
     * Contact lens diameter measured in millimeters.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public $diameter = null;

    /**
     * The recommended maximum wear period for the lens.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRQuantity
     */
    public $duration = null;

    /**
     * Special color or pattern.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $color = null;

    /**
     * Brand recommendations or restrictions.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $brand = null;

    /**
     * Notes for special requirements such as coatings and lens materials.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRAnnotation[]
     */
    public $note = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'VisionPrescription.Dispense';

    /**
     * Identifies the type of vision correction product which is required for the patient.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Identifies the type of vision correction product which is required for the patient.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $product
     * @return $this
     */
    public function setProduct($product)
    {
        $this->product = $product;
        return $this;
    }

    /**
     * The eye for which the lens applies.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRVisionEyes
     */
    public function getEye()
    {
        return $this->eye;
    }

    /**
     * The eye for which the lens applies.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRVisionEyes $eye
     * @return $this
     */
    public function setEye($eye)
    {
        $this->eye = $eye;
        return $this;
    }

    /**
     * Lens power measured in diopters (0.25 units).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public function getSphere()
    {
        return $this->sphere;
    }

    /**
     * Lens power measured in diopters (0.25 units).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDecimal $sphere
     * @return $this
     */
    public function setSphere($sphere)
    {
        $this->sphere = $sphere;
        return $this;
    }

    /**
     * Power adjustment for astigmatism measured in diopters (0.25 units).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public function getCylinder()
    {
        return $this->cylinder;
    }

    /**
     * Power adjustment for astigmatism measured in diopters (0.25 units).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDecimal $cylinder
     * @return $this
     */
    public function setCylinder($cylinder)
    {
        $this->cylinder = $cylinder;
        return $this;
    }

    /**
     * Adjustment for astigmatism measured in integer degrees.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRInteger
     */
    public function getAxis()
    {
        return $this->axis;
    }

    /**
     * Adjustment for astigmatism measured in integer degrees.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRInteger $axis
     * @return $this
     */
    public function setAxis($axis)
    {
        $this->axis = $axis;
        return $this;
    }

    /**
     * Amount of prism to compensate for eye alignment in fractional units.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public function getPrism()
    {
        return $this->prism;
    }

    /**
     * Amount of prism to compensate for eye alignment in fractional units.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDecimal $prism
     * @return $this
     */
    public function setPrism($prism)
    {
        $this->prism = $prism;
        return $this;
    }

    /**
     * The relative base, or reference lens edge, for the prism.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRVisionBase
     */
    public function getBase()
    {
        return $this->base;
    }

    /**
     * The relative base, or reference lens edge, for the prism.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRVisionBase $base
     * @return $this
     */
    public function setBase($base)
    {
        $this->base = $base;
        return $this;
    }

    /**
     * Power adjustment for multifocal lenses measured in diopters (0.25 units).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public function getAdd()
    {
        return $this->add;
    }

    /**
     * Power adjustment for multifocal lenses measured in diopters (0.25 units).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDecimal $add
     * @return $this
     */
    public function setAdd($add)
    {
        $this->add = $add;
        return $this;
    }

    /**
     * Contact lens power measured in diopters (0.25 units).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public function getPower()
    {
        return $this->power;
    }

    /**
     * Contact lens power measured in diopters (0.25 units).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDecimal $power
     * @return $this
     */
    public function setPower($power)
    {
        $this->power = $power;
        return $this;
    }

    /**
     * Back curvature measured in millimeters.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public function getBackCurve()
    {
        return $this->backCurve;
    }

    /**
     * Back curvature measured in millimeters.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDecimal $backCurve
     * @return $this
     */
    public function setBackCurve($backCurve)
    {
        $this->backCurve = $backCurve;
        return $this;
    }

    /**
     * Contact lens diameter measured in millimeters.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public function getDiameter()
    {
        return $this->diameter;
    }

    /**
     * Contact lens diameter measured in millimeters.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDecimal $diameter
     * @return $this
     */
    public function setDiameter($diameter)
    {
        $this->diameter = $diameter;
        return $this;
    }

    /**
     * The recommended maximum wear period for the lens.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRQuantity
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * The recommended maximum wear period for the lens.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRQuantity $duration
     * @return $this
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
        return $this;
    }

    /**
     * Special color or pattern.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Special color or pattern.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $color
     * @return $this
     */
    public function setColor($color)
    {
        $this->color = $color;
        return $this;
    }

    /**
     * Brand recommendations or restrictions.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * Brand recommendations or restrictions.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $brand
     * @return $this
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;
        return $this;
    }

    /**
     * Notes for special requirements such as coatings and lens materials.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRAnnotation[]
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Notes for special requirements such as coatings and lens materials.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRAnnotation $note
     * @return $this
     */
    public function addNote($note)
    {
        $this->note[] = $note;
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
            if (isset($data['product'])) {
                $this->setProduct($data['product']);
            }
            if (isset($data['eye'])) {
                $this->setEye($data['eye']);
            }
            if (isset($data['sphere'])) {
                $this->setSphere($data['sphere']);
            }
            if (isset($data['cylinder'])) {
                $this->setCylinder($data['cylinder']);
            }
            if (isset($data['axis'])) {
                $this->setAxis($data['axis']);
            }
            if (isset($data['prism'])) {
                $this->setPrism($data['prism']);
            }
            if (isset($data['base'])) {
                $this->setBase($data['base']);
            }
            if (isset($data['add'])) {
                $this->setAdd($data['add']);
            }
            if (isset($data['power'])) {
                $this->setPower($data['power']);
            }
            if (isset($data['backCurve'])) {
                $this->setBackCurve($data['backCurve']);
            }
            if (isset($data['diameter'])) {
                $this->setDiameter($data['diameter']);
            }
            if (isset($data['duration'])) {
                $this->setDuration($data['duration']);
            }
            if (isset($data['color'])) {
                $this->setColor($data['color']);
            }
            if (isset($data['brand'])) {
                $this->setBrand($data['brand']);
            }
            if (isset($data['note'])) {
                if (is_array($data['note'])) {
                    foreach ($data['note'] as $d) {
                        $this->addNote($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"note" must be array of objects or null, '.gettype($data['note']).' seen.');
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
        if (isset($this->product)) {
            $json['product'] = $this->product;
        }
        if (isset($this->eye)) {
            $json['eye'] = $this->eye;
        }
        if (isset($this->sphere)) {
            $json['sphere'] = $this->sphere;
        }
        if (isset($this->cylinder)) {
            $json['cylinder'] = $this->cylinder;
        }
        if (isset($this->axis)) {
            $json['axis'] = $this->axis;
        }
        if (isset($this->prism)) {
            $json['prism'] = $this->prism;
        }
        if (isset($this->base)) {
            $json['base'] = $this->base;
        }
        if (isset($this->add)) {
            $json['add'] = $this->add;
        }
        if (isset($this->power)) {
            $json['power'] = $this->power;
        }
        if (isset($this->backCurve)) {
            $json['backCurve'] = $this->backCurve;
        }
        if (isset($this->diameter)) {
            $json['diameter'] = $this->diameter;
        }
        if (isset($this->duration)) {
            $json['duration'] = $this->duration;
        }
        if (isset($this->color)) {
            $json['color'] = $this->color;
        }
        if (isset($this->brand)) {
            $json['brand'] = $this->brand;
        }
        if (0 < count($this->note)) {
            $json['note'] = [];
            foreach ($this->note as $note) {
                $json['note'][] = $note;
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
            $sxe = new \SimpleXMLElement('<VisionPrescriptionDispense xmlns="http://hl7.org/fhir"></VisionPrescriptionDispense>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->product)) {
            $this->product->xmlSerialize(true, $sxe->addChild('product'));
        }
        if (isset($this->eye)) {
            $this->eye->xmlSerialize(true, $sxe->addChild('eye'));
        }
        if (isset($this->sphere)) {
            $this->sphere->xmlSerialize(true, $sxe->addChild('sphere'));
        }
        if (isset($this->cylinder)) {
            $this->cylinder->xmlSerialize(true, $sxe->addChild('cylinder'));
        }
        if (isset($this->axis)) {
            $this->axis->xmlSerialize(true, $sxe->addChild('axis'));
        }
        if (isset($this->prism)) {
            $this->prism->xmlSerialize(true, $sxe->addChild('prism'));
        }
        if (isset($this->base)) {
            $this->base->xmlSerialize(true, $sxe->addChild('base'));
        }
        if (isset($this->add)) {
            $this->add->xmlSerialize(true, $sxe->addChild('add'));
        }
        if (isset($this->power)) {
            $this->power->xmlSerialize(true, $sxe->addChild('power'));
        }
        if (isset($this->backCurve)) {
            $this->backCurve->xmlSerialize(true, $sxe->addChild('backCurve'));
        }
        if (isset($this->diameter)) {
            $this->diameter->xmlSerialize(true, $sxe->addChild('diameter'));
        }
        if (isset($this->duration)) {
            $this->duration->xmlSerialize(true, $sxe->addChild('duration'));
        }
        if (isset($this->color)) {
            $this->color->xmlSerialize(true, $sxe->addChild('color'));
        }
        if (isset($this->brand)) {
            $this->brand->xmlSerialize(true, $sxe->addChild('brand'));
        }
        if (0 < count($this->note)) {
            foreach ($this->note as $note) {
                $note->xmlSerialize(true, $sxe->addChild('note'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
