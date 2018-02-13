<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRDevice;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * This resource identifies an instance or a type of a manufactured item that is used in the provision of healthcare without being substantially changed through that activity. The device may be a medical or non-medical device.  Medical devices include durable (reusable) medical equipment, implantable devices, as well as disposable equipment used for diagnostic, treatment, and research for healthcare and public health.  Non-medical devices may include items such as a machine, cellphone, computer, application, etc.
 */
class FHIRDeviceUdi extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The device identifier (DI) is a mandatory, fixed portion of a UDI that identifies the labeler and the specific version or model of a device.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $deviceIdentifier = null;

    /**
     * Name of device as used in labeling or catalog.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $name = null;

    /**
     * The identity of the authoritative source for UDI generation within a  jurisdiction.  All UDIs are globally unique within a single namespace. with the appropriate repository uri as the system.  For example,  UDIs of devices managed in the U.S. by the FDA, the value is  http://hl7.org/fhir/NamingSystem/fda-udi.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public $jurisdiction = null;

    /**
     * The full UDI carrier as the human readable form (HRF) representation of the barcode string as printed on the packaging of the device.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $carrierHRF = null;

    /**
     * The full UDI carrier of the Automatic Identification and Data Capture (AIDC) technology representation of the barcode string as printed on the packaging of the device - E.g a barcode or RFID.   Because of limitations on character sets in XML and the need to round-trip JSON data through XML, AIDC Formats *SHALL* be base64 encoded.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRBase64Binary
     */
    public $carrierAIDC = null;

    /**
     * Organization that is charged with issuing UDIs for devices.  For example, the US FDA issuers include :
1) GS1:
http://hl7.org/fhir/NamingSystem/gs1-di,
2) HIBCC:
http://hl7.org/fhir/NamingSystem/hibcc-dI,
3) ICCBBA for blood containers:
http://hl7.org/fhir/NamingSystem/iccbba-blood-di,
4) ICCBA for other devices:
http://hl7.org/fhir/NamingSystem/iccbba-other-di.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public $issuer = null;

    /**
     * A coded entry to indicate how the data was entered.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUDIEntryType
     */
    public $entryType = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Device.Udi';

    /**
     * The device identifier (DI) is a mandatory, fixed portion of a UDI that identifies the labeler and the specific version or model of a device.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getDeviceIdentifier()
    {
        return $this->deviceIdentifier;
    }

    /**
     * The device identifier (DI) is a mandatory, fixed portion of a UDI that identifies the labeler and the specific version or model of a device.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $deviceIdentifier
     * @return $this
     */
    public function setDeviceIdentifier($deviceIdentifier)
    {
        $this->deviceIdentifier = $deviceIdentifier;
        return $this;
    }

    /**
     * Name of device as used in labeling or catalog.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Name of device as used in labeling or catalog.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * The identity of the authoritative source for UDI generation within a  jurisdiction.  All UDIs are globally unique within a single namespace. with the appropriate repository uri as the system.  For example,  UDIs of devices managed in the U.S. by the FDA, the value is  http://hl7.org/fhir/NamingSystem/fda-udi.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public function getJurisdiction()
    {
        return $this->jurisdiction;
    }

    /**
     * The identity of the authoritative source for UDI generation within a  jurisdiction.  All UDIs are globally unique within a single namespace. with the appropriate repository uri as the system.  For example,  UDIs of devices managed in the U.S. by the FDA, the value is  http://hl7.org/fhir/NamingSystem/fda-udi.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUri $jurisdiction
     * @return $this
     */
    public function setJurisdiction($jurisdiction)
    {
        $this->jurisdiction = $jurisdiction;
        return $this;
    }

    /**
     * The full UDI carrier as the human readable form (HRF) representation of the barcode string as printed on the packaging of the device.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getCarrierHRF()
    {
        return $this->carrierHRF;
    }

    /**
     * The full UDI carrier as the human readable form (HRF) representation of the barcode string as printed on the packaging of the device.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $carrierHRF
     * @return $this
     */
    public function setCarrierHRF($carrierHRF)
    {
        $this->carrierHRF = $carrierHRF;
        return $this;
    }

    /**
     * The full UDI carrier of the Automatic Identification and Data Capture (AIDC) technology representation of the barcode string as printed on the packaging of the device - E.g a barcode or RFID.   Because of limitations on character sets in XML and the need to round-trip JSON data through XML, AIDC Formats *SHALL* be base64 encoded.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRBase64Binary
     */
    public function getCarrierAIDC()
    {
        return $this->carrierAIDC;
    }

    /**
     * The full UDI carrier of the Automatic Identification and Data Capture (AIDC) technology representation of the barcode string as printed on the packaging of the device - E.g a barcode or RFID.   Because of limitations on character sets in XML and the need to round-trip JSON data through XML, AIDC Formats *SHALL* be base64 encoded.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRBase64Binary $carrierAIDC
     * @return $this
     */
    public function setCarrierAIDC($carrierAIDC)
    {
        $this->carrierAIDC = $carrierAIDC;
        return $this;
    }

    /**
     * Organization that is charged with issuing UDIs for devices.  For example, the US FDA issuers include :
1) GS1:
http://hl7.org/fhir/NamingSystem/gs1-di,
2) HIBCC:
http://hl7.org/fhir/NamingSystem/hibcc-dI,
3) ICCBBA for blood containers:
http://hl7.org/fhir/NamingSystem/iccbba-blood-di,
4) ICCBA for other devices:
http://hl7.org/fhir/NamingSystem/iccbba-other-di.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public function getIssuer()
    {
        return $this->issuer;
    }

    /**
     * Organization that is charged with issuing UDIs for devices.  For example, the US FDA issuers include :
1) GS1:
http://hl7.org/fhir/NamingSystem/gs1-di,
2) HIBCC:
http://hl7.org/fhir/NamingSystem/hibcc-dI,
3) ICCBBA for blood containers:
http://hl7.org/fhir/NamingSystem/iccbba-blood-di,
4) ICCBA for other devices:
http://hl7.org/fhir/NamingSystem/iccbba-other-di.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUri $issuer
     * @return $this
     */
    public function setIssuer($issuer)
    {
        $this->issuer = $issuer;
        return $this;
    }

    /**
     * A coded entry to indicate how the data was entered.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUDIEntryType
     */
    public function getEntryType()
    {
        return $this->entryType;
    }

    /**
     * A coded entry to indicate how the data was entered.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUDIEntryType $entryType
     * @return $this
     */
    public function setEntryType($entryType)
    {
        $this->entryType = $entryType;
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
            if (isset($data['deviceIdentifier'])) {
                $this->setDeviceIdentifier($data['deviceIdentifier']);
            }
            if (isset($data['name'])) {
                $this->setName($data['name']);
            }
            if (isset($data['jurisdiction'])) {
                $this->setJurisdiction($data['jurisdiction']);
            }
            if (isset($data['carrierHRF'])) {
                $this->setCarrierHRF($data['carrierHRF']);
            }
            if (isset($data['carrierAIDC'])) {
                $this->setCarrierAIDC($data['carrierAIDC']);
            }
            if (isset($data['issuer'])) {
                $this->setIssuer($data['issuer']);
            }
            if (isset($data['entryType'])) {
                $this->setEntryType($data['entryType']);
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
        if (isset($this->deviceIdentifier)) {
            $json['deviceIdentifier'] = $this->deviceIdentifier;
        }
        if (isset($this->name)) {
            $json['name'] = $this->name;
        }
        if (isset($this->jurisdiction)) {
            $json['jurisdiction'] = $this->jurisdiction;
        }
        if (isset($this->carrierHRF)) {
            $json['carrierHRF'] = $this->carrierHRF;
        }
        if (isset($this->carrierAIDC)) {
            $json['carrierAIDC'] = $this->carrierAIDC;
        }
        if (isset($this->issuer)) {
            $json['issuer'] = $this->issuer;
        }
        if (isset($this->entryType)) {
            $json['entryType'] = $this->entryType;
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
            $sxe = new \SimpleXMLElement('<DeviceUdi xmlns="http://hl7.org/fhir"></DeviceUdi>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->deviceIdentifier)) {
            $this->deviceIdentifier->xmlSerialize(true, $sxe->addChild('deviceIdentifier'));
        }
        if (isset($this->name)) {
            $this->name->xmlSerialize(true, $sxe->addChild('name'));
        }
        if (isset($this->jurisdiction)) {
            $this->jurisdiction->xmlSerialize(true, $sxe->addChild('jurisdiction'));
        }
        if (isset($this->carrierHRF)) {
            $this->carrierHRF->xmlSerialize(true, $sxe->addChild('carrierHRF'));
        }
        if (isset($this->carrierAIDC)) {
            $this->carrierAIDC->xmlSerialize(true, $sxe->addChild('carrierAIDC'));
        }
        if (isset($this->issuer)) {
            $this->issuer->xmlSerialize(true, $sxe->addChild('issuer'));
        }
        if (isset($this->entryType)) {
            $this->entryType->xmlSerialize(true, $sxe->addChild('entryType'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
