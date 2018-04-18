<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\InputFilter;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Exception\InvalidServiceException;
use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\Stdlib\InitializableInterface;

/**
 * Plugin manager implementation for input filters.
 *
 * @method InputFilterInterface|InputInterface get($name)
 */
class InputFilterPluginManager extends AbstractPluginManager
{
    /**
     * Default alias of plugins
     *
     * @var string[]
     */
    protected $aliases = [
        'inputfilter'         => InputFilter::class,
        'inputFilter'         => InputFilter::class,
        'InputFilter'         => InputFilter::class,
        'collection'          => CollectionInputFilter::class,
        'Collection'          => CollectionInputFilter::class,
        'optionalinputfilter' => OptionalInputFilter::class,
        'optionalInputFilter' => OptionalInputFilter::class,
        'OptionalInputFilter' => OptionalInputFilter::class,
    ];

    /**
     * Default set of plugins
     *
     * @var string[]
     */
    protected $factories = [
        InputFilter::class                      => InvokableFactory::class,
        CollectionInputFilter::class            => InvokableFactory::class,
        OptionalInputFilter::class              => InvokableFactory::class,
        // v2 canonical FQCN
        'zendinputfilterinputfilter'            => InvokableFactory::class,
        'zendinputfiltercollectioninputfilter'  => InvokableFactory::class,
        'zendinputfilteroptionalinputfilter'    => InvokableFactory::class,
    ];

    /**
     * Whether or not to share by default (v3)
     *
     * @var bool
     */
    protected $sharedByDefault = false;

    /**
     * Whether or not to share by default (v2)
     *
     * @var bool
     */
    protected $shareByDefault = false;

    /**
     * @param null|\Zend\ServiceManager\ConfigInterface|ContainerInterface $configOrContainer
     *     For zend-servicemanager v2, null or a ConfigInterface instance are
     *     allowed; for v3, a ContainerInterface is expected.
     * @param array $v3config Optional configuration array (zend-servicemanager v3 only)
     */
    public function __construct($configOrContainer = null, array $v3config = [])
    {
        $this->initializers[] = [$this, 'populateFactory'];
        parent::__construct($configOrContainer, $v3config);
    }

    /**
     * Inject this and populate the factory with filter chain and validator chain
     *
     * @param ContainerInterface|InputFilter $containerOrInputFilter    When using ServiceManager v3
     *                                                                  this will be the plugin manager instance
     * @param InputFilter                    $inputFilter               This is only used with ServiceManager v3
     */
    public function populateFactory($containerOrInputFilter, $inputFilter = null)
    {
        $inputFilter = $containerOrInputFilter instanceof ContainerInterface ? $inputFilter : $containerOrInputFilter;

        if (! $inputFilter instanceof InputFilter) {
            return;
        }

        $factory = $inputFilter->getFactory();
        $factory->setInputFilterManager($this);
    }

    /**
     * Populate the filter and validator managers for the default filter/validator chains.
     *
     * @param Factory $factory
     * @return void
     */
    public function populateFactoryPluginManagers(Factory $factory)
    {
        $container = property_exists($this, 'creationContext')
            ? $this->creationContext // v3
            : $this->serviceLocator; // v2

        if ($container && $container->has('FilterManager')) {
            $factory->getDefaultFilterChain()->setPluginManager($container->get('FilterManager'));
        }

        if ($container && $container->has('ValidatorManager')) {
            $factory->getDefaultValidatorChain()->setPluginManager($container->get('ValidatorManager'));
        }
    }

    /**
     * {@inheritDoc} (v3)
     */
    public function validate($plugin)
    {
        if ($plugin instanceof InputFilterInterface || $plugin instanceof InputInterface) {
            // Hook to perform various initialization, when the inputFilter is not created through the factory
            if ($plugin instanceof InitializableInterface) {
                $plugin->init();
            }

            // we're okay
            return;
        }

        throw new InvalidServiceException(sprintf(
            'Plugin of type %s is invalid; must implement %s or %s',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
            InputFilterInterface::class,
            InputInterface::class
        ));
    }

    /**
     * Validate the plugin (v2)
     *
     * Checks that the filter loaded is either a valid callback or an instance
     * of FilterInterface.
     *
     * @param  mixed                      $plugin
     * @return void
     * @throws Exception\RuntimeException if invalid
     */
    public function validatePlugin($plugin)
    {
        try {
            $this->validate($plugin);
        } catch (InvalidServiceException $e) {
            throw new Exception\RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
