<?php

/**
 * @see       https://github.com/laminas/laminas-mvc for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\Exception;

/**
 * @deprecated Since 3.2.0
 */
class ReachedFinalHandlerException extends RuntimeException
{
    /**
     * @return self
     */
    public static function create()
    {
        return new self('Reached the final handler for middleware pipe - check the pipe configuration');
    }
}
