<?php

/**
 * @see       https://github.com/laminas/laminas-http for the canonical source repository
 * @copyright https://github.com/laminas/laminas-http/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-http/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Http;

use Laminas\Stdlib\Message;

/**
 * HTTP standard message (Request/Response)
 *
 * @link      http://www.w3.org/Protocols/rfc2616/rfc2616-sec4.html#sec4
 */
abstract class AbstractMessage extends Message
{
    /**#@+
     * @const string Version constant numbers
     */
    const VERSION_10 = '1.0';
    const VERSION_11 = '1.1';
    const VERSION_2  = '2';
    /**#@-*/

    /**
     * @var string
     */
    protected $version = self::VERSION_11;

    /**
     * @var Headers|null
     */
    protected $headers;

    /**
     * Set the HTTP version for this object, one of 1.0, 1.1 or 2
     * (AbstractMessage::VERSION_10, AbstractMessage::VERSION_11, AbstractMessage::VERSION_2)
     *
     * @param  string $version (Must be 1.0, 1.1 or 2)
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function setVersion($version)
    {
        if (! in_array($version, [self::VERSION_10, self::VERSION_11, self::VERSION_2])) {
            throw new Exception\InvalidArgumentException(
                'Not valid or not supported HTTP version: ' . $version
            );
        }
        $this->version = $version;
        return $this;
    }

    /**
     * Return the HTTP version for this request
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Provide an alternate Parameter Container implementation for headers in this object,
     * (this is NOT the primary API for value setting, for that see getHeaders())
     *
     * @see    getHeaders()
     * @param  Headers $headers
     * @return $this
     */
    public function setHeaders(Headers $headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * Return the header container responsible for headers
     *
     * @return Headers
     */
    public function getHeaders()
    {
        if ($this->headers === null || is_string($this->headers)) {
            // this is only here for fromString lazy loading
            $this->headers = (is_string($this->headers)) ? Headers::fromString($this->headers) : new Headers();
        }

        return $this->headers;
    }

    /**
     * Allow PHP casting of this object
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }
}
