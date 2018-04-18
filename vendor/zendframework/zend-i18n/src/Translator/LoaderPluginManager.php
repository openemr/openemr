<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\I18n\Translator;

use Zend\I18n\Exception;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Exception\InvalidServiceException;
use Zend\ServiceManager\Factory\InvokableFactory;

/**
 * Plugin manager implementation for translation loaders.
 *
 * Enforces that loaders retrieved are either instances of
 * Loader\FileLoaderInterface or Loader\RemoteLoaderInterface. Additionally,
 * it registers a number of default loaders.
 *
 * If you are wanting to use the ability to load translation files from the
 * include_path, you will need to create a factory to override the defaults
 * defined in this class. A simple factory might look like:
 *
 * <code>
 * function ($translators) {
 *     $adapter = new Gettext();
 *     $adapter->setUseIncludePath(true);
 *     return $adapter;
 * }
 * </code>
 *
 * You may need to override the Translator service factory to make this happen
 * more easily. That can be done by extending it:
 *
 * <code>
 * use Zend\I18n\Translator\TranslatorServiceFactory;
 * // or Zend\Mvc\I18n\TranslatorServiceFactory
 * use Zend\ServiceManager\ServiceLocatorInterface;
 *
 * class MyTranslatorServiceFactory extends TranslatorServiceFactory
 * {
 *     public function createService(ServiceLocatorInterface $services)
 *     {
 *         $translator = parent::createService($services);
 *         $translator->getLoaderPluginManager()->setFactory(...);
 *         return $translator;
 *     }
 * }
 * </code>
 *
 * You would then specify your custom factory in your service configuration.
 */
class LoaderPluginManager extends AbstractPluginManager
{
    protected $aliases = [
        'gettext'  => Loader\Gettext::class,
        'getText'  => Loader\Gettext::class,
        'GetText'  => Loader\Gettext::class,
        'ini'      => Loader\Ini::class,
        'phparray' => Loader\PhpArray::class,
        'phpArray' => Loader\PhpArray::class,
        'PhpArray' => Loader\PhpArray::class,
    ];

    protected $factories = [
        Loader\Gettext::class  => InvokableFactory::class,
        Loader\Ini::class      => InvokableFactory::class,
        Loader\PhpArray::class => InvokableFactory::class,
        // Legacy (v2) due to alias resolution; canonical form of resolved
        // alias is used to look up the factory, while the non-normalized
        // resolved alias is used as the requested name passed to the factory.
        'zendi18ntranslatorloadergettext'  => InvokableFactory::class,
        'zendi18ntranslatorloaderini'      => InvokableFactory::class,
        'zendi18ntranslatorloaderphparray' => InvokableFactory::class
    ];

    /**
     * Validate the plugin.
     *
     * Checks that the filter loaded is an instance of
     * Loader\FileLoaderInterface or Loader\RemoteLoaderInterface.
     *
     * @param  mixed $plugin
     * @return void
     * @throws Exception\RuntimeException if invalid
     */
    public function validate($plugin)
    {
        if ($plugin instanceof Loader\FileLoaderInterface || $plugin instanceof Loader\RemoteLoaderInterface) {
            // we're okay
            return;
        }

        throw new InvalidServiceException(sprintf(
            'Plugin of type %s is invalid; must implement %s\Loader\FileLoaderInterface '
            . 'or %s\Loader\RemoteLoaderInterface',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
            __NAMESPACE__,
            __NAMESPACE__
        ));
    }

    /**
     * Validate the plugin is of the expected type (v2).
     *
     * Proxies to `validate()`.
     *
     * @param mixed $plugin
     * @throws Exception\RuntimeException
     */
    public function validatePlugin($plugin)
    {
        try {
            $this->validate($plugin);
        } catch (InvalidServiceException $e) {
            throw new Exception\RuntimeException(sprintf(
                'Plugin of type %s is invalid; must implement %s\Loader\FileLoaderInterface '
                . 'or %s\Loader\RemoteLoaderInterface',
                (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
                __NAMESPACE__,
                __NAMESPACE__
            ));
        }
    }
}
