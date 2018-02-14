<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRTestScript;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A structured set of tests against a FHIR server implementation to determine compliance against the FHIR specification.
 */
class FHIRTestScriptFixture extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Whether or not to implicitly create the fixture during setup. If true, the fixture is automatically created on each server being tested during setup, therefore no create operation is required for this fixture in the TestScript.setup section.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public $autocreate = null;

    /**
     * Whether or not to implicitly delete the fixture during teardown. If true, the fixture is automatically deleted on each server being tested during teardown, therefore no delete operation is required for this fixture in the TestScript.teardown section.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public $autodelete = null;

    /**
     * Reference to the resource (containing the contents of the resource needed for operations).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $resource = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'TestScript.Fixture';

    /**
     * Whether or not to implicitly create the fixture during setup. If true, the fixture is automatically created on each server being tested during setup, therefore no create operation is required for this fixture in the TestScript.setup section.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public function getAutocreate()
    {
        return $this->autocreate;
    }

    /**
     * Whether or not to implicitly create the fixture during setup. If true, the fixture is automatically created on each server being tested during setup, therefore no create operation is required for this fixture in the TestScript.setup section.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRBoolean $autocreate
     * @return $this
     */
    public function setAutocreate($autocreate)
    {
        $this->autocreate = $autocreate;
        return $this;
    }

    /**
     * Whether or not to implicitly delete the fixture during teardown. If true, the fixture is automatically deleted on each server being tested during teardown, therefore no delete operation is required for this fixture in the TestScript.teardown section.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public function getAutodelete()
    {
        return $this->autodelete;
    }

    /**
     * Whether or not to implicitly delete the fixture during teardown. If true, the fixture is automatically deleted on each server being tested during teardown, therefore no delete operation is required for this fixture in the TestScript.teardown section.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRBoolean $autodelete
     * @return $this
     */
    public function setAutodelete($autodelete)
    {
        $this->autodelete = $autodelete;
        return $this;
    }

    /**
     * Reference to the resource (containing the contents of the resource needed for operations).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Reference to the resource (containing the contents of the resource needed for operations).
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
            if (isset($data['autocreate'])) {
                $this->setAutocreate($data['autocreate']);
            }
            if (isset($data['autodelete'])) {
                $this->setAutodelete($data['autodelete']);
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
        if (isset($this->autocreate)) {
            $json['autocreate'] = $this->autocreate;
        }
        if (isset($this->autodelete)) {
            $json['autodelete'] = $this->autodelete;
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
            $sxe = new \SimpleXMLElement('<TestScriptFixture xmlns="http://hl7.org/fhir"></TestScriptFixture>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->autocreate)) {
            $this->autocreate->xmlSerialize(true, $sxe->addChild('autocreate'));
        }
        if (isset($this->autodelete)) {
            $this->autodelete->xmlSerialize(true, $sxe->addChild('autodelete'));
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
