<?php

/**
 * @see       https://github.com/laminas/laminas-http for the canonical source repository
 * @copyright https://github.com/laminas/laminas-http/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-http/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Http\Header;

/**
 * Interface for HTTP Header classes.
 */
interface HeaderInterface
{
    /**
     * Factory to generate a header object from a string
     *
     * @param string $headerLine
     * @return static
     * @throws Exception\InvalidArgumentException If the header does not match RFC 2616 definition.
     * @see http://tools.ietf.org/html/rfc2616#section-4.2
     */
    public static function fromString($headerLine);

    /**
     * Retrieve header name
     *
     * @return string
     */
    public function getFieldName();

    /**
     * Retrieve header value
     *
     * @return string
     */
    public function getFieldValue();

    /**
     * Cast to string
     *
     * Returns in form of "NAME: VALUE"
     *
     * @return string
     */
    public function toString();
}
