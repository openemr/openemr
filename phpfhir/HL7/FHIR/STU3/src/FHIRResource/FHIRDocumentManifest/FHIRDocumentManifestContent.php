<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRDocumentManifest;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A collection of documents compiled for a purpose together with metadata that applies to the collection.
 */
class FHIRDocumentManifestContent extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRAttachment
     */
    public $pAttachment = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $pReference = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'DocumentManifest.Content';

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRAttachment
     */
    public function getPAttachment()
    {
        return $this->pAttachment;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRAttachment $pAttachment
     * @return $this
     */
    public function setPAttachment($pAttachment)
    {
        $this->pAttachment = $pAttachment;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getPReference()
    {
        return $this->pReference;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $pReference
     * @return $this
     */
    public function setPReference($pReference)
    {
        $this->pReference = $pReference;
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
            if (isset($data['pAttachment'])) {
                $this->setPAttachment($data['pAttachment']);
            }
            if (isset($data['pReference'])) {
                $this->setPReference($data['pReference']);
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
        if (isset($this->pAttachment)) {
            $json['pAttachment'] = $this->pAttachment;
        }
        if (isset($this->pReference)) {
            $json['pReference'] = $this->pReference;
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
            $sxe = new \SimpleXMLElement('<DocumentManifestContent xmlns="http://hl7.org/fhir"></DocumentManifestContent>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->pAttachment)) {
            $this->pAttachment->xmlSerialize(true, $sxe->addChild('pAttachment'));
        }
        if (isset($this->pReference)) {
            $this->pReference->xmlSerialize(true, $sxe->addChild('pReference'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
