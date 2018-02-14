<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRDocumentReference;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A reference to a document.
 */
class FHIRDocumentReferenceContent extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The document or URL of the document along with critical metadata to prove content has integrity.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRAttachment
     */
    public $attachment = null;

    /**
     * An identifier of the document encoding, structure, and template that the document conforms to beyond the base format indicated in the mimeType.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCoding
     */
    public $format = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'DocumentReference.Content';

    /**
     * The document or URL of the document along with critical metadata to prove content has integrity.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRAttachment
     */
    public function getAttachment()
    {
        return $this->attachment;
    }

    /**
     * The document or URL of the document along with critical metadata to prove content has integrity.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRAttachment $attachment
     * @return $this
     */
    public function setAttachment($attachment)
    {
        $this->attachment = $attachment;
        return $this;
    }

    /**
     * An identifier of the document encoding, structure, and template that the document conforms to beyond the base format indicated in the mimeType.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCoding
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * An identifier of the document encoding, structure, and template that the document conforms to beyond the base format indicated in the mimeType.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCoding $format
     * @return $this
     */
    public function setFormat($format)
    {
        $this->format = $format;
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
            if (isset($data['attachment'])) {
                $this->setAttachment($data['attachment']);
            }
            if (isset($data['format'])) {
                $this->setFormat($data['format']);
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
        if (isset($this->attachment)) {
            $json['attachment'] = $this->attachment;
        }
        if (isset($this->format)) {
            $json['format'] = $this->format;
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
            $sxe = new \SimpleXMLElement('<DocumentReferenceContent xmlns="http://hl7.org/fhir"></DocumentReferenceContent>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->attachment)) {
            $this->attachment->xmlSerialize(true, $sxe->addChild('attachment'));
        }
        if (isset($this->format)) {
            $this->format->xmlSerialize(true, $sxe->addChild('format'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
