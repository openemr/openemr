<?php namespace HL7\FHIR\STU3\FHIRElement;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement;

/**
 * For referring to data content defined in other formats.
 * If the element is present, it must have a value for at least one of the defined elements, an @id referenced from the Narrative, or extensions
 */
class FHIRAttachment extends FHIRElement implements \JsonSerializable
{
    /**
     * Identifies the type of the data in the attachment and allows a method to be chosen to interpret or render the data. Includes mime type parameters such as charset where appropriate.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCode
     */
    public $contentType = null;

    /**
     * The human language of the content. The value can be any valid value according to BCP 47.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCode
     */
    public $language = null;

    /**
     * The actual data of the attachment - a sequence of bytes. In XML, represented using base64.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRBase64Binary
     */
    public $data = null;

    /**
     * An alternative location where the data can be accessed.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public $url = null;

    /**
     * The number of bytes of data that make up this attachment (before base64 encoding, if that is done).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUnsignedInt
     */
    public $size = null;

    /**
     * The calculated hash of the data using SHA-1. Represented using base64.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRBase64Binary
     */
    public $hash = null;

    /**
     * A label or set of text to display in place of the data.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $title = null;

    /**
     * The date that the attachment was first created.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDateTime
     */
    public $creation = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Attachment';

    /**
     * Identifies the type of the data in the attachment and allows a method to be chosen to interpret or render the data. Includes mime type parameters such as charset where appropriate.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCode
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Identifies the type of the data in the attachment and allows a method to be chosen to interpret or render the data. Includes mime type parameters such as charset where appropriate.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCode $contentType
     * @return $this
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
        return $this;
    }

    /**
     * The human language of the content. The value can be any valid value according to BCP 47.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCode
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * The human language of the content. The value can be any valid value according to BCP 47.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCode $language
     * @return $this
     */
    public function setLanguage($language)
    {
        $this->language = $language;
        return $this;
    }

    /**
     * The actual data of the attachment - a sequence of bytes. In XML, represented using base64.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRBase64Binary
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * The actual data of the attachment - a sequence of bytes. In XML, represented using base64.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRBase64Binary $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * An alternative location where the data can be accessed.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * An alternative location where the data can be accessed.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUri $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * The number of bytes of data that make up this attachment (before base64 encoding, if that is done).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUnsignedInt
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * The number of bytes of data that make up this attachment (before base64 encoding, if that is done).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUnsignedInt $size
     * @return $this
     */
    public function setSize($size)
    {
        $this->size = $size;
        return $this;
    }

    /**
     * The calculated hash of the data using SHA-1. Represented using base64.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRBase64Binary
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * The calculated hash of the data using SHA-1. Represented using base64.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRBase64Binary $hash
     * @return $this
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
        return $this;
    }

    /**
     * A label or set of text to display in place of the data.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * A label or set of text to display in place of the data.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * The date that the attachment was first created.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDateTime
     */
    public function getCreation()
    {
        return $this->creation;
    }

    /**
     * The date that the attachment was first created.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDateTime $creation
     * @return $this
     */
    public function setCreation($creation)
    {
        $this->creation = $creation;
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
            if (isset($data['language'])) {
                $this->setLanguage($data['language']);
            }
            if (isset($data['data'])) {
                $this->setData($data['data']);
            }
            if (isset($data['url'])) {
                $this->setUrl($data['url']);
            }
            if (isset($data['size'])) {
                $this->setSize($data['size']);
            }
            if (isset($data['hash'])) {
                $this->setHash($data['hash']);
            }
            if (isset($data['title'])) {
                $this->setTitle($data['title']);
            }
            if (isset($data['creation'])) {
                $this->setCreation($data['creation']);
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
        if (isset($this->contentType)) {
            $json['contentType'] = $this->contentType;
        }
        if (isset($this->language)) {
            $json['language'] = $this->language;
        }
        if (isset($this->data)) {
            $json['data'] = $this->data;
        }
        if (isset($this->url)) {
            $json['url'] = $this->url;
        }
        if (isset($this->size)) {
            $json['size'] = $this->size;
        }
        if (isset($this->hash)) {
            $json['hash'] = $this->hash;
        }
        if (isset($this->title)) {
            $json['title'] = $this->title;
        }
        if (isset($this->creation)) {
            $json['creation'] = $this->creation;
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
            $sxe = new \SimpleXMLElement('<Attachment xmlns="http://hl7.org/fhir"></Attachment>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->contentType)) {
            $this->contentType->xmlSerialize(true, $sxe->addChild('contentType'));
        }
        if (isset($this->language)) {
            $this->language->xmlSerialize(true, $sxe->addChild('language'));
        }
        if (isset($this->data)) {
            $this->data->xmlSerialize(true, $sxe->addChild('data'));
        }
        if (isset($this->url)) {
            $this->url->xmlSerialize(true, $sxe->addChild('url'));
        }
        if (isset($this->size)) {
            $this->size->xmlSerialize(true, $sxe->addChild('size'));
        }
        if (isset($this->hash)) {
            $this->hash->xmlSerialize(true, $sxe->addChild('hash'));
        }
        if (isset($this->title)) {
            $this->title->xmlSerialize(true, $sxe->addChild('title'));
        }
        if (isset($this->creation)) {
            $this->creation->xmlSerialize(true, $sxe->addChild('creation'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
