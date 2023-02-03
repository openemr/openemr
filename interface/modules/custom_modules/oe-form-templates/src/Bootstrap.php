<?php

/**
 * Bootstrap Forms Template Engine.
 *
 * @author    Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2022-2023 Providence Healthtech
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\FormTemplates;

/**
 * Note the below use statements are importing classes from the OpenEMR core codebase
 */
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Kernel;
use OpenEMR\Events\Core\TwigEnvironmentEvent;
use OpenEMR\Events\Globals\GlobalsInitializedEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Twig\Error\LoaderError;
use Twig\Loader\FilesystemLoader;
use OpenEMR\Events\Main\Tabs\RenderEvent;
use OpenEMR\Menu\MenuEvent;

class Bootstrap
{
    const MODULE_INSTALLATION_PATH = "/interface/modules/custom_modules/";

    const MODULE_DISPLAY_NAME = "Form Templates";

    const MODULE_MACHINE_NAME = "oe-form-templates";

    const TEMPLATES_TABLE = "form_templates_template";

    const FORMS_TABLE = "form_templates_form";

    /**
     * @var EventDispatcherInterface The object responsible for sending and subscribing to events through the OpenEMR system
     */
    private $eventDispatcher;

    /**
     * @var GlobalConfig Holds our module global configuration values that can be used throughout the module.
     */
    private $globalsConfig;

    /**
     * @var string The folder name of the module.  Set dynamically from searching the filesystem.
     */
    private $moduleDirectoryName;

    /**
     * @var \Twig\Environment The twig rendering environment
     */
    private $twig;

    /**
     * @var SystemLogger
     */
    private $logger;

    public function __construct(EventDispatcherInterface $eventDispatcher, ?Kernel $kernel = null)
    {
        global $GLOBALS;

        if (empty($kernel)) {
            $kernel = new Kernel();
        }

        // NOTE: eventually you will be able to pull the twig container directly from the kernel instead of instantiating
        // it here.
        $twig = new TwigContainer($this->getTemplatePath(), $kernel);
        $twigEnv = $twig->getTwig();
        $this->twig = $twigEnv;

        $this->moduleDirectoryName = basename(__DIR__);
        $this->eventDispatcher = $eventDispatcher;
    }

    static public function moduleURL(string $controller, string $action): string
    {
        $url = "%DIR%%NAME%/index.php?controller=%C%&action=%A%";
        $replacements = [
            "%DIR%" => self::MODULE_INSTALLATION_PATH,
            "%NAME%" => self::MODULE_MACHINE_NAME,
            "%C%" => $controller,
            "%A%" => $action
        ];
        return str_replace(array_keys($replacements), array_values($replacements), $url);
    }

    public function subscribeToEvents()
    {
        // $this->addGlobalSettings();
        $this->registerTemplateEvents();
        $this->registerMenuEvents();

        // we only add the rest of our event listeners and configuration if we have been fully setup and configured
        // if ($this->globalsConfig->isConfigured()) {
        // }
    }

    public function registerMenuEvents()
    {
        $this->eventDispatcher->addListener(MenuEvent::MENU_UPDATE, [$this, 'addMenuItem']);
    }

    /**
     * We tie into any events dealing with the templates / page rendering of the system here
     */
    public function registerTemplateEvents()
    {
        $this->eventDispatcher->addListener(TwigEnvironmentEvent::EVENT_CREATED, [$this, 'addTemplateOverrideLoader']);
    }

    public function addMenuItem(MenuEvent $event)
    {
        $menu = $event->getMenu();

        $menuItem = new \stdClass();
        $menuItem->requirement = 0;
        $menuItem->target = 'mod';
        $menuItem->menu_id = 'mod_form_template';
        $menuItem->label = xlt(self::MODULE_DISPLAY_NAME);
        $menuItem->url = self::MODULE_INSTALLATION_PATH . "oe-form-templates/index.php?controller=configuration";
        $menuItem->children = [];
        $menuItem->acl_req = ['admin', 'super'];
        $menuItem->global_req = [];

        foreach ($menu as $item) {
            if ($item->menu_id == 'admimg') {
                $item->children[] = $menuItem;
                break;
            }
        }

        $event->setMenu($menu);
        return $event;
    }

    /**
     * @param TwigEnvironmentEvent $event
     */
    public function addTemplateOverrideLoader(TwigEnvironmentEvent $event)
    {
        try {
            $twig = $event->getTwigEnvironment();
            if ($twig === $this->twig) {
                // we do nothing if its our own twig environment instantiated that we already setup
                return;
            }
            // we make sure we can override our file system directory here.
            $loader = $twig->getLoader();
            if ($loader instanceof FilesystemLoader) {
                $loader->prependPath($this->getTemplatePath());
            }
        } catch (LoaderError $error) {
            $this->logger->errorLogCaller("Failed to create template loader", ['innerMessage' => $error->getMessage(), 'trace' => $error->getTraceAsString()]);
        }
    }

    private function getPublicPath()
    {
        return self::MODULE_INSTALLATION_PATH . ($this->moduleDirectoryName ?? '') . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR;
    }

    private function getAssetPath()
    {
        return $this->getPublicPath() . 'assets' . DIRECTORY_SEPARATOR;
    }

    static public function getTemplatePath()
    {
        return \dirname(__DIR__) . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR;
    }
}
