<?php
/**
 * @see       https://github.com/zendframework/zend-cache for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-cache/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Cache\Psr\CacheItemPool;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Psr\Cache\CacheItemInterface;

final class CacheItem implements CacheItemInterface
{
    /**
     * Cache key
     * @var string
     */
    private $key;

    /**
     * Cache value
     * @var mixed|null
     */
    private $value;

    /**
     * True if the cache item lookup resulted in a cache hit or if they item is deferred or successfully saved
     * @var bool
     */
    private $isHit = false;

    /**
     * Timestamp item will expire at if expiresAt() called, null otherwise
     * @var int|null
     */
    private $expiration = null;

    /**
     * Seconds after item is stored it will expire at if expiresAfter() called, null otherwise
     * @var int|null
     */
    private $ttl = null;

    /**
     * @var DateTimeZone
     */
    private $tz;

    /**
     * Constructor.
     *
     * @param string $key
     * @param mixed $value
     * @param bool $isHit
     */
    public function __construct($key, $value, $isHit)
    {
        $this->key   = $key;
        $this->value = $isHit ? $value : null;
        $this->isHit = $isHit;
        $this->utc   = new DateTimeZone('UTC');
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function isHit()
    {
        if (! $this->isHit) {
            return false;
        }
        $ttl = $this->getTtl();
        return $ttl === null || $ttl > 0;
    }

    /**
     * Sets isHit value
     *
     * This function is called by CacheItemPoolDecorator::saveDeferred() and is not intended for use by other calling
     * code.
     *
     * @param boolean $isHit
     * @return $this
     */
    public function setIsHit($isHit)
    {
        $this->isHit = $isHit;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function set($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function expiresAt($expiration)
    {
        if (! ($expiration === null || $expiration instanceof DateTimeInterface)) {
            throw new InvalidArgumentException('$expiration must be null or an instance of DateTimeInterface');
        }

        $this->expiration = $expiration instanceof DateTimeInterface ? $expiration->getTimestamp() : null;
        $this->ttl = null;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function expiresAfter($time)
    {
        if ($time instanceof DateInterval) {
            $now = new DateTimeImmutable('now', $this->utc);
            $end = $now->add($time);
            $this->ttl = $end->getTimestamp() - $now->getTimestamp();
        } elseif (is_int($time) || $time === null) {
            $this->ttl = $time;
        } else {
            throw new InvalidArgumentException(sprintf('Invalid $time "%s"', gettype($time)));
        }

        $this->expiration = null;

        return $this;
    }

    /**
     * Returns number of seconds until item expires
     *
     * If NULL, the pool should use the default TTL for the storage adapter. If <= 0, the item has expired.
     *
     * @return int|null
     */
    public function getTtl()
    {
        $ttl = $this->ttl;
        if ($this->expiration !== null) {
            $ttl = $this->expiration - time();
        }
        return $ttl;
    }
}
