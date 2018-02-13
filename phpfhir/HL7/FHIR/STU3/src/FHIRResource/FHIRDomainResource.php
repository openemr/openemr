<?php namespace HL7\FHIR\STU3\FHIRResource;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRResource;
use HL7\FHIR\STU3\FHIRResourceContainer;

/**
 * A resource that includes narrative, extensions, and contained resources.
 */
class FHIRDomainResource extends FHIRResource implements \JsonSerializable
{
    /**
     * A human-readable narrative that contains a summary of the resource, and may be used to represent the content of the resource to a human. The narrative need not encode all the structured data, but is required to contain sufficient detail to make it "clinically safe" for a human to just read the narrative. Resource definitions may define what content should be represented in the narrative to ensure clinical safety.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRNarrative
     */
    public $text = null;

    /**
     * These resources do not have an independent existence apart from the resource that contains them - they cannot be identified independently, and nor can they have their own independent transaction scope.
     * @var \HL7\FHIR\STU3\FHIRResourceContainer[]
     */
    public $contained = [];

    /**
     * May be used to represent additional information that is not part of the basic definition of the resource. In order to make the use of extensions safe and manageable, there is a strict set of governance  applied to the definition and use of extensions. Though any implementer is allowed to define an extension, there is a set of requirements that SHALL be met as part of the definition of the extension.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRExtension[]
     */
    public $extension = [];

    /**
     * May be used to represent additional information that is not part of the basic definition of the resource, and that modifies the understanding of the element that contains it. Usually modifier elements provide negation or qualification. In order to make the use of extensions safe and manageable, there is a strict set of governance applied to the definition and use of extensions. Though any implementer is allowed to define an extension, there is a set of requirements that SHALL be met as part of the definition of the extension. Applications processing a resource are required to check for modifier extensions.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRExtension[]
     */
    public $modifierExtension = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'DomainResource';

    /**
     * A human-readable narrative that contains a summary of the resource, and may be used to represent the content of the resource to a human. The narrative need not encode all the structured data, but is required to contain sufficient detail to make it "clinically safe" for a human to just read the narrative. Resource definitions may define what content should be represented in the narrative to ensure clinical safety.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRNarrative
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * A human-readable narrative that contains a summary of the resource, and may be used to represent the content of the resource to a human. The narrative need not encode all the structured data, but is required to contain sufficient detail to make it "clinically safe" for a human to just read the narrative. Resource definitions may define what content should be represented in the narrative to ensure clinical safety.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRNarrative $text
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    /**
     * These resources do not have an independent existence apart from the resource that contains them - they cannot be identified independently, and nor can they have their own independent transaction scope.
     * @return array
     */
    public function getContained()
    {
        if (count($this->contained) > 0) {
            $resources = [];
            foreach ($this->contained as $container) {
                if ($container instanceof FHIRResourceContainer) {
                    $resources[] = $container->jsonSerialize();
                }
            }
            return $resources;
        }
        return [];
    }

    /**
     * These resources do not have an independent existence apart from the resource that contains them - they cannot be identified independently, and nor can they have their own independent transaction scope.
     * @param \HL7\FHIR\STU3\FHIRResourceContainer $contained
     * @return $this
     */
    public function addContained($contained)
    {
        $this->contained[] = $contained;
        return $this;
    }

    /**
     * May be used to represent additional information that is not part of the basic definition of the resource. In order to make the use of extensions safe and manageable, there is a strict set of governance  applied to the definition and use of extensions. Though any implementer is allowed to define an extension, there is a set of requirements that SHALL be met as part of the definition of the extension.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRExtension[]
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * May be used to represent additional information that is not part of the basic definition of the resource. In order to make the use of extensions safe and manageable, there is a strict set of governance  applied to the definition and use of extensions. Though any implementer is allowed to define an extension, there is a set of requirements that SHALL be met as part of the definition of the extension.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRExtension $extension
     * @return $this
     */
    public function addExtension($extension)
    {
        $this->extension[] = $extension;
        return $this;
    }

    /**
     * May be used to represent additional information that is not part of the basic definition of the resource, and that modifies the understanding of the element that contains it. Usually modifier elements provide negation or qualification. In order to make the use of extensions safe and manageable, there is a strict set of governance applied to the definition and use of extensions. Though any implementer is allowed to define an extension, there is a set of requirements that SHALL be met as part of the definition of the extension. Applications processing a resource are required to check for modifier extensions.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRExtension[]
     */
    public function getModifierExtension()
    {
        return $this->modifierExtension;
    }

    /**
     * May be used to represent additional information that is not part of the basic definition of the resource, and that modifies the understanding of the element that contains it. Usually modifier elements provide negation or qualification. In order to make the use of extensions safe and manageable, there is a strict set of governance applied to the definition and use of extensions. Though any implementer is allowed to define an extension, there is a set of requirements that SHALL be met as part of the definition of the extension. Applications processing a resource are required to check for modifier extensions.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRExtension $modifierExtension
     * @return $this
     */
    public function addModifierExtension($modifierExtension)
    {
        $this->modifierExtension[] = $modifierExtension;
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
            if (isset($data['text'])) {
                $this->setText($data['text']);
            }
            if (isset($data['contained'])) {
                if (is_array($data['contained'])) {
                    foreach ($data['contained'] as $d) {
                        $this->addContained($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"contained" must be array of objects or null, '.gettype($data['contained']).' seen.');
                }
            }
            if (isset($data['extension'])) {
                if (is_array($data['extension'])) {
                    foreach ($data['extension'] as $d) {
                        $this->addExtension($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"extension" must be array of objects or null, '.gettype($data['extension']).' seen.');
                }
            }
            if (isset($data['modifierExtension'])) {
                if (is_array($data['modifierExtension'])) {
                    foreach ($data['modifierExtension'] as $d) {
                        $this->addModifierExtension($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"modifierExtension" must be array of objects or null, '.gettype($data['modifierExtension']).' seen.');
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
        if (isset($this->text)) {
            $json['text'] = $this->text;
        }
        if (0 < count($this->contained)) {
            $json['contained'] = [];
            foreach ($this->contained as $contained) {
                $json['contained'][] = $contained;
            }
        }
        if (0 < count($this->extension)) {
            $json['extension'] = [];
            foreach ($this->extension as $extension) {
                $json['extension'][] = $extension;
            }
        }
        if (0 < count($this->modifierExtension)) {
            $json['modifierExtension'] = [];
            foreach ($this->modifierExtension as $modifierExtension) {
                $json['modifierExtension'][] = $modifierExtension;
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
            $sxe = new \SimpleXMLElement('<DomainResource xmlns="http://hl7.org/fhir"></DomainResource>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->text)) {
            $this->text->xmlSerialize(true, $sxe->addChild('text'));
        }
        if (0 < count($this->contained)) {
            foreach ($this->contained as $contained) {
                $contained->xmlSerialize(true, $sxe->addChild('contained'));
            }
        }
        if (0 < count($this->extension)) {
            foreach ($this->extension as $extension) {
                $extension->xmlSerialize(true, $sxe->addChild('extension'));
            }
        }
        if (0 < count($this->modifierExtension)) {
            foreach ($this->modifierExtension as $modifierExtension) {
                $modifierExtension->xmlSerialize(true, $sxe->addChild('modifierExtension'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
