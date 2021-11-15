<?php

/**
 * @see       https://github.com/laminas/laminas-http for the canonical source repository
 * @copyright https://github.com/laminas/laminas-http/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-http/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Http\Header;

class GenericMultiHeader extends GenericHeader implements MultipleHeaderInterface
{
    public static function fromString($headerLine)
    {
        list($fieldName, $fieldValue) = GenericHeader::splitHeaderLine($headerLine);

        if (strpos($fieldValue, ',')) {
            $headers = [];
            foreach (explode(',', $fieldValue) as $multiValue) {
                $headers[] = new static($fieldName, $multiValue);
            }
            return $headers;
        } else {
            $header = new static($fieldName, $fieldValue);
            return $header;
        }
    }

    public function toStringMultipleHeaders(array $headers)
    {
        $name  = $this->getFieldName();
        $values = [$this->getFieldValue()];
        foreach ($headers as $header) {
            if (! $header instanceof static) {
                throw new Exception\InvalidArgumentException(
                    'This method toStringMultipleHeaders was expecting an array of headers of the same type'
                );
            }
            $values[] = $header->getFieldValue();
        }
        return $name . ': ' . implode(',', $values) . "\r\n";
    }
}
