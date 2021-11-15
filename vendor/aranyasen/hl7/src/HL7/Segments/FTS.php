<?php

namespace Aranyasen\HL7\Segments;

use Aranyasen\HL7\Segment;

/**
 * FTS: File Trailer Segment
 * Ref: https://hl7-definition.caristix.com/v2/HL7v2.3/Segments/FTS
 */
class FTS extends Segment
{
    public function __construct(array $fields = null)
    {
        parent::__construct('FTS', $fields);
    }

    public function setFileBatchCount($value, int $position = 1)
    {
        return $this->setField($position, $value);
    }

    public function setFileTrailerComment($value, int $position = 2)
    {
        return $this->setField($position, $value);
    }

    // -------------------- Getter Methods ------------------------------

    public function getFileBatchCount(int $position = 1)
    {
        return $this->getField($position);
    }

    public function getFileTrailerComment(int $position = 2)
    {
        return $this->getField($position);
    }
    
}
