<?php
/**
 * @see       https://github.com/zendframework/zend-cache for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-cache/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Cache\Psr\SimpleCache;

use Psr\SimpleCache\CacheException as PsrCacheException;
use RuntimeException;

class SimpleCacheException extends RuntimeException implements PsrCacheException
{
}
