<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\View;

use Interop\Container\ContainerInterface;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\SharedEventManagerInterface;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\I18n\Translator\TranslatorInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Exception\InvalidServiceException;
use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\View\Exception\InvalidHelperException;

/**
 * Plugin manager implementation for view helpers
 *
 * Enforces that helpers retrieved are instances of
 * Helper\HelperInterface. Additionally, it registers a number of default
 * helpers.
 */
class HelperPluginManager extends AbstractPluginManager
{
    /**
     * Default helper aliases
     *
     * Most of these are present for legacy purposes, as v2 of the service
     * manager normalized names when fetching services.
     *
     * @var string[]
     */
    protected $aliases = [
        'asset'               => Helper\Asset::class,
        'Asset'               => Helper\Asset::class,
        'basePath'            => Helper\BasePath::class,
        'BasePath'            => Helper\BasePath::class,
        'basepath'            => Helper\BasePath::class,
        'Cycle'               => Helper\Cycle::class,
        'cycle'               => Helper\Cycle::class,
        'declareVars'         => Helper\DeclareVars::class,
        'DeclareVars'         => Helper\DeclareVars::class,
        'declarevars'         => Helper\DeclareVars::class,
        'Doctype'             => Helper\Doctype::class,
        'doctype'             => Helper\Doctype::class, // overridden by a factory in ViewHelperManagerFactory
        'escapeCss'           => Helper\EscapeCss::class,
        'EscapeCss'           => Helper\EscapeCss::class,
        'escapecss'           => Helper\EscapeCss::class,
        'escapeHtmlAttr'      => Helper\EscapeHtmlAttr::class,
        'EscapeHtmlAttr'      => Helper\EscapeHtmlAttr::class,
        'escapehtmlattr'      => Helper\EscapeHtmlAttr::class,
        'escapeHtml'          => Helper\EscapeHtml::class,
        'EscapeHtml'          => Helper\EscapeHtml::class,
        'escapehtml'          => Helper\EscapeHtml::class,
        'escapeJs'            => Helper\EscapeJs::class,
        'EscapeJs'            => Helper\EscapeJs::class,
        'escapejs'            => Helper\EscapeJs::class,
        'escapeUrl'           => Helper\EscapeUrl::class,
        'EscapeUrl'           => Helper\EscapeUrl::class,
        'escapeurl'           => Helper\EscapeUrl::class,
        'flashmessenger'      => Helper\FlashMessenger::class,
        'flashMessenger'      => Helper\FlashMessenger::class,
        'FlashMessenger'      => Helper\FlashMessenger::class,
        'Gravatar'            => Helper\Gravatar::class,
        'gravatar'            => Helper\Gravatar::class,
        'headLink'            => Helper\HeadLink::class,
        'HeadLink'            => Helper\HeadLink::class,
        'headlink'            => Helper\HeadLink::class,
        'headMeta'            => Helper\HeadMeta::class,
        'HeadMeta'            => Helper\HeadMeta::class,
        'headmeta'            => Helper\HeadMeta::class,
        'headScript'          => Helper\HeadScript::class,
        'HeadScript'          => Helper\HeadScript::class,
        'headscript'          => Helper\HeadScript::class,
        'headStyle'           => Helper\HeadStyle::class,
        'HeadStyle'           => Helper\HeadStyle::class,
        'headstyle'           => Helper\HeadStyle::class,
        'headTitle'           => Helper\HeadTitle::class,
        'HeadTitle'           => Helper\HeadTitle::class,
        'headtitle'           => Helper\HeadTitle::class,
        'htmlflash'           => Helper\HtmlFlash::class,
        'htmlFlash'           => Helper\HtmlFlash::class,
        'HtmlFlash'           => Helper\HtmlFlash::class,
        'htmllist'            => Helper\HtmlList::class,
        'htmlList'            => Helper\HtmlList::class,
        'HtmlList'            => Helper\HtmlList::class,
        'htmlobject'          => Helper\HtmlObject::class,
        'htmlObject'          => Helper\HtmlObject::class,
        'HtmlObject'          => Helper\HtmlObject::class,
        'htmlpage'            => Helper\HtmlPage::class,
        'htmlPage'            => Helper\HtmlPage::class,
        'HtmlPage'            => Helper\HtmlPage::class,
        'htmlquicktime'       => Helper\HtmlQuicktime::class,
        'htmlQuicktime'       => Helper\HtmlQuicktime::class,
        'HtmlQuicktime'       => Helper\HtmlQuicktime::class,
        'htmltag'             => Helper\HtmlTag::class,
        'htmlTag'             => Helper\HtmlTag::class,
        'HtmlTag'             => Helper\HtmlTag::class,
        'identity'            => Helper\Identity::class,
        'Identity'            => Helper\Identity::class,
        'inlinescript'        => Helper\InlineScript::class,
        'inlineScript'        => Helper\InlineScript::class,
        'InlineScript'        => Helper\InlineScript::class,
        'json'                => Helper\Json::class,
        'Json'                => Helper\Json::class,
        'layout'              => Helper\Layout::class,
        'Layout'              => Helper\Layout::class,
        'paginationcontrol'   => Helper\PaginationControl::class,
        'paginationControl'   => Helper\PaginationControl::class,
        'PaginationControl'   => Helper\PaginationControl::class,
        'partial'             => Helper\Partial::class,
        'partialloop'         => Helper\PartialLoop::class,
        'partialLoop'         => Helper\PartialLoop::class,
        'PartialLoop'         => Helper\PartialLoop::class,
        'Partial'             => Helper\Partial::class,
        'placeholder'         => Helper\Placeholder::class,
        'Placeholder'         => Helper\Placeholder::class,
        'renderchildmodel'    => Helper\RenderChildModel::class,
        'renderChildModel'    => Helper\RenderChildModel::class,
        'RenderChildModel'    => Helper\RenderChildModel::class,
        'render_child_model'  => Helper\RenderChildModel::class,
        'rendertoplaceholder' => Helper\RenderToPlaceholder::class,
        'renderToPlaceholder' => Helper\RenderToPlaceholder::class,
        'RenderToPlaceholder' => Helper\RenderToPlaceholder::class,
        'serverurl'           => Helper\ServerUrl::class,
        'serverUrl'           => Helper\ServerUrl::class,
        'ServerUrl'           => Helper\ServerUrl::class,
        'url'                 => Helper\Url::class,
        'Url'                 => Helper\Url::class,
        'view_model'          => Helper\ViewModel::class,
        'viewmodel'           => Helper\ViewModel::class,
        'viewModel'           => Helper\ViewModel::class,
        'ViewModel'           => Helper\ViewModel::class,
    ];

