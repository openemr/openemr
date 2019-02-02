<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Math\BigInteger;

abstract class BigInteger
{
    /**
     * The default adapter.
     *
     * @var Adapter\AdapterInterface
     */
    protected static $defaultAdapter = null;

    /**
     * Create a BigInteger adapter instance
     *
     * @param  string|null $adapterName
     * @return Adapter\AdapterInterface
     */
    public static function factory($adapterName = null)
    {
        if (null === $adapterName) {
            return static::getAvailableAdapter();
        }

        $adapterName = sprintf('%s\\Adapter\\%s', __NAMESPACE__, ucfirst($adapterName));
        if (! class_exists($adapterName)
            || ! is_subclass_of($adapterName, Adapter\AdapterInterface::class)
        ) {
            throw new Exception\InvalidArgumentException(sprintf(
                'The adapter %s either does not exist or does not implement %s',
                $adapterName,
                Adapter\AdapterInterface::class
            ));
        }

        return new $adapterName();
    }

    /**
     * Set default BigInteger adapter
     *
     * @param string|Adapter\AdapterInterface $adapter
     */
    public static function setDefaultAdapter($adapter)
    {
        static::$defaultAdapter = static::factory($adapter);
    }

    /**
     * Get default BigInteger adapter
     *
     * @return null|Adapter\AdapterInterface
     */
    public static function getDefaultAdapter()
    {
        if (null === static::$defaultAdapter) {
            static::$defaultAdapter = static::getAvailableAdapter();
        }
        return static::$defaultAdapter;
    }

    /**
     * Determine and return available adapter
     *
     * @return Adapter\AdapterInterface
     * @throws Exception\RuntimeException
     */
    public static function getAvailableAdapter()
    {
        if (extension_loaded('gmp')) {
            return static::factory('Gmp');
        }

        if (extension_loaded('bcmath')) {
            return static::factory('Bcmath');
        }

        throw new Exception\RuntimeException('Big integer math support is not detected');
    }

    /**
     * Call adapter methods statically
     *
     * @param  string $method
     * @param  mixed $args
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        $adapter = static::getDefaultAdapter();
        return call_user_func_array([$adapter, $method], $args);
    }
}
