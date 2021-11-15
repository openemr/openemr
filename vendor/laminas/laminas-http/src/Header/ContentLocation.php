<?php

/**
 * @see       https://github.com/laminas/laminas-http for the canonical source repository
 * @copyright https://github.com/laminas/laminas-http/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-http/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Http\Header;

/**
 * Content-Location Header
 *
 * @link       http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.14
 */
class ContentLocation extends AbstractLocation
{
    /**
     * Return header name
     *
     * @return string
     */
    public function getFieldName()
    {
        return 'Content-Location';
    }
}
