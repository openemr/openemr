<?php

/**
 * @see       https://github.com/laminas/laminas-mvc for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\Service;

use Interop\Container\ContainerInterface;
use Laminas\Http\PhpEnvironment\Response as HttpResponse;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ResponseFactory implements FactoryInterface
{
    /**
     * Create and return a response instance.
     *
     * @param  ContainerInterface $container
     * @param  string $name
     * @param  null|array $options
     * @return HttpResponse
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        return new HttpResponse();
    }
}
