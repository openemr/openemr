<?php

/**
 * @see       https://github.com/laminas/laminas-xmlrpc for the canonical source repository
 * @copyright https://github.com/laminas/laminas-xmlrpc/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-xmlrpc/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\XmlRpc\Server;

/**
 * Laminas\XmlRpc\Server\Cache: cache Laminas\XmlRpc\Server server definition
 */
class Cache extends \Laminas\Server\Cache
{
    /**
     * @var array Skip system methods when caching XML-RPC server
     */
    protected static $skipMethods = [
        'system.listMethods',
        'system.methodHelp',
        'system.methodSignature',
        'system.multicall',
    ];
}
