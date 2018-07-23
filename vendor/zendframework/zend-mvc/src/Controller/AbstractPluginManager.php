<?php
/**
 * @see       https://github.com/zendframework/zend-mvc for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-mvc/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Mvc\Controller;

use Zend\Mvc\Exception;
use Zend\ServiceManager\AbstractPluginManager as BasePluginManager;
use Zend\ServiceManager\Exception\InvalidServiceException;
use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\Stdlib\DispatchableInterface;

/**
 * Base functionality for the controller plugins plugin manager.
 *
 * Functionality is split between two concrete implementations as the signatures
 * for `get()` vary between zend-servicemanager v2 and v3. The autoloader aliases
 * `Zend\Mvc\Controller\PluginManager` to the version-appropriate class, which
 * in turn composses this trait.
 */
abstract class AbstractPluginManager extends BasePluginManager
{
    /**
     * Plugins must be of this type.
     *
     * @var string
     */
    protected $instanceOf = Plugin\PluginInterface::class;

    /**
     * @var string[] Default aliases
     */
    protected $aliases = [
        'AcceptableViewModelSelector' => Plugin\AcceptableViewModelSelector::class,
        'acceptableViewModelSelector' => Plugin\AcceptableViewModelSelector::class,
        'acceptableviewmodelselector' => Plugin\AcceptableViewModelSelector::class,
        'FilePostRedirectGet'         => Plugin\FilePostRedirectGet::class,
        'filePostRedirectGet'         => Plugin\FilePostRedirectGet::class,
        'filepostredirectget'         => Plugin\FilePostRedirectGet::class,
        'fileprg'                     => Plugin\FilePostRedirectGet::class,
        'FlashMessenger'              => Plugin\FlashMessenger::class,
        'flashMessenger'              => Plugin\FlashMessenger::class,
        'flashmessenger'              => Plugin\FlashMessenger::class,
        'Forward'                     => Plugin\Forward::class,
        'forward'                     => Plugin\Forward::class,
        'Identity'                    => Plugin\Identity::class,
        'identity'                    => Plugin\Identity::class,
        'Layout'                      => Plugin\Layout::class,
        'layout'                      => Plugin\Layout::class,
        'Params'                      => Plugin\Params::class,
        'params'                      => Plugin\Params::class,
        'PostRedirectGet'             => Plugin\PostRedirectGet::class,
        'postRedirectGet'             => Plugin\PostRedirectGet::class,
        'postredirectget'             => Plugin\PostRedirectGet::class,
        'prg'                         => Plugin\PostRedirectGet::class,
        'Redirect'                    => Plugin\Redirect::class,
        'redirect'                    => Plugin\Redirect::class,
        'Url'                         => Plugin\Url::class,
        'url'                         => Plugin\Url::class,
        'CreateHttpNotFoundModel'     => Plugin\CreateHttpNotFoundModel::class,
        'createHttpNotFoundModel'     => Plugin\CreateHttpNotFoundModel::class,
        'createhttpnotfoundmodel'     => Plugin\CreateHttpNotFoundModel::class,
        'CreateConsoleNotFoundModel'  => Plugin\CreateConsoleNotFoundModel::class,
        'createConsoleNotFoundModel'  => Plugin\CreateConsoleNotFoundModel::class,
        'createconsolenotfoundmodel'  => Plugin\CreateConsoleNotFoundModel::class,
    ];

    /**
     * @var string[]|callable[] Default factories
     */
    protected $factories = [
        Plugin\Forward::class                     => Plugin\Service\ForwardFactory::class,
        Plugin\Identity::class                    => Plugin\Service\IdentityFactory::class,
        Plugin\AcceptableViewModelSelector::class => InvokableFactory::class,
        Plugin\FilePostRedirectGet::class         => InvokableFactory::class,
        Plugin\FlashMessenger::class              => InvokableFactory::class,
        Plugin\Layout::class                      => InvokableFactory::class,
        Plugin\Params::class                      => InvokableFactory::class,
        Plugin\PostRedirectGet::class             => InvokableFactory::class,
        Plugin\Redirect::class                    => InvokableFactory::class,
        Plugin\Url::class                         => InvokableFactory::class,
        Plugin\CreateHttpNotFoundModel::class     => InvokableFactory::class,
        Plugin\CreateConsoleNotFoundModel::class  => InvokableFactory::class,

        // v2 normalized names

        'zendmvccontrollerpluginforward'                     => Plugin\Service\ForwardFactory::class,
        'zendmvccontrollerpluginidentity'                    => Plugin\Service\IdentityFactory::class,
        'zendmvccontrollerpluginacceptableviewmodelselector' => InvokableFactory::class,
        'zendmvccontrollerpluginfilepostredirectget'         => InvokableFactory::class,
        'zendmvccontrollerpluginflashmessenger'              => InvokableFactory::class,
        'zendmvccontrollerpluginlayout'                      => InvokableFactory::class,
        'zendmvccontrollerpluginparams'                      => InvokableFactory::class,
        'zendmvccontrollerpluginpostredirectget'             => InvokableFactory::class,
        'zendmvccontrollerpluginredirect'                    => InvokableFactory::class,
        'zendmvccontrollerpluginurl'                         => InvokableFactory::class,
        'zendmvccontrollerplugincreatehttpnotfoundmodel'     => InvokableFactory::class,
        'zendmvccontrollerplugincreateconsolenotfoundmodel'  => InvokableFactory::class,
    ];

    /**
     * @var DispatchableInterface
     */
    protected $controller;

    /**
     * Set controller
     *
     * @param  DispatchableInterface $controller
     * @return PluginManager
     */
    public function setController(DispatchableInterface $controller)
    {
        $this->controller = $controller;

        return $this;
    }

    /**
     * Retrieve controller instance
     *
     * @return null|DispatchableInterface
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Inject a helper instance with the registered controller
     *
     * @param  object $plugin
     * @return void
     */
    public function injectController($plugin)
    {
        if (!is_object($plugin)) {
            return;
        }
        if (!method_exists($plugin, 'setController')) {
            return;
        }

        $controller = $this->getController();
        if (!$controller instanceof DispatchableInterface) {
            return;
        }

        $plugin->setController($controller);
    }

    /**
     * Validate a plugin (v3)
     *
     * {@inheritDoc}
     */
    public function validate($plugin)
    {
        if (! $plugin instanceof $this->instanceOf) {
            throw new InvalidServiceException(sprintf(
                'Plugin of type "%s" is invalid; must implement %s',
                (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
                $this->instanceOf
            ));
        }
    }

    /**
     * Validate a plugin (v2)
     *
     * {@inheritDoc}
     *
     * @throws Exception\InvalidPluginException
     */
    public function validatePlugin($plugin)
    {
        try {
            $this->validate($plugin);
        } catch (InvalidServiceException $e) {
            throw new Exception\InvalidPluginException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }
}
