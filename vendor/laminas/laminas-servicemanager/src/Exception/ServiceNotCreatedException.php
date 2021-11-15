<?php

declare(strict_types=1);

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ServiceManager\Exception;

use Interop\Container\Exception\ContainerException;
use RuntimeException as SplRuntimeException;

/**
 * This exception is thrown when the service locator do not manage to create
 * the service (factory that has an error...)
 */
class ServiceNotCreatedException extends SplRuntimeException implements
    ContainerException,
    ExceptionInterface
{
}
