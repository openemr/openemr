<?php

/**
 * @see       https://github.com/laminas/laminas-soap for the canonical source repository
 * @copyright https://github.com/laminas/laminas-soap/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-soap/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Soap\Exception;

use RuntimeException as SPLRuntimeException;

/**
 * Exception thrown when there is an error during program execution
 */
class RuntimeException extends SPLRuntimeException implements ExceptionInterface
{
}
