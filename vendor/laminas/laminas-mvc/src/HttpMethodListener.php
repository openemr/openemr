<?php

/**
 * @see       https://github.com/laminas/laminas-mvc for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc;

use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Http\Request as HttpRequest;
use Laminas\Http\Response as HttpResponse;

class HttpMethodListener extends AbstractListenerAggregate
{
    /**
     * @var array
     */
    protected $allowedMethods = [
        HttpRequest::METHOD_CONNECT,
        HttpRequest::METHOD_DELETE,
        HttpRequest::METHOD_GET,
        HttpRequest::METHOD_HEAD,
        HttpRequest::METHOD_OPTIONS,
        HttpRequest::METHOD_PATCH,
        HttpRequest::METHOD_POST,
        HttpRequest::METHOD_PUT,
        HttpRequest::METHOD_PROPFIND,
        HttpRequest::METHOD_TRACE,
    ];

    /**
     * @var bool
     */
    protected $enabled = true;

    /**
     * @param bool  $enabled
     * @param array $allowedMethods
     */
    public function __construct($enabled = true, $allowedMethods = [])
    {
        $this->setEnabled($enabled);

        if (! empty($allowedMethods)) {
            $this->setAllowedMethods($allowedMethods);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        if (! $this->isEnabled()) {
            return;
        }

        $this->listeners[] = $events->attach(
            MvcEvent::EVENT_ROUTE,
            [$this, 'onRoute'],
            10000
        );
    }

    /**
     * @param  MvcEvent $e
     * @return void|HttpResponse
     */
    public function onRoute(MvcEvent $e)
    {
        $request = $e->getRequest();
        $response = $e->getResponse();

        if (! $request instanceof HttpRequest || ! $response instanceof HttpResponse) {
            return;
        }

        $method = $request->getMethod();

        if (in_array($method, $this->getAllowedMethods())) {
            return;
        }

        $response->setStatusCode(405);

        return $response;
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return $this->allowedMethods;
    }

    /**
     * @param array $allowedMethods
     */
    public function setAllowedMethods(array $allowedMethods)
    {
        foreach ($allowedMethods as &$value) {
            $value = strtoupper($value);
        }
        $this->allowedMethods = $allowedMethods;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = (bool) $enabled;
    }
}
