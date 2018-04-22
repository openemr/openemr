<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Feed\Reader\Http;

use Psr\Http\Message\ResponseInterface as Psr7ResponseInterface;

/**
 * ResponseInterface wrapper for a PSR-7 response.
 */
class Psr7ResponseDecorator implements HeaderAwareResponseInterface
{
    /**
     * @var Psr7ResponseInterface
     */
    private $decoratedResponse;

    /**
     * @param Psr7ResponseInterface $response
     */
    public function __construct(Psr7ResponseInterface $response)
    {
        $this->decoratedResponse = $response;
    }

    /**
     * Return the original PSR-7 response being decorated.
     *
     * @return Psr7ResponseInterface
     */
    public function getDecoratedResponse()
    {
        return $this->decoratedResponse;
    }

    /**
     * {@inheritDoc}
     */
    public function getBody()
    {
        return (string) $this->decoratedResponse->getBody();
    }

    /**
     * {@inheritDoc}
     */
    public function getStatusCode()
    {
        return $this->decoratedResponse->getStatusCode();
    }

    /**
     * {@inheritDoc}
     */
    public function getHeaderLine($name, $default = null)
    {
        if (! $this->decoratedResponse->hasHeader($name)) {
            return $default;
        }
        return $this->decoratedResponse->getHeaderLine($name);
    }
}
