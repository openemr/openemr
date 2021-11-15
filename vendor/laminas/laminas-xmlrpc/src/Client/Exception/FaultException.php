<?php

/**
 * @see       https://github.com/laminas/laminas-xmlrpc for the canonical source repository
 * @copyright https://github.com/laminas/laminas-xmlrpc/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-xmlrpc/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\XmlRpc\Client\Exception;

use Laminas\XmlRpc\Exception;

/**
 * Thrown by Laminas\XmlRpc\Client when an XML-RPC fault response is returned.
 */
class FaultException extends Exception\BadMethodCallException implements ExceptionInterface
{
}
