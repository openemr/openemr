<?php namespace HL7\FHIR\STU3\FHIRDomainResource;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRResource\FHIRDomainResource;

/**
 * The technical details of an endpoint that can be used for electronic services, such as for web services providing XDS.b or a REST endpoint for another FHIR server. This may include any security context information.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIREndpoint extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * Identifier for the organization that is used to identify the endpoint across multiple disparate systems.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = [];

    /**
     * active | suspended | error | off | test.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIREndpointStatus
     */
    public $status = null;

    /**
     * A coded value that represents the technical details of the usage of this endpoint, such as what WSDLs should be used in what way. (e.g. XDS.b/DICOM/cds-hook).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCoding
     */
    public $connectionType = null;

    /**
     * A friendly name that this endpoint can be referred to with.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $name = null;

    /**
     * The organization that manages this endpoint (even if technically another organisation is hosting this in the cloud, it is the organisation associated with the data).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $managingOrganization = null;

    /**
     * Contact details for a human to contact about the subscription. The primary use of this for system administrator troubleshooting.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRContactPoint[]
     */
    public $contact = [];

    /**
     * The interval during which the endpoint is expected to be operational.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRPeriod
     */
    public $period = null;

    /**
     * The payload type describes the acceptable content that can be communicated on the endpoint.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public $payloadType = [];

    /**
     * The mime type to send the payload in - e.g. application/fhir+xml, application/fhir+json. If the mime type is not specified, then the sender could send any content (including no content depending on the connectionType).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCode[]
     */
    public $payloadMimeType = [];

    /**
     * The uri that describes the actual end-point to connect to.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public $address = null;

    /**
     * Additional headers / information to send as part of the notification.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString[]
     */
    public $header = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'Endpoint';

    /**
     * Identifier for the organization that is used to identify the endpoint across multiple disparate systems.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Identifier for the organization that is used to identify the endpoint across multiple disparate systems.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * active | suspended | error | off | test.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIREndpointStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * active | suspended | error | off | test.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIREndpointStatus $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * A coded value that represents the technical details of the usage of this endpoint, such as what WSDLs should be used in what way. (e.g. XDS.b/DICOM/cds-hook).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCoding
     */
    public function getConnectionType()
    {
        return $this->connectionType;
    }

    /**
     * A coded value that represents the technical details of the usage of this endpoint, such as what WSDLs should be used in what way. (e.g. XDS.b/DICOM/cds-hook).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCoding $connectionType
     * @return $this
     */
    public function setConnectionType($connectionType)
    {
        $this->connectionType = $connectionType;
        return $this;
    }

    /**
     * A friendly name that this endpoint can be referred to with.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * A friendly name that this endpoint can be referred to with.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * The organization that manages this endpoint (even if technically another organisation is hosting this in the cloud, it is the organisation associated with the data).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getManagingOrganization()
    {
        return $this->managingOrganization;
    }

    /**
     * The organization that manages this endpoint (even if technically another organisation is hosting this in the cloud, it is the organisation associated with the data).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $managingOrganization
     * @return $this
     */
    public function setManagingOrganization($managingOrganization)
    {
        $this->managingOrganization = $managingOrganization;
        return $this;
    }

    /**
     * Contact details for a human to contact about the subscription. The primary use of this for system administrator troubleshooting.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRContactPoint[]
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Contact details for a human to contact about the subscription. The primary use of this for system administrator troubleshooting.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRContactPoint $contact
     * @return $this
     */
    public function addContact($contact)
    {
        $this->contact[] = $contact;
        return $this;
    }

    /**
     * The interval during which the endpoint is expected to be operational.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRPeriod
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * The interval during which the endpoint is expected to be operational.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRPeriod $period
     * @return $this
     */
    public function setPeriod($period)
    {
        $this->period = $period;
        return $this;
    }

    /**
     * The payload type describes the acceptable content that can be communicated on the endpoint.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public function getPayloadType()
    {
        return $this->payloadType;
    }

    /**
     * The payload type describes the acceptable content that can be communicated on the endpoint.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $payloadType
     * @return $this
     */
    public function addPayloadType($payloadType)
    {
        $this->payloadType[] = $payloadType;
        return $this;
    }

    /**
     * The mime type to send the payload in - e.g. application/fhir+xml, application/fhir+json. If the mime type is not specified, then the sender could send any content (including no content depending on the connectionType).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCode[]
     */
    public function getPayloadMimeType()
    {
        return $this->payloadMimeType;
    }

    /**
     * The mime type to send the payload in - e.g. application/fhir+xml, application/fhir+json. If the mime type is not specified, then the sender could send any content (including no content depending on the connectionType).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCode $payloadMimeType
     * @return $this
     */
    public function addPayloadMimeType($payloadMimeType)
    {
        $this->payloadMimeType[] = $payloadMimeType;
        return $this;
    }

    /**
     * The uri that describes the actual end-point to connect to.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * The uri that describes the actual end-point to connect to.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUri $address
     * @return $this
     */
    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    /**
     * Additional headers / information to send as part of the notification.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString[]
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * Additional headers / information to send as part of the notification.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $header
     * @return $this
     */
    public function addHeader($header)
    {
        $this->header[] = $header;
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
            if (isset($data['identifier'])) {
                if (is_array($data['identifier'])) {
                    foreach ($data['identifier'] as $d) {
                        $this->addIdentifier($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"identifier" must be array of objects or null, '.gettype($data['identifier']).' seen.');
                }
            }
            if (isset($data['status'])) {
                $this->setStatus($data['status']);
            }
            if (isset($data['connectionType'])) {
                $this->setConnectionType($data['connectionType']);
            }
            if (isset($data['name'])) {
                $this->setName($data['name']);
            }
            if (isset($data['managingOrganization'])) {
                $this->setManagingOrganization($data['managingOrganization']);
            }
            if (isset($data['contact'])) {
                if (is_array($data['contact'])) {
                    foreach ($data['contact'] as $d) {
                        $this->addContact($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"contact" must be array of objects or null, '.gettype($data['contact']).' seen.');
                }
            }
            if (isset($data['period'])) {
                $this->setPeriod($data['period']);
            }
            if (isset($data['payloadType'])) {
                if (is_array($data['payloadType'])) {
                    foreach ($data['payloadType'] as $d) {
                        $this->addPayloadType($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"payloadType" must be array of objects or null, '.gettype($data['payloadType']).' seen.');
                }
            }
            if (isset($data['payloadMimeType'])) {
                if (is_array($data['payloadMimeType'])) {
                    foreach ($data['payloadMimeType'] as $d) {
                        $this->addPayloadMimeType($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"payloadMimeType" must be array of objects or null, '.gettype($data['payloadMimeType']).' seen.');
                }
            }
            if (isset($data['address'])) {
                $this->setAddress($data['address']);
            }
            if (isset($data['header'])) {
                if (is_array($data['header'])) {
                    foreach ($data['header'] as $d) {
                        $this->addHeader($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"header" must be array of objects or null, '.gettype($data['header']).' seen.');
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
        $json['resourceType'] = $this->_fhirElementName;
        if (0 < count($this->identifier)) {
            $json['identifier'] = [];
            foreach ($this->identifier as $identifier) {
                $json['identifier'][] = $identifier;
            }
        }
        if (isset($this->status)) {
            $json['status'] = $this->status;
        }
        if (isset($this->connectionType)) {
            $json['connectionType'] = $this->connectionType;
        }
        if (isset($this->name)) {
            $json['name'] = $this->name;
        }
        if (isset($this->managingOrganization)) {
            $json['managingOrganization'] = $this->managingOrganization;
        }
        if (0 < count($this->contact)) {
            $json['contact'] = [];
            foreach ($this->contact as $contact) {
                $json['contact'][] = $contact;
            }
        }
        if (isset($this->period)) {
            $json['period'] = $this->period;
        }
        if (0 < count($this->payloadType)) {
            $json['payloadType'] = [];
            foreach ($this->payloadType as $payloadType) {
                $json['payloadType'][] = $payloadType;
            }
        }
        if (0 < count($this->payloadMimeType)) {
            $json['payloadMimeType'] = [];
            foreach ($this->payloadMimeType as $payloadMimeType) {
                $json['payloadMimeType'][] = $payloadMimeType;
            }
        }
        if (isset($this->address)) {
            $json['address'] = $this->address;
        }
        if (0 < count($this->header)) {
            $json['header'] = [];
            foreach ($this->header as $header) {
                $json['header'][] = $header;
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
            $sxe = new \SimpleXMLElement('<Endpoint xmlns="http://hl7.org/fhir"></Endpoint>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->identifier)) {
            foreach ($this->identifier as $identifier) {
                $identifier->xmlSerialize(true, $sxe->addChild('identifier'));
            }
        }
        if (isset($this->status)) {
            $this->status->xmlSerialize(true, $sxe->addChild('status'));
        }
        if (isset($this->connectionType)) {
            $this->connectionType->xmlSerialize(true, $sxe->addChild('connectionType'));
        }
        if (isset($this->name)) {
            $this->name->xmlSerialize(true, $sxe->addChild('name'));
        }
        if (isset($this->managingOrganization)) {
            $this->managingOrganization->xmlSerialize(true, $sxe->addChild('managingOrganization'));
        }
        if (0 < count($this->contact)) {
            foreach ($this->contact as $contact) {
                $contact->xmlSerialize(true, $sxe->addChild('contact'));
            }
        }
        if (isset($this->period)) {
            $this->period->xmlSerialize(true, $sxe->addChild('period'));
        }
        if (0 < count($this->payloadType)) {
            foreach ($this->payloadType as $payloadType) {
                $payloadType->xmlSerialize(true, $sxe->addChild('payloadType'));
            }
        }
        if (0 < count($this->payloadMimeType)) {
            foreach ($this->payloadMimeType as $payloadMimeType) {
                $payloadMimeType->xmlSerialize(true, $sxe->addChild('payloadMimeType'));
            }
        }
        if (isset($this->address)) {
            $this->address->xmlSerialize(true, $sxe->addChild('address'));
        }
        if (0 < count($this->header)) {
            foreach ($this->header as $header) {
                $header->xmlSerialize(true, $sxe->addChild('header'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
