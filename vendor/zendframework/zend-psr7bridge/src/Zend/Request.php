<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @see       http://github.com/zendframework/zend-diactoros for the canonical source repository
 * @copyright Copyright (c) 2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-diactoros/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Psr7Bridge\Zend;

use Zend\Http\Header\Cookie;
use Zend\Http\PhpEnvironment\Request as BaseRequest;
use Zend\Stdlib\Parameters;

class Request extends BaseRequest
{
    /**
     * Overload constructor.
     *
     * This method overloads the original constructor to accept the various
     * request metadata instead of pulling from superglobals.
     *
     * @param string $method
     * @param string|\Psr\Http\Message\UriInterface $uri
     * @param array $headers
     * @param array $cookies
     * @param array $queryStringArguments
     * @param array $postParameters
     * @param array $uploadedFiles
     * @param array $serverParams
     */
    public function __construct(
        $method,
        $uri,
        array $headers,
        array $cookies,
        array $queryStringArguments,
        array $postParameters,
        array $uploadedFiles,
        array $serverParams
    ) {
        $this->setAllowCustomMethods(true);

        $this->setMethod($method);
        $this->setRequestUri((string) $uri);
        $this->setUri((string) $uri);

        $headerCollection = $this->getHeaders();
        foreach ($headers as $name => $values) {
            foreach ($values as $value) {
                $headerCollection->addHeaderLine($name, $value);
            }
        }

        if (! empty($cookies)) {
            $headerCollection->addHeader(new Cookie($cookies));
        }

        $this->setQuery(new Parameters($queryStringArguments));
        $this->setPost(new Parameters($postParameters));
        $this->setFiles(new Parameters($uploadedFiles));

        // Do not use `setServerParams()`, as that extracts headers, URI, etc.
        $this->serverParams = new Parameters($serverParams);
    }
}
