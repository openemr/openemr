<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Feed\Reader\Http;

interface HeaderAwareResponseInterface extends ResponseInterface
{
    /**
     * Retrieve a header (as a single line) from the response.
     *
     * Header name lookups MUST be case insensitive.
     *
     * Since the only header values the feed reader consumes are singular
     * in nature, this method is expected to return a string, and not
     * an array of values.
     *
     * @param string $name Header name to retrieve.
     * @param mixed $default Default value to use if header is not present.
     * @return string
     */
    public function getHeaderLine($name, $default = null);
}
