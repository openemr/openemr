<?php

namespace Zend\Filter\Word\Service;

use Interop\Container\ContainerInterface;
use Traversable;
use Zend\Filter\Word\SeparatorToSeparator;
use Zend\ServiceManager\Exception\InvalidServiceException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SeparatorToSeparatorFactory implements FactoryInterface
{
    /**
     * Options to pass to the constructor (when used in v2), if any.
     *
     * @param null|array
     */
    private $creationOptions = [];

    public function __construct($creationOptions = null)
    {
        if (null === $creationOptions) {
            return;
        }

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

    /**
     * {@inheritDoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new SeparatorToSeparator(
            isset($options['search_separator']) ? $options['search_separator'] : ' ',
            isset($options['replacement_separator']) ? $options['replacement_separator'] : '-'
        );
    }

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator, self::class, $this->creationOptions);
    }

    public function setCreationOptions(array $options)
    {
        $this->creationOptions = $options;
    }
}
