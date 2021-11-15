<?php

namespace Aranyasen\HL7\Segments;

use Aranyasen\HL7\Segment;

/**
 * FHS: File Header Segment
 * Ref: https://hl7-definition.caristix.com/v2/HL7v2.3/Segments/FHS
 */
class FHS extends Segment
{
    public function __construct(array $fields = null)
    {
        parent::__construct('FHS', $fields);
    }

    public function setFileFieldSeparator($value, int $position = 1)
    {
        return $this->setField($position, $value);
    }

    public function setFileEncodingCharacters($value, int $position = 2)
    {
        return $this->setField($position, $value);
    }

    public function setFileSendingApplication($value, int $position = 3)
    {
        return $this->setField($position, $value);
    }

    public function setFileSendingFacility($value, int $position = 4)
    {
        return $this->setField($position, $value);
    }

    public function setFileRecievingApplication($value, int $position = 5)
    {
        return $this->setField($position, $value);
    }

    public function setFileRecievingFacility($value, int $position = 6)
    {
        return $this->setField($position, $value);
    }

    public function setFileCreationDateTime($value, int $position = 7)
    {
        return $this->setField($position, $value);
    }

    public function setFileSecurity($value, int $position = 8)
    {
        return $this->setField($position, $value);
    }

    public function setFileNameId($value, int $position = 9)
    {
        return $this->setField($position, $value);
    }

    public function setFileHeaderComment($value, int $position = 10)
    {
        return $this->setField($position, $value);
    }

    public function setFileControlId($value, int $position = 11)
    {
        return $this->setField($position, $value);
    }

    public function setReferenceFileControlId($value, int $position = 12)
    {
        return $this->setField($position, $value);
    }

    // -------------------- Getter Methods ------------------------------

    public function getFileFieldSeparator(int $position = 1)
    {
        return $this->getField($position);
    }

    public function getFileEncodingCharacters(int $position = 2)
    {
        return $this->getField($position);
    }

    public function getFileSendingApplication(int $position = 3)
    {
        return $this->getField($position);
    }

    public function getFileSendingFacility(int $position = 4)
    {
        return $this->getField($position);
    }

    public function getFileRecievingApplication(int $position = 5)
    {
        return $this->getField($position);
    }

    public function getFileRecievingFacility(int $position = 6)
    {
        return $this->getField($position);
    }

    public function getFileCreationDateTime(int $position = 7)
    {
        return $this->getField($position);
    }

    public function getFileSecurity(int $position = 8)
    {
        return $this->getField($position);
    }

    public function getFileNameId(int $position = 9)
    {
        return $this->getField($position);
    }

    public function getFileHeaderComment(int $position = 10)
    {
        return $this->getField($position);
    }

    public function getFileControlId(int $position = 11)
    {
        return $this->getField($position);
    }

    public function getReferenceFileControlId(int $position = 12)
    {
        return $this->getField($position);
    }
    
}
