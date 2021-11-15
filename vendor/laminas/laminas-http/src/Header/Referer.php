<?php

/**
 * @see       https://github.com/laminas/laminas-http for the canonical source repository
 * @copyright https://github.com/laminas/laminas-http/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-http/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Http\Header;

use Laminas\Uri\Http as HttpUri;

/**
 * Content-Location Header
 *
 * @link       http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.36
 */
class Referer extends AbstractLocation
{
    /**
     * Set the URI/URL for this header
     * according to RFC Referer URI should not have fragment
     *
     * @param  string|HttpUri $uri
     * @return $this
     */
    public function setUri($uri)
    {
        parent::setUri($uri);
        $this->uri->setFragment(null);

        return $this;
    }

    /**
     * Return header name
     *
     * @return string
     */
    public function getFieldName()
    {
        return 'Referer';
    }
}
