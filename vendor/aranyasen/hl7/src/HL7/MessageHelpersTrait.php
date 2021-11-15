<?php

namespace Aranyasen\HL7;

use Aranyasen\Exceptions\HL7Exception;
use Aranyasen\HL7\Segments\MSH;

trait MessageHelpersTrait
{
    /**
     * Get the segment identified by index as string, using the messages separators.
     *
     * @param int $index Index for segment to get
     * @return string|null String representation of segment
     */
    public function getSegmentAsString(int $index): ?string
    {
        $seg = $this->getSegmentByIndex($index);

        if ($seg === null) {
            return null;
        }

        return $this->segmentToString($seg);
    }

    /**
     * Get the field identified by $fieldIndex from segment $segmentIndex.
     *
     * Returns empty string if field is not set.
     *
     * @param int $segmentIndex Index for segment to get
     * @param int $fieldIndex Index for field to get
     * @return mixed String representation of field
     * @access public
     */
    public function getSegmentFieldAsString(int $segmentIndex, int $fieldIndex)
    {
        $segment = $this->getSegmentByIndex($segmentIndex);

        if ($segment === null) {
            return null;
        }

        $field = $segment->getField($fieldIndex);

        if (!$field) {
            return null;
        }

        $fieldString = null;

        if (\is_array($field)) {
            foreach ($field as $i => $iValue) {
                \is_array($field[$i])
                    ? ($fieldString .= implode($this->subcomponentSeparator, $field[$i]))
                    : ($fieldString .= $field[$i]);

                if ($i < (\count($field) - 1)) {
                    $fieldString .= $this->componentSeparator;
                }
            }
        }
        else {
            $fieldString .= $field;
        }

        return $fieldString;
    }

    /**
     * Write HL7 to a file
     *
     * @param string $filename
     * @throws HL7Exception
     */
    public function toFile(string $filename): void
    {
        file_put_contents($filename, $this->toString(true));
        if (!file_exists($filename)) {
            throw new HL7Exception("Failed to write HL7 to file '$filename'");
        }
    }

    /**
     * Check if given message is an ORM
     *
     * @return bool
     */
    public function isOrm(): bool
    {
        /** @var MSH $msh */
        $msh = $this->getFirstSegmentInstance('MSH');
        return false !== strpos($msh->getMessageType(), 'ORM');
    }

    /**
     * Check if given message is an ORU
     *
     * @return bool
     */
    public function isOru(): bool
    {
        /** @var MSH $msh */
        $msh = $this->getFirstSegmentInstance('MSH');
        return false !== strpos($msh->getMessageType(), 'ORU');
    }

    /**
     * Check if given message is an ADT
     *
     * @return bool
     */
    public function isAdt(): bool
    {
        /** @var MSH $msh */
        $msh = $this->getFirstSegmentInstance('MSH');
        return false !== strpos($msh->getMessageType(), 'ADT');
    }

    /**
     * Check if given message is a SIU
     *
     * @return bool
     */
    public function isSiu(): bool
    {
        /** @var MSH $msh */
        $msh = $this->getFirstSegmentInstance('MSH');
        return false !== strpos($msh->getMessageType(), 'SIU');
    }

    /**
     * Check if given segment is present in the message object
     *
     * @param string $segment
     * @return bool
     */
    public function hasSegment(string $segment): bool
    {
        return count($this->getSegmentsByName(strtoupper($segment))) > 0;
    }

    /**
     * Return the first segment with given name in the message
     *
     * @param string $segment name of the segment to return
     * @return mixed|null
     */
    public function getFirstSegmentInstance(string $segment)
    {
        if (!$this->hasSegment($segment)) {
            return null;
        }
        return $this->getSegmentsByName($segment)[0];
    }

    /**
     * Remove a segment from the message
     *
     * @param Segment $segment
     * @param bool $reIndex After deleting, re-index remaining segments of same name
     */
    public function removeSegment(Segment $segment, bool $reIndex = false): void
    {
        if(($key = array_search($segment, $this->segments, true)) !== false) {
            unset($this->segments[$key]);
        }

        if (!$reIndex) {
            return;
        }

        $segments = $this->getSegmentsByName($segment->getName());
        $index = 1;
        /** @var Segment $seg */
        foreach ($segments as $seg) {
            $seg->setField(1, $index++);
        }
    }

    /**
     * Check if the message has any data
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->getSegments());
    }
}
