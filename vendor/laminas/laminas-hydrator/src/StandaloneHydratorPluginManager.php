<?php

/**
 * @see       https://github.com/laminas/laminas-hydrator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-hydrator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-hydrator/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Hydrator;

use Psr\Container\ContainerInterface;

use function strtolower;

/**
 * Standalone hydrator manager.
 *
 * This class implements a standalone version of the HydratorPluginManager
 * that can be used anywhere a PSR-11 ContainerInterface is expected.
 *
 * It will load any hydrator implementation shipped in this package, and only
 * those hydrators shipped in this package, using:
 *
 * - The fully qualified class name.
 * - The class name minus the namespace.
 * - The fully qualified class name minus the "Hydrator" suffix (for BC
 *   compatibility with v2 names).
 * - The class name minus the namespace or the "Hydrator" suffix (for BC
 *   compatibility with v2 names).
 *
 * If you want to be able to configure additional services, you will need to
 * either install laminas-servicemanager and use the HydratorPluginManager;
 * wire hydrators into your application container; or write your own
 * implementation.
 */
final class StandaloneHydratorPluginManager implements HydratorPluginManagerInterface
{
    /**
     * To allow using the short name (class name without namespace), this maps
     * the lowercase name to the FQCN. For hydrators that in previous versions
     * did not have the Hydrator suffix, it also maps the class name without
     * the suffix.
     *
     * @var array<string, string>
     */
    private $aliases = [
        'arrayserializable'         => ArraySerializableHydrator::class,
        ArraySerializable::class    => ArraySerializableHydrator::class,
        'arrayserializablehydrator' => ArraySerializableHydrator::class,
        ClassMethods::class         => ClassMethodsHydrator::class,
        'classmethods'              => ClassMethodsHydrator::class,
        'classmethodshydrator'      => ClassMethodsHydrator::class,
        'delegatinghydrator'        => DelegatingHydrator::class,
        ObjectProperty::class       => ObjectPropertyHydrator::class,
        'objectpropertyhydrator'    => ObjectPropertyHydrator::class,
        'objectproperty'            => ObjectPropertyHydrator::class,
        Reflection::class           => ReflectionHydrator::class,
        'reflectionhydrator'        => ReflectionHydrator::class,
        'reflection'                => ReflectionHydrator::class,

        // Legacy Zend Framework aliases
        \Zend\Hydrator\ArraySerializable::class => ArraySerializableHydrator::class,
        \Zend\Hydrator\ClassMethods::class => ClassMethodsHydrator::class,
        \Zend\Hydrator\ObjectProperty::class => ObjectPropertyHydrator::class,
        \Zend\Hydrator\Reflection::class => ReflectionHydrator::class,
    ];

    /**
     * @var array<string, callable>
     */
    private $factories = [];

    public function __construct()
    {
        $invokableFactory = function (ContainerInterface $container, string $class): object {
            return new $class();
        };

        $this->factories = [
            ArraySerializableHydrator::class => $invokableFactory,
            ClassMethodsHydrator::class      => $invokableFactory,
            DelegatingHydrator::class        => new DelegatingHydratorFactory(),
            ObjectPropertyHydrator::class    => $invokableFactory,
            ReflectionHydrator::class        => $invokableFactory,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function get($id)
    {
        $class = $this->resolveName($id);
        if (! $class) {
            throw Exception\MissingHydratorServiceException::forService($id);
        }

        $instance = ($this->factories[$class])($this, $class);

        return $instance;
    }

    /**
     * {@inheritDoc}
     */
    public function has($id)
    {
        return null !== $this->resolveName($id);
    }

    /**
     * Resolve a service name from an identifier.
     */
    private function resolveName(string $name) : ?string
    {
        if (isset($this->factories[$name])) {
            return $name;
        }

        if (isset($this->aliases[$name])) {
            return $this->aliases[$name];
        }

        return $this->aliases[strtolower($name)] ?? null;
    }
}
