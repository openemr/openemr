<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Service;

use Interop\Container\ContainerInterface;
use Zend\Console\Console;
use Zend\Console\Response as ConsoleResponse;
use Zend\Http\PhpEnvironment\Response as HttpResponse;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\MessageInterface;

class ResponseFactory implements FactoryInterface
{
    /**
     * Create and return a response instance, according to current environment.
     *
     * @param  ContainerInterface $container
     * @param  string $name
     * @param  null|array $options
     * @return MessageInterface
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        if (Console::isConsole()) {
            return new ConsoleResponse();
        }

        return new HttpResponse();
    }

    /**
     * Create and return MessageInterface instance
     *
     * For use with zend-servicemanager v2; proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @return MessageInterface
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, MessageInterface::class);
    }
}
