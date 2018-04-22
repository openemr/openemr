<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Form;

use Traversable;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Exception\InvalidServiceException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for instantiating form elements
 */
final class ElementFactory implements FactoryInterface
{
    /**
     * Options to pass to the constructor (when used in v2), if any.
     *
     * @param null|array
     */
    private $creationOptions;

    /**
     * @param null|array|Traversable $creationOptions
     * @throws InvalidServiceException if $creationOptions cannot be coerced to
     *     an array.
     */
    public function __construct($creationOptions = null)
    {
        if (null === $creationOptions) {
            return;
        }

        $this->setCreationOptions($creationOptions);
    }

    /**
     * Create an instance of the requested class name.
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        if ($options === null) {
            $options = [];
        }

        if (isset($options['name'])) {
            $name = $options['name'];
        } else {
            // 'Zend\Form\Element' -> 'element'
            $parts = explode('\\', $requestedName);
            $name = strtolower(array_pop($parts));
        }

        if (isset($options['options'])) {
            $options = $options['options'];
        }

        return new $requestedName($name, $options);
    }

    /**
     * Create an instance of the named service.
     *
     * First, it checks if `$canonicalName` resolves to a class, and, if so, uses
     * that value to proxy to `__invoke()`.
     *
     * Next, if `$requestedName` is non-empty and resolves to a class, this
     * method uses that value to proxy to `__invoke()`.
     *
     * Finally, if the above each fail, it raises an exception.
     *
     * The approach above is performed as version 2 has two distinct behaviors
     * under which factories are invoked:
     *
     * - If an alias was used, $canonicalName is the resolved name, and
     *   $requestedName is the service name requested, in which case $canonicalName
     *   is likely the qualified class name;
     * - Otherwise, $canonicalName is the normalized name, and $requestedName
     *   is the original service name requested (typically the qualified class name).
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param null|string $canonicalName
     * @param null|string $requestedName
     * @return object
     * @throws InvalidServiceException
     */
    public function createService(ServiceLocatorInterface $serviceLocator, $canonicalName = null, $requestedName = null)
    {
        if (class_exists($canonicalName)) {
            return $this($serviceLocator, $canonicalName, $this->creationOptions);
        }

        if (is_string($requestedName) && class_exists($requestedName)) {
            return $this($serviceLocator, $requestedName, $this->creationOptions);
        }

        throw new InvalidServiceException(sprintf(
            '%s requires that the requested name is provided on invocation; please update your tests or '
            . 'consuming container',
            __CLASS__
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function setCreationOptions(array $creationOptions)
    {
        if ($creationOptions instanceof Traversable) {
            $creationOptions = iterator_to_array($creationOptions);
        }

        if (! is_array($creationOptions)) {
            throw new InvalidServiceException(sprintf(
                '%s cannot use non-array, non-traversable creation options; received %s',
                __CLASS__,
                (is_object($creationOptions) ? get_class($creationOptions) : gettype($creationOptions))
            ));
        }

        $this->creationOptions = $creationOptions;
    }
}
