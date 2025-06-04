<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 *
 * Class creation date: June 14th, 2019
 *
 * PHPFHIR Copyright:
 *
 * Copyright 2016-2017 Daniel Carbone (daniel.p.carbone@gmail.com)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *        http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 *
 * FHIR Copyright Notice:
 *
 *   Copyright (c) 2011+, HL7, Inc.
 *   All rights reserved.
 *
 *   Redistribution and use in source and binary forms, with or without modification,
 *   are permitted provided that the following conditions are met:
 *
 *    * Redistributions of source code must retain the above copyright notice, this
 *      list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above copyright notice,
 *      this list of conditions and the following disclaimer in the documentation
 *      and/or other materials provided with the distribution.
 *    * Neither the name of HL7 nor the names of its contributors may be used to
 *      endorse or promote products derived from this software without specific
 *      prior written permission.
 *
 *   THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 *   ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 *   WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 *   IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 *   INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 *   NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 *   PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 *   WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 *   ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 *   POSSIBILITY OF SUCH DAMAGE.
 *
 *
 *   Generated on Thu, Dec 27, 2018 22:37+1100 for FHIR v4.0.0
 *
 *   Note: the schemas & schematrons do not contain all of the rules about what makes resources
 *   valid. Implementers will still need to be familiar with the content of the specification and with
 *   any profiles that apply to the resources in order to make a conformant implementation.
 *
 */

use OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement;

/**
 * A Capability Statement documents a set of capabilities (behaviors) of a FHIR Server for a particular version of FHIR that may be used as a statement of actual server functionality or a statement of required or desired server implementation.
 */
