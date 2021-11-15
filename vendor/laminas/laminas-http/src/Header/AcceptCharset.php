<?php

/**
 * @see       https://github.com/laminas/laminas-http for the canonical source repository
 * @copyright https://github.com/laminas/laminas-http/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-http/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Http\Header;

use Laminas\Http\Header\Accept\FieldValuePart;

/**
 * Accept Charset Header
 *
 * @see        http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.2
 */
class AcceptCharset extends AbstractAccept
{
    protected $regexAddType = '#^([a-zA-Z0-9+-]+|\*)$#';

    /**
     * Get field name
     *
     * @return string
     */
    public function getFieldName()
    {
        return 'Accept-Charset';
    }

    /**
     * Cast to string
     *
     * @return string
     */
    public function toString()
    {
        return 'Accept-Charset: ' . $this->getFieldValue();
    }

    /**
     * Add a charset, with the given priority
     *
     * @param  string $type
     * @param  int|float $priority
     * @return $this
     */
    public function addCharset($type, $priority = 1)
    {
        return $this->addType($type, $priority);
    }

    /**
     * Does the header have the requested charset?
     *
     * @param  string $type
     * @return bool
     */
    public function hasCharset($type)
    {
        return $this->hasType($type);
    }

    /**
     * Parse the keys contained in the header line
     *
     * @param string $fieldValuePart
     * @return \Laminas\Http\Header\Accept\FieldValuePart\CharsetFieldValuePart
     * @see \Laminas\Http\Header\AbstractAccept::parseFieldValuePart()
     */
    protected function parseFieldValuePart($fieldValuePart)
    {
        $internalValues = parent::parseFieldValuePart($fieldValuePart);

        return new FieldValuePart\CharsetFieldValuePart($internalValues);
    }
}
