<?php namespace HL7\FHIR\STU3\FHIRResource;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRResource;

/**
 * A binary resource can contain any content, whether text, image, pdf, zip archive, etc.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRBinary extends FHIRResource implements \JsonSerializable
{
    /**
     * MimeType of the binary content represented as a standard MimeType (BCP 13).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCode
     */
    public $contentType = null;

    /**
     * Treat this binary as if it was this other resource for access control purposes.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $securityContext = null;

    /**
     * The actual content, base64 encoded.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRBase64Binary
     */
    public $content = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Binary';

    /**
     * MimeType of the binary content represented as a standard MimeType (BCP 13).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCode
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * MimeType of the binary content represented as a standard MimeType (BCP 13).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCode $contentType
     * @return $this
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
        return $this;
    }

    /**
     * Treat this binary as if it was this other resource for access control purposes.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getSecurityContext()
    {
        return $this->securityContext;
    }

    /**
     * Treat this binary as if it was this other resource for access control purposes.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $securityContext
     * @return $this
     */
    public function setSecurityContext($securityContext)
    {
        $this->securityContext = $securityContext;
        return $this;
    }

    /**
     * The actual content, base64 encoded.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRBase64Binary
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * The actual content, base64 encoded.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRBase64Binary $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;
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
            if (isset($data['contentType'])) {
                $this->setContentType($data['contentType']);
            }
            if (isset($data['securityContext'])) {
                $this->setSecurityContext($data['securityContext']);
            }
            if (isset($data['content'])) {
                $this->setContent($data['content']);
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
        if (isset($this->contentType)) {
            $json['contentType'] = $this->contentType;
        }
        if (isset($this->securityContext)) {
            $json['securityContext'] = $this->securityContext;
        }
        if (isset($this->content)) {
            $json['content'] = $this->content;
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
            $sxe = new \SimpleXMLElement('<Binary xmlns="http://hl7.org/fhir"></Binary>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->contentType)) {
            $this->contentType->xmlSerialize(true, $sxe->addChild('contentType'));
        }
        if (isset($this->securityContext)) {
            $this->securityContext->xmlSerialize(true, $sxe->addChild('securityContext'));
        }
        if (isset($this->content)) {
            $this->content->xmlSerialize(true, $sxe->addChild('content'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