class FHIRCapabilityStatementResource extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * A type of resource exposed via the restful interface.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCode
     */
    public $type = null;

    /**
     * A specification of the profile that describes the solution's overall support for the resource, including any constraints on cardinality, bindings, lengths or other limitations. See further discussion in [Using Profiles](profiling.html#profile-uses).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical
     */
    public $profile = null;

    /**
     * A list of profiles that represent different use cases supported by the system. For a server, "supported by the system" means the system hosts/produces a set of resources that are conformant to a particular profile, and allows clients that use its services to search using this profile and to find appropriate data. For a client, it means the system will search by this profile and process data according to the guidance implicit in the profile. See further discussion in [Using Profiles](profiling.html#profile-uses).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical[]
     */
    public $supportedProfile = [];

    /**
     * Additional information about the resource type used by the system.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public $documentation = null;

    /**
     * Identifies a restful operation supported by the solution.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementInteraction[]
     */
    public $interaction = [];

    /**
     * This field is set to no-version to specify that the system does not support (server) or use (client) versioning for this resource type. If this has some other value, the server must at least correctly track and populate the versionId meta-property on resources. If the value is 'versioned-update', then the server supports all the versioning features, including using e-tags for version integrity in the API.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRResourceVersionPolicy
     */
    public $versioning = null;

    /**
     * A flag for whether the server is able to return past versions as part of the vRead operation.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $readHistory = null;

    /**
     * A flag to indicate that the server allows or needs to allow the client to create new identities on the server (that is, the client PUTs to a location where there is no existing resource). Allowing this operation means that the server allows the client to create new identities on the server.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $updateCreate = null;

    /**
     * A flag that indicates that the server supports conditional create.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $conditionalCreate = null;

    /**
     * A code that indicates how the server supports conditional read.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRConditionalReadStatus
     */
    public $conditionalRead = null;

    /**
     * A flag that indicates that the server supports conditional update.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $conditionalUpdate = null;

    /**
     * A code that indicates how the server supports conditional delete.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRConditionalDeleteStatus
     */
    public $conditionalDelete = null;

    /**
     * A set of flags that defines how references are supported.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReferenceHandlingPolicy[]
     */
    public $referencePolicy = [];

    /**
     * A list of _include values supported by the server.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public $searchInclude = [];

    /**
     * A list of _revinclude (reverse include) values supported by the server.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public $searchRevInclude = [];

    /**
     * Search parameters for implementations to support and/or make use of - either references to ones defined in the specification, or additional ones defined for/by the implementation.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementSearchParam[]
     */
    public $searchParam = [];

    /**
     * Definition of an operation or a named query together with its parameters and their meaning and type. Consult the definition of the operation for details about how to invoke the operation, and the parameters.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementOperation[]
     */
    public $operation = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'CapabilityStatement.Resource';

    /**
     * A type of resource exposed via the restful interface.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCode
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * A type of resource exposed via the restful interface.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCode $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * A specification of the profile that describes the solution's overall support for the resource, including any constraints on cardinality, bindings, lengths or other limitations. See further discussion in [Using Profiles](profiling.html#profile-uses).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * A specification of the profile that describes the solution's overall support for the resource, including any constraints on cardinality, bindings, lengths or other limitations. See further discussion in [Using Profiles](profiling.html#profile-uses).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical $profile
     * @return $this
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;
        return $this;
    }

    /**
     * A list of profiles that represent different use cases supported by the system. For a server, "supported by the system" means the system hosts/produces a set of resources that are conformant to a particular profile, and allows clients that use its services to search using this profile and to find appropriate data. For a client, it means the system will search by this profile and process data according to the guidance implicit in the profile. See further discussion in [Using Profiles](profiling.html#profile-uses).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical[]
     */
    public function getSupportedProfile()
    {
        return $this->supportedProfile;
    }

    /**
     * A list of profiles that represent different use cases supported by the system. For a server, "supported by the system" means the system hosts/produces a set of resources that are conformant to a particular profile, and allows clients that use its services to search using this profile and to find appropriate data. For a client, it means the system will search by this profile and process data according to the guidance implicit in the profile. See further discussion in [Using Profiles](profiling.html#profile-uses).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical $supportedProfile
     * @return $this
     */
    public function addSupportedProfile($supportedProfile)
    {
        $this->supportedProfile[] = $supportedProfile;
        return $this;
    }

    /**
     * Additional information about the resource type used by the system.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public function getDocumentation()
    {
        return $this->documentation;
    }

    /**
     * Additional information about the resource type used by the system.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown $documentation
     * @return $this
     */
    public function setDocumentation($documentation)
    {
        $this->documentation = $documentation;
        return $this;
    }

    /**
     * Identifies a restful operation supported by the solution.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementInteraction[]
     */
    public function getInteraction()
    {
        return $this->interaction;
    }

    /**
     * Identifies a restful operation supported by the solution.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementInteraction $interaction
     * @return $this
     */
    public function addInteraction($interaction)
    {
        $this->interaction[] = $interaction;
        return $this;
    }

    /**
     * This field is set to no-version to specify that the system does not support (server) or use (client) versioning for this resource type. If this has some other value, the server must at least correctly track and populate the versionId meta-property on resources. If the value is 'versioned-update', then the server supports all the versioning features, including using e-tags for version integrity in the API.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRResourceVersionPolicy
     */
    public function getVersioning()
    {
        return $this->versioning;
    }

    /**
     * This field is set to no-version to specify that the system does not support (server) or use (client) versioning for this resource type. If this has some other value, the server must at least correctly track and populate the versionId meta-property on resources. If the value is 'versioned-update', then the server supports all the versioning features, including using e-tags for version integrity in the API.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRResourceVersionPolicy $versioning
     * @return $this
     */
    public function setVersioning($versioning)
    {
        $this->versioning = $versioning;
        return $this;
    }

    /**
     * A flag for whether the server is able to return past versions as part of the vRead operation.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getReadHistory()
    {
        return $this->readHistory;
    }

    /**
     * A flag for whether the server is able to return past versions as part of the vRead operation.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $readHistory
     * @return $this
     */
    public function setReadHistory($readHistory)
    {
        $this->readHistory = $readHistory;
        return $this;
    }

    /**
     * A flag to indicate that the server allows or needs to allow the client to create new identities on the server (that is, the client PUTs to a location where there is no existing resource). Allowing this operation means that the server allows the client to create new identities on the server.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getUpdateCreate()
    {
        return $this->updateCreate;
    }

    /**
     * A flag to indicate that the server allows or needs to allow the client to create new identities on the server (that is, the client PUTs to a location where there is no existing resource). Allowing this operation means that the server allows the client to create new identities on the server.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $updateCreate
     * @return $this
     */
    public function setUpdateCreate($updateCreate)
    {
        $this->updateCreate = $updateCreate;
        return $this;
    }

    /**
     * A flag that indicates that the server supports conditional create.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getConditionalCreate()
    {
        return $this->conditionalCreate;
    }

    /**
     * A flag that indicates that the server supports conditional create.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $conditionalCreate
     * @return $this
     */
    public function setConditionalCreate($conditionalCreate)
    {
        $this->conditionalCreate = $conditionalCreate;
        return $this;
    }

    /**
     * A code that indicates how the server supports conditional read.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRConditionalReadStatus
     */
    public function getConditionalRead()
    {
        return $this->conditionalRead;
    }

    /**
     * A code that indicates how the server supports conditional read.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRConditionalReadStatus $conditionalRead
     * @return $this
     */
    public function setConditionalRead($conditionalRead)
    {
        $this->conditionalRead = $conditionalRead;
        return $this;
    }

    /**
     * A flag that indicates that the server supports conditional update.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getConditionalUpdate()
    {
        return $this->conditionalUpdate;
    }

    /**
     * A flag that indicates that the server supports conditional update.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $conditionalUpdate
     * @return $this
     */
    public function setConditionalUpdate($conditionalUpdate)
    {
        $this->conditionalUpdate = $conditionalUpdate;
        return $this;
    }

    /**
     * A code that indicates how the server supports conditional delete.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRConditionalDeleteStatus
     */
    public function getConditionalDelete()
    {
        return $this->conditionalDelete;
    }

    /**
     * A code that indicates how the server supports conditional delete.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRConditionalDeleteStatus $conditionalDelete
     * @return $this
     */
    public function setConditionalDelete($conditionalDelete)
    {
        $this->conditionalDelete = $conditionalDelete;
        return $this;
    }

    /**
     * A set of flags that defines how references are supported.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReferenceHandlingPolicy[]
     */
    public function getReferencePolicy()
    {
        return $this->referencePolicy;
    }

    /**
     * A set of flags that defines how references are supported.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReferenceHandlingPolicy $referencePolicy
     * @return $this
     */
    public function addReferencePolicy($referencePolicy)
    {
        $this->referencePolicy[] = $referencePolicy;
        return $this;
    }

    /**
     * A list of _include values supported by the server.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public function getSearchInclude()
    {
        return $this->searchInclude;
    }

    /**
     * A list of _include values supported by the server.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $searchInclude
     * @return $this
     */
    public function addSearchInclude($searchInclude)
    {
        $this->searchInclude[] = $searchInclude;
        return $this;
    }

    /**
     * A list of _revinclude (reverse include) values supported by the server.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public function getSearchRevInclude()
    {
        return $this->searchRevInclude;
    }

    /**
     * A list of _revinclude (reverse include) values supported by the server.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $searchRevInclude
     * @return $this
     */
    public function addSearchRevInclude($searchRevInclude)
    {
        $this->searchRevInclude[] = $searchRevInclude;
        return $this;
    }

    /**
     * Search parameters for implementations to support and/or make use of - either references to ones defined in the specification, or additional ones defined for/by the implementation.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementSearchParam[]
     */
    public function getSearchParam()
    {
        return $this->searchParam;
    }

    /**
     * Search parameters for implementations to support and/or make use of - either references to ones defined in the specification, or additional ones defined for/by the implementation.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementSearchParam $searchParam
     * @return $this
     */
    public function addSearchParam($searchParam)
    {
        $this->searchParam[] = $searchParam;
        return $this;
    }

    /**
     * Definition of an operation or a named query together with its parameters and their meaning and type. Consult the definition of the operation for details about how to invoke the operation, and the parameters.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementOperation[]
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * Definition of an operation or a named query together with its parameters and their meaning and type. Consult the definition of the operation for details about how to invoke the operation, and the parameters.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementOperation $operation
     * @return $this
     */
    public function addOperation($operation)
    {
        $this->operation[] = $operation;
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
            if (isset($data['supportedProfile'])) {
                if (is_array($data['supportedProfile'])) {
                    foreach ($data['supportedProfile'] as $d) {
                        $this->addSupportedProfile($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"supportedProfile" must be array of objects or null, ' . gettype($data['supportedProfile']) . ' seen.');
                }
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
                    throw new \InvalidArgumentException('"interaction" must be array of objects or null, ' . gettype($data['interaction']) . ' seen.');
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
                    throw new \InvalidArgumentException('"referencePolicy" must be array of objects or null, ' . gettype($data['referencePolicy']) . ' seen.');
                }
            }
            if (isset($data['searchInclude'])) {
                if (is_array($data['searchInclude'])) {
                    foreach ($data['searchInclude'] as $d) {
                        $this->addSearchInclude($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"searchInclude" must be array of objects or null, ' . gettype($data['searchInclude']) . ' seen.');
                }
            }
            if (isset($data['searchRevInclude'])) {
                if (is_array($data['searchRevInclude'])) {
                    foreach ($data['searchRevInclude'] as $d) {
                        $this->addSearchRevInclude($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"searchRevInclude" must be array of objects or null, ' . gettype($data['searchRevInclude']) . ' seen.');
                }
            }
            if (isset($data['searchParam'])) {
                if (is_array($data['searchParam'])) {
                    foreach ($data['searchParam'] as $d) {
                        $this->addSearchParam($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"searchParam" must be array of objects or null, ' . gettype($data['searchParam']) . ' seen.');
                }
            }
            if (isset($data['operation'])) {
                if (is_array($data['operation'])) {
                    foreach ($data['operation'] as $d) {
                        $this->addOperation($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"operation" must be array of objects or null, ' . gettype($data['operation']) . ' seen.');
                }
            }
        } elseif (null !== $data) {
            throw new \InvalidArgumentException('$data expected to be array of values, saw "' . gettype($data) . '"');
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
    public function jsonSerialize(): mixed
    {
        $json = parent::jsonSerialize();
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->profile)) {
            $json['profile'] = $this->profile;
        }
        if (0 < count($this->supportedProfile)) {
            $json['supportedProfile'] = [];
            foreach ($this->supportedProfile as $supportedProfile) {
                $json['supportedProfile'][] = $supportedProfile;
            }
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
        if (0 < count($this->operation)) {
            $json['operation'] = [];
            foreach ($this->operation as $operation) {
                $json['operation'][] = $operation;
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
        if (0 < count($this->supportedProfile)) {
            foreach ($this->supportedProfile as $supportedProfile) {
                $supportedProfile->xmlSerialize(true, $sxe->addChild('supportedProfile'));
            }
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
        if (0 < count($this->operation)) {
            foreach ($this->operation as $operation) {
                $operation->xmlSerialize(true, $sxe->addChild('operation'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
