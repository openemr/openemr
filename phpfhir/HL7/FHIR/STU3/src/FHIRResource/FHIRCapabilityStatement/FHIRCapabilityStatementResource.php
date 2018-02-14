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
class FHIRCapabilityStatementResource extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * A type of resource exposed via the restful interface.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRResourceType
     */
    public $type = null;

    /**
     * A specification of the profile that describes the solution's overall support for the resource, including any constraints on cardinality, bindings, lengths or other limitations. See further discussion in [Using Profiles](profiling.html#profile-uses).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $profile = null;

    /**
     * Additional information about the resource type used by the system.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRMarkdown
     */
    public $documentation = null;

    /**
     * Identifies a restful operation supported by the solution.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementInteraction[]
     */
    public $interaction = [];

    /**
     * This field is set to no-version to specify that the system does not support (server) or use (client) versioning for this resource type. If this has some other value, the server must at least correctly track and populate the versionId meta-property on resources. If the value is 'versioned-update', then the server supports all the versioning features, including using e-tags for version integrity in the API.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRResourceVersionPolicy
     */
    public $versioning = null;

    /**
     * A flag for whether the server is able to return past versions as part of the vRead operation.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public $readHistory = null;

    /**
     * A flag to indicate that the server allows or needs to allow the client to create new identities on the server (e.g. that is, the client PUTs to a location where there is no existing resource). Allowing this operation means that the server allows the client to create new identities on the server.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public $updateCreate = null;

    /**
     * A flag that indicates that the server supports conditional create.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public $conditionalCreate = null;

    /**
     * A code that indicates how the server supports conditional read.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRConditionalReadStatus
     */
    public $conditionalRead = null;

    /**
     * A flag that indicates that the server supports conditional update.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public $conditionalUpdate = null;

    /**
     * A code that indicates how the server supports conditional delete.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRConditionalDeleteStatus
     */
    public $conditionalDelete = null;

    /**
     * A set of flags that defines how references are supported.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReferenceHandlingPolicy[]
     */
    public $referencePolicy = [];

    /**
     * A list of _include values supported by the server.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString[]
     */
    public $searchInclude = [];

    /**
     * A list of _revinclude (reverse include) values supported by the server.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString[]
     */
    public $searchRevInclude = [];

    /**
     * Search parameters for implementations to support and/or make use of - either references to ones defined in the specification, or additional ones defined for/by the implementation.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementSearchParam[]
     */
    public $searchParam = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'CapabilityStatement.Resource';

    /**
     * A type of resource exposed via the restful interface.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRResourceType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * A type of resource exposed via the restful interface.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRResourceType $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * A specification of the profile that describes the solution's overall support for the resource, including any constraints on cardinality, bindings, lengths or other limitations. See further discussion in [Using Profiles](profiling.html#profile-uses).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * A specification of the profile that describes the solution's overall support for the resource, including any constraints on cardinality, bindings, lengths or other limitations. See further discussion in [Using Profiles](profiling.html#profile-uses).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $profile
     * @return $this
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;
        return $this;
    }

    /**
     * Additional information about the resource type used by the system.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRMarkdown
     */
    public function getDocumentation()
    {
        return $this->documentation;
    }

    /**
     * Additional information about the resource type used by the system.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRMarkdown $documentation
     * @return $this
     */
    public function setDocumentation($documentation)
    {
        $this->documentation = $documentation;
        return $this;
    }

    /**
     * Identifies a restful operation supported by the solution.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementInteraction[]
     */
    public function getInteraction()
    {
        return $this->interaction;
    }

    /**
     * Identifies a restful operation supported by the solution.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementInteraction $interaction
     * @return $this
     */
    public function addInteraction($interaction)
    {
        $this->interaction[] = $interaction;
        return $this;
    }

    /**
     * This field is set to no-version to specify that the system does not support (server) or use (client) versioning for this resource type. If this has some other value, the server must at least correctly track and populate the versionId meta-property on resources. If the value is 'versioned-update', then the server supports all the versioning features, including using e-tags for version integrity in the API.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRResourceVersionPolicy
     */
    public function getVersioning()
    {
        return $this->versioning;
    }

    /**
     * This field is set to no-version to specify that the system does not support (server) or use (client) versioning for this resource type. If this has some other value, the server must at least correctly track and populate the versionId meta-property on resources. If the value is 'versioned-update', then the server supports all the versioning features, including using e-tags for version integrity in the API.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRResourceVersionPolicy $versioning
     * @return $this
     */
    public function setVersioning($versioning)
    {
        $this->versioning = $versioning;
        return $this;
    }

    /**
     * A flag for whether the server is able to return past versions as part of the vRead operation.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public function getReadHistory()
    {
        return $this->readHistory;
    }

    /**
     * A flag for whether the server is able to return past versions as part of the vRead operation.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRBoolean $readHistory
     * @return $this
     */
    public function setReadHistory($readHistory)
    {
        $this->readHistory = $readHistory;
        return $this;
    }

    /**
     * A flag to indicate that the server allows or needs to allow the client to create new identities on the server (e.g. that is, the client PUTs to a location where there is no existing resource). Allowing this operation means that the server allows the client to create new identities on the server.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public function getUpdateCreate()
    {
        return $this->updateCreate;
    }

    /**
     * A flag to indicate that the server allows or needs to allow the client to create new identities on the server (e.g. that is, the client PUTs to a location where there is no existing resource). Allowing this operation means that the server allows the client to create new identities on the server.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRBoolean $updateCreate
     * @return $this
     */
    public function setUpdateCreate($updateCreate)
    {
        $this->updateCreate = $updateCreate;
        return $this;
    }

    /**
     * A flag that indicates that the server supports conditional create.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public function getConditionalCreate()
    {
        return $this->conditionalCreate;
    }

    /**
     * A flag that indicates that the server supports conditional create.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRBoolean $conditionalCreate
     * @return $this
     */
    public function setConditionalCreate($conditionalCreate)
    {
        $this->conditionalCreate = $conditionalCreate;
        return $this;
    }

    /**
     * A code that indicates how the server supports conditional read.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRConditionalReadStatus
     */
    public function getConditionalRead()
    {
        return $this->conditionalRead;
    }

    /**
     * A code that indicates how the server supports conditional read.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRConditionalReadStatus $conditionalRead
     * @return $this
     */
    public function setConditionalRead($conditionalRead)
    {
        $this->conditionalRead = $conditionalRead;
        return $this;
    }

    /**
     * A flag that indicates that the server supports conditional update.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public function getConditionalUpdate()
    {
        return $this->conditionalUpdate;
    }

    /**
     * A flag that indicates that the server supports conditional update.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRBoolean $conditionalUpdate
     * @return $this
     */
    public function setConditionalUpdate($conditionalUpdate)
    {
        $this->conditionalUpdate = $conditionalUpdate;
        return $this;
    }

    /**
     * A code that indicates how the server supports conditional delete.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRConditionalDeleteStatus
     */
    public function getConditionalDelete()
    {
        return $this->conditionalDelete;
    }

    /**
     * A code that indicates how the server supports conditional delete.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRConditionalDeleteStatus $conditionalDelete
     * @return $this
     */
    public function setConditionalDelete($conditionalDelete)
    {
        $this->conditionalDelete = $conditionalDelete;
        return $this;
    }

    /**
     * A set of flags that defines how references are supported.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReferenceHandlingPolicy[]
     */
    public function getReferencePolicy()
    {
        return $this->referencePolicy;
    }

    /**
     * A set of flags that defines how references are supported.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReferenceHandlingPolicy $referencePolicy
     * @return $this
     */
    public function addReferencePolicy($referencePolicy)
    {
        $this->referencePolicy[] = $referencePolicy;
        return $this;
    }

    /**
     * A list of _include values supported by the server.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString[]
     */
    public function getSearchInclude()
    {
        return $this->searchInclude;
    }

    /**
     * A list of _include values supported by the server.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $searchInclude
     * @return $this
     */
    public function addSearchInclude($searchInclude)
    {
        $this->searchInclude[] = $searchInclude;
        return $this;
    }

    /**
     * A list of _revinclude (reverse include) values supported by the server.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString[]
     */
    public function getSearchRevInclude()
    {
        return $this->searchRevInclude;
    }

    /**
     * A list of _revinclude (reverse include) values supported by the server.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $searchRevInclude
     * @return $this
     */
    public function addSearchRevInclude($searchRevInclude)
    {
        $this->searchRevInclude[] = $searchRevInclude;
        return $this;
    }

    /**
     * Search parameters for implementations to support and/or make use of - either references to ones defined in the specification, or additional ones defined for/by the implementation.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementSearchParam[]
     */
    public function getSearchParam()
    {
        return $this->searchParam;
    }

    /**
     * Search parameters for implementations to support and/or make use of - either references to ones defined in the specification, or additional ones defined for/by the implementation.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementSearchParam $searchParam
     * @return $this
     */
    public function addSearchParam($searchParam)
    {
        $this->searchParam[] = $searchParam;
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
            if (isset($data['profile'])) {
                $this->setProfile($data['profile']);
            }
            if (isset($data['documentation'])) {
                $this->setDocumentation($data['documentation']);
            }
            if (isset($data['interaction'])) {
                if (is_array($data['interaction'])) {
                    foreach ($data['interaction'] as $d) {
                        $this->addInteraction($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"interaction" must be array of objects or null, '.gettype($data['interaction']).' seen.');
                }
            }
            if (isset($data['versioning'])) {
                $this->setVersioning($data['versioning']);
            }
            if (isset($data['readHistory'])) {
                $this->setReadHistory($data['readHistory']);
            }
            if (isset($data['updateCreate'])) {
                $this->setUpdateCreate($data['updateCreate']);
            }
            if (isset($data['conditionalCreate'])) {
                $this->setConditionalCreate($data['conditionalCreate']);
            }
            if (isset($data['conditionalRead'])) {
                $this->setConditionalRead($data['conditionalRead']);
            }
            if (isset($data['conditionalUpdate'])) {
                $this->setConditionalUpdate($data['conditionalUpdate']);
            }
            if (isset($data['conditionalDelete'])) {
                $this->setConditionalDelete($data['conditionalDelete']);
            }
            if (isset($data['referencePolicy'])) {
                if (is_array($data['referencePolicy'])) {
                    foreach ($data['referencePolicy'] as $d) {
                        $this->addReferencePolicy($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"referencePolicy" must be array of objects or null, '.gettype($data['referencePolicy']).' seen.');
                }
            }
            if (isset($data['searchInclude'])) {
                if (is_array($data['searchInclude'])) {
                    foreach ($data['searchInclude'] as $d) {
                        $this->addSearchInclude($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"searchInclude" must be array of objects or null, '.gettype($data['searchInclude']).' seen.');
                }
            }
            if (isset($data['searchRevInclude'])) {
                if (is_array($data['searchRevInclude'])) {
                    foreach ($data['searchRevInclude'] as $d) {
                        $this->addSearchRevInclude($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"searchRevInclude" must be array of objects or null, '.gettype($data['searchRevInclude']).' seen.');
                }
            }
            if (isset($data['searchParam'])) {
                if (is_array($data['searchParam'])) {
                    foreach ($data['searchParam'] as $d) {
                        $this->addSearchParam($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"searchParam" must be array of objects or null, '.gettype($data['searchParam']).' seen.');
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
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->profile)) {
            $json['profile'] = $this->profile;
        }
        if (isset($this->documentation)) {
            $json['documentation'] = $this->documentation;
        }
        if (0 < count($this->interaction)) {
            $json['interaction'] = [];
            foreach ($this->interaction as $interaction) {
                $json['interaction'][] = $interaction;
            }
        }
        if (isset($this->versioning)) {
            $json['versioning'] = $this->versioning;
        }
        if (isset($this->readHistory)) {
            $json['readHistory'] = $this->readHistory;
        }
        if (isset($this->updateCreate)) {
            $json['updateCreate'] = $this->updateCreate;
        }
        if (isset($this->conditionalCreate)) {
            $json['conditionalCreate'] = $this->conditionalCreate;
        }
        if (isset($this->conditionalRead)) {
            $json['conditionalRead'] = $this->conditionalRead;
        }
        if (isset($this->conditionalUpdate)) {
            $json['conditionalUpdate'] = $this->conditionalUpdate;
        }
        if (isset($this->conditionalDelete)) {
            $json['conditionalDelete'] = $this->conditionalDelete;
        }
        if (0 < count($this->referencePolicy)) {
            $json['referencePolicy'] = [];
            foreach ($this->referencePolicy as $referencePolicy) {
                $json['referencePolicy'][] = $referencePolicy;
            }
        }
        if (0 < count($this->searchInclude)) {
            $json['searchInclude'] = [];
            foreach ($this->searchInclude as $searchInclude) {
                $json['searchInclude'][] = $searchInclude;
            }
        }
        if (0 < count($this->searchRevInclude)) {
            $json['searchRevInclude'] = [];
            foreach ($this->searchRevInclude as $searchRevInclude) {
                $json['searchRevInclude'][] = $searchRevInclude;
            }
        }
        if (0 < count($this->searchParam)) {
            $json['searchParam'] = [];
            foreach ($this->searchParam as $searchParam) {
                $json['searchParam'][] = $searchParam;
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
            $sxe = new \SimpleXMLElement('<CapabilityStatementResource xmlns="http://hl7.org/fhir"></CapabilityStatementResource>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->profile)) {
            $this->profile->xmlSerialize(true, $sxe->addChild('profile'));
        }
        if (isset($this->documentation)) {
            $this->documentation->xmlSerialize(true, $sxe->addChild('documentation'));
        }
        if (0 < count($this->interaction)) {
            foreach ($this->interaction as $interaction) {
                $interaction->xmlSerialize(true, $sxe->addChild('interaction'));
            }
        }
        if (isset($this->versioning)) {
            $this->versioning->xmlSerialize(true, $sxe->addChild('versioning'));
        }
        if (isset($this->readHistory)) {
            $this->readHistory->xmlSerialize(true, $sxe->addChild('readHistory'));
        }
        if (isset($this->updateCreate)) {
            $this->updateCreate->xmlSerialize(true, $sxe->addChild('updateCreate'));
        }
        if (isset($this->conditionalCreate)) {
            $this->conditionalCreate->xmlSerialize(true, $sxe->addChild('conditionalCreate'));
        }
        if (isset($this->conditionalRead)) {
            $this->conditionalRead->xmlSerialize(true, $sxe->addChild('conditionalRead'));
        }
        if (isset($this->conditionalUpdate)) {
            $this->conditionalUpdate->xmlSerialize(true, $sxe->addChild('conditionalUpdate'));
        }
        if (isset($this->conditionalDelete)) {
            $this->conditionalDelete->xmlSerialize(true, $sxe->addChild('conditionalDelete'));
        }
        if (0 < count($this->referencePolicy)) {
            foreach ($this->referencePolicy as $referencePolicy) {
                $referencePolicy->xmlSerialize(true, $sxe->addChild('referencePolicy'));
            }
        }
        if (0 < count($this->searchInclude)) {
            foreach ($this->searchInclude as $searchInclude) {
                $searchInclude->xmlSerialize(true, $sxe->addChild('searchInclude'));
            }
        }
        if (0 < count($this->searchRevInclude)) {
            foreach ($this->searchRevInclude as $searchRevInclude) {
                $searchRevInclude->xmlSerialize(true, $sxe->addChild('searchRevInclude'));
            }
        }
        if (0 < count($this->searchParam)) {
            foreach ($this->searchParam as $searchParam) {
                $searchParam->xmlSerialize(true, $sxe->addChild('searchParam'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
