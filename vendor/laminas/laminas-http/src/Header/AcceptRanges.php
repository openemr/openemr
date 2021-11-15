<?php

/**
 * @see       https://github.com/laminas/laminas-http for the canonical source repository
 * @copyright https://github.com/laminas/laminas-http/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-http/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Http\Header;

/**
 * Accept Ranges Header
 *
 * @see        http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.5
 */
class AcceptRanges implements HeaderInterface
{
    protected $rangeUnit;

    public static function fromString($headerLine)
    {
        list($name, $value) = GenericHeader::splitHeaderLine($headerLine);

        // check to ensure proper header type for this factory
        if (strtolower($name) !== 'accept-ranges') {
            throw new Exception\InvalidArgumentException(
                'Invalid header line for Accept-Ranges string'
            );
        }

        return new static($value);
    }

    public function __construct($rangeUnit = null)
    {
        if ($rangeUnit !== null) {
            $this->setRangeUnit($rangeUnit);
        }
    }

    public function getFieldName()
    {
        return 'Accept-Ranges';
    }

    public function getFieldValue()
    {
        return $this->getRangeUnit();
    }

    public function setRangeUnit($rangeUnit)
    {
        HeaderValue::assertValid($rangeUnit);
        $this->rangeUnit = $rangeUnit;
        return $this;
    }

    public function getRangeUnit()
    {
        return (string) $this->rangeUnit;
    }

    public function toString()
    {
        return 'Accept-Ranges: ' . $this->getFieldValue();
    }
}
