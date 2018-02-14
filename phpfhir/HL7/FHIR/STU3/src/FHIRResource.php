<?php namespace HL7\FHIR\STU3;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 *  
 */

/**
 * This is the base resource type for everything.
 */
class FHIRResource implements \JsonSerializable
{
    /**
     * The logical id of the resource, as used in the URL for the resource. Once assigned, this value never changes.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRId
     */
    public $id = null;

    /**
     * The metadata about the resource. This is content that is maintained by the infrastructure. Changes to the content may not always be associated with version changes to the resource.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRMeta
     */
    public $meta = null;

    /**
     * A reference to a set of rules that were followed when the resource was constructed, and which must be understood when processing the content.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public $implicitRules = null;

    /**
     * The base language in which the resource is written.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCode
     */
    public $language = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Resource';

    /**
     * The logical id of the resource, as used in the URL for the resource. Once assigned, this value never changes.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRId
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * The logical id of the resource, as used in the URL for the resource. Once assigned, this value never changes.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRId $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * The metadata about the resource. This is content that is maintained by the infrastructure. Changes to the content may not always be associated with version changes to the resource.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRMeta
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * The metadata about the resource. This is content that is maintained by the infrastructure. Changes to the content may not always be associated with version changes to the resource.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRMeta $meta
     * @return $this
     */
    public function setMeta($meta)
    {
        $this->meta = $meta;
        return $this;
    }

    /**
     * A reference to a set of rules that were followed when the resource was constructed, and which must be understood when processing the content.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public function getImplicitRules()
    {
        return $this->implicitRules;
    }

    /**
     * A reference to a set of rules that were followed when the resource was constructed, and which must be understood when processing the content.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUri $implicitRules
     * @return $this
     */
    public function setImplicitRules($implicitRules)
    {
        $this->implicitRules = $implicitRules;
        return $this;
    }

    /**
     * The base language in which the resource is written.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCode
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * The base language in which the resource is written.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCode $language
     * @return $this
     */
    public function setLanguage($language)
    {
        $this->language = $language;
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
            if (isset($data['id'])) {
                $this->setId($data['id']);
            }
            if (isset($data['meta'])) {
                $this->setMeta($data['meta']);
            }
            if (isset($data['implicitRules'])) {
                $this->setImplicitRules($data['implicitRules']);
            }
            if (isset($data['language'])) {
                $this->setLanguage($data['language']);
            }
        } else if (null !== $data) {
            throw new \InvalidArgumentException('$data expected to be array of values, saw "'.gettype($data).'"');
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getId();
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $json = [];
        if (isset($this->id)) {
            $json['id'] = $this->id;
        }
        if (isset($this->meta)) {
            $json['meta'] = $this->meta;
        }
        if (isset($this->implicitRules)) {
            $json['implicitRules'] = $this->implicitRules;
        }
        if (isset($this->language)) {
            $json['language'] = $this->language;
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
            $sxe = new \SimpleXMLElement('<Resource xmlns="http://hl7.org/fhir"></Resource>');
        }
        if (isset($this->id)) {
            $this->id->xmlSerialize(true, $sxe->addChild('id'));
        }
        if (isset($this->meta)) {
            $this->meta->xmlSerialize(true, $sxe->addChild('meta'));
        }
        if (isset($this->implicitRules)) {
            $this->implicitRules->xmlSerialize(true, $sxe->addChild('implicitRules'));
        }
        if (isset($this->language)) {
            $this->language->xmlSerialize(true, $sxe->addChild('language'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
