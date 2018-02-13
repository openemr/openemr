<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRCapabilityStatement;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A Capability Statement documents a set of capabilities (behaviors) of a FHIR Server that may be used as a statement of actual server functionality or a statement of required or desired server implementation.
 */
class FHIRCapabilityStatementDocument extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Mode of this document declaration - whether an application is a producer or consumer.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDocumentMode
     */
    public $mode = null;

    /**
     * A description of how the application supports or uses the specified document profile.  For example, when documents are created, what action is taken with consumed documents, etc.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $documentation = null;

    /**
     * A constraint on a resource used in the document.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $profile = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'CapabilityStatement.Document';

    /**
     * Mode of this document declaration - whether an application is a producer or consumer.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDocumentMode
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Mode of this document declaration - whether an application is a producer or consumer.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDocumentMode $mode
     * @return $this
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
        return $this;
    }

    /**
     * A description of how the application supports or uses the specified document profile.  For example, when documents are created, what action is taken with consumed documents, etc.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getDocumentation()
    {
        return $this->documentation;
    }

    /**
     * A description of how the application supports or uses the specified document profile.  For example, when documents are created, what action is taken with consumed documents, etc.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $documentation
     * @return $this
     */
    public function setDocumentation($documentation)
    {
        $this->documentation = $documentation;
        return $this;
    }

    /**
     * A constraint on a resource used in the document.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * A constraint on a resource used in the document.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $profile
     * @return $this
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;
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
            if (isset($data['mode'])) {
                $this->setMode($data['mode']);
            }
            if (isset($data['documentation'])) {
                $this->setDocumentation($data['documentation']);
            }
            if (isset($data['profile'])) {
                $this->setProfile($data['profile']);
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
        if (isset($this->mode)) {
            $json['mode'] = $this->mode;
        }
        if (isset($this->documentation)) {
            $json['documentation'] = $this->documentation;
        }
        if (isset($this->profile)) {
            $json['profile'] = $this->profile;
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
            $sxe = new \SimpleXMLElement('<CapabilityStatementDocument xmlns="http://hl7.org/fhir"></CapabilityStatementDocument>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->mode)) {
            $this->mode->xmlSerialize(true, $sxe->addChild('mode'));
        }
        if (isset($this->documentation)) {
            $this->documentation->xmlSerialize(true, $sxe->addChild('documentation'));
        }
        if (isset($this->profile)) {
            $this->profile->xmlSerialize(true, $sxe->addChild('profile'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
