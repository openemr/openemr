<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Paginator\Adapter\Service;

use Iterator;
use Interop\Container\ContainerInterface;
use Zend\Paginator\Iterator as IteratorAdapter;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class IteratorFactory implements FactoryInterface
{
    /**
     * Options to use when creating adapter (v2)
     *
     * @var null|array
     */
    protected $creationOptions;

    /**
     * {@inheritDoc}
     *
     * @return IteratorAdapter
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        if (null === $options || empty($options)) {
            throw new ServiceNotCreatedException(sprintf(
                '%s requires a minimum of an Iterator instance',
                IteratorAdapter::class
            ));
        }

        $iterator = array_shift($options);

        if (! $iterator instanceof Iterator) {
            throw new ServiceNotCreatedException(sprintf(
                '%s requires an Iterator instance; received %s',
                IteratorAdapter::class,
                (is_object($iterator) ? get_class($iterator) : gettype($iterator))
            ));
        }

        return new $requestedName($iterator);
    }

    /**
     * Create and return an IteratorAdapter instance (v2)
     *
     * @param ServiceLocatorInterface $container
     * @param null|string $name
     * @param string $requestedName
     * @return IteratorAdapter
     */
    public function createService(
        ServiceLocatorInterface $container,
        $name = null,
        $requestedName = IteratorAdapter::class
    ) {
        return $this($container, $requestedName, $this->creationOptions);
    }

    /**
     * Options to use with factory (v2)
     *
     * @param array $creationOptions
     * @return void
     */
    public function setCreationOptions(array $creationOptions)
    {
        $this->creationOptions = $creationOptions;
    }
}
