<?php

/**
 * @see       https://github.com/laminas/laminas-http for the canonical source repository
 * @copyright https://github.com/laminas/laminas-http/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-http/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Http\Client\Adapter\Exception;

use Laminas\Http\Client\Exception;

class OutOfRangeException extends Exception\OutOfRangeException implements
    ExceptionInterface
{
}
