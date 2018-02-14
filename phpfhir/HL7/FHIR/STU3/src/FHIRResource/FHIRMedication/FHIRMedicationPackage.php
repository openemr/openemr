<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRMedication;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * This resource is primarily used for the identification and definition of a medication. It covers the ingredients and the packaging for a medication.
 */
class FHIRMedicationPackage extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The kind of container that this package comes as.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $container = null;

    /**
     * A set of components that go to make up the described item.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRMedication\FHIRMedicationContent[]
     */
    public $content = [];

    /**
     * Information about a group of medication produced or packaged from one production run.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRMedication\FHIRMedicationBatch[]
     */
    public $batch = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'Medication.Package';

    /**
     * The kind of container that this package comes as.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * The kind of container that this package comes as.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $container
     * @return $this
     */
    public function setContainer($container)
    {
        $this->container = $container;
        return $this;
    }

    /**
     * A set of components that go to make up the described item.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRMedication\FHIRMedicationContent[]
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * A set of components that go to make up the described item.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRMedication\FHIRMedicationContent $content
     * @return $this
     */
    public function addContent($content)
    {
        $this->content[] = $content;
        return $this;
    }

    /**
     * Information about a group of medication produced or packaged from one production run.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRMedication\FHIRMedicationBatch[]
     */
    public function getBatch()
    {
        return $this->batch;
    }

    /**
     * Information about a group of medication produced or packaged from one production run.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRMedication\FHIRMedicationBatch $batch
     * @return $this
     */
    public function addBatch($batch)
    {
        $this->batch[] = $batch;
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
            if (isset($data['container'])) {
                $this->setContainer($data['container']);
            }
            if (isset($data['content'])) {
                if (is_array($data['content'])) {
                    foreach ($data['content'] as $d) {
                        $this->addContent($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"content" must be array of objects or null, '.gettype($data['content']).' seen.');
                }
            }
            if (isset($data['batch'])) {
                if (is_array($data['batch'])) {
                    foreach ($data['batch'] as $d) {
                        $this->addBatch($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"batch" must be array of objects or null, '.gettype($data['batch']).' seen.');
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
        if (isset($this->container)) {
            $json['container'] = $this->container;
        }
        if (0 < count($this->content)) {
            $json['content'] = [];
            foreach ($this->content as $content) {
                $json['content'][] = $content;
            }
        }
        if (0 < count($this->batch)) {
            $json['batch'] = [];
            foreach ($this->batch as $batch) {
                $json['batch'][] = $batch;
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
            $sxe = new \SimpleXMLElement('<MedicationPackage xmlns="http://hl7.org/fhir"></MedicationPackage>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->container)) {
            $this->container->xmlSerialize(true, $sxe->addChild('container'));
        }
        if (0 < count($this->content)) {
            foreach ($this->content as $content) {
                $content->xmlSerialize(true, $sxe->addChild('content'));
            }
        }
        if (0 < count($this->batch)) {
            foreach ($this->batch as $batch) {
                $batch->xmlSerialize(true, $sxe->addChild('batch'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
