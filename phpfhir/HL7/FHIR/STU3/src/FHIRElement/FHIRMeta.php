<?php namespace HL7\FHIR\STU3\FHIRElement;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement;

/**
 * The metadata about a resource. This is content in the resource that is maintained by the infrastructure. Changes to the content may not always be associated with version changes to the resource.
 * If the element is present, it must have a value for at least one of the defined elements, an @id referenced from the Narrative, or extensions
 */
class FHIRMeta extends FHIRElement implements \JsonSerializable
{
    /**
     * The version specific identifier, as it appears in the version portion of the URL. This values changes when the resource is created, updated, or deleted.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRId
     */
    public $versionId = null;

    /**
     * When the resource last changed - e.g. when the version changed.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRInstant
     */
    public $lastUpdated = null;

    /**
     * A list of profiles (references to [[[StructureDefinition]]] resources) that this resource claims to conform to. The URL is a reference to [[[StructureDefinition.url]]].
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUri[]
     */
    public $profile = [];

    /**
     * Security labels applied to this resource. These tags connect specific resources to the overall security policy and infrastructure.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCoding[]
     */
    public $security = [];

    /**
     * Tags applied to this resource. Tags are intended to be used to identify and relate resources to process and workflow, and applications are not required to consider the tags when interpreting the meaning of a resource.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCoding[]
     */
    public $tag = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'Meta';

    /**
     * The version specific identifier, as it appears in the version portion of the URL. This values changes when the resource is created, updated, or deleted.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRId
     */
    public function getVersionId()
    {
        return $this->versionId;
    }

    /**
     * The version specific identifier, as it appears in the version portion of the URL. This values changes when the resource is created, updated, or deleted.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRId $versionId
     * @return $this
     */
    public function setVersionId($versionId)
    {
        $this->versionId = $versionId;
        return $this;
    }

    /**
     * When the resource last changed - e.g. when the version changed.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRInstant
     */
    public function getLastUpdated()
    {
        return $this->lastUpdated;
    }

    /**
     * When the resource last changed - e.g. when the version changed.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRInstant $lastUpdated
     * @return $this
     */
    public function setLastUpdated($lastUpdated)
    {
        $this->lastUpdated = $lastUpdated;
        return $this;
    }

    /**
     * A list of profiles (references to [[[StructureDefinition]]] resources) that this resource claims to conform to. The URL is a reference to [[[StructureDefinition.url]]].
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUri[]
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * A list of profiles (references to [[[StructureDefinition]]] resources) that this resource claims to conform to. The URL is a reference to [[[StructureDefinition.url]]].
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUri $profile
     * @return $this
     */
    public function addProfile($profile)
    {
        $this->profile[] = $profile;
        return $this;
    }

    /**
     * Security labels applied to this resource. These tags connect specific resources to the overall security policy and infrastructure.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCoding[]
     */
    public function getSecurity()
    {
        return $this->security;
    }

    /**
     * Security labels applied to this resource. These tags connect specific resources to the overall security policy and infrastructure.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCoding $security
     * @return $this
     */
    public function addSecurity($security)
    {
        $this->security[] = $security;
        return $this;
    }

    /**
     * Tags applied to this resource. Tags are intended to be used to identify and relate resources to process and workflow, and applications are not required to consider the tags when interpreting the meaning of a resource.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCoding[]
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * Tags applied to this resource. Tags are intended to be used to identify and relate resources to process and workflow, and applications are not required to consider the tags when interpreting the meaning of a resource.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCoding $tag
     * @return $this
     */
    public function addTag($tag)
    {
        $this->tag[] = $tag;
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
            if (isset($data['versionId'])) {
                $this->setVersionId($data['versionId']);
            }
            if (isset($data['lastUpdated'])) {
                $this->setLastUpdated($data['lastUpdated']);
            }
            if (isset($data['profile'])) {
                if (is_array($data['profile'])) {
                    foreach ($data['profile'] as $d) {
                        $this->addProfile($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"profile" must be array of objects or null, '.gettype($data['profile']).' seen.');
                }
            }
            if (isset($data['security'])) {
                if (is_array($data['security'])) {
                    foreach ($data['security'] as $d) {
                        $this->addSecurity($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"security" must be array of objects or null, '.gettype($data['security']).' seen.');
                }
            }
            if (isset($data['tag'])) {
                if (is_array($data['tag'])) {
                    foreach ($data['tag'] as $d) {
                        $this->addTag($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"tag" must be array of objects or null, '.gettype($data['tag']).' seen.');
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
        if (isset($this->versionId)) {
            $json['versionId'] = $this->versionId;
        }
        if (isset($this->lastUpdated)) {
            $json['lastUpdated'] = $this->lastUpdated;
        }
        if (0 < count($this->profile)) {
            $json['profile'] = [];
            foreach ($this->profile as $profile) {
                $json['profile'][] = $profile;
            }
        }
        if (0 < count($this->security)) {
            $json['security'] = [];
            foreach ($this->security as $security) {
                $json['security'][] = $security;
            }
        }
        if (0 < count($this->tag)) {
            $json['tag'] = [];
            foreach ($this->tag as $tag) {
                $json['tag'][] = $tag;
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
            $sxe = new \SimpleXMLElement('<Meta xmlns="http://hl7.org/fhir"></Meta>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->versionId)) {
            $this->versionId->xmlSerialize(true, $sxe->addChild('versionId'));
        }
        if (isset($this->lastUpdated)) {
            $this->lastUpdated->xmlSerialize(true, $sxe->addChild('lastUpdated'));
        }
        if (0 < count($this->profile)) {
            foreach ($this->profile as $profile) {
                $profile->xmlSerialize(true, $sxe->addChild('profile'));
            }
        }
        if (0 < count($this->security)) {
            foreach ($this->security as $security) {
                $security->xmlSerialize(true, $sxe->addChild('security'));
            }
        }
        if (0 < count($this->tag)) {
            foreach ($this->tag as $tag) {
                $tag->xmlSerialize(true, $sxe->addChild('tag'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
