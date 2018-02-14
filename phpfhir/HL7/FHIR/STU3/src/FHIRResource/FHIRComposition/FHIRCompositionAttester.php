<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRComposition;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A set of healthcare-related information that is assembled together into a single logical document that provides a single coherent statement of meaning, establishes its own context and that has clinical attestation with regard to who is making the statement. While a Composition defines the structure, it does not actually contain the content: rather the full content of a document is contained in a Bundle, of which the Composition is the first resource contained.
 */
class FHIRCompositionAttester extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The type of attestation the authenticator offers.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCompositionAttestationMode[]
     */
    public $mode = [];

    /**
     * When the composition was attested by the party.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDateTime
     */
    public $time = null;

    /**
     * Who attested the composition in the specified way.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $party = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Composition.Attester';

    /**
     * The type of attestation the authenticator offers.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCompositionAttestationMode[]
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * The type of attestation the authenticator offers.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCompositionAttestationMode $mode
     * @return $this
     */
    public function addMode($mode)
    {
        $this->mode[] = $mode;
        return $this;
    }

    /**
     * When the composition was attested by the party.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDateTime
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * When the composition was attested by the party.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDateTime $time
     * @return $this
     */
    public function setTime($time)
    {
        $this->time = $time;
        return $this;
    }

    /**
     * Who attested the composition in the specified way.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getParty()
    {
        return $this->party;
    }

    /**
     * Who attested the composition in the specified way.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $party
     * @return $this
     */
    public function setParty($party)
    {
        $this->party = $party;
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
            if (isset($data['mode'])) {
                if (is_array($data['mode'])) {
                    foreach ($data['mode'] as $d) {
                        $this->addMode($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"mode" must be array of objects or null, '.gettype($data['mode']).' seen.');
                }
            }
            if (isset($data['time'])) {
                $this->setTime($data['time']);
            }
            if (isset($data['party'])) {
                $this->setParty($data['party']);
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
        if (0 < count($this->mode)) {
            $json['mode'] = [];
            foreach ($this->mode as $mode) {
                $json['mode'][] = $mode;
            }
        }
        if (isset($this->time)) {
            $json['time'] = $this->time;
        }
        if (isset($this->party)) {
            $json['party'] = $this->party;
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
            $sxe = new \SimpleXMLElement('<CompositionAttester xmlns="http://hl7.org/fhir"></CompositionAttester>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->mode)) {
            foreach ($this->mode as $mode) {
                $mode->xmlSerialize(true, $sxe->addChild('mode'));
            }
        }
        if (isset($this->time)) {
            $this->time->xmlSerialize(true, $sxe->addChild('time'));
        }
        if (isset($this->party)) {
            $this->party->xmlSerialize(true, $sxe->addChild('party'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
