<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Service;

use Interop\Container\ContainerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\Form\FormElementManager\FormElementManagerV2Polyfill;
use Zend\Form\FormElementManager\FormElementManagerV3Polyfill;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class FormAnnotationBuilderFactory implements FactoryInterface
{
    /**
     * Create service
     *
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
        $eventManager       = $container->get('EventManager');
        $annotationBuilder->setEventManager($eventManager);

        $formElementManager = $container->get('FormElementManager');

        $this->injectFactory($formElementManager, $container, $annotationBuilder);

        $config = $container->get('config');
        if (isset($config['form_annotation_builder'])) {
            $config = $config['form_annotation_builder'];

            if (isset($config['annotations'])) {
                foreach ((array) $config['annotations'] as $fullyQualifiedClassName) {
                    $annotationBuilder->getAnnotationParser()->registerAnnotation($fullyQualifiedClassName);
                }
            }

            if (isset($config['listeners'])) {
                foreach ((array) $config['listeners'] as $listenerName) {
                    $listener = $container->get($listenerName);
                    if (!($listener instanceof ListenerAggregateInterface)) {
                        throw new ServiceNotCreatedException(sprintf('Invalid event listener (%s) provided', $listenerName));
                    }
                    $listener->attach($eventManager);
                }
            }

            if (isset($config['preserve_defined_order'])) {
                $annotationBuilder->setPreserveDefinedOrder($config['preserve_defined_order']);
            }
        }

        return $annotationBuilder;
    }

    /**
     * Create and return AnnotationBuilder instance
     *
     * For use with zend-servicemanager v2; proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @return AnnotationBuilder
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, AnnotationBuilder::class);
    }

    /**
     * Handle zend-servicemanager dependent InitializerInterface signature
     *
     * @param FormElementManagerV2Polyfill|FormElementManagerV3Polyfill $formElementManager
     * @param ContainerInterface                                        $container
     * @param AnnotationBuilder                                         $annotationBuilder
     *
     * @return void
     */
    private function injectFactory(
        $formElementManager,
        ContainerInterface $container,
        AnnotationBuilder $annotationBuilder
    ) {
        if ($formElementManager instanceof FormElementManagerV2Polyfill) {
            $formElementManager->injectFactory($annotationBuilder, $formElementManager);
            return;
        }

        if ($formElementManager instanceof FormElementManagerV3Polyfill) {
            $formElementManager->injectFactory($container, $annotationBuilder);
        }
    }
}
