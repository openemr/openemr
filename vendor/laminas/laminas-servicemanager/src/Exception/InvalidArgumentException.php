<?php

declare(strict_types=1);

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ServiceManager\Exception;

use InvalidArgumentException as SplInvalidArgumentException;
use Laminas\ServiceManager\AbstractFactoryInterface;
use Laminas\ServiceManager\Initializer\InitializerInterface;

use function get_class;
use function gettype;
use function is_object;
use function sprintf;

/**
 * @inheritDoc
 */
class InvalidArgumentException extends SplInvalidArgumentException implements ExceptionInterface
{
    /**
     * @param mixed $initializer
     */
    public static function fromInvalidInitializer($initializer): self
    {
        return new self(sprintf(
            'An invalid initializer was registered. Expected a callable or an'
            . ' instance of "%s"; received "%s"',
            InitializerInterface::class,
            is_object($initializer) ? get_class($initializer) : gettype($initializer)
        ));
    }

    /**
     * @param mixed $abstractFactory
     */
    public static function fromInvalidAbstractFactory($abstractFactory): self
    {
        return new self(sprintf(
            'An invalid abstract factory was registered. Expected an instance of or a valid'
            . ' class name resolving to an implementation of "%s", but "%s" was received.',
            AbstractFactoryInterface::class,
            is_object($abstractFactory) ? get_class($abstractFactory) : gettype($abstractFactory)
        ));
    }
}
