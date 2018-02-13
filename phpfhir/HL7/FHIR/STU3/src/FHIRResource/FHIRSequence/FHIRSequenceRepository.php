<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRSequence;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * Raw data describing a biological sequence.
 */
class FHIRSequenceRepository extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Click and see / RESTful API / Need login to see / RESTful API with authentication / Other ways to see resource.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRRepositoryType
     */
    public $type = null;

    /**
     * URI of an external repository which contains further details about the genetics data.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public $url = null;

    /**
     * URI of an external repository which contains further details about the genetics data.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $name = null;

    /**
     * Id of the variant in this external repository. The server will understand how to use this id to call for more info about datasets in external repository.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $datasetId = null;

    /**
     * Id of the variantset in this external repository. The server will understand how to use this id to call for more info about variantsets in external repository.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $variantsetId = null;

    /**
     * Id of the read in this external repository.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $readsetId = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Sequence.Repository';

    /**
     * Click and see / RESTful API / Need login to see / RESTful API with authentication / Other ways to see resource.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRRepositoryType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Click and see / RESTful API / Need login to see / RESTful API with authentication / Other ways to see resource.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRRepositoryType $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * URI of an external repository which contains further details about the genetics data.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * URI of an external repository which contains further details about the genetics data.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUri $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * URI of an external repository which contains further details about the genetics data.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * URI of an external repository which contains further details about the genetics data.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Id of the variant in this external repository. The server will understand how to use this id to call for more info about datasets in external repository.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getDatasetId()
    {
        return $this->datasetId;
    }

    /**
     * Id of the variant in this external repository. The server will understand how to use this id to call for more info about datasets in external repository.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $datasetId
     * @return $this
     */
    public function setDatasetId($datasetId)
    {
        $this->datasetId = $datasetId;
        return $this;
    }

    /**
     * Id of the variantset in this external repository. The server will understand how to use this id to call for more info about variantsets in external repository.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getVariantsetId()
    {
        return $this->variantsetId;
    }

    /**
     * Id of the variantset in this external repository. The server will understand how to use this id to call for more info about variantsets in external repository.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $variantsetId
     * @return $this
     */
    public function setVariantsetId($variantsetId)
    {
        $this->variantsetId = $variantsetId;
        return $this;
    }

    /**
     * Id of the read in this external repository.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getReadsetId()
    {
        return $this->readsetId;
    }

    /**
     * Id of the read in this external repository.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $readsetId
     * @return $this
     */
    public function setReadsetId($readsetId)
    {
        $this->readsetId = $readsetId;
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
            if (isset($data['url'])) {
                $this->setUrl($data['url']);
            }
            if (isset($data['name'])) {
                $this->setName($data['name']);
            }
            if (isset($data['datasetId'])) {
                $this->setDatasetId($data['datasetId']);
            }
            if (isset($data['variantsetId'])) {
                $this->setVariantsetId($data['variantsetId']);
            }
            if (isset($data['readsetId'])) {
                $this->setReadsetId($data['readsetId']);
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
        if (isset($this->url)) {
            $json['url'] = $this->url;
        }
        if (isset($this->name)) {
            $json['name'] = $this->name;
        }
        if (isset($this->datasetId)) {
            $json['datasetId'] = $this->datasetId;
        }
        if (isset($this->variantsetId)) {
            $json['variantsetId'] = $this->variantsetId;
        }
        if (isset($this->readsetId)) {
            $json['readsetId'] = $this->readsetId;
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
            $sxe = new \SimpleXMLElement('<SequenceRepository xmlns="http://hl7.org/fhir"></SequenceRepository>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->url)) {
            $this->url->xmlSerialize(true, $sxe->addChild('url'));
        }
        if (isset($this->name)) {
            $this->name->xmlSerialize(true, $sxe->addChild('name'));
        }
        if (isset($this->datasetId)) {
            $this->datasetId->xmlSerialize(true, $sxe->addChild('datasetId'));
        }
        if (isset($this->variantsetId)) {
            $this->variantsetId->xmlSerialize(true, $sxe->addChild('variantsetId'));
        }
        if (isset($this->readsetId)) {
            $this->readsetId->xmlSerialize(true, $sxe->addChild('readsetId'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