    /**
     * Default factories
     *
     * basepath, doctype, and url are set up as factories in the ViewHelperManagerFactory.
     * basepath and url are not very useful without their factories, however the doctype
     * helper works fine as an invokable. The factory for doctype simply checks for the
     * config value from the merged config.
     *
     * @var array
     */
    protected $factories = [
        Helper\Asset::class               => Helper\Service\AssetFactory::class,
        Helper\FlashMessenger::class      => Helper\Service\FlashMessengerFactory::class,
        Helper\Identity::class            => Helper\Service\IdentityFactory::class,
        Helper\BasePath::class            => InvokableFactory::class,
        Helper\Cycle::class               => InvokableFactory::class,
        Helper\DeclareVars::class         => InvokableFactory::class,
        Helper\Doctype::class             => InvokableFactory::class, // overridden in ViewHelperManagerFactory
        Helper\EscapeHtml::class          => InvokableFactory::class,
        Helper\EscapeHtmlAttr::class      => InvokableFactory::class,
        Helper\EscapeJs::class            => InvokableFactory::class,
        Helper\EscapeCss::class           => InvokableFactory::class,
        Helper\EscapeUrl::class           => InvokableFactory::class,
        Helper\Gravatar::class            => InvokableFactory::class,
        Helper\HtmlTag::class             => InvokableFactory::class,
        Helper\HeadLink::class            => InvokableFactory::class,
        Helper\HeadMeta::class            => InvokableFactory::class,
        Helper\HeadScript::class          => InvokableFactory::class,
        Helper\HeadStyle::class           => InvokableFactory::class,
        Helper\HeadTitle::class           => InvokableFactory::class,
        Helper\HtmlFlash::class           => InvokableFactory::class,
        Helper\HtmlList::class            => InvokableFactory::class,
        Helper\HtmlObject::class          => InvokableFactory::class,
        Helper\HtmlPage::class            => InvokableFactory::class,
        Helper\HtmlQuicktime::class       => InvokableFactory::class,
        Helper\InlineScript::class        => InvokableFactory::class,
        Helper\Json::class                => InvokableFactory::class,
        Helper\Layout::class              => InvokableFactory::class,
        Helper\PaginationControl::class   => InvokableFactory::class,
        Helper\PartialLoop::class         => InvokableFactory::class,
        Helper\Partial::class             => InvokableFactory::class,
        Helper\Placeholder::class         => InvokableFactory::class,
        Helper\RenderChildModel::class    => InvokableFactory::class,
        Helper\RenderToPlaceholder::class => InvokableFactory::class,
        Helper\ServerUrl::class           => InvokableFactory::class,
        Helper\Url::class                 => InvokableFactory::class,
        Helper\ViewModel::class           => InvokableFactory::class,

        // v2 canonical FQCNs

        'zendviewhelperasset'             => Helper\Service\AssetFactory::class,
        'zendviewhelperflashmessenger'    => Helper\Service\FlashMessengerFactory::class,
        'zendviewhelperidentity'          => Helper\Service\IdentityFactory::class,
        'zendviewhelperbasepath'          => InvokableFactory::class,
        'zendviewhelpercycle'             => InvokableFactory::class,
        'zendviewhelperdeclarevars'       => InvokableFactory::class,
        'zendviewhelperdoctype'           => InvokableFactory::class,
        'zendviewhelperescapehtml'        => InvokableFactory::class,
        'zendviewhelperescapehtmlattr'    => InvokableFactory::class,
        'zendviewhelperescapejs'          => InvokableFactory::class,
        'zendviewhelperescapecss'         => InvokableFactory::class,
        'zendviewhelperescapeurl'         => InvokableFactory::class,
        'zendviewhelpergravatar'          => InvokableFactory::class,
        'zendviewhelperhtmltag'           => InvokableFactory::class,
        'zendviewhelperheadlink'          => InvokableFactory::class,
        'zendviewhelperheadmeta'          => InvokableFactory::class,
        'zendviewhelperheadscript'        => InvokableFactory::class,
        'zendviewhelperheadstyle'         => InvokableFactory::class,
        'zendviewhelperheadtitle'         => InvokableFactory::class,
        'zendviewhelperhtmlflash'         => InvokableFactory::class,
        'zendviewhelperhtmllist'          => InvokableFactory::class,
        'zendviewhelperhtmlobject'        => InvokableFactory::class,
        'zendviewhelperhtmlpage'          => InvokableFactory::class,
        'zendviewhelperhtmlquicktime'     => InvokableFactory::class,
        'zendviewhelperinlinescript'      => InvokableFactory::class,
        'zendviewhelperjson'              => InvokableFactory::class,
        'zendviewhelperlayout'            => InvokableFactory::class,
        'zendviewhelperpaginationcontrol' => InvokableFactory::class,
        'zendviewhelperpartialloop'       => InvokableFactory::class,
        'zendviewhelperpartial'           => InvokableFactory::class,
        'zendviewhelperplaceholder'       => InvokableFactory::class,
        'zendviewhelperrenderchildmodel'  => InvokableFactory::class,
        'zendviewhelperrendertoplaceholder' => InvokableFactory::class,
        'zendviewhelperserverurl'         => InvokableFactory::class,
        'zendviewhelperurl'               => InvokableFactory::class,
        'zendviewhelperviewmodel'         => InvokableFactory::class,
    ];

