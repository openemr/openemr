<?php

namespace Aranyasen\HL7\Segments;

use Aranyasen\HL7\Segment;

/**
 * MSA: Message acknowledgement segment
 * Ref: http://hl7-definition.caristix.com:9010/HL7%20v2.3/segment/MSA
 */
class MSA extends Segment
{
    public function __construct(array $fields = null)
    {
        parent::__construct('MSA', $fields);
    }

    public function setAcknowledgementCode($value, int $position = 1)
    {
        return $this->setField($position, $value);
    }

    public function setMessageControlID($value, int $position = 2)
    {
        return $this->setField($position, $value);
    }

    public function setTextMessage($value, int $position = 3)
    {
        return $this->setField($position, $value);
    }

    public function setExpectedSequenceNumber($value, int $position = 4)
    {
        return $this->setField($position, $value);
    }

    public function setDelayedAcknowledgementType($value, int $position = 5)
    {
        return $this->setField($position, $value);
    }

    public function setErrorCondition($value, int $position = 6)
    {
        return $this->setField($position, $value);
    }

    // -------------------- Getter Methods ------------------------------

    public function getAcknowledgementCode(int $position = 1)
    {
        return $this->getField($position);
    }

    public function getMessageControlID(int $position = 2)
    {
        return $this->getField($position);
    }

    public function getTextMessage(int $position = 3)
    {
        return $this->getField($position);
    }

    public function getExpectedSequenceNumber(int $position = 4)
    {
        return $this->getField($position);
    }

    public function getDelayedAcknowledgementType(int $position = 5)
    {
        return $this->getField($position);
    }

    public function getErrorCondition(int $position = 6)
    {
        return $this->getField($position);
    }
}
