<?php
/**
 * @see       https://github.com/zendframework/zend-cache for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-cache/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Cache\Psr\CacheItemPool;

use Psr\Cache\CacheException as CacheExceptionInterface;
use RuntimeException;

class CacheException extends RuntimeException implements CacheExceptionInterface
{
}
