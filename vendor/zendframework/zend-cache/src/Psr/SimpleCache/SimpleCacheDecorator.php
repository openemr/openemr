<?php
/**
 * @see       https://github.com/zendframework/zend-cache for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-cache/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Cache\Psr\SimpleCache;

use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Psr\SimpleCache\CacheInterface as SimpleCacheInterface;
use Throwable;
use Traversable;
use Zend\Cache\Exception\InvalidArgumentException as ZendCacheInvalidArgumentException;
use Zend\Cache\Psr\SerializationTrait;
use Zend\Cache\Storage\ClearByNamespaceInterface;
use Zend\Cache\Storage\FlushableInterface;
use Zend\Cache\Storage\StorageInterface;

/**
 * Decorate a zend-cache storage adapter for usage as a PSR-16 implementation.
 */
class SimpleCacheDecorator implements SimpleCacheInterface
{
    use SerializationTrait;

    /**
     * Characters reserved by PSR-16 that are not valid in cache keys.
     */
    const INVALID_KEY_CHARS = ':@{}()/\\';

    /**
     * @var bool
     */
    private $providesPerItemTtl = true;

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * Reference used by storage when calling getItem() to indicate status of
     * operation.
     *
     * @var null|bool
     */
    private $success;

    /**
     * @var DateTimeZone
     */
    private $utc;

    public function __construct(StorageInterface $storage)
    {
        if ($this->isSerializationRequired($storage)) {
            throw new SimpleCacheException(sprintf(
                'The storage adapter "%s" requires a serializer plugin; please see'
                . ' https://docs.zendframework.com/zend-cache/storage/plugin/#quick-start'
                . ' for details on how to attach the plugin to your adapter.',
                get_class($storage)
            ));
        }

        $this->memoizeTtlCapabilities($storage);
        $this->storage = $storage;
        $this->utc = new DateTimeZone('UTC');
    }

    /**
     * {@inheritDoc}
     */
    public function get($key, $default = null)
    {
        $this->validateKey($key);

        $this->success = null;
        try {
            $result = $this->storage->getItem($key, $this->success);
        } catch (Throwable $e) {
            throw static::translateException($e);
        } catch (Exception $e) {
            throw static::translateException($e);
        }

        $result = $result === null ? $default : $result;
        return $this->success ? $result : $default;
    }

