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
use Zend\Console\Request as ConsoleRequest;
use Zend\Http\PhpEnvironment\Request as HttpRequest;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RequestFactory implements FactoryInterface
{
    /**
     * Create and return a request instance, according to current environment.
     *
     * @param  ContainerInterface $container
     * @param  string $name
     * @param  null|array $options
     * @return ConsoleRequest|HttpRequest
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        if (Console::isConsole()) {
            return new ConsoleRequest();
        }

        return new HttpRequest();
    }

    /**
     * Create and return HttpRequest or ConsoleRequest instance
     *
     * For use with zend-servicemanager v2; proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @return HttpRequest|ConsoleRequest
     */
    public function createService(ServiceLocatorInterface $container)
    {
        $type = Console::isConsole() ? ConsoleRequest::class : HttpRequest::class;
        return $this($container, $type);
    }
}