    /**
     * @var Renderer\RendererInterface
     */
    protected $renderer;

    /**
     * Constructor
     *
     * Merges provided configuration with default configuration.
     *
     * Adds initializers to inject the attached renderer and translator, if
     * any, to the currently requested helper.
     *
     * @param null|ConfigInterface|ContainerInterface $configOrContainerInstance
     * @param array $v3config If $configOrContainerInstance is a container, this
     *     value will be passed to the parent constructor.
     */
    public function __construct($configOrContainerInstance = null, array $v3config = [])
    {
        $this->initializers[] = [$this, 'injectRenderer'];
        $this->initializers[] = [$this, 'injectTranslator'];
        $this->initializers[] = [$this, 'injectEventManager'];

        parent::__construct($configOrContainerInstance, $v3config);
    }

    /**
     * Set renderer
     *
     * @param  Renderer\RendererInterface $renderer
     * @return HelperPluginManager
     */
    public function setRenderer(Renderer\RendererInterface $renderer)
    {
        $this->renderer = $renderer;

        return $this;
    }

    /**
     * Retrieve renderer instance
     *
     * @return null|Renderer\RendererInterface
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    /**
     * Inject a helper instance with the registered renderer
     *
     * @param ContainerInterface|Helper\HelperInterface $first helper instance
     *     under zend-servicemanager v2, ContainerInterface under v3.
     * @param ContainerInterface|Helper\HelperInterface $second
     *     ContainerInterface under zend-servicemanager v3, helper instance
     *     under v2. Ignored regardless.
     */
    public function injectRenderer($first, $second)
    {
        $helper = ($first instanceof ContainerInterface)
            ? $second
            : $first;

        if (! $helper instanceof Helper\HelperInterface) {
            return;
        }

        $renderer = $this->getRenderer();
        if (null === $renderer) {
            return;
        }
        $helper->setView($renderer);
    }

