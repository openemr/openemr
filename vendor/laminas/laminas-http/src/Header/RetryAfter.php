<?php

/**
 * @see       https://github.com/laminas/laminas-http for the canonical source repository
 * @copyright https://github.com/laminas/laminas-http/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-http/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Http\Header;

/**
 * Retry-After HTTP Header
 *
 * @link       http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.37
 */
class RetryAfter extends AbstractDate
{
    /**
     * Value of header in delta-seconds
     * By default set to 1 hour
     *
     * @var int
     */
    protected $deltaSeconds = 3600;

    /**
     * Create Retry-After header from string
     *
     * @param  string $headerLine
     * @return static
     * @throws Exception\InvalidArgumentException
     */
    public static function fromString($headerLine)
    {
        $dateHeader = new static();

        list($name, $date) = GenericHeader::splitHeaderLine($headerLine);

        // check to ensure proper header type for this factory
        if (strtolower($name) !== strtolower($dateHeader->getFieldName())) {
            throw new Exception\InvalidArgumentException(
                'Invalid header line for "' . $dateHeader->getFieldName() . '" header string'
            );
        }

        if (is_numeric($date)) {
            $dateHeader->setDeltaSeconds($date);
        } else {
            $dateHeader->setDate($date);
        }

        return $dateHeader;
    }

    /**
     * Set number of seconds
     *
     * @param int $delta
     * @return $this
     */
    public function setDeltaSeconds($delta)
    {
        $this->deltaSeconds = (int) $delta;
        return $this;
    }

    /**
     * Get number of seconds
     *
     * @return int
     */
    public function getDeltaSeconds()
    {
        return $this->deltaSeconds;
    }

    /**
     * Get header name
     *
     * @return string
     */
    public function getFieldName()
    {
        return 'Retry-After';
    }

    /**
     * Returns date if it's set, or number of seconds
     *
     * @return int|string
     */
    public function getFieldValue()
    {
        return ($this->date === null) ? $this->deltaSeconds : $this->getDate();
    }

    /**
     * Return header line
     *
     * @return string
     */
    public function toString()
    {
        return 'Retry-After: ' . $this->getFieldValue();
    }
}
