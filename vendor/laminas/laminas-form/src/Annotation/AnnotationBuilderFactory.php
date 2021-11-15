<?php

/**
 * @see       https://github.com/laminas/laminas-form for the canonical source repository
 * @copyright https://github.com/laminas/laminas-form/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-form/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Form\Annotation;

use Interop\Container\ContainerInterface;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\Form\Factory;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

use function is_array;
use function sprintf;

class AnnotationBuilderFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  string $name
     * @param  null|array $options
     * @return AnnotationBuilder
     * @throws ServiceNotCreatedException for invalid listener configuration.
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        //setup a form factory which can use custom form elements
        $annotationBuilder = new AnnotationBuilder();
        $eventManager      = $container->get('EventManager');
        $annotationBuilder->setEventManager($eventManager);

        $this->injectFactory($annotationBuilder->getFormFactory(), $container);

        $config = $this->marshalConfig($container);
        if (isset($config['preserve_defined_order'])) {
            $annotationBuilder->setPreserveDefinedOrder($config['preserve_defined_order']);
        }

        $this->injectAnnotations($config, $annotationBuilder);
        $this->injectListeners($config, $eventManager, $container);

        return $annotationBuilder;
    }

    /**
     * Create and return AnnotationBuilder instance
     *
     * For use with laminas-servicemanager v2; proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @return AnnotationBuilder
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, AnnotationBuilder::class);
    }

    /**
     * Marshal annotation builder configuration, if any.
     *
     * Looks for the `config` service in the container, returning an empty array
     * if not found.
     *
     * If found, checks for a `form_annotation_builder` entry, returning an empty
     * array if not found or not an array.
     *
     * Otherwise, returns the `form_annotation_builder` array.
     *
     * @param ContainerInterface $container
     * @return array
     */
    private function marshalConfig(ContainerInterface $container)
    {
        if (! $container->has('config')) {
            return [];
        }

        $config = $container->get('config');
        $config = isset($config['form_annotation_builder'])
            ? $config['form_annotation_builder']
            : [];

        return is_array($config) ? $config : [];
    }

    /**
     * Inject annotations from configuration, if any.
     *
     * @param array $config
     * @param AnnotationBuilder $builder
     * @return void
     */
    private function injectAnnotations(array $config, AnnotationBuilder $builder)
    {
        if (! isset($config['annotations'])) {
            return;
        }

        $parser = $builder->getAnnotationParser();
        foreach ($config['annotations'] as $fullyQualifiedClassName) {
            $parser->registerAnnotation($fullyQualifiedClassName);
        }
    }

    /**
     * Inject event listeners from configuration, if any.
     *
     * Loops through the 'listeners' array, and:
     *
     * - attempts to fetch it from the container
     * - if the fetched instance is not a `ListenerAggregate`, raises an exception
     * - otherwise attaches it to the event manager
     *
     * @param array $config
     * @param EventManagerInterface $events
     * @param ContainerInterface $container
     * @return void
     * @throws ServiceNotCreatedException if any listener is not an event listener
     *     aggregate.
     */
    private function injectListeners(array $config, EventManagerInterface $events, ContainerInterface $container)
    {
        if (! isset($config['listeners'])) {
            return;
        }

        foreach ($config['listeners'] as $listenerName) {
            $listener = $container->get($listenerName);

            if (! $listener instanceof ListenerAggregateInterface) {
                throw new ServiceNotCreatedException(sprintf('Invalid event listener (%s) provided', $listenerName));
            }

            $listener->attach($events);
        }
    }

    /**
     * Inject the annotation builder's factory instance with the FormElementManager.
     *
     * Also injects the factory with the InputFilterManager if present.
     *
     * @param Factory $factory
     * @param ContainerInterface $container
     */
    private function injectFactory(Factory $factory, ContainerInterface $container)
    {
        $factory->setFormElementManager($container->get('FormElementManager'));

        if ($container->has('InputFilterManager')) {
            $inputFilters = $container->get('InputFilterManager');
            $factory->getInputFilterFactory()->setInputFilterManager($inputFilters);
        }
    }
}
