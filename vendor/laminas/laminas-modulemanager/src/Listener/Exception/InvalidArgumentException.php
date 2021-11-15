<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ModuleManager\Listener\Exception;

use Laminas\ModuleManager\Exception;

/**
 * Invalid Argument Exception
 */
class InvalidArgumentException extends Exception\InvalidArgumentException implements ExceptionInterface
{
}
