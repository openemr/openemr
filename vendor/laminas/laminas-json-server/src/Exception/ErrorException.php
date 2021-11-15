<?php

/**
 * @see       https://github.com/laminas/laminas-json-server for the canonical source repository
 * @copyright https://github.com/laminas/laminas-json-server/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-json-server/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Json\Server\Exception;

/**
 * Thrown by Laminas\Json\Server\Client when a JSON-RPC fault response is returned.
 */
class ErrorException extends BadMethodCallException implements ExceptionInterface
{
}
