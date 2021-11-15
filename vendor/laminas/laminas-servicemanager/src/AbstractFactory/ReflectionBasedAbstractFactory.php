<?php

declare(strict_types=1);

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ServiceManager\AbstractFactory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;

use function array_map;
use function class_exists;
use function sprintf;

/**
 * Reflection-based factory.
 *
 * To ease development, this factory may be used for classes with
 * type-hinted arguments that resolve to services in the application
 * container; this allows omitting the step of writing a factory for
 * each controller.
 *
 * You may use it as either an abstract factory:
 *
 * <code>
 * 'service_manager' => [
 *     'abstract_factories' => [
 *         ReflectionBasedAbstractFactory::class,
 *     ],
 * ],
 * </code>
 *
 * Or as a factory, mapping a class name to it:
 *
 * <code>
 * 'service_manager' => [
 *     'factories' => [
 *         MyClassWithDependencies::class => ReflectionBasedAbstractFactory::class,
 *     ],
 * ],
 * </code>
 *
 * The latter approach is more explicit, and also more performant.
 *
 * The factory has the following constraints/features:
 *
 * - A parameter named `$config` typehinted as an array will receive the
 *   application "config" service (i.e., the merged configuration).
 * - Parameters type-hinted against array, but not named `$config` will
 *   be injected with an empty array.
 * - Scalar parameters will result in an exception being thrown, unless
 *   a default value is present; if the default is present, that will be used.
 * - If a service cannot be found for a given typehint, the factory will
 *   raise an exception detailing this.
 * - Some services provided by Laminas components do not have
 *   entries based on their class name (for historical reasons); the
 *   factory allows defining a map of these class/interface names to the
 *   corresponding service name to allow them to resolve.
 *
 * `$options` passed to the factory are ignored in all cases, as we cannot
 * make assumptions about which argument(s) they might replace.
 *
 * Based on the LazyControllerAbstractFactory from laminas-mvc.
 */
class ReflectionBasedAbstractFactory implements AbstractFactoryInterface
{
    /**
     * Maps known classes/interfaces to the service that provides them; only
     * required for those services with no entry based on the class/interface
     * name.
     *
     * Extend the class if you wish to add to the list.
     *
     * Example:
     *
     * <code>
     * [
     *     \Laminas\Filter\FilterPluginManager::class       => 'FilterManager',
     *     \Laminas\Validator\ValidatorPluginManager::class => 'ValidatorManager',
     * ]
     * </code>
     *
     * @var string[]
     */
    protected $aliases = [];

    /**
     * Constructor.
     *
     * Allows overriding the internal list of aliases. These should be of the
     * form `class name => well-known service name`; see the documentation for
     * the `$aliases` property for details on what is accepted.
     *
     * @param string[] $aliases
     */
    public function __construct(array $aliases = [])
    {
        if (! empty($aliases)) {
            $this->aliases = $aliases;
        }
    }

    /**
     * {@inheritDoc}
     *
     * @return DispatchableInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $reflectionClass = new ReflectionClass($requestedName);

        if (null === ($constructor = $reflectionClass->getConstructor())) {
            return new $requestedName();
        }

        $reflectionParameters = $constructor->getParameters();

        if (empty($reflectionParameters)) {
            return new $requestedName();
        }

        $resolver = $container->has('config')
            ? $this->resolveParameterWithConfigService($container, $requestedName)
            : $this->resolveParameterWithoutConfigService($container, $requestedName);

        $parameters = array_map($resolver, $reflectionParameters);

        return new $requestedName(...$parameters);
    }

    /**
     * {@inheritDoc}
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        return class_exists($requestedName) && $this->canCallConstructor($requestedName);
    }

    private function canCallConstructor(string $requestedName) : bool
    {
        $constructor = (new ReflectionClass($requestedName))->getConstructor();

        return $constructor === null || $constructor->isPublic();
    }

    /**
     * Resolve a parameter to a value.
     *
     * Returns a callback for resolving a parameter to a value, but without
     * allowing mapping array `$config` arguments to the `config` service.
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @return callable
     */
    private function resolveParameterWithoutConfigService(ContainerInterface $container, $requestedName)
    {
        /**
         * @param ReflectionParameter $parameter
         * @return mixed
         * @throws ServiceNotFoundException If type-hinted parameter cannot be
         *   resolved to a service in the container.
         */
        return function (ReflectionParameter $parameter) use ($container, $requestedName) {
            return $this->resolveParameter($parameter, $container, $requestedName);
        };
    }

    /**
     * Returns a callback for resolving a parameter to a value, including mapping 'config' arguments.
     *
     * Unlike resolveParameter(), this version will detect `$config` array
     * arguments and have them return the 'config' service.
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @return callable
     */
    private function resolveParameterWithConfigService(ContainerInterface $container, $requestedName)
    {
        /**
         * @param ReflectionParameter $parameter
         * @return mixed
         * @throws ServiceNotFoundException If type-hinted parameter cannot be
         *   resolved to a service in the container.
         */
        return function (ReflectionParameter $parameter) use ($container, $requestedName) {
            if ($parameter->getName() === 'config') {
                $type = $parameter->getType();
                if ($type instanceof ReflectionNamedType && $type->getName() === 'array') {
                    return $container->get('config');
                }
            }
            return $this->resolveParameter($parameter, $container, $requestedName);
        };
    }

    /**
     * Logic common to all parameter resolution.
     *
     * @param ReflectionParameter $parameter
     * @param ContainerInterface $container
     * @param string $requestedName
     * @return mixed
     * @throws ServiceNotFoundException If type-hinted parameter cannot be
     *   resolved to a service in the container.
     */
    private function resolveParameter(ReflectionParameter $parameter, ContainerInterface $container, $requestedName)
    {
        $type = $parameter->getType();
        $type = $type instanceof ReflectionNamedType ? $type->getName() : null;

        if ($type === 'array') {
            return [];
        }

        if ($type === null || (is_string($type) && ! class_exists($type) && ! interface_exists($type))) {
            if (! $parameter->isDefaultValueAvailable()) {
                throw new ServiceNotFoundException(sprintf(
                    'Unable to create service "%s"; unable to resolve parameter "%s" '
                    . 'to a class, interface, or array type',
                    $requestedName,
                    $parameter->getName()
                ));
            }

            return $parameter->getDefaultValue();
        }

        $type = $this->aliases[$type] ?? $type;

        if ($container->has($type)) {
            return $container->get($type);
        }

        if (! $parameter->isOptional()) {
            throw new ServiceNotFoundException(sprintf(
                'Unable to create service "%s"; unable to resolve parameter "%s" using type hint "%s"',
                $requestedName,
                $parameter->getName(),
                $type
            ));
        }

        // Type not available in container, but the value is optional and has a
        // default defined.
        return $parameter->getDefaultValue();
    }
}
