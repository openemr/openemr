<?php

declare(strict_types=1);

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ServiceManager\Exception;

use RuntimeException as SplRuntimeException;

/**
 * This exception is thrown by plugin managers when the created object does not match
 * the plugin manager's conditions
 */
class InvalidServiceException extends SplRuntimeException implements ExceptionInterface
{
}
