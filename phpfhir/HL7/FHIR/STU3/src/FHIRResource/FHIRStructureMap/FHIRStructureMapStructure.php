<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRStructureMap;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A Map of relationships between 2 structures that can be used to transform data.
 */
class FHIRStructureMapStructure extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The canonical URL that identifies the structure.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public $url = null;

    /**
     * How the referenced structure is used in this mapping.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRStructureMapModelMode
     */
    public $mode = null;

    /**
     * The name used for this type in the map.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $alias = null;

    /**
     * Documentation that describes how the structure is used in the mapping.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $documentation = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'StructureMap.Structure';

    /**
     * The canonical URL that identifies the structure.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * The canonical URL that identifies the structure.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUri $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * How the referenced structure is used in this mapping.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRStructureMapModelMode
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * How the referenced structure is used in this mapping.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRStructureMapModelMode $mode
     * @return $this
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
        return $this;
    }

    /**
     * The name used for this type in the map.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * The name used for this type in the map.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $alias
     * @return $this
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * Documentation that describes how the structure is used in the mapping.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getDocumentation()
    {
        return $this->documentation;
    }

    /**
     * Documentation that describes how the structure is used in the mapping.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $documentation
     * @return $this
     */
    public function setDocumentation($documentation)
    {
        $this->documentation = $documentation;
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
            if (isset($data['url'])) {
                $this->setUrl($data['url']);
            }
            if (isset($data['mode'])) {
                $this->setMode($data['mode']);
            }
            if (isset($data['alias'])) {
                $this->setAlias($data['alias']);
            }
            if (isset($data['documentation'])) {
                $this->setDocumentation($data['documentation']);
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
        if (isset($this->url)) {
            $json['url'] = $this->url;
        }
        if (isset($this->mode)) {
            $json['mode'] = $this->mode;
        }
        if (isset($this->alias)) {
            $json['alias'] = $this->alias;
        }
        if (isset($this->documentation)) {
            $json['documentation'] = $this->documentation;
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
            $sxe = new \SimpleXMLElement('<StructureMapStructure xmlns="http://hl7.org/fhir"></StructureMapStructure>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->url)) {
            $this->url->xmlSerialize(true, $sxe->addChild('url'));
        }
        if (isset($this->mode)) {
            $this->mode->xmlSerialize(true, $sxe->addChild('mode'));
        }
        if (isset($this->alias)) {
            $this->alias->xmlSerialize(true, $sxe->addChild('alias'));
        }
        if (isset($this->documentation)) {
            $this->documentation->xmlSerialize(true, $sxe->addChild('documentation'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