    /**
     * {@inheritDoc}
     */
    public function set($key, $value, $ttl = null)
    {
        $this->validateKey($key);
        $ttl = $this->convertTtlToInteger($ttl);

        // PSR-16 states that 0 or negative TTL values should result in cache
        // invalidation for the item.
        if (null !== $ttl && 1 > $ttl) {
            return $this->delete($key);
        }

        // If a positive TTL is set, but the adapter does not support per-item
        // TTL, we return false immediately.
        if (null !== $ttl && ! $this->providesPerItemTtl) {
            return false;
        }

        $options = $this->storage->getOptions();
        $previousTtl = $options->getTtl();
        $options->setTtl($ttl);

        try {
            $result = $this->storage->setItem($key, $value);
        } catch (Throwable $e) {
            throw static::translateException($e);
        } catch (Exception $e) {
            throw static::translateException($e);
        } finally {
            $options->setTtl($previousTtl);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function delete($key)
    {
        $this->validateKey($key);

        try {
            return null !== $this->storage->removeItem($key);
        } catch (Throwable $e) {
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function clear()
    {
        $namespace = $this->storage->getOptions()->getNamespace();

        if ($this->storage instanceof ClearByNamespaceInterface && $namespace) {
            return $this->storage->clearByNamespace($namespace);
        }

        if ($this->storage instanceof FlushableInterface) {
            return $this->storage->flush();
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getMultiple($keys, $default = null)
    {
        $keys = $this->convertIterableToArray($keys, false, __FUNCTION__);
        array_walk($keys, [$this, 'validateKey']);

        try {
            $results = $this->storage->getItems($keys);
        } catch (Throwable $e) {
            throw static::translateException($e);
        } catch (Exception $e) {
            throw static::translateException($e);
        }

        foreach ($keys as $key) {
            if (! isset($results[$key])) {
                $results[$key] = $default;
                continue;
            }
        }

        return $results;
    }

    /**
     * {@inheritDoc}
     */
    public function setMultiple($values, $ttl = null)
    {
        $values = $this->convertIterableToArray($values, true, __FUNCTION__);
        $keys = array_keys($values);
        $ttl = $this->convertTtlToInteger($ttl);

        // PSR-16 states that 0 or negative TTL values should result in cache
        // invalidation for the items.
        if (null !== $ttl && 1 > $ttl) {
            return $this->deleteMultiple($keys);
        }

        array_walk($keys, [$this, 'validateKey']);

        // If a positive TTL is set, but the adapter does not support per-item
        // TTL, we return false -- but not until after we validate keys.
        if (null !== $ttl && ! $this->providesPerItemTtl) {
            return false;
        }

        $options = $this->storage->getOptions();
        $previousTtl = $options->getTtl();
        $options->setTtl($ttl);

        try {
            $result = $this->storage->setItems($values);
        } catch (Throwable $e) {
            throw static::translateException($e);
        } catch (Exception $e) {
            throw static::translateException($e);
        } finally {
            $options->setTtl($previousTtl);
        }

        if (empty($result)) {
            return true;
        }

        foreach ($result as $index => $key) {
            if (! $this->storage->hasItem($key)) {
                unset($result[$index]);
            }
        }

        return empty($result);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteMultiple($keys)
    {
        $keys = $this->convertIterableToArray($keys, false, __FUNCTION__);
        if (empty($keys)) {
            return true;
        }

        array_walk($keys, [$this, 'validateKey']);

        try {
            $result = $this->storage->removeItems($keys);
        } catch (Throwable $e) {
            return false;
        } catch (Exception $e) {
            return false;
        }

        if (empty($result)) {
            return true;
        }

        foreach ($result as $index => $key) {
            if (! $this->storage->hasItem($key)) {
                unset($result[$index]);
            }
        }

        return empty($result);
    }

    /**
     * {@inheritDoc}
     */
    public function has($key)
    {
        $this->validateKey($key);

        try {
            return $this->storage->hasItem($key);
        } catch (Throwable $e) {
            throw static::translateException($e);
        } catch (Exception $e) {
            throw static::translateException($e);
        }
    }

    /**
     * @param Throwable|Exception $e
     * @return SimpleCacheException
     */
    private static function translateException($e)
    {
        $exceptionClass = $e instanceof ZendCacheInvalidArgumentException
            ? SimpleCacheInvalidArgumentException::class
            : SimpleCacheException::class;

        return new $exceptionClass($e->getMessage(), $e->getCode(), $e);
    }

    /**
     * @param string $key
     * @return void
     * @throws SimpleCacheInvalidArgumentException if key is invalid
     */
    private function validateKey($key)
    {
        if ('' === $key) {
            throw new SimpleCacheInvalidArgumentException(
                'Invalid key provided; cannot be empty'
            );
        }

        if (0 === $key) {
            // cache/integration-tests erroneously tests that ['0' => 'value']
            // is a valid payload to setMultiple(). However, PHP silently
            // converts '0' to 0, which would normally be invalid. For now,
            // we need to catch just this single value so tests pass.
            // I have filed an issue to correct the test:
            // https://github.com/php-cache/integration-tests/issues/92
            return $key;
        }

        if (! is_string($key)) {
            throw new SimpleCacheInvalidArgumentException(sprintf(
                'Invalid key provided of type "%s"%s; must be a string',
                is_object($key) ? get_class($key) : gettype($key),
                is_scalar($key) ? sprintf(' (%s)', var_export($key, true)) : ''
            ));
        }

        $regex = sprintf('/[%s]/', preg_quote(self::INVALID_KEY_CHARS, '/'));
        if (preg_match($regex, $key)) {
            throw new SimpleCacheInvalidArgumentException(sprintf(
                'Invalid key "%s" provided; cannot contain any of (%s)',
                $key,
                self::INVALID_KEY_CHARS
            ));
        }

        if (preg_match('/^.{65,}/u', $key)) {
            throw new SimpleCacheInvalidArgumentException(sprintf(
                'Invalid key "%s" provided; key is too long. Must be no more than 64 characters',
                $key
            ));
        }
    }

    /**
     * Determine if the storage adapter provides per-item TTL capabilities
     *
     * @param StorageInterface $storage
     * @return void
     */
    private function memoizeTtlCapabilities(StorageInterface $storage)
    {
        $capabilities = $storage->getCapabilities();
        $this->providesPerItemTtl = $capabilities->getStaticTtl() && (0 < $capabilities->getMinTtl());
    }

    /**
     * @param int|DateInterval
     * @return null|int
     * @throws SimpleCacheInvalidArgumentException for invalid arguments
     */
    private function convertTtlToInteger($ttl)
    {
        // null === absence of a TTL
        if (null === $ttl) {
            return null;
        }

        // integers are always okay
        if (is_int($ttl)) {
            return $ttl;
        }

        // Numeric strings evaluating to integers can be cast
        if (is_string($ttl)
            && ('0' === $ttl
                || preg_match('/^[1-9][0-9]+$/', $ttl)
            )
        ) {
            return (int) $ttl;
        }

        // DateIntervals require conversion
        if ($ttl instanceof DateInterval) {
            $now = new DateTimeImmutable('now', $this->utc);
            $end = $now->add($ttl);
            return $end->getTimestamp() - $now->getTimestamp();
        }

        // All others are invalid
        throw new SimpleCacheInvalidArgumentException(sprintf(
            'Invalid TTL "%s" provided; must be null, an integer, or a %s instance',
            is_object($ttl) ? get_class($ttl) : var_export($ttl, true),
            DateInterval::class
        ));
    }

    /**
     * @param array|iterable $iterable
     * @param bool $useKeys Whether or not to preserve keys during conversion
     * @param string $forMethod Method that called this one; used for reporting
     *     invalid values.
     * @return array
     * @throws SimpleCacheInvalidArgumentException for invalid $iterable values
     */
    private function convertIterableToArray($iterable, $useKeys, $forMethod)
    {
        if (is_array($iterable)) {
            return $iterable;
        }

        if (! $iterable instanceof Traversable) {
            throw new SimpleCacheInvalidArgumentException(sprintf(
                'Invalid value provided to %s::%s; must be an array or Traversable',
                __CLASS__,
                $forMethod
            ));
        }

        $array = [];
        foreach ($iterable as $key => $value) {
            if (! $useKeys) {
                $array[] = $value;
                continue;
            }

            if (! is_string($key) && ! is_int($key) && ! is_float($key)) {
                throw new SimpleCacheInvalidArgumentException(sprintf(
                    'Invalid key detected of type "%s"; must be a scalar',
                    is_object($key) ? get_class($key) : gettype($key)
                ));
            }
            $array[$key] = $value;
        }
        return $array;
    }
}
