<?php namespace HL7\FHIR\STU3\FHIRElement;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement;

/**
 * Related artifacts such as additional documentation, justification, or bibliographic references.
 * If the element is present, it must have a value for at least one of the defined elements, an @id referenced from the Narrative, or extensions
 */
class FHIRRelatedArtifact extends FHIRElement implements \JsonSerializable
{
    /**
     * The type of relationship to the related artifact.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRRelatedArtifactType
     */
    public $type = null;

    /**
     * A brief description of the document or knowledge resource being referenced, suitable for display to a consumer.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $display = null;

    /**
     * A bibliographic citation for the related artifact. This text SHOULD be formatted according to an accepted citation format.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $citation = null;

    /**
     * A url for the artifact that can be followed to access the actual content.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public $url = null;

    /**
     * The document being referenced, represented as an attachment. This is exclusive with the resource element.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRAttachment
     */
    public $document = null;

    /**
     * The related resource, such as a library, value set, profile, or other knowledge resource.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $resource = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'RelatedArtifact';

    /**
     * The type of relationship to the related artifact.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRRelatedArtifactType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * The type of relationship to the related artifact.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRRelatedArtifactType $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * A brief description of the document or knowledge resource being referenced, suitable for display to a consumer.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getDisplay()
    {
        return $this->display;
    }

    /**
     * A brief description of the document or knowledge resource being referenced, suitable for display to a consumer.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $display
     * @return $this
     */
    public function setDisplay($display)
    {
        $this->display = $display;
        return $this;
    }

    /**
     * A bibliographic citation for the related artifact. This text SHOULD be formatted according to an accepted citation format.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getCitation()
    {
        return $this->citation;
    }

    /**
     * A bibliographic citation for the related artifact. This text SHOULD be formatted according to an accepted citation format.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $citation
     * @return $this
     */
    public function setCitation($citation)
    {
        $this->citation = $citation;
        return $this;
    }

    /**
     * A url for the artifact that can be followed to access the actual content.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * A url for the artifact that can be followed to access the actual content.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUri $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * The document being referenced, represented as an attachment. This is exclusive with the resource element.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRAttachment
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * The document being referenced, represented as an attachment. This is exclusive with the resource element.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRAttachment $document
     * @return $this
     */
    public function setDocument($document)
    {
        $this->document = $document;
        return $this;
    }

    /**
     * The related resource, such as a library, value set, profile, or other knowledge resource.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * The related resource, such as a library, value set, profile, or other knowledge resource.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $resource
     * @return $this
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
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
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['display'])) {
                $this->setDisplay($data['display']);
            }
            if (isset($data['citation'])) {
                $this->setCitation($data['citation']);
            }
            if (isset($data['url'])) {
                $this->setUrl($data['url']);
            }
            if (isset($data['document'])) {
                $this->setDocument($data['document']);
            }
            if (isset($data['resource'])) {
                $this->setResource($data['resource']);
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
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->display)) {
            $json['display'] = $this->display;
        }
        if (isset($this->citation)) {
            $json['citation'] = $this->citation;
        }
        if (isset($this->url)) {
            $json['url'] = $this->url;
        }
        if (isset($this->document)) {
            $json['document'] = $this->document;
        }
        if (isset($this->resource)) {
            $json['resource'] = $this->resource;
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
            $sxe = new \SimpleXMLElement('<RelatedArtifact xmlns="http://hl7.org/fhir"></RelatedArtifact>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->display)) {
            $this->display->xmlSerialize(true, $sxe->addChild('display'));
        }
        if (isset($this->citation)) {
            $this->citation->xmlSerialize(true, $sxe->addChild('citation'));
        }
        if (isset($this->url)) {
            $this->url->xmlSerialize(true, $sxe->addChild('url'));
        }
        if (isset($this->document)) {
            $this->document->xmlSerialize(true, $sxe->addChild('document'));
        }
        if (isset($this->resource)) {
            $this->resource->xmlSerialize(true, $sxe->addChild('resource'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