    /**
     * Inject a helper instance with the registered translator
     *
     * @param ContainerInterface|Helper\HelperInterface $first helper instance
     *     under zend-servicemanager v2, ContainerInterface under v3.
     * @param ContainerInterface|Helper\HelperInterface $second
     *     ContainerInterface under zend-servicemanager v3, helper instance
     *     under v2. Ignored regardless.
     */
    public function injectTranslator($first, $second)
    {
        if ($first instanceof ContainerInterface) {
            // v3 usage
            $container = $first;
            $helper = $second;
        } else {
            // v2 usage; grab the parent container
            $container = $second->getServiceLocator();
            $helper = $first;
        }

        // Allow either direct implementation or duck-typing.
        if (! $helper instanceof TranslatorAwareInterface
            && ! method_exists($helper, 'setTranslator')
        ) {
            return;
        }

        if (! $container) {
            // Under zend-navigation v2.5, the navigation PluginManager is
            // always lazy-loaded, which means it never has a parent
            // container.
            return;
        }

        if (method_exists($helper, 'hasTranslator') && $helper->hasTranslator()) {
            return;
        }

        if ($container->has('MvcTranslator')) {
            $helper->setTranslator($container->get('MvcTranslator'));
            return;
        }

        if ($container->has(TranslatorInterface::class)) {
            $helper->setTranslator($container->get(TranslatorInterface::class));
            return;
        }

        if ($container->has('Translator')) {
            $helper->setTranslator($container->get('Translator'));
            return;
        }
    }

    /**
     * Inject a helper instance with the registered event manager
     *
     * @param ContainerInterface|Helper\HelperInterface $first helper instance
     *     under zend-servicemanager v2, ContainerInterface under v3.
     * @param ContainerInterface|Helper\HelperInterface $second
     *     ContainerInterface under zend-servicemanager v3, helper instance
     *     under v2. Ignored regardless.
     */
    public function injectEventManager($first, $second)
    {
        if ($first instanceof ContainerInterface) {
            // v3 usage
            $container = $first;
            $helper = $second;
        } else {
            // v2 usage; grab the parent container
            $container = $second->getServiceLocator();
            $helper = $first;
        }

        if (! $container) {
            // Under zend-navigation v2.5, the navigation PluginManager is
            // always lazy-loaded, which means it never has a parent
            // container.
            return;
        }

        if (! $helper instanceof EventManagerAwareInterface) {
            return;
        }

        if (! $container->has('EventManager')) {
            // If the container doesn't have an EM service, do nothing.
            return;
        }

        $events = $helper->getEventManager();
        if (! $events || ! $events->getSharedManager() instanceof SharedEventManagerInterface) {
            $helper->setEventManager($container->get('EventManager'));
        }
    }

    /**
     * Validate the plugin is of the expected type (v3).
     *
     * Validates against callables and HelperInterface implementations.
     *
     * @param mixed $instance
     * @throws InvalidServiceException
     */
    public function validate($instance)
    {
        if (! is_callable($instance) && ! $instance instanceof Helper\HelperInterface) {
            throw new InvalidServiceException(
                sprintf(
                    '%s can only create instances of %s and/or callables; %s is invalid',
                    get_class($this),
                    Helper\HelperInterface::class,
                    (is_object($instance) ? get_class($instance) : gettype($instance))
                )
            );
        }
    }

    /**
     * Validate the plugin is of the expected type (v2).
     *
     * Proxies to `validate()`.
     *
     * @param mixed $instance
     * @throws InvalidHelperException
     */
    public function validatePlugin($instance)
    {
        try {
            $this->validate($instance);
        } catch (InvalidServiceException $e) {
            throw new InvalidHelperException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
